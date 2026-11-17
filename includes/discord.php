<?php
/**
 * Discord notifications via webhooks.
 *
 * Two optional webhooks (config.local.php or environment variables):
 *   discord.webhook        public channel: results, weekend previews, reminders, news
 *   discord.admin_webhook  private channel: contact messages and task errors
 *                          (falls back to nothing — admin events are never posted publicly)
 *
 * scripts/discord-notify.php runs every few minutes, detects new data and
 * remembers what was posted in storage/discord-state.json so nothing is sent twice.
 */
declare(strict_types=1);

const DISCORD_STATE_FILE = STORAGE_DIR . '/discord-state.json';
const DISCORD_RED = 0xE10600;
const DISCORD_REMINDER_MINUTES = 30;
defined('DISCORD_RESULT_WINDOW_DAYS') || define('DISCORD_RESULT_WINDOW_DAYS', 7);

function discord_webhook(string $channel = 'public'): ?string
{
    $url = (string)config($channel === 'admin' ? 'discord.admin_webhook' : 'discord.webhook', '');
    return preg_match('#^https://(?:ptb\.|canary\.)?discord(?:app)?\.com/api/webhooks/\d+/[\w-]+$#', $url) ? $url : null;
}

function discord_enabled(string $channel = 'public'): bool
{
    return discord_webhook($channel) !== null;
}

/**
 * Post a message to a Discord webhook. Returns true on success.
 *
 * @param list<array> $embeds Discord embed objects (max 10)
 */
function discord_send(array $embeds, string $channel = 'public', string $content = ''): bool
{
    $url = discord_webhook($channel);
    if (!$url) {
        return false;
    }
    $payload = [
        'username'         => 'F1SITE.NL',
        'avatar_url'       => url('afbeeldingen/logo/f1_icon.png'),
        'content'          => mb_substr($content, 0, 2000),
        'embeds'           => array_slice($embeds, 0, 10),
        'allowed_mentions' => ['parse' => []],
    ];
    for ($attempt = 0; $attempt < 2; $attempt++) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_USERAGENT      => HTTP_USER_AGENT,
        ]);
        $body = (string)curl_exec($ch);
        $status = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        if ($status >= 200 && $status < 300) {
            return true;
        }
        if ($status === 429) {
            // Rate limited: Discord tells us how long to wait.
            $wait = (float)(json_decode($body, true)['retry_after'] ?? 1);
            usleep((int)(min(5, max(0.5, $wait)) * 1_000_000));
            continue;
        }
        error_log("Discord webhook failed ($status): " . mb_substr($body, 0, 300));
        return false;
    }
    return false;
}

/** Discord timestamp that every member sees in their own timezone. */
function discord_time(DateTimeInterface $dt, string $style = 'F'): string
{
    return '<t:' . $dt->getTimestamp() . ':' . $style . '>';
}

function discord_color(string $hex): int
{
    return (int)hexdec(ltrim(safe_color($hex, '#e10600'), '#'));
}

/* ---------------------------------------------------------------------------
 * State (what has already been posted)
 * ------------------------------------------------------------------------ */

function discord_state(): array
{
    $data = is_file(DISCORD_STATE_FILE) ? json_decode((string)file_get_contents(DISCORD_STATE_FILE), true) : null;
    return is_array($data) ? $data : [];
}

function discord_save_state(array $state): void
{
    // Keep the file small: forget keys older than ~60 days.
    $cutoff = time() - 60 * 86400;
    $state['sent'] = array_filter($state['sent'] ?? [], static fn($t) => $t > $cutoff);
    @file_put_contents(DISCORD_STATE_FILE, json_encode($state, JSON_PRETTY_PRINT), LOCK_EX);
}

/* ---------------------------------------------------------------------------
 * Embeds
 * ------------------------------------------------------------------------ */

function discord_result_embed(int $year, int $round, string $type): ?array
{
    $data = f1_race($year, $round, $type);
    if (!$data) {
        return null;
    }
    $race = $data['race'];
    $rows = $data['rows'];
    $flag = country_iso($race['country']);
    $flagEmoji = $flag ? implode('', array_map(static fn($c) => mb_chr(0x1F1E6 + ord($c) - 97), str_split($flag))) . ' ' : '';
    $medals = ['🥇', '🥈', '🥉'];
    $line = static fn(array $r, string $extra = '') => '**' . $r['givenName'] . ' ' . $r['familyName'] . '** · ' . $r['constructor'] . ($extra !== '' ? ' · `' . $extra . '`' : '');

    if ($type === 'qualifying') {
        $lines = [];
        foreach (array_slice($rows, 0, 10) as $i => $q) {
            $time = $q['q3'] ?? $q['q2'] ?? $q['q1'] ?? '';
            $lines[] = ($i < 3 ? $medals[$i] : '`P' . str_pad((string)$q['position'], 2, ' ', STR_PAD_LEFT) . '`') . ' ' . $line($q, (string)$time);
        }
        return [
            'title'       => "{$flagEmoji}{$race['name']} — Qualifying",
            'url'         => race_url($year, $round) . '#qualifying',
            'description' => "🏁 **Pole position: {$rows[0]['name']}** ({$rows[0]['constructor']})\n\n" . implode("\n", $lines),
            'color'       => discord_color($rows[0]['color']),
            'footer'      => ['text' => "Round $round · $year · Full grid on f1site.nl"],
            'timestamp'   => gmdate('c'),
        ];
    }

    $isSprint = $type === 'sprint';
    $lines = [];
    foreach (array_slice($rows, 0, 10) as $i => $r) {
        $lines[] = ($i < 3 ? $medals[$i] : '`P' . str_pad((string)$r['position'], 2, ' ', STR_PAD_LEFT) . '`') . ' ' . $line($r, result_time($r)) . ($r['points'] ? " · +{$r['points']}" : '');
    }
    $fields = [];
    if (!$isSprint) {
        foreach ($rows as $r) {
            if ($r['fastestLapRank'] === 1) {
                $fields[] = ['name' => '⚡ Fastest lap', 'value' => "{$r['name']} · `{$r['fastestLapTime']}`", 'inline' => true];
            }
        }
        $gains = array_filter($rows, static fn($r) => $r['classified'] && $r['grid'] > 0);
        usort($gains, static fn($a, $b) => ($b['grid'] - $b['position']) <=> ($a['grid'] - $a['position']));
        if ($gains && ($g = $gains[0])['grid'] - $g['position'] > 0) {
            $fields[] = ['name' => '📈 Biggest gain', 'value' => "{$g['name']} · +" . ($g['grid'] - $g['position']) . " places (P{$g['grid']} → P{$g['position']})", 'inline' => true];
        }
        $dnfs = array_map(static fn($r) => $r['familyName'], array_filter($rows, 'is_dnf'));
        if ($dnfs) {
            $fields[] = ['name' => '🛑 Retirements', 'value' => implode(', ', $dnfs), 'inline' => false];
        }
        // Championship top 5 after this race.
        $standings = f1_driver_standings($year);
        if ($standings['round'] >= $round && $standings['rows']) {
            $top = array_map(static fn($s) => "`{$s['position']}.` {$s['familyName']} — {$s['points']}", array_slice($standings['rows'], 0, 5));
            $fields[] = ['name' => '🏆 Championship', 'value' => implode("\n", $top), 'inline' => false];
        }
    }
    return [
        'title'       => $flagEmoji . $race['name'] . ($isSprint ? ' — Sprint' : ' — Race result'),
        'url'         => race_url($year, $round) . ($isSprint ? '#sprint' : ''),
        'description' => "🏆 **{$rows[0]['name']} wins" . ($isSprint ? ' the sprint' : '') . "!** ({$rows[0]['constructor']})\n\n" . implode("\n", $lines),
        'color'       => discord_color($rows[0]['color']),
        'fields'      => $fields,
        'footer'      => ['text' => "Round $round · $year · Full results, pit stops & lap chart on f1site.nl"],
        'timestamp'   => gmdate('c'),
    ];
}

function discord_weekend_embed(array $row): array
{
    $lines = array_map(
        static fn($s) => '**' . $s['label'] . '** — ' . discord_time($s['at'], 'F') . ' (' . discord_time($s['at'], 'R') . ')',
        weekend_sessions($row)
    );
    $embed = [
        'title'       => "🏎️ Race week: {$row['grandprix']}",
        'url'         => url('circuit-details.php', ['key' => $row['circuit_key'], 'year' => $row['race_year']]),
        'description' => "Round {$row['calendar_order']} · {$row['title']}, {$row['location']}\n" . (!empty($row['sprint_datetime']) ? "⚡ **Sprint weekend**\n" : '') . "\n" . implode("\n", $lines),
        'color'       => DISCORD_RED,
        'footer'      => ['text' => 'Times are shown in your own timezone · f1site.nl'],
    ];
    if (!empty($row['map_url'])) {
        $embed['thumbnail'] = ['url' => $row['map_url']];
    }
    return $embed;
}

function discord_reminder_embed(array $row, array $session): array
{
    $icons = ['Race' => '🏁', 'Qualifying' => '⏱️', 'Sprint' => '⚡', 'Sprint Qualifying' => '⏱️'];
    return [
        'title'       => ($icons[$session['label']] ?? '🔔') . " {$session['label']} starts " . discord_time($session['at'], 'R'),
        'url'         => url('circuit-details.php', ['key' => $row['circuit_key'], 'year' => $row['race_year']]),
        'description' => "**{$row['grandprix']}** · {$row['title']}\nLights out at " . discord_time($session['at'], 't') . '.',
        'color'       => DISCORD_RED,
    ];
}

function discord_champion_embed(array $fight): array
{
    $l = $fight['leader'];
    $isTeams = $fight['mode'] === 'constructors';
    $name = $isTeams ? $l['constructor'] : $l['name'];
    return [
        'title'       => '🏆 ' . ($isTeams ? "{$name} are the {$fight['year']} Constructors' Champions!" : "{$name} is the {$fight['year']} World Champion!"),
        'url'         => url('standings.php', ['year' => $fight['year']]) . ($isTeams ? '#constructors' : ''),
        'description' => "Title secured with **{$l['points']} points** and **{$l['wins']} wins**"
            . (count($fight['remaining']) ? ', with ' . count($fight['remaining']) . ' race' . (count($fight['remaining']) === 1 ? '' : 's') . ' still to go.' : '.'),
        'color'       => 0xD4A017,
    ];
}

function discord_news_embed(array $article): array
{
    $embed = [
        'title'  => mb_substr($article['titel'], 0, 250),
        'url'    => $article['artikel_url'],
        'color'  => 0x2B2D31,
        'footer' => ['text' => '📰 ' . ($article['source'] ?: 'News')],
    ];
    if (!empty($article['afbeelding_url'])) {
        $embed['thumbnail'] = ['url' => $article['afbeelding_url']];
    }
    return $embed;
}

/* ---------------------------------------------------------------------------
 * The notifier: run regularly by scripts/discord-notify.php
 * ------------------------------------------------------------------------ */

/**
 * Check for new results, weekend previews, session reminders and decided
 * championships, and post what has not been posted before.
 *
 * @param bool $dryRun Only report what would be sent.
 */
function discord_notify(bool $dryRun = false): array
{
    if (!discord_enabled()) {
        return ['Discord is not configured (discord.webhook in config.local.php).'];
    }
    $state = discord_state();
    $firstRun = empty($state['initialised']);
    $sent = &$state['sent'];
    $sent ??= [];
    $log = [];
    $now = new DateTimeImmutable('now', new DateTimeZone('UTC'));

    $post = static function (string $key, array $embed, string $label) use (&$sent, &$log, $dryRun, $firstRun): void {
        if (isset($sent[$key])) {
            return;
        }
        if ($firstRun) {
            $sent[$key] = time(); // First run: remember existing data without posting it.
            return;
        }
        if ($dryRun) {
            $log[] = "Would post: $label";
            return;
        }
        if (discord_send([$embed])) {
            $sent[$key] = time();
            $log[] = "Posted: $label";
        } else {
            $log[] = "Failed to post: $label";
        }
    };

    // 1. Results of the last week's races (qualifying, sprint, race).
    $year = current_season();
    foreach (f1_schedule($year) as $round => $race) {
        if (!$race['datetime'] || $race['datetime'] > $now->modify('+1 day') || $race['datetime'] < $now->modify('-' . DISCORD_RESULT_WINDOW_DAYS . ' days')) {
            continue;
        }
        $types = $race['hasSprint'] ? ['qualifying', 'sprint', 'results'] : ['qualifying', 'results'];
        foreach ($types as $type) {
            $key = "result:$year:$round:$type";
            if (isset($sent[$key])) {
                continue;
            }
            $embed = discord_result_embed($year, $round, $type);
            if ($embed) {
                $post($key, $embed, "$type {$race['name']}");
            }
        }
    }

    // 2. Championships decided.
    foreach (['drivers', 'constructors'] as $mode) {
        $fight = f1_title_fight($year, $mode);
        if ($fight && $fight['decided']) {
            $post("champion:$year:$mode", discord_champion_embed($fight), "$mode champion $year");
        }
    }

    // 3. Weekend preview (from 3 days before the first session) and session reminders.
    $next = db_next_race();
    if ($next) {
        $sessions = weekend_sessions($next);
        $first = $sessions[0]['at'] ?? null;
        if ($first && $now >= $first->modify('-3 days') && $now < $first) {
            $post("weekend:{$next['race_year']}:{$next['circuit_key']}", discord_weekend_embed($next), "weekend preview {$next['grandprix']}");
        }
        foreach ($sessions as $s) {
            $minutes = ($s['at']->getTimestamp() - $now->getTimestamp()) / 60;
            if ($minutes > 0 && $minutes <= DISCORD_REMINDER_MINUTES) {
                $post("reminder:{$next['race_year']}:{$next['circuit_key']}:{$s['key']}", discord_reminder_embed($next, $s), "reminder {$s['label']} {$next['grandprix']}");
            }
        }
    }

    if ($firstRun) {
        $state['initialised'] = time();
        $log[] = 'First run: existing results were marked as already posted. New data will be posted from now on.';
    }
    if (!$dryRun || $firstRun) {
        discord_save_state($state);
    }
    return $log ?: ['Nothing new to post.'];
}

/** Post newly fetched news articles (called by task_fetch_news). */
function discord_post_news(array $articles): void
{
    if (!$articles || !discord_enabled() || !(bool)config('discord.post_news', true)) {
        return;
    }
    foreach (array_chunk($articles, 10) as $chunk) {
        discord_send(array_map('discord_news_embed', $chunk));
    }
}

/** Notify the private admin channel. */
function discord_admin(string $title, string $description, array $fields = [], ?string $link = null): void
{
    if (!discord_enabled('admin')) {
        return;
    }
    $embed = [
        'title'       => mb_substr($title, 0, 250),
        'description' => mb_substr($description, 0, 3500),
        'fields'      => $fields,
        'color'       => 0x5865F2,
        'timestamp'   => gmdate('c'),
    ];
    if ($link) {
        $embed['url'] = $link;
    }
    discord_send([$embed], 'admin');
}

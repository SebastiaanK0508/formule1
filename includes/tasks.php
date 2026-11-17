<?php
/**
 * Maintenance tasks, shared by the cron scripts in /scripts and the admin
 * panel's Tools page. Each task returns a list of human-readable log lines.
 */
declare(strict_types=1);

/** Fetch the latest F1 headlines from NewsAPI.org into f1_nieuws. */
function task_fetch_news(): array
{
    $key = (string)config('newsapi_key', '');
    if ($key === '') {
        return ['NewsAPI key missing: set newsapi_key in config.local.php.'];
    }
    $query = '("Formula 1" OR "F1" OR "Grand Prix")';
    $domains = 'autosport.com,motorsport.com,skysports.com,espn.com,the-race.com,racefans.net,f1i.com,gpblog.com';
    $url = 'https://newsapi.org/v2/everything?' . http_build_query([
        'q' => $query, 'domains' => $domains, 'language' => 'en', 'sortBy' => 'publishedAt', 'pageSize' => 50,
    ]);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_USERAGENT      => HTTP_USER_AGENT,
        CURLOPT_HTTPHEADER     => ['X-Api-Key: ' . $key],
    ]);
    $body = curl_exec($ch);
    $status = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $data = json_decode((string)$body, true);
    if ($status !== 200 || !is_array($data)) {
        return ["NewsAPI request failed (HTTP $status): " . ($data['message'] ?? 'no response')];
    }

    $stmt = db()->prepare('INSERT IGNORE INTO f1_nieuws (titel, artikel_url, publicatie_datum, afbeelding_url, source) VALUES (?, ?, ?, ?, ?)');
    $added = $skipped = 0;
    $new = [];
    foreach ($data['articles'] ?? [] as $a) {
        $title = trim((string)($a['title'] ?? ''));
        $link = trim((string)($a['url'] ?? ''));
        $image = trim((string)($a['urlToImage'] ?? ''));
        $haystack = strtolower($title . ' ' . ($a['description'] ?? ''));
        $relevant = preg_match('/\b(f1|formula (1|one)|grand prix)\b/', $haystack);
        if ($title === '' || $link === '' || $image === '' || !$relevant || !preg_match('#^https?://#', $link)) {
            $skipped++;
            continue;
        }
        $row = [
            'titel'            => mb_substr($title, 0, 255),
            'artikel_url'      => mb_substr($link, 0, 512),
            'publicatie_datum' => gmdate('Y-m-d H:i:s', strtotime((string)($a['publishedAt'] ?? 'now')) ?: time()),
            'afbeelding_url'   => mb_substr($image, 0, 512),
            'source'           => mb_substr((string)($a['source']['name'] ?? 'News'), 0, 255),
        ];
        $stmt->execute(array_values($row));
        if ($stmt->rowCount()) {
            $added++;
            $new[] = $row;
        } else {
            $skipped++;
        }
    }
    // Oldest first, so the newest headline ends up at the bottom of the Discord channel.
    discord_post_news(array_reverse($new));
    return ["News fetched: $added new, $skipped skipped or already present."];
}

/** Delete news articles older than $days days. */
function task_prune_news(int $days = 5): array
{
    $stmt = db()->prepare('DELETE FROM f1_nieuws WHERE COALESCE(publicatie_datum, created_at) < UTC_TIMESTAMP() - INTERVAL ? DAY');
    $stmt->execute([$days]);
    return ['Old news removed: ' . $stmt->rowCount() . " articles older than $days days."];
}

/**
 * Fill in session times (FP1–Race) for a season from the OpenF1 API.
 * Meetings are matched to our calendar by race date, so differing names or
 * round numbers do not matter. Only empty or changed values are updated.
 */
function task_sync_sessions(int $year): array
{
    $sessions = http_json("https://api.openf1.org/v1/sessions?year=$year", 600, "openf1_sessions_$year");
    if (!$sessions) {
        return ["OpenF1 returned no sessions for $year."];
    }

    $meetings = [];
    foreach ($sessions as $s) {
        $name = strtolower((string)($s['session_name'] ?? ''));
        $column = match (true) {
            $name === 'practice 1' => 'fp1_datetime',
            $name === 'practice 2' => 'fp2_datetime',
            $name === 'practice 3' => 'fp3_datetime',
            str_contains($name, 'sprint') && (str_contains($name, 'qualifying') || str_contains($name, 'shootout')) => 'sprint_quali_datetime',
            $name === 'sprint' => 'sprint_datetime',
            $name === 'qualifying' => 'quali_datetime',
            $name === 'race' => 'race_datetime',
            default => null,
        };
        if ($column && !empty($s['date_start'])) {
            $meetings[$s['meeting_key']][$column] = (new DateTimeImmutable($s['date_start']))->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');
            $meetings[$s['meeting_key']]['_name'] = $s['location'] ?? $s['country_name'] ?? (string)$s['meeting_key'];
        }
    }

    $log = [];
    $find = db()->prepare('SELECT circuit_key, grandprix FROM circuits WHERE race_year = ? AND race_datetime BETWEEN ? - INTERVAL 3 DAY AND ? + INTERVAL 3 DAY LIMIT 1');
    foreach ($meetings as $times) {
        if (empty($times['race_datetime'])) {
            continue;
        }
        $find->execute([$year, $times['race_datetime'], $times['race_datetime']]);
        $row = $find->fetch();
        if (!$row) {
            $log[] = "No calendar match for {$times['_name']} ({$times['race_datetime']} UTC).";
            continue;
        }
        unset($times['_name']);
        $set = implode(', ', array_map(static fn($c) => "$c = :$c", array_keys($times)));
        $stmt = db()->prepare("UPDATE circuits SET $set WHERE circuit_key = :k AND race_year = :y");
        $stmt->execute($times + ['k' => $row['circuit_key'], 'y' => $year]);
        $log[] = ($stmt->rowCount() ? 'Updated: ' : 'Unchanged: ') . $row['grandprix'];
    }
    return $log ?: ['Nothing to update.'];
}

/** Pre-load the API cache for the current season so visitors get fast pages. */
function task_warm_cache(): array
{
    $year = current_season();
    $start = microtime(true);
    f1_schedule($year);
    f1_driver_standings($year);
    f1_constructor_standings($year);
    foreach (['results', 'sprint', 'qualifying'] as $type) {
        f1_season($year, $type);
    }
    foreach (db_drivers() as $d) {
        if ((int)$d['is_active'] === 1 && ($id = api_driver_id($d))) {
            f1_driver_career($id);
        }
    }
    require_once INC_DIR . '/quiz.php';
    daily_quiz(date('Y-m-d'));
    daily_quiz(date('Y-m-d', strtotime('+1 day')));
    return [sprintf('Cache warmed for %d (incl. daily quiz) in %.1fs.', $year, microtime(true) - $start)];
}

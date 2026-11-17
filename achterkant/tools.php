<?php
declare(strict_types=1);
require __DIR__ . '/_bootstrap.php';
require_login();

$log = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_post();
    set_time_limit(180);
    $log = match ((string)($_POST['task'] ?? '')) {
        'sync'   => task_sync_sessions(max(2018, min(current_season() + 1, (int)($_POST['year'] ?? current_season())))),
        'news'   => task_fetch_news(),
        'prune'  => task_prune_news(max(1, (int)($_POST['days'] ?? 5))),
        'cache'  => ['API-cache geleegd: ' . http_cache_clear() . ' bestanden verwijderd.'],
        'warm'   => task_warm_cache(),
        'discord_test' => [
            discord_send([['title' => '✅ F1SITE.NL is connected', 'description' => 'Race results, weekend previews, session reminders and news will be posted in this channel.', 'color' => DISCORD_RED, 'url' => url()]])
                ? 'Testbericht verstuurd naar het publieke kanaal.' : 'Versturen mislukt — controleer de webhook-URL.',
            discord_enabled('admin')
                ? (discord_send([['title' => '✅ Admin notifications connected', 'description' => 'Contact messages and cron errors will be posted here.', 'color' => 0x5865F2]], 'admin') ? 'Testbericht verstuurd naar het admin-kanaal.' : 'Versturen naar admin-kanaal mislukt.')
                : 'Geen admin-webhook ingesteld (optioneel).',
        ],
        'discord_dry' => discord_notify(true),
        'discord_run' => discord_notify(),
        'discord_last' => (static function (): array {
            $year = latest_results_season();
            $round = f1_driver_standings($year)['round'];
            $embed = $round ? discord_result_embed($year, $round, 'results') : null;
            return [$embed && discord_send([$embed]) ? "Uitslag van ronde $round ($year) gepost." : 'Kon de laatste uitslag niet posten.'];
        })(),
        default  => ['Onbekende taak.'],
    };
}

$cacheFiles = glob(CACHE_DIR . '/*.json') ?: [];
$cacheSize = array_sum(array_map('filesize', $cacheFiles));

admin_header('Onderhoud', 'tools');
?>
<?php if ($log): ?>
    <div class="card card-pad mb-6">
        <h2 class="text-lg mb-2">Resultaat</h2>
        <pre class="mono text-sm" style="white-space: pre-wrap; margin: 0"><?= e(implode("\n", $log)) ?></pre>
    </div>
<?php endif; ?>

<div class="grid-2">
    <form class="card card-pad stack" method="post">
        <?= csrf_field() ?><input type="hidden" name="task" value="sync">
        <div><h2 class="text-lg">Sessietijden synchroniseren</h2><p class="muted text-sm mt-1">Haalt de tijden van alle trainingen, kwalificaties, sprints en races op bij OpenF1 en vult de kalender aan. Races worden op datum gekoppeld.</p></div>
        <div class="row"><select class="select select-sm" name="year" style="width: auto"><?php for ($y = current_season() + 1; $y >= 2023; $y--): ?><option<?= $y === current_season() ? ' selected' : '' ?>><?= $y ?></option><?php endfor; ?></select>
        <button class="btn btn-primary btn-sm" type="submit"><?= icon('refresh', 'icon-sm') ?> Synchroniseren</button></div>
    </form>

    <form class="card card-pad stack" method="post">
        <?= csrf_field() ?><input type="hidden" name="task" value="news">
        <div><h2 class="text-lg">Nieuws ophalen</h2><p class="muted text-sm mt-1">Haalt direct de nieuwste F1-koppen op via NewsAPI. Normaal gebeurt dit automatisch via de cronjob <code>scripts/fetch-news.php</code>.</p></div>
        <div><button class="btn btn-primary btn-sm" type="submit"><?= icon('newspaper', 'icon-sm') ?> Nu ophalen</button></div>
    </form>

    <form class="card card-pad stack" method="post">
        <?= csrf_field() ?><input type="hidden" name="task" value="prune">
        <div><h2 class="text-lg">Oud nieuws opruimen</h2><p class="muted text-sm mt-1">Verwijdert nieuwsberichten ouder dan het opgegeven aantal dagen.</p></div>
        <div class="row"><input class="input select-sm" type="number" name="days" value="5" min="1" max="365" style="width: 90px"><span class="muted text-sm">dagen</span>
        <button class="btn btn-secondary btn-sm" type="submit" onclick="return confirm('Oude berichten verwijderen?')"><?= icon('trash', 'icon-sm') ?> Opruimen</button></div>
    </form>

    <div class="card card-pad stack">
        <div><h2 class="text-lg">API-cache</h2><p class="muted text-sm mt-1">Uitslagen en standen worden lokaal gecachet (<?= count($cacheFiles) ?> bestanden, <?= round($cacheSize / 1048576, 1) ?> MB). Leeg de cache als data niet ververst lijkt; vul hem daarna opnieuw voor snelle pagina's.</p></div>
        <div class="row-wrap">
            <form method="post"><?= csrf_field() ?><input type="hidden" name="task" value="cache"><button class="btn btn-secondary btn-sm" type="submit" onclick="return confirm('Cache legen?')"><?= icon('trash', 'icon-sm') ?> Cache legen</button></form>
            <form method="post"><?= csrf_field() ?><input type="hidden" name="task" value="warm"><button class="btn btn-secondary btn-sm" type="submit"><?= icon('refresh', 'icon-sm') ?> Cache vullen</button></form>
        </div>
    </div>
</div>

<div class="card card-pad mt-6 stack">
    <div class="spread">
        <div><h2 class="text-lg">Discord</h2><p class="muted text-sm mt-1">Uitslagen, raceweekend-previews, herinneringen 30 min voor elke sessie, kampioenen en nieuws in je Discord-kanaal. Contactberichten en cron-fouten gaan naar het (optionele) admin-kanaal.</p></div>
        <div class="row-wrap">
            <span class="badge <?= discord_enabled() ? 'badge-green' : '' ?>">Publiek: <?= discord_enabled() ? 'actief' : 'niet ingesteld' ?></span>
            <span class="badge <?= discord_enabled('admin') ? 'badge-green' : '' ?>">Admin: <?= discord_enabled('admin') ? 'actief' : 'niet ingesteld' ?></span>
        </div>
    </div>
    <?php if (discord_enabled()): ?>
        <div class="row-wrap">
            <?php foreach (['discord_test' => ['Testbericht sturen', 'check'], 'discord_dry' => ['Bekijk wat er gepost zou worden', 'eye'], 'discord_run' => ['Nu controleren en posten', 'refresh'], 'discord_last' => ['Laatste race-uitslag opnieuw posten', 'flag']] as $task => [$label, $ico]): ?>
                <form method="post"><?= csrf_field() ?><input type="hidden" name="task" value="<?= $task ?>"><button class="btn btn-secondary btn-sm" type="submit"><?= icon($ico, 'icon-sm') ?> <?= e($label) ?></button></form>
            <?php endforeach; ?>
        </div>
        <?php $ds = discord_state(); ?>
        <p class="text-xs subtle">Notifier <?= !empty($ds['initialised']) ? 'actief sinds ' . e(date('j M Y H:i', (int)$ds['initialised'])) . ' · ' . count($ds['sent'] ?? []) . ' meldingen bijgehouden' : 'nog niet gedraaid — de eerste run markeert bestaande uitslagen als al gepost' ?>.</p>
    <?php else: ?>
        <div class="alert alert-info"><?= icon('info') ?><div class="text-sm">Maak in Discord een webhook aan (<strong>Serverinstellingen → Integraties → Webhooks → Nieuwe webhook → URL kopiëren</strong>) en zet de URL in <code>config.local.php</code> onder <code>'discord' => ['webhook' => '…']</code>. Optioneel een tweede webhook voor een privékanaal als <code>admin_webhook</code>.</div></div>
    <?php endif; ?>
</div>

<div class="card card-pad mt-6">
    <h2 class="text-lg mb-2">Cronjobs</h2>
    <p class="muted text-sm mb-4">Stel deze in bij je hostingprovider (pas het pad aan):</p>
    <pre class="mono text-sm" style="white-space: pre-wrap; margin: 0; background: var(--surface-2); padding: 12px; border-radius: 8px"><?= e(implode("\n", [
        '0 */2 * * *  php ' . ROOT_DIR . '/scripts/fetch-news.php',
        '30 4 * * *   php ' . ROOT_DIR . '/scripts/prune-news.php 5',
        '0 5 * * *    php ' . ROOT_DIR . '/scripts/sync-sessions.php',
        '15 * * * *   php ' . ROOT_DIR . '/scripts/warm-cache.php',
        '*/5 * * * *  php ' . ROOT_DIR . '/scripts/discord-notify.php',
    ])) ?></pre>
</div>
<?php admin_footer();

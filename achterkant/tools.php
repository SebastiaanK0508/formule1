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

<div class="card card-pad mt-6">
    <h2 class="text-lg mb-2">Cronjobs</h2>
    <p class="muted text-sm mb-4">Stel deze in bij je hostingprovider (pas het pad aan):</p>
    <pre class="mono text-sm" style="white-space: pre-wrap; margin: 0; background: var(--surface-2); padding: 12px; border-radius: 8px"><?= e(implode("\n", [
        '0 */2 * * *  php ' . ROOT_DIR . '/scripts/fetch-news.php',
        '30 4 * * *   php ' . ROOT_DIR . '/scripts/prune-news.php 5',
        '0 5 * * *    php ' . ROOT_DIR . '/scripts/sync-sessions.php',
        '15 * * * *   php ' . ROOT_DIR . '/scripts/warm-cache.php',
    ])) ?></pre>
</div>
<?php admin_footer();

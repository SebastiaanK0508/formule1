<?php
declare(strict_types=1);
require __DIR__ . '/_bootstrap.php';
require_login();

$counts = [
    'drivers'  => (int)db_value('SELECT COUNT(*) FROM drivers WHERE is_active = 1'),
    'teams'    => (int)db_value('SELECT COUNT(*) FROM teams WHERE is_active = 1'),
    'news'     => (int)db_value('SELECT COUNT(*) FROM f1_nieuws'),
    'unread'   => (int)db_value('SELECT COUNT(*) FROM contact WHERE is_read = 0'),
];
$year = current_season();
$races = db_calendar($year);
$next = db_next_race();
$latestNews = db_one('SELECT titel, publicatie_datum FROM f1_nieuws ORDER BY publicatie_datum DESC LIMIT 1');
$newsAge = $latestNews ? (time() - (db_datetime($latestNews['publicatie_datum'])?->getTimestamp() ?? time())) / 3600 : null;

// Data quality checks.
$issues = [];
$missingSessions = array_filter($races, static fn($r) => count(weekend_sessions($r)) < 5 && db_datetime($r['race_datetime']) > new DateTimeImmutable());
if ($missingSessions) {
    $issues[] = ['Sessietijden ontbreken voor ' . count($missingSessions) . ' komende race(s): ' . implode(', ', array_map(static fn($r) => $r['grandprix'], array_slice($missingSessions, 0, 4))) . '.', admin_url('tools.php'), 'Synchroniseren'];
}
$noTeam = (int)db_value('SELECT COUNT(*) FROM drivers WHERE is_active = 1 AND team_id IS NULL');
if ($noTeam) {
    $issues[] = ["$noTeam actieve coureur(s) zonder team.", admin_url('list.php', ['type' => 'drivers']), 'Bekijken'];
}
$apiDrivers = f1_driver_standings(latest_results_season())['rows'];
$unmatched = array_filter($apiDrivers, static fn($r) => !driver_for_api($r) && $r['points'] > 0);
if ($unmatched) {
    $issues[] = ['Coureurs met punten zonder profiel: ' . implode(', ', array_map(static fn($r) => $r['name'], $unmatched)) . '.', admin_url('edit.php', ['type' => 'drivers']), 'Toevoegen'];
}
if ($newsAge !== null && $newsAge > 24) {
    $issues[] = ['Het nieuwste nieuwsbericht is ' . round($newsAge) . ' uur oud — draait de nieuws-cronjob nog?', admin_url('tools.php'), 'Nu ophalen'];
}
if (!cache_writable()) {
    $issues[] = ['De cachemap (storage/cache) is niet schrijfbaar voor de webserver. Elke pagina vraagt dan live de API aan en loopt tegen de rate limit aan. Oplossen: sudo chown -R www-data:www-data storage', admin_url('tools.php'), 'Onderhoud'];
}
if (!$races) {
    $issues[] = ["Er staan nog geen races voor $year in de kalender.", admin_url('edit.php', ['type' => 'circuits', 'race_year' => $year]), 'Toevoegen'];
}
$messages = db_all('SELECT * FROM contact ORDER BY ID DESC LIMIT 5');

admin_header('Dashboard', 'dashboard');
?>
<div class="stats mb-6">
    <a class="stat link-plain" href="<?= e(admin_url('list.php', ['type' => 'drivers'])) ?>"><div class="stat-label">Actieve coureurs</div><div class="stat-value"><?= $counts['drivers'] ?></div></a>
    <a class="stat link-plain" href="<?= e(admin_url('list.php', ['type' => 'teams'])) ?>"><div class="stat-label">Actieve teams</div><div class="stat-value"><?= $counts['teams'] ?></div></a>
    <a class="stat link-plain" href="<?= e(admin_url('list.php', ['type' => 'news'])) ?>"><div class="stat-label">Nieuwsberichten</div><div class="stat-value"><?= $counts['news'] ?></div></a>
    <a class="stat link-plain" href="<?= e(admin_url('messages.php')) ?>"><div class="stat-label">Ongelezen berichten</div><div class="stat-value<?= $counts['unread'] ? ' accent' : '' ?>"><?= $counts['unread'] ?></div></a>
</div>

<div class="grid-2">
    <div class="stack-lg">
        <div class="card">
            <div class="card-head"><h2>Aandachtspunten</h2><span class="meta"><?= count($issues) ?></span></div>
            <?php if (!$issues): ?>
                <div class="card-pad row"><span class="badge badge-green"><?= icon('check', 'icon-sm') ?> Alles in orde</span><span class="muted text-sm">Geen problemen gevonden.</span></div>
            <?php endif; ?>
            <?php foreach ($issues as [$text, $href, $cta]): ?>
                <div class="result-item">
                    <?= icon('alert', 'icon') ?><span class="text-sm"><?= e($text) ?></span>
                    <a class="btn btn-secondary btn-sm kind" href="<?= e($href) ?>"><?= e($cta) ?></a>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="card">
            <div class="card-head"><h2>Laatste berichten</h2><a class="btn btn-ghost btn-sm" href="<?= e(admin_url('messages.php')) ?>">Alle berichten</a></div>
            <?php if (!$messages): ?><div class="empty">Nog geen berichten.</div><?php endif; ?>
            <?php foreach ($messages as $m): ?>
                <a class="result-item" href="<?= e(admin_url('messages.php', ['id' => $m['ID']])) ?>">
                    <span class="dot" style="--c: <?= $m['is_read'] ? 'var(--border-strong)' : 'var(--red)' ?>"></span>
                    <div class="truncate"><div class="<?= $m['is_read'] ? '' : 'fw-700' ?> truncate"><?= e($m['contact_subject'] ?: '(geen onderwerp)') ?></div><div class="text-xs muted"><?= e($m['contact_name']) ?> · <?= e($m['contact_email']) ?></div></div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="stack-lg">
        <?php if ($next): ?>
        <div class="card accent-top">
            <div class="card-head"><h2>Volgende race</h2><a class="btn btn-ghost btn-sm" href="<?= e(admin_url('edit.php', ['type' => 'circuits', 'circuit_key' => $next['circuit_key'], 'race_year' => $next['race_year']])) ?>"><?= icon('edit', 'icon-sm') ?> Bewerken</a></div>
            <div class="card-pad">
                <div class="fw-700 text-lg"><?= e($next['grandprix']) ?></div>
                <div class="muted text-sm"><?= e($next['title']) ?> · ronde <?= (int)$next['calendar_order'] ?></div>
                <table class="table table-compact mt-4">
                    <?php foreach (weekend_sessions($next) as $s): ?>
                        <tr><td><?= e($s['label']) ?></td><td class="num"><?= e(fmt_date($s['at'], 'D j M H:i')) ?></td></tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <div class="card card-pad">
            <h2 class="text-lg mb-4">Snel naar</h2>
            <div class="row-wrap">
                <a class="btn btn-secondary btn-sm" href="<?= e(admin_url('edit.php', ['type' => 'news'])) ?>"><?= icon('plus', 'icon-sm') ?> Nieuwsbericht</a>
                <a class="btn btn-secondary btn-sm" href="<?= e(admin_url('edit.php', ['type' => 'drivers'])) ?>"><?= icon('plus', 'icon-sm') ?> Coureur</a>
                <a class="btn btn-secondary btn-sm" href="<?= e(admin_url('edit.php', ['type' => 'circuits', 'race_year' => $year])) ?>"><?= icon('plus', 'icon-sm') ?> Race</a>
                <a class="btn btn-secondary btn-sm" href="<?= e(admin_url('tools.php')) ?>"><?= icon('refresh', 'icon-sm') ?> Onderhoud</a>
            </div>
        </div>

        <div class="card card-pad">
            <h2 class="text-lg mb-4">Systeem</h2>
            <dl class="facts" style="grid-template-columns: 1fr 1fr">
                <div class="fact"><dt>PHP-versie</dt><dd><?= e(PHP_VERSION) ?></dd></div>
                <div class="fact"><dt>API-cache</dt><dd><?= count(glob(CACHE_DIR . '/*.json') ?: []) ?> bestanden</dd></div>
                <div class="fact"><dt>Races <?= $year ?></dt><dd><?= count($races) ?></dd></div>
                <div class="fact"><dt>Laatste nieuws</dt><dd><?= $newsAge !== null ? round($newsAge) . ' uur geleden' : '—' ?></dd></div>
            </dl>
        </div>
    </div>
</div>
<?php admin_footer();

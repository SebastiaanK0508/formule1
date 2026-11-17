<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$q = mb_substr(str_param('q'), 0, 80);
$results = ['drivers' => [], 'teams' => [], 'races' => [], 'news' => []];

if (mb_strlen($q) >= 2) {
    $like = '%' . addcslashes($q, '%_\\') . '%';
    $results['drivers'] = db_all(
        "SELECT d.*, t.team_name, t.team_color FROM drivers d LEFT JOIN teams t ON t.team_id = d.team_id
         WHERE d.first_name <> '' AND (CONCAT(d.first_name, ' ', d.last_name) LIKE ? OR d.nationality LIKE ? OR d.driver_number = ?)
         ORDER BY d.is_active DESC, d.last_name LIMIT 12",
        [$like, $like, ctype_digit($q) ? (int)$q : -1]
    );
    $results['teams'] = db_all(
        'SELECT * FROM teams WHERE team_name LIKE ? OR full_team_name LIKE ? OR team_principal LIKE ? OR base_location LIKE ? ORDER BY is_active DESC, team_name LIMIT 8',
        [$like, $like, $like, $like]
    );
    $results['races'] = db_all(
        'SELECT * FROM circuits WHERE race_datetime IS NOT NULL AND (grandprix LIKE ? OR title LIKE ? OR location LIKE ?) ORDER BY race_year DESC, calendar_order LIMIT 10',
        [$like, $like, $like]
    );
    $results['news'] = db_all(
        'SELECT titel, artikel_url, publicatie_datum, source FROM f1_nieuws WHERE titel LIKE ? ORDER BY publicatie_datum DESC LIMIT 6',
        [$like]
    );
}
$count = array_sum(array_map('count', $results));

$page = [
    'title'   => $q !== '' ? "Search: $q" : 'Search',
    'noindex' => true,
];
require INC_DIR . '/layout/header.php';
?>
<div class="container" style="max-width: 880px">
    <header class="page-head">
        <h1 class="page-title">Search</h1>
        <form class="row mt-4" action="<?= e(url('zoek.php')) ?>" method="get" role="search">
            <label class="sr-only" for="q">Search</label>
            <input class="input" id="q" type="search" name="q" value="<?= e($q) ?>" placeholder="Driver, team, Grand Prix or circuit…" autofocus>
            <button class="btn btn-primary" type="submit"><?= icon('search', 'icon-sm') ?> Search</button>
        </form>
        <?php if ($q !== ''): ?><p class="muted mt-3"><?= $count ?> result<?= $count === 1 ? '' : 's' ?> for “<?= e($q) ?>”</p><?php endif; ?>
    </header>

    <section class="section stack-lg">
        <?php if ($q !== '' && mb_strlen($q) < 2): ?>
            <div class="alert alert-info">Please enter at least two characters.</div>
        <?php elseif ($q !== '' && !$count): ?>
            <div class="card empty"><?= icon('search', 'icon-lg') ?><h3>No results</h3><p>Try a driver's surname, a team name or a country.</p></div>
        <?php endif; ?>

        <?php if ($results['drivers']): ?>
        <div class="card">
            <div class="card-head"><h2>Drivers</h2></div>
            <?php foreach ($results['drivers'] as $d): ?>
                <a class="result-item" href="<?= e(driver_url($d)) ?>">
                    <?php if ($d['image']): ?><img class="avatar" src="<?= e($d['image']) ?>" alt=""><?php endif; ?>
                    <div><div class="fw-600"><?= e($d['first_name'] . ' ' . $d['last_name']) ?></div><div class="text-sm muted"><?= e($d['team_name'] ?? 'No team') ?> · #<?= e($d['driver_number']) ?></div></div>
                    <span class="kind badge"><?= $d['is_active'] ? 'Driver' : 'Former' ?></span>
                </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if ($results['teams']): ?>
        <div class="card">
            <div class="card-head"><h2>Teams</h2></div>
            <?php foreach ($results['teams'] as $t): ?>
                <a class="result-item" href="<?= e(team_url($t)) ?>">
                    <span class="swatch" style="--c: <?= e(safe_color($t['team_color'])) ?>; height: 32px"></span>
                    <div><div class="fw-600"><?= e($t['team_name']) ?></div><div class="text-sm muted"><?= e($t['full_team_name']) ?></div></div>
                    <span class="kind badge">Team</span>
                </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if ($results['races']): ?>
        <div class="card">
            <div class="card-head"><h2>Grands Prix</h2></div>
            <?php foreach ($results['races'] as $r): ?>
                <a class="result-item" href="<?= e(url('circuit-details.php', ['key' => $r['circuit_key'], 'year' => $r['race_year']])) ?>">
                    <?php if ($r['country_flag_url']): ?><img class="flag" src="<?= e($r['country_flag_url']) ?>" alt=""><?php endif; ?>
                    <div><div class="fw-600"><?= e($r['grandprix']) ?> <?= e($r['race_year']) ?></div><div class="text-sm muted"><?= e($r['title']) ?> · <?= e(weekend_range($r)) ?></div></div>
                    <span class="kind badge">Race</span>
                </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if ($results['news']): ?>
        <div class="card">
            <div class="card-head"><h2>News</h2></div>
            <?php foreach ($results['news'] as $n): ?>
                <a class="result-item" href="<?= e($n['artikel_url']) ?>" target="_blank" rel="noopener">
                    <div><div class="fw-600"><?= e($n['titel']) ?></div><div class="text-sm muted"><?= e($n['source']) ?></div></div>
                    <span class="kind"><?= icon('external', 'icon-sm') ?></span>
                </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </section>
</div>
<?php require INC_DIR . '/layout/footer.php'; ?>

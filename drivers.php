<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$drivers = db_all(
    'SELECT d.*, t.team_name, t.team_color, t.team_id AS tid
     FROM drivers d LEFT JOIN teams t ON t.team_id = d.team_id
     WHERE d.is_active = 1 AND d.first_name <> \'\'
     ORDER BY t.team_name, d.driver_number'
);

// Attach the current championship position and points.
$season = latest_results_season();
$standing = [];
foreach (f1_driver_standings($season)['rows'] as $row) {
    $d = driver_for_api($row);
    if ($d) {
        $standing[(int)$d['driver_id']] = $row;
    }
}
usort($drivers, static function ($a, $b) use ($standing) {
    $pa = $standing[(int)$a['driver_id']]['position'] ?? 999;
    $pb = $standing[(int)$b['driver_id']]['position'] ?? 999;
    return [$pa, $a['team_name'], $a['last_name']] <=> [$pb, $b['team_name'], $b['last_name']];
});
$teams = array_values(array_unique(array_filter(array_column($drivers, 'team_name'))));
sort($teams);

$page = [
    'title'       => current_season() . ' F1 Drivers',
    'description' => 'All ' . count($drivers) . ' drivers on the ' . current_season() . ' Formula 1 grid: teams, car numbers, championship positions and full profiles.',
    'nav'         => 'drivers',
];
require INC_DIR . '/layout/header.php';
?>
<div class="container">
    <header class="page-head">
        <div class="eyebrow"><?= count($drivers) ?> drivers · <?= count($teams) ?> teams</div>
        <h1 class="page-title"><?= current_season() ?> Drivers</h1>
        <p class="page-lead">The full grid, ordered by championship position. Tap the star to follow a driver — they will appear on your home page.</p>
    </header>

    <section class="section" style="padding-top: 16px">
        <div class="chips mb-6" data-chip-filter="#driver-grid" role="group" aria-label="Filter by team">
            <button class="chip is-active" type="button" data-value="all">All teams</button>
            <?php foreach ($teams as $t): ?>
                <button class="chip" type="button" data-value="<?= e(slugify($t)) ?>"><?= e($t) ?></button>
            <?php endforeach; ?>
        </div>

        <div class="grid-cards" id="driver-grid">
            <?php foreach ($drivers as $d):
                $color = safe_color($d['team_color'], '#e10600');
                $s = $standing[(int)$d['driver_id']] ?? null; ?>
                <article class="card card-link driver-card" style="--c: <?= e($color) ?>" data-group="<?= e(slugify((string)$d['team_name'])) ?>">
                    <button class="fav-btn" type="button" data-follow="driver:<?= (int)$d['driver_id'] ?>" aria-label="Follow <?= e($d['first_name'] . ' ' . $d['last_name']) ?>"><?= icon('star') ?></button>
                    <a class="link-plain" href="<?= e(driver_url($d)) ?>">
                        <div class="photo">
                            <?php if (!empty($d['image'])): ?><img src="<?= e($d['image']) ?>" alt="<?= e($d['first_name'] . ' ' . $d['last_name']) ?>" loading="lazy"><?php endif; ?>
                            <span class="number"><?= e($d['driver_number']) ?></span>
                        </div>
                        <div class="body">
                            <span class="first row" style="gap: 6px"><?php if (!empty($d['flag_url'])): ?><img class="flag" src="<?= e($d['flag_url']) ?>" alt=""><?php endif; ?><?= e($d['first_name']) ?></span>
                            <span class="last"><?= e($d['last_name']) ?></span>
                            <div class="meta">
                                <span class="team-name"><span class="swatch"></span><?= e($d['team_name'] ?? 'Reserve') ?></span>
                                <?php if ($s): ?><span><strong class="fw-700" style="color: var(--text)">P<?= (int)$s['position'] ?></strong> · <?= e(fmt_number($s['points'])) ?> pts</span><?php endif; ?>
                            </div>
                        </div>
                    </a>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
</div>
<?php require INC_DIR . '/layout/footer.php'; ?>

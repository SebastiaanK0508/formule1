<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$slug = str_param('slug');
$driver = null;
foreach (db_drivers() as $d) {
    if (driver_slug($d) === $slug) {
        $driver = $d;
        break;
    }
}
if (!$driver) {
    abort_404('We could not find this driver.');
}

$name = $driver['first_name'] . ' ' . $driver['last_name'];
$color = safe_color($driver['team_color'] ?? null, '#e10600');
$age = age_from($driver['date_of_birth']);
$apiId = api_driver_id($driver);

$year = int_param('year', latest_results_season(), FIRST_SEASON, current_season());
$standing = null;
$season = $summary = $h2h = [];
$career = null;
if ($apiId) {
    foreach (f1_driver_standings($year)['rows'] as $row) {
        if ($row['driverId'] === $apiId) {
            $standing = $row;
        }
    }
    $season = f1_driver_season($year, $apiId);
    $summary = season_summary($season);
    $h2h = f1_teammate_h2h($year, $apiId);
    $career = f1_driver_career($apiId);
}

$chart = null;
if (count($season) > 1) {
    $chart = ['type' => 'finishes', 'color' => $color, 'labels' => [], 'races' => [], 'finish' => [], 'grid' => []];
    foreach ($season as $round => $r) {
        $chart['labels'][] = 'R' . $round;
        $chart['races'][] = $r['race']['name'];
        $chart['finish'][] = is_dnf($r['result']) ? null : $r['result']['position'];
        $chart['grid'][] = $r['result']['grid'] ?: null;
    }
}

$page = [
    'title'       => "$name — Driver profile & stats",
    'description' => "$name" . ($driver['team_name'] ? " ({$driver['team_name']})" : '') . ': career statistics, ' . $year . ' season results, head-to-head against teammates and biography.',
    'nav'         => 'drivers',
    'charts'      => (bool)$chart,
    'image'       => $driver['image'] ?: null,
    'schema'      => [
        '@context'    => 'https://schema.org',
        '@type'       => 'Person',
        'name'        => $name,
        'birthDate'   => $driver['date_of_birth'],
        'birthPlace'  => $driver['place_of_birth'],
        'nationality' => $driver['nationality'],
        'image'       => $driver['image'],
        'jobTitle'    => 'Formula 1 driver',
        'memberOf'    => $driver['team_name'] ? ['@type' => 'SportsTeam', 'name' => $driver['team_name']] : null,
    ],
];
if (!$page['image']) {
    unset($page['image']);
}
require INC_DIR . '/layout/header.php';
?>
<section class="profile-hero" style="--c: <?= e($color) ?>">
    <div class="container">
        <div>
            <nav class="breadcrumb" aria-label="Breadcrumb"><a href="<?= e(url('drivers.php')) ?>">Drivers</a><span class="sep">/</span><span><?= e($name) ?></span></nav>
            <?php if ($driver['driver_number']): ?><div class="big-number"><?= e($driver['driver_number']) ?></div><?php endif; ?>
            <h1><span class="first"><?= e($driver['first_name']) ?></span><?= e($driver['last_name']) ?></h1>
            <div class="chips-row">
                <?php if ($driver['team_name']): ?>
                    <a class="team-name link-plain" href="<?= e(team_url(['team_id' => $driver['team_id']])) ?>"><span class="swatch"></span><?= e($driver['team_name']) ?></a>
                <?php elseif (!$driver['is_active']): ?>
                    <span class="badge">Not on the current grid</span>
                <?php endif; ?>
                <?php if ($driver['nationality']): ?><span><?php if ($driver['flag_url']): ?><img class="flag" src="<?= e($driver['flag_url']) ?>" alt=""><?php endif; ?><?= e($driver['nationality']) ?></span><?php endif; ?>
                <?php if ($age !== null): ?><span><?= icon('calendar', 'icon-sm') ?><?= $age ?> years · born <?= e(fmt_date($driver['date_of_birth'])) ?></span><?php endif; ?>
                <?php if ($driver['place_of_birth']): ?><span><?= icon('map-pin', 'icon-sm') ?><?= e($driver['place_of_birth']) ?></span><?php endif; ?>
            </div>
            <div class="actions">
                <button class="btn btn-secondary follow-btn" type="button" data-follow="driver:<?= (int)$driver['driver_id'] ?>"><?= icon('star', 'icon-sm') ?><span data-follow-label>Follow</span></button>
                <a class="btn btn-ghost" href="<?= e(url('compare.php', ['d1' => $driver['driver_id']])) ?>"><?= icon('swap', 'icon-sm') ?> Compare</a>
            </div>
        </div>
        <?php if ($driver['image']): ?>
            <div class="portrait"><img src="<?= e($driver['image']) ?>" alt="<?= e($name) ?>"></div>
        <?php endif; ?>
    </div>
</section>

<div class="container">
    <section class="section">
        <div class="section-title">
            <h2><?= e($year) ?> season</h2>
            <form method="get" action="<?= e(url('driver-details.php')) ?>">
                <input type="hidden" name="slug" value="<?= e($slug) ?>">
                <label class="sr-only" for="year">Season</label>
                <select class="select select-sm" id="year" name="year" data-autosubmit>
                    <?php for ($y = current_season(); $y >= max(FIRST_SEASON, current_season() - 15); $y--): ?>
                        <option value="<?= $y ?>"<?= $y === $year ? ' selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </form>
        </div>
        <?php if (!$season): ?>
            <div class="card empty"><?= icon('flag', 'icon-lg') ?><h3>No races in <?= e($year) ?></h3><p><?= e($name) ?> did not start a Grand Prix this season.</p></div>
        <?php else: ?>
            <div class="stats" style="--cols: 6">
                <div class="stat accent-top" style="--c: <?= e($color) ?>"><div class="stat-label">Championship</div><div class="stat-value"><?= $standing ? 'P' . (int)$standing['position'] : '–' ?></div></div>
                <div class="stat"><div class="stat-label">Points</div><div class="stat-value"><?= e(fmt_number($standing['points'] ?? $summary['points'])) ?></div></div>
                <div class="stat"><div class="stat-label">Wins</div><div class="stat-value"><?= (int)$summary['wins'] ?></div></div>
                <div class="stat"><div class="stat-label">Podiums</div><div class="stat-value"><?= (int)$summary['podiums'] ?></div></div>
                <div class="stat"><div class="stat-label">Poles</div><div class="stat-value"><?= (int)$summary['poles'] ?></div></div>
                <div class="stat"><div class="stat-label">Avg. finish</div><div class="stat-value"><?= $summary['avgFinish'] !== null ? e($summary['avgFinish']) : '–' ?></div><div class="stat-sub"><?= (int)$summary['dnfs'] ?> DNF</div></div>
            </div>

            <div class="grid-2 mt-6">
                <?php if ($chart): ?>
                <div class="card">
                    <div class="card-head"><h3>Grid vs finish</h3><span class="meta">Race by race</span></div>
                    <div class="card-pad"><div class="chart-box-sm" style="height: 280px"><canvas data-chart="chart-finishes" aria-label="Grid and finishing positions per race"></canvas></div></div>
                    <script type="application/json" id="chart-finishes"><?= json_encode($chart, JSON_HEX_TAG) ?></script>
                </div>
                <?php endif; ?>

                <?php if ($h2h): ?>
                <div class="card">
                    <div class="card-head"><h3>Teammate head-to-head</h3><span class="meta"><?= e($year) ?></span></div>
                    <div class="card-pad stack-lg">
                        <?php foreach ($h2h as $m):
                            $mate = $m['teammate'];
                            $qTotal = $m['qualiAhead'] + $m['qualiBehind']; ?>
                            <div>
                                <div class="spread mb-4">
                                    <span class="fw-700"><?= e($driver['last_name']) ?></span>
                                    <span class="text-xs subtle">vs · <?= (int)$m['races'] ?> races together</span>
                                    <span class="fw-700"><?= driver_link($mate) ?></span>
                                </div>
                                <div class="h2h">
                                    <?php foreach ([['Race', $m['raceAhead'], $m['raceBehind']], ['Qualifying', $m['qualiAhead'], $m['qualiBehind']], ['Points', $m['points'], $m['teammatePoints']]] as [$label, $a, $b]):
                                        $tot = $a + $b; ?>
                                        <div class="a <?= $a > $b ? 'win' : '' ?>"><?= e(fmt_number($a)) ?></div>
                                        <div class="label"><?= $label ?></div>
                                        <div class="b <?= $b > $a ? 'win' : '' ?>"><?= e(fmt_number($b)) ?></div>
                                        <div class="h2h-bar"><span style="width: <?= $tot ? round($a / $tot * 100) : 50 ?>%; background: <?= e($color) ?>"></span><span style="flex: 1; background: var(--surface-3)"></span></div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <div class="card table-wrap mt-6">
                <table class="table">
                    <thead><tr><th>Rd</th><th>Grand Prix</th><th class="hide-sm">Team</th><th class="num">Quali</th><th class="num">Grid</th><th class="num">Finish</th><th class="num hide-sm">Sprint</th><th class="num">Pts</th></tr></thead>
                    <tbody>
                    <?php foreach ($season as $round => $r): $res = $r['result']; ?>
                        <tr>
                            <td class="muted tabular"><?= (int)$round ?></td>
                            <td><a class="link-plain fw-600" href="<?= e(race_url($year, $round)) ?>"><?= e(str_replace(' Grand Prix', ' GP', $r['race']['name'])) ?></a></td>
                            <td class="hide-sm"><?= team_link($res) ?></td>
                            <td class="num muted"><?= $r['quali'] ? (int)$r['quali']['position'] : '–' ?></td>
                            <td class="num muted"><?= $res['grid'] ?: 'PL' ?></td>
                            <td class="num">
                                <?php if ($res['classified']): ?><span class="<?= pos_class($res['position']) ?>"><?= (int)$res['position'] ?></span>
                                <?php else: ?><span class="pos pos-dnf" title="<?= e($res['status']) ?>">DNF</span><?php endif; ?>
                            </td>
                            <td class="num hide-sm muted"><?= $r['sprint'] ? e($r['sprint']['positionText']) : '' ?></td>
                            <td class="num points"><?= e(fmt_number($res['points'] + ($r['sprint']['points'] ?? 0))) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

    <?php if ($career && $career['races']): ?>
    <section class="section">
        <div class="section-title"><h2>Career</h2><?php if ($career['debut']): ?><span class="text-sm muted">Debut: <a href="<?= e(race_url($career['debut']['season'], $career['debut']['round'])) ?>"><?= e($career['debut']['season'] . ' ' . $career['debut']['name']) ?></a></span><?php endif; ?></div>
        <div class="stats" style="--cols: 6">
            <div class="stat"><div class="stat-label">World titles</div><div class="stat-value"><?= (int)$driver['championships_won'] ?></div></div>
            <div class="stat"><div class="stat-label">Seasons</div><div class="stat-value"><?= (int)$career['seasons'] ?></div></div>
            <div class="stat"><div class="stat-label">Grands Prix</div><div class="stat-value"><?= (int)$career['races'] ?></div></div>
            <div class="stat"><div class="stat-label">Wins</div><div class="stat-value"><?= (int)$career['wins'] ?></div><div class="stat-sub"><?= $career['races'] ? round($career['wins'] / $career['races'] * 100, 1) : 0 ?>% win rate</div></div>
            <div class="stat"><div class="stat-label">Podiums</div><div class="stat-value"><?= (int)$career['podiums'] ?></div></div>
            <div class="stat"><div class="stat-label">Pole positions</div><div class="stat-value"><?= (int)$career['poles'] ?></div></div>
        </div>
    </section>
    <?php endif; ?>

    <?php if (!empty($driver['description'])): ?>
    <section class="section">
        <div class="section-title"><h2>Biography</h2></div>
        <div class="card card-pad prose" style="max-width: none"><p><?= nl2br(e($driver['description'])) ?></p></div>
    </section>
    <?php endif; ?>
</div>
<?php require INC_DIR . '/layout/footer.php'; ?>

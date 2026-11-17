<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$year = int_param('year', current_season(), FIRST_SEASON, current_season());

// Old links pointed to results.php?year=&round= — the race page now lives elsewhere.
if (isset($_GET['round'])) {
    redirect(race_url($year, int_param('round', 1, 1, 30)), 301);
}

$schedule = f1_schedule($year);
$results = f1_season($year, 'results');
$quali = $year >= 2003 ? f1_season($year, 'qualifying') : [];
$sprints = $year >= 2021 ? f1_season($year, 'sprint') : [];
$now = new DateTimeImmutable();

$rows = [];
foreach ($schedule as $round => $race) {
    $res = $results[$round]['rows'] ?? [];
    $winner = $res[0] ?? null;
    $pole = $quali[$round]['rows'][0] ?? null;
    if (!$pole && $res) {
        foreach ($res as $r) {
            if ($r['grid'] === 1) { $pole = $r; break; }
        }
    }
    $fastest = null;
    foreach ($res as $r) {
        if ($r['fastestLapRank'] === 1) { $fastest = $r; break; }
    }
    $rows[] = compact('race', 'winner', 'pole', 'fastest') + ['done' => (bool)$res, 'sprint' => $sprints[$round]['rows'][0] ?? null];
}
$completed = count(array_filter($rows, static fn($r) => $r['done']));

// Win tally for the season summary.
$winCounts = [];
foreach ($rows as $r) {
    if ($r['winner']) {
        $id = $r['winner']['driverId'];
        $winCounts[$id] ??= ['row' => $r['winner'], 'wins' => 0];
        $winCounts[$id]['wins']++;
    }
}
usort($winCounts, static fn($a, $b) => $b['wins'] <=> $a['wins']);

$page = [
    'title'       => "$year Formula 1 Results",
    'description' => "Every Grand Prix of the $year Formula 1 season: winners, pole positions, fastest laps and full race results.",
    'nav'         => 'results',
];
require INC_DIR . '/layout/header.php';
?>
<div class="container">
    <header class="page-head">
        <div class="spread">
            <div>
                <div class="eyebrow"><?= $completed ?> of <?= count($rows) ?> races completed</div>
                <h1 class="page-title"><?= e($year) ?> Results</h1>
            </div>
            <form method="get" action="<?= e(url('results.php')) ?>">
                <label class="sr-only" for="year">Season</label>
                <select class="select select-sm" id="year" name="year" data-autosubmit>
                    <?php for ($y = current_season(); $y >= FIRST_SEASON; $y--): ?>
                        <option value="<?= $y ?>"<?= $y === $year ? ' selected' : '' ?>><?= $y ?> season</option>
                    <?php endfor; ?>
                </select>
                <noscript><button class="btn btn-secondary btn-sm">Go</button></noscript>
            </form>
        </div>
    </header>

    <?php if ($winCounts): ?>
    <section class="section" style="padding-bottom: 0">
        <div class="stats" style="--cols: <?= min(4, count($winCounts) + 1) ?>">
            <div class="stat">
                <div class="stat-label">Different winners</div>
                <div class="stat-value"><?= count($winCounts) ?></div>
                <div class="stat-sub">from <?= $completed ?> races</div>
            </div>
            <?php foreach (array_slice($winCounts, 0, 3) as $w): ?>
                <div class="stat accent-top" style="--c: <?= e($w['row']['color']) ?>">
                    <div class="stat-label"><?= e($w['row']['constructor']) ?></div>
                    <div class="stat-value"><?= (int)$w['wins'] ?> <span class="text-sm muted" style="font-family: var(--font-sans)">win<?= $w['wins'] === 1 ? '' : 's' ?></span></div>
                    <div class="stat-sub"><?= driver_link($w['row']) ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <section class="section">
        <?php if (!$rows): ?>
            <div class="card empty"><?= icon('calendar', 'icon-lg') ?><h3>No races found for <?= e($year) ?></h3></div>
        <?php else: ?>
        <div class="card table-wrap">
            <table class="table">
                <thead>
                    <tr><th class="w-pos">Rd</th><th>Grand Prix</th><th class="hide-sm">Date</th><th>Winner</th><th class="hide-sm">Pole</th><th class="hide-sm">Fastest lap</th><th></th></tr>
                </thead>
                <tbody>
                <?php foreach ($rows as $r): $race = $r['race']; ?>
                    <tr>
                        <td class="muted tabular"><?= (int)$race['round'] ?></td>
                        <td>
                            <div class="row" style="gap: 10px">
                                <?= flag_img(country_iso($race['country']), $race['country']) ?>
                                <div>
                                    <?php if ($r['done']): ?>
                                        <a class="fw-600 link-plain" href="<?= e(race_url($year, $race['round'])) ?>"><?= e($race['name']) ?></a>
                                    <?php else: ?>
                                        <span class="fw-600"><?= e($race['name']) ?></span>
                                    <?php endif; ?>
                                    <div class="text-xs subtle"><?= e($race['circuitName']) ?><?= $race['hasSprint'] ? ' · Sprint' : '' ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="hide-sm nowrap muted"><?= e(fmt_date($race['date'], 'j M')) ?></td>
                        <td>
                            <?php if ($r['winner']): ?>
                                <div class="driver-cell"><span class="swatch" style="--c: <?= e($r['winner']['color']) ?>"></span><?= driver_link($r['winner']) ?></div>
                            <?php elseif ($race['datetime'] && $race['datetime'] > $now): ?>
                                <span class="badge">Upcoming</span>
                            <?php else: ?>
                                <span class="subtle">Awaiting results</span>
                            <?php endif; ?>
                        </td>
                        <td class="hide-sm"><?= $r['pole'] ? driver_link($r['pole'], true) : '<span class="subtle">–</span>' ?></td>
                        <td class="hide-sm">
                            <?php if ($r['fastest']): ?>
                                <span class="row" style="gap: 6px"><?= driver_link($r['fastest'], true) ?> <span class="mono subtle text-xs"><?= e($r['fastest']['fastestLapTime']) ?></span></span>
                            <?php else: ?><span class="subtle">–</span><?php endif; ?>
                        </td>
                        <td class="num">
                            <?php if ($r['done']): ?>
                                <a class="btn btn-ghost btn-sm" href="<?= e(race_url($year, $race['round'])) ?>" aria-label="Results <?= e($race['name']) ?>"><?= icon('chevron-right', 'icon-sm') ?></a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </section>
</div>
<?php require INC_DIR . '/layout/footer.php'; ?>

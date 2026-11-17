<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$latest = latest_results_season();
$year = int_param('year', $latest, 1996, current_season());
$stats = f1_season_stats($year);
$drivers = $stats['drivers'];
$minRaces = max(1, (int)ceil($stats['rounds'] / 2));

/** Top 3 drivers for a metric (higher is better unless $asc). */
$leaders = static function (string $metric, bool $asc = false, int $min = 0) use ($drivers): array {
    $pool = array_filter($drivers, static fn($d) => $d['races'] >= $min && $d[$metric] !== null && ($asc || $d[$metric] > 0));
    usort($pool, static fn($a, $b) => $asc ? $a[$metric] <=> $b[$metric] : $b[$metric] <=> $a[$metric]);
    return array_slice($pool, 0, 3);
};

$records = [
    ['Most wins', 'wins', $leaders('wins'), 'trophy', ''],
    ['Most pole positions', 'poles', $leaders('poles'), 'flag', ''],
    ['Most podiums', 'podiums', $leaders('podiums'), 'trophy', ''],
    ['Most fastest laps', 'fastestLaps', $leaders('fastestLaps'), 'bolt', ''],
    ['Best average finish', 'avgFinish', $leaders('avgFinish', true, $minRaces), 'chart', 'Min. ' . $minRaces . ' races, excluding retirements'],
    ['Most places gained', 'gained', $leaders('gained'), 'arrow-right', 'Grid to finish, all races combined'],
    ['Most points finishes', 'pointsFinishes', $leaders('pointsFinishes'), 'check', ''],
    ['Most retirements', 'dnfs', $leaders('dnfs'), 'alert', 'DNF, DNS and disqualifications'],
];
$totalPoints = array_sum(array_column($stats['teams'], 'points')) ?: 1;

$page = [
    'title'       => "$year F1 Season Statistics",
    'description' => "Formula 1 $year in numbers: most wins, poles, podiums and fastest laps, every teammate battle and team reliability.",
    'nav'         => 'stats',
];
require INC_DIR . '/layout/header.php';
?>
<div class="container">
    <header class="page-head">
        <div class="spread">
            <div>
                <div class="eyebrow"><?= (int)$stats['rounds'] ?> races analysed</div>
                <h1 class="page-title"><?= e($year) ?> in numbers</h1>
                <p class="page-lead">Season records, every teammate battle and how the teams compare.</p>
            </div>
            <form method="get" action="<?= e(url('stats.php')) ?>">
                <label class="sr-only" for="year">Season</label>
                <select class="select select-sm" id="year" name="year" data-autosubmit>
                    <?php for ($y = current_season(); $y >= 1996; $y--): ?>
                        <option value="<?= $y ?>"<?= $y === $year ? ' selected' : '' ?>><?= $y ?> season</option>
                    <?php endfor; ?>
                </select>
            </form>
        </div>
    </header>

    <?php if (!$stats['rounds']): ?>
        <div class="card empty mt-6"><?= icon('chart', 'icon-lg') ?><h3>No races yet in <?= e($year) ?></h3></div>
    <?php else: ?>
    <section class="section">
        <div class="grid-4" style="grid-template-columns: repeat(auto-fill, minmax(min(100%, 260px), 1fr))">
            <?php foreach ($records as [$label, $metric, $top, $ico, $note]):
                $first = $top[0] ?? null; ?>
                <div class="card card-pad accent-top record" style="--c: <?= e($first['row']['color'] ?? 'var(--border)') ?>">
                    <div class="spread text-sm muted"><span><?= e($label) ?></span><?= icon($ico, 'icon-sm') ?></div>
                    <?php if ($first): ?>
                        <div class="stat-value mt-2"><?= e(fmt_number($first[$metric], $metric === 'avgFinish' ? 2 : 0)) ?></div>
                        <div class="fw-600 mt-1"><?= driver_link($first['row']) ?></div>
                        <div class="stack mt-3" style="gap: 4px">
                            <?php foreach (array_slice($top, 1) as $i => $r): ?>
                                <div class="spread text-sm"><span class="muted"><?= $i + 2 ?>. <?= e($r['row']['familyName']) ?></span><span class="tabular"><?= e(fmt_number($r[$metric], $metric === 'avgFinish' ? 2 : 0)) ?></span></div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="stat-value mt-2 subtle">–</div>
                    <?php endif; ?>
                    <?php if ($note): ?><p class="text-xs subtle mt-3"><?= e($note) ?></p><?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <?php if ($stats['battles']): ?>
    <section class="section">
        <div class="section-title"><h2>Teammate battles</h2><span class="text-sm muted">Who beat whom in the same car</span></div>
        <div class="grid-cards" style="grid-template-columns: repeat(auto-fill, minmax(min(100%, 360px), 1fr))">
            <?php foreach ($stats['battles'] as $b): ?>
                <div class="card card-pad accent-top" style="--c: <?= e($b['a']['color']) ?>">
                    <div class="text-xs subtle mb-2" style="text-transform: uppercase; letter-spacing: .08em"><?= e($b['a']['constructor']) ?> · <?= (int)$b['races'] ?> races</div>
                    <div class="spread mb-4 fw-700"><span><?= driver_link($b['a'], true) ?></span><span><?= driver_link($b['b'], true) ?></span></div>
                    <div class="h2h">
                        <?php foreach ([['Qualifying', $b['qualiAhead'], $b['qualiBehind']], ['Race', $b['raceAhead'], $b['raceBehind']], ['Points', $b['points'], $b['teammatePoints']]] as [$label, $x, $y]):
                            $tot = $x + $y; ?>
                            <div class="a <?= $x > $y ? 'win' : '' ?>" style="font-size: 22px"><?= e(fmt_number($x)) ?></div>
                            <div class="label"><?= e($label) ?></div>
                            <div class="b <?= $y > $x ? 'win' : '' ?>" style="font-size: 22px"><?= e(fmt_number($y)) ?></div>
                            <div class="h2h-bar"><span style="width: <?= $tot ? round($x / $tot * 100) : 50 ?>%; background: <?= e($b['a']['color']) ?>"></span><span style="flex: 1; background: var(--text-subtle); opacity: .5"></span></div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <section class="section">
        <div class="section-title"><h2>Teams</h2><span class="text-sm muted">Share of all points scored</span></div>
        <div class="card table-wrap">
            <table class="table">
                <thead><tr><th>Team</th><th class="num">Points</th><th class="hide-sm" style="width: 30%">Share</th><th class="num">Wins</th><th class="num hide-sm">Podiums</th><th class="num">Reliability</th></tr></thead>
                <tbody>
                <?php foreach ($stats['teams'] as $t):
                    $share = $t['points'] / $totalPoints * 100;
                    $reliability = $t['starts'] ? (1 - $t['dnfs'] / $t['starts']) * 100 : 0; ?>
                    <tr>
                        <td class="fw-600"><?= team_link($t['row']) ?></td>
                        <td class="num points"><?= e(fmt_number($t['points'])) ?></td>
                        <td class="hide-sm"><div class="row" style="gap: 10px"><div class="bar" style="flex: 1; --c: <?= e($t['row']['color']) ?>; --w: <?= round($share / max(1, max(array_map(static fn($x) => $x['points'], $stats['teams'])) / $totalPoints * 100) * 100) ?>%"><span></span></div><span class="text-xs muted tabular" style="width: 40px"><?= number_format($share, 1) ?>%</span></div></td>
                        <td class="num"><?= $t['wins'] ?: '<span class="subtle">0</span>' ?></td>
                        <td class="num hide-sm"><?= (int)$t['podiums'] ?></td>
                        <td class="num"><span class="<?= $reliability >= 90 ? 'delta-up' : ($reliability < 80 ? 'delta-down' : '') ?>"><?= number_format($reliability, 0) ?>%</span></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <p class="text-xs subtle mt-2">Reliability = share of race starts that ended classified. Points include sprints.</p>
    </section>
    <?php endif; ?>
</div>
<?php require INC_DIR . '/layout/footer.php'; ?>

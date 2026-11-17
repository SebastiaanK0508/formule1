<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$latest = latest_results_season();
$year = int_param('year', $latest, FIRST_SEASON, current_season());
$drivers = f1_driver_standings($year);
$constructors = $year >= 1958 ? f1_constructor_standings($year) : ['round' => 0, 'rows' => []];
$progression = $drivers['rows'] ? f1_points_progression($year, 8) : null;
$teamProgression = $constructors['rows'] ? f1_points_progression($year, 6, 'constructors') : null;
$leader = $drivers['rows'][0]['points'] ?? 0;
$teamLeader = $constructors['rows'][0]['points'] ?? 0;
$totalRounds = count(f1_schedule($year));
$isFinal = $year < current_season() || ($totalRounds && $drivers['round'] >= $totalRounds);

$page = [
    'title'       => "$year F1 Championship Standings",
    'description' => "Formula 1 $year drivers' and constructors' championship standings, points gaps and the points progression round by round.",
    'nav'         => 'standings',
    'charts'      => (bool)$progression,
];
require INC_DIR . '/layout/header.php';
?>
<div class="container">
    <header class="page-head">
        <div class="spread">
            <div>
                <div class="eyebrow"><?= $isFinal ? 'Final standings' : 'After round ' . (int)$drivers['round'] . ($totalRounds ? " of $totalRounds" : '') ?></div>
                <h1 class="page-title"><?= e($year) ?> Standings</h1>
            </div>
            <form method="get" action="<?= e(url('standings.php')) ?>">
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

    <?php if (!$drivers['rows']): ?>
        <div class="card empty mt-6">
            <?= icon('flag', 'icon-lg') ?>
            <h3>The <?= e($year) ?> season hasn't started yet</h3>
            <p>Standings appear here after the first race. <a href="<?= e(url('standings.php', ['year' => $year - 1])) ?>">View <?= $year - 1 ?> standings</a></p>
        </div>
    <?php else: ?>
    <section class="section">
        <div class="tabs" role="tablist">
            <button class="tab" role="tab" id="t-drivers" data-hash="drivers" aria-controls="p-drivers" aria-selected="true">Drivers</button>
            <?php if ($constructors['rows']): ?>
                <button class="tab" role="tab" id="t-constructors" data-hash="constructors" aria-controls="p-constructors" aria-selected="false" tabindex="-1">Constructors</button>
            <?php endif; ?>
        </div>

        <div id="p-drivers" role="tabpanel" aria-labelledby="t-drivers" class="stack-lg">
            <?php title_fight_card(f1_title_fight($year)); ?>
            <div class="card table-wrap">
                <table class="table">
                    <thead><tr><th class="w-pos">Pos</th><th>Driver</th><th class="hide-sm">Team</th><th class="num hide-sm">Wins</th><th class="hide-sm"></th><th class="num">Points</th><th class="num hide-sm">Gap</th></tr></thead>
                    <tbody>
                    <?php foreach ($drivers['rows'] as $r): ?>
                        <tr>
                            <td><span class="<?= pos_class($r['position']) ?>"><?= (int)$r['position'] ?: '–' ?></span></td>
                            <td><div class="driver-cell"><?= flag_img(nationality_iso($r['nationality']), $r['nationality']) ?><?= driver_link($r) ?></div></td>
                            <td class="hide-sm"><?= team_link($r) ?></td>
                            <td class="num hide-sm"><?= $r['wins'] ?: '–' ?></td>
                            <td class="hide-sm" style="width: 18%"><div class="bar" style="--c: <?= e($r['color']) ?>; --w: <?= $leader ? round($r['points'] / $leader * 100) : 0 ?>%"><span></span></div></td>
                            <td class="num points"><?= e(fmt_number($r['points'])) ?></td>
                            <td class="num hide-sm muted"><?= $r['position'] === 1 ? 'Leader' : '−' . e(fmt_number($leader - $r['points'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php if ($progression && count($progression['labels']) > 1): ?>
            <div class="card">
                <div class="card-head"><h2>Points progression</h2><span class="meta">Top <?= count($progression['series']) ?> · cumulative incl. sprints</span></div>
                <div class="card-pad"><div class="chart-box"><canvas data-chart="chart-drivers" aria-label="Points progression chart"></canvas></div></div>
                <script type="application/json" id="chart-drivers"><?= json_encode(['type' => 'progression'] + $progression, JSON_HEX_TAG) ?></script>
            </div>
            <?php endif; ?>
        </div>

        <?php if ($constructors['rows']): ?>
        <div id="p-constructors" role="tabpanel" aria-labelledby="t-constructors" class="stack-lg" hidden>
            <?php title_fight_card(f1_title_fight($year, 'constructors')); ?>
            <div class="card table-wrap">
                <table class="table">
                    <thead><tr><th class="w-pos">Pos</th><th>Team</th><th class="num hide-sm">Wins</th><th class="hide-sm"></th><th class="num">Points</th><th class="num hide-sm">Gap</th></tr></thead>
                    <tbody>
                    <?php foreach ($constructors['rows'] as $r): ?>
                        <tr>
                            <td><span class="<?= pos_class($r['position']) ?>"><?= (int)$r['position'] ?: '–' ?></span></td>
                            <td class="fw-600"><?= team_link($r) ?></td>
                            <td class="num hide-sm"><?= $r['wins'] ?: '–' ?></td>
                            <td class="hide-sm" style="width: 25%"><div class="bar" style="--c: <?= e($r['color']) ?>; --w: <?= $teamLeader ? round($r['points'] / $teamLeader * 100) : 0 ?>%"><span></span></div></td>
                            <td class="num points"><?= e(fmt_number($r['points'])) ?></td>
                            <td class="num hide-sm muted"><?= $r['position'] === 1 ? 'Leader' : '−' . e(fmt_number($teamLeader - $r['points'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php if ($teamProgression && count($teamProgression['labels']) > 1): ?>
            <div class="card">
                <div class="card-head"><h2>Points progression</h2><span class="meta">Top <?= count($teamProgression['series']) ?> teams</span></div>
                <div class="card-pad"><div class="chart-box"><canvas data-chart="chart-teams" aria-label="Constructor points progression chart"></canvas></div></div>
                <script type="application/json" id="chart-teams"><?= json_encode(['type' => 'progression'] + $teamProgression, JSON_HEX_TAG) ?></script>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </section>
    <?php endif; ?>
</div>
<?php require INC_DIR . '/layout/footer.php'; ?>

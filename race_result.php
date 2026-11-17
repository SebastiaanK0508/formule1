<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$year = int_param('year', current_season(), FIRST_SEASON, current_season());
$schedule = f1_schedule($year);
if (!$schedule) {
    abort_404("We couldn't find the $year season.");
}

// Default to the most recent race that has results.
$defaultRound = 1;
foreach ($schedule as $round => $r) {
    if ($r['datetime'] && $r['datetime'] < new DateTimeImmutable('-2 hours')) {
        $defaultRound = $round;
    }
}
$round = int_param('round', $defaultRound, 1, max(array_keys($schedule)));
$meta = $schedule[$round] ?? abort_404('This round does not exist.');

$race = f1_race($year, $round);
$quali = f1_race($year, $round, 'qualifying');
$sprint = $meta['hasSprint'] ? f1_race($year, $round, 'sprint') : null;
$pitstops = ($race && $year >= 2011) ? f1_pitstops($year, $round) : [];

// Lookups for pit stops (driverId → row) and positions gained.
$byDriver = [];
foreach ($race['rows'] ?? [] as $r) {
    $byDriver[$r['driverId']] = $r;
}
$gains = [];
foreach ($race['rows'] ?? [] as $r) {
    if ($r['grid'] > 0 && $r['classified']) {
        $gains[] = ['label' => $r['code'] ?: $r['familyName'], 'value' => $r['grid'] - $r['position'], 'row' => $r];
    }
}
usort($gains, static fn($a, $b) => $b['value'] <=> $a['value']);

$winner = $race['rows'][0] ?? null;
$pole = $quali['rows'][0] ?? null;
$fastest = null;
foreach ($race['rows'] ?? [] as $r) {
    if ($r['fastestLapRank'] === 1) { $fastest = $r; break; }
}
$biggestGain = $gains[0] ?? null;

$pitCount = [];
$fastestStop = null;
foreach ($pitstops as $ps) {
    $pitCount[$ps['driverId']] = max($pitCount[$ps['driverId']] ?? 0, (int)$ps['stop']);
    if (is_numeric($ps['duration'] ?? null) && (!$fastestStop || (float)$ps['duration'] < (float)$fastestStop['duration'])) {
        $fastestStop = $ps;
    }
}

$prev = $schedule[$round - 1] ?? null;
$next = $schedule[$round + 1] ?? null;
$calendarRow = calendar_for_api_race($meta);

$page = [
    'title'       => "{$meta['name']} $year — Results",
    'description' => $winner
        ? "Full results of the {$year} {$meta['name']}: won by {$winner['name']} ({$winner['constructor']}). Race classification, qualifying, " . ($sprint ? 'sprint, ' : '') . 'pit stops and positions gained.'
        : "Results of the {$year} {$meta['name']} at {$meta['circuitName']}.",
    'nav'         => 'results',
    'charts'      => count($gains) > 1 || ($race && $year >= 1996),
    'schema'      => [
        '@context'  => 'https://schema.org',
        '@type'     => 'SportsEvent',
        'name'      => "{$year} {$meta['name']}",
        'startDate' => $meta['datetime']?->format(DATE_ATOM),
        'sport'     => 'Formula 1',
        'location'  => ['@type' => 'Place', 'name' => $meta['circuitName'], 'address' => trim($meta['locality'] . ', ' . $meta['country'], ', ')],
    ],
];
require INC_DIR . '/layout/header.php';

/** Shared race/sprint classification table. */
$renderClassification = static function (array $rows, bool $isSprint) use ($pitCount): void { ?>
    <div class="card table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th class="w-pos">Pos</th><th class="hide-sm">No</th><th>Driver</th><th class="hide-sm">Team</th>
                    <th class="num hide-sm">Laps</th><th class="num">Time / Status</th><th class="num hide-sm">Grid</th><th class="num hide-sm">+/−</th>
                    <?php if (!$isSprint && $pitCount): ?><th class="num hide-sm">Stops</th><?php endif; ?>
                    <th class="num">Pts</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($rows as $r):
                $delta = ($r['grid'] > 0 && $r['classified']) ? $r['grid'] - $r['position'] : null; ?>
                <tr>
                    <td><span class="<?= $r['classified'] ? pos_class($r['position']) : 'pos pos-dnf' ?>"><?= e($r['classified'] ? $r['positionText'] : ($r['positionText'] === 'R' ? 'DNF' : $r['positionText'])) ?></span></td>
                    <td class="hide-sm muted tabular"><?= e($r['number']) ?></td>
                    <td>
                        <div class="driver-cell">
                            <span class="swatch" style="--c: <?= e($r['color']) ?>"></span><?= driver_link($r) ?>
                            <?php if (!$isSprint && $r['fastestLapRank'] === 1): ?><span class="badge badge-purple" title="Fastest lap <?= e($r['fastestLapTime']) ?>">FL</span><?php endif; ?>
                        </div>
                    </td>
                    <td class="hide-sm"><?= team_link($r) ?></td>
                    <td class="num hide-sm muted"><?= e($r['laps']) ?></td>
                    <td class="num mono"><?= e(result_time($r)) ?></td>
                    <td class="num hide-sm muted"><?= $r['grid'] ? (int)$r['grid'] : 'PL' ?></td>
                    <td class="num hide-sm <?= $delta > 0 ? 'delta-up' : ($delta < 0 ? 'delta-down' : 'subtle') ?>"><?= $delta === null ? '–' : ($delta > 0 ? '▲' . $delta : ($delta < 0 ? '▼' . abs($delta) : '–')) ?></td>
                    <?php if (!$isSprint && $pitCount): ?><td class="num hide-sm muted"><?= $pitCount[$r['driverId']] ?? 0 ?></td><?php endif; ?>
                    <td class="num points"><?= $r['points'] ? e(fmt_number($r['points'])) : '<span class="subtle">0</span>' ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php };
?>
<div class="container">
    <header class="page-head">
        <nav class="breadcrumb" aria-label="Breadcrumb">
            <a href="<?= e(url('results.php', ['year' => $year])) ?>"><?= e($year) ?> results</a><span class="sep">/</span><span>Round <?= (int)$round ?></span>
        </nav>
        <div class="spread">
            <div>
                <div class="eyebrow">Round <?= (int)$round ?> · <?= e(fmt_date($meta['date'])) ?></div>
                <h1 class="page-title"><?= e($meta['name']) ?></h1>
                <p class="page-lead row" style="gap: 8px">
                    <?= flag_img(country_iso($meta['country']), $meta['country']) ?>
                    <?= e($meta['circuitName']) ?> · <?= e(trim($meta['locality'] . ', ' . $meta['country'], ', ')) ?>
                </p>
            </div>
            <div class="row-wrap">
                <?php if ($prev): ?><a class="btn btn-secondary btn-sm" href="<?= e(race_url($year, $prev['round'])) ?>"><?= icon('chevron-left', 'icon-sm') ?> <?= e(str_replace(' Grand Prix', '', $prev['name'])) ?></a><?php endif; ?>
                <?php if ($next): ?><a class="btn btn-secondary btn-sm" href="<?= e(race_url($year, $next['round'])) ?>"><?= e(str_replace(' Grand Prix', '', $next['name'])) ?> <?= icon('chevron-right', 'icon-sm') ?></a><?php endif; ?>
            </div>
        </div>
    </header>

    <?php if (!$race && !$quali): ?>
        <section class="section">
            <div class="card empty">
                <?= icon('clock', 'icon-lg') ?>
                <h3>No results yet</h3>
                <p>The <?= e($meta['name']) ?> takes place on <?= local_time($meta['datetime'], 'datetime') ?>. Results appear here shortly after each session.</p>
                <?php if ($calendarRow): ?>
                    <a class="btn btn-secondary mt-4" href="<?= e(url('circuit-details.php', ['key' => $calendarRow['circuit_key'], 'year' => $year])) ?>">View the weekend schedule</a>
                <?php endif; ?>
            </div>
        </section>
    <?php else: ?>

    <?php if ($winner): ?>
    <section class="section" style="padding-bottom: 0">
        <div class="stats">
            <div class="stat accent-top" style="--c: <?= e($winner['color']) ?>">
                <div class="stat-label">Winner</div>
                <div class="stat-value" style="font-size: 24px"><?= e($winner['familyName']) ?></div>
                <div class="stat-sub"><?= e($winner['constructor']) ?> · <?= e($winner['time'] ?? '') ?></div>
            </div>
            <div class="stat">
                <div class="stat-label">Pole position</div>
                <div class="stat-value" style="font-size: 24px"><?= e($pole['familyName'] ?? '–') ?></div>
                <div class="stat-sub mono"><?= e($pole ? ($pole['q3'] ?? $pole['q2'] ?? $pole['q1'] ?? '') : '') ?></div>
            </div>
            <div class="stat">
                <div class="stat-label">Fastest lap</div>
                <div class="stat-value" style="font-size: 24px; color: var(--purple)"><?= e($fastest['familyName'] ?? '–') ?></div>
                <div class="stat-sub mono"><?= $fastest ? e($fastest['fastestLapTime']) . ' · lap ' . e($fastest['fastestLapLap']) : '' ?></div>
            </div>
            <div class="stat">
                <?php if ($fastestStop): $fs = $byDriver[$fastestStop['driverId']] ?? null; ?>
                    <div class="stat-label">Fastest pit stop</div>
                    <div class="stat-value" style="font-size: 24px"><?= e($fastestStop['duration']) ?>s</div>
                    <div class="stat-sub"><?= e($fs['familyName'] ?? $fastestStop['driverId']) ?> · lap <?= e($fastestStop['lap']) ?></div>
                <?php else: ?>
                    <div class="stat-label">Biggest gain</div>
                    <div class="stat-value" style="font-size: 24px"><?= e($biggestGain ? $biggestGain['row']['familyName'] : '–') ?></div>
                    <div class="stat-sub"><?= $biggestGain ? '+' . (int)$biggestGain['value'] . ' places' : '' ?></div>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <section class="section">
        <div class="tabs" role="tablist">
            <?php if ($race): ?><button class="tab" role="tab" id="t-race" data-hash="race" aria-controls="p-race" aria-selected="true">Race</button><?php endif; ?>
            <?php if ($quali): ?><button class="tab" role="tab" id="t-quali" data-hash="qualifying" aria-controls="p-quali" aria-selected="<?= $race ? 'false' : 'true' ?>">Qualifying</button><?php endif; ?>
            <?php if ($sprint): ?><button class="tab" role="tab" id="t-sprint" data-hash="sprint" aria-controls="p-sprint" aria-selected="false">Sprint</button><?php endif; ?>
            <?php if ($pitstops): ?><button class="tab" role="tab" id="t-pits" data-hash="pit-stops" aria-controls="p-pits" aria-selected="false">Pit stops <span class="count"><?= count($pitstops) ?></span></button><?php endif; ?>
            <?php if ($race && $year >= 1996): ?><button class="tab" role="tab" id="t-laps" data-hash="lap-chart" aria-controls="p-laps" aria-selected="false">Lap chart</button><?php endif; ?>
            <?php if (count($gains) > 1): ?><button class="tab" role="tab" id="t-gains" data-hash="positions" aria-controls="p-gains" aria-selected="false">Positions gained</button><?php endif; ?>
        </div>

        <?php if ($race): ?>
        <div id="p-race" role="tabpanel" aria-labelledby="t-race">
            <?php $renderClassification($race['rows'], false); ?>
        </div>
        <?php endif; ?>

        <?php if ($quali): ?>
        <div id="p-quali" role="tabpanel" aria-labelledby="t-quali"<?= $race ? ' hidden' : '' ?>>
            <div class="card table-wrap">
                <table class="table">
                    <thead><tr><th class="w-pos">Pos</th><th>Driver</th><th class="hide-sm">Team</th><th class="num">Q1</th><th class="num">Q2</th><th class="num">Q3</th><th class="num hide-sm">Gap</th></tr></thead>
                    <tbody>
                    <?php
                    $toSec = static function (?string $t): ?float {
                        if (!$t || !preg_match('/^(?:(\d+):)?(\d+(?:\.\d+)?)$/', $t, $m)) { return null; }
                        return (int)$m[1] * 60 + (float)$m[2];
                    };
                    $poleTime = $toSec($pole['q3'] ?? $pole['q2'] ?? $pole['q1'] ?? null);
                    foreach ($quali['rows'] as $q):
                        $best = $toSec($q['q3'] ?? $q['q2'] ?? $q['q1'] ?? null);
                        $gap = ($best && $poleTime && $q['position'] > 1) ? '+' . number_format($best - $poleTime, 3) : ''; ?>
                        <tr>
                            <td><span class="<?= pos_class($q['position']) ?>"><?= (int)$q['position'] ?></span></td>
                            <td><div class="driver-cell"><span class="swatch" style="--c: <?= e($q['color']) ?>"></span><?= driver_link($q) ?></div></td>
                            <td class="hide-sm"><?= team_link($q) ?></td>
                            <td class="num mono <?= $q['q2'] ? 'subtle' : '' ?>"><?= e($q['q1'] ?: '–') ?></td>
                            <td class="num mono <?= $q['q3'] ? 'subtle' : '' ?>"><?= e($q['q2'] ?: '–') ?></td>
                            <td class="num mono"><?= e($q['q3'] ?: '–') ?></td>
                            <td class="num hide-sm muted mono"><?= e($gap) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($sprint): ?>
        <div id="p-sprint" role="tabpanel" aria-labelledby="t-sprint" hidden>
            <?php $renderClassification($sprint['rows'], true); ?>
        </div>
        <?php endif; ?>

        <?php if ($pitstops): ?>
        <div id="p-pits" role="tabpanel" aria-labelledby="t-pits" hidden>
            <div class="card table-wrap">
                <table class="table table-compact">
                    <thead><tr><th>Driver</th><th class="hide-sm">Team</th><th class="num">Stop</th><th class="num">Lap</th><th class="num hide-sm">Time of day</th><th class="num">Duration</th></tr></thead>
                    <tbody>
                    <?php
                    usort($pitstops, static fn($a, $b) => [(int)$a['lap'], $a['time']] <=> [(int)$b['lap'], $b['time']]);
                    foreach ($pitstops as $ps):
                        $d = $byDriver[$ps['driverId']] ?? null; ?>
                        <tr<?= $fastestStop === $ps ? ' class="is-highlight"' : '' ?>>
                            <td><div class="driver-cell"><span class="swatch" style="--c: <?= e($d['color'] ?? '#888') ?>"></span><?= $d ? driver_link($d) : e($ps['driverId']) ?></div></td>
                            <td class="hide-sm"><?= $d ? team_link($d) : '' ?></td>
                            <td class="num"><?= (int)$ps['stop'] ?></td>
                            <td class="num"><?= (int)$ps['lap'] ?></td>
                            <td class="num hide-sm muted mono"><?= e($ps['time']) ?></td>
                            <td class="num mono fw-600"><?= e($ps['duration']) ?><?= is_numeric($ps['duration']) ? 's' : '' ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <p class="text-xs subtle mt-2">Duration is the total time spent in the pit lane, not the stationary time.</p>
        </div>
        <?php endif; ?>

        <?php if (count($gains) > 1): ?>
        <?php if ($race && $year >= 1996): ?>
        <div id="p-laps" role="tabpanel" aria-labelledby="t-laps" hidden>
            <div class="card card-pad">
                <div class="spread mb-4">
                    <div><h2 class="text-lg">Lap-by-lap positions</h2><p class="text-sm muted">Hover a line to follow one driver. Gaps in a line mean the driver retired.</p></div>
                </div>
                <div class="chart-box" data-laps="<?= e(url('api/laps.php', ['year' => $year, 'round' => $round])) ?>">
                    <div class="empty"><?= icon('chart', 'icon-lg') ?><p>Loading lap data…</p></div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div id="p-gains" role="tabpanel" aria-labelledby="t-gains" hidden>
            <div class="card card-pad">
                <div style="position: relative; height: <?= max(320, count($gains) * 24) ?>px"><canvas data-chart="chart-gains" aria-label="Positions gained from grid to finish"></canvas></div>
                <script type="application/json" id="chart-gains"><?= json_encode(['type' => 'gains', 'labels' => array_column($gains, 'label'), 'values' => array_column($gains, 'value')], JSON_HEX_TAG) ?></script>
            </div>
        </div>
        <?php endif; ?>
    </section>
    <?php endif; ?>
</div>
<?php require INC_DIR . '/layout/footer.php'; ?>

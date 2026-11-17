<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$key = str_param('key');
if ($key === '' || !preg_match('/^[a-z0-9_\-]{1,50}$/i', $key)) {
    abort_404('No circuit selected.');
}
$years = array_map('intval', array_column(db_all('SELECT race_year FROM circuits WHERE circuit_key = ? ORDER BY race_year DESC', [$key]), 'race_year'));
if (!$years) {
    abort_404('We could not find this circuit.');
}
$year = int_param('year', in_array(current_season(), $years, true) ? current_season() : $years[0]);
$c = db_one('SELECT * FROM circuits WHERE circuit_key = ? AND race_year = ?', [$key, $year])
    ?? db_one('SELECT * FROM circuits WHERE circuit_key = ? ORDER BY race_year DESC LIMIT 1', [$key]);
$year = (int)$c['race_year'];

$sessions = weekend_sessions($c);
$now = new DateTimeImmutable('now', new DateTimeZone('UTC'));
$raceAt = db_datetime($c['race_datetime']);
$apiRace = api_race_for_calendar($c);
$hasResults = $apiRace && $raceAt && $raceAt < $now && f1_race($year, $apiRace['round']);
$showWeather = $raceAt && $raceAt > $now && $raceAt->getTimestamp() - time() < 15 * 86400;

$winners = $apiRace ? f1_circuit_winners($apiRace['circuitId']) : [];
$mostWins = [];
foreach ($winners as $w) {
    $mostWins[$w['driverId']] ??= ['row' => $w, 'n' => 0];
    $mostWins[$w['driverId']]['n']++;
}
usort($mostWins, static fn($a, $b) => $b['n'] <=> $a['n']);

$highlights = array_filter(array_map('trim', explode(',', (string)($c['highlights'] ?? ''))));

$page = [
    'title'       => "{$c['grandprix']} $year — {$c['title']}",
    'description' => "{$c['grandprix']} $year at {$c['title']}: weekend schedule in your timezone, circuit facts, lap record and past winners.",
    'nav'         => 'schedule',
    'image'       => $c['map_url'] ?: null,
    'schema'      => [
        '@context'  => 'https://schema.org',
        '@type'     => 'SportsEvent',
        'name'      => "{$c['grandprix']} $year",
        'startDate' => $raceAt?->format(DATE_ATOM),
        'sport'     => 'Formula 1',
        'location'  => ['@type' => 'Place', 'name' => $c['title'], 'address' => $c['location']],
    ],
];
if (!$page['image']) {
    unset($page['image']);
}
require INC_DIR . '/layout/header.php';
?>
<div class="container">
    <header class="page-head">
        <nav class="breadcrumb" aria-label="Breadcrumb">
            <a href="<?= e(url('kalender.php', ['year' => $year])) ?>"><?= e($year) ?> calendar</a><span class="sep">/</span><span><?= e($c['grandprix']) ?></span>
        </nav>
        <div class="spread">
            <div>
                <div class="eyebrow">Round <?= (int)$c['calendar_order'] ?> · <?= e(weekend_range($c)) ?> <?= e($year) ?></div>
                <h1 class="page-title"><?= e($c['grandprix']) ?></h1>
                <p class="page-lead row" style="gap: 8px">
                    <?php if (!empty($c['country_flag_url'])): ?><img class="flag" src="<?= e($c['country_flag_url']) ?>" alt=""><?php endif; ?>
                    <?= e($c['title']) ?> · <?= e($c['location']) ?>
                </p>
            </div>
            <div class="row-wrap">
                <?php if ($hasResults): ?>
                    <a class="btn btn-primary" href="<?= e(race_url($year, $apiRace['round'])) ?>"><?= icon('flag', 'icon-sm') ?> Race results</a>
                <?php endif; ?>
                <?php if (count($years) > 1): ?>
                <form method="get" action="<?= e(url('circuit-details.php')) ?>">
                    <input type="hidden" name="key" value="<?= e($key) ?>">
                    <label class="sr-only" for="year">Season</label>
                    <select class="select select-sm" id="year" name="year" data-autosubmit>
                        <?php foreach ($years as $y): ?><option value="<?= $y ?>"<?= $y === $year ? ' selected' : '' ?>><?= $y ?></option><?php endforeach; ?>
                    </select>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <section class="section">
        <div class="layout-sidebar">
            <div class="stack-lg">
                <?php if ($sessions): ?>
                <div class="card">
                    <div class="card-head">
                        <h2>Weekend schedule</h2>
                        <a class="btn btn-ghost btn-sm" href="<?= e(url('calendar.ics.php', ['year' => $year])) ?>"><?= icon('calendar', 'icon-sm') ?> Add to calendar</a>
                    </div>
                    <div class="table-wrap">
                        <table class="table">
                            <tbody>
                            <?php foreach ($sessions as $s): $past = $s['at'] < $now; ?>
                                <tr<?= $past ? ' style="opacity:.6"' : '' ?>>
                                    <td class="fw-600"><?= e($s['label']) ?></td>
                                    <td><?= local_time($s['at'], 'date') ?></td>
                                    <td class="num fw-600"><?= local_time($s['at'], 'time') ?></td>
                                    <td class="num" style="width: 110px"><?= $past ? '<span class="badge">Done</span>' : '' ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if ($showWeather && $apiRace): ?>
                        <div class="card-foot"><div class="weather" style="margin: 0" data-weather="<?= e(url('api/weather.php', ['year' => $year, 'round' => $c['calendar_order']])) ?>" hidden></div></div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <?php if (!empty($c['map_url'])): ?>
                <div class="card card-pad" style="background: #fff">
                    <img src="<?= e($c['map_url']) ?>" alt="Track layout of <?= e($c['title']) ?>" style="margin: 0 auto; max-height: 520px">
                </div>
                <?php endif; ?>

                <?php if (!empty($c['description']) || $highlights): ?>
                <div class="card card-pad prose" style="max-width: none">
                    <h2 style="margin-top: 0">About the circuit</h2>
                    <?php if (!empty($c['description'])): ?><p><?= nl2br(e($c['description'])) ?></p><?php endif; ?>
                    <?php if ($highlights): ?>
                        <div class="chips mt-3"><?php foreach ($highlights as $h): ?><span class="chip"><?= e($h) ?></span><?php endforeach; ?></div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <?php if ($winners): ?>
                <div class="card">
                    <div class="card-head"><h2>Previous winners</h2><span class="meta"><?= count($winners) ?> races at this circuit</span></div>
                    <div class="table-wrap">
                        <table class="table table-compact">
                            <thead><tr><th>Year</th><th>Driver</th><th class="hide-sm">Team</th><th class="num hide-sm">Time</th></tr></thead>
                            <tbody>
                            <?php foreach (array_slice($winners, 0, 12) as $w): ?>
                                <tr>
                                    <td><a class="fw-600" href="<?= e(race_url($w['season'], $w['round'])) ?>"><?= (int)$w['season'] ?></a></td>
                                    <td><div class="driver-cell"><span class="swatch" style="--c: <?= e($w['color']) ?>"></span><?= driver_link($w) ?></div></td>
                                    <td class="hide-sm"><?= team_link($w) ?></td>
                                    <td class="num mono hide-sm muted"><?= e($w['time'] ?? '') ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <aside class="stack">
                <div class="card card-pad">
                    <h2 class="text-lg mb-4">Circuit facts</h2>
                    <dl class="facts" style="grid-template-columns: 1fr 1fr">
                        <div class="fact"><dt>First Grand Prix</dt><dd><?= e($c['first_gp_year'] ?: '–') ?></dd></div>
                        <div class="fact"><dt>Number of laps</dt><dd><?= e($c['lap_count'] ?: '–') ?></dd></div>
                        <div class="fact"><dt>Circuit length</dt><dd><?= $c['circuit_length_km'] > 0 ? e(number_format((float)$c['circuit_length_km'], 3)) . ' km' : '–' ?></dd></div>
                        <div class="fact"><dt>Race distance</dt><dd><?= $c['race_distance_km'] > 0 ? e(number_format((float)$c['race_distance_km'], 3)) . ' km' : '–' ?></dd></div>
                    </dl>
                    <?php if (!empty($c['lap_record'])): ?>
                        <hr class="divider">
                        <div class="fact"><dt class="text-xs subtle">Lap record</dt><dd class="fw-600 mono"><?= e($c['lap_record']) ?></dd></div>
                    <?php endif; ?>
                </div>

                <?php if ($mostWins && $mostWins[0]['n'] > 1): ?>
                <div class="card card-pad">
                    <h2 class="text-lg mb-4">Most wins here</h2>
                    <div class="stack" style="gap: 10px">
                        <?php foreach (array_slice($mostWins, 0, 5) as $m): ?>
                            <div class="spread text-sm">
                                <span class="row" style="gap: 8px"><span class="swatch" style="--c: <?= e($m['row']['color']) ?>"></span><?= driver_link($m['row']) ?></span>
                                <span class="fw-700"><?= (int)$m['n'] ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <p class="text-xs subtle mt-3">Based on the last <?= count($winners) ?> Grands Prix held at this circuit.</p>
                </div>
                <?php endif; ?>

                <a class="btn btn-secondary w-full" href="<?= e(url('kalender.php', ['year' => $year])) ?>"><?= icon('arrow-left', 'icon-sm') ?> Back to the <?= e($year) ?> calendar</a>
            </aside>
        </div>
    </section>
</div>
<?php require INC_DIR . '/layout/footer.php'; ?>

<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

/* ---- Next race weekend ---- */
$next = db_next_race();
$sessions = $next ? weekend_sessions($next) : [];
$now = new DateTimeImmutable('now', new DateTimeZone('UTC'));
$nextSession = null;
$liveSession = null;
foreach ($sessions as $s) {
    $end = $s['at']->modify($s['label'] === 'Race' ? '+2 hours' : '+1 hour');
    if ($s['at'] <= $now && $now <= $end) {
        $liveSession = $s;
    }
    if (!$nextSession && $s['at'] > $now) {
        $nextSession = $s;
    }
}
$roundsInSeason = $next ? (int)db_value('SELECT COUNT(*) FROM circuits WHERE race_year = ?', [$next['race_year']]) : 0;
$raceAt = $next ? db_datetime($next['race_datetime']) : null;
$showWeather = $raceAt && $raceAt->getTimestamp() - time() < 15 * 86400;

/* ---- Latest results & standings ---- */
$season = latest_results_season();
$driverStandings = f1_driver_standings($season);
$constructorStandings = f1_constructor_standings($season);
$lastRound = $driverStandings['round'];
$lastRace = $lastRound ? f1_race($season, $lastRound) : null;
$lastQuali = $lastRound ? f1_race($season, $lastRound, 'qualifying') : null;
$leaderPts = $driverStandings['rows'][0]['points'] ?? 0;
$leaderTeamPts = $constructorStandings['rows'][0]['points'] ?? 0;

/* ---- News ---- */
$news = db_all('SELECT titel, artikel_url, publicatie_datum, afbeelding_url, source FROM f1_nieuws ORDER BY publicatie_datum DESC, id DESC LIMIT 6');

$page = [
    'nav'    => 'home',
    'schema' => [
        '@context' => 'https://schema.org',
        '@type'    => 'WebSite',
        'name'     => 'F1SITE.NL',
        'url'      => url(),
        'potentialAction' => [
            '@type'       => 'SearchAction',
            'target'      => url('zoek.php') . '?q={search_term_string}',
            'query-input' => 'required name=search_term_string',
        ],
    ],
];
require INC_DIR . '/layout/header.php';
?>

<div class="container">
    <?php if ($next): ?>
    <section class="section" aria-labelledby="next-race-title">
        <div class="hero">
            <div class="hero-body">
                <div>
                    <div class="eyebrow">
                        <?= $liveSession ? 'Live now' : 'Next Grand Prix' ?> · Round <?= (int)$next['calendar_order'] ?><?= $roundsInSeason ? ' of ' . $roundsInSeason : '' ?>
                    </div>
                    <h1 id="next-race-title"><?= e($next['grandprix']) ?></h1>
                    <div class="hero-meta">
                        <span><?= icon('map-pin', 'icon-sm') ?><?= e($next['title']) ?></span>
                        <span><?= icon('calendar', 'icon-sm') ?><?= e(weekend_range($next)) ?> <?= e($next['race_year']) ?></span>
                    </div>
                    <?php if ($showWeather): ?>
                        <div class="weather" data-weather="<?= e(url('api/weather.php', ['year' => $next['race_year'], 'round' => $next['calendar_order']])) ?>" hidden></div>
                    <?php endif; ?>
                    <div class="row-wrap mt-6">
                        <a class="btn btn-primary" href="<?= e(url('circuit-details.php', ['key' => $next['circuit_key'], 'year' => $next['race_year']])) ?>">Race weekend guide <?= icon('arrow-right', 'icon-sm') ?></a>
                        <a class="btn btn-secondary" href="<?= e(url('kalender.php')) ?>">Full schedule</a>
                    </div>
                </div>
                <div>
                    <?php if ($liveSession): ?>
                        <div class="countdown-label"><span class="badge badge-live">Live</span><span><?= e($liveSession['label']) ?> is underway</span></div>
                    <?php endif; ?>
                    <?php if ($nextSession): ?>
                        <div class="countdown-label">
                            <span>Until <strong><?= e($nextSession['label']) ?></strong></span>
                            <span><?= local_time($nextSession['at'], 'datetime') ?></span>
                        </div>
                        <div class="countdown" data-countdown="<?= e($nextSession['at']->format('Y-m-d\TH:i:s\Z')) ?>" aria-label="Countdown">
                            <div><strong data-unit="d">0</strong><span>Days</span></div>
                            <div><strong data-unit="h">00</strong><span>Hours</span></div>
                            <div><strong data-unit="m">00</strong><span>Min</span></div>
                            <div><strong data-unit="s">00</strong><span>Sec</span></div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php if ($sessions): ?>
            <div class="sessions">
                <?php foreach ($sessions as $s): ?>
                    <div class="session<?= $s['at'] < $now && $s !== $liveSession ? ' is-past' : '' ?><?= $s === $nextSession ? ' is-next' : '' ?>">
                        <div class="label"><?= e($s['label']) ?></div>
                        <div class="when"><?= local_time($s['at'], 'weekday-time') ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </section>
    <?php endif; ?>

    <section class="section" data-favourites="<?= e(url('api/favorites.php')) ?>" hidden aria-labelledby="fav-title">
        <div class="section-title"><h2 id="fav-title">Following</h2></div>
        <div class="grid-cards-sm" data-favourites-list></div>
    </section>

    <?php $fight = f1_title_fight($season); if ($fight): ?>
    <section class="section" style="padding-bottom: 0">
        <?php require INC_DIR . '/partials/title-fight.php'; ?>
    </section>
    <?php endif; ?>

    <section class="section">
        <div class="grid-2">
            <?php if ($lastRace): ?>
            <div class="card">
                <div class="card-head">
                    <div>
                        <h2><?= e($lastRace['race']['name']) ?></h2>
                        <div class="meta"><?= e($season) ?> · Round <?= (int)$lastRound ?> · <?= e(fmt_date($lastRace['race']['date'])) ?></div>
                    </div>
                    <a class="btn btn-secondary btn-sm" href="<?= e(race_url($season, $lastRound)) ?>">Full results</a>
                </div>
                <div class="card-pad">
                    <div class="podium">
                        <?php foreach ([1, 0, 2] as $i):
                            $r = $lastRace['rows'][$i] ?? null;
                            if (!$r) { continue; } ?>
                            <div class="podium-step p<?= $i + 1 ?>" style="--c: <?= e($r['color']) ?>">
                                <div class="p">P<?= $i + 1 ?></div>
                                <div class="n"><?= driver_link($r, true) ?></div>
                                <div class="t"><?= e($r['constructor']) ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="table-wrap">
                    <table class="table table-compact">
                        <thead><tr><th class="w-pos">Pos</th><th>Driver</th><th class="hide-sm">Team</th><th class="num">Time</th><th class="num">Pts</th></tr></thead>
                        <tbody>
                        <?php foreach (array_slice($lastRace['rows'], 3, 7) as $r): ?>
                            <tr>
                                <td><span class="<?= pos_class($r['position']) ?>"><?= e($r['positionText']) ?></span></td>
                                <td><div class="driver-cell"><span class="swatch" style="--c: <?= e($r['color']) ?>"></span><?= driver_link($r) ?></div></td>
                                <td class="hide-sm"><?= team_link($r) ?></td>
                                <td class="num mono"><?= e(result_time($r)) ?></td>
                                <td class="num points"><?= e(fmt_number($r['points'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php if ($lastQuali): $pole = $lastQuali['rows'][0]; ?>
                <div class="card-foot row">
                    <span class="badge">Pole</span>
                    <span><?= driver_link($pole) ?></span>
                    <span class="mono muted" style="margin-left:auto"><?= e($pole['q3'] ?? $pole['q2'] ?? $pole['q1'] ?? '') ?></span>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-head">
                    <div>
                        <h2><?= e($season) ?> Championship</h2>
                        <div class="meta">After round <?= (int)$lastRound ?></div>
                    </div>
                    <a class="btn btn-secondary btn-sm" href="<?= e(url('standings.php')) ?>">All standings</a>
                </div>
                <div class="tabs" role="tablist" style="padding: 0 12px; margin-bottom: 0">
                    <button class="tab" role="tab" id="t-drv" aria-controls="p-drv" aria-selected="true">Drivers</button>
                    <button class="tab" role="tab" id="t-con" aria-controls="p-con" aria-selected="false" tabindex="-1">Constructors</button>
                </div>
                <div id="p-drv" role="tabpanel" aria-labelledby="t-drv" class="table-wrap">
                    <table class="table table-compact">
                        <tbody>
                        <?php foreach (array_slice($driverStandings['rows'], 0, 10) as $r): ?>
                            <tr>
                                <td class="w-pos"><span class="<?= pos_class($r['position']) ?>"><?= (int)$r['position'] ?></span></td>
                                <td><div class="driver-cell"><span class="swatch" style="--c: <?= e($r['color']) ?>"></span><?= driver_link($r) ?></div></td>
                                <td class="hide-sm" style="width: 30%"><div class="bar" style="--c: <?= e($r['color']) ?>; --w: <?= $leaderPts ? round($r['points'] / $leaderPts * 100) : 0 ?>%"><span></span></div></td>
                                <td class="num points"><?= e(fmt_number($r['points'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div id="p-con" role="tabpanel" aria-labelledby="t-con" class="table-wrap" hidden>
                    <table class="table table-compact">
                        <tbody>
                        <?php foreach (array_slice($constructorStandings['rows'], 0, 10) as $r): ?>
                            <tr>
                                <td class="w-pos"><span class="<?= pos_class($r['position']) ?>"><?= (int)$r['position'] ?></span></td>
                                <td><?= team_link($r) ?></td>
                                <td class="hide-sm" style="width: 30%"><div class="bar" style="--c: <?= e($r['color']) ?>; --w: <?= $leaderTeamPts ? round($r['points'] / $leaderTeamPts * 100) : 0 ?>%"><span></span></div></td>
                                <td class="num points"><?= e(fmt_number($r['points'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <?php if ($news): ?>
    <section class="section" aria-labelledby="news-title">
        <div class="section-title">
            <h2 id="news-title">Latest news</h2>
            <a class="link-more" href="<?= e(url('nieuws.php')) ?>">All news <?= icon('arrow-right', 'icon-sm') ?></a>
        </div>
        <div class="grid-3">
            <?php foreach ($news as $article): require INC_DIR . '/partials/news-card.php'; endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
</div>

<?php require INC_DIR . '/layout/footer.php'; ?>

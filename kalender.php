<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$years = db_season_years();
$defaultYear = in_array(current_season(), $years, true) ? current_season() : ($years[0] ?? current_season());
$year = int_param('year', $defaultYear, FIRST_SEASON, current_season() + 1);
if ($years && !in_array($year, $years, true)) {
    $year = $defaultYear;
}

$races = db_calendar($year);
$now = new DateTimeImmutable('now', new DateTimeZone('UTC'));

// Winners per race date, from the API results.
$winners = [];
foreach (f1_season($year, 'results') as $race) {
    if ($race['race']['datetime'] && $race['rows']) {
        $winners[$race['race']['datetime']->format('Y-m-d')] = $race['rows'][0] + ['_round' => $race['race']['round']];
    }
}
$findWinner = static function (DateTimeImmutable $at) use ($winners): ?array {
    foreach ([0, -1, 1] as $shift) {
        $key = $at->modify("$shift day")->format('Y-m-d');
        if (isset($winners[$key])) {
            return $winners[$key];
        }
    }
    return null;
};

$nextKey = null;
foreach ($races as $r) {
    $at = db_datetime($r['race_datetime']);
    if ($at && $at->modify('+3 hours') > $now) {
        $nextKey = $r['circuit_key'];
        break;
    }
}
$completed = 0;
foreach ($races as $r) {
    $at = db_datetime($r['race_datetime']);
    if ($at && $at < $now) {
        $completed++;
    }
}
$sprintCount = count(array_filter($races, static fn($r) => !empty($r['sprint_datetime'])));

$page = [
    'title'       => "$year F1 Race Calendar",
    'description' => "The full $year Formula 1 calendar: all " . count($races) . " Grands Prix with dates, session times in your timezone, circuits and winners.",
    'nav'         => 'schedule',
];
require INC_DIR . '/layout/header.php';
?>
<div class="container">
    <header class="page-head">
        <div class="spread">
            <div>
                <div class="eyebrow"><?= count($races) ?> rounds · <?= $sprintCount ?> sprint weekends</div>
                <h1 class="page-title"><?= e($year) ?> Calendar</h1>
                <p class="page-lead"><?= $completed ?> of <?= count($races) ?> races completed. All session times are shown in your local timezone.</p>
            </div>
            <div class="row-wrap">
                <a class="btn btn-secondary btn-sm" href="<?= e(url('calendar.ics.php', ['year' => $year])) ?>"><?= icon('download', 'icon-sm') ?> Add to calendar</a>
                <form method="get" action="<?= e(url('kalender.php')) ?>">
                    <label class="sr-only" for="year">Season</label>
                    <select class="select select-sm" id="year" name="year" data-autosubmit>
                        <?php foreach ($years as $y): ?>
                            <option value="<?= $y ?>"<?= $y === $year ? ' selected' : '' ?>><?= $y ?> season</option>
                        <?php endforeach; ?>
                    </select>
                    <noscript><button class="btn btn-secondary btn-sm">Go</button></noscript>
                </form>
            </div>
        </div>
    </header>

    <section class="section">
        <?php if (!$races): ?>
            <div class="card empty"><?= icon('calendar', 'icon-lg') ?><h3>No races scheduled for <?= e($year) ?> yet</h3></div>
        <?php else: ?>
        <div class="grid-cards">
            <?php foreach ($races as $i => $r):
                $at = db_datetime($r['race_datetime']);
                $done = $at && $at->modify('+3 hours') < $now;
                $isNext = $r['circuit_key'] === $nextKey;
                $winner = ($done && $at) ? $findWinner($at) : null;
                $detailUrl = url('circuit-details.php', ['key' => $r['circuit_key'], 'year' => $year]); ?>
                <article class="card race-card<?= $done ? ' is-done' : '' ?><?= $isNext ? ' is-next' : '' ?>">
                    <a class="map" href="<?= e($detailUrl) ?>" tabindex="-1" aria-hidden="true">
                        <?php if (!empty($r['map_url'])): ?>
                            <img src="<?= e($r['map_url']) ?>" alt="" loading="lazy">
                        <?php else: ?>
                            <?= icon('flag', 'icon-lg') ?>
                        <?php endif; ?>
                    </a>
                    <div class="body">
                        <div class="round">
                            <span>Round <?= (int)($r['calendar_order'] ?: $i + 1) ?></span>
                            <?php if ($isNext): ?><span class="badge badge-red">Next</span>
                            <?php elseif (!empty($r['sprint_datetime'])): ?><span class="badge">Sprint</span><?php endif; ?>
                        </div>
                        <h3><a class="link-plain" href="<?= e($detailUrl) ?>"><?= e($r['grandprix']) ?></a></h3>
                        <div class="where">
                            <?php if (!empty($r['country_flag_url'])): ?><img class="flag" src="<?= e($r['country_flag_url']) ?>" alt="" loading="lazy"><?php endif; ?>
                            <?= e($r['title']) ?>
                        </div>
                        <div class="foot">
                            <span class="fw-600"><?= e(weekend_range($r)) ?></span>
                            <?php if ($winner): ?>
                                <a class="row link-plain text-sm" style="gap: 6px" href="<?= e(race_url($year, $winner['_round'])) ?>">
                                    <?= icon('trophy', 'icon-sm') ?><span class="swatch" style="--c: <?= e($winner['color']) ?>"></span><?= e($winner['familyName']) ?>
                                </a>
                            <?php elseif ($done): ?>
                                <span class="subtle text-sm">Completed</span>
                            <?php else: ?>
                                <span class="muted text-sm"><?= local_time($at, 'weekday-time') ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </section>
</div>
<?php require INC_DIR . '/layout/footer.php'; ?>

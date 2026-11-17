<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$all = array_values(array_filter(db_drivers(), static fn($d) => (int)$d['is_active'] === 1));
usort($all, static fn($a, $b) => strcmp($a['last_name'], $b['last_name']));
$byId = array_column($all, null, 'driver_id');

// Default comparison: the top two of the championship.
$season = latest_results_season();
$standings = f1_driver_standings($season)['rows'];
$defaults = [];
foreach ($standings as $row) {
    if (($d = driver_for_api($row)) && isset($byId[$d['driver_id']])) {
        $defaults[] = (int)$d['driver_id'];
    }
    if (count($defaults) === 2) {
        break;
    }
}
$id1 = int_param('d1', $defaults[0] ?? (int)($all[0]['driver_id'] ?? 0));
$id2 = int_param('d2', $defaults[1] ?? (int)($all[1]['driver_id'] ?? 0));
if ($id1 === $id2) {
    foreach ($defaults as $alt) {
        if ($alt !== $id1) { $id2 = $alt; break; }
    }
}

$build = static function (int $id) use ($season, $standings): ?array {
    $d = db_drivers()[$id] ?? null;
    if (!$d) {
        return null;
    }
    $apiId = api_driver_id($d);
    $standing = null;
    foreach ($standings as $row) {
        if ($row['driverId'] === $apiId) { $standing = $row; }
    }
    $seasonRows = $apiId ? f1_driver_season($season, $apiId) : [];
    return [
        'db'       => $d,
        'apiId'    => $apiId,
        'color'    => safe_color($d['team_color'], '#e10600'),
        'standing' => $standing,
        'season'   => $seasonRows,
        'summary'  => season_summary($seasonRows),
        'career'   => $apiId ? f1_driver_career($apiId) : null,
    ];
};
$a = $build($id1);
$b = $build($id2);

// Direct duels in races both drivers started this season.
$duel = ['race' => [0, 0], 'quali' => [0, 0], 'races' => 0];
if ($a && $b) {
    foreach ($a['season'] as $round => $ra) {
        $rb = $b['season'][$round] ?? null;
        if (!$rb) {
            continue;
        }
        $duel['races']++;
        $duel['race'][$ra['result']['position'] < $rb['result']['position'] ? 0 : 1]++;
        if ($ra['quali'] && $rb['quali']) {
            $duel['quali'][$ra['quali']['position'] < $rb['quali']['position'] ? 0 : 1]++;
        }
    }
}

$rows = [];
if ($a && $b) {
    $rows = [
        ["$season season", null, null, null],
        ['Championship position', $a['standing']['position'] ?? null, $b['standing']['position'] ?? null, false],
        ['Points', $a['standing']['points'] ?? $a['summary']['points'], $b['standing']['points'] ?? $b['summary']['points'], true],
        ['Wins', $a['summary']['wins'], $b['summary']['wins'], true],
        ['Podiums', $a['summary']['podiums'], $b['summary']['podiums'], true],
        ['Pole positions', $a['summary']['poles'], $b['summary']['poles'], true],
        ['Fastest laps', $a['summary']['fastestLaps'], $b['summary']['fastestLaps'], true],
        ['Average finish', $a['summary']['avgFinish'], $b['summary']['avgFinish'], false],
        ['Best finish', $a['summary']['best'], $b['summary']['best'], false],
        ['Retirements', $a['summary']['dnfs'], $b['summary']['dnfs'], false],
        ['Career', null, null, null],
        ['World titles', (int)$a['db']['championships_won'], (int)$b['db']['championships_won'], true],
        ['Grands Prix', $a['career']['races'] ?? null, $b['career']['races'] ?? null, true],
        ['Wins', $a['career']['wins'] ?? null, $b['career']['wins'] ?? null, true],
        ['Podiums', $a['career']['podiums'] ?? null, $b['career']['podiums'] ?? null, true],
        ['Pole positions', $a['career']['poles'] ?? null, $b['career']['poles'] ?? null, true],
        ['Age', age_from($a['db']['date_of_birth']), age_from($b['db']['date_of_birth']), null],
    ];
}

$title = ($a && $b) ? "{$a['db']['last_name']} vs {$b['db']['last_name']}" : 'Compare drivers';
$page = [
    'title'       => "$title — Driver comparison",
    'description' => 'Compare two Formula 1 drivers head to head: season results, direct duels, career wins, podiums and pole positions.',
    'nav'         => 'compare',
];
require INC_DIR . '/layout/header.php';

$select = static function (string $name, int $selected) use ($all): string {
    $html = '<select class="select" name="' . e($name) . '" id="' . e($name) . '" data-autosubmit>';
    foreach ($all as $d) {
        $html .= '<option value="' . (int)$d['driver_id'] . '"' . ((int)$d['driver_id'] === $selected ? ' selected' : '') . '>' . e($d['first_name'] . ' ' . $d['last_name']) . ($d['team_name'] ? ' — ' . e($d['team_name']) : '') . '</option>';
    }
    return $html . '</select>';
};
?>
<div class="container" style="max-width: 980px">
    <header class="page-head">
        <div class="eyebrow">Head to head</div>
        <h1 class="page-title">Compare drivers</h1>
        <p class="page-lead">Pick two drivers to compare their <?= e($season) ?> season and their careers side by side.</p>
    </header>

    <section class="section" style="padding-top: 16px">
        <form class="card card-pad" method="get" action="<?= e(url('compare.php')) ?>">
            <div class="compare-head">
                <div class="field"><label for="d1">Driver 1</label><?= $select('d1', $id1) ?></div>
                <a class="vs" href="<?= e(url('compare.php', ['d1' => $id2, 'd2' => $id1])) ?>" title="Swap drivers" aria-label="Swap drivers">VS</a>
                <div class="field"><label for="d2">Driver 2</label><?= $select('d2', $id2) ?></div>
            </div>
            <noscript><button class="btn btn-primary mt-4">Compare</button></noscript>
        </form>

        <?php if ($a && $b): ?>
        <div class="card card-pad mt-6">
            <div class="compare-head mb-6">
                <?php foreach ([$a, $b] as $i => $x): $d = $x['db']; ?>
                    <?php if ($i === 1): ?><span></span><?php endif; ?>
                    <a class="compare-driver link-plain" href="<?= e(driver_url($d)) ?>" style="--c: <?= e($x['color']) ?>">
                        <?php if ($d['image']): ?><img class="avatar" src="<?= e($d['image']) ?>" alt=""><?php endif; ?>
                        <h2><?= e($d['last_name']) ?></h2>
                        <span class="team-name text-sm"><span class="swatch"></span><?= e($d['team_name'] ?? '—') ?> · #<?= e($d['driver_number']) ?></span>
                    </a>
                <?php endforeach; ?>
            </div>

            <?php if ($duel['races']): ?>
            <div class="h2h mb-6">
                <?php foreach (['Ahead in races' => $duel['race'], 'Ahead in qualifying' => $duel['quali']] as $label => [$x, $y]): $tot = $x + $y; ?>
                    <div class="a <?= $x > $y ? 'win' : '' ?>"><?= $x ?></div>
                    <div class="label"><?= e($label) ?></div>
                    <div class="b <?= $y > $x ? 'win' : '' ?>"><?= $y ?></div>
                    <div class="h2h-bar"><span style="width: <?= $tot ? round($x / $tot * 100) : 50 ?>%; background: <?= e($a['color']) ?>"></span><span style="flex: 1; background: <?= e($a['color'] === $b['color'] ? 'var(--text-subtle)' : $b['color']) ?>"></span></div>
                <?php endforeach; ?>
                <p class="text-xs subtle text-center" style="grid-column: 1 / -1">Based on the <?= $duel['races'] ?> races both drivers started in <?= e($season) ?>.</p>
            </div>
            <?php endif; ?>

            <?php foreach ($rows as [$label, $va, $vb, $higherBetter]):
                if ($va === null && $vb === null && $higherBetter === null && !in_array($label, ['Age'], true)): ?>
                    <h3 class="text-sm subtle mt-6 mb-2" style="text-transform: uppercase; letter-spacing: .08em"><?= e($label) ?></h3>
                <?php continue; endif;
                $winA = $winB = false;
                if ($higherBetter !== null && $va !== null && $vb !== null && $va != $vb) {
                    $winA = $higherBetter ? $va > $vb : $va < $vb;
                    $winB = !$winA;
                } ?>
                <div class="compare-row">
                    <div class="v <?= $winA ? 'win' : '' ?>"><?= $va === null ? '–' : e(fmt_number($va)) ?></div>
                    <div class="label"><?= e($label) ?></div>
                    <div class="v <?= $winB ? 'win' : '' ?>"><?= $vb === null ? '–' : e(fmt_number($vb)) ?></div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
            <div class="card empty mt-6"><h3>Select two drivers to compare</h3></div>
        <?php endif; ?>
    </section>
</div>
<?php require INC_DIR . '/layout/footer.php'; ?>

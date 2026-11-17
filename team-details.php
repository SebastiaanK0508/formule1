<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$team = null;
if (isset($_GET['id'])) {
    $team = db_teams()[int_param('id', 0)] ?? null;
} elseif (($slug = str_param('slug')) !== '') {
    foreach (db_teams() as $t) {
        if (slugify($t['team_name']) === $slug || team_key($t['team_name']) === team_key($slug)) {
            $team = $t;
            break;
        }
    }
}
if (!$team) {
    abort_404('We could not find this team.');
}

$color = safe_color($team['team_color'], '#e10600');
$drivers = db_all('SELECT * FROM drivers WHERE team_id = ? AND is_active = 1 ORDER BY driver_number', [$team['team_id']]);
$year = latest_results_season();

// Find this team in the API standings to get its constructorId.
$standing = null;
$constructorId = null;
foreach ([$year, $year - 1] as $y) {
    foreach (f1_constructor_standings($y)['rows'] as $row) {
        if ($row['team'] && (int)$row['team']['team_id'] === (int)$team['team_id']) {
            $constructorId = $row['constructorId'];
            if ($y === $year) {
                $standing = $row;
            }
            break 2;
        }
    }
}

// Season results for this team, round by round.
$rounds = [];
$driverTotals = [];
if ($constructorId) {
    $sprints = f1_season($year, 'sprint');
    foreach (f1_season($year, 'results') as $round => $race) {
        $entries = array_values(array_filter($race['rows'], static fn($r) => $r['constructorId'] === $constructorId));
        if (!$entries) {
            continue;
        }
        $pts = 0.0;
        foreach ($entries as $r) {
            $pts += $r['points'];
            $driverTotals[$r['driverId']] ??= ['row' => $r, 'points' => 0.0, 'best' => null];
            $driverTotals[$r['driverId']]['points'] += $r['points'];
            if ($r['classified'] && ($driverTotals[$r['driverId']]['best'] === null || $r['position'] < $driverTotals[$r['driverId']]['best'])) {
                $driverTotals[$r['driverId']]['best'] = $r['position'];
            }
        }
        foreach ($sprints[$round]['rows'] ?? [] as $s) {
            if ($s['constructorId'] === $constructorId) {
                $pts += $s['points'];
                $driverTotals[$s['driverId']] ??= ['row' => $s, 'points' => 0.0, 'best' => null];
                $driverTotals[$s['driverId']]['points'] += $s['points'];
            }
        }
        usort($entries, static fn($a, $b) => $a['position'] <=> $b['position']);
        $rounds[$round] = ['race' => $race['race'], 'entries' => $entries, 'points' => $pts];
    }
    uasort($driverTotals, static fn($a, $b) => $b['points'] <=> $a['points']);
}
$career = $constructorId ? f1_constructor_career($constructorId) : null;
$wins = count(array_filter($rounds, static fn($r) => $r['entries'][0]['position'] === 1));
$podiums = 0;
foreach ($rounds as $r) {
    foreach ($r['entries'] as $e) {
        if ($e['classified'] && $e['position'] <= 3) {
            $podiums++;
        }
    }
}

$page = [
    'title'       => "{$team['team_name']} — Team profile",
    'description' => ($team['full_team_name'] ?: $team['team_name']) . ": drivers, team principal, base, power unit, {$year} results and all-time statistics.",
    'nav'         => 'teams',
    'schema'      => [
        '@context' => 'https://schema.org',
        '@type'    => 'SportsTeam',
        'name'     => $team['full_team_name'] ?: $team['team_name'],
        'sport'    => 'Formula 1',
        'logo'     => $team['logo_url'],
        'url'      => $team['website_url'],
        'athlete'  => array_map(static fn($d) => ['@type' => 'Person', 'name' => $d['first_name'] . ' ' . $d['last_name']], $drivers),
    ],
];
require INC_DIR . '/layout/header.php';
?>
<section class="profile-hero" style="--c: <?= e($color) ?>">
    <div class="container">
        <div>
            <nav class="breadcrumb" aria-label="Breadcrumb"><a href="<?= e(url('teams.php')) ?>">Teams</a><span class="sep">/</span><span><?= e($team['team_name']) ?></span></nav>
            <h1><span class="first"><?= e($team['full_team_name']) ?></span><?= e($team['team_name']) ?></h1>
            <div class="chips-row">
                <?php if ($team['base_location']): ?><span><?= icon('map-pin', 'icon-sm') ?><?= e($team['base_location']) ?></span><?php endif; ?>
                <?php if ($team['first_entry_year']): ?><span><?= icon('calendar', 'icon-sm') ?>First entry <?= e($team['first_entry_year']) ?></span><?php endif; ?>
                <?php if (!$team['is_active']): ?><span class="badge">Not on the current grid</span><?php endif; ?>
            </div>
            <div class="actions">
                <button class="btn btn-secondary follow-btn" type="button" data-follow="team:<?= (int)$team['team_id'] ?>"><?= icon('star', 'icon-sm') ?><span data-follow-label>Follow</span></button>
                <?php if ($team['website_url']): ?><a class="btn btn-ghost" href="<?= e($team['website_url']) ?>" rel="noopener">Official website <?= icon('external', 'icon-sm') ?></a><?php endif; ?>
            </div>
        </div>
        <?php if ($team['logo_url']): ?>
            <span class="logo-plate team-logo" style="padding: 18px 24px"><img src="<?= e($team['logo_url']) ?>" alt="<?= e($team['team_name']) ?> logo" style="max-height: 120px"></span>
        <?php endif; ?>
    </div>
</section>

<div class="container">
    <section class="section">
        <div class="layout-sidebar">
            <div class="stack-lg">
                <div>
                    <div class="section-title"><h2><?= e($year) ?> season</h2></div>
                    <div class="stats">
                        <div class="stat accent-top" style="--c: <?= e($color) ?>"><div class="stat-label">Constructors' position</div><div class="stat-value"><?= $standing ? 'P' . (int)$standing['position'] : '–' ?></div></div>
                        <div class="stat"><div class="stat-label">Points</div><div class="stat-value"><?= e(fmt_number($standing['points'] ?? 0)) ?></div></div>
                        <div class="stat"><div class="stat-label">Wins</div><div class="stat-value"><?= $wins ?></div></div>
                        <div class="stat"><div class="stat-label">Podiums</div><div class="stat-value"><?= $podiums ?></div></div>
                    </div>
                </div>

                <?php if ($drivers): ?>
                <div>
                    <div class="section-title"><h2>Drivers</h2></div>
                    <div class="grid-cards" style="grid-template-columns: repeat(auto-fill, minmax(min(100%, 240px), 1fr))">
                        <?php foreach ($drivers as $d):
                            $apiRow = null;
                            foreach ($driverTotals as $tot) {
                                $m = driver_for_api($tot['row']);
                                if ($m && (int)$m['driver_id'] === (int)$d['driver_id']) { $apiRow = $tot; }
                            } ?>
                            <a class="card card-link driver-card link-plain" style="--c: <?= e($color) ?>" href="<?= e(driver_url($d)) ?>">
                                <div class="photo"><?php if ($d['image']): ?><img src="<?= e($d['image']) ?>" alt="<?= e($d['first_name'] . ' ' . $d['last_name']) ?>" loading="lazy"><?php endif; ?><span class="number"><?= e($d['driver_number']) ?></span></div>
                                <div class="body">
                                    <span class="first"><?= e($d['first_name']) ?></span>
                                    <span class="last"><?= e($d['last_name']) ?></span>
                                    <?php if ($apiRow): ?><div class="meta"><span><?= e(fmt_number($apiRow['points'])) ?> pts</span><span>Best: <?= $apiRow['best'] ? 'P' . (int)$apiRow['best'] : '–' ?></span></div><?php endif; ?>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($rounds): ?>
                <div class="card">
                    <div class="card-head"><h2>Race by race</h2><span class="meta"><?= count($rounds) ?> races</span></div>
                    <div class="table-wrap">
                        <table class="table table-compact">
                            <thead><tr><th>Rd</th><th>Grand Prix</th><th>Best</th><th class="hide-sm">Second car</th><th class="num">Pts</th></tr></thead>
                            <tbody>
                            <?php foreach ($rounds as $round => $r): ?>
                                <tr>
                                    <td class="muted tabular"><?= (int)$round ?></td>
                                    <td><a class="link-plain fw-600" href="<?= e(race_url($year, $round)) ?>"><?= e(str_replace(' Grand Prix', ' GP', $r['race']['name'])) ?></a></td>
                                    <?php foreach ([0, 1] as $i): $e = $r['entries'][$i] ?? null; ?>
                                        <td<?= $i ? ' class="hide-sm"' : '' ?>>
                                            <?php if ($e): ?>
                                                <span class="row" style="gap: 8px">
                                                    <span class="<?= $e['classified'] ? pos_class($e['position']) : 'pos pos-dnf' ?>"><?= $e['classified'] ? (int)$e['position'] : 'DNF' ?></span>
                                                    <span class="text-sm"><?= e($e['familyName']) ?></span>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    <?php endforeach; ?>
                                    <td class="num points"><?= e(fmt_number($r['points'])) ?></td>
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
                    <h2 class="text-lg mb-4">Team facts</h2>
                    <dl class="facts" style="grid-template-columns: 1fr">
                        <?php foreach ([
                            'Full name'          => $team['full_team_name'],
                            'Team principal'     => $team['team_principal'],
                            'Technical director' => $team['technical_director'],
                            'Base'               => $team['base_location'],
                            'Chassis'            => $team['chassis'],
                            'Power unit'         => $team['current_engine_supplier'],
                            'First entry'        => $team['first_entry_year'],
                        ] as $label => $value): if (!$value) { continue; } ?>
                            <div class="fact"><dt><?= e($label) ?></dt><dd><?= e($value) ?></dd></div>
                        <?php endforeach; ?>
                    </dl>
                </div>
                <?php if ($career && $career['seasons']): ?>
                <div class="card card-pad">
                    <h2 class="text-lg mb-4">All-time record</h2>
                    <dl class="facts" style="grid-template-columns: 1fr 1fr">
                        <div class="fact"><dt>Constructors' titles</dt><dd class="text-lg"><?= (int)$team['championships_won'] ?></dd></div>
                        <div class="fact"><dt>Seasons</dt><dd class="text-lg"><?= (int)$career['seasons'] ?></dd></div>
                        <div class="fact"><dt>Race wins</dt><dd class="text-lg"><?= (int)$career['wins'] ?></dd></div>
                        <div class="fact"><dt>Pole positions</dt><dd class="text-lg"><?= (int)$career['poles'] ?></dd></div>
                    </dl>
                    <p class="text-xs subtle mt-3">Under the current constructor entry (<?= e($constructorId) ?>).</p>
                </div>
                <?php endif; ?>
            </aside>
        </div>
    </section>
</div>
<?php require INC_DIR . '/layout/footer.php'; ?>

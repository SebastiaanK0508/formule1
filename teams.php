<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$teams = db_all('SELECT * FROM teams WHERE is_active = 1 ORDER BY team_name');
$drivers = db_all('SELECT driver_id, first_name, last_name, image, team_id, driver_number FROM drivers WHERE is_active = 1 AND team_id IS NOT NULL ORDER BY driver_number');
$byTeam = [];
foreach ($drivers as $d) {
    $byTeam[(int)$d['team_id']][] = $d;
}

$season = latest_results_season();
$standing = [];
foreach (f1_constructor_standings($season)['rows'] as $row) {
    if ($row['team']) {
        $standing[(int)$row['team']['team_id']] = $row;
    }
}
usort($teams, static fn($a, $b) => [$standing[(int)$a['team_id']]['position'] ?? 99, $a['team_name']] <=> [$standing[(int)$b['team_id']]['position'] ?? 99, $b['team_name']]);

// All-time constructor archive (static snapshot of the f1db dataset).
$archive = json_decode((string)@file_get_contents(DATA_DIR . '/constructors-archive.json'), true) ?: [];
usort($archive, static fn($a, $b) => [$b['totalRaceWins'], $b['totalRaceStarts']] <=> [$a['totalRaceWins'], $a['totalRaceStarts']]);

$page = [
    'title'       => current_season() . ' F1 Teams',
    'description' => 'All ' . count($teams) . ' Formula 1 teams of ' . current_season() . ' with drivers, team principals and standings — plus an archive of every constructor in F1 history.',
    'nav'         => 'teams',
];
require INC_DIR . '/layout/header.php';
?>
<div class="container">
    <header class="page-head">
        <div class="eyebrow"><?= count($teams) ?> constructors</div>
        <h1 class="page-title"><?= current_season() ?> Teams</h1>
        <p class="page-lead">Every team on the grid, ordered by their position in the <?= e($season) ?> constructors' championship.</p>
    </header>

    <section class="section" style="padding-top: 16px">
        <div class="grid-cards">
            <?php foreach ($teams as $t):
                $color = safe_color($t['team_color']);
                $s = $standing[(int)$t['team_id']] ?? null; ?>
                <article class="card card-link accent-top team-card" style="--c: <?= e($color) ?>">
                    <div class="spread" style="align-items: flex-start">
                        <div class="logo">
                            <?php if (!empty($t['logo_url'])): ?><span class="logo-plate"><img src="<?= e($t['logo_url']) ?>" alt="<?= e($t['team_name']) ?> logo" loading="lazy"></span><?php endif; ?>
                        </div>
                        <button class="icon-btn follow-btn" type="button" data-follow="team:<?= (int)$t['team_id'] ?>" aria-label="Follow <?= e($t['team_name']) ?>"><?= icon('star') ?></button>
                    </div>
                    <div>
                        <h3><a class="link-plain" href="<?= e(team_url($t)) ?>"><?= e($t['team_name']) ?></a></h3>
                        <div class="full"><?= e($t['full_team_name']) ?></div>
                    </div>
                    <div class="drivers">
                        <?php foreach ($byTeam[(int)$t['team_id']] ?? [] as $d): ?>
                            <a class="person link-plain" href="<?= e(driver_url($d)) ?>">
                                <?php if ($d['image']): ?><img class="avatar" src="<?= e($d['image']) ?>" alt="" loading="lazy"><?php endif; ?>
                                <?= e($d['last_name']) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                    <div class="standing">
                        <span><?= $t['team_principal'] ? 'Principal: ' . e($t['team_principal']) : e($t['base_location']) ?></span>
                        <?php if ($s): ?><span><strong>P<?= (int)$s['position'] ?></strong> · <?= e(fmt_number($s['points'])) ?> pts</span><?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <?php if ($archive): ?>
    <section class="section" id="archive">
        <div class="toolbar">
            <div>
                <h2 style="font-size: 22px">Constructor archive</h2>
                <p class="muted text-sm mt-1"><?= count($archive) ?> constructors that have entered a Formula 1 World Championship race.</p>
            </div>
            <div style="min-width: min(100%, 320px)">
                <label class="sr-only" for="archive-filter">Filter constructors</label>
                <input class="input" id="archive-filter" type="search" placeholder="Filter by name or country…" data-text-filter="#archive-table" data-empty="#archive-empty">
            </div>
        </div>
        <div class="card table-wrap" style="max-height: 640px; overflow-y: auto">
            <table class="table table-compact" id="archive-table">
                <thead style="position: sticky; top: 0; z-index: 1"><tr><th>Constructor</th><th class="hide-sm">Country</th><th class="num">Starts</th><th class="num">Wins</th><th class="num hide-sm">Podiums</th><th class="num hide-sm">Poles</th><th class="num">Titles</th></tr></thead>
                <tbody>
                <?php foreach ($archive as $a):
                    $country = ucwords(str_replace('-', ' ', (string)$a['countryId'])); ?>
                    <tr data-search="<?= e(strtolower($a['name'] . ' ' . $a['fullName'] . ' ' . $country)) ?>">
                        <td><div class="team-name" style="color: var(--text)"><span class="swatch" style="--c: <?= e(team_color(str_replace('-', '_', $a['id']), $a['name'])) ?>"></span><span><span class="fw-600"><?= e($a['name']) ?></span><?php if ($a['fullName'] !== $a['name']): ?> <span class="subtle text-xs hide-sm"><?= e($a['fullName']) ?></span><?php endif; ?></span></div></td>
                        <td class="hide-sm muted"><?= e($country) ?></td>
                        <td class="num"><?= (int)$a['totalRaceStarts'] ?></td>
                        <td class="num fw-600"><?= (int)$a['totalRaceWins'] ?: '<span class="subtle">0</span>' ?></td>
                        <td class="num hide-sm"><?= (int)$a['totalPodiums'] ?></td>
                        <td class="num hide-sm"><?= (int)$a['totalPolePositions'] ?></td>
                        <td class="num"><?= (int)$a['totalChampionshipWins'] ? '<span class="badge badge-red">' . (int)$a['totalChampionshipWins'] . '</span>' : '<span class="subtle">–</span>' ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <div class="empty" id="archive-empty" hidden><h3>No constructors found</h3><p>Try a different name or country.</p></div>
        </div>
        <p class="text-xs subtle mt-2">Archive statistics come from a historic snapshot of the F1DB dataset and may not include the most recent races.</p>
    </section>
    <?php endif; ?>
</div>
<?php require INC_DIR . '/layout/footer.php'; ?>

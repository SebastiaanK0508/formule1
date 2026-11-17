<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$year = in_array(current_season(), db_season_years(), true) ? current_season() : (db_season_years()[0] ?? current_season());
$races = db_calendar($year);
$drivers = db_all("SELECT driver_id, first_name, last_name FROM drivers WHERE is_active = 1 AND first_name <> '' ORDER BY last_name");
$teams = db_all('SELECT team_id, team_name FROM teams WHERE is_active = 1 ORDER BY team_name');

$page = ['title' => 'Sitemap', 'description' => 'An overview of every section of F1SITE.NL.'];
require INC_DIR . '/layout/header.php';
?>
<div class="container">
    <header class="page-head">
        <h1 class="page-title">Sitemap</h1>
    </header>
    <section class="section">
        <div class="grid-cards">
            <div class="card card-pad">
                <h2 class="text-lg mb-4">Pages</h2>
                <ul class="stack" style="gap: 8px; list-style: none; padding: 0; margin: 0">
                    <?php foreach (['' => 'Home', 'kalender.php' => 'Race calendar', 'results.php' => 'Results', 'standings.php' => 'Standings', 'stats.php' => 'Season stats', 'quiz.php' => 'Daily quiz', 'drivers.php' => 'Drivers', 'teams.php' => 'Teams', 'compare.php' => 'Compare drivers', 'nieuws.php' => 'News', 'zoek.php' => 'Search', 'contact.php' => 'Contact', 'privacy-en.php' => 'Privacy policy', 'cookiebeleid.php' => 'Cookie policy', 'algemenevoorwaarden-en.php' => 'Terms of use'] as $href => $label): ?>
                        <li><a href="<?= e(url($href)) ?>"><?= e($label) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="card card-pad">
                <h2 class="text-lg mb-4"><?= e($year) ?> Grands Prix</h2>
                <ul class="stack" style="gap: 8px; list-style: none; padding: 0; margin: 0">
                    <?php foreach ($races as $r): ?><li><a href="<?= e(url('circuit-details.php', ['key' => $r['circuit_key'], 'year' => $year])) ?>"><?= e($r['grandprix']) ?></a></li><?php endforeach; ?>
                </ul>
            </div>
            <div class="card card-pad">
                <h2 class="text-lg mb-4">Drivers</h2>
                <ul class="stack" style="gap: 8px; list-style: none; padding: 0; margin: 0">
                    <?php foreach ($drivers as $d): ?><li><a href="<?= e(driver_url($d)) ?>"><?= e($d['first_name'] . ' ' . $d['last_name']) ?></a></li><?php endforeach; ?>
                </ul>
            </div>
            <div class="card card-pad">
                <h2 class="text-lg mb-4">Teams</h2>
                <ul class="stack" style="gap: 8px; list-style: none; padding: 0; margin: 0">
                    <?php foreach ($teams as $t): ?><li><a href="<?= e(team_url($t)) ?>"><?= e($t['team_name']) ?></a></li><?php endforeach; ?>
                </ul>
            </div>
        </div>
    </section>
</div>
<?php require INC_DIR . '/layout/footer.php'; ?>

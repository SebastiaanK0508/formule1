<?php
/**
 * XML sitemap for search engines, served as /sitemap.xml via .htaccess.
 */
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$urls = [];
$add = static function (string $loc, string $freq, string $priority) use (&$urls): void {
    $urls[] = compact('loc', 'freq', 'priority');
};

$add(url(), 'hourly', '1.0');
foreach (['kalender.php' => 'daily', 'results.php' => 'daily', 'standings.php' => 'daily', 'stats.php' => 'daily', 'quiz.php' => 'daily', 'drivers.php' => 'weekly', 'teams.php' => 'weekly', 'compare.php' => 'weekly', 'nieuws.php' => 'hourly'] as $p => $f) {
    $add(url($p), $f, '0.8');
}
foreach (['contact.php', 'sitemap.php', 'privacy-en.php', 'cookiebeleid.php', 'algemenevoorwaarden-en.php'] as $p) {
    $add(url($p), 'yearly', '0.2');
}
foreach (db_all("SELECT first_name, last_name FROM drivers WHERE is_active = 1 AND first_name <> ''") as $d) {
    $add(driver_url($d), 'weekly', '0.7');
}
foreach (db_all('SELECT team_id FROM teams WHERE is_active = 1') as $t) {
    $add(team_url($t), 'weekly', '0.7');
}
foreach (db_all('SELECT circuit_key, race_year FROM circuits WHERE race_year >= ? AND race_datetime IS NOT NULL', [current_season() - 1]) as $c) {
    $add(url('circuit-details.php', ['key' => $c['circuit_key'], 'year' => $c['race_year']]), 'weekly', '0.6');
}
foreach (f1_schedule(current_season()) as $race) {
    if ($race['datetime'] && $race['datetime'] < new DateTimeImmutable()) {
        $add(race_url(current_season(), $race['round']), 'monthly', '0.6');
    }
}
for ($y = current_season() - 1; $y >= current_season() - 10; $y--) {
    $add(url('results.php', ['year' => $y]), 'yearly', '0.4');
    $add(url('standings.php', ['year' => $y]), 'yearly', '0.4');
}

header('Content-Type: application/xml; charset=utf-8');
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($urls as $u) {
    echo '  <url><loc>' . htmlspecialchars($u['loc'], ENT_XML1) . '</loc><changefreq>' . $u['freq'] . '</changefreq><priority>' . $u['priority'] . "</priority></url>\n";
}
echo "</urlset>\n";

<?php
/**
 * Details for the drivers/teams a visitor follows (ids are stored in their browser).
 * GET api/favorites.php?drivers=1,6&teams=4
 */
declare(strict_types=1);
require dirname(__DIR__) . '/includes/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

$ids = static fn(string $key): array => array_slice(array_values(array_unique(array_filter(
    array_map('intval', explode(',', (string)($_GET[$key] ?? '')))
))), 0, 30);

$season = latest_results_season();
$driverStanding = $teamStanding = [];
foreach (f1_driver_standings($season)['rows'] as $row) {
    if ($d = driver_for_api($row)) {
        $driverStanding[(int)$d['driver_id']] = $row;
    }
}
foreach (f1_constructor_standings($season)['rows'] as $row) {
    if ($row['team']) {
        $teamStanding[(int)$row['team']['team_id']] = $row;
    }
}

$out = ['drivers' => [], 'teams' => []];
foreach ($ids('drivers') as $id) {
    $d = db_drivers()[$id] ?? null;
    if ($d) {
        $s = $driverStanding[$id] ?? null;
        $out['drivers'][] = [
            'name'     => $d['first_name'] . ' ' . $d['last_name'],
            'url'      => driver_url($d),
            'image'    => $d['image'],
            'color'    => safe_color($d['team_color'], '#e10600'),
            'position' => $s['position'] ?? null,
            'points'   => $s ? fmt_number($s['points']) : null,
        ];
    }
}
foreach ($ids('teams') as $id) {
    $t = db_teams()[$id] ?? null;
    if ($t) {
        $s = $teamStanding[$id] ?? null;
        $out['teams'][] = [
            'name'     => $t['team_name'],
            'url'      => team_url($t),
            'image'    => null,
            'color'    => safe_color($t['team_color']),
            'position' => $s['position'] ?? null,
            'points'   => $s ? fmt_number($s['points']) : null,
        ];
    }
}
echo json_encode($out);

<?php
/** Lap-by-lap positions for the race lap chart. GET api/laps.php?year=2026&round=19 */
declare(strict_types=1);
require dirname(__DIR__) . '/includes/bootstrap.php';
set_time_limit(60);

header('Content-Type: application/json; charset=utf-8');
$year = int_param('year', current_season(), 1996, current_season());
$round = int_param('round', 1, 1, 30);
$data = f1_lap_positions($year, $round);
header('Cache-Control: public, max-age=' . ($data['laps'] && $data['complete'] ? 86400 : 30));
echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

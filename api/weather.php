<?php
/**
 * Race-day weather forecast (Open-Meteo) for a calendar round.
 * GET api/weather.php?year=2026&round=20
 */
declare(strict_types=1);
require dirname(__DIR__) . '/includes/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: public, max-age=1800');

$year = int_param('year', current_season(), FIRST_SEASON, current_season() + 1);
$round = int_param('round', 0, 0, 40);
$row = db_one('SELECT * FROM circuits WHERE race_year = ? AND calendar_order = ?', [$year, $round]);
$raceAt = $row ? db_datetime($row['race_datetime']) : null;
if (!$row || !$raceAt) {
    echo json_encode(['status' => 'unavailable']);
    exit;
}

$daysUntil = (int)floor(($raceAt->getTimestamp() - time()) / 86400);
if ($daysUntil < 0 || $daysUntil > 15) {
    echo json_encode(['status' => $daysUntil < 0 ? 'past' : 'too_far', 'days_until' => $daysUntil]);
    exit;
}

// Coordinates: from the API schedule, otherwise geocode the location name.
$apiRace = api_race_for_calendar($row);
$lat = $apiRace['lat'] ?? null;
$lon = $apiRace['long'] ?? null;
if ($lat === null) {
    $city = trim(explode(',', (string)$row['location'])[0]);
    $geo = http_json('https://geocoding-api.open-meteo.com/v1/search?count=1&name=' . urlencode($city), 30 * 86400, 'geocode_' . slugify($city));
    $lat = $geo['results'][0]['latitude'] ?? null;
    $lon = $geo['results'][0]['longitude'] ?? null;
}
if ($lat === null) {
    echo json_encode(['status' => 'unavailable']);
    exit;
}

$date = $raceAt->format('Y-m-d');
$weather = http_json(
    sprintf(
        'https://api.open-meteo.com/v1/forecast?latitude=%F&longitude=%F&daily=temperature_2m_max,temperature_2m_min,precipitation_probability_max,weathercode&timezone=auto&start_date=%s&end_date=%s',
        $lat, $lon, $date, $date
    ),
    3600,
    'weather_' . $row['circuit_key'] . '_' . $date
);
$daily = $weather['daily'] ?? null;
if (!$daily || !isset($daily['temperature_2m_max'][0])) {
    echo json_encode(['status' => 'unavailable']);
    exit;
}

echo json_encode([
    'status'       => 'success',
    'date'         => $date,
    'temp_max'     => (int)round((float)$daily['temperature_2m_max'][0]),
    'temp_min'     => (int)round((float)$daily['temperature_2m_min'][0]),
    'rain_chance'  => (int)($daily['precipitation_probability_max'][0] ?? 0),
    'weather_code' => (int)($daily['weathercode'][0] ?? 0),
]);

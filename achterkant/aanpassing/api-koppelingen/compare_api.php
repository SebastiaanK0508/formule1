<?php
require_once __DIR__ . '/../../../db_config.php';
/** @var PDO $pdo */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

function fetchCompareData($url, $cacheName) {
    $cacheDir = __DIR__ . '/../../../cache';
    if (!is_dir($cacheDir)) mkdir($cacheDir, 0777, true);
    $cacheFile = $cacheDir . '/' . $cacheName . '.json';

    if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < 3600)) {
        return json_decode(file_get_contents($cacheFile), true);
    }
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 6);
    curl_setopt($ch, CURLOPT_USERAGENT, 'F1Site-Bot/1.0');
    $json = curl_exec($ch);
    if ($json) {
        file_put_contents($cacheFile, $json);
        return json_decode($json, true);
    }
    return null;
}

$id1 = isset($_GET['d1']) ? (int)$_GET['d1'] : 0;
$id2 = isset($_GET['d2']) ? (int)$_GET['d2'] : 0;
$year = date('Y');

function buildDriverPayload(PDO $pdo, int $driverId, string $year) {
    $stmt = $pdo->prepare("SELECT d.*, t.team_color, t.team_name FROM drivers d LEFT JOIN teams t ON d.team_id = t.team_id WHERE d.driver_id = :id");
    $stmt->execute(['id' => $driverId]);
    $driver = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$driver) return null;

    $age = null;
    if (!empty($driver['date_of_birth'])) {
        $age = (new DateTime())->diff(new DateTime($driver['date_of_birth']))->y;
    }

    $seasonStats = ['position' => null, 'points' => null, 'wins' => 0, 'podiums' => 0];
    $standings = fetchCompareData("https://api.jolpi.ca/ergast/f1/{$year}/driverStandings.json", "current_standings_{$year}");
    $fullName = strtolower(trim($driver['first_name'] . ' ' . $driver['last_name']));
    $surname = strtolower(trim($driver['last_name']));
    if (isset($standings['MRData']['StandingsTable']['StandingsLists'][0]['DriverStandings'])) {
        foreach ($standings['MRData']['StandingsTable']['StandingsLists'][0]['DriverStandings'] as $s) {
            $name = strtolower(trim($s['Driver']['givenName'] . ' ' . $s['Driver']['familyName']));
            // Fall back to surname match for nickname cases (e.g. our "Kimi Antonelli"
            // vs. the API's official "Andrea Kimi Antonelli").
            if ($name === $fullName || strtolower(trim($s['Driver']['familyName'])) === $surname) {
                $seasonStats['position'] = $s['position'];
                $seasonStats['points'] = $s['points'];
                $seasonStats['wins'] = $s['wins'];
                break;
            }
        }
    }

    return [
        'id' => $driver['driver_id'],
        'name' => $driver['first_name'] . ' ' . $driver['last_name'],
        'nationality' => $driver['nationality'],
        'flag_url' => $driver['flag_url'],
        'image' => $driver['image'] ?: $driver['image_url'],
        'number' => $driver['driver_number'],
        'age' => $age,
        'team_name' => $driver['team_name'],
        'team_color' => $driver['team_color'] ?: '#E10600',
        'championships_won' => (int)$driver['championships_won'],
        'career_points' => (float)$driver['career_points'],
        'season' => $seasonStats,
    ];
}

$result = [
    'status' => 'success',
    'driver1' => buildDriverPayload($pdo, $id1, $year),
    'driver2' => buildDriverPayload($pdo, $id2, $year),
];

echo json_encode($result);

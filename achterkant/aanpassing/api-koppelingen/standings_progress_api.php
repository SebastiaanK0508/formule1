<?php
require_once __DIR__ . '/../../../db_config.php';
/** @var PDO $pdo */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

function fetchProgressData($url, $cacheName) {
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
    curl_setopt($ch, CURLOPT_TIMEOUT, 8);
    curl_setopt($ch, CURLOPT_USERAGENT, 'F1Site-Bot/1.0');
    $json = curl_exec($ch);

    if ($json) {
        file_put_contents($cacheFile, $json);
        return json_decode($json, true);
    }
    return null;
}

$year = date('Y');
$result = ['status' => 'success', 'rounds' => [], 'drivers' => []];

try {
    $teamColors = [];
    $stmt = $pdo->query("SELECT team_name, team_color FROM teams");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $teamColors[$row['team_name']] = $row['team_color'];
    }

    $currentStandings = fetchProgressData(
        "https://api.jolpi.ca/ergast/f1/{$year}/driverStandings.json",
        "current_standings_{$year}"
    );
    $topDrivers = [];
    if (isset($currentStandings['MRData']['StandingsTable']['StandingsLists'][0]['DriverStandings'])) {
        $list = $currentStandings['MRData']['StandingsTable']['StandingsLists'][0]['DriverStandings'];
        foreach (array_slice($list, 0, 6) as $d) {
            $topDrivers[$d['Driver']['driverId']] = [
                'name' => $d['Driver']['code'] ?? $d['Driver']['familyName'],
                'team_color' => $teamColors[$d['Constructors'][0]['name']] ?? '#E10600',
                'points' => [],
            ];
        }
    }
    $totalRounds = (int)($currentStandings['MRData']['StandingsTable']['StandingsLists'][0]['round'] ?? 0);

    if ($totalRounds > 0 && !empty($topDrivers)) {
        for ($round = 1; $round <= $totalRounds; $round++) {
            $roundData = fetchProgressData(
                "https://api.jolpi.ca/ergast/f1/{$year}/{$round}/driverStandings.json",
                "round_standings_{$year}_{$round}"
            );
            $result['rounds'][] = "R{$round}";
            $roundList = $roundData['MRData']['StandingsTable']['StandingsLists'][0]['DriverStandings'] ?? [];
            $seen = [];
            foreach ($roundList as $d) {
                $id = $d['Driver']['driverId'];
                if (isset($topDrivers[$id])) {
                    $topDrivers[$id]['points'][] = (float)$d['points'];
                    $seen[$id] = true;
                }
            }
            foreach ($topDrivers as $id => &$info) {
                if (!isset($seen[$id])) {
                    $prev = end($info['points']);
                    $info['points'][] = ($prev !== false) ? $prev : 0;
                }
            }
            unset($info);
        }
    }

    $result['drivers'] = array_values($topDrivers);
} catch (PDOException $e) {
    $result = ['status' => 'error', 'message' => 'DB error'];
}

echo json_encode($result);

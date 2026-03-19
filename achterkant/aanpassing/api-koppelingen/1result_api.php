<?php
require_once 'db_config.php';
/** @var PDO $pdo */

$current_year = date('Y');
$selected_round = isset($_GET['round']) ? (int)$_GET['round'] : null;
$races_in_season = [];
$race_details = null;
$race_results = [];
$qualifying_results = [];
$sprint_results = [];
$sprint_quali_results = [];
$fp1_results = [];
$fp2_results = [];
$fp3_results = [];
$team_colors_from_db = [];
$error_message = '';

try {
    $stmt = $pdo->query("SELECT team_name, team_color FROM teams");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $team_colors_from_db[$row['team_name']] = $row['team_color'];
    }
    $stmt = $pdo->prepare("SELECT circuit_key, title, grandprix, race_datetime, calendar_order FROM circuits WHERE YEAR(race_datetime) = :year ORDER BY calendar_order ASC");
    $stmt->execute([':year' => $current_year]);
    $db_races = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($db_races as $race) {
        $races_in_season[] = [
            'round' => $race['calendar_order'],
            'raceName' => $race['grandprix'],
            'date' => $race['race_datetime']
        ];
    }
    if ($selected_round === null && !empty($races_in_season)) {
        $now = new DateTime();
        $latest = 1;
        foreach ($races_in_season as $r) {
            if (new DateTime($r['date']) < $now) $latest = $r['round'];
        }
        $selected_round = $latest;
    }
} catch (PDOException $e) {
    error_log("DB Error: " . $e->getMessage());
}
function fetchF1Data($url, $cacheName) {
    $cacheFile = "cache/" . $cacheName . ".json";
    if (!is_dir('cache')) mkdir('cache', 0777, true);

    if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < 3600)) {
        return json_decode(file_get_contents($cacheFile), true);
    }

    $json = @file_get_contents($url);
    if ($json) {
        file_put_contents($cacheFile, $json);
        return json_decode($json, true);
    }
    return null;
}
if ($selected_round !== null) {
    $baseUrl = "http://api.jolpi.ca/ergast/f1/{$current_year}/{$selected_round}/";
    $data = fetchF1Data($baseUrl . "results.json", "race_{$current_year}_{$selected_round}");
    if (isset($data['MRData']['RaceTable']['Races'][0])) {
        $race = $data['MRData']['RaceTable']['Races'][0];
        $race_details = [
            'name' => $race['raceName'],
            'circuit' => $race['Circuit']['circuitName'],
            'date' => $race['date']
        ];
        foreach ($race['Results'] as $res) {
            $team = $res['Constructor']['name'];
            $race_results[] = [
                'position' => $res['position'],
                'driver_name' => $res['Driver']['givenName'] . ' ' . $res['Driver']['familyName'],
                'team_name' => $team,
                'team_color' => $team_colors_from_db[$team] ?? '#E10600',
                'lap_time_or_status' => $res['Time']['time'] ?? $res['status']
            ];
        }
    }
    $data = fetchF1Data($baseUrl . "qualifying.json", "qual_{$current_year}_{$selected_round}");
    if (isset($data['MRData']['RaceTable']['Races'][0]['QualifyingResults'])) {
        foreach ($data['MRData']['RaceTable']['Races'][0]['QualifyingResults'] as $q) {
            $team = $q['Constructor']['name'];
            $qualifying_results[] = [
                'position' => $q['position'],
                'driver' => $q['Driver']['familyName'],
                'team_name' => $team,
                'team_color' => $team_colors_from_db[$team] ?? '#E10600',
                'q1' => $q['Q1'] ?? '-', 'q2' => $q['Q2'] ?? '-', 'q3' => $q['Q3'] ?? '-'
            ];
        }
    }
    $data = fetchF1Data($baseUrl . "sprint.json", "sprint_{$current_year}_{$selected_round}");
    if (isset($data['MRData']['RaceTable']['Races'][0]['SprintResults'])) {
        foreach ($data['MRData']['RaceTable']['Races'][0]['SprintResults'] as $s) {
            $team = $s['Constructor']['name'];
            $sprint_results[] = [
                'position' => $s['position'],
                'driver_name' => $s['Driver']['familyName'],
                'team_color' => $team_colors_from_db[$team] ?? '#E10600',
                'lap_time_or_status' => $s['Time']['time'] ?? $s['status']
            ];
        }
    }

    
    try {
        $stmt = $pdo->prepare("SELECT * FROM f1_sessie_results WHERE circuit_id = :cid AND session_type = 'FP1' ORDER BY position ASC");
    } catch (PDOException $e) { }
}
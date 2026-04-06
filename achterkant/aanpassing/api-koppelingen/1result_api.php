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
$fp1_results = [];
$fp2_results = [];
$fp3_results = [];
$team_colors_from_db = [];
$error_message = '';

try {
    // 1. Teamkleuren ophalen
    $stmt = $pdo->query("SELECT team_name, team_color FROM teams");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $team_colors_from_db[$row['team_name']] = $row['team_color'];
    }

    // 2. Kalender ophalen
    $stmt = $pdo->prepare("SELECT circuit_key, title, grandprix, race_datetime, calendar_order FROM circuits WHERE YEAR(race_datetime) = :year ORDER BY calendar_order ASC");
    $stmt->execute([':year' => $current_year]);
    $db_races = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $now = new DateTime();
    $latest_completed_round = null;
    $selected_race_db = null;

    foreach ($db_races as $race) {
        $race_date = new DateTime($race['race_datetime']);
        $races_in_season[] = [
            'round' => $race['calendar_order'],
            'raceName' => $race['grandprix'],
            'date' => $race['race_datetime'],
            'circuit_key' => $race['circuit_key']
        ];
        
        if ($race_date < $now) {
            $latest_completed_round = $race['calendar_order'];
        }
    }

    // Bepaal welke ronde we tonen
    if ($selected_round === null) {
        $selected_round = $latest_completed_round ?? 1;
    }

    // Zoek de database-details voor de geselecteerde ronde
    foreach ($db_races as $race) {
        if ((int)$race['calendar_order'] === $selected_round) {
            $selected_race_db = $race;
            break;
        }
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

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5); 
    curl_setopt($ch, CURLOPT_USERAGENT, 'F1Site-Bot/1.0');

    $json = curl_exec($ch);
    // curl_close($ch); // Deprecated in PHP 8.5

    if ($json) {
        file_put_contents($cacheFile, $json);
        return json_decode($json, true);
    }
    return null;
}

if ($selected_round !== null) {
    // API URL hersteld (geen dubbele results meer)
    $baseUrl = "https://api.jolpi.ca/ergast/f1/{$current_year}/{$selected_round}/";

    // Race resultaten
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

    // Kwalificatie
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

    // Sprint
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

    // Vrije trainingen uit eigen DB (FP1, FP2, FP3)
    if ($selected_race_db) {
        $cid = $selected_race_db['circuit_key'];
        $sessions = ['FP1' => &$fp1_results, 'FP2' => &$fp2_results, 'FP3' => &$fp3_results];
        
        foreach ($sessions as $type => &$result_array) {
            try {
                $stmt = $pdo->prepare("SELECT * FROM f1_sessie_results WHERE circuit_id = :cid AND session_type = :type ORDER BY position ASC");
                $stmt->execute([':cid' => $cid, ':type' => $type]);
                $result_array = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) { 
                error_log("Session error: " . $e->getMessage());
            }
        }
    }

    // Fallback voor race_details als API faalt
    if ($race_details === null && $selected_race_db) {
        $race_details = [
            'name' => $selected_race_db['grandprix'],
            'circuit' => $selected_race_db['title'],
            'date' => $selected_race_db['race_datetime']
        ];
    }
}
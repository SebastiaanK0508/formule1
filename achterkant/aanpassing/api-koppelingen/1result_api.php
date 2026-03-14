<?php
require_once 'db_config.php';
/** @var PDO $pdo */

// 1. Basis instellingen
$current_year = date('Y'); // 2026
$selected_round = isset($_GET['round']) ? (int)$_GET['round'] : (isset($_GET['calendar_order']) ? (int)$_GET['calendar_order'] : 1);

$races_in_season = [];
$race_details = null;
$race_results = [];
$qualifying_results = [];
$error_message = '';
$team_colors_from_db = [];
$nextGrandPrix = null;
$targetDateTime = null;

try {
    $stmt = $pdo->query("SELECT team_name, team_color FROM teams");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $clean_db_name = strtolower(trim($row['team_name']));
        $team_colors_from_db[$clean_db_name] = $row['team_color'];
    }

    $stmt = $pdo->prepare("
        SELECT circuit_key, title, grandprix, location, race_datetime, calendar_order
        FROM circuits
        WHERE race_datetime IS NOT NULL AND YEAR(race_datetime) = :current_year
        ORDER BY calendar_order ASC
    ");
    $stmt->bindParam(':current_year', $current_year, PDO::PARAM_INT);
    $stmt->execute();
    $db_races = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($db_races)) {
        foreach ($db_races as $race) {
            $races_in_season[] = [
                'round' => (int)$race['calendar_order'],
                'raceName' => $race['grandprix'],
                'date' => $race['race_datetime'],
                'circuit_key' => $race['circuit_key']
            ];
        }
    }

    // 4. Volgende Grand Prix ophalen voor de countdown
    $stmt = $pdo->prepare("
        SELECT grandprix, race_datetime, title, location
        FROM circuits
        WHERE race_datetime > NOW() AND YEAR(race_datetime) = :current_year
        ORDER BY race_datetime ASC
        LIMIT 1
    ");
    $stmt->bindParam(':current_year', $current_year, PDO::PARAM_INT);
    $stmt->execute();
    $next_race_data = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($next_race_data) {
        $nextGrandPrix = [
            'grandprix' => $next_race_data['grandprix'],
            'title'     => $next_race_data['title'],
            'location'  => $next_race_data['location'], 
            'circuit'   => $next_race_data['title']     
        ];
        $targetDateTime = $next_race_data['race_datetime'];
    }

} catch (\PDOException $e) {
    error_log("Database fout: " . $e->getMessage());
}

// 5. RACE RESULTS OPHALEN
try {
    if ($selected_round !== null) {
        $race_results_url = "https://api.jolpi.ca/ergast/f1/{$current_year}/{$selected_round}/results.json";
        $json_data_results = @file_get_contents($race_results_url);

        if ($json_data_results) {
            $results_data = json_decode($json_data_results, true);
            if (isset($results_data['MRData']['RaceTable']['Races'][0])) {
                $current_race_api_data = $results_data['MRData']['RaceTable']['Races'][0];
                $race_details = [
                    'name' => $current_race_api_data['raceName'],
                    'circuit' => $current_race_api_data['Circuit']['circuitName'],
                    'date' => $current_race_api_data['date']
                ];

                foreach ($current_race_api_data['Results'] as $res) {
                    $team_api_name = $res['Constructor']['name'];
                    $clean_team_api = strtolower(trim($team_api_name));
                    
                    $race_results[] = [
                        'position' => $res['position'],
                        'driver_name' => $res['Driver']['givenName'] . ' ' . $res['Driver']['familyName'],
                        'team_name' => $team_api_name,
                        'lap_time_or_status' => $res['Time']['time'] ?? $res['status'],
                        'team_color' => $team_colors_from_db[$clean_team_api] ?? '#CCCCCC'
                    ];
                }
            }
        }
    }
} catch (Exception $e) {
    error_log("Race API fout: " . $e->getMessage());
}

// 6. KWALIFICATIE OPHALEN (Gecorrigeerd volgens jouw URL-voorbeeld)
try {
    if ($selected_round !== null) {
        $cache_file_qual = "cache_qualifying_{$current_year}_{$selected_round}.json";
        $json_data_qual = null;

        // Controleer of cache bestaat en jonger is dan 1 uur
        if (file_exists($cache_file_qual) && (time() - filemtime($cache_file_qual) < 3600)) {
            $json_data_qual = file_get_contents($cache_file_qual);
        } else {
            // Jouw werkende URL structuur:
            $qual_url = "https://api.jolpi.ca/ergast/f1/{$current_year}/{$selected_round}/qualifying.json";
            $json_data_qual = @file_get_contents($qual_url);

            if ($json_data_qual) {
                $check_data = json_decode($json_data_qual, true);
                // Sla de cache alleen op als er echt resultaten zijn gevonden
                if (!empty($check_data['MRData']['RaceTable']['Races'][0]['QualifyingResults'])) {
                    file_put_contents($cache_file_qual, $json_data_qual);
                } else {
                    // API is bereikbaar maar data is leeg (sessie nog niet gereden), dus niet cachen
                    $json_data_qual = null;
                    if (file_exists($cache_file_qual)) unlink($cache_file_qual);
                }
            }
        }

        if ($json_data_qual) {
            $qual_data = json_decode($json_data_qual, true);
            if (isset($qual_data['MRData']['RaceTable']['Races'][0]['QualifyingResults'])) {
                foreach ($qual_data['MRData']['RaceTable']['Races'][0]['QualifyingResults'] as $q_res) {
                    $team_api_name = $q_res['Constructor']['name'];
                    $clean_team_api = strtolower(trim($team_api_name));
                    
                    $qualifying_results[] = [
                        'position' => $q_res['position'],
                        'driver' => $q_res['Driver']['familyName'],
                        'team_color' => $team_colors_from_db[$clean_team_api] ?? '#E10600', // Rood als fallback
                        'q1' => $q_res['Q1'] ?? '-',
                        'q2' => $q_res['Q2'] ?? '-',
                        'q3' => $q_res['Q3'] ?? '-'
                    ];
                }
            }
        }
    }
} catch (Exception $e) {
    error_log("Qualifying API fout: " . $e->getMessage());
}
?>
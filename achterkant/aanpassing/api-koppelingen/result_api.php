<?php

require_once 'db_config.php';
/** @var PDO $pdo */ 

$selected_year = isset($_GET['year']) ? (int)$_GET['year'] : null;
$selected_round = isset($_GET['round']) ? (int)$_GET['round'] : null;

$races_in_season = [];
$race_details = null;
$race_results = [];
$error_message = '';
$team_colors_from_db = [];
$available_years = [];

// 1. Beschikbare jaren ophalen
try {
    $stmt = $pdo->query("SELECT DISTINCT YEAR(race_datetime) AS race_year FROM circuits WHERE race_datetime IS NOT NULL ORDER BY race_year DESC");
    $available_years = $stmt->fetchAll(PDO::FETCH_COLUMN);
    if(empty($available_years)) {
        $available_years[] = date('Y');
    }
} catch (\PDOException $e) {
    error_log("Fout bij het ophalen van beschikbare jaren: " . $e->getMessage());
    $error_message = "Er is een probleem opgetreden bij het laden van de beschikbare jaren.";
}

if ($selected_year === null) {
    $selected_year = !empty($available_years) ? $available_years[0] : date('Y');
}

// 2. Teamkleuren ophalen
try {
    $stmt = $pdo->query("SELECT team_name, team_color FROM teams");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $team_colors_from_db[$row['team_name']] = $row['team_color'];
    }
} catch (\PDOException $e) {
    error_log("Fout teamkleuren: " . $e->getMessage());
}

// 3. Kalender ophalen en huidige ronde bepalen
try {
    $stmt = $pdo->prepare("
        SELECT circuit_key, title, grandprix, location, race_datetime, calendar_order
        FROM circuits
        WHERE race_datetime IS NOT NULL AND YEAR(race_datetime) = :selected_year
        ORDER BY calendar_order ASC
    ");
    $stmt->bindParam(':selected_year', $selected_year, PDO::PARAM_INT);
    $stmt->execute();
    $db_races = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($db_races)) {
        $current_date_time = new DateTime();
        $latest_completed_round = 0;

        foreach ($db_races as $race) {
            $race_date_str = $race['race_datetime'] ?? null;
            
            $races_in_season[] = [
                'round' => $race['calendar_order'], 
                'raceName' => $race['grandprix'],
                'date' => $race_date_str,
                'circuit_key' => $race['circuit_key'] 
            ];
            
            // Bepaal de laatst gereden ronde voor de automatische selectie
            if ($race_date_str) {
                try {
                    $race_dt = new DateTime($race_date_str);
                    if ($race_dt < $current_date_time) {
                        $latest_completed_round = (int)$race['calendar_order'];
                    }
                } catch (Exception $e) {
                    error_log("Datum fout bij ronde " . $race['calendar_order']);
                }
            }
        }

        if ($selected_round === null) {
            $selected_round = ($latest_completed_round > 0) ? $latest_completed_round : 1;
        }
    }
} catch (\PDOException $e) {
    $error_message = "Fout bij laden kalender: " . $e->getMessage();
}

// 4. RESULTATEN OPHALEN
try {
    if ($selected_round !== null && !empty($db_races)) {
        $selected_race_db = null;
        foreach ($db_races as $race) {
            if ((int)$race['calendar_order'] === $selected_round) {
                $selected_race_db = $race;
                break;
            }
        }

        if ($selected_race_db) {
            $race_date_str = $selected_race_db['race_datetime'];
            $race_date = new DateTime($race_date_str);
            $now = new DateTime();

            // API alleen aanroepen als de race in het verleden ligt
            if ($race_date < $now) {
                // Gebruik HTTPS en zorg voor een correcte URL-structuur
                $race_results_url = "https://api.jolpi.ca/ergast/f1/{$selected_year}/{$selected_round}/results.json";
                
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $race_results_url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 5); 
                curl_setopt($ch, CURLOPT_USERAGENT, 'F1Site-Bot/1.0');
                
                $json_data_results = curl_exec($ch);
                // curl_close($ch); // Deprecated in PHP 8.5+, PHP ruimt dit zelf op.
                
                if ($json_data_results) {
                    $results_data = json_decode($json_data_results, true);
                    if (isset($results_data['MRData']['RaceTable']['Races'][0])) {
                        $api_race = $results_data['MRData']['RaceTable']['Races'][0];
                        $race_details = [
                            'name' => $api_race['raceName'],
                            'circuit' => $api_race['Circuit']['circuitName'],
                            'location' => $api_race['Circuit']['Location']['locality'],
                            'country' => $api_race['Circuit']['Location']['country'],
                            'date' => $api_race['date'],
                            'year' => $api_race['season']
                        ];
                        
                        foreach ($api_race['Results'] as $driver_result) {
                            $team_name = $driver_result['Constructor']['name'];
                            $race_results[] = [
                                'position' => $driver_result['position'],
                                'driver_name' => $driver_result['Driver']['givenName'] . ' ' . $driver_result['Driver']['familyName'],
                                'team_name' => $team_name,
                                'lap_time_or_status' => $driver_result['Time']['time'] ?? $driver_result['status'],
                                'team_color' => $team_colors_from_db[$team_name] ?? '#CCCCCC'
                            ];
                        }
                    }
                }
            }

            // Fallback: Als API faalt of race is nog niet geweest, gebruik eigen DB info
            if ($race_details === null) {
                $race_details = [
                    'name' => $selected_race_db['grandprix'],
                    'circuit' => $selected_race_db['title'],
                    'location' => $selected_race_db['location'],
                    'country' => 'TBD',
                    'date' => $selected_race_db['race_datetime'],
                    'year' => $selected_year
                ];
            }
        }
    }
} catch (Exception $e) {
    $error_message = "Systeemfout: " . $e->getMessage();
}
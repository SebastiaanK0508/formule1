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
    $selected_year = $available_years[0];
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

// 3. Kalender voor geselecteerd jaar ophalen uit EIGEN database
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
        foreach ($db_races as $race) {
            $races_in_season[] = [
                'round' => $race['calendar_order'], 
                'raceName' => $race['grandprix'],
                'date' => $race['race_datetime'],
                'circuit_key' => $race['circuit_key'] 
            ];
        }
        if ($selected_round === null) {
            $latest_completed_round = 0;
            $current_date_time = new DateTime();
            foreach ($races_in_season as $race) {
                if (new DateTime($race['date']) < $current_date_time) {
                    $latest_completed_round = (int)$race['round'];
                }
            }
            $selected_round = ($latest_completed_round > 0) ? $latest_completed_round : 1;
        }
    }
} catch (\PDOException $e) {
    $error_message = "Fout bij laden kalender.";
}

// 4. RESULTATEN OPHALEN (Alleen als de race al geweest is)
try {
    if ($selected_round !== null) {
        $selected_race_db = null;
        foreach ($db_races as $race) {
            if ((int)$race['calendar_order'] === $selected_round) {
                $selected_race_db = $race;
                break;
            }
        }

        if ($selected_race_db) {
            $race_date = new DateTime($selected_race_db['race_datetime']);
            $now = new DateTime();

            // BELANGRIJK: Alleen API aanroepen als de race in het verleden ligt
            if ($race_date < $now) {
                $race_results_url = 'http://api.jolpi.ca/ergast/f1/' . $selected_year . '/' . $selected_round . '/results.json';
                $json_data_results = @file_get_contents($race_results_url);
                
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
                            $race_results[] = [
                                'position' => $driver_result['position'],
                                'driver_name' => $driver_result['Driver']['givenName'] . ' ' . $driver_result['Driver']['familyName'],
                                'team_name' => $driver_result['Constructor']['name'],
                                'lap_time_or_status' => $driver_result['Time']['time'] ?? $driver_result['status'],
                                'team_color' => $team_colors_from_db[$driver_result['Constructor']['name']] ?? '#CCCCCC'
                            ];
                        }
                    }
                }
            }

            // Als er GEEN API resultaten zijn (omdat het 2026 is of race nog moet komen), 
            // vul dan de details vanuit je eigen database zodat de pagina niet leeg is.
            if (empty($race_results)) {
                $race_details = [
                    'name' => $selected_race_db['grandprix'],
                    'circuit' => $selected_race_db['title'],
                    'location' => $selected_race_db['location'],
                    'country' => 'TBD',
                    'date' => $selected_race_db['race_datetime'],
                    'year' => $selected_year
                ];
                // $error_message blijft leeg, de HTML zal nu zeggen "Geen uitslagen beschikbaar"
            }
        }
    }
} catch (Exception $e) {
    $error_message = "Fout: " . $e->getMessage();
}
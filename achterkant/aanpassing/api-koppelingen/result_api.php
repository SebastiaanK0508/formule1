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
try {
    $stmt = $pdo->query("SELECT team_name, team_color FROM teams");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $team_colors_from_db[$row['team_name']] = $row['team_color'];
    }
} catch (\PDOException $e) {
    error_log("Fout bij het ophalen van teamkleuren uit de database: " . $e->getMessage());
    if (empty($error_message)) {
        $error_message = "Er is een probleem opgetreden bij het laden van teamkleuren uit de database.";
    }
}
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
        foreach ($db_races as $index => $race) {
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
                $race_date_time = new DateTime($race['date']);
                if ($race_date_time < $current_date_time && (int)$race['round'] > $latest_completed_round) {
                    $latest_completed_round = (int)$race['round'];
                }
            }
            $selected_round = ($latest_completed_round > 0) ? $latest_completed_round : 1;
        }
    } else {
        $error_message = "Geen kalendergegevens gevonden voor seizoen " . $selected_year . " in de database.";
    }
} catch (\PDOException $e) {
    error_log("Fout bij het ophalen van kalenderdata uit de database: " . $e->getMessage());
    if (empty($error_message)) {
        $error_message = "Er is een probleem opgetreden bij het laden van de kalenderdata uit de database.";
    }
}
try {
    if ($selected_round !== null) {
        $current_circuit_key = null;
        foreach ($races_in_season as $race) {
            if ((int)$race['round'] === $selected_round) {
                $current_circuit_key = $race['circuit_key'];
                break;
            }
        }
        if ($current_circuit_key === null) {
             throw new Exception("Kon geen circuitinformatie vinden voor ronde " . $selected_round . ".");
        }
        $race_results_url = 'http://api.jolpi.ca/ergast/f1/' . $selected_year . '/' . $selected_round . '/results.json';
        $json_data_results = @file_get_contents($race_results_url);
        if ($json_data_results === false) {
            throw new Exception("Kon geen race-uitslagen ophalen voor ronde " . $selected_round . " van de Jolpica-f1 API.");
        }
        $results_data = json_decode($json_data_results, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Fout bij het decoderen van resultaten JSON: " . json_last_error_msg());
        }
        if (isset($results_data['MRData']['RaceTable']['Races']) && !empty($results_data['MRData']['RaceTable']['Races'])) {
            $current_race_api_data = $results_data['MRData']['RaceTable']['Races'][0];
            $race_details = [
                'name' => $current_race_api_data['raceName'] ?? 'Onbekende Race',
                'circuit' => $current_race_api_data['Circuit']['circuitName'] ?? 'Onbekend Circuit',
                'location' => $current_race_api_data['Circuit']['Location']['locality'] ?? 'N.v.t.',
                'country' => $current_race_api_data['Circuit']['Location']['country'] ?? 'N.v.t.',
                'date' => $current_race_api_data['date'] ?? 'Onbekende Datum',
                'year' => $current_race_api_data['season'] ?? $selected_year
            ];
            if (isset($current_race_api_data['Results']) && !empty($current_race_api_data['Results'])) {
                foreach ($current_race_api_data['Results'] as $driver_result) {
                    $driver_name = ($driver_result['Driver']['givenName'] ?? '') . ' ' . ($driver_result['Driver']['familyName'] ?? '');
                    $team_name_from_api = $driver_result['Constructor']['name'] ?? 'Onbekend Team';
                    $color_for_team = $team_colors_from_db[$team_name_from_api] ?? '#CCCCCC'; 
                    $lap_time_or_status = 'N.v.t.';
                    if (isset($driver_result['Time']['time'])) {
                        $lap_time_or_status = $driver_result['Time']['time'];
                    } elseif (isset($driver_result['status'])) {
                        $lap_time_or_status = $driver_result['status'];
                    }
                    $race_results[] = [
                        'position' => $driver_result['position'] ?? '-',
                        'driver_name' => $driver_name,
                        'team_name' => $team_name_from_api,
                        'lap_time_or_status' => $lap_time_or_status,
                        'team_color' => $color_for_team 
                    ];
                }
            } else {
                $error_message = "Geen coureurresultaten gevonden voor deze race.";
            }
        } else {
            $error_message = "No Data!";
        }
    }
} catch (Exception $e) {
    $error_message = "Er is een probleem opgetreden bij het ophalen van de race-uitslagen: " . $e->getMessage();
}
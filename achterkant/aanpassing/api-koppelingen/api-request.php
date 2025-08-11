<?php

// Gebruik __DIR__ om het absolute pad van de map te krijgen waar dit script zich bevindt.
// Voeg de submap 'json' toe aan dit pad, zodat de bestanden gevonden worden.
$base_dir = __DIR__ . '/json';

// Functie om een JSON-bestand in te laden en te controleren op fouten.
function loadJsonFile($filename, $base_dir) {
    $path = $base_dir . '/' . $filename;
    
    // Controleer of het bestand bestaat en of het leesbaar is.
    if (!is_readable($path)) {
        // Stop en geef een duidelijke foutmelding met het volledige pad.
        die("Fout: Bestand '$filename' niet gevonden of onleesbaar. Gezocht op: '$path'");
    }
    
    $json_data = file_get_contents($path);
    $decoded_data = json_decode($json_data, true);
    
    if ($decoded_data === null && json_last_error() !== JSON_ERROR_NONE) {
        die("Fout bij het decoderen van '$filename': " . json_last_error_msg());
    }
    
    return $decoded_data;
}

// --- START VAN DE CODE ---

try {
    // Laad alle benodigde bestanden in.
    $drivers_data = loadJsonFile('drivers.json', $base_dir);
    $teams_data = loadJsonFile('teams.json', $base_dir);
    $races_data = loadJsonFile('races.json', $base_dir);
    $results_data = loadJsonFile('results.json', $base_dir);
} catch (Exception $e) {
    die($e->getMessage());
}

// De rest van de code om de data te verwerken is hetzelfde als eerder
$selected_season = 2024;
$drivers_by_id = array_column($drivers_data['drivers'], null, 'id');
$teams_by_id = array_column($teams_data['teams'], null, 'id');
$races_by_id = array_column($races_data['races'], null, 'id');
$season_data = [];

foreach ($results_data['results'] as $result) {
    $race_id = $result['raceId'];
    if (isset($races_by_id[$race_id]) && $races_by_id[$race_id]['season']['year'] == $selected_season) {
        $driver_id = $result['driverId'];
        $team_id = $result['constructorId'];
        if (isset($drivers_by_id[$driver_id]) && isset($teams_by_id[$team_id])) {
            $driver_info = $drivers_by_id[$driver_id];
            $team_info = $teams_by_id[$team_id];
            $race_info = $races_by_id[$race_id];
            $full_name = $driver_info['firstName'] . ' ' . $driver_info['lastName'];
            $team_name = $team_info['name'];
            $race_name = $race_info['name'];

            if (!isset($season_data[$driver_id])) {
                $season_data[$driver_id] = [
                    'name' => $full_name,
                    'nationality' => $driver_info['nationality'],
                    'teams' => [],
                    'results' => []
                ];
            }
            
            if (!in_array($team_name, $season_data[$driver_id]['teams'])) {
                $season_data[$driver_id]['teams'][] = $team_name;
            }

            $season_data[$driver_id]['results'][] = [
                'race' => $race_name,
                'position' => $result['position'],
                'points' => $result['points']
            ];
        }
    }
}

echo "<h1>F1 Coureurs en teams in seizoen $selected_season</h1>";
echo "<pre>";
print_r($season_data);
echo "</pre>";
?>
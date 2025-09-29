<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

function fetchData($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if (curl_errno($ch)) {
        error_log("Curl error: " . curl_error($ch) . " for URL: " . $url);
        curl_close($ch);
        return ['error' => 'Curl Error', 'message' => curl_error($ch), 'http_code' => 0];
    }
    curl_close($ch);

    if ($httpCode >= 400) {
        error_log("HTTP error: " . $httpCode . " for URL: " . $url . " Response: " . $response);
        return ['error' => 'HTTP Error', 'message' => 'Fout bij het ophalen van data, HTTP status: ' . $httpCode, 'http_code' => $httpCode];
    }

    $data = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("JSON decode error: " . json_last_error_msg() . " Response: " . $response);
        return ['error' => 'JSON Error', 'message' => 'Fout bij het decoderen van JSON-respons', 'http_code' => $httpCode];
    }
    return $data;
}
$drivers_standings_url = "https://api.jolpi.ca/ergast/f1/current/driverStandings.json";
$constructors_standings_url = "https://api.jolpi.ca/ergast/f1/current/constructorStandings.json";
$all_standings = [
    'status' => 'success',
    'drivers' => [],
    'constructors' => [],
    'message' => ''
];

$drivers_data = fetchData($drivers_standings_url);
if (isset($drivers_data['error'])) {
    $all_standings['status'] = 'error';
    $all_standings['message'] .= 'Fout bij het ophalen van coureursklassement: ' . $drivers_data['message'] . '; ';
} else {
    if (isset($drivers_data['MRData']['StandingsTable']['StandingsLists'][0]['DriverStandings'])) {
        foreach ($drivers_data['MRData']['StandingsTable']['StandingsLists'][0]['DriverStandings'] as $driverStanding) {
            $all_standings['drivers'][] = [
                'position' => $driverStanding['position'],
                'points' => $driverStanding['points'],
                'wins' => $driverStanding['wins'],
                'driver_id' => $driverStanding['Driver']['driverId'],
                'given_name' => $driverStanding['Driver']['givenName'],
                'family_name' => $driverStanding['Driver']['familyName'],
                'nationality' => $driverStanding['Driver']['nationality'],
                'constructor_name' => $driverStanding['Constructors'][0]['name']
            ];
        }
    } else {
        $all_standings['status'] = 'error';
        $all_standings['message'] .= 'Geen coureursklassement gevonden in Jolpica respons (controleer of het seizoen al actief is of voltooid is). ';
    }
}

$constructors_data = fetchData($constructors_standings_url);
if (isset($constructors_data['error'])) {
    $all_standings['status'] = 'error';
    $all_standings['message'] .= 'Fout bij het ophalen van constructeursklassement: ' . $constructors_data['message'] . '; ';
} else {
    if (isset($constructors_data['MRData']['StandingsTable']['StandingsLists'][0]['ConstructorStandings'])) {
        foreach ($constructors_data['MRData']['StandingsTable']['StandingsLists'][0]['ConstructorStandings'] as $constructorStanding) {
            $all_standings['constructors'][] = [
                'position' => $constructorStanding['position'],
                'points' => $constructorStanding['points'],
                'wins' => $constructorStanding['wins'],
                'constructor_id' => $constructorStanding['Constructor']['constructorId'],
                'name' => $constructorStanding['Constructor']['name'],
                'nationality' => $constructorStanding['Constructor']['nationality']
            ];
        }
    } else {
        $all_standings['status'] = 'error';
        $all_standings['message'] .= 'Geen constructeursklassement gevonden in Jolpica respons (controleer of het seizoen al actief is of voltooid is). ';
    }
}

if (!empty($all_standings['message']) && $all_standings['status'] === 'success') {
    $all_standings['status'] = 'error';
}


echo json_encode($all_standings, JSON_PRETTY_PRINT);

?>
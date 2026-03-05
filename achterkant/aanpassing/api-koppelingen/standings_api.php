<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

function fetchData($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'F1Site-Standings-Bot/1.0');

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if (curl_errno($ch)) {
        $error_msg = curl_error($ch);
        return ['error' => 'Curl Error: ' . $error_msg];
    }

    if ($httpCode >= 400) {
        return ['error' => 'HTTP Error', 'http_code' => $httpCode];
    }

    return json_decode($response, true);
}
$year = 'current';

$drivers_standings_url = "https://api.jolpi.ca/ergast/f1/{$year}/driverStandings.json";
$constructors_standings_url = "https://api.jolpi.ca/ergast/f1/{$year}/constructorStandings.json";

$all_standings = [
    'status' => 'success',
    'drivers' => [],
    'constructors' => [],
    'message' => ''
];
$drivers_data = fetchData($drivers_standings_url);

if (!isset($drivers_data['error']) && isset($drivers_data['MRData']['StandingsTable']['StandingsLists'][0]['DriverStandings'])) {
    foreach ($drivers_data['MRData']['StandingsTable']['StandingsLists'][0]['DriverStandings'] as $driverStanding) {
        $all_standings['drivers'][] = [
            'position'         => $driverStanding['position'] ?? '0',
            'points'           => $driverStanding['points'] ?? '0',
            'wins'             => $driverStanding['wins'] ?? '0',
            'given_name'       => $driverStanding['Driver']['givenName'] ?? 'Onbekend',
            'family_name'      => $driverStanding['Driver']['familyName'] ?? '',
            'constructor_name' => $driverStanding['Constructors'][0]['name'] ?? 'Onbekend'
        ];
    }
}
$constructors_data = fetchData($constructors_standings_url);

if (!isset($constructors_data['error']) && isset($constructors_data['MRData']['StandingsTable']['StandingsLists'][0]['ConstructorStandings'])) {
    foreach ($constructors_data['MRData']['StandingsTable']['StandingsLists'][0]['ConstructorStandings'] as $constructorStanding) {
        $all_standings['constructors'][] = [
            'position' => $constructorStanding['position'] ?? '0',
            'points'   => $constructorStanding['points'] ?? '0',
            'name'     => $constructorStanding['Constructor']['name'] ?? 'Onbekend'
        ];
    }
}

if (empty($all_standings['drivers']) && empty($all_standings['constructors'])) {
    $all_standings['message'] = "Het klassement voor het seizoen " . date('Y') . " is nog niet beschikbaar.";
}
echo json_encode($all_standings, JSON_PRETTY_PRINT);
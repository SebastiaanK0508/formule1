<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

function fetchJson($url, $cacheFile, $ttl) {
    if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < $ttl)) {
        return json_decode(file_get_contents($cacheFile), true);
    }
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 6);
    curl_setopt($ch, CURLOPT_USERAGENT, 'F1Site-Bot/1.0');
    $json = curl_exec($ch);
    if ($json) {
        file_put_contents($cacheFile, $json);
        return json_decode($json, true);
    }
    return null;
}

$circuitKey = isset($_GET['circuit']) ? preg_replace('/[^a-z0-9_]/', '', $_GET['circuit']) : '';
$raceDate = isset($_GET['date']) ? preg_replace('/[^0-9\-]/', '', $_GET['date']) : '';
$locationName = isset($_GET['location']) ? trim($_GET['location']) : '';

$cacheDir = __DIR__ . '/../../../cache';
if (!is_dir($cacheDir)) mkdir($cacheDir, 0777, true);

$result = ['status' => 'unavailable'];

if ($circuitKey && $raceDate) {
    $circuitData = fetchJson(
        "https://api.jolpi.ca/ergast/f1/circuits/{$circuitKey}.json",
        "$cacheDir/circuit_geo_{$circuitKey}.json",
        30 * 86400
    );
    $loc = $circuitData['MRData']['CircuitTable']['Circuits'][0]['Location'] ?? null;

    // New or recently-added circuits (e.g. Madrid 2026) aren't in Ergast's circuit
    // reference table yet, so fall back to free geocoding by location name.
    if (!$loc && $locationName) {
        $cityPart = trim(explode(',', $locationName)[0]);
        $geo = fetchJson(
            'https://geocoding-api.open-meteo.com/v1/search?count=1&name=' . urlencode($cityPart),
            "$cacheDir/geocode_" . preg_replace('/[^a-z0-9]/i', '_', strtolower($cityPart)) . '.json',
            30 * 86400
        );
        $g = $geo['results'][0] ?? null;
        if ($g) {
            $loc = ['lat' => $g['latitude'], 'long' => $g['longitude'], 'locality' => $g['name'], 'country' => $g['country_code'] ?? ''];
        }
    }

    if ($loc) {
        $daysUntil = (strtotime($raceDate) - strtotime(date('Y-m-d'))) / 86400;

        if ($daysUntil >= 0 && $daysUntil <= 15) {
            $weatherUrl = "https://api.open-meteo.com/v1/forecast?latitude={$loc['lat']}&longitude={$loc['long']}"
                . "&daily=temperature_2m_max,temperature_2m_min,precipitation_probability_max,weathercode"
                . "&timezone=auto&start_date={$raceDate}&end_date={$raceDate}";
            $weather = fetchJson($weatherUrl, "$cacheDir/weather_{$circuitKey}_{$raceDate}.json", 3600);
            $daily = $weather['daily'] ?? null;

            if ($daily && isset($daily['temperature_2m_max'][0])) {
                $result = [
                    'status' => 'success',
                    'location' => ($loc['locality'] ?? '') . ', ' . ($loc['country'] ?? ''),
                    'temp_max' => round($daily['temperature_2m_max'][0]),
                    'temp_min' => round($daily['temperature_2m_min'][0]),
                    'rain_chance' => $daily['precipitation_probability_max'][0] ?? 0,
                    'weather_code' => $daily['weathercode'][0] ?? 0,
                ];
            }
        } else {
            $result = ['status' => 'too_far', 'days_until' => (int)$daysUntil];
        }
    }
}

echo json_encode($result);

<?php

// Definieer het jaar waarvoor je de coureurs wilt ophalen
$year = date("Y"); // Huidig jaar (2025)

// Ergast API URL voor coureurs van een specifiek jaar
$apiUrl = "http://ergast.com/api/f1/{$year}/drivers.json";

// Initialiseer cURL-sessie
$ch = curl_init();

// Stel cURL-opties in
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Zorg ervoor dat de respons wordt geretourneerd als string
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Volg eventuele redirects
curl_setopt($ch, CURLOPT_TIMEOUT, 30); // Timeout na 30 seconden

// Voer de cURL-request uit
$response = curl_exec($ch);

// Controleer op cURL-fouten
if (curl_errno($ch)) {
    echo 'cURL Fout: ' . curl_error($ch);
    exit;
}

// Sluit de cURL-sessie
curl_close($ch);

// Decodeer de JSON-respons
$data = json_decode($response, true);

// Controleer of de decoding succesvol was en of er data is
if (json_last_error() !== JSON_ERROR_NONE) {
    echo "Fout bij het decoderen van JSON: " . json_last_error_msg();
    exit;
}

if (!isset($data['MRData']['DriverTable']['Drivers'])) {
    echo "Geen coureurs gevonden of onverwachte API-respons voor jaar " . $year . ".\n";
    echo "Controleer of er al data beschikbaar is voor het seizoen " . $year . ".\n";
    exit;
}

$drivers = $data['MRData']['DriverTable']['Drivers'];

echo "--- Formule 1 Coureurs " . $year . " ---\n";
if (empty($drivers)) {
    echo "Geen coureurs gevonden voor seizoen " . $year . ". Het seizoen is misschien nog niet gestart of data is nog niet beschikbaar.\n";
} else {
    foreach ($drivers as $driver) {
        $fullName = $driver['givenName'] . ' ' . $driver['familyName'];
        $nationality = $driver['nationality'];
        $driverId = $driver['driverId'];
        $url = isset($driver['url']) ? $driver['url'] : 'N/A';

        echo "Naam: " . $fullName . " (ID: " . $driverId . ")\n";
        echo "Nationaliteit: " . $nationality . "\n";
        if ($url !== 'N/A') {
            echo "Wikipedia: " . $url . "\n";
        }
        echo "--------------------------\n";
    }
}

?>
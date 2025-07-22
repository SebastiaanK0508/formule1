<?php

// Basis URL voor de OpenF1 API
define('OPENF1_BASE_URL', 'https://api.openf1.org/v1/');

/**
 * Functie om een cURL request uit te voeren naar de OpenF1 API
 * @param string $endpoint Het API endpoint (bijv. 'meetings')
 * @param array $params Query parameters (bijv. ['year' => 2024])
 * @return array|false Gedeconstrueerde JSON response of false bij fout
 */
function callOpenF1Api(string $endpoint, array $params = []): array|false
{
    $url = OPENF1_BASE_URL . $endpoint;
    if (!empty($params)) {
        $url .= '?' . http_build_query($params);
    }

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => [
            "Accept: application/json"
        ],
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);

    if ($err) {
        // Log de cURL fout naar de PHP error log
        error_log("cURL Error for $url: " . $err);
        return false;
    } else {
        $data = json_decode($response, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
            return $data;
        } else {
            // Log JSON decode fouten of niet-array responses
            error_log("JSON Decode Error or non-array response from $url: " . json_last_error_msg() . " - Raw Response: " . $response);
            return false;
        }
    }
}

/**
 * Berekent de punten op basis van de positie en het sessietype.
 * De puntensystemen kunnen variëren per F1-seizoen, dit is gebaseerd op 2024 regels.
 * @param int|null $position De eindpositie van de coureur. Kan null zijn als niet voltooid.
 * @param string $sessionType Het type sessie ('Race' of 'Sprint').
 * @return int Het aantal toegekende punten.
 */
function calculatePoints(?int $position, string $sessionType): int
{
    // Geen punten als positie niet geldig is
    if ($position === null || $position < 1) {
        return 0;
    }

    $points = 0;

    if ($sessionType === 'Race') {
        $racePoints = [25, 18, 15, 12, 10, 8, 6, 4, 2, 1]; // Top 10
        if ($position >= 1 && $position <= count($racePoints)) {
            $points = $racePoints[$position - 1];
        }
    } elseif ($sessionType === 'Sprint') {
        $sprintPoints = [8, 7, 6, 5, 4, 3, 2, 1]; // Top 8
        if ($position >= 1 && $position <= count($sprintPoints)) {
            $points = $sprintPoints[$position - 1];
        }
    }
    // TODO: Voeg logica toe voor Fastest Lap punt indien nodig (als 'fastest_lap' data beschikbaar is en coureur in top 10 eindigde)

    return $points;
}


/**
 * Haalt de coureur standen op voor het opgegeven seizoen
 * @param int $year Het jaar waarvoor de standen moeten worden opgehaald.
 * @return array Een associatieve array met coureurnummers als sleutel en hun totale punten, naam en teamnaam.
 */
function getDriverStandings(int $year): array
{
    $allDriverPoints = [];
    $cachedDrivers = []; // Cache om driver info maar één keer op te halen

    // 1. Haal alle meetings (Grand Prix weekends) op voor het opgegeven jaar
    $meetings = callOpenF1Api('meetings', ['year' => $year]);

    if (!$meetings) {
        echo "Geen meetings gevonden voor $year." . PHP_EOL;
        return [];
    }

    foreach ($meetings as $meeting) {
        // We zijn geïnteresseerd in 'Race' en 'Sprint' sessies voor punten
        $sessionTypesToConsider = ['Race', 'Sprint'];
        $scoringSession = null;

        foreach ($sessionTypesToConsider as $type) {
            $sessions = callOpenF1Api('sessions', [
                'meeting_key' => $meeting['meeting_key'],
                'session_type' => $type,
            ]);

            if (is_array($sessions) && !empty($sessions)) {
                $potentialSession = $sessions[0];
                // Controleer of de sessie daadwerkelijk is afgelopen
                if (isset($potentialSession['date_end']) && strtotime($potentialSession['date_end']) < time()) {
                    $scoringSession = $potentialSession;
                    break; // Neem de eerste gevonden sessie die is afgelopen (Race heeft prioriteit over Sprint in de array)
                }
            }
        }

        if (!$scoringSession) {
            // Als er geen afgeronde Race of Sprint sessie is gevonden, ga dan naar de volgende meeting
            // echo "Geen afgeronde Race of Sprint sessie gevonden voor " . $meeting['meeting_name'] . PHP_EOL; // Optioneel: de-comment voor debugging
            continue;
        }

        $sessionKey = $scoringSession['session_key'];
        $currentSessionType = $scoringSession['session_type'];

        echo "Verwerken van " . $currentSessionType . " voor " . $meeting['meeting_name'] . PHP_EOL;

        // --- HIER ZIT DE KERN VAN DE OPLOSSING ---
        // Gebruik de 'results' endpoint om de uiteindelijke posities van de coureurs te krijgen
        // We verwijderen hier het 'result_status' filter om meer resultaten te vangen,
        // en vertrouwen op de 'position' check in calculatePoints().
        $results = callOpenF1Api('results', ['session_key' => $sessionKey]);

        if (is_array($results) && !empty($results)) {
            foreach ($results as $resultData) {
                $driverNumber = $resultData['driver_number'] ?? null;
                $position = $resultData['position'] ?? null; // 'position' is beschikbaar in 'results' endpoint

                // Verwerk alleen als coureurnummer en een geldige positie aanwezig zijn
                if ($driverNumber !== null && $position !== null) {
                    $points = calculatePoints((int)$position, $currentSessionType);

                    // Haal coureur informatie op, of gebruik de cache
                    if (!isset($cachedDrivers[$driverNumber])) {
                        $driverInfo = callOpenF1Api('drivers', ['driver_number' => $driverNumber]);
                        if ($driverInfo && !empty($driverInfo)) {
                            $cachedDrivers[$driverNumber] = $driverInfo[0];
                        } else {
                            // Fallback voor ontbrekende driver info om waarschuwingen te voorkomen
                            $cachedDrivers[$driverNumber] = ['full_name' => 'Coureur ' . $driverNumber, 'team_name' => 'Onbekend team'];
                            error_log("Coureur info niet gevonden voor nummer: $driverNumber in sessie $sessionKey");
                        }
                    }

                    $driverName = $cachedDrivers[$driverNumber]['full_name'] ?? ('Coureur ' . $driverNumber);
                    $teamName = $cachedDrivers[$driverNumber]['team_name'] ?? 'Onbekend team';

                    // Voeg punten toe aan de totale score van de coureur
                    if (isset($allDriverPoints[$driverNumber])) {
                        $allDriverPoints[$driverNumber]['points'] += $points;
                    } else {
                        $allDriverPoints[$driverNumber] = [
                            'name' => $driverName,
                            'points' => $points,
                            'team_name' => $teamName
                        ];
                    }
                }
            }
        } else {
            error_log("Geen geldige resultaten gevonden voor session_key: $sessionKey.");
            continue;
        }
    }

    // Sorteer de coureurs op punten in aflopende volgorde
    uasort($allDriverPoints, function($a, $b) {
        if ($a['points'] == $b['points']) {
            return 0;
        }
        return ($a['points'] > $b['points']) ? -1 : 1;
    });

    return $allDriverPoints;
}

// Haal de standings op voor het jaar 2024.
$currentYear = 2024;
$standings = getDriverStandings($currentYear);

echo "<h2>Formule 1 Coureur Standings " . $currentYear . "</h2>";
if (!empty($standings)) {
    echo "<ol>";
    foreach ($standings as $driverData) {
        echo "<li>" . htmlspecialchars($driverData['name']) . " (" . htmlspecialchars($driverData['team_name']) . "): " . $driverData['points'] . " punten</li>";
    }
    echo "</ol>";
} else {
    echo "<p>Geen standings beschikbaar of er zijn problemen met de API.</p>";
}

?>
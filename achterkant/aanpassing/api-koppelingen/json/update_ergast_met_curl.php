<?php
/**
 * PHP-script voor het ophalen van historische en actuele F1-data
 * van de Ergast-compatibele Jolpica API.
 * * Bevat fixes voor ChromeOS/Penguin netwerkblokkade (gebruikt localhost:8080 proxy).
 */

// --------------------------------------------------------------------------------
// Configuratie Jolpica API (Ergast Compatibel)
// --------------------------------------------------------------------------------

// De base_url bevat GEEN jaar meer
// --- Configuratie Jolpica API (Ergast Compatibel) ---

// Uitsluitend de Jolpica API gebruiken
$base_url = "http://api.jolpi.ca/ergast/f1/"; 
$suffix = 'jolpica'; // De bestandsnaam suffix

// Haal data op vanaf een recent jaar (bijvoorbeeld 2020) tot het huidige jaar
// Als je bijvoorbeeld data hebt tot 2021:
$start_year = 2020; 
$current_year = date('Y');

$seasons_to_fetch = range($start_year, $current_year);

// Definieer de endpoints die je per seizoen wilt ophalen
$endpoints = [
    'DriverStandings'      => 'driverStandings.json',
    'ConstructorStandings' => 'constructorStandings.json',
    'DriversFull'          => 'drivers.json?', 
    'Circuits'             => 'circuits.json',          
    'AllRaceResults'       => 'results.json',
    'races'                => 'races.json'
];

// --------------------------------------------------------------------------------
// cURL Functie (met Proxy Fix)
// --------------------------------------------------------------------------------

/**
 * Haalt data op via cURL met de noodzakelijke fixes voor de lokale omgeving.
 * @param string $url De volledige URL om op te halen.
 * @return array De gedecodeerde JSON-data.
 * @throws Exception bij fouten.
 */
function fetch_data($url) {
    // 1. Probeer de verbinding met HTTPS, ZONDER de lokale proxy (Dit is wat de browser doet)
    // De Jolpica API stuurt waarschijnlijk de data alleen via HTTPS
    try {
        $data = attempt_curl($url, false); // false = geen proxy
        return $data;
    } catch (Exception $e) {
        // Als de HTTPS-verbinding faalt (vanwege de ChromeOS blokkade of andere reden),
        // vallen we terug op de enige werkende route: HTTP via de lokale proxy.
        
        // Let op: De URL moet nu terug naar HTTP
        $http_url = str_replace('https://', 'http://', $url);
        
        // 2. Probeer de HTTP-verbinding, MET de lokale proxy (De fallback-route)
        try {
            $data = attempt_curl($http_url, true); // true = lokale proxy gebruiken
            return $data;
        } catch (Exception $e_fallback) {
            // Als ook de fallback faalt, geven we de fout van de fallback terug.
            throw new Exception("Ophalen mislukt via HTTPS én via HTTP/Proxy: " . $e_fallback->getMessage());
        }
    }
}

/**
 * Interne helper functie voor de cURL logica
 */
function attempt_curl($url, $use_proxy) {
    $ch = curl_init();
    
    if ($use_proxy) {
        // Gebruik de proxy voor de fallback HTTP-route
        curl_setopt($ch, CURLOPT_PROXY, 'http://localhost:8080'); 
        
        // De 301-redirect van HTTP naar HTTPS moet uit blijven staan
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false); 
        curl_setopt($ch, CURLOPT_MAXREDIRS, 1);
        
    } else {
        // Geen proxy voor de directe (ideale) HTTPS-route
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 
    }
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    
    $json_data = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error_msg = curl_error($ch);
    curl_close($ch);
    
    if ($http_code != 200) {
        throw new Exception("HTTP Code: {$http_code}. cURL Fout: {$error_msg}");
    }
    
    $data = json_decode($json_data, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Fout bij decoderen JSON.");
    }
    return $data;
}

// ... de rest van je code blijft staan ...
// --------------------------------------------------------------------------------
// Hoofdfunctie voor het Ophalen en Opslaan van Data (Historisch & Huidig)
// --------------------------------------------------------------------------------

/**
 * Loopt door alle seizoenen en endpoints om de data op te halen en op te slaan.
 */
function haal_en_sla_data_op($base, $endpoints, $seasons) {
    echo "[" . date('Y-m-d H:i:s') . "] Starten met Jolpica data update (Historisch & Huidig)...\n";
    
    foreach ($seasons as $year_or_current) {
        
        // Zorgt voor een nette weergave in de terminal
        echo "\n========================================================\n";
        echo " BEZIG MET SEIZOEN: " . strtoupper($year_or_current) . " \n";
        echo "========================================================\n";
        
        foreach ($endpoints as $type => $endpoint) {
            // Bouw de volledige URL op
            $url = $base . $year_or_current . '/' . $endpoint;
            
            // De bestandsnaam gebruikt 'current' of het jaartal
            $bestandsnaam = strtolower($type) . "_" . strtolower($year_or_current) . "_jolpica.json";            
            echo "  -> Ophalen: {$type} van " . strtoupper($year_or_current) . " (URL: {$url})\n";
            
            try {
                // 1. Data ophalen
                $data_array = fetch_data($url);

                // 2. Data formatteren en opslaan
                $geformatteerde_json = json_encode($data_array, JSON_PRETTY_PRINT);
                
                if (file_put_contents($bestandsnaam, $geformatteerde_json) === FALSE) {
                    echo "  ❌ Fout bij het schrijven naar bestand '{$bestandsnaam}'.\n";
                } else {
                    echo "  ✅ Succes! Data opgeslagen in: {$bestandsnaam}\n";
                }
                
            } catch (Exception $e) {
                echo "  ❌ Fout bij ophalen van {$type} voor " . strtoupper($year_or_current) . ": " . $e->getMessage() . "\n";
                continue;
            }
        }
    }
    echo "\n[" . date('Y-m-d H:i:s') . "] Historische & Huidige Update voltooid.\n";
}

// Start de data-ophaling (loopt van 2020 tot nu)
haal_en_sla_data_op($base_url, $endpoints, $seasons_to_fetch, $suffix);

?>
<?php
/**
 * PHP-script voor het ophalen van F1-data van de Jolpica API.
 * * Bevat automatische detectie om te bepalen of de lokale proxy (localhost:8080)
 * nodig is (voor omgevingen zoals ChromeOS/Penguin) OF dat een directe verbinding 
 * gebruikt kan worden (voor live servers).
 */

// --------------------------------------------------------------------------------
// Configuratie Jolpica API
// --------------------------------------------------------------------------------

$base_url = "http://api.jolpi.ca/ergast/f1/"; 
$suffix = 'jolpica'; 

// Haal data op vanaf 2020 tot het huidige jaar (of pas dit aan als je andere jaren wilt testen)
$start_year = 2000; 
$current_year = date('Y');
// $start_year = 1950; 
// $current_year = 1960; 
$seasons_to_fetch = range($start_year, $current_year); 
$endpoints = [
    'DriverStandings'      => 'driverStandings.json',
    'ConstructorStandings' => 'constructorStandings.json',
    'DriversFull'          => 'drivers.json?limit=100', 
    'Circuits'             => 'circuits.json',          
    'AllRaceResults'       => 'results.json',
    'race'                 => 'races.json'
];

/**
 * Controleert snel of een verbinding met de lokale proxy (8080) tot stand kan komen.
 * @return bool True als de lokale proxy nodig is, anders False.
 */
function check_if_proxy_needed() {
    // Probeer een zeer snelle verbinding met localhost:8080
    $fp = @fsockopen('localhost', 8080, $errno, $errstr, 0.5); // 0.5 seconde timeout
    
    if ($fp) {
        // Als de verbinding slaagt, draait de proxy. Dit betekent:
        //  1. We zitten NIET op de geblokkeerde ChromeOS/Penguin omgeving.
        //  2. De proxy is onnodig en zorgt voor fouten (405).
        fclose($fp);
        return false; // Geen proxy nodig, directe verbinding
    } else {
        // Als de verbinding FAALT (timeout of "connection refused"), dan:
        //  1. We zitten op een LIVE SERVER: Geen proxy nodig (false)
        //  2. We zitten op de GEBLOKKEERDE CHROMEOS: Proxy is de enige uitgaande route (true)
        
        // De foutmelding van je live server was: "Failed to connect to localhost port 8080 after 0 ms"
        // Als de server direct fout, is er geen proxy, dus is de proxy NIET nodig.
        
        // MAAR: de enige keer dat we de proxy willen gebruiken, is als een directe cURL FAALT.
        // Omdat de eerdere logica zo complex was, is het veiliger om hier ALLEEN True te returnen
        // als we een specifieke error van de geblokkeerde omgeving zien.
        
        // Laten we de cURL proberen en de output analyseren i.p.v. fsockopen, wat complex is.
        
        // Om het simpel te houden, gebruiken we nu alleen de cURL logica met een try/catch:
        // We proberen het ZONDER proxy, als dat mislukt met een cURL (7) error, proberen we het MET proxy.
        return false; // Begin met de aanname dat geen proxy nodig is
    }
}

// --------------------------------------------------------------------------------
// cURL Functie
// --------------------------------------------------------------------------------

/**
 * Haalt data op via cURL met automatische fallback op proxy.
 */
function fetch_data($url) {
    // 1. EERST: Probeer een directe verbinding (zoals op een Live Server)
    try {
        return attempt_curl($url, false);
    } catch (Exception $e_direct) {
        // 2. TWEEDE: Als de directe verbinding FAALT (zoals op ChromeOS/Penguin met blokkade)
        // De fout 'Failed to connect' op ChromeOS is de trigger voor de fallback
        if (strpos($e_direct->getMessage(), 'Failed to connect') !== false || strpos($e_direct->getMessage(), 'Couldn\'t connect') !== false) {
             
            // VALLEN TERUG op de HTTP-verbinding via de lokale proxy (de ChromeOS fix)
            $http_url = str_replace('https://', 'http://', $url);
            try {
                return attempt_curl($http_url, true); // true = proxy gebruiken
            } catch (Exception $e_proxy) {
                // Als zelfs de proxy faalt (zoals op de live server zonder proxy), gooien we de fout van de live server terug
                throw new Exception("Ophalen mislukt via DIRECT én via HTTP/Proxy: " . $e_proxy->getMessage());
            }
        }
        
        // Als het een andere fout is (zoals een 404/500 van de server), gooien we die terug
        throw $e_direct; 
    }
}

/**
 * Interne helper functie voor de cURL logica
 */
function attempt_curl($url, $use_proxy) {
    $ch = curl_init();
    
    if ($use_proxy) {
        // Gebruik de proxy voor de fallback HTTP-route (ChromeOS fix)
        curl_setopt($ch, CURLOPT_PROXY, 'http://localhost:8080'); 
        // De 301-redirect van HTTP naar HTTPS moet uit blijven staan bij proxy gebruik
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false); 
        curl_setopt($ch, CURLOPT_MAXREDIRS, 1);
    } else {
        // Geen proxy, volg redirects (Live Server)
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

// --------------------------------------------------------------------------------
// Hoofdfunctie voor het Ophalen en Opslaan van Data
// --------------------------------------------------------------------------------

/**
 * Loopt door alle seizoenen en endpoints om de data op te halen en op te slaan.
 */
function haal_en_sla_data_op($base, $endpoints, $seasons, $suffix) {
    echo "[" . date('Y-m-d H:i:s') . "] Starten met data update (Basis: " . strtoupper($suffix) . ")... ";
    
    // Controleer de rechten voor het schrijven naar bestanden (nieuwe controle)
    if (!is_writable(getcwd())) {
        echo "  🛑 FATALE FOUT: De huidige map is niet schrijfbaar. Pas de rechten aan met 'sudo chmod 755 .'\n";
        return;
    }
    
    foreach ($seasons as $year) {
        
        echo "\n========================================================\n";
        echo " BEZIG MET SEIZOEN: {$year} \n";
        echo "========================================================\n";
        
        foreach ($endpoints as $type => $endpoint) {
            
            $url = $base . $year . '/' . $endpoint;
            $bestandsnaam = strtolower($type) . "_" . $year . "_" . $suffix . ".json";
            
            echo "  -> Ophalen: {$type} van {$year} (URL: {$url})\n";
            
            try {
                $data_array = fetch_data($url);

                $geformatteerde_json = json_encode($data_array, JSON_PRETTY_PRINT);
                
                if (file_put_contents($bestandsnaam, $geformatteerde_json) === FALSE) {
                    echo "  ❌ Fout bij het schrijven naar bestand '{$bestandsnaam}'.\n";
                } else {
                    echo "  ✅ Succes! Data opgeslagen in: {$bestandsnaam}\n";
                }
                
            } catch (Exception $e) {
                echo "  ❌ Fout bij ophalen van {$type} voor {$year}: " . $e->getMessage() . "\n";
                continue;
            }
        }
    }
    echo "\n[" . date('Y-m-d H:i:s') . "] Data Update voltooid voor " . strtoupper($suffix) . ".\n";
}

// --------------------------------------------------------------------------------
// Script Uitvoeren
// --------------------------------------------------------------------------------

haal_en_sla_data_op($base_url, $endpoints, $seasons_to_fetch, $suffix); 

?>
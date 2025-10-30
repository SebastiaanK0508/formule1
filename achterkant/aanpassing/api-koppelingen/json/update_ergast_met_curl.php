<?php

$base_url = "http://api.jolpi.ca/ergast/f1/"; 
$suffix = 'jolpica'; 

$start_year = 2024; 
$current_year = date('Y');
$seasons_to_fetch = range($start_year, $current_year); 
$endpoints = [
    'DriverStandings'      => 'driverStandings.json',
    'ConstructorStandings' => 'constructorStandings.json',
    'DriversFull'          => 'drivers.json', 
    'Circuits'             => 'circuits.json',          
    'AllRaceResults'       => 'results.json',
    'race'                 => 'races.json',
    'sprint'               => 'sprint.json',
    'constructors'         => 'constructors.json',
    'seasons'              => 'seasons.json'
];

/**
 * 
 * @return bool 
 */
function check_if_proxy_needed() {
    $fp = @fsockopen('localhost', 8080, $errno, $errstr, 0.5); // 0.5 seconde timeout
    
    if ($fp) {
        fclose($fp);
        return false;
    } else {
        return false; 
    }
}

function fetch_data($url) {
    try {
        return attempt_curl($url, false);
    } catch (Exception $e_direct) {
        if (strpos($e_direct->getMessage(), 'Failed to connect') !== false || strpos($e_direct->getMessage(), 'Couldn\'t connect') !== false) {
            $http_url = str_replace('https://', 'http://', $url);
            try {
                return attempt_curl($http_url, true); 
            } catch (Exception $e_proxy) {
                throw new Exception("Ophalen mislukt via DIRECT én via HTTP/Proxy: " . $e_proxy->getMessage());
            }
        }
        throw $e_direct; 
    }
}
function attempt_curl($url, $use_proxy) {
    $ch = curl_init();
    
    if ($use_proxy) {
        curl_setopt($ch, CURLOPT_PROXY, 'http://localhost:8080'); 
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false); 
        curl_setopt($ch, CURLOPT_MAXREDIRS, 1);
    } else {
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
function haal_en_sla_data_op($base, $endpoints, $seasons, $suffix) {
    echo "[" . date('Y-m-d H:i:s') . "] Starten met data update (Basis: " . strtoupper($suffix) . ")... ";
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

haal_en_sla_data_op($base_url, $endpoints, $seasons_to_fetch, $suffix); 

?>
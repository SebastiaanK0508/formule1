<?php

// Configuratie
$base_url = "https://api.openf1.org/v1/drivers";
$output_filename = 'openf1_unique_drivers.json';

// Haal ALLE drivers op van de OpenF1 database (dit omvat de meest historische data die zij hebben)
$full_url = $base_url;

// --- Haal de data op ---
// Gebruik file_get_contents om het GET-verzoek te doen
$response_json = @file_get_contents($full_url);

if ($response_json === FALSE) {
    die("❌ Fout: Kon geen verbinding maken met de OpenF1 API of geen data ophalen.");
}

// Decodeer de JSON naar een PHP array
$all_drivers_data = json_decode($response_json, true);

// ----------------------------------------------------
// 2. Data Verwerken, Meer Data Toevoegen en Uniek maken
// ----------------------------------------------------

// Dit zal onze uiteindelijke array van unieke coureurs worden
$unique_drivers = [];

if (!empty($all_drivers_data)) {
    foreach ($all_drivers_data as $driver) {
        
        // **Gebruik de unieke identificatie (UUID) als sleutel voor de deduplicatie.**
        // De driver_uuid is het meest betrouwbare unieke veld in de OpenF1 dataset.
        // Als dit veld mist, valt de code terug op het driver_number.
        $key = $driver['driver_uuid'] ?? $driver['driver_number']; 
        
        // Zorg ervoor dat we ALLE velden overnemen die de API verstrekt.
        // Door de driver direct toe te wijzen, nemen we alle data mee
        // en overschrijven we eerdere, mogelijk minder complete vermeldingen.
        $unique_drivers[$key] = $driver;
    }
}

// Converteer de associatieve array (met unieke keys) terug naar een simpele lijst voor JSON
$final_drivers_list = array_values($unique_drivers);

// ----------------------------------------------------
// 3. Data Opslaan als één JSON-bestand (Overschrijft het oude bestand)
// ----------------------------------------------------

// Converteer de PHP array terug naar een JSON string
// JSON_PRETTY_PRINT voor leesbaarheid
$final_json_output = json_encode($final_drivers_list, JSON_PRETTY_PRINT);

// Schrijf de JSON string naar het bestand. 
// **file_put_contents() overschrijft het bestaande bestand automatisch.**
$bytes_written = file_put_contents($output_filename, $final_json_output);

// Controleer op succes
if ($bytes_written !== FALSE) {
    $file_size_kb = round($bytes_written / 1024, 2);
    echo "✅ **Update Succesvol!** Het bestand **" . $output_filename . "** is bijgewerkt/overschreven.<br>";
    echo "Bestandsgrootte: " . $file_size_kb . " KB<br>";
    echo "Totaal aantal unieke coureurs: " . count($final_drivers_list);
} else {
    echo "❌ **Fout!** Het is niet gelukt om het bestand **" . $output_filename . "** te schrijven. Controleer de schrijfrechten van de map.";
}

?>
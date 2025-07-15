<?php
// update_f1_data.php
// Bestandspad naar je JSON-data (als je JSON gebruikt). Pas dit aan indien nodig.
$jsonFile = __DIR__ . '/drivers.json';

// --- ESSENTIELE BEVEILIGING VOOR EEN WEBHOOK ---
// Dit is JOUW GEHEIME SLEUTEL. VERVANG DEZE DOOR EEN LANGE, COMPLEXE EN UNIEKE WAARDE!
$secret_token = 'WJD83K9BCOQ13BW4ACZYVQEA3CEQM9ZV5J54EZO4HO0CC6RHZ5ROWPI4U1Q0QVX0';

// Controleer of het 'token' parameter aanwezig is in de URL en of deze overeenkomt met je geheime token
if (!isset($_GET['token']) || $_GET['token'] !== $secret_token) {
    // Als het token ontbreekt of onjuist is, stuur een 403 Forbidden statuscode
    header("HTTP/1.1 403 Forbidden");
    die("Toegang geweigerd. Ongeldig of ontbrekend token.");
}
// --- EINDE BEVEILIGING ---


// --- HIER BEGINT JOUW DATA-VERWERKINGS LOGICA ---
// Deze code zal alleen worden uitgevoerd als het geheime token correct is.

// Optie 1: Data uit JSON-bestand lezen, bijwerken en terugschrijven (zoals eerder besproken)
// Pas dit aan met jouw specifieke update-logica.
try {
    if (!file_exists($jsonFile)) {
        throw new Exception("Data bestand 'drivers.json' niet gevonden.");
    }
    $json_data = file_get_contents($jsonFile);
    $drivers = json_decode($json_data, true);

    // Voorbeeld van update-logica (dit zou dynamisch moeten zijn in een echte app!)
    $updated_successfully = false;
    foreach ($drivers as &$driver) {
        if ($driver['name'] === 'Max Verstappen') { // Zoek de coureur
            $driver['points'] += 25; // Update punten
            $driver['wins'] += 1;    // Update zeges
            $updated_successfully = true;
            break;
        }
    }
    unset($driver); // Verbreek de referentie

    if (!$updated_successfully) {
        throw new Exception("Coureur 'Max Verstappen' niet gevonden voor update.");
    }

    $new_json_data = json_encode($drivers, JSON_PRETTY_PRINT);
    if (file_put_contents($jsonFile, $new_json_data, LOCK_EX) === false) {
        throw new Exception("Kan 'drivers.json' niet opslaan.");
    }

    // Alles ging goed
    header("HTTP/1.1 200 OK"); // Stuur een succes statuscode
    echo "F1 coureursdata succesvol bijgewerkt via JSON!";

} catch (Exception $e) {
    // Log de fout voor je eigen administratie (bekijk je Apache error logs)
    error_log("Webhook fout: " . $e->getMessage());
    header("HTTP/1.1 500 Internal Server Error"); // Stuur een serverfout statuscode
    echo "Fout bij het verwerken van de update: " . $e->getMessage();
}

// Optie 2: Data ophalen van een externe F1 API en in je MariaDB/JSON opslaan
/*
// Als je deze optie kiest, zorg dan dat je database-connectie code hier staat
// en de logica om de externe API aan te roepen en de resultaten op te slaan.
// Dit kan complexer zijn afhankelijk van de API die je gebruikt.

// Bijvoorbeeld (code van eerder):
$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "formule1";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // ... je code om API aan te roepen en in DB op te slaan ...
    header("HTTP/1.1 200 OK");
    echo "Data van externe API succesvol opgehaald en verwerkt.";

} catch (PDOException $e) {
    error_log("Webhook database fout: " . $e->getMessage());
    header("HTTP/1.1 500 Internal Server Error");
    echo "Fout bij database-actie: " . $e->getMessage();
}
*/

?>
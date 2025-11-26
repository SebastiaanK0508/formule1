<?php

// 0. Database configuratie inladen
// Zorg ervoor dat 'db_config.php' de PDO-verbinding ($pdo) correct definieert met server-specifieke gegevens.
require_once 'db_config.php'; 

// 1. Configuratie voor de API
// De nieuwe open-source URL zonder API-sleutel
$new_api_url = "https://f1newsapi.onrender.com/news/f1"; 

// 2. Initialiseer cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $new_api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// --- BELANGRIJKE TEST VOOR SERVER HTTPS/SSL FOUTEN ---
// Dit schakelt de controle op SSL-certificaten uit, vaak nodig op shared hosting.
// VERWIJDER DEZE TWEE REGELS ZODRA HET PROBLEEM IS OPGELOST!
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
// --------------------------------------------------

// 3. Voer de aanroep uit en sluit cURL
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch); // Vang cURL fouten op
curl_close($ch);

// --- 4. Verwerking en Opslag van de respons ---

echo "<h2>📰 F1 Nieuws Scraper Resultaat</h2>";
echo "API Gebruikt: " . htmlspecialchars($new_api_url) . "<br>";

if ($curl_error) {
    // Toon een duidelijke cURL foutmelding als de verbinding faalt
    echo "❌ **cURL VERBINDINGSFOUT:** De aanroep naar de API is mislukt. Reden: " . htmlspecialchars($curl_error) . "<br>";
    echo "<hr>";
    exit(); // Stop het script hier
}

if ($http_code === 200) {
    $data = json_decode($response, true); 
    
    // API levert de data direct in de root als een array
    $articles = $data; 

    // Robustere array-verwerking: verzeker dat we door een array van artikelen loopen
    if (!is_array($articles) && is_array($data) && !empty($data)) {
        $articles = [$data];
    } elseif (!is_array($articles)) {
        $articles = [];
    }
    
    // Herstel associatieve array sleutels naar numerieke
    if (count($articles) > 1 && array_keys($articles) !== range(0, count($articles) - 1)) {
        $articles = array_values($articles);
    }
    

    $count_saved = 0;
    $count_skipped_duplicate = 0;
    
    echo "Totaal aantal artikelen opgehaald: " . count($articles) . "<br>";
    echo "⚠️ Datumfiltering is niet mogelijk, slaat alle artikelen op (tenzij duplicaat).<hr>";

    if (!empty($articles)) {
        // Bereid de INSERT-statement voor
        // LET OP: publicatie_datum en afbeelding_url worden NULL
        $stmt = $pdo->prepare("INSERT INTO f1_nieuws (titel, artikel_url, publicatie_datum, afbeelding_url) VALUES (?, ?, ?, ?)");

        foreach ($articles as $article) {
            $titel = $article['title'] ?? 'Geen Titel';
            $url = $article['url'] ?? '';
            // Datum en Afbeelding zijn niet beschikbaar in deze API-respons
            $published_date_mysql = null;
            $image_url = null; 

            try {
                // Voer de INSERT uit
                $stmt->execute([
                    $titel, 
                    $url, 
                    $published_date_mysql, 
                    $image_url             
                ]);
                echo "✅ Artikel opgeslagen: " . htmlspecialchars($titel) . "<br>";
                $count_saved++;

            } catch (\PDOException $e) {
                if ($e->getCode() == 23000) { 
                    // Foutcode 23000 = Duplicaat entry (door Unique Key op artikel_url)
                    $count_skipped_duplicate++;
                } else {
                    echo "❌ Databasefout bij opslaan: " . $e->getMessage() . "<br>";
                }
            }
        }
        
        echo "<hr><strong>Samenvatting:</strong><br>";
        echo "Artikelen opgeslagen: $count_saved<br>";
        echo "Duplicaten overgeslagen: $count_skipped_duplicate";
    } else {
        echo "Geen artikelen gevonden in de API-respons om op te slaan.";
    }
} else {
    // Foutafhandeling van de API-aanroep (bijv. HTTP 404, 500, etc.)
    echo "Fout bij het ophalen van de data. HTTP Code: " . $http_code . "<br>";
    // Toon de respons alleen als deze niet te groot is om debuggen te vergemakkelijken
    if (strlen($response) < 500) {
        echo "API-respons: " . htmlspecialchars($response);
    }
}

?>
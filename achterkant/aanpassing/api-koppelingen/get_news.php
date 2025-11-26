<?php

require_once 'db_config.php'; 

$new_api_url = "https://f1newsapi.onrender.com/news/f1"; 

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $new_api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    $data = json_decode($response, true); 
    
    // API levert de data direct in de root als een array
    $articles = $data; 

    // Verzeker dat we door een array van artikelen loopen
    if (!is_array($articles)) {
        if (is_array($data) && !empty($data)) {
            $articles = [$data];
        } else {
            $articles = [];
        }
    }
    
    // Zorgt ervoor dat de array numeriek geïndexeerd is voor de foreach-lus
    if (count($articles) > 1 && array_keys($articles) !== range(0, count($articles) - 1)) {
        $articles = array_values($articles);
    }
    

    $count_saved = 0;
    $count_skipped_duplicate = 0;
    
    echo "<h2>📰 F1 Nieuws Scraper Resultaat</h2>";
    echo "API Gebruikt: " . htmlspecialchars($new_api_url) . "<br>";
    echo "Totaal aantal artikelen opgehaald: " . count($articles) . "<br>";
    echo "⚠️ Datumfiltering is niet mogelijk, slaat alle artikelen op (tenzij duplicaat).<hr>";

    if (!empty($articles)) {
        // Bereid de INSERT-statement voor
        $stmt = $pdo->prepare("INSERT INTO f1_nieuws (titel, artikel_url, publicatie_datum, afbeelding_url) VALUES (?, ?, ?, ?)");

        foreach ($articles as $article) {
            $titel = $article['title'] ?? 'Geen Titel';
            $url = $article['url'] ?? '';
            // Datum en Afbeelding worden leeg gelaten omdat ze niet beschikbaar zijn
            $published_date_mysql = null;
            $image_url = null; 

            try {
                // Voer de INSERT uit
                $stmt->execute([
                    $titel, 
                    $url, 
                    $published_date_mysql, // NULL
                    $image_url             // NULL
                ]);
                echo "✅ Artikel opgeslagen: " . htmlspecialchars($titel) . "<br>";
                $count_saved++;

            } catch (\PDOException $e) {
                if ($e->getCode() == 23000) { 
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
    // Foutafhandeling van de API-aanroep
    echo "Fout bij het ophalen van de data. HTTP Code: " . $http_code . "<br>";
    echo "API-respons: " . htmlspecialchars($response);
}

?>
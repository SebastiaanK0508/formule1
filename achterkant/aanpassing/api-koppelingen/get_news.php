<?php
require_once 'db_config.php'; 
$new_api_url = "https://f1newsapi.onrender.com/news/f1"; 
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $new_api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);
echo "<h2>📰 F1 Nieuws Scraper Resultaat</h2>";
echo "API Gebruikt: " . htmlspecialchars($new_api_url) . "<br>";

if ($curl_error) {
    echo "❌ **cURL VERBINDINGSFOUT:** De aanroep naar de API is mislukt. Reden: " . htmlspecialchars($curl_error) . "<br>";
    echo "<hr>";
    exit();
}
if ($http_code === 200) {
    $data = json_decode($response, true); 
    $articles = $data; 
    if (!is_array($articles) && is_array($data) && !empty($data)) {
        $articles = [$data];
    } elseif (!is_array($articles)) {
        $articles = [];
    }
    if (count($articles) > 1 && array_keys($articles) !== range(0, count($articles) - 1)) {
        $articles = array_values($articles);
    }

    $count_saved = 0;
    $count_skipped_duplicate = 0;
    
    echo "Totaal aantal artikelen opgehaald: " . count($articles) . "<br>";
    echo "⚠️ Datumfiltering is niet mogelijk, slaat alle artikelen op (tenzij duplicaat).<hr>";

    if (!empty($articles)) {
        $stmt = $pdo->prepare("INSERT INTO f1_nieuws (titel, artikel_url, publicatie_datum, afbeelding_url) VALUES (?, ?, ?, ?)");

        foreach ($articles as $article) {
            $titel = $article['title'] ?? 'Geen Titel';
            $url = $article['url'] ?? '';
            $published_date_mysql = null;
            $image_url = null; 

            try {
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
    echo "Fout bij het ophalen van de data. HTTP Code: " . $http_code . "<br>";
    if (strlen($response) < 500) {
        echo "API-respons: " . htmlspecialchars($response);
    }
}

?>
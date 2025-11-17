<?php

// 0. Database configuratie inladen
require_once 'db_config.php'; 

$thirty_days_ago = (new DateTime('-30 days'))->format('Y-m-d');
$today = (new DateTime('now'))->format('Y-m-d');

// 1. Configuratie voor de API
$api_host = "f1-latest-news.p.rapidapi.com";
$base_url = "https://" . $api_host . "/news"; 
$api_key  = "0a2c4ea3ecmsh9844ecbf51d7592p1c1076jsn52785b2db328"; 

// 2. Optionele parameters voor de zoekopdracht
// $params = [
//     'pageSize' => 50,         // <-- Meer berichten per aanroep
//     'from' => $thirty_days_ago, // <-- Startdatum toegevoegd
//     'to' => $today              // <-- Einddatum toegevoegd
// ];

// 3. Bouw de volledige URL op
// $query_string = http_build_query($params);
// $full_url = $base_url . '?' . $query_string;
$full_url = $base_url;
// 4. Initialiseer cURL en stel headers in voor RapidAPI
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $full_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$headers = [
    "X-RapidAPI-Key: " . $api_key,
    "X-RapidAPI-Host: " . $api_host
];
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

// 5. Voer de aanroep uit en sluit cURL
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// --- 6. Verwerking en Opslag van de respons ---
if ($http_code === 200) {
    $data = json_decode($response, true); 
    $articles = $data['articles'] ?? $data;

    $count_saved = 0;
    $count_skipped = 0;

    echo "<h2>📰 Nieuwsartikelen Opgehaald en Opgeslagen</h2>";
    echo "Totaal aantal resultaten opgehaald: " . count($articles) . "<br>";
    echo "Zoekperiode: $thirty_days_ago tot $today<hr>";

    if (!empty($articles)) {
        // Bereid de INSERT-statement voor met de kolom 'afbeelding_url'
        $stmt = $pdo->prepare("INSERT INTO f1_nieuws (titel, artikel_url, publicatie_datum, afbeelding_url) VALUES (?, ?, ?, ?)");

        foreach ($articles as $article) {
            $titel = $article['title'] ?? 'Geen Titel';
            $url = $article['url'] ?? '';
            $published_date = $article['published_date'] ?? null;
            $image_url = $article['image_url'] ?? $article['urlToImage'] ?? null; 

            // Formatteer de datum naar MySQL-formaat
            $published_date_mysql = null;
            if ($published_date) {
                try {
                    $dt = new DateTime($published_date);
                    $published_date_mysql = $dt->format('Y-m-d H:i:s');
                } catch (Exception $e) {
                    // Geen geldige datum
                }
            }

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
                    echo "⚠️ Artikel overgeslagen (duplicaat URL): " . htmlspecialchars($titel) . "<br>";
                    $count_skipped++;
                } else {
                    echo "❌ Databasefout bij opslaan: " . $e->getMessage() . "<br>";
                }
            }
        }
        echo "<hr><strong>Samenvatting:</strong> $count_saved artikelen opgeslagen. $count_skipped duplicaten overgeslagen.";
    } else {
        echo "Geen artikelen gevonden om op te slaan.";
    }
} else {
    // Foutafhandeling van de API-aanroep
    echo "Fout bij het ophalen van de data. HTTP Code: " . $http_code . "<br>";
    echo "API-respons: " . htmlspecialchars($response);
}

?>
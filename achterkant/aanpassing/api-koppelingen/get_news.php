<?php
function log_message($message) {
    echo "[" . date('Y-m-d H:i:s') . "] " . $message . "\n";
}
require_once 'db_config.php'; 
log_message("--- START F1 Nieuws Scraper ---");

$new_api_url = "https://f1newsapi.onrender.com/news/f1"; 
log_message("API-Endpoint: " . $new_api_url);
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $new_api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);
if ($curl_error) {
    log_message("❌ cURL FOUT: De aanroep naar de API is mislukt. Reden: " . $curl_error);
    log_message("--- EINDE SCRAPER (MISLUKT) ---");
    exit(1);
}

if ($http_code !== 200) {
    log_message("❌ HTTP FOUT: Fout bij ophalen data. HTTP Code: " . $http_code);
    if (strlen($response) < 500) {
        log_message("API-Respons (Kort): " . trim($response));
    }
    log_message("--- EINDE SCRAPER (MISLUKT) ---");
    exit(1);
}

$data = json_decode($response, true); 
$articles = $data; 
if (!is_array($articles)) {
    log_message("⚠️ PARSE WAARSCHUWING: Respons is geen array. Aantal artikelen: 0");
    $articles = [];
}

$total_articles = count($articles);
log_message("✅ Data succesvol opgehaald. Totaal aantal artikelen om te verwerken: " . $total_articles);

$count_saved = 0;
$count_skipped_duplicate = 0;
$count_skipped_missing_data = 0;

if ($total_articles > 0) {
    $stmt = $pdo->prepare("INSERT INTO f1_nieuws (titel, artikel_url, publicatie_datum, afbeelding_url) VALUES (?, ?, ?, ?)");

    foreach ($articles as $index => $article) {
        $titel = $article['title'] ?? null;
        $url = $article['url'] ?? null;
        $published_date_api = $article['publishedAt'] ?? null;
        $image_url = $article['urlToImage'] ?? null;
        if (!$titel || !$url) {
            log_message("⚠️ ARTIKEL " . ($index + 1) . " OVERGESLAGEN: Ontbrekende Tite/URL. JSON Index: " . $index);
            $count_skipped_missing_data++;
            continue;
        }
                $published_date_mysql = null;
        if ($published_date_api) {
            try {
                $dt = new DateTime($published_date_api);
                $published_date_mysql = $dt->format('Y-m-d H:i:s');
            } catch (Exception $e) {
                log_message("⚠️ ARTIKEL " . ($index + 1) . " (" . $titel . "): Kon publicatiedatum niet parsen.");
            }
        }
        try {
            $stmt->execute([
                $titel, 
                $url, 
                $published_date_mysql, 
                $image_url             
            ]);
            log_message("   -> Artikel opgeslagen: " . substr($titel, 0, 50) . "...");
            $count_saved++;

        } catch (\PDOException $e) {
            if ($e->getCode() == 23000) { 
                log_message("   -> Duplicaat overgeslagen: " . substr($titel, 0, 50) . "...");
                $count_skipped_duplicate++;
            } else {
                log_message("   -> ❌ DB FOUT bij opslaan: " . substr($titel, 0, 50) . "... Fout: " . $e->getMessage());
            }
        }
    }
} else {
    log_message("Geen artikelen gevonden in de API-respons om op te slaan.");
}
log_message("--- SAMENVATTING ---");
log_message("Artikelen succesvol opgeslagen: " . $count_saved);
log_message("Duplicaten overgeslagen: " . $count_skipped_duplicate);
log_message("Overgeslagen (data ontbreekt): " . $count_skipped_missing_data);
log_message("--- EINDE F1 Nieuws Scraper ---");
?>
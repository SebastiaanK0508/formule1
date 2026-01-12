<?php
function log_message($message) {
    echo "[" . date('Y-m-d H:i:s') . "] " . $message . "\n";
}

require_once 'db_config.php'; 
log_message("--- START F1 NIEUWS SCRAPER (STRICTE F1 FILTER) ---");

$apiKey = '967a1f5bab57402eba78e99d0f157d64';
$keywords = '("Formula 1" OR "F1" OR "Grand Prix" OR "Verstappen" OR "Hamilton" OR "Norris" OR "Leclerc" OR "Piastri" OR "Sainz" OR "Alonso" OR "Russell" OR "Christian Horner" OR "Toto Wolff" OR "Adrian Newey" OR "Red Bull Racing" OR "Mercedes F1" OR "Scuderia Ferrari" OR "McLaren F1" OR "Silly Season" OR "Paddock" OR "FIA" OR "Qualifying")';
$query = urlencode($keywords);
$domains = "autosport.com,skysports.com,motorsport.com,espn.com,f1i.com,gpblog.com,racefans.net,f1.com";

$api_url = "https://newsapi.org/v2/everything?q={$query}&domains={$domains}&language=en&sortBy=publishedAt&pageSize=40&apiKey={$apiKey}";

log_message("API-Endpoint: NewsAPI.org (Strict Sport Filter)");
log_message("Geselecteerde Domeinen: " . $domains);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERAGENT, 'F1SiteScraper/1.0');
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$data = json_decode($response, true);

if ($http_code !== 200) {
    log_message("❌ API FOUT: " . ($data['message'] ?? 'Onbekende fout'));
    exit(1);
}

$articles = $data['articles'] ?? [];
log_message("✅ Artikelen gevonden: " . count($articles));

$count_saved = 0;
$count_skipped = 0;

if (count($articles) > 0) {
    $stmt = $pdo->prepare("INSERT INTO f1_nieuws (titel, artikel_url, publicatie_datum, afbeelding_url, source) VALUES (?, ?, ?, ?, ?)");

    foreach ($articles as $article) {
        $titel = $article['title'] ?? '';
        $url = $article['url'] ?? '';
        $image_url = $article['urlToImage'] ?? '';
        $source = $article['source']['name'] ?? 'F1 Official';

        // EXTRA CHECK: Bevat de titel wel echt F1 gerelateerde woorden? (Dubbele veiligheid)
        $cleanTitle = strtolower($titel);
        if (!str_contains($cleanTitle, 'f1') && !str_contains($cleanTitle, 'formula') && !str_contains($cleanTitle, 'grand prix') && !str_contains($cleanTitle, 'verstappen')) {
            continue;
        }

        if (empty($titel) || empty($url) || empty($image_url)) {
            continue; 
        }

        try {
            $stmt->execute([
                $titel, 
                $url, 
                date('Y-m-d H:i:s', strtotime($article['publishedAt'])), 
                $image_url,
                $source             
            ]);
            $count_saved++;
        } catch (\PDOException $e) {
            $count_skipped++;
        }
    }
}
log_message("--- KLAAR ---");
log_message("Nieuw: {$count_saved} | Overgeslagen: {$count_skipped}");
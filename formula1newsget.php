<?php

// --- API Gegevens (vervang met jouw RapidAPI gegevens) ---
$apiHost = "f1-latest-news.p.rapidapi.com"; // Bijv. api-formula1.p.rapidapi.com
$apiKey = "0a2c4ea3ecmsh9844ecbf51d7592p1c1076jsn52785b2db328"; // Jouw persoonlijke RapidAPI sleutel
$apiUrl = "https://" . $apiHost ; "/news/f1"; // Bijv. https://api-formula1.p.rapidapi.com/news/latest

$dbHost = 'localhost';
$dbName = 'formule1';
$dbUser = 'root';
$dbPass = '';

function fetchAndStoreNews($apiHost, $apiKey, $apiUrl, $dbHost, $dbName, $dbUser, $dbPass) {
    echo "Start met ophalen en opslaan van nieuws...\n";

    // --- 1. API Verzoek versturen met cURL ---
    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => $apiUrl,
        CURLOPT_RETURNTRANSFER => true, // Zorgt ervoor dat de respons als string terugkomt i.p.v. direct te printen
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30, // Timeout na 30 seconden
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => [
            "X-RapidAPI-Host: " . $apiHost,
            "X-RapidAPI-Key: " . $apiKey
        ],
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
        echo "cURL Fout: " . $err . "\n";
        return;
    }

    $data = json_decode($response, true); // Decodeer de JSON respons naar een associatieve array

    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "Fout bij het decoderen van JSON respons: " . json_last_error_msg() . "\n";
        echo "API Respons: " . $response . "\n";
        return;
    }

    // --- 2. Database verbinding maken met PDO ---
    try {
        $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Zorgt ervoor dat PDO exceptions gooit bij fouten
        echo "Succesvol verbonden met de database.\n";
    } catch (PDOException $e) {
        echo "Database verbindingsfout: " . $e->getMessage() . "\n";
        return;
    }

    // --- 3. Data verwerken en opslaan ---
    // Dit deel is AFHANKELIJK van de exacte structuur van de data die JOUW SPECIFIEKE API retourneert!
    // Pas de paden naar 'title', 'content', 'imageUrl' aan op basis van de JSON die je in Insomnia ziet.
    // Voorbeeld: als de data onder een 'articles' sleutel zit en elk artikel heeft 'title', 'content', 'imageUrl'
    
    // Debugging: Print de hele respons om de structuur te zien als het niet werkt
    // echo "API Data Structuur: " . print_r($data, true) . "\n";

    if (isset($data['articles']) && is_array($data['articles'])) {
        $insertStmt = $pdo->prepare("INSERT INTO news (title, news_content, image_url) VALUES (:title, :news_content, :image_url)");

        foreach ($data['articles'] as $article) {
            $title = $article['title'] ?? null;
            $newsContent = $article['content'] ?? null;
            $imageUrl = $article['imageUrl'] ?? null;

            // Zorg dat je geen lege titels of content opslaat (conform je NOT NULL constraints)
            if ($title && $newsContent) {
                try {
                    $insertStmt->execute([
                        ':title' => $title,
                        ':news_content' => $newsContent,
                        ':image_url' => $imageUrl
                    ]);
                    echo "Nieuwsbericht '" . $title . "' succesvol opgeslagen.\n";
                } catch (PDOException $e) {
                    // Controleer op duplicate entry error (bijv. als je een UNIQUE constraint op title hebt)
                    if ($e->getCode() == 23000) { // SQLSTATE voor Integrity constraint violation
                        echo "Waarschuwing: Nieuwsbericht '" . $title . "' bestaat al (duplicaat overgeslagen).\n";
                    } else {
                        echo "Fout bij opslaan van '" . $title . "': " . $e->getMessage() . "\n";
                    }
                }
            } else {
                echo "Waarschuwing: Nieuwsbericht zonder titel of content overgeslagen.\n";
            }
        }
    } else {
        echo "Geen 'articles' gevonden in de API respons of onverwachte structuur.\n";
        echo "Volledige API Respons:\n" . json_encode($data, JSON_PRETTY_PRINT) . "\n";
    }

    echo "Klaar met het proces.\n";
}

// Roep de functie aan met jouw gegevens
fetchAndStoreNews($apiHost, $apiKey, $apiUrl, $dbHost, $dbName, $dbUser, $dbPass);

?>
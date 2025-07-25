<?php
// meer-nieuws.php

// Zorg ervoor dat db_config.php correct is ingesteld en de $pdo variabele beschikbaar maakt.
require_once 'db_config.php';
/** @var PDO $pdo */ // Dit is een DocBlock voor IDE's, heeft geen invloed op de runtime.

$news = []; // Initialiseer de $news array leeg

try {
    // Haal ALLE nieuwsartikelen op uit de database, gesorteerd op datum (meest recente eerst).
    // Let op: Gebruik de correcte kolomnaam voor de ID uit je database, dit lijkt 'news_id' te zijn.
    $stmt = $pdo->query("SELECT news_id, title, news_content, image_url, keywords, source, date FROM news ORDER BY date DESC");
    $news = $stmt->fetchAll(PDO::FETCH_ASSOC); // Haal alle rijen op als associatieve arrays
} catch (\PDOException $e) {
    // Vang eventuele databasefouten op en toon een bericht.
    error_log("Fout bij het ophalen van nieuws artikelen op meer-nieuws.php: " . $e->getMessage());
    echo "<p style='color: red; text-align: center;'>Er is een probleem opgetreden bij het laden van de nieuwsartikelen. Probeer het later opnieuw.</p>";
    $news = []; // Zorg ervoor dat $news leeg blijft bij een fout
}

// Het POST-gedeelte dat je had, is hier niet nodig.
// De links op deze pagina gebruiken GET om naar news-detail.php te gaan.
// Dit blok code kan veilig worden verwijderd, tenzij je hier een andere POST-functionaliteit voor had.

$selectednews = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['news_id'])) {
    $newsid = $_POST['news_id'];                
    try {
        $stmt = $pdo->prepare("SELECT news_id, title, source FROM news WHERE news_id = :news_id");
        $stmt->bindParam(':news_id', $newsid, PDO::PARAM_INT);
        $stmt->execute();
        $selectednews = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$selectednews) {
            echo "Nieuws niet gevonden.";
        }
    } catch (\PDOException $e) {
        echo "Fout bij het ophalen van nieuwsartikelen: " . $e->getMessage();
    }
}


// Sorteren is al gedaan in de SQL-query (ORDER BY date DESC), dus usort is hier redundant.
// Mocht je op een ander criterium willen sorteren, dan kun je usort hier nog steeds gebruiken.
// usort($news, function($a, $b) {
//     return strtotime($b['date']) - strtotime($a['date']);
// });

?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Al het Nieuws - Formula 1</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="header-content container">
            <h1 class="site-title" id="sitename">FORMULA 1 SEASON 2025</h1>
            <nav class="main-nav">
                <a href="index.php" class="active">Home</a>
                <a href="kalender.php">Schedule</a>
                <a href="teams.php">Teams</a>
                <a href="drivers.php">Drivers</a>
                <a href="standings.php">Standings</a>
            </nav>
        </div>
    </header>

    <div class="back-button-section">
        <a class="back-button" href="index.php">Terug naar overzicht</a>
    </div>

    <?php if ($news): ?>
        <div class="all-news-container">
            <?php foreach ($news as $item): ?>
                <a href="news-detail.php?news_id=<?php echo htmlspecialchars($item['news_id']); ?>" class="news-item-link">
                    <article class="news-item">
                        <div class="news-image">
                            <?php if (!empty($item['image_url'])): ?>
                                <img class="all-news-img" src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>">
                            <?php else: ?>
                                <img class="all-news-img" src="placeholder.png" alt="Geen afbeelding beschikbaar" class="placeholder-image">
                            <?php endif; ?>
                        </div>
                        <div class="news-content">
                            <h4><?php echo htmlspecialchars($item['title']); ?></h4>
                            <p class="news-date"><?php echo htmlspecialchars($item['date']); ?></p>
                        </div>
                    </article>
                </a>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p style="text-align: center;">Er zijn momenteel geen nieuwsartikelen beschikbaar.</p>
    <?php endif; ?>

</body>
</html>
<?php
require_once 'db_config.php';
/** @var PDO $pdo */
try {
    $pdo = new PDO($dsn, $user, $pass, $pdoOptions);
    $stmt = $pdo->prepare("SELECT grandprix, location, race_datetime FROM circuits WHERE race_datetime > NOW() ORDER BY calendar_order ASC LIMIT 1");
    $stmt->execute();
    $nextGrandPrix = $stmt->fetch();
    if ($nextGrandPrix) {
        $targetDateTime = (new DateTime($nextGrandPrix['race_datetime']))->format(DateTime::ATOM);
    }
} catch (\PDOException $e) {
    error_log("Databasefout bij ophalen volgende Grand Prix: " . $e->getMessage());
    $nextGrandPrix = null; 
}
?>

<?php
require_once 'db_config.php';
/** @var PDO $pdo */

$news = [];
try {
    $stmt = $pdo->query("SELECT news_id, title, news_content, image_url, keywords, source, date FROM news ORDER BY date DESC");
    $news = $stmt->fetchAll();
} catch (\PDOException $e) {
    echo "Fout bij het ophalen van nieuws artikelen: " . $e->getMessage();
}
$selectednews = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['news_id'])) {
    $newsid = $_POST['news_id'];

    try {
        $stmt = $pdo->prepare("SELECT news_id, title, source FROM news WHERE news_content = :newsconten");
        $stmt->bindParam(':news_id', $newsid, PDO::PARAM_INT);
        $stmt->execute();
        $selectednews = $stmt->fetch();

        if (!$selectednews) {
            echo "Nieuws niet gevonden.";
        }
    } catch (\PDOException $e) {
        echo "Fout bij het ophalen van niewsartikelen: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formula 1 Season 2025 - Home</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Basisstyling voor de hele nieuwssectie */
.page-header-section {
    padding: 20px;
    background-color: #f9f9f9;
    border-radius: 8px;
    margin-bottom: 30px;
}

.page-heading,
.section-title {
    text-align: center;
    margin-bottom: 25px;
}

.section-title {
    font-size: 1.8em;
    border-bottom: 2px solid #eee;
    padding-bottom: 10px;
}

/* Styling voor de nieuwscontainer (om items te rangschikken) */
.news-container {
    display: grid; /* Gebruik grid voor een responsieve lay-out */
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); /* Responsieve kolommen */
    gap: 30px; /* Ruimte tussen nieuwsitems */
    justify-content: center; /* Centreer items als ze de rij niet vullen */
    padding: 20px;
}

/* Styling voor elke individuele nieuwsitemlink */
.news-item-link {
    text-decoration: none; /* Verwijder onderstreping van links */
    color: inherit; /* Erf tekstkleur van ouder */
    display: block; /* Maak het hele gebied klikbaar */
    transition: transform 0.2s ease-in-out; /* Vloeiend zweefeffect */
}

.news-item-link:hover {
    transform: translateY(-5px); /* Lift-effect bij hover */
}

/* Styling voor de inhoud van het nieuwsartikel */
.news-item {
    background-color: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    overflow: hidden; /* Zorgt ervoor dat de hoeken van de afbeelding afgerond zijn */
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08); /* Zachte schaduw */
    display: flex; /* Rangschik afbeelding en inhoud */
    flex-direction: column; /* Stapel afbeelding boven inhoud */
    height: 100%; /* Zorg ervoor dat alle items dezelfde hoogte hebben in het raster */
}

.news-image {
    width: 100%;
    height: 200px; /* Vaste hoogte voor afbeeldingen */
    overflow: hidden;
}

.news-image img {
    width: 100%;
    height: 100%;
    object-fit: cover; /* Snijd afbeeldingen netjes bij */
    display: block;
}

.news-content {
    padding: 15px;
    flex-grow: 1; /* Laat inhoud de resterende ruimte innemen */
    display: flex;
    flex-direction: column;
    justify-content: space-between; /* Duw datum naar beneden */
}

.news-content h4 {
    margin-top: 0;
    margin-bottom: 10px;
    color: #007bff; /* Een mooie blauwe kleur voor titels */
    font-size: 1.3em;
}

.news-content .news-date {
    font-size: 0.9em;
    color: #777;
    text-align: right;
    margin-top: auto; /* Duwt de datum naar de onderkant van het inhoudsgebied */
}

/* Bericht wanneer er geen nieuws beschikbaar is */
p {
    text-align: center;
    color: #666;
    padding: 20px;
}

/* Responsieve aanpassingen */
@media (max-width: 768px) {
    .news-container {
        grid-template-columns: 1fr; /* Enkele kolom op kleinere schermen */
        padding: 10px;
    }

    .news-item {
        flex-direction: row; /* Afbeelding en inhoud naast elkaar op kleinere schermen */
        height: auto;
    }

    .news-image {
        width: 150px; /* Vaste breedte voor afbeelding in rij-lay-out */
        height: 100%;
        min-height: 120px; /* Zorg ervoor dat afbeelding enige hoogte heeft */
    }

    .news-content {
        padding: 10px;
    }
}

@media (max-width: 480px) {
    .news-item {
        flex-direction: column; /* Opnieuw stapelen op zeer kleine schermen */
    }
    .news-image {
        width: 100%;
        height: 180px;
    }
}
    </style>
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

    <main class="container">
<section class="page-header-section">
    <h2 class="page-heading">NIEUWS</h2>
    <section class="race-calendar">
        <h3 class="section-title">Laatste Nieuws</h3>
        <?php

        if ($news):
            $displayed_news = array_slice($news, 0, 10);
        ?>
            <div class="news-container">
                <?php foreach ($displayed_news as $item): ?>
                    <a href="news-detail.php?news_id=<?php echo htmlspecialchars($item['news_id']); ?>" class="news-item-link">
                        <article class="news-item">
                            <div class="news-image">
                                <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>">
                            </div>
                            <div class="news-content">
                                <h4><?php echo htmlspecialchars($item['title']); ?></h4>
                                <p class="news-date"><?php echo htmlspecialchars($item['date']); ?></p>
                            </div>
                        </article>
                    </a>
                <?php endforeach; ?>
            </div>

            <?php if (count($news) > 1): // Toon de knop alleen als er meer dan 10 artikelen zijn ?>
                <div class="more-news-button-container">
                    <a href="all-news.php" class="button primary-button">Meer nieuws</a>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <p>Er zijn momenteel geen nieuwsartikelen beschikbaar.</p>
        <?php endif; ?>
    </section>
</section>
    </main>

    <footer>
        <div class="footer-content container">
            <p>&copy; 2025 Webbair. Alle rechten voorbehouden.</p>
            <div class="social-links">
                <a href="#" aria-label="Facebook">Facebook</a>
                <a href="#" aria-label="Twitter">Twitter</a>
                <a href="#" aria-label="Instagram">Instagram</a>
            </div>
        </div>
    </footer>

    <script>
        <?php if ($nextGrandPrix): ?>
        const targetDateTime = new Date('<?php echo $targetDateTime; ?>');
        const countdownElement = document.getElementById('countdown');
        function updateCountdown() {
            const now = new Date().getTime();
            const distance = targetDateTime - now;
            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);
            if (distance < 0) {
                countdownElement.innerHTML = "De race is bezig of voorbij!";
                clearInterval(countdownInterval);
            } else {
                countdownElement.innerHTML =
                    `${days}d ${hours}u ${minutes}m ${seconds}s`;
            }
        }
        updateCountdown();
        const countdownInterval = setInterval(updateCountdown, 1000);
        <?php else: ?>
        console.log("Geen volgende Grand Prix om af te tellen.");
        <?php endif; ?>
    </script>
</body>
</html>
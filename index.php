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

// Verwerk het formulier als het is ingediend
$selectednews = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['news_id'])) {
    $newsid = $_POST['news_id'];

    try {
        // Haal de details van de geselecteerde coureur op
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
            <?php if ($nextGrandPrix): ?>
            <h2 class="page-heading"><?php echo htmlspecialchars($nextGrandPrix['grandprix']); ?></h2>
            <div class="news-grid">
                    <div id="countdown" class="countdown-time">Laden...</div>
                <?php else: ?>
                    <p>Geen aankomende Grand Prix gevonden of er is een probleem met de database.</p>
                <?php endif; ?>
            </div>
        </section>

        <section class="page-header-section">
            <h2 class="page-heading">Welcome to the Formula 1 Season 2025</h2>
            <p class="page-intro">Get ready for an electrifying season of speed, strategy, and pure racing adrenaline!</p>
        </section>



        <section class="page-header-section">
            <h2 class="page-heading">NEWS</h2>
            <section class="race-calendar">
                <h3 class="section-title">Latest News</h3>
                    <?php if ($news): ?>
                        <table class="news-table">
                            <thead>
                                <tr>
                                    <th>Titel</th>
                                    <th>Bron</th>
                                    <th>Datum</th>
                                    <th>select</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($news as $item): ?>
                                    <tr>
                                        <form action="">
                                        <td><img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt=""></td>
                                        <td><?php echo htmlspecialchars($item['title']); ?></td>
                                        <td><?php echo htmlspecialchars($item['date']); ?></td>
                                        </form>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>Er zijn momenteel geen nieuwsartikelen beschikbaar.</p>
                    <?php endif; ?>
                </div>
            </div>
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
<?php
require_once 'db_config.php';
/** @var PDO $pdo */ 
$circuitsData = [];
try {
    $stmt = $pdo->query("SELECT circuit_key, grandprix, location, map_url, race_datetime, title FROM circuits ORDER BY calendar_order ASC");
    $circuitsData = $stmt->fetchAll();
} catch (\PDOException $e) {
    echo "Fout bij het ophalen van circuits: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formule 1 Kalender 2025 - Overzicht</title>
    <link rel="stylesheet" href="style2.css">
</head>
<body>
    <header>
        <div class="header-content container">
            <h1 class="site-title">FORMULA 1 SEASON 2025</h1>
            <nav class="main-nav">
                <a href="index.php">Home</a>
                <a href="kalender.php" class="active">Schedule</a>
                <a href="teams.php">Teams</a>
                <a href="drivers.php">Drivers</a>
                <a href="results.php">Results</a>
                <a href="standings.php">Standings</a>
            </nav>
        </div>
    </header>
    <main class="container">
        <section class="page-header-section">
            <h2 class="page-heading">F1 SEASON 2025 SCHEDULE</h2>
        </section>
        <section class="f1-section">
            <div class="data-card-row">
                
                    <?php if (!empty($circuitsData)): ?>
                    <?php foreach ($circuitsData as $circuit): ?>
                        <?php
                            $raceDateTime = new DateTime($circuit['race_datetime']);
                            $raceDay = $raceDateTime->format('j'); 
                            $raceYear = $raceDateTime->format('Y');
                            $raceMonthEnglish = $raceDateTime->format('F'); 
                            $displayDate = ($raceDay - 2) . ' - ' . $raceDay . ' ' . $raceMonthEnglish . ' ' . $raceYear;
                            if ($circuit['circuit_key'] === 'las_vegas') {
                                $displayDate = ($raceDay - 1) . ' - ' . $raceDay . ' ' . $raceMonthEnglish . ' ' . $raceYear . ' (Zaterdag)';
                            }
                        ?>
                        <article class="data-card">
                            <a href="circuit-details.php?key=<?php echo htmlspecialchars($circuit['circuit_key']); ?>" class="data-link">
                                <div class="race-image-container">
                                    <img src="<?php echo htmlspecialchars($circuit['map_url']); ?>" alt="Circuit <?php echo htmlspecialchars($circuit['grandprix']); ?>" class="circuit-image">
                                </div>
                                <div class="info">
                                    <h4><?php echo htmlspecialchars($circuit['grandprix']); ?></h4>
                                    <p><strong>Date:</strong> <?php echo htmlspecialchars($displayDate); ?></p>
                                    <p><strong>Location:</strong> <?php echo htmlspecialchars($circuit['location']); ?></p>
                                    <p><strong>Circuit:</strong> <?php echo htmlspecialchars($circuit['title']); ?></p>
                                </div>
                            </a>
                        </article>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-gray-600">Geen circuits gevonden in de database. Voeg circuits toe via de beheerpagina.</p>
                    <?php endif; ?>
                
            </div>
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
</body>
</html>

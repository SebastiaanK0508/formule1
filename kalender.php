<?php
require_once 'db_config.php';
/** @var PDO $pdo */
$circuitsData = [];
$availableYears = [];
$selectedYear = null;

// Haal alle unieke jaren op uit de database
try {
    $stmt = $pdo->query("SELECT DISTINCT YEAR(race_datetime) AS race_year FROM circuits ORDER BY race_year DESC");
    $availableYears = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (\PDOException $e) {
    echo "Fout bij het ophalen van jaren: " . $e->getMessage();
}

// Bepaal welk jaar geselecteerd is
if (isset($_GET['year']) && in_array($_GET['year'], $availableYears)) {
    $selectedYear = $_GET['year'];
} elseif (!empty($availableYears)) {
    $selectedYear = $availableYears[0]; // Toon het meest recente jaar als standaard
}

// Haal de data op voor het geselecteerde jaar
if ($selectedYear) {
    try {
        $stmt = $pdo->prepare("
            SELECT circuit_key, grandprix, location, map_url, race_datetime, title
            FROM circuits
            WHERE YEAR(race_datetime) = :selectedYear
            ORDER BY calendar_order ASC
        ");
        $stmt->bindParam(':selectedYear', $selectedYear, PDO::PARAM_INT);
        $stmt->execute();
        $circuitsData = $stmt->fetchAll();
    } catch (\PDOException $e) {
        echo "Fout bij het ophalen van circuits: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/x-icon" href="/afbeeldingen/logo/f1logobgrm.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formula 1 Schedule - <?php echo htmlspecialchars($selectedYear ?? 'N/A'); ?></title>
    <link rel="stylesheet" href="style2.css">
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
</head>
<body>
    <header>
        <div class="header-content container">
            <h1 class="site-title">FORMULA 1</h1>
            <button class="menu-toggle" aria-controls="main-nav-links" aria-expanded="false" aria-label="Toggle navigation">&#9776; </button>
            <nav class="main-nav" id="main-nav-links" data-visible="false">
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
            <h2 class="page-heading">F1 SEASON <?php echo htmlspecialchars($selectedYear ?? 'N/A'); ?> SCHEDULE</h2>
            <form method="GET" action="kalender.php" class="year-selector">
                <label for="year-select">Selecteer jaar:</label>
                <select name="year" id="year-select" onchange="this.form.submit()">
                    <?php foreach ($availableYears as $year): ?>
                        <option value="<?php echo htmlspecialchars($year); ?>"
                                <?php echo ($year == $selectedYear) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($year); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
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
                    <p class="text-gray-600">Geen circuits gevonden voor het jaar <?php echo htmlspecialchars($selectedYear); ?>.</p>
                <?php endif; ?>
            </div>
        </section>
    </main>
    <footer>
        <div class="footer-content container">
            <p>&copy; 2025 Webbair. Alle rechten voorbehouden.</p>
            <div class="social-links">
                <a href="#" aria-label="Facebook">Facebook</a>
                <a href="#" aria-label="Twitter">X</a>
                <a href="" aria-label="Instagram">Instagram</a>
            </div>
            <div class="social-links">
                <a href="privacy.html">Privacy Beleid</a>
                <a href="algemenevoorwaarden.html">Algemene Voorwaarden</a>
                <a href="contact.html">Contact</a>
            </div>
        </div>
    </footer>
<script src="mobiel_nav.js" defer></script>
</body>
</html>
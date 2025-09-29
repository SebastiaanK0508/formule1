<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formula 1 Season 2025 - Drivers</title>
    <link rel="icon" type="image/x-icon" href="/afbeeldingen/logo/f1logobgrm.png">
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">

    <?php
    require_once 'db_config.php';
    /** @var PDO $pdo */
    $allDrivers = []; 
    try {
        $stmt = $pdo->query("SELECT d.driver_id, d.first_name, d.last_name, d.driver_number, d.flag_url, t.team_name, t.full_team_name, t.team_id, t.team_color FROM drivers d LEFT JOIN teams t ON d.team_id = t.team_id WHERE d.is_active = TRUE ORDER BY d.driver_number ASC");        $allDrivers = $stmt->fetchAll();
    } catch (\PDOException $e) {
        error_log("Fout bij het ophalen van alle coureurs: " . $e->getMessage());
        echo "<p>Er is een fout opgetreden bij het laden van de coureurs. Probeer het later opnieuw.</p>";
    }
    $selectedDriver = null; 
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['driver_id'])) {
        $driverId = $_POST['driver_id'];
        try {
            $stmt = $pdo->query("SELECT driver_id, first_name, last_name, driver_number, team_name, t.full_team_name, flag_url, driver_color, is_active FROM drivers WHERE is_active = TRUE ORDER BY driver_number ASC");           
            $selectedDriver = $stmt->fetch();
            if (!$selectedDriver) {
                echo "<p>Coureur niet gevonden met ID: " . htmlspecialchars($driverId) . "</p>";
            }
        } catch (\PDOException $e) {
            error_log("Fout bij het ophalen van geselecteerde coureurdetails: " . $e->getMessage());
            echo "<p>Er is een fout opgetreden bij het ophalen van coureurdetails.</p>";
        }
    }
    ?>
    <link rel="stylesheet" href="style2.css">
    <style>    
    :root {
        --team-main-color: <?php echo isset($team['team_color']) && $team['team_color'] ? htmlspecialchars($team['team_color']) : 'rgb(0,0,0)'; ?>;
        }
    </style>
</head>
<body>
    <header>
        <div class="header-content container">
            <h1 class="site-title">FORMULA 1</h1>
            <button class="menu-toggle" aria-controls="main-nav-links" aria-expanded="false" aria-label="Toggle navigation">&#9776; </button>
            <nav class="main-nav" id="main-nav-links" data-visible="false">
                <a href="index.php">Home</a>
                <a href="kalender.php">Schedule</a>
                <a href="teams.php">Teams</a>
                <a href="drivers.php" class="active">Drivers</a>
                <a href="results.php">Results</a>
                <a href="standings.php">Standings</a>
            </nav>
        </div>
    </header>

    <main class="container">
        <section class="page-header-section">
            <h2 class="page-heading">DRIVERS FORMULA 1 2025</h2>
        </section>

        <section class="f1-section">
            <div class="data-card-row">
                <?php if (!empty($allDrivers)): ?>
                    <?php foreach ($allDrivers as $driver): ?>
                        <?php
                            $driverSlug = strtolower(str_replace(' ', '-', htmlspecialchars($driver['first_name'] . '-' . $driver['last_name'])));
                            $driverPageUrl = 'driver-details.php?slug=' . $driverSlug;
                            $driverColor = isset($driver['team_color']) && $driver['team_color'] ? htmlspecialchars($driver['team_color']) : '#CCCCCC';
                        ?>
                        <article class="data-card">
                            <a href="<?php echo $driverPageUrl; ?>" class="driver-link">
                                <div class="info">
                                    <h3 class="driver-name" style="padding-left: 10px; color: <?php echo htmlspecialchars($driver['team_color']); ?>;">
                                        <?php echo htmlspecialchars($driver['first_name'] . ' ' . $driver['last_name']); ?>
                                    </h3>
                                    <div class="driver-details">
                                        <p><strong>Team:</strong> <?php echo htmlspecialchars($driver['full_team_name']); ?></p>
                                        <p><strong>Number:</strong> #<?php echo htmlspecialchars($driver['driver_number']); ?></p>
                                    </div>
                                </div>
                            </a>
                        </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Geen coureurs beschikbaar om weer te geven.</p>
                <?php endif; ?>
                <div>
                    <a href="all_drivers.php"><button class="all_drivers_button">All drivers ever</button></a>
                </div>
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
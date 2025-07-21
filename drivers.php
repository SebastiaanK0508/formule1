<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formula 1 Season 2025 - Drivers</title>
    <?php
    require_once 'db_config.php';
    /** @var PDO $pdo */
    $allDrivers = []; 
    try {
    $stmt = $pdo->query("SELECT driver_id, first_name, last_name, driver_number, team_name, flag_url, driver_color, is_active FROM drivers WHERE is_active = TRUE ORDER BY driver_number ASC");
        $allDrivers = $stmt->fetchAll();
    } catch (\PDOException $e) {
        error_log("Fout bij het ophalen van alle coureurs: " . $e->getMessage());
        echo "<p>Er is een fout opgetreden bij het laden van de coureurs. Probeer het later opnieuw.</p>";
    }
    $selectedDriver = null; 
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['driver_id'])) {
        $driverId = $_POST['driver_id'];

        try {
            $stmt = $pdo->prepare("SELECT first_name, last_name, driver_number, team_name, flag_url, driver_color FROM drivers WHERE driver_id = :driver_id");
            $stmt->bindParam(':driver_id', $driverId, PDO::PARAM_INT);
            $stmt->execute();
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
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="header-content container">
            <h1 class="site-title">FORMULA 1 SEASON 2025</h1>
            <nav class="main-nav">
                <a href="index.php">Home</a>
                <a href="kalender.html">Schedule</a>
                <a href="teams.php">Teams</a>
                <a href="drivers.php" class="active">Drivers</a>
                <a href="standings.html">Standings</a>
            </nav>
        </div>
    </header>

    <main class="container">
        <section class="page-header-section">
            <h2 class="page-heading">DRIVERS FORMULA 1 2025</h2>
        </section>

        <section class="race-calandar">
            <div class="race-grid">

<?php
// Loop door ALLE opgehaalde coureurs en genereer dynamisch de HTML
if (!empty($allDrivers)) {
    foreach ($allDrivers as $driver) {
        // Zorg ervoor dat alle benodigde velden bestaan in je database en query.
        // Een fallback voor kleur als deze niet in de DB staat
        $driverColor = isset($driver['driver_color']) && $driver['driver_color'] ? htmlspecialchars($driver['driver_color']) : 'rgb(0,0,0)'; // Standaard zwart

        // Dynamische link naar de generieke driver.php pagina
        // Pass de 'slug' (bijv. max-verstappen) mee als een GET-parameter
        $driverSlug = strtolower(str_replace(' ', '-', htmlspecialchars($driver['first_name'] . '-' . $driver['last_name'])));
        $driverPageUrl = 'driver-details.php?slug=' . $driverSlug; // Changed to driver.php

        echo '<a href="' . $driverPageUrl . '" class="race-card" style="--driver-color: ' . $driverColor . ';">';
        echo '    <div class="driver-header">';
        echo '        <h2 class="driver-name">' . htmlspecialchars($driver['first_name']) . ' ' . htmlspecialchars($driver['last_name']) . '</h2>';
        echo '    </div>';
        echo '    <div class="driver-info">';
        echo '        <p class="driver-team">' . htmlspecialchars($driver['team_name']) . '</p>';
        echo '        <p class="driver-number">#' . htmlspecialchars($driver['driver_number']) . '</p>';
        echo '    </div>';
        echo '</a>';
    }
} else {
    echo "<p>Geen coureurs beschikbaar om weer te geven.</p>";
}
?>

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
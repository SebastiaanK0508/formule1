<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formula 1 Season 2025 - Drivers</title>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">

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
    <link rel="stylesheet" href="style2.css">
</head>
<body>
    <header>
        <div class="header-content container">
            <h1 class="site-title">FORMULA 1 SEASON 2025</h1>
            <nav class="main-nav">
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
                            $driverColor = isset($driver['driver_color']) && $driver['driver_color'] ? htmlspecialchars($driver['driver_color']) : '#CCCCCC'; // Standaard grijs
                        ?>
                        <article class="data-card">
                            <a href="<?php echo $driverPageUrl; ?>" class="driver-link">
                                <div class="info">
                                    <h3 class="driver-name" style="padding-left: 10px;">
                                        <?php echo htmlspecialchars($driver['first_name'] . ' ' . $driver['last_name']); ?>
                                    </h3>
                                    <div class="driver-details">
                                        <p><strong>Team:</strong> <?php echo htmlspecialchars($driver['team_name']); ?></p>
                                        <p><strong>Number:</strong> #<?php echo htmlspecialchars($driver['driver_number']); ?></p>
                                    </div>
                                </div>
                            </a>
                        </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Geen coureurs beschikbaar om weer te geven.</p>
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
</body>
</html>
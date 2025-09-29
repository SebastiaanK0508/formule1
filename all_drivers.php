<?php
// Pad naar het JSON-bestand
$jsonFile = 'achterkant/aanpassing/api-koppelingen/json/drivers.json';
$allDrivers = [];
if (file_exists($jsonFile)) {
    $jsonData = file_get_contents($jsonFile);
    $allDrivers = json_decode($jsonData, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("Fout bij het decoderen van JSON: " . json_last_error_msg());
        $allDrivers = [];
    }
} else {
    error_log("JSON-bestand niet gevonden: " . $jsonFile);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style2.css">
    <link rel="icon" type="image/x-icon" href="/afbeeldingen/logo/f1logobgrm.png">
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <title>All Drivers</title>
</head>
<body>
    <header>
        <div class="header-content container">
            <h1 class="site-title">FORMULA 1</h1>
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
            <h2 class="page-heading">ALL DRIVERS FORMULA 1</h2>
        </section>

        <section class="driver-list-section">
            <div class="filter-controls">
                <input type="text" id="searchInput" placeholder="Zoek op naam...">
                <select id="sortSelect">
                    <option value="az">Naam (A-Z)</option>
                    <option value="za">Naam (Z-A)</option>
                    <option value="oldest">Geboortedatum (Oudst eerst)</option>
                    <option value="youngest">Geboortedatum (Jongst eerst)</option>
                </select>
            </div>
            <ul class="driver-list" id="driverList">
                <?php if (!empty($allDrivers)): ?>
                    <?php foreach ($allDrivers as $driver): ?>
                        <li class="driver-item" data-name="<?php echo htmlspecialchars($driver['firstName'] . ' ' . $driver['lastName']); ?>" data-dob="<?php echo htmlspecialchars($driver['dateOfBirth']); ?>">
                            <span class="driver-name-all">
                                <?php echo htmlspecialchars($driver['firstName'] . ' ' . $driver['lastName']); ?>
                            </span>
                            <div class="driver-details-list">
                                <span class="driver-info"><strong class="driver-info-strong">Date of Birth:</strong> <?php echo htmlspecialchars($driver['dateOfBirth']); ?></span>
                                <span class="driver-info"><strong class="driver-info-strong">Place of Birth:</strong> <?php echo htmlspecialchars($driver['placeOfBirth']),', ', htmlspecialchars($driver['countryOfBirthCountryId']); ?></span>
                                <span class="driver-info"><strong class="driver-info-strong">nationality:</strong> <?php echo htmlspecialchars($driver['nationalityCountryId']); ?></span>
                            </div>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Geen coureurs beschikbaar om weer te geven.</p>
                <?php endif; ?>
            </ul>
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
    <script src="all_drivers_filter.js"></script>
</body>
</html>
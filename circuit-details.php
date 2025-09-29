<?php
require_once 'db_config.php';
/** @var PDO $pdo */
$circuitKey = isset($_GET['key']) ? htmlspecialchars($_GET['key']) : null;
if ($circuitKey) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM circuits WHERE circuit_key = :circuit_key");
        $stmt->bindParam(':circuit_key', $circuitKey);
        $stmt->execute();
        $circuitDetails = $stmt->fetch();
        if (!$circuitDetails) {
            $message = "<p class='error-message'>Circuit met sleutel " . htmlspecialchars($circuitKey) . " niet gevonden.</p>";
        }
    } catch (\PDOException $e) {
        $message = "<p class='error-message'>Fout bij het ophalen van circuitdetails: " . $e->getMessage() . "</p>";
    }
} else {
    $message = "<p class='error-message'>Geen geldige circuit-sleutel opgegeven.</p>";
}
if (!is_array($circuitDetails)) {
    $circuitDetails = [];
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Details Circuit: <?php echo htmlspecialchars($circuitDetails['grandprix'] ?? 'Onbekend'); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style2.css">
    <link rel="icon" type="image/x-icon" href="/afbeeldingen/logo/f1logobgrm.png">
    <style>
        .back-link-container {
            text-align: center;
            margin-top: 40px;
        }
        .back-link {
            display: inline-block;
            padding: 12px 25px;
            background-color: #ff0000ff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 1.1em;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }
        .back-link:hover {
            background-color: #a10000ff;
            transform: translateY(-2px);
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
                <a href="kalender.php" class="active">Schedule</a>
                <a href="teams.php">Teams</a>
                <a href="drivers.php">Drivers</a>
                <a href="results.php">Results</a>
                <a href="standings.php">Standings</a>
            </nav>
        </div>
    </header>
    <main class="container">
        <div class="">
            <?php if ($circuitDetails && $circuitKey): ?>
                <div class="page-header-section">
                    <h1 class="page-heading"><?php echo htmlspecialchars($circuitDetails['grandprix'] ?? 'Onbekend'); ?></h1>
                    <p><?php echo htmlspecialchars($circuitDetails['location'] ?? 'Locatie onbekend'); ?></p>
                </div>
                <section class="f1-section-pos">
                    <div class="f1-section">
                        <div class="container">
                            <h3>Algemene Informatie</h3>
                            <div class="detail-item">
                                <strong>Circuit Name:</strong>
                                <span><?php echo htmlspecialchars($circuitDetails['title'] ?? 'N.v.t.'); ?></span>
                            </div>
                            <div class="detail-item">
                                <strong>First Year GP:</strong>
                                <span><?php echo htmlspecialchars($circuitDetails['first_gp_year'] ?? 'N.v.t.'); ?></span>
                            </div>

                            <div class="detail-item">
                                <strong>Race Date & Time:</strong>
                                <span><?php echo htmlspecialchars($circuitDetails['race_datetime'] ? date('d-m-Y H:i', strtotime($circuitDetails['race_datetime'])) : 'N.v.t.'); ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="f1-section">
                        <div class="country-flag-circuit-position">
                            <img class="country-flag" src="<?php echo htmlspecialchars($circuitDetails['country_flag_url'] ?? 'N.v.t.'); ?>" alt="Country_Flag">
                        </div>
                    </div>
                    <div class="f1-section">
                        <div class="container">
                            <h3>Circuit Details</h3>
                            <div class="detail-item">
                                <strong>Laps:</strong>
                                <span><?php echo htmlspecialchars($circuitDetails['lap_count'] ?? 'N.v.t.'); ?></span>
                            </div>
                            <div class="detail-item">
                                <strong>Circuit Lenght:</strong>
                                <span><?php echo htmlspecialchars(number_format($circuitDetails['circuit_length_km'] ?? 0, 3, ',', '.')) . ' km'; ?></span>
                            </div>
                            <div class="detail-item">
                                <strong>Race Distance:</strong>
                                <span><?php echo htmlspecialchars(number_format($circuitDetails['race_distance_km'] ?? 0, 3, ',', '.')) . ' km'; ?></span>
                            </div>
                            <div class="detail-item">
                                <strong>Lap Record:</strong>
                                <span><?php echo htmlspecialchars($circuitDetails['lap_record'] ?? 'N.v.t.'); ?></span>
                            </div>
                        </div>
                    </div>
                    
                </section>
                <?php if (!empty($circuitDetails['map_url'])): ?>
                    <div class="circuit-image-display">
                        <img class="circuit-main-map" src="<?php echo htmlspecialchars($circuitDetails['map_url']); ?>" alt="Kaart van <?php echo htmlspecialchars($circuitDetails['grandprix']); ?>">
                    </div>
                <?php endif; ?>
                <div class="back-link-container">
                    <a href="kalender.php" class="back-link">Back to Schedule</a>
                </div>
                <?php else: ?>
                    <p class="message error-message">De details van dit circuit konden niet worden geladen of het circuit bestaat niet.</p>
                    <div class="back-link-container">
                        <a href="kalender.php" class="back-link">Terug naar Kalender</a>
                    </div>
            <?php endif; ?>
        </div>
    </main>
    <footer>
        <div class="footer-content container">
            <p>&copy; 2025 <a style="color: white;" target=_blank href="https://webbair.nl">Webbair</a>. Alle rechten voorbehouden.</p>
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

<?php
// Database configuratie
$host = "localhost";
$db = "formule1";
$user = "root";
$pass = "root";
$charset = "utf8mb4";

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

$pdo = null;
$circuitDetails = null; // Voor de details van het circuit
$message = ''; // Voor foutmeldingen

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("Verbindingsfout: " . $e->getMessage());
}

// Haal de circuit_key op uit de URL (GET parameter)
$circuitKey = isset($_GET['key']) ? htmlspecialchars($_GET['key']) : null;

// Haal de circuit details op voor weergave
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

// Zorg ervoor dat $circuitDetails een array is, zelfs als er geen data is
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
    <!-- Google Fonts: Oswald voor koppen, Roboto voor tekst -->
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css"> <!-- Behoud je algemene style.css -->
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
        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-weight: bold;
            text-align: center;
        }
        .error-message {
            background-color: #000000ff;
            color: #721c24;
            border: 1px solid #000000ff;
        }
    </style>
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
                <a href="standings.php">Standings</a>
            </nav>
        </div>
    </header>
    <main class="container">
        <div class="">
            <?php echo $message; // Toon foutmeldingen ?>

            <?php if ($circuitDetails && $circuitKey): ?>
                <div class="page-header-section">
                    <h1 class="page-heading"><?php echo htmlspecialchars($circuitDetails['grandprix'] ?? 'Onbekend'); ?></h1>
                    <p><?php echo htmlspecialchars($circuitDetails['location'] ?? 'Locatie onbekend'); ?></p>
                </div>


                <div class="details-section">
                    <div class="detail-card">
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

                    <div class="detail-card">
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

                
                <?php if (!empty($circuitDetails['map_url'])): ?>
                    <div class="circuit-image-display">
                        <img class="circuit-main-map" src="<?php echo htmlspecialchars($circuitDetails['map_url']); ?>" alt="Kaart van <?php echo htmlspecialchars($circuitDetails['grandprix']); ?>">
                    </div>
                <?php endif; ?>

                <?php if (!empty($circuitDetails['description'])): ?>
                    <div class="description-section">
                        <h3>Track Description</h3>
                        <p><?php echo nl2br(htmlspecialchars($circuitDetails['description'])); ?></p>
                    </div>
                <?php endif; ?>

                <?php if (!empty($circuitDetails['highlights'])): ?>
                    <div class="highlights-section">
                        <h3>Highlights</h3>
                        <p><?php echo nl2br(htmlspecialchars($circuitDetails['highlights'])); ?></p>
                    </div>
                <?php endif; ?>

                <div class="back-link-container">
                    <a href="kalender.php" class="back-link">Terug naar Kalender</a>
                </div>

            <?php else: ?>
                <p class="message error-message">De details van dit circuit konden niet worden geladen of het circuit bestaat niet.</p>
                <div class="back-link-container">
                    <a href="kalender.php" class="back-link">Terug naar Kalender</a>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>

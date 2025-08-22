<?php

require_once 'db_config.php';
/** @var PDO $pdo */
$driverSlug = isset($_GET['slug']) ? $_GET['slug'] : '';

if (empty($driverSlug)) {
    header('Location: index.php');
    exit;
}
$nameParts = explode('-', $driverSlug);
$firstName = isset($nameParts[0]) ? ucfirst($nameParts[0]) : '';
$lastName = isset($nameParts[1]) ? ucfirst($nameParts[1]) : '';
try {
    $stmt = $pdo->prepare("
        SELECT
            d.*,
            t.team_color,
            t.team_name
        FROM
            drivers d
        JOIN
            teams t ON d.team_id = t.team_id
        WHERE
            LOWER(REPLACE(CONCAT(d.first_name, '-', d.last_name), ' ', '')) = :slug
    ");
    $stmt->bindParam(':slug', $driverSlug);
    $stmt->execute();
    $driver = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$driver) {
        echo "<h1>Coureur niet gevonden!</h1>";
        echo "<p>De coureur die u zoekt, is helaas niet gevonden.</p>";
        echo "<p><a href='index.php'>Terug naar overzicht</a></p>";
        exit;
    }
} catch (PDOException $e) {
    echo "Databasefout: " . $e->getMessage();
    exit;
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($driver['first_name'] . ' ' . $driver['last_name']); ?> | Driver Details</title>
    <link rel="stylesheet" href="style2.css">
    <link rel="icon" type="image/x-icon" href="/afbeeldingen/logo/f1logobgrm.png">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Oswald:wght@400;600;700&display=swap" rel="stylesheet">
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
        <div class="page-header-section">
            <h1 class="page-heading"><?php echo htmlspecialchars($driver['first_name'] . ' ' . $driver['last_name']); ?></h1>
        </div>

        <div class="driver-details-grid">
            <div class="driver-image-container">
                <?php if (!empty($driver['image'])): ?>
                    <img src="<?php echo htmlspecialchars($driver['image']); ?>" alt="<?php echo htmlspecialchars($driver['first_name'] . ' ' . $driver['last_name']); ?>" class="driver-image-details">
                <?php endif; ?>
            </div>
            
            <div class="driver-info-container">
                <dl class="driver-details-list">
                    <dt>Team:</dt>
                    <dd><?php echo htmlspecialchars($driver['team_name']); ?></dd>

                    <dt>Driver Number: </dt>
                    <dd>#<?php echo htmlspecialchars($driver['driver_number']); ?></dd>

                    <dt>nationality: </dt>
                    <dd>
                        <?php if (!empty($driver['flag_url'])): ?>
                            <img src="<?php echo htmlspecialchars($driver['flag_url']); ?>" alt="Vlag" class="flag-icon">
                        <?php endif; ?>
                        <?php echo htmlspecialchars($driver['nationality']); ?>
                    </dd>

                    <?php if (!empty($driver['date_of_birth'])): ?>
                        <dt>Date of Birth: </dt>
                        <dd><?php echo htmlspecialchars(date('d-m-Y', strtotime($driver['date_of_birth']))); ?></dd>
                    <?php endif; ?>
                    
                    <?php if (isset($driver['career_points'])): ?>
                        <dt>Career Points: </dt>
                        <dd><?php echo htmlspecialchars(number_format($driver['career_points'], 1)); ?></dd>
                    <?php endif; ?>
                    
                    <?php if (isset($driver['championships_won'])): ?>
                        <dt>championships won: </dt>
                        <dd><?php echo htmlspecialchars($driver['championships_won']); ?></dd>
                    <?php endif; ?>
                </dl>
            </div>
        </div>

        <?php if (!empty($driver['description'])): ?>
            <div class="driver-description">
                <h2>Over de coureur</h2>
                <p><?php echo nl2br(htmlspecialchars($driver['description'])); ?></p>
            </div>
        <?php endif; ?>

        <div style="clear: both;"></div>
        <a href="drivers.php" class="back-link">← Terug naar het coureuroverzicht</a>
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
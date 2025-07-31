<?php

require_once 'db_config.php';
/** @var PDO $pdo */

// Get the driver slug from the URL
$driverSlug = isset($_GET['slug']) ? $_GET['slug'] : '';

// Validate and sanitize the slug
if (empty($driverSlug)) {
    // Redirect or show an error if no slug is provided
    header('Location: index.php'); // Or a 404 page
    exit;
}

// Convert slug back to first_name and last_name for querying
// This assumes your slug format is "firstname-lastname"
$nameParts = explode('-', $driverSlug);
$firstName = isset($nameParts[0]) ? ucfirst($nameParts[0]) : '';
$lastName = isset($nameParts[1]) ? ucfirst($nameParts[1]) : '';

// Prepare and execute a database query to get the specific driver's details
try {
    // We gebruiken een JOIN om de team_color en mogelijk andere teamdetails op te halen
    $stmt = $pdo->prepare("
        SELECT
            d.*, -- Selecteer alle kolommen van de drivers tabel
            t.team_color, -- Selecteer de team_color van de teams tabel
            t.team_name -- Zorg ervoor dat team_name ook beschikbaar blijft via de join
            -- Voeg hier eventueel t.logo_url of andere teamvelden toe als je die wilt tonen
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
        // Driver not found
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
    <title>Driver Details</title>
    <link rel="stylesheet" href="style2.css">
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
    <div class="details-container">
        <h1 class="drivername"><?php echo htmlspecialchars($driver['first_name'] . ' ' . $driver['last_name']); ?></h1>

        <?php if (!empty($driver['image'])): ?>
            <img src="<?php echo htmlspecialchars($driver['image']); ?>" alt="<?php echo htmlspecialchars($driver['first_name'] . ' ' . $driver['last_name']); ?>" class="driver-image-details">
        <?php endif; ?>

        <div class="driver-details">
            <p class="driver-detail-item"><strong>Team:</strong> <?php echo htmlspecialchars($driver['team_name']); ?></p>
            <p class="driver-detail-item"><strong>Coureur Nummer:</strong> #<?php echo htmlspecialchars($driver['driver_number']); ?></p>
            <p class="driver-detail-item"><strong>Nationaliteit:</strong>
                <?php if (!empty($driver['flag_url'])): ?>
                    <img src="<?php echo htmlspecialchars($driver['flag_url']); ?>" alt="Vlag" style="height: 20px; vertical-align: middle; margin-right: 5px;">
                <?php endif; ?>
                <?php echo htmlspecialchars($driver['nationality']); ?>
            </p>
            <?php if (!empty($driver['date_of_birth'])): ?>
                <p class="driver-detail-item"><strong>Geboortedatum:</strong> <?php echo htmlspecialchars(date('d-m-Y', strtotime($driver['date_of_birth']))); ?></p>
            <?php endif; ?>
            <?php if (!empty($driver['place_of_birth'])): ?>
                <p class="driver-detail-item"><strong>Geboorteplaats:</strong> <?php echo htmlspecialchars($driver['place_of_birth']); ?></p>
            <?php endif; ?>

            <?php if (isset($driver['championships_won'])): ?>
                <p class="driver-detail-item"><strong>Kampioenschappen gewonnen:</strong> <?php echo htmlspecialchars($driver['championships_won']); ?></p>
            <?php endif; ?>
            <?php if (isset($driver['career_points'])): ?>
                <p class="driver-detail-item"><strong>Carrière Punten:</strong> <?php echo htmlspecialchars(number_format($driver['career_points'], 1)); ?></p>
            <?php endif; ?>
            <?php if (isset($driver['is_active'])): ?>
                <p class="driver-detail-item"><strong>Status:</strong> <?php echo $driver['is_active'] ? 'Actief' : 'Inactief'; ?></p>
            <?php endif; ?>
            <?php if (!empty($driver['description'])): ?>
                <p class="driver-detail-item"><strong>Beschrijving:</strong> <?php echo nl2br(htmlspecialchars($driver['description'])); ?></p>
            <?php endif; ?>
        </div>

        <div style="clear: both;"></div> <a href="drivers.php" class="back-link">← Terug naar het coureuroverzicht</a>
    </div>
</body>
</html>
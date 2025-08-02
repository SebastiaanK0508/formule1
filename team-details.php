<?php

require_once 'db_config.php';
/** @var PDO $pdo */

// Get the team ID from the URL
$teamId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Validate and sanitize the ID
if ($teamId === 0) {
    // Redirect or show an error if no valid ID is provided
    header('Location: teams.php'); // Redirect to the teams overview page
    exit;
}

// Prepare and execute a database query to get the specific team's details
try {
    $stmt = $pdo->prepare("
        SELECT *
        FROM teams
        WHERE team_id = :id
    ");
    $stmt->bindParam(':id', $teamId, PDO::PARAM_INT);
    $stmt->execute();
    $team = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$team) {
        // Team not found
        echo "<h1>Team niet gevonden!</h1>";
        echo "<p>Het team dat u zoekt, is helaas niet gevonden.</p>";
        echo "<p><a href='teams.php'>Terug naar overzicht</a></p>";
        exit;
    }
} catch (PDOException $e) {
    echo "Databasefout: " . $e->getMessage();
    exit;
}

// Fetch drivers for this team
$teamDrivers = [];
if ($team) {
    // The teamId is already available, so we can use it directly
    try {
        $stmtDrivers = $pdo->prepare("
            SELECT
                driver_id, first_name, last_name, driver_number, image
            FROM
                drivers
            WHERE
                team_id = :team_id
            ORDER BY
                driver_number ASC
        ");
        $stmtDrivers->bindParam(':team_id', $teamId, PDO::PARAM_INT);
        $stmtDrivers->execute();
        $teamDrivers = $stmtDrivers->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Fout bij het ophalen van coureurs voor team: " . $e->getMessage());
        // Optionally display a user-friendly message on the page if needed
    }
}

?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($team['full_team_name']); ?></title>
    <link rel="stylesheet" href="style2.css">
    <style>
        :root {
            --team-main-color: <?php echo isset($team['team_color']) && $team['team_color'] ? htmlspecialchars($team['team_color']) : 'rgb(0,0,0)'; ?>;
        }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Oswald:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <header>
        <div class="header-content container">
            <h1 class="site-title">FORMULA 1 SEASON 2025</h1>
            <nav class="main-nav">
                <a href="index.php">Home</a>
                <a href="kalender.php">Schedule</a>
                <a href="teams.php" class="active">Teams</a>
                <a href="drivers.php">Drivers</a>
                <a href="results.php">Results</a>
                <a href="standings.php">Standings</a>
            </nav>
        </div>
    </header>
    <div class="details-container">
        <h1 class="team-name-heading"><?php echo htmlspecialchars($team['full_team_name']); ?></h1>

        <?php if (!empty($team['logo_url'])): ?>
            <img src="<?php echo htmlspecialchars($team['logo_url']); ?>" alt="<?php echo htmlspecialchars($team['full_team_name']); ?> Logo" class="team-logo-details">
        <?php endif; ?>

        <div class="team-details">
            <p class="team-detail-item"><strong>Basislocatie:</strong> <?php echo htmlspecialchars($team['base_location']); ?></p>
            <p class="team-detail-item"><strong>Teamleider:</strong> <?php echo htmlspecialchars($team['team_principal']); ?></p>
            <p class="team-detail-item"><strong>Technisch directeur:</strong> <?php echo htmlspecialchars($team['technical_director']); ?></p>
            <p class="team-detail-item"><strong>Chassis:</strong> <?php echo htmlspecialchars($team['chassis']); ?></p>
            <p class="team-detail-item"><strong>Motorleverancier:</strong> <?php echo htmlspecialchars($team['current_engine_supplier']); ?></p>
            <?php if (isset($team['championships_won'])): ?>
                <p class="team-detail-item"><strong>Constructeurskampioenschappen:</strong> <?php echo htmlspecialchars($team['championships_won']); ?></p>
            <?php endif; ?>
            <?php if (isset($team['total_victories'])): ?>
                <p class="team-detail-item"><strong>Totaal aantal overwinningen:</strong> <?php echo htmlspecialchars($team['total_victories']); ?></p>
            <?php endif; ?>
            <?php if (isset($team['is_active'])): ?>
                <p class="team-detail-item"><strong>Status:</strong> <?php echo $team['is_active'] ? 'Actief' : 'Inactief'; ?></p>
            <?php endif; ?>
            <?php if (!empty($team['description'])): ?>
                <p class="team-detail-item"><strong>Beschrijving:</strong> <?php echo nl2br(htmlspecialchars($team['description'])); ?></p>
            <?php endif; ?>
        </div>

        <div class="team-drivers-section">
            <h2>Coureurs van dit team:</h2>
            <?php if (!empty($teamDrivers)): ?>
                <div class="drivers-grid">
                    <?php foreach ($teamDrivers as $driver):
                        // Construct the slug for the driver details page
                        $driverSlug = strtolower(str_replace(' ', '-', $driver['first_name'] . '-' . $driver['last_name']));
                        $driverPageUrl = 'driver-details.php?slug=' . $driverSlug;
                    ?>
                        <a href="<?php echo htmlspecialchars($driverPageUrl); ?>" class="driver-card">
                            <?php if (!empty($driver['image'])): ?>
                                <img src="<?php echo htmlspecialchars($driver['image']); ?>" alt="<?php echo htmlspecialchars($driver['first_name'] . ' ' . $driver['last_name']); ?>">
                            <?php endif; ?>
                            <div>
                                <p><?php echo htmlspecialchars($driver['first_name'] . ' ' . $driver['last_name']); ?></p>
                                <p><?php echo htmlspecialchars($driver['driver_number']); ?></p>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p style="color: #a0a0a0; text-align: center;">Geen coureurs gevonden voor dit team.</p>
            <?php endif; ?>
        </div>

        <div style="clear: both;"></div>
        <a href="teams.php" class="back-link">← Terug naar het teamoverzicht</a>
    </div>
</body>
</html>
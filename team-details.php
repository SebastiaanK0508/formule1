<?php

require_once 'db_config.php';
/** @var PDO $pdo */
$teamId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($teamId === 0) {
    header('Location: teams.php'); 
    exit;
}
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
        echo "<h1>Team niet gevonden!</h1>";
        echo "<p>Het team dat u zoekt, is helaas niet gevonden.</p>";
        echo "<p><a href='teams.php'>Terug naar overzicht</a></p>";
        exit;
    }
} catch (PDOException $e) {
    echo "Databasefout: " . $e->getMessage();
    exit;
}

$teamDrivers = [];
if ($team) {
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

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .page-heading {
            color: var(--team-main-color);
            border-bottom: 2px solid var(--team-main-color);
        }

        /* .f1-section-teams {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 40px;
            background-color: #1a1a1a;
            padding: 30px;
            border-radius: 8px;
        } */

        .f1-section-teams .info {
            flex-basis: 60%;
        }

        .f1-section-teams .team-logo-details {
            flex-basis: 40%;
            max-width: 100%;
            height: auto;
            object-fit: contain;
        }

        .team-detail-item {
            margin: 10px 0;
        }

        .team-detail-item strong {
            color: var(--team-main-color); /* Maakt de labels in de teamkleur */
        }
        
        /* Coureurs sectie */
        .f1-section-drivers-teams {
            margin-top: 40px;
        }

        .f1-section-drivers-teams h2 {
            font-family: 'Oswald', sans-serif;
            font-size: 2rem;
            color: var(--team-main-color);
            text-align: center;
            margin-bottom: 20px;
        }
        
        .drivers-grid {
            display: flex; /* Gebruik flexbox voor horizontale uitlijning */
            justify-content: center; /* Centreer de kaarten in de container */
            gap: 40px; /* Ruimte tussen de twee kaarten */
            flex-wrap: wrap; /* Zorgt ervoor dat ze onder elkaar komen op kleine schermen */
        }

        .back-link {
            display: inline-block;
            margin-top: 40px;
            color: white;
            background-color: var(--team-main-color);
            text-decoration: none;
            font-weight: bold;
            transition: color 0.3s ease;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        .main > .back-link {
            display: block;
            text-align: center;
            margin-top: 40px;
        }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
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
    <main class="container">
        <div class="page-header-section">
            <h1 class="page-heading"><?php echo htmlspecialchars($team['full_team_name']); ?></h1>
        </div>

        <section class="f1-section-teams">
            <div class="info">
                <p class="team-detail-item"><strong>Base Location:</strong> <?php echo htmlspecialchars($team['base_location']); ?></p>
                <p class="team-detail-item"><strong>Team Principal:</strong> <?php echo htmlspecialchars($team['team_principal']); ?></p>
                <p class="team-detail-item"><strong>Techical Director:</strong> <?php echo htmlspecialchars($team['technical_director']); ?></p>
                <p class="team-detail-item"><strong>Chassis:</strong> <?php echo htmlspecialchars($team['chassis']); ?></p>
                <p class="team-detail-item"><strong>Engine Supplier:</strong> <?php echo htmlspecialchars($team['current_engine_supplier']); ?></p>
                <?php if (isset($team['championships_won'])): ?>
                    <p class="team-detail-item"><strong>Constructor championships won:</strong> <?php echo htmlspecialchars($team['championships_won']); ?></p>
                <?php endif; ?>
                <?php if (isset($team['total_victories'])): ?>
                    <p class="team-detail-item"><strong>X-Wins:</strong> <?php echo htmlspecialchars($team['total_victories']); ?></p>
                <?php endif; ?>
                <?php if (isset($team['is_active'])): ?>
                    <p class="team-detail-item"><strong>Status:</strong> <?php echo $team['is_active'] ? 'Actief' : 'Inactief'; ?></p>
                <?php endif; ?>
                <?php if (!empty($team['description'])): ?>
                    <p class="team-detail-item"><strong>description:</strong> <?php echo nl2br(htmlspecialchars($team['description'])); ?></p>
                <?php endif; ?>
            </div>
            <div class="">
                <?php if (!empty($team['logo_url'])): ?>
                    <img class="team-logo" src="<?php echo htmlspecialchars($team['logo_url']); ?>" alt="<?php echo htmlspecialchars($team['full_team_name']); ?> Logo" class="team-logo-details">
                <?php endif; ?>
            </div>
        </section>
    <div class="f1-section-drivers-teams">
    <h2>Drivers:</h2>
    <?php if (!empty($teamDrivers)): ?>
        <div class="drivers-grid">
            <?php foreach ($teamDrivers as $driver):
                $driverSlug = strtolower(str_replace(' ', '-', $driver['first_name'] . '-' . $driver['last_name']));
                $driverPageUrl = 'driver-details.php?slug=' . $driverSlug;?>

                <a class="driver-card" href="<?php echo htmlspecialchars($driverPageUrl); ?>">
                    <?php if (!empty($driver['image'])): ?>
                        <img src="<?php echo htmlspecialchars($driver['image']); ?>" alt="<?php echo htmlspecialchars($driver['first_name'] . ' ' . $driver['last_name']); ?>">
                    <?php endif; ?>
                    <div class="driver-info">
                        <p class="driver-name"><?php echo htmlspecialchars($driver['first_name'] . ' ' . $driver['last_name']); ?></p>
                        <p class="driver-number"><?php echo htmlspecialchars($driver['driver_number']); ?></p>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
            <?php else: ?>
                <p style="color: #a0a0a0; text-align: center;">Geen coureurs gevonden voor dit team.</p>
            <?php endif; ?>
        </div>
    
    </main>
    <footer>
        <div class="footer-content container">
            <p>&copy; 2025 Webbair. Alle rechten voorbehouden.</p>
            <div class="social-links">
                <a href="#" aria-label="Facebook">Facebook</a>
                <a href="#" aria-label="Twitter">X</a>
                <a href="www.webbair.online" aria-label="Instagram">Instagram</a>
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
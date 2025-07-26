<?php

require_once 'db_config.php';
/** @var PDO $pdo */

// Get the team slug from the URL
$teamSlug = isset($_GET['slug']) ? $_GET['slug'] : '';

// Validate and sanitize the slug
if (empty($teamSlug)) {
    // Redirect or show an error if no slug is provided
    header('Location: teams.php'); // Redirect to the teams overview page
    exit;
}

// Prepare and execute a database query to get the specific team's details
try {
    // Corrected: Use distinct named parameters for each occurrence in the query
    $stmt = $pdo->prepare("
        SELECT
            *
        FROM
            teams
        WHERE
            LOWER(REPLACE(team_name, ' ', '-')) = :slug1 OR LOWER(REPLACE(full_team_name, ' ', '-')) = :slug2
    ");
    // Bind the same value to both distinct named parameters
    $stmt->bindParam(':slug1', $teamSlug);
    $stmt->bindParam(':slug2', $teamSlug);
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
    $teamId = $team['team_id'];
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
    <title><?php echo htmlspecialchars($team['full_team_name']); ?> Details</title>
    <link rel="stylesheet" href="style.css">
    <style>
        :root {
            --team-main-color: <?php echo isset($team['team_color']) && $team['team_color'] ? htmlspecialchars($team['team_color']) : 'rgb(0,0,0)'; ?>;
        }
        .details-container {
            max-width: 800px;
            margin: 20px auto;
            background-color: #343434ff; /* Zorg dat deze kleur overeenkomt met je huisstijl of var */
            padding: 30px;
            border-radius: 8px;
            color: #f8f8f8; /* Lichtgrijze tekst voor leesbaarheid op donkere achtergrond */
        }

        .team-name-heading {
            color: #ef4444; /* Rode accentkleur voor de teamnaam */
            text-align: center;
            margin-bottom: 20px;
            font-size: 2.5em; /* Grotere lettergrootte */
            font-family: 'Oswald', sans-serif; /* Gebruik een van je geïmporteerde fonts */
            font-weight: 700;
        }

        .team-logo-details {
            display: block;
            max-width: 250px; /* Grootte van het logo */
            height: auto;
            margin: 0 auto 20px auto; /* Centreer het logo en voeg onderruimte toe */
            border-radius: 5px; /* Optioneel: licht afgeronde hoeken */
        }

        .team-detail-item {
            margin-bottom: 10px;
            font-size: 1.1em;
            display: flex; /* Gebruik flexbox voor betere uitlijning */
            align-items: center; /* Centreer items verticaal */
        }

        .team-detail-item strong {
            margin-right: 8px;
            color: #ffffff; /* Witte kleur voor labels */
        }

        .team-detail-item img {
            height: 25px; /* Grootte voor vlaggen of kleine iconen */
            vertical-align: middle;
            margin-right: 8px;
            border-radius: 3px;
        }

        /* Styling for the new team drivers section */
        .team-drivers-section {
            margin-top: 2rem;
            padding-top: 1rem;
            border-top: 1px solid #4a4a4a; /* Lichte lijn om secties te scheiden */
        }

        .team-drivers-section h2 {
            color: #ffffff;
            font-size: 1.8em;
            margin-bottom: 1rem;
            text-align: center;
            font-family: 'Oswald', sans-serif;
            font-weight: 600;
        }

        .drivers-grid {
            display: grid;
            grid-template-columns: 1fr; /* Standaard 1 kolom op kleine schermen */
            gap: 1rem; /* Ruimte tussen coureurkaarten */
        }

        @media (min-width: 640px) { /* 2 kolommen op grotere schermen */
            .drivers-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        .driver-card {
            display: flex;
            align-items: center;
            background-color: #2d2d2d; /* Iets lichtere achtergrond voor coureurkaart */
            padding: 1rem;
            border-radius: 0.5rem;
            text-decoration: none;
            color: inherit;
            transition: background-color 0.2s ease, transform 0.2s ease;
        }

        .driver-card:hover {
            background-color: #3a3a3a;
            transform: translateY(-3px);
        }

        .driver-card img {
            width: 4rem; /* 64px */
            height: 4rem; /* 64px */
            border-radius: 9999px; /* Volledig rond */
            object-fit: cover;
            margin-right: 1rem;
            border: 2px solid #ef4444; /* Rode rand om de afbeelding */
        }

        .driver-card p {
            margin: 0;
            line-height: 1.2;
        }

        .driver-card p:first-child {
            font-size: 1.25rem; /* Grotere naam */
            font-weight: 600;
            color: #ffffff;
        }

        .driver-card p:last-child {
            font-size: 1rem;
            color: #a0a0a0; /* Lichtere kleur voor nummer */
        }

        .team-name-heading {
            color: var(--team-main-color); /* Gebruik de dynamische teamkleur */
            text-align: center;
            margin-bottom: 20px;
            font-size: 2.5em; /* Grotere lettergrootte */
            font-family: 'Oswald', sans-serif; /* Gebruik een van je geïmporteerde fonts */
            font-weight: 700;
        }


        .back-link {
            display: block;
            margin-top: 30px;
            text-align: center;
            color: #ef4444; /* Rode accentkleur */
            text-decoration: none;
            font-weight: bold;
            transition: color 0.3s ease;
        }

        .back-link:hover {
            color: #dc2626; /* Donkerdere rode bij hover */
        }

        /* Responsive adjustments for headers and navigation (from your style.css) */


        /* Media query for desktop navigation */
        @media (min-width: 768px) {
            .header-content.container {
                flex-direction: row;
            }
            .site-title {
                margin-bottom: 0;
            }
        }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Oswald:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <header>
        <div class="header-content container">
            <h1 class="site-title">Formula 1 Season 2025</h1>
            <nav class="main-nav">
                <a href="index.php">Home</a>
                <a href="kalender.php">Schedule</a>
                <a href="teams.php" class="active">Teams</a>
                <a href="drivers.php">Drivers</a>
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

        <!-- New section for Team Drivers -->
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

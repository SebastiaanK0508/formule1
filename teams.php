<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formula 1 Season 2025 - Teams</title>
    <link rel="stylesheet" href="style2.css">
        <?php
    require_once 'db_config.php';
    /** @var PDO $pdo */
    $allTeams = []; 
    try {
    $stmt = $pdo->query("SELECT team_id, team_name, base_location, team_principal, team_color, full_team_name FROM teams WHERE is_active = TRUE ORDER BY team_name ASC");
        $allTeams = $stmt->fetchAll();
    } catch (\PDOException $e) {
        error_log("Fout bij het ophalen van alle teams: " . $e->getMessage());
        echo "<p>Er is een fout opgetreden bij het laden van de teams. Probeer het later opnieuw.</p>";
    }
    $selectedTeam = null; 
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['team_id'])) {
        $teamId = $_POST['team_id'];

        try {
            $stmt = $pdo->prepare("SELECT team_id, team_name, base_location, team_principal, team_color, full_team_name FROM teams WHERE team_id = :team_id");
            $stmt->bindParam(':driver_id', $teamId, PDO::PARAM_INT);
            $stmt->execute();
            $selectedTeam = $stmt->fetch();

            if (!$selectedTeam) {
                echo "<p>Coureur niet gevonden met ID: " . htmlspecialchars($teamId) . "</p>";
            }
        } catch (\PDOException $e) {
            error_log("Fout bij het ophalen van geselecteerde teamdetails: " . $e->getMessage());
            echo "<p>Er is een fout opgetreden bij het ophalen van teamdetails.</p>";
        }
    }
    ?>
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
        <section class="page-header-section">
            <h2 class="page-heading">TEAMS FORMULA 1</h1>
        </section>
<!--
        <section class="carousel-section">
            <div class="carousel-container">
                <div class="carousel">
                    <img src="https://i.pinimg.com/736x/de/b8/f7/deb8f73797a429fe13741d78f27693f8.jpg" alt="McLaren Logo">
                    <img src="https://logos-world.net/wp-content/uploads/2020/07/Ferrari-Scuderia-Logo.png" alt="Ferrari Logo">
                    <img src="https://sportsbase.io/images/gpfans/copy_620x348/e67b41b5f739e3a7050609ed2a488b174476723b.png" alt="Red Bull Logo">
                    <img src="https://media.formula1.com/image/upload/f_auto,c_limit,q_75,w_1320/content/dam/fom-website/2018-redesign-assets/team%20logos/mercedes" alt="Mercedes Logo">
                    <img src="https://www.formule1.nl/app/uploads/2024/05/1645620884650-1-1-Cropped-1-419x290.jpg" alt="Alpine Logo">
                    <img src="https://media.formula1.com/image/upload/f_auto,c_limit,q_auto,w_1320/content/dam/fom-website/2018-redesign-assets/team%20logos/aston%20martin" alt="Aston Martin Logo">
                    <img src="https://media.formula1.com/image/upload/f_auto,c_limit,q_auto,w_1320/fom-website/2018-redesign-assets/team%20logos/racing%20bulls" alt="Racing Bulls Logo">
                    <img src="https://media.formula1.com/image/upload/f_auto,c_limit,q_auto,w_1320/content/dam/fom-website/2018-redesign-assets/team%20logos/williams" alt="Williams Logo">
                    <img src="https://f1madness.co.za/wp-content/uploads/2015/03/Haas-F1-250x180.jpg" alt="Haas Logo">
                    <img src="https://cdn-3.motorsport.com/images/amp/0L17d5W2/s1000/logo-stakef1team-rgb-pos-1.jpg" alt="Stake Sauber Logo">
                    <img src="https://i.pinimg.com/736x/de/b8/f7/deb8f73797a429fe13741d78f27693f8.jpg" alt="McLaren Logo">
                    <img src="https://logos-world.net/wp-content/uploads/2020/07/Ferrari-Scuderia-Logo.png" alt="Ferrari Logo">
                    <img src="https://sportsbase.io/images/gpfans/copy_620x348/e67b41b5f739e3a7050609ed2a488b174476723b.png" alt="Red Bull Logo">
                    <img src="https://media.formula1.com/image/upload/f_auto,c_limit,q_75,w_1320/content/dam/fom-website/2018-redesign-assets/team%20logos/mercedes" alt="Mercedes Logo">
                    <img src="https://www.formule1.nl/app/uploads/2024/05/1645620884650-1-1-Cropped-1-419x290.jpg" alt="Alpine Logo">
                    <img src="https://media.formula1.com/image/upload/f_auto,c_limit,q_auto,w_1320/content/dam/fom-website/2018-redesign-assets/team%20logos/aston%20martin" alt="Aston Martin Logo">
                    <img src="https://media.formula1.com/image/upload/f_auto,c_limit,q_auto,w_1320/fom-website/2018-redesign-assets/team%20logos/racing%20bulls" alt="Racing Bulls Logo">
                    <img src="https://media.formula1.com/image/upload/f_auto,c_limit,q_auto,w_1320/content/dam/fom-website/2018-redesign-assets/team%20logos/williams" alt="Williams Logo">
                    <img src="https://f1madness.co.za/wp-content/uploads/2015/03/Haas-F1-250x180.jpg" alt="Haas Logo">
                    <img src="https://cdn-3.motorsport.com/images/amp/0L17d5W2/s1000/logo-stakef1team-rgb-pos-1.jpg" alt="Stake Sauber Logo">
                </div>
            </div>
        </section>-->

        <section class="f1-section">
            <div class="data-card-row">
                <?php if (!empty($allTeams)): ?>
                    <?php foreach ($allTeams as $team): ?>
                        <article class="data-card">
                            <a href="team-details.php?id=<?php echo htmlspecialchars($team['team_id']); ?>" class="team-link">
                                <div class="info">
                                    <h3 class="team-name" style="color: <?php echo htmlspecialchars($team['team_color']); ?>; padding-left: 10px;">
                                        <?php echo htmlspecialchars($team['full_team_name']); ?>
                                    </h3>
                                    <p><strong>Base:</strong> <?php echo htmlspecialchars($team['base_location']); ?></p>
                                    <p><strong>Team Principal:</strong> <?php echo htmlspecialchars($team['team_principal']); ?></p>
                                </div>
                            </a>
                        </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Geen teams beschikbaar om weer te geven.</p>
                <?php endif; ?>
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
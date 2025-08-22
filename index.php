<?php
require_once 'achterkant/aanpassing/api-koppelingen/1result_api.php';
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formula 1 Season 2025 - Home</title>
    <link rel="stylesheet" href="style2.css">
    <link rel="icon" type="image/x-icon" href="/afbeeldingen/logo/f1logobgrm.png">
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
</head>
<body>
    <header>
        <div class="header-content container">
            <h1 id="site-title-header" class="site-title">FORMULA 1</h1>
            <nav class="main-nav">
                <a href="index.php" class="active">Home</a>
                <a href="kalender.php">Schedule</a>
                <a href="teams.php">Teams</a>
                <a href="drivers.php">Drivers</a>
                <a href="results.php">Results</a>
                <a href="standings.php">Standings</a>
            </nav>
        </div>
    </header>
    <main class="container">
        <div class="page-header-section">
            <div>
                <h3 class="page-heading">
                    <?php
                    if ($nextGrandPrix) {
                        echo htmlspecialchars($nextGrandPrix['grandprix']);
                    } else {
                        echo "Geen aankomende Grand Prix";
                    }
                    ?>
                </h3>
            </div>
            <div class="page-heading" id="countdown">
            </div>
        </div>
            <?php if ($error_message): ?>
                <div class="error-message">
                    <?php echo $error_message; ?>
                </div>
            <?php else: ?>
                <div class="selection-link">
                    <?php if (!empty($races_in_season)): ?>
                    <?php else: ?>
                        <p>Geen races gevonden</p>
                    <?php endif; ?>
                </div>
            <?php if ($race_details): ?>
            <section class="f1-section">
                <div class="race-info-card">
                    <h2 class="page-heading">Result <?php echo htmlspecialchars($race_details['name']); ?></h2>
                    <p><strong>Location:</strong> <?php echo htmlspecialchars($race_details['circuit']); ?>, <?php echo htmlspecialchars($race_details['location']); ?>, <?php echo htmlspecialchars($race_details['country']); ?></p>
                    <p><strong>Date:</strong> <?php echo htmlspecialchars((new DateTime($race_details['date']))->format('d-m-Y')); ?></p>
                </div>
                <?php if (!empty($race_results)): ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Pos</th>
                            <th>Driver</th>
                            <th>Team</th>
                            <th>Time / Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($race_results as $result): ?>
                            <tr>
                                <td style="border-left: 5px solid <?php echo htmlspecialchars($result['team_color']); ?>;">
                                    <?php echo htmlspecialchars($result['position']); ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($result['driver_name']); ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($result['team_name']); ?>
                                </td>
                                <td style="border-right: 5px solid <?php echo htmlspecialchars($result['team_color']); ?>;">
                                    <?php echo htmlspecialchars($result['lap_time_or_status']); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p>Er zijn geen resultaten beschikbaar voor deze race.</p>
                <?php endif; ?>
            </section>
            <?php else: ?>
                <p>Selecteer een race om de resultaten te bekijken.</p>
            <?php endif; ?>
        <?php endif; ?>
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

    <script>
        <?php if ($nextGrandPrix): ?>
        const targetDateTime = new Date('<?php echo $targetDateTime; ?>');
        const countdownElement = document.getElementById('countdown');
        function updateCountdown() {
            const now = new Date().getTime();
            const distance = targetDateTime - now;
            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);
            if (distance < 0) {
                countdownElement.innerHTML = "De race is bezig of voorbij!";
                clearInterval(countdownInterval);
            } else {
                countdownElement.innerHTML =
                    `${days}d ${hours}h ${minutes}m ${seconds}s`;
            }
        }
        updateCountdown();
        const countdownInterval = setInterval(updateCountdown, 1000);
        <?php else: ?>
        console.log("Geen volgende Grand Prix om af te tellen.");
        <?php endif; ?>
    </script>
    
</body>
</html>
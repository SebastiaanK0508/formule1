<?php
require_once 'achterkant/aanpassing/api-koppelingen/result_api.php';
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formula 1 Results</title>
    <!-- Google Fonts: Oswald voor koppen, Roboto voor tekst -->
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style2.css">
        
</head>
<body>
    <header>
        <div class="header-content container">
            <h1 class="site-title">FORMULA 1 SEASON 2025</h1>
            <nav class="main-nav">
                <a href="index.php">Home</a>
                <a href="kalender.php">Schedule</a>
                <a href="teams.php">Teams</a>
                <a href="drivers.php">Drivers</a>
                <a href="results.php" class="active">Results</a>
                <a href="standings.php">Standings</a>
            </nav>
        </div>
    </header>

    <main class="contianer">
        <section class="page-header-section">
        <?php if ($error_message): ?>
            <div class="error-message">
                <?php echo $error_message; ?>
            </div>
        <?php else: ?>
            <div class="selection-link">
                <?php if (!empty($races_in_season)): ?>
                    <?php foreach ($races_in_season as $race): ?>
                        <a href="results.php?round=<?php echo htmlspecialchars($race['round']); ?>"
                           class="<?php echo ($selected_round == $race['round']) ? 'active' : ''; ?>">
                            Round <?php echo htmlspecialchars($race['round']); ?>: <?php echo htmlspecialchars($race['raceName']); ?> (<?php echo htmlspecialchars((new DateTime($race['date']))->format('d-m')); ?>)
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Geen races gevonden</p>
                <?php endif; ?>
            </div>
        </section>
        <section class="f1-section">
            <div class="grid">
                <h2 class="page-heading">FORMULE 1 RACE RESULTS <?php echo $current_year; ?></h2>
                    <?php if (empty($race_results)): ?>
                        <p class="error-message">Geen uitslagen beschikbaar voor de geselecteerde race. Mogelijk is deze race nog niet verreden.</p>
                    <?php else: ?>
                    <div class="race-info-card">
                        <h3><?php echo htmlspecialchars($race_details['name']); ?></h3>
                        <p><strong>Circuit:</strong> <?php echo htmlspecialchars($race_details['circuit']); ?></p>
                        <p><strong>Locatie:</strong> <?php echo htmlspecialchars($race_details['location']); ?>, <?php echo htmlspecialchars($race_details['country']); ?></p>
                        <p><strong>Datum:</strong> <?php echo htmlspecialchars((new DateTime($race_details['date']))->format('d-m-Y')); ?></p>
                    </div>

                    <div class="data-table-container">
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
                                    <tr style="border-left: 5px solid <?php echo htmlspecialchars($result['team_color'] ?? '#CCCCCC'); ?>;">
                                        <td class="position"><?php echo htmlspecialchars($result['position']); ?></td>
                                        <td class="driver-name"><?php echo htmlspecialchars($result['driver_name']); ?></td>
                                        <td style="color: <?php echo htmlspecialchars($result['team_color'] ?? '#CCCCCC'); ?>;" class="team-name"><?php echo htmlspecialchars($result['team_name']); ?></td>
                                        <td class="lap-time-status"><?php echo htmlspecialchars($result['lap_time_or_status']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <footer>
        <div class="footer-content container">
            <p>&copy; <?php echo $current_year; ?> Webbair. Alle rechten voorbehouden.</p>
            <div class="social-links">
                <a href="#" aria-label="Facebook">Facebook</a>
                <a href="#" aria-label="Twitter">Twitter</a>
                <a href="#" aria-label="Instagram">Instagram</a>
            </div>
        </div>
    </footer>
</body>
</html>

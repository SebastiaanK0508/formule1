<?php
require_once 'achterkant/aanpassing/api-koppelingen/result_api.php';
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formula 1 Results</title>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style2.css">
    <link rel="icon" type="image/x-icon" href="/afbeeldingen/logo/f1logobgrm.png">
</head>
<body>
    <header>
        <div class="header-content container">
            <h1 class="site-title">FORMULA 1</h1>
            <button class="menu-toggle" aria-controls="main-nav-links" aria-expanded="false" aria-label="Toggle navigation">&#9776; </button>
            <nav class="main-nav" id="main-nav-links" data-visible="false">
                <a href="index.php">Home</a>
                <a href="kalender.php">Schedule</a>
                <a href="teams.php">Teams</a>
                <a href="drivers.php">Drivers</a>
                <a href="results.php" class="active">Results</a>
                <a href="standings.php">Standings</a>
            </nav>
        </div>
    </header>
    <main class="container">
        <section class="page-header-section">
            <h2 class="page-heading">Results</h2>
        </section>
    
        <section class="result-container">
            <?php if ($error_message): ?>
            <div class="error-message">
                <?php echo $error_message; ?>
            </div>
            <?php else: ?>
                <div class="selection-container">
                    <form action="results.php" method="get">
                        <label for="year">Select a Year</label>
                        <select id="year" name="year" onchange="this.form.submit()">
                            <?php foreach ($available_years as $year): ?>
                                <option value="<?php echo htmlspecialchars($year); ?>" 
                                        <?php echo ($selected_year == $year) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($year); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <noscript><input type="submit" value="Selecteer"></noscript>
                    </form>
                    <?php if (!empty($races_in_season)): ?>
                        <?php foreach ($races_in_season as $race): ?>
                            <a href="results.php?year=<?php echo htmlspecialchars($selected_year); ?>&round=<?php echo htmlspecialchars($race['round']); ?>"
                            class="<?php echo ($selected_round == $race['round']) ? 'active' : ''; ?>">
                                 <?php echo htmlspecialchars($race['raceName']); ?>: <?php echo htmlspecialchars($selected_year); ?>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>Geen races gevonden</p>
                    <?php endif; ?>
                </div>

                <section class="results-container">
                    <div class="results-grid">
                            <?php if (empty($race_results)): ?>
                                <p class="error-message">Geen uitslagen beschikbaar voor de geselecteerde race. Mogelijk is deze race nog niet verreden.</p>
                            <?php else: ?>
                            <div class="info">
                                <h3 class="page-heading"><?php echo htmlspecialchars($race_details['name']); ?> <?php echo htmlspecialchars($race_details['year']); ?></h3>
                                <p><strong>Circuit:</strong> <?php echo htmlspecialchars($race_details['circuit']); ?></p>
                                <p><strong>Location:</strong> <?php echo htmlspecialchars($race_details['location']); ?>, <?php echo htmlspecialchars($race_details['country']); ?></p>
                                <p><strong>Date:</strong> <?php echo htmlspecialchars((new DateTime($race_details['date']))->format('d-m-Y')); ?></p>
                            </div>
                            <div class="result-table-container">
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
        </section>
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
    <script src="mobiel_nav.js" defer></script> 
</body>
</html>
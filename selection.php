<?php
// De API-koppeling is hier ook noodzakelijk om de jaren en races op te halen
require_once 'achterkant/aanpassing/api-koppelingen/result_api.php';

// Zorg ervoor dat de logica die $available_years, $selected_year, en $races_in_season vult,
// ook hier draait, net zoals aan het begin van results.php.
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Race / Year</title>
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
    <main class="container selection-page-main">
        <section class="page-header-section">
            <h2 class="page-heading">Select Race / Year</h2>
        </section>
    
        <section class="result-container">
            <?php if ($error_message): ?>
            <div class="error-message">
                <?php echo $error_message; ?>
            </div>
            <?php else: ?>
                <div class="selection-container mobile-full-view">
                    
                    <form action="selection.php" method="get">
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
                    
                    <h3>Races voor <?php echo htmlspecialchars($selected_year); ?></h3>

                    <?php if (!empty($races_in_season)): ?>
                        <div class="race-link-list">
                            <?php foreach ($races_in_season as $race): ?>
                                <a href="results.php?year=<?php echo htmlspecialchars($selected_year); ?>&round=<?php echo htmlspecialchars($race['round']); ?>">
                                     <?php echo htmlspecialchars($race['raceName']); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p>Geen races gevonden</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
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
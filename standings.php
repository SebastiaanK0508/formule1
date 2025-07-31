<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial=" initial-scale.0">
    <title>Formula 1 Season 2025 - Standings</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="header-content container">
            <h1 class="site-title">FORMULA 1 SEASON 2025</h1>
            <nav class="main-nav">
                <a href="index.php">Home</a>
                <a href="kalender.php" class="active">Schedule</a>
                <a href="teams.php">Teams</a>
                <a href="drivers.php">Drivers</a>
                <a href="results.php">Results</a>
                <a href="standings.php">Standings</a>
            </nav>
        </div>
    </header>

<main class="container">
    <section class="page-header-section">
        <h2 class="page-heading">Standings 2025</h2>
    </section>

    <section id="standings-content">
        <p>Loading...</p>
        </section>
    </main>

        <footer>
            <div class="footer-content container">
                <p>&copy; 2025 Webbair. Alle rechten voorbehouden.</p>
                <div class="social-links">
                    <a href="#" aria-label="Facebook">Facebook</a>
                    <a href="#" aria-label="Twitter">X</a>
                    <a href="#" aria-label="Instagram">Instagram</a>
                </div>
            </div>
        </footer>

<script>
    const standingsContent = document.getElementById('standings-content'); // Naam gewijzigd naar standings-content

    async function fetchChampionshipStandings() {
        try {
            // Pas de URL aan naar de locatie van je PHP-script
            // Let op: De URL 'achterkant/aanpassing/api-koppelingen/standings_api.php' is relatief.
            // Zorg ervoor dat dit pad klopt vanaf de locatie van dit standings.php bestand.
            // Als standings.php in de root staat en standings_api.php in die submap, is het pad correct.
            const response = await fetch('achterkant/aanpassing/api-koppelingen/standings_api.php');
            const data = await response.json();

            if (data.status === 'success') {
                displayChampionshipStandings(data.drivers, data.constructors);
            } else {
                standingsContent.innerHTML = `<p class="error-message">Fout bij het laden van klassementen: ${data.message}</p>`;
                console.error('API Error:', data.message);
            }
        } catch (error) {
            standingsContent.innerHTML = `<p class="error-message">Netwerkfout bij het laden van klassementen.</p>`;
            console.error('Fetch Error:', error);
        }
    }

    function displayChampionshipStandings(drivers, constructors) {
        let html = '<div class="standings-grid">'; // Start de grid container hier

        // Coureursklassement
        html += '<div class="standings-table-container">'; // Container voor de coureurstabel
        html += '<h4>Coureursklassement</h4>';
        if (drivers.length > 0) {
            html += `
                <table class="standings-table"> <thead>
                        <tr>
                            <th>Pos.</th>
                            <th>Driver</th>
                            <th>Team</th>
                            <th>Points</th>
                            <th>Wins</th>
                        </tr>
                    </thead>
                    <tbody>
            `;
            drivers.forEach(driver => {
                html += `
                    <tr>
                        <td>${driver.position}</td>
                        <td>${driver.given_name} ${driver.family_name}</td>
                        <td>${driver.constructor_name}</td>
                        <td>${driver.points}</td>
                        <td>${driver.wins}</td>
                    </tr>
                `;
            });
            html += `
                    </tbody>
                </table>
            `;
        } else {
            html += '<p>Geen coureursklassement beschikbaar op dit moment.</p>';
        }
        html += '</div>'; // Sluit standings-table-container voor coureurs

        // Constructeursklassement
        html += '<div class="standings-table-container">'; // Container voor de constructeurstabel
        html += '<h4>Constructeursklassement</h4>';
        if (constructors.length > 0) {
            html += `
                <table class="standings-table"> <thead>
                        <tr>
                            <th>Pos.</th>
                            <th>Team</th>
                            <th>Points</th>
                            <th>Wins</th>
                        </tr>
                    </thead>
                    <tbody>
            `;
            constructors.forEach(constructor => {
                html += `
                    <tr>
                        <td>${constructor.position}</td>
                        <td>${constructor.name}</td>
                        <td>${constructor.points}</td>
                        <td>${constructor.wins}</td>
                    </tr>
                `;
            });
            html += `
                    </tbody>
                </table>
            `;
        } else {
            html += '<p>Geen constructeursklassement beschikbaar op dit moment.</p>';
        }
        html += '</div>'; // Sluit standings-table-container voor constructeurs

        html += '</div>'; // Sluit de standings-grid container

        standingsContent.innerHTML = html; // Injecteer alle gegenereerde HTML
    }

    // Roep de functie aan bij het laden van de pagina
    fetchChampionshipStandings();
</script>
</body>
</html>
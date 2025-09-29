<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formula 1 - Standings</title>
    <link rel="stylesheet" href="style2.css">
    <link rel="icon" type="image/x-icon" href="/afbeeldingen/logo/f1logobgrm.png">
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --f1-red: #E10600;
            --f1-dark-bg: #15151E;
            --card-bg: #FFFFFF;
            --text-color: #333333;
            --border-color-light: #EEEEEE;
        }
        html, body {
            overflow-x: hidden; 
            width: 100%;
        }
        .standings-grid {
            display: flex;
            gap: 20px; 
            margin-top: 20px;
        }
        .standings-grid h4 {
            text-align: center;
            margin-bottom: 15px;
        }
        .standings-grid > .standings-table-container {
            flex: 1; 
            min-width: 0;
            overflow-x: hidden;
            border-radius: 12px; 
            background-color: var(--card-bg, #FFFFFF);
            padding: 20px; 
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .standings-grid .standings-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            color: var(--text-color);
            text-align: left; 
        }
        .standings-grid .standings-table th {
            background-color: #f0f0f0;
            color: var(--text-color);
            text-transform: uppercase;
        }
        .standings-table .team-name-mobile-driver {
             display: table-cell; 
        }
        @media (max-width: 500px) {
            .standings-grid {
                flex-direction: column; 
                gap: 15px;
                width: 100%; 
            }
            .standings-grid > .standings-table-container {
                flex: none; 
                position: relative;
                left: 50%;
                right: 50%;
                margin-left: -50vw;
                margin-right: -50vw;
                width: 100vw;
                padding: 15px 10px; 
                box-sizing: border-box; 
                border-radius: 0; 
                overflow-x: hidden; 
                box-shadow: none;
            }
            .standings-table thead {
                display: none;
            }
            .standings-table tr {
                display: block;
                margin-bottom: 15px;
                border: 1px solid var(--border-color-light);
                border-left: 5px solid var(--f1-red);
                border-radius: 6px;
                padding: 10px;
                box-shadow: 0 1px 3px rgba(0,0,0,0.5);
            }
            .standings-table td {
                display: block;
                border: none;
                padding: 5px 0;
                text-align: left;
            }
            .standings-table .team-name-mobile-driver {
                 display: none; 
            }
            .standings-table tr td:first-child {
                font-weight: 700;
                font-size: 1.2em;
                margin-bottom: 5px;
                padding-bottom: 5px;
                border-bottom: 1px dashed var(--border-color-light);
                display: inline-block;
                width: 30px;
            }
            .standings-table tr td:nth-child(2),
            .standings-table tr td:nth-child(3) {
                font-size: 1em;
                font-weight: 500;
                display: inline-block;
                margin-left: 10px;
            }
            .standings-table tr td:last-child {
                float: right;
                font-weight: 700;
                color: var(--f1-red);
                font-size: 1.1em;
                margin-top: -1.5em;
            }
            .standings-table tr:has(td:nth-child(2):not(:last-child)) td:nth-child(2) {
                width: calc(100% - 100px);
            }

        }
    </style>

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
                <a href="results.php">Results</a>
                <a href="standings.php" class="active">Standings</a>
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
                <a href="" aria-label="Instagram">Instagram</a>
            </div>
            <div class="social-links">
                <a href="privacy.html">Privacy Beleid</a>
                <a href="algemenevoorwaarden.html">Algemene Voorwaarden</a>
                <a href="contact.html">Contact</a>
            </div>
        </div>
    </footer>

<script>
    const standingsContent = document.getElementById('standings-content');
    async function fetchChampionshipStandings() {
        try {
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
        let html = '<div class="standings-grid">';
        html += '<div class="standings-table-container">'; 
        html += '<h4>Drivers Championship</h4>';
        if (drivers.length > 0) {
            html += `
                <table class="standings-table"> 
                    <thead>
                        <tr>
                            <th>Pos.</th>
                            <th>Driver</th>
                            <th class="team-name-mobile-driver">Team</th>
                            <th>Points</th>
                        </tr>
                    </thead>
                    <tbody>
            `;
            drivers.forEach(driver => {
                html += `
                    <tr>
                        <td>${driver.position}</td>
                        <td>${driver.given_name} ${driver.family_name}</td>
                        <td class="team-name-mobile-driver">${driver.constructor_name}</td>
                        <td>${driver.points}</td>
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
        html += '</div>'; 
        html += '<div class="standings-table-container">';
        html += '<h4>Constructors Championship</h4>';
        if (constructors.length > 0) {
            html += `
                <table class="standings-table"> <thead>
                        <tr>
                            <th>Pos.</th>
                            <th>Team</th>
                            <th>Points</th>
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
        html += '</div>'; 
        html += '</div>'; 
        standingsContent.innerHTML = html;
    }
    fetchChampionshipStandings();
</script>
<script src="mobiel_nav.js" defer></script>
</body>
</html>
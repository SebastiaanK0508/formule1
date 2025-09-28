<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formula 1 Season 2025 - Teams</title>
    <link rel="stylesheet" href="teamcss.css">
    <link rel="icon" type="image/x-icon" href="/afbeeldingen/logo/f1logobgrm.png">
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <?php
    require_once 'db_config.php';
    /** @var PDO $pdo */
    $activeTeams = [];
    try {
        $stmt = $pdo->query("SELECT team_id, team_name, base_location, team_principal, team_color, full_team_name FROM teams WHERE is_active = TRUE ORDER BY team_name ASC");
        $activeTeams = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (\PDOException $e) {
        error_log("Fout bij het ophalen van actieve teams: " . $e->getMessage());
    }
    ?>
</head>
<body>
    <header>
        <div class="header-content container">
            <h1 class="site-title">FORMULA 1</h1>
            <button class="menu-toggle" aria-controls="main-nav-links" aria-expanded="false" aria-label="Toggle navigation">&#9776; </button>
            <nav class="main-nav" id="main-nav-links" data-visible="false">
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
            <h2 class="page-heading">TEAMS FORMULA 1</h2>
        </section>
        <section class="f1-section">
            <div class="data-card-row">
                <?php if (!empty($activeTeams)): ?>
                    <?php foreach ($activeTeams as $team): ?>
                        <article class="data-card">
                            <a href="team-details.php?id=<?php echo htmlspecialchars($team['team_id']); ?>" class="team-link">
                                <div class="info">
                                    <h3 class="team-name" style="color: <?php echo htmlspecialchars($team['team_color']); ?>; padding-left: 10px;">
                                        <?php echo htmlspecialchars($team['full_team_name']); ?>
                                    </h3>
                                    <p><strong>Base:</strong> <?php echo htmlspecialchars($team['base_location'] ?? 'N/A'); ?></p>
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
        <!-- <section class="f1-section">
            <div class="all-teams-header">
                <h2>All Teams Ever</h2>
                <div class="filter-section">
                    <label for="team-filter">Filter on Name or Country:</label>
                    <input type="text" id="team-filter">
                </div>
            </div>
            <div class="data-card-row" id="history-team-row">
                <p id="loading-message">Loading historical teams...</p>
                <p id="no-results-message" style="display: none;">Er zijn geen resultaten gevonden.</p>
            </div>
        </section> -->
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
    document.addEventListener('DOMContentLoaded', function() {
        const filterInput = document.getElementById('team-filter');
        const dataCardRow = document.getElementById('history-team-row');
        const noResultsMessage = document.getElementById('no-results-message');
        const loadingMessage = document.getElementById('loading-message');
        let allHistoricalTeams = [];
        const renderTeamCards = (teams) => {
            dataCardRow.innerHTML = '';
            if (teams && Array.isArray(teams) && teams.length > 0) {
                teams.forEach(team => {
                    const cardHtml = `
                        <article class="data-card filterable-card"
                            data-fullname="${(team.full_team_name || '').toLowerCase()}"
                            data-country="${(team.base_location || '').toLowerCase()}">
                            <a href="team-details.php?id=${team.team_id || ''}" class="team-link">
                                <div class="info">
                                    <h3 class="teamname"><strong></strong> ${team.full_team_name || 'N/A'}</h3>
                                    <p>${team.base_location || 'N/A'}</p>
                                </div>
                            </a>
                        </article>
                    `;
                    dataCardRow.insertAdjacentHTML('beforeend', cardHtml);
                });
                noResultsMessage.style.display = 'none';
            } else {
                noResultsMessage.style.display = 'block';
            }
        };
        const handleFilter = () => {
            const filterText = filterInput.value.toLowerCase().trim();
            const filteredTeams = allHistoricalTeams.filter(team => {
                const fullName = (team.full_team_name || '').toLowerCase();
                const country = (team.base_location || '').toLowerCase();
                return fullName.includes(filterText) || country.includes(filterText);
            });
            renderTeamCards(filteredTeams);
        };
        fetch('/achterkant/aanpassing/api-koppelingen/json/teams.json')
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (!Array.isArray(data)) {
                    throw new Error('Fetched data is not an array. Please check your JSON file structure.');
                }
                allHistoricalTeams = data;
                loadingMessage.style.display = 'none';
                renderTeamCards(allHistoricalTeams);
                if (filterInput) {
                    filterInput.addEventListener('keyup', handleFilter);
                }
            })
            .catch(error => {
                console.error('Error loading historical teams:', error);
                loadingMessage.textContent = 'Kon de gegevens niet laden. Controleer of de JSON-structuur correct is en het bestand bestaat.';
                loadingMessage.style.color = 'red';
            });
    });
</script>
<script src="mobiel_nav.js" defer></script>

</body>
</html>
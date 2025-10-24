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
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formula 1 Season 2025 - Teams</title>
    <link rel="icon" type="image/x-icon" href="/afbeeldingen/logo/f1logobgrm.png">
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'sans': ['Roboto', 'sans-serif'],
                        'oswald': ['Oswald', 'sans-serif'],
                    },
                    colors: {
                        'f1-red': '#E10600', 
                        'f1-black': '#15151E', 
                        'f1-gray': '#3A3A40',
                    }
                }
            }
        }
    </script>
    <style>
        @media (max-width: 767px) {
            .main-nav[data-visible="false"] {
                display: none;
            }
            .main-nav {
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background-color: #15151E;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
                padding: 1rem;
                display: flex;
                flex-direction: column;
                z-index: 40;
                border-top: 1px solid #E10600;
            }
            .main-nav a {
                padding: 0.5rem 0;
            }
        }
    </style>
</head>
<body class="bg-f1-black text-gray-100 font-sans min-h-screen flex flex-col">
    <header class="bg-black shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex justify-between items-center header-content container">
            <h1 class="text-3xl font-oswald font-extrabold text-f1-red tracking-widest site-title">
                FORMULA 1
            </h1>
            <button class="md:hidden text-2xl text-f1-red hover:text-white menu-toggle" 
                    aria-controls="main-nav-links" aria-expanded="false" aria-label="Toggle navigation">
                &#9776; 
            </button>
            <nav class="main-nav md:flex md:space-x-8 text-sm font-semibold uppercase tracking-wider" 
                 id="main-nav-links" data-visible="false">
                <a href="index.php" class="block py-2 px-3 md:p-0 hover:text-f1-red transition duration-150">Home</a>
                <a href="kalender.php" class="block py-2 px-3 md:p-0 hover:text-f1-red transition duration-150">Schedule</a>
                <a href="teams.php" class="block py-2 px-3 md:p-0 text-f1-red border-b-2 border-f1-red md:border-none active transition duration-150">Teams</a>
                <a href="drivers.php" class="block py-2 px-3 md:p-0 hover:text-f1-red transition duration-150">Drivers</a>
                <a href="results.php" class="block py-2 px-3 md:p-0 hover:text-f1-red transition duration-150">Results</a>
                <a href="standings.php" class="block py-2 px-3 md:p-0 hover:text-f1-red transition duration-150">Standings</a>
            </nav>
        </div>
    </header>
    
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-8 flex-grow container">
        
        <section class="bg-f1-gray p-6 rounded-lg shadow-xl mb-8 page-header-section">
            <h2 class="text-xl md:text-3xl font-oswald font-bold text-white uppercase page-heading text-center">
                TEAMS FORMULA 1 2025
            </h2>
        </section>
        
        <section class="f1-section">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 data-card-row">
                <?php if (!empty($activeTeams)): ?>
                    <?php foreach ($activeTeams as $team): ?>
                        <?php $teamColor = htmlspecialchars($team['team_color'] ?? '#CCCCCC'); ?>
                        <article class="bg-f1-gray rounded-xl shadow-lg overflow-hidden transition-all duration-300 hover:shadow-2xl hover:scale-[1.02] data-card min-h-40">
                            <a href="team-details.php?id=<?php echo htmlspecialchars($team['team_id']); ?>" class="team-link block p-4 border-l-4 h-full" style="border-left-color: <?php echo $teamColor; ?>;">
                                <div class="info">
                                    <h3 class="team-name text-2xl font-oswald font-bold uppercase mb-2 transition duration-150" style="color: <?php echo $teamColor; ?>;">
                                        <?php echo htmlspecialchars($team['full_team_name']); ?>    
                                    </h3>
                                    <p class="text-sm text-gray-300">
                                        <span class="font-semibold">Basis:</span> <?php echo htmlspecialchars($team['base_location'] ?? 'N/A'); ?>
                                    </p>
                                    <p class="text-sm text-gray-300">
                                        <span class="font-semibold">Team Principal:</span> <?php echo htmlspecialchars($team['team_principal']); ?>
                                    </p>
                                </div>
                            </a>
                        </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-gray-400 p-6 bg-f1-gray rounded-lg lg:col-span-3">Geen teams beschikbaar om weer te geven.</p>
                <?php endif; ?>
            </div>
        </section>
        
        <section class="f1-section mt-12 bg-f1-gray p-6 rounded-lg shadow-xl">
            <div class="flex flex-col md:flex-row justify-between items-center mb-6 all-teams-header">
                <h2 class="text-2xl font-oswald font-bold text-white">Alle Teams Ooit</h2>
                <div class="filter-section mt-4 md:mt-0 flex items-center space-x-2">
                    <label for="team-filter" class="text-gray-300 text-sm font-semibold">Filter:</label>
                    <input type="text" id="team-filter" placeholder="Naam of Land..." class="bg-f1-black border border-gray-600 text-white p-2 rounded-md focus:ring-f1-red focus:border-f1-red text-sm w-48">
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 data-card-row" id="history-team-row">
                <p id="loading-message" class="text-gray-400 col-span-4 p-4 text-center">Loading historical teams...</p>
                <p id="no-results-message" class="text-f1-red col-span-4 p-4 text-center" style="display: none;">Er zijn geen resultaten gevonden.</p>
            </div>
        </section>
    </main>
    
    <footer class="bg-black mt-12 py-6 border-t border-f1-red">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center footer-content container">
            <p class="text-gray-400 text-sm mb-4">&copy; 2025 Webbair. Alle rechten voorbehouden.</p>
            <div class="flex flex-wrap justify-center space-x-4 mb-4 social-links">
                <a href="#" class="text-gray-400 hover:text-f1-red transition duration-150" aria-label="Facebook">Facebook</a>
                <a href="#" class="text-gray-400 hover:text-f1-red transition duration-150" aria-label="Twitter">X</a>
                <a href="" class="text-gray-400 hover:text-f1-red transition duration-150" aria-label="Instagram">Instagram</a>
            </div>
            <div class="flex flex-wrap justify-center space-x-4 text-xs social-links">
                <a href="privacy.html" class="text-gray-500 hover:text-white transition duration-150">Privacy Beleid</a>
                <a href="algemenevoorwaarden.html" class="text-gray-500 hover:text-white transition duration-150">Algemene Voorwaarden</a>
                <a href="contact.html" class="text-gray-500 hover:text-white transition duration-150">Contact</a>
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
                const teamId = team.id || '';
                const fullName = team.fullName || 'Onbekend Team';
                const baseLocation = team.countryId || 'N/A';
                const teamColor = team.team_color || '#CCCCCC';
                const cardHtml = `
                    <article class="bg-f1-gray rounded-lg shadow-md transition-all duration-300 hover:shadow-xl hover:scale-[1.03] data-card filterable-card"
                        data-fullname="${(fullName).toLowerCase()}"
                        data-country="${(baseLocation).toLowerCase()}">
                        <a href="team-details.php?id=${teamId}" class="team-link block">
                            <div class="p-4 border-l-4 h-full" style="border-left-color: ${teamColor};">
                                <div class="info">
                                    <h3 class="team-name text-lg font-semibold text-white mb-1">
                                        ${fullName}
                                    </h3>
                                    <p class="text-sm text-gray-400">${baseLocation}</p>
                                </div>
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
            const fullName = (team.fullName || '').toLowerCase();
            const country = (team.countryId || '').toLowerCase();
            return fullName.includes(filterText) || country.includes(filterText);
        });
        renderTeamCards(filteredTeams);
    };
    fetch('achterkant/aanpassing/api-koppelingen/json/teams.json')
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (!Array.isArray(data)) {
                throw new Error('Fetched data is not an array.');
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
            loadingMessage.textContent = 'Kon de gegevens niet laden. Controleer of de JSON-structuur en het pad correct zijn.';
            loadingMessage.style.color = '#E10600'; 
        });
    const nav = document.getElementById('main-nav-links');
    const toggle = document.querySelector('.menu-toggle');
    toggle.addEventListener('click', () => {
        const isVisible = nav.getAttribute('data-visible') === 'true';
        nav.setAttribute('data-visible', String(!isVisible));
        toggle.setAttribute('aria-expanded', String(!isVisible));
    });
});
</script>
</body>
</html>
<?php
require_once 'achterkant/aanpassing/api-koppelingen/result_api.php';

// --- Schema.org Data Preparatie ---
$schemaData = [];

// 1. SportsEvent met SportsResult Schema voor de getoonde race
if (!empty($race_results) && $race_details) {
    
    $results = [];
    foreach ($race_results as $result) {
        // Maak een 'Person' object van elke coureur
        $results[] = [
            '@type' => 'Person',
            'name' => htmlspecialchars($result['driver_name']),
            'alumniOf' => [
                '@type' => 'SportsTeam',
                'name' => htmlspecialchars($result['team_name']),
            ],
            'sport' => 'Formula 1',
        ];
    }

    $raceDate = (new DateTime($race_details['date']))->format('Y-m-d');
    
    $raceSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'SportsEvent',
        'name' => 'Grand Prix van ' . htmlspecialchars($race_details['name']) . ' ' . htmlspecialchars($race_details['year']),
        'startDate' => $raceDate,
        'location' => [
            '@type' => 'Place',
            'name' => htmlspecialchars($race_details['circuit']),
            'address' => [
                '@type' => 'PostalAddress',
                ''
            ],
        ],
        'result' => [
            '@type' => 'SportsResults',
            'winningTeam' => $race_results[0]['team_name'] ?? 'Niet beschikbaar',
            // De winnaar (eerste coureur)
            'winningTies' => [
                '@type' => 'Win',
                'winner' => [
                    '@type' => 'Person',
                    'name' => $race_results[0]['driver_name'] ?? 'Niet beschikbaar'
                ]
            ],
            'performer' => $results, // Alle coureurs in de uitslag
        ],
        'sport' => 'Formula 1'
    ];
    $schemaData = $raceSchema;
}

?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <script src="https://t.contentsquare.net/uxa/688c1fe6f0f7c.js"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formula 1 Results - <?php echo $race_details['name'] ?? 'Overzicht'; ?> <?php echo $race_details['year'] ?? ''; ?></title>
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
                        'f1-dark-table': '#21212B',
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
    <link rel="stylesheet" href="table.css">
    
    <?php if (!empty($schemaData)): ?>
    <script type="application/ld+json">
    <?php echo json_encode($schemaData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>
    </script>
    <?php endif; ?>
    
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
                <a href="teams.php" class="block py-2 px-3 md:p-0 hover:text-f1-red transition duration-150">Teams</a>
                <a href="drivers.php" class="block py-2 px-3 md:p-0 hover:text-f1-red transition duration-150">Drivers</a>
                <a href="results.php" class="block py-2 px-3 md:p-0 text-f1-red border-b-2 border-f1-red md:border-none active transition duration-150">Results</a>
                <a href="standings.php" class="block py-2 px-3 md:p-0 hover:text-f1-red transition duration-150">Standings</a>
            </nav>
        </div>
    </header>
    
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-8 flex-grow container">
        
        <section class="bg-f1-gray p-6 rounded-lg shadow-xl mb-8 page-header-section">
            <h2 class="text-xl md:text-3xl font-oswald font-bold text-white uppercase page-heading text-center">
                RACE RESULTS
            </h2>
        </section>

        <a href="selection.php?year=<?php echo htmlspecialchars($selected_year); ?>" 
           class="md:hidden bg-f1-red text-white py-3 px-6 rounded-lg font-bold uppercase tracking-wider hover:bg-red-700 transition w-full text-center block mb-6 mobile-selection-button" 
           aria-label="Selecteer Race of Jaar">
            Select Race / Year
        </a>

        <section class="result-container grid grid-cols-1 md:grid-cols-4 gap-6">
            
            <?php if ($error_message): ?>
            <div class="md:col-span-4 bg-red-800 text-white p-4 rounded-lg error-message">
                <?php echo $error_message; ?>
            </div>
            <?php else: ?>
                
                <div class="hidden md:block md:col-span-1 bg-f1-gray p-4 rounded-lg shadow-md h-fit selection-container desktop-selection">
                    <form action="results.php" method="get" class="mb-4">
                        <label for="year" class="block text-sm font-oswald uppercase text-f1-red mb-2">Select year</label>
                        <select id="year" name="year" onchange="this.form.submit()"
                                class="w-full bg-f1-black border border-gray-700 text-white p-2 rounded-md focus:ring-f1-red focus:border-f1-red text-sm">
                            <?php foreach ($available_years as $year): ?>
                                <option value="<?php echo htmlspecialchars($year); ?>" 
                                        <?php echo ($selected_year == $year) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($year); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <noscript><input type="submit" value="Selecteer" class="mt-2 bg-f1-red hover:bg-red-700 text-white p-2 rounded w-full"></noscript>
                    </form>
                    
                    <h4 class="text-sm font-oswald uppercase text-gray-300 border-t border-gray-700 pt-4 mb-2">Season <?php echo htmlspecialchars($selected_year); ?></h4>
                    <?php if (!empty($races_in_season)): ?>
                        <nav class="space-y-1">
                        <?php foreach ($races_in_season as $race): ?>
                            <a href="results.php?year=<?php echo htmlspecialchars($selected_year); ?>&round=<?php echo htmlspecialchars($race['round']); ?>"
                            class="block p-2 text-sm rounded transition-all 
                                <?php echo ($selected_round == $race['round']) ? 'bg-f1-red text-white font-bold' : 'text-gray-300 hover:bg-f1-black'; ?>">
                                 <?php echo htmlspecialchars($race['raceName']); ?>
                            </a>
                        <?php endforeach; ?>
                        </nav>
                    <?php else: ?>
                        <p class="text-gray-400 text-sm">Geen races gevonden</p>
                    <?php endif; ?>
                </div>
                
                <section class="md:col-span-3 results-container">
                    <div class="bg-f1-gray p-6 rounded-lg shadow-lg results-grid">
                            <?php if (empty($race_results)): ?>
                                <p class="text-yellow-400 p-4 bg-f1-dark-table rounded-lg error-message">
                                    Geen uitslagen beschikbaar voor de geselecteerde race. Mogelijk is deze race nog niet verreden.
                                </p>
                            <?php else: ?>
                            
                            <div class="info mb-6 pb-4 border-b border-gray-700">
                                <h3 class="text-3xl font-oswald font-extrabold text-white mb-2">
                                    <?php echo htmlspecialchars($race_details['name']); ?> 
                                    <span class="text-f1-red"><?php echo htmlspecialchars($race_details['year']); ?></span>
                                </h3>
                                <p class="text-gray-300"><strong class="text-white">Circuit:</strong> <?php echo htmlspecialchars($race_details['circuit']); ?></p>
                                <p class="text-gray-300"><strong class="text-white">Location:</strong> <?php echo htmlspecialchars($race_details['location']); ?>, <?php echo htmlspecialchars($race_details['country']); ?></p>
                                <p class="text-gray-300"><strong class="text-white">Date:</strong> <?php echo htmlspecialchars((new DateTime($race_details['date']))->format('d-m-Y')); ?></p>
                            </div>
                            
                            <table class="data-table w-full border-collapse rounded-lg overflow-hidden">
                                <thead>
                                    <tr class="bg-f1-dark-table text-white uppercase text-sm">
                                        <th class="rounded-tl-lg w-1/12">Pos</th>
                                        <th class="w-4/12">Driver</th>
                                        <th class="w-4/12">Team</th>
                                        <th class="rounded-tr-lg w-3/12">Time / Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($race_results as $result): ?>
                                        <tr style="border-left-color: <?php echo htmlspecialchars($result['team_color'] ?? '#CCCCCC'); ?>;">
                                            <td class="position font-oswald font-bold text-lg text-white"><?php echo htmlspecialchars($result['position']); ?></td>
                                            <td class="driver-name font-semibold text-gray-100"><?php echo htmlspecialchars($result['driver_name']); ?></td>
                                            <td style="color: <?php echo htmlspecialchars($result['team_color'] ?? '#CCCCCC'); ?>;" class="team-name font-medium text-sm"><?php echo htmlspecialchars($result['team_name']); ?></td>
                                            <td class="lap-time-status font-mono text-gray-300"><?php echo htmlspecialchars($result['lap_time_or_status']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </section>
        </section>
    </main>

    <footer class="bg-black mt-12 py-8 border-t border-red-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-left pb-6 border-b border-gray-800">
                <div class="md:col-span-1 text-center md:text-left">
                    <h3 class="text-xl font-bold text-white mb-2 tracking-wider">F1SITE.NL</h3>
                    <p class="text-gray-500 text-sm mb-2">
                        De snelste bron voor F1 nieuws en data.
                    </p>
                </div>
                <div class="md:col-span-1 text-center md:text-left">
                    <h4 class="text-lg font-semibold text-red-500 mb-3 uppercase">Externe Sites</h4>
                    <ul class="space-y-2">
                        <li>
                            <a href="https://www.f1site.online" target="_blank" 
                            class="text-gray-400 text-sm hover:text-red-500 transition duration-150 block">
                            Voetbalsite (Zustersite)
                            </a>
                        </li>
                        <li>
                            <a href="https://www.webbair.nl" target="_blank" 
                            class="text-gray-400 text-sm hover:text-red-500 transition duration-150 block">
                            Webbair (Ontwikkelaar)
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="md:col-span-1 text-center md:text-left">
                    <h4 class="text-lg font-semibold text-red-500 mb-3 uppercase">Navigatie & Info</h4>
                    <ul class="space-y-2">
                        <li><a href="sitemap.html" class="text-gray-400 text-sm hover:text-red-500 transition duration-150 block">Sitemap</a></li>
                        <li><a href="privacy-en.html" class="text-gray-400 text-sm hover:text-red-500 transition duration-150 block">Privacy Policy (EN)</a></li>
                        <li><a href="algemenevoorwaarden-en.html" class="text-gray-400 text-sm hover:text-red-500 transition duration-150 block">Terms and Conditions (EN)</a></li>
                        <li><a href="contact.html" class="text-gray-400 text-sm hover:text-red-500 transition duration-150 block">Contact</a></li>
                    </ul>
                </div>
            </div>
            <div class="md:col-span-1 text-center md:text-left">
                <p class="text-gray-500 text-xs mt-4">&copy; 2025 Webbair. Alle rechten voorbehouden.</p>
            </div>
        </div>
    </footer>
    
    <script>
        document.addEventListener('DOMContentLoaded', () => {
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
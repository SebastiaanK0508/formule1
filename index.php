<?php
// ====================================================================
// NIEUWS OPHALEN VIA DATABASE (VEREIST db_config.php)
// ====================================================================
require_once 'db_config.php'; 

$news_articles = [];
try {
    // Haal de meest recente 10 artikelen op. Sorteer op publicatie datum (DESC) of ID als fallback.
    $stmt = $pdo->query("SELECT titel, artikel_url, publicatie_datum, afbeelding_url FROM f1_nieuws ORDER BY publicatie_datum DESC, id DESC LIMIT 10");
    $news_articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Fout bij ophalen nieuwsartikelen: " . $e->getMessage());
    // Optioneel: zet een foutmelding voor de gebruiker: $error_message_news = "Nieuws kon niet geladen worden.";
}

// ====================================================================
// BESTAANDE PHP LOGICA VOOR GRAND PRIX & SCHEMA
// ====================================================================
require_once 'achterkant/aanpassing/api-koppelingen/1result_api.php';
if (isset($nextGrandPrix) && $nextGrandPrix && !isset($targetDateTime)) {
    // Handmatige fallback datum voor de 2025 Qatar GP
    $targetDateTime = '2025-11-20T14:00:00+01:00'; 
}

$schemaData = [
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'WebSite',
            'url' => 'https://f1site.online/',
            'name' => 'Formula 1 - F1SITE.NL',
            'description' => 'De snelste bron voor Formule 1 nieuws, uitslagen, kalender en coureurs.',
        ]
    ]
];

// Schema data voor volgende Grand Prix
if (isset($nextGrandPrix) && $nextGrandPrix) {
    $raceDate = (new DateTime($targetDateTime))->format(DateTime::ISO8601);
    $schemaData['@graph'][] = [
        '@type' => 'SportsEvent',
        'name' => htmlspecialchars($nextGrandPrix['grandprix']),
        'startDate' => $raceDate,
        'location' => [
            '@type' => 'Place',
            'name' => htmlspecialchars($nextGrandPrix['grandprix']),
            'address' => [
                '@type' => 'PostalAddress',
                'name' => 'Circuit van ' . htmlspecialchars($nextGrandPrix['circuit']),
            ],
        ],
        'sport' => 'Formula 1',
        'competitor' => [
            '@type' => 'Organization',
            'name' => 'F1 Teams',
        ]
    ];
}

// Schema data voor recente race resultaten
if (isset($race_details) && !empty($race_results)) {
    
    $results = [];
    foreach ($race_results as $result) {
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

    $raceSchema = [
        '@type' => 'SportsEvent',
        'name' => 'Grand Prix van ' . htmlspecialchars($race_details['name']),
        'startDate' => htmlspecialchars((new DateTime($race_details['date']))->format('Y-m-d')),
        'location' => [
            '@type' => 'Place',
            'name' => htmlspecialchars($race_details['circuit']) . ', ' . htmlspecialchars($race_details['country']),
        ],
        'result' => [
            '@type' => 'SportsResults',
            'winningTeam' => $race_results[0]['team_name'] ?? 'Niet beschikbaar',
            'winningTies' => [
                '@type' => 'Win',
                'winner' => [
                    '@type' => 'Person',
                    'name' => $race_results[0]['driver_name'] ?? 'Niet beschikbaar'
                ]
            ],
            'performer' => $results,
            'position' => $race_results[0]['position'] ?? null,
        ],
        'sport' => 'Formula 1'
    ];
    $schemaData['@graph'][] = $raceSchema;
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta name="description" content="Het meest complete F1 archief & statistieken sinds 1950. Vind uitslagen, records en alle coureursdata. Duik in de F1 historie!" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formula 1 - Home</title>
    <script src="https://t.contentsquare.net/uxa/688c1fe6f0f7c.js"></script>
    <link rel="icon" type="image/x-icon" href="/afbeeldingen/logo/f1logobgrm.png">
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="table.css">
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
        /* Responsive navigation styling */
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

        /* Nieuwe stijl voor de nieuws sectie */
        .news-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem; /* Tailwind gap-6 */
        }
        .news-card {
            transition: transform 0.2s, box-shadow 0.2s;
            border-left: 5px solid transparent; /* Standaard zonder kleur */
        }
        .news-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 15px rgba(225, 6, 0, 0.2); /* Rode schaduw bij hover */
            border-left-color: #E10600; /* F1-red */
        }
        .news-card h3 a:hover {
            color: #E10600;
        }
    </style>
    
    <?php if (!empty($schemaData)): ?>
    <script type="application/ld+json">
    <?php echo json_encode($schemaData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>
    </script>
    <?php endif; ?>
    
</head>
<body class="bg-f1-black text-gray-100 font-sans">
    
    <header class="bg-black shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex justify-between items-center header-content container">
            <h1 id="site-title-header" class="text-3xl font-oswald font-extrabold text-f1-red tracking-widest site-title">
                FORMULA 1
            </h1>
            
            <button class="md:hidden text-2xl text-f1-red hover:text-white menu-toggle" 
                    aria-controls="main-nav-links" aria-expanded="false" aria-label="Toggle navigation">
                &#9776; 
            </button>
            
            <nav class="main-nav md:flex md:space-x-8 text-sm font-semibold uppercase tracking-wider" 
                 id="main-nav-links" data-visible="false">
                <a href="index.php" class="block py-2 px-3 md:p-0 text-f1-red border-b-2 border-f1-red md:border-none active transition duration-150">Home</a>
                <a href="kalender.php" class="block py-2 px-3 md:p-0 hover:text-f1-red transition duration-150">Schedule</a>
                <a href="teams.php" class="block py-2 px-3 md:p-0 hover:text-f1-red transition duration-150">Teams</a>
                <a href="drivers.php" class="block py-2 px-3 md:p-0 hover:text-f1-red transition duration-150">Drivers</a>
                <a href="results.php" class="block py-2 px-3 md:p-0 hover:text-f1-red transition duration-150">Results</a>
                <a href="standings.php" class="block py-2 px-3 md:p-0 hover:text-f1-red transition duration-150">Standings</a>
            </nav>
        </div>
    </header>
    
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-8 container">
        
        <div class="bg-f1-gray p-6 rounded-lg shadow-xl mb-8 flex flex-col md:flex-row justify-between items-center page-header-section">
            <div class="text-center md:text-left mb-4 md:mb-0">
                <h3 class="text-xl md:text-2xl font-oswald font-bold text-white uppercase page-heading">
                    <?php
                    if (isset($nextGrandPrix) && $nextGrandPrix) {
                        echo htmlspecialchars($nextGrandPrix['grandprix']);
                    } else {
                        echo "Geen aankomende Grand Prix";
                    }
                    ?>
                </h3>
                <p class="text-sm text-gray-400">Next Race</p>
            </div>
            <div class="text-center text-3xl md:text-4xl font-oswald font-extrabold text-f1-red page-heading" id="countdown">
                </div>
        </div>
        
        <section class="mb-12 f1-section">
            <h2 class="text-3xl font-oswald font-bold text-white uppercase mb-6 border-b border-f1-red pb-2 news-heading">
                Laatste F1 Nieuws
            </h2>
            
            <?php if (!empty($news_articles)): ?>
                <div class="news-grid">
                    <?php foreach ($news_articles as $article): ?>
                        <div class="bg-f1-gray p-5 rounded-lg shadow-xl news-card">
                            <?php if ($article['afbeelding_url']): ?>
                                <img src="<?php echo htmlspecialchars($article['afbeelding_url']); ?>" alt="Afbeelding bij nieuwsartikel" class="w-full h-40 object-cover rounded-md mb-4 news-image">
                            <?php endif; ?>
                            <h3 class="text-xl font-oswald font-semibold mb-2 news-title">
                                <a href="<?php echo htmlspecialchars($article['artikel_url']); ?>" target="_blank" 
                                   class="text-gray-100 hover:text-f1-red transition duration-150">
                                    <?php echo htmlspecialchars($article['titel']); ?>
                                </a>
                            </h3>
                            <?php if ($article['publicatie_datum']): ?>
                                <p class="text-xs text-gray-400 news-date">
                                    <?php 
                                        try {
                                            $date = new DateTime($article['publicatie_datum']);
                                            echo 'Gepubliceerd op: ' . $date->format('d-m-Y H:i');
                                        } catch (Exception $e) {
                                            echo 'Datum onbekend';
                                        }
                                    ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-gray-400">Er zijn momenteel geen nieuwsartikelen beschikbaar. Zorg ervoor dat de scraper draait en de database vult.</p>
            <?php endif; ?>
        </section>
        <?php if (isset($error_message) && $error_message): ?>
            <div class="bg-red-900 text-white p-4 rounded-lg mb-8 error-message">
                <?php echo $error_message; ?>
            </div>
        <?php else: ?>
            <div class="selection-link">
                <?php if (isset($races_in_season) && !empty($races_in_season)): ?>
                    <?php else: ?>
                    <p class="text-gray-400">Geen races gevonden</p>
                <?php endif; ?>
            </div>
            
            <?php if (isset($race_details) && $race_details): ?>
            <section class="bg-f1-gray p-6 rounded-lg shadow-xl f1-section">
                
                <div class="border-b border-gray-600 pb-4 mb-6 race-info-card">
                    <h2 class="text-2xl font-oswald font-bold text-f1-red mb-2 page-heading">
                        Result <?php echo htmlspecialchars($race_details['name']); ?>
                    </h2>
                    <p class="text-gray-300 text-sm">
                        <strong>Location:</strong> <?php echo htmlspecialchars($race_details['circuit']); ?>, 
                        <?php echo htmlspecialchars($race_details['location']); ?>, 
                        <?php echo htmlspecialchars($race_details['country']); ?>
                    </p>
                    <p class="text-gray-300 text-sm">
                        <strong>Date:</strong> <?php echo htmlspecialchars((new DateTime($race_details['date']))->format('d-m-Y')); ?>
                    </p>
                </div>
                
                <?php if (!empty($race_results)): ?>
                <div class="overflow-x-auto">
                    <table class="data-table w-full border-collapse rounded-lg overflow-hidden">
                        <thead class="bg-f1-red text-white uppercase text-sm">
                            <tr>
                                <th class="py-3 px-4 text-left font-bold rounded-tl-lg">Pos</th>
                                <th class="py-3 px-4 text-left font-bold">Driver</th>
                                <th class="py-3 px-4 text-left font-bold">Team</th>
                                <th class="py-3 px-4 text-left font-bold rounded-tr-lg">Time / Status</th>
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
                </div>
                <?php else: ?>
                <p class="text-gray-400">Er zijn geen resultaten beschikbaar voor deze race.</p>
                <?php endif; ?>
            </section>
            <?php else: ?>
                <p class="text-gray-400">Selecteer een race om de resultaten te bekijken.</p>
            <?php endif; ?>
        <?php endif; ?>
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
                            <a href="https://www.webbair.nl" target="_blank" 
                            class="text-gray-400 text-sm hover:text-red-500 transition duration-150 block">
                            Webbair (Ontwikkelaar)
                            </a>
                        </li>
                        <li>
                            <a href="https://www.urenheld.webbair.nl" target="_blank" 
                            class="text-gray-400 text-sm hover:text-red-500 transition duration-150 block">
                            Urenheld
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
    <script>
        <?php if (isset($nextGrandPrix) && $nextGrandPrix && isset($targetDateTime)): ?>
        const targetDateTime = new Date('<?php echo $targetDateTime; ?>').getTime(); 
        const countdownElement = document.getElementById('countdown');
        function updateCountdown() {
            const now = new Date().getTime();
            const distance = targetDateTime - now;
            
            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);
            
            if (distance < 0) {
                countdownElement.innerHTML = "<span class='text-white text-xl'>Race is bezig!</span>";
                clearInterval(countdownInterval);
            } else {
                countdownElement.innerHTML =
                    `<span class="text-f1-red">${days}</span>d <span class="text-white">|</span> <span class="text-f1-red">${hours}</span>h <span class="text-white">|</span> <span class="text-f1-red">${minutes}</span>m <span class="text-white">|</span> <span class="text-f1-red">${seconds}</span>s`;
            }
        }
        updateCountdown();
        const countdownInterval = setInterval(updateCountdown, 1000);
        <?php else: ?>
        document.getElementById('countdown').innerHTML = "<span class='text-white text-xl'>Niet beschikbaar</span>";
        console.log("Geen volgende Grand Prix om af te tellen of $targetDateTime is niet gezet.");
        <?php endif; ?>
    </script>
</body>
</html>
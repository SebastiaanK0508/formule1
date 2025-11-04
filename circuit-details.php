<?php
require_once 'db_config.php';
/** @var PDO $pdo */
$circuitKey = isset($_GET['key']) ? htmlspecialchars($_GET['key']) : null;
$circuitDetails = [];
$message = '';

if ($circuitKey) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM circuits WHERE circuit_key = :circuit_key");
        $stmt->bindParam(':circuit_key', $circuitKey);
        $stmt->execute();
        $circuitDetails = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$circuitDetails) {
            $message = "<p class='text-red-500'>Circuit met sleutel " . htmlspecialchars($circuitKey) . " niet gevonden.</p>";
        }
    } catch (\PDOException $e) {
        $message = "<p class='text-red-500'>Fout bij het ophalen van circuitdetails: " . $e->getMessage() . "</p>";
    }
} else {
    $message = "<p class='text-red-500'>Geen geldige circuit-sleutel opgegeven.</p>";
}

if (!is_array($circuitDetails)) {
    $circuitDetails = [];
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta name="description" content="Het meest complete F1 archief & statistieken sinds 1950. Vind uitslagen, records en alle coureursdata. Duik in de F1 historie!" />
    <script src="https://t.contentsquare.net/uxa/688c1fe6f0f7c.js"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Details Circuit: <?php echo htmlspecialchars($circuitDetails['grandprix'] ?? 'Onbekend'); ?></title>
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
</head>
<body class="bg-f1-black text-gray-100 font-sans min-h-screen flex flex-col">
    
    <header class="bg-black shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex justify-between items-center header-content container">
            <h1 class="text-3xl font-oswald font-extrabold text-f1-red tracking-widest site-title">
                FORMULA 1
            </h1>
            <button class="md:hidden text-2xl text-f1-red hover:text-white menu-toggle" 
                    aria-controls="main-nav-links" aria-expanded="false" aria-label="Toggle navigation">&#9776; </button>
            <nav class="main-nav md:flex md:space-x-8 text-sm font-semibold uppercase tracking-wider" id="main-nav-links" data-visible="false">
                <a href="index.php" class="block py-2 px-3 md:p-0 hover:text-f1-red transition duration-150">Home</a>
                <a href="kalender.php" class="block py-2 px-3 md:p-0 text-f1-red border-b-2 border-f1-red md:border-none active transition duration-150">Schedule</a>
                <a href="teams.php" class="block py-2 px-3 md:p-0 hover:text-f1-red transition duration-150">Teams</a>
                <a href="drivers.php" class="block py-2 px-3 md:p-0 hover:text-f1-red transition duration-150">Drivers</a>
                <a href="results.php" class="block py-2 px-3 md:p-0 hover:text-f1-red transition duration-150">Results</a>
                <a href="standings.php" class="block py-2 px-3 md:p-0 hover:text-f1-red transition duration-150">Standings</a>
            </nav>
        </div>
    </header>
    
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-8 flex-grow container">
        
        <?php if ($circuitDetails && $circuitKey): ?>
            
            <div class="page-header-section bg-f1-gray p-6 rounded-lg shadow-xl mb-8">
                <h1 class="text-4xl md:text-5xl font-oswald font-extrabold text-f1-red uppercase page-heading">
                    <?php echo htmlspecialchars($circuitDetails['grandprix'] ?? 'Onbekend'); ?>
                </h1>
                <p class="text-xl text-gray-300 mt-1"><?php echo htmlspecialchars($circuitDetails['location'] ?? 'Locatie onbekend'); ?></p>
            </div>

            <section class="bg-f1-gray p-6 rounded-lg shadow-xl mb-8 f1-section-pos">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    
                    <div class="f1-section border-l-4 border-f1-red pl-4">
                        <h3 class="text-2xl font-oswald font-bold text-white mb-4">General Information</h3>
                        <div class="space-y-3 text-gray-300">
                            <div class="detail-item flex justify-between">
                                <strong class="text-f1-red font-semibold">Circuit Name:</strong>
                                <span><?php echo htmlspecialchars($circuitDetails['title'] ?? 'N.v.t.'); ?></span>
                            </div>
                            <div class="detail-item flex justify-between">
                                <strong class="text-f1-red font-semibold">First GP Year:</strong>
                                <span><?php echo htmlspecialchars($circuitDetails['first_gp_year'] ?? 'N.v.t.'); ?></span>
                            </div>
                            <div class="detail-item flex justify-between">
                                <strong class="text-f1-red font-semibold">Date & Time:</strong>
                                <span><?php echo htmlspecialchars($circuitDetails['race_datetime'] ? date('d-m-Y H:i', strtotime($circuitDetails['race_datetime'])) : 'N.v.t.'); ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="country-flag-circuit-position flex items-center justify-center">
                        <?php if (!empty($circuitDetails['country_flag_url'])): ?>
                            <img class="country-flag max-h-40 w-full object-contain shadow-lg rounded-lg" 
                                 src="<?php echo htmlspecialchars($circuitDetails['country_flag_url']); ?>" alt="Vlag van Land">
                        <?php else: ?>
                            <p class="text-gray-400">Vlag not available</p>
                        <?php endif; ?>
                    </div>

                    <div class="f1-section border-l-4 border-f1-red pl-4">
                        <h3 class="text-2xl font-oswald font-bold text-white mb-4">Circuit details</h3>
                        <div class="space-y-3 text-gray-300">
                            <div class="detail-item flex justify-between">
                                <strong class="text-f1-red font-semibold">Laps:</strong>
                                <span><?php echo htmlspecialchars($circuitDetails['lap_count'] ?? 'N.v.t.'); ?></span>
                            </div>
                            <div class="detail-item flex justify-between">
                                <strong class="text-f1-red font-semibold">Circuit length:</strong>
                                <span><?php echo htmlspecialchars(number_format($circuitDetails['circuit_length_km'] ?? 0, 3, ',', '.')) . ' km'; ?></span>
                            </div>
                            <div class="detail-item flex justify-between">
                                <strong class="text-f1-red font-semibold">Race distance:</strong>
                                <span><?php echo htmlspecialchars(number_format($circuitDetails['race_distance_km'] ?? 0, 3, ',', '.')) . ' km'; ?></span>
                            </div>
                            <div class="detail-item flex justify-between">
                                <strong class="text-f1-red font-semibold">Lap record:</strong>
                                <span><?php echo htmlspecialchars($circuitDetails['lap_record'] ?? 'N.v.t.'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            
            <?php if (!empty($circuitDetails['map_url'])): ?>
                <div class="circuit-image-display bg-f1-dark-table p-4 rounded-lg shadow-xl mb-8">
                    <img class="circuit-main-map w-full h-auto object-contain rounded-lg" 
                         src="<?php echo htmlspecialchars($circuitDetails['map_url']); ?>" 
                         alt="Kaart van <?php echo htmlspecialchars($circuitDetails['grandprix']); ?>">
                </div>
            <?php endif; ?>

            <div class="back-link-container text-center mt-8 mb-4">
                <a href="kalender.php" class="bg-f1-red text-white py-3 px-6 rounded-lg font-bold uppercase tracking-wider hover:bg-red-700 transition back-link">
                    &larr; Back to shedule 
                </a>
            </div>

        <?php else: ?>
            <div class="bg-f1-gray p-6 rounded-lg shadow-xl text-center">
                <p class="text-red-500 mb-6 message error-message"><?php echo $message; ?></p>
                <div class="back-link-container">
                    <a href="kalender.php" class="bg-f1-red text-white py-3 px-6 rounded-lg font-bold uppercase tracking-wider hover:bg-red-700 transition back-link">
                        &larr; Back to shedule
                    </a>
                </div>
            </div>
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

            if (nav && toggle) {
                toggle.addEventListener('click', () => {
                    const isVisible = nav.getAttribute('data-visible') === 'true';
                    nav.setAttribute('data-visible', String(!isVisible));
                    toggle.setAttribute('aria-expanded', String(!isVisible));
                });
            }
        });
    </script>
</body>
</html>
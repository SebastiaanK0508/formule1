<?php
require_once 'db_config.php';
/** @var PDO $pdo */
$circuitsData = [];
$availableYears = [];
$selectedYear = null;

try {
    $stmt = $pdo->query("SELECT DISTINCT YEAR(race_datetime) AS race_year FROM circuits ORDER BY race_year DESC");
    $availableYears = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (\PDOException $e) {
}
if (isset($_GET['year']) && in_array($_GET['year'], $availableYears)) {
    $selectedYear = $_GET['year'];
} elseif (!empty($availableYears)) {
    $selectedYear = $availableYears[0];
}
if ($selectedYear) {
    try {
        $stmt = $pdo->prepare("
            SELECT circuit_key, grandprix, location, map_url, race_datetime, title
            FROM circuits
            WHERE YEAR(race_datetime) = :selectedYear
            ORDER BY calendar_order ASC
        ");
        $stmt->bindParam(':selectedYear', $selectedYear, PDO::PARAM_INT);
        $stmt->execute();
        $circuitsData = $stmt->fetchAll();
    } catch (\PDOException $e) {
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta name="description" content="Het meest complete F1 archief & statistieken sinds 1950. Vind uitslagen, records en alle coureursdata. Duik in de F1 historie!" />
    <script src="https://t.contentsquare.net/uxa/688c1fe6f0f7c.js"></script>
    <meta charset="UTF-8">
    <link rel="icon" type="image/x-icon" href="/afbeeldingen/logo/f1logobgrm.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formula 1 Schedule - <?php echo htmlspecialchars($selectedYear ?? 'N/A'); ?></title>
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
                <a href="kalender.php" class="block py-2 px-3 md:p-0 text-f1-red border-b-2 border-f1-red md:border-none transition duration-150 active">Schedule</a>
                <a href="teams.php" class="block py-2 px-3 md:p-0 hover:text-f1-red transition duration-150">Teams</a>
                <a href="drivers.php" class="block py-2 px-3 md:p-0 hover:text-f1-red transition duration-150">Drivers</a>
                <a href="results.php" class="block py-2 px-3 md:p-0 hover:text-f1-red transition duration-150">Results</a>
                <a href="standings.php" class="block py-2 px-3 md:p-0 hover:text-f1-red transition duration-150">Standings</a>
            </nav>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-8 flex-grow container">
        
        <section class="bg-f1-gray p-6 rounded-lg shadow-xl mb-8 flex flex-col md:flex-row justify-between items-center page-header-section">
            <h2 class="text-xl md:text-3xl font-oswald font-bold text-white uppercase page-heading mb-4 md:mb-0">
                F1 SEASON <?php echo htmlspecialchars($selectedYear ?? 'N/A'); ?> SCHEDULE
            </h2>
            <form method="GET" action="kalender.php" class="year-selector flex items-center space-x-2">
                <label for="year-select" class="text-gray-300 text-sm font-semibold">Select year:</label>
                <select name="year" id="year-select" onchange="this.form.submit()"
                        class="bg-f1-black border border-gray-600 text-white p-2 rounded-md focus:ring-f1-red focus:border-f1-red text-sm cursor-pointer">
                    <?php foreach ($availableYears as $year): ?>
                        <option value="<?php echo htmlspecialchars($year); ?>"
                                <?php echo ($year == $selectedYear) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($year); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </section>
        
        <section class="f1-section">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 data-card-row">
                <?php if (!empty($circuitsData)): ?>
                    <?php foreach ($circuitsData as $circuit): ?>
                        <?php
                            $raceDateTime = new DateTime($circuit['race_datetime']);
                            $raceDay = $raceDateTime->format('j');
                            $raceYear = $raceDateTime->format('Y');
                            $raceMonth = strftime('%B', $raceDateTime->getTimestamp());
                            $displayDate = ($raceDay - 2) . ' - ' . $raceDay . ' ' . $raceMonth . ' ' . $raceYear;
                            if ($circuit['circuit_key'] === 'las_vegas') {
                                $displayDate = ($raceDay - 1) . ' - ' . $raceDay . ' ' . $raceMonth . ' ' . $raceYear . ' (Zaterdag)';
                            }
                        ?>
                        <article class="bg-f1-gray rounded-xl shadow-lg overflow-hidden transition-all duration-300 hover:shadow-2xl hover:scale-[1.02] data-card">
                            <a href="circuit-details.php?key=<?php echo htmlspecialchars($circuit['circuit_key']); ?>" class="block data-link">
                                <div class="w-full h-36 overflow-hidden race-image-container">
                                    <img src="<?php echo htmlspecialchars($circuit['map_url']); ?>" alt="Circuit <?php echo htmlspecialchars($circuit['grandprix']); ?>" 
                                         class="w-full h-full object-cover circuit-image transform transition duration-500 hover:scale-110">
                                </div>
                                <div class="p-4 info">
                                    <h4 class="text-lg font-oswald font-bold text-f1-red mb-2 uppercase">
                                        <?php echo htmlspecialchars($circuit['grandprix']); ?>
                                    </h4>
                                    <p class="text-sm text-gray-300">
                                        <span class="font-semibold">Date:</span> <?php echo htmlspecialchars($displayDate); ?>
                                    </p>
                                    <p class="text-sm text-gray-300">
                                        <span class="font-semibold">Location:</span> <?php echo htmlspecialchars($circuit['location']); ?>
                                    </p>
                                    <p class="text-sm text-gray-300">
                                        <span class="font-semibold">Circuit:</span> <?php echo htmlspecialchars($circuit['title']); ?>
                                    </p>
                                </div>
                            </a>
                        </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-gray-400 lg:col-span-4 p-6 bg-f1-gray rounded-lg">
                        No race found for the selected year<?php echo htmlspecialchars($selectedYear); ?>.
                    </p>
                <?php endif; ?>
            </div>
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
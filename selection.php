<?php
require_once 'achterkant/aanpassing/api-koppelingen/result_api.php';
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formula 1 - Select Race / Year</title>
    <link rel="icon" type="image/x-icon" href="/afbeeldingen/logo/f1logobgrm.png">
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <script src="https://t.contentsquare.net/uxa/688c1fe6f0f7c.js"></script>
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
                        'f1-table-header': '#21212B', 
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
                Select Race / Year
            </h2>
        </section>
    
        <section class="result-container flex justify-center">
            <?php if ($error_message): ?>
            <div class="bg-red-800 text-white p-4 rounded-lg shadow-md w-full max-w-lg">
                <?php echo $error_message; ?>
            </div>
            <?php else: ?>
                <div class="selection-container w-full max-w-lg bg-f1-gray p-6 rounded-lg shadow-xl mobile-full-view">
                    
                    <form action="selection.php" method="get" class="mb-6">
                        <label for="year" class="block text-lg font-semibold text-white mb-2">Select a Year</label>
                        <select id="year" name="year" onchange="this.form.submit()"
                                class="w-full bg-f1-table-header border border-gray-600 text-white p-3 rounded-lg focus:ring-f1-red focus:border-f1-red cursor-pointer appearance-none">
                            <?php foreach ($available_years as $year): ?>
                                <option value="<?php echo htmlspecialchars($year); ?>" 
                                        <?php echo ($selected_year == $year) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($year); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <noscript><input type="submit" value="Selecteer" class="mt-4 bg-f1-red text-white font-bold py-2 px-4 rounded hover:bg-red-700 transition"></noscript>
                    </form>
                    
                    <h3 class="text-xl font-oswald font-semibold text-white mb-4 border-b border-gray-600 pb-2">
                        Races voor <?php echo htmlspecialchars($selected_year); ?>
                    </h3>

                    <?php if (!empty($races_in_season)): ?>
                        <div class="race-link-list space-y-3">
                            <?php foreach ($races_in_season as $race): ?>
                                <a href="results.php?year=<?php echo htmlspecialchars($selected_year); ?>&round=<?php echo htmlspecialchars($race['round']); ?>"
                                   class="block bg-f1-table-header text-white p-3 rounded-lg shadow hover:bg-f1-red transition duration-150">
                                     <?php echo htmlspecialchars($race['raceName']); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-gray-400">Geen races gevonden</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
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
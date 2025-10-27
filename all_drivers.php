<?php
// Pad naar het JSON-bestand
$jsonFile = 'achterkant/aanpassing/api-koppelingen/json/drivers.json';
$allDrivers = [];
$error_message = '';

if (file_exists($jsonFile)) {
    $jsonData = file_get_contents($jsonFile);
    $allDrivers = json_decode($jsonData, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("Fout bij het decoderen van JSON: " . json_last_error_msg());
        $allDrivers = [];
        $error_message = "Fout: Kon coureursgegevens niet correct decoderen.";
    }
} else {
    error_log("JSON-bestand niet gevonden: " . $jsonFile);
    $error_message = "Fout: Coureursgegevensbestand niet gevonden.";
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <script src="https://t.contentsquare.net/uxa/688c1fe6f0f7c.js"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Drivers Formula 1</title>
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
        /* Mobile menu styles */
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
                <a href="kalender.php" class="block py-2 px-3 md:p-0 hover:text-f1-red transition duration-150">Schedule</a>
                <a href="teams.php" class="block py-2 px-3 md:p-0 hover:text-f1-red transition duration-150">Teams</a>
                <a href="drivers.php" class="block py-2 px-3 md:p-0 text-f1-red border-b-2 border-f1-red md:border-none active transition duration-150">Drivers</a>
                <a href="results.php" class="block py-2 px-3 md:p-0 hover:text-f1-red transition duration-150">Results</a>
                <a href="standings.php" class="block py-2 px-3 md:p-0 hover:text-f1-red transition duration-150">Standings</a>
            </nav>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-8 flex-grow container">
        
        <section class="bg-f1-gray p-6 rounded-lg shadow-xl mb-8 page-header-section">
            <h2 class="text-xl md:text-3xl font-oswald font-bold text-white uppercase page-heading text-center">
                ALL DRIVERS FORMULA 1
            </h2>
        </section>

        <section class="driver-list-section bg-f1-gray p-6 rounded-lg shadow-xl">
            
            <div class="filter-controls flex flex-col sm:flex-row gap-4 mb-8">
                <input type="text" id="searchInput" placeholder="Zoek op naam..."
                       class="flex-1 p-3 bg-f1-black border border-gray-700 text-white rounded-md focus:ring-f1-red focus:border-f1-red"
                       aria-label="Zoeken op coureursnaam">
                
                <select id="sortSelect"
                        class="p-3 bg-f1-black border border-gray-700 text-white rounded-md focus:ring-f1-red focus:border-f1-red sm:w-auto">
                    <option value="az">Naam (A-Z)</option>
                    <option value="za">Naam (Z-A)</option>
                    <option value="oldest">Geboortedatum (Oudst eerst)</option>
                    <option value="youngest">Geboortedatum (Jongst eerst)</option>
                </select>
            </div>
            
            <?php if ($error_message): ?>
                <p class="text-red-500 p-4 bg-f1-dark-table rounded-lg"><?php echo $error_message; ?></p>
            <?php elseif (empty($allDrivers)): ?>
                <p class="text-gray-400 p-4">Geen coureurs beschikbaar om weer te geven.</p>
            <?php else: ?>
            
            <ul class="driver-list space-y-3" id="driverList">
                <?php foreach ($allDrivers as $driver): ?>
                    <li class="driver-item bg-f1-dark-table p-4 rounded-lg shadow-md border-l-4 border-f1-red transition duration-200 hover:bg-black" 
                        data-name="<?php echo htmlspecialchars($driver['firstName'] . ' ' . $driver['lastName']); ?>" 
                        data-dob="<?php echo htmlspecialchars($driver['dateOfBirth']); ?>">
                        
                        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center">
                            <span class="driver-name-all text-xl font-oswald font-bold text-white uppercase mb-2 sm:mb-0">
                                <?php echo htmlspecialchars($driver['firstName'] . ' ' . $driver['lastName']); ?>
                            </span>
                            
                            <div class="driver-details-list text-sm text-gray-300 space-y-1 sm:space-y-0 sm:space-x-4 sm:flex">
                                <span class="driver-info">
                                    <strong class="driver-info-strong font-semibold text-f1-red">Geboortedatum:</strong> <?php echo htmlspecialchars($driver['dateOfBirth']); ?>
                                </span>
                                <span class="driver-info hidden md:inline">|</span>
                                <span class="driver-info">
                                    <strong class="driver-info-strong font-semibold text-f1-red">Geboorteplaats:</strong> <?php echo htmlspecialchars($driver['placeOfBirth']),', ', htmlspecialchars($driver['countryOfBirthCountryId']); ?>
                                </span>
                                <span class="driver-info hidden md:inline">|</span>
                                <span class="driver-info">
                                    <strong class="driver-info-strong font-semibold text-f1-red">Nationaliteit:</strong> <?php echo htmlspecialchars($driver['nationalityCountryId']); ?>
                                </span>
                            </div>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
            
            <?php endif; ?>
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
    
    <script src="all_drivers_filter.js"></script>
    
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
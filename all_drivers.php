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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Het meest complete F1 archief & statistieken sinds 1950. Vind uitslagen, records en alle coureursdata. Duik in de F1 historie!" />
    <title>All Drivers | F1SITE.NL</title>
    
    <link rel="icon" type="image/x-icon" href="/afbeeldingen/logo/f1logobgrm.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;700;900&family=Oswald:wght@700&display=swap" rel="stylesheet">
    
    <script src="https://t.contentsquare.net/uxa/688c1fe6f0f7c.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { 
                        'sans': ['Inter', 'sans-serif'], 
                        'oswald': ['Oswald', 'sans-serif'] 
                    },
                    colors: { 
                        'f1-red': '#E10600', 
                        'f1-black': '#0b0b0f', 
                        'f1-card': '#16161c' 
                    }
                }
            }
        }
    </script>

    <style>
        body { background-color: #0b0b0f; color: #fff; }
        .bg-pattern {
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.02'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }

        .elite-card {
            @apply bg-f1-card/50 p-6 md:p-10 rounded-[2.5rem] border border-white/5 shadow-2xl backdrop-blur-sm;
        }

        .driver-item-card {
            @apply bg-black/40 p-5 rounded-2xl border border-white/5 transition-all duration-300 hover:border-f1-red/50 hover:bg-black/60 shadow-lg;
        }

        .form-input-elite {
            @apply flex-1 p-3 bg-black/40 border border-white/10 text-white rounded-xl focus:ring-2 focus:ring-f1-red focus:border-transparent transition-all outline-none italic text-sm;
        }

        /* Mobile menu handling */
        @media (max-width: 767px) {
            .main-nav[data-visible="false"] { display: none; }
            .main-nav {
                position: absolute; top: 100%; left: 0; right: 0;
                background-color: #0b0b0f; padding: 1.5rem; display: flex;
                flex-direction: column; z-index: 100; border-bottom: 2px solid #E10600;
            }
        }

        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #0b0b0f; }
        ::-webkit-scrollbar-thumb { background: #E10600; border-radius: 10px; }
    </style>
</head>
<body class="bg-pattern min-h-screen flex flex-col italic">

    <header class="bg-black/90 backdrop-blur-md sticky top-0 z-50 border-b border-f1-red/30">
        <div class="max-w-7xl mx-auto px-6 py-5 flex justify-between items-center relative">
            <h1 class="text-3xl font-oswald font-black italic text-white uppercase tracking-tighter">
                F1SITE<span class="text-f1-red">.NL</span>
            </h1>
            
            <button class="md:hidden text-2xl text-f1-red menu-toggle" aria-expanded="false">&#9776;</button>

            <nav class="main-nav hidden md:flex space-x-8 text-[11px] font-black uppercase tracking-widest text-white" id="main-nav-links" data-visible="false">
                <a href="index.php" class="hover:text-f1-red transition">Home</a>
                <a href="kalender.php" class="hover:text-f1-red transition">Schedule</a>
                <a href="teams.php" class="hover:text-f1-red transition">Teams</a>
                <a href="drivers.php" class="text-f1-red transition">Drivers</a>
                <a href="results.php" class="hover:text-f1-red transition">Results</a>
                <a href="standings.php" class="hover:text-f1-red transition">Standings</a>
            </nav>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-6 py-16 flex-grow">
        
        <div class="mb-16 text-center">
            <h1 class="text-6xl md:text-8xl font-oswald font-black uppercase italic tracking-tighter leading-none mb-4">
                ALL<span class="text-f1-red">DRIVERS</span>
            </h1>
        </div>

        <div class="elite-card">
            <div class="flex flex-col sm:flex-row gap-4 mb-12">
                <input type="text" id="searchInput" placeholder="ZOEK OP NAAM..."
                       class="form-input-elite"
                       aria-label="Zoeken op coureursnaam">
                
                <select id="sortSelect"
                        class="form-input-elite sm:w-64 cursor-pointer">
                    <option value="az">NAME (A-Z)</option>
                    <option value="za">NAME (Z-A)</option>
                    <option value="oldest">DOB (OLDEST FIRST)</option>
                    <option value="youngest">DOB (YOUNGEST FIRST)</option>
                </select>
            </div>

            <?php if ($error_message): ?>
                <div class="p-6 bg-red-900/20 border border-red-500/50 rounded-2xl text-red-500 text-center uppercase font-black tracking-widest text-xs">
                    <?php echo $error_message; ?>
                </div>
            <?php elseif (empty($allDrivers)): ?>
                <p class="text-gray-500 text-center italic">Geen coureurs beschikbaar om weer te geven.</p>
            <?php else: ?>
            
                <ul class="grid grid-cols-1 gap-4" id="driverList">
                    <?php foreach ($allDrivers as $driver): ?>
                        <li class="driver-item-card driver-item" 
                            data-name="<?php echo htmlspecialchars($driver['firstName'] . ' ' . $driver['lastName']); ?>" 
                            data-dob="<?php echo htmlspecialchars($driver['dateOfBirth']); ?>">
                            
                            <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4">
                                <span class="text-2xl font-oswald font-black text-white uppercase italic tracking-tighter">
                                    <?php echo htmlspecialchars($driver['firstName']); ?> <span class="text-f1-red"><?php echo htmlspecialchars($driver['lastName']); ?></span>
                                </span>
                                
                                <div class="flex flex-wrap gap-y-2 gap-x-6 text-[11px] uppercase font-bold tracking-widest text-gray-400">
                                    <div class="flex items-center gap-2">
                                        <span class="text-f1-red">DOB:</span> 
                                        <span class="text-white"><?php echo htmlspecialchars($driver['dateOfBirth']); ?></span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-f1-red">BORN:</span> 
                                        <span class="text-white"><?php echo htmlspecialchars($driver['placeOfBirth']) . ', ' . htmlspecialchars($driver['countryOfBirthCountryId']); ?></span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-f1-red">NAT:</span> 
                                        <span class="text-white"><?php echo htmlspecialchars($driver['nationalityCountryId']); ?></span>
                                    </div>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            
            <?php endif; ?>
        </div>
    </main>

    <footer class="bg-black py-12 border-t-2 border-f1-red mt-12">
        <div class="max-w-7xl mx-auto px-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-left pb-8 border-b border-white/5">
                <div>
                    <h3 class="text-xl font-oswald font-black text-white italic tracking-tighter mb-2 uppercase">F1SITE.NL</h3>
                    <p class="text-gray-500 text-xs italic uppercase tracking-widest">De snelste bron voor F1 nieuws en data.</p>
                </div>
                <div>
                    <h4 class="text-sm font-oswald font-bold text-f1-red mb-3 uppercase italic">Externe Sites</h4>
                    <ul class="space-y-2">
                        <li><a href="https://www.webbair.nl" target="_blank" class="text-gray-400 text-xs hover:text-white transition italic uppercase">Webbair (Ontwikkelaar)</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-sm font-oswald font-bold text-f1-red mb-3 uppercase italic">Navigatie & Info</h4>
                    <ul class="space-y-2 text-xs uppercase italic tracking-wider">
                        <li><a href="sitemap.html" class="text-gray-400 hover:text-white transition">Sitemap</a></li>
                        <li><a href="privacy-en.html" class="text-gray-400 hover:text-white transition">Privacy Policy (EN)</a></li>
                        <li><a href="algemenevoorwaarden-en.html" class="text-gray-400 hover:text-white transition">Terms and Conditions (EN)</a></li>
                        <li><a href="contact.html" class="text-gray-400 hover:text-white transition">Contact</a></li>
                    </ul>
                </div>
            </div>
            <div class="text-center pt-8 text-gray-600 text-[10px] font-black uppercase tracking-[0.5em] italic">
                &copy; <?php echo (date('Y')); ?> Webbair. Alle rechten voorbehouden.
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
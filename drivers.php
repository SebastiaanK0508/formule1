<?php
require_once 'db_config.php';
/** @var PDO $pdo */
$allDrivers = []; 
try {
    $stmt = $pdo->query("SELECT d.driver_id, d.first_name, d.last_name, d.driver_number, d.flag_url, t.team_name, t.full_team_name, t.team_id, t.team_color FROM drivers d LEFT JOIN teams t ON d.team_id = t.team_id WHERE d.is_active = TRUE ORDER BY d.driver_number ASC");
    $allDrivers = $stmt->fetchAll();
} catch (\PDOException $e) {
    error_log("Fout bij het ophalen van alle coureurs: " . $e->getMessage());
}

// SEO Schema Data
$schemaData = [
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'CollectionPage',
            'url' => 'https://f1site.online/drivers.php', 
            'name' => 'Formula 1 Drivers 2026',
            'about' => 'Huidige coureurs, teams en nummers voor het Formule 1-seizoen.',
        ]
    ]
];

$driverListItems = [];
foreach ($allDrivers as $index => $driver) {
    $driverSlug = strtolower(str_replace(' ', '-', htmlspecialchars($driver['first_name'] . '-' . $driver['last_name'])));
    $driverListItems[] = [
        '@type' => 'ListItem',
        'position' => $index + 1,
        'item' => [
            '@type' => 'Person',
            'name' => htmlspecialchars($driver['first_name']) . ' ' . htmlspecialchars($driver['last_name']),
            'jobTitle' => 'Formula 1 Driver',
            'alumniOf' => ['@type' => 'SportsTeam', 'name' => htmlspecialchars($driver['full_team_name'])]
        ]
    ];
}
if (!empty($driverListItems)) {
    $schemaData['@graph'][] = [
        '@type' => 'ItemList',
        'itemListElement' => $driverListItems
    ];
}
?>
<!DOCTYPE html>
<html lang="nl" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>F1 Drivers <?php echo date('Y'); ?> | F1SITE.NL</title>
    <meta name="description" content="Bekijk alle actieve Formule 1 coureurs, hun racenummers en teams." />
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;700;900&family=Oswald:wght@700&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { 'sans': ['Inter', 'sans-serif'], 'oswald': ['Oswald', 'sans-serif'] },
                    colors: { 'f1-red': '#E10600', 'f1-dark': '#0b0b0f', 'f1-card': '#16161c' }
                }
            }
        }
    </script>

    <style>
        body { background-color: #0b0b0f; color: #fff; overflow-x: hidden; }
        .bg-pattern {
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
        .header-glass { background: rgba(11, 11, 15, 0.9); backdrop-filter: blur(15px); border-bottom: 1px solid rgba(225, 6, 0, 0.3); }
        .f1-border { position: relative; }
        .f1-border::before { content: ""; position: absolute; top: 0; left: 0; width: 45px; height: 4px; background: #E10600; z-index: 10; }
        
        #mobile-menu { transform: translateX(100%); transition: transform 0.4s ease-in-out; background: #0b0b0f; z-index: 101; }
        #mobile-menu.active { transform: translateX(0); }

        .driver-card { transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
        .driver-card:hover { transform: scale(1.03); border-color: rgba(225, 6, 0, 0.4); }
    </style>
    
    <script type="application/ld+json"><?php echo json_encode($schemaData); ?></script>
</head>
<body class="bg-pattern">

    <div id="mobile-menu" class="fixed inset-y-0 right-0 w-full p-10 flex flex-col items-center justify-center">
        <button onclick="toggleMenu()" class="absolute top-8 right-8 text-5xl font-light">&times;</button>
        <nav class="flex flex-col space-y-10 text-4xl font-oswald font-black uppercase italic text-center">
            <a href="index.php" onclick="toggleMenu()">Home</a>
            <a href="kalender.php" onclick="toggleMenu()">Schedule</a>
            <a href="teams.php" onclick="toggleMenu()">Teams</a>
            <a href="drivers.php" class="text-f1-red" onclick="toggleMenu()">Drivers</a>
            <a href="results.php" onclick="toggleMenu()">Results</a>
            <a href="standings.php" onclick="toggleMenu()">Standings</a>
        </nav>
    </div>

    <header class="header-glass sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-6 py-5 flex justify-between items-center">
            <a href="index.php" class="text-3xl font-oswald font-black italic tracking-tighter text-white uppercase">F1SITE<span class="text-f1-red">.NL</span></a>
            <nav class="hidden lg:flex space-x-10 text-[11px] font-bold uppercase tracking-[0.25em]">
                <a href="index.php" class="hover:text-f1-red transition">Home</a>
                <a href="kalender.php" class="hover:text-f1-red transition">Schedule</a>
                <a href="teams.php" class="hover:text-f1-red transition">Teams</a>
                <a href="drivers.php" class="text-f1-red border-b-2 border-f1-red pb-1">Drivers</a>
                <a href="results.php" class="hover:text-f1-red transition">Results</a>
                <a href="standings.php" class="hover:text-f1-red transition">Standings</a>
            </nav>
            <button onclick="toggleMenu()" class="lg:hidden text-white text-3xl">☰</button>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-6 py-12">
        
        <section class="mb-16" data-aos="fade-down">
            <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                <div>
                    <h2 class="text-4xl md:text-6xl font-oswald font-black uppercase italic tracking-tighter leading-none">
                        F1 <span class="text-f1-red">DRIVERS</span> <?php echo date('Y'); ?>
                    </h2>
                    <p class="text-gray-500 text-xs font-black uppercase tracking-[0.4em] mt-4 flex items-center gap-2">
                        <span class="w-8 h-[1px] bg-f1-red"></span> The official grid
                    </p>
                </div>
                <a href="all_drivers.php" class="group flex items-center gap-4 bg-f1-card border border-white/5 px-8 py-4 rounded-full hover:bg-f1-red transition-all duration-300">
                    <span class="text-[10px] font-black uppercase tracking-widest italic">All Drivers Ever</span>
                    <span class="text-f1-red group-hover:text-white transition-colors text-xl">→</span>
                </a>
            </div>
        </section>

        <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
            <?php if (!empty($allDrivers)): ?>
                <?php $i=0; foreach ($allDrivers as $driver): ?>
                    <?php
                        $driverSlug = strtolower(str_replace(' ', '-', htmlspecialchars($driver['first_name'] . '-' . $driver['last_name'])));
                        $driverPageUrl = 'driver-details.php?slug=' . $driverSlug;
                        $teamColor = $driver['team_color'] ?: '#E10600';
                    ?>
                    <article data-aos="fade-up" data-aos-delay="<?php echo $i*40; ?>" 
                             class="driver-card f1-border bg-f1-card rounded-br-3xl border-r border-b border-white/5 overflow-hidden group">
                        
                        <a href="<?php echo $driverPageUrl; ?>" class="block p-6">
                            <div class="flex items-start justify-between mb-8">
                                <div class="flex flex-col">
                                    <span class="text-5xl font-oswald font-black italic opacity-20 group-hover:opacity-100 transition-opacity" style="color: <?php echo $teamColor; ?>;">
                                        <?php echo htmlspecialchars($driver['driver_number']); ?>
                                    </span>
                                    <div class="w-10 h-1 mt-1" style="background-color: <?php echo $teamColor; ?>;"></div>
                                </div>
                                <?php if($driver['flag_url']): ?>
                                    <img src="<?php echo htmlspecialchars($driver['flag_url']); ?>" alt="Nationality" class="w-8 shadow-lg rounded-sm opacity-80">
                                <?php endif; ?>
                            </div>

                            <div class="mb-6">
                                <h3 class="text-lg font-medium text-gray-400 leading-none mb-1"><?php echo htmlspecialchars($driver['first_name']); ?></h3>
                                <h4 class="text-2xl font-oswald font-black uppercase italic tracking-tight group-hover:text-f1-red transition-colors">
                                    <?php echo htmlspecialchars($driver['last_name']); ?>
                                </h4>
                            </div>
                            
                            <div class="pt-6 border-t border-white/5 flex flex-col gap-1">
                                <span class="text-[10px] font-black uppercase tracking-widest text-gray-500">Team</span>
                                <span class="text-xs font-bold text-gray-300 truncate italic tracking-wider"><?php echo htmlspecialchars($driver['full_team_name']); ?></span>
                            </div>

                            <div class="mt-8 flex justify-end">
                                <span class="text-f1-red opacity-0 group-hover:opacity-100 transition-all transform translate-x-4 group-hover:translate-x-0">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                                </span>
                            </div>
                        </a>
                    </article>
                <?php $i++; endforeach; ?>
            <?php else: ?>
                <div class="col-span-full py-20 text-center bg-f1-card rounded-3xl border border-white/5">
                    <p class="text-gray-500 font-bold uppercase tracking-widest italic animate-pulse">Waiting for drivers to exit the pit lane...</p>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <footer class="bg-black mt-24 py-16 border-t-2 border-f1-red">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-12 text-left pb-12 border-b border-white/5">
                <div class="space-y-4 text-center md:text-left">
                    <h3 class="text-2xl font-oswald font-black text-white italic tracking-tighter uppercase">F1SITE<span class="text-f1-red">.NL</span></h3>
                    <p class="text-gray-500 text-sm font-medium leading-relaxed max-w-xs mx-auto md:mx-0">De snelste bron voor Formule 1 nieuws, statistieken en coureursdata.</p>
                </div>

                <div class="text-center md:text-left">
                    <h4 class="text-xs font-black text-f1-red mb-6 uppercase tracking-[0.3em]">Ontwikkelaar</h4>
                    <ul class="space-y-4">
                        <li><a href="https://www.webius.nl" target="_blank" class="text-gray-400 text-sm font-bold hover:text-white transition duration-200 block uppercase tracking-wider">Webius</a></li>
                    </ul>
                </div>

                <div class="text-center md:text-left">
                    <h4 class="text-xs font-black text-f1-red mb-6 uppercase tracking-[0.3em]">Navigatie & Info</h4>
                    <ul class="space-y-4">
                        <li><a href="sitemap.html" class="text-gray-400 text-sm font-bold hover:text-white transition duration-200 block uppercase tracking-wider">Sitemap</a></li>
                        <li><a href="privacy-en.html" class="text-gray-400 text-sm font-bold hover:text-white transition duration-200 block uppercase tracking-wider">Privacy Policy</a></li>
                        <li><a href="contact.html" class="text-gray-400 text-sm font-bold hover:text-white transition duration-200 block uppercase tracking-wider">Contact</a></li>
                    </ul>
                </div>
            </div>
            <div class="pt-10 text-center md:text-left">
                <p class="text-gray-600 text-[10px] font-black uppercase tracking-[0.5em] italic">&copy; 2026 WEBIUS. Alle rechten voorbehouden.</p>
            </div>
        </div>
    </footer>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            AOS.init({ duration: 800, once: true });
            window.toggleMenu = () => { document.getElementById('mobile-menu').classList.toggle('active'); };
        });
    </script> 
</body>
</html>
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
    error_log($e->getMessage());
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
        error_log($e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="nl" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>F1 Schedule <?php echo htmlspecialchars($selectedYear); ?> | F1SITE.NL</title>
    <meta name="description" content="Bekijk de volledige Formule 1 kalender van <?php echo htmlspecialchars($selectedYear); ?>." />
    
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

        .img-ratio { position: relative; width: 100%; padding-top: 56.25%; overflow: hidden; background: #000; }
        .img-ratio img { position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: contain; padding: 10px; transition: transform 0.5s ease; }
        .race-card:hover img { transform: scale(1.1); }

        #mobile-menu { transform: translateX(100%); transition: transform 0.4s ease-in-out; background: #0b0b0f; z-index: 101; }
        #mobile-menu.active { transform: translateX(0); }

        select { background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%23E10600'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E"); background-position: right 0.5rem center; background-repeat: no-repeat; background-size: 1.5em 1.5em; padding-right: 2.5rem; -webkit-appearance: none; }
    </style>
</head>
<body class="bg-pattern">

    <div id="mobile-menu" class="fixed inset-y-0 right-0 w-full p-10 flex flex-col items-center justify-center">
        <button onclick="toggleMenu()" class="absolute top-8 right-8 text-5xl font-light">&times;</button>
        <nav class="flex flex-col space-y-10 text-4xl font-oswald font-black uppercase italic text-center">
            <a href="index.php" onclick="toggleMenu()">Home</a>
            <a href="kalender.php" class="text-f1-red" onclick="toggleMenu()">Schedule</a>
            <a href="teams.php" onclick="toggleMenu()">Teams</a>
            <a href="drivers.php" onclick="toggleMenu()">Drivers</a>
            <a href="results.php" onclick="toggleMenu()">Results</a>
            <a href="standings.php" onclick="toggleMenu()">Standings</a>
        </nav>
    </div>

    <header class="header-glass sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-6 py-5 flex justify-between items-center">
            <a href="index.php" class="text-3xl font-oswald font-black italic tracking-tighter text-white uppercase">F1SITE<span class="text-f1-red">.NL</span></a>
            <nav class="hidden lg:flex space-x-10 text-[11px] font-bold uppercase tracking-[0.25em]">
                <a href="index.php" class="hover:text-f1-red transition">Home</a>
                <a href="kalender.php" class="text-f1-red border-b-2 border-f1-red pb-1">Schedule</a>
                <a href="teams.php" class="hover:text-f1-red transition">Teams</a>
                <a href="drivers.php" class="hover:text-f1-red transition">Drivers</a>
                <a href="results.php" class="hover:text-f1-red transition">Results</a>
                <a href="standings.php" class="hover:text-f1-red transition">Standings</a>
            </nav>
            <button onclick="toggleMenu()" class="lg:hidden text-white text-3xl">☰</button>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-6 py-12">
        
        <section class="mb-16" data-aos="fade-down">
            <div class="bg-f1-card border border-white/5 p-8 md:p-12 rounded-[2.5rem] flex flex-col md:flex-row justify-between items-center gap-8">
                <div>
                    <h2 class="text-4xl md:text-6xl font-oswald font-black uppercase italic tracking-tighter">
                        F1 SEASON <span class="text-f1-red"><?php echo htmlspecialchars($selectedYear); ?></span>
                    </h2>
                    <p class="text-gray-400 uppercase tracking-[0.3em] text-xs font-bold mt-2">Official Race Calendar</p>
                </div>

                <form method="GET" action="kalender.php" class="relative">
                    <select name="year" onchange="this.form.submit()" 
                            class="bg-f1-dark border-2 border-f1-red/30 text-white px-6 py-3 rounded-full font-bold focus:border-f1-red outline-none transition-all cursor-pointer hover:bg-f1-red/10">
                        <?php foreach ($availableYears as $year): ?>
                            <option value="<?php echo htmlspecialchars($year); ?>" <?php echo ($year == $selectedYear) ? 'selected' : ''; ?>>
                                SEASON <?php echo htmlspecialchars($year); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>
        </section>
        
        <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
            <?php if (!empty($circuitsData)): ?>
                <?php $i=0; foreach ($circuitsData as $circuit): ?>
                    <?php
                        $raceDateTime = new DateTime($circuit['race_datetime']);
                        $raceDay = $raceDateTime->format('j');
                        $raceYear = $raceDateTime->format('Y');
                        setlocale(LC_TIME, 'nl_NL', 'Dutch');
                        $raceMonth = strftime('%B', $raceDateTime->getTimestamp());
                        
                        $displayDate = ($raceDay - 2) . ' - ' . $raceDay . ' ' . $raceMonth;
                        if ($circuit['circuit_key'] === 'las_vegas') {
                            $displayDate = ($raceDay - 1) . ' - ' . $raceDay . ' ' . $raceMonth . ' (Sat)';
                        }
                    ?>
                    <article data-aos="fade-up" data-aos-delay="<?php echo $i*50; ?>" 
                             class="race-card f1-border bg-f1-card rounded-br-3xl border-r border-b border-white/5 flex flex-col transition-all duration-500 hover:border-f1-red/40 overflow-hidden">
                        
                        <a href="circuit-details.php?key=<?php echo htmlspecialchars($circuit['circuit_key']); ?>" class="block group">
                            <div class="img-ratio">
                                <img src="<?php echo htmlspecialchars($circuit['map_url']); ?>" alt="<?php echo htmlspecialchars($circuit['grandprix']); ?>">
                                <div class="absolute inset-0 bg-gradient-to-t from-f1-card to-transparent opacity-60"></div>
                            </div>
                            
                            <div class="p-8">
                                <div class="flex items-center justify-between mb-4">
                                    <span class="text-f1-red text-[10px] font-black uppercase tracking-widest italic">Round <?php echo ($i + 1); ?></span>
                                    <span class="bg-white/5 px-3 py-1 rounded-full text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                                        <?php echo htmlspecialchars($raceYear); ?>
                                    </span>
                                </div>
                                
                                <h3 class="text-2xl font-oswald font-black uppercase italic mb-6 group-hover:text-f1-red transition-colors">
                                    <?php echo htmlspecialchars($circuit['grandprix']); ?>
                                </h3>

                                <div class="space-y-3">
                                    <div class="flex items-center gap-3 text-sm">
                                        <span class="text-f1-red font-bold">DATE:</span>
                                        <span class="text-gray-300 font-medium"><?php echo htmlspecialchars($displayDate); ?></span>
                                    </div>
                                    <div class="flex items-center gap-3 text-sm">
                                        <span class="text-f1-red font-bold">LOC:</span>
                                        <span class="text-gray-400"><?php echo htmlspecialchars($circuit['location']); ?></span>
                                    </div>
                                </div>

                                <div class="mt-8 pt-6 border-t border-white/5 flex justify-between items-center">
                                    <span class="text-[10px] font-black uppercase tracking-widest text-gray-500">Circuit Details</span>
                                    <span class="text-f1-red text-xl transform group-hover:translate-x-2 transition-transform">→</span>
                                </div>
                            </div>
                        </a>
                    </article>
                <?php $i++; endforeach; ?>
            <?php else: ?>
                <div class="lg:col-span-3 text-center py-20 bg-f1-card rounded-3xl border border-white/5">
                    <p class="text-gray-400 font-bold uppercase tracking-widest">Geen races gevonden voor dit jaar.</p>
                </div>
            <?php endif; ?>
        </section>
    </main>
    
    <footer class="bg-black mt-24 py-16 border-t-2 border-f1-red">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-12 text-left pb-12 border-b border-white/5">
                
                <div class="space-y-4 text-center md:text-left">
                    <h3 class="text-2xl font-oswald font-black text-white italic tracking-tighter uppercase">F1SITE<span class="text-f1-red">.NL</span></h3>
                    <p class="text-gray-500 text-sm font-medium leading-relaxed max-w-xs mx-auto md:mx-0">
                        De snelste bron voor het laatste Formule 1 nieuws, statistieken en race-updates.
                    </p>
                </div>

                <div class="text-center md:text-left">
                    <h4 class="text-xs font-black text-f1-red mb-6 uppercase tracking-[0.3em]">Ontwikkelaar</h4>
                    <ul class="space-y-4">
                        <li>
                            <a href="https://www.webius.nl" target="_blank" class="text-gray-400 text-sm font-bold hover:text-white transition duration-200 block uppercase tracking-wider">
                                Webius
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="text-center md:text-left">
                    <h4 class="text-xs font-black text-f1-red mb-6 uppercase tracking-[0.3em]">Navigatie & Info</h4>
                    <ul class="space-y-4">
                        <li><a href="sitemap.html" class="text-gray-400 text-sm font-bold hover:text-white transition duration-200 block uppercase tracking-wider">Sitemap</a></li>
                        <li><a href="privacy-en.html" class="text-gray-400 text-sm font-bold hover:text-white transition duration-200 block uppercase tracking-wider">Privacy Policy (EN)</a></li>
                        <li><a href="algemenevoorwaarden-en.html" class="text-gray-400 text-sm font-bold hover:text-white transition duration-200 block uppercase tracking-wider">Terms and Conditions (EN)</a></li>
                        <li><a href="contact.html" class="text-gray-400 text-sm font-bold hover:text-white transition duration-200 block uppercase tracking-wider">Contact</a></li>
                    </ul>
                </div>
            </div>

            <div class="pt-10 text-center md:text-left">
                <p class="text-gray-600 text-[10px] font-black uppercase tracking-[0.5em] italic">
                    &copy; 2026 WEBIUS. Alle rechten voorbehouden.
                </p>
            </div>
        </div>
    </footer>
    
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            AOS.init({ duration: 1000, once: true });
            
            window.toggleMenu = () => {
                document.getElementById('mobile-menu').classList.toggle('active');
            };
        });
    </script>
</body>
</html>
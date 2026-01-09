<?php
require_once 'db_config.php'; 

// 1. Nieuws ophalen
$limit_home = 12; 
$news_articles = [];
try {
    $stmt = $pdo->prepare("SELECT titel, artikel_url, publicatie_datum, afbeelding_url, source FROM f1_nieuws ORDER BY publicatie_datum DESC, id DESC LIMIT :limit");
    $stmt->bindParam(':limit', $limit_home, PDO::PARAM_INT);
    $stmt->execute();
    $news_articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Fout bij ophalen nieuwsartikelen: " . $e->getMessage());
}

// 2. Volgende GP ophalen (2026 Fix)
$nextGrandPrix = null;
$targetDateTime = null;
try {
    $stmtNext = $pdo->prepare("SELECT grandprix, race_datetime, title FROM circuits WHERE race_datetime > NOW() ORDER BY race_datetime ASC LIMIT 1");
    $stmtNext->execute();
    $nextGPData = $stmtNext->fetch(PDO::FETCH_ASSOC);

    if ($nextGPData) {
        $nextGrandPrix = ['grandprix' => $nextGPData['grandprix'], 'circuit' => $nextGPData['title']];
        $dateObj = new DateTime($nextGPData['race_datetime']);
        $targetDateTime = $dateObj->format('Y-m-d\TH:i:s'); 
        $backupGP = $nextGrandPrix;
        $backupTime = $targetDateTime;
    }
} catch (PDOException $e) {
    error_log("Database fout: " . $e->getMessage());
}

// 3. API koppeling laden
require_once 'achterkant/aanpassing/api-koppelingen/1result_api.php';
if ((!isset($nextGrandPrix['grandprix']) || empty($nextGrandPrix['grandprix'])) && isset($backupGP)) {
    $nextGrandPrix = $backupGP;
    $targetDateTime = $backupTime;
}

// 4. Schema.org data
$schemaData = ['@context' => 'https://schema.org', '@graph' => [['@type' => 'WebSite', 'url' => 'https://f1site.online/', 'name' => 'Formula 1 - F1SITE.NL']]];
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formula 1 - Home</title>
    <link rel="icon" type="image/x-icon" href="/afbeeldingen/logo/f1logobgrm.png">
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { 'sans': ['Roboto', 'sans-serif'], 'oswald': ['Oswald', 'sans-serif'] },
                    colors: { 'f1-red': '#E10600', 'f1-black': '#15151E', 'f1-gray': '#3A3A40' }
                }
            }
        }
    </script>
    <style>
        /* Navigatie Mobile */
        @media (max-width: 767px) {
            .main-nav[data-visible="false"] { display: none; }
            .main-nav { position: absolute; top: 100%; left: 0; right: 0; background-color: #15151E; padding: 1rem; display: flex; flex-direction: column; z-index: 40; border-top: 1px solid #E10600; }
        }
        .news-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; }
        .news-card { transition: transform 0.2s; border-left: 5px solid transparent; }
        .news-card:hover { transform: translateY(-3px); border-left-color: #E10600; box-shadow: 0 10px 15px rgba(225, 6, 0, 0.2); }

        /* Compact Cookie Modal Styles */
        #cookie-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.85);
            backdrop-filter: blur(4px);
            z-index: 10000;
            align-items: center;
            justify-content: center;
        }
        .cookie-modal {
            background: #1f1f27;
            border: 1px solid #333;
            max-width: 400px;
            width: 90%;
            border-radius: 12px;
            border-top: 4px solid #E10600;
        }
        /* Switch/Schuifjes */
        .switch { position: relative; display: inline-block; width: 34px; height: 20px; }
        .switch input { opacity: 0; width: 0; height: 0; }
        .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #444; transition: .4s; border-radius: 20px; }
        .slider:before { position: absolute; content: ""; height: 14px; width: 14px; left: 3px; bottom: 3px; background-color: white; transition: .4s; border-radius: 50%; }
        input:checked + .slider { background-color: #E10600; }
        input:checked + .slider:before { transform: translateX(14px); }
    </style>
</head>
<body class="bg-f1-black text-gray-100 font-sans">

    <div id="cookie-overlay">
        <div class="cookie-modal p-6 shadow-2xl">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-f1-red rounded-full flex items-center justify-center shrink-0 text-lg">🍪</div>
                <div>
                    <h2 class="text-lg font-oswald font-bold text-white uppercase leading-tight">Cookies</h2>
                    <p class="text-[10px] text-gray-500 uppercase tracking-widest">Privacy instellingen</p>
                </div>
            </div>
            
            <div class="space-y-3 mb-6">
                <div class="flex items-center justify-between p-2 bg-black/30 rounded border border-gray-800">
                    <span class="text-xs font-bold text-gray-300 uppercase">Functioneel</span>
                    <span class="text-[9px] text-gray-500 font-bold italic">ALTIJD AAN</span>
                </div>
                <div class="flex items-center justify-between p-2 bg-black/30 rounded border border-gray-800">
                    <span class="text-xs font-bold text-gray-300 uppercase">Statistieken</span>
                    <label class="switch scale-90">
                        <input type="checkbox" id="check-stats">
                        <span class="slider"></span>
                    </label>
                </div>
                <div class="flex items-center justify-between p-2 bg-black/30 rounded border border-gray-800">
                    <span class="text-xs font-bold text-gray-300 uppercase">Marketing</span>
                    <label class="switch scale-90">
                        <input type="checkbox" id="check-marketing">
                        <span class="slider"></span>
                    </label>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <button id="btn-save-selection" class="py-2 text-[11px] border border-gray-600 text-gray-300 font-bold uppercase rounded hover:bg-gray-800 transition">Sta selectie toe</button>
                <button id="btn-accept-all" class="py-2 text-[11px] bg-f1-red text-white font-bold uppercase rounded hover:bg-red-700 transition">Alles akkoord</button>
            </div>
            <p class="text-center mt-4"><a href="cookiebeleid.html" class="text-[10px] text-gray-600 hover:text-f1-red underline italic">Bekijk ons cookiebeleid</a></p>
        </div>
    </div>

    <header class="bg-black shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
            <h1 class="text-3xl font-oswald font-extrabold text-f1-red tracking-widest">FORMULA 1</h1>
            <button class="md:hidden text-2xl text-f1-red menu-toggle" aria-label="Menu">&#9776;</button>
            <nav class="main-nav md:flex md:space-x-8 text-sm font-semibold uppercase tracking-wider" id="main-nav-links" data-visible="false">
                <a href="index.php" class="text-f1-red border-b-2 border-f1-red pb-1">Home</a>
                <a href="kalender.php" class="hover:text-f1-red transition">Schedule</a>
                <a href="teams.php" class="hover:text-f1-red transition">Teams</a>
                <a href="drivers.php" class="hover:text-f1-red transition">Drivers</a>
                <a href="results.php" class="hover:text-f1-red transition">Results</a>
                <a href="standings.php" class="hover:text-f1-red transition">Standings</a>
            </nav>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 mt-8">
        <div class="bg-f1-gray p-6 rounded-lg shadow-xl mb-8 flex flex-col md:flex-row justify-between items-center border-l-4 border-f1-red">
            <div class="text-center md:text-left mb-4 md:mb-0">
                <h3 class="text-xl md:text-2xl font-oswald font-bold text-white uppercase">
                    <?php echo ($nextGrandPrix) ? htmlspecialchars($nextGrandPrix['grandprix']) : "Geen aankomende Grand Prix"; ?>
                </h3>
                <p class="text-sm text-gray-400">Next Race</p>
            </div>
            <div class="text-center text-3xl md:text-4xl font-oswald font-extrabold text-f1-red" id="countdown">--:--:--</div>
        </div>

        <section class="mb-12">
            <h2 class="text-3xl font-oswald font-bold text-white uppercase mb-6 border-b border-f1-red pb-2">Recent F1 News</h2>
            <?php if (!empty($news_articles)): ?>
                <div class="news-grid">
                    <?php foreach ($news_articles as $article): ?>
                        <div class="bg-f1-gray p-5 rounded-lg shadow-xl news-card flex flex-col">
                            <?php if ($article['afbeelding_url']): ?>
                                <img src="<?php echo htmlspecialchars($article['afbeelding_url']); ?>" alt="News" class="w-full h-44 object-cover rounded-md mb-4">
                            <?php endif; ?>
                            <h3 class="text-xl font-oswald font-semibold mb-2 flex-grow">
                                <a href="<?php echo htmlspecialchars($article['artikel_url']); ?>" target="_blank" class="text-gray-100 hover:text-f1-red transition line-clamp-2"><?php echo htmlspecialchars($article['titel']); ?></a>
                            </h3>
                            <?php if ($article['source']): ?><span class="text-f1-red font-bold uppercase text-[10px]">Source: <?php echo htmlspecialchars($article['source']); ?></span><?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <footer class="bg-black mt-12 py-8 border-t border-f1-red text-center">
        <p class="text-gray-500 text-xs">&copy; 2026 Webbair - F1SITE.NL. Alle rechten voorbehouden.</p>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Menu Toggle
            const nav = document.getElementById('main-nav-links');
            document.querySelector('.menu-toggle').addEventListener('click', () => {
                const isVisible = nav.getAttribute('data-visible') === 'true';
                nav.setAttribute('data-visible', String(!isVisible));
            });

            // Cookie Logica
            const overlay = document.getElementById('cookie-overlay');
            if (!localStorage.getItem('f1_cookie_choice')) {
                overlay.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            }

            function saveChoices(stats, marketing) {
                localStorage.setItem('f1_cookie_choice', JSON.stringify({
                    stats, marketing, date: new Date().toISOString()
                }));
                overlay.style.display = 'none';
                document.body.style.overflow = 'auto';
            }

            document.getElementById('btn-accept-all').addEventListener('click', () => saveChoices(true, true));
            document.getElementById('btn-save-selection').addEventListener('click', () => {
                saveChoices(document.getElementById('check-stats').checked, document.getElementById('check-marketing').checked);
            });

            // Countdown Logic
            <?php if ($targetDateTime): ?>
            const target = new Date('<?php echo $targetDateTime; ?>').getTime();
            function updateCountdown() {
                const dist = target - new Date().getTime();
                if (dist < 0) { document.getElementById('countdown').innerHTML = "RACE LIVE"; return; }
                const d = Math.floor(dist / 86400000), h = Math.floor((dist % 86400000) / 3600000), m = Math.floor((dist % 3600000) / 60000), s = Math.floor((dist % 60000) / 1000);
                document.getElementById('countdown').innerHTML = `${d}d | ${h}h | ${m}m | ${s}s`;
            }
            setInterval(updateCountdown, 1000); updateCountdown();
            <?php endif; ?>
        });
    </script>
</body>
</html>
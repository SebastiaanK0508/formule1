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

// 2. Volgende GP ophalen
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
    }
} catch (PDOException $e) {
    error_log("Database fout: " . $e->getMessage());
}

require_once 'achterkant/aanpassing/api-koppelingen/1result_api.php';

// SEO Variabelen
$pageTitle = "F1SITE.NL | Laatste Formule 1 Nieuws, Uitslagen & Kalender 2026";
$pageDesc = "Blijf op de hoogte van het laatste Formule 1 nieuws. Bekijk de live countdown naar de volgende Grand Prix, uitslagen, standen en de volledige F1 kalender van 2026.";
$currentUrl = "https://f1site.nl" . $_SERVER['REQUEST_URI'];
$ogImage = "https://f1site.nl/afbeeldingen/og-image-default.jpg"; // Zorg dat dit bestand bestaat voor social media
?>
<!DOCTYPE html>
<html lang="nl" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title><?php echo $pageTitle; ?></title>
    <meta name="title" content="<?php echo $pageTitle; ?>">
    <meta name="description" content="<?php echo $pageDesc; ?>">
    <meta name="keywords" content="F1 nieuws, Formule 1 2026, Max Verstappen, F1 kalender, F1 uitslagen, Grand Prix countdown, live F1 updates">
    <meta name="author" content="F1SITE.NL">
    <link rel="canonical" href="<?php echo $currentUrl; ?>">

    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo $currentUrl; ?>">
    <meta property="og:title" content="<?php echo $pageTitle; ?>">
    <meta property="og:description" content="<?php echo $pageDesc; ?>">
    <meta property="og:image" content="<?php echo $ogImage; ?>">

    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="<?php echo $currentUrl; ?>">
    <meta property="twitter:title" content="<?php echo $pageTitle; ?>">
    <meta property="twitter:description" content="<?php echo $pageDesc; ?>">
    <meta property="twitter:image" content="<?php echo $ogImage; ?>">

    <link rel="icon" type="image/png" href="/favicon.png">

    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "SportsEvent",
      "name": "Formula 1 World Championship 2026",
      "description": "De ultieme bron voor Formule 1 nieuws en statistieken.",
      "publisher": {
        "@type": "Organization",
        "name": "F1SITE.NL",
        "logo": {
          "@type": "ImageObject",
          "url": "https://f1site.nl/logo.png"
        }
      }
    }
    </script>

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
        .timer-unit { background: linear-gradient(180deg, #1f1f27 0%, #111116 100%); border: 1px solid rgba(255,255,255,0.05); }
        #cookie-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.92); backdrop-filter: blur(10px); z-index: 10000; align-items: center; justify-content: center; }
        .img-ratio { position: relative; width: 100%; padding-top: 56.25%; overflow: hidden; }
        .img-ratio img { position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s ease; }
        .news-card:hover img { transform: scale(1.08); }
        #mobile-menu { position: fixed; right: 0; top: 0; bottom: 0; width: 100%; transform: translateX(100%); transition: transform 0.4s ease-in-out; background: #0b0b0f; z-index: 101; }
        #mobile-menu.active { transform: translateX(0); }
    </style>
</head>
<body class="bg-pattern">

    <div id="cookie-overlay">
        <div class="bg-f1-card p-10 rounded-2xl border-t-4 border-f1-red max-w-sm w-full mx-4 shadow-2xl text-center">
            <h2 class="text-3xl font-oswald font-black mb-4 uppercase italic tracking-tighter">Data Pitstop</h2>
            <p class="text-gray-400 text-sm mb-8">Accepteer cookies voor de snelste race-ervaring.</p>
            <button onclick="acceptCookies()" class="w-full bg-f1-red py-4 rounded-lg font-bold uppercase text-sm hover:brightness-110 transition shadow-lg">Akkoord</button>
        </div>
    </div>

    <div id="mobile-menu" class="p-10 flex flex-col items-center justify-center">
        <button onclick="toggleMenu()" class="absolute top-8 right-8 text-5xl font-light text-white hover:text-f1-red transition">&times;</button>
        <nav class="flex flex-col space-y-10 text-4xl font-oswald font-black uppercase italic text-center">
            <a href="index.php" class="text-f1-red" onclick="toggleMenu()">Home</a>
            <a href="kalender.php" onclick="toggleMenu()">Schedule</a>
            <a href="teams.php" onclick="toggleMenu()">Teams</a>
            <a href="drivers.php" onclick="toggleMenu()">Drivers</a>
            <a href="results.php" onclick="toggleMenu()">Results</a>
            <a href="standings.php" onclick="toggleMenu()">Standings</a>
        </nav>
    </div>

    <header class="header-glass sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-6 py-5 flex justify-between items-center">
            <a href="index.php" class="flex items-baseline gap-1" aria-label="F1SITE.NL Home">
                <span class="text-3xl font-oswald font-black italic tracking-tighter text-white uppercase">F1SITE<span class="text-f1-red">.NL</span></span>
            </a>
            <nav class="hidden lg:flex space-x-10 text-[11px] font-bold uppercase tracking-[0.25em]">
                <a href="index.php" class="text-f1-red border-b-2 border-f1-red pb-1">Home</a>
                <a href="kalender.php" class="hover:text-f1-red transition">Schedule</a>
                <a href="teams.php" class="hover:text-f1-red transition">Teams</a>
                <a href="drivers.php" class="hover:text-f1-red transition">Drivers</a>
                <a href="results.php" class="hover:text-f1-red transition">Results</a>
                <a href="standings.php" class="hover:text-f1-red transition">Standings</a>
            </nav>
            <button onclick="toggleMenu()" class="lg:hidden text-white text-3xl" aria-label="Menu openen">☰</button>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-6 py-12">
        <section class="mb-24" data-aos="fade-down">
            <div class="relative p-10 md:p-16 rounded-[2.5rem] bg-f1-card border border-white/5 overflow-hidden">
                <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-12">
                    <div>
                        <div class="flex items-center gap-3 mb-6"><span class="h-[2px] w-12 bg-f1-red"></span><span class="text-f1-red text-xs font-black uppercase tracking-[0.3em]">Next Grand Prix Countdown</span></div>
                        <h2 class="text-5xl md:text-7xl font-oswald font-black uppercase italic leading-none mb-4 tracking-tighter">
                            <?php echo ($nextGrandPrix) ? htmlspecialchars($nextGrandPrix['grandprix']) : "Aankomende Race"; ?>
                        </h2>
                        <p class="text-gray-400 text-lg flex items-center gap-2">📍 <?php echo htmlspecialchars($nextGrandPrix['circuit'] ?? 'Wordt geladen...'); ?></p>
                    </div>
                    <div class="grid grid-cols-4 gap-4" id="countdown">
                        <?php foreach(['Days' => 'd', 'Hrs' => 'h', 'Min' => 'm', 'Sec' => 's'] as $label => $id): ?>
                        <div class="timer-unit rounded-2xl p-5 md:p-7 text-center min-w-[85px] md:min-w-[115px]">
                            <div class="text-3xl md:text-5xl font-oswald font-bold text-white mb-1" id="unit-<?php echo $id; ?>">00</div>
                            <div class="text-[9px] uppercase font-black text-f1-red tracking-widest"><?php echo $label; ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </section>

        <section>
            <div class="flex items-center justify-between mb-16">
                <h2 class="text-4xl font-oswald font-black uppercase italic tracking-tighter">Latest <span class="text-f1-red">News</span></h2>
                <div class="hidden md:block h-[1px] flex-grow mx-10 bg-white/10"></div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10 mb-20">
                <?php if (!empty($news_articles)): ?>
                    <?php $i=0; foreach ($news_articles as $article): ?>
                        <article data-aos="fade-up" data-aos-delay="<?php echo $i*50; ?>" class="news-card f1-border bg-f1-card rounded-br-3xl border-r border-b border-white/5 flex flex-col transition-all duration-500 hover:border-f1-red/40 overflow-hidden h-full">
                            <div class="img-ratio">
                                <img src="<?php echo htmlspecialchars($article['afbeelding_url']); ?>" alt="F1 nieuws: <?php echo htmlspecialchars($article['titel']); ?>" loading="lazy">
                            </div>
                            <div class="p-8 flex flex-col flex-grow">
                                <div class="flex items-center justify-between mb-5">
                                    <span class="text-[10px] font-black text-f1-red uppercase tracking-widest"><?php echo htmlspecialchars($article['source']); ?></span>
                                    <time datetime="<?php echo $article['publicatie_datum']; ?>" class="text-[10px] text-gray-600 uppercase font-bold"><?php echo date('d M Y', strtotime($article['publicatie_datum'])); ?></time>
                                </div>
                                <h3 class="text-xl font-bold leading-tight mb-8 flex-grow">
                                    <a href="<?php echo htmlspecialchars($article['artikel_url']); ?>" target="_blank" class="hover:text-f1-red transition"><?php echo htmlspecialchars($article['titel']); ?></a>
                                </h3>
                                <div class="pt-6 border-t border-white/5 mt-auto">
                                    <a href="<?php echo htmlspecialchars($article['artikel_url']); ?>" target="_blank" class="inline-flex items-center gap-2 text-[10px] font-black uppercase tracking-widest hover:gap-4 transition-all">Lees Verder <span class="text-f1-red text-lg">→</span></a>
                                </div>
                            </div>
                        </article>
                    <?php $i++; endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="flex justify-center" data-aos="fade-up">
                <a href="nieuws.php" class="group relative inline-flex items-center justify-center px-12 py-5 overflow-hidden font-black uppercase tracking-[0.4em] text-[11px] text-white transition-all duration-300 bg-f1-card border border-white/10 rounded-full hover:border-f1-red/50 shadow-2xl">
                    <span class="absolute inset-0 w-full h-full bg-gradient-to-r from-f1-red/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></span>
                    <span class="relative flex items-center gap-3">
                        Bekijk Nieuwsarchief 
                        <span class="text-f1-red text-xl group-hover:translate-x-2 transition-transform duration-300">→</span>
                    </span>
                </a>
            </div>
        </section>
    </main>

    <footer class="bg-black mt-24 py-16 border-t-2 border-f1-red">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-12 text-left pb-12 border-b border-white/5">
                
                <div class="space-y-4 text-center md:text-left">
                    <h3 class="text-2xl font-oswald font-black text-white italic tracking-tighter uppercase">F1SITE<span class="text-f1-red">.NL</span></h3>
                    <p class="text-gray-500 text-sm font-medium leading-relaxed max-w-xs mx-auto md:mx-0">
                        Your ultimate source for the latest Formula 1 news, live countdowns, statistics and race updates.                    </p>
                </div>

                <div class="text-center md:text-left">
                    <h4 class="text-xs font-black text-f1-red mb-6 uppercase tracking-[0.3em]">Developer</h4>
                    <ul class="space-y-4">
                        <li>
                            <a href="https://www.webius.nl" target="_blank" class="text-gray-400 text-sm font-bold hover:text-white transition duration-200 block uppercase tracking-wider">Webius</a>
                        </li>
                    </ul>
                </div>

                <div class="text-center md:text-left">
                    <h4 class="text-xs font-black text-f1-red mb-6 uppercase tracking-[0.3em]">Navigatie & Info</h4>
                    <ul class="space-y-4">
                        <li><a href="sitemap.php" class="text-gray-400 text-sm font-bold hover:text-white transition duration-200 block uppercase tracking-wider">Sitemap</a></li>
                        <li><a href="privacy-en.html" class="text-gray-400 text-sm font-bold hover:text-white transition duration-200 block uppercase tracking-wider">Privacy Policy</a></li>
                        <li><a href="algemenevoorwaarden-en.html" class="text-gray-400 text-sm font-bold hover:text-white transition duration-200 block uppercase tracking-wider">Terms & Conditions</a></li>
                        <li><a href="contact.html" class="text-gray-400 text-sm font-bold hover:text-white transition duration-200 block uppercase tracking-wider">Contact</a></li>
                    </ul>
                </div>
            </div>

            <div class="pt-10 flex flex-col md:flex-row justify-between items-center gap-4 text-center md:text-left">
                <p class="text-gray-600 text-[10px] font-black uppercase tracking-[0.5em] italic">
                    &copy; <?php echo date('Y'); ?> WEBIUS. All rights reserved.
                </p>
                <div class="flex gap-6">
                    <span class="text-f1-red opacity-20 text-xl font-oswald italic font-black uppercase" aria-hidden="true">Faster than light</span>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            AOS.init({ duration: 1000, once: true });
            
            const overlay = document.getElementById('cookie-overlay');
            if (!localStorage.getItem('f1_consent_fixed')) {
                overlay.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            }
            window.acceptCookies = () => {
                localStorage.setItem('f1_consent_fixed', 'true');
                overlay.style.display = 'none';
                document.body.style.overflow = 'auto';
            };
            
            window.toggleMenu = () => { document.getElementById('mobile-menu').classList.toggle('active'); };

            <?php if ($targetDateTime): ?>
            const target = new Date('<?php echo $targetDateTime; ?>').getTime();
            function update() {
                const now = new Date().getTime();
                const d = target - now;
                if (d < 0) return;
                document.getElementById('unit-d').innerText = Math.floor(d / 86400000);
                document.getElementById('unit-h').innerText = String(Math.floor((d % 86400000) / 3600000)).padStart(2, '0');
                document.getElementById('unit-m').innerText = String(Math.floor((d % 3600000) / 60000)).padStart(2, '0');
                document.getElementById('unit-s').innerText = String(Math.floor((d % 60000) / 1000)).padStart(2, '0');
            }
            setInterval(update, 1000); update();
            <?php endif; ?>
        });
    </script>
</body>
</html>
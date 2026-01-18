<?php
require_once 'db_config.php';
$pageTitle = "F1 News | Latest Updates & Grand Prix Insights | F1SITE.NL";
$pageDesc = "Stay updated with the latest Formula 1 news. From race results to technical updates and team transfers, we cover the 2026 season in full speed.";
$currentUrl = "https://f1site.nl" . $_SERVER['REQUEST_URI'];
$ogImage = "https://f1site.nl/afbeeldingen/og-image-default.jpg";
$limit = 12; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;
try {
    $total_stmt = $pdo->query("SELECT COUNT(*) FROM f1_nieuws");
    $total_articles = $total_stmt->fetchColumn();
    $total_pages = ceil($total_articles / $limit);
    $stmt = $pdo->prepare("SELECT titel, artikel_url, publicatie_datum, afbeelding_url, source FROM f1_nieuws ORDER BY publicatie_datum DESC, id DESC LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $news_articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("F1SITE Database Error: " . $e->getMessage());
    $news_articles = [];
}
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <meta name="description" content="<?= $pageDesc ?>">
    <link rel="canonical" href="<?= $currentUrl ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= $currentUrl ?>">
    <meta property="og:title" content="<?= $pageTitle ?>">
    <meta property="og:description" content="<?= $pageDesc ?>">
    <meta property="og:image" content="<?= $ogImage ?>">
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
        .nav-link { font-style: normal !important; transition: all 0.3s ease; }
        .pagination-btn {
            @apply px-5 py-3 bg-f1-card border border-white/10 text-white font-bold text-sm transition-all duration-300 rounded-lg hover:border-f1-red hover:text-f1-red flex items-center justify-center min-w-[45px];
            font-style: normal;
        }
        .pagination-btn.active {
            @apply bg-f1-red border-f1-red text-white shadow-lg shadow-f1-red/20;
        }
        .img-ratio { position: relative; width: 100%; padding-top: 56.25%; overflow: hidden; }
        .img-ratio img { position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s ease; }
        .news-card:hover img { transform: scale(1.08); }
        #mobile-menu { position: fixed; right: 0; top: 0; bottom: 0; width: 100%; transform: translateX(100%); transition: transform 0.4s ease-in-out; background: #0b0b0f; z-index: 101; }
        #mobile-menu.active { transform: translateX(0); }
    </style>
</head>
<body class="bg-pattern font-sans">
    <div id="mobile-menu" class="p-10 flex flex-col items-center justify-center">
        <button onclick="toggleMenu()" class="absolute top-8 right-8 text-5xl font-light text-white hover:text-f1-red transition">&times;</button>
        <nav class="flex flex-col space-y-10 text-4xl font-oswald font-black uppercase italic text-center">
            <a href="index.php" onclick="toggleMenu()">Home</a>
            <a href="kalender.php" onclick="toggleMenu()">Schedule</a>
            <a href="teams.php" onclick="toggleMenu()">Teams</a>
            <a href="drivers.php" onclick="toggleMenu()">Drivers</a>
            <a href="results.php" onclick="toggleMenu()">Results</a>
            <a href="standings.php" onclick="toggleMenu()">Standings</a>
        </nav>
    </div>

    <header class="header-glass sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-6 py-5 flex justify-between items-center">
            <a href="index.php" class="flex items-baseline gap-1">
                <span class="text-3xl font-oswald font-black italic tracking-tighter text-white uppercase">F1SITE<span class="text-f1-red">.NL</span></span>
            </a>
            <nav class="hidden lg:flex space-x-10 text-[11px] font-bold uppercase tracking-[0.25em]">
                <a href="index.php" class="nav-link hover:text-f1-red">Home</a>
                <a href="kalender.php" class="nav-link hover:text-f1-red">Schedule</a>
                <a href="teams.php" class="nav-link hover:text-f1-red">Teams</a>
                <a href="drivers.php" class="nav-link hover:text-f1-red">Drivers</a>
                <a href="results.php" class="nav-link hover:text-f1-red">Results</a>
                <a href="standings.php" class="nav-link hover:text-f1-red">Standings</a>
            </nav>
            <button onclick="toggleMenu()" class="lg:hidden text-white text-3xl">☰</button>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-6 py-16">
        <h2 class="text-4xl font-oswald font-black uppercase italic tracking-tighter mb-12">Latest <span class="text-f1-red">News</span></h2>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
            <?php foreach ($news_articles as $article): ?>
            <article data-aos="fade-up" class="news-card bg-f1-card rounded-br-3xl border-r border-b border-white/5 flex flex-col overflow-hidden transition-all hover:border-f1-red/40">
                <div class="img-ratio">
                    <img src="<?= htmlspecialchars($article['afbeelding_url']) ?>" alt="<?= htmlspecialchars($article['titel']) ?>">
                </div>
                <div class="p-8 flex flex-col flex-grow">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-[10px] font-black text-f1-red uppercase tracking-widest"><?= htmlspecialchars($article['source']) ?></span>
                        <span class="text-[10px] text-gray-500 font-bold"><?= date('d M Y', strtotime($article['publicatie_datum'])) ?></span>
                    </div>
                    <h3 class="text-xl font-bold leading-tight mb-6">
                        <a href="<?= htmlspecialchars($article['artikel_url']) ?>" target="_blank" class="hover:text-f1-red transition"><?= htmlspecialchars($article['titel']) ?></a>
                    </h3>
                    <div class="mt-auto pt-6 border-t border-white/5">
                        <a href="<?= htmlspecialchars($article['artikel_url']) ?>" target="_blank" class="inline-flex items-center gap-2 text-[10px] font-black uppercase tracking-widest hover:gap-4 transition-all italic">Read More <span class="text-f1-red text-lg">→</span></a>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>

        <?php if ($total_pages > 1): ?>
        <div class="flex flex-col items-center justify-center space-y-6 mt-20" data-aos="fade-up">
            <div class="flex items-center gap-2">
                
                <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1 ?>" class="pagination-btn group">
                        <span class="group-hover:-translate-x-1 transition-transform">←</span>
                    </a>
                <?php endif; ?>
                
                <?php 
                $start = max(1, $page - 2);
                $end = min($total_pages, $page + 2);
                
                for ($i = $start; $i <= $end; $i++): ?>
                    <a href="?page=<?= $i ?>" class="pagination-btn <?= ($i == $page) ? 'active' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?= $page + 1 ?>" class="pagination-btn group">
                        <span class="group-hover:translate-x-1 transition-transform">→</span>
                    </a>
                <?php endif; ?>

            </div>
            <p class="text-[10px] text-gray-500 uppercase font-black tracking-[0.3em]">Showing page <?= $page ?> of <?= $total_pages ?></p>
        </div>
        <?php endif; ?>

    </main>

    <footer class="bg-black mt-24 py-16 border-t-2 border-f1-red">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-12 text-left pb-12 border-b border-white/5">
                <div class="space-y-4">
                    <h3 class="text-2xl font-oswald font-black text-white italic tracking-tighter uppercase">F1SITE<span class="text-f1-red">.NL</span></h3>
                    <p class="text-gray-500 text-sm font-medium leading-relaxed italic">
                        The fastest source for Formula 1 news and race data.
                    </p>
                </div>
                <div>
                    <h4 class="text-xs font-black text-f1-red mb-6 uppercase tracking-[0.3em] italic">Developer</h4>
                    <ul class="space-y-4 font-bold text-sm uppercase tracking-wider">
                        <li><a href="https://www.webius.nl" target="_blank" class="text-gray-400 hover:text-white transition">Webius</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-xs font-black text-f1-red mb-6 uppercase tracking-[0.3em] italic">Navigation</h4>
                    <ul class="space-y-4 font-bold text-sm uppercase tracking-wider">
                        <li><a href="sitemap.php" class="text-gray-400 hover:text-white transition">Sitemap</a></li>
                        <li><a href="privacy-en.html" class="text-gray-400 hover:text-white transition">Privacy Policy</a></li>
                        <li><a href="algemenevoorwaarden-en.html" class="text-gray-400 hover:text-white transition">Terms & Conditions</a></li>
                        <li><a href="contact.html" class="text-gray-400 hover:text-white transition">Contact</a></li>
                    </ul>
                </div>
            </div>
            <div class="pt-10 flex justify-between items-center">
                <p class="text-gray-600 text-[10px] font-black uppercase tracking-[0.5em] italic">
                    &copy; 2026 WEBIUS. All rights reserved.
                </p>
            </div>
        </div>
    </footer>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            AOS.init({ duration: 1000, once: true });
            window.toggleMenu = () => { 
                const menu = document.getElementById('mobile-menu');
                menu.classList.toggle('active');
            };
        });
    </script>
</body>
</html>
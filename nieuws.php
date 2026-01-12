<?php
require_once 'db_config.php'; 
require_once 'achterkant/aanpassing/api-koppelingen/1result_api.php';
$limit = 12; 
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$total_articles = 0;
try {
    $count_stmt = $pdo->query("SELECT COUNT(*) FROM f1_nieuws");
    $total_articles = $count_stmt->fetchColumn();
} catch (PDOException $e) {
    error_log("Fout bij tellen nieuwsartikelen: " . $e->getMessage());
}
$total_pages = ceil($total_articles / $limit);

$news_articles = [];
try {
    $stmt = $pdo->prepare("SELECT titel, artikel_url, publicatie_datum, afbeelding_url, source 
                           FROM f1_nieuws 
                           ORDER BY publicatie_datum DESC, id DESC 
                           LIMIT :limit OFFSET :offset");
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $news_articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Fout bij ophalen nieuwsartikelen voor paginering: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>News Archive | F1SITE.NL</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;700;900&family=Oswald:wght@700&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { 'sans': ['Inter', 'sans-serif'], 'oswald': ['Oswald', 'sans-serif'] },
                    colors: { 'f1-red': '#E10600', 'f1-black': '#0b0b0f', 'f1-card': '#16161c' }
                }
            }
        }
    </script>

    <style>
        body { background-color: #0b0b0f; color: #fff; overflow-x: hidden; }
        .bg-pattern {
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.02'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }

        .header-glass { background: rgba(11, 11, 15, 0.95); backdrop-filter: blur(10px); border-bottom: 1px solid rgba(225, 6, 0, 0.3); }
        
        #mobile-menu { 
            position: fixed; inset: 0; background: #0b0b0f; 
            z-index: 10000; transform: translateX(100%); 
            transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1); 
        }
        #mobile-menu.active { transform: translateX(0); }

        .news-card-elite {
            @apply bg-f1-card/50 rounded-[2rem] border border-white/5 shadow-2xl backdrop-blur-sm overflow-hidden flex flex-col transition-all duration-300;
        }
        
        .pagination-link-elite {
            @apply px-4 py-2 text-[10px] font-black uppercase tracking-widest rounded-xl transition-all duration-200;
        }

        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #0b0b0f; }
        ::-webkit-scrollbar-thumb { background: #E10600; border-radius: 10px; }
    </style>
</head>

<body class="bg-pattern min-h-screen flex flex-col italic">

    <div id="mobile-menu" class="flex flex-col items-center justify-center">
        <button onclick="toggleMenu()" class="absolute top-8 right-8 text-5xl font-light text-f1-red">&times;</button>
        <nav class="flex flex-col space-y-8 text-3xl font-oswald font-black uppercase italic text-center">
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
                <span class="text-2xl md:text-3xl font-oswald font-black italic tracking-tighter text-white uppercase">F1SITE<span class="text-f1-red">.NL</span></span>
            </a>
            
            <nav class="hidden lg:flex space-x-10 text-[11px] font-bold uppercase tracking-[0.25em]">
                <a href="index.php" class="hover:text-f1-red transition">Home</a>
                <a href="kalender.php" class="hover:text-f1-red transition">Schedule</a>
                <a href="teams.php" class="hover:text-f1-red transition">Teams</a>
                <a href="drivers.php" class="hover:text-f1-red transition">Drivers</a>
                <a href="results.php" class="hover:text-f1-red transition">Results</a>
                <a href="standings.php" class="hover:text-f1-red transition">Standings</a>
            </nav>

            <button onclick="toggleMenu()" class="lg:hidden text-f1-red text-3xl">☰</button>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-6 py-12 md:py-20 flex-grow">
        
        <div class="mb-12 md:mb-20 text-center" data-aos="fade-down">
            <h1 class="text-5xl md:text-8xl font-oswald font-black uppercase italic tracking-tighter leading-none">
                NEWS<span class="text-f1-red">ARCHIVE</span>
            </h1>
        </div>

        <?php if (!empty($news_articles)): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-10">
                <?php foreach ($news_articles as $article): ?>
                    <article class="news-card-elite" data-aos="fade-up">
                        <div class="relative h-48 md:h-56 overflow-hidden">
                            <?php if ($article['afbeelding_url']): ?>
                                <img src="<?php echo htmlspecialchars($article['afbeelding_url']); ?>" 
                                     alt="F1 News" 
                                     class="w-full h-full object-cover transition-transform duration-700 hover:scale-110">
                            <?php else: ?>
                                <div class="w-full h-full bg-zinc-900 flex items-center justify-center">
                                    <span class="text-f1-red font-oswald italic font-bold">F1SITE.NL</span>
                                </div>
                            <?php endif; ?>
                            <div class="absolute top-4 left-4">
                                <span class="bg-f1-red text-white text-[8px] font-black px-3 py-1 uppercase tracking-tighter rounded-full">
                                    <?php echo htmlspecialchars($article['source'] ?? 'News'); ?>
                                </span>
                            </div>
                        </div>

                        <div class="p-6 md:p-8 flex flex-col flex-grow">
                            <h3 class="text-lg md:text-xl font-oswald font-black uppercase italic leading-tight mb-6 flex-grow">
                                <a href="<?php echo htmlspecialchars($article['artikel_url']); ?>" target="_blank" 
                                   class="hover:text-f1-red transition-colors duration-200">
                                    <?php echo htmlspecialchars($article['titel']); ?>
                                </a>
                            </h3>
                            
                            <div class="pt-4 border-t border-white/5 flex justify-between items-center">
                                <span class="text-[9px] text-gray-500 font-bold uppercase tracking-widest">
                                    <?php echo date('d M Y', strtotime($article['publicatie_datum'])); ?>
                                </span>
                                <a href="<?php echo htmlspecialchars($article['artikel_url']); ?>" target="_blank" class="text-f1-red text-[10px] font-black uppercase tracking-tighter hover:text-white transition-colors">
                                    Read more +
                                </a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <nav class="flex flex-wrap justify-center items-center gap-2 mt-16" aria-label="Pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>" class="pagination-link-elite bg-white/5 hover:bg-f1-red text-white">Prev</a>
                <?php endif; ?>

                <?php 
                $start_page = max(1, $page - 1);
                $end_page = min($total_pages, $page + 1);
                for ($i = $start_page; $i <= $end_page; $i++): ?>
                    <a href="?page=<?php echo $i; ?>" 
                       class="pagination-link-elite <?php echo ($i == $page ? 'bg-f1-red text-white' : 'bg-white/5 text-gray-400'); ?>">
                       <?php echo $i; ?>
                    </a>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?>" class="pagination-link-elite bg-white/5 hover:bg-f1-red text-white">Next</a>
                <?php endif; ?>
            </nav>

        <?php else: ?>
            <div class="bg-f1-card/50 p-20 rounded-[2.5rem] border border-white/5 text-center">
                <p class="text-gray-500 font-oswald italic uppercase tracking-widest">No articles found.</p>
            </div>
        <?php endif; ?>
    </main>
    
    <footer class="bg-black py-12 border-t-2 border-f1-red">
        <div class="max-w-7xl mx-auto px-6 text-center md:text-left">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-10 pb-8 border-b border-white/5">
                <div>
                    <h3 class="text-xl font-oswald font-black text-white italic tracking-tighter mb-2 uppercase">F1SITE<span class="text-f1-red">.NL</span></h3>
                    <p class="text-gray-500 text-[10px] italic uppercase tracking-widest">Premium Formula 1 News Hub.</p>
                </div>
                <div>
                    <h4 class="text-xs font-black text-f1-red mb-4 uppercase italic tracking-widest">Navigation</h4>
                    <ul class="space-y-4">
                        <li><a href="sitemap.php" class="text-gray-400 text-sm font-bold hover:text-white transition duration-200 block uppercase tracking-wider">Sitemap</a></li>
                        <li><a href="privacy-en.html" class="text-gray-400 text-sm font-bold hover:text-white transition duration-200 block uppercase tracking-wider">Privacy Policy</a></li>
                        <li><a href="algemenevoorwaarden-en.html" class="text-gray-400 text-sm font-bold hover:text-white transition duration-200 block uppercase tracking-wider">Terms & Conditions</a></li>
                        <li><a href="contact.html" class="text-gray-400 text-sm font-bold hover:text-white transition duration-200 block uppercase tracking-wider">Contact</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-xs font-black text-f1-red mb-4 uppercase italic tracking-widest">Developer</h4>
                    <a href="https://www.webbair.nl" target="_blank" class="text-[10px] text-gray-400 uppercase italic hover:text-white">Webbair</a>
                </div>
            </div>
            <p class="pt-8 text-gray-700 text-[9px] font-black uppercase tracking-[0.4em] italic">&copy; 2026 Webbair. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            AOS.init({ duration: 800, once: true });
            window.toggleMenu = () => {
                document.getElementById('mobile-menu').classList.toggle('active');
            };
        });
    </script> 
</body>
</html>`
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
    <?php include 'navigatie/head.php'; ?>
    <style>
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
    </style>
</head>
<body class="bg-pattern font-sans">
    <?php include 'navigatie/header.php'; ?>

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

    <?php include 'navigatie/footer.php'; ?>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            AOS.init({ duration: 1000, once: true });
        });
    </script>
</body>
</html>
<?php
require_once 'db_config.php'; 
$limit_home = 6; 
$news_articles = [];
try {
    $stmt = $pdo->prepare("SELECT titel, artikel_url, publicatie_datum, afbeelding_url, source FROM f1_nieuws ORDER BY publicatie_datum DESC, id DESC LIMIT :limit");
    $stmt->bindParam(':limit', $limit_home, PDO::PARAM_INT);
    $stmt->execute();
    $news_articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Fout bij ophalen nieuwsartikelen: " . $e->getMessage());
}
$nextGrandPrix = null;
$targetDateTime = null;
try {
$sqlNextGP = "SELECT grandprix, race_datetime, title, location FROM circuits ORDER BY race_datetime ASC LIMIT 1";
    $stmtNextGP = $pdo->prepare($sqlNextGP);
    $stmtNextGP->execute();
    
    $nextGPData = $stmtNextGP->fetch(PDO::FETCH_ASSOC);

    if ($nextGPData) {
        $nextGrandPrix = $nextGPData; 
        
        $dateObj = new DateTime($nextGPData['race_datetime']);
        $targetDateTime = $dateObj->format('Y-m-d\TH:i:s'); 
    }
} catch (PDOException $e) {
    error_log("Database fout: " . $e->getMessage());
}

require_once 'achterkant/aanpassing/api-koppelingen/1result_api.php';
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <?php include 'navigatie/head.php'; ?>
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
    <style>
        .f1-border { position: relative; }
        .f1-border::before { content: ""; position: absolute; top: 0; left: 0; width: 45px; height: 4px; background: #E10600; z-index: 10; }
        .timer-unit { background: linear-gradient(180deg, #1f1f27 0%, #111116 100%); border: 1px solid rgba(255,255,255,0.05); }
        .img-ratio { position: relative; width: 100%; padding-top: 56.25%; overflow: hidden; }
        .img-ratio img { position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s ease; }
        .news-card:hover img { transform: scale(1.08); }
    </style>
</head>
<body class="bg-pattern">
    <?php include 'navigatie/header.php'; ?>
    <main class="max-w-7xl mx-auto px-6 py-12">
        <section class="mb-24" data-aos="fade-down">
            <div class="relative p-10 md:p-16 rounded-[2.5rem] bg-f1-card border border-white/5 overflow-hidden">
                <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-12">
                    <div>
                        <div class="flex items-center gap-3 mb-6"><span class="h-[2px] w-12 bg-f1-red"></span><span class="text-f1-red text-xs font-black uppercase tracking-[0.3em]">Next Grand Prix Countdown</span></div>
                        <h2 class="text-5xl md:text-7xl font-oswald font-black uppercase italic leading-none mb-4 tracking-tighter">
                            <?php echo ($nextGrandPrix) ? htmlspecialchars($nextGrandPrix['grandprix']) : "Aankomende Race"; ?>
                        </h2>
                        <p class="text-gray-400 text-lg flex items-center gap-2">📍 <?php echo htmlspecialchars($nextGrandPrix['title'] ?? 'Wordt geladen...'); ?>, <?php echo htmlspecialchars($nextGrandPrix['location'] ?? ''); ?></p>
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
                        View News Archive 
                        <span class="text-f1-red text-xl group-hover:translate-x-2 transition-transform duration-300">→</span>
                    </span>
                </a>
            </div>
        </section>
    </main>

    <?php include 'navigatie/footer.php'; ?>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            AOS.init({ duration: 1000, once: true });
            
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
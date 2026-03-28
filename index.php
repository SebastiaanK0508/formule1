<?php
require_once 'db_config.php'; 
/** @var PDO $pdo */

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
$dbNextGP = null; 
$targetDateTime = null;

try {
    $sqlNextGP = "SELECT * FROM circuits WHERE race_datetime >= NOW() ORDER BY race_datetime ASC LIMIT 1";
    $stmtNextGP = $pdo->prepare($sqlNextGP);
    $stmtNextGP->execute();
    $dbNextGP = $stmtNextGP->fetch(PDO::FETCH_ASSOC);

    if ($dbNextGP) {
        $sessionTimes = [
            $dbNextGP['fp1_datetime'] ?? null,
            $dbNextGP['fp2_datetime'] ?? $dbNextGP['sprint_quali_datetime'] ?? null,
            $dbNextGP['fp3_datetime'] ?? $dbNextGP['sprint_datetime'] ?? null,
            $dbNextGP['quali_datetime'] ?? null,
            $dbNextGP['race_datetime'] ?? null
        ];

        $targetTime = $dbNextGP['race_datetime']; 
        foreach ($sessionTimes as $sTime) {
            if (!empty($sTime) && $sTime !== '0000-00-00 00:00:00' && strtotime($sTime) > time()) {
                $targetTime = $sTime;
                break;
            }
        }
        $targetDateTime = (new DateTime($targetTime))->format('Y-m-d\TH:i:s'); 
    }
} catch (\PDOException $e) {
    error_log("DB Fout: " . $e->getMessage());
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
        .results-scroll {
            max-height: 650px; 
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: #E10600 rgba(255,255,255,0.05);
        }
        .results-scroll::-webkit-scrollbar { width: 4px; }
        .results-scroll::-webkit-scrollbar-track { background: rgba(255,255,255,0.05); }
        .results-scroll::-webkit-scrollbar-thumb { background-color: #E10600; border-radius: 10px; }
        .pos-1 { color: #FFD700 !important; }
        .pos-2 { color: #C0C0C0 !important; }
        .pos-3 { color: #CD7F32 !important; }
    </style>
</head>
<body class="bg-pattern">
    <?php include 'navigatie/header.php'; ?>
    <main class="max-w-7xl mx-auto px-6 py-12">
        <section class="mb-24" data-aos="fade-down">
            <div class="relative p-6 md:p-12 rounded-[2.5rem] bg-f1-card border border-white/5 overflow-hidden">
                <div class="relative z-10">
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-8 mb-12">
                        <div class="text-center lg:text-left">
                            <div class="flex items-center justify-center lg:justify-start gap-3 mb-4">
                                <span class="h-[2px] w-8 bg-f1-red"></span>
                                <span class="text-f1-red text-[10px] font-black uppercase tracking-[0.3em]">Next Session Countdown</span>
                            </div>
                            <h2 class="text-4xl md:text-7xl font-oswald font-black uppercase italic leading-none mb-4 tracking-tighter">
                                <?php echo ($dbNextGP) ? htmlspecialchars($dbNextGP['grandprix']) : "Aankomende Race"; ?>
                            </h2>
                            <p class="text-gray-400 text-sm md:text-lg flex items-center justify-center lg:justify-start gap-2">
                                <span class="opacity-60 text-f1-red">📍</span> 
                                <?php echo htmlspecialchars($dbNextGP['title'] ?? 'Circuit info...'); ?>
                            </p>
                        </div>
                        
                        <div class="grid grid-cols-4 gap-2 md:gap-4" id="countdown">
                            <?php foreach(['Days' => 'd', 'Hrs' => 'h', 'Min' => 'm', 'Sec' => 's'] as $label => $id): ?>
                            <div class="timer-unit rounded-2xl p-3 md:p-6 text-center border border-white/5 bg-white/[0.02] shadow-inner min-w-[70px] md:min-w-[100px]">
                                <div class="text-2xl md:text-5xl font-oswald font-bold text-white leading-none tabular-nums" id="unit-<?php echo $id; ?>">00</div>
                                <div class="text-[7px] md:text-[9px] uppercase font-black text-f1-red tracking-widest mt-1 opacity-80"><?php echo $label; ?></div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 border-t border-white/10 pt-8">
                        <?php 
                        if ($dbNextGP):
                            $isSprint = !empty($dbNextGP['sprint_datetime']);
                            $displaySessions = [
                                'Practice 1' => $dbNextGP['fp1_datetime'] ?? null,
                                ($isSprint ? 'Sprint Quali' : 'Practice 2') => $isSprint ? ($dbNextGP['sprint_quali_datetime'] ?? null) : ($dbNextGP['fp2_datetime'] ?? null),
                                ($isSprint ? 'Sprint Race' : 'Practice 3')  => $isSprint ? ($dbNextGP['sprint_datetime'] ?? null) : ($dbNextGP['fp3_datetime'] ?? null),
                                'Qualifying' => $dbNextGP['quali_datetime'] ?? null,
                                'Grand Prix' => $dbNextGP['race_datetime'] ?? null
                            ];

                    foreach($displaySessions as $label => $time): 
                        if(empty($time) || $time === '0000-00-00 00:00:00') continue;
                        $sessionTs = strtotime($time);
                        $isPassed = $sessionTs < time();
                        $isLive = (time() >= $sessionTs && time() <= ($sessionTs + 7200));
                    ?>
                        <div class="flex items-center lg:flex-col lg:justify-center p-4 rounded-2xl border transition-all duration-500 <?php echo $isLive ? 'bg-f1-red/10 border-f1-red animate-pulse' : 'bg-white/[0.02] border-white/5'; ?> <?php echo ($isPassed && !$isLive) ? 'opacity-30 grayscale' : ''; ?>">                    
                            <div class="flex-1 lg:flex-none lg:mb-2 text-left lg:text-center">
                                <p class="text-[8px] font-black uppercase tracking-widest <?php echo $isLive ? 'text-white' : 'text-f1-red'; ?>"><?php echo $label; ?></p>
                                <p class="font-oswald text-sm font-bold uppercase italic text-white/90"><?php echo date('d M', $sessionTs); ?></p>
                            </div>
                            <div class="text-right lg:text-center">
                                <p class="text-lg font-oswald font-black text-white"><?php echo date('H:i', $sessionTs); ?></p>
                            </div>
                        </div>
                        <?php endforeach; else: ?>
                            <p class="text-gray-500 text-xs italic p-4">Geen data beschikbaar in database.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
        <section class="mb-24" data-aos="fade-up">
            <div class="flex items-center justify-between mb-12">
                <h2 class="text-4xl font-oswald font-black uppercase italic tracking-tighter">
                    Latest Results: <span class="text-f1-red"><?php echo htmlspecialchars($race_details['name'] ?? 'Grand Prix'); ?></span>
                </h2>
                <a href="race_result.php?round=<?php echo $selected_round; ?>" class="hidden md:flex items-center gap-3 text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 hover:text-f1-red transition-all group">
                    View Full Weekend Analysis
                    <span class="w-8 h-[1px] bg-gray-700 group-hover:bg-f1-red group-hover:w-12 transition-all"></span>
                </a>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 items-start">
                <div class="bg-f1-card rounded-[2.5rem] border border-white/5 overflow-hidden shadow-2xl flex flex-col">
                    <div class="p-6 bg-white/5 border-b border-white/5 flex justify-between items-center">
                        <div>
                            <span class="text-xs font-black uppercase tracking-widest text-white">Race Classification</span>
                            <p class="text-[10px] text-gray-500 uppercase mt-1">Full Grid Results</p>
                        </div>
                        <span class="text-[10px] font-bold text-f1-red uppercase px-3 py-1 bg-f1-red/10 rounded-full border border-f1-red/20 italic">Round <?php echo $selected_round; ?></span>
                    </div>
                    
                    <div class="results-scroll h-[450px]"> <table class="w-full text-left">
                            <tbody class="divide-y divide-white/5">
                                <?php if (!empty($race_results)): ?>
                                    <?php foreach (array_slice($race_results, 0, 10) as $res): // Toon top 10 op index ?>
                                        <tr class="hover:bg-white/[0.03] transition-colors group">
                                            <td class="px-6 py-4 font-oswald font-bold text-2xl italic text-gray-700 w-16 <?php echo ($res['position'] <= 3) ? 'pos-' . $res['position'] : ''; ?>">
                                                <?php echo str_pad($res['position'], 2, "0", STR_PAD_LEFT); ?>
                                            </td>
                                            <td class="px-4 py-4">
                                                <div class="flex items-center gap-4">
                                                    <div class="w-1 h-10 rounded-full" style="background-color: <?php echo $res['team_color']; ?>;"></div>
                                                    <div>
                                                        <span class="text-white font-bold block text-base leading-none mb-1"><?php echo htmlspecialchars($res['driver_name']); ?></span>
                                                        <span class="text-[10px] text-gray-500 uppercase tracking-widest"><?php echo htmlspecialchars($res['team_name']); ?></span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 text-right">
                                                <span class="text-[11px] font-mono text-gray-400 bg-white/5 px-2 py-1 rounded"><?php echo $res['lap_time_or_status']; ?></span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <a href="race_result.php?round=<?php echo $selected_round; ?>" class="p-4 bg-white/[0.02] border-t border-white/5 text-center text-[10px] font-black uppercase tracking-widest text-gray-500 hover:text-white transition-colors">
                        View All <?php echo count($race_results); ?> Finishers →
                    </a>
                </div>

                <div class="bg-f1-card rounded-[2.5rem] border border-white/5 overflow-hidden shadow-2xl flex flex-col">
                    <div class="p-6 bg-white/5 border-b border-white/5 flex justify-between items-center">
                        <div>
                            <span class="text-xs font-black uppercase tracking-widest text-white">Qualifying Standings</span>
                            <p class="text-[10px] text-gray-500 uppercase mt-1">Saturdays Performance</p>
                        </div>
                        <div class="flex gap-2">
                            <span class="text-[9px] font-bold text-gray-400 uppercase px-2 py-1 bg-white/5 rounded border border-white/10">Q1-Q3</span>
                        </div>
                    </div>

                    <div class="results-scroll h-[450px]">
                        <table class="w-full text-left">
                            <tbody class="divide-y divide-white/5">
                                <?php if (!empty($qualifying_results)): ?>
                                    <?php foreach (array_slice($qualifying_results, 0, 10) as $q): ?>
                                        <tr class="hover:bg-white/[0.03] transition-colors group">
                                            <td class="px-6 py-4 font-oswald font-bold text-2xl italic text-gray-700 w-16 <?php echo ($q['position'] <= 3) ? 'pos-' . $q['position'] : ''; ?>">
                                                <?php echo str_pad($q['position'], 2, "0", STR_PAD_LEFT); ?>
                                            </td>
                                            <td class="px-4 py-4">
                                                <div class="flex items-center gap-4">
                                                    <div class="w-1 h-10 rounded-full" style="background-color: <?php echo $q['team_color']; ?>;"></div>
                                                    <div>
                                                        <span class="text-white font-bold block text-base leading-none mb-1"><?php echo htmlspecialchars($q['driver']); ?></span>
                                                        <span class="text-[10px] text-gray-500 uppercase tracking-widest">
                                                            <?php echo ($q['q3'] !== '-') ? 'Q3 Session' : (($q['q2'] !== '-') ? 'Q2 Session' : 'Q1 Session'); ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 text-right">
                                                <span class="text-xs font-mono text-f1-red font-bold">
                                                    <?php echo ($q['q3'] !== '-') ? $q['q3'] : (($q['q2'] !== '-') ? $q['q2'] : $q['q1']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <a href="race_result.php?round=<?php echo $selected_round; ?>" class="p-4 bg-white/[0.02] border-t border-white/5 text-center text-[10px] font-black uppercase tracking-widest text-gray-500 hover:text-white transition-colors">
                        Check Sprint & Practice Times →
                    </a>
                </div>
            </div>
            <div class="flex justify-center mt-12">
                <a href="race_result.php?round=<?php echo $selected_round; ?>" class="group flex items-center gap-4 bg-f1-red px-10 py-4 rounded-full font-oswald font-black uppercase italic tracking-widest hover:bg-white hover:text-black transition-all duration-500 shadow-xl shadow-f1-red/20">
                    <span>Full Weekend Results</span>
                    <span class="text-xl group-hover:translate-x-2 transition-transform">→</span>
                </a>
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
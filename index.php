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
<html lang="en">
<head>
    <?php include 'navigatie/head.php'; ?>
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "SportsEvent",
      "name": "Formula 1 World Championship 2026",
      "description": "The ultimate source for Formula 1 news and stats.",
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
        <section id="my-favorites-section" class="mb-16 hidden" data-aos="fade-down">
            <div class="flex items-center gap-3 mb-6">
                <span class="h-[2px] w-8 bg-f1-red"></span>
                <span class="text-f1-red text-[10px] font-black uppercase tracking-[0.3em]">My F1</span>
            </div>
            <div id="my-favorites-list" class="flex gap-4 overflow-x-auto pb-2"></div>
        </section>
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
                            <div id="weather-widget" class="hidden mt-4 inline-flex items-center gap-3 bg-white/5 border border-white/10 rounded-2xl px-5 py-3"></div>
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
                            <p class="text-gray-500 text-xs italic p-4">No data available in the database.</p>
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
        async function loadMyFavorites() {
            if (!window.F1Favorites) return;
            const driverIds = window.F1Favorites.getDrivers();
            const teamIds = window.F1Favorites.getTeams();
            if (!driverIds.length && !teamIds.length) return;

            try {
                const params = new URLSearchParams();
                if (driverIds.length) params.set('drivers', driverIds.join(','));
                if (teamIds.length) params.set('teams', teamIds.join(','));
                const res = await fetch(`favorites_api.php?${params.toString()}`);
                const data = await res.json();
                const section = document.getElementById('my-favorites-section');
                const list = document.getElementById('my-favorites-list');
                if (!data.drivers.length && !data.teams.length) return;

                let html = '';
                data.drivers.forEach(d => {
                    html += `<a href="driver-details.php?slug=${d.slug}" class="shrink-0 w-40 bg-f1-card rounded-2xl border border-white/5 overflow-hidden hover:border-f1-red/40 transition-all">
                        <div class="h-24 overflow-hidden bg-black/30"><img src="${d.image || ''}" class="w-full h-full object-cover object-top" onerror="this.style.opacity=0" alt="${d.name}"></div>
                        <div class="p-3 border-t-2" style="border-color:${d.team_color}">
                            <span class="block text-[10px] font-black uppercase tracking-widest text-white truncate">${d.name}</span>
                        </div>
                    </a>`;
                });
                data.teams.forEach(t => {
                    html += `<a href="team-details.php?id=${t.id}" class="shrink-0 w-40 bg-f1-card rounded-2xl border border-white/5 overflow-hidden hover:border-f1-red/40 transition-all flex flex-col items-center justify-center p-4 gap-3" style="border-top:2px solid ${t.team_color}">
                        <img src="${t.logo || ''}" class="h-10 object-contain" onerror="this.style.opacity=0" alt="${t.name}">
                        <span class="block text-[10px] font-black uppercase tracking-widest text-white text-center truncate">${t.name}</span>
                    </a>`;
                });
                list.innerHTML = html;
                section.classList.remove('hidden');
            } catch (e) { /* silently ignore */ }
        }

        function weatherIcon(code) {
            if (code === 0) return '☀️';
            if ([1,2,3].includes(code)) return '⛅';
            if ([45,48].includes(code)) return '🌫️';
            if ([51,53,55,56,57,61,63,65,66,67,80,81,82].includes(code)) return '🌧️';
            if ([71,73,75,77,85,86].includes(code)) return '❄️';
            if ([95,96,99].includes(code)) return '⛈️';
            return '🌡️';
        }

        async function loadWeatherWidget() {
            <?php if ($dbNextGP): ?>
            try {
                const res = await fetch(`achterkant/aanpassing/api-koppelingen/weather_api.php?circuit=<?php echo urlencode($dbNextGP['circuit_key']); ?>&date=<?php echo urlencode(date('Y-m-d', strtotime($dbNextGP['race_datetime']))); ?>&location=<?php echo urlencode($dbNextGP['location'] ?? ''); ?>`);
                const data = await res.json();
                const widget = document.getElementById('weather-widget');
                if (data.status === 'success') {
                    widget.innerHTML = `
                        <span class="text-3xl">${weatherIcon(data.weather_code)}</span>
                        <div class="text-left">
                            <p class="text-white font-oswald font-black italic text-lg leading-none">${data.temp_min}° &ndash; ${data.temp_max}°C</p>
                            <p class="text-[9px] text-gray-500 uppercase font-black tracking-widest mt-1">🌧️ ${data.rain_chance}% chance of rain · Race day</p>
                        </div>`;
                    widget.classList.remove('hidden');
                } else if (data.status === 'too_far') {
                    widget.innerHTML = `<span class="text-[10px] text-gray-500 uppercase font-black tracking-widest">Forecast available from 15 days before the race</span>`;
                    widget.classList.remove('hidden');
                }
            } catch (e) { /* silently ignore */ }
            <?php endif; ?>
        }

        document.addEventListener('DOMContentLoaded', () => {
            AOS.init({ duration: 1000, once: true });
            loadMyFavorites();
            loadWeatherWidget();

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
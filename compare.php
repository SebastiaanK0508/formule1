<?php
require_once 'db_config.php';
/** @var PDO $pdo */
$drivers = [];
try {
    $stmt = $pdo->query("SELECT driver_id, first_name, last_name, team_name FROM drivers WHERE is_active = 1 ORDER BY last_name ASC");
    $drivers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Compare page DB error: " . $e->getMessage());
}
$d1 = isset($_GET['d1']) ? (int)$_GET['d1'] : ($drivers[0]['driver_id'] ?? 0);
$d2 = isset($_GET['d2']) ? (int)$_GET['d2'] : ($drivers[1]['driver_id'] ?? 0);
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Comparison | F1SITE.NL</title>
    <meta name="description" content="Compare two Formula 1 drivers side by side: season performance, career stats and more." />
    <?php include 'navigatie/head.php'; ?>
    <style>
        .f1-border { position: relative; }
        .f1-border::before { content: ""; position: absolute; top: 0; left: 0; width: 45px; height: 4px; background: #E10600; z-index: 10; }
        .vs-badge { background: radial-gradient(circle, #E10600 0%, #7a0300 100%); }
        select.driver-select { appearance: none; -webkit-appearance: none; }
        .stat-row:nth-child(odd) { background: rgba(255,255,255,0.02); }
    </style>
</head>
<body class="bg-pattern">
    <?php include 'navigatie/header.php'; ?>
    <main class="max-w-6xl mx-auto px-6 py-12">
        <section class="mb-12" data-aos="fade-down">
            <h2 class="text-4xl md:text-6xl font-oswald font-black uppercase italic tracking-tighter leading-none">
                DRIVER <span class="text-f1-red">COMPARISON</span>
            </h2>
            <p class="text-gray-500 text-xs font-black uppercase tracking-[0.4em] mt-4 flex items-center gap-2">
                <span class="w-8 h-[1px] bg-f1-red"></span> Head-to-head stats
            </p>
        </section>

        <section class="grid grid-cols-1 md:grid-cols-[1fr_auto_1fr] gap-4 items-center mb-12" data-aos="fade-up">
            <select id="select-d1" class="driver-select w-full bg-f1-card border border-white/10 rounded-2xl px-6 py-4 font-oswald font-bold uppercase italic text-white focus:outline-none focus:border-f1-red">
                <?php foreach ($drivers as $d): ?>
                    <option value="<?php echo $d['driver_id']; ?>" <?php echo ($d['driver_id'] == $d1) ? 'selected' : ''; ?>><?php echo htmlspecialchars($d['first_name'] . ' ' . $d['last_name']); ?></option>
                <?php endforeach; ?>
            </select>
            <div class="vs-badge w-14 h-14 rounded-full flex items-center justify-center text-white font-oswald font-black italic mx-auto shrink-0">VS</div>
            <select id="select-d2" class="driver-select w-full bg-f1-card border border-white/10 rounded-2xl px-6 py-4 font-oswald font-bold uppercase italic text-white focus:outline-none focus:border-f1-red">
                <?php foreach ($drivers as $d): ?>
                    <option value="<?php echo $d['driver_id']; ?>" <?php echo ($d['driver_id'] == $d2) ? 'selected' : ''; ?>><?php echo htmlspecialchars($d['first_name'] . ' ' . $d['last_name']); ?></option>
                <?php endforeach; ?>
            </select>
        </section>

        <section id="compare-content" class="min-h-[400px]">
            <div class="flex flex-col items-center justify-center p-20">
                <div class="w-12 h-12 border-4 border-f1-red border-t-transparent rounded-full animate-spin"></div>
            </div>
        </section>
    </main>

    <?php include 'navigatie/footer.php'; ?>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        const content = document.getElementById('compare-content');
        const select1 = document.getElementById('select-d1');
        const select2 = document.getElementById('select-d2');

        function statRow(label, v1, v2, higherIsBetter = true) {
            const n1 = parseFloat(v1) || 0, n2 = parseFloat(v2) || 0;
            let c1 = 'text-white', c2 = 'text-white';
            if (n1 !== n2) {
                const win1 = higherIsBetter ? n1 > n2 : n1 < n2;
                c1 = win1 ? 'text-f1-red' : 'text-gray-500';
                c2 = win1 ? 'text-gray-500' : 'text-f1-red';
            }
            return `<div class="stat-row grid grid-cols-3 items-center px-6 py-4 rounded-xl">
                <span class="font-oswald font-black text-xl italic ${c1} text-left">${v1 ?? '–'}</span>
                <span class="text-[10px] font-black uppercase tracking-widest text-gray-500 text-center">${label}</span>
                <span class="font-oswald font-black text-xl italic ${c2} text-right">${v2 ?? '–'}</span>
            </div>`;
        }

        function driverHeader(d) {
            return `<div class="flex flex-col items-center text-center">
                <div class="w-32 h-32 rounded-full overflow-hidden border-4 mb-4" style="border-color:${d.team_color}">
                    <img src="${d.image || ''}" class="w-full h-full object-cover object-top" onerror="this.style.display='none'" alt="${d.name}">
                </div>
                <h3 class="text-2xl font-oswald font-black uppercase italic">${d.name}</h3>
                <p class="text-[10px] font-black uppercase tracking-widest mt-1" style="color:${d.team_color}">${d.team_name || 'Free Agent'} · #${d.number || '–'}</p>
                <a href="driver-details.php?slug=${d.name.toLowerCase().replace(/\s+/g,'-')}" class="mt-3 text-[9px] text-gray-500 hover:text-white uppercase tracking-widest border-b border-white/20">Full profile →</a>
            </div>`;
        }

        async function loadComparison() {
            content.innerHTML = `<div class="flex flex-col items-center justify-center p-20"><div class="w-12 h-12 border-4 border-f1-red border-t-transparent rounded-full animate-spin"></div></div>`;
            const d1 = select1.value, d2 = select2.value;
            try {
                const res = await fetch(`achterkant/aanpassing/api-koppelingen/compare_api.php?d1=${d1}&d2=${d2}`);
                const data = await res.json();
                if (!data.driver1 || !data.driver2) {
                    content.innerHTML = `<div class="bg-f1-card border border-white/5 p-12 rounded-[2rem] text-center text-gray-500 uppercase text-xs font-black tracking-widest">Could not load driver data.</div>`;
                    return;
                }
                const a = data.driver1, b = data.driver2;
                content.innerHTML = `
                    <div class="bg-f1-card rounded-[2.5rem] border border-white/5 p-8 md:p-12" data-aos="fade-up">
                        <div class="grid grid-cols-2 gap-8 mb-10 pb-10 border-b border-white/5">
                            ${driverHeader(a)}
                            ${driverHeader(b)}
                        </div>
                        <div class="space-y-1">
                            ${statRow('Season Points', a.season.points, b.season.points)}
                            ${statRow('Season Position', a.season.position, b.season.position, false)}
                            ${statRow('Season Wins', a.season.wins, b.season.wins)}
                            ${statRow('Career Points', a.career_points, b.career_points)}
                            ${statRow('Championships', a.championships_won, b.championships_won)}
                            ${statRow('Age', a.age, b.age, false)}
                        </div>
                    </div>`;
            } catch (e) {
                content.innerHTML = `<div class="bg-f1-card border border-f1-red/30 p-12 rounded-[2rem] text-center text-f1-red uppercase text-xs font-black tracking-widest">Connection error.</div>`;
            }
        }

        select1.addEventListener('change', loadComparison);
        select2.addEventListener('change', loadComparison);
        document.addEventListener('DOMContentLoaded', () => {
            AOS.init({ duration: 800, once: true });
            loadComparison();
        });
    </script>
</body>
</html>

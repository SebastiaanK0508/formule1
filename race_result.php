<?php 
require_once 'achterkant/aanpassing/api-koppelingen/1result_api.php'; 
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <?php include 'navigatie/head.php'; ?>
    <style>
        .results-scroll { max-height: 650px; overflow-y: auto; scrollbar-width: thin; scrollbar-color: #E10600 rgba(255,255,255,0.05); }
        .tab-btn.active { border-bottom: 3px solid #E10600; color: white; opacity: 1; }
        .pos-1 { color: #FFD700 !important; text-shadow: 0 0 15px rgba(255,215,0,0.2); }
        .pos-2 { color: #C0C0C0 !important; }
        .pos-3 { color: #CD7F32 !important; }
        .bg-f1-card { background: rgba(22, 22, 28, 0.95); backdrop-filter: blur(10px); }
    </style>
</head>
<body class="bg-pattern text-white">
    <?php include 'navigatie/header.php'; ?>

    <main class="max-w-7xl mx-auto px-6 py-12">
        
        <div class="mb-16" data-aos="fade-down">
            <div class="flex items-center gap-3 mb-4">
                <span class="h-[2px] w-10 bg-f1-red"></span>
                <span class="text-f1-red text-[10px] font-black uppercase tracking-[0.4em]">Official Classification</span>
            </div>
            <h1 class="text-6xl md:text-8xl font-oswald font-black uppercase italic tracking-tighter leading-none mb-4">
                <?php echo htmlspecialchars($race_details['name'] ?? 'Grand Prix'); ?>
            </h1>
            <div class="flex flex-wrap gap-6 text-gray-400 font-bold uppercase text-xs tracking-widest">
                <span>📍 <?php echo htmlspecialchars($race_details['circuit']); ?></span>
                <span>📅 <?php echo date('d F Y', strtotime($race_details['date'])); ?></span>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 mb-20">
            <section class="bg-f1-card rounded-[2.5rem] border border-white/5 overflow-hidden shadow-2xl" data-aos="fade-right">
                <div class="p-8 bg-white/5 border-b border-white/5 flex justify-between items-center">
                    <h2 class="text-3xl font-oswald font-black uppercase italic">Grand Prix <span class="text-f1-red">Race</span></h2>
                    <span class="bg-f1-red text-white text-[10px] px-3 py-1 font-black rounded-full italic uppercase">Official</span>
                </div>
                <div class="results-scroll">
                    <?php renderTable($race_results, false); ?>
                </div>
            </section>

            <section class="bg-f1-card rounded-[2.5rem] border border-white/5 overflow-hidden shadow-2xl" data-aos="fade-left">
                <div class="p-8 bg-white/5 border-b border-white/5 flex justify-between items-center">
                    <h2 class="text-3xl font-oswald font-black uppercase italic">Qualifying</h2>
                    <span class="border border-white/20 text-gray-400 text-[10px] px-3 py-1 font-black rounded-full italic uppercase">Saturday</span>
                </div>
                <div class="results-scroll">
                    <?php renderTable($qualifying_results, true); ?>
                </div>
            </section>
        </div>

        <?php if (!empty($sprint_results) || !empty($sprint_quali_results)): ?>
        <section class="mb-20" data-aos="fade-up">
            <div class="flex items-center justify-between mb-8">
                <h2 class="text-3xl font-oswald font-black uppercase italic">Sprint & <span class="text-f1-red">Sessions</span></h2>
                <div class="flex gap-4 border-b border-white/5">
                    <?php if(!empty($sprint_results)): ?>
                        <button onclick="openTab(event, 'sprint-race')" class="tab-btn active px-6 py-4 text-[10px] font-black uppercase tracking-widest transition-all">Sprint Race</button>
                    <?php endif; ?>
                    </div>
            </div>

            <div id="sprint-race" class="tab-content block bg-f1-card rounded-3xl border border-white/5 overflow-hidden shadow-xl">
                <?php renderTable($sprint_results, false); ?>
            </div>
        </section>
        <?php endif; ?>

    </main>

    <?php include 'navigatie/footer.php'; ?>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({ duration: 1000, once: true });
        function openTab(evt, tabName) {
            let i, tabcontent, tablinks;
            tabcontent = document.getElementsByClassName("tab-content");
            for (i = 0; i < tabcontent.length; i++) { tabcontent[i].style.display = "none"; }
            tablinks = document.getElementsByClassName("tab-btn");
            for (i = 0; i < tablinks.length; i++) { tablinks[i].className = tablinks[i].className.replace(" active", ""); }
            document.getElementById(tabName).style.display = "block";
            evt.currentTarget.className += " active";
        }
    </script>
</body>
</html>

<?php
function renderTable($data, $isQualy) {
    if (empty($data)) {
        echo "<p class='p-10 text-center text-gray-500 italic uppercase text-xs tracking-widest'>Geen data beschikbaar voor deze sessie.</p>";
        return;
    }
    echo '<table class="w-full text-left"><tbody class="divide-y divide-white/5">';
    foreach ($data as $res) {
        $pos = $res['position'];
        $posClass = ($pos <= 3) ? 'pos-' . $pos : '';
        $driver = $isQualy ? $res['driver'] : $res['driver_name'];
        $time = $isQualy ? (($res['q3'] !== '-') ? $res['q3'] : (($res['q2'] !== '-') ? $res['q2'] : $res['q1'])) : $res['lap_time_or_status'];
        
        echo '<tr class="hover:bg-white/[0.02] transition-colors group">';
        echo '<td class="px-8 py-5 font-oswald font-bold text-3xl italic text-gray-800 w-24 ' . $posClass . '">' . str_pad($pos, 2, "0", STR_PAD_LEFT) . '</td>';
        echo '<td class="px-4 py-5">';
        echo '<div class="flex items-center gap-4">';
        echo '<div class="w-1.5 h-10 rounded-full" style="background-color:' . $res['team_color'] . '"></div>';
        echo '<div><span class="text-white font-bold block text-base leading-none mb-1">' . htmlspecialchars($driver) . '</span>';
        echo '<span class="text-[9px] text-gray-500 uppercase font-black tracking-tighter">' . ($res['team_name'] ?? '') . '</span></div>';
        echo '</div></td>';
        echo '<td class="px-8 py-5 text-right"><span class="text-[11px] font-mono text-f1-red font-bold">' . $time . '</span></td>';
        echo '</tr>';
    }
    echo '</tbody></table>';
}
?>
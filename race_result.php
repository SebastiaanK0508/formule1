<?php 
require_once 'achterkant/aanpassing/api-koppelingen/1result_api.php'; 
?>
<!DOCTYPE html>
<html lang="en">
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

        <?php
        $gainLossData = [];
        foreach ($race_results as $rr) {
            if (!empty($rr['grid']) && (int)$rr['grid'] > 0 && is_numeric($rr['position'])) {
                $gainLossData[] = [
                    'driver' => $rr['driver_name'],
                    'team_color' => $rr['team_color'],
                    'delta' => (int)$rr['grid'] - (int)$rr['position'],
                ];
            }
        }
        usort($gainLossData, fn($a, $b) => $b['delta'] <=> $a['delta']);
        ?>
        <?php if (!empty($gainLossData)): ?>
        <section class="mb-16" data-aos="fade-up">
            <div class="bg-f1-card rounded-[2.5rem] border border-white/5 p-6 md:p-10">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h4 class="text-xl font-oswald font-black uppercase italic tracking-wider">Grid vs Finish</h4>
                        <p class="text-[10px] text-gray-500 uppercase tracking-widest mt-1">Positions Gained &amp; Lost</p>
                    </div>
                </div>
                <div class="relative" style="height: <?php echo max(320, count($gainLossData) * 26); ?>px;">
                    <canvas id="gainLossChart"></canvas>
                </div>
            </div>
        </section>
        <script>
            window.__gainLossData = <?php echo json_encode($gainLossData); ?>;
        </script>
        <?php endif; ?>

        <?php if (!empty($fastest_lap_holder) || !empty($pit_stops)): ?>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-16">
            <?php if (!empty($fastest_lap_holder)): ?>
            <div class="lg:col-span-1 bg-f1-card rounded-[2rem] border border-purple-500/30 p-8 relative overflow-hidden" data-aos="fade-up">
                <div class="absolute -top-6 -right-6 text-8xl opacity-10">⚡</div>
                <span class="text-purple-400 text-[10px] font-black uppercase tracking-[0.3em]">Fastest Lap</span>
                <div class="mt-4 flex items-center gap-4">
                    <div class="w-1.5 h-12 rounded-full" style="background-color: <?php echo htmlspecialchars($fastest_lap_holder['team_color']); ?>;"></div>
                    <div>
                        <p class="text-xl font-oswald font-black uppercase italic text-white leading-none"><?php echo htmlspecialchars($fastest_lap_holder['driver_name']); ?></p>
                        <p class="text-[10px] text-gray-500 uppercase font-black tracking-widest mt-1"><?php echo htmlspecialchars($fastest_lap_holder['team_name']); ?> · Lap <?php echo htmlspecialchars($fastest_lap_holder['lap']); ?></p>
                    </div>
                </div>
                <p class="mt-6 text-4xl font-mono font-black text-purple-400"><?php echo htmlspecialchars($fastest_lap_holder['time']); ?></p>
            </div>
            <?php endif; ?>

            <?php if (!empty($pit_stops)): ?>
            <div class="lg:col-span-2 bg-f1-card rounded-[2rem] border border-white/5 overflow-hidden" data-aos="fade-up" data-aos-delay="100">
                <div class="p-6 bg-white/5 border-b border-white/5 flex justify-between items-center">
                    <span class="text-xs font-black uppercase tracking-widest text-white">Pit Stop Log</span>
                    <span class="text-[10px] text-gray-500 uppercase font-black"><?php echo count($pit_stops); ?> stops</span>
                </div>
                <div class="results-scroll max-h-[220px]">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="text-[9px] font-black uppercase tracking-widest text-gray-500 bg-black/20">
                                <th class="px-6 py-3">Driver</th>
                                <th class="px-6 py-3">Lap</th>
                                <th class="px-6 py-3">Stop #</th>
                                <th class="px-6 py-3 text-right">Duration</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            <?php foreach ($pit_stops as $ps): ?>
                            <tr class="hover:bg-white/[0.02] transition-colors">
                                <td class="px-6 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-1 h-6 rounded-full" style="background-color: <?php echo htmlspecialchars($ps['team_color']); ?>;"></div>
                                        <span class="text-white font-bold text-xs"><?php echo htmlspecialchars($ps['driver_name']); ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-3 text-gray-400 font-mono text-xs"><?php echo htmlspecialchars($ps['lap']); ?></td>
                                <td class="px-6 py-3 text-gray-400 font-mono text-xs"><?php echo htmlspecialchars($ps['stop']); ?></td>
                                <td class="px-6 py-3 text-right font-mono text-f1-red font-bold text-xs"><?php echo htmlspecialchars($ps['duration']); ?>s</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.4/chart.umd.min.js"></script>
    <script>
        AOS.init({ duration: 1000, once: true });

        if (window.__gainLossData && window.__gainLossData.length) {
            const gl = window.__gainLossData;
            new Chart(document.getElementById('gainLossChart').getContext('2d'), {
                type: 'bar',
                data: {
                    labels: gl.map(d => d.driver),
                    datasets: [{
                        data: gl.map(d => d.delta),
                        backgroundColor: gl.map(d => d.delta > 0 ? '#22c55e' : (d.delta < 0 ? '#E10600' : '#6b7280')),
                        borderRadius: 4,
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { ticks: { color: '#6b7280' }, grid: { color: 'rgba(255,255,255,0.05)' } },
                        y: { ticks: { color: '#d1d5db', font: { weight: 'bold', size: 10 } }, grid: { display: false } },
                    }
                }
            });
        }
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
        echo "<p class='p-10 text-center text-gray-500 italic uppercase text-xs tracking-widest'>No data available for this session.</p>";
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
        $flBadge = (!$isQualy && ($res['fastest_lap_rank'] ?? null) === '1') ? ' <span class="text-purple-400" title="Fastest Lap">⚡</span>' : '';
        echo '<div><span class="text-white font-bold block text-base leading-none mb-1">' . htmlspecialchars($driver) . $flBadge . '</span>';
        echo '<span class="text-[9px] text-gray-500 uppercase font-black tracking-tighter">' . ($res['team_name'] ?? '') . '</span></div>';
        echo '</div></td>';
        echo '<td class="px-8 py-5 text-right"><span class="text-[11px] font-mono text-f1-red font-bold">' . $time . '</span></td>';
        echo '</tr>';
    }
    echo '</tbody></table>';
}
?>
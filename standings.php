<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>F1 Standings <?php echo date('Y'); ?> | F1SITE.NL</title>
    <meta name="description" content="The current standings in the Formula 1 world championship for drivers and constructors." />
    <?php include 'navigatie/head.php'; ?>
    <style>
        .f1-border { position: relative; }
        .f1-border::before { content: ""; position: absolute; top: 0; left: 0; width: 45px; height: 4px; background: #E10600; z-index: 10; }
        .standing-row { transition: all 0.2s ease; border-bottom: 1px solid rgba(255,255,255,0.03); }
        .standing-row:hover { background: rgba(225, 6, 0, 0.05); }
        .standing-row:last-child { border-bottom: none; }
    </style>
</head>
<body class="bg-pattern">
    <?php include 'navigatie/header.php'; ?>
    <main class="max-w-7xl mx-auto px-6 py-12">
        <section class="mb-12" data-aos="fade-down">
            <h2 class="text-4xl md:text-6xl font-oswald font-black uppercase italic tracking-tighter leading-none">
                CHAMPIONSHIP <span class="text-f1-red">STANDINGS</span>
            </h2>
            <p class="text-gray-500 text-xs font-black uppercase tracking-[0.4em] mt-4 flex items-center gap-2">
                <span class="w-8 h-[1px] bg-f1-red"></span> Season <?php echo date('Y'); ?> Official Rankings
            </p>
        </section>
        <section id="standings-content" class="min-h-[400px]">
            <div class="flex flex-col items-center justify-center p-20">
                <div class="w-12 h-12 border-4 border-f1-red border-t-transparent rounded-full animate-spin"></div>
                <p class="mt-4 text-[10px] font-black uppercase tracking-[0.3em] text-gray-500 italic">Syncing with FIA data...</p>
            </div>
        </section>

        <section class="mt-16" data-aos="fade-up">
            <div class="bg-f1-card rounded-[2.5rem] border border-white/5 p-6 md:p-10">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h4 class="text-xl font-oswald font-black uppercase italic tracking-wider">Points Progression</h4>
                        <p class="text-[10px] text-gray-500 uppercase tracking-widest mt-1">Top 6 · Round by Round</p>
                    </div>
                </div>
                <div id="progress-chart-wrap" class="relative h-[350px] md:h-[420px]">
                    <canvas id="progressChart"></canvas>
                </div>
                <p id="progress-empty" class="hidden text-center text-gray-500 italic uppercase text-xs tracking-widest py-10">No race round data available yet this season.</p>
            </div>
        </section>

    </main>
    <?php include 'navigatie/footer.php'; ?>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.4/chart.umd.min.js"></script>
    <script>
        const standingsContent = document.getElementById('standings-content');

        async function fetchProgressionChart() {
            try {
                const response = await fetch('achterkant/aanpassing/api-koppelingen/standings_progress_api.php');
                const data = await response.json();
                if (data.status !== 'success' || !data.rounds.length || !data.drivers.length) {
                    document.getElementById('progress-chart-wrap').classList.add('hidden');
                    document.getElementById('progress-empty').classList.remove('hidden');
                    return;
                }
                const ctx = document.getElementById('progressChart').getContext('2d');
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: data.rounds,
                        datasets: data.drivers.map(d => ({
                            label: d.name,
                            data: d.points,
                            borderColor: d.team_color,
                            backgroundColor: d.team_color + '33',
                            tension: 0.3,
                            pointRadius: 2,
                            borderWidth: 2,
                        }))
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: { mode: 'index', intersect: false },
                        plugins: {
                            legend: { labels: { color: '#d1d5db', font: { weight: 'bold', size: 10 } } },
                        },
                        scales: {
                            x: { ticks: { color: '#6b7280' }, grid: { color: 'rgba(255,255,255,0.05)' } },
                            y: { ticks: { color: '#6b7280' }, grid: { color: 'rgba(255,255,255,0.05)' } },
                        }
                    }
                });
            } catch (e) {
                document.getElementById('progress-chart-wrap').classList.add('hidden');
                document.getElementById('progress-empty').classList.remove('hidden');
            }
        }
        
        async function fetchChampionshipStandings() {
            try {
                const response = await fetch('achterkant/aanpassing/api-koppelingen/standings_api.php'); 
                const data = await response.json();
                
                if (data.status === 'success' && (data.drivers.length > 0 || data.constructors.length > 0)) {
                    displayChampionshipStandings(data.drivers, data.constructors);
                } else {
                    renderEmptyState();
                }
            } catch (error) {
                standingsContent.innerHTML = `
                    <div class="bg-f1-card border border-f1-red/30 p-10 rounded-3xl text-center">
                        <p class="text-f1-red font-bold uppercase tracking-widest italic">Connection Error: Could not reach the paddock data.</p>
                    </div>`;
            }
        }

        function renderEmptyState() {
            standingsContent.innerHTML = `
                <div class="bg-f1-card border border-white/5 p-12 md:p-20 rounded-[3rem] text-center" data-aos="zoom-in">
                    <div class="text-f1-red text-6xl mb-8">🏁</div>
                    <h3 class="text-3xl md:text-4xl font-oswald font-black text-white mb-4 uppercase italic italic tracking-tighter">Season hasn't started yet</h3>
                    <p class="text-gray-500 max-w-md mx-auto font-medium leading-relaxed uppercase text-[10px] tracking-[0.2em]">There are no points earned at this moment. As soon as the lights go out in Bahrain, the live standings will appear here.</p>
                    <a href="kalender.php" class="inline-block mt-10 bg-white text-black px-10 py-4 rounded-full font-black uppercase text-[10px] tracking-widest hover:bg-f1-red hover:text-white transition-all duration-300">View Schedule</a>
                </div>
            `;
        }

        function displayChampionshipStandings(drivers, constructors) {
            let html = '<div class="grid grid-cols-1 lg:grid-cols-2 gap-10 items-start">';
            html += `
            <div class="f1-border bg-f1-card rounded-br-[3rem] border-r border-b border-white/5 overflow-hidden" data-aos="fade-right">
                <div class="p-6 border-b border-white/5 bg-white/5">
                    <h4 class="text-xl font-oswald font-black text-white uppercase italic tracking-wider">Drivers Championship</h4>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-[10px] font-black uppercase tracking-widest text-gray-500 bg-black/20">
                                <th class="px-6 py-4">Pos</th>
                                <th class="px-6 py-4">Driver</th>
                                <th class="px-6 py-4 text-right">Points</th>
                            </tr>
                        </thead>
                        <tbody>`;
            
            drivers.forEach(driver => {
                html += `
                    <tr class="standing-row group">
                        <td class="px-6 py-5 font-oswald font-black italic text-xl text-gray-500 group-hover:text-f1-red transition-colors">${driver.position}</td>
                        <td class="px-6 py-5">
                            <div class="font-bold text-white uppercase text-sm tracking-tight">${driver.given_name} <span class="text-f1-red">${driver.family_name}</span></div>
                            <div class="text-[9px] font-black text-gray-600 uppercase tracking-widest mt-1">${driver.constructor_name}</div>
                        </td>
                        <td class="px-6 py-5 text-right font-oswald font-black italic text-xl text-white">${driver.points}</td>
                    </tr>`;
            });
            html += '</tbody></table></div></div>';
            html += `
            <div class="f1-border bg-f1-card rounded-br-[3rem] border-r border-b border-white/5 overflow-hidden" data-aos="fade-left">
                <div class="p-6 border-b border-white/5 bg-white/5">
                    <h4 class="text-xl font-oswald font-black text-white uppercase italic tracking-wider">Constructors Championship</h4>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-[10px] font-black uppercase tracking-widest text-gray-500 bg-black/20">
                                <th class="px-6 py-4">Pos</th>
                                <th class="px-6 py-4">Team</th>
                                <th class="px-6 py-4 text-right">Points</th>
                            </tr>
                        </thead>
                        <tbody>`;
            
            constructors.forEach(constructor => {
                html += `
                    <tr class="standing-row group">
                        <td class="px-6 py-5 font-oswald font-black italic text-xl text-gray-500 group-hover:text-f1-red transition-colors">${constructor.position}</td>
                        <td class="px-6 py-5">
                            <div class="font-bold text-white uppercase text-sm tracking-tight">${constructor.name}</div>
                        </td>
                        <td class="px-6 py-5 text-right font-oswald font-black italic text-xl text-white">${constructor.points}</td>
                    </tr>`;
            });
            html += '</tbody></table></div></div></div>';
            
            standingsContent.innerHTML = html;
        }
        document.addEventListener('DOMContentLoaded', () => {
            AOS.init({ duration: 800, once: true });
            fetchChampionshipStandings();
            fetchProgressionChart();
            
            window.toggleMenu = () => {
                document.getElementById('mobile-menu').classList.toggle('active');
            };
        });
    </script>
</body>
</html>
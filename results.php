<?php
require_once 'achterkant/aanpassing/api-koppelingen/result_api.php';

// Zorg dat we variabelen hebben voor de selector, ook als de API hapert
$available_years = $available_years ?? range(date("Y"), 1950);
$races_in_season = $races_in_season ?? [];
$selected_year = $_GET['year'] ?? date("Y");
$selected_round = $_GET['round'] ?? 1;

$schemaData = [];
if (!empty($race_results) && $race_details) {
    $results = [];
    foreach ($race_results as $result) {
        $results[] = [
            '@type' => 'Person',
            'name' => htmlspecialchars($result['driver_name']),
            'alumniOf' => ['@type' => 'SportsTeam', 'name' => htmlspecialchars($result['team_name'])],
            'sport' => 'Formula 1',
        ];
    }
    $raceDate = (new DateTime($race_details['date']))->format('Y-m-d');
    $schemaData = [
        '@context' => 'https://schema.org',
        '@type' => 'SportsEvent',
        'name' => 'Grand Prix van ' . htmlspecialchars($race_details['name']) . ' ' . htmlspecialchars($race_details['year']),
        'startDate' => $raceDate,
        'location' => ['@type' => 'Place', 'name' => htmlspecialchars($race_details['circuit'])],
        'result' => [
            '@type' => 'SportsResults',
            'winningTeam' => $race_results[0]['team_name'] ?? 'N/A',
            'winningTies' => ['@type' => 'Win', 'winner' => ['@type' => 'Person', 'name' => $race_results[0]['driver_name'] ?? 'N/A']],
            'performer' => $results,
        ],
        'sport' => 'Formula 1'
    ];
}
?>
<!DOCTYPE html>
<html lang="nl" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>F1 Results <?php echo $race_details['name'] ?? 'Overview'; ?> | F1SITE.NL</title>
    
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
        
        #mobile-menu, #mobile-selection-drawer { transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1); }
        .active-drawer { transform: translateY(0) !important; }

        .sidebar-scroll::-webkit-scrollbar { width: 4px; }
        .sidebar-scroll::-webkit-scrollbar-thumb { background: #E10600; border-radius: 10px; }

        .standing-row { transition: all 0.2s ease; border-bottom: 1px solid rgba(255,255,255,0.03); }
        .standing-row:hover { background: rgba(225, 6, 0, 0.05); }
    </style>
    
    <?php if (!empty($schemaData)): ?>
    <script type="application/ld+json"><?php echo json_encode($schemaData); ?></script>
    <?php endif; ?>
</head>
<body class="bg-pattern">

    <div id="mobile-menu" class="fixed inset-y-0 right-0 w-full p-10 flex flex-col items-center justify-center bg-f1-dark z-[110] translate-x-full lg:hidden">
        <button onclick="toggleMenu()" class="absolute top-8 right-8 text-5xl font-light">&times;</button>
        <nav class="flex flex-col space-y-10 text-4xl font-oswald font-black uppercase italic text-center">
            <a href="index.php">Home</a>
            <a href="kalender.php">Schedule</a>
            <a href="teams.php">Teams</a>
            <a href="drivers.php">Drivers</a>
            <a href="results.php" class="text-f1-red">Results</a>
            <a href="standings.php">Standings</a>
        </nav>
    </div>

    <div id="mobile-selection-drawer" class="fixed inset-0 z-[120] bg-f1-dark/98 backdrop-blur-xl translate-y-full lg:hidden">
        <div class="flex flex-col h-full">
            <div class="p-6 border-b border-white/10 flex justify-between items-center">
                <h3 class="font-oswald font-black italic uppercase text-xl">Archive Select</h3>
                <button onclick="toggleSelectionDrawer()" class="text-4xl text-f1-red">&times;</button>
            </div>
            
            <div class="p-4 border-b border-white/5">
                <input type="text" id="raceSearch" placeholder="Search Grand Prix..." 
                       class="w-full bg-white/5 border border-white/10 rounded-xl p-4 text-sm font-bold focus:outline-none focus:ring-1 focus:ring-f1-red">
            </div>

            <div class="flex-grow overflow-y-auto p-6 space-y-8 sidebar-scroll">
                <div>
                    <label class="text-[10px] font-black uppercase tracking-[0.3em] text-f1-red mb-4 block">Select Season</label>
                    <div class="grid grid-cols-3 gap-2">
                        <?php foreach ($available_years as $year): ?>
                            <a href="results.php?year=<?php echo $year; ?>" 
                               class="py-3 text-center rounded-xl font-bold text-sm border <?php echo ($selected_year == $year) ? 'bg-f1-red border-f1-red text-white' : 'bg-white/5 border-white/10 text-gray-400'; ?>">
                                <?php echo $year; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div id="raceListContainer">
                    <label class="text-[10px] font-black uppercase tracking-[0.3em] text-f1-red mb-4 block">Select Race (<?php echo $selected_year; ?>)</label>
                    <div class="space-y-2">
                        <?php foreach ($races_in_season as $race): ?>
                            <a href="results.php?year=<?php echo $selected_year; ?>&round=<?php echo $race['round']; ?>"
                               class="race-item flex items-center justify-between p-4 rounded-2xl border <?php echo ($selected_round == $race['round']) ? 'bg-f1-red border-f1-red text-white' : 'bg-white/5 border-white/10 text-gray-300'; ?>">
                                <span class="text-xs font-black uppercase italic tracking-tight race-name"><?php echo htmlspecialchars($race['raceName']); ?></span>
                                <span class="opacity-50 text-[10px]">#<?php echo $race['round']; ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <header class="header-glass sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-6 py-5 flex justify-between items-center">
            <a href="index.php" class="text-3xl font-oswald font-black italic tracking-tighter text-white uppercase">F1SITE<span class="text-f1-red">.NL</span></a>
            <nav class="hidden lg:flex space-x-10 text-[11px] font-bold uppercase tracking-[0.25em]">
                <a href="index.php" class="hover:text-f1-red transition">Home</a>
                <a href="kalender.php" class="hover:text-f1-red transition">Schedule</a>
                <a href="teams.php" class="hover:text-f1-red transition">Teams</a>
                <a href="drivers.php" class="hover:text-f1-red transition">Drivers</a>
                <a href="results.php" class="text-f1-red border-b-2 border-f1-red pb-1">Results</a>
                <a href="standings.php" class="hover:text-f1-red transition">Standings</a>
            </nav>
            <button onclick="toggleMenu()" class="lg:hidden text-white text-3xl">☰</button>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-6 py-12">
        <div class="lg:hidden mb-8">
            <button onclick="toggleSelectionDrawer()" class="w-full flex items-center justify-center gap-3 bg-f1-red py-4 rounded-2xl font-black uppercase text-xs tracking-widest shadow-xl shadow-f1-red/30 active:scale-95 transition-transform">
                <span>🏁</span> Filter Season & Race
            </button>
        </div>

        <div class="flex flex-col lg:flex-row gap-10">
            <aside class="hidden lg:block w-72 shrink-0 space-y-8" data-aos="fade-right">
                <div class="bg-f1-card p-6 rounded-3xl border border-white/5 shadow-2xl">
                    <form action="results.php" method="get">
                        <label class="text-[9px] font-black uppercase tracking-[0.3em] text-f1-red mb-3 block">Archive</label>
                        <select name="year" onchange="this.form.submit()" class="w-full bg-black border border-white/10 text-white p-3 rounded-xl text-xs font-bold focus:ring-1 focus:ring-f1-red cursor-pointer">
                            <?php foreach ($available_years as $year): ?>
                                <option value="<?php echo $year; ?>" <?php echo ($selected_year == $year) ? 'selected' : ''; ?>>Season <?php echo $year; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>

                <div class="bg-f1-card rounded-3xl border border-white/5 overflow-hidden shadow-2xl">
                    <div class="p-6 border-b border-white/5 bg-white/5">
                        <h4 class="text-[10px] font-black uppercase tracking-[0.2em] italic">Season Calendar</h4>
                    </div>
                    <nav class="p-4 space-y-1 max-h-[500px] overflow-y-auto sidebar-scroll">
                        <?php foreach ($races_in_season as $race): ?>
                            <a href="results.php?year=<?php echo $selected_year; ?>&round=<?php echo $race['round']; ?>"
                               class="flex items-center gap-3 p-3 rounded-xl text-[10px] font-bold uppercase transition-all
                               <?php echo ($selected_round == $race['round']) ? 'bg-f1-red text-white' : 'text-gray-500 hover:text-white hover:bg-white/5'; ?>">
                                <span class="opacity-30">#<?php echo $race['round']; ?></span>
                                <?php echo htmlspecialchars($race['raceName']); ?>
                            </a>
                        <?php endforeach; ?>
                    </nav>
                </div>
            </aside>

            <div class="flex-grow min-w-0">
                <?php if (!empty($error_message)): ?>
                    <div class="bg-red-500/10 border border-red-500/50 p-6 rounded-3xl text-red-500 font-bold uppercase text-xs tracking-widest italic">
                        ⚠️ <?php echo $error_message; ?>
                    </div>
                <?php else: ?>
                    
                    <div class="bg-f1-card rounded-[3rem] p-8 md:p-12 border border-white/5 mb-10 relative overflow-hidden" data-aos="fade-down">
                        <div class="relative z-10">
                            <div class="flex items-center gap-3 mb-4">
                                <span class="w-8 h-[1px] bg-f1-red"></span>
                                <span class="text-[10px] font-black uppercase tracking-[0.4em] text-gray-500 italic">Grand Prix Result</span>
                            </div>
                            <h2 class="text-4xl md:text-6xl font-oswald font-black uppercase italic tracking-tighter leading-none mb-6">
                                <?php echo htmlspecialchars($race_details['name'] ?? ''); ?> <span class="text-f1-red"><?php echo $race_details['year'] ?? ''; ?></span>
                            </h2>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-6 border-t border-white/5">
                                <div>
                                    <span class="text-[9px] font-black text-gray-500 uppercase tracking-widest">Circuit</span>
                                    <p class="text-xs font-bold text-white mt-1"><?php echo htmlspecialchars($race_details['circuit'] ?? 'N/A'); ?></p>
                                </div>
                                <div>
                                    <span class="text-[9px] font-black text-gray-500 uppercase tracking-widest">Location</span>
                                    <p class="text-xs font-bold text-white mt-1"><?php echo htmlspecialchars($race_details['location'] ?? ''); ?>, <?php echo htmlspecialchars($race_details['country'] ?? ''); ?></p>
                                </div>
                                <div>
                                    <span class="text-[9px] font-black text-gray-500 uppercase tracking-widest">Date</span>
                                    <p class="text-xs font-bold text-white mt-1"><?php echo isset($race_details['date']) ? (new DateTime($race_details['date']))->format('d-m-Y') : 'N/A'; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-f1-card rounded-[2.5rem] border border-white/5 overflow-hidden shadow-2xl" data-aos="fade-up">
                        <?php if (empty($race_results)): ?>
                            <div class="p-20 text-center italic">
                                <div class="text-4xl mb-4">🏁</div>
                                <p class="text-gray-500 font-black uppercase text-[10px] tracking-widest">No results available for this session.</p>
                            </div>
                        <?php else: ?>
                            <div class="overflow-x-auto">
                                <table class="w-full text-left">
                                    <thead>
                                        <tr class="bg-black/40 text-[9px] font-black uppercase tracking-[0.2em] text-gray-500">
                                            <th class="px-6 py-5">Pos</th>
                                            <th class="px-6 py-5">Driver</th>
                                            <th class="px-6 py-5">Team</th>
                                            <th class="px-6 py-5 text-right">Time / Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-white/5">
                                        <?php foreach ($race_results as $result): 
                                            $tColor = htmlspecialchars($result['team_color'] ?? '#3A3A40');
                                            $teamSlug = strtolower(str_replace(' ', '-', $result['team_name']));
                                        ?>
                                            <tr class="standing-row group">
                                                <td class="px-6 py-5">
                                                    <span class="font-oswald font-black italic text-xl group-hover:text-f1-red transition-colors"><?php echo $result['position']; ?></span>
                                                </td>
                                                <td class="px-6 py-5">
                                                    <div class="text-sm font-bold text-white uppercase"><?php echo htmlspecialchars($result['driver_name']); ?></div>
                                                </td>
                                                <td class="px-6 py-5">
                                                    <a href="team-details.php?slug=<?php echo $teamSlug; ?>" class="flex items-center gap-2 group/team">
                                                        <span class="w-1 h-3 rounded-full" style="background: <?php echo $tColor; ?>;"></span>
                                                        <span class="text-[10px] font-black uppercase tracking-wider group-hover/team:underline" style="color: <?php echo $tColor; ?>;">
                                                            <?php echo htmlspecialchars($result['team_name']); ?>
                                                        </span>
                                                    </a>
                                                </td>
                                                <td class="px-6 py-5 text-right font-mono text-[11px] text-gray-400">
                                                    <?php echo htmlspecialchars($result['lap_time_or_status']); ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <footer class="bg-black mt-24 py-16 border-t-2 border-f1-red">
        <div class="max-w-7xl mx-auto px-6 text-center">
            <h3 class="text-2xl font-oswald font-black text-white italic tracking-tighter uppercase mb-4">F1SITE<span class="text-f1-red">.NL</span></h3>
            <p class="text-gray-600 text-[10px] font-black uppercase tracking-[0.5em] italic">&copy; 2026 WEBIUS. All rights reserved.</p>
        </div>
    </footer>
    
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            AOS.init({ duration: 800, once: true });
            window.toggleMenu = () => {
                const menu = document.getElementById('mobile-menu');
                menu.classList.toggle('translate-x-full');
            };
            window.toggleSelectionDrawer = () => {
                const drawer = document.getElementById('mobile-selection-drawer');
                drawer.classList.toggle('active-drawer');
                document.body.style.overflow = drawer.classList.contains('active-drawer') ? 'hidden' : '';
            };
            const searchInput = document.getElementById('raceSearch');
            searchInput.addEventListener('input', function(e) {
                const term = e.target.value.toLowerCase();
                const items = document.querySelectorAll('.race-item');
                items.forEach(item => {
                    const name = item.querySelector('.race-name').textContent.toLowerCase();
                    if (name.includes(term)) {
                        item.style.display = 'flex';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        });
    </script> 
</body>
</html>
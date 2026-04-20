<?php
require_once 'db_config.php';
/** @var PDO $pdo */
$activeTeams = [];
try {
    $stmt = $pdo->query("SELECT team_id, team_name, base_location, team_principal, team_color, full_team_name FROM teams WHERE is_active = TRUE ORDER BY team_name ASC");
    $activeTeams = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (\PDOException $e) {
    error_log("Fout bij het ophalen van actieve teams: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="nl" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>F1 Teams <?php echo date('Y'); ?> | F1SITE.NL</title>
    <?php include 'navigatie/head.php'; ?>
    <style>
        .team-card {
            background: linear-gradient(145deg, rgba(22, 22, 28, 0.9), rgba(11, 11, 15, 1));
            transition: all 0.5s cubic-bezier(0.23, 1, 0.32, 1);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        .team-card:hover {
            transform: scale(1.02) translateY(-5px);
            border-color: var(--team-color-border);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4), 0 0 20px var(--team-color-shadow);
        }
        .speed-line {
            position: absolute;
            bottom: 0;
            left: 0;
            height: 4px;
            width: 0;
            transition: width 0.6s ease;
        }
        .team-card:hover .speed-line { width: 100%; }
        .history-grid::-webkit-scrollbar { width: 4px; }
        .history-grid::-webkit-scrollbar-thumb { background: #E10600; border-radius: 10px; }
    </style>
</head>
<body class="bg-pattern text-white italic">
    <?php include 'navigatie/header.php'; ?>

    <main class="max-w-7xl mx-auto px-6 py-16">
        
        <header class="mb-20 text-center" data-aos="zoom-out">
            <span class="text-f1-red font-black tracking-[0.4em] text-xs uppercase mb-4 block underline decoration-f1-red/30 underline-offset-8">Constructor Standings</span>
            <h1 class="text-6xl md:text-8xl font-oswald font-black uppercase italic tracking-tighter leading-none">
                THE <span class="text-f1-red">GRID</span>
            </h1>
            <p class="text-gray-500 mt-6 max-w-xl mx-auto text-sm md:text-base leading-relaxed">
                Explore the engineering titans of the <?php echo date('Y'); ?> season. An elite lineup ranging from heritage legends to modern-day powerhouses.
            </p>
        </header>

        <section class="mb-32">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php if (!empty($activeTeams)): ?>
                    <?php foreach ($activeTeams as $i => $team): 
                        $teamColor = htmlspecialchars($team['team_color'] ?? '#E10600');
                        list($r, $g, $b) = sscanf($teamColor, "#%02x%02x%02x");
                    ?>
                        <article data-aos="fade-up" data-aos-delay="<?php echo $i*50; ?>" 
                                 class="team-card relative group rounded-2xl p-1 overflow-hidden"
                                 style="--team-color-border: <?php echo $teamColor; ?>; --team-color-shadow: rgba(<?php echo "$r,$g,$b"; ?>, 0.2);">
                            
                            <div class="speed-line" style="background-color: <?php echo $teamColor; ?>;"></div>
                            
                            <a href="team-details.php?id=<?php echo htmlspecialchars($team['team_id']); ?>" class="relative block p-8 h-full bg-[#16161c] rounded-2xl">
                                <div class="absolute top-6 right-6 flex items-center gap-2">
                                    <span class="text-[9px] font-black uppercase tracking-widest text-gray-500 group-hover:text-white">Active</span>
                                    <div class="w-2 h-2 rounded-full animate-pulse" style="background-color: <?php echo $teamColor; ?>;"></div>
                                </div>

                                <h3 class="text-3xl font-oswald font-black uppercase italic mb-8 pr-10 leading-[0.9] group-hover:scale-105 transition-transform origin-left">
                                    <?php echo htmlspecialchars($team['team_name']); ?>
                                    <span class="block text-xs font-sans not-italic text-gray-500 mt-2 tracking-widest font-bold">
                                        <?php echo htmlspecialchars($team['full_team_name']); ?>
                                    </span>
                                </h3>
                                
                                <div class="grid grid-cols-2 gap-4 mb-8">
                                    <div class="p-3 bg-black/30 rounded-lg border border-white/5">
                                        <span class="block text-[8px] text-f1-red font-black uppercase tracking-widest mb-1">HQ Base</span>
                                        <span class="text-xs font-bold text-gray-200"><?php echo htmlspecialchars($team['base_location'] ?? 'N/A'); ?></span>
                                    </div>
                                    <div class="p-3 bg-black/30 rounded-lg border border-white/5">
                                        <span class="block text-[8px] text-f1-red font-black uppercase tracking-widest mb-1">Principal</span>
                                        <span class="text-xs font-bold text-gray-200"><?php echo htmlspecialchars($team['team_principal']); ?></span>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between pt-4 border-t border-white/5">
                                    <span class="text-[10px] font-black uppercase tracking-[0.3em] text-gray-500 group-hover:text-white transition-colors">Technical Profile</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-f1-red translate-x-0 group-hover:translate-x-2 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                    </svg>
                                </div>
                            </a>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
        
        <section class="relative" data-aos="fade-up">
            <div class="elite-card bg-f1-card/30 backdrop-blur-xl p-8 md:p-12 rounded-[3rem] border border-white/10 overflow-hidden">
                <div class="absolute top-0 right-0 text-[12rem] font-black text-white/[0.02] -translate-y-1/2 translate-x-1/4 select-none italic">ARCHIVE</div>

                <div class="relative z-10 flex flex-col lg:flex-row justify-between items-end mb-12 gap-8">
                    <div>
                        <h2 class="text-4xl font-oswald font-black text-white uppercase italic tracking-tighter">THE <span class="text-f1-red">ARCHIVES</span></h2>
                        <p class="text-gray-500 text-[10px] font-black uppercase tracking-[0.4em] mt-2">Historic teams & defunct constructors</p>
                    </div>

                    <div class="relative w-full max-w-md group">
                        <input type="text" id="team-filter" placeholder="SEARCH FOR A LEGACY..." 
                               class="w-full bg-black/50 border border-white/10 text-white px-8 py-4 rounded-xl font-bold focus:border-f1-red outline-none transition-all placeholder:text-gray-700 italic text-sm">
                        <div class="absolute right-6 top-1/2 -translate-y-1/2 text-f1-red group-focus-within:animate-bounce pointer-events-none">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 max-h-[600px] overflow-y-auto pr-4 history-grid" id="history-team-row">
                    <div id="loading-message" class="col-span-full py-20 text-center">
                        <div class="inline-block w-8 h-8 border-4 border-f1-red border-t-transparent rounded-full animate-spin mb-4"></div>
                        <p class="text-gray-500 font-black uppercase tracking-widest text-xs italic">Syncing technical data...</p>
                    </div>
                    <p id="no-results-message" class="text-f1-red col-span-full py-20 text-center font-black uppercase tracking-[0.5em]" style="display: none;">D.N.F - NO RESULTS FOUND</p>
                </div>
            </div>
        </section>
    </main>

    <?php include 'navigatie/footer.php'; ?>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        AOS.init({ duration: 800, once: true, easing: 'ease-out-quad' });
        
        const filterInput = document.getElementById('team-filter');
        const dataCardRow = document.getElementById('history-team-row');
        const noResultsMessage = document.getElementById('no-results-message');
        const loadingMessage = document.getElementById('loading-message');

        const renderTeamCards = (teams) => {
            dataCardRow.innerHTML = '';
            if (teams && teams.length > 0) {
                teams.forEach(team => {
                    const teamColor = team.team_color || '#333';
                    const cardHtml = `
                        <article class="bg-black/40 p-4 rounded-xl border border-white/5 transition-all hover:border-white/20 hover:bg-white/5 group">
                            <a href="team-details.php?id=${team.id}" class="block">
                                <div class="w-full h-1 mb-3 rounded-full opacity-30 group-hover:opacity-100 transition-opacity" style="background-color: ${teamColor}"></div>
                                <h3 class="text-gray-200 group-hover:text-white font-bold uppercase italic text-[11px] mb-1 tracking-tight truncate">${team.fullName}</h3>
                                <p class="text-[9px] text-gray-600 font-black uppercase tracking-widest">${team.countryId || 'World'}</p>
                            </a>
                        </article>
                    `;
                    dataCardRow.insertAdjacentHTML('beforeend', cardHtml);
                });
                noResultsMessage.style.display = 'none';
            } else {
                noResultsMessage.style.display = 'block';
            }
        };

        fetch('achterkant/aanpassing/api-koppelingen/json/teams.json')
            .then(response => response.json())
            .then(data => {
                loadingMessage.style.display = 'none';
                renderTeamCards(data);
                filterInput.addEventListener('input', (e) => {
                    const text = e.target.value.toLowerCase().trim();
                    const filtered = data.filter(t => 
                        (t.fullName || '').toLowerCase().includes(text) || 
                        (t.countryId || '').toLowerCase().includes(text)
                    );
                    renderTeamCards(filtered);
                });
            })
            .catch(() => {
                loadingMessage.innerHTML = '<p class="text-f1-red uppercase font-black">Data error in the pits.</p>';
            });
    });
    </script>
</body>
</html>
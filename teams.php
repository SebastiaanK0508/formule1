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
    <meta name="description" content="Overzicht van alle huidige Formule 1 teams en historische renstallen." />
    <?php include 'navigatie/head.php'; ?>
    <style>
        .f1-border { position: relative; }
        .f1-border::before { content: ""; position: absolute; top: 0; left: 0; width: 45px; height: 4px; background: #E10600; z-index: 10; }
        .team-card { transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
        .team-card:hover { transform: translateY(-10px); }
    </style>
</head>
<body class="bg-pattern">
    <?php include 'navigatie/header.php'; ?>
    <main class="max-w-7xl mx-auto px-6 py-12">
        
        <section class="mb-16" data-aos="fade-down">
            <div class="flex items-center justify-between mb-8">
                <h2 class="text-4xl md:text-5xl font-oswald font-black uppercase italic tracking-tighter">
                    CURRENT <span class="text-f1-red">TEAMS</span>
                </h2>
                <div class="hidden md:block h-[1px] flex-grow mx-10 bg-white/10"></div>
                <span class="text-f1-red font-black text-xs uppercase tracking-[0.3em] italic"><?php echo date('Y'); ?> Grid</span>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php if (!empty($activeTeams)): ?>
                    <?php $i=0; foreach ($activeTeams as $team): ?>
                        <?php $teamColor = htmlspecialchars($team['team_color'] ?? '#E10600'); ?>
                        <article data-aos="fade-up" data-aos-delay="<?php echo $i*50; ?>" 
                                 class="team-card f1-border bg-f1-card rounded-br-3xl border-r border-b border-white/5 overflow-hidden relative group">
                            
                            <div class="absolute -right-20 -top-20 w-40 h-40 rounded-full blur-[80px] opacity-20 transition-all group-hover:opacity-40" style="background-color: <?php echo $teamColor; ?>;"></div>

                            <a href="team-details.php?id=<?php echo htmlspecialchars($team['team_id']); ?>" class="block p-8">
                                <div class="flex flex-col h-full">
                                    <h3 class="text-2xl font-oswald font-black uppercase italic mb-6 leading-tight group-hover:text-white transition-colors" style="color: <?php echo $teamColor; ?>;">
                                        <?php echo htmlspecialchars($team['full_team_name']); ?>    
                                    </h3>
                                    
                                    <div class="space-y-4 mb-8">
                                        <div class="flex items-center justify-between text-xs border-b border-white/5 pb-2">
                                            <span class="text-gray-500 font-bold uppercase tracking-widest">Base</span>
                                            <span class="text-gray-200 font-medium"><?php echo htmlspecialchars($team['base_location'] ?? 'N/A'); ?></span>
                                        </div>
                                        <div class="flex items-center justify-between text-xs border-b border-white/5 pb-2">
                                            <span class="text-gray-500 font-bold uppercase tracking-widest">Principal</span>
                                            <span class="text-gray-200 font-medium"><?php echo htmlspecialchars($team['team_principal']); ?></span>
                                        </div>
                                    </div>

                                    <div class="mt-auto flex justify-between items-center">
                                        <span class="text-[10px] font-black uppercase tracking-widest text-gray-400">View Profile</span>
                                        <div class="w-8 h-[2px] bg-white/20 transition-all group-hover:w-12 group-hover:bg-f1-red"></div>
                                    </div>
                                </div>
                            </a>
                        </article>
                    <?php $i++; endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
        
        <section class="mt-24 pt-16 border-t border-white/5">
            <div class="bg-f1-card/50 backdrop-blur-md p-8 md:p-12 rounded-[2.5rem] border border-white/5">
                <div class="flex flex-col lg:flex-row justify-between items-center mb-12 gap-8">
                    <div>
                        <h2 class="text-3xl font-oswald font-black text-white uppercase italic italic tracking-tighter">ALL TEAMS <span class="text-f1-red">HISTORY</span></h2>
                        <p class="text-gray-500 text-[10px] font-black uppercase tracking-[0.3em] mt-2">The complete Formula 1 archive</p>
                    </div>

                    <div class="relative w-full max-w-md">
                        <input type="text" id="team-filter" placeholder="Filter by Name or Country..." 
                               class="w-full bg-f1-dark border border-white/10 text-white px-6 py-4 rounded-full font-bold focus:border-f1-red outline-none transition-all placeholder:text-gray-600 italic">
                        <div class="absolute right-6 top-1/2 -translate-y-1/2 text-f1-red opacity-50">🔍</div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4" id="history-team-row">
                    <p id="loading-message" class="text-gray-500 col-span-full p-12 text-center font-bold uppercase tracking-widest italic animate-pulse">Loading data pitstop...</p>
                    <p id="no-results-message" class="text-f1-red col-span-full p-12 text-center font-bold uppercase tracking-widest" style="display: none;">No teams found on this line.</p>
                </div>
            </div>
        </section>
    </main>
    <?php include 'navigatie/footer.php'; ?>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        AOS.init({ duration: 1000, once: true });
        
        window.toggleMenu = () => { document.getElementById('mobile-menu').classList.toggle('active'); };

        const filterInput = document.getElementById('team-filter');
        const dataCardRow = document.getElementById('history-team-row');
        const noResultsMessage = document.getElementById('no-results-message');
        const loadingMessage = document.getElementById('loading-message');
        let allHistoricalTeams = [];

        const renderTeamCards = (teams) => {
            dataCardRow.innerHTML = '';
            if (teams && teams.length > 0) {
                teams.forEach(team => {
                    const teamColor = team.team_color || '#333';
                    const cardHtml = `
                        <article class="bg-f1-dark/50 p-5 rounded-xl border border-white/5 transition-all hover:border-f1-red/30 hover:bg-f1-dark">
                            <a href="team-details.php?id=${team.id}" class="block">
                                <h3 class="text-white font-bold uppercase italic text-sm mb-1">${team.fullName}</h3>
                                <div class="flex items-center gap-2">
                                    <div class="w-2 h-2 rounded-full" style="background-color: ${teamColor}"></div>
                                    <p class="text-[10px] text-gray-500 font-black uppercase tracking-widest">${team.countryId || 'N/A'}</p>
                                </div>
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
                allHistoricalTeams = data;
                loadingMessage.style.display = 'none';
                renderTeamCards(allHistoricalTeams);
                filterInput.addEventListener('keyup', () => {
                    const text = filterInput.value.toLowerCase().trim();
                    const filtered = allHistoricalTeams.filter(t => 
                        (t.fullName || '').toLowerCase().includes(text) || 
                        (t.countryId || '').toLowerCase().includes(text)
                    );
                    renderTeamCards(filtered);
                });
            })
            .catch(err => {
                loadingMessage.textContent = 'Data error in the pits.';
                loadingMessage.style.color = '#E10600';
            });
    });
    </script>
</body>
</html>
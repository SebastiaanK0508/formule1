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
        .f1-border { position: relative; }
        .f1-border::before { content: ""; position: absolute; top: 0; left: 0; width: 45px; height: 4px; background: #E10600; z-index: 10; }
        
        #mobile-menu { transform: translateX(100%); transition: transform 0.4s ease-in-out; background: #0b0b0f; z-index: 101; }
        #mobile-menu.active { transform: translateX(0); }

        .team-card { transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
        .team-card:hover { transform: translateY(-10px); }
    </style>
</head>
<body class="bg-pattern">

    <div id="mobile-menu" class="fixed inset-y-0 right-0 w-full p-10 flex flex-col items-center justify-center">
        <button onclick="toggleMenu()" class="absolute top-8 right-8 text-5xl font-light">&times;</button>
        <nav class="flex flex-col space-y-10 text-4xl font-oswald font-black uppercase italic text-center">
            <a href="index.php" onclick="toggleMenu()">Home</a>
            <a href="kalender.php" onclick="toggleMenu()">Schedule</a>
            <a href="teams.php" class="text-f1-red" onclick="toggleMenu()">Teams</a>
            <a href="drivers.php" onclick="toggleMenu()">Drivers</a>
            <a href="results.php" onclick="toggleMenu()">Results</a>
            <a href="standings.php" onclick="toggleMenu()">Standings</a>
        </nav>
    </div>

    <header class="header-glass sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-6 py-5 flex justify-between items-center">
            <a href="index.php" class="text-3xl font-oswald font-black italic tracking-tighter text-white uppercase">F1SITE<span class="text-f1-red">.NL</span></a>
            <nav class="hidden lg:flex space-x-10 text-[11px] font-bold uppercase tracking-[0.25em]">
                <a href="index.php" class="hover:text-f1-red transition">Home</a>
                <a href="kalender.php" class="hover:text-f1-red transition">Schedule</a>
                <a href="teams.php" class="text-f1-red border-b-2 border-f1-red pb-1">Teams</a>
                <a href="drivers.php" class="hover:text-f1-red transition">Drivers</a>
                <a href="results.php" class="hover:text-f1-red transition">Results</a>
                <a href="standings.php" class="hover:text-f1-red transition">Standings</a>
            </nav>
            <button onclick="toggleMenu()" class="lg:hidden text-white text-3xl">☰</button>
        </div>
    </header>

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
    
    <footer class="bg-black mt-24 py-16 border-t-2 border-f1-red">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-12 text-left pb-12 border-b border-white/5">
                <div class="space-y-4 text-center md:text-left">
                    <h3 class="text-2xl font-oswald font-black text-white italic tracking-tighter uppercase">F1SITE<span class="text-f1-red">.NL</span></h3>
                    <p class="text-gray-500 text-sm font-medium leading-relaxed max-w-xs mx-auto md:mx-0">De snelste bron voor het laatste Formule 1 nieuws en statistieken.</p>
                </div>

                <div class="text-center md:text-left">
                    <h4 class="text-xs font-black text-f1-red mb-6 uppercase tracking-[0.3em]">Ontwikkelaar</h4>
                    <ul class="space-y-4">
                        <li><a href="https://www.webius.nl" target="_blank" class="text-gray-400 text-sm font-bold hover:text-white transition duration-200 block uppercase tracking-wider">Webius</a></li>
                    </ul>
                </div>

                <div class="text-center md:text-left">
                    <h4 class="text-xs font-black text-f1-red mb-6 uppercase tracking-[0.3em]">Navigatie & Info</h4>
                    <ul class="space-y-4">
                        <li><a href="sitemap.html" class="text-gray-400 text-sm font-bold hover:text-white transition duration-200 block uppercase tracking-wider">Sitemap</a></li>
                        <li><a href="privacy-en.html" class="text-gray-400 text-sm font-bold hover:text-white transition duration-200 block uppercase tracking-wider">Privacy Policy</a></li>
                        <li><a href="contact.html" class="text-gray-400 text-sm font-bold hover:text-white transition duration-200 block uppercase tracking-wider">Contact</a></li>
                    </ul>
                </div>
            </div>
            <div class="pt-10 text-center md:text-left">
                <p class="text-gray-600 text-[10px] font-black uppercase tracking-[0.5em] italic">&copy; 2026 WEBIUS. Alle rechten voorbehouden.</p>
            </div>
        </div>
    </footer>

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
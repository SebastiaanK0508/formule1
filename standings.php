<!DOCTYPE html>
<html lang="nl" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>F1 Standings <?php echo date('Y'); ?> | F1SITE.NL</title>
    <meta name="description" content="De actuele stand in het wereldkampioenschap Formule 1 voor coureurs en constructeurs." />
    
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

        /* Table custom styling */
        .standing-row { transition: all 0.2s ease; border-bottom: 1px solid rgba(255,255,255,0.03); }
        .standing-row:hover { background: rgba(225, 6, 0, 0.05); }
        .standing-row:last-child { border-bottom: none; }
    </style>
</head>
<body class="bg-pattern">

    <div id="mobile-menu" class="fixed inset-y-0 right-0 w-full p-10 flex flex-col items-center justify-center">
        <button onclick="toggleMenu()" class="absolute top-8 right-8 text-5xl font-light">&times;</button>
        <nav class="flex flex-col space-y-10 text-4xl font-oswald font-black uppercase italic text-center">
            <a href="index.php" onclick="toggleMenu()">Home</a>
            <a href="kalender.php" onclick="toggleMenu()">Schedule</a>
            <a href="teams.php" onclick="toggleMenu()">Teams</a>
            <a href="drivers.php" onclick="toggleMenu()">Drivers</a>
            <a href="results.php" onclick="toggleMenu()">Results</a>
            <a href="standings.php" class="text-f1-red" onclick="toggleMenu()">Standings</a>
        </nav>
    </div>

    <header class="header-glass sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-6 py-5 flex justify-between items-center">
            <a href="index.php" class="text-3xl font-oswald font-black italic tracking-tighter text-white uppercase">F1SITE<span class="text-f1-red">.NL</span></a>
            <nav class="hidden lg:flex space-x-10 text-[11px] font-bold uppercase tracking-[0.25em]">
                <a href="index.php" class="hover:text-f1-red transition">Home</a>
                <a href="kalender.php" class="hover:text-f1-red transition">Schedule</a>
                <a href="teams.php" class="hover:text-f1-red transition">Teams</a>
                <a href="drivers.php" class="hover:text-f1-red transition">Drivers</a>
                <a href="results.php" class="hover:text-f1-red transition">Results</a>
                <a href="standings.php" class="text-f1-red border-b-2 border-f1-red pb-1">Standings</a>
            </nav>
            <button onclick="toggleMenu()" class="lg:hidden text-white text-3xl">☰</button>
        </div>
    </header>

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

    </main>

    <footer class="bg-black mt-24 py-16 border-t-2 border-f1-red">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-12 text-left pb-12 border-b border-white/5">
                <div class="space-y-4 text-center md:text-left">
                    <h3 class="text-2xl font-oswald font-black text-white italic tracking-tighter uppercase">F1SITE<span class="text-f1-red">.NL</span></h3>
                    <p class="text-gray-500 text-sm font-medium leading-relaxed max-w-xs mx-auto md:mx-0">De snelste bron voor Formule 1 nieuws, statistieken en live standen.</p>
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
        const standingsContent = document.getElementById('standings-content');
        
        async function fetchChampionshipStandings() {
            try {
                // Hier roepen we de API aan
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
            
            // --- DRIVERS TABLE ---
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

            // --- CONSTRUCTORS TABLE ---
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

        // Initialize
        document.addEventListener('DOMContentLoaded', () => {
            AOS.init({ duration: 800, once: true });
            fetchChampionshipStandings();
            
            window.toggleMenu = () => {
                document.getElementById('mobile-menu').classList.toggle('active');
            };
        });
    </script>
</body>
</html>
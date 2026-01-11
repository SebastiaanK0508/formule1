<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sitemap | F1SITE.NL</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;700;900&family=Oswald:wght@700&display=swap" rel="stylesheet">
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
        body { background-color: #0b0b0f; color: #fff; }
        .bg-pattern {
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.02'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
        .header-glass { background: rgba(11, 11, 15, 0.9); backdrop-filter: blur(15px); border-bottom: 1px solid rgba(225, 6, 0, 0.3); }
        .sitemap-link { @apply text-gray-400 hover:text-f1-red transition-all duration-200 block py-1 text-sm; }
        .section-card { @apply bg-f1-card/30 p-8 rounded-[2rem] border border-white/5 hover:border-f1-red/20 transition-all duration-500; }
    </style>
</head>
<body class="bg-pattern min-h-screen flex flex-col">

    <div id="mobile-menu" class="fixed inset-y-0 right-0 w-full p-10 flex flex-col items-center justify-center">
        <button onclick="toggleMenu()" class="absolute top-8 right-8 text-5xl font-light">&times;</button>
        <nav class="flex flex-col space-y-10 text-4xl font-oswald font-black uppercase italic text-center">
            <a href="index.php" onclick="toggleMenu()">Home</a>
            <a href="kalender.php" onclick="toggleMenu()">Schedule</a>
            <a href="teams.php" onclick="toggleMenu()">Teams</a>
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
                <a href="teams.php" class="hover:text-f1-red transition">Teams</a>
                <a href="drivers.php" class="hover:text-f1-red transition">Drivers</a>
                <a href="results.php" class="hover:text-f1-red transition">Results</a>
                <a href="standings.php" class="hover:text-f1-red transition">Standings</a>
            </nav>
            <button onclick="toggleMenu()" class="lg:hidden text-white text-3xl">☰</button>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-6 py-16 flex-grow">
        
        <div class="mb-16 text-center md:text-left">
            <h1 class="text-6xl md:text-8xl font-oswald font-black uppercase italic tracking-tighter leading-none mb-4">
                SITE<span class="text-f1-red">MAP</span>
            </h1>
            <p class="text-gray-500 font-bold uppercase tracking-[0.4em] text-xs">Volledig index overzicht van het 2025/2026 seizoen</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">

            <section class="section-card">
                <h3 class="text-2xl font-oswald font-black uppercase italic text-f1-red mb-6 border-b border-white/10 pb-2">Basic & Info</h3>
                <ul class="space-y-1">
                    <li><a href="https://f1site.nl/" class="sitemap-link font-bold text-gray-200">Home Pagina</a></li>
                    <li><a href="https://f1site.nl/kalender.php" class="sitemap-link">Kalender</a></li>
                    <li><a href="https://f1site.nl/teams.php" class="sitemap-link">Teams Overzicht</a></li>
                    <li><a href="https://f1site.nl/drivers.php" class="sitemap-link">Coureurs Overzicht</a></li>
                    <li><a href="https://f1site.nl/results.php" class="sitemap-link">Race Uitslagen</a></li>
                    <li><a href="https://f1site.nl/standings.php" class="sitemap-link">Klassementen</a></li>
                    <li class="pt-4"><a href="https://f1site.nl/privacy.html" class="sitemap-link text-xs italic">Privacybeleid (NL)</a></li>
                    <li><a href="https://f1site.nl/algemenevoorwaarden.html" class="sitemap-link text-xs italic">Algemene Voorwaarden (NL)</a></li>
                    <li><a href="https://f1site.nl/contact.html" class="sitemap-link text-xs italic">Contact</a></li>
                </ul>
            </section>

            <section class="section-card">
                <h3 class="text-2xl font-oswald font-black uppercase italic text-f1-red mb-6 border-b border-white/10 pb-2">Circuits</h3>
                <ul class="grid grid-cols-2 gap-x-4">
                    <li><a href="https://f1site.nl/circuit-details.php?key=australia" class="sitemap-link">Australië</a></li>
                    <li><a href="https://f1site.nl/circuit-details.php?key=china" class="sitemap-link">China</a></li>
                    <li><a href="https://f1site.nl/circuit-details.php?key=japan" class="sitemap-link">Japan</a></li>
                    <li><a href="https://f1site.nl/circuit-details.php?key=bahrain" class="sitemap-link">Bahrein</a></li>
                    <li><a href="https://f1site.nl/circuit-details.php?key=saudi_arabia" class="sitemap-link">Saudi-Arabië</a></li>
                    <li><a href="https://f1site.nl/circuit-details.php?key=miami" class="sitemap-link">Miami</a></li>
                    <li><a href="https://f1site.nl/circuit-details.php?key=emilia_romagna" class="sitemap-link">Emilia-Romagna</a></li>
                    <li><a href="https://f1site.nl/circuit-details.php?key=monaco" class="sitemap-link">Monaco</a></li>
                    <li><a href="https://f1site.nl/circuit-details.php?key=spain" class="sitemap-link">Spanje</a></li>
                    <li><a href="https://f1site.nl/circuit-details.php?key=canada" class="sitemap-link">Canada</a></li>
                    <li><a href="https://f1site.nl/circuit-details.php?key=austria" class="sitemap-link">Oostenrijk</a></li>
                    <li><a href="https://f1site.nl/circuit-details.php?key=great_britain" class="sitemap-link">Groot-Brittannië</a></li>
                    <li><a href="https://f1site.nl/circuit-details.php?key=belgium" class="sitemap-link">België</a></li>
                    <li><a href="https://f1site.nl/circuit-details.php?key=hungary" class="sitemap-link">Hongarije</a></li>
                    <li><a href="https://f1site.nl/circuit-details.php?key=netherlands" class="sitemap-link">Nederland</a></li>
                    <li><a href="https://f1site.nl/circuit-details.php?key=italy" class="sitemap-link">Italië</a></li>
                    <li><a href="https://f1site.nl/circuit-details.php?key=azerbaijan" class="sitemap-link">Azerbeidzjan</a></li>
                    <li><a href="https://f1site.nl/circuit-details.php?key=singapore" class="sitemap-link">Singapore</a></li>
                    <li><a href="https://f1site.nl/circuit-details.php?key=usa" class="sitemap-link">USA</a></li>
                    <li><a href="https://f1site.nl/circuit-details.php?key=mexico" class="sitemap-link">Mexico</a></li>
                    <li><a href="https://f1site.nl/circuit-details.php?key=brazil" class="sitemap-link">Brazilië</a></li>
                    <li><a href="https://f1site.nl/circuit-details.php?key=las_vegas" class="sitemap-link">Las Vegas</a></li>
                    <li><a href="https://f1site.nl/circuit-details.php?key=qatar" class="sitemap-link">Qatar</a></li>
                    <li><a href="https://f1site.nl/circuit-details.php?key=abu_dhabi" class="sitemap-link">Abu Dhabi</a></li>
                </ul>
            </section>

            <section class="section-card">
                <h3 class="text-2xl font-oswald font-black uppercase italic text-f1-red mb-6 border-b border-white/10 pb-2">Coureurs</h3>
                <ul class="grid grid-cols-1 sm:grid-cols-2 gap-x-4">
                    <li><a href="https://f1site.nl/driver-details.php?slug=max-verstappen" class="sitemap-link font-bold">Max Verstappen</a></li>
                    <li><a href="https://f1site.nl/driver-details.php?slug=charles-leclerc" class="sitemap-link">Charles Leclerc</a></li>
                    <li><a href="https://f1site.nl/driver-details.php?slug=lewis-hamilton" class="sitemap-link">Lewis Hamilton</a></li>
                    <li><a href="https://f1site.nl/driver-details.php?slug=carlos-sainz" class="sitemap-link">Carlos Sainz</a></li>
                    <li><a href="https://f1site.nl/driver-details.php?slug=george-russell" class="sitemap-link">George Russell</a></li>
                    <li><a href="https://f1site.nl/driver-details.php?slug=lando-norris" class="sitemap-link">Lando Norris</a></li>
                    <li><a href="https://f1site.nl/driver-details.php?slug=oscar-piastri" class="sitemap-link">Oscar Piastri</a></li>
                    <li><a href="https://f1site.nl/driver-details.php?slug=fernando-alonso" class="sitemap-link">Fernando Alonso</a></li>
                    <li><a href="https://f1site.nl/driver-details.php?slug=lance-stroll" class="sitemap-link">Lance Stroll</a></li>
                    <li><a href="https://f1site.nl/driver-details.php?slug=alexander-albon" class="sitemap-link">Alexander Albon</a></li>
                    <li><a href="https://f1site.nl/driver-details.php?slug=yuki-tsunoda" class="sitemap-link">Yuki Tsunoda</a></li>
                    <li><a href="https://f1site.nl/driver-details.php?slug=nico-hulkenberg" class="sitemap-link">Nico Hülkenberg</a></li>
                    <li><a href="https://f1site.nl/driver-details.php?slug=liam-lawson" class="sitemap-link">Liam Lawson</a></li>
                    <li><a href="https://f1site.nl/driver-details.php?slug=pierre-gasly" class="sitemap-link">Pierre Gasly</a></li>
                    <li><a href="https://f1site.nl/driver-details.php?slug=esteban-ocon" class="sitemap-link">Esteban Ocon</a></li>
                    <li><a href="https://f1site.nl/driver-details.php?slug=kimi-antonelli" class="sitemap-link">Kimi Antonelli</a></li>
                    <li><a href="https://f1site.nl/driver-details.php?slug=oliver-bearman" class="sitemap-link">Oliver Bearman</a></li>
                    <li><a href="https://f1site.nl/driver-details.php?slug=gabriel-bortoleto" class="sitemap-link">Gabriel Bortoleto</a></li>
                    <li><a href="https://f1site.nl/driver-details.php?slug=isack-hadjar" class="sitemap-link">Isack Hadjar</a></li>
                    <li><a href="https://f1site.nl/driver-details.php?slug=franco-colapinto" class="sitemap-link">Franco Colapinto</a></li>
                    <li><a href="https://f1site.nl/driver-details.php?slug=jack-doohan" class="sitemap-link">Jack Doohan</a></li>
                </ul>
            </section>

            <section class="section-card">
                <h3 class="text-2xl font-oswald font-black uppercase italic text-f1-red mb-6 border-b border-white/10 pb-2">Teams</h3>
                <ul class="space-y-1">
                    <li><a href="https://f1site.nl/team-details.php?id=1" class="sitemap-link">Red Bull Racing</a></li>
                    <li><a href="https://f1site.nl/team-details.php?id=2" class="sitemap-link">Mercedes-AMG F1</a></li>
                    <li><a href="https://f1site.nl/team-details.php?id=3" class="sitemap-link">Scuderia Ferrari</a></li>
                    <li><a href="https://f1site.nl/team-details.php?id=4" class="sitemap-link">McLaren F1 Team</a></li>
                    <li><a href="https://f1site.nl/team-details.php?id=5" class="sitemap-link">Aston Martin Aramco F1 Team</a></li>
                    <li><a href="https://f1site.nl/team-details.php?id=6" class="sitemap-link">BWT Alpine F1 Team</a></li>
                    <li><a href="https://f1site.nl/team-details.php?id=7" class="sitemap-link">Williams Racing</a></li>
                    <li><a href="https://f1site.nl/team-details.php?id=8" class="sitemap-link">Racing Bulls</a></li>
                    <li><a href="https://f1site.nl/team-details.php?id=9" class="sitemap-link">Stake F1 Team Kick Sauber</a></li>
                    <li><a href="https://f1site.nl/team-details.php?id=10" class="sitemap-link">MoneyGram Haas F1 Team</a></li>
                </ul>
            </section>

            <section class="section-card md:col-span-2">
                <h3 class="text-2xl font-oswald font-black uppercase italic text-f1-red mb-6 border-b border-white/10 pb-2">Race Uitslagen 2025</h3>
                <div class="grid grid-cols-4 sm:grid-cols-6 md:grid-cols-8 gap-3">
                    <?php for($i=1; $i<=24; $i++): ?>
                        <a href="https://f1site.nl/results.php?year=2025&round=<?php echo $i; ?>" 
                           class="bg-white/5 py-3 rounded-xl hover:bg-f1-red text-center text-[10px] font-black transition-all duration-300 border border-white/5">
                            R<?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
            </section>
        </div>
    </main>

    <footer class="bg-black py-12 mt-20 border-t-2 border-f1-red">
        <div class="max-w-7xl mx-auto px-6 text-center">
            <h3 class="text-2xl font-oswald font-black text-white italic tracking-tighter uppercase mb-4">F1SITE<span class="text-f1-red">.NL</span></h3>
            <p class="text-gray-600 text-[10px] font-black uppercase tracking-[0.5em]">&copy; 2026 Webbair. Alle rechten voorbehouden.</p>
        </div>
    </footer>

</body>
</html>
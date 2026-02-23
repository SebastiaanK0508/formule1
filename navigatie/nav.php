<div id="mobile-menu" class="p-10 flex flex-col items-center justify-center">
        <button onclick="toggleMenu()" class="absolute top-8 right-8 text-5xl font-light text-white hover:text-f1-red transition">&times;</button>
        <nav class="flex flex-col space-y-10 text-4xl font-oswald font-black uppercase italic text-center">
            <a href="index.php" class="text-f1-red" onclick="toggleMenu()">Home</a>
            <a href="kalender.php" onclick="toggleMenu()">Schedule</a>
            <a href="teams.php" onclick="toggleMenu()">Teams</a>
            <a href="drivers.php" onclick="toggleMenu()">Drivers</a>
            <a href="results.php" onclick="toggleMenu()">Results</a>
            <a href="standings.php" onclick="toggleMenu()">Standings</a>
        </nav>
    </div>

    <header class="header-glass sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-6 py-5 flex justify-between items-center">
            <a href="index.php" class="flex items-baseline gap-1" aria-label="F1SITE.NL Home">
                <span class="text-3xl font-oswald font-black italic tracking-tighter text-white uppercase">F1SITE<span class="text-f1-red">.NL</span></span>
            </a>
            <nav class="hidden lg:flex space-x-10 text-[11px] font-bold uppercase tracking-[0.25em]">
                <a href="index.php" class="text-f1-red border-b-2 border-f1-red pb-1">Home</a>
                <a href="kalender.php" class="hover:text-f1-red transition">Schedule</a>
                <a href="teams.php" class="hover:text-f1-red transition">Teams</a>
                <a href="drivers.php" class="hover:text-f1-red transition">Drivers</a>
                <a href="results.php" class="hover:text-f1-red transition">Results</a>
                <a href="standings.php" class="hover:text-f1-red transition">Standings</a>
            </nav>
            <button onclick="toggleMenu()" class="lg:hidden text-white text-3xl" aria-label="Menu openen">☰</button>
        </div>
    </header>
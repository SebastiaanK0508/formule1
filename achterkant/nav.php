<?php
$server = $_SERVER['SERVER_NAME'];
$requestUri = $_SERVER['REQUEST_URI'];

if ($server === 'localhost' || $server === '127.0.0.1') {
    $baseUrl = "http://localhost:8080/formule1/achterkant/";
} else {
    $baseUrl = "https://achterkant.f1site.nl/";
}
?>
<base href="<?php echo htmlspecialchars($baseUrl); ?>">
<aside class="hidden lg:flex flex-col w-72 bg-f1-dark border-r border-white/5 p-6 sticky top-0 h-screen">
    <div class="mb-12 px-4">
        <h1 class="text-3xl font-oswald font-black italic tracking-tighter">F1SITE<span class="text-f1-red">.NL</span></h1>
        <span class="text-[10px] font-black text-gray-500 tracking-[0.3em] uppercase">Control Center</span>
    </div>

    <nav class="flex-grow space-y-2">
        <a href="dashboard.php" class="sidebar-link flex items-center gap-4 px-4 py-3 rounded-lg text-f1-red bg-white/5 font-bold">
            <span>📊</span> Dashboard
        </a>
        <a href="bewerken/nieuws.php" class="sidebar-link flex items-center gap-4 px-4 py-3 rounded-lg text-gray-400 hover:text-white transition">
            <span>📰</span> Nieuws Beheer
        </a>
        <a href="bewerken/circuits.php" class="sidebar-link flex items-center gap-4 px-4 py-3 rounded-lg text-gray-400 hover:text-white transition">
            <span>🏁</span> Circuits & Kalender
        </a>
        <a href="bewerken/teams.php" class="sidebar-link flex items-center gap-4 px-4 py-3 rounded-lg text-gray-400 hover:text-white transition">
            <span>🏎️</span> Formule 1 Teams
        </a>
        <a href="bewerken/drivers.php" class="sidebar-link flex items-center gap-4 px-4 py-3 rounded-lg text-gray-400 hover:text-white transition">
            <span>⛑️</span> Formule 1 Courreurs
        </a>
        </a>
    </nav>

    <div class="mt-auto pt-6 border-t border-white/5">
        <div class="flex items-center gap-3 px-4 mb-4">
            <div class="w-10 h-10 rounded-full bg-f1-red flex items-center justify-center font-bold">SB</div>
            <div>
                <p class="text-xs font-bold"><?php echo $_SESSION['admin_name']; ?></p>
                <p class="text-[10px] text-gray-500 uppercase">Chief Engineer</p>
            </div>
        </div>
        <a href="logout.php" class="block text-center py-3 rounded-xl border border-white/10 text-xs font-bold hover:bg-red-600 transition">LOGOUT</a>
    </div>
</aside>
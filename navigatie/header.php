<?php
$current_page = basename($_SERVER['PHP_SELF']);

function nav_class($pageName, $current_page, $is_mobile = false) {
    $active = ($pageName === $current_page);
    
    if ($is_mobile) {
        return $active ? 'text-f1-red' : 'text-white hover:text-f1-red transition';
    }
    
    return $active 
        ? 'text-f1-red border-b-2 border-f1-red pb-1' 
        : 'hover:text-f1-red transition pb-1 border-b-2 border-transparent';
}
$server = $_SERVER['SERVER_NAME'];
$requestUri = $_SERVER['REQUEST_URI'];

if ($server === 'localhost') {
    $baseUrl = (strpos($requestUri, '/~sebastiaanbaskamphuis') !== false)
        ? "http://localhost:8080/~sebastiaanbaskamphuis/formule1/"
        : "http://localhost/formule1/";
} else {
    $baseUrl = "https://f1site.nl/";
}
?>
<base href="<?php echo $baseUrl; ?>">
<div id="mobile-menu" class="fixed inset-0 bg-black/95 z-[60] p-10 flex flex-col items-center justify-center invisible opacity-0 transition-all duration-300">
    <button onclick="toggleMenu()" class="absolute top-8 right-8 text-5xl font-light text-white hover:text-f1-red transition">&times;</button>
    <nav class="flex flex-col space-y-10 text-4xl font-oswald font-black uppercase italic text-center">
        <a href="index.php" class="<?php echo nav_class('index.php', $current_page, true); ?>" onclick="toggleMenu()">Home</a>
        <a href="kalender.php" class="<?php echo nav_class('kalender.php', $current_page, true); ?>" onclick="toggleMenu()">Schedule</a>
        <a href="teams.php" class="<?php echo nav_class('teams.php', $current_page, true); ?>" onclick="toggleMenu()">Teams</a>
        <a href="drivers.php" class="<?php echo nav_class('drivers.php', $current_page, true); ?>" onclick="toggleMenu()">Drivers</a>
        <a href="results.php" class="<?php echo nav_class('results.php', $current_page, true); ?>" onclick="toggleMenu()">Results</a>
        <a href="standings.php" class="<?php echo nav_class('standings.php', $current_page, true); ?>" onclick="toggleMenu()">Standings</a>
    </nav>
</div>

<header class="header-glass sticky top-0 z-50 bg-black/80 backdrop-blur-md">
    <div class="max-w-7xl mx-auto px-6 py-5 flex justify-between items-center">
        <a href="index.php" class="flex items-baseline gap-1" aria-label="F1SITE.NL Home">
            <span class="text-3xl font-oswald font-black italic tracking-tighter text-white uppercase">F1SITE<span class="text-f1-red">.NL</span></span>
        </a>
        
        <nav class="hidden lg:flex space-x-10 text-[11px] font-bold uppercase tracking-[0.25em]">
            <a href="index.php" class="<?php echo nav_class('index.php', $current_page); ?>">Home</a>
            <a href="kalender.php" class="<?php echo nav_class('kalender.php', $current_page); ?>">Schedule</a>
            <a href="teams.php" class="<?php echo nav_class('teams.php', $current_page); ?>">Teams</a>
            <a href="drivers.php" class="<?php echo nav_class('drivers.php', $current_page); ?>">Drivers</a>
            <a href="results.php" class="<?php echo nav_class('results.php', $current_page); ?>">Results</a>
            <a href="standings.php" class="<?php echo nav_class('standings.php', $current_page); ?>">Standings</a>
        </nav>

        <button onclick="toggleMenu()" class="lg:hidden text-white text-3xl" aria-label="Menu openen">☰</button>
    </div>
</header>
<script>
    const toggleMenu = () => {
        const menu = document.getElementById('mobile-menu');
        menu.classList.toggle('invisible');
        menu.classList.toggle('opacity-0');
        document.body.classList.toggle('overflow-hidden');
    };
</script>
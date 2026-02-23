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

if ($server === 'localhost' || $server === '127.0.0.1') {
    $baseUrl = (strpos($requestUri, '/~sebastiaanbaskamphuis') !== false)
        ? "http://localhost:8080/~sebastiaanbaskamphuis/formule1/"
        : "http://localhost/formule1/";
} else {
    $baseUrl = "https://f1site.nl/";
}
?>
<style>
    #mobile-menu {
        transform: translateX(100%);
        transition: all 0.3s ease-in-out;
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
    }
    
    #mobile-menu.is-open {
        transform: translateX(0);
        visibility: visible !important;
        opacity: 1 !important;
        pointer-events: auto !important;
    }
    @media (max-height: 500px) and (orientation: landscape) {
        #mobile-menu nav {
            display: grid;
            grid-template-columns: 1fr 1fr; 
            gap: 1rem !important;
            padding-top: 2rem;
            space-y: 0 !important; 
        }
        
        #mobile-menu nav a {
            font-size: 1.5rem !important;
            margin-top: 0 !important;
        }

        #mobile-menu .close-btn-container {
            top: 1rem !important;
            right: 1rem !important;
        }
    }

    .menu-trigger { cursor: pointer; -webkit-tap-highlight-color: transparent; }
</style>
<base href="<?php echo $baseUrl; ?>">
<div id="mobile-menu" 
     class="fixed inset-0 bg-black/95 z-[9999] p-6 flex flex-col items-center justify-center invisible opacity-0 pointer-events-none">
    <button id="close-menu-btn" class="absolute top-8 right-8 text-5xl text-white p-4 leading-none z-[10000]">&times;</button>
    <nav class="flex flex-col space-y-6 text-4xl font-oswald font-black uppercase italic text-center text-white w-full max-w-2xl">
        <a href="index.php" class="mobile-link <?php echo nav_class('index.php', $current_page, true); ?>">Home</a>
        <a href="kalender.php" class="mobile-link <?php echo nav_class('kalender.php', $current_page, true); ?>">Schedule</a>
        <a href="teams.php" class="mobile-link <?php echo nav_class('teams.php', $current_page, true); ?>">Teams</a>
        <a href="drivers.php" class="mobile-link <?php echo nav_class('drivers.php', $current_page, true); ?>">Drivers</a>
        <a href="results.php" class="mobile-link <?php echo nav_class('results.php', $current_page, true); ?>">Results</a>
        <a href="standings.php" class="mobile-link <?php echo nav_class('standings.php', $current_page, true); ?>">Standings</a>
    </nav>
</div>

<header class="header-glass sticky top-0 z-[100] bg-black/80 backdrop-blur-md border-b border-white/10">
    <div class="max-w-7xl mx-auto px-6 py-5 flex justify-between items-center">
        
        <a href="index.php" class="flex items-baseline gap-1">
            <span class="text-3xl font-oswald font-black italic text-white uppercase">F1SITE<span class="text-f1-red">.NL</span></span>
        </a>
        
        <nav class="hidden lg:flex space-x-10 text-[11px] font-bold uppercase tracking-[0.25em] text-white">
            <a href="index.php" class="<?php echo nav_class('index.php', $current_page); ?>">Home</a>
            <a href="kalender.php" class="<?php echo nav_class('kalender.php', $current_page); ?>">Schedule</a>
            <a href="teams.php" class="<?php echo nav_class('teams.php', $current_page); ?>">Teams</a>
            <a href="drivers.php" class="<?php echo nav_class('drivers.php', $current_page); ?>">Drivers</a>
            <a href="results.php" class="<?php echo nav_class('results.php', $current_page); ?>">Results</a>
            <a href="standings.php" class="<?php echo nav_class('standings.php', $current_page); ?>">Standings</a>
        </nav>

        <div id="mobile-menu-btn" class="lg:hidden menu-trigger z-[110] p-4 -mr-4">
            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path id="menu-icon-path" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-16 6h16"></path>
            </svg>
        </div>
    </div>
</header>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const menuBtn = document.getElementById('mobile-menu-btn');
        const closeBtn = document.getElementById('close-menu-btn');
        const mobileMenu = document.getElementById('mobile-menu');
        const menuIconPath = document.getElementById('menu-icon-path');
        const mobileLinks = document.querySelectorAll('.mobile-link');
        
        let menuOpen = false;

        function toggleMenu() {
            menuOpen = !menuOpen;
            if (menuOpen) {
                mobileMenu.classList.add('is-open');
                menuIconPath.setAttribute('d', 'M6 18L18 6M6 6l12 12'); 
                document.body.style.overflow = 'hidden';
            } else {
                mobileMenu.classList.remove('is-open');
                menuIconPath.setAttribute('d', 'M4 6h16M4 12h16m-16 6h16'); 
                document.body.style.overflow = '';
            }
        }
        menuBtn.addEventListener('click', toggleMenu);
        closeBtn.addEventListener('click', toggleMenu);
        mobileLinks.forEach(link => {
            link.addEventListener('click', () => {
                if (menuOpen) toggleMenu();
            });
        });
    });
</script>
<?php
$current_page = basename($_SERVER['PHP_SELF']);

function nav_class($pageName, $current_page, $is_mobile = false) {
    $active = ($pageName === $current_page);
    if ($is_mobile) {
        return $active ? 'text-f1-red font-bold' : 'text-white/70 hover:text-white transition-all';
    }
    return $active 
        ? 'text-f1-red border-b-2 border-f1-red pb-1' 
        : 'hover:text-f1-red transition-all pb-1 border-b-2 border-transparent';
}

$links = [
    'index.php' => 'Home',
    'kalender.php' => 'Schedule',
    'teams.php' => 'Teams',
    'drivers.php' => 'Drivers',
    'results.php' => 'Results',
    'standings.php' => 'Standings'
];
?>

<style>
    #mobile-menu {
        position: fixed;
        inset: 0;
        z-index: 9999;
        background: rgba(10, 10, 10, 0.98);
        backdrop-filter: blur(15px);
        transform: translateY(-100%);
        transition: transform 0.5s cubic-bezier(0.8, 0, 0.2, 1);
        display: flex;
        flex-direction: column;
        padding: 1.5rem;
        visibility: hidden;
    }
    
    #mobile-menu.is-open {
        transform: translateY(0);
        visibility: visible;
    }

    /* Subtiele Verticale Lijn */
    .nav-wrapper {
        position: relative;
        padding-left: 20px;
        margin-top: 2rem;
    }

    .nav-line {
        position: absolute;
        left: 0;
        top: 5px;
        bottom: 5px;
        width: 2px;
        background: #E10600;
        transform: scaleY(0);
        transform-origin: top;
        transition: transform 0.6s ease-out 0.3s;
    }

    #mobile-menu.is-open .nav-line {
        transform: scaleY(1);
    }

    /* Compacte Linkjes */
    .mobile-link {
        opacity: 0;
        transform: translateY(10px);
        transition: all 0.3s ease-out;
    }

    #mobile-menu.is-open .mobile-link {
        opacity: 1;
        transform: translateY(0);
    }

    /* Stagger delays */
    <?php for($i=1; $i<=6; $i++): ?>
    #mobile-menu.is-open .mobile-link:nth-child(<?php echo $i+1; ?>) { transition-delay: <?php echo 0.1 + ($i * 0.05); ?>s; }
    <?php endfor; ?>
</style>

<div id="mobile-menu">
    <div class="flex justify-between items-center w-full mb-8">
        <span class="text-lg font-oswald font-black italic text-white uppercase tracking-tighter">
            F1SITE<span class="text-f1-red">.NL</span>
        </span>
        <button id="close-menu-btn" class="text-white/50 hover:text-white p-2">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>

    <nav class="nav-wrapper">
        <div class="nav-line"></div>
        <div class="flex flex-col space-y-4">
            <span class="text-[9px] font-bold text-white/30 uppercase tracking-[0.3em] mb-2">Menu</span>
            <?php foreach($links as $file => $label): ?>
            <a href="<?php echo $file; ?>" class="mobile-link flex items-center justify-between py-1 <?php echo nav_class($file, $current_page, true); ?>">
                <span class="text-2xl font-oswald font-bold uppercase italic tracking-tight">
                    <?php echo $label; ?>
                </span>
                <span class="text-f1-red/50 text-sm">/0<?php echo array_search($file, array_keys($links)) + 1; ?></span>
            </a>
            <?php endforeach; ?>
        </div>
    </nav>

    <div class="mt-auto pt-8 border-t border-white/5 flex justify-between items-center text-[10px] uppercase tracking-widest font-bold text-white/20">
        <div class="flex gap-4">
            <a href="#" class="hover:text-f1-red transition-colors">TW</a>
            <a href="#" class="hover:text-f1-red transition-colors">IG</a>
        </div>
        <span>&copy; <?php echo date('Y'); ?></span>
    </div>
</div>

<header class="sticky top-0 z-[100] bg-black/90 backdrop-blur-md border-b border-white/5">
    <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
        <a href="index.php" class="text-2xl font-oswald font-black italic text-white uppercase tracking-tighter">
            F1SITE<span class="text-f1-red">.NL</span>
        </a>
        
        <nav class="hidden lg:flex space-x-8 text-[10px] font-bold uppercase tracking-[0.2em] text-white/80">
            <?php foreach($links as $file => $label): ?>
                <a href="<?php echo $file; ?>" class="<?php echo nav_class($file, $current_page); ?>"><?php echo $label; ?></a>
            <?php endforeach; ?>
        </nav>

        <button id="mobile-menu-btn" class="lg:hidden flex flex-col justify-between w-6 h-3.5">
            <span class="w-full h-[1.5px] bg-white"></span>
            <span class="w-full h-[1.5px] bg-f1-red"></span>
            <span class="w-full h-[1.5px] bg-white"></span>
        </button>
    </div>
</header>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const menuBtn = document.getElementById('mobile-menu-btn');
    const closeBtn = document.getElementById('close-menu-btn');
    const mobileMenu = document.getElementById('mobile-menu');

    const toggle = (open) => {
        mobileMenu.classList.toggle('is-open', open);
        document.body.style.overflow = open ? 'hidden' : '';
    };

    if(menuBtn) menuBtn.addEventListener('click', () => toggle(true));
    if(closeBtn) closeBtn.addEventListener('click', () => toggle(false));

    document.querySelectorAll('.mobile-link').forEach(link => {
        link.addEventListener('click', () => toggle(false));
    });
});
</script>
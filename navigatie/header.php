<?php
$current_page = basename($_SERVER['PHP_SELF']);
function nav_class($pageName, $current_page, $is_mobile = false) {
    $active = ($pageName === $current_page);
    if ($is_mobile) {
        return $active ? 'text-f1-red font-black' : 'text-white/60 hover:text-white transition-all';
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
        top: 0;
        right: 0;
        bottom: 0;
        width: 300px; 
        max-width: 85%;
        z-index: 9999;
        background: #0a0a0a;
        border-left: 2px solid #E10600;
        box-shadow: -10px 0 30px rgba(0,0,0,0.8);
        transform: translateX(100%);
        transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        display: flex;
        flex-direction: column;
        padding: 2rem 1.5rem;
    }    
    #mobile-menu.is-open {
        transform: translateX(0);
    }
    #menu-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.7);
        backdrop-filter: blur(4px);
        z-index: 9998;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    }
    #menu-backdrop.is-visible {
        opacity: 1;
        visibility: visible;
    }
    .mobile-link {
        opacity: 0;
        transform: translateX(20px);
        transition: all 0.4s ease-out;
    }
    #mobile-menu.is-open .mobile-link {
        opacity: 1;
        transform: translateX(0);
    }
    <?php $i = 1; foreach($links as $link): ?>
    #mobile-menu.is-open .mobile-link:nth-child(<?php echo $i + 1; ?>) { 
        transition-delay: <?php echo 0.1 + ($i * 0.05); ?>s; 
    }
    <?php $i++; endforeach; ?>
    .desktop-link {
        position: relative;
    }
    .desktop-link::after {
        content: '';
        position: absolute;
        width: 0;
        height: 2px;
        bottom: -4px;
        left: 0;
        background-color: #E10600;
        transition: width 0.3s ease;
    }
    .desktop-link:hover::after {
        width: 100%;
    }
</style>

<div id="menu-backdrop"></div>

<div id="mobile-menu">
    <div class="flex justify-between items-center w-full mb-10">
        <span class="text-[10px] font-black uppercase tracking-[0.3em] text-white/20">Menu</span>
        <button id="close-menu-btn" class="text-white/50 hover:text-white transition-colors">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>

    <nav class="flex flex-col space-y-2">
        <?php $count = 1; foreach($links as $file => $label): ?>
        <a href="<?php echo $file; ?>" class="mobile-link group py-4 border-b border-white/5 <?php echo nav_class($file, $current_page, true); ?>">
            <div class="flex items-center justify-between">
                <span class="text-2xl font-oswald font-black uppercase italic tracking-tighter transition-transform group-hover:translate-x-2">
                    <?php echo $label; ?>
                </span>
                <span class="text-f1-red font-mono text-[10px] opacity-40">0<?php echo $count++; ?></span>
            </div>
        </a>
        <?php endforeach; ?>
    </nav>

    <div class="mt-auto">
        <div class="text-[10px] text-white/20 font-bold uppercase tracking-widest mb-4">Official Website</div>
        <a href="index.php" class="text-xl font-oswald font-black italic text-white uppercase tracking-tighter">
            F1SITE<span class="text-f1-red">.NL</span>
        </a>
    </div>
</div>

<header class="sticky top-0 z-[100] bg-black/90 backdrop-blur-md border-b border-white/5">
    <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
        <a href="index.php" class="text-2xl font-oswald font-black italic text-white uppercase tracking-tighter">
            F1SITE<span class="text-f1-red">.NL</span>
        </a>
        
        <nav class="hidden lg:flex items-center space-x-8 text-[11px] font-black uppercase tracking-[0.2em] text-white/70">
            <?php foreach($links as $file => $label): ?>
                <a href="<?php echo $file; ?>" class="desktop-link <?php echo nav_class($file, $current_page); ?>"><?php echo $label; ?></a>
                <?php if(next($links)): ?> <span class="text-white/10">/</span> <?php endif; ?>
            <?php endforeach; ?>
        </nav>

        <button id="mobile-menu-btn" class="lg:hidden flex flex-col justify-between w-6 h-3.5">
            <span class="w-full h-[1.5px] bg-white"></span>
            <span class="w-3/4 h-[1.5px] bg-f1-red self-end"></span>
            <span class="w-full h-[1.5px] bg-white"></span>
        </button>
    </div>
</header>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const menuBtn = document.getElementById('mobile-menu-btn');
    const closeBtn = document.getElementById('close-menu-btn');
    const backdrop = document.getElementById('menu-backdrop');
    const mobileMenu = document.getElementById('mobile-menu');

    const toggleMenu = (show) => {
        if (show) {
            mobileMenu.classList.add('is-open');
            backdrop.classList.add('is-visible');
            document.body.style.overflow = 'hidden'; 
        } else {
            mobileMenu.classList.remove('is-open');
            backdrop.classList.remove('is-visible');
            document.body.style.overflow = '';
        }
    };

    if(menuBtn) menuBtn.addEventListener('click', () => toggleMenu(true));
    if(closeBtn) closeBtn.addEventListener('click', () => toggleMenu(false));
    if(backdrop) backdrop.addEventListener('click', () => toggleMenu(false));
    document.querySelectorAll('.mobile-link').forEach(link => {
        link.addEventListener('click', () => toggleMenu(false));
    });
});
</script>
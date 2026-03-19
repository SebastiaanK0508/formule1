<?php
$current_page = basename($_SERVER['PHP_SELF']);

function nav_class($pageName, $current_page, $is_mobile = false) {
    $active = ($pageName === $current_page);
    if ($is_mobile) {
        return $active ? 'text-f1-red font-black scale-105' : 'text-white/40 hover:text-white transition-all';
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
    /* JOUW TECHNIEK: De basis overlay */
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

    /* NIEUWE OPMAAK: Animatie voor de linkjes */
    .mobile-link {
        opacity: 0;
        transform: translateY(15px);
        transition: all 0.4s ease-out;
    }

    #mobile-menu.is-open .mobile-link {
        opacity: 1;
        transform: translateY(0);
    }

    /* Stagger delays voor de linkjes */
    <?php $i = 1; foreach($links as $link): ?>
    #mobile-menu.is-open .mobile-link:nth-child(<?php echo $i + 1; ?>) { transition-delay: <?php echo 0.1 + ($i * 0.05); ?>s; }
    <?php $i++; endforeach; ?>

    /* Desktop Menu Hover Effect */
    .desktop-link {
        position: relative;
        transition: color 0.3s;
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

<div id="mobile-menu">
    <div class="flex justify-between items-center w-full mb-12">
        <a href="index.php" class="text-xl font-oswald font-black italic text-white uppercase tracking-tighter">
            F1SITE<span class="text-f1-red">.NL</span>
        </a>
        <button id="close-menu-btn" class="text-white/50 hover:text-white p-2">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
    </div>

    <nav class="flex flex-col space-y-6 items-center justify-center flex-grow">
        <?php $count = 1; foreach($links as $file => $label): ?>
        <a href="<?php echo $file; ?>" class="mobile-link group flex flex-col items-center <?php echo nav_class($file, $current_page, true); ?>">
            <span class="text-f1-red font-mono text-[10px] mb-1">/0<?php echo $count++; ?></span>
            <span class="text-4xl font-oswald font-black uppercase italic tracking-tighter transition-transform group-hover:scale-110">
                <?php echo $label; ?>
            </span>
        </a>
        <?php endforeach; ?>
    </nav>
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

    // Sluit bij klik op link
    document.querySelectorAll('.mobile-link').forEach(link => {
        link.addEventListener('click', () => toggle(false));
    });
});
</script>
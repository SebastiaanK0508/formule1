<?php
$server = $_SERVER['SERVER_NAME'];
$requestUri = $_SERVER['REQUEST_URI'];

if ($server === 'localhost' || $server === '127.0.0.1') {
    $baseUrl = "http://localhost:8080/formule1/achterkant/";
} else {
    $baseUrl = "https://achterkant.f1site.nl/";
}

$currentPage = basename($_SERVER['PHP_SELF']);
?>
<base href="<?php echo htmlspecialchars($baseUrl); ?>">

<script src="https://unpkg.com/lucide@latest"></script>
<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&family=Oswald:wght@400;700;900&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script> 

<script>
    tailwind.config = {
        theme: {
            extend: {
                fontFamily: { 
                    'sans': ['Inter', 'sans-serif'], 
                    'oswald': ['Oswald', 'sans-serif'] 
                },
                colors: { 
                    'f1-red': '#E10600', 
                    'f1-dark': '#0b0b0f', 
                    'f1-card': '#16161c' 
                }
            }
        }
    }
</script>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 10px; }  
    .nav-blur { 
        background-color: #121217; 
        backdrop-filter: blur(12px); 
        -webkit-backdrop-filter: blur(12px);
    }
    .f1-red-text { 
        color: #E10600 !important; 
    }

    .sidebar-link { 
        transition: all 0.3s ease;
        border-left: 3px solid transparent; 
        color: #9ca3af; 
    }
    
    .sidebar-link:hover { 
        padding-left: 1.5rem; 
        background: linear-gradient(90deg, rgba(225, 6, 0, 0.1) 0%, transparent 100%); 
        border-left-color: #E10600; 
        color: #ffffff;
    }

    .sidebar-link.active { 
        background: linear-gradient(90deg, rgba(225, 6, 0, 0.15) 0%, transparent 100%); 
        border-left-color: #E10600; 
        color: #ffffff !important;
        font-weight: 700;
    }
    .f1-logo-white { 
        color: #ffffff;
    }
</style>

<div x-data="{ mobileMenu: false }" class="antialiased text-white">
    
    <div class="lg:hidden fixed top-0 left-0 right-0 h-16 nav-blur border-b border-white/10 z-[100] flex items-center justify-between px-6">
        <h1 class="font-oswald font-black italic text-xl tracking-tighter uppercase">
            F1SITE<span class="text-f1-red">.NL</span>
        </h1>
        <button @click="mobileMenu = !mobileMenu" class="p-2 hover:bg-white/5 rounded-lg transition">
            <i x-show="!mobileMenu" data-lucide="menu"></i>
            <i x-show="mobileMenu" data-lucide="x"></i>
        </button>
    </div>

    <aside :class="mobileMenu ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'" class="fixed top-0 left-0 z-[110] flex flex-col w-72 h-screen nav-blur border-r border-white/5 p-6 transition-transform duration-300 lg:sticky">
        <div class="mb-10 px-4 hidden lg:block">
            <h1 class="text-3xl font-oswald font-black italic tracking-tighter f1-gradient-text uppercase">
                F1SITE<span class="text-f1-red">.NL</span>
            </h1>
            <div class="flex items-center gap-2 mt-1">
                <span class="w-2 h-2 rounded-full bg-f1-red animate-pulse shadow-[0_0_8px_#E10600]"></span>
                <span class="text-[9px] font-black text-gray-500 tracking-[0.3em] uppercase">Telemetry Active</span>
            </div>
        </div>
        <nav class="flex-grow space-y-1 overflow-y-auto pr-2 custom-scrollbar">
            <?php
            $menuItems = [
                ['url' => 'dashboard.php', 'label' => 'Dashboard', 'icon' => 'layout-dashboard'],
                ['url' => 'bewerken/nieuws.php', 'label' => 'Nieuws Beheer', 'icon' => 'newspaper'],
                ['url' => 'bewerken/circuits.php', 'label' => 'Circuits & Kalender', 'icon' => 'map-pin'],
                ['url' => 'bewerken/teams.php', 'label' => 'F1 Teams', 'icon' => 'shield'],
                ['url' => 'bewerken/drivers.php', 'label' => 'Coureurs', 'icon' => 'user-circle']
            ];
            foreach ($menuItems as $item): 
            ?>
                <a href="<?php echo $item['url']; ?>" 
                   class="sidebar-link group flex items-center gap-4 px-4 py-3 rounded-r-xl text-gray-400 <?php echo $isActive ? 'active' : ''; ?>">
                    <i data-lucide="<?php echo $item['icon']; ?>" class="w-5 h-5 group-hover:text-f1-red transition-colors"></i>
                    <span class="text-sm tracking-wide uppercase italic font-oswald group-hover:text-white"><?php echo $item['label']; ?></span>
                </a>
            <?php endforeach; ?>
        </nav>
        <div class="mt-auto pt-6 border-t border-white/10 bg-black/20 -mx-6 px-6">
            <div class="flex items-center gap-4 px-2 mb-6 cursor-default">
                <div class="relative">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-f1-red to-red-900 flex items-center justify-center font-black text-white text-xs">
                        <?php echo isset($_SESSION['admin_name']) ? strtoupper(substr($_SESSION['admin_name'], 0, 2)) : 'AD'; ?>
                    </div>
                    <div class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-green-500 border-2 border-[#121217] rounded-full"></div>
                </div>
                <div class="overflow-hidden">
                    <p class="text-xs font-bold truncate uppercase"><?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?></p>
                    <p class="text-[8px] text-gray-500 uppercase font-black tracking-widest mt-1">Chief Engineer</p>
                </div>
            </div>
            <a href="logout.php" 
               class="flex items-center justify-center gap-2 w-full py-3 rounded-xl bg-white/5 border border-white/10 text-[9px] font-black uppercase tracking-[0.2em] hover:bg-f1-red hover:text-white transition-all duration-300">
                <i data-lucide="log-out" class="w-3.5 h-3.5"></i>
                Sign Off
            </a>
        </div>
    </aside>

    <div x-show="mobileMenu" 
         x-transition.opacity
         @click="mobileMenu = false" 
         class="fixed inset-0 bg-black/80 backdrop-blur-sm z-[105] lg:hidden">
    </div>
</div>

<script>
    lucide.createIcons();
</script>
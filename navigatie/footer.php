<?php
$current_page = basename($_SERVER['PHP_SELF']);

// Base URL bepaling voor localhost of live
$server = $_SERVER['SERVER_NAME'];
if ($server === 'localhost' || $server === '127.0.0.1') {
    $baseUrl = "http://localhost:8080/formule1/";
} else {
    $baseUrl = "https://f1site.nl/";
}
?>

<footer class="bg-black mt-24 py-12 md:py-20 border-t-2 border-f1-red">
    <div class="max-w-7xl mx-auto px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-12 text-center md:text-left pb-16">
            
            <div class="md:col-span-2 space-y-6">
                <a href="<?php echo $baseUrl; ?>" class="inline-block transition-transform active:scale-95">
                    <h3 class="text-4xl md:text-3xl font-oswald font-black text-white italic tracking-tighter uppercase">F1SITE<span class="text-f1-red">.NL</span></h3>
                </a>
                <p class="text-gray-500 text-sm font-medium leading-relaxed max-w-sm mx-auto md:mx-0">
                    The premier digital destination for Formula 1 enthusiasts. Delivering real-time data, expert technical analysis, and the latest updates from the paddock directly to your screen.
                </p>
            </div>

            <div>
                <h4 class="text-[10px] font-black text-f1-red mb-6 uppercase tracking-[0.3em]">Navigation & Info</h4>
                <ul class="space-y-4">
                    <?php
                    $nav_items = [
                        'sitemap.php' => 'Sitemap',
                        'privacy-en.html' => 'Privacy Policy',
                        'algemenevoorwaarden-en.html' => 'Terms & Conditions',
                        'contact.php' => 'Contact Us'
                    ];

                    foreach ($nav_items as $url => $label) {
                        $active_class = ($current_page === $url) ? 'text-f1-red' : 'text-gray-400';
                        echo "<li><a href=\"$url\" class=\"$active_class text-sm font-bold hover:text-white transition-colors duration-200 block uppercase tracking-wider py-1\">$label</a></li>";
                    }
                    ?>
                </ul>
            </div>

            <div>
                <h4 class="text-[10px] font-black text-f1-red mb-6 uppercase tracking-[0.3em]">Engineering</h4>
                <div class="space-y-1">
                    <span class="text-gray-500 text-[10px] uppercase font-bold tracking-widest block mb-1">Developed by</span>
                    <a href="https://www.webius.nl" target="_blank" rel="noopener" class="text-white text-lg font-oswald font-bold hover:text-f1-red transition-colors duration-200 uppercase italic">Webius</a>
                </div>
            </div>
        </div>

        <div class="pt-8 border-t border-white/5 flex flex-col md:flex-row justify-between items-center gap-8">
            <div class="flex flex-col md:flex-row items-center gap-4 md:gap-8">
                <p class="text-gray-600 text-[9px] font-black uppercase tracking-[0.4em] italic text-center md:text-left">
                    &copy; <?php echo date('Y'); ?>  F1SITE.NL - All rights reserved.
                </p>
                <div class="hidden md:block h-4 w-[1px] bg-white/10"></div>
                <p class="text-gray-700 text-[9px] font-bold uppercase tracking-widest text-center">Not affiliated with the Formula One Group | Developed by Bas Kamphuis</p>
            </div>
            
            <div class="flex items-center gap-4">
                <span class="h-[1px] w-8 bg-f1-red/30"></span>
                <span class="text-f1-red text-sm font-oswald italic font-black uppercase tracking-tighter">Faster than light</span>
            </div>
        </div>
    </div>
</footer>
<?php if (!isset($_COOKIE['f1_consent'])): ?>
<div id="cookie-overlay" class="fixed inset-0 bg-black/95 backdrop-blur-xl z-[9999] flex items-center justify-center p-4 md:p-6 opacity-0 transition-opacity duration-500 overflow-y-auto">
    <div class="bg-[#0a0a0a] border border-white/10 rounded-[2rem] w-full max-w-2xl shadow-2xl relative overflow-hidden flex flex-col max-h-[95vh] md:max-h-[90vh]">
        
        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-transparent via-f1-red to-transparent"></div>
        
        <div class="p-6 md:p-8 pb-4 text-center md:text-left">
            <div class="flex items-center gap-3 mb-2 justify-center md:justify-start">
                <span class="text-f1-red font-black tracking-widest text-[10px] uppercase">Configuration</span>
                <div class="h-[1px] w-12 bg-white/10"></div>
            </div>
            <h2 class="text-3xl md:text-4xl font-oswald font-black uppercase italic tracking-tighter text-white leading-none">
                Data <span class="text-f1-red">Pitstop</span>
            </h2>
            <p class="text-gray-400 text-xs md:text-sm mt-4 leading-relaxed">
                Tune your privacy settings. We use cookies to optimize the aerodynamics of our website and provide you with the fastest news updates.
            </p>
        </div>
        <div class="px-6 md:px-8 py-4 overflow-y-auto space-y-3">
            <div class="bg-white/5 border border-white/5 p-4 rounded-2xl flex items-center justify-between opacity-50">
                <div class="flex-grow pr-4">
                    <h4 class="text-white font-bold text-xs uppercase tracking-wider">Necessary</h4>
                    <p class="text-gray-500 text-[10px]">Required for basic site functionality.</p>
                </div>
                <div class="relative inline-flex items-center">
                    <div class="w-10 h-5 bg-f1-red rounded-full"></div>
                    <div class="absolute left-5 top-1 bg-white w-3 h-3 rounded-full"></div>
                </div>
            </div>

            <label class="bg-white/5 border border-white/5 p-4 rounded-2xl flex items-center justify-between hover:bg-white/10 transition-colors cursor-pointer group">
                <div class="flex-grow pr-4">
                    <h4 class="text-white font-bold text-xs uppercase tracking-wider group-hover:text-f1-red transition-colors">Analytics</h4>
                    <p class="text-gray-500 text-[10px]">Helps us improve performance and site features.</p>
                </div>
                <div class="relative">
                    <input type="checkbox" id="check-analytics" class="sr-only peer" checked>
                    <div class="w-10 h-5 bg-gray-700 rounded-full peer peer-checked:bg-f1-red after:content-[''] after:absolute after:top-[4px] after:left-[4px] after:bg-white after:rounded-full after:h-3 after:w-3 after:transition-all peer-checked:after:translate-x-5"></div>
                </div>
            </label>

            <label class="bg-white/5 border border-white/5 p-4 rounded-2xl flex items-center justify-between hover:bg-white/10 transition-colors cursor-pointer group">
                <div class="flex-grow pr-4">
                    <h4 class="text-white font-bold text-xs uppercase tracking-wider group-hover:text-f1-red transition-colors">Marketing</h4>
                    <p class="text-gray-500 text-[10px]">Tailored content and advertisements based on your interests.</p>
                </div>
                <div class="relative">
                    <input type="checkbox" id="check-marketing" class="sr-only peer">
                    <div class="w-10 h-5 bg-gray-700 rounded-full peer peer-checked:bg-f1-red after:content-[''] after:absolute after:top-[4px] after:left-[4px] after:bg-white after:rounded-full after:h-3 after:w-3 after:transition-all peer-checked:after:translate-x-5"></div>
                </div>
            </label>
        </div>

        <div class="p-6 md:p-8 pt-4 grid grid-cols-1 md:grid-cols-2 gap-3">
            <button onclick="saveCookieSettings('all')" 
                class="bg-f1-red text-white py-4 rounded-xl font-black uppercase text-xs tracking-widest hover:brightness-110 active:scale-95 transition-all shadow-xl order-1">
                Accept All
            </button>
            <button onclick="saveCookieSettings('custom')" 
                class="bg-white/10 text-white py-4 rounded-xl font-black uppercase text-xs tracking-widest hover:bg-white/20 active:scale-95 transition-all order-2">
                Save Selection
            </button>
        </div>

        <div class="pb-6 text-center">
            <a href="cookiebeleid.html" class="text-[9px] text-gray-600 uppercase font-black tracking-widest hover:text-white transition">Privacy & Cookie Policy</a>
        </div>
    </div>
</div>

<script>
    window.addEventListener('load', () => {
        const overlay = document.getElementById('cookie-overlay');
        if (overlay) {
            overlay.classList.remove('opacity-0');
            document.body.style.overflow = 'hidden';
        }
    });
    function saveCookieSettings(mode) {
    const analytics = document.getElementById('check-analytics').checked;
    const marketing = document.getElementById('check-marketing').checked;
    let type = 'essential';

    if (mode === 'all') {
        type = 'all';
    } else {
        if (analytics && marketing) {
            type = 'all';
        } else if (analytics) {
            type = 'analytics';
        } else if (marketing) {
            type = 'marketing';
        } else {
            type = 'essential';
        }
    }

    fetch('navigatie/cookie.php?action=accept&type=' + type)
        .then(response => {
            window.location.reload(); 
        })
        .catch(error => {
            console.error('Fout bij opslaan cookies:', error);
            window.location.reload();
        });
    }
</script>
<?php endif; ?>
<?php
$current_page = basename($_SERVER['PHP_SELF']);

$server = $_SERVER['SERVER_NAME'];
$requestUri = $_SERVER['REQUEST_URI'];
if ($server === 'localhost' || $server === '127.0.0.1') {
    $baseUrl = "http://localhost:8080/formule1/";
} else {
    $baseUrl = "https://f1site.nl/";
}
?>
<footer class="bg-black mt-24 py-16 border-t-2 border-f1-red">
    <div class="max-w-7xl mx-auto px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-12 text-left pb-12 border-b border-white/5">
            
            <div class="space-y-4 text-center md:text-left">
                <a href="<?php echo $baseUrl; ?>" class="inline-block">
                    <h3 class="text-2xl font-oswald font-black text-white italic tracking-tighter uppercase">F1SITE<span class="text-f1-red">.NL</span></h3>
                </a>
                <p class="text-gray-500 text-sm font-medium leading-relaxed max-w-xs mx-auto md:mx-0">
                    Your ultimate source for the latest Formula 1 news, live countdowns, statistics and race updates.
                </p>
            </div>

            <div class="text-center md:text-left">
                <h4 class="text-xs font-black text-f1-red mb-6 uppercase tracking-[0.3em]">Developer</h4>
                <ul class="space-y-4">
                    <li>
                        <a href="https://www.webius.nl" target="_blank" rel="noopener" class="text-gray-400 text-sm font-bold hover:text-white transition duration-200 block uppercase tracking-wider">Webius</a>
                    </li>
                </ul>
            </div>

            <div class="text-center md:text-left">
                <h4 class="text-xs font-black text-f1-red mb-6 uppercase tracking-[0.3em]">Navigatie & Info</h4>
                <ul class="space-y-4">
                    <?php
                    $nav_items = [
                        'sitemap.php' => 'Sitemap',
                        'privacy-en.html' => 'Privacy Policy',
                        'algemenevoorwaarden-en.html' => 'Terms & Conditions',
                        'contact.php' => 'Contact'
                    ];

                    foreach ($nav_items as $url => $label) {
                        $active_class = ($current_page === $url) ? 'text-f1-red' : 'text-gray-400';
                        echo "<li><a href=\"$url\" class=\"$active_class text-sm font-bold hover:text-white transition duration-200 block uppercase tracking-wider\">$label</a></li>";
                    }
                    ?>
                </ul>
            </div>
        </div>

        <div class="pt-10 flex flex-col md:flex-row justify-between items-center gap-4 text-center md:text-left">
            <p class="text-gray-600 text-[10px] font-black uppercase tracking-[0.5em] italic">
                &copy; <?php echo date('Y'); ?> WEBIUS. All rights reserved.
            </p>
            <div class="flex gap-6">
                <span class="text-f1-red opacity-20 text-xl font-oswald italic font-black uppercase" aria-hidden="true">Faster than light</span>
            </div>
        </div>
    </div>
</footer>
<?php 
if (!isset($_COOKIE['f1_consent'])): 
?>
<div id="cookie-overlay" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-[9999] flex items-center justify-center transition-opacity duration-300">
    <div class="bg-zinc-900 p-8 rounded-2xl border-t-4 border-f1-red max-w-md w-full mx-4 shadow-2xl relative overflow-hidden">
        
        <div class="absolute -right-10 -top-10 opacity-10 pointer-events-none">
            <svg width="200" height="200" viewBox="0 0 100 100"><path d="M0 0 L100 0 L100 20 L20 20 L0 100 Z" fill="white"/></svg>
        </div>

        <div class="relative z-10 text-center">
            <h2 class="text-3xl font-oswald font-black mb-2 uppercase italic tracking-tighter flex items-center justify-center gap-2 text-white">
                <span class="text-f1-red">/</span> Data Pitstop
            </h2>
            
            <p class="text-gray-300 text-sm mb-6 leading-relaxed">
                Om je de snelste race-ervaring en de scherpste analyses te bieden, gebruiken we cookies. Hiermee optimaliseren we de site en tonen we relevante F1-content.
            </p>

            <div class="space-y-3">
                <button onclick="handleCookieConsent('all')" 
                    class="w-full bg-f1-red py-4 rounded-lg font-bold uppercase text-sm text-white hover:brightness-110 transition shadow-lg transform hover:scale-[1.02] active:scale-95">
                    Accepteer Alle Cookies
                </button>
                
                <div class="flex gap-3">
                    <button onclick="handleCookieConsent('essential')" 
                        class="flex-1 bg-white/10 py-3 rounded-lg font-bold uppercase text-[10px] text-white tracking-widest hover:bg-white/20 transition">
                        Alleen Functioneel
                    </button>
                    
                    <a href="cookiebeleid.html" target="_blank" 
                        class="flex-1 border border-white/20 py-3 rounded-lg font-bold uppercase text-[10px] tracking-widest text-gray-400 hover:text-white transition flex items-center justify-center">
                        Cookie Consent
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function handleCookieConsent(type) {
        const overlay = document.getElementById('cookie-overlay');
        if (overlay) {
            overlay.classList.add('opacity-0');
            document.body.style.overflow = 'auto'; 
            document.body.style.position = '';
            
            setTimeout(() => {
                overlay.style.display = 'none';
            }, 300);
        }

        localStorage.setItem('f1_consent_fixed', type);
        fetch('cookie.php?action=accept&type=' + type)
            .then(response => console.log('Consent saved'));
    }
    window.addEventListener('load', () => {
        const overlay = document.getElementById('cookie-overlay');
        if (overlay && window.getComputedStyle(overlay).display !== 'none') {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = 'auto'; 
        }
    });
</script>
<?php endif; ?>
<?php
$current_page = basename($_SERVER['PHP_SELF']);
$server = $_SERVER['SERVER_NAME'];
$baseUrl = ($server === 'localhost' || $server === '127.0.0.1') ? "http://localhost:8080/formule1/" : "https://f1site.nl/";
?>

<footer class="bg-[#050505] mt-24 pt-20 pb-10 relative overflow-hidden">
    <div class="absolute top-0 left-0 w-full h-[1px] bg-gradient-to-r from-transparent via-f1-red/50 to-transparent"></div>
    <div class="absolute -top-24 -left-24 w-96 h-96 bg-f1-red/5 rounded-full blur-[120px]"></div>

    <div class="max-w-7xl mx-auto px-6 relative z-10">
        <div class="flex flex-col lg:flex-row justify-between gap-16 mb-20">
            
            <div class="max-w-md space-y-8">
                <a href="<?php echo $baseUrl; ?>" class="inline-block group">
                    <div class="flex items-baseline gap-1">
                        <h3 class="text-5xl font-oswald font-black text-white italic tracking-[calc(-0.05em)] uppercase leading-none transition-transform group-hover:scale-[1.02]">
                            F1SITE<span class="text-f1-red">.NL</span>
                        </h3>
                    </div>
                    <div class="h-1 w-12 bg-f1-red mt-2 transition-all group-hover:w-full"></div>
                </a>
                <p class="text-gray-400 text-sm font-light leading-relaxed tracking-wide">
                    Elevating the Formula 1 experience through high-fidelity data and technical storytelling. Where engineering meets digital excellence.
                </p>
                <div class="flex gap-4">
                    <div class="px-4 py-2 border border-white/10 rounded-full text-[9px] font-black text-white uppercase tracking-[0.2em] bg-white/5">
                        Est. 2024
                    </div>
                    <div class="px-4 py-2 border border-f1-red/20 rounded-full text-[9px] font-black text-f1-red uppercase tracking-[0.2em] bg-f1-red/5">
                        High Performance Content
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 gap-12">
                <div class="space-y-6">
                    <span class="text-[10px] font-black text-f1-red uppercase tracking-[0.4em]">Index</span>
                    <ul class="space-y-4">
                        <?php
                        $nav_items = [
                            'sitemap.php' => 'Sitemap',
                            'contact.php' => 'Contact'
                        ];
                        foreach ($nav_items as $url => $label) {
                            $active = ($current_page === $url) ? 'text-white' : 'text-gray-500';
                            echo "<li><a href=\"$url\" class=\"$active text-xs font-bold hover:text-f1-red transition-all uppercase tracking-widest\">$label</a></li>";
                        }
                        ?>
                    </ul>
                </div>
                <div class="space-y-6">
                    <span class="text-[10px] font-black text-f1-red uppercase tracking-[0.4em]">Legal</span>
                    <ul class="space-y-4">
                        <?php
                        $nav_items = [
                            'privacy-en.php' => 'Privacy',
                            'algemenevoorwaarden-en.php' => 'Terms'
                        ];
                        foreach ($nav_items as $url => $label) {
                            $active = ($current_page === $url) ? 'text-white' : 'text-gray-500';
                            echo "<li><a href=\"$url\" class=\"$active text-xs font-bold hover:text-f1-red transition-all uppercase tracking-widest\">$label</a></li>";
                        }
                        ?>
                    </ul>
                </div>
                <div class="col-span-2 md:col-span-1 space-y-6">
                    <span class="text-[10px] font-black text-f1-red uppercase tracking-[0.4em]">Studio</span>
                    <a href="https://www.webius.nl" target="_blank" class="block group">
                        <span class="text-2xl font-oswald font-bold text-white group-hover:text-f1-red transition-all italic uppercase">Webius</span>
                        <span class="block text-[10px] text-gray-600 font-bold tracking-tighter">DIGITAL ARCHITECTURE</span>
                    </a>
                </div>
            </div>
        </div>

        <div class="pt-10 border-t border-white/5 flex flex-col md:flex-row justify-between items-center gap-6">
            <div class="flex flex-col gap-1">
                <p class="text-gray-600 text-[9px] font-bold uppercase tracking-[0.3em]">
                    © <?php echo date('Y'); ?> F1SITE.NL | ALL RIGHTS RESERVED
                </p>
                <p class="text-gray-800 text-[8px] font-bold uppercase tracking-[0.2em]">
                    Built by <a href="#" class="hover:text-gray-400">Sebastiaan Kamphuis</a> • Independent Media
                </p>
            </div>
            <div class="flex items-center gap-6">
                <span class="w-12 h-[1px] bg-white/10"></span>
                <span class="text-white/20 text-[10px] font-oswald italic uppercase tracking-[0.5em]">Faster than light</span>
            </div>
        </div>
    </div>
</footer>

<?php if (!isset($_COOKIE['f1_consent'])): ?>
<div id="cookie-overlay" class="fixed inset-0 z-[9999] flex items-center justify-center p-6 opacity-0 pointer-events-none transition-all duration-[1000ms] backdrop-blur-[20px] bg-black/60">
    <div class="bg-[#0c0c0c] border border-white/10 rounded-[3rem] w-full max-w-2xl overflow-hidden shadow-[0_32px_64px_-12px_rgba(0,0,0,0.8)] translate-y-12 transition-transform duration-[800ms]" id="cookie-modal">
        
        <div class="flex flex-col md:flex-row">
            <div class="bg-f1-red p-12 text-white flex flex-col justify-between md:w-5/12">
                <div class="space-y-4">
                    <div class="w-12 h-[2px] bg-white/30"></div>
                    <h2 class="text-4xl font-oswald font-black uppercase italic tracking-tighter leading-none">The<br>Pitstop</h2>
                </div>
                <p class="text-[10px] font-black uppercase tracking-[0.2em] leading-relaxed opacity-80 mt-12 md:mt-0">
                    Precision tuning for your digital experience.
                </p>
            </div>

            <div class="p-8 md:p-12 md:w-7/12 space-y-8">
                <div class="space-y-2">
                    <h3 class="text-white text-lg font-bold">Privacy Settings</h3>
                    <p class="text-gray-500 text-xs font-medium">Select your preferred telemetry level for this session.</p>
                </div>

                <div class="space-y-4">
                    <label class="flex items-center justify-between group cursor-pointer">
                        <span class="text-[10px] font-black uppercase tracking-widest text-gray-400 group-hover:text-white transition-colors">Analytics Data</span>
                        <input type="checkbox" id="check-analytics" class="sr-only peer" checked>
                        <div class="w-12 h-[2px] bg-gray-800 relative peer-checked:bg-f1-red transition-colors after:content-[''] after:absolute after:-top-1 after:left-0 after:w-3 after:h-3 after:bg-gray-600 peer-checked:after:bg-white peer-checked:after:translate-x-9 after:transition-all"></div>
                    </label>
                    <label class="flex items-center justify-between group cursor-pointer">
                        <span class="text-[10px] font-black uppercase tracking-widest text-gray-400 group-hover:text-white transition-colors">Marketing Intel</span>
                        <input type="checkbox" id="check-marketing" class="sr-only peer">
                        <div class="w-12 h-[2px] bg-gray-800 relative peer-checked:bg-f1-red transition-colors after:content-[''] after:absolute after:-top-1 after:left-0 after:w-3 after:h-3 after:bg-gray-600 peer-checked:after:bg-white peer-checked:after:translate-x-9 after:transition-all"></div>
                    </label>
                </div>

                <div class="pt-4 space-y-3">
                    <button onclick="saveCookieSettings('all')" class="w-full bg-white text-black py-4 rounded-full font-black text-[10px] uppercase tracking-[0.2em] hover:bg-f1-red hover:text-white transition-all transform active:scale-95">
                        Accept All Systems
                    </button>
                    <button onclick="saveCookieSettings('custom')" class="w-full text-gray-500 py-2 font-black text-[9px] uppercase tracking-[0.2em] hover:text-white transition-all">
                        Save Preferences
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    window.addEventListener('load', () => {
        const overlay = document.getElementById('cookie-overlay');
        const modal = document.getElementById('cookie-modal');
        if (overlay) {
            setTimeout(() => {
                overlay.classList.remove('opacity-0', 'pointer-events-none');
                modal.classList.remove('translate-y-12');
                document.body.style.overflow = 'hidden';
            }, 400);
        }
    });

    function saveCookieSettings(mode) {
        const analytics = document.getElementById('check-analytics').checked;
        const marketing = document.getElementById('check-marketing').checked;
        let type = (mode === 'all') ? 'all' : (analytics && marketing ? 'all' : (analytics ? 'analytics' : (marketing ? 'marketing' : 'essential')));
        
        fetch(`navigatie/cookie.php?action=accept&type=${type}`)
            .then(() => {
                document.getElementById('cookie-modal').classList.add('translate-y-12');
                document.getElementById('cookie-overlay').classList.add('opacity-0');
                setTimeout(() => window.location.reload(), 600);
            });
    }
</script>
<?php endif; ?>
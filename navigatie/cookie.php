<div id="cookie-overlay" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-[9999] flex items-center justify-center">
    <div class="bg-f1-card p-8 rounded-2xl border-t-4 border-f1-red max-w-md w-full mx-4 shadow-2xl relative overflow-hidden">
        <div class="absolute -right-10 -top-10 opacity-10">
            <svg width="200" height="200" viewBox="0 0 100 100"><path d="M0 0 L100 0 L100 20 L20 20 L0 100 Z" fill="white"/></svg>
        </div>

        <div class="relative z-10">
            <h2 class="text-3xl font-oswald font-black mb-2 uppercase italic tracking-tighter flex items-center justify-center gap-2">
                <span class="text-f1-red">/</span> Data Pitstop
            </h2>
            
            <p class="text-gray-300 text-sm mb-6 leading-relaxed">
                Om je de snelste race-ervaring en de scherpste analyses te bieden, gebruiken we cookies. Hiermee optimaliseren we de site en tonen we relevante F1-content.
            </p>

            <div class="space-y-3">
                <button onclick="acceptCookies()" 
                    class="w-full bg-f1-red py-4 rounded-lg font-bold uppercase text-sm hover:brightness-110 transition shadow-lg transform hover:scale-[1.02] active:scale-95">
                    Accepteer Alle Cookies
                </button>
                
                <div class="flex gap-3">
                    <button onclick="acceptEssential()" 
                        class="flex-1 bg-white/10 py-3 rounded-lg font-bold uppercase text-[10px] tracking-widest hover:bg-white/20 transition">
                        Alleen Functioneel
                    </button>
                    
                    <a href="/privacy-policy" 
                        class="flex-1 border border-white/20 py-3 rounded-lg font-bold uppercase text-[10px] tracking-widest text-gray-400 hover:text-white transition flex items-center justify-center">
                        Privacy Policy
                    </a>
                </div>
            </div>

            <p class="mt-6 text-[10px] text-gray-500 uppercase tracking-widest">
                Snelheid is alles, ook voor je privacy.
            </p>
        </div>
    </div>
</div>
<script>
    const overlay = document.getElementById('cookie-overlay');
        if (!localStorage.getItem('f1_consent_fixed')) {
            overlay.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
        window.acceptCookies = () => {
            localStorage.setItem('f1_consent_fixed', 'true');
            overlay.style.display = 'none';
            document.body.style.overflow = 'auto';
        };
</script>
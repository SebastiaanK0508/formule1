<!DOCTYPE html>
<html lang="nl">
<head>
    <?php include 'head.php'; ?>
</head>
<body class="bg-pattern min-h-screen flex items-center justify-center p-6 text-white">

    <main class="w-full max-w-[450px]" data-aos="zoom-in">
        <div class="text-center mb-10">
            <h1 class="text-5xl font-oswald font-black uppercase italic tracking-tighter leading-none">
                F1SITE<span class="text-f1-red">.NL</span><span class="text-sm block font-sans font-bold tracking-[0.5em] text-gray-500 mt-2">DASHBOARD</span>
            </h1>
        </div>

        <div class="news-card f1-border bg-f1-card rounded-br-[3rem] border-r border-b border-white/5 p-10 md:p-12 shadow-2xl relative overflow-hidden">
            <div class="absolute -top-24 -right-24 w-48 h-48 bg-f1-red/10 blur-[80px] rounded-full"></div>
            
            <div class="relative z-10">
                <div class="flex items-center gap-3 mb-8">
                    <span class="h-[2px] w-8 bg-f1-red"></span>
                    <span class="text-f1-red text-[10px] font-black uppercase tracking-[0.3em]">Identity Verification</span>
                </div>

                <h2 class="text-3xl font-oswald font-black uppercase italic mb-8 tracking-tighter">Paddock <span class="text-f1-red">Access</span></h2>

                <form action="auth_check.php" method="POST" class="relative z-50 space-y-6">
                    
                    <div class="space-y-2 text-left">
                        <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 ml-1">Engineer ID</label>
                        <input type="text" name="username" required 
                            class="w-full px-5 py-4 rounded-xl font-bold text-white bg-white/5 border border-white/10 outline-none focus:border-f1-red transition-all duration-300 placeholder:text-gray-700"
                            placeholder="Gebruikersnaam">
                    </div>

                    <div class="space-y-2 text-left">
                        <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 ml-1">Security Token</label>
                        <input type="password" name="password" required 
                            class="w-full px-5 py-4 rounded-xl font-bold text-white bg-white/5 border border-white/10 outline-none focus:border-f1-red transition-all duration-300 placeholder:text-gray-700"
                            placeholder="••••••••••••">
                    </div>

                    <div class="pt-4">
                        <button type="submit" name="login" 
                            class="group relative w-full flex items-center justify-center px-8 py-5 overflow-hidden font-black uppercase tracking-[0.4em] text-[11px] text-white bg-white/5 border border-white/10 rounded-full hover:border-f1-red/50 shadow-xl active:scale-95 transition-all duration-300 cursor-pointer">
                            
                            <span class="relative z-10 flex items-center gap-3 pointer-events-none">
                                Start Session <span class="text-f1-red text-xl group-hover:translate-x-2 transition-transform duration-300">→</span>
                            </span>

                            <div class="absolute inset-0 w-full h-full bg-gradient-to-r from-f1-red/40 to-transparent opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none"></div>
                        </button>
                    </div>

                </form>
            </div>
        </div>

        <div class="mt-10 text-center">
            <p class="text-[9px] font-black uppercase tracking-[0.5em] text-gray-600">
                &copy; 2026 Formula 1 World Championship Database
            </p>
        </div>
    </main>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({ duration: 800, once: true });
    </script>
</body>
</html>
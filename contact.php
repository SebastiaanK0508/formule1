<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact | F1SITE.NL</title>
    <?php include 'navigatie/head.php'; ?>
    <style>
        .elite-card { @apply bg-f1-card p-8 md:p-12 rounded-[2.5rem] border border-white/5 shadow-2xl; }
    </style>
</head>
<body class="bg-pattern font-sans">
    <?php include 'navigatie/header.php'; ?>
    <main class="max-w-3xl mx-auto px-6 py-16 flex-grow">
        <div class="mb-16 text-center" data-aos="fade-down">
            <h1 class="text-6xl md:text-8xl font-oswald font-black uppercase italic tracking-tighter leading-none mb-4">
                GET IN<span class="text-f1-red">TOUCH</span>
            </h1>
        </div>
        <div class="elite-card relative overflow-hidden" data-aos="fade-up">
            <div id="status-box" class="hidden mb-8 p-5 rounded-xl border italic font-bold uppercase tracking-widest text-xs text-center"></div>

            <h2 class="text-4xl font-oswald font-black uppercase italic tracking-tighter mb-8 border-l-4 border-f1-red pl-6">
                Send a <span class="text-f1-red">Message</span>
            </h2>
            
            <form class="space-y-4" id="contact-form" action="contact_connect" method="post">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <input class="w-full bg-black/40 border-l-2 border-white/10 p-5 text-white focus:border-f1-red transition-all outline-none italic text-sm placeholder:text-white/20" 
                           type="text" name="contact_name" placeholder="YOUR NAME" required>
                    <input class="w-full bg-black/40 border-l-2 border-white/10 p-5 text-white focus:border-f1-red transition-all outline-none italic text-sm placeholder:text-white/20" 
                           type="email" name="contact_email" placeholder="YOUR EMAIL" required>
                </div>
                <input class="w-full bg-black/40 border-l-2 border-white/10 p-5 text-white focus:border-f1-red transition-all outline-none italic text-sm placeholder:text-white/20" 
                       type="text" name="contact_subject" placeholder="SUBJECT" required>
                <textarea class="w-full bg-black/40 border-l-2 border-white/10 p-5 text-white focus:border-f1-red transition-all outline-none italic text-sm placeholder:text-white/20 min-h-[150px] resize-none" 
                          name="contact_message" placeholder="TYPE YOUR MESSAGE HERE..." required></textarea>
                <div class="flex justify-end pt-4">
                    <button type="submit" class="group relative flex items-center gap-4 bg-transparent border-2 border-f1-red px-10 py-4 overflow-hidden transition-all hover:bg-f1-red">
                        <span class="relative z-10 text-xs font-black uppercase tracking-[0.3em] text-white">
                            Transmit Data
                        </span>
                        <svg class="relative z-10 w-5 h-5 text-f1-red group-hover:text-white group-hover:translate-x-2 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </button>
                </div>
            </form>
        </div>
    </main>
    <?php include 'navigatie/footer.php'; ?>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            AOS.init({ duration: 1000, once: true });
            window.toggleMenu = () => { 
                document.getElementById('mobile-menu').classList.toggle('active'); 
            };
            const params = new URLSearchParams(window.location.search);
            if (params.get('status') === 'success') {
                const box = document.getElementById('status-box');
                box.textContent = "Message successfully transmitted!";
                box.classList.remove('hidden');
                box.classList.add('bg-green-500/10', 'border-green-500/50', 'text-green-500');
            }
        });
    </script>
</body>
</html>
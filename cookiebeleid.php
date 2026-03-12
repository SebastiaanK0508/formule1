<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cookiebeleid | F1SITE.NL</title>
    
    <?php include 'navigatie/head.php'; ?>

    <style>
        .bg-pattern {
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.02'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }

        .elite-card {
            background: rgba(22, 22, 28, 0.5);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 2.5rem;
            padding: 3rem;
        }

        .policy-h2 {
            font-family: 'Oswald', sans-serif;
            font-weight: 900;
            text-transform: uppercase;
            font-style: italic;
            font-size: 1.5rem;
            color: white;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .red-speed-line {
            height: 2px;
            flex-grow: 1;
            background: linear-gradient(to right, #e10600, transparent);
        }

        .policy-p {
            color: #9ca3af;
            font-size: 0.9375rem;
            line-height: 1.625;
            margin-bottom: 2rem;
        }
        .f1-table thead th {
            @apply text-[10px] uppercase tracking-[0.2em] pb-4 text-f1-red font-black;
        }
        .f1-table tbody td {
            @apply py-5 border-t border-white/5 text-sm;
        }
    </style>
</head>
<body class="bg-[#0b0b0f] bg-pattern min-h-screen flex flex-col italic selection:bg-f1-red">

    <?php include 'navigatie/header.php'; ?>

    <main class="max-w-4xl mx-auto px-6 py-16 flex-grow w-full">
        
        <div class="mb-16 text-center">
            <span class="text-f1-red font-black tracking-[0.3em] text-xs uppercase mb-4 block underline decoration-f1-red/30 underline-offset-8">Data Configuration</span>
            <h1 class="text-6xl md:text-8xl font-oswald font-black uppercase italic tracking-tighter leading-none mb-4">
                COOKIE<span class="text-f1-red">BELEID</span>
            </h1>
            <div class="h-1 w-24 bg-f1-red mx-auto mt-6"></div>
        </div>

        <div class="elite-card space-y-12">
            <section>
                <h2 class="policy-h2">1. Wat zijn cookies? <span class="red-speed-line"></span></h2>
                <p class="policy-p">
                    Cookies zijn kleine data-pakketjes die door je browser op je apparaat worden opgeslagen. Zie ze als de 'telemetrie' van je bezoek: ze helpen ons om de website aerodynamisch te laten functioneren en de snelheid van je gebruikerservaring te optimaliseren.
                </p>
            </section>

            <section>
                <h2 class="policy-h2">2. Telemetrie Data Type <span class="red-speed-line"></span></h2>
                <div class="overflow-x-auto mt-6 bg-black/20 rounded-2xl p-6 border border-white/5">
                    <table class="w-full text-left f1-table">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Doel</th>
                                <th class="text-right">Retentie</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-400">
                            <tr class="hover:bg-white/5 transition-colors group">
                                <td class="text-white font-bold italic uppercase tracking-tighter">Noodzakelijk</td>
                                <td>Cruciaal voor de veiligheid en basisfunctionaliteit.</td>
                                <td class="text-right font-mono text-f1-red">12 Maanden</td>
                            </tr>
                            <tr class="hover:bg-white/5 transition-colors group">
                                <td class="text-white font-bold italic uppercase tracking-tighter">Analyse</td>
                                <td>Anonieme data om onze content-snelheid te verbeteren.</td>
                                <td class="text-right font-mono text-f1-red">24 Maanden</td>
                            </tr>
                            <tr class="hover:bg-white/5 transition-colors group">
                                <td class="text-white font-bold italic uppercase tracking-tighter">Marketing</td>
                                <td>Gepersonaliseerde updates en relevante advertenties.</td>
                                <td class="text-right font-mono text-f1-red">12 Maanden</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <section>
                <h2 class="policy-h2">3. Pitstop: Voorkeuren <span class="red-speed-line"></span></h2>
                <p class="policy-p">
                    Wil je je instellingen resetten? Door je voorkeuren te verwijderen, wordt de cookie-banner bij je volgende bezoek opnieuw geactiveerd, zodat je je keuze kunt herzien.
                </p>
                <button onclick="clearPreferences()" class="group relative overflow-hidden bg-f1-red text-white py-4 px-8 rounded-xl font-black uppercase tracking-widest text-[11px] transition-all hover:scale-105 active:scale-95 shadow-lg shadow-f1-red/20">
                    <span class="relative z-10">Reset Mijn Voorkeuren</span>
                    <div class="absolute inset-0 bg-white/20 translate-y-full group-hover:translate-y-0 transition-transform duration-300"></div>
                </button>
            </section>

            <section class="mt-12 pt-8 border-t border-white/5">
                <p class="text-[10px] text-gray-600 uppercase font-black leading-loose italic tracking-widest">
                    Dit beleid is gesynchroniseerd met onze <a href="algemenevoorwaarden.html" class="text-gray-400 hover:text-f1-red">Algemene Voorwaarden</a>.
                </p>
            </section>
        </div>
    </main>

    <?php include 'navigatie/footer.php'; ?>

    <script>
        function clearPreferences() {
            document.cookie = "f1_consent=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
            alert('Settings Reset: De browser-cache is opgeschoond. De cookie-banner verschijnt bij je volgende bezoek.');            
            window.location.href = 'index.php';
        }
    </script>
</body>
</html>
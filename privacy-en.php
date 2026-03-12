<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy | F1SITE.NL</title>
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
    </style>
</head>
<body class="bg-[#0b0b0f] bg-pattern min-h-screen flex flex-col italic selection:bg-f1-red">
    <?php include 'navigatie/header.php'; ?>
    <main class="max-w-4xl mx-auto px-6 py-16 flex-grow w-full">
        <div class="mb-16 text-center">
            <span class="text-f1-red font-black tracking-[0.3em] text-xs uppercase mb-4 block underline decoration-f1-red/30 underline-offset-8">Data Safety</span>
            <h1 class="text-6xl md:text-8xl font-oswald font-black uppercase italic tracking-tighter leading-none mb-4">
                PRIVACY<span class="text-f1-red">POLICY</span>
            </h1>
            <div class="h-1 w-24 bg-f1-red mx-auto mt-6"></div>
        </div>
        <div class="elite-card space-y-12 shadow-2xl">
            <p class="text-lg text-white font-bold mb-10 border-b border-white/5 pb-8 italic leading-relaxed">
                This privacy policy describes how your personal data is collected, used, and shared by <span class="text-f1-red uppercase tracking-tighter italic">Webius</span>.
            </p>
            <section>
                <h2 class="policy-h2">01. Data Collected <span class="red-speed-line"></span></h2>
                <p class="policy-p"><strong class="text-white">Automatically Collected Data:</strong> IP address, browser type, and internet provider via cookies and log files for security and analytics.</p>
                <p class="policy-p"><strong class="text-white">Data You Provide:</strong> Name and email address provided voluntarily via contact forms.</p>
            </section>
            <section>
                <h2 class="policy-h2">02. Use of Your Data <span class="red-speed-line"></span></h2>
                <p class="policy-p">Your information is used to maintain and improve the Website, analyze behavior for optimization, and respond to your requests. We treat your data with the same precision as a qualifying lap.</p>
            </section>
            <section>
                <h2 class="policy-h2">03. Sharing Your Data <span class="red-speed-line"></span></h2>
                <p class="policy-p">We do not sell your data. Sharing only occurs with explicit permission, for hosting services, or when required by law to protect our rights or comply with a judicial proceeding.</p>
            </section>
            <section>
                <h2 class="policy-h2">04. Cookies <span class="red-speed-line"></span></h2>
                <p class="policy-p">We use cookies for functionality and anonymous statistics. You can refuse them via browser settings, though this may impact the "aerodynamics" of your browsing experience.</p>
            </section>
            <section>
                <h2 class="policy-h2">05. Your Rights (GDPR) <span class="red-speed-line"></span></h2>
                <ul class="space-y-4 mb-8 text-gray-400 text-sm italic">
                    <li class="flex items-center gap-4">
                        <span class="w-2 h-2 bg-f1-red rotate-45 shrink-0"></span> 
                        <p>Right to access, rectification, and data portability.</p>
                    </li>
                    <li class="flex items-center gap-4">
                        <span class="w-2 h-2 bg-f1-red rotate-45 shrink-0"></span> 
                        <p>Right to erasure and the right to object to processing.</p>
                    </li>
                    <li class="flex items-center gap-4">
                        <span class="w-2 h-2 bg-f1-red rotate-45 shrink-0"></span> 
                        <p>Right to withdraw consent at any time.</p>
                    </li>
                </ul>
            </section>
            <section>
                <h2 class="policy-h2">06. Changes <span class="red-speed-line"></span></h2>
                <p class="policy-p">This policy may be updated to reflect changes in our practices. Regular review of this page is recommended to stay up to speed.</p>
            </section>
            <section>
                <h2 class="policy-h2">07. Contact <span class="red-speed-line"></span></h2>
                <p class="policy-p">For questions or to exercise your rights, contact Webius via the contact form on our website or at the address specified in our legal information.</p>
            </section>
        </div>
        <div class="mt-12 text-center">
            <p class="text-gray-600 text-[10px] uppercase tracking-widest font-black italic">Last updated: <?php echo date('F d, Y'); ?></p>
        </div>
    </main>
    <?php include 'navigatie/footer.php'; ?>
</body>
</html>
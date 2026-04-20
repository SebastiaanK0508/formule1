<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms and Conditions | F1SITE.NL</title>
    
    <?php include 'navigatie/head.php'; ?>

    <style>
        .bg-pattern {
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.02'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
        .elite-card {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 2rem;
            padding: 2.5rem;
            backdrop-filter: blur(10px);
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
            font-size: 0.875rem;
            line-height: 1.625;
            margin-bottom: 2rem;
        }
    </style>
</head>
<body class="bg-[#0b0b0f] bg-pattern min-h-screen flex flex-col italic selection:bg-f1-red">

    <?php include 'navigatie/header.php'; ?>

    <main class="max-w-4xl mx-auto px-6 py-16 flex-grow w-full">
        
        <div class="mb-16 text-center">
            <span class="text-f1-red font-black tracking-[0.3em] text-xs uppercase mb-4 block">Legal Framework</span>
            <h1 class="text-5xl md:text-8xl font-oswald font-black uppercase italic tracking-tighter leading-none mb-4">
                TERMS & <span class="text-f1-red">CONDITIONS</span>
            </h1>
            <div class="h-1 w-24 bg-f1-red mx-auto"></div>
        </div>

        <div class="elite-card space-y-12">
            <section>
                <h2 class="policy-h2">1. Definitions <span class="red-speed-line"></span></h2>
                <ul class="space-y-4 text-gray-400 text-sm">
                    <li class="flex gap-3">
                        <span class="text-f1-red">/</span>
                        <p><strong class="text-white uppercase tracking-tighter">Website:</strong> The Formula 1 website owned by Webius.</p>
                    </li>
                    <li class="flex gap-3">
                        <span class="text-f1-red">/</span>
                        <p><strong class="text-white uppercase tracking-tighter">User:</strong> Anyone who visits and/or uses the Website.</p>
                    </li>
                    <li class="flex gap-3">
                        <span class="text-f1-red">/</span>
                        <p><strong class="text-white uppercase tracking-tighter">Services:</strong> All information, content, and functionalities offered.</p>
                    </li>
                    <li class="flex gap-3">
                        <span class="text-f1-red">/</span>
                        <p><strong class="text-white uppercase tracking-tighter">Webius:</strong> The owner and administrator of the Website.</p>
                    </li>
                </ul>
            </section>

            <section>
                <h2 class="policy-h2">2. Applicability <span class="red-speed-line"></span></h2>
                <p class="policy-p">By visiting and using the Website, the User agrees to these terms. Webius reserves the right to change these Terms and Conditions at any time. Continued use of the site following changes constitutes acceptance of those changes.</p>
            </section>

            <section>
                <h2 class="policy-h2">3. Use of the Website <span class="red-speed-line"></span></h2>
                <p class="policy-p">The information provided on F1SITE.NL is for general purposes only. Webius uses an external API which may cause data to be out of date or occasionally inaccurate. Users are not permitted to copy, distribute, or "scrape" content without explicit written permission.</p>
            </section>

            <section>
                <h2 class="policy-h2">4. Intellectual Property <span class="red-speed-line"></span></h2>
                <p class="policy-p">All rights related to texts, images, custom code, logos, and layout are the property of Webius. Personal, non-commercial use is permitted, provided no copyright notices are removed.</p>
            </section>

            <section>
                <h2 class="policy-h2">5. Liability <span class="red-speed-line"></span></h2>
                <p class="policy-p">Webius is not liable for damage resulting from the use of the Website or third-party links. While we strive for 100% uptime, uninterrupted operation is not guaranteed. We are not responsible for decisions made based on the data provided.</p>
            </section>

            <section>
                <h2 class="policy-h2">6. Privacy <span class="red-speed-line"></span></h2>
                <p class="policy-p">Processing of personal data is described in our <a href="privacy-en.php" class="text-f1-red hover:underline">Privacy Policy</a>. By using this site, you acknowledge and agree to our data handling practices.</p>
            </section>

            <section>
                <h2 class="policy-h2">7. Applicable Law <span class="red-speed-line"></span></h2>
                <p class="policy-p">These terms are subject to Dutch law. All disputes arising from or in connection with these terms will be submitted to the competent court in the Netherlands.</p>
            </section>
        </div>

        <div class="mt-12 text-center">
            <p class="text-gray-600 text-[10px] uppercase tracking-widest font-bold">Last updated: <?php echo date('F d, Y'); ?></p>
        </div>
    </main>

    <?php include 'navigatie/footer.php'; ?>

</body>
</html>
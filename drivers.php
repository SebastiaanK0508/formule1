<?php
require_once 'db_config.php';
/** @var PDO $pdo */

$allDrivers = []; 
try {
    $stmt = $pdo->query("SELECT d.driver_id, d.first_name, d.last_name, d.driver_number, d.flag_url, d.image, t.team_name, t.full_team_name, t.team_id, t.team_color 
                         FROM drivers d 
                         LEFT JOIN teams t ON d.team_id = t.team_id 
                         WHERE d.is_active = TRUE 
                         ORDER BY d.driver_number ASC");
    $allDrivers = $stmt->fetchAll();
} catch (\PDOException $e) {
    error_log("Fout bij het ophalen van alle coureurs: " . $e->getMessage());
}

// SEO Schema Data
$schemaData = [
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'CollectionPage',
            'url' => 'https://f1site.nl/drivers.php', 
            'name' => 'Formula 1 Drivers 2026',
            'about' => 'Official F1 Grid for the 2026 season.',
        ]
    ]
];
?>
<!DOCTYPE html>
<html lang="nl" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>F1 Drivers <?php echo date('Y'); ?> | F1SITE.NL</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;700;900&family=Oswald:wght@700&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { 'sans': ['Inter', 'sans-serif'], 'oswald': ['Oswald', 'sans-serif'] },
                    colors: { 'f1-red': '#E10600', 'f1-dark': '#0b0b0f', 'f1-card': '#16161c' }
                }
            }
        }
    </script>

    <style>
        body { background-color: #0b0b0f; color: #fff; overflow-x: hidden; }
        .bg-pattern {
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
        .f1-border { position: relative; }
        .f1-border::before { content: ""; position: absolute; top: 0; left: 0; width: 45px; height: 4px; background: #E10600; z-index: 20; }
        .driver-card { 
            display: flex;
            flex-direction: column; 
            transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1); 
            position: relative; 
            overflow: hidden;
            background-color: #16161c;
        }
        @media (min-width: 768px) {
            .driver-card { 
                flex-direction: row; 
                height: 320px; 
            }
        }
        .driver-card:hover { 
            transform: translateY(-5px); 
            border-color: rgba(225, 6, 0, 0.5);
        }
        .card-inner-content {
            flex: 1;
            padding: 2rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            z-index: 10;
        }
        .driver-image-wrapper {
            width: 100%; 
            height: 250px; 
            position: relative;
            background: linear-gradient(180deg, rgba(0,0,0,0) 0%, rgba(22,22,28,1) 100%);
            overflow: hidden;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        @media (min-width: 768px) {
            .driver-image-wrapper { 
                width: 42%; 
                height: 100%;
                background: none;
                border-bottom: none;
                border-left: 1px solid rgba(255,255,255,0.05);
            }
        }
        .driver-image-container {
            width: 100%;
            height: 100%;
            object-fit: contain;
            object-position: bottom center;
            transition: transform 0.6s ease;
        }
        .driver-card:hover .driver-image-container {
            transform: scale(1.05);
        }
        .card-link { position: absolute; inset: 0; z-index: 30; }
    </style>
    
    <script type="application/ld+json"><?php echo json_encode($schemaData); ?></script>
</head>
<body class="bg-pattern min-h-screen flex flex-col">

    <?php include 'navigatie/header.php'; ?>

    <main class="max-w-7xl mx-auto px-6 py-12 flex-grow">
        <section class="mb-12 md:mb-20 text-center md:text-left" data-aos="fade-down">
            <h2 class="text-4xl md:text-7xl font-oswald font-black uppercase italic tracking-tighter leading-none">
                F1 DRIVERS <span class="text-f1-red"><?php echo date('Y'); ?></span> 
            </h2>
            <div class="flex items-center justify-center md:justify-start gap-3 mt-4">
                <span class="w-10 h-[2px] bg-f1-red"></span>
                <p class="text-gray-500 text-[10px] font-black uppercase tracking-[0.3em]">The official 2026 grid line-up</p>
            </div>
        </section>
        <section class="grid grid-cols-1 lg:grid-cols-2 gap-8 md:gap-12">
            <?php if (!empty($allDrivers)): ?>
                <?php $i=0; foreach ($allDrivers as $driver): ?>
                    <?php
                        $fname = $driver['first_name'] ?? '';
                        $lname = $driver['last_name'] ?? 'Driver';
                        $driverSlug = strtolower(str_replace(' ', '-', htmlspecialchars($fname . '-' . $lname)));
                        $driverPageUrl = 'driver-details.php?slug=' . $driverSlug;
                        $teamColor = $driver['team_color'] ?? '#E10600';
                        $driverImg = $driver['image'] ?? 'assets/img/placeholder.png';
                        $teamName = $driver['full_team_name'] ?? $driver['team_name'] ?? 'F1 Team';
                    ?>
                    <article data-aos="fade-up" data-aos-delay="<?php echo $i*50; ?>" 
                             class="driver-card f1-border rounded-br-[2.5rem] border border-white/5 group">
                        
                        <a href="<?php echo $driverPageUrl; ?>" class="card-link" aria-label="Details <?php echo htmlspecialchars($lname); ?>"></a>

                        <div class="driver-image-wrapper order-1 md:order-2">
                            <img src="<?php echo htmlspecialchars($driverImg); ?>" 
                                 alt="<?php echo htmlspecialchars($lname); ?>" 
                                 class="driver-image-container">
                        </div>

                        <div class="card-inner-content order-2 md:order-1">
                            <div>
                                <div class="flex items-center gap-4 mb-4">
                                    <span class="text-5xl md:text-6xl font-oswald font-black italic" style="color: <?php echo $teamColor; ?>;">
                                        <?php echo str_pad(htmlspecialchars((string)($driver['driver_number'] ?? '00')), 2, "0", STR_PAD_LEFT); ?>
                                    </span>
                                    <?php if(!empty($driver['flag_url'])): ?>
                                        <img src="<?php echo htmlspecialchars($driver['flag_url']); ?>" alt="Flag" class="w-6 h-auto shadow-sm">
                                    <?php endif; ?>
                                </div>
                                
                                <div class="w-12 h-1 mb-4" style="background-color: <?php echo $teamColor; ?>;"></div>
                                <h3 class="text-lg md:text-xl font-medium text-gray-400 leading-none mb-1"><?php echo htmlspecialchars($fname); ?></h3>
                                <h4 class="text-3xl md:text-5xl font-oswald font-black uppercase italic tracking-tighter group-hover:text-f1-red transition-colors leading-none">
                                    <?php echo htmlspecialchars($lname); ?>
                                </h4>
                            </div>
                            <div class="mt-8 pt-6 border-t border-white/5">
                                <span class="text-[9px] font-black uppercase tracking-widest text-f1-red block mb-1">Constructor</span>
                                <span class="text-sm font-bold text-gray-300 italic truncate block">
                                    <?php echo htmlspecialchars($teamName); ?>
                                </span>
                            </div>
                        </div>
                    </article>
                <?php $i++; endforeach; ?>
            <?php endif; ?>
        </section>
    </main>

    <?php include 'navigatie/footer.php'; ?>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            AOS.init({ duration: 800, once: true });
        });
    </script> 
</body>
</html>
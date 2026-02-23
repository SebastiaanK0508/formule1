<?php
require_once 'db_config.php';
/** @var PDO $pdo */
$circuitKey = isset($_GET['key']) ? htmlspecialchars($_GET['key']) : null;
$circuitDetails = [];
$message = '';

if ($circuitKey) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM circuits WHERE circuit_key = :circuit_key");
        $stmt->bindParam(':circuit_key', $circuitKey);
        $stmt->execute();
        $circuitDetails = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$circuitDetails) {
            $message = "Circuit niet gevonden.";
        }
    } catch (\PDOException $e) {
        $message = "Fout bij het ophalen van data.";
    }
} else {
    $message = "Geen circuit geselecteerd.";
}

if (!is_array($circuitDetails)) { $circuitDetails = []; }
?>
<!DOCTYPE html>
<html lang="nl" class="scroll-smooth">
<head>
    <?php include 'navigatie/head.php'; ?>

    <style>
        .header-glass { background: rgba(11, 11, 15, 0.9); backdrop-filter: blur(15px); border-bottom: 1px solid rgba(225, 6, 0, 0.3); }
        .stat-card { background: rgba(22, 22, 28, 0.6); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.05); transition: all 0.3s ease; }
        .stat-card:hover { border-color: rgba(225, 6, 0, 0.4); transform: translateY(-2px); }
    </style>
</head>
<body class="bg-pattern">
    <?php include 'navigatie/header.php'; ?> 
    <main class="max-w-7xl mx-auto px-6 py-12">
        <?php if ($circuitDetails && $circuitKey): ?>
            <section class="mb-12" data-aos="fade-down">
                <div class="flex flex-col md:flex-row items-end justify-between gap-6">
                    <div>
                        <div class="flex items-center gap-3 mb-4">
                            <?php if (!empty($circuitDetails['country_flag_url'])): ?>
                                <img src="<?php echo htmlspecialchars($circuitDetails['country_flag_url']); ?>" class="w-10 h-auto shadow-lg" alt="Flag">
                            <?php endif; ?>
                            <span class="text-f1-red font-black text-xs uppercase tracking-[0.4em] italic">Circuit Profile</span>
                        </div>
                        <h1 class="text-5xl md:text-7xl font-oswald font-black uppercase italic tracking-tighter leading-none">
                            <?php echo htmlspecialchars($circuitDetails['grandprix']); ?>
                        </h1>
                        <p class="text-gray-400 text-xl mt-4 font-light uppercase tracking-widest"><?php echo htmlspecialchars($circuitDetails['location']); ?></p>
                    </div>
                    <div class="hidden md:block">
                        <a href="kalender.php" class="text-[10px] font-black uppercase tracking-[0.3em] text-gray-500 hover:text-white transition flex items-center gap-2">
                           <span class="text-f1-red text-xl">←</span> Back to schedule
                        </a>
                    </div>
                </div>
            </section>

            <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
                <div class="stat-card p-6 rounded-2xl f1-border" data-aos="fade-up" data-aos-delay="0">
                    <span class="text-[10px] font-black uppercase tracking-widest text-gray-500">First Grand Prix</span>
                    <p class="text-2xl font-oswald font-black italic mt-2"><?php echo htmlspecialchars($circuitDetails['first_gp_year'] ?? 'N/A'); ?></p>
                </div>
                <div class="stat-card p-6 rounded-2xl" data-aos="fade-up" data-aos-delay="100">
                    <span class="text-[10px] font-black uppercase tracking-widest text-gray-500">Number of Laps</span>
                    <p class="text-2xl font-oswald font-black italic mt-2"><?php echo htmlspecialchars($circuitDetails['lap_count'] ?? 'N/A'); ?></p>
                </div>
                <div class="stat-card p-6 rounded-2xl" data-aos="fade-up" data-aos-delay="200">
                    <span class="text-[10px] font-black uppercase tracking-widest text-gray-500">Circuit Length</span>
                    <p class="text-2xl font-oswald font-black italic mt-2"><?php echo number_format($circuitDetails['circuit_length_km'] ?? 0, 3, ',', '.'); ?> <span class="text-xs text-f1-red">KM</span></p>
                </div>
                <div class="stat-card p-6 rounded-2xl" data-aos="fade-up" data-aos-delay="300">
                    <span class="text-[10px] font-black uppercase tracking-widest text-gray-500">Race Distance</span>
                    <p class="text-2xl font-oswald font-black italic mt-2"><?php echo number_format($circuitDetails['race_distance_km'] ?? 0, 3, ',', '.'); ?> <span class="text-xs text-f1-red">KM</span></p>
                </div>
            </section>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-12 items-start mb-12">
                <div class="lg:col-span-2 order-2 lg:order-1" data-aos="zoom-in">
                    <div class="bg-f1-card p-8 md:p-12 rounded-[3rem] border border-white/5 relative overflow-hidden group">
                        <div class="absolute inset-0 bg-gradient-to-tr from-f1-red/5 to-transparent"></div>
                        <?php if (!empty($circuitDetails['map_url'])): ?>
                            <img src="<?php echo htmlspecialchars($circuitDetails['map_url']); ?>" class="relative z-10 w-full h-auto drop-shadow-[0_0_30px_rgba(0,0,0,0.5)] transform group-hover:scale-105 transition-transform duration-700" alt="Track Map">
                        <?php endif; ?>
                    </div>
                </div>

                <div class="order-1 lg:order-2 space-y-8" data-aos="fade-left">
                    <div class="bg-f1-card p-8 rounded-[2rem] border border-white/5">
                        <h3 class="text-xl font-oswald font-black uppercase italic mb-6 flex items-center gap-2">
                            <span class="w-2 h-2 bg-f1-red rounded-full"></span> Technical Specs
                        </h3>
                        <div class="space-y-6">
                            <div>
                                <span class="text-[9px] font-black uppercase tracking-widest text-gray-500 block mb-1">Official Name</span>
                                <p class="text-sm font-bold text-gray-200"><?php echo htmlspecialchars($circuitDetails['title'] ?? 'N/A'); ?></p>
                            </div>
                            <div>
                                <span class="text-[9px] font-black uppercase tracking-widest text-gray-500 block mb-1">Lap Record</span>
                                <p class="text-xl font-oswald font-black italic text-f1-red"><?php echo htmlspecialchars($circuitDetails['lap_record'] ?? 'N/A'); ?></p>
                            </div>
                            <div>
                                <span class="text-[9px] font-black uppercase tracking-widest text-gray-500 block mb-1">Race Start</span>
                                <p class="text-sm font-bold text-gray-200"><?php echo $circuitDetails['race_datetime'] ? date('d-m-Y | H:i', strtotime($circuitDetails['race_datetime'])) : 'N/A'; ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex flex-col gap-4">
                        <a href="kalender.php" class="w-full bg-f1-red py-5 rounded-2xl text-center font-black uppercase text-xs tracking-widest hover:bg-white hover:text-black transition-all duration-300">
                            Full Season Schedule
                        </a>
                    </div>
                </div>
            </div>

        <?php else: ?>
            <div class="py-20 text-center" data-aos="fade-up">
                <div class="text-6xl mb-6">⚠️</div>
                <h2 class="text-3xl font-oswald font-black uppercase italic italic tracking-tighter"><?php echo $message; ?></h2>
                <a href="kalender.php" class="inline-block mt-8 text-f1-red font-black uppercase tracking-widest border-b border-f1-red pb-1">Return to base</a>
            </div>
        <?php endif; ?>
        
    </main>

    <?php include 'navigatie/footer.php'; ?>
    
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            AOS.init({ duration: 800, once: true });
            window.toggleMenu = () => { document.getElementById('mobile-menu').classList.toggle('active'); };
        });
    </script>
</body>
</html>
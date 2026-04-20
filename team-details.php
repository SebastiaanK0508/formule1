<?php
require_once 'db_config.php';
/** @var PDO $pdo */
$teamId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($teamId === 0) { header('Location: teams.php'); exit; }

$team = null;
try {
    $stmt = $pdo->prepare("SELECT * FROM teams WHERE team_id = :id");
    $stmt->execute(['id' => $teamId]);
    $team = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$team) { header("Location: teams.php"); exit; }
} catch (PDOException $e) { exit("Databasefout."); }

$teamDrivers = [];
$teamColor = htmlspecialchars($team['team_color'] ?? '#E10600');
list($r, $g, $b) = sscanf($teamColor, "#%02x%02x%02x");

try {
    $stmtDrivers = $pdo->prepare("SELECT driver_id, first_name, last_name, driver_number, image FROM drivers WHERE team_id = :team_id AND is_active = TRUE ORDER BY driver_number ASC");
    $stmtDrivers->execute(['team_id' => $teamId]);
    $teamDrivers = $stmtDrivers->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) { error_log($e->getMessage()); }
?>
<!DOCTYPE html>
<html lang="nl" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($team['full_team_name']); ?> | F1SITE.NL</title>
    <?php include 'navigatie/head.php'; ?>
    <style>
        :root { --team-color: <?php echo $teamColor; ?>; --team-rgb: <?php echo "$r, $g, $b"; ?>; }
        .hero-title { font-size: clamp(3rem, 8vw, 7rem); line-height: 0.85; }
        .bg-blur-dot { position: absolute; width: 40vw; height: 40vw; background: rgba(var(--team-rgb), 0.15); filter: blur(120px); border-radius: 50%; z-index: -1; }
        .stat-card { background: linear-gradient(145deg, rgba(255,255,255,0.03) 0%, rgba(255,255,255,0.01) 100%); transition: all 0.4s ease; }
        .stat-card:hover { border-color: var(--team-color); transform: translateY(-5px); }
        .diagonal-bg { clip-path: polygon(0 0, 100% 0, 100% 85%, 0% 100%); background: #16161c; height: 60vh; width: 100%; position: absolute; top: 0; left: 0; z-index: -2; }
        @keyframes float { 0%, 100% { transform: translateY(0) rotate(0deg); } 50% { transform: translateY(-15px) rotate(2deg); } }
        .animate-float { animation: float 8s ease-in-out infinite; }
    </style>
</head>
<body class="bg-pattern bg-[var(--team-color)]text-white italic selection:bg-f1-red selection:text-white">
    <?php include 'navigatie/header.php'; ?>

    <main class="relative min-h-screen">
        <div class="diagonal-bg"></div>
        <div class="bg-blur-dot top-0 -right-20"></div>

        <div class="max-w-7xl mx-auto px-6 py-12 lg:py-24 relative z-10">
            
            <section class="flex flex-col lg:flex-row items-end gap-16 mb-24" data-aos="fade-up">
                <div class="lg:w-2/3">
                    <div class="flex items-center gap-4 mb-8">
                        <span class="px-4 py-1 bg-f1-red text-white text-[10px] font-black uppercase tracking-widest italic rounded-sm">Constructor</span>
                        <span class="text-gray-500 font-bold uppercase tracking-widest text-[10px]">Active Grid <?php echo date('Y'); ?></span>
                    </div>
                    
                    <h1 class="hero-title font-oswald font-black uppercase italic tracking-tighter mb-6">
                        <?php echo htmlspecialchars($team['team_name']); ?>
                    </h1>
                    
                    <p class="text-2xl md:text-3xl font-oswald text-gray-400 uppercase italic tracking-tight mb-10 max-w-2xl leading-none">
                        <?php echo htmlspecialchars($team['full_team_name']); ?>
                    </p>

                    <div class="flex flex-wrap gap-8 py-8 border-y border-white/5">
                        <div class="flex flex-col">
                            <span class="text-f1-red font-black uppercase text-[10px] tracking-widest mb-1">Base of Operations</span>
                            <span class="text-lg font-bold uppercase"><?php echo htmlspecialchars($team['base_location']); ?></span>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-f1-red font-black uppercase text-[10px] tracking-widest mb-1">Power Unit</span>
                            <span class="text-lg font-bold uppercase"><?php echo htmlspecialchars($team['current_engine_supplier']); ?></span>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-f1-red font-black uppercase text-[10px] tracking-widest mb-1">Championships</span>
                            <span class="text-lg font-bold uppercase"><?php echo htmlspecialchars($team['championships_won'] ?? '0'); ?> Titles</span>
                        </div>
                    </div>
                </div>

                <div class="lg:w-1/3 flex justify-center lg:justify-end w-full">
                    <?php if (!empty($team['logo_url'])): ?>
                    <div class="relative group">
                        <div class="absolute inset-0 bg-[var(--team-color)] opacity-20 blur-3xl rounded-full animate-pulse"></div>
                        <img src="<?php echo htmlspecialchars($team['logo_url']); ?>" alt="Logo" class="max-h-64 object-contain animate-float relative z-10 drop-shadow-2xl">
                    </div>
                    <?php endif; ?>
                </div>
            </section>

            <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-32">
                <div class="stat-card p-6 rounded-2xl border border-white/5" data-aos="fade-up" data-aos-delay="100">
                    <h4 class="text-gray-500 text-[10px] font-black uppercase tracking-widest mb-4">Team Leadership</h4>
                    <p class="text-xs text-f1-red font-bold uppercase mb-1">Principal</p>
                    <p class="text-xl font-oswald font-black italic uppercase mb-4"><?php echo htmlspecialchars($team['team_principal']); ?></p>
                    <p class="text-xs text-f1-red font-bold uppercase mb-1">Tech Director</p>
                    <p class="text-xl font-oswald font-black italic uppercase"><?php echo htmlspecialchars($team['technical_director']); ?></p>
                </div>

                <div class="stat-card p-6 rounded-2xl border border-white/5" data-aos="fade-up" data-aos-delay="200">
                    <h4 class="text-gray-500 text-[10px] font-black uppercase tracking-widest mb-4">Engineering</h4>
                    <p class="text-xs text-f1-red font-bold uppercase mb-1">Chassis</p>
                    <p class="text-xl font-oswald font-black italic uppercase mb-4"><?php echo htmlspecialchars($team['chassis']); ?></p>
                    <p class="text-xs text-f1-red font-bold uppercase mb-1">Supplier</p>
                    <p class="text-xl font-oswald font-black italic uppercase"><?php echo htmlspecialchars($team['current_engine_supplier']); ?></p>
                </div>

                <div class="stat-card p-6 rounded-2xl border border-white/5 bg-gradient-to-br from-f1-red/10 to-transparent" data-aos="fade-up" data-aos-delay="300">
                    <h4 class="text-gray-500 text-[10px] font-black uppercase tracking-widest mb-4">Performance</h4>
                    <div class="flex items-end gap-2">
                        <p class="text-5xl font-oswald font-black italic uppercase leading-none"><?php echo htmlspecialchars($team['total_victories'] ?? '0'); ?></p>
                        <p class="text-[10px] font-black uppercase text-f1-red pb-1">Grand Prix Wins</p>
                    </div>
                    <div class="mt-6 h-1 w-full bg-white/5 rounded-full overflow-hidden">
                        <div class="h-full bg-f1-red" style="width: 75%"></div>
                    </div>
                </div>
            </section>

            <section class="mb-20">
                <div class="flex items-center justify-between mb-16">
                    <h2 class="text-4xl md:text-6xl font-oswald font-black uppercase italic tracking-tighter">
                        ACTIVE <span class="text-f1-red">LINEUP</span>
                    </h2>
                    <span class="h-[2px] flex-grow mx-10 bg-white/5 hidden md:block"></span>
                    <span class="text-[10px] font-black uppercase tracking-[0.4em] text-gray-500">Season <?php echo date('Y'); ?></span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
                    <?php foreach ($teamDrivers as $driver): 
                        $driverSlug = strtolower(str_replace(' ', '-', $driver['first_name'] . '-' . $driver['last_name']));
                    ?>
                    <a href="driver-details.php?slug=<?php echo $driverSlug; ?>" 
                       class="group relative bg-[#16161c] rounded-3xl overflow-hidden border border-white/5 transition-all duration-500 hover:border-f1-red/50">
                        
                        <div class="flex flex-col sm:flex-row h-full">
                            <div class="sm:w-1/2 h-80 sm:h-[450px] overflow-hidden bg-black relative">
                                <img src="<?php echo htmlspecialchars($driver['image'] ?: '/afbeeldingen/coureurs/default.jpg'); ?>" 
                                     alt="Driver" 
                                     class="w-full h-full object-cover object-top grayscale group-hover:grayscale-0 transition-all duration-1000 group-hover:scale-105">
                                <div class="absolute inset-0 bg-gradient-to-r from-transparent to-[#16161c] hidden sm:block"></div>
                                <div class="absolute inset-0 bg-gradient-to-t from-[#16161c] to-transparent sm:hidden"></div>
                            </div>
                            
                            <div class="sm:w-1/2 p-10 flex flex-col justify-between relative overflow-hidden">
                                <div class="absolute -right-4 -top-4 text-[12rem] font-black italic opacity-[0.03] text-white group-hover:opacity-[0.08] transition-opacity">
                                    <?php echo htmlspecialchars($driver['driver_number']); ?>
                                </div>

                                <div class="relative z-10">
                                    <span class="text-f1-red font-black text-4xl italic font-oswald mb-2 block">#<?php echo htmlspecialchars($driver['driver_number']); ?></span>
                                    <h3 class="text-gray-500 font-bold uppercase tracking-widest text-xs mb-1"><?php echo htmlspecialchars($driver['first_name']); ?></h3>
                                    <h4 class="text-4xl font-oswald font-black uppercase italic tracking-tight group-hover:text-white transition-colors">
                                        <?php echo htmlspecialchars($driver['last_name']); ?>
                                    </h4>
                                </div>

                                <div class="mt-8 flex items-center gap-4 text-[10px] font-black uppercase tracking-[0.3em] group-hover:text-f1-red transition-colors">
                                    <span>Bekijk Statistieken</span>
                                    <div class="h-1 w-8 bg-white/10 group-hover:bg-f1-red transition-all group-hover:w-16"></div>
                                </div>
                            </div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            </section>

            <div class="text-center pt-20 border-t border-white/5">
                <a href="teams.php" class="inline-flex items-center gap-6 group">
                    <span class="text-[10px] font-black uppercase tracking-[0.4em] text-gray-500 group-hover:text-white transition-colors">Back to all constructors</span>
                    <div class="w-12 h-12 rounded-full border border-white/10 flex items-center justify-center group-hover:bg-white group-hover:text-black transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </div>
                </a>
            </div>
        </div>
    </main>

    <?php include 'navigatie/footer.php'; ?>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            AOS.init({ duration: 1000, once: true });
        });
    </script>
</body>
</html>
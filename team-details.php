<?php
require_once 'db_config.php';
/** @var PDO $pdo */
$teamId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($teamId === 0) {
    header('Location: teams.php'); 
    exit;
}

$team = null;
try {
    $stmt = $pdo->prepare("SELECT * FROM teams WHERE team_id = :id");
    $stmt->bindParam(':id', $teamId, PDO::PARAM_INT);
    $stmt->execute();
    $team = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$team) {
        http_response_code(404);
        echo "<div style='background:#0b0b0f; color:white; height:100vh; display:flex; align-items:center; justify-content:center; font-family:sans-serif;'>Team niet gevonden. <a href='teams.php' style='color:#E10600; margin-left:10px;'>Terug naar overzicht</a></div>";
        exit;
    }
} catch (PDOException $e) {
    http_response_code(500);
    exit("Databasefout.");
}

$teamDrivers = [];
$teamColor = htmlspecialchars($team['team_color'] ?? '#E10600');

try {
    $stmtDrivers = $pdo->prepare("SELECT driver_id, first_name, last_name, driver_number, image FROM drivers WHERE team_id = :team_id AND is_active = TRUE ORDER BY driver_number ASC");
    $stmtDrivers->bindParam(':team_id', $teamId, PDO::PARAM_INT);
    $stmtDrivers->execute();
    $teamDrivers = $stmtDrivers->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log($e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="nl" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($team['full_team_name']); ?> | F1SITE.NL</title>
    <?php include 'navigatie/head.php'; ?>
    <style>
        .team-border { border-left: 4px solid <?php echo $teamColor; ?>; }
        .team-gradient { background: linear-gradient(135deg, <?php echo $teamColor; ?>22 0%, transparent 100%); }        
        .driver-image-container::after {
            content: ""; position: absolute; bottom: 0; left: 0; right: 0; height: 50%;
            background: linear-gradient(to top, #16161c, transparent);
        }
    </style>
</head>
<body class="bg-pattern">
    <?php include 'navigatie/header.php'; ?>
    <main class="max-w-7xl mx-auto px-6 py-12">
        <section class="mb-16" data-aos="fade-down">
            <div class="flex flex-col lg:flex-row items-center gap-12 bg-f1-card/50 rounded-[3rem] p-8 md:p-12 border border-white/5 team-gradient relative overflow-hidden">
                <div class="flex-1 z-10 text-center lg:text-left">
                    <span class="text-xs font-black uppercase tracking-[0.5em] mb-4 block" style="color: <?php echo $teamColor; ?>;">Official Constructor</span>
                    <h1 class="text-5xl md:text-7xl font-oswald font-black uppercase italic tracking-tighter leading-none mb-6">
                        <?php echo htmlspecialchars($team['full_team_name']); ?>
                    </h1>
                    <div class="flex flex-wrap justify-center lg:justify-start gap-4">
                        <div class="px-6 py-2 bg-black/40 rounded-full border border-white/10 text-[10px] font-black uppercase tracking-widest">
                            Base: <?php echo htmlspecialchars($team['base_location']); ?>
                        </div>
                        <div class="px-6 py-2 bg-black/40 rounded-full border border-white/10 text-[10px] font-black uppercase tracking-widest">
                            Power: <?php echo htmlspecialchars($team['current_engine_supplier']); ?>
                        </div>
                    </div>
                </div>
                <?php if (!empty($team['logo_url'])): ?>
                <div class="flex-shrink-0 z-10">
                    <img src="<?php echo htmlspecialchars($team['logo_url']); ?>" alt="Team Logo" class="max-h-48 md:max-h-64 object-contain drop-shadow-[0_0_30px_rgba(255,255,255,0.1)]">
                </div>
                <?php endif; ?>
            </div>
        </section>

        <section class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-16">
            <div class="bg-f1-card p-8 rounded-3xl border border-white/5 team-border" data-aos="fade-up" data-aos-delay="0">
                <h4 class="text-[10px] font-black uppercase tracking-[0.3em] text-gray-500 mb-4">Leadership</h4>
                <div class="space-y-4">
                    <div>
                        <span class="text-[9px] font-bold text-f1-red uppercase">Team Principal</span>
                        <p class="text-lg font-oswald font-black italic uppercase"><?php echo htmlspecialchars($team['team_principal']); ?></p>
                    </div>
                    <div>
                        <span class="text-[9px] font-bold text-f1-red uppercase">Technical Director</span>
                        <p class="text-lg font-oswald font-black italic uppercase"><?php echo htmlspecialchars($team['technical_director']); ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-f1-card p-8 rounded-3xl border border-white/5 team-border" data-aos="fade-up" data-aos-delay="100">
                <h4 class="text-[10px] font-black uppercase tracking-[0.3em] text-gray-500 mb-4">Car Stats</h4>
                <div class="space-y-4">
                    <div>
                        <span class="text-[9px] font-bold text-f1-red uppercase">Chassis</span>
                        <p class="text-lg font-oswald font-black italic uppercase"><?php echo htmlspecialchars($team['chassis']); ?></p>
                    </div>
                    <div>
                        <span class="text-[9px] font-bold text-f1-red uppercase">Engine</span>
                        <p class="text-lg font-oswald font-black italic uppercase"><?php echo htmlspecialchars($team['current_engine_supplier']); ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-f1-card p-8 rounded-3xl border border-white/5 team-border" data-aos="fade-up" data-aos-delay="200">
                <h4 class="text-[10px] font-black uppercase tracking-[0.3em] text-gray-500 mb-4">History</h4>
                <div class="space-y-4">
                    <div>
                        <span class="text-[9px] font-bold text-f1-red uppercase">World Titles</span>
                        <p class="text-3xl font-oswald font-black italic uppercase"><?php echo htmlspecialchars($team['championships_won'] ?? '0'); ?></p>
                    </div>
                    <div>
                        <span class="text-[9px] font-bold text-f1-red uppercase">Total Wins</span>
                        <p class="text-3xl font-oswald font-black italic uppercase text-f1-red"><?php echo htmlspecialchars($team['total_victories'] ?? '0'); ?></p>
                    </div>
                </div>
            </div>
        </section>

        <?php if (!empty($team['description'])): ?>
        <section class="mb-20" data-aos="fade-up">
            <div class="max-w-4xl">
                <h2 class="text-2xl font-oswald font-black uppercase italic italic tracking-tighter mb-6 flex items-center gap-4">
                    <span class="w-12 h-[2px] bg-f1-red"></span> The Constructor Story
                </h2>
                <p class="text-gray-400 leading-loose font-medium first-letter:text-5xl first-letter:font-oswald first-letter:font-black first-letter:text-f1-red first-letter:mr-3 first-letter:float-left uppercase text-xs tracking-wider">
                    <?php echo nl2br(htmlspecialchars($team['description'])); ?>
                </p>
            </div>
        </section>
        <?php endif; ?>

        <section class="mb-20">
            <h2 class="text-3xl font-oswald font-black uppercase italic tracking-tighter mb-10 text-center">
                ACTIVE <span class="text-f1-red">LINEUP</span>
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                <?php foreach ($teamDrivers as $driver): 
                    $driverSlug = strtolower(str_replace(' ', '-', $driver['first_name'] . '-' . $driver['last_name']));
                    $driverPageUrl = 'driver-details.php?slug=' . $driverSlug;
                ?>
                <a href="<?php echo htmlspecialchars($driverPageUrl); ?>" 
                   class="group bg-f1-card rounded-[2.5rem] overflow-hidden border border-white/5 flex flex-col md:flex-row hover:border-white/20 transition-all duration-500" data-aos="zoom-in">
                    
                    <div class="w-full md:w-1/2 h-72 md:h-96 relative overflow-hidden">
                        <img src="<?php echo htmlspecialchars($driver['image'] ?: '/afbeeldingen/coureurs/default.jpg'); ?>" 
                             alt="Driver" 
                             class="w-full h-full object-cover grayscale group-hover:grayscale-0 group-hover:scale-110 transition-all duration-700 object-top">
                        <div class="absolute inset-0 bg-gradient-to-t from-f1-card via-transparent to-transparent opacity-60"></div>
                    </div>
                    
                    <div class="w-full md:w-1/2 p-8 flex flex-col justify-center relative bg-f1-card">
                        <div class="absolute top-4 right-8 text-5xl font-oswald font-black italic opacity-10" style="color: <?php echo $teamColor; ?>;">
                            #<?php echo htmlspecialchars($driver['driver_number']); ?>
                        </div>
                        
                        <div class="relative z-10">
                            <h3 class="text-sm font-bold text-gray-400 uppercase tracking-widest leading-none mb-1">
                                <?php echo htmlspecialchars($driver['first_name']); ?>
                            </h3>
                            <h4 class="text-3xl font-oswald font-black uppercase italic tracking-tight group-hover:text-f1-red transition-colors">
                                <?php echo htmlspecialchars($driver['last_name']); ?>
                            </h4>
                            
                            <div class="mt-6 flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.2em] text-f1-red">
                                <span>View Profile</span>
                                <span class="group-hover:translate-x-2 transition-transform duration-300">→</span>
                            </div>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </section>
        <div class="text-center">
            <a href="teams.php" class="inline-block bg-white text-black px-12 py-5 rounded-full font-black uppercase text-[10px] tracking-[0.3em] hover:bg-f1-red hover:text-white transition-all duration-300">
                Back to Constructor list
            </a>
        </div>

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
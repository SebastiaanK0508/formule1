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
        .header-glass { background: rgba(11, 11, 15, 0.9); backdrop-filter: blur(15px); border-bottom: 1px solid rgba(225, 6, 0, 0.3); }
        .team-border { border-left: 4px solid <?php echo $teamColor; ?>; }
        .team-gradient { background: linear-gradient(135deg, <?php echo $teamColor; ?>22 0%, transparent 100%); }
        
        #mobile-menu { transform: translateX(100%); transition: transform 0.4s ease-in-out; background: #0b0b0f; z-index: 101; }
        #mobile-menu.active { transform: translateX(0); }
        
        .driver-image-container::after {
            content: ""; position: absolute; bottom: 0; left: 0; right: 0; height: 50%;
            background: linear-gradient(to top, #16161c, transparent);
        }
    </style>
</head>
<body class="bg-pattern">

<div id="mobile-menu" class="fixed inset-y-0 right-0 w-full p-10 flex flex-col items-center justify-center">
        <button onclick="toggleMenu()" class="absolute top-8 right-8 text-5xl font-light">&times;</button>
        <nav class="flex flex-col space-y-10 text-4xl font-oswald font-black uppercase italic text-center">
            <a href="index.php" onclick="toggleMenu()">Home</a>
            <a href="kalender.php" onclick="toggleMenu()">Schedule</a>
            <a href="teams.php" class="text-f1-red" onclick="toggleMenu()">Teams</a>
            <a href="drivers.php" onclick="toggleMenu()">Drivers</a>
            <a href="results.php" onclick="toggleMenu()">Results</a>
            <a href="standings.php" onclick="toggleMenu()">Standings</a>
        </nav>
    </div>

    <header class="header-glass sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-6 py-5 flex justify-between items-center">
            <a href="index.php" class="text-3xl font-oswald font-black italic tracking-tighter text-white uppercase">F1SITE<span class="text-f1-red">.NL</span></a>
            <nav class="hidden lg:flex space-x-10 text-[11px] font-bold uppercase tracking-[0.25em]">
                <a href="index.php" class="hover:text-f1-red transition">Home</a>
                <a href="kalender.php" class="hover:text-f1-red transition">Schedule</a>
                <a href="teams.php" class="text-f1-red border-b-2 border-f1-red pb-1">Teams</a>
                <a href="drivers.php" class="hover:text-f1-red transition">Drivers</a>
                <a href="results.php" class="hover:text-f1-red transition">Results</a>
                <a href="standings.php" class="hover:text-f1-red transition">Standings</a>
            </nav>
            <button onclick="toggleMenu()" class="lg:hidden text-white text-3xl">☰</button>
        </div>
    </header>

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
            <h2 class="text-3xl font-oswald font-black uppercase italic italic tracking-tighter mb-10 text-center">
                ACTIVE <span class="text-f1-red">LINEUP</span>
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                <?php foreach ($teamDrivers as $driver): 
                    $driverSlug = strtolower(str_replace(' ', '-', $driver['first_name'] . '-' . $driver['last_name']));
                    $driverPageUrl = 'driver-details.php?slug=' . $driverSlug;
                ?>
                <a href="<?php echo htmlspecialchars($driverPageUrl); ?>" 
                   class="group bg-f1-card rounded-[2.5rem] overflow-hidden border border-white/5 flex flex-col sm:flex-row items-center hover:border-white/20 transition-all duration-500" data-aos="zoom-in">
                    
                    <div class="w-full sm:w-1/2 relative driver-image-container overflow-hidden h-64 sm:h-auto self-stretch">
                        <img src="<?php echo htmlspecialchars($driver['image'] ?: '/afbeeldingen/coureurs/default.jpg'); ?>" 
                             alt="Driver" class="w-full h-full object-cover grayscale group-hover:grayscale-0 group-hover:scale-110 transition-all duration-700">
                    </div>
                    
                    <div class="w-full sm:w-1/2 p-10 text-center sm:text-left">
                        <span class="text-5xl font-oswald font-black italic opacity-20" style="color: <?php echo $teamColor; ?>;">
                            #<?php echo htmlspecialchars($driver['driver_number']); ?>
                        </span>
                        <h3 class="text-lg font-bold text-gray-400 mt-4 leading-none"><?php echo htmlspecialchars($driver['first_name']); ?></h3>
                        <h4 class="text-3xl font-oswald font-black uppercase italic tracking-tight group-hover:text-f1-red transition-colors">
                            <?php echo htmlspecialchars($driver['last_name']); ?>
                        </h4>
                        <div class="mt-6 inline-flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.2em] text-f1-red">
                            Profile <span class="group-hover:translate-x-2 transition-transform">→</span>
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

    <footer class="bg-black mt-24 py-16 border-t-2 border-f1-red">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-12 text-left pb-12 border-b border-white/5">
                <div class="space-y-4 text-center md:text-left">
                    <h3 class="text-2xl font-oswald font-black text-white italic tracking-tighter uppercase">F1SITE<span class="text-f1-red">.NL</span></h3>
                    <p class="text-gray-500 text-sm font-medium leading-relaxed max-w-xs mx-auto md:mx-0">De snelste bron voor Formule 1 nieuws, statistieken en constructor data.</p>
                </div>
                <div class="text-center md:text-left">
                    <h4 class="text-xs font-black text-f1-red mb-6 uppercase tracking-[0.3em]">Developer</h4>
                    <p class="text-gray-400 text-sm font-bold uppercase">WEBIUS</p>
                </div>
                <div class="text-center md:text-left">
                    <h4 class="text-xs font-black text-f1-red mb-6 uppercase tracking-[0.3em]">Legal</h4>
                    <ul class="space-y-4">
                        <li><a href="privacy-en.html" class="text-gray-400 text-sm font-bold hover:text-white transition duration-200 block uppercase tracking-wider">Privacy Policy</a></li>
                    </ul>
                </div>
            </div>
            <p class="pt-10 text-gray-600 text-[10px] font-black uppercase tracking-[0.5em] italic text-center md:text-left italic">&copy; 2026 WEBIUS.</p>
        </div>
    </footer>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            AOS.init({ duration: 800, once: true });
            window.toggleMenu = () => { document.getElementById('mobile-menu').classList.toggle('active'); };
        });
    </script>
</body>
</html>
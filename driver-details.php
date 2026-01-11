<?php
require_once 'db_config.php';
/** @var PDO $pdo */
$driverSlug = isset($_GET['slug']) ? $_GET['slug'] : '';
if (empty($driverSlug)) {
    header('Location: index.php');
    exit;
}

$driver = null;
try {
    $stmt = $pdo->prepare("
        SELECT d.*, t.team_color, t.team_name, t.full_team_name
        FROM drivers d
        JOIN teams t ON d.team_id = t.team_id
        WHERE LOWER(REPLACE(CONCAT(d.first_name, '-', d.last_name), ' ', '')) = :slug
    ");
    $stmt->bindParam(':slug', $driverSlug);
    $stmt->execute();
    $driver = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$driver) {
        http_response_code(404);
        exit("Coureur niet gevonden.");
    }
} catch (PDOException $e) {
    http_response_code(500);
    exit("Databasefout.");
}

$teamColor = htmlspecialchars($driver['team_color'] ?? '#E10600');
$driverFirstName = htmlspecialchars($driver['first_name']);
$driverLastName = htmlspecialchars($driver['last_name']);
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $driverFirstName . ' ' . $driverLastName; ?> | Driver Profile</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;700;900&family=Oswald:wght@700&display=swap" rel="stylesheet">
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
        body { background-color: #0b0b0f; color: #fff; }
        .bg-pattern {
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
        .header-glass { background: rgba(11, 11, 15, 0.9); backdrop-filter: blur(15px); border-bottom: 1px solid rgba(225, 6, 0, 0.3); }
        .driver-glow { 
            position: absolute; top: 0; left: 0; width: 100%; height: 100%; 
            background: radial-gradient(circle at center, <?php echo $teamColor; ?>22 0%, transparent 70%);
            pointer-events: none;
        }
    </style>
</head>
<body class="bg-pattern min-h-screen flex flex-col">

    <div id="mobile-menu" class="fixed inset-y-0 right-0 w-full p-10 flex flex-col items-center justify-center">
        <button onclick="toggleMenu()" class="absolute top-8 right-8 text-5xl font-light">&times;</button>
        <nav class="flex flex-col space-y-10 text-4xl font-oswald font-black uppercase italic text-center">
            <a href="index.php" onclick="toggleMenu()">Home</a>
            <a href="kalender.php" onclick="toggleMenu()">Schedule</a>
            <a href="teams.php" onclick="toggleMenu()">Teams</a>
            <a href="drivers.php" class="text-f1-red" onclick="toggleMenu()">Drivers</a>
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
                <a href="teams.php" class="hover:text-f1-red transition">Teams</a>
                <a href="drivers.php" class="text-f1-red border-b-2 border-f1-red pb-1">Drivers</a>
                <a href="results.php" class="hover:text-f1-red transition">Results</a>
                <a href="standings.php" class="hover:text-f1-red transition">Standings</a>
            </nav>
            <button onclick="toggleMenu()" class="lg:hidden text-white text-3xl">☰</button>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-6 py-12 flex-grow">
        
        <div class="flex flex-col lg:flex-row gap-12 items-start">
            
            <div class="w-full lg:w-5/12 sticky top-32">
                <div class="relative group">
                    <div class="driver-glow"></div>
                    <?php if (!empty($driver['image'])): ?>
                        <img src="<?php echo htmlspecialchars($driver['image']); ?>" 
                             alt="<?php echo $driverFirstName; ?>" 
                             class="w-full h-auto rounded-[2rem] shadow-[0_0_50px_rgba(0,0,0,0.8)] border border-white/10 relative z-10">
                    <?php endif; ?>
                    
                    <div class="absolute -bottom-10 -right-4 text-[12rem] font-oswald font-black italic opacity-10 pointer-events-none select-none z-0" style="color: <?php echo $teamColor; ?>;">
                        <?php echo htmlspecialchars($driver['driver_number']); ?>
                    </div>
                </div>
            </div>

            <div class="w-full lg:w-7/12">
                <div class="mb-10">
                    <div class="flex items-center gap-4 mb-4">
                        <span class="h-[2px] w-12 bg-f1-red"></span>
                        <span class="text-xs font-black uppercase tracking-[0.4em] text-gray-400 italic">
                            <?php echo htmlspecialchars($driver['full_team_name']); ?>
                        </span>
                    </div>
                    <h1 class="text-6xl md:text-8xl font-oswald font-black uppercase italic tracking-tighter leading-[0.85] mb-6">
                        <?php echo $driverFirstName; ?><br>
                        <span class="text-f1-red"><?php echo $driverLastName; ?></span>
                    </h1>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-10">
                    <div class="bg-f1-card p-8 rounded-3xl border border-white/5 relative overflow-hidden group">
                        <div class="absolute top-0 left-0 w-1 h-full" style="background: <?php echo $teamColor; ?>;"></div>
                        <span class="text-[10px] font-black uppercase tracking-widest text-gray-500">Number</span>
                        <p class="text-4xl font-oswald font-black italic mt-2">#<?php echo htmlspecialchars($driver['driver_number']); ?></p>
                    </div>

                    <div class="bg-f1-card p-8 rounded-3xl border border-white/5 relative overflow-hidden">
                        <div class="absolute top-0 left-0 w-1 h-full" style="background: <?php echo $teamColor; ?>;"></div>
                        <span class="text-[10px] font-black uppercase tracking-widest text-gray-500">Championships</span>
                        <p class="text-4xl font-oswald font-black italic mt-2"><?php echo htmlspecialchars($driver['championships_won'] ?? '0'); ?></p>
                    </div>

                    <div class="bg-f1-card p-8 rounded-3xl border border-white/5">
                        <span class="text-[10px] font-black uppercase tracking-widest text-gray-500">Nationality</span>
                        <div class="flex items-center gap-3 mt-3">
                            <?php if (!empty($driver['flag_url'])): ?>
                                <img src="<?php echo htmlspecialchars($driver['flag_url']); ?>" class="w-8 h-auto rounded shadow-sm" alt="Flag">
                            <?php endif; ?>
                            <p class="text-xl font-bold uppercase tracking-tight"><?php echo htmlspecialchars($driver['nationality']); ?></p>
                        </div>
                    </div>

                    <div class="bg-f1-card p-8 rounded-3xl border border-white/5">
                        <span class="text-[10px] font-black uppercase tracking-widest text-gray-500">Career Points</span>
                        <p class="text-3xl font-oswald font-black italic mt-2 text-f1-red">
                            <?php echo number_format($driver['career_points'] ?? 0, 1, ',', '.'); ?>
                        </p>
                    </div>
                </div>

                <?php if (!empty($driver['description'])): ?>
                <div class="bg-white/5 p-10 rounded-[2.5rem] border border-white/5 relative mb-10">
                    <h3 class="text-xs font-black uppercase tracking-[0.4em] text-f1-red mb-6 italic">The Biography</h3>
                    <p class="text-gray-400 leading-relaxed text-sm font-medium italic">
                        <?php echo nl2br(htmlspecialchars($driver['description'])); ?>
                    </p>
                </div>
                <?php endif; ?>

                <a href="drivers.php" class="inline-flex items-center gap-4 bg-white text-black px-10 py-5 rounded-full font-black uppercase text-[10px] tracking-[0.3em] hover:bg-f1-red hover:text-white transition-all duration-500">
                    <span>&larr;</span> Back to Ranking
                </a>
            </div>
        </div>
    </main>
    <footer class="bg-black py-16 mt-12 border-t-2 border-f1-red">
        <div class="max-w-7xl mx-auto px-6 text-center">
            <p class="text-gray-600 text-[10px] font-black uppercase tracking-[0.6em] italic">&copy; 2026 WEBIUS.NL - ALL RIGHTS RESERVED</p>
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
<?php
require_once 'db_config.php';
/** @var PDO $pdo */
$driverSlug = isset($_GET['slug']) ? $_GET['slug'] : '';
if (empty($driverSlug)) { header('Location: index.php'); exit; }

$driver = null;
try {
    $stmt = $pdo->prepare("
        SELECT d.*, t.team_color, t.team_name, t.full_team_name
        FROM drivers d
        JOIN teams t ON d.team_id = t.team_id
        WHERE LOWER(REPLACE(CONCAT(d.first_name, '-', d.last_name), ' ', '')) = :slug
    ");
    $stmt->execute(['slug' => $driverSlug]);
    $driver = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$driver) { header("Location: drivers.php"); exit; }
} catch (PDOException $e) { exit("Databasefout."); }

$teamColor = htmlspecialchars($driver['team_color'] ?? '#E10600');
list($r, $g, $b) = sscanf($teamColor, "#%02x%02x%02x");
$age = null;
if (!empty($driver['date_of_birth'])) {
    $birthDate = new DateTime($driver['date_of_birth']);
    $today = new DateTime();
    $age = $today->diff($birthDate)->y;
}
?>
<!DOCTYPE html>
<html lang="nl" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars(($driver['first_name'] ?? '') . ' ' . ($driver['last_name'] ?? '')); ?> | F1SITE.NL</title>
    <?php include 'navigatie/head.php'; ?>
    <style>
        :root { --team-color: <?php echo $teamColor; ?>; --team-rgb: <?php echo "$r, $g, $b"; ?>; }
        
        .font-outline {
            -webkit-text-stroke: 2px white;
            color: transparent;
        }

        .diagonal-bg {
            clip-path: polygon(0 0, 100% 0, 100% 70%, 0% 90%);
            background: linear-gradient(180deg, #16161c 0%, #0b0b0f 100%);
            height: 75vh; width: 100%; position: absolute; top: 0; left: 0; z-index: -2;
        }
        .driver-hero-img {
            height: 400px;
            object-fit: cover;
            object-position: top center;
            width: 100%;
        }
        
        @media (min-width: 1024px) {
            .driver-hero-img { height: auto; min-height: 650px; }
        }

        .stat-card {
            background: linear-gradient(145deg, rgba(255,255,255,0.03) 0%, rgba(255,255,255,0.01) 100%);
            transition: all 0.4s ease;
        }
    </style>
</head>
<body class="bg-[#0b0b0f] text-white italic selection:bg-f1-red">
    <?php include 'navigatie/header.php'; ?>

    <main class="relative min-h-screen overflow-hidden pt-12 lg:pt-24">
        <div class="diagonal-bg"></div>

        <div class="max-w-7xl mx-auto px-6 relative z-10">
            
            <div class="flex flex-col lg:flex-row-reverse gap-12 lg:items-center">
                
                <div class="w-full lg:w-7/12" data-aos="fade-left">
                    <div class="flex items-center gap-4 mb-8">
                        <span class="px-4 py-1 bg-f1-red text-white text-[10px] font-black uppercase tracking-widest italic rounded-sm">Driver Profile</span>
                        <?php if(!empty($driver['full_team_name'])): ?>
                            <span class="text-gray-500 font-bold uppercase tracking-widest text-[10px]"><?php echo htmlspecialchars($driver['full_team_name']); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <h1 class="text-6xl md:text-9xl font-oswald font-black uppercase italic tracking-tighter leading-[0.8] mb-6">
                        <?php echo htmlspecialchars($driver['first_name'] ?? ''); ?><br>
                        <span class="font-outline"><?php echo htmlspecialchars($driver['last_name'] ?? ''); ?></span>
                    </h1>

                    <div class="flex flex-wrap gap-6 py-8 border-y border-white/5 bg-black/20 px-6 rounded-2xl mb-12">
                        <?php if(!empty($driver['date_of_birth'])): ?>
                        <div class="flex flex-col">
                            <span class="text-f1-red font-black uppercase text-[10px] tracking-widest mb-1">Geboren</span>
                            <span class="text-lg font-bold uppercase italic"><?php echo date('d-m-Y', strtotime($driver['date_of_birth'])); ?></span>
                        </div>
                        <?php endif; ?>

                        <?php if($age): ?>
                        <div class="flex flex-col">
                            <span class="text-f1-red font-black uppercase text-[10px] tracking-widest mb-1">Leeftijd</span>
                            <span class="text-lg font-bold uppercase italic"><?php echo $age; ?> Jaar</span>
                        </div>
                        <?php endif; ?>

                        <?php if(!empty($driver['place_of_birth'])): ?>
                        <div class="flex flex-col">
                            <span class="text-f1-red font-black uppercase text-[10px] tracking-widest mb-1">Geboorteplaats</span>
                            <span class="text-lg font-bold uppercase italic"><?php echo htmlspecialchars($driver['place_of_birth']); ?></span>
                        </div>
                        <?php endif; ?>

                        <?php if(!empty($driver['nationality'])): ?>
                        <div class="flex flex-col">
                            <span class="text-f1-red font-black uppercase text-[10px] tracking-widest mb-1">Nationaliteit</span>
                            <div class="flex items-center gap-3">
                                <?php if (!empty($driver['flag_url'])): ?>
                                    <img src="<?php echo htmlspecialchars($driver['flag_url']); ?>" class="w-6 h-auto rounded-sm" alt="Flag">
                                <?php endif; ?>
                                <span class="text-lg font-bold uppercase italic"><?php echo htmlspecialchars($driver['nationality']); ?></span>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="w-full lg:w-5/12" data-aos="fade-right">
                    <div class="relative group bg-f1-card rounded-[3rem] overflow-hidden border border-white/10 shadow-2xl">
                        <?php if(!empty($driver['driver_number'])): ?>
                        <div class="absolute top-6 left-6 z-20 bg-black/70 backdrop-blur-md px-5 py-3 rounded-2xl border border-white/10">
                            <span class="text-4xl font-oswald font-black italic" style="color: var(--team-color);">#<?php echo htmlspecialchars($driver['driver_number']); ?></span>
                        </div>
                        <?php endif; ?>
                        <img src="<?php echo htmlspecialchars($driver['image'] ?? '/afbeeldingen/coureurs/default.jpg'); ?>" 
                             alt="Driver" 
                             class="driver-hero-img group-hover:scale-105 transition-transform duration-700">
                    </div>
                </div>
            </div>
            <section class="grid grid-cols-2 lg:grid-cols-4 gap-4 mt-16 mb-24">
                <div class="stat-card p-6 rounded-2xl border border-white/5">
                    <h4 class="text-gray-500 text-[10px] font-black uppercase tracking-widest mb-4">Racing Identity</h4>
                    <span class="text-5xl font-oswald font-black italic">#<?php echo htmlspecialchars($driver['driver_number'] ?? '00'); ?></span>
                </div>
                <div class="stat-card p-6 rounded-2xl border border-white/5">
                    <h4 class="text-gray-500 text-[10px] font-black uppercase tracking-widest mb-4">Performance</h4>
                    <span class="text-5xl font-oswald font-black italic text-f1-red"><?php echo number_format($driver['career_points'] ?? 0, 1, ',', '.'); ?></span>
                    <p class="text-[9px] font-black uppercase tracking-widest text-white mt-2">Points</p>
                </div>
                <div class="stat-card p-6 rounded-2xl border border-white/5">
                    <h4 class="text-gray-500 text-[10px] font-black uppercase tracking-widest mb-4">Hall of Fame</h4>
                    <span class="text-5xl font-oswald font-black italic"><?php echo htmlspecialchars($driver['championships_won'] ?? '0'); ?></span>
                    <p class="text-[9px] font-black uppercase tracking-widest text-white mt-2">Titles</p>
                </div>
                <div class="stat-card p-6 rounded-2xl border border-white/5">
                    <h4 class="text-gray-500 text-[10px] font-black uppercase tracking-widest mb-4">Grid Status</h4>
                    <p class="text-xl font-oswald font-black italic uppercase leading-tight text-white"><?php echo htmlspecialchars($driver['team_name'] ?? 'Free Agent'); ?></p>
                </div>
            </section>
            <?php if (!empty($driver['description'])): ?>
            <section class="mb-32 max-w-5xl mx-auto" data-aos="fade-up">
                <div class="relative p-8 md:p-12 border border-white/5 bg-f1-card/20 rounded-[2rem]">
                    <h2 class="text-2xl font-oswald font-black uppercase italic tracking-tighter mb-8 flex items-center gap-4">
                        <span class="w-12 h-1 bg-f1-red"></span> The Profile
                    </h2>
                    <p class="text-gray-400 leading-relaxed font-medium text-sm md:text-base italic">
                        <?php echo nl2br(htmlspecialchars($driver['description'])); ?>
                    </p>
                </div>
            </section>
            <?php endif; ?>
            <div class="text-center pb-20 border-t border-white/5">
                <a href="drivers.php" class="inline-flex items-center gap-6 group">
                    <span class="text-[10px] font-black uppercase tracking-[0.4em] text-gray-500 group-hover:text-white transition-colors">Back to all drivers</span>
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
        document.addEventListener('DOMContentLoaded', () => { AOS.init({ duration: 1000, once: true }); });
    </script>
</body>
</html>
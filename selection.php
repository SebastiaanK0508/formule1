<?php
require_once 'db_config.php';
/** @var PDO $pdo */

$teamSlug = isset($_GET['slug']) ? $_GET['slug'] : '';
if (empty($teamSlug)) {
    header('Location: index.php');
    exit;
}

$team = null;
try {
    $stmt = $pdo->prepare("
        SELECT * FROM teams 
        WHERE LOWER(REPLACE(team_name, ' ', '-')) = :slug
    ");
    $stmt->bindParam(':slug', $teamSlug);
    $stmt->execute();
    $team = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$team) {
        http_response_code(404);
        exit("Team niet gevonden.");
    }
    $stmtDrivers = $pdo->prepare("SELECT * FROM drivers WHERE team_id = :tid ORDER BY last_name ASC");
    $stmtDrivers->execute(['tid' => $team['team_id']]);
    $drivers = $stmtDrivers->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    exit("Databasefout.");
}

$teamColor = htmlspecialchars($team['team_color'] ?? '#E10600');
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($team['full_team_name']); ?> | Team Profile</title>
    
    <?php include 'navigatie/head.php'; ?>

    <style>
        .team-glow { 
            position: absolute; top: 0; left: 0; width: 100%; height: 100%; 
            background: radial-gradient(circle at center, <?php echo $teamColor; ?>15 0%, transparent 70%);
            pointer-events: none;
        }
    </style>
</head>
<body class="bg-pattern min-h-screen flex flex-col">
    <?php include 'navigatie/header.php'; ?>
    <main class="max-w-7xl mx-auto px-6 py-12 flex-grow">
        <div class="flex flex-col lg:flex-row gap-12 items-start">
            <div class="w-full lg:w-5/12 sticky top-32">
                <div class="relative">
                    <div class="team-glow"></div>
                    <?php if (!empty($team['team_logo'])): ?>
                        <img src="<?php echo htmlspecialchars($team['team_logo']); ?>" 
                             alt="<?php echo htmlspecialchars($team['team_name']); ?>" 
                             class="w-full h-auto rounded-[2rem] shadow-[0_0_50px_rgba(0,0,0,0.6)] border border-white/5 relative z-10">
                    <?php endif; ?>
                    
                    <div class="absolute -bottom-8 -left-4 text-[8rem] font-oswald font-black italic opacity-5 pointer-events-none select-none z-0" style="color: <?php echo $teamColor; ?>;">
                        EST
                    </div>
                </div>
            </div>

            <div class="w-full lg:w-7/12">
                <div class="mb-10">
                    <div class="flex items-center gap-4 mb-4">
                        <span class="h-[2px] w-12" style="background: <?php echo $teamColor; ?>;"></span>
                        <span class="text-xs font-black uppercase tracking-[0.4em] text-gray-500 italic">Constructor Profile</span>
                    </div>
                    <h1 class="text-5xl md:text-7xl font-oswald font-black uppercase italic tracking-tighter leading-[0.9] mb-4">
                        <?php echo htmlspecialchars($team['team_name']); ?>
                    </h1>
                    <p class="text-xl text-gray-400 font-bold uppercase tracking-tight"><?php echo htmlspecialchars($team['full_team_name']); ?></p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-10">
                    <div class="bg-f1-card p-8 rounded-3xl border border-white/5">
                        <span class="text-[10px] font-black uppercase tracking-widest text-gray-500">Base</span>
                        <p class="text-2xl font-oswald font-black italic mt-2 uppercase tracking-wide">
                            <?php echo htmlspecialchars($team['base'] ?? 'N/A'); ?>
                        </p>
                    </div>
                    <div class="bg-f1-card p-8 rounded-3xl border border-white/5">
                        <span class="text-[10px] font-black uppercase tracking-widest text-gray-500">Power Unit</span>
                        <p class="text-2xl font-oswald font-black italic mt-2 uppercase tracking-wide">
                            <?php echo htmlspecialchars($team['power_unit'] ?? 'N/A'); ?>
                        </p>
                    </div>
                </div>

                <div class="mb-10">
                    <h3 class="text-xs font-black uppercase tracking-[0.3em] text-f1-red mb-6 italic">Current Line-up</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <?php foreach ($drivers as $dr): ?>
                            <a href="driver-details.php?slug=<?php echo strtolower($dr['first_name'].'-'.$dr['last_name']); ?>" 
                               class="group bg-f1-card p-6 rounded-2xl border border-white/5 hover:border-f1-red/50 transition-all duration-300">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <span class="text-[10px] font-black text-gray-500">#<?php echo $dr['driver_number']; ?></span>
                                        <p class="text-lg font-oswald font-black uppercase italic group-hover:text-f1-red transition-colors">
                                            <?php echo htmlspecialchars($dr['first_name'] . ' ' . $dr['last_name']); ?>
                                        </p>
                                    </div>
                                    <span class="text-2xl opacity-0 group-hover:opacity-100 transition-opacity">&rarr;</span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <?php if (!empty($team['description'])): ?>
                <div class="bg-white/5 p-10 rounded-[2.5rem] border border-white/5 relative mb-10">
                    <h3 class="text-xs font-black uppercase tracking-[0.4em] text-f1-red mb-6 italic">History & Engineering</h3>
                    <p class="text-gray-400 leading-relaxed text-sm font-medium italic">
                        <?php echo nl2br(htmlspecialchars($team['description'])); ?>
                    </p>
                </div>
                <?php endif; ?>

                <a href="teams.php" class="inline-flex items-center gap-4 bg-white text-black px-10 py-5 rounded-full font-black uppercase text-[10px] tracking-[0.3em] hover:bg-f1-red hover:text-white transition-all duration-500">
                    <span>&larr;</span> All Constructors
                </a>
            </div>
        </div>
    </main>
    <?php include 'navigatie/footer.php'; ?>
</body>
</html>
<?php
require_once 'db_config.php';
/** @var PDO $pdo */

$allDrivers = [];
try {
    $stmt = $pdo->query("SELECT d.driver_id, d.first_name, d.last_name, d.driver_number, d.flag_url, d.image, t.team_name, t.team_color
                         FROM drivers d
                         LEFT JOIN teams t ON d.team_id = t.team_id
                         WHERE d.is_active = TRUE
                         ORDER BY t.team_name ASC, d.driver_number ASC");
    $allDrivers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (\PDOException $e) {
    error_log("Error fetching drivers: " . $e->getMessage());
}

$teamNames = [];
foreach ($allDrivers as $d) {
    if (!empty($d['team_name']) && !in_array($d['team_name'], $teamNames)) {
        $teamNames[] = $d['team_name'];
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>F1 Drivers <?php echo date('Y'); ?> | F1SITE.NL</title>
    <meta name="description" content="Meet every driver on the current Formula 1 grid: teams, car numbers and full profiles." />
    <?php include 'navigatie/head.php'; ?>
    <style>
        .f1-border { position: relative; }
        .f1-border::before { content: ""; position: absolute; top: 0; left: 0; width: 45px; height: 4px; background: #E10600; z-index: 10; }
        .driver-card { background: linear-gradient(180deg, rgba(22,22,28,0.9) 0%, rgba(11,11,15,1) 100%); border: 1px solid rgba(255,255,255,0.05); transition: all 0.4s cubic-bezier(0.22,1,0.36,1); }
        .driver-card:hover { transform: translateY(-6px); border-color: var(--team-color); box-shadow: 0 20px 40px rgba(0,0,0,0.5); }
        .driver-img-wrap { aspect-ratio: 1 / 1; overflow: hidden; background: #1a1a20; position: relative; }
        .driver-img { width: 100%; height: 100%; object-fit: cover; object-position: top; filter: grayscale(60%); transition: 0.6s cubic-bezier(0.22,1,0.36,1); }
        .driver-card:hover .driver-img { filter: grayscale(0%); transform: scale(1.08); }
        .fav-star { background: rgba(0,0,0,0.6); backdrop-filter: blur(4px); transition: all 0.2s ease; }
        .fav-star.is-following { color: #FFD700; }
        .filter-pill { transition: all 0.2s ease; }
        .filter-pill.active { background: #E10600; color: #fff; border-color: #E10600; }
    </style>
</head>
<body class="bg-pattern">
    <?php include 'navigatie/header.php'; ?>
    <main class="max-w-7xl mx-auto px-6 py-16">

        <header class="mb-14 text-center" data-aos="zoom-out">
            <span class="text-f1-red font-black tracking-[0.4em] text-xs uppercase mb-4 block underline decoration-f1-red/30 underline-offset-8">Full Grid Lineup</span>
            <h1 class="text-6xl md:text-8xl font-oswald font-black uppercase italic tracking-tighter leading-none">
                THE <span class="text-f1-red">DRIVERS</span>
            </h1>
            <p class="text-gray-500 mt-6 max-w-xl mx-auto text-sm md:text-base leading-relaxed">
                The full <?php echo date('Y'); ?> starting grid — <?php echo count($allDrivers); ?> drivers across <?php echo count($teamNames); ?> teams.
            </p>
        </header>

        <?php if (!empty($teamNames)): ?>
        <div id="team-filters" class="flex flex-wrap justify-center gap-2 mb-14" data-aos="fade-up">
            <button data-filter="all" class="filter-pill active px-5 py-2 rounded-full border border-white/10 text-[10px] font-black uppercase tracking-widest">All Teams</button>
            <?php foreach ($teamNames as $tn): ?>
                <button data-filter="<?php echo htmlspecialchars($tn); ?>" class="filter-pill px-5 py-2 rounded-full border border-white/10 text-[10px] font-black uppercase tracking-widest text-gray-400"><?php echo htmlspecialchars($tn); ?></button>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <section id="driver-grid" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5 mb-20">
            <?php if (!empty($allDrivers)): ?>
                <?php foreach ($allDrivers as $i => $driver):
                    $fname = $driver['first_name'] ?? '';
                    $lname = $driver['last_name'] ?? '';
                    $teamColor = htmlspecialchars($driver['team_color'] ?? '#E10600');
                    $slug = strtolower($fname . '-' . $lname);
                ?>
                    <article data-aos="fade-up" data-aos-delay="<?php echo min($i * 40, 400); ?>" data-team="<?php echo htmlspecialchars($driver['team_name'] ?? ''); ?>"
                             class="driver-card group rounded-2xl overflow-hidden relative" style="--team-color: <?php echo $teamColor; ?>;">
                        <button data-follow-driver="<?php echo (int)$driver['driver_id']; ?>" data-icon-only class="fav-star absolute top-3 right-3 z-20 w-9 h-9 rounded-full flex items-center justify-center text-white text-sm border border-white/10"></button>
                        <a href="driver-details.php?slug=<?php echo htmlspecialchars($slug); ?>" class="block">
                            <div class="driver-img-wrap">
                                <img src="<?php echo htmlspecialchars($driver['image'] ?: '/afbeeldingen/coureurs/default.jpg'); ?>" class="driver-img" loading="lazy" alt="<?php echo htmlspecialchars($fname . ' ' . $lname); ?>">
                                <span class="absolute bottom-2 left-3 text-3xl font-oswald font-black italic text-white/10 group-hover:text-f1-red transition-colors">#<?php echo htmlspecialchars($driver['driver_number'] ?? '00'); ?></span>
                            </div>
                            <div class="p-4 border-t-4" style="border-top-color: <?php echo $teamColor; ?>;">
                                <span class="text-[9px] font-black text-gray-500 uppercase tracking-widest block mb-1"><?php echo htmlspecialchars($fname); ?></span>
                                <h2 class="text-lg font-oswald font-black uppercase italic text-white leading-none mb-2 truncate"><?php echo htmlspecialchars($lname); ?></h2>
                                <span class="text-[9px] font-black uppercase tracking-[0.15em]" style="color: <?php echo $teamColor; ?>;"><?php echo htmlspecialchars($driver['team_name'] ?? 'Free Agent'); ?></span>
                            </div>
                        </a>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="col-span-full text-center text-gray-500 uppercase text-xs font-black tracking-widest py-20">No drivers found.</p>
            <?php endif; ?>
        </section>
    </main>
    <?php include 'navigatie/footer.php'; ?>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            AOS.init({ duration: 800, once: true });

            document.querySelectorAll('#team-filters .filter-pill').forEach(btn => {
                btn.addEventListener('click', () => {
                    document.querySelectorAll('#team-filters .filter-pill').forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');
                    const filter = btn.dataset.filter;
                    document.querySelectorAll('#driver-grid article').forEach(card => {
                        card.style.display = (filter === 'all' || card.dataset.team === filter) ? '' : 'none';
                    });
                });
            });
        });
    </script>
</body>
</html>

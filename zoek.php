<?php
require_once 'db_config.php';
/** @var PDO $pdo */

$query = isset($_GET['q']) ? trim($_GET['q']) : '';
$results = [
    'drivers' => [],
    'teams' => [],
    'circuits' => []
];

if (!empty($query)) {
    $searchWildcard = "%$query%";

    try {
        // 1. Zoek Coureurs
        $stmt = $pdo->prepare("SELECT * FROM drivers WHERE name LIKE :q OR nationality LIKE :q LIMIT 5");
        $stmt->execute([':q' => $searchWildcard]);
        $results['drivers'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 2. Zoek Teams
        $stmt = $pdo->prepare("SELECT * FROM teams WHERE team_name LIKE :q LIMIT 5");
        $stmt->execute([':q' => $searchWildcard]);
        $results['teams'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 3. Zoek Circuits/Races
        $stmt = $pdo->prepare("SELECT * FROM circuits WHERE grandprix LIKE :q OR title LIKE :q OR location LIKE :q ORDER BY race_datetime DESC LIMIT 10");
        $stmt->execute([':q' => $searchWildcard]);
        $results['circuits'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        error_log("Search Error: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <?php include 'navigatie/head.php'; ?>
    <style>
        .search-result-card {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.05);
            transition: all 0.3s ease;
        }
        .search-result-card:hover {
            background: rgba(225, 6, 0, 0.05);
            border-color: rgba(225, 6, 0, 0.3);
            transform: translateY(-2px);
        }
        .result-tag {
            font-size: 9px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            padding: 2px 8px;
            border-radius: 4px;
            background: rgba(255,255,255,0.05);
            color: rgba(255,255,255,0.4);
        }
    </style>
</head>
<body class="bg-pattern text-white">
    <?php include 'navigatie/header.php'; ?>

    <main class="max-w-5xl mx-auto px-6 py-12">
        <header class="mb-16" data-aos="fade-down">
            <h1 class="text-4xl md:text-6xl font-oswald font-black uppercase italic tracking-tighter mb-4">
                Search <span class="text-f1-red">Results</span>
            </h1>
            <p class="text-gray-500 uppercase tracking-widest text-xs font-bold">
                Showing results for: <span class="text-white">"<?php echo htmlspecialchars($query); ?>"</span>
            </p>
        </header>

        <?php if (empty($results['drivers']) && empty($results['teams']) && empty($results['circuits'])): ?>
            <div class="py-20 text-center border border-dashed border-white/10 rounded-[2.5rem]">
                <span class="text-4xl mb-4 block">🏎️💨</span>
                <h2 class="text-xl font-bold uppercase italic">No results found</h2>
                <p class="text-gray-500 text-sm mt-2">Try searching for a driver name, team, or Grand Prix.</p>
                <button onclick="document.getElementById('search-btn-desktop').click()" class="mt-8 text-f1-red font-black uppercase text-[10px] tracking-widest hover:underline">Try Again</button>
            </div>
        <?php else: ?>
            
            <div class="space-y-12">
                <?php if (!empty($results['circuits'])): ?>
                <section data-aos="fade-up">
                    <h3 class="text-sm font-black uppercase tracking-[0.3em] text-f1-red mb-6 flex items-center gap-4">
                        Grands Prix <span class="h-[1px] flex-grow bg-white/5"></span>
                    </h3>
                    <div class="grid grid-cols-1 gap-4">
                        <?php foreach ($results['circuits'] as $race): ?>
                        <a href="result_race.php?round=<?php echo $race['calendar_order']; ?>" class="search-result-card p-6 rounded-2xl flex items-center justify-between group">
                            <div class="flex items-center gap-6">
                                <div class="text-center">
                                    <span class="block text-[10px] font-black text-gray-500 uppercase"><?php echo date('M', strtotime($race['race_datetime'])); ?></span>
                                    <span class="text-2xl font-oswald font-black italic"><?php echo date('d', strtotime($race['race_datetime'])); ?></span>
                                </div>
                                <div>
                                    <span class="result-tag mb-2 inline-block">Race Session</span>
                                    <h4 class="text-lg font-bold group-hover:text-f1-red transition-colors"><?php echo htmlspecialchars($race['grandprix']); ?></h4>
                                    <p class="text-xs text-gray-500"><?php echo htmlspecialchars($race['title']); ?> • <?php echo htmlspecialchars($race['location']); ?></p>
                                </div>
                            </div>
                            <span class="text-f1-red opacity-0 group-hover:opacity-100 transition-all translate-x-[-10px] group-hover:translate-x-0">→</span>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </section>
                <?php endif; ?>

                <?php if (!empty($results['drivers'])): ?>
                <section data-aos="fade-up">
                    <h3 class="text-sm font-black uppercase tracking-[0.3em] text-f1-red mb-6 flex items-center gap-4">
                        Drivers <span class="h-[1px] flex-grow bg-white/5"></span>
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <?php foreach ($results['drivers'] as $driver): ?>
                        <a href="drivers.php#<?php echo $driver['id']; ?>" class="search-result-card p-5 rounded-2xl flex items-center gap-4 group">
                            <div class="w-12 h-12 rounded-full bg-white/5 flex items-center justify-center font-oswald font-black italic text-xl text-white/20">
                                <?php echo substr($driver['name'], 0, 1); ?>
                            </div>
                            <div>
                                <span class="result-tag mb-1 inline-block">Athlete</span>
                                <h4 class="font-bold group-hover:text-f1-red transition-colors"><?php echo htmlspecialchars($driver['name']); ?></h4>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </section>
                <?php endif; ?>

            </div>
        <?php endif; ?>
    </main>

    <?php include 'navigatie/footer.php'; ?>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>AOS.init({ duration: 800, once: true });</script>
</body>
</html>
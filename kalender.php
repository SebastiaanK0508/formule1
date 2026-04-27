<?php
require_once 'db_config.php';
/** @var PDO $pdo */
$circuitsData = [];
$availableYears = [];
$selectedYear = null;
try {
    $stmt = $pdo->query("SELECT DISTINCT YEAR(race_datetime) AS race_year FROM circuits ORDER BY race_year DESC");
    $availableYears = $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
} catch (\PDOException $e) {
    error_log($e->getMessage());
}
if (isset($_GET['year']) && in_array($_GET['year'], $availableYears)) {
    $selectedYear = $_GET['year'];
} elseif (!empty($availableYears)) {
    $selectedYear = $availableYears[0];
} else {
    $selectedYear = date('Y'); 
}
if ($selectedYear) {
    try {
        $stmt = $pdo->prepare("
            SELECT circuit_key, grandprix, location, map_url, race_datetime, title
            FROM circuits
            WHERE YEAR(race_datetime) = :selectedYear
            ORDER BY calendar_order ASC
        ");
        $stmt->bindParam(':selectedYear', $selectedYear, PDO::PARAM_INT);
        $stmt->execute();
        $circuitsData = $stmt->fetchAll();
    } catch (\PDOException $e) {
        error_log($e->getMessage());
    }
}
$dateFormatter = new IntlDateFormatter(
    'nl_NL',
    IntlDateFormatter::LONG,
    IntlDateFormatter::NONE,
    null,
    null,
    'd MMMM'
);
?>
<!DOCTYPE html>
<html lang="nl" class="scroll-smooth">
<head>
    <title>F1 Schedule <?php echo htmlspecialchars($selectedYear); ?> | F1SITE.NL</title>
    <?php include 'navigatie/head.php'; ?>
    <style>        
        .f1-border { position: relative; }
        .f1-border::before { content: ""; position: absolute; top: 0; left: 0; width: 45px; height: 4px; background: #E10600; z-index: 10; }
        .img-ratio { position: relative; width: 100%; padding-top: 56.25%; overflow: hidden; background: #000; }
        .img-ratio img { position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: contain; padding: 10px; transition: transform 0.5s ease; }
        .race-card:hover img { transform: scale(1.1); }
        select {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none; 
            background-repeat: no-repeat;
        }
        select option { background: #15151e; color: white; }
    </style>
</head>
<body class="bg-pattern text-white">
    <?php include 'navigatie/header.php'; ?>
    <main class="max-w-7xl mx-auto px-6 py-12">
        <section class="mb-16" data-aos="fade-down">
            <div class="bg-f1-card border border-white/5 p-8 md:p-12 rounded-[2.5rem] flex flex-col md:flex-row justify-between items-center gap-8 overflow-hidden">
                <div class="min-w-0">
                    <h2 class="text-4xl md:text-6xl font-oswald font-black uppercase italic tracking-tighter truncate">
                        F1 SEASON <span class="text-f1-red"><?php echo htmlspecialchars($selectedYear); ?></span>
                    </h2>
                    <p class="text-gray-400 uppercase tracking-[0.3em] text-xs font-bold mt-2">Official Race Calendar</p>
                </div>
                <form method="GET" action="kalender.php" class="w-full md:w-auto shrink-0">
                    <select name="year" onchange="this.form.submit()" 
                            class="w-full md:w-56 bg-f1-dark border-2 border-f1-red/30 text-white pl-6 pr-12 py-3 rounded-full font-bold focus:border-f1-red outline-none transition-all cursor-pointer hover:bg-f1-red/10 appearance-none bg-[right_1.2rem_center] bg-[length:1.2em_1.2em]"
                            style="background-image: url('data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' fill=\'none\' viewBox=\'0 0 24 24\' stroke=\'%23E10600\'%3E%3Cpath stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'3\' d=\'M19 9l-7 7-7-7\'%3E%3C/path%3E%3C/svg%3E');">
                        <?php if (empty($availableYears)): ?>
                            <option value="">Geen seizoenen beschikbaar</option>
                        <?php else: ?>
                            <?php foreach ($availableYears as $year): ?>
                                <?php if ($year !== null): ?>
                                    <option value="<?php echo htmlspecialchars($year); ?>" 
                                        <?php echo ((string)$year === (string)$selectedYear) ? 'selected' : ''; ?>>
                                        SEASON <?php echo htmlspecialchars($year); ?>
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </form>
            </div>
        </section>
        <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
            <?php if (!empty($circuitsData)): ?>
                <?php $i=0; foreach ($circuitsData as $circuit): ?>
                    <?php
                        $raceDateTime = new DateTime($circuit['race_datetime']);
                        $raceDay = $raceDateTime->format('j');
                        $raceYear = $raceDateTime->format('Y');
                        $formattedDate = $dateFormatter->format($raceDateTime);
                        $startDay = $raceDay - 2;
                        $displayDate = $startDay . ' - ' . $formattedDate;
                        if ($circuit['circuit_key'] === 'las_vegas') {
                            $displayDate = ($raceDay - 1) . ' - ' . $formattedDate . ' (Sat)';
                        }
                    ?>
                    <article data-aos="fade-up" data-aos-delay="<?php echo $i*50; ?>" 
                             class="race-card f1-border bg-f1-card rounded-br-3xl border-r border-b border-white/5 flex flex-col transition-all duration-500 hover:border-f1-red/40 overflow-hidden">
                        
                        <a href="circuit-details.php?key=<?php echo htmlspecialchars($circuit['circuit_key']); ?>" class="block group">
                            <div class="img-ratio">
                                <img src="<?php echo htmlspecialchars($circuit['map_url']); ?>" alt="<?php echo htmlspecialchars($circuit['grandprix']); ?>" loading="lazy">
                                <div class="absolute inset-0 bg-gradient-to-t from-f1-card to-transparent opacity-60"></div>
                            </div>
                            
                            <div class="p-8">
                                <div class="flex items-center justify-between mb-4">
                                    <span class="text-f1-red text-[10px] font-black uppercase tracking-widest italic">Round <?php echo ($i + 1); ?></span>
                                    <span class="bg-white/5 px-3 py-1 rounded-full text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                                        <?php echo htmlspecialchars($raceYear); ?>
                                    </span>
                                </div>
                                
                                <h3 class="text-2xl font-oswald font-black uppercase italic mb-6 group-hover:text-f1-red transition-colors">
                                    <?php echo htmlspecialchars($circuit['grandprix']); ?>
                                </h3>
                                <div class="space-y-3">
                                    <div class="flex items-center gap-3 text-sm">
                                        <span class="text-f1-red font-bold uppercase w-12">Date:</span>
                                        <span class="text-gray-300 font-medium"><?php echo htmlspecialchars($displayDate); ?></span>
                                    </div>
                                    <div class="flex items-center gap-3 text-sm">
                                        <span class="text-f1-red font-bold uppercase w-12">Loc:</span>
                                        <span class="text-gray-400"><?php echo htmlspecialchars($circuit['location']); ?></span>
                                    </div>
                                </div>
                                <div class="mt-8 pt-6 border-t border-white/5 flex justify-between items-center">
                                    <span class="text-[10px] font-black uppercase tracking-widest text-gray-500">Circuit Details</span>
                                    <span class="text-f1-red text-xl transform group-hover:translate-x-2 transition-transform">→</span>
                                </div>
                            </div>
                        </a>
                    </article>
                <?php $i++; endforeach; ?>
            <?php else: ?>
                <div class="lg:col-span-3 text-center py-20 bg-f1-card rounded-3xl border border-white/5">
                    <p class="text-gray-400 font-bold uppercase tracking-widest">Geen races gevonden voor dit jaar.</p>
                </div>
            <?php endif; ?>
        </section>
    </main>
    <?php include 'navigatie/footer.php'; ?>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof AOS !== 'undefined') {
                AOS.init({ 
                    duration: 1000, 
                    once: true,
                    disable: 'mobile' 
                });
            }
        });
    </script>
</body>
</html>
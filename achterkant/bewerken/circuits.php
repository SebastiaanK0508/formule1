<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../index.php");
    exit;
}

require_once '../db_config.php';
/** @var PDO $pdo */
$years = [];
try {
    $yearStmt = $pdo->query("SELECT DISTINCT YEAR(race_datetime) as year FROM circuits ORDER BY year DESC");
    $years = $yearStmt->fetchAll(PDO::FETCH_COLUMN);
} catch (\PDOException $e) {
    error_log("Fout bij ophalen jaren: " . $e->getMessage());
}
$selectedYear = $_GET['year'] ?? 'all';
$circuitsData = [];
try {
    $sql = "SELECT circuit_key, grandprix, location, race_datetime, calendar_order FROM circuits";
    $params = [];
    if ($selectedYear !== 'all') {
        $sql .= " WHERE YEAR(race_datetime) = :year";
        $params['year'] = $selectedYear;
    }
    $sql .= " ORDER BY YEAR(race_datetime) DESC, calendar_order ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $circuitsData = $stmt->fetchAll();
} catch (\PDOException $e) {
    error_log("Fout bij het ophalen van circuits: " . $e->getMessage());
}
$currentYearHeader = null;
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Race Schedule | F1 Management</title>
    <?php include '../head.php'; ?>
    <style>
        :root { --f1-red: #E10600; }
        .bg-f1-card { background: rgba(22, 22, 28, 0.8); backdrop-filter: blur(10px); }
        .bg-pattern { background-color: #0b0b0f; background-image: radial-gradient(rgba(255,255,255,0.03) 1px, transparent 0); background-size: 40px 40px; }
        .font-oswald { font-family: 'Oswald', sans-serif; }
        
        tr.race-row { transition: all 0.3s ease; cursor: pointer; border-left: 4px solid transparent; }
        tr.race-row:hover { 
            background: rgba(255, 255, 255, 0.03) !important; 
            border-left-color: var(--f1-red);
            transform: translateX(5px);
        }
        
        .date-badge {
            background: linear-gradient(145deg, #1e1e24, #16161c);
            border: 1px solid rgba(255,255,255,0.05);
        }

        select {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%23E10600'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 1em;
        }
    </style>
</head>
<body class="bg-pattern text-white font-sans overflow-x-hidden">

    <div class="flex min-h-screen">
        <?php include '../nav.php'; ?>

        <main class="flex-grow p-6 lg:p-12">
            
            <header class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-12" data-aos="fade-down">
                <div>
                    <h2 class="text-4xl lg:text-5xl font-oswald font-black uppercase italic tracking-tighter leading-none">
                        Shedule <span class="text-f1-red"><?php echo date('Y'); ?></span>
                    </h2>
                    <p class="text-gray-500 text-sm mt-2 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-f1-red animate-pulse"></span>
                        Management Dashboard <span class="text-white/30">|</span> Total Events: <?php echo count($circuitsData); ?>
                    </p>
                </div>
                
                <div class="flex flex-wrap gap-4">
                    <form method="GET" action="" class="relative">
                        <select name="year" onchange="this.form.submit()" 
                            class="bg-f1-card border border-white/10 text-white text-[11px] font-black uppercase tracking-widest px-6 py-3 rounded-full outline-none focus:border-f1-red transition-all cursor-pointer min-w-[140px]">
                            <option value="all">All Seasons</option>
                            <?php foreach ($years as $y): ?>
                                <option value="<?php echo $y; ?>" <?php echo ($selectedYear == $y) ? 'selected' : ''; ?>>
                                    Season <?php echo $y; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>

                    <a href="add/add-circuit.php" class="bg-f1-red text-white px-8 py-3 rounded-full font-black text-[11px] uppercase tracking-[0.2em] hover:scale-105 hover:shadow-[0_0_20px_rgba(225,6,0,0.4)] transition-all duration-300">
                        + Add Grand Prix
                    </a>
                </div>
            </header>

            <div class="bg-f1-card rounded-[2.5rem] border border-white/5 overflow-hidden shadow-2xl" data-aos="fade-up">
                <div class="p-8 border-b border-white/5 bg-white/2 flex justify-between items-center">
                    <h3 class="font-oswald font-black uppercase italic text-xl tracking-tighter">Event <span class="text-f1-red">Timeline</span></h3>
                    <span class="text-[9px] font-black text-gray-600 uppercase tracking-widest italic uppercase">Double click row for technical details</span>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-[10px] font-black text-gray-500 uppercase tracking-[0.2em] border-b border-white/5">
                                <th class="p-6">Round</th>
                                <th class="p-6">Grand Prix</th>
                                <th class="p-6">Location</th>
                                <th class="p-6">Race Session</th>
                                <th class="p-6 text-right px-10">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            <?php if (!empty($circuitsData)): ?>
                                <?php foreach ($circuitsData as $circuit): 
                                    $year = date('Y', strtotime($circuit['race_datetime']));
                                    
                                    // Jaar-kop tonen bij verandering van jaar (alleen bij 'All Seasons')
                                    if ($year !== $currentYearHeader && $selectedYear === 'all'): 
                                        $currentYearHeader = $year;
                                ?>
                                    <tr class="bg-white/5 pointer-events-none">
                                        <td colspan="5" class="px-6 py-4">
                                            <div class="flex items-center gap-4">
                                                <span class="text-f1-red font-oswald text-2xl font-black italic"><?php echo $year; ?></span>
                                                <div class="h-[1px] flex-grow bg-white/10"></div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>

                                <tr class="race-row group" data-circuit-key="<?php echo htmlspecialchars($circuit['circuit_key']); ?>">
                                    <td class="p-6">
                                        <span class="font-oswald text-xl italic text-white/20 group-hover:text-f1-red transition">
                                            R<?php echo str_pad($circuit['calendar_order'], 2, '0', STR_PAD_LEFT); ?>
                                        </span>
                                    </td>
                                    <td class="p-6">
                                        <p class="font-bold text-base text-gray-100 group-hover:text-white transition uppercase tracking-tight">
                                            <?php echo htmlspecialchars($circuit['grandprix']); ?>
                                        </p>
                                    </td>
                                    <td class="p-6">
                                        <div class="flex items-center gap-2">
                                            <span class="text-lg opacity-70">📍</span>
                                            <span class="text-xs font-bold text-gray-400 uppercase tracking-widest italic">
                                                <?php echo htmlspecialchars($circuit['location']); ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="p-6">
                                        <div class="date-badge inline-block px-4 py-2 rounded-xl">
                                            <p class="text-[10px] font-black text-f1-red uppercase tracking-tighter">
                                                <?php echo date('D, d M Y', strtotime($circuit['race_datetime'])); ?>
                                            </p>
                                            <p class="text-[9px] text-gray-500 font-bold uppercase">
                                                Starts at <?php echo date('H:i', strtotime($circuit['race_datetime'])); ?> GMT
                                            </p>
                                        </div>
                                    </td>
                                    <td class="p-6 text-right px-10">
                                        <?php 
                                            $raceDate = strtotime($circuit['race_datetime']);
                                            $now = time();
                                            if($raceDate < $now):
                                        ?>
                                            <span class="text-[9px] font-black uppercase text-gray-600 tracking-widest">Completed</span>
                                        <?php else: ?>
                                            <span class="text-[9px] font-black uppercase text-green-500 tracking-widest animate-pulse">Upcoming</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="p-20 text-center">
                                        <p class="text-gray-600 font-oswald uppercase italic text-lg tracking-widest">No race events found for this selection...</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            AOS.init({ duration: 800, once: true });

            const tableRows = document.querySelectorAll('tr.race-row');
            tableRows.forEach(row => {
                row.addEventListener('dblclick', function() {
                    const circuitKey = this.dataset.circuitKey;
                    if (circuitKey) {
                        window.location.href = 'circuit-details.php?key=' + circuitKey;
                    }
                });
            });
        });
    </script>
</body>
</html>
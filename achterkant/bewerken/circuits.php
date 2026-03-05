<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../index.php");
    exit;
}
require_once '../db_config.php';
/** @var PDO $pdo */

$circuitsData = [];
try {
    $stmt = $pdo->query("SELECT circuit_key, grandprix, location, race_datetime FROM circuits ORDER BY calendar_order ASC");
    $circuitsData = $stmt->fetchAll();
} catch (\PDOException $e) {
    error_log("Fout bij het ophalen van circuits: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Race Schedule | F1 Management</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&family=Oswald:wght@700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        :root { --f1-red: #E10600; }
        .bg-f1-card { background: rgba(22, 22, 28, 0.8); backdrop-filter: blur(10px); }
        .bg-pattern { background-color: #0b0b0f; background-image: radial-gradient(rgba(255,255,255,0.03) 1px, transparent 0); background-size: 40px 40px; }
        .font-oswald { font-family: 'Oswald', sans-serif; }
        
        tr { transition: all 0.3s ease; cursor: pointer; border-left: 4px solid transparent; }
        tr:hover { 
            background: rgba(255, 255, 255, 0.03) !important; 
            border-left-color: var(--f1-red);
            transform: translateX(5px);
        }
        
        .date-badge {
            background: linear-gradient(145deg, #1e1e24, #16161c);
            border: 1px solid rgba(255,255,255,0.05);
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
                        Race <span class="text-f1-red">Calendar</span>
                    </h2>
                    <p class="text-gray-500 text-sm mt-2 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-f1-red animate-pulse"></span>
                        Season 2025 Schedule <span class="text-white/30">|</span> Total Events: <?php echo count($circuitsData); ?>
                    </p>
                </div>
                
                <div class="flex gap-4">
                    <a href="add/add-circuit.php" class="bg-f1-red text-white px-8 py-3 rounded-full font-black text-[11px] uppercase tracking-[0.2em] hover:scale-105 hover:shadow-[0_0_20px_rgba(225,6,0,0.4)] transition-all duration-300">
                        + Add Grand Prix
                    </a>
                </div>
            </header>

            <div class="bg-f1-card rounded-[2.5rem] border border-white/5 overflow-hidden shadow-2xl" data-aos="fade-up">
                <div class="p-8 border-b border-white/5 bg-white/2 flex justify-between items-center">
                    <h3 class="font-oswald font-black uppercase italic text-xl tracking-tighter">Event <span class="text-f1-red">Timeline</span></h3>
                    <span class="text-[9px] font-black text-gray-600 uppercase tracking-widest italic">Double click row for technical details</span>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
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
                            <?php if (!empty($circuitsData)): $round = 1; ?>
                                <?php foreach ($circuitsData as $circuit): ?>
                                <tr class="group" data-circuit-key="<?php echo htmlspecialchars($circuit['circuit_key']); ?>">
                                    <td class="p-6">
                                        <span class="font-oswald text-xl italic text-white/20 group-hover:text-f1-red transition">
                                            R<?php echo str_pad($round++, 2, '0', STR_PAD_LEFT); ?>
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
                                        <p class="text-gray-600 font-oswald uppercase italic text-lg tracking-widest">No race events scheduled in database...</p>
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

            const tableRows = document.querySelectorAll('tbody tr');
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
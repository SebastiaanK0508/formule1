<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../index.php");
    exit;
}
require_once '../db_config.php';
/** @var PDO $pdo */

$search = $_GET['search'] ?? '';
$drivers = [];

try {
    if (!empty($search)) {
        $sql = "SELECT d.driver_id, d.first_name, d.last_name, d.driver_number, t.team_name 
                FROM drivers d 
                LEFT JOIN teams t ON d.team_id = t.team_id 
                WHERE d.first_name LIKE :search OR d.last_name LIKE :search OR t.team_name LIKE :search
                ORDER BY d.driver_number ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':search', '%' . $search . '%');
    } else {
        $sql = "SELECT d.driver_id, d.first_name, d.last_name, d.driver_number, t.team_name 
                FROM drivers d 
                LEFT JOIN teams t ON d.team_id = t.team_id 
                ORDER BY d.driver_number ASC";
        $stmt = $pdo->prepare($sql);
    }
    $stmt->execute();
    $drivers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $totalDrivers = count($drivers);
} catch (\PDOException $e) {
    error_log("Fout bij het ophalen van coureurs: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Drivers - F1 Management</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&family=Oswald:wght@700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        :root { --f1-red: #E10600; }
        .bg-f1-card { background: rgba(22, 22, 28, 0.8); backdrop-filter: blur(10px); }
        .bg-pattern { background-color: #0b0b0f; background-image: radial-gradient(rgba(255,255,255,0.03) 1px, transparent 0); background-size: 40px 40px; }
        .text-f1-red { color: var(--f1-red); }
        .bg-f1-red { background-color: var(--f1-red); }
        .font-oswald { font-family: 'Oswald', sans-serif; }
        
        tr { transition: all 0.2s ease; cursor: pointer; }
        tr:hover { background: rgba(225, 6, 0, 0.05) !important; }
        
        .search-input {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            color: white;
            transition: all 0.3s ease;
        }
        .search-input:focus {
            border-color: var(--f1-red);
            outline: none;
            box-shadow: 0 0 15px rgba(225, 6, 0, 0.2);
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
                        Grid <span class="text-f1-red">Personnel</span>
                    </h2>
                    <p class="text-gray-500 text-sm mt-2 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-f1-red animate-pulse"></span>
                        Managing <span class="text-white font-bold"><?php echo $totalDrivers; ?></span> active entries
                    </p>
                </div>
                
                <div class="flex flex-wrap gap-4">
                    <form method="GET" class="relative group">
                        <input type="text" name="search" placeholder="Search driver or team..." 
                               value="<?php echo htmlspecialchars($search); ?>"
                               class="search-input pl-4 pr-12 py-3 rounded-full text-xs font-bold uppercase tracking-widest w-64">
                        <button type="submit" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 hover:text-f1-red">
                            🔍
                        </button>
                    </form>
                    <a href="add/add-drivers.php" class="bg-f1-red text-white px-8 py-3 rounded-full font-black text-[11px] uppercase tracking-[0.2em] hover:scale-105 hover:shadow-[0_0_20px_rgba(225,6,0,0.4)] transition-all duration-300 flex items-center">
                        + Add Driver
                    </a>
                </div>
            </header>

            <div class="grid grid-cols-1 gap-8">
                
                <div class="bg-f1-card rounded-[2.5rem] border border-white/5 overflow-hidden shadow-2xl" data-aos="fade-up">
                    <div class="p-8 border-b border-white/5 flex justify-between items-center bg-white/2">
                        <h3 class="font-oswald font-black uppercase italic text-xl tracking-tighter">Current <span class="text-f1-red">Drivers</span></h3>
                        <?php if(!empty($search)): ?>
                            <a href="drivers.php" class="text-[9px] font-black text-f1-red uppercase tracking-widest border-b border-f1-red/30 pb-1">Reset Filters</a>
                        <?php endif; ?>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="text-[10px] font-black text-gray-500 uppercase tracking-[0.2em] border-b border-white/5">
                                    <th class="p-6"># NO.</th>
                                    <th class="p-6">Full Name</th>
                                    <th class="p-6">Constructors Team</th>
                                    <th class="p-6 text-center">Telemetry</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5">
                                <?php if (!empty($drivers)): ?>
                                    <?php foreach ($drivers as $driver): ?>
                                    <tr class="group" onclick="window.location.href='driver-details.php?id=<?php echo $driver['driver_id']; ?>'">
                                        <td class="p-6">
                                            <span class="font-oswald text-2xl italic font-black text-f1-red group-hover:scale-110 inline-block transition">
                                                <?php echo htmlspecialchars($driver['driver_number']); ?>
                                            </span>
                                        </td>
                                        <td class="p-6">
                                            <p class="font-bold text-base text-gray-200 group-hover:text-white transition uppercase tracking-tight">
                                                <?php echo htmlspecialchars($driver['first_name'] . ' ' . $driver['last_name']); ?>
                                            </p>
                                        </td>
                                        <td class="p-6">
                                            <span class="px-3 py-1 bg-white/5 border border-white/10 text-gray-400 text-[9px] font-black rounded-full uppercase tracking-[0.15em] group-hover:border-f1-red/50 transition">
                                                <?php echo htmlspecialchars($driver['team_name'] ?? 'Unassigned'); ?>
                                            </span>
                                        </td>
                                        <td class="p-6 text-center">
                                            <span class="text-[10px] font-black uppercase text-gray-600 group-hover:text-white transition tracking-widest">
                                                View Profile →
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="p-20 text-center">
                                            <p class="text-gray-600 font-oswald uppercase italic text-lg tracking-widest">No drivers found in sector...</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </main>
    </div>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            AOS.init({ 
                duration: 800, 
                once: true,
                easing: 'ease-out-quad'
            });
        });
    </script>
</body>
</html>
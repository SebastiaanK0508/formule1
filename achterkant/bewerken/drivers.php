<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../index.php");
    exit;
}
require_once '../db_config.php';
/** @var PDO $pdo */

$activeDrivers = [];
$retiredDrivers = [];

try {
    $sql = "SELECT d.*, t.team_name AS linked_team_name, t.team_color,
            COALESCE(t.team_name, d.team_name, 'Independent') AS display_team_name 
            FROM drivers d 
            LEFT JOIN teams t ON d.team_id = t.team_id
            ORDER BY d.driver_number ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $allDrivers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($allDrivers as $driver) {
        if ($driver['is_active']) {
            $activeDrivers[] = $driver;
        } else {
            $retiredDrivers[] = $driver;
        }
    }
} catch (\PDOException $e) {
    error_log("Fout bij het ophalen van coureurs: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Drivers Grid | F1 Management</title>
    <?php include '../head.php'; ?>
    <style>
        :root { --f1-red: #E10600; }
        .bg-pattern { background-color: #0b0b0f; background-image: radial-gradient(rgba(255,255,255,0.03) 1px, transparent 0); background-size: 40px 40px; }
        .font-oswald { font-family: 'Oswald', sans-serif; }
        tr { 
            transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1); 
            cursor: pointer;
            border-left: none !important;
        }
        .active-row:hover { 
            background: linear-gradient(90deg, rgba(255, 255, 255, 0.05) 0%, transparent 100%) !important;
            transform: scale(1.01) translateX(10px);
        }
        .retired-row { opacity: 0.6; filter: grayscale(0.8); transition: all 0.5s ease; }
        .retired-row:hover { 
            opacity: 1; 
            filter: grayscale(0); 
            background: rgba(255,255,255,0.03) !important;
            transform: translateX(5px);
        }
        .section-divider {
            height: 1px;
            background: linear-gradient(90deg, var(--f1-red), transparent);
            margin: 4rem 0 2rem 0;
            opacity: 0.5;
        }
        .search-focus:focus-within {
            border-color: var(--f1-red);
            box-shadow: 0 0 25px rgba(225, 6, 0, 0.2);
        }
    </style>
</head>
<body class="bg-pattern text-white font-sans min-h-screen antialiased">

    <div class="flex">
        <?php include '../nav.php'; ?>

        <main class="flex-grow p-6 lg:p-12">
            <div class="max-w-7xl mx-auto">
                
                <header class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-12" data-aos="fade-down">
                    <div>
                        <h2 class="text-6xl font-oswald font-black uppercase italic tracking-tighter leading-none">
                            Drivers <span class="text-f1-red">Database</span>
                        </h2>
                        <p class="text-gray-500 text-[10px] font-black uppercase tracking-[0.3em] mt-3 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-f1-red animate-pulse"></span>
                            Current Grid & Historical Archives
                        </p>
                    </div>
                    
                    <div class="flex flex-wrap items-center gap-4 w-full lg:w-auto">
                        <div class="relative w-full lg:w-80 search-focus bg-white/5 border border-white/10 rounded-full transition-all duration-300">
                            <i data-lucide="search" class="absolute left-5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500"></i>
                            <input type="text" id="masterSearch" placeholder="Search name, team or #..." 
                                   class="bg-transparent pl-14 pr-6 py-4 rounded-full text-xs font-bold w-full focus:outline-none text-white">
                        </div>
                        <a href="add/add-drivers.php" class="bg-f1-red text-white px-8 py-4 rounded-full font-black text-[10px] uppercase tracking-[0.2em] hover:bg-red-700 transition-all shadow-lg">
                            + Add Driver
                        </a>
                    </div>
                </header>

                <div class="mb-6 flex items-center gap-4" data-aos="fade-right">
                    <h3 class="font-oswald text-2xl uppercase italic font-black tracking-tighter">Active <span class="text-f1-red">Grid</span></h3>
                    <div class="h-[2px] flex-grow bg-white/5"></div>
                </div>

                <div class="bg-[#16161c]/80 backdrop-blur-xl rounded-[2.5rem] border border-white/5 overflow-hidden shadow-2xl mb-16" data-aos="fade-up">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <tbody class="divide-y divide-white/5">
                                <?php foreach ($activeDrivers as $driver): 
                                    $accentColor = !empty($driver['driver_color']) ? $driver['driver_color'] : (!empty($driver['team_color']) ? $driver['team_color'] : '#E10600');
                                    $searchStr = strtolower(($driver['first_name']??'').' '.($driver['last_name']??'').' '.($driver['display_team_name']??'').' '.($driver['driver_number']??''));
                                ?>
                                <tr class="driver-row active-row group" 
                                    onclick="window.location.href='bewerken/driver-details.php?id=<?php echo $driver['driver_id']; ?>'"
                                    data-search="<?php echo htmlspecialchars($searchStr); ?>">
                                    <td class="p-8 w-32 text-center">
                                        <span class="font-oswald text-5xl italic font-black transition-all" style="color: <?php echo $accentColor; ?>;">
                                            <?php echo htmlspecialchars((string)$driver['driver_number']); ?>
                                        </span>
                                    </td>
                                    <td class="p-8">
                                        <div class="flex items-center gap-5">
                                            <div class="relative">
                                                <img src="<?php echo htmlspecialchars($driver['image'] ?? '../assets/img/default-driver.png'); ?>" 
                                                     class="w-14 h-14 rounded-full object-cover object-top border-2 border-white/10 group-hover:border-white/30 transition shadow-xl">
                                                <?php if(!empty($driver['flag_url'])): ?>
                                                    <img src="<?php echo htmlspecialchars($driver['flag_url']); ?>" class="absolute -bottom-1 -right-1 w-6 h-4 object-cover rounded shadow-sm">
                                                <?php endif; ?>
                                            </div>
                                            <div>
                                                <p class="font-bold text-xl text-gray-200 group-hover:text-white transition uppercase tracking-tighter leading-tight">
                                                    <?php echo htmlspecialchars($driver['first_name'] . ' ' . $driver['last_name']); ?>
                                                </p>
                                                <p class="text-[9px] text-gray-500 font-black uppercase tracking-widest mt-1"><?php echo htmlspecialchars($driver['nationality']); ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-8">
                                        <span class="px-5 py-2 bg-white/5 border border-white/10 text-gray-300 text-[10px] font-black rounded-lg uppercase tracking-widest transition group-hover:bg-white/10"
                                              style="border-bottom: 3px solid <?php echo $accentColor; ?>;">
                                            <?php echo htmlspecialchars($driver['display_team_name']); ?>
                                        </span>
                                    </td>
                                    <td class="p-8 text-right">
                                        <i data-lucide="chevron-right" class="w-6 h-6 text-gray-700 group-hover:text-f1-red group-hover:translate-x-2 transition-all"></i>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <?php if(!empty($retiredDrivers)): ?>
                <div class="mb-6 flex items-center gap-4" data-aos="fade-right">
                    <h3 class="font-oswald text-2xl uppercase italic font-black tracking-tighter text-gray-600">Hall of <span class="text-gray-400 text-opacity-30">Fame</span></h3>
                    <div class="h-[1px] flex-grow bg-white/5"></div>
                </div>

                <div class="bg-black/20 rounded-[2.5rem] border border-white/5 overflow-hidden transition-all" data-aos="fade-up">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <tbody class="divide-y divide-white/5">
                                <?php foreach ($retiredDrivers as $driver):
                                    $searchStr = strtolower(($driver['first_name']??'').' '.($driver['last_name']??'').' '.($driver['display_team_name']??'').' '.($driver['driver_number']??''));
                                ?>
                                <tr class="driver-row retired-row group" 
                                    onclick="window.location.href='bewerken/driver-details.php?id=<?php echo $driver['driver_id']; ?>'"
                                    data-search="<?php echo htmlspecialchars($searchStr); ?>">
                                    <td class="p-6 w-32 text-center">
                                        <span class="font-oswald text-3xl italic font-black text-gray-700 group-hover:text-gray-400 transition">
                                            <?php echo htmlspecialchars((string)$driver['driver_number']); ?>
                                        </span>
                                    </td>
                                    <td class="p-6">
                                        <div class="flex items-center gap-4">
                                            <img src="<?php echo htmlspecialchars($driver['image'] ?? '../assets/img/default-driver.png'); ?>" 
                                                 class="w-10 h-10 rounded-full object-cover object-top grayscale opacity-50 group-hover:grayscale-0 group-hover:opacity-100 transition-all duration-500">
                                            <div>
                                                <p class="font-bold text-base text-gray-500 group-hover:text-white transition uppercase">
                                                    <?php echo htmlspecialchars($driver['first_name'] . ' ' . $driver['last_name']); ?>
                                                </p>
                                                <p class="text-[8px] text-gray-700 font-bold uppercase tracking-widest italic">Retired Constructor: <?php echo htmlspecialchars($driver['display_team_name']); ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-6 text-right text-gray-800 font-black uppercase italic text-[10px] tracking-widest">
                                        History Archive
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>
                <div id="noResults" style="display: none;" class="py-32 text-center" data-aos="zoom-in">
                    <i data-lucide="ghost" class="w-16 h-16 text-gray-800 mx-auto mb-6"></i>
                    <p class="text-gray-600 font-oswald uppercase italic text-2xl tracking-widest">No matching driver telemetry found...</p>
                </div>

            </div>
        </main>
    </div>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            AOS.init({ duration: 800, once: true });
            lucide.createIcons();
            const searchInput = document.getElementById('masterSearch');
            const rows = document.querySelectorAll('.driver-row');
            const noResults = document.getElementById('noResults');

            searchInput.addEventListener('input', function() {
                const term = this.value.toLowerCase().trim();
                let matches = 0;

                rows.forEach(row => {
                    const content = row.getAttribute('data-search');
                    if (content.includes(term)) {
                        row.style.display = '';
                        matches++;
                    } else {
                        row.style.display = 'none';
                    }
                });
                noResults.style.display = (matches === 0) ? 'block' : 'none';
            });
        });
    </script>
</body>
</html>
<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../index.php");
    exit;
}
require_once '../db_config.php';
/** @var PDO $pdo */

$selectedYear = $_GET['year'] ?? date('Y');

try {
    $sql = "SELECT * FROM circuits WHERE YEAR(race_datetime) = :year ORDER BY calendar_order ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['year' => $selectedYear]);
    $races = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $years = $pdo->query("SELECT DISTINCT YEAR(race_datetime) as y FROM circuits ORDER BY y DESC")->fetchAll(PDO::FETCH_COLUMN);
} catch (\PDOException $e) {
    error_log($e->getMessage());
}

function formatSession($datetime) {
    if (empty($datetime) || $datetime === '0000-00-00 00:00:00') {
        return [
            'date' => 'TBC', 
            'time' => '--:--', 
            'passed' => false
        ];
    }
    $ts = strtotime($datetime);
    if (!$ts) {
        return ['date' => 'TBC', 'time' => '--:--', 'passed' => false];
    }

    return [
        'date' => date('d M', $ts),
        'time' => date('H:i', $ts),
        'passed' => $ts < time()
    ];
}
?>
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Race Calendar | F1 Management</title>
    <?php include '../head.php'; ?>
    <style>
        :root { --f1-red: #E10600; }
        .bg-pattern { background-color: #0b0b0f; background-image: radial-gradient(rgba(255,255,255,0.03) 1px, transparent 0); background-size: 40px 40px; }
        .font-oswald { font-family: 'Oswald', sans-serif; }
        .session-card { background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); transition: all 0.3s ease; }
        .race-card:hover .session-card { border-color: rgba(225, 6, 0, 0.3); }
        .passed { opacity: 0.4; filter: grayscale(1); }
        .upcoming-glow { box-shadow: 0 0 20px rgba(225, 6, 0, 0.1); }
    </style>
</head>
<body class="bg-pattern text-white font-sans min-h-screen">
    <div class="flex">
        <?php include '../nav.php'; ?>
        <main class="flex-grow p-6 lg:p-12">
            <header class="flex flex-col md:flex-row justify-between items-end gap-6 mb-12" data-aos="fade-down">
                <div>
                    <h2 class="text-6xl font-oswald font-black uppercase italic tracking-tighter leading-none">
                        Calendar <span class="text-f1-red">2026</span>
                    </h2>
                    <p class="text-gray-500 text-[10px] font-black uppercase tracking-[0.3em] mt-3 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-f1-red animate-pulse"></span>
                        Full Season Session Telemetry
                    </p>
                </div>
            </header>
            <div class="space-y-8">
                <?php foreach ($races as $race): 
                    $isNext = (strtotime($race['race_datetime']) > time());
                ?>
                <div class="race-card bg-[#16161c]/80 backdrop-blur-xl rounded-[3rem] border border-white/5 overflow-hidden transition-all duration-500 hover:border-white/20 shadow-2xl <?php echo $isNext ? 'upcoming-glow' : ''; ?>" data-aos="fade-up">
                    <div class="p-8 lg:p-10 flex flex-col lg:flex-row justify-between items-center gap-8 border-b border-white/5">
                        <div class="flex items-center gap-8 text-center lg:text-left">
                            <span class="font-oswald text-6xl italic font-black text-white/5 uppercase">R<?php echo $race['calendar_order']; ?></span>
                            <div>
                                <h3 class="text-3xl font-oswald font-black uppercase italic tracking-tighter leading-none">
                                    <?php echo htmlspecialchars($race['grandprix']); ?>
                                </h3>
                                <p class="text-f1-red text-[10px] font-black uppercase tracking-widest mt-2 flex items-center justify-center lg:justify-start gap-2">
                                    <i data-lucide="map-pin" class="w-3 h-3"></i>
                                    <?php echo htmlspecialchars($race['location']); ?>
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-6">
                            <div class="text-right">
                                <p class="text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">Race Weekend</p>
                                <p class="font-oswald text-xl font-bold italic"><?php echo date('d M', strtotime($race['race_datetime'] . ' -2 days')); ?> — <?php echo date('d M Y', strtotime($race['race_datetime'])); ?></p>
                            </div>
                            <a href="bewerken/circuit-details.php?key=<?php echo $race['circuit_key']; ?>" class="bg-white/5 hover:bg-f1-red p-4 rounded-2xl transition-all group">
                                <i data-lucide="settings-2" class="w-5 h-5 text-gray-400 group-hover:text-white"></i>
                            </a>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-3 <?php echo !empty($race['sprint_datetime']) ? 'lg:grid-cols-6' : 'lg:grid-cols-5'; ?> divide-x divide-white/5">
                        <?php 
                        $sessions = [];
                        $sessions[] = ['label' => 'FP1', 'key' => 'fp1_datetime'];
                        if (!empty($race['sprint_datetime'])) {
                            $sessions[] = ['label' => 'Sprint Quali', 'key' => 'sprint_quali_datetime'];
                            $sessions[] = ['label' => 'Sprint Race', 'key' => 'sprint_datetime'];
                            $sessions[] = ['label' => 'Qualifying', 'key' => 'quali_datetime'];
                        } else {
                            $sessions[] = ['label' => 'FP2', 'key' => 'fp2_datetime'];
                            $sessions[] = ['label' => 'FP3', 'key' => 'fp3_datetime'];
                            $sessions[] = ['label' => 'Qualifying', 'key' => 'quali_datetime'];
                        }
                        $sessions[] = ['label' => 'Grand Prix', 'key' => 'race_datetime'];
                        foreach ($sessions as $session): 
                            $data = formatSession($race[$session['key']] ?? null);
                        ?>
                        <div class="p-6 flex flex-col items-center justify-center text-center session-card <?php echo $data['passed'] ? 'passed' : ''; ?>">
                            <p class="text-[8px] font-black text-gray-500 uppercase tracking-[0.2em] mb-3">
                                <?php echo $session['label']; ?>
                            </p>
                            <p class="font-oswald text-lg font-bold italic mb-1">
                                <?php echo $data['date']; ?>
                            </p>
                            <p class="text-xs font-black <?php echo $data['passed'] ? 'text-gray-600' : 'text-f1-red'; ?>">
                                <?php echo $data['time']; ?>
                            </p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php if(empty($races)): ?>
            <div class="py-24 text-center">
                <i data-lucide="calendar-x" class="w-16 h-16 text-gray-800 mx-auto mb-6"></i>
                <p class="text-gray-600 font-oswald uppercase italic text-2xl tracking-widest">No scheduled events for this season</p>
            </div>
            <?php endif; ?>
        </main>
    </div>

    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            AOS.init({ duration: 800, once: true });
            lucide.createIcons();
        });
    </script>
</body>
</html>
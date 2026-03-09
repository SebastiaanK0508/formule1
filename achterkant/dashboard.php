<?php
session_start();
if (!isset($_SESSION['logged_in'])) { header("Location: index.php"); exit; }
require_once 'db_config.php';
/** @var PDO $pdo */

try {
    $totalNews = $pdo->query("SELECT COUNT(*) FROM f1_nieuws")->fetchColumn();
    $totalTeams = $pdo->query("SELECT COUNT(*) FROM teams WHERE is_active = 1")->fetchColumn();
    $totalDrivers = $pdo->query("SELECT COUNT(*) FROM drivers WHERE is_active = 1")->fetchColumn();
    $newMessages = $pdo->query("SELECT COUNT(*) FROM contact")->fetchColumn();
    $stmtRace = $pdo->query("SELECT title, race_datetime, location FROM circuits WHERE race_datetime > NOW() ORDER BY race_datetime ASC LIMIT 1");
    $nextRace = $stmtRace->fetch(PDO::FETCH_ASSOC);
    $topDriver = $pdo->query("SELECT first_name, last_name, championships_won FROM drivers ORDER BY championships_won DESC LIMIT 1")->fetch();
    $articles = $pdo->query("SELECT id, titel, source, publicatie_datum FROM f1_nieuws ORDER BY publicatie_datum DESC LIMIT 5")->fetchAll();

} catch (PDOException $e) {
    die("Data Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <?php include 'head.php'; ?>
    <style>
        .f1-card { background: rgba(22, 22, 28, 0.7); backdrop-filter: blur(15px); border: 1px solid rgba(255,255,255,0.05); }
        .f1-red-gradient { background: linear-gradient(135deg, #E10600 0%, #8b0000 100%); }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; }
    </style>
</head>
<body class="bg-[#0b0b0f] text-white font-sans overflow-x-hidden">

    <div class="flex min-h-screen">
        <?php include 'nav.php'; ?>
        
        <main class="flex-grow p-6 lg:p-10 mt-16 lg:mt-0">
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-10 gap-4">
                <div data-aos="fade-right">
                    <h1 class="text-4xl font-oswald font-black italic uppercase tracking-tighter text-white">Engineering <span class="text-f1-red">Station</span></h1>
                    <p class="text-gray-500 text-xs font-bold uppercase tracking-widest mt-1">Live Database Access: <span class="text-green-500">Connected</span></p>
                </div>
                
                <?php if($newMessages > 0): ?>
                <div class="bg-blue-600/20 border border-blue-500/50 px-4 py-2 rounded-xl flex items-center gap-3 animate-bounce">
                    <i data-lucide="mail" class="w-4 h-4 text-blue-400"></i>
                    <span class="text-[10px] font-black uppercase tracking-tight"><?php echo $newMessages; ?> New Contact Requests</span>
                </div>
                <?php endif; ?>
            </div>

            <div class="stats-grid mb-10">
                <div class="f1-card p-6 rounded-3xl" data-aos="zoom-in">
                    <p class="text-[10px] font-black text-gray-500 uppercase tracking-widest mb-2">Active Grid</p>
                    <h3 class="text-3xl font-oswald font-black italic"><?php echo $totalTeams; ?> <span class="text-sm text-gray-600">TEAMS</span></h3>
                </div>
                <div class="f1-card p-6 rounded-3xl" data-aos="zoom-in" data-aos-delay="100">
                    <p class="text-[10px] font-black text-gray-500 uppercase tracking-widest mb-2">Entry List</p>
                    <h3 class="text-3xl font-oswald font-black italic"><?php echo $totalDrivers; ?> <span class="text-sm text-gray-600">DRIVERS</span></h3>
                </div>
                <div class="f1-card p-6 rounded-3xl" data-aos="zoom-in" data-aos-delay="200">
                    <p class="text-[10px] font-black text-gray-500 uppercase tracking-widest mb-2">News Database</p>
                    <h3 class="text-3xl font-oswald font-black italic"><?php echo $totalNews; ?> <span class="text-sm text-gray-600">POSTS</span></h3>
                </div>
                <div class="f1-card p-6 rounded-3xl border-l-4 border-l-f1-red" data-aos="zoom-in" data-aos-delay="300">
                    <p class="text-[10px] font-black text-gray-500 uppercase tracking-widest mb-2">G.O.A.T Status</p>
                    <h3 class="text-xl font-oswald font-black italic uppercase"><?php echo $topDriver['last_name']; ?></h3>
                    <p class="text-[9px] text-f1-red font-bold uppercase"><?php echo $topDriver['championships_won']; ?> World Titles</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 f1-card rounded-[2.5rem] overflow-hidden shadow-2xl" data-aos="fade-up">
                    <div class="p-8 border-b border-white/5 flex justify-between items-center">
                        <h3 class="font-oswald font-black uppercase italic text-xl tracking-tighter">Telemetrie <span class="text-f1-red">Feed</span></h3>
                        <a href="bewerken/add/add-news.php" class="text-[10px] font-black bg-f1-red px-4 py-2 rounded-full uppercase tracking-tighter">Add Entry</a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <tbody class="divide-y divide-white/5">
                                <?php foreach($articles as $a): ?>
                                <tr class="hover:bg-white/[0.02] transition-all group">
                                    <td class="p-6">
                                        <div class="flex items-center gap-4">
                                            <div class="hidden sm:block w-2 h-2 rounded-full bg-f1-red"></div>
                                            <div>
                                                <p class="font-bold text-sm text-gray-200 group-hover:text-white transition leading-tight mb-1"><?php echo htmlspecialchars($a['titel']); ?></p>
                                                <p class="text-[9px] text-gray-600 font-bold uppercase"><?php echo $a['source']; ?> • <?php echo date('H:i', strtotime($a['publicatie_datum'])); ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-6 text-right">
                                        <div class="flex justify-end gap-3 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <a href="edit_news.php?id=<?php echo $a['id']; ?>" class="text-gray-500 hover:text-white"><i data-lucide="edit-3" class="w-4 h-4"></i></a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="space-y-8">
                    <?php if($nextRace): ?>
                    <div class="f1-red-gradient rounded-[2.5rem] p-8 shadow-[0_20px_50px_rgba(225,6,0,0.3)] relative overflow-hidden" data-aos="fade-left">
                        <div class="relative z-10">
                            <div class="flex justify-between items-start mb-6">
                                <span class="px-3 py-1 bg-black/20 rounded-full text-[9px] font-black uppercase tracking-widest">Next Grand Prix</span>
                                <i data-lucide="timer" class="w-5 h-5 opacity-50"></i>
                            </div>
                            <h4 class="text-3xl font-oswald font-black italic uppercase leading-none mb-2"><?php echo $nextRace['title']; ?></h4>
                            <p class="text-[10px] font-bold uppercase tracking-widest opacity-80 mb-8"><?php echo $nextRace['location']; ?></p>
                            
                            <div id="race-timer" class="flex gap-4">
                                <div>
                                    <span id="d" class="text-3xl font-oswald font-black italic">00</span>
                                    <p class="text-[8px] uppercase font-black opacity-60">Days</p>
                                </div>
                                <span class="text-2xl opacity-30">:</span>
                                <div>
                                    <span id="h" class="text-3xl font-oswald font-black italic">00</span>
                                    <p class="text-[8px] uppercase font-black opacity-60">Hrs</p>
                                </div>
                                <span class="text-2xl opacity-30">:</span>
                                <div>
                                    <span id="m" class="text-3xl font-oswald font-black italic">00</span>
                                    <p class="text-[8px] uppercase font-black opacity-60">Min</p>
                                </div>
                            </div>
                        </div>
                        <div class="absolute -bottom-10 -right-5 text-[120px] font-black italic opacity-10 select-none">GP</div>
                    </div>
                    <?php endif; ?>

                    <div class="f1-card rounded-[2.5rem] p-8" data-aos="fade-left" data-aos-delay="200">
                        <h4 class="font-oswald font-black uppercase italic text-lg tracking-tighter mb-6 flex items-center gap-2">
                            <span class="w-1 h-4 bg-f1-red"></span> Data Integrity
                        </h4>
                        <div class="space-y-4 text-[11px]">
                            <div class="flex justify-between items-center border-b border-white/5 pb-2">
                                <span class="text-gray-500 uppercase font-bold">SQL Status</span>
                                <span class="text-green-500 font-black tracking-widest uppercase">Optimal</span>
                            </div>
                            <div class="flex justify-between items-center border-b border-white/5 pb-2">
                                <span class="text-gray-500 uppercase font-bold">SSL Certificate</span>
                                <span class="text-blue-400 font-black tracking-widest uppercase">Active</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-500 uppercase font-bold">PHP Version</span>
                                <span class="text-white font-black tracking-widest uppercase"><?php echo phpversion(); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({ duration: 800, once: true });
        lucide.createIcons();

        <?php if($nextRace): ?>
        // COUNTDOWN LOGIC
        const raceTime = new Date("<?php echo $nextRace['race_datetime']; ?>").getTime();
        setInterval(() => {
            const now = new Date().getTime();
            const diff = raceTime - now;
            if(diff < 0) return;
            document.getElementById('d').innerText = Math.floor(diff / (1000 * 60 * 60 * 24)).toString().padStart(2, '0');
            document.getElementById('h').innerText = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)).toString().padStart(2, '0');
            document.getElementById('m').innerText = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60)).toString().padStart(2, '0');
        }, 1000);
        <?php endif; ?>
    </script>
</body>
</html>
<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../index.php");
    exit;
}
require_once '../db_config.php';
/** @var PDO $pdo */

$search = $_GET['search'] ?? '';
$teams = [];

try {
    if (!empty($search)) {
        $sql = "SELECT team_id, full_team_name, base_location, team_principal, team_logo FROM teams 
                WHERE full_team_name LIKE :search 
                OR base_location LIKE :search 
                OR team_principal LIKE :search 
                ORDER BY full_team_name ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':search', '%' . $search . '%');
    } else {
        $sql = "SELECT team_id, full_team_name, base_location, team_principal, team_logo FROM teams ORDER BY full_team_name ASC";
        $stmt = $pdo->prepare($sql);
    }
    $stmt->execute();
    $teams = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (\PDOException $e) {
    error_log("Fout bij het ophalen van teams: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Constructor Standings | F1 Administration</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&family=Oswald:wght@700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        :root { --f1-red: #E10600; }
        .bg-f1-card { background: rgba(22, 22, 28, 0.8); backdrop-filter: blur(10px); }
        .bg-pattern { background-color: #0b0b0f; background-image: radial-gradient(rgba(255,255,255,0.03) 1px, transparent 0); background-size: 40px 40px; }
        .font-oswald { font-family: 'Oswald', sans-serif; }
        
        .search-input {
            background: rgba(255,255,255,0.05) !important;
            border: 1px solid rgba(255,255,255,0.1) !important;
            transition: all 0.3s ease;
        }
        .search-input:focus {
            border-color: var(--f1-red) !important;
            box-shadow: 0 0 15px rgba(225, 6, 0, 0.2);
            outline: none;
        }

        tr { transition: all 0.3s ease; cursor: pointer; border-left: 4px solid transparent; }
        tr:hover { 
            background: rgba(255, 255, 255, 0.03) !important; 
            border-left-color: var(--f1-red);
            transform: translateX(5px);
        }
    </style>
</head>
<body class="bg-pattern text-white font-sans min-h-screen">
    <div class="flex">
        <?php include '../nav.php'; ?>
        <main class="flex-grow p-6 lg:p-12">
            <div class="max-w-7xl mx-auto">
                
                <header class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6 mb-12" data-aos="fade-down">
                    <div>
                        <h2 class="text-5xl font-oswald font-black uppercase italic tracking-tighter">
                            F1 <span class="text-f1-red">Teams</span>
                        </h2>
                        <p class="text-gray-500 text-[10px] font-black uppercase tracking-[0.2em] mt-2 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-f1-red"></span>
                            Constructors Grid Management
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-4 w-full lg:w-auto">
                        <form method="GET" class="flex gap-2 w-full lg:w-auto">
                            <input type="text" name="search" placeholder="Search by name, base..." 
                                   value="<?php echo htmlspecialchars($search); ?>" 
                                   class="search-input px-6 py-3 rounded-full text-xs font-bold w-full lg:w-64 text-white">
                            <button type="submit" class="bg-white/5 hover:bg-white/10 border border-white/10 px-6 py-3 rounded-full text-[10px] font-black uppercase tracking-widest transition">
                                Search
                            </button>
                            <?php if(!empty($search)): ?>
                                <a href="teams.php" class="bg-f1-red/10 border border-f1-red/20 text-f1-red px-6 py-3 rounded-full text-[10px] font-black uppercase tracking-widest transition flex items-center">Reset</a>
                            <?php endif; ?>
                        </form>
                        <a href="add/add-team.php" class="bg-f1-red text-white px-8 py-3 rounded-full font-black text-[10px] uppercase tracking-[0.2em] hover:scale-105 transition shadow-[0_0_20px_rgba(225,6,0,0.3)]">
                            + Add Team
                        </a>
                    </div>
                </header>

                <div class="bg-f1-card rounded-[2.5rem] border border-white/5 overflow-hidden shadow-2xl" data-aos="fade-up">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="text-[10px] font-black text-gray-500 uppercase tracking-[0.2em] border-b border-white/5">
                                    <th class="p-8">Constructor</th>
                                    <th class="p-8">Headquarters</th>
                                    <th class="p-8">Team Principal</th>
                                    <th class="p-8 text-right">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5">
                                <?php if (!empty($teams)): ?>
                                    <?php foreach ($teams as $team): ?>
                                        <tr class="group" data-team-id="<?php echo htmlspecialchars($team['team_id']); ?>">
                                            <td class="p-8">
                                                <div class="flex items-center gap-4">
                                                    <div class="w-10 h-10 rounded-lg bg-white/5 border border-white/10 flex items-center justify-center font-oswald text-xl italic font-black text-white/20 group-hover:text-f1-red transition">
                                                        <?php echo substr($team['full_team_name'], 0, 1); ?>
                                                    </div>
                                                    <div>
                                                        <p class="font-oswald text-lg font-black uppercase italic tracking-tight group-hover:text-f1-red transition">
                                                            <?php echo htmlspecialchars($team['full_team_name']); ?>
                                                        </p>
                                                        <p class="text-[9px] font-black text-gray-600 uppercase tracking-widest">Team ID: #<?php echo $team['team_id']; ?></p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="p-8">
                                                <div class="flex items-center gap-2">
                                                    <span class="text-xs font-bold text-gray-400 uppercase tracking-tighter">
                                                        <?php echo htmlspecialchars($team['base_location']); ?>
                                                    </span>
                                                </div>
                                            </td>
                                            <td class="p-8">
                                                <span class="text-xs font-black text-white/70 uppercase tracking-widest italic">
                                                    <?php echo htmlspecialchars($team['team_principal']); ?>
                                                </span>
                                            </td>
                                            <td class="p-8 text-right">
                                                <span class="inline-block w-2 h-2 rounded-full bg-green-500 shadow-[0_0_10px_rgba(34,197,94,0.5)]"></span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="p-20 text-center">
                                            <div class="flex flex-col items-center gap-4">
                                                <span class="text-4xl">🏎️💨</span>
                                                <p class="text-gray-500 font-oswald uppercase italic text-lg tracking-widest">No teams found matching your telemetry...</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="p-6 border-t border-white/5 bg-white/2">
                         <p class="text-[9px] font-black text-gray-600 uppercase tracking-[0.3em] text-center italic">
                            Double click any entry to access full technical dossier
                         </p>
                    </div>
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
                    const teamId = this.dataset.teamId;
                    if (teamId) {
                        window.location.href = 'team-details.php?id=' + teamId;
                    }
                });
            });
        });
    </script>
</body>
</html>
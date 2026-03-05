<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../index.php");
    exit;
}
require_once '../db_config.php';
/** @var PDO $pdo */

$teamDetails = [];
$message = '';
$teamId = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $teamId) {
    try {
        $sql = "UPDATE teams SET
                    team_name = :team_name, team_color = :team_color, full_team_name = :full_team_name,
                    base_location = :base_location, team_principal = :team_principal, 
                    technical_director = :technical_director, championships_won = :championships_won,
                    chassis = :chassis, first_entry_year = :first_entry_year,
                    website_url = :website_url, logo_url = :logo_url,
                    current_engine_supplier = :current_engine_supplier, is_active = :is_active
                WHERE team_id = :team_id";

        $stmt = $pdo->prepare($sql);
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        $stmt->execute([
            ':team_name' => $_POST['team_name'],
            ':team_color' => $_POST['team_color'],
            ':full_team_name' => $_POST['full_team_name'],
            ':base_location' => $_POST['base_location'],
            ':team_principal' => $_POST['team_principal'],
            ':technical_director' => $_POST['technical_director'],
            ':championships_won' => (int)$_POST['championships_won'],
            ':chassis' => $_POST['chassis'],
            ':first_entry_year' => (int)$_POST['first_entry_year'],
            ':website_url' => $_POST['website_url'],
            ':logo_url' => $_POST['logo_url'],
            ':current_engine_supplier' => $_POST['current_engine_supplier'],
            ':is_active' => $isActive,
            ':team_id' => $teamId
        ]);

        $message = "<div class='bg-green-500/20 border border-green-500 text-green-400 p-4 rounded-xl mb-6 font-bold uppercase text-xs tracking-widest'>✓ Constructor Dossier Updated</div>";
    } catch (\PDOException $e) {
        $message = "<div class='bg-red-900/50 border border-red-500 text-white p-4 rounded-xl mb-6'>System Error: " . $e->getMessage() . "</div>";
    }
}

if ($teamId) {
    $stmt = $pdo->prepare("SELECT * FROM teams WHERE team_id = :team_id");
    $stmt->execute([':team_id' => $teamId]);
    $teamDetails = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Team Profile | <?php echo htmlspecialchars($teamDetails['team_name'] ?? 'Constructor'); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&family=Oswald:wght@700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        :root { --f1-red: #E10600; --team-accent: <?php echo $teamDetails['team_color'] ?? '#E10600'; ?>; }
        .bg-f1-card { background: rgba(22, 22, 28, 0.9); backdrop-filter: blur(15px); }
        .bg-pattern { background-color: #0b0b0f; background-image: radial-gradient(rgba(255,255,255,0.03) 1px, transparent 0); background-size: 40px 40px; }
        .font-oswald { font-family: 'Oswald', sans-serif; }
        
        input, select, textarea { 
            background: rgba(255,255,255,0.03) !important;
            border: 1px solid rgba(255,255,255,0.1) !important;
            color: #fff !important;
            transition: all 0.3s ease;
        }
        input:focus { border-color: var(--team-accent) !important; outline: none; box-shadow: 0 0 15px rgba(255,255,255,0.1); }
        input[readonly] { background: transparent !important; border-color: transparent !important; cursor: default; }
        .form-label { font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.15em; color: #4b5563; margin-bottom: 0.5rem; display: block; }
        .team-border { border-left: 4px solid var(--team-accent); }
    </style>
</head>
<body class="bg-pattern text-white font-sans min-h-screen">

    <div class="flex">
        <?php include '../nav.php'; ?>

        <main class="flex-grow p-6 lg:p-12">
            <div class="max-w-6xl mx-auto">
                
                <div class="flex justify-between items-center mb-10" data-aos="fade-down">
                    <a href="teams.php" class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-500 hover:text-white transition">← Grid Overview</a>
                    <button type="button" id="editBtn" class="bg-white/5 border border-white/10 px-8 py-3 rounded-full text-[10px] font-black uppercase tracking-[0.2em] hover:bg-white/10 transition">Edit Constructor</button>
                </div>

                <?php echo $message; ?>

                <?php if ($teamDetails): ?>
                <form method="POST" id="teamForm">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                        
                        <div class="lg:col-span-4 space-y-6" data-aos="fade-right">
                            <div class="bg-f1-card rounded-[2.5rem] border border-white/5 p-8 text-center team-border relative overflow-hidden">
                                <div class="absolute -right-4 -top-4 opacity-10">
                                    <div class="w-32 h-32 rounded-full" style="background: var(--team-accent);"></div>
                                </div>
                                <img src="<?php echo htmlspecialchars($teamDetails['logo_url']); ?>" class="w-40 h-40 object-contain mx-auto mb-6 p-4 bg-white/5 rounded-3xl" alt="Logo">
                                <h1 class="text-3xl font-oswald font-black uppercase italic tracking-tighter leading-tight">
                                    <?php echo htmlspecialchars($teamDetails['team_name']); ?>
                                </h1>
                                <div class="mt-4 flex justify-center gap-2">
                                    <span class="px-3 py-1 bg-white/5 rounded-full text-[9px] font-black uppercase tracking-widest text-gray-400 italic">
                                        Est. <?php echo htmlspecialchars($teamDetails['first_entry_year']); ?>
                                    </span>
                                </div>
                            </div>

                            <div class="bg-f1-card rounded-[2.5rem] border border-white/5 p-8">
                                <label class="form-label">Active Entry</label>
                                <div class="flex items-center gap-4">
                                    <input type="checkbox" name="is_active" class="w-6 h-6 rounded accent-green-500" <?php echo $teamDetails['is_active'] ? 'checked' : ''; ?> disabled>
                                    <span class="text-xs font-bold uppercase tracking-widest text-gray-500">Currently Competing</span>
                                </div>
                                <div class="mt-8">
                                    <label class="form-label">Team Brand Color</label>
                                    <input type="color" name="team_color" value="<?php echo htmlspecialchars($teamDetails['team_color']); ?>" class="w-full h-12 rounded-xl border-none cursor-pointer" disabled>
                                </div>
                            </div>
                        </div>

                        <div class="lg:col-span-8 space-y-6" data-aos="fade-left">
                            <div class="bg-f1-card rounded-[2.5rem] border border-white/5 p-10">
                                <h3 class="font-oswald font-black uppercase italic text-2xl tracking-tighter mb-10 pb-4 border-b border-white/5 flex items-center gap-3">
                                    Constructor <span class="text-f1-red">Dossier</span>
                                    <span class="text-[10px] ml-auto text-gray-600 font-sans not-italic tracking-normal font-normal uppercase">Technical Regs 2025</span>
                                </h3>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                    <div class="md:col-span-2">
                                        <label class="form-label">Full Legal Team Name</label>
                                        <input type="text" name="full_team_name" value="<?php echo htmlspecialchars($teamDetails['full_team_name']); ?>" class="w-full p-4 rounded-xl text-sm font-bold" readonly>
                                    </div>
                                    <div>
                                        <label class="form-label">Team Principal</label>
                                        <input type="text" name="team_principal" value="<?php echo htmlspecialchars($teamDetails['team_principal']); ?>" class="w-full p-4 rounded-xl text-sm font-bold" readonly>
                                    </div>
                                    <div>
                                        <label class="form-label">Technical Director</label>
                                        <input type="text" name="technical_director" value="<?php echo htmlspecialchars($teamDetails['technical_director']); ?>" class="w-full p-4 rounded-xl text-sm font-bold" readonly>
                                    </div>
                                    <div>
                                        <label class="form-label">Headquarters Base</label>
                                        <input type="text" name="base_location" value="<?php echo htmlspecialchars($teamDetails['base_location']); ?>" class="w-full p-4 rounded-xl text-sm font-bold" readonly>
                                    </div>
                                    <div>
                                        <label class="form-label">Engine Partner</label>
                                        <input type="text" name="current_engine_supplier" value="<?php echo htmlspecialchars($teamDetails['current_engine_supplier']); ?>" class="w-full p-4 rounded-xl text-sm font-bold italic text-f1-red" readonly>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-12 pt-10 border-t border-white/5">
                                    <div>
                                        <label class="form-label">Current Chassis</label>
                                        <input type="text" name="chassis" value="<?php echo htmlspecialchars($teamDetails['chassis']); ?>" class="w-full p-4 rounded-xl text-sm font-mono" readonly>
                                    </div>
                                    <div>
                                        <label class="form-label">WCC Titles</label>
                                        <input type="number" name="championships_won" value="<?php echo htmlspecialchars($teamDetails['championships_won']); ?>" class="w-full p-4 rounded-xl text-sm font-bold" readonly>
                                    </div>
                                </div>

                                <div id="editFields" class="hidden mt-10 space-y-6 p-6 bg-white/[0.02] rounded-3xl border border-dashed border-white/10">
                                    <h4 class="form-label text-white">Asset Telemetry</h4>
                                    <div class="grid grid-cols-1 gap-4">
                                        <input type="text" name="team_name" value="<?php echo htmlspecialchars($teamDetails['team_name']); ?>" placeholder="Short Name" class="w-full p-3 text-xs">
                                        <input type="text" name="logo_url" value="<?php echo htmlspecialchars($teamDetails['logo_url']); ?>" placeholder="Logo URL" class="w-full p-3 text-xs font-mono">
                                        <input type="text" name="website_url" value="<?php echo htmlspecialchars($teamDetails['website_url']); ?>" placeholder="Official Website" class="w-full p-3 text-xs">
                                    </div>
                                </div>

                                <div class="mt-10">
                                    <button type="submit" id="saveBtn" class="hidden bg-f1-red text-white px-12 py-4 rounded-full font-black text-[11px] uppercase tracking-[0.2em] hover:scale-105 transition shadow-[0_0_20px_rgba(225,6,0,0.4)]">
                                        Synchronize Data
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                <?php endif; ?>
            </div>
        </main>
    </div>

    

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            AOS.init({ duration: 800, once: true });

            const editBtn = document.getElementById('editBtn');
            const saveBtn = document.getElementById('saveBtn');
            const editFields = document.getElementById('editFields');
            const inputs = document.querySelectorAll('#teamForm input, #teamForm select');

            editBtn.addEventListener('click', () => {
                inputs.forEach(el => {
                    el.readOnly = false;
                    el.disabled = false;
                });
                editFields.classList.remove('hidden');
                saveBtn.classList.remove('hidden');
                editBtn.classList.add('hidden');
            });
        });
    </script>
</body>
</html>
<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../index.php");
    exit;
}
require_once '../db_config.php';
/** @var PDO $pdo */

$driverDetails = null; 
$teams = [];
$message = '';

// Verwerk Verwijderen
if (isset($_POST['delete_driver']) && isset($_POST['driver_id'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM drivers WHERE driver_id = :id");
        $stmt->execute([':id' => $_POST['driver_id']]);
        header("Location: ../drivers.php?deleted=success");
        exit;
    } catch (\PDOException $e) {
        $message = "<div class='bg-red-900/50 border border-red-500 text-white p-4 rounded-xl mb-6 font-bold text-xs uppercase tracking-widest'>Error: Kan coureur niet verwijderen (mogelijk nog gekoppeld aan resultaten)</div>";
    }
}

// Teams ophalen voor de selectie
try {
    $stmt_teams = $pdo->query("SELECT team_id, team_name FROM teams ORDER BY team_name ASC");
    $teams = $stmt_teams->fetchAll(PDO::FETCH_ASSOC);
} catch (\PDOException $e) {
    $message = "<div class='bg-red-900/50 border border-red-500 text-white p-4 rounded-xl mb-6'>Verbindingsfout: " . $e->getMessage() . "</div>";
}

$driverId = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : null;

// Verwerk Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $driverId && !isset($_POST['delete_driver'])) {
    try {
        $sql = "UPDATE drivers SET
                    first_name = :first_name, last_name = :last_name, nationality = :nationality,
                    date_of_birth = :date_of_birth, driver_number = :driver_number, team_id = :team_id, 
                    championships_won = :championships_won, career_points = :career_points,
                    image = :image, flag_url = :flag_url, place_of_birth = :place_of_birth, 
                    description = :description, is_active = :is_active
                WHERE driver_id = :driver_id";

        $stmt = $pdo->prepare($sql);
        $teamId = ($_POST['team_id'] == 0) ? null : $_POST['team_id'];
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        $stmt->execute([
            ':first_name' => $_POST['first_name'],
            ':last_name' => $_POST['last_name'],
            ':nationality' => $_POST['nationality'],
            ':date_of_birth' => !empty($_POST['date_of_birth']) ? $_POST['date_of_birth'] : null,
            ':driver_number' => $_POST['driver_number'],
            ':team_id' => $teamId,
            ':championships_won' => $_POST['championships_won'],
            ':career_points' => $_POST['career_points'],
            ':image' => $_POST['image'],
            ':flag_url' => $_POST['flag_url'],
            ':place_of_birth' => $_POST['place_of_birth'],
            ':description' => $_POST['description'],
            ':is_active' => $isActive,
            ':driver_id' => $driverId
        ]);

        $message = "<div class='bg-green-500/20 border border-green-500 text-green-400 p-4 rounded-xl mb-6 font-bold uppercase text-xs tracking-widest shadow-[0_0_20px_rgba(34,197,94,0.1)]'>✓ Profile Telemetry Updated Successfully</div>";
    } catch (\PDOException $e) {
        $message = "<div class='bg-red-900/50 border border-red-500 text-white p-4 rounded-xl mb-6'>Error: " . $e->getMessage() . "</div>";
    }
}

// Coureur gegevens ophalen
if ($driverId) {
    $stmt = $pdo->prepare("SELECT d.*, t.team_name, t.team_color FROM drivers d LEFT JOIN teams t ON d.team_id = t.team_id WHERE d.driver_id = :id");
    $stmt->execute([':id' => $driverId]);
    $driverDetails = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Profile | <?php echo htmlspecialchars((string)($driverDetails['last_name'] ?? 'Details')); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&family=Oswald:wght@700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        :root { --f1-red: #E10600; }
        .bg-f1-card { background: rgba(22, 22, 28, 0.9); backdrop-filter: blur(15px); }
        .bg-pattern { background-color: #0b0b0f; background-image: radial-gradient(rgba(255,255,255,0.03) 1px, transparent 0); background-size: 40px 40px; }
        .font-oswald { font-family: 'Oswald', sans-serif; }
        
        input, select, textarea { 
            background: rgba(255,255,255,0.03) !important;
            border: 1px solid rgba(255,255,255,0.1) !important;
            color: #fff !important;
            transition: all 0.3s ease;
        }
        input:focus, select:focus, textarea:focus {
            border-color: var(--f1-red) !important;
            background: rgba(255,255,255,0.07) !important;
            outline: none;
            box-shadow: 0 0 15px rgba(225, 6, 0, 0.2);
        }
        input[readonly], textarea[readonly], select[disabled] {
            background: transparent !important;
            border-color: transparent !important;
            cursor: default;
        }
        .form-label { font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em; color: #6b7280; margin-bottom: 0.5rem; display: block; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-pattern text-white font-sans min-h-screen antialiased" x-data="{ editing: false, deleteModal: false }">
    <div class="flex">
        <?php include '../nav.php'; ?>
        
        <main class="flex-grow p-6 lg:p-12">
            <div class="max-w-5xl mx-auto">
                
                <div class="flex justify-between items-center mb-8" data-aos="fade-down">
                    <a href="../drivers.php" class="text-xs font-black uppercase tracking-widest text-gray-500 hover:text-f1-red transition flex items-center gap-2">
                        <i data-lucide="arrow-left" class="w-3 h-3"></i> Back to Grid
                    </a>
                    <div class="flex gap-4">
                        <button type="button" @click="editing = !editing" class="bg-white/5 border border-white/10 px-6 py-2 rounded-full text-[10px] font-black uppercase tracking-[0.2em] hover:bg-white/10 transition" x-text="editing ? 'Cancel Edit' : 'Edit Profile'"></button>
                        <button type="button" @click="deleteModal = true" class="bg-red-600/10 border border-red-500/20 text-red-500 px-6 py-2 rounded-full text-[10px] font-black uppercase tracking-[0.2em] hover:bg-red-600 hover:text-white transition">Retire Driver</button>
                    </div>
                </div>

                <?php echo $message; ?>

                <?php if ($driverDetails): ?>
                <form method="POST" id="driverForm">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        
                        <div class="space-y-6" data-aos="fade-right">
                            <div class="bg-f1-card rounded-[2.5rem] border border-white/5 p-8 text-center relative overflow-hidden shadow-2xl">
                                <div class="absolute top-4 right-6 font-oswald text-6xl italic opacity-10 font-black" style="color: <?php echo $driverDetails['team_color'] ?? '#E10600'; ?>">
                                    #<?php echo htmlspecialchars((string)$driverDetails['driver_number']); ?>
                                </div>
                                
                                <div class="relative w-48 h-48 mx-auto mb-6">
                                    <img src="<?php echo htmlspecialchars((string)($driverDetails['image'] ?? '../assets/img/default-driver.png')); ?>" 
                                         class="w-full h-full rounded-full object-cover object-top border-4 border-white/5 shadow-2xl" 
                                         alt="Driver Image">
                                    <?php if(!empty($driverDetails['flag_url'])): ?>
                                        <img src="<?php echo htmlspecialchars((string)$driverDetails['flag_url']); ?>" class="absolute bottom-2 right-2 w-8 h-5 object-cover rounded shadow-md">
                                    <?php endif; ?>
                                </div>
                                
                                <h1 class="text-3xl font-oswald font-black uppercase italic tracking-tighter leading-none mb-2">
                                    <?php echo htmlspecialchars((string)$driverDetails['first_name']); ?> <br>
                                    <span class="text-f1-red"><?php echo htmlspecialchars((string)$driverDetails['last_name']); ?></span>
                                </h1>
                                <p class="text-gray-500 text-[10px] font-black uppercase tracking-[0.2em]">
                                    <?php echo htmlspecialchars((string)($driverDetails['team_name'] ?? 'Free Agent')); ?>
                                </p>
                            </div>

                            <div class="bg-f1-card rounded-[2.5rem] border border-white/5 p-8 transition-all" :class="editing ? 'border-f1-red/30 bg-f1-red/5' : ''">
                                <label class="form-label">Active Status</label>
                                <div class="flex items-center gap-3">
                                    <input type="checkbox" name="is_active" class="w-5 h-5 accent-red-600 rounded" <?php echo ($driverDetails['is_active']) ? 'checked' : ''; ?> :disabled="!editing">
                                    <span class="text-xs font-bold" :class="editing ? 'text-white' : 'text-gray-500'">Active Grid Member</span>
                                </div>
                            </div>
                        </div>

                        <div class="lg:col-span-2 space-y-6" data-aos="fade-left">
                            <div class="bg-f1-card rounded-[2.5rem] border border-white/5 p-10 shadow-2xl">
                                <h3 class="font-oswald font-black uppercase italic text-xl tracking-tighter mb-8 border-b border-white/5 pb-4 flex items-center gap-3">
                                    Driver <span class="text-f1-red">Telemetry Data</span>
                                    <span x-show="editing" class="text-[9px] bg-f1-red text-white px-2 py-0.5 rounded ml-auto tracking-widest uppercase" x-cloak>Edit Mode</span>
                                </h3>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="form-label">First Name</label>
                                        <input type="text" name="first_name" value="<?php echo htmlspecialchars((string)$driverDetails['first_name']); ?>" class="w-full p-3 rounded-lg text-sm font-bold" :readonly="!editing" required>
                                    </div>
                                    <div>
                                        <label class="form-label">Last Name</label>
                                        <input type="text" name="last_name" value="<?php echo htmlspecialchars((string)$driverDetails['last_name']); ?>" class="w-full p-3 rounded-lg text-sm font-bold" :readonly="!editing" required>
                                    </div>
                                    <div>
                                        <label class="form-label">Nationality</label>
                                        <input type="text" name="nationality" value="<?php echo htmlspecialchars((string)$driverDetails['nationality']); ?>" class="w-full p-3 rounded-lg text-sm font-bold" :readonly="!editing">
                                    </div>
                                    <div>
                                        <label class="form-label">Driver Number</label>
                                        <input type="number" name="driver_number" value="<?php echo htmlspecialchars((string)$driverDetails['driver_number']); ?>" class="w-full p-3 rounded-lg text-sm font-bold" :readonly="!editing">
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="form-label">Constructors Team</label>
                                        <select name="team_id" class="w-full p-3 rounded-lg text-sm font-bold" :disabled="!editing">
                                            <option value="0">Independent / No Team</option>
                                            <?php foreach ($teams as $team): ?>
                                                <option value="<?php echo $team['team_id']; ?>" <?php echo ($driverDetails['team_id'] == $team['team_id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars((string)$team['team_name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8 pt-8 border-t border-white/5">
                                    <div>
                                        <label class="form-label">World Championships</label>
                                        <input type="number" name="championships_won" value="<?php echo htmlspecialchars((string)$driverDetails['championships_won']); ?>" class="w-full p-3 rounded-lg text-sm font-bold" :readonly="!editing">
                                    </div>
                                    <div>
                                        <label class="form-label">Career Points</label>
                                        <input type="number" step="0.01" name="career_points" value="<?php echo htmlspecialchars((string)$driverDetails['career_points']); ?>" class="w-full p-3 rounded-lg text-sm font-bold" :readonly="!editing">
                                    </div>
                                </div>
                                <div class="mt-8">
                                    <label class="form-label">Biography & Legacy</label>
                                    <textarea name="description" rows="5" class="w-full p-4 rounded-xl text-sm leading-relaxed" :readonly="!editing"><?php echo htmlspecialchars((string)$driverDetails['description']); ?></textarea>
                                </div>
                                <div class="mt-8 pt-8 border-t border-white/5 space-y-6" x-show="editing" x-cloak x-transition>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label class="form-label">Date of Birth</label>
                                            <input type="date" name="date_of_birth" value="<?php echo $driverDetails['date_of_birth']; ?>" class="w-full p-3 rounded-lg text-sm font-bold">
                                        </div>
                                        <div>
                                            <label class="form-label">Place of Birth</label>
                                            <input type="text" name="place_of_birth" value="<?php echo htmlspecialchars((string)$driverDetails['place_of_birth']); ?>" class="w-full p-3 rounded-lg text-sm font-bold">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="form-label">Driver Profile Image URL</label>
                                        <input type="text" name="image" value="<?php echo htmlspecialchars((string)$driverDetails['image']); ?>" class="w-full p-3 rounded-lg text-xs font-mono">
                                    </div>
                                    <div>
                                        <label class="form-label">Flag Icon (URL or SVG)</label>
                                        <input type="text" name="flag_url" value="<?php echo htmlspecialchars((string)$driverDetails['flag_url']); ?>" class="w-full p-3 rounded-lg text-xs font-mono">
                                    </div>
                                </div>
                                <div class="mt-10 pt-10 border-t border-white/5" x-show="editing" x-cloak>
                                    <button type="submit" class="w-full md:w-auto bg-f1-red text-white px-12 py-4 rounded-full font-black text-xs uppercase tracking-[0.2em] hover:scale-105 active:scale-95 transition-all shadow-[0_10px_30px_rgba(225,6,0,0.4)]">
                                        Save All Changes
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

    <div x-show="deleteModal" class="fixed inset-0 z-[500] flex items-center justify-center p-4" x-cloak>
        <div class="fixed inset-0 bg-black/90 backdrop-blur-sm" @click="deleteModal = false"></div>
        <div class="bg-[#16161c] border border-white/10 w-full max-w-md rounded-[2.5rem] p-10 shadow-2xl relative text-center">
            <div class="absolute top-0 left-0 w-full h-1 bg-f1-red shadow-[0_0_15px_#E10600]"></div>
            <div class="w-20 h-20 bg-f1-red/10 rounded-full flex items-center justify-center mx-auto mb-6">
                <i data-lucide="trash-2" class="w-10 h-10 text-f1-red"></i>
            </div>
            <h3 class="font-oswald font-black uppercase italic text-3xl mb-4 text-white">Delete <span class="text-f1-red">Driver</span></h3>
            <p class="text-gray-400 text-sm mb-10">Weet je zeker dat je deze coureur volledig wilt verwijderen? Deze actie kan niet ongedaan worden gemaakt.</p>
            
            <form method="POST" class="flex gap-4">
                <input type="hidden" name="driver_id" value="<?php echo $driverId; ?>">
                <button type="button" @click="deleteModal = false" class="flex-1 px-6 py-4 rounded-2xl bg-white/5 text-[10px] font-black uppercase text-white hover:bg-white/10 transition">Annuleren</button>
                <button type="submit" name="delete_driver" class="flex-1 px-6 py-4 rounded-2xl bg-f1-red text-white text-[10px] font-black uppercase hover:bg-red-700 transition shadow-lg">Definitief Verwijderen</button>
            </form>
        </div>
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
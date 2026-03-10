<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../index.php");
    exit;
}
require_once '../db_config.php';
/** @var PDO $pdo */

$circuitDetails = null;
$message = '';
$circuitKey = isset($_GET['key']) ? htmlspecialchars($_GET['key']) : null;

// --- 1. VERWERK FORMULIER DATA ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $circuitKey) {
    try {
        $sql = "UPDATE circuits SET
                    title = :title, grandprix = :grandprix, location = :location,
                    country_flag_url = :country_flag_url, map_url = :map_url,
                    first_gp_year = :first_gp_year, lap_count = :lap_count,
                    circuit_length_km = :circuit_length_km, race_distance_km = :race_distance_km,
                    lap_record = :lap_record, description = :description,
                    highlights = :highlights, calendar_order = :calendar_order,
                    fp1_datetime = :fp1, fp2_datetime = :fp2, fp3_datetime = :fp3,
                    sprint_quali_datetime = :sprint_quali, sprint_datetime = :sprint,
                    quali_datetime = :quali, race_datetime = :race
                WHERE circuit_key = :circuit_key";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':title' => $_POST['title'],
            ':grandprix' => $_POST['grandprix'],
            ':location' => $_POST['location'],
            ':country_flag_url' => $_POST['country_flag_url'],
            ':map_url' => $_POST['map_url'],
            ':first_gp_year' => $_POST['first_gp_year'],
            ':lap_count' => $_POST['lap_count'],
            ':circuit_length_km' => $_POST['circuit_length_km'],
            ':race_distance_km' => $_POST['race_distance_km'],
            ':lap_record' => $_POST['lap_record'],
            ':description' => $_POST['description'],
            ':highlights' => $_POST['highlights'],
            ':calendar_order' => $_POST['calendar_order'],
            ':fp1' => $_POST['fp1_datetime'] ?: null,
            ':fp2' => $_POST['fp2_datetime'] ?: null,
            ':fp3' => $_POST['fp3_datetime'] ?: null,
            ':sprint_quali' => $_POST['sprint_quali_datetime'] ?: null,
            ':sprint' => $_POST['sprint_datetime'] ?: null,
            ':quali' => $_POST['quali_datetime'] ?: null,
            ':race' => $_POST['race_datetime'] ?: null,
            ':circuit_key' => $circuitKey
        ]);
        $message = "<div class='bg-green-500/20 border border-green-500 text-green-400 p-4 rounded-xl mb-6 font-bold uppercase text-xs tracking-widest'>✓ Telemetry & Session Data Synchronized</div>";
    } catch (\PDOException $e) {
        $message = "<div class='bg-red-900/50 border border-red-500 text-white p-4 rounded-xl mb-6'>Error: " . $e->getMessage() . "</div>";
    }
}

// --- 2. HAAL CIRCUIT DATA OP ---
if ($circuitKey) {
    $stmt = $pdo->prepare("SELECT * FROM circuits WHERE circuit_key = :circuit_key");
    $stmt->execute([':circuit_key' => $circuitKey]);
    $circuitDetails = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Circuit Details | <?php echo htmlspecialchars($circuitDetails['grandprix'] ?? 'Technical'); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&family=Oswald:wght@700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        :root { --f1-red: #E10600; }
        .bg-f1-card { background: rgba(22, 22, 28, 0.9); backdrop-filter: blur(15px); }
        .bg-pattern { background-color: #0b0b0f; background-image: radial-gradient(rgba(255,255,255,0.03) 1px, transparent 0); background-size: 40px 40px; }
        .font-oswald { font-family: 'Oswald', sans-serif; }
        input, textarea { 
            background: rgba(255,255,255,0.03) !important;
            border: 1px solid rgba(255,255,255,0.1) !important;
            color: #fff !important;
            transition: all 0.3s ease;
        }
        input:focus, textarea:focus {
            border-color: var(--f1-red) !important;
            background: rgba(255,255,255,0.07) !important;
            outline: none;
            box-shadow: 0 0 15px rgba(225, 6, 0, 0.2);
        }
        input[readonly], textarea[readonly] {
            background: transparent !important;
            border-color: transparent !important;
            cursor: default;
        }
        .form-label { font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em; color: #6b7280; margin-bottom: 0.5rem; display: block; }
        /* Style for datetime-local icon */
        ::-webkit-calendar-picker-indicator { filter: invert(1); opacity: 0.5; cursor: pointer; }
    </style>
</head>
<body class="bg-pattern text-white font-sans min-h-screen overflow-x-hidden">
    <div class="flex min-h-screen">
        <?php include '../nav.php'; ?>
        <main class="flex-grow p-6 lg:p-12">
            <div class="max-w-6xl mx-auto">
                <div class="flex justify-between items-center mb-8" data-aos="fade-down">
                    <a href="bewerken/circuits.php" class="text-xs font-black uppercase tracking-widest text-gray-500 hover:text-f1-red transition flex items-center gap-2">
                        <span>←</span> Back to Schedule
                    </a>
                    <button type="button" id="editButton" class="bg-white/5 border border-white/10 px-8 py-3 rounded-full text-[10px] font-black uppercase tracking-[0.2em] hover:bg-white/10 transition">
                        Edit Circuit & Times
                    </button>
                </div>

                <?php echo $message; ?>

                <?php if ($circuitDetails): ?>
                <form method="POST" id="circuitForm">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                        
                        <div class="lg:col-span-4 space-y-6" data-aos="fade-right">
                            <div class="bg-f1-card rounded-[2.5rem] border border-white/5 p-8 relative overflow-hidden group">
                                <div class="absolute top-6 right-8 font-oswald text-6xl italic opacity-5 font-black">
                                    #<?php echo htmlspecialchars($circuitDetails['calendar_order']); ?>
                                </div>
                                <img src="<?php echo htmlspecialchars($circuitDetails['map_url']); ?>" class="w-full h-auto mb-6 transform group-hover:scale-105 transition-transform duration-700" alt="Circuit Map">
                                <div class="text-center">
                                    <h1 class="text-2xl font-oswald font-black uppercase italic tracking-tighter leading-none mb-2">
                                        <?php echo htmlspecialchars($circuitDetails['grandprix']); ?>
                                    </h1>
                                    <p class="text-f1-red text-[10px] font-black uppercase tracking-[0.2em]">
                                        <?php echo htmlspecialchars($circuitDetails['location']); ?>
                                    </p>
                                </div>
                            </div>

                            <div class="bg-f1-card rounded-[2.5rem] border border-white/5 p-8">
                                <label class="form-label">Calendar Sequence</label>
                                <input type="number" name="calendar_order" value="<?php echo htmlspecialchars($circuitDetails['calendar_order']); ?>" class="w-full p-4 rounded-xl text-xl font-oswald italic font-black" readonly>
                            </div>
                        </div>

                        <div class="lg:col-span-8 space-y-6" data-aos="fade-left">
                            <div class="bg-f1-card rounded-[2.5rem] border border-white/5 p-10">
                                
                                <div class="flex items-center gap-4 mb-8">
                                    <div class="w-2 h-8 bg-f1-red rounded-full"></div>
                                    <h3 class="font-oswald font-black uppercase italic text-2xl tracking-tighter">Technical <span class="text-f1-red">Specs</span></h3>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                    <div>
                                        <label class="form-label">Circuit Official Name</label>
                                        <input type="text" name="title" value="<?php echo htmlspecialchars($circuitDetails['title']); ?>" class="w-full p-3 rounded-lg text-sm font-bold" readonly required>
                                    </div>
                                    <div>
                                        <label class="form-label">Grand Prix Title</label>
                                        <input type="text" name="grandprix" value="<?php echo htmlspecialchars($circuitDetails['grandprix']); ?>" class="w-full p-3 rounded-lg text-sm font-bold" readonly required>
                                    </div>
                                </div>

                                <div class="mt-12 pt-10 border-t border-white/5">
                                    <div class="flex items-center gap-4 mb-8">
                                        <div class="w-2 h-8 bg-f1-red rounded-full"></div>
                                        <h3 class="font-oswald font-black uppercase italic text-2xl tracking-tighter">Weekend <span class="text-f1-red">Schedule</span></h3>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <?php 
                                        $sessions = [
                                            'fp1_datetime' => 'Practice 1',
                                            'fp2_datetime' => 'Practice 2',
                                            'fp3_datetime' => 'Practice 3',
                                            'sprint_quali_datetime' => 'Sprint Qualifying',
                                            'sprint_datetime' => 'Sprint Race',
                                            'quali_datetime' => 'Official Qualifying',
                                            'race_datetime' => 'Main Grand Prix'
                                        ];
                                        foreach ($sessions as $name => $label): 
                                            $val = !empty($circuitDetails[$name]) ? date('Y-m-d\TH:i', strtotime($circuitDetails[$name])) : '';
                                        ?>
                                            <div class="bg-white/[0.02] p-4 rounded-2xl border border-white/5">
                                                <label class="form-label"><?php echo $label; ?></label>
                                                <input type="datetime-local" name="<?php echo $name; ?>" value="<?php echo $val; ?>" class="w-full p-2 rounded-lg text-xs font-bold" readonly>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-10 p-6 bg-white/[0.02] rounded-3xl border border-white/5">
                                    <div>
                                        <label class="form-label">Total Laps</label>
                                        <input type="number" name="lap_count" value="<?php echo htmlspecialchars($circuitDetails['lap_count']); ?>" class="w-full p-3 rounded-lg text-sm font-bold" readonly>
                                    </div>
                                    <div>
                                        <label class="form-label">Lap Length (KM)</label>
                                        <input type="number" step="0.001" name="circuit_length_km" value="<?php echo htmlspecialchars($circuitDetails['circuit_length_km']); ?>" class="w-full p-3 rounded-lg text-sm font-bold text-f1-red" readonly>
                                    </div>
                                    <div>
                                        <label class="form-label">Total Distance (KM)</label>
                                        <input type="number" step="0.001" name="race_distance_km" value="<?php echo htmlspecialchars($circuitDetails['race_distance_km']); ?>" class="w-full p-3 rounded-lg text-sm font-bold" readonly>
                                    </div>
                                </div>

                                <div class="mt-8">
                                    <label class="form-label">Circuit Profile / Description</label>
                                    <textarea name="description" rows="5" class="w-full p-4 rounded-2xl text-sm leading-relaxed" readonly><?php echo htmlspecialchars($circuitDetails['description']); ?></textarea>
                                </div>

                                <div id="assetFields" class="hidden mt-10 pt-10 border-t border-white/5 space-y-6">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label class="form-label">Map Image URL</label>
                                            <input type="text" name="map_url" value="<?php echo htmlspecialchars($circuitDetails['map_url']); ?>" class="w-full p-3 rounded-lg text-xs font-mono">
                                        </div>
                                        <div>
                                            <label class="form-label">Country Flag URL</label>
                                            <input type="text" name="country_flag_url" value="<?php echo htmlspecialchars($circuitDetails['country_flag_url']); ?>" class="w-full p-3 rounded-lg text-xs font-mono">
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-10">
                                    <button type="submit" id="saveButton" class="hidden bg-f1-red text-white px-12 py-4 rounded-full font-black text-[11px] uppercase tracking-[0.2em] hover:scale-105 transition shadow-[0_0_25px_rgba(225,6,0,0.4)]">
                                        Commit Changes
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

            const editBtn = document.getElementById('editButton');
            const saveBtn = document.getElementById('saveButton');
            const assetFields = document.getElementById('assetFields');
            const inputs = document.querySelectorAll('#circuitForm input, #circuitForm textarea');

            editBtn.addEventListener('click', () => {
                inputs.forEach(el => el.readOnly = false);
                assetFields.classList.remove('hidden');
                saveBtn.classList.remove('hidden');
                editBtn.classList.add('hidden');
                document.querySelector('input[name="title"]').focus();
            });
        });
    </script>
</body>
</html>
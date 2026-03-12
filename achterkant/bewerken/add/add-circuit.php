<?php
session_start();
if (!isset($_SESSION['logged_in'])) { header("Location: ../../index.php"); exit; }
require_once '../db_config.php';
/** @var PDO $pdo */

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $sql = "INSERT INTO circuits (
                    circuit_key, title, grandprix, location, map_url, first_gp_year,
                    lap_count, circuit_length_km, race_distance_km, lap_record, description,
                    highlights, calendar_order, race_year, race_datetime, country_flag_url
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $_POST['circuit_key'],
            $_POST['title'],
            $_POST['grandprix'],
            $_POST['location'],
            $_POST['map_url'],
            $_POST['first_gp_year'] ?: null,
            $_POST['lap_count'] ?: null,
            $_POST['circuit_length_km'] ?: 0.000,
            $_POST['race_distance_km'] ?: 0.000,
            $_POST['lap_record'],
            $_POST['description'],
            $_POST['highlights'],
            $_POST['calendar_order'],
            $_POST['race_year'] ?: date('Y'),
            $_POST['race_datetime'],
            $_POST['country_flag_url']
        ]);
        $message = "success";
    } catch (PDOException $e) {
        $message = "error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <?php include '../../head.php'; ?>
    <style>
        .form-input { 
            background: rgba(255,255,255,0.03); 
            border: 1px solid rgba(255,255,255,0.1); 
            transition: all 0.3s ease;
        }
        .form-input:focus { 
            background: rgba(255,255,255,0.07); 
            border-color: #E10600; 
            outline: none; 
            box-shadow: 0 0 15px rgba(225, 6, 0, 0.1);
        }
        .f1-card { background: rgba(22, 22, 28, 0.7); backdrop-filter: blur(15px); border: 1px solid rgba(255,255,255,0.05); }
    </style>
</head>
<body class="bg-[#0b0b0f] text-white font-sans">

    <div class="flex min-h-screen">
        <?php include '../../nav.php'; ?>
        
        <main class="flex-grow p-6 lg:p-12 mt-16 lg:mt-0">
            <div class="max-w-6xl mx-auto">
                
                <header class="mb-10" data-aos="fade-down">
                    <a href="../schedule.php" class="text-gray-500 hover:text-white text-xs font-black uppercase tracking-widest flex items-center gap-2 mb-4">
                        <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Calendar
                    </a>
                    <h1 class="text-4xl font-oswald font-black italic uppercase tracking-tighter">Construct <span class="text-f1-red">New Circuit</span></h1>
                </header>

                <?php if ($message === "success"): ?>
                    <div class="bg-green-500/10 border border-green-500/50 p-4 rounded-2xl mb-8 flex items-center gap-4 text-green-400 font-bold uppercase text-xs tracking-widest animate-pulse">
                        <i data-lucide="check-circle"></i> Circuit successfully added to the database!
                    </div>
                <?php elseif (str_contains($message, 'error')): ?>
                    <div class="bg-red-500/10 border border-red-500/50 p-4 rounded-2xl mb-8 text-red-400 font-bold text-xs">
                        <?= htmlspecialchars($message) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    
                    <div class="lg:col-span-2 space-y-6">
                        <div class="f1-card p-8 rounded-[2.5rem]" data-aos="fade-right">
                            <h3 class="text-f1-red font-black text-[10px] uppercase tracking-[0.2em] mb-6">General Specifications</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="text-[10px] font-black uppercase text-gray-500 tracking-widest mb-2 block">Circuit Key (Unique)</label>
                                    <input type="text" name="circuit_key" required placeholder="zandvoort" class="form-input w-full p-4 rounded-xl text-sm font-bold">
                                </div>
                                <div>
                                    <label class="text-[10px] font-black uppercase text-gray-500 tracking-widest mb-2 block">Circuit Name</label>
                                    <input type="text" name="title" required placeholder="Circuit Zandvoort" class="form-input w-full p-4 rounded-xl text-sm font-bold">
                                </div>
                                <div>
                                    <label class="text-[10px] font-black uppercase text-gray-500 tracking-widest mb-2 block">Grand Prix Name</label>
                                    <input type="text" name="grandprix" required placeholder="Dutch Grand Prix" class="form-input w-full p-4 rounded-xl text-sm font-bold">
                                </div>
                                <div>
                                    <label class="text-[10px] font-black uppercase text-gray-500 tracking-widest mb-2 block">Location</label>
                                    <input type="text" name="location" required placeholder="Zandvoort, Netherlands" class="form-input w-full p-4 rounded-xl text-sm font-bold">
                                </div>
                            </div>

                            <div class="mt-6">
                                <label class="text-[10px] font-black uppercase text-gray-500 tracking-widest mb-2 block">Description</label>
                                <textarea name="description" rows="4" class="form-input w-full p-4 rounded-xl text-sm font-bold" placeholder="Historisch duizencircuit..."></textarea>
                            </div>
                        </div>

                        <div class="f1-card p-8 rounded-[2.5rem]" data-aos="fade-right" data-aos-delay="100">
                            <h3 class="text-f1-red font-black text-[10px] uppercase tracking-[0.2em] mb-6">Technical Data</h3>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                <div>
                                    <label class="text-[10px] font-black uppercase text-gray-500 tracking-widest mb-2 block">Laps</label>
                                    <input type="number" name="lap_count" class="form-input w-full p-4 rounded-xl text-sm font-bold">
                                </div>
                                <div>
                                    <label class="text-[10px] font-black uppercase text-gray-500 tracking-widest mb-2 block">Length (km)</label>
                                    <input type="number" step="0.001" name="circuit_length_km" class="form-input w-full p-4 rounded-xl text-sm font-bold">
                                </div>
                                <div>
                                    <label class="text-[10px] font-black uppercase text-gray-500 tracking-widest mb-2 block">Race Dist.</label>
                                    <input type="number" step="0.001" name="race_distance_km" class="form-input w-full p-4 rounded-xl text-sm font-bold">
                                </div>
                                <div>
                                    <label class="text-[10px] font-black uppercase text-gray-500 tracking-widest mb-2 block">1st GP Year</label>
                                    <input type="number" name="first_gp_year" class="form-input w-full p-4 rounded-xl text-sm font-bold">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-6" data-aos="fade-left">
                        <div class="f1-card p-8 rounded-[2.5rem] border-t-4 border-t-f1-red">
                            <h3 class="text-[10px] font-black text-gray-500 uppercase tracking-widest mb-6">Scheduling</h3>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="text-[10px] font-black uppercase text-gray-400 mb-2 block">Race Year</label>
                                    <input type="number" name="race_year" value="<?= date('Y') ?>" required class="form-input w-full p-4 rounded-xl text-sm font-bold">
                                </div>
                                <div>
                                    <label class="text-[10px] font-black uppercase text-gray-400 mb-2 block">Calendar Order</label>
                                    <input type="number" name="calendar_order" required class="form-input w-full p-4 rounded-xl text-sm font-bold">
                                </div>
                                <div>
                                    <label class="text-[10px] font-black uppercase text-gray-400 mb-2 block">Race Date & Time</label>
                                    <input type="datetime-local" name="race_datetime" required class="form-input w-full p-4 rounded-xl text-sm font-bold">
                                </div>
                            </div>
                        </div>

                        <div class="f1-card p-8 rounded-[2.5rem]">
                            <h3 class="text-[10px] font-black text-gray-500 uppercase tracking-widest mb-6">Assets</h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="text-[10px] font-black uppercase text-gray-400 mb-2 block">Track Map URL</label>
                                    <input type="url" name="map_url" id="map_input" class="form-input w-full p-4 rounded-xl text-sm font-bold text-xs">
                                </div>
                                <div>
                                    <label class="text-[10px] font-black uppercase text-gray-400 mb-2 block">Country Flag URL</label>
                                    <input type="url" name="country_flag_url" class="form-input w-full p-4 rounded-xl text-sm font-bold text-xs">
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="w-full bg-f1-red hover:bg-red-700 text-white font-black uppercase tracking-[0.2em] py-6 rounded-3xl transition-all shadow-lg shadow-f1-red/20 text-xs flex items-center justify-center gap-3">
                            <i data-lucide="plus-circle" class="w-5 h-5"></i> Add Circuit to Paddock
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
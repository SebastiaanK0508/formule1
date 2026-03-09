<?php
session_start();
if (!isset($_SESSION['logged_in'])) { header("Location: ../../index.php"); exit; }
require_once '../db_config.php';
/** @var PDO $pdo */

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titel = $_POST['titel'];
    $artikel_url = $_POST['artikel_url'];
    $afbeelding_url = $_POST['afbeelding_url'];
    $source = $_POST['source'];
    $pub_date = $_POST['publicatie_datum'] ?: date('Y-m-d H:i:s');

    try {
        $stmt = $pdo->prepare("INSERT INTO f1_nieuws (titel, artikel_url, afbeelding_url, source, publicatie_datum) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$titel, $artikel_url, $afbeelding_url, $source, $pub_date]);
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
            <div class="max-w-4xl mx-auto">
                
                <header class="mb-10" data-aos="fade-down">
                    <a href="dashboard.php" class="text-gray-500 hover:text-white text-xs font-black uppercase tracking-widest flex items-center gap-2 mb-4">
                        <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Paddock
                    </a>
                    <h1 class="text-4xl font-oswald font-black italic uppercase tracking-tighter">Deploy <span class="text-f1-red">New Article</span></h1>
                </header>

                <?php if ($message === "success"): ?>
                    <div class="bg-green-500/10 border border-green-500/50 p-4 rounded-2xl mb-8 flex items-center gap-4 text-green-400 font-bold uppercase text-xs tracking-widest animate-pulse">
                        <i data-lucide="check-circle"></i> Article deployed to live feed!
                    </div>
                <?php endif; ?>

                <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">
                    <div class="lg:col-span-3 f1-card p-8 rounded-[2.5rem]" data-aos="fade-right">
                        <form method="POST" class="space-y-6">
                            <div>
                                <label class="text-[10px] font-black uppercase text-gray-500 tracking-widest mb-2 block">Article Title</label>
                                <input type="text" name="titel" required placeholder="Verstappen domineert in..." class="form-input w-full p-4 rounded-xl text-sm font-bold">
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="text-[10px] font-black uppercase text-gray-500 tracking-widest mb-2 block">Source</label>
                                    <input type="text" name="source" placeholder="Motorsport.com" class="form-input w-full p-4 rounded-xl text-sm font-bold">
                                </div>
                                <div>
                                    <label class="text-[10px] font-black uppercase text-gray-500 tracking-widest mb-2 block">Pub. Date</label>
                                    <input type="datetime-local" name="publicatie_datum" class="form-input w-full p-4 rounded-xl text-sm font-bold text-gray-400">
                                </div>
                            </div>

                            <div>
                                <label class="text-[10px] font-black uppercase text-gray-500 tracking-widest mb-2 block">Source URL (Redirect)</label>
                                <input type="url" name="artikel_url" required placeholder="https://..." class="form-input w-full p-4 rounded-xl text-sm font-bold">
                            </div>

                            <div>
                                <label class="text-[10px] font-black uppercase text-gray-500 tracking-widest mb-2 block">Image URL</label>
                                <input type="url" name="afbeelding_url" id="img_input" placeholder="https://..." class="form-input w-full p-4 rounded-xl text-sm font-bold">
                            </div>

                            <button type="submit" class="w-full bg-f1-red hover:bg-red-700 text-white font-black uppercase tracking-[0.2em] py-5 rounded-2xl transition-all shadow-lg shadow-f1-red/20 text-xs">
                                Confirm & Upload Article
                            </button>
                        </form>
                    </div>

                    <div class="lg:col-span-2 space-y-6" data-aos="fade-left">
                        <div class="f1-card p-6 rounded-[2.5rem] border-t-4 border-t-f1-red">
                            <h3 class="text-[10px] font-black text-gray-500 uppercase tracking-widest mb-6">Article Preview</h3>
                            
                            <div class="rounded-2xl overflow-hidden bg-black/40 mb-4 aspect-video relative flex items-center justify-center border border-white/5">
                                <img id="preview_img" src="https://via.placeholder.com/800x450/111/333?text=F1SITE.NL" class="w-full h-full object-cover opacity-60">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/80 to-transparent"></div>
                            </div>

                            <p id="preview_source" class="text-f1-red font-black text-[9px] uppercase tracking-widest mb-2">Source Name</p>
                            <h4 id="preview_title" class="text-xl font-oswald font-black italic uppercase tracking-tighter leading-tight mb-4 text-gray-400">Title will appear here...</h4>
                            
                            <div class="flex items-center gap-2 text-gray-600">
                                <i data-lucide="calendar" class="w-3 h-3"></i>
                                <span class="text-[9px] font-bold uppercase tracking-tighter">Live Date Deployment</span>
                            </div>
                        </div>

                        <div class="p-6 bg-white/5 rounded-3xl border border-white/5">
                            <h4 class="text-[10px] font-black uppercase text-gray-400 mb-2">Engineer Tip</h4>
                            <p class="text-[10px] text-gray-500 italic leading-relaxed">Zorg dat de Image URL eindigt op .jpg of .png voor de beste weergave in de frontend app.</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        lucide.createIcons();
        const titleInput = document.querySelector('input[name="titel"]');
        const sourceInput = document.querySelector('input[name="source"]');
        const imgInput = document.getElementById('img_input');
        titleInput.addEventListener('input', (e) => {
            document.getElementById('preview_title').innerText = e.target.value || "Title will appear here...";
            document.getElementById('preview_title').classList.toggle('text-white', e.target.value);
            document.getElementById('preview_title').classList.toggle('text-gray-400', !e.target.value);
        });
        sourceInput.addEventListener('input', (e) => {
            document.getElementById('preview_source').innerText = e.target.value || "Source Name";
        });
        imgInput.addEventListener('input', (e) => {
            if(e.target.value) {
                document.getElementById('preview_img').src = e.target.value;
                document.getElementById('preview_img').classList.remove('opacity-60');
            }
        });
    </script>
</body>
</html>
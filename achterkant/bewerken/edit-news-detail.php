<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../index.php");
    exit;
}
require_once '../db_config.php';
/** @var PDO $pdo */

$newsItem = null;
$message = '';
$newsId = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_POST['news_id_hidden']) ? (int)$_POST['news_id_hidden'] : null);

if ($newsId) {
    try {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['news_id_hidden'])) {
            $stmt = $pdo->prepare("UPDATE f1_nieuws SET 
                titel = :titel, 
                publicatie_datum = :publicatie_datum, 
                afbeelding_url = :afbeelding_url, 
                source = :source 
                WHERE id = :id");
            
            $stmt->execute([
                ':titel'            => $_POST['titel'],
                ':publicatie_datum' => $_POST['publicatie_datum'],
                ':afbeelding_url'   => $_POST['afbeelding_url'],
                ':source'           => $_POST['source'],
                ':id'               => $newsId
            ]);
            $message = "<div class='bg-green-500/20 border border-green-500 text-green-400 p-4 rounded-xl mb-6 font-bold uppercase text-xs tracking-widest' data-aos='zoom-in'>✓ Article Updated Successfully</div>";
        }

        $stmt = $pdo->prepare("SELECT * FROM f1_nieuws WHERE id = :id");
        $stmt->execute([':id' => $newsId]);
        $newsItem = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$newsItem) {
            $message = "<div class='bg-red-900/50 border border-red-500 text-white p-4 rounded-xl mb-6'>Article not found.</div>";
        }
    } catch (\PDOException $e) {
        $message = "<div class='bg-red-900/50 border border-red-500 text-white p-4 rounded-xl mb-6'>Database Error: " . $e->getMessage() . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Article | F1 Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&family=Oswald:wght@700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        :root { --f1-red: #E10600; }
        .bg-f1-card { background: rgba(22, 22, 28, 0.9); backdrop-filter: blur(15px); }
        .bg-pattern { background-color: #0b0b0f; background-image: radial-gradient(rgba(255,255,255,0.03) 1px, transparent 0); background-size: 40px 40px; }
        .font-oswald { font-family: 'Oswald', sans-serif; }
        
        input { 
            background: rgba(255,255,255,0.03) !important;
            border: 1px solid rgba(255,255,255,0.1) !important;
            color: #fff !important;
            transition: all 0.3s ease;
        }
        input:focus {
            border-color: var(--f1-red) !important;
            background: rgba(255,255,255,0.07) !important;
            outline: none;
            box-shadow: 0 0 15px rgba(225, 6, 0, 0.2);
        }
        .form-label { font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em; color: #6b7280; margin-bottom: 0.5rem; display: block; }
    </style>
</head>
<body class="bg-pattern text-white font-sans min-h-screen">
    <div class="flex">
        <?php include '../nav.php'; ?>
        <main class="flex-grow p-6 lg:p-12">
            <div class="max-w-6xl mx-auto">
                <div class="mb-8" data-aos="fade-down">
                    <a href="bewerken/nieuws.php" class="text-xs font-black uppercase tracking-widest text-gray-500 hover:text-f1-red transition flex items-center gap-2">
                        <span>←</span> Back to Newsroom
                    </a>
                </div>

                <?php echo $message; ?>

                <?php if ($newsItem): ?>
                <form method="POST">
                    <input type="hidden" name="news_id_hidden" value="<?php echo $newsItem['id']; ?>">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                        
                        <div class="lg:col-span-8 space-y-6" data-aos="fade-right">
                            <div class="bg-f1-card rounded-[2.5rem] border border-white/5 p-8 lg:p-10">
                                <div class="mb-8">
                                    <label class="form-label">Article Headline</label>
                                    <input type="text" name="titel" value="<?php echo htmlspecialchars($newsItem['titel'] ?? ''); ?>" 
                                           class="w-full p-4 rounded-xl text-xl font-oswald font-black uppercase italic tracking-tight" required>
                                </div>
                                
                                <div>
                                    <label class="form-label">Article Link (Original Source URL)</label>
                                    <input type="url" name="artikel_url" value="<?php echo htmlspecialchars($newsItem['artikel_url'] ?? ''); ?>" 
                                           class="w-full p-4 rounded-xl text-xs font-mono text-f1-red">
                                </div>
                            </div>
                        </div>

                        <div class="lg:col-span-4 space-y-6" data-aos="fade-left">
                            <div class="bg-f1-card rounded-[2.5rem] border border-white/5 p-8">
                                <h3 class="font-oswald font-black uppercase italic text-lg mb-6 border-b border-white/5 pb-2 text-f1-red">Publishing Info</h3>
                                
                                <div class="space-y-6">
                                    <div>
                                        <label class="form-label">Publication Date</label>
                                        <input type="date" name="publicatie_datum" 
                                               value="<?php echo $newsItem['publicatie_datum'] ? date('Y-m-d', strtotime($newsItem['publicatie_datum'])) : ''; ?>" 
                                               class="w-full p-3 rounded-lg text-xs font-bold uppercase" required>
                                    </div>

                                    <div>
                                        <label class="form-label">Article Source Name</label>
                                        <input type="text" name="source" value="<?php echo htmlspecialchars($newsItem['source'] ?? ''); ?>" 
                                               class="w-full p-3 rounded-lg text-xs font-bold" placeholder="bijv. Sky Sports">
                                    </div>
                                </div>
                            </div>

                            <div class="bg-f1-card rounded-[2.5rem] border border-white/5 p-8">
                                <h3 class="font-oswald font-black uppercase italic text-lg mb-6 border-b border-white/5 pb-2">Media</h3>
                                <label class="form-label">Header Image URL</label>
                                <input type="text" name="afbeelding_url" value="<?php echo htmlspecialchars($newsItem['afbeelding_url'] ?? ''); ?>" 
                                       class="w-full p-3 rounded-lg text-[10px] font-mono mb-4">
                                
                                <?php if (!empty($newsItem['afbeelding_url'])): ?>
                                    <img src="<?php echo htmlspecialchars($newsItem['afbeelding_url']); ?>" class="w-full rounded-xl border border-white/10" alt="Preview">
                                <?php endif; ?>
                            </div>

                            <div class="flex flex-col gap-3">
                                <button type="submit" class="w-full bg-f1-red text-white py-4 rounded-full font-black text-xs uppercase tracking-widest hover:scale-[1.02] transition shadow-lg shadow-red-600/20">
                                    Update Article
                                </button>
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
        });
    </script>
</body>
</html>
<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../index.php");
    exit;
}
require_once '../db_config.php';
/** @var PDO $pdo */
$search = $_GET['search'] ?? '';
$news = [];
try {
    if (!empty($search)) {
        $sql = "SELECT * FROM f1_nieuws 
                WHERE titel LIKE :search 
                ORDER BY id DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':search', '%' . $search . '%');
    } else {
        $sql = "SELECT * FROM f1_nieuws 
                ORDER BY id DESC";
        $stmt = $pdo->prepare($sql);
    }
    $stmt->execute();
    $news = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (\PDOException $e) {
    error_log("Fout bij het ophalen van nieuws: " . $e->getMessage());
    $db_error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Newsroom Control | F1 Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&family=Oswald:wght@700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        :root { --f1-red: #E10600; }
        .bg-f1-card { background: rgba(22, 22, 28, 0.8); backdrop-filter: blur(10px); }
        .bg-pattern { background-color: #0b0b0f; background-image: radial-gradient(rgba(255,255,255,0.03) 1px, transparent 0); background-size: 40px 40px; }
        .font-oswald { font-family: 'Oswald', sans-serif; }
        
        tr { transition: all 0.3s ease; cursor: pointer; border-left: 4px solid transparent; }
        tr:hover { 
            background: rgba(255, 255, 255, 0.03) !important; 
            border-left-color: var(--f1-red);
        }

        .delete-btn {
            background: rgba(220, 38, 38, 0.1);
            color: #ef4444;
            border: 1px solid rgba(220, 38, 38, 0.2);
            transition: all 0.3s ease;
        }
        .delete-btn:hover {
            background: #dc2626;
            color: white;
            box-shadow: 0 0 15px rgba(220, 38, 38, 0.4);
        }
        .search-bar {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
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
                        <h2 class="text-5xl font-oswald font-black uppercase italic tracking-tighter leading-none">
                            News <span class="text-f1-red">Control</span>
                        </h2>
                        <p class="text-gray-500 text-[10px] font-black uppercase tracking-[0.2em] mt-2 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-f1-red animate-pulse"></span>
                            Editorial Content Management
                        </p>
                    </div>
                    <div class="flex flex-wrap items-center gap-4 w-full lg:w-auto">
                        <form method="GET" class="flex gap-2 w-full lg:w-auto">
                            <input type="text" name="search" placeholder="Search articles..." 
                                   value="<?php echo htmlspecialchars($search); ?>" 
                                   class="search-bar px-6 py-3 rounded-full text-xs font-bold w-full lg:w-64 text-white focus:outline-none focus:border-f1-red transition">
                            <button type="submit" class="bg-white/5 hover:bg-white/10 border border-white/10 px-6 py-3 rounded-full text-[10px] font-black uppercase tracking-widest transition">
                                Search
                            </button>
                        </form>
                        <a href="add/add-news.php" class="bg-f1-red text-white px-8 py-3 rounded-full font-black text-[10px] uppercase tracking-[0.2em] hover:scale-105 transition shadow-[0_0_20px_rgba(225,6,0,0.3)]">
                            + New Article
                        </a>
                    </div>
                </header>
                <div class="bg-f1-card rounded-[2.5rem] border border-white/5 overflow-hidden shadow-2xl" data-aos="fade-up">
                    <div class="p-8 border-b border-white/5 flex justify-between items-center bg-white/2">
                        <span class="text-[10px] font-black text-gray-500 uppercase tracking-widest italic">Double-click to edit article body</span>
                        <span class="text-[10px] font-black text-f1-red uppercase tracking-widest"><?php echo count($news); ?> Articles Published</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] border-b border-white/5">
                                    <th class="p-8">Article Headline</th>
                                    <th class="p-8">Publication Date</th>
                                    <th class="p-8 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5">
                                <?php if (!empty($news)): ?>
                                    <?php foreach ($news as $item): ?>
                                        <tr class="group" data-newsid="<?php echo htmlspecialchars($item['id']); ?>">
                                            <td class="p-8">
                                                <p class="font-bold text-base text-gray-100 group-hover:text-white transition line-clamp-1">
                                                    <?php echo htmlspecialchars($item['titel']); ?>
                                                </p>
                                                <p class="text-[9px] text-gray-600 font-bold uppercase tracking-widest mt-1">Source: <?php echo htmlspecialchars($item['source'] ?? 'Internal'); ?></p>
                                            </td>
                                            <td class="p-8">
                                                <span class="text-xs font-black text-gray-500 uppercase tracking-tighter">
                                                    <?php 
                                                        echo !empty($item['publicatie_datum']) ? date('d M Y', strtotime($item['publicatie_datum'])) : 'No Date'; 
                                                    ?>
                                                </span>
                                            </td>
                                            <td class="p-8 text-right">
                                                <button 
                                                    class="delete-btn px-6 py-2 rounded-full text-[9px] font-black uppercase tracking-widest" 
                                                    data-id="<?php echo htmlspecialchars($item['id']); ?>"
                                                    data-title="<?php echo htmlspecialchars($item['titel']); ?>"
                                                >Delete</button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="p-20 text-center">
                                            <p class="text-gray-500 font-oswald uppercase italic text-lg tracking-widest">No articles found in archives...</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div> <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            AOS.init({ duration: 800, once: true });
            const tableRows = document.querySelectorAll('tbody tr');
            tableRows.forEach(row => {
                row.addEventListener('dblclick', function() {
                    const newsId = this.dataset.newsid;
                    if (newsId) {
                        window.location.href = 'bewerken/edit-news-detail.php?id=' + newsId;
                    }
                });
            });
            const deleteButtons = document.querySelectorAll('.delete-btn');
            deleteButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const newsId = this.dataset.id;
                    const title = this.dataset.title;
                    
                    if (confirm(`Weet je zeker dat je "${title}" wilt verwijderen?`)) {
                        fetch('delete_news.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: `id=${newsId}`
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                this.closest('tr').style.opacity = '0';
                                setTimeout(() => this.closest('tr').remove(), 300);
                            } else {
                                alert('Fout: ' + data.message);
                            }
                        })
                        .catch(err => alert('Netwerkfout bij verwijderen.'));
                    }
                });
            });
        });
    </script>
</body>
</html>
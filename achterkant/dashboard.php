<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}
require_once 'db_config.php';
try {
    $countStmt = $pdo->query("SELECT COUNT(*) FROM f1_nieuws");
    $totalArticles = $countStmt->fetchColumn();
    $newsStmt = $pdo->query("SELECT id, titel, source, publicatie_datum FROM f1_nieuws ORDER BY publicatie_datum DESC LIMIT 4");
    $articles = $newsStmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Dashboard Database Fout: " . $e->getMessage());
    $articles = [];
    $totalArticles = 0;
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <?php include 'head.php'; ?>
    <style>
        .bg-f1-card { background: rgba(22, 22, 28, 0.8); backdrop-filter: blur(10px); }
        .sidebar-link:hover { background: linear-gradient(90deg, rgba(225, 6, 0, 0.1) 0%, transparent 100%); border-left: 3px solid #E10600; }
        .stat-card { border-bottom: 1px solid rgba(255,255,255,0.05); transition: all 0.3s ease; }
        .stat-card:hover { transform: translateY(-5px); border-bottom-color: #E10600; }
        .glow-red { box-shadow: 0 0 20px rgba(225, 6, 0, 0.15); }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #0b0b0f; }
        ::-webkit-scrollbar-thumb { background: #333; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #E10600; }
    </style>
</head>
<body class="bg-pattern text-white font-sans overflow-x-hidden">

    <div class="lg:hidden bg-f1-dark p-4 border-b border-white/10 flex justify-between items-center sticky top-0 z-50">
        <h1 class="font-oswald font-black italic text-xl uppercase tracking-tighter">F1<span class="text-f1-red">SITE</span></h1>
        <button class="bg-white/5 p-2 rounded text-xs font-bold uppercase tracking-widest border border-white/10">Menu</button>
    </div>

    <div class="flex min-h-screen">
        
        <?php include 'nav.php'; ?>

        <main class="flex-grow p-6 lg:p-12">
            
            <header class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-12" data-aos="fade-down">
                <div>
                    <h2 class="text-4xl lg:text-5xl font-oswald font-black uppercase italic tracking-tighter leading-none">
                        Paddock <span class="text-f1-red">Overview</span>
                    </h2>
                    <p class="text-gray-500 text-sm mt-2 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                        Telemetry active for engineer: <span class="text-white font-bold"><?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Unknown'); ?></span>
                    </p>
                </div>
                <div class="flex gap-4">
                    <a href="add_news.php" class="bg-f1-red text-white px-8 py-4 rounded-full font-black text-[11px] uppercase tracking-[0.2em] hover:scale-105 hover:shadow-[0_0_20px_rgba(225,6,0,0.4)] transition-all duration-300">
                        + Deploy News
                    </a>
                </div>
            </header>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
                <?php 
                $stats = [
                    ['title' => 'Total Articles', 'val' => number_format($totalArticles, 0, ',', '.'), 'trend' => 'Live Database'],
                    ['title' => 'System Health', 'val' => '99.8%', 'trend' => 'Optimal'],
                    ['title' => 'API Uptime', 'val' => '100%', 'trend' => 'Stable'],
                    ['title' => 'DB Latency', 'val' => '12ms', 'trend' => 'Fast']
                ];
                $delay = 0;
                foreach($stats as $s): ?>
                <div class="stat-card bg-f1-card p-8 rounded-[2rem] border border-white/5 relative overflow-hidden" data-aos="fade-up" data-aos-delay="<?php echo $delay; ?>">
                    <p class="text-[10px] font-black text-gray-500 uppercase tracking-[0.2em] mb-3"><?php echo $s['title']; ?></p>
                    <h3 class="text-4xl font-oswald font-black italic tracking-tighter text-white"><?php echo $s['val']; ?></h3>
                    <div class="flex items-center gap-2 mt-2">
                        <span class="w-1 h-1 rounded-full bg-f1-red"></span>
                        <span class="text-[9px] text-f1-red font-black uppercase tracking-widest"><?php echo $s['trend']; ?></span>
                    </div>
                </div>
                <?php $delay += 100; endforeach; ?>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <div class="lg:col-span-2 bg-f1-card rounded-[2.5rem] border border-white/5 overflow-hidden shadow-2xl" data-aos="fade-right">
                    <div class="p-8 border-b border-white/5 flex justify-between items-center bg-white/2">
                        <h3 class="font-oswald font-black uppercase italic text-xl tracking-tighter">Recent <span class="text-f1-red">News</span> Stream</h3>
                        <span class="text-[9px] font-black text-gray-600 uppercase tracking-widest italic">Showing last 5 entries</span>
                    </div>
                    
                    <div class="overflow-x-auto text-white">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="text-[10px] font-black text-gray-500 uppercase tracking-[0.2em] border-b border-white/5">
                                    <th class="p-6">Article Description</th>
                                    <th class="p-6">Source</th>
                                    <th class="p-6 text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5">
                                <?php if (!empty($articles)): ?>
                                    <?php foreach ($articles as $article): ?>
                                    <tr class="hover:bg-white/[0.03] transition-all group">
                                        <td class="p-6">
                                            <p class="font-bold text-sm text-gray-200 group-hover:text-white transition tracking-tight leading-snug max-w-md">
                                                <?php echo htmlspecialchars($article['titel']); ?>
                                            </p>
                                            <p class="text-[10px] text-gray-600 font-bold mt-1 uppercase tracking-tighter">
                                                📅 <?php echo date('d M Y | H:i', strtotime($article['publicatie_datum'])); ?>
                                            </p>
                                        </td>
                                        <td class="p-6">
                                            <span class="px-3 py-1 bg-white/5 border border-white/10 text-gray-400 text-[9px] font-black rounded-full uppercase tracking-[0.15em]">
                                                <?php echo htmlspecialchars($article['source'] ?? 'Unknown'); ?>
                                            </span>
                                        </td>
                                        <td class="p-6">
                                            <div class="flex justify-center items-center gap-4">
                                                <a href="edit_news.php?id=<?php echo $article['id']; ?>" class="text-[10px] font-black uppercase text-gray-500 hover:text-white transition tracking-widest">Edit</a>
                                                <button onclick="confirmDelete(<?php echo $article['id']; ?>)" class="text-[10px] font-black uppercase text-red-900 hover:text-f1-red transition tracking-widest">Delete</button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="p-20 text-center">
                                            <p class="text-gray-600 font-oswald uppercase italic text-lg tracking-widest">No data in stream...</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="space-y-8" data-aos="fade-left">
                    <div class="bg-f1-red rounded-[2.5rem] p-10 glow-red relative overflow-hidden group transition-transform duration-500 hover:scale-[1.02]">
                        <div class="relative z-10">
                            <span class="inline-block px-3 py-1 bg-white/20 rounded-full text-[9px] font-black uppercase tracking-widest mb-6">Upcoming Event</span>
                            <h4 class="text-4xl font-oswald font-black italic uppercase leading-tight mb-2 tracking-tighter">Monaco <br>Grand Prix</h4>
                            <p class="text-white/70 font-bold uppercase text-[10px] tracking-widest">Monte Carlo Circuit 🇮🇩</p>
                            
                            <div class="mt-10 pt-8 border-t border-white/20 flex items-end justify-between">
                                <div>
                                    <p class="text-xs font-black uppercase text-white/60 mb-1">Qualifying</p>
                                    <p class="text-3xl font-oswald font-black italic tracking-tighter text-white">16:00 <span class="text-sm">CET</span></p>
                                </div>
                                <span class="text-5xl opacity-20">🏁</span>
                            </div>
                        </div>
                        <div class="absolute -bottom-10 -right-10 w-48 h-48 bg-black/10 rounded-full blur-3xl pointer-events-none"></div>
                    </div>

                    <div class="bg-f1-card rounded-[2.5rem] border border-white/5 p-8 shadow-xl">
                        <div class="flex items-center gap-3 mb-8">
                            <span class="w-1 h-4 bg-f1-red rounded-full"></span>
                            <h4 class="font-oswald font-black uppercase italic text-xl tracking-tighter">System <span class="text-f1-red">Logs</span></h4>
                        </div>
                        <div class="space-y-6">
                            <div class="flex gap-4">
                                <div class="w-2 h-2 rounded-full bg-green-500 mt-1 flex-shrink-0 shadow-[0_0_10px_rgba(34,197,94,0.5)]"></div>
                                <div>
                                    <p class="text-[11px] text-gray-300 font-bold leading-tight uppercase tracking-tight">Scraper: 1Result API</p>
                                    <p class="text-[10px] text-gray-500 mt-1 italic">Successful sync - 14 new records added.</p>
                                </div>
                            </div>
                            <div class="flex gap-4">
                                <div class="w-2 h-2 rounded-full bg-blue-500 mt-1 flex-shrink-0 shadow-[0_0_10px_rgba(59,130,246,0.5)]"></div>
                                <div>
                                    <p class="text-[11px] text-gray-300 font-bold leading-tight uppercase tracking-tight">Security Protocol</p>
                                    <p class="text-[10px] text-gray-500 mt-1 italic">New login detected from IP: 127.0.0.1</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </main>
    </div>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            AOS.init({ duration: 800, once: true });
        });

        function confirmDelete(id) {
            if (confirm('⚠️ WARNING: Deleting this article is permanent. Proceed?')) {
                window.location.href = 'delete_news.php?id=' + id;
            }
        }
    </script>
</body>
</html>
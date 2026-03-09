<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../index.php");
    exit;
}
require_once 'db_config.php';
/** @var PDO $pdo */
$news = [];
try {
    $sql = "SELECT * FROM f1_nieuws ORDER BY id DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $news = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (\PDOException $e) {
    error_log("Fout bij het ophalen van nieuws: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Newsroom Control | F1 Admin</title>
    <?php include '../head.php'; ?>
    <style>
        :root { --f1-red: #E10600; }
        .bg-pattern { background-color: #0b0b0f; background-image: radial-gradient(rgba(255,255,255,0.03) 1px, transparent 0); background-size: 40px 40px; }
        .font-oswald { font-family: 'Oswald', sans-serif; }
        tr { 
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); 
            cursor: pointer;
        }
        tr:hover { 
            background: rgba(255, 255, 255, 0.04) !important;
            transform: scale(1.005);
        }
        .delete-btn {
            opacity: 0;
            transform: translateX(10px);
            transition: all 0.3s ease;
        }
        tr:hover .delete-btn {
            opacity: 1;
            transform: translateX(0);
        }
        .search-focus:focus-within {
            box-shadow: 0 0 20px rgba(225, 6, 0, 0.2);
            border-color: var(--f1-red);
        }

        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-pattern text-white font-sans min-h-screen antialiased">
    <div class="flex">
        <?php include '../nav.php'; ?>
        
        <main class="flex-grow p-6 lg:p-12">
            <div class="max-w-7xl mx-auto">
                <header class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6 mb-12" data-aos="fade-down">
                    <div>
                        <h2 class="text-6xl font-oswald font-black uppercase italic tracking-tighter leading-none">
                            News <span class="text-f1-red">Articles</span>
                        </h2>
                        <p class="text-gray-500 text-[10px] font-black uppercase tracking-[0.3em] mt-3 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-f1-red animate-pulse"></span>
                            Live Editorial Feed
                        </p>
                    </div>
                    
                    <div class="flex flex-wrap items-center gap-4 w-full lg:w-auto">
                        <div class="relative w-full lg:w-80 search-focus rounded-full border border-white/10 transition-all duration-300 bg-white/5">
                            <i data-lucide="search" class="absolute left-5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500"></i>
                            <input type="text" id="liveSearch" placeholder="Search headline..." 
                                   class="bg-transparent pl-14 pr-6 py-4 rounded-full text-xs font-bold w-full focus:outline-none text-white">
                        </div>
                        <a href="add/add-news.php" class="bg-f1-red text-white px-8 py-4 rounded-full font-black text-[10px] uppercase tracking-[0.2em] hover:bg-red-700 transition-all shadow-[0_10px_20px_rgba(225,6,0,0.2)]">
                            + New Article
                        </a>
                    </div>
                </header>

                <div class="bg-[#16161c]/80 backdrop-blur-xl rounded-[2.5rem] border border-white/5 overflow-hidden shadow-2xl" data-aos="fade-up">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left" id="newsTable">
                            <thead>
                                <tr class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] border-b border-white/5 bg-white/[0.02]">
                                    <th class="p-8">Article Details</th>
                                    <th class="p-8">Timestamp</th>
                                    <th class="p-8 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5">
                                <?php if (!empty($news)): ?>
                                    <?php foreach ($news as $item): ?>
                                        <tr class="news-row group" 
                                            data-newsid="<?php echo htmlspecialchars($item['id']); ?>"
                                            data-title="<?php echo strtolower(htmlspecialchars($item['titel'])); ?>">
                                            <td class="p-8">
                                                <p class="font-bold text-lg text-gray-200 group-hover:text-white transition line-clamp-1 mb-1">
                                                    <?php echo htmlspecialchars($item['titel']); ?>
                                                </p>
                                                <span class="text-[9px] text-f1-red font-black uppercase italic tracking-widest">
                                                    <?php echo htmlspecialchars($item['source'] ?? 'F1SITE.NL'); ?>
                                                </span>
                                            </td>
                                            <td class="p-8">
                                                <span class="text-xs font-black text-gray-500 uppercase tracking-tighter">
                                                    <?php echo date('d M Y', strtotime($item['publicatie_datum'] ?? 'now')); ?>
                                                </span>
                                            </td>
                                            <td class="p-8 text-right">
                                                <button class="delete-btn px-6 py-2.5 rounded-full text-[9px] font-black uppercase tracking-widest bg-red-600/10 text-red-500 border border-red-500/20 hover:bg-red-600 hover:text-white transition-all" 
                                                        data-id="<?php echo $item['id']; ?>"
                                                        data-title="<?php echo htmlspecialchars($item['titel']); ?>">
                                                    Delete
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>

                                <tr id="noResults" style="display: none;">
                                    <td colspan="3" class="p-20 text-center">
                                        <i data-lucide="search-x" class="w-12 h-12 text-gray-700 mx-auto mb-4"></i>
                                        <p class="text-gray-500 font-oswald uppercase italic text-xl tracking-widest">No matching articles found...</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <div x-data="{ open: false, newsId: null, newsTitle: '' }" 
         @open-delete-modal.window="open = true; newsId = $event.detail.id; newsTitle = $event.detail.title"
         @confirm-delete.window="handleDelete($event.detail.id)"
         x-show="open" x-cloak
         class="fixed inset-0 z-[200] flex items-center justify-center p-4">
        
        <div x-show="open" x-transition.opacity class="fixed inset-0 bg-black/90 backdrop-blur-md"></div>

        <div x-show="open" 
             x-transition:enter="transition ease-out duration-300" 
             x-transition:enter-start="opacity-0 scale-90 translate-y-8" 
             x-transition:enter-end="opacity-100 scale-100 translate-y-0" 
             @click.away="open = false"
             class="bg-[#16161c] border border-white/10 w-full max-w-md rounded-[2.5rem] p-10 shadow-2xl relative overflow-hidden text-center">
            
            <div class="absolute top-0 left-0 w-full h-1 bg-f1-red shadow-[0_0_15px_#E10600]"></div>

            <div class="w-20 h-20 bg-f1-red/10 rounded-full flex items-center justify-center mx-auto mb-8">
                <i data-lucide="alert-triangle" class="w-10 h-10 text-f1-red"></i>
            </div>
            
            <h3 class="font-oswald font-black uppercase italic text-3xl mb-4 tracking-tighter text-white">Confirm <span class="text-f1-red">Deletion</span></h3>
            <p class="text-gray-400 text-sm mb-10 leading-relaxed font-medium">
                Weet je zeker dat je het artikel <br>
                <span class="text-white font-bold italic text-base" x-text="'\'' + newsTitle + '\''"></span> <br>
                wilt verwijderen?
            </p>

            <div class="flex gap-4">
                <button @click="open = false" class="flex-1 px-6 py-4 rounded-2xl bg-white/5 border border-white/10 text-[10px] font-black uppercase tracking-widest hover:bg-white/10 transition-all text-white">Abort</button>
                <button @click="$dispatch('confirm-delete', { id: newsId }); open = false" class="flex-1 px-6 py-4 rounded-2xl bg-f1-red text-white text-[10px] font-black uppercase tracking-widest hover:bg-red-700 transition-all shadow-[0_10px_30px_rgba(225,6,0,0.3)]">Confirm</button>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            AOS.init({ duration: 800, once: true });
            lucide.createIcons();

            // LIVE SEARCH LOGICA
            const searchInput = document.getElementById('liveSearch');
            const rows = document.querySelectorAll('.news-row');
            const noResults = document.getElementById('noResults');

            searchInput.addEventListener('input', function() {
                const term = this.value.toLowerCase();
                let hasMatches = false;

                rows.forEach(row => {
                    const title = row.getAttribute('data-title');
                    if (title.includes(term)) {
                        row.style.display = '';
                        hasMatches = true;
                    } else {
                        row.style.display = 'none';
                    }
                });

                noResults.style.display = hasMatches ? 'none' : '';
            });

            // Dubbelklik voor bewerken
            rows.forEach(row => {
                row.addEventListener('dblclick', function() {
                    window.location.href = 'bewerken/edit-news-detail.php?id=' + this.dataset.newsid;
                });
            });

            // Delete buttons (trigger modal)
            document.querySelectorAll('.delete-btn').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    window.dispatchEvent(new CustomEvent('open-delete-modal', { 
                        detail: { id: this.dataset.id, title: this.dataset.title } 
                    }));
                });
            });
        });
        function handleDelete(id) {
            const row = document.querySelector(`tr[data-newsid="${id}"]`);
            fetch('bewerken/delete_news.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id=${id}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    row.style.transform = 'translateX(50px)';
                    row.style.opacity = '0';
                    setTimeout(() => row.remove(), 400);
                } else {
                    alert('Fout: ' + data.message);
                }
            })
            .catch(() => alert('Systeemfout bij verwijderen.'));
        }
    </script>
</body>
</html>
<?php
session_start();
if (!isset($_SESSION['logged_in'])) { header("Location: index.php"); exit; }
require_once 'db_config.php';
/** @var PDO $pdo */

// 1. VERWIJDEREN
if (isset($_GET['delete_id'])) {
    $stmt = $pdo->prepare("DELETE FROM contact WHERE ID = ?");
    $stmt->execute([$_GET['delete_id']]);
    header("Location: " . $_SERVER['PHP_SELF']); 
    exit;
}

// 2. MARKEREN ALS GELEZEN (Eén bericht via AJAX)
if (isset($_GET['mark_read'])) {
    $stmt = $pdo->prepare("UPDATE contact SET is_read = 1 WHERE ID = ?");
    $stmt->execute([$_GET['mark_read']]);
    exit; // Cruciaal: stopt de rest van de pagina-opbouw voor de AJAX-aanroep
}

if (isset($_GET['mark_all_read'])) {
    $pdo->query("UPDATE contact SET is_read = 1");
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

try {
    $messages = $pdo->query("SELECT * FROM contact ORDER BY ID DESC")->fetchAll(PDO::FETCH_ASSOC);
    $totalMessages = count($messages);
} catch (PDOException $e) {
    die("Telemetry Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <?php include '../head.php'; ?>
    <style>
        .f1-card { background: rgba(22, 22, 28, 0.7); backdrop-filter: blur(15px); border: 1px solid rgba(255,255,255,0.05); }
        .message-row { cursor: pointer; transition: all 0.2s ease; }
        .message-row:hover { background: rgba(255, 255, 255, 0.03); }
        .status-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; transition: all 0.5s ease; }
        .status-dot.unread { background: #3b82f6; box-shadow: 0 0 10px rgba(59,130,246,0.5); }
        .status-dot.read { background: rgba(255,255,255,0.1); box-shadow: none; }
        
        #messageModal {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 999;
            background: rgba(0, 0, 0, 0.85);
            backdrop-filter: blur(15px);
        }
        .modal-content {
            background: #16161c;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 0 80px rgba(0, 0, 0, 0.8);
            max-height: 85vh; 
            display: flex;
            flex-direction: column;
        }
        .modal-body-scroll {
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: #E10600 rgba(255,255,255,0.05);
        }
        .modal-body-scroll::-webkit-scrollbar { width: 6px; }
        .modal-body-scroll::-webkit-scrollbar-track { background: rgba(255,255,255,0.05); }
        .modal-body-scroll::-webkit-scrollbar-thumb { background-color: #E10600; border-radius: 10px; }
    </style>
</head>
<body class="bg-[#0b0b0f] text-white font-sans overflow-x-hidden">

    <div class="flex min-h-screen">
        <?php include '../nav.php'; ?>
        <main class="flex-grow p-6 lg:p-10 mt-16 lg:mt-0">
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-10 gap-4">
                <div data-aos="fade-right">
                    <h1 class="text-4xl font-oswald font-black italic uppercase tracking-tighter text-white">Comms <span class="text-f1-red">Inbox</span></h1>
                    <p class="text-gray-500 text-xs font-bold uppercase tracking-widest mt-1">Intercepted Transmissions: <span class="text-blue-500"><?php echo $totalMessages; ?> Total</span></p>
                </div>
                <div class="flex gap-4">
                    <a href="bewerken/contact.php?mark_all_read=1" class="text-[10px] font-black border border-white/10 px-4 py-2 rounded-full uppercase tracking-widest hover:bg-white/5 transition flex items-center gap-2">
                        <i data-lucide="check-check" class="w-3 h-3"></i> Mark all read
                    </a>
                </div>
            </div>
            <div class="f1-card rounded-[2.5rem] overflow-hidden shadow-2xl" data-aos="fade-up">
                <div class="p-8 border-b border-white/5 bg-white/[0.02]">
                    <h3 class="font-oswald font-black uppercase italic text-xl tracking-tighter text-white">Signal <span class="text-f1-red">Log</span></h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="text-[10px] font-black text-gray-500 uppercase tracking-widest border-b border-white/5">
                                <th class="p-6">Sender & Source</th>
                                <th class="p-6">Transmission Content</th>
                                <th class="p-6 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            <?php if(empty($messages)): ?>
                                <tr>
                                    <td colspan="3" class="p-20 text-center">
                                        <i data-lucide="radio" class="w-12 h-12 text-gray-800 mx-auto mb-4 text-gray-700"></i>
                                        <p class="text-gray-600 font-bold uppercase tracking-widest text-xs">No signals detected</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($messages as $msg): 
                                    $js_params = sprintf(
                                        "%d, '%s', '%s', '%s', '%s'",
                                        $msg['ID'],
                                        addslashes(htmlspecialchars($msg['contact_name'])),
                                        addslashes(htmlspecialchars($msg['contact_email'])),
                                        addslashes(htmlspecialchars($msg['contact_subject'])),
                                        addslashes(htmlspecialchars($msg['contact_message']))
                                    );
                                ?>
                                <tr class="message-row group" id="row-<?php echo $msg['ID']; ?>" ondblclick="openMessage(<?php echo $js_params; ?>)">
                                    <td class="p-6 align-top w-1/4">
                                        <div class="flex items-start gap-4">
                                            <div id="dot-<?php echo $msg['ID']; ?>" class="mt-1 status-dot <?php echo $msg['is_read'] == 1 ? 'read' : 'unread'; ?>"></div>
                                            <div>
                                                <p class="font-black text-sm text-white leading-tight mb-1"><?php echo htmlspecialchars($msg['contact_name']); ?></p>
                                                <p class="text-[10px] text-gray-500 font-bold lowercase tracking-tight"><?php echo htmlspecialchars($msg['contact_email']); ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-6 align-top">
                                        <div class="space-y-1">
                                            <p class="text-[10px] font-black text-f1-red uppercase tracking-widest italic"><?php echo htmlspecialchars($msg['contact_subject'] ?: 'NO SUBJECT'); ?></p>
                                            <p class="text-sm text-gray-400 group-hover:text-gray-200 transition-colors line-clamp-2 italic">
                                                "<?php echo htmlspecialchars($msg['contact_message']); ?>"
                                            </p>
                                        </div>
                                    </td>
                                    <td class="p-6 text-right align-top">
                                        <div class="flex justify-end gap-3 lg:opacity-0 group-hover:opacity-100 transition-opacity">
                                            <button onclick="openMessage(<?php echo $js_params; ?>)" class="p-2 bg-white/5 hover:bg-white/10 rounded-lg transition text-gray-400 hover:text-white">
                                                <i data-lucide="eye" class="w-4 h-4"></i>
                                            </button>
                                            <a href="bewerken/contact.php?delete_id=<?php echo $msg['ID']; ?>" class="p-2 bg-f1-red/10 hover:bg-f1-red/20 rounded-lg transition" onclick="event.stopPropagation(); return confirm('Erase transmission data permanently?')">
                                                <i data-lucide="trash-2" class="w-4 h-4 text-f1-red"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <div id="messageModal" class="flex items-center justify-center p-4">
        <div class="modal-content w-full max-w-2xl rounded-[2.5rem] overflow-hidden border-t-4 border-t-f1-red" data-aos="zoom-in">
            <div class="p-8 border-b border-white/5 flex justify-between items-center bg-white/[0.02]">
                <div>
                    <h3 id="modalSubject" class="font-oswald font-black uppercase italic text-2xl tracking-tighter text-f1-red"></h3>
                    <p id="modalSender" class="text-[10px] font-black text-gray-500 uppercase tracking-widest mt-1"></p>
                </div>
                <button onclick="closeModal()" class="p-2 hover:bg-white/10 rounded-full transition">
                    <i data-lucide="x" class="w-6 h-6 text-gray-400"></i>
                </button>
            </div>
            <div class="p-8 lg:p-10 modal-body-scroll">
                <div class="bg-black/40 p-8 rounded-3xl border border-white/5">
                    <p id="modalBody" class="text-gray-300 leading-relaxed italic whitespace-pre-wrap text-sm md:text-base"></p>
                </div>
            </div>
            <div class="p-6 bg-white/[0.01] border-t border-white/5 text-right">
                <button onclick="closeModal()" class="text-[10px] font-black bg-white/5 px-8 py-4 rounded-full uppercase tracking-widest hover:bg-white/10 transition border border-white/5">Close Radio Link</button>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({ duration: 800, once: true });
        lucide.createIcons();

        function openMessage(id, name, email, subject, message) {
            document.getElementById('modalSubject').innerText = subject ? subject : 'NO SUBJECT';
            document.getElementById('modalSender').innerText = `Uplink from: ${name} (${email})`;
            document.getElementById('modalBody').innerText = message;
            document.getElementById('messageModal').style.display = 'flex';
            document.body.style.overflow = 'hidden'; 
            
            fetch(`?mark_read=${id}`).then(response => {
                if(response.ok) {
                    const dot = document.getElementById(`dot-${id}`);
                    if(dot) {
                        dot.classList.remove('unread');
                        dot.classList.add('read');
                    }
                }
            });
        }

        function closeModal() {
            document.getElementById('messageModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        window.onclick = function(event) {
            let modal = document.getElementById('messageModal');
            if (event.target == modal) closeModal();
        }
    </script>
</body>
</html>
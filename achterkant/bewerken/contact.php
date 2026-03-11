<?php
session_start();
if (!isset($_SESSION['logged_in'])) { header("Location: index.php"); exit; }
require_once 'db_config.php';
/** @var PDO $pdo */
if (isset($_GET['mark_read'])) {
    $stmt = $pdo->prepare("UPDATE contact SET is_read = 1 WHERE ID = ?");
    $stmt->execute([$_GET['mark_read']]);
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
        .message-row:hover { background: rgba(225, 6, 0, 0.05); }
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
        }
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
            </div>

            <div class="f1-card rounded-[2.5rem] overflow-hidden shadow-2xl" data-aos="fade-up">
                <div class="p-8 border-b border-white/5 flex justify-between items-center bg-white/[0.02]">
                    <h3 class="font-oswald font-black uppercase italic text-xl tracking-tighter">Signal <span class="text-f1-red">Log</span></h3>
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
                                        <i data-lucide="radio" class="w-12 h-12 text-gray-800 mx-auto mb-4"></i>
                                        <p class="text-gray-600 font-bold uppercase tracking-widest text-xs">No signals detected</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($messages as $msg): ?>
                                <tr class="message-row transition-all group" id="row-<?php echo $msg['ID']; ?>">
                                    <td class="p-6 align-top w-1/4">
                                        <div class="flex items-start gap-4">
                                            <div id="dot-<?php echo $msg['ID']; ?>" class="mt-1 status-dot <?php echo $msg['is_read'] ? 'read' : 'unread'; ?>"></div>
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
                                        <div class="flex justify-end gap-3 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <button onclick="openMessage(<?php echo $msg['ID']; ?>, '<?php echo addslashes(htmlspecialchars($msg['contact_name'])); ?>', '<?php echo addslashes(htmlspecialchars($msg['contact_email'])); ?>', '<?php echo addslashes(htmlspecialchars($msg['contact_subject'])); ?>', '<?php echo addslashes(htmlspecialchars($msg['contact_message'])); ?>')" class="p-2 bg-white/5 hover:bg-white/10 rounded-lg transition text-gray-400 hover:text-white">
                                                <i data-lucide="eye" class="w-4 h-4"></i>
                                            </button>
                                            <a href="delete_message.php?id=<?php echo $msg['ID']; ?>" class="p-2 bg-f1-red/10 hover:bg-f1-red/20 rounded-lg transition" onclick="return confirm('Erase transmission data?')">
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
            <div class="p-10">
                <div class="bg-black/20 p-6 rounded-2xl border border-white/5">
                    <p id="modalBody" class="text-gray-300 leading-relaxed italic"></p>
                </div>
            </div>
            <div class="p-6 bg-white/[0.01] border-t border-white/5 text-right">
                <button onclick="closeModal()" class="text-[10px] font-black bg-white/5 px-6 py-3 rounded-full uppercase tracking-widest hover:bg-white/10 transition">Close Signal</button>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({ duration: 800, once: true });
        lucide.createIcons();

        function openMessage(id, name, email, subject, message) {
            // Vul de modal
            document.getElementById('modalSubject').innerText = subject ? subject : 'NO SUBJECT';
            document.getElementById('modalSender').innerText = `From: ${name} (${email})`;
            document.getElementById('modalBody').innerText = message;
            document.getElementById('messageModal').style.display = 'flex';
            fetch(`?mark_read=${id}`)
                .then(() => {
                    const dot = document.getElementById(`dot-${id}`);
                    if(dot) {
                        dot.classList.remove('unread');
                        dot.classList.add('read');
                    }
                });
        }

        function closeModal() {
            document.getElementById('messageModal').style.display = 'none';
        }

        window.onclick = function(event) {
            let modal = document.getElementById('messageModal');
            if (event.target == modal) closeModal();
        }
    </script>
</body>
</html>
<?php
$jsonFile = 'achterkant/aanpassing/api-koppelingen/json/drivers.json';
$allDrivers = [];
$error_message = '';

if (file_exists($jsonFile)) {
    $jsonData = file_get_contents($jsonFile);
    $allDrivers = json_decode($jsonData, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        $allDrivers = [];
        $error_message = "Data corrupt.";
    }
} else {
    $error_message = "Bestand niet gevonden.";
}
?>
<!DOCTYPE html>
<html lang="nl" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Drivers | F1SITE.NL</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;900&family=Oswald:wght@700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { 'sans': ['Inter', 'sans-serif'], 'oswald': ['Oswald', 'sans-serif'] },
                    colors: { 'f1-red': '#E10600', 'f1-dark': '#0b0b0f', 'f1-card': '#16161c' }
                }
            }
        }
    </script>

    <style>
        body { background-color: #0b0b0f; color: #fff; overflow-x: hidden; }
        .bg-pattern {
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.02'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
        .header-glass { background: rgba(11, 11, 15, 0.9); backdrop-filter: blur(15px); border-bottom: 1px solid rgba(225, 6, 0, 0.3); }
        #mobile-menu { 
            transform: translateX(100%); 
            transition: transform 0.4s ease-in-out; 
            background: #0b0b0f; 
            z-index: 101; 
        }
        #mobile-menu.active { transform: translateX(0); }
        .driver-card {
            background: #16161c;
            border: 1px solid rgba(255,255,255,0.05);
            transition: all 0.3s ease;
        }
        .driver-card:hover {
            border-color: rgba(225, 6, 0, 0.5);
            transform: translateY(-3px);
            background: #1c1c24;
        }
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #0b0b0f; }
        ::-webkit-scrollbar-thumb { background: #E10600; border-radius: 10px; }
    </style>
</head>
<body class="bg-pattern min-h-screen flex flex-col italic">
    <div id="mobile-menu" class="fixed inset-y-0 right-0 w-full p-10 flex flex-col items-center justify-center lg:hidden">
        <button onclick="toggleMenu()" class="absolute top-8 right-8 text-5xl font-light">&times;</button>
        <nav class="flex flex-col space-y-10 text-4xl font-oswald font-black uppercase italic text-center">
            <a href="index.php" onclick="toggleMenu()">Home</a>
            <a href="kalender.php" onclick="toggleMenu()">Schedule</a>
            <a href="teams.php" onclick="toggleMenu()">Teams</a>
            <a href="drivers.php" class="text-f1-red" onclick="toggleMenu()">Drivers</a>
            <a href="results.php" onclick="toggleMenu()">Results</a>
            <a href="standings.php" onclick="toggleMenu()">Standings</a>
        </nav>
    </div>
    <header class="header-glass sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-6 py-5 flex justify-between items-center">
            <a href="index.php" class="text-3xl font-oswald font-black italic tracking-tighter text-white uppercase">F1SITE<span class="text-f1-red">.NL</span></a>
            <nav class="hidden lg:flex space-x-10 text-[11px] font-bold uppercase tracking-[0.25em]">
                <a href="index.php" class="hover:text-f1-red transition">Home</a>
                <a href="kalender.php" class="hover:text-f1-red transition">Schedule</a>
                <a href="teams.php" class="hover:text-f1-red transition">Teams</a>
                <a href="drivers.php" class="text-f1-red border-b-2 border-f1-red pb-1">Drivers</a>
                <a href="results.php" class="hover:text-f1-red transition">Results</a>
                <a href="standings.php" class="hover:text-f1-red transition">Standings</a>
            </nav>
            <button onclick="toggleMenu()" class="lg:hidden text-white text-3xl">☰</button>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-6 py-16 flex-grow w-full">
        
        <div class="mb-16 text-center">
            <h1 class="text-6xl md:text-8xl font-oswald font-black uppercase italic tracking-tighter leading-none mb-4">
                ALL<span class="text-f1-red">DRIVERS</span>
            </h1>
        </div>

        <div class="bg-f1-card p-6 md:p-8 rounded-[2.5rem] border border-white/5 shadow-2xl mb-12">
            <div class="flex flex-col md:flex-row gap-4">
                <input type="text" id="searchInput" placeholder="SEARCH BY NAME..."
                       class="flex-1 p-4 bg-black border border-white/10 text-white rounded-2xl focus:ring-2 focus:ring-f1-red outline-none italic text-sm font-bold uppercase tracking-wider transition-all">
                
                <select id="sortSelect" class="md:w-64 p-4 bg-black border border-white/10 text-white rounded-2xl focus:ring-2 focus:ring-f1-red outline-none italic text-xs font-bold uppercase tracking-widest cursor-pointer">
                    <option value="az">NAME (A-Z)</option>
                    <option value="za">NAME (Z-A)</option>
                    <option value="oldest">DOB (OLDEST)</option>
                    <option value="youngest">DOB (YOUNGEST)</option>
                </select>
            </div>
        </div>

        <?php if ($error_message): ?>
            <div class="p-6 bg-red-900/20 border border-red-500/50 rounded-2xl text-red-500 text-center uppercase font-black tracking-widest text-xs">
                <?php echo $error_message; ?>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="driverList">
                <?php foreach ($allDrivers as $driver): ?>
                    <div class="driver-card p-8 rounded-[2rem] driver-item group" 
                        data-name="<?php echo htmlspecialchars($driver['firstName'] . ' ' . $driver['lastName']); ?>" 
                        data-dob="<?php echo htmlspecialchars($driver['dateOfBirth']); ?>">
                        
                        <div class="flex flex-col h-full">
                            <div class="mb-8">
                                <span class="text-f1-red text-[10px] font-black uppercase tracking-[0.3em] block mb-2">
                                    <?php echo htmlspecialchars($driver['nationalityCountryId'] ?? 'F1'); ?>
                                </span>
                                <h3 class="text-2xl font-oswald font-black text-white uppercase italic tracking-tighter leading-none group-hover:text-f1-red transition-colors">
                                    <?php echo htmlspecialchars($driver['firstName']); ?> <br>
                                    <span class="text-4xl"><?php echo htmlspecialchars($driver['lastName']); ?></span>
                                </h3>
                            </div>
                            
                            <div class="mt-auto space-y-3 pt-6 border-t border-white/5">
                                <div class="flex justify-between items-center">
                                    <span class="text-[9px] font-black text-gray-500 uppercase tracking-widest italic">DOB</span>
                                    <span class="text-xs font-bold text-white"><?php echo htmlspecialchars($driver['dateOfBirth']); ?></span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-[9px] font-black text-gray-500 uppercase tracking-widest italic">Place</span>
                                    <span class="text-xs font-bold text-white truncate max-w-[150px]"><?php echo htmlspecialchars($driver['placeOfBirth']); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <footer class="bg-black py-16 border-t-2 border-f1-red mt-12">
        <div class="max-w-7xl mx-auto px-6 text-center">
            <h3 class="text-2xl font-oswald font-black text-white italic tracking-tighter mb-4 uppercase">F1SITE<span class="text-f1-red">.NL</span></h3>
            <p class="text-gray-600 text-[10px] font-black uppercase tracking-[0.5em] italic">
                &copy; <?php echo (date('Y')); ?> WEBIUS. All rights reserved.
            </p>
        </div>
    </footer>
    
    <script>
        function toggleMenu() {
            document.getElementById('mobile-menu').classList.toggle('active');
            document.body.style.overflow = document.getElementById('mobile-menu').classList.contains('active') ? 'hidden' : '';
        }
        const searchInput = document.getElementById('searchInput');
        const sortSelect = document.getElementById('sortSelect');
        const driverList = document.getElementById('driverList');
        const originalDrivers = Array.from(document.getElementsByClassName('driver-item'));

        function updateList() {
            const term = searchInput.value.toLowerCase();
            const sort = sortSelect.value;

            let filtered = originalDrivers.filter(item => 
                item.getAttribute('data-name').toLowerCase().includes(term)
            );

            filtered.sort((a, b) => {
                if (sort === 'az') return a.getAttribute('data-name').localeCompare(b.getAttribute('data-name'));
                if (sort === 'za') return b.getAttribute('data-name').localeCompare(a.getAttribute('data-name'));
                if (sort === 'oldest') return new Date(a.getAttribute('data-dob')) - new Date(b.getAttribute('data-dob'));
                if (sort === 'youngest') return new Date(b.getAttribute('data-dob')) - new Date(a.getAttribute('data-dob'));
                return 0;
            });

            driverList.innerHTML = '';
            filtered.forEach(item => driverList.appendChild(item));
        }

        searchInput.addEventListener('input', updateList);
        sortSelect.addEventListener('change', updateList);
    </script>
</body>
</html>
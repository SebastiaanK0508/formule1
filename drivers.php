<?php
require_once 'db_config.php';
/** @var PDO $pdo */
$allDrivers = []; 
try {
    $stmt = $pdo->query("SELECT d.driver_id, d.first_name, d.last_name, d.driver_number, d.flag_url, t.team_name, t.full_team_name, t.team_id, t.team_color FROM drivers d LEFT JOIN teams t ON d.team_id = t.team_id WHERE d.is_active = TRUE ORDER BY d.driver_number ASC");
    $allDrivers = $stmt->fetchAll();
} catch (\PDOException $e) {
    error_log("Fout bij het ophalen van alle coureurs: " . $e->getMessage());
    // Foutmelding weggelaten uit HTML voor nu
}
$selectedDriver = null; 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['driver_id'])) {
    $driverId = $_POST['driver_id'];
    try {
        $stmt = $pdo->prepare("SELECT driver_id, first_name, last_name, driver_number, team_name, t.full_team_name, flag_url, driver_color, is_active FROM drivers WHERE driver_id = :driverId AND is_active = TRUE");           
        $stmt->bindParam(':driverId', $driverId, PDO::PARAM_INT);
        $stmt->execute();
        $selectedDriver = $stmt->fetch();
        if (!$selectedDriver) {
        }
    } catch (\PDOException $e) {
        error_log("Fout bij het ophalen van geselecteerde coureurdetails: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formula 1 Season 2025 - Drivers</title>
    <link rel="icon" type="image/x-icon" href="/afbeeldingen/logo/f1logobgrm.png">
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'sans': ['Roboto', 'sans-serif'],
                        'oswald': ['Oswald', 'sans-serif'],
                    },
                    colors: {
                        'f1-red': '#E10600', 
                        'f1-black': '#15151E', 
                        'f1-gray': '#3A3A40',
                    }
                }
            }
        }
    </script>
    <style>
        @media (max-width: 767px) {
            .main-nav[data-visible="false"] {
                display: none;
            }
            .main-nav {
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background-color: #15151E;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
                padding: 1rem;
                display: flex;
                flex-direction: column;
                z-index: 40;
                border-top: 1px solid #E10600;
            }
            .main-nav a {
                padding: 0.5rem 0;
            }
        }
    </style>
</head>
<body class="bg-f1-black text-gray-100 font-sans min-h-screen flex flex-col">
    
    <header class="bg-black shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex justify-between items-center header-content container">
            <h1 class="text-3xl font-oswald font-extrabold text-f1-red tracking-widest site-title">
                FORMULA 1
            </h1>
            
            <button class="md:hidden text-2xl text-f1-red hover:text-white menu-toggle" 
                    aria-controls="main-nav-links" aria-expanded="false" aria-label="Toggle navigation">
                &#9776; 
            </button>
            
            <nav class="main-nav md:flex md:space-x-8 text-sm font-semibold uppercase tracking-wider" 
                 id="main-nav-links" data-visible="false">
                <a href="index.php" class="block py-2 px-3 md:p-0 hover:text-f1-red transition duration-150">Home</a>
                <a href="kalender.php" class="block py-2 px-3 md:p-0 hover:text-f1-red transition duration-150">Schedule</a>
                <a href="teams.php" class="block py-2 px-3 md:p-0 hover:text-f1-red transition duration-150">Teams</a>
                <a href="drivers.php" class="block py-2 px-3 md:p-0 text-f1-red border-b-2 border-f1-red md:border-none active transition duration-150">Drivers</a>
                <a href="results.php" class="block py-2 px-3 md:p-0 hover:text-f1-red transition duration-150">Results</a>
                <a href="standings.php" class="block py-2 px-3 md:p-0 hover:text-f1-red transition duration-150">Standings</a>
            </nav>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-8 flex-grow container">
        
        <section class="bg-f1-gray p-6 rounded-lg shadow-xl mb-8 page-header-section">
            <h2 class="text-xl md:text-3xl font-oswald font-bold text-white uppercase page-heading text-center">
                DRIVERS FORMULA 1 2025
            </h2>
        </section>

        <section class="f1-section">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 data-card-row">
                <?php if (!empty($allDrivers)): ?>
                    <?php foreach ($allDrivers as $driver): ?>
                        <?php
                            $driverSlug = strtolower(str_replace(' ', '-', htmlspecialchars($driver['first_name'] . '-' . $driver['last_name'])));
                            $driverPageUrl = 'driver-details.php?slug=' . $driverSlug;
                            // Gebruik de teamkleur of een standaard kleur als deze niet beschikbaar is
                            $driverColor = isset($driver['team_color']) && $driver['team_color'] ? htmlspecialchars($driver['team_color']) : '#CCCCCC';
                        ?>
                        <article class="bg-f1-gray rounded-xl shadow-lg overflow-hidden transition-all duration-300 hover:shadow-2xl hover:scale-[1.02] data-card min-h-40">
                            <a href="<?php echo $driverPageUrl; ?>" class="driver-link block flex items-center p-4 border-l-4 h-full"  style="border-left-color: <?php echo $driverColor; ?>;">                                    
                                    <div class="w-1/4 text-center">
                                        <p class="text-4xl font-oswald font-extrabold text-f1-red leading-none">
                                            #<?php echo htmlspecialchars($driver['driver_number']); ?>
                                        </p>
                                    </div>

                                    <div class="w-3/4 info pl-4">
                                        <h3 class="driver-name text-xl font-oswald font-bold text-white transition duration-150 hover:text-f1-red">
                                            <?php echo htmlspecialchars($driver['first_name']); ?>
                                            <span class="uppercase"><?php echo htmlspecialchars($driver['last_name']); ?></span>
                                        </h3>
                                        
                                        <div class="driver-details mt-1 text-sm text-gray-400">
                                            <p><span class="font-semibold">Team:</span> <?php echo htmlspecialchars($driver['full_team_name']); ?></p>
                                            </div>
                                    </div>
                            </a>
                        </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-gray-400 lg:col-span-4 p-6 bg-f1-gray rounded-lg">Geen coureurs beschikbaar om weer te geven.</p>
                <?php endif; ?>
                
                <div class="lg:col-span-4 text-center mt-4">
                    <a href="all_drivers.php">
                        <button class="bg-f1-red text-white py-3 px-8 rounded-lg font-bold uppercase tracking-wider hover:bg-red-700 transition all_drivers_button">
                            Alle Coureurs Ooit Bekijken
                        </button>
                    </a>
                </div>
            </div>
        </section>
    </main>

    <footer class="bg-black mt-12 py-6 border-t border-f1-red">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center footer-content container">
            <p class="text-gray-400 text-sm mb-4">&copy; 2025 Webbair. Alle rechten voorbehouden.</p>
            <div class="flex flex-wrap justify-center space-x-4 mb-4 social-links">
                <a href="#" class="text-gray-400 hover:text-f1-red transition duration-150" aria-label="Facebook">Facebook</a>
                <a href="#" class="text-gray-400 hover:text-f1-red transition duration-150" aria-label="Twitter">X</a>
                <a href="" class="text-gray-400 hover:text-f1-red transition duration-150" aria-label="Instagram">Instagram</a>
            </div>
            <div class="flex flex-wrap justify-center space-x-4 text-xs social-links">
                <a href="privacy.html" class="text-gray-500 hover:text-white transition duration-150">Privacy Beleid</a>
                <a href="algemenevoorwaarden.html" class="text-gray-500 hover:text-white transition duration-150">Algemene Voorwaarden</a>
                <a href="contact.html" class="text-gray-500 hover:text-white transition duration-150">Contact</a>
            </div>
        </div>
    </footer>
    
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const nav = document.getElementById('main-nav-links');
            const toggle = document.querySelector('.menu-toggle');

            toggle.addEventListener('click', () => {
                const isVisible = nav.getAttribute('data-visible') === 'true';
                nav.setAttribute('data-visible', String(!isVisible));
                toggle.setAttribute('aria-expanded', String(!isVisible));
            });
        });
    </script> 
</body>
</html>
<?php

require_once 'db_config.php';
/** @var PDO $pdo */
$teamId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($teamId === 0) {
    header('Location: teams.php'); 
    exit;
}

$team = null;
try {
    $stmt = $pdo->prepare("
        SELECT *
        FROM teams
        WHERE team_id = :id
    ");
    $stmt->bindParam(':id', $teamId, PDO::PARAM_INT);
    $stmt->execute();
    $team = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$team) {
        // Gebruik de F1-styling voor de foutpagina
        http_response_code(404);
        ?>
        <!DOCTYPE html>
        <html lang="nl">
        <head>
            <meta charset="UTF-8">
            <title>Team niet gevonden | F1</title>
            <script src="https://cdn.tailwindcss.com"></script>
        </head>
        <body class="bg-f1-black text-gray-100 p-8">
            <div class="max-w-xl mx-auto bg-f1-gray p-8 rounded-lg shadow-xl text-center">
                <h1 class="text-3xl font-oswald text-f1-red mb-4">404 - Team niet gevonden!</h1>
                <p class="text-gray-300 mb-6">Het team dat u zoekt, is helaas niet gevonden in onze database.</p>
                <a href='teams.php' class="text-white bg-f1-red py-2 px-4 rounded hover:bg-red-700 transition">Terug naar teamoverzicht</a>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
} catch (PDOException $e) {
    // Databasefout
    http_response_code(500);
    ?>
    <!DOCTYPE html>
    <html lang="nl">
    <head>
        <script src="https://t.contentsquare.net/uxa/688c1fe6f0f7c.js"></script>
        <meta charset="UTF-8">
        <title>Databasefout | F1</title>
        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="bg-f1-black text-gray-100 p-8">
        <div class="max-w-xl mx-auto bg-f1-gray p-8 rounded-lg shadow-xl text-center">
            <h1 class="text-3xl font-oswald text-f1-red mb-4">Databasefout</h1>
            <p class="text-gray-300 mb-6">Er is een fout opgetreden bij het laden van de gegevens.</p>
            <a href='teams.php' class="text-white bg-f1-red py-2 px-4 rounded hover:bg-red-700 transition mt-4 inline-block">Terug naar teamoverzicht</a>
        </div>
    </body>
    </html>
    <?php
    exit;
}

$teamDrivers = [];
$teamColor = htmlspecialchars($team['team_color'] ?? '#CCCCCC');

if ($team) {
    try {
        $stmtDrivers = $pdo->prepare("
            SELECT
                driver_id, first_name, last_name, driver_number, image
            FROM
                drivers
            WHERE
                team_id = :team_id AND is_active = TRUE 
            ORDER BY
                driver_number ASC
        ");
        $stmtDrivers->bindParam(':team_id', $teamId, PDO::PARAM_INT);
        $stmtDrivers->execute();
        $teamDrivers = $stmtDrivers->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error team GET: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($team['full_team_name']); ?></title>
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
        /* Mobile menu styles */
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
                <a href="teams.php" class="block py-2 px-3 md:p-0 text-f1-red border-b-2 border-f1-red md:border-none active transition duration-150">Teams</a>
                <a href="drivers.php" class="block py-2 px-3 md:p-0 hover:text-f1-red transition duration-150">Drivers</a>
                <a href="results.php" class="block py-2 px-3 md:p-0 hover:text-f1-red transition duration-150">Results</a>
                <a href="standings.php" class="block py-2 px-3 md:p-0 hover:text-f1-red transition duration-150">Standings</a>
            </nav>
        </div>
    </header>
    
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-8 flex-grow container">
        
        <div class="page-header-section mb-8">
            <h1 class="text-4xl md:text-5xl font-oswald font-extrabold uppercase pb-3 page-heading border-b-4" style="border-bottom-color: <?php echo $teamColor; ?>; color: <?php echo $teamColor; ?>;">
                <?php echo htmlspecialchars($team['full_team_name']); ?>
            </h1>
        </div>

        <section class="bg-f1-gray p-6 rounded-lg shadow-xl f1-section-teams grid grid-cols-1 md:grid-cols-3 gap-8">
            
            <div class="md:col-span-2 info space-y-3 border-l-4 pl-4" style="border-left-color: <?php echo $teamColor; ?>;">
                <p class="text-gray-300 team-detail-item">
                    <strong class="font-semibold" style="color: <?php echo $teamColor; ?>;">Basis Locatie: </strong> <?php echo htmlspecialchars($team['base_location']); ?>
                </p>
                <p class="text-gray-300 team-detail-item">
                    <strong class="font-semibold" style="color: <?php echo $teamColor; ?>;">Team Principal: </strong> <?php echo htmlspecialchars($team['team_principal']); ?>
                </p>
                <p class="text-gray-300 team-detail-item">
                    <strong class="font-semibold" style="color: <?php echo $teamColor; ?>;">Technisch Directeur: </strong> <?php echo htmlspecialchars($team['technical_director']); ?>
                </p>
                <p class="text-gray-300 team-detail-item">
                    <strong class="font-semibold" style="color: <?php echo $teamColor; ?>;">Chassis: </strong> <?php echo htmlspecialchars($team['chassis']); ?>
                </p>
                <p class="text-gray-300 team-detail-item">
                    <strong class="font-semibold" style="color: <?php echo $teamColor; ?>;">Motor Leverancier: </strong> <?php echo htmlspecialchars($team['current_engine_supplier']); ?>
                </p>
                <?php if (isset($team['championships_won'])): ?>
                    <p class="text-gray-300 team-detail-item">
                        <strong class="font-semibold" style="color: <?php echo $teamColor; ?>;">Gewonnen Constructeurs Kampioenschappen: </strong> <?php echo htmlspecialchars($team['championships_won']); ?>
                    </p>
                <?php endif; ?>
                <?php if (isset($team['total_victories'])): ?>
                    <p class="text-gray-300 team-detail-item">
                        <strong class="font-semibold" style="color: <?php echo $teamColor; ?>;">Totaal Overwinningen (X-Wins): </strong> <span class="text-f1-red font-bold"><?php echo htmlspecialchars($team['total_victories']); ?></span>
                    </p>
                <?php endif; ?>
                <?php if (isset($team['is_active'])): ?>
                    <p class="text-gray-300 team-detail-item">
                        <strong class="font-semibold" style="color: <?php echo $teamColor; ?>;">Status: </strong> <?php echo $team['is_active'] ? '<span class="text-green-400">Actief</span>' : '<span class="text-red-400">Inactief</span>'; ?>
                    </p>
                <?php endif; ?>
            </div>
            
            <div class="md:col-span-1 flex items-center justify-center">
                <?php if (!empty($team['logo_url'])): ?>
                    <img class="team-logo-details max-h-48 w-full object-contain" src="<?php echo htmlspecialchars($team['logo_url']); ?>" alt="<?php echo htmlspecialchars($team['full_team_name']); ?> Logo">
                <?php endif; ?>
            </div>
        </section>

        <?php if (!empty($team['description'])): ?>
            <div class="bg-f1-gray p-6 rounded-lg shadow-xl mt-8 driver-description">
                <h2 class="text-2xl font-oswald mb-4 border-b border-gray-700 pb-2" style="color: <?php echo $teamColor; ?>;">Over het team</h2>
                <p class="text-gray-300 leading-relaxed"><?php echo nl2br(htmlspecialchars($team['description'])); ?></p>
            </div>
        <?php endif; ?>

        <div class="f1-section-drivers-teams mt-12">
            <h2 class="text-3xl font-oswald text-center mb-6" style="color: <?php echo $teamColor; ?>;">Huidige Coureurs</h2>
            <?php if (!empty($teamDrivers)): ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-8 drivers-grid">
                    <?php foreach ($teamDrivers as $driver):
                        $driverSlug = strtolower(str_replace(' ', '-', $driver['first_name'] . '-' . $driver['last_name']));
                        $driverPageUrl = 'driver-details.php?slug=' . $driverSlug;?>

                        <a class="driver-card bg-f1-gray rounded-xl shadow-lg overflow-hidden transition-all duration-300 hover:shadow-2xl hover:scale-[1.03] flex border-l-4" 
                           href="<?php echo htmlspecialchars($driverPageUrl); ?>" 
                           style="border-left-color: <?php echo $teamColor; ?>;">
                            
                            <?php if (!empty($driver['image'])): ?>
                                <img src="<?php echo htmlspecialchars($driver['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($driver['first_name'] . ' ' . $driver['last_name']); ?>"
                                     class="w-1/3 h-full object-cover">
                            <?php endif; ?>
                            
                            <div class="w-2/3 p-4 flex flex-col justify-center driver-info">
                                <p class="text-sm text-gray-400">#<?php echo htmlspecialchars($driver['driver_number']); ?></p>
                                <p class="driver-name text-xl font-oswald font-bold text-white uppercase mt-1 transition duration-150 hover:text-f1-red">
                                    <?php echo htmlspecialchars($driver['first_name']); ?><br>
                                    <span class="text-2xl"><?php echo htmlspecialchars($driver['last_name']); ?></span>
                                </p>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-gray-400 text-center p-4 bg-f1-gray rounded-lg">Geen actieve coureurs gevonden voor dit team.</p>
            <?php endif; ?>
        </div>

        <div class="mt-8 text-center">
            <a href="teams.php" class="bg-f1-red text-white py-3 px-6 rounded-lg font-bold uppercase tracking-wider hover:bg-red-700 transition back-link">
                &larr; Back to team list
            </a>
        </div>
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
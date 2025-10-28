<?php
require_once 'db_config.php';
/** @var PDO $pdo */
$driverSlug = isset($_GET['slug']) ? $_GET['slug'] : '';

// Redirect als er geen slug is
if (empty($driverSlug)) {
    header('Location: index.php');
    exit;
}

$driver = null;
try {
    // Zoek de coureur op basis van de slug
    $stmt = $pdo->prepare("
        SELECT
            d.*,
            t.team_color,
            t.team_name,
            t.full_team_name
        FROM
            drivers d
        JOIN
            teams t ON d.team_id = t.team_id
        WHERE
            LOWER(REPLACE(CONCAT(d.first_name, '-', d.last_name), ' ', '')) = :slug
    ");
    $stmt->bindParam(':slug', $driverSlug);
    $stmt->execute();
    $driver = $stmt->fetch(PDO::FETCH_ASSOC);

    // Als de coureur niet gevonden is
    if (!$driver) {
        // Gebruik de F1-styling voor de foutpagina
        http_response_code(404);
        ?>
        <!DOCTYPE html>
        <html lang="nl">
        <head>
            <meta charset="UTF-8">
            <title>Coureur niet gevonden | F1</title>
            <script src="https://cdn.tailwindcss.com"></script>
            </head>
        <body class="bg-f1-black text-gray-100 p-8">
            <div class="max-w-xl mx-auto bg-f1-gray p-8 rounded-lg shadow-xl text-center">
                <h1 class="text-3xl font-oswald text-f1-red mb-4">404 - Coureur niet gevonden!</h1>
                <p class="text-gray-300 mb-6">De coureur die u zoekt, is helaas niet gevonden in onze database.</p>
                <a href='drivers.php' class="text-white bg-f1-red py-2 px-4 rounded hover:bg-red-700 transition">Terug naar coureuroverzicht</a>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
} catch (PDOException $e) {
    // Databasefout (gebruik F1-styling voor de foutpagina)
    http_response_code(500);
    ?>
    <!DOCTYPE html>
    <html lang="nl">
    <head>
        <meta charset="UTF-8">
        <title>Databasefout | F1</title>
        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="bg-f1-black text-gray-100 p-8">
        <div class="max-w-xl mx-auto bg-f1-gray p-8 rounded-lg shadow-xl text-center">
            <h1 class="text-3xl font-oswald text-f1-red mb-4">Databasefout</h1>
            <p class="text-gray-300 mb-6">Er is een fout opgetreden bij het laden van de gegevens.</p>
            <p class="text-xs text-red-400">Details: <?php echo htmlspecialchars($e->getMessage()); ?></p>
            <a href='drivers.php' class="text-white bg-f1-red py-2 px-4 rounded hover:bg-red-700 transition mt-4 inline-block">Terug naar coureuroverzicht</a>
        </div>
    </body>
    </html>
    <?php
    exit;
}
$teamColor = htmlspecialchars($driver['team_color'] ?? '#CCCCCC');
$driverFirstName = htmlspecialchars($driver['first_name']);
$driverLastName = htmlspecialchars($driver['last_name']);
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta name="description" content="Het meest complete F1 archief & statistieken sinds 1950. Vind uitslagen, records en alle coureursdata. Duik in de F1 historie!" />
    <script src="https://t.contentsquare.net/uxa/688c1fe6f0f7c.js"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $driverFirstName . ' ' . $driverLastName; ?> | Driver Details</title>
    <link rel="icon" type="image/x-icon" href="/afbeeldingen/logo/f1logobgrm.png">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Oswald:wght@400;600;700&display=swap" rel="stylesheet">
    
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
                <a href="teams.php" class="block py-2 px-3 md:p-0 hover:text-f1-red transition duration-150">Teams</a>
                <a href="drivers.php" class="block py-2 px-3 md:p-0 text-f1-red border-b-2 border-f1-red md:border-none active transition duration-150">Drivers</a>
                <a href="results.php" class="block py-2 px-3 md:p-0 hover:text-f1-red transition duration-150">Results</a>
                <a href="standings.php" class="block py-2 px-3 md:p-0 hover:text-f1-red transition duration-150">Standings</a>
            </nav>
        </div>
    </header>
    
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-8 flex-grow container">
        
        <div class="bg-f1-gray p-6 rounded-lg shadow-xl mb-8 page-header-section">
            <h1 class="text-3xl md:text-5xl font-oswald font-extrabold uppercase text-white page-heading text-center">
                <span style="color: <?php echo $teamColor; ?>;">#<?php echo htmlspecialchars($driver['driver_number']); ?></span> 
                <?php echo $driverFirstName . ' ' . $driverLastName; ?>
            </h1>
            <p class="text-center text-xl text-gray-400 font-semibold mt-1"><?php echo htmlspecialchars($driver['full_team_name']); ?></p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 driver-details-grid bg-f1-gray p-6 rounded-lg shadow-xl">
            
            <div class="md:col-span-2 driver-image-container">
                <?php if (!empty($driver['image'])): ?>
                    <img src="<?php echo htmlspecialchars($driver['image']); ?>" 
                         alt="<?php echo $driverFirstName . ' ' . $driverLastName; ?>" 
                         class="w-full h-auto object-cover rounded-lg shadow-2xl driver-image-details">
                <?php else: ?>
                    <div class="w-full h-64 bg-gray-700 flex items-center justify-center rounded-lg text-gray-400">Geen afbeelding beschikbaar</div>
                <?php endif; ?>
            </div>
            
            <div class="md:col-span-1 border-l-4 pl-4 driver-info-container" style="border-left-color: <?php echo $teamColor; ?>;">
                <dl class="driver-details-list space-y-3">
                    <div class="flex justify-between items-center">
                        <dt class="font-bold text-gray-300">Team:</dt>
                        <dd class="text-white font-semibold" style="color: <?php echo $teamColor; ?>;"><?php echo htmlspecialchars($driver['team_name']); ?></dd>
                    </div>

                    <div class="flex justify-between items-center border-t border-gray-700 pt-3">
                        <dt class="font-bold text-gray-300">Driver Number:</dt>
                        <dd class="text-white font-bold text-2xl">#<?php echo htmlspecialchars($driver['driver_number']); ?></dd>
                    </div>

                    <div class="flex justify-between items-center border-t border-gray-700 pt-3">
                        <dt class="font-bold text-gray-300">Nationality:</dt>
                        <dd class="text-white flex items-center">
                            <?php if (!empty($driver['flag_url'])): ?>
                                <img src="<?php echo htmlspecialchars($driver['flag_url']); ?>" alt="Vlag" class="flag-icon w-6 h-auto mr-2 rounded shadow-md">
                            <?php endif; ?>
                            <?php echo htmlspecialchars($driver['nationality']); ?>
                        </dd>
                    </div>

                    <?php if (!empty($driver['date_of_birth'])): ?>
                        <div class="flex justify-between items-center border-t border-gray-700 pt-3">
                            <dt class="font-bold text-gray-300">Date of birth:</dt>
                            <dd class="text-white"><?php echo htmlspecialchars(date('d-m-Y', strtotime($driver['date_of_birth']))); ?></dd>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($driver['career_points'])): ?>
                        <div class="flex justify-between items-center border-t border-gray-700 pt-3">
                            <dt class="font-bold text-gray-300">Career points:</dt>
                            <dd class="text-f1-red font-bold text-xl"><?php echo htmlspecialchars(number_format($driver['career_points'], 1, ',', '.')); ?></dd>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($driver['championships_won'])): ?>
                        <div class="flex justify-between items-center border-t border-gray-700 pt-3">
                            <dt class="font-bold text-gray-300">Championsships won:</dt>
                            <dd class="text-white font-bold text-xl"><?php echo htmlspecialchars($driver['championships_won']); ?></dd>
                        </div>
                    <?php endif; ?>
                </dl>
            </div>
        </div>

        <?php if (!empty($driver['description'])): ?>
            <div class="bg-f1-gray p-6 rounded-lg shadow-xl mt-8 driver-description">
                <h2 class="text-2xl font-oswald text-f1-red mb-4 border-b border-gray-700 pb-2">About the Driver</h2>
                <p class="text-gray-300 leading-relaxed"><?php echo nl2br(htmlspecialchars($driver['description'])); ?></p>
            </div>
        <?php endif; ?>

        <div class="mt-8 text-center">
            <a href="drivers.php" class="bg-f1-red text-white py-3 px-6 rounded-lg font-bold uppercase tracking-wider hover:bg-red-700 transition back-link">
                &larr; Back to Drivers list
            </a>
        </div>
    </main>
    
    <footer class="bg-black mt-12 py-8 border-t border-red-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-left pb-6 border-b border-gray-800">
                <div class="md:col-span-1 text-center md:text-left">
                    <h3 class="text-xl font-bold text-white mb-2 tracking-wider">F1SITE.NL</h3>
                    <p class="text-gray-500 text-sm mb-2">
                        De snelste bron voor F1 nieuws en data.
                    </p>
                </div>
                <div class="md:col-span-1 text-center md:text-left">
                    <h4 class="text-lg font-semibold text-red-500 mb-3 uppercase">Externe Sites</h4>
                    <ul class="space-y-2">
                        <li>
                            <a href="https://www.f1site.online" target="_blank" 
                            class="text-gray-400 text-sm hover:text-red-500 transition duration-150 block">
                            Voetbalsite (Zustersite)
                            </a>
                        </li>
                        <li>
                            <a href="https://www.webbair.nl" target="_blank" 
                            class="text-gray-400 text-sm hover:text-red-500 transition duration-150 block">
                            Webbair (Ontwikkelaar)
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="md:col-span-1 text-center md:text-left">
                    <h4 class="text-lg font-semibold text-red-500 mb-3 uppercase">Navigatie & Info</h4>
                    <ul class="space-y-2">
                        <li><a href="sitemap.html" class="text-gray-400 text-sm hover:text-red-500 transition duration-150 block">Sitemap</a></li>
                        <li><a href="privacy-en.html" class="text-gray-400 text-sm hover:text-red-500 transition duration-150 block">Privacy Policy (EN)</a></li>
                        <li><a href="algemenevoorwaarden-en.html" class="text-gray-400 text-sm hover:text-red-500 transition duration-150 block">Terms and Conditions (EN)</a></li>
                        <li><a href="contact.html" class="text-gray-400 text-sm hover:text-red-500 transition duration-150 block">Contact</a></li>
                    </ul>
                </div>
            </div>
            <div class="md:col-span-1 text-center md:text-left">
                <p class="text-gray-500 text-xs mt-4">&copy; 2025 Webbair. Alle rechten voorbehouden.</p>
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
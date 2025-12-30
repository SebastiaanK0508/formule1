<?php
require_once 'db_config.php'; 
require_once 'achterkant/aanpassing/api-koppelingen/1result_api.php';
$limit = 12; // Artikelen per pagina
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$total_articles = 0;
try {
    $count_stmt = $pdo->query("SELECT COUNT(*) FROM f1_nieuws");
    $total_articles = $count_stmt->fetchColumn();
} catch (PDOException $e) {
    error_log("Fout bij tellen nieuwsartikelen: " . $e->getMessage());
}
$total_pages = ceil($total_articles / $limit);

$news_articles = [];
try {
    $stmt = $pdo->prepare("SELECT titel, artikel_url, publicatie_datum, afbeelding_url, source 
                           FROM f1_nieuws 
                           ORDER BY publicatie_datum DESC, id DESC 
                           LIMIT :limit OFFSET :offset");
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $news_articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Fout bij ophalen nieuwsartikelen voor paginering: " . $e->getMessage());
}

if (isset($nextGrandPrix) && $nextGrandPrix && !isset($targetDateTime)) {
    $targetDateTime = '2025-11-20T14:00:00+01:00'; 
}

$schemaData = [
    '@context' => 'https://schema.org',
    '@type' => 'WebPage',
    'name' => 'F1 News',
    'url' => 'https://f1site.online/nieuws.php',
    'description' => 'Complete archive of Formula 1 news items.'
];
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta name="description" content="Volledig archief van alle Formule 1 nieuwsberichten van F1SITE.NL." />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>F1site - NEWS</title>
    <script src="https://t.contentsquare.net/uxa/688c1fe6f0f7c.js"></script>
    <link rel="icon" type="image/x-icon" href="/afbeeldingen/logo/f1logobgrm.png">
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="table.css">
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
        .news-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }
        .news-card {
            transition: transform 0.2s, box-shadow 0.2s;
            border-left: 5px solid transparent;
            display: flex;
            flex-direction: column;
            height: 100%; 
        }
        .news-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 15px rgba(225, 6, 0, 0.2);
            border-left-color: #E10600;
        }
        .news-image {
            height: 180px; 
        }
        .pagination-link {
            transition: background-color 0.2s, color 0.2s;
        }
        .pagination-link:hover {
            background-color: #E10600;
            color: white;
        }
        .pagination-link.active {
            background-color: #E10600;
            color: white;
            font-weight: bold;
        }
    </style>
    
    <?php if (!empty($schemaData)): ?>
    <script type="application/ld+json">
    <?php echo json_encode($schemaData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>
    </script>
    <?php endif; ?>
    
</head>
<body class="bg-f1-black text-gray-100 font-sans">
    
    <header class="bg-black shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex justify-between items-center header-content container">
            <h1 id="site-title-header" class="text-3xl font-oswald font-extrabold text-f1-red tracking-widest site-title">
                FORMULA 1
            </h1>
            <button class="md:hidden text-2xl text-f1-red hover:text-white menu-toggle" 
                    aria-controls="main-nav-links" aria-expanded="false" aria-label="Toggle navigation">
                &#9776; 
            </button>
            <nav class="main-nav md:flex md:space-x-8 text-sm font-semibold uppercase tracking-wider" 
                 id="main-nav-links" data-visible="false">
                <a href="index.php" class="block py-2 px-3 md:p-0 text-f1-red border-b-2 border-f1-red md:border-none active transition duration-150">Home</a>
                <a href="kalender.php" class="block py-2 px-3 md:p-0 hover:text-f1-red transition duration-150">Schedule</a>
                <a href="teams.php" class="block py-2 px-3 md:p-0 hover:text-f1-red transition duration-150">Teams</a>
                <a href="drivers.php" class="block py-2 px-3 md:p-0 hover:text-f1-red transition duration-150">Drivers</a>
                <a href="results.php" class="block py-2 px-3 md:p-0 hover:text-f1-red transition duration-150">Results</a>
                <a href="standings.php" class="block py-2 px-3 md:p-0 hover:text-f1-red transition duration-150">Standings</a>
            </nav>
        </div>
    </header>
    
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-8 container">
        <!-- nog toevoegen later -->
        <!-- <div class="bg-f1-gray p-6 rounded-lg shadow-xl mb-8 flex flex-col md:flex-row justify-between items-center page-header-section">
            <div class="text-center md:text-left mb-4 md:mb-0">
                <h3 class="text-xl md:text-2xl font-oswald font-bold text-white uppercase page-heading">
                    <?php
                    // if (isset($nextGrandPrix) && $nextGrandPrix) {
                    //     echo htmlspecialchars($nextGrandPrix['grandprix']);
                    // } else {
                    //     echo "Geen aankomende Grand Prix";
                    // }
                    // ?>
                </h3>
                <p class="text-sm text-gray-400">Next Race</p>
            </div>
            <div class="text-center text-3xl md:text-4xl font-oswald font-extrabold text-f1-red page-heading" id="countdown">
            </div>
        </div> -->

        <section class="mb-12 f1-section">
            <h2 class="text-4xl font-oswald font-bold text-white uppercase mb-8 border-b border-f1-red pb-3 news-heading">
                News Archive
            </h2>
            <?php if (!empty($news_articles)): ?>
                <div class="news-grid mb-10">
                    <?php foreach ($news_articles as $article): ?>
                        <div class="bg-f1-gray p-5 rounded-lg shadow-xl news-card">
                            <?php if ($article['afbeelding_url']): ?>
                                <img src="<?php echo htmlspecialchars($article['afbeelding_url']); ?>" alt="Afbeelding bij nieuwsartikel" class="w-full news-image object-cover rounded-md mb-4">
                            <?php endif; ?>
                            <div class="flex flex-col flex-grow">
                                <h3 class="text-xl font-oswald font-semibold mb-2 news-title flex-grow">
                                    <a href="<?php echo htmlspecialchars($article['artikel_url']); ?>" target="_blank" 
                                       class="text-gray-100 hover:text-f1-red transition duration-150 block">
                                        <?php echo htmlspecialchars($article['titel']); ?>
                                    </a>
                                </h3>
                                
                                <div class="mt-auto">
                                    <?php if (!empty($article['source'])): ?>
                                        <span class="text-f1-red font-bold uppercase text-xs mb-1 block">
                                            Source: <?php echo htmlspecialchars($article['source']); ?>
                                        </span>
                                    <?php endif; ?>

                                    <?php if ($article['publicatie_datum']): ?>
                                        <p class="text-xs text-gray-400 news-date">
                                            <?php 
                                                try {
                                                    $date = new DateTime($article['publicatie_datum']);
                                                    echo 'Gepubliceerd op: ' . $date->format('d-m-Y H:i');
                                                } catch (Exception $e) {
                                                    echo 'Datum onbekend';
                                                }
                                            ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <nav class="flex justify-center items-center space-x-2 mt-8" aria-label="Pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>" 
                           class="pagination-link px-4 py-2 text-sm bg-f1-gray rounded-lg hover:bg-f1-red hover:text-white transition duration-150">
                           &larr; Vorige
                        </a>
                    <?php endif; ?>

                    <?php 
                    // Toon maximaal 7 paginanummers, gecentreerd rond de huidige pagina
                    $start_page = max(1, $page - 3);
                    $end_page = min($total_pages, $page + 3);
                    
                    if ($end_page - $start_page < 6) {
                        $start_page = max(1, $end_page - 6);
                    }
                    if ($end_page - $start_page < 6) {
                        $end_page = min($total_pages, $start_page + 6);
                    }
                    
                    if ($start_page > 1) {
                        echo '<a href="?page=1" class="pagination-link px-4 py-2 text-sm rounded-lg bg-f1-gray">1</a>';
                        if ($start_page > 2) {
                            echo '<span class="px-2 py-2 text-sm text-gray-400">...</span>';
                        }
                    }

                    for ($i = $start_page; $i <= $end_page; $i++): ?>
                        <a href="?page=<?php echo $i; ?>" 
                           class="pagination-link px-4 py-2 text-sm rounded-lg <?php echo ($i == $page ? 'active' : 'bg-f1-gray'); ?>">
                           <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($end_page < $total_pages): 
                        if ($end_page < $total_pages - 1) {
                            echo '<span class="px-2 py-2 text-sm text-gray-400">...</span>';
                        }
                        echo '<a href="?page=' . $total_pages . '" class="pagination-link px-4 py-2 text-sm rounded-lg bg-f1-gray">' . $total_pages . '</a>';
                    endif; ?>

                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page + 1; ?>" 
                           class="pagination-link px-4 py-2 text-sm bg-f1-gray rounded-lg hover:bg-f1-red hover:text-white transition duration-150">
                           Volgende &rarr;
                        </a>
                    <?php endif; ?>
                </nav>

            <?php else: ?>
                <p class="text-gray-400">Er zijn geen nieuwsartikelen gevonden in het archief.</p>
            <?php endif; ?>
        </section>
        
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
                            <a href="https://www.webbair.nl" target="_blank" 
                            class="text-gray-400 text-sm hover:text-red-500 transition duration-150 block">
                            Webbair (Ontwikkelaar)
                            </a>
                        </li>
                        <li>
                            <a href="https://urenheld.webbair.nl" target="_blank" 
                            class="text-gray-400 text-sm hover:text-red-500 transition duration-150 block">
                            Urenheld
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
                <p class="text-gray-500 text-xs mt-4">&copy; <?php echo (date('Y')); ?> Webbair. Alle rechten voorbehouden.</p>
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
    <script>
        <?php if (isset($nextGrandPrix) && $nextGrandPrix && isset($targetDateTime)): ?>
        const targetDateTime = new Date('<?php echo $targetDateTime; ?>').getTime(); 
        const countdownElement = document.getElementById('countdown');
        function updateCountdown() {
            const now = new Date().getTime();
            const distance = targetDateTime - now;
            
            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);
            
            if (distance < 0) {
                countdownElement.innerHTML = "<span class='text-white text-xl'>Race is bezig!</span>";
                clearInterval(countdownInterval);
            } else {
                countdownElement.innerHTML =
                    `<span class="text-f1-red">${days}</span>d <span class="text-white">|</span> <span class="text-f1-red">${hours}</span>h <span class="text-white">|</span> <span class="text-f1-red">${minutes}</span>m <span class="text-white">|</span> <span class="text-f1-red">${seconds}</span>s`;
            }
        }
        updateCountdown();
        const countdownInterval = setInterval(updateCountdown, 1000);
        <?php else: ?>
        document.getElementById('countdown').innerHTML = "<span class='text-white text-xl'>Niet beschikbaar</span>";
        console.log("Geen volgende Grand Prix om af te tellen of $targetDateTime is niet gezet.");
        <?php endif; ?>
    </script>
</body>
</html>
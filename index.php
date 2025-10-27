<?php
    require_once 'achterkant/aanpassing/api-koppelingen/1result_api.php';
    if (isset($nextGrandPrix) && $nextGrandPrix && !isset($targetDateTime)) {
        $targetDateTime = '2025-11-20T14:00:00+01:00'; 
    }
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formula 1 - Home</title>
    <script src="https://t.contentsquare.net/uxa/688c1fe6f0f7c.js"></script>
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
        
        <div class="bg-f1-gray p-6 rounded-lg shadow-xl mb-8 flex flex-col md:flex-row justify-between items-center page-header-section">
            <div class="text-center md:text-left mb-4 md:mb-0">
                <h3 class="text-xl md:text-2xl font-oswald font-bold text-white uppercase page-heading">
                    <?php
                    if ($nextGrandPrix) {
                        echo htmlspecialchars($nextGrandPrix['grandprix']);
                    } else {
                        echo "Geen aankomende Grand Prix";
                    }
                    ?>
                </h3>
                <p class="text-sm text-gray-400">Next Race</p>
            </div>
            <div class="text-center text-3xl md:text-4xl font-oswald font-extrabold text-f1-red page-heading" id="countdown">
                </div>
        </div>
        
        <?php if ($error_message): ?>
            <div class="bg-red-900 text-white p-4 rounded-lg mb-8 error-message">
                <?php echo $error_message; ?>
            </div>
        <?php else: ?>
            <div class="selection-link">
                <?php if (!empty($races_in_season)): ?>
                    <?php else: ?>
                    <p class="text-gray-400">Geen races gevonden</p>
                <?php endif; ?>
            </div>
            
            <?php if ($race_details): ?>
            <section class="bg-f1-gray p-6 rounded-lg shadow-xl f1-section">
                
                <div class="border-b border-gray-600 pb-4 mb-6 race-info-card">
                    <h2 class="text-2xl font-oswald font-bold text-f1-red mb-2 page-heading">
                        Result <?php echo htmlspecialchars($race_details['name']); ?>
                    </h2>
                    <p class="text-gray-300 text-sm">
                        <strong>Location:</strong> <?php echo htmlspecialchars($race_details['circuit']); ?>, 
                        <?php echo htmlspecialchars($race_details['location']); ?>, 
                        <?php echo htmlspecialchars($race_details['country']); ?>
                    </p>
                    <p class="text-gray-300 text-sm">
                        <strong>Date:</strong> <?php echo htmlspecialchars((new DateTime($race_details['date']))->format('d-m-Y')); ?>
                    </p>
                </div>
                
                <?php if (!empty($race_results)): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-f1-black rounded-lg data-table">
                        <thead class="bg-f1-red text-white uppercase text-xs tracking-wider">
                            <tr>
                                <th class="py-3 px-4 text-left font-bold rounded-tl-lg">Pos</th>
                                <th class="py-3 px-4 text-left font-bold">Driver</th>
                                <th class="py-3 px-4 text-left font-bold">Team</th>
                                <th class="py-3 px-4 text-left font-bold rounded-tr-lg">Time / Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($race_results as $result): ?>
                                <tr class="border-b border-gray-700 hover:bg-gray-800 transition duration-150">
                                    <td class="py-3 px-4 font-oswald font-bold text-lg text-f1-red">
                                        <?php echo htmlspecialchars($result['position']); ?>
                                    </td>
                                    <td class="py-3 px-4 font-semibold">
                                        <?php echo htmlspecialchars($result['driver_name']); ?>
                                    </td>
                                    <td class="py-3 px-4 font-medium" 
                                        style="border-left: 5px solid <?php echo htmlspecialchars($result['team_color']); ?>; padding-left: 10px;">
                                        <?php echo htmlspecialchars($result['team_name']); ?>
                                    </td>
                                    <td class="py-3 px-4 text-sm text-gray-300">
                                        <?php echo htmlspecialchars($result['lap_time_or_status']); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <p class="text-gray-400">Er zijn geen resultaten beschikbaar voor deze race.</p>
                <?php endif; ?>
            </section>
            <?php else: ?>
                <p class="text-gray-400">Selecteer een race om de resultaten te bekijken.</p>
            <?php endif; ?>
        <?php endif; ?>
    </main>
    
    <footer class="bg-black mt-12 py-6 border-t border-f1-red">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center footer-content container">
            <p class="text-gray-400 text-sm mb-4">&copy; 2025 Webbair. Alle rechten voorbehouden.</p>
            <div class="flex flex-wrap justify-center space-x-4 mb-4 social-links">
                <a href="#" class="text-gray-400 hover:text-f1-red transition duration-150" aria-label="Facebook">Facebook</a>
                <a href="#" class="text-gray-400 hover:text-f1-red transition duration-150" aria-label="Twitter">X</a>
                <a href="www.webbair.online" class="text-gray-400 hover:text-f1-red transition duration-150" aria-label="Instagram">Instagram</a>
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
    <script>
        <?php if ($nextGrandPrix && isset($targetDateTime)): ?>
        const targetDateTime = new Date('<?php echo $targetDateTime; ?>').getTime(); // Gebruik .getTime()
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
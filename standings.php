<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formula 1 - Standings</title>
    <link rel="icon" type="image/x-icon" href="/afbeeldingen/logo/f1logobgrm.png">
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <script src="https://t.contentsquare.net/uxa/688c1fe6f0f7c.js"></script>
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
                        'f1-table-header': '#21212B', 
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
            .standings-table tr {
                padding-right: 3rem !important; /* Voldoende ruimte voor de punten */
            }
            .team-name-mobile-driver {
                display: flex;
                flex-direction: row;
                order: 1; /* Zorgt dat het na de Coureursnaam komt */
                margin-left: 48px; /* Lijn uit met Coureursnaam (40px pos + 8px margin/padding) */
                font-size: 0.600rem; 
                color: #B0B0B0;
                padding-left: 0 !important; /* Reset padding van de td */
            }
            .standings-table thead {
                display: none;
            }
        }
        .standings-table-container {
            background-color: #3A3A40; 
            border-radius: 0.75rem;
            padding: 1.25rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.5);
        }
        .standings-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            color: #D1D5DB;
            text-align: left;
        }
        .standings-table th {
            background-color: #21212B;
            color: white;
            padding: 0.75rem 0.5rem;
            text-transform: uppercase;
            font-size: 0.875rem;
            font-family: 'Oswald', sans-serif;
        }
        .standings-table td {
            padding: 0.75rem 0.5rem;
            border-bottom: 1px solid #3A3A40;
        }
        .standings-table tbody tr:hover {
            background-color: rgba(225, 6, 0, 0.1);
        }
    </style>

</head>
<link rel="stylesheet" href="table.css">
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
                <a href="drivers.php" class="block py-2 px-3 md:p-0 hover:text-f1-red transition duration-150">Drivers</a>
                <a href="results.php" class="block py-2 px-3 md:p-0 hover:text-f1-red transition duration-150">Results</a>
                <a href="standings.php" class="block py-2 px-3 md:p-0 text-f1-red border-b-2 border-f1-red md:border-none active transition duration-150">Standings</a>
            </nav>
        </div>
    </header>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-8 flex-grow container">
    
    <section class="bg-f1-gray p-6 rounded-lg shadow-xl mb-8 page-header-section">
        <h2 class="text-xl md:text-3xl font-oswald font-bold text-white uppercase page-heading text-center">
            Standings 2025
        </h2>
    </section>

    <section id="standings-content">
        <p class="text-center text-gray-400 p-4">Loading...</p>
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
    const standingsContent = document.getElementById('standings-content');
    
    async function fetchChampionshipStandings() {
        try {
            // Dit moet verwijzen naar uw API endpoint
            const response = await fetch('achterkant/aanpassing/api-koppelingen/standings_api.php'); 
            const data = await response.json();
            
            if (data.status === 'success') {
                displayChampionshipStandings(data.drivers, data.constructors);
            } else {
                standingsContent.innerHTML = `<p class="text-red-500 text-center p-4">Fout bij het laden van klassementen: ${data.message}</p>`;
                console.error('API Error:', data.message);
            }
        } catch (error) {
            standingsContent.innerHTML = `<p class="text-red-500 text-center p-4">Netwerkfout bij het laden van klassementen.</p>`;
            console.error('Fetch Error:', error);
        }
    }

    function displayChampionshipStandings(drivers, constructors) {
        let html = '<div class="flex flex-col lg:flex-row gap-6 standings-grid">';
        html += '<div class="flex-1 min-w-0 data-table-container">'; 
        html += '<h4 class="text-xl font-oswald font-semibold text-white text-center mb-4">Drivers Championship</h4>';
        if (drivers.length > 0) {
            html += `
                <table class="data-table w-full border-collapse rounded-lg overflow-hidden"> 
                    <thead>
                        <tr>
                            <th class="rounded-tl-lg">Pos.</th>
                            <th>Driver</th>
                            <th class="hidden lg:table-cell">Team</th>
                            <th class="rounded-tr-lg text-right">Points</th>
                        </tr>
                    </thead>
                    <tbody>
            `;
            drivers.forEach(driver => {
                html += `
                    <tr class="flex flex-wrap relative mb-4 bg-f1-gray border-l-4 border-f1-red rounded lg:table-row lg:mb-0 lg:border-l-0">
                        
                        <td class="font-oswald font-bold text-2xl w-[40px] flex-shrink-0 text-white p-2 lg:p-3 lg:w-auto lg:text-base">
                            ${driver.position}
                        </td>
                        <td class="font-medium flex-1 ml-2 text-base text-[#E1E1E1] p-2 lg:p-3">
                            ${driver.given_name} ${driver.family_name}
                        </td>
                        <td class="team-name-mobile-driver lg:table-cell text-gray-400 text-sm p-3">
                            ${driver.constructor_name}
                        </td>
                        <td class="font-bold text-f1-red text-xl absolute top-3 right-3 lg:static lg:p-3 lg:text-right lg:text-base">
                            ${driver.points}
                        </td>
                    </tr>
                `;
            });
            html += `
                    </tbody>
                </table>
            `;
        } else {
            html += '<p class="text-gray-400 p-4">Geen coureursklassement beschikbaar op dit moment.</p>';
        }
        html += '</div>'; // end standings-table-container (Drivers)
        
        // CONSTRUCTORS STANDINGS CONTAINER
        html += '<div class="flex-1 min-w-0 data-table-container">';
        html += '<h4 class="text-xl font-oswald font-semibold text-white text-center mb-4">Constructors Championship</h4>';
        if (constructors.length > 0) {
            html += `
                <table class="data-table w-full border-collapse rounded-lg overflow-hidden"> 
                    <thead>
                        <tr>
                            <th class="rounded-tl-lg">Pos.</th>
                            <th>Team</th>
                            <th class="rounded-tr-lg text-right">Points</th>
                        </tr>
                    </thead>
                    <tbody>
            `;
            constructors.forEach(constructor => {
                html += `
                    <tr class="flex flex-wrap relative mb-4 bg-f1-gray border-l-4 border-f1-red rounded lg:table-row lg:mb-0 lg:border-l-0">
                        <td class="font-oswald font-bold text-2xl w-[40px] flex-shrink-0 text-white p-2 lg:p-3 lg:w-auto lg:text-base">
                            ${constructor.position}
                        </td>
                        
                        <td class="font-medium flex-1 ml-2 text-base text-[#E1E1E1] p-2 lg:p-3">
                            ${constructor.name}
                        </td>
                        
                        <td class="font-bold text-f1-red text-xl absolute top-3 right-3 lg:static lg:p-3 lg:text-right lg:text-base">
                            ${constructor.points}
                        </td>
                    </tr>
                `;
            });
            html += `
                    </tbody>
                </table>
            `;
        } else {
            html += '<p class="text-gray-400 p-4">Geen constructeursklassement beschikbaar op dit moment.</p>';
        }
        html += '</div>'; // end standings-table-container (Constructors)
        
        html += '</div>'; // end standings-grid
        
        standingsContent.innerHTML = html;
    }
    
    fetchChampionshipStandings();
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
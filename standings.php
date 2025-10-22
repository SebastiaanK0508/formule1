<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formula 1 - Standings</title>
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
                        'f1-table-header': '#21212B', // Iets lichter dan de achtergrond voor de kop
                    }
                }
            }
        }
    </script>
    <style>
        /* Mobile Nav Toggle */
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

        /* CUSTOM CSS voor de Responsieve Tabellen (Tailwind Media Queries werken niet in de JS string) */
        
        /* Basis Table Styling voor Desktop */
        .standings-table-container {
            /* Vervangt de basis .standings-table-container CSS */
            background-color: #3A3A40; /* f1-gray */
            border-radius: 0.75rem;
            padding: 1.25rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.5);
        }
        .standings-table {
            /* Vervangt de basis .standings-table CSS */
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            color: #D1D5DB; /* gray-300 */
            text-align: left;
        }
        .standings-table th {
            /* Vervangt de basis .standings-table th CSS */
            background-color: #21212B; /* f1-table-header */
            color: white;
            padding: 0.75rem 0.5rem;
            text-transform: uppercase;
            font-size: 0.875rem; /* text-sm */
            font-family: 'Oswald', sans-serif;
        }
        .standings-table td {
            /* Standaard cel styling */
            padding: 0.75rem 0.5rem;
            border-bottom: 1px solid #3A3A40; /* f1-gray */
        }
        .standings-table tbody tr:hover {
            background-color: rgba(225, 6, 0, 0.1); /* f1-red met transparantie */
        }

        /* Responsive aanpassing voor Mobile (max-width: 768px, equivalent aan Tailwind's md breakpoint) */
        @media (max-width: 767px) {
            .standings-grid {
                flex-direction: column; 
                gap: 1rem;
                width: 100%; 
            }
            .standings-table-container {
                /* Verwijder padding en marges voor full width */
                padding: 0;
                box-shadow: none;
            }
            .standings-table {
                /* Zorgt dat de container niet te ver uitloopt */
                margin-left: -1rem;
                margin-right: -1rem;
                width: calc(100% + 2rem);
            }
            .standings-table thead {
                /* Verberg de desktop header */
                display: none;
            }
            .standings-table tr {
                /* Maak er een card van */
                display: flex;
                flex-wrap: wrap; 
                margin-bottom: 1rem;
                background-color: #3A3A40; /* f1-gray */
                border-left: 5px solid var(--f1-red); /* Dynamische F1-rode streep */
                border-radius: 0.5rem;
                padding: 0.75rem;
                box-shadow: 0 2px 4px rgba(0,0,0,0.5);
                position: relative;
                width: 100%;
            }
            .standings-table td {
                border: none;
                padding: 0.25rem 0;
                text-align: left;
            }
            
            /* Positie */
            .standings-table tr td:first-child {
                font-weight: 700;
                font-size: 1.5rem;
                margin-bottom: 0.25rem;
                width: 40px;
                color: white;
            }
            /* Naam (Coureur/Team) */
            .standings-table tr td:nth-child(2) {
                font-size: 1rem;
                font-weight: 500;
                margin-left: 0.5rem;
                flex-grow: 1; 
                color: #E1E1E1;
            }
            /* Punten */
            .standings-table tr td:last-child {
                font-weight: 700;
                color: #E10600; /* f1-red */
                font-size: 1.25rem;
                position: absolute; 
                top: 0.75rem;
                right: 0.75rem;
            }
            /* Team Name (alleen voor Coureurs, maar verbergen we sowieso) */
            .team-name-mobile-driver {
                display: none; 
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
        // Gebruik Tailwind classes direct in de HTML string
        let html = '<div class="flex flex-col lg:flex-row gap-6 standings-grid">';
        
        // DRIVERS STANDINGS CONTAINER
        html += '<div class="flex-1 min-w-0 standings-table-container">'; 
        html += '<h4 class="text-xl font-oswald font-semibold text-white text-center mb-4">Drivers Championship</h4>';
        if (drivers.length > 0) {
            html += `
                <table class="standings-table"> 
                    <thead>
                        <tr>
                            <th class="rounded-tl-lg">Pos.</th>
                            <th>Driver</th>
                            <th class="team-name-mobile-driver hidden lg:table-cell">Team</th>
                            <th class="rounded-tr-lg text-right">Points</th>
                        </tr>
                    </thead>
                    <tbody>
            `;
            drivers.forEach(driver => {
                html += `
                    <tr>
                        <td class="font-oswald font-bold">${driver.position}</td>
                        <td class="font-medium">${driver.given_name} ${driver.family_name}</td>
                        <td class="team-name-mobile-driver hidden lg:table-cell text-gray-400 text-sm">${driver.constructor_name}</td>
                        <td class="font-bold text-f1-red text-right">${driver.points}</td>
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
        html += '<div class="flex-1 min-w-0 standings-table-container">';
        html += '<h4 class="text-xl font-oswald font-semibold text-white text-center mb-4">Constructors Championship</h4>';
        if (constructors.length > 0) {
            html += `
                <table class="standings-table"> 
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
                    <tr>
                        <td class="font-oswald font-bold">${constructor.position}</td>
                        <td class="font-medium">${constructor.name}</td>
                        <td class="font-bold text-f1-red text-right">${constructor.points}</td>
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

    // Mobile Nav Toggle (voor volledigheid)
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
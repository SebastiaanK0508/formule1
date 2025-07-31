<?php
require_once 'achterkant/aanpassing/api-koppelingen/result_api.php';
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formula 1 Results</title>
    <!-- Google Fonts: Oswald voor koppen, Roboto voor tekst -->
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        /* Specifieke CSS voor de race-uitslagen weergave, nu ingebed */
        body {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f0f2f5;
            color: #333;
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        header {
            background-color: #1a1a1a;
            color: #fff;
            padding: 15px 0;
            border-bottom: 5px solid #dc0000; /* F1 rood accent */
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap; /* Voor responsiviteit */
        }

        .site-title {
            font-family: 'Oswald', sans-serif;
            margin: 0;
            font-size: 1.8em;
            color: #fff;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .main-nav a {
            color: #fff;
            text-decoration: none;
            padding: 8px 15px;
            margin-left: 10px;
            border-radius: 5px;
            transition: background-color 0.3s ease, color 0.3s ease;
            font-weight: 500;
        }

        .main-nav a:hover,
        .main-nav a.active {
            background-color: #dc0000; /* F1 rood */
            color: #fff;
        }

        /* Main content styling */
        .results-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }
        .page-heading {
            font-family: 'Oswald', sans-serif;
            font-size: 2.5em;
            color: #1a1a1a;
            text-align: center;
            margin-bottom: 30px;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 2px solid #dc0000;
            padding-bottom: 10px;
            display: inline-block;
            margin-left: auto;
            margin-right: auto;
        }
        /* .page-intro is verwijderd zoals gevraagd */
        .race-selection-container {
            text-align: center;
            margin-bottom: 30px;
            padding: 15px;
            background-color: #f0f0f0;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            display: flex; /* Gebruik flexbox voor horizontale lay-out */
            flex-wrap: wrap; /* Laten wrappen op kleinere schermen */
            justify-content: center;
            gap: 10px; /* Ruimte tussen de links */
        }
        .race-selection-container a {
            display: inline-block; /* Zorgt ervoor dat padding en margins werken */
            text-decoration: none;
            color: #1a1a1a;
            background-color: #fff;
            padding: 10px 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 0.9em;
            transition: background-color 0.3s ease, border-color 0.3s ease, color 0.3s ease;
            white-space: nowrap; /* Voorkom dat tekst breekt */
        }
        .race-selection-container a:hover {
            background-color: #e0e0e0;
            border-color: #a0a0a0;
        }
        .race-selection-container a.active {
            background-color: #dc0000; /* Actieve link in F1 rood */
            color: #fff;
            border-color: #dc0000;
            font-weight: bold;
        }
        /* Styling voor de opties binnen de select (beperkte controle met pure CSS) */
        /* Deze sectie is nu niet meer direct van toepassing, maar behouden voor referentie */
        .race-selection-container select option {
            padding: 10px;
            background-color: #fff;
            color: #333;
        }

        .race-info-card {
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            text-align: center;
        }
        .race-info-card h3 {
            font-family: 'Oswald', sans-serif;
            font-size: 1.8em;
            color: #dc0000;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        .race-info-card p {
            font-size: 1em;
            color: #555;
            margin-bottom: 5px;
        }
        .results-table-container {
            overflow-x: auto;
            margin-bottom: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }
        .results-table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
        }
        .results-table th, .results-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        .results-table th {
            background-color: #1a1a1a; /* Donkere header */
            color: #dc0000; /* F1 rood */
            font-family: 'Oswald', sans-serif;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.9em;
        }
        .results-table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .results-table tbody tr:hover {
            background-color: #f0f0f0;
        }
        .results-table td {
            color: #333;
            font-size: 0.95em;
        }
        .results-table .position {
            font-weight: bold;
            color: #1a1a1a;
            text-align: center;
            width: 80px; /* Vaste breedte voor positie */
        }
        .results-table .driver-name {
            font-weight: 500;
            color: #0056b3; /* Blauw voor coureur naam */
        }
        .results-table .team-name {
            color: #666;
        }
        .results-table .lap-time-status {
            font-style: italic;
            color: #888;
        }
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            margin-top: 20px;
        }

        /* Footer styling */
        footer {
            background-color: #1a1a1a;
            color: #fff;
            padding: 20px 0;
            margin-top: 40px;
            text-align: center;
        }

        .footer-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .social-links a {
            color: #fff;
            text-decoration: none;
            margin: 0 10px;
            transition: color 0.3s ease;
        }

        .social-links a:hover {
            color: #dc0000; /* F1 rood */
        }

        /* Responsive aanpassingen */
        @media (max-width: 768px) {
            .page-heading {
                font-size: 2em;
            }
            .race-info-card h3 {
                font-size: 1.5em;
            }
            .results-table th, .results-table td {
                padding: 8px 10px;
                font-size: 0.85em;
            }
            .results-table .position {
                width: 60px;
            }
            .race-selection-container {
                flex-direction: column; /* Stapelen op kleinere schermen */
                gap: 5px;
            }
            .race-selection-container a {
                width: 90%; /* Nemen bijna de volledige breedte in */
                margin: 0 auto; /* Centreer ze */
            }
            .header-content {
                flex-direction: column;
                text-align: center;
            }
            .main-nav {
                margin-top: 15px;
            }
            .main-nav a {
                margin: 5px;
                display: inline-block;
            }
        }
        @media (max-width: 480px) {
            .page-heading {
                font-size: 1.8em;
            }
            .race-info-card {
                padding: 15px;
            }
            .results-table th, .results-table td {
                padding: 6px 8px;
                font-size: 0.8em;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="header-content container">
            <h1 class="site-title">FORMULA 1 SEASON 2025</h1>
            <nav class="main-nav">
                <a href="index.php">Home</a>
                <a href="kalender.php">Schedule</a>
                <a href="teams.php">Teams</a>
                <a href="drivers.php">Drivers</a>
                <a href="results.php" class="active">Results</a>
                <a href="standings.php">Standings</a>
            </nav>
        </div>
    </header>

    <main class="contianer">
        <section class="results-container">
        <?php if ($error_message): ?>
            <div class="error-message">
                <?php echo $error_message; ?>
            </div>
        <?php else: ?>
            <div class="race-selection-container">
                <?php if (!empty($races_in_season)): ?>
                    <?php foreach ($races_in_season as $race): ?>
                        <a href="results.php?round=<?php echo htmlspecialchars($race['round']); ?>"
                           class="<?php echo ($selected_round == $race['round']) ? 'active' : ''; ?>">
                            Round <?php echo htmlspecialchars($race['round']); ?>: <?php echo htmlspecialchars($race['raceName']); ?> (<?php echo htmlspecialchars((new DateTime($race['date']))->format('d-m')); ?>)
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Geen races gevonden</p>
                <?php endif; ?>
            </div>
        </section>
        <section class="results-container">
        <h2 class="page-heading">FORMULE 1 RACE RESULTS <?php echo $current_year; ?></h2>
        


            <?php if (empty($race_results)): ?>
                <p class="error-message">Geen uitslagen beschikbaar voor de geselecteerde race. Mogelijk is deze race nog niet verreden.</p>
            <?php else: ?>
                <div class="race-info-card">
                    <h3><?php echo htmlspecialchars($race_details['name']); ?></h3>
                    <p><strong>Circuit:</strong> <?php echo htmlspecialchars($race_details['circuit']); ?></p>
                    <p><strong>Locatie:</strong> <?php echo htmlspecialchars($race_details['location']); ?>, <?php echo htmlspecialchars($race_details['country']); ?></p>
                    <p><strong>Datum:</strong> <?php echo htmlspecialchars((new DateTime($race_details['date']))->format('d-m-Y')); ?></p>
                </div>

                <div class="results-table-container">
                    <table class="results-table">
                        <thead>
                            <tr>
                                <th class="position">Pos</th>
                                <th>Driver</th>
                                <th>Team</th>
                                <th>Time / Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($race_results as $result): ?>
                                <tr style="border-left: 5px solid <?php echo htmlspecialchars($result['team_color'] ?? '#CCCCCC'); ?>;">
                                    <td class="position"><?php echo htmlspecialchars($result['position']); ?></td>
                                    <td class="driver-name"><?php echo htmlspecialchars($result['driver_name']); ?></td>
                                    <td style="color: <?php echo htmlspecialchars($result['team_color'] ?? '#CCCCCC'); ?>;" class="team-name"><?php echo htmlspecialchars($result['full_team_name']); ?></td>
                                    <td class="lap-time-status"><?php echo htmlspecialchars($result['lap_time_or_status']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        <?php endif; ?>
        </section>
    </main>

    <footer>
        <div class="footer-content container">
            <p>&copy; <?php echo $current_year; ?> Webbair. Alle rechten voorbehouden.</p>
            <div class="social-links">
                <a href="#" aria-label="Facebook">Facebook</a>
                <a href="#" aria-label="Twitter">Twitter</a>
                <a href="#" aria-label="Instagram">Instagram</a>
            </div>
        </div>
    </footer>
</body>
</html>

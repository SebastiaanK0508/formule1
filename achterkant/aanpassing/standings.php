<?php
$host = 'localhost';    
$dbname = 'formule1';  
$username = 'root';    
$password = 'root';   

try {
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {

    die("Databaseverbinding mislukt: " . $e->getMessage());
}

$sql_drivers = "
    SELECT
        d.driver_id,
        d.first_name,
        d.last_name,
        t.team_name,
        SUM(ps.points) AS total_points
    FROM
        drivers d
    JOIN
        teams t ON d.team_name = t.team_name
    JOIN
        race_results rr ON d.driver_id = rr.driver_id
    JOIN
        points_system ps ON rr.position = ps.position
    GROUP BY
        d.driver_id, d.first_name, d.last_name, t.team_name
    ORDER BY
        total_points DESC, d.last_name ASC;
";

$stmt_drivers = $pdo->query($sql_drivers);
$driver_standings = $stmt_drivers->fetchAll(PDO::FETCH_ASSOC);

$sql_teams = "
    SELECT
        t.team_id,
        t.team_name,
        SUM(ps.points) AS total_points
    FROM
        teams t
    JOIN
        drivers d ON t.team_name = d.team_name -- Weer de join via team_name
    JOIN
        race_results rr ON d.driver_id = rr.driver_id
    JOIN
        points_system ps ON rr.position = ps.position
    GROUP BY
        t.team_id, t.team_name
    ORDER BY
        total_points DESC, t.team_name ASC;
";

$stmt_teams = $pdo->query($sql_teams);
$team_standings = $stmt_teams->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Webbair Framework - Standings</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="" type="image/x-icon">
</head>
<body class="">
    <header class="main-header">
        <div class="header-title">
            <h1>Formula 1 site - Standings</h1>
        </div>
        <div class="header-info">
            <p class="header-sitename">Formula 1</p>
            <img class="header-logo" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAOEAAADhCAMAAAAJbSJIAAAAkFBMVEX////uKibuIx7uJyPxYV/tGxb2k5HwOTTuIBv+9fXzdHLuIx/wW1n0e3j//Pz2kY/95+f2l5X3npzzbWvtFhD+8PD2mZf5srHzaWb4pqXwPzv82djyWVb96en94+L6xcTxUE71iYf7zs3vMS35u7n0g4HwR0T4rKv71tX0fnztDQPvOzf6ysn5uLf3oqHwRUIQ2pq/AAAEuklEQVR4nO3Z25qqOBAG0ECIBlBAAUEFPLSHbvv0/m/3ic7Mnn2RYtRq9s2/bjtfY1FJVUiEAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD4g5Kw2yzTaV4HP23yFgrR1RNKc+BNLlxvZvlKFoXS2v9pars2AQYqdtNyzhpfd85Pnval9IagTibAsPGJIbKYJ3zhtVVQFHqg6Ay/ac07bRQVoPfFF195nvjU22SnGpPBNiAzGPNlMDyc/Hiw4Cx/azMYaGKIlHwZ7KJ42Pg8Hax7A/T4isxmqwdbfTe66ewUJQMsvrimaLLUgy5Aww+ya5sghkhvwRSfEEtv4AR6amIyWE7IIqPYikxyVEMH6E9Kk8EJnUG2NRimgweoapPBjG4Tki2D4TQeOkAdlDZAssj4bG2inBaDRfY3nff03Qb4iU0YDd0EzRW0VLekpyrcXbSeDZ9DPywHbRJZTU+VHFHV/BtWCK4PZafAA/TrszSDf9+CafJB9VKwVryIyRSar6c02Wwa7LblTi31v1US7Eau5WYMtHSDfTuZ1Sz0o9pvZpmX8uP5XRwfIV2TWVAbjoqnaX2MTJvZ/hT1tYsb1Vvdj4k3qproOKteHWbT9HjNZigHbxH7rbvRSvWV2zOs8WPlKx5JFfBmZKlrWZAY1W5HZv7gDjN8r85jwo7YnbsTPuY+UO2GrKJnBmK1NbL7dbzIeb8yI9k2yfvNL366vvjbBtgY/3okMruz58r7h/aCSOk3sFKWPLNim6IGconszYuPx7nWkZ7PT0VPUGzHFJw4rd4B6ZafohhjxUICXUdLfJmwh4glQEwHaSwRxZj42NRkU/XtRtjbxSaxBvX01IyrmUymzvvrbhM9WZD6JO4nrCa2YcwcoU9G7F+XrgxVRZHST2YPTgjtA++Pp71DGNlG9uH++CjozYk6MeChAdS0yPW1ixlRkkjOxR9H2bCEZcWfwus/saxMpT3xCLKkM5naKLrjX4GVxzSB9ZMHVJqgVJrW9TGc/+r61iZ69qJzxxCfElzuDJsDSHn0z3z5Jbb/W25y+AB1xFZml+2BbqtRm8I04+paxr+8W2+yUdBXVXFU0mbv74O27Jtw5D06ljr+D6H7L/9Em2IoM0cZvKz05ugZI5R03XRner7/IFClTkQkXRJG53mGVkSuD/mre9j7AJaupE3XGDIwIDEp7jxweXa1aNfvHnxvSRUYPk0F75OTecqhp+fiD25w+suBqE8nIXWTisQ2wnLp+yXWj83iAZJGJ01sVfa0+P6v1/jMT62r9UIAz91YtlvbEIsxdu3E9fXwJ9rWJf3YySVW/r+pDdFmI4+WRgyiqjcffNsA2d1UDHT2xTjJ6iv63yERTIfKXoG3Gj3wFp+4p6nv2xCJ03pCq6IkMJj1tYvfr5SXXCINttQ3uj9C0cXcGT/aD3n1Dqo9P7DbuaRO3CNN6tcvvjjA8KueWqjjZNpDlhePvz1XR2vVvb35rE0keCVHP08uhvru6HnJiT3WtW1/OEaMnAhRVNCVEv1+fJTuT0V310XTH8xPPBAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAADo8RfREGoQy0Mv3QAAAABJRU5ErkJggg==" alt="LogoKlein">
            <a href="../index.html">
                <button class="loguit-knop">Log Uit</button>
            </a>
        </div>
    </header>
    <main class="main-content-area">
        <section class="menu-section">
            <div class="sidebar-menu">
                <h3 class="menu-kop">Website Beheer</h3>
                <a class="menu-link" href="../dashboard.html">Dashboard</a>
                <a class="menu-link" href="home.php">Homepage</a>
                <a class="menu-link" href="news.php">News</a>
                <a class="menu-link" href="schedule.php">Schedule</a>
                <a class="menu-link" href="teams.php">Teams</a>
                <a class="menu-link" href="drivers.php">Drivers</a>
                <a class="menu-link active" href="standings.php">Standings</a>
            </div>
        </section>
        <section class="main-content-panel">
           <div>
                <div>
                    <a href="add/add-result.php"><button class="achterkantbutton">Add Result</button></a>
                    <a href="../dashboard.html"><button class="achterkantbutton">Dashboard</button></a>
                </div>
            </div>

            <h3 class="text-xl font-bold mt-6 mb-4">Coureur Standen</h3>
            <div class="standings-table-container mt-4">
                <?php if (!empty($driver_standings)): ?>
                    <table class="min-w-full bg-white border border-gray-300 shadow-md rounded-lg overflow-hidden">
                        <thead class="bg-gray-200">
                            <tr>
                                <th class="py-3 px-4 border-b text-left text-sm font-semibold text-gray-700">Positie</th>
                                <th class="py-3 px-4 border-b text-left text-sm font-semibold text-gray-700">Coureur</th>
                                <th class="py-3 px-4 border-b text-left text-sm font-semibold text-gray-700">Team</th>
                                <th class="py-3 px-4 border-b text-left text-sm font-semibold text-gray-700">Punten</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $position = 1; ?>
                            <?php foreach ($driver_standings as $driver): ?>
                                <tr class="<?php echo ($position % 2 == 0) ? 'bg-gray-50' : 'bg-white'; ?>">
                                    <td class="py-2 px-4 border-b text-sm text-gray-800"><?php echo $position++; ?></td>
                                    <td class="py-2 px-4 border-b text-sm text-gray-800"><?php echo htmlspecialchars($driver['first_name'] . ' ' . $driver['last_name']); ?></td>
                                    <td class="py-2 px-4 border-b text-sm text-gray-800"><?php echo htmlspecialchars($driver['team_name']); ?></td>
                                    <td class="py-2 px-4 border-b text-sm text-gray-800 font-bold"><?php echo htmlspecialchars($driver['total_points']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="text-gray-600">Geen coureurstanden gevonden. Controleer de database en of er gegevens zijn ingevoerd.</p>
                <?php endif; ?>
            </div>

            <h3 class="text-xl font-bold mt-8 mb-4">Team Standen</h3>
            <div class="standings-table-container mt-4">
                <?php if (!empty($team_standings)): ?>
                    <table class="min-w-full bg-white border border-gray-300 shadow-md rounded-lg overflow-hidden">
                        <thead class="bg-gray-200">
                            <tr>
                                <th class="py-3 px-4 border-b text-left text-sm font-semibold text-gray-700">Positie</th>
                                <th class="py-3 px-4 border-b text-left text-sm font-semibold text-gray-700">Team</th>
                                <th class="py-3 px-4 border-b text-left text-sm font-semibold text-gray-700">Punten</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $position_team = 1; ?>
                            <?php foreach ($team_standings as $team): ?>
                                <tr class="<?php echo ($position_team % 2 == 0) ? 'bg-gray-50' : 'bg-white'; ?>">
                                    <td class="py-2 px-4 border-b text-sm text-gray-800"><?php echo $position_team++; ?></td>
                                    <td class="py-2 px-4 border-b text-sm text-gray-800"><?php echo htmlspecialchars($team['team_name']); ?></td>
                                    <td class="py-2 px-4 border-b text-sm text-gray-800 font-bold"><?php echo htmlspecialchars($team['total_points']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="text-gray-600">Geen teamstanden gevonden. Controleer de database en of er gegevens zijn ingevoerd.</p>
                <?php endif; ?>
            </div>
        </section>
    </main>
</body>
</html>
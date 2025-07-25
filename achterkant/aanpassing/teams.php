<!DOCTYPE html>
<html lang="nl">
<head>
    <?php
// Database configuratie
$host = "localhost";
$db = "formule1"; // Zorg dat dit overeenkomt met je database naam
$user = "root"; // Zorg dat dit overeenkomt met je database gebruikersnaam
$pass = "root"; // Zorg dat dit overeenkomt met je database wachtwoord
$charset = "utf8mb4";

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

$pdo = null; // Initialiseer $pdo buiten de try-block

try {
    // Maak verbinding met de database via PDO
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // Afhandeling van verbindingsfouten
    die("Verbindingsfout: " . $e->getMessage());
}

// Haal alle coureurs op voor de dropdown lijst
$drivers = [];
try {
    $stmt = $pdo->query("SELECT team_id, full_team_name, base_location, team_principal FROM teams ORDER BY full_team_name ASC");
    $drivers = $stmt->fetchAll();
} catch (\PDOException $e) {
    echo "Fout bij het ophalen van coureurs: " . $e->getMessage();
}

// Verwerk het formulier als het is ingediend
$selectedDriver = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['team_id'])) {
    $driverId = $_POST['team_id'];

    try {
        // Haal de details van de geselecteerde coureur op
        $stmt = $pdo->prepare("SELECT full_team_name, base_location, team_principal FROM teams WHERE team_id = :team_id");
        $stmt->bindParam(':team_id', $driverId, PDO::PARAM_INT);
        $stmt->execute();
        $selectedDriver = $stmt->fetch();

        if (!$selectedDriver) {
            echo "Coureur niet gevonden.";
        }
    } catch (\PDOException $e) {
        echo "Fout bij het ophalen van coureurdetails: " . $e->getMessage();
    }
}
?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Webbair Framework</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="" type="image/x-icon">
</head>
<body>
    <header class="main-header">
        <div class="header-title">
            <h1>Formula 1 site - Teams</h1>
        </div>
        <div class="header-info">
            <p class="header-sitename">Formula 1</p>
            <img class="header-logo" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAOEAAADhCAMAAAAJbSJIAAAAkFBMVEX////uKibuIx7uJyPxYV/tGxb2k5HwOTTuIBv+9fXzdHLuIx/wW1n0e3j//Pz2kY/95+f2l5X3npzzbWvtFhD+8PD2mZf5srHzaWb4pqXwPzv82djyWVb96en94+L6xcTxUE71iYf7zs3vMS35u7n0g4HwR0T4rKv71tX0fnztDQPvOzf6ysn5uLf3oqHwRUIQ2pq/AAAEuklEQVR4nO3Z25qqOBAG0ECIBlBAAUEFPLSHbvv0/m83ic7Mnn2RYtRq9s2/bjtfY1FJVUiEAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD4g5Kw2yzTaV4HP23yFgrR1RNKc+CNLlxvZvlKFoXS2v9pars2AQYqdtNyzhpfd85Pnval9IagTibAsPGJIbKYJ3zhtVVQFHqg6Ay/ac07bRQVoPfFF195nvjU22SnGpPBNiAzGPNlMDyc/Hiw4Cx/azMYaGKIlHwZ7KJ42Pg8Hax7A/T4isxmqwdbfTe66ewUJQMsvrimaLLUgy5Aww+ya5sghkhvwRSfEEtv4AR6amIyWE7IIqPYikxyVEMH6E9Kk8EJnUG2NRimgweoapPBjG4Tki2D4TQeOkAdlDZAssj4bG2inBaDRfY3nff3Qb4iU0YDd0EzRW0VLekpyrcXbSeDZ9DPywHbRJZTU+VHFHV/BtWCK4PZafAA/TrszSDf9+CafJB9VKwVryIyRSar6c02Wwa7LblTi31v1US7Eau5WYMtHSDfTuZ1Sz0o9pvZpmX8uP5XRwfIV2TWVAbjoqnaX2MTJvZ/hT1tYsb1Vvdj4k3qproOKteHWbT9HjNZigHbxH7rbvRSvWV2zOs8WPlKx5JFfBmZKlrWZAY1W5HZv7gDjN8r85jwo7YnbsTPuY+UO2GrKJnBmK1NbL7dbzIeb8yI9k2yfvNL366vvjbBtgY/3okMruz58r7h/aCSOk3sFKWPLNim6IGconszYuPx7nWkZ7PT0VPUGzHFJw4rd4B6ZafohhjxUICXUdLfJmwh4glQEwHaSwRxZj42NRkU/XtRtjbxSaxBvX01IyrmUymzvvrbhM9WZD6JO4nrCa2YcwcoU9G7F+XrgxVRZHST2YPTgjtA++Pp71DGNlG9uH++CjozYk6MeChAdS0yPW1ixlRkkjOxR9H2bCEZcWfwus/saxMpT3xCLKkM5naKLrjX4GVxzSB9ZMHVJqgVJrW9TGc/+r61iZ69qJzxxCfElzuDJsDSHn0z3z5Jbb/W25y+AB1xFZml+2BbqtRm8I04+paxr+8W2+yUdBXVXFU0mbv74O27Jtw5D06ljr+D6H7L/9Em2IoM0cZvKz05ugZI5R03XRner7/IFClTkQkXRJG53mGVkSuD/mre9j7AJaupE3XGDI6IDEp7jxweXa9aNfvHnxvSRUYPk0F75OTecqhp+fiD25w+suBqE8nIXWTisQ2wnLp+yXWj83iAZJGJ01sVfa0+P6v1/jMT62r9UIAz91YtlvbEIsxdu3E9fXwJ9rWJf3YySVW/r+pDdFmI4+WRgyiqjcffNsA2d1UDHT2xTjJ6iv63yERTIfKXoG3Gj3wFp+4p6nv2xCJ03pCq6IkMJj1tYvfr5SXXCINttQ3uj9C0cXcGT/aD3n1Dqo9P7DbuaRO3CNN6tcvvjjA8KueWqjjZNpDlhePvz1XR2vVvb35rE0keCVHP08uhvru6HnJiT3WtW1/OEaMnAhRVNCVEv1+fJTuT0V310XTH8xPPBAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAADo8RfREGoQy0Mv3QAAAABJRU5ErkJggg==" alt="LogoKlein">
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
                <a class="menu-link" href="standings.php">Standings</a>
            </div>
        </section>
        <section class="main-content-panel">
            <div class="headerhoofdpagina">
                <h2 class="main-title">Teams</h2>
            </div>
            <div>
                <div>
                    <a href="add/add-team.php"><button class="achterkantbutton">Add Team</button></a>
                    <a href="../dashboard.html"><button class="achterkantbutton">Dashboard</button></a>
                </div>
            </div>
            <div>
                <div>
                <table>
                    <thead>
                        <tr>
                            <th>Team name</th>
                            <th>Location</th>
                            <th>Team principal</th>
                        </tr>
                    </thead>
                        <tbody>
                            <?php if (!empty($drivers)): ?>
                                <?php foreach ($drivers as $driver): ?>
                                    <tr data-driver-id="<?php echo htmlspecialchars($driver['team_id']); ?>">
                                        <td><?php echo htmlspecialchars($driver['full_team_name']); ?></td>
                                        <td><?php echo htmlspecialchars($driver['base_location']); ?></td>
                                        <td><?php echo htmlspecialchars($driver['team_principal']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="3">Geen team gevonden in de database.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tableRows = document.querySelectorAll('tbody tr');

            tableRows.forEach(row => {
                row.addEventListener('dblclick', function() {
                    const driverId = this.dataset.driverId;
                    if (driverId) {
                        // Stuur de gebruiker door naar de detailpagina met de driver_id
                        window.location.href = 'team-details.php?id=' + driverId;
                    }
                });
            });
        });
    </script>
</body>
</html>
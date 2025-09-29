<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: ../index.html');
    exit;
}
require_once 'db_config.php';

$circuitsData = [];
try {
    // Haal ALLE circuits op voor weergave in de tabel
    // Sorteer op calendar_order ASC om de juiste kalendervolgorde te krijgen
    $stmt = $pdo->query("SELECT circuit_key, grandprix, location, race_datetime FROM circuits ORDER BY calendar_order ASC");
    $circuitsData = $stmt->fetchAll();
} catch (\PDOException $e) {
    echo "Fout bij het ophalen van circuits: " . $e->getMessage();
}

$selectedCircuit = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['circuit_key'])) {
    $circuitkey = $_POST['circuit_key'];

    try {

        $stmt = $pdo->prepare("SELECT grandprix, location, race_datetime FROM circuits WHERE circuit_key = :circuit_key");
        $stmt->bindParam(':circuit_key', $circuitkey, PDO::PARAM_STR); 
        $stmt->execute();
        $selectedCircuit = $stmt->fetch();

        if (!$selectedCircuit) {
            echo "Circuit niet gevonden.";
        }
    } catch (\PDOException $e) {
        echo "Fout bij het ophalen van circuitdetails: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Webbair Framework - F1 Circuits</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="" type="image/x-icon">
</head>
<body class="">
    <header class="main-header">
        <div class="header-title">
            <h1>Formula 1 site</h1>
        </div>
        <div class="header-info">
            <p class="header-sitename">Formula 1 - Schedule</p>
            <img class="header-logo" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAOEAAADhCAMAAAAJbSJIAAAAkFBMVEX////uKibuIx7uJyPxYV/tGxb2k5HwOTTuIBv+9fXzdHLuIx/wW1n0e3j//Pz2kY/95+f2l5X3npzzbWvtFhD+8PD2mZf5srHzaWb4pqXwPzv82djyWVb96en94+L6xcTxUE71iYf7zs3vMS35u7n0g4HwR0T4rKv71tX0fnztDQPvOzf6ysn5uLf3oqHwRUIQ2pq/AAAEuklEQVR4nO3Z25qqOBAG0ECIBlBAAUEFPLSHbvv0/m/3R+3Mnn2RYtRq9s2/bjtfY1FJVUiEAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD4g5Kw2yzTaV4HP23yFgrR1RNKc+CNLlxvZvlKFoXS2v9pars2AQYqdtNyzhpfd85Pnval9IagTibAsPGJIbKYJ3zhtVVQFHqg6Ay/ac07bRQVoPfFF195nvjU22SnGpPBNiAzGPMlMDyc/Hiw4Cx/azMYaGKIlHwZ7KJ42Pg8Hax7A/T4isxmqwdbfTe66ewUJQMsvrimaLLUgy5Aww+ya5sghkhvwRSfEEtv4AR6amIyWE7IIqPYikxyVEMH6E9Kk8EJnUG2NRimgweoapPBjG4Tki2D4TQeOkAdlDZAssj4bG2inBaDRfY3nff3Qb4iU0YDd0EzRW0VLekpyrcXbSeDZ9DPywHbRJZTU+VHFHV/BtWCK4PZafAA/TrszSDf9+CafJB9VKwVryIyRSar6c02Wwa7LblTi11v1US7Eau5WYMtHSDfTuZ1Sz0o9pvZpmX8uP5XRwfIV2TWVAbjoqnaX2MTJvZ/hT1tYsb1Vvdj4k3qproOKteHWbT9HjNZigHbxH7rbvRSvWV2zOs8WPlKx5JFfBmZKlrWZAY1W5HZv7gDjN8r85jwo7YnbsTPuY+UO2GrKJnBmK1NbL7dbzIeb8yI9k2yfvNL366vvjbBtgY/3okMruz58r7h/aCSOk3sFKWPLNim6IGconszYsPx7nWkZ7PT0VPUGzHFJw4rd4B6ZafohhjxUICXUdLfJmwh4glQEwHaSwRxZj42NRkU/XtRtjbxSaxBvX01IyrmUymzvvrbhM9WZD6JO4nrCa2YcwcoU9G7F+XrgxVRZHST2YPTgjtA++Pp71DGNlG9uH++CjozYk6MeChAdS0yPW1ixlRkkjOxR9H2bCEZcWfwus/saxMpT3xCLKkM5naKLrjX4GVxzSB9ZMHVJqgVJrW9TGc/+r61iZ69qJzxxCfElzuDJsDSHn0z3z5Jbb/W25y+AB1xFZml+2BbqtRm8I04+paxr+8W2+yUdBXVXFU0mbv74O27Jtw5D06ljr+D6H7L/9Em2IoM0cZvKz05ugZI5R03XRner7/IFClTkQkXRJG53mGVkSuD/mre9j7AJaupE3XGDI6IDEp7jxweXa16NfvHnxvSRUYPk0F75OTecqhp+fiD25w+suBqE8nIXWTisQ2wnLp+yXWj83iAZJGJ01sVfa0+P6v1/jMT62r9UIAz91YtlvbEIsxdu3E9fXwJ9rWJf3YySVW/r+pDdFmI4+WRgyiqjcffNsA2d1UDHT2xTjJ6iv63yERTIfKXoG3Gj3wFp+4p6nv2xCJ03pCq6IkMJj1tYvfr5SXXCINttQ3uj9C0cXcGT/aD3n1Dqo9P7DbuaRO3CNN6tcvvjjA8KueWqjjZNpDlhePvz1XR2vVvb35rE0keCVHP08uhvru6HnJiT3WtW1/OEaMnAhRVNCVEv1+fJTuT0V310XTH8xPPBAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAADo8RfREGoQy0Mv3QAAAABJRU5ErkJggg==" alt="LogoKlein">
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
                <h2 class="">F1 Circuits Kalender 2025</h2>
                <p class="">Bekijk alle circuits van het komende F1 seizoen.</p>
            </div>

            <div>
                <div>
                    <a href="add/add-circuit.php"><button class="achterkantbutton">Add</button></a>
                    <a href="../dashboard.html"><button class="achterkantbutton">Dashboard</button></a>
                </div>
            </div>

            <table class="circuits-table">
                <thead>
                    <tr>
                        <th>Grand Prix</th>
                        <th>Locatie</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($circuitsData)): ?>
                        <?php foreach ($circuitsData as $circuit): ?>
                            <tr data-circuit-key="<?php echo htmlspecialchars($circuit['circuit_key']); ?>">
                                <td><?php echo htmlspecialchars($circuit['grandprix']); ?></td>
                                <td><?php echo htmlspecialchars($circuit['location']); ?></td>
                                <td><?php echo htmlspecialchars($circuit['race_datetime']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="2">Geen circuits gevonden in de database.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </main>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tableRows = document.querySelectorAll('tbody tr');

            tableRows.forEach(row => {
                row.addEventListener('dblclick', function() {
                    // CORRECTIE 1: Gebruik camelCase voor dataset property
                    const circuitKey = this.dataset.circuitKey; // 'data-circuit-key' wordt 'dataset.circuitKey'

                    if (circuitKey) {
                        // CORRECTIE 2: Zorg dat de URL parameter 'key' is, zoals circuit-details.php verwacht
                        window.location.href = 'circuit-details.php?key=' + circuitKey;
                    }
                });
            });
        });
    </script>
</body>
</html>
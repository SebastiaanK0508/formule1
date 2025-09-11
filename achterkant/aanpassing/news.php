<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: ../index.html');
    exit;
}
require_once 'db_config.php';
/** @var PDO $pdo */

$search = $_GET['search'] ?? '';

$news = [];
try {
    if (!empty($search)) {
        $sql = "SELECT news_id, title, source, date FROM news WHERE title LIKE :search OR source LIKE :search ORDER BY date DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':search', '%' . $search . '%');
    } else {
        $sql = "SELECT news_id, title, source, date FROM news ORDER BY date DESC";
        $stmt = $pdo->prepare($sql);
    }
    $stmt->execute();
    $news = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (\PDOException $e) {
    error_log("Fout bij het ophalen van nieuwsartikelen op news.php: " . $e->getMessage());
    echo "<p style='color: red; text-align: center;'>Er is een probleem opgetreden bij het laden van de nieuwsartikelen. Probeer het later opnieuw.</p>";
    $news = [];
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nieuws Beheer - Formula 1</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="/path/to/your/favicon.ico" type="image/x-icon">
</head>
<body>
    <header class="main-header">
        <div class="header-title">
            <h1>Formula 1 site - Nieuws</h1>
        </div>
        <div class="header-info">
            <p class="header-sitename">Formula 1 - Nieuws</p>
            <img class="header-logo" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAOEAAADhCAMAAAAJbSJIAAAAkFBMVEX////uKibuIx7uJyPxYV/tGxb2k5HwOTTuIBv+9fXzdHLuIx/wW1n0e3j//Pz2kY/95+f2l5X3npzzbWvtFhD+8PD2mZf5srHzaWb4pqXwPzv82djyWVb96en94+L6xcTxUE71iYf7zs3vMS35u7n0g4HwR0T4rKv71tX0fnztDQPvOzf6ysn5uLf3oqHwRUIQ2pq/AAAEuklEQVR4nO3Z25qqOBAG0ECIBlBAAUEFPLSHbvv0/m83ic3Mnn2RYtRq9s2/bjtfY1FJVUiEAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD4g5Kw2yzTaV4HP23yFgrR1RNKc+CNLlxvZvlKFoXS2v9pars2AQYqdtNyzhpfd85Pnval9IagTibAsPGJIbKYJ3zhtVVQFHqg6Ay/ac07bRQVoPfFF195nvjU22SnGpPBNiAzGPNlMDyc/Hiw4Cx/azMYaGKIlHwZ7KJ42Pg8Hax7A/T4isxmqwdbfTe66ewUJQMsvrimaLLUgy5Aww+ya5sghkhvwRSfEEtv4AR6amIyWE7IIqPYikxyVEMH6E9Kk8EJnUG2NRimgweoapPBjG4Tki2D4TQeOkAdlDZAssj4bG2inBaDRfY3nff3Qb4iU0YDd0EzRW0VLekpyrcXbSeDZ9DPywHbRJZTU+VHFHV/BtWCK4PZafAA/TrszSDf9+CafJB9VKwVryIyRSar6c02Wwa7LblTi31v1US7Eau5WYMtHSDfTuZ1Sz0o9pvZpmX8uP5XRwfIV2TWVAbjoqnaX2MTJvZ/hT1tYsb1Vvdj4k3qproOKteHWbT9HjNZigHbxH7rbvRSvWV2zOs8WPlKx5JFfBmZKlrWZAY1W5HZv7gDjN8r85jwo7YnbsTPuY+UO2GrKJnBmK1NbL7dbzIeb8yI9k2yfvNL366vvjbBtgY/3okMruz58r7h/aCSOk3sFKWPLNim6IGconszYuPx7nWkZ7PT0VPUGzHFJw4rd4B6ZafohhjxUICXUdLfJmwh4glQEwHaSwRxZj42NRkU/XtRtjbxSaxBvX01IyrmUymzvvrbhM9WZD6JO4nrCa2YcwcoU9G7F+XrgxVRZHST2YPTgjtA++Pp71DGNlG9uH++CjozYk6MeChAdS0yPW1ixlRkkjOxR9H2bCEZcWfwus/saxMpT3xCLKkM5naKLrjX4GVxzSB9ZMHVJqgVJrW9TGc/+r61iZ69qJzxxCfElzuDJsDSHn0z3z5Jbb/W25y+AB1xFZml+2BbqtRm8I04+paxr+8W2+yUdBXVXFU0mbv74O27Jtw5D06ljr+D6H7L/9Em2IoM0cZvKz05ugZI5R03XRner7/IFClTkQkXRJG53mGVkSuD/mre9j7AJaupE3XGDI6IDEp7jxweXa1aNfvHnxvSRUYPk0F75OTecqhp+fiD25w+suBqE8nIXWTisQ2wnLp+yXWj83iAZJGJ01sVfa0+P6v1/jMT62r9UIAz91YtlvbEIsxdu3E9fXwJ9rWJf3YySVW/r+pDdFmI4+WRgyiqjcffNsA2d1UDHT2xTjJ6iv63yERTIfKXoG3Gj3wFp+4p6nv2xCJ03pCq6IkMJj1tYvfr5SXXCINttQ3uj9C0cXcGT/aD3n1Dqo9P7DbuaRO3CNN6tcvvjjA8KueWqjjZNpDlhePvz1XR2vVvb35rE0keCVHP08uhvru6HnJiT3WtW1/OEaMnAhRVNCVEv1+fJTuT0V310XTH8xPPBAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAADo8RfREGoQy0Mv3QAAAABJRU5ErkJggg==" alt="Logo Klein">
            <a href="../index.html">
                <button class="loguit-knop">Log Uit</button>
            </a>
        </div>
    </header>
    <main class="main-content-area">
        <section class="menu-section">
            <div class="sidebar-menu">
                <h3 class="menu-kop">Website Beheer</h3>
                <a class="menu-link" href="../dashboard.php">Dashboard</a>
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
                <h2>Nieuws sectie</h2>
            </div>
            <div class="content-panel">
                <div class="news-actions">
                    <a href="../dashboard.html" class="button achterkantbutton">Dashboard</a>
                    <a href="add/add-news.php" class="button achterkantbutton">Nieuws Toevoegen</a>
                    <form method="GET" action="news.php" style="display: inline-block; margin-left: 1rem;">
                        <input type="text" name="search" placeholder="Zoek op titel of bron..." value="<?php echo htmlspecialchars($search); ?>" style="padding: 8px; border-radius: 6px; border: 1px solid #ccc;">
                        <button type="submit" class="achterkantbutton">Zoek</button>
                        <a href="news.php" class="achterkantbutton" style="text-decoration: none;">Reset</a>
                    </form>
                </div>
                <div>
                    <?php if (!empty($news)): ?>
                        <table class="news-table">
                            <thead>
                                <tr>
                                    <th>Titel</th>
                                    <th>Bron</th>
                                    <th>Datum</th>
                                    </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($news as $item): ?>
                                    <tr data-newsid="<?php echo htmlspecialchars($item['news_id']); ?>">
                                        <td><?php echo htmlspecialchars($item['title']); ?></td>
                                        <td><?php echo htmlspecialchars($item['source']); ?></td>
                                        <td><?php echo htmlspecialchars($item['date']); ?></td>
                                        </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>Er zijn momenteel geen nieuwsartikelen beschikbaar.</p>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Event listener voor dubbelklik op tabelrijen
            const tableRows = document.querySelectorAll('tbody tr');
            tableRows.forEach(row => {
                row.addEventListener('dblclick', function() {
                    // Haal de news_id op uit het data-attribuut van de rij
                    const newsId = this.dataset.newsid;
                    if (newsId) {
                        // Stuur de gebruiker door naar de bewerkpagina
                        window.location.href = 'edit-news-detail.php?news_id=' + newsId;
                    }
                });
            });
            // Alle andere knop-specifieke JavaScript-code is hieronder verwijderd,
            // aangezien de knoppen zelf uit de HTML zijn gehaald.
        });
    </script>
</body>
</html>
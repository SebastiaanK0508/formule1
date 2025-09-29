<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: ../index.html');
    exit;
}
require_once 'db_config.php';
/** @var PDO $pdo */

$newsItem = null;
$message = '';
if (isset($_GET['news_id'])) {
    $newsId = $_GET['news_id'];

    try {
        $stmt = $pdo->prepare("SELECT news_id, title, news_content, image_url, keywords, source, date FROM news WHERE news_id = :news_id");
        $stmt->bindParam(':news_id', $newsId, PDO::PARAM_INT);
        $stmt->execute();
        $newsItem = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$newsItem) {
            $message = "<p style='color: red;'>Nieuwsartikel niet gevonden.</p>";
        }
    } catch (\PDOException $e) {
        $message = "<p style='color: red;'>Fout bij het ophalen van nieuwsartikelgegevens: " . htmlspecialchars($e->getMessage()) . "</p>";
        error_log("Error fetching news item for edit: " . $e->getMessage());
    }
} else {
    $message = "<p style='color: red;'>Geen nieuwsartikel geselecteerd voor bewerking.</p>";
}

// Stap 2: Formulier verwerken bij POST-verzoek
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['news_id_hidden'])) {
    $newsIdToUpdate = $_POST['news_id_hidden'];
    $title = trim($_POST['title']);
    $news_content = trim($_POST['news_content']);
    $image_url = trim($_POST['image_url']);
    $keywords = trim($_POST['keywords']);
    $source = trim($_POST['source']);
    $date = trim($_POST['date']); // Datum kan handmatig worden gewijzigd

    // Basic validatie
    if (empty($title) || empty($news_content) || empty($source)) {
        $message = '<p style="color: red;">Titel, inhoud en bron zijn verplicht!</p>';
    } else {
        try {
            $stmt = $pdo->prepare("
                UPDATE news
                SET
                    title = :title,
                    news_content = :news_content,
                    image_url = :image_url,
                    keywords = :keywords,
                    source = :source,
                    date = :date
                WHERE news_id = :news_id
            ");
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':news_content', $news_content);
            $stmt->bindParam(':image_url', $image_url);
            $stmt->bindParam(':keywords', $keywords);
            $stmt->bindParam(':source', $source);
            $stmt->bindParam(':date', $date);
            $stmt->bindParam(':news_id', $newsIdToUpdate, PDO::PARAM_INT);

            if ($stmt->execute()) {
                $message = '<p style="color: green;">Nieuwsartikel succesvol bijgewerkt!</p>';
                // Herlaad de nieuwsitem data om de bijgewerkte waarden te tonen na succesvolle update
                $stmt = $pdo->prepare("SELECT news_id, title, news_content, image_url, keywords, source, date FROM news WHERE news_id = :news_id");
                $stmt->bindParam(':news_id', $newsIdToUpdate, PDO::PARAM_INT);
                $stmt->execute();
                $newsItem = $stmt->fetch(PDO::FETCH_ASSOC);

            } else {
                $message = '<p style="color: red;">Fout bij het bijwerken van het nieuwsartikel.</p>';
            }
        } catch (\PDOException $e) {
            $message = '<p style="color: red;">Databasefout bij bijwerken: ' . htmlspecialchars($e->getMessage()) . '</p>';
            error_log("Error updating news item: " . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nieuwsartikel Bewerken - Formula 1</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="style.css">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f4f4f4; color: #333; }
        .container { max-width: 800px; margin: 20px auto; background-color: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        h1 { color: #0056b3; text-align: center; margin-bottom: 30px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #555; }
        input[type="text"],
        input[type="number"],
        input[type="date"],
        textarea,
        select {
            width: calc(100% - 22px);
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            background-color: #e9e9e9; /* Standaard niet bewerkbaar */
        }
        input[type="checkbox"] {
            margin-right: 10px;
        }
        input[type="text"]:focus,
        input[type="number"]:focus,
        input[type="date"]:focus,
        textarea:focus,
        select:focus {
            outline: none;
            border-color: #007bff;
        }

        /* Styles voor bewerkbare velden */
        input[type="text"]:not([readonly]),
        input[type="number"]:not([readonly]),
        input[type="date"]:not([readonly]),
        textarea:not([readonly]),
        select:not([disabled]) {
            background-color: white;
        }

        .button-group { margin-top: 20px; text-align: center; }
        .button-group button, .button-group a {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
            display: inline-block;
            margin: 0 5px;
        }
        #editButton { background-color: #007bff; color: white; }
        #editButton:hover { background-color: #0056b3; }
        #saveButton { background-color: #28a745; color: white; display: none; }
        #saveButton:hover { background-color: #218838; }
        .back-link { background-color: #6c757d; color: white; }
        .back-link:hover { background-color: #5a6268; }

        .driver-image { max-width: 200px; height: auto; display: block; margin: 0 auto 20px auto; border-radius: 8px; }
        .message { padding: 10px; margin-bottom: 20px; border-radius: 4px; }
        .success-message { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error-message { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>
    <header class="main-header">
        <div class="header-title">
            <h1>Formula 1 site - Nieuws Bewerken</h1>
        </div>
        </header>

    <main class="main-content-area">
        <section class="menu-section">
            </section>
        <section class="main-content-panel">
            <div class="headerhoofdpagina">
                <h2>Nieuwsartikel Bewerken</h2>
            </div>
            <div class="content-panel">
                <?php echo $message; ?>

                <?php if ($newsItem): ?>
                    <form action="edit-news-detail.php" method="POST" class="add-edit-form">
                        <input type="hidden" name="news_id_hidden" value="<?php echo htmlspecialchars($newsItem['news_id']); ?>">

                        <div class="form-group">
                            <label for="title">Titel:</label>
                            <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($newsItem['title']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="news_content">Inhoud:</label>
                            <textarea id="news_content" name="news_content" rows="15" required><?php echo htmlspecialchars($newsItem['news_content']); ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="image_url">Afbeelding URL:</label>
                            <input type="url" id="image_url" name="image_url" value="<?php echo htmlspecialchars($newsItem['image_url']); ?>">
                            <?php if (!empty($newsItem['image_url'])): ?>
                                <img src="<?php echo htmlspecialchars($newsItem['image_url']); ?>" alt="Huidige afbeelding" style="max-width: 200px; margin-top: 10px; display: block;">
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="keywords">Trefwoorden (komma-gescheiden):</label>
                            <input type="text" id="keywords" name="keywords" value="<?php echo htmlspecialchars($newsItem['keywords']); ?>">
                        </div>

                        <div class="form-group">
                            <label for="source">Bron:</label>
                            <input type="text" id="source" name="source" value="<?php echo htmlspecialchars($newsItem['source']); ?>">
                        </div>

                        <div class="form-group">
                            <label for="date">Datum:</label>
                            <input type="date" id="date" name="date" value="<?php echo htmlspecialchars(date('Y-m-d', strtotime($newsItem['date']))); ?>" required>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="achterkantbutton">Opslaan</button>
                            <button type="button" id="deleteButton" class="achterkantbutton">Verwijderen</button>
                            <button type="button" id="annulerenButton" class="achterkantbutton">Annuleren</button>
                            <button type="button" id="terugButton" class="achterkantbutton">Terug</button>

                        </div>
                    </form>
                <?php else: ?>
                    <p>Selecteer een nieuwsartikel om te bewerken via de nieuwslijst.</p>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Event listener voor de "Annuleren" knop
            const annulerenButton = document.getElementById('annulerenButton');
            if (annulerenButton) {
                annulerenButton.addEventListener('click', function() {
                    window.location.href = 'news.php'; // Navigeer naar news.php
                });
            }

            // Event listener voor de "Terug" knop
            const terugButton = document.getElementById('terugButton');
            if (terugButton) {
                terugButton.addEventListener('click', function() {
                    window.location.href = 'news.php'; // Navigeer naar news.php
                });
            }

            // Event listener voor de "Verwijderen" knop met waarschuwing
            const deleteButton = document.getElementById('deleteButton');
            if (deleteButton) {
                deleteButton.addEventListener('click', function() {
                    // Haal de news_id op uit het verborgen veld
                    const newsId = document.querySelector('input[name="news_id_hidden"]').value;

                    if (newsId && confirm('Weet je zeker dat je dit nieuwsartikel definitief wilt verwijderen? Deze actie kan NIET ongedaan gemaakt worden.')) {
                        // Als de gebruiker op OK klikt, navigeer dan naar de delete-pagina met de news_id
                        window.location.href = 'delete-news.php?news_id=' + newsId;
                    }
                    // Als de gebruiker op Annuleren klikt, gebeurt er niets
                });
            }
        });
    </script>
</body>
</html>
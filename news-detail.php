<?php

require_once 'db_config.php'; 
/** @var PDO $pdo */
$news_id = isset($_GET['news_id']) ? (int)$_GET['news_id'] : 0;
$current_news = null;
if ($news_id > 0) {
    $stmt = $pdo->prepare("SELECT news_id, title, date, image_url, news_content, source, keywords FROM news WHERE news_id = :news_id");
    $stmt->bindParam(':news_id', $news_id, PDO::PARAM_INT);
    $stmt->execute();
    $current_news = $stmt->fetch();
}

?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $current_news ? htmlspecialchars($current_news['title']) : 'Nieuws Artikel Niet Gevonden'; ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="article-body">
    <div class="article-container">
        <?php if ($current_news): ?>
            <img src="<?php echo htmlspecialchars($current_news['image_url']); ?>" alt="<?php echo htmlspecialchars($current_news['title']); ?>" class="article-image">
            <h1 class="article-title"><?php echo htmlspecialchars($current_news['title']); ?></h1>
            <span class="article-date"><?php echo htmlspecialchars($current_news['date']); ?></span>
            <div class="article-content">
                <p><?php echo nl2br(htmlspecialchars($current_news['news_content'])); ?></p>
                <div class="keyandbron">
                    <p><?php echo (htmlspecialchars($current_news['keywords'])); ?></p>
                    <p>Bron: <?php echo (htmlspecialchars($current_news['source'])); ?></p>
                </div>
            </div>
            <a href="index.php" class="back-button">Terug naar Nieuws Overzicht</a>
        <?php else: ?>
            <h1>Nieuws Artikel Niet Gevonden</h1>
            <p>Het opgevraagde nieuwsartikel kon niet worden gevonden.</p>
            <a href="index.php" class="back-button">Terug naar Nieuws Overzicht</a>
        <?php endif; ?>
    </div>
</body>
</html>
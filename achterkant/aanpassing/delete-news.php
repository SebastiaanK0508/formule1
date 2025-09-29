<?php

require_once 'db_config.php';
/** @var PDO $pdo */

$message = '';

if (isset($_GET['news_id'])) {
    $newsIdToDelete = $_GET['news_id'];

    try {
        $stmtCheck = $pdo->prepare("SELECT news_id FROM news WHERE news_id = :news_id");
        $stmtCheck->bindParam(':news_id', $newsIdToDelete, PDO::PARAM_INT);
        $stmtCheck->execute();

        if ($stmtCheck->rowCount() > 0) {
            // Artikel bestaat, ga verder met verwijderen
            $stmtDelete = $pdo->prepare("DELETE FROM news WHERE news_id = :news_id");
            $stmtDelete->bindParam(':news_id', $newsIdToDelete, PDO::PARAM_INT);

            if ($stmtDelete->execute()) {
                $message = "Nieuwsartikel succesvol verwijderd!";
            } else {
                $message = "Fout bij het verwijderen van het nieuwsartikel.";
            }
        } else {
            $message = "Nieuwsartikel niet gevonden.";
        }
    } catch (\PDOException $e) {
        $message = "Databasefout bij verwijderen: " . htmlspecialchars($e->getMessage());
        error_log("Error deleting news item: " . $e->getMessage());
    }
} else {
    $message = "Geen nieuwsartikel geselecteerd om te verwijderen.";
}

// Redirect altijd terug naar de nieuwslijstpagina na poging tot verwijderen
// Voeg een statusparameter toe om de boodschap daar te tonen
header("Location: news.php?status=" . urlencode($message));
exit();
?>
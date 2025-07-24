<?php
$host = "localhost";
$db = "formule1";
$user = "root";
$pass = "root";
$charset = "utf8mb4";
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,   
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,         
    PDO::ATTR_EMULATE_PREPARES   => false,                   
];

$pdo = null; 
$message = ''; 

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("Verbindingsfout met de database: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $title = $_POST['title'] ?? '';
        $newscontent = $_POST['news_content'] ?? ''; 
        $imageUrl = $_POST['image_url'] ?? null;
        $source = $_POST['source'] ?? null;
        $keywords = $_POST['keywords'] ?? null;
        $dateFromForm = $_POST['date'] ?? ''; 

        if (empty($title) || empty($newscontent)) {
            $message = "<p class='error-message'>Vul alle verplichte velden in (Titel, Nieuwscontent).</p>";
        } else {
            $columns = ['title', 'text', 'image_url', 'source', 'keywords'];
            $placeholders = [':title', ':text', ':image_url', ':source', ':keywords'];

            if (!empty($dateFromForm)) {
                $columns[] = 'date';
                $placeholders[] = ':date';
            }

            $sql = "INSERT INTO news (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
            
            $stmt = $pdo->prepare($sql);

            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':text', $newscontent); 
            $stmt->bindParam(':image_url', $imageUrl);
            $stmt->bindParam(':source', $source);
            $stmt->bindParam(':keywords', $keywords);

            if (!empty($dateFromForm)) {
                $stmt->bindParam(':date', $dateFromForm);
            }

            if ($stmt->execute()) {
                $message = "<p class='success-message'>Nieuwsbericht succesvol toegevoegd!</p>";
                $_POST = array(); 
            } else {
                $message = "<p class='error-message'>Fout bij het toevoegen van het nieuwsbericht.</p>";
            }
        }
    } catch (\PDOException $e) {
        if ($e->getCode() == 23000) { 
            $message = "<p class='error-message'>Fout: Een nieuwsbericht met deze titel of een andere unieke combinatie bestaat al.</p>";
        } else {
            $message = "<p class='error-message'>Databasefout bij toevoegen: " . $e->getMessage() . "</p>";
        }
    }
}
?>

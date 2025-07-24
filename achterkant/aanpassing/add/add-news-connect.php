<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
echo "je script is gestart<br>"; // Voeg <br> toe voor betere leesbaarheid

$host = "localhost";
$db = "formule1";
$user = "root";
$pass = "root";
$charset = "utf8mb4";

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,   // Zorgt ervoor dat PDO Exceptions gooit bij fouten
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,         // Haalt rijen op als associatieve arrays
    PDO::ATTR_EMULATE_PREPARES   => false,                    // Zorgt voor echte voorbereide statements (veiliger, sneller)
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    // echo "Succesvol verbonden met de database.<br>"; // Optioneel: bevestig verbinding
} catch (\PDOException $e) {
    // Dit vangt verbindingsfouten op
    die("Verbindingsfout met de database: " . $e->getMessage());
}

$title = $_POST['title'] ?? '';
$newscontent = $_POST['news_content'] ?? ''; 
$imageUrl = $_POST['image_url'] ?? null;
$source = $_POST['source'] ?? null;
$keywords = $_POST['keywords'] ?? null;
$dateFromForm = $_POST['date'] ?? ''; 

// ----- START VAN DE OPLOSSING -----

$columns = "title, news_content, image_url, source, keywords";
$placeholders = ":title, :news_content, :image_url, :source, :keywords";
$params = [
    ':title' => $title,
    ':news_content' => $newscontent, // LET OP: Je gebruikte :text, moet :news_content zijn om overeen te komen met de kolomnaam
    ':image_url' => $imageUrl,
    ':source' => $source,
    ':keywords' => $keywords
];

if (!empty($dateFromForm)) {
    $columns .= ", date"; // Voeg de kolom 'date' toe aan de lijst
    $placeholders .= ", :date"; // Voeg de placeholder ':date' toe aan de lijst
    $params[':date'] = $dateFromForm; // Voeg de datum toe aan de parameters
}

$sql = "INSERT INTO news ($columns) VALUES ($placeholders)";

// ----- EINDE VAN DE OPLOSSING -----

try {
    $stmt = $pdo->prepare($sql);

    // Bind alle parameters in één keer, nu in een loop
    foreach ($params as $paramName => $paramValue) {
        $stmt->bindValue($paramName, $paramValue);
    }

    // VOEG DEZE REGEL TOE: De query moet worden uitgevoerd!
    $stmt->execute();

    header("Location: ../news.php?message=Nieuwsbericht succesvol toegevoegd!");
    echo "Nieuwsbericht succesvol toegevoegd!<br>";

} catch (\PDOException $e) {
    // Dit vangt fouten op die optreden tijdens prepare() of execute()
    if ($e->getCode() == 23000) { // Specifieke foutcode voor duplicate entry
        $message = "<p class='error-message'>Fout: Een nieuwsbericht met deze titel of een andere unieke combinatie bestaat al.</p>";
        echo $message;
    } else {
        $message = "<p class='error-message'>Databasefout bij toevoegen: " . $e->getMessage() . "</p>";
        echo $message; // Toon de algemene databasefout
    }
}

// Optioneel: Voeg hier code toe om de pagina af te sluiten of door te sturen
// echo "Einde van het script."; // Je kunt hier ook testen of het script de einde bereikt
?>
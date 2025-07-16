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

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("Verbindingsfout: " . $e->getMessage());
}

$firstName = $_POST['first_name'] ?? '';
$lastName = $_POST['last_name'] ?? '';
$nationality = $_POST['nationality'] ?? '';
$dateOfBirth = $_POST['date_of_birth'] ?? null;
$driverNumber = $_POST['driver_number'] ?? null;
$teamName = $_POST['team_name'] ?? '';
$championshipsWon = $_POST['championships_won'] ?? 0;
$careerPoints = $_POST['career_points'] ?? 0.00;
$imageUrl = $_POST['image'] ?? null;
$isActive = isset($_POST['is_active']) ? 1 : 0;

$sql = "INSERT INTO drivers (
            first_name,
            last_name,
            nationality,
            date_of_birth,
            driver_number,
            team_name,
            championships_won,
            career_points,
            image,
            is_active
        ) VALUES (
            :first_name,
            :last_name,
            :nationality,
            :date_of_birth,
            :driver_number,
            :team_name,
            :championships_won,
            :career_points,
            :image,
            :is_active
        )";

try {
    $stmt = $pdo->prepare($sql);

    $stmt->bindParam(':first_name', $firstName);
    $stmt->bindParam(':last_name', $lastName);
    $stmt->bindParam(':nationality', $nationality);
    $stmt->bindParam(':date_of_birth', $dateOfBirth);
    $stmt->bindParam(':driver_number', $driverNumber, PDO::PARAM_INT);
    $stmt->bindParam(':team_name', $teamName);
    $stmt->bindParam(':championships_won', $championshipsWon, PDO::PARAM_INT);
    $stmt->bindParam(':career_points', $careerPoints);
    $stmt->bindParam(':image', $imageUrl);
    $stmt->bindParam(':is_active', $isActive, PDO::PARAM_INT);

    $stmt->execute();

    echo "Nieuwe coureur succesvol toegevoegd!";
    echo '<br><a href="add-drivers.php"><button>nog 1 toevoegen</button></a>';
    echo '<br><a href="../drivers.php"><button>Terug naar overzicht</button></a>';

} catch (\PDOException $e) {
    echo "Fout: " . $e->getMessage();
}

?>
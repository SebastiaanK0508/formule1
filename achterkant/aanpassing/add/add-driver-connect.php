<?php
require_once 'db_config.php';
/** @var PDO $pdo */ 
$firstName = $_POST['first_name'] ?? '';
$lastName = $_POST['last_name'] ?? '';
$nationality = $_POST['nationality'] ?? '';
$dateOfBirth = $_POST['date_of_birth'] ?? null;
$driverNumber = $_POST['driver_number'] ?? null;
$teamName = $_POST['team_name'] ?? '';
$championshipsWon = $_POST['championships_won'] ?? 0;
$careerPoints = $_POST['career_points'] ?? 0.00;
$imageUrl = $_POST['image'] ?? null;
$placeOfBirth = $_POST['place_of_birth'] ?? null;
$description = $_POST['description'] ?? null;
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
            place_of_birth,
            description,
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
            :place_of_birth,
            :description,
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
    $stmt->bindparam(':place_of_birth',$dateOfBirth);
    $stmt->bindparam(':description',$description);
    $stmt->bindParam(':is_active', $isActive, PDO::PARAM_INT);

    $stmt->execute();

    echo "Nieuwe coureur succesvol toegevoegd!";
    echo '<br><a href="add-drivers.php"><button>nog 1 toevoegen</button></a>';
    echo '<br><a href="../drivers.php"><button>Terug naar overzicht</button></a>';

} catch (\PDOException $e) {
    echo "Fout: " . $e->getMessage();
}

?>
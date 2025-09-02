<?php
session_start();
$servername = "localhost";
$username = "webuser";
$password = "binck@guus2025"; 
$dbname = "formule1";
try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Verbinding mislukt: " . $e->getMessage());
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['username']) && isset($_POST['password_hash'])) {
        $user = $_POST['username'];
        $pass = $_POST['password_hash'];
        $stmt = $conn->prepare("SELECT id, password_hash FROM users WHERE username = :username");
        $stmt->bindParam(':username', $user);
        $stmt->execute();
        $user_data = $stmt->fetch(); 
        if ($user_data) { 
            $id = $user_data['id'];
            $hashed_password = $user_data['password_hash'];
            if (password_verify($pass, $hashed_password)) {
                $_SESSION['loggedin'] = true;
                $_SESSION['id'] = $id;
                $_SESSION['username'] = $user;
                header("Location: dashboard.php");
                exit;
            } else {
                header("Location: index.html?error=invalid_credentials");
            }
        } else {
            header("Location: index.html?error=invalid_credentials");
            exit;
        }
    } else {
        header("Location: index.html?error=missing_data");
        exit;
    }
} else {
    echo "Geen gegevens verzonden.";
}
?>

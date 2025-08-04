<?php
require_once 'db_config.php';
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
                header("Location: dashboard.html");
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

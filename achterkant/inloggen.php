<?php
session_start();

$servername = "localhost"; 
$username = "root"; 
$password = ""; 
$dbname = "legpuzzelvandaag"; 

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Verbinding mislukt: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['email']) && isset($_POST['password_hash'])) {
        $user = $_POST['email'];
        $pass = $_POST['password_hash'];

        $stmt = $conn->prepare("SELECT id, password_hash FROM users WHERE email = ?");
        $stmt->bind_param("s", $user);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id, $hashed_password);
            $stmt->fetch();

            if (password_verify($pass, $hashed_password)) {
                $_SESSION['loggedin'] = true;
                $_SESSION['id'] = $id;
                $_SESSION['email'] = $user;
                header("Location: dashboard.html");
                exit;
            } else {
                header("Location: index.html");
            }
        } else {
            header("Location: index.html");
        }

        $stmt->close();
    } else {
        header("Location: index.html");
    }
} else {
    echo "Geen gegevens verzonden.";
}

$conn->close();
?>
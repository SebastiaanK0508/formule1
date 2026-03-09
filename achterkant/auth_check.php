<?php
// 1. Forceer alle fouten naar het scherm
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

echo "DEBUG: Script gestart...<br>";

if (!file_exists('db_config.php')) {
    die("FOUT: db_config.php niet gevonden op de server!");
}

require_once 'db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "DEBUG: POST ontvangen...<br>";
    
    $user = $_POST['username'] ?? '';
    $pass = $_POST['password'] ?? '';

    if (empty($user) || empty($pass)) {
        die("DEBUG: Gebruikersnaam of wachtwoord is leeg in de POST.");
    }

    try {
        if (!isset($pdo)) {
            die("FOUT: De variabele \$pdo is niet gedefinieerd. Check db_config.php");
        }

        $stmt = $pdo->prepare("SELECT id, username, password_hash FROM admin_users WHERE username = :username LIMIT 1");
        $stmt->execute(['username' => $user]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin) {
            echo "DEBUG: Gebruiker gevonden in DB...<br>";
            if (password_verify($pass, $admin['password_hash'])) {
                echo "DEBUG: Wachtwoord match! Redirecten naar dashboard...<br>";
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['logged_in'] = true;
                echo "<script>window.location.href='dashboard.php';</script>";
                exit;
            } else {
                die("DEBUG: Wachtwoord onjuist voor deze gebruiker.");
            }
        } else {
            die("DEBUG: Gebruiker '$user' niet gevonden in de tabel admin_users.");
        }
    } catch (Exception $e) {
        die("KRITIEKE FOUT: " . $e->getMessage());
    }
} else {
    echo "DEBUG: Geen POST data gevonden. Kom je wel van het formulier af?";
}
?>
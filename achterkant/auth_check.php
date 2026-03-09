<?php
session_start();
require_once 'db_config.php'; 
/** @var PDO $pdo */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['username'] ?? '';
    $pass = $_POST['password'] ?? '';

    if (!empty($user) && !empty($pass)) {
    echo "Stap 1: Input ontvangen<br>";
    try {
        $stmt = $pdo->prepare("SELECT id, username, password_hash FROM admin_users WHERE username = :username LIMIT 1");
        $stmt->execute(['username' => $user]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "Stap 2: Database query uitgevoerd<br>";

        if ($admin) {
            echo "Stap 3: Gebruiker gevonden. Hash checken...<br>";
            if (password_verify($pass, $admin['password_hash'])) {
                echo "Stap 4: Wachtwoord correct!";
                // $_SESSION logs...
                // header...
            } else {
                echo "Stap 4: Wachtwoord incorrect.";
            }
        } else {
            echo "Stap 3: Gebruiker NIET gevonden.";
        }
    } catch (PDOException $e) {
        echo "Fout: " . $e->getMessage();
    }
    die("<br>Einde debug.");
}
}
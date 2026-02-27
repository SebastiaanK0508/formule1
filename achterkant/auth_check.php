<?php
session_start();
require_once 'db_config.php'; // Zorg dat hier je PDO verbinding in staat

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['username'] ?? '';
    $pass = $_POST['password'] ?? '';

    if (!empty($user) && !empty($pass)) {
        try {
            $stmt = $pdo->prepare("SELECT id, username, password_hash FROM admin_users WHERE username = :username LIMIT 1");
            $stmt->execute(['username' => $user]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($admin && password_verify($pass, $admin['password_hash'])) {
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_name'] = $admin['username'];
                $_SESSION['logged_in'] = true;

                header("Location: dashboard.php");
                exit;
            } else {
                header("Location: index.php?error=invalid_credentials");
                exit;
            }
        } catch (PDOException $e) {
            error_log($e->getMessage());
            die("Systeemfout in de pitstraat.");
        }
    }
}
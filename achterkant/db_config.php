<?php
$host = "localhost";
$db = "formule1";
$user = "webuser";
$pass = "binck@guus2025"; 
$charset = "utf8mb4";
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$pdoOptions = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];
$pdo = null; 
try {
    $pdo = new PDO($dsn, $user, $pass, $pdoOptions);
} catch (\PDOException $e) {
    error_log("Database connection error: " . $e->getMessage()); 
    die("There was a problem connecting to the database. Please try again later."); 
}
?>
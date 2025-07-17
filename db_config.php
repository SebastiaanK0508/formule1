<?php
// db_connect.php

// Database configuratie
$host = "localhost";
$db = "formule1";     // Make sure this matches your database name
$user = "root";        // Make sure this matches your database username
$pass = "root";        // Make sure this matches your database password (often empty for XAMPP root user)
$charset = "utf8mb4";

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$pdoOptions = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

// Initialize $pdo as null before the try-catch block
$pdo = null; 

try {
    // Attempt to create a new PDO instance (this is where the connection happens)
    $pdo = new PDO($dsn, $user, $pass, $pdoOptions);
} catch (\PDOException $e) {
    // If connection fails, log the error and stop the script
    error_log("Database connection error: " . $e->getMessage()); // Logs the error for debugging
    die("There was a problem connecting to the database. Please try again later."); // User-friendly message
}
?>
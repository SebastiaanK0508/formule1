<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Niet geautoriseerd']);
    exit;
}
require_once '../db_config.php';
/** @var PDO $pdo */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = intval($_POST['id']);

    try {
        $stmt = $pdo->prepare("DELETE FROM f1_nieuws WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Kon het bericht niet verwijderen uit de database.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database fout: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Geen geldig ID ontvangen.']);
}
<?php
session_start();
require_once '../db_config.php';
/** @var PDO $pdo */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM drivers WHERE driver_id = :id");
        $stmt->execute([':id' => $_POST['id']]);
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}
<?php
require_once 'db_config.php';
/** @var PDO $pdo */
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: contact.html?status=error');
        exit;
    }
    $contact_name = $_POST['contact_name'] ?? null;
    $contact_email = $_POST['contact_email'] ?? null;
    $contact_subject = $_POST['contact_subject'] ?? null;
    $contact_message = $_POST['contact_message'] ?? null;
    $sql = "INSERT INTO contact (
                contact_name, contact_email, contact_subject, contact_message
            ) VALUES (
                :contact_name, :contact_email, :contact_subject, :contact_message
            )";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':contact_name'    => $contact_name,
        ':contact_email'   => $contact_email,
        ':contact_subject' => $contact_subject,
        ':contact_message' => $contact_message
    ]);
    header('Location: contact.html?status=success');
    exit;
} catch (Exception $e) {
    error_log("F1SITE Error: " . $e->getMessage());
    header('Location: contact.html?status=error');
    exit;
}
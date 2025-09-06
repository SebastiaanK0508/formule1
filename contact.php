<?php
    require_once 'db_config.php';
    /** @var PDO $pdo */
try {
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
    $stmt->bindParam(':contact_name', $contact_name);
    $stmt->bindParam(':contact_email', $contact_email);
    $stmt->bindParam(':contact_subject', $contact_subject);
    $stmt->bindParam(':contact_message', $contact_message);
    var_dump($stmt);
    die('123');
    $stmt->execute();
    $response['success'] = true;
    $response['message'] = 'Message succesvol verzonden!';

} catch (\PDOException $e) {
    error_log("Database query error: " . $e->getMessage());
    $response['message'] = 'Er is een databasefout opgetreden bij het opslaan van de Message: ' . $e->getMessage();
} catch (\Exception $e) {
    error_log("General script error: " . $e->getMessage());
    $response['message'] = 'Er is een onverwachte fout opgetreden.';
}
  header('location:contact.html');
?>


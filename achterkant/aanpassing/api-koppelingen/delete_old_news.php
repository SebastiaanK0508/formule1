<?php
function log_message($message) {
    echo "[" . date('Y-m-d H:i:s') . "] " . $message . "\n";
}

require_once 'db_config.php'; 
log_message("--- START Oude Nieuws Opschoning ---");
$cutoff_datetime = date('Y-m-d H:i:s', strtotime('-2 days'));

log_message("Cutoff Datum/Tijd ingesteld op: " . $cutoff_datetime);
log_message("Artikelen met created_at OUDER dan dit worden verwijderd.");

try {
    $stmt = $pdo->prepare("DELETE FROM f1_nieuws WHERE created_at < ?");
    $stmt->execute([$cutoff_datetime]);

    $rows_deleted = $stmt->rowCount();
    
    if ($rows_deleted > 0) {
        log_message("✅ SUCCES: " . $rows_deleted . " oude artikelen verwijderd.");
    } else {
        log_message("⚠️ GEEN ACTIE: Geen artikelen gevonden ouder dan 2 dagen om te verwijderen.");
    }

} catch (\PDOException $e) {
    log_message("❌ DATABASE FOUT: Fout bij het verwijderen van oude berichten.");
    log_message("Foutdetails: " . $e->getMessage());
    exit(1); // Exit met foutcode bij databasefout
}

log_message("--- EINDE Oude Nieuws Opschoning ---");

?>
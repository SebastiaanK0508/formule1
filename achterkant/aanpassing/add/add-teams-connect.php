<?php

header('Content-Type: application/json');
$response = ['success' => false, 'message' => ''];

require_once 'db_config.php';
/** @var PDO $pdo */ 

try {
    $team_name = $_POST['team_name'] ?? '';
    $team_color = $_POST['team_color'] ?? '#000000';
    $full_team_name = $_POST['full_team_name'] ?? null;
    $base_location = $_POST['base_location'] ?? null;
    $team_principal = $_POST['team_principal'] ?? null;
    $technical_director = $_POST['technical_director'] ?? null;
    $championships_won = $_POST['championships_won'] ?? 0;
    $first_entry_year = $_POST['first_entry_year'] ?? null;
    $website_url = $_POST['website_url'] ?? null;
    $logo_url = $_POST['logo_url'] ?? null;
    $current_engine_supplier = $_POST['current_engine_supplier'] ?? null;

    $is_active = isset($_POST['is_active']);

    if (empty($team_name)) {
        $response['message'] = 'Team Name is empty. Please provide a team name.';
        echo json_encode($response);
        exit(); 
    }

    $championships_won = (int)$championships_won;
    $first_entry_year = $first_entry_year !== null ? (int)$first_entry_year : null;

    $sql = "INSERT INTO teams (
                team_name, team_color, full_team_name, base_location,
                team_principal, technical_director, championships_won,
                first_entry_year, website_url, logo_url,
                current_engine_supplier, is_active
            ) VALUES (
                :team_name, :team_color, :full_team_name, :base_location,
                :team_principal, :technical_director, :championships_won,
                :first_entry_year, :website_url, :logo_url,
                :current_engine_supplier, :is_active
            )";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':team_name', $team_name);
    $stmt->bindParam(':team_color', $team_color);
    $stmt->bindParam(':full_team_name', $full_team_name);
    $stmt->bindParam(':base_location', $base_location);
    $stmt->bindParam(':team_principal', $team_principal);
    $stmt->bindParam(':technical_director', $technical_director);
    $stmt->bindParam(':championships_won', $championships_won, PDO::PARAM_INT);
    $stmt->bindParam(':first_entry_year', $first_entry_year, PDO::PARAM_INT);
    $stmt->bindParam(':website_url', $website_url);
    $stmt->bindParam(':logo_url', $logo_url);
    $stmt->bindParam(':current_engine_supplier', $current_engine_supplier);
    $stmt->bindParam(':is_active', $is_active, PDO::PARAM_BOOL); 

    $stmt->execute();
    $response['success'] = true;
    $response['message'] = 'Team succesvol toegevoegd aan de database!';

} catch (\PDOException $e) {
    error_log("Database query error: " . $e->getMessage());
    $response['message'] = 'Er is een databasefout opgetreden bij het opslaan van het team: ' . $e->getMessage();
} catch (\Exception $e) {
    error_log("General script error: " . $e->getMessage());
    $response['message'] = 'Er is een onverwachte fout opgetreden.';
}
  header('location:../teams.php');
echo json_encode($response);
?>

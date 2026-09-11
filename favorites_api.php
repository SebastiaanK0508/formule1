<?php
require_once 'db_config.php';
/** @var PDO $pdo */

header('Content-Type: application/json');

$driverIds = isset($_GET['drivers']) ? array_filter(array_map('intval', explode(',', $_GET['drivers']))) : [];
$teamIds = isset($_GET['teams']) ? array_filter(array_map('intval', explode(',', $_GET['teams']))) : [];

$result = ['drivers' => [], 'teams' => []];

try {
    if (!empty($driverIds)) {
        $placeholders = implode(',', array_fill(0, count($driverIds), '?'));
        $stmt = $pdo->prepare("SELECT d.driver_id, d.first_name, d.last_name, d.image, t.team_color FROM drivers d LEFT JOIN teams t ON d.team_id = t.team_id WHERE d.driver_id IN ($placeholders)");
        $stmt->execute(array_values($driverIds));
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $d) {
            $result['drivers'][] = [
                'id' => $d['driver_id'],
                'name' => $d['first_name'] . ' ' . $d['last_name'],
                'slug' => strtolower(str_replace(' ', '-', $d['first_name'] . '-' . $d['last_name'])),
                'image' => $d['image'],
                'team_color' => $d['team_color'] ?: '#E10600',
            ];
        }
    }
    if (!empty($teamIds)) {
        $placeholders = implode(',', array_fill(0, count($teamIds), '?'));
        $stmt = $pdo->prepare("SELECT team_id, team_name, team_color, logo_url FROM teams WHERE team_id IN ($placeholders)");
        $stmt->execute(array_values($teamIds));
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $t) {
            $result['teams'][] = [
                'id' => $t['team_id'],
                'name' => $t['team_name'],
                'logo' => $t['logo_url'],
                'team_color' => $t['team_color'] ?: '#E10600',
            ];
        }
    }
} catch (PDOException $e) {
    error_log("favorites_api error: " . $e->getMessage());
}

echo json_encode($result);

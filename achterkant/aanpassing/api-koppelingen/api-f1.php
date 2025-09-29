<?php
// api-f1.php

header('Content-Type: application/json');
require_once 'db_config.php';
/** @var PDO $pdo */

function sendResponse($data) {
    echo json_encode($data, JSON_PRETTY_PRINT);
    exit;
}

$endpoint = $_GET['endpoint'] ?? 'drivers';
$year = $_GET['year'] ?? null;
$driver_id = $_GET['driver'] ?? null;

$response = ['status' => 'success', 'data' => []];

try {
    switch ($endpoint) {
        
        case 'drivers':
            /** @var PDO $pdo */

            $stmt = $pdo->prepare("SELECT * FROM `drivers`");
            $stmt->execute();
            $response['data'] = $stmt->fetchAll();
            break;

        case 'teams':
            /** @var PDO $pdo */

            $stmt = $pdo->prepare("SELECT * FROM `teams`");
            $stmt->execute();
            $response['data'] = $stmt->fetchAll();
            break;

        case 'results':
            $sql = "
                SELECT 
                    res.race_year, 
                    c.grandprix AS race_name, 
                    d.first_name, 
                    d.last_name, 
                    t.team_name, 
                    res.position, 
                    res.points 
                FROM `race_results` res
                JOIN `drivers` d ON res.driver_id = d.driver_id
                LEFT JOIN `teams` t ON d.team_id = t.team_id
                LEFT JOIN `circuits` c ON res.circuit_key = c.circuit_key AND res.race_year = c.race_year
                WHERE 1=1
            ";
            $params = [];
            if ($year) {
                $sql .= " AND res.race_year = ?";
                $params[] = $year;
            }
            if ($driver_id) {
                $sql .= " AND d.driver_id = ?";
                $params[] = $driver_id;
            }
            $sql .= " ORDER BY res.race_year DESC, c.calendar_order ASC, res.position ASC";
            /** @var PDO $pdo */


            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $response['data'] = $stmt->fetchAll();
            break;

        default:
            http_response_code(404);
            $response = ['status' => 'error', 'message' => 'Endpoint niet gevonden.'];
            break;
    }
} catch (\PDOException $e) {
    http_response_code(500);
    $response = ['status' => 'error', 'message' => 'Fout bij het uitvoeren van de query: ' . $e->getMessage()];
}

sendResponse($response);
?>
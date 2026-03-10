<?php
session_start();
require_once '../db_config.php';
/** @var PDO $pdo */

echo "<h2>Starting OpenF1 Sync...</h2>";

$year = 2026;
$apiUrl = "https://api.openf1.org/v1/sessions?year=" . $year;

// Helper functie om API datum naar MySQL DATETIME te converteren
function formatToMySQL($dateStr) {
    if (empty($dateStr)) return null;
    try {
        $date = new DateTime($dateStr);
        return $date->format('Y-m-d H:i:s'); // Verwijst de T en de +00:00 offset
    } catch (Exception $e) {
        return null;
    }
}

$response = @file_get_contents($apiUrl);
if (!$response) { die("Fout bij ophalen API data."); }

$sessions = json_decode($response, true);
$groupedSessions = [];

foreach ($sessions as $session) {
    $loc = $session['location'];
    if (!isset($groupedSessions[$loc])) {
        $groupedSessions[$loc] = [
            'fp1' => null, 'fp2' => null, 'fp3' => null, 
            'quali' => null, 'sprint_quali' => null, 'sprint' => null, 'race' => null
        ];
    }

    $name = strtolower($session['session_name']);
    // Gebruik hier de nieuwe format functie
    $startTime = formatToMySQL($session['date_start']);

    if (str_contains($name, 'practice 1')) {
        $groupedSessions[$loc]['fp1'] = $startTime;
    } elseif (str_contains($name, 'practice 2')) {
        $groupedSessions[$loc]['fp2'] = $startTime;
    } elseif (str_contains($name, 'practice 3')) {
        $groupedSessions[$loc]['fp3'] = $startTime;
    } elseif (str_contains($name, 'sprint qualifying')) {
        $groupedSessions[$loc]['sprint_quali'] = $startTime; 
    } elseif (str_contains($name, 'sprint') && !str_contains($name, 'qualifying')) {
        $groupedSessions[$loc]['sprint'] = $startTime;
    } elseif (str_contains($name, 'qualifying')) {
        $groupedSessions[$loc]['quali'] = $startTime;
    } elseif (str_contains($name, 'race')) {
        $groupedSessions[$loc]['race'] = $startTime;
    }
}

foreach ($groupedSessions as $location => $times) {
    try {
        // We filteren alleen op de GP waar de RACE_DATETIME in het juiste jaar valt
        $sql = "UPDATE circuits SET 
                    fp1_datetime = :fp1, 
                    fp2_datetime = :fp2, 
                    fp3_datetime = :fp3, 
                    sprint_quali_datetime = :sprint_quali,
                    sprint_datetime = :sprint, 
                    quali_datetime = :quali, 
                    race_datetime = :race 
                WHERE location LIKE :loc 
                AND YEAR(race_datetime) = :year";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':fp1'          => $times['fp1'],
            ':fp2'          => $times['fp2'],
            ':fp3'          => $times['fp3'],
            ':sprint_quali' => $times['sprint_quali'],
            ':sprint'       => $times['sprint'],
            ':quali'        => $times['quali'],
            ':race'         => $times['race'],
            ':loc'          => '%' . $location . '%',
            ':year'         => $year
        ]);

        if ($stmt->rowCount() > 0) {
            echo "✅ Updated: " . htmlspecialchars($location) . "<br>";
        } else {
            // Optioneel: laat zien welke locaties niet gematcht konden worden
            echo "⚠️ No match for: " . htmlspecialchars($location) . "<br>";
        }
    } catch (\PDOException $e) {
        echo "❌ Error updating " . htmlspecialchars($location) . ": " . $e->getMessage() . "<br>";
    }
}

echo "<br><strong>Sync Complete!</strong> <a href='kalender.php'>Terug naar kalender</a>";
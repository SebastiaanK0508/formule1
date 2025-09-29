<?php
// // json-to-db-migrator.php - STAP 1: TEAMS MIGREREN

// require_once 'db_config.php';
// /** @var PDO $pdo */

// function loadJsonFile($filename) {
//     $path = __DIR__ . '/json/' . $filename;
//     if (!is_readable($path)) {
//         die("Fout: Bestand '$path' niet gevonden of onleesbaar.");
//     }
//     $json_data = file_get_contents($path);
//     return json_decode($json_data, true);
// }

// echo "Starten van Teams migratie...\n";

// try {
//     $teams_json = loadJsonFile('teams.json');

    // --- Teams toevoegen ---
//     echo "Teams toevoegen...\n";
//     $sql = "INSERT INTO `teams` (`team_id`, `team_name`, `team_color`, `full_team_name`, `base_location`, `team_principal`, `technical_director`, `first_entry_year`, `website_url`, `logo_url`, `current_engine_supplier`, `is_active`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE `team_name`=VALUES(`team_name`)";
//     $stmt = $pdo->prepare($sql);
    
//     if (isset($teams_json['teams']) && is_array($teams_json['teams'])) {
//         foreach ($teams_json['teams'] as $team) {
//             $stmt->execute([
//                 $team['id'] ?? null, $team['name'] ?? null, $team['teamColor'] ?? null, $team['fullName'] ?? null, 
//                 $team['baseLocation'] ?? null, $team['teamPrincipal'] ?? null, $team['technicalDirector'] ?? null, 
//                 $team['firstEntryYear'] ?? null, $team['websiteUrl'] ?? null, $team['logoUrl'] ?? null, 
//                 $team['currentEngineSupplier'] ?? null, $team['active'] ?? 1
//             ]);
//             echo "Team '" . ($team['name'] ?? 'Onbekend') . "' succesvol toegevoegd.\n";
//         }
//     }
//     echo "\nTeams migratie voltooid.\n";

// } catch (PDOException $e) {
//     echo "FATALE FOUT: " . $e->getMessage() . "\n";
// }

// ?>

<?php
// json-to-db-migrator.php - ROBUUSTE DEBUG VERSIE

// require_once 'db_config.php';
// /** @var PDO $pdo */



// // Controleer of de PDO-verbinding succesvol is
// if ($pdo) {
//     echo "✅ Database connectie succesvol!\n";
//     echo "Je kunt de PDO-variabele nu gebruiken om queries uit te voeren.\n";

//     // Simpele test query
//     try {
//         $stmt = $pdo->query("SELECT COUNT(*) FROM `drivers`");
//         $count = $stmt->fetchColumn();
//         echo "Aantal rijen in 'drivers' tabel: " . $count . "\n";
//     } catch (PDOException $e) {
//         echo "❌ Fout bij uitvoeren testquery: " . $e->getMessage() . "\n";
//     }

// } else {
//     echo "❌ Database connectie mislukt. Controleer db_config.php\n";
// }



// function loadJsonFile($filename) {
//     $path = __DIR__ . '/json/' . $filename;
//     if (!is_readable($path)) {
//         die("Fout: Bestand '$path' niet gevonden of onleesbaar.");
//     }
//     $json_data = file_get_contents($path);
//     return json_decode($json_data, true);
// }

// echo "Start datamigratie...\n";

// try {
//     // Laad de data
//     $drivers_json = loadJsonFile('drivers.json');
//     $teams_json = loadJsonFile('teams.json');
//     $races_json = loadJsonFile('f1db-grands-prix.json');
//     $results_json = loadJsonFile('f1db-races-race-results.json');

//     // --- Pre-processing: Maak een koppeling tussen raceId en circuit data ---
//     $race_to_circuit = [];
//     if (isset($races_json['races']) && is_array($races_json['races'])) {
//         foreach ($races_json['races'] as $race) {
//             $race_to_circuit[$race['id']] = [
//                 'circuit_key' => $race['circuit']['circuitId'] ?? null,
//                 'race_year' => $race['season']['year'] ?? null
//             ];
//         }
//     }

//     // --- Teams toevoegen ---
//     echo "Teams toevoegen...\n";
//     $sql = "INSERT INTO `teams` (`team_id`, `team_name`, `team_color`, `full_team_name`, `base_location`, `team_principal`, `technical_director`, `first_entry_year`, `website_url`, `logo_url`, `current_engine_supplier`, `is_active`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE `team_name`=VALUES(`team_name`)";
//     $stmt = $pdo->prepare($sql);
//     if (isset($teams_json['teams']) && is_array($teams_json['teams'])) {
//         foreach ($teams_json['teams'] as $team) {
//             $stmt->execute([
//                 $team['id'] ?? null, $team['name'] ?? null, $team['teamColor'] ?? null, $team['fullName'] ?? null,
//                 $team['baseLocation'] ?? null, $team['teamPrincipal'] ?? null, $team['technicalDirector'] ?? null,
//                 $team['firstEntryYear'] ?? null, $team['websiteUrl'] ?? null, $team['logoUrl'] ?? null,
//                 $team['currentEngineSupplier'] ?? null, $team['active'] ?? 1
//             ]);
//         }
//     }
//     echo "Teams succesvol toegevoegd.\n";

// // --- Drivers toevoegen ---
// echo "Drivers toevoegen...\n";
// $insertedRows = 0;
// if (isset($drivers_json['drivers']) && is_array($drivers_json['drivers'])) {
//     $sql = "INSERT INTO `drivers` (`driver_id`, `first_name`, `last_name`, `nationality`, `date_of_birth`, `driver_number`, `is_active`, `image`, `flag_url`, `driver_color`, `place_of_birth`, `description`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE `first_name`=VALUES(`first_name`), `last_name`=VALUES(`last_name`)";
//     try {
//         $stmt = $pdo->prepare($sql);
//         foreach ($drivers_json['drivers'] as $driver) {
//             $params = [
//                 $driver['driverId'] ?? null, $driver['givenName'] ?? null, $driver['familyName'] ?? null, $driver['nationality'] ?? null,
//                 $driver['dateOfBirth'] ?? null, $driver['permanentNumber'] ?? null, $driver['active'] ?? 1,
//                 $driver['url'] ?? null, $driver['flagUrl'] ?? null, $driver['color'] ?? null,
//                 $driver['placeOfBirth'] ?? null, $driver['description'] ?? null
//             ];
            
//             $stmt->execute($params);
//             $insertedRows += $stmt->rowCount(); // Tel het aantal toegevoegde/aangepaste rijen
//         }
//         echo "Drivers succesvol toegevoegd. Totaal aantal rijen gewijzigd: " . $insertedRows . "\n";
//     } catch (PDOException $e) {
//         echo "Fout bij Drivers toevoegen: " . $e->getMessage() . "\n";
//     }
// }

//     // --- Circuits toevoegen ---
//     echo "Circuits toevoegen...\n";
//     $sql = "INSERT INTO `circuits` (`circuit_key`, `title`, `grandprix`, `location`, `map_url`, `first_gp_year`, `lap_count`, `circuit_length_km`, `race_distance_km`, `lap_record`, `description`, `highlights`, `calendar_order`, `race_year`, `race_datetime`, `country_flag_url`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE `title`=VALUES(`title`)";
//     $stmt = $pdo->prepare($sql);
//     if (isset($races_json['races']) && is_array($races_json['races'])) {
//         foreach ($races_json['races'] as $race) {
//             $circuit = $race['circuit'];
//             $season = $race['season'];

//             $stmt->execute([
//                 $circuit['circuitId'] ?? null, $circuit['name'] ?? null, $race['name'] ?? null, $circuit['location']['country'] ?? null,
//                 $circuit['mapUrl'] ?? null, $circuit['firstGpYear'] ?? null, $circuit['lapCount'] ?? null,
//                 $circuit['lengthKm'] ?? null, $circuit['distanceKm'] ?? null, $circuit['lapRecord'] ?? null,
//                 $circuit['description'] ?? null, $circuit['highlights'] ?? null, $race['round'] ?? null,
//                 $season['year'] ?? null, $race['raceDate'] ?? null, $circuit['flagUrl'] ?? null
//             ]);
//         }
//     }
//     echo "Circuits succesvol toegevoegd.\n";

//     // --- Race Results toevoegen ---
//     echo "Race Results toevoegen...\n";
//     $sql = "INSERT INTO `race_results` (`circuit_key`, `driver_id`, `race_year`, `race_type`, `position`, `points`, `laps_completed`, `finish_status`, `fastest_lap_time`, `time_offset`, `pole_position`) VALUES (?, ?, ?, 'Race', ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE `position`=VALUES(`position`), `points`=VALUES(`points`)";
//     $stmt = $pdo->prepare($sql);
//     if (is_array($results_json)) {
//         foreach ($results_json as $result) {
//             $raceId = $result['raceId'];
//             if (isset($race_to_circuit[$raceId])) {
//                 $circuit_key = $race_to_circuit[$raceId]['circuit_key'];
//                 $race_year = $race_to_circuit[$raceId]['race_year'];
//                 $stmt->execute([
//                     $circuit_key, $result['driverId'], $race_year, $result['positionNumber'] ?? null,
//                     $result['points'] ?? null, $result['laps'] ?? null, $result['reasonRetired'] ?? null,
//                     $result['fastestLap']['time'] ?? null, $result['gap'] ?? null,
//                     $result['polePosition'] ?? 0
//                 ]);
//             }
//         }
//     }
//     echo "Race Results succesvol toegevoegd.\n";

//     echo "\nAlle data migratie is voltooid.\n";

// } catch (PDOException $e) {
//     echo "FATALE FOUT TIJDENS MIGRATIE: " . $e->getMessage() . "\n";
//     echo "Foutcode: " . $e->getCode() . "\n";
//     echo "SQLSTATE: " . $e->errorInfo[0] . "\n";
// }

?>
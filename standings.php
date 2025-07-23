<?php
$host = 'localhost';    
$dbname = 'formule1';  
$username = 'root';    
$password = 'root';   

try {
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {

    die("Databaseverbinding mislukt: " . $e->getMessage());
}

$sql_drivers = "
    SELECT
        d.driver_id,
        d.first_name,
        d.last_name,
        t.team_name,
        SUM(ps.points) AS total_points
    FROM
        drivers d
    JOIN
        teams t ON d.team_name = t.team_name
    JOIN
        race_results rr ON d.driver_id = rr.driver_id
    JOIN
        points_system ps ON rr.position = ps.position
    GROUP BY
        d.driver_id, d.first_name, d.last_name, t.team_name
    ORDER BY
        total_points DESC, d.last_name ASC;
";

$stmt_drivers = $pdo->query($sql_drivers);
$driver_standings = $stmt_drivers->fetchAll(PDO::FETCH_ASSOC);

$sql_teams = "
    SELECT
        t.team_id,
        t.team_name,
        SUM(ps.points) AS total_points
    FROM
        teams t
    JOIN
        drivers d ON t.team_name = d.team_name -- Weer de join via team_name
    JOIN
        race_results rr ON d.driver_id = rr.driver_id
    JOIN
        points_system ps ON rr.position = ps.position
    GROUP BY
        t.team_id, t.team_name
    ORDER BY
        total_points DESC, t.team_name ASC;
";

$stmt_teams = $pdo->query($sql_teams);
$team_standings = $stmt_teams->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formula 1 Season 2025 - Drivers</title>
    <link rel="stylesheet" href="style.css">
    <style></style>
</head>
<body>
    <header>
        <div class="header-content container">
            <h1 class="site-title">Formula 1 Season 2025</h1>
            <nav class="main-nav">
                <a href="index.php">Home</a>
                <a href="kalender.php">Schedule</a>
                <a href="teams.php" >Teams</a>
                <a href="drivers.php">Drivers</a>
                <a href="standings.php" class="active">Standings</a>
            </nav>
        </div>
    </header>

    <main id="f1-standings-container" class="container">
        <section class="standings-grid">
                <div class="standings-table-container mt-4">
                    <h3 class="">Coureur Standen</h3>
                    <?php if (!empty($driver_standings)): ?>
                    <table class="min-w-full bg-white border border-gray-300 shadow-md rounded-lg overflow-hidden">
                        <thead class="">
                            <tr class="standingstrtab">
                                <th class="standingstabheader">Positie</th>
                                <th class="standingstabheader">Coureur</th>
                                <th class="standingstabheader">Team</th>
                                <th class="standingstabheader">Punten</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $position = 1; ?>
                            <?php foreach ($driver_standings as $driver): ?>
                                <tr class="standingstrtab">
                                    <td class="standingstabheader"><?php echo $position++; ?></td>
                                    <td class="standingstabheader"><?php echo htmlspecialchars($driver['first_name'] . ' ' . $driver['last_name']); ?></td>
                                    <td class="standingstabheader"><?php echo htmlspecialchars($driver['team_name']); ?></td>
                                    <td class="standingstabheader"><?php echo intval($driver['total_points']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                        <p class="text-gray-600">Geen coureurstanden gevonden. Controleer de database en of er gegevens zijn ingevoerd.</p>
                    <?php endif; ?>
                </div>


                <div class="standings-table-container">
                    <h3 class="text-xl font-bold mt-8 mb-4">Team Standen</h3>
                    <?php if (!empty($team_standings)): ?>
                    <table class="min-w-full bg-white border border-gray-300 shadow-md rounded-lg overflow-hidden">
                        <thead class="bg-gray-200">
                            <tr>
                                <th class="py-3 px-4 border-b text-left text-sm font-semibold text-gray-700">Positie</th>
                                <th class="py-3 px-4 border-b text-left text-sm font-semibold text-gray-700">Team</th>
                                <th class="py-3 px-4 border-b text-left text-sm font-semibold text-gray-700">Punten</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $position_team = 1; ?>
                            <?php foreach ($team_standings as $team): ?>
                                <tr class="<?php echo ($position_team % 2 == 0) ? 'bg-gray-50' : 'bg-white'; ?>">
                                    <td class="py-2 px-4 border-b text-sm text-gray-800"><?php echo $position_team++; ?></td>
                                    <td class="py-2 px-4 border-b text-sm text-gray-800"><?php echo htmlspecialchars($team['team_name']); ?></td>
                                    <td class="py-2 px-4 border-b text-sm text-gray-800 font-bold"><?php echo intval($team['total_points']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                        <p class="text-gray-600">Geen teamstanden gevonden. Controleer de database en of er gegevens zijn ingevoerd.</p>
                    <?php endif; ?>
                </div>
        </section>
    </main>
</body>
</html>
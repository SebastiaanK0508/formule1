<?php
require_once 'db_config.php';

/** @var PDO $pdo */ 

$teams = [];

try {
    $stmt_teams = $pdo->query("SELECT team_id, team_name FROM teams ORDER BY team_name ASC");
    $teams = $stmt_teams->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database error fetching teams: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Webbair Framework</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="" type="image/x-icon">
</head>
<body>
    <header class="main-header">
        <div class="header-title">
            <h1>Formula 1 site - Coureurs Toevoegen</h1>
        </div>
    </header>
    <main class="main-content-area">
        <section class="main-content-panel">
            <div class="headerhoofdpagina">
                <h2 class="main-title">Nieuwe Coureur Toevoegen</h2>
            </div>
            <div>
                <a href="../drivers.php"><button class="adddriverbutton">Terug naar Coureurs Overzicht</button></a>
            </div>
            <div>
                <form action="add-driver-connect.php" method="POST" class="">
                    <div>
                        <label for="first_name" class="block text-sm font-medium text-gray-700 mb-1">Voornaam:</label>
                        <input type="text" id="first_name" name="first_name" required class="">
                    </div>

                    <div>
                        <label for="last_name" class="block text-sm font-medium text-gray-700 mb-1">Achternaam:</label>
                        <input type="text" id="last_name" name="last_name" required class="">
                    </div>

                    <div>
                        <label for="nationality" class="block text-sm font-medium text-gray-700 mb-1">Nationaliteit:</label>
                        <input type="text" id="nationality" name="nationality" required class="">
                    </div>

                    <div>
                        <label for="date_of_birth" class="block text-sm font-medium text-gray-700 mb-1">Geboortedatum:</label>
                        <input type="date" id="date_of_birth" name="date_of_birth" class="">
                    </div>

                    <div>
                        <label for="driver_number" class="block text-sm font-medium text-gray-700 mb-1">Coureursnummer (optioneel):</label>
                        <input type="number" id="driver_number" name="driver_number" min="1" max="99" class="">
                    </div>

                    <div>
                        <label for="team_id" class="block text-sm font-medium text-gray-700 mb-1">Teamnaam (huidig):</label>
                        <select class="" name="team_id" id="team_id">
                            <option value="" disabled selected>--selecteer team--</option>
                            <?php foreach ($teams as $team): ?>
                                <option value="<?php echo htmlspecialchars($team['team_id']); ?>">
                                    <?php echo htmlspecialchars($team['team_name']); ?>
                                </option>
                            <?php endforeach; ?>
                            <option value="0">N.V.T. (Geen team)</option>
                        </select>
                    </div>

                    <div>
                        <label for="championships_won" class="block text-sm font-medium text-gray-700 mb-1">Kampioenschappen gewonnen:</label>
                        <input type="number" id="championships_won" name="championships_won" min="0" value="0" class="">
                    </div>

                    <div>
                        <label for="career_points" class="block text-sm font-medium text-gray-700 mb-1">Carrièrepunten:</label>
                        <input type="number" id="career_points" name="career_points" step="0.01" min="0" value="0.00" class="">
                    </div>

                    <div>
                        <label for="image_url" class="block text-sm font-medium text-gray-700 mb-1">Coureursfoto URL:</label>
                        <input type="text" id="image_url" name="image_url" class="">
                    </div>
                    
                    <div>
                        <label for="flag_url" class="block text-sm font-medium text-gray-700 mb-1">Landvlag URL:</label>
                        <input type="text" id="flag_url" name="flag_url" class="">
                    </div>

                    <div>
                        <label for="place_of_birth" class="block text-sm font-medium text-gray-700 mb-1">Geboorteplaats:</label>
                        <input type="text" id="place_of_birth" name="place_of_birth" class="">
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Beschrijving:</label>
                        <textarea id="description" name="description" rows="5" class=""></textarea>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" id="is_active" name="is_active" checked class="mr-2">
                        <label for="is_active" class="text-sm font-medium text-gray-700">Momenteel actief?</label>
                    </div>

                    <div>
                        <button type="submit" class="adddriverbutton">Coureur Toevoegen</button>
                    </div>
                </form>
            </div>
        </section>
    </main>
</body>
</html>
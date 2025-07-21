<?php
$response = ['success' => false, 'message' => ''];
$message = '';
$teamDetails = [];

$host = "localhost";
$db = "formule1";
$user = "root";
$pass = "root";
$charset = "utf8mb4";

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$pdoOptions = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

$pdo = null;

try {
    $pdo = new PDO($dsn, $user, $pass, $pdoOptions);
} catch (\PDOException $e) {
    error_log("Database connection error: " . $e->getMessage());
    $message = "Er was een probleem met de databaseverbinding. Probeer het later opnieuw.";
    die("Verbindingsfout: " . $e->getMessage());
}

$teamId = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $teamId) {
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
            $message = "<p class='error-message'>Team Naam is verplicht. Geef een teamnaam op.</p>";
        } else {

            $championships_won = (int)$championships_won;
            $first_entry_year = $first_entry_year !== null ? (int)$first_entry_year : null;

            $sql = "UPDATE teams SET
                        team_name = :team_name,
                        team_color = :team_color,
                        full_team_name = :full_team_name,
                        base_location = :base_location,
                        team_principal = :team_principal,
                        technical_director = :technical_director,
                        championships_won = :championships_won,
                        first_entry_year = :first_entry_year,
                        website_url = :website_url,
                        logo_url = :logo_url,
                        current_engine_supplier = :current_engine_supplier,
                        is_active = :is_active
                    WHERE team_id = :team_id";

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
            $stmt->bindParam(':team_id', $teamId, PDO::PARAM_INT);

            if ($stmt->execute()) {
                $message = "<p class='success-message'>Teamgegevens succesvol bijgewerkt!</p>";
            } else {
                $message = "<p class='error-message'>Fout bij het bijwerken van teamgegevens.</p>";
            }
        }
    } catch (\PDOException $e) {
        error_log("Database query error (update team): " . $e->getMessage());
        $message = "<p class='error-message'>Databasefout bij bijwerken: " . $e->getMessage() . "</p>";
    } catch (\Exception $e) {
        error_log("General script error (update team): " . $e->getMessage());
        $message = "<p class='error-message'>Er is een onverwachte fout opgetreden bij het bijwerken.</p>";
    }
}

if ($teamId) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM teams WHERE team_id = :team_id");
        $stmt->bindParam(':team_id', $teamId, PDO::PARAM_INT);
        $stmt->execute();
        $teamDetails = $stmt->fetch();

        if (!$teamDetails) {
            $message = "<p class='error-message'>Team met ID " . htmlspecialchars($teamId) . " niet gevonden.</p>";
        }
    } catch (\PDOException $e) {
        error_log("Database query error (fetch team): " . $e->getMessage());
        $message = "<p class='error-message'>Fout bij het ophalen van teamdetails: " . $e->getMessage() . "</p>";
    }
} else {
    $message = "<p class='error-message'>Geen geldige team-ID opgegeven.</p>";
}

if (!is_array($teamDetails)) {
    $teamDetails = [];
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Details Team: <?php echo htmlspecialchars($teamDetails['team_name'] ?? 'Onbekend Team'); ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f4f4f4; color: #333; }
        .container { max-width: 800px; margin: 20px auto; background-color: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        h1 { color: #0056b3; text-align: center; margin-bottom: 30px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #555; }
        input[type="text"],
        input[type="number"],
        input[type="date"],
        input[type="color"], 
        textarea,
        select {
            width: calc(100% - 22px);
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            background-color: #e9e9e9;
        }
        input[type="checkbox"] {
            margin-right: 10px;
        }
        input[type="text"]:focus,
        input[type="number"]:focus,
        input[type="date"]:focus,
        input[type="color"]:focus, /* Toegevoegd */
        textarea:focus,
        select:focus {
            outline: none;
            border-color: #007bff;
        }

        input[type="text"]:not([readonly]),
        input[type="number"]:not([readonly]),
        input[type="date"]:not([readonly]),
        input[type="color"]:not([readonly]), 
        textarea:not([readonly]),
        select:not([disabled]) {
            background-color: white;
        }

        .button-group { margin-top: 20px; text-align: center; }
        .button-group button, .button-group a {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
            display: inline-block;
            margin: 0 5px;
        }
        #editButton { background-color: #007bff; color: white; }
        #editButton:hover { background-color: #0056b3; }
        #saveButton { background-color: #28a745; color: white; display: none; } 
        #saveButton:hover { background-color: #218838; }
        .back-link { background-color: #6c757d; color: white; }
        .back-link:hover { background-color: #5a6268; }

        .team-logo { max-width: 200px; height: auto; display: block; margin: 0 auto 20px auto; border-radius: 8px; }
        .message { padding: 10px; margin-bottom: 20px; border-radius: 4px; }
        .success-message { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error-message { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>

    <div class="container">
        <?php echo $message; ?>

        <?php if ($teamDetails && $teamId): ?>
            <h1>Details van Team: <?php echo htmlspecialchars($teamDetails['team_name'] ?? 'Onbekend Team'); ?></h1>

            <?php if (!empty($teamDetails['logo_url'])): ?>
                <img src="<?php echo htmlspecialchars($teamDetails['logo_url']); ?>" alt="Logo van <?php echo htmlspecialchars($teamDetails['team_name']); ?>" class="team-logo">
            <?php endif; ?>

            <form method="POST" action="">
                <input type="hidden" name="team_id" value="<?php echo htmlspecialchars($teamDetails['team_id']); ?>">

                <div class="form-group">
                    <label for="team_name">Team Naam:</label>
                    <input type="text" id="team_name" name="team_name" value="<?php echo htmlspecialchars($teamDetails['team_name'] ?? ''); ?>" required readonly>
                </div>

                <div class="form-group">
                    <label for="team_color">Team Kleur:</label>
                    <input type="color" id="team_color" name="team_color" value="<?php echo htmlspecialchars($teamDetails['team_color'] ?? '#000000'); ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="full_team_name">Volledige Team Naam:</label>
                    <input type="text" id="full_team_name" name="full_team_name" value="<?php echo htmlspecialchars($teamDetails['full_team_name'] ?? ''); ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="base_location">Basis Locatie:</label>
                    <input type="text" id="base_location" name="base_location" value="<?php echo htmlspecialchars($teamDetails['base_location'] ?? ''); ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="team_principal">Team Principal:</label>
                    <input type="text" id="team_principal" name="team_principal" value="<?php echo htmlspecialchars($teamDetails['team_principal'] ?? ''); ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="technical_director">Technisch Directeur:</label>
                    <input type="text" id="technical_director" name="technical_director" value="<?php echo htmlspecialchars($teamDetails['technical_director'] ?? ''); ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="championships_won">Kampioenschappen Gewonnen:</label>
                    <input type="number" id="championships_won" name="championships_won" value="<?php echo htmlspecialchars($teamDetails['championships_won'] ?? 0); ?>" min="0" readonly>
                </div>

                <div class="form-group">
                    <label for="first_entry_year">Eerste Deelname Jaar:</label>
                    <input type="number" id="first_entry_year" name="first_entry_year" value="<?php echo htmlspecialchars($teamDetails['first_entry_year'] ?? ''); ?>" min="1900" max="2100" readonly>
                </div>

                <div class="form-group">
                    <label for="website_url">Website URL:</label>
                    <input type="text" id="website_url" name="website_url" value="<?php echo htmlspecialchars($teamDetails['website_url'] ?? ''); ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="logo_url">Logo URL:</label>
                    <input type="text" id="logo_url" name="logo_url" value="<?php echo htmlspecialchars($teamDetails['logo_url'] ?? ''); ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="current_engine_supplier">Huidige Motorleverancier:</label>
                    <input type="text" id="current_engine_supplier" name="current_engine_supplier" value="<?php echo htmlspecialchars($teamDetails['current_engine_supplier'] ?? ''); ?>" readonly>
                </div>

                <div class="form-group">
                    <input type="checkbox" id="is_active" name="is_active" <?php echo ($teamDetails['is_active'] ?? 0) ? 'checked' : ''; ?> disabled>
                    <label for="is_active">Actief?</label>
                </div>

                <div class="button-group">
                    <button type="button" id="editButton">Bewerk</button>
                    <button type="submit" id="saveButton">Opslaan</button>
                    <a href="teams.php" class="back-link">Terug naar Overzicht</a>
                </div>
            </form>

        <?php else: ?>
            <p>De details van dit team konden niet worden geladen of het team bestaat niet.</p>
            <a href="teams.php" class="back-link">Terug naar Overzicht</a>
        <?php endif; ?>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const editButton = document.getElementById('editButton');
            const saveButton = document.getElementById('saveButton');
            
            const inputs = form.querySelectorAll('input:not([type="hidden"]), textarea, select');

            function setEditable(isEditable) {
                inputs.forEach(input => {
                    if (input.type === 'checkbox') {
                        input.disabled = !isEditable; 
                    } else if (input.tagName === 'SELECT' || input.tagName === 'TEXTAREA') {
                        input.disabled = !isEditable; 
                    } else {
                        input.readOnly = !isEditable;
                    }
                });

                if (isEditable) {
                    editButton.style.display = 'none';
                    saveButton.style.display = 'inline-block';
                } else {
                    editButton.style.display = 'inline-block';
                    saveButton.style.display = 'none';
                }
            }

            setEditable(false);

            editButton.addEventListener('click', function() {
                setEditable(true);
            });

            form.addEventListener('submit', function() {
                setEditable(true); 
            });
        });
    </script>

</body>
</html>

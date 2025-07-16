<?php
// Database configuratie
$host = "localhost";
$db = "formule1";
$user = "root";
$pass = "root";
$charset = "utf8mb4";

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

$pdo = null;
$driverDetails = null; // Voor de details van de coureur
$teams = []; // Voor de lijst van teams
$message = ''; // Voor succes- of foutmeldingen

try {
    $pdo = new PDO($dsn, $user, $pass, $options);

    // Haal alle unieke teamnamen op voor de dropdown
    $stmt = $pdo->query("SELECT DISTINCT team_name FROM drivers WHERE team_name IS NOT NULL AND team_name != '' ORDER BY team_name ASC");
    $teams = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (!in_array('No Team', $teams)) {
        $teams[] = 'No Team';
    }

} catch (\PDOException $e) {
    die("Verbindingsfout: " . $e->getMessage());
}

$driverId = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : null;

// Als het een POST-verzoek is, probeer dan de gegevens bij te werken
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $driverId) {
    try {
        $firstName = $_POST['first_name'] ?? '';
        $lastName = $_POST['last_name'] ?? '';
        $nationality = $_POST['nationality'] ?? '';
        $dateOfBirth = $_POST['date_of_birth'] ?? null;
        $driverNumber = $_POST['driver_number'] ?? null;
        $teamName = $_POST['team_name'] ?? '';
        $championshipsWon = $_POST['championships_won'] ?? 0;
        $careerPoints = $_POST['career_points'] ?? 0.00;
        $imageUrl = $_POST['image'] ?? null;
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        $sql = "UPDATE drivers SET
                    first_name = :first_name,
                    last_name = :last_name,
                    nationality = :nationality,
                    date_of_birth = :date_of_birth,
                    driver_number = :driver_number,
                    team_name = :team_name,
                    championships_won = :championships_won,
                    career_points = :career_points,
                    image = :image,
                    is_active = :is_active
                WHERE driver_id = :driver_id";

        $stmt = $pdo->prepare($sql);

        $stmt->bindParam(':first_name', $firstName);
        $stmt->bindParam(':last_name', $lastName);
        $stmt->bindParam(':nationality', $nationality);
        $stmt->bindParam(':date_of_birth', $dateOfBirth);
        $stmt->bindParam(':driver_number', $driverNumber, PDO::PARAM_INT);
        $stmt->bindParam(':team_name', $teamName);
        $stmt->bindParam(':championships_won', $championshipsWon, PDO::PARAM_INT);
        $stmt->bindParam(':career_points', $careerPoints);
        $stmt->bindParam(':image', $imageUrl);
        $stmt->bindParam(':is_active', $isActive, PDO::PARAM_INT);
        $stmt->bindParam(':driver_id', $driverId, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $message = "<p class='success-message'>Coureurgegevens succesvol bijgewerkt!</p>";
            // Herlaad de driverDetails na de update om de nieuwste gegevens te tonen
            $stmt = $pdo->prepare("SELECT * FROM drivers WHERE driver_id = :driver_id");
            $stmt->bindParam(':driver_id', $driverId, PDO::PARAM_INT);
            $stmt->execute();
            $driverDetails = $stmt->fetch();
        } else {
            $message = "<p class='error-message'>Fout bij het bijwerken van coureurgegevens.</p>";
        }

    } catch (\PDOException $e) {
        $message = "<p class='error-message'>Databasefout bij bijwerken: " . $e->getMessage() . "</p>";
    }
}

// Haal de driver details op voor weergave (ook na een POST-update)
if ($driverId && !$driverDetails) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM drivers WHERE driver_id = :driver_id");
        $stmt->bindParam(':driver_id', $driverId, PDO::PARAM_INT);
        $stmt->execute();
        $driverDetails = $stmt->fetch();

        if (!$driverDetails) {
            $message = "<p class='error-message'>Coureur met ID " . htmlspecialchars($driverId) . " niet gevonden.</p>";
        }
    } catch (\PDOException $e) {
        $message = "<p class='error-message'>Fout bij het ophalen van coureurdetails: " . $e->getMessage() . "</p>";
    }
} else if (!$driverId) {
    $message = "<p class='error-message'>Geen geldige coureur-ID opgegeven.</p>";
}

if (!is_array($driverDetails)) {
    $driverDetails = [];
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Details Coureur: <?php echo htmlspecialchars($driverDetails['first_name'] ?? 'Onbekend') . ' ' . htmlspecialchars($driverDetails['last_name'] ?? 'Onbekend'); ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f4f4f4; color: #333; }
        .container { max-width: 800px; margin: 20px auto; background-color: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        h1 { color: #0056b3; text-align: center; margin-bottom: 30px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #555; }
        input[type="text"],
        input[type="number"],
        input[type="date"],
        select {
            width: calc(100% - 22px);
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            background-color: #e9e9e9; /* Standaard niet bewerkbaar */
        }
        input[type="checkbox"] {
            margin-right: 10px;
        }
        input[type="text"]:focus,
        input[type="number"]:focus,
        input[type="date"]:focus,
        select:focus {
            outline: none;
            border-color: #007bff;
        }

        /* Styles voor bewerkbare velden */
        input[type="text"]:not([readonly]),
        input[type="number"]:not([readonly]),
        input[type="date"]:not([readonly]),
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
        #saveButton { background-color: #28a745; color: white; display: none; } /* Standaard verborgen */
        #saveButton:hover { background-color: #218838; }
        .back-link { background-color: #6c757d; color: white; }
        .back-link:hover { background-color: #5a6268; }

        .driver-image { max-width: 200px; height: auto; display: block; margin: 0 auto 20px auto; border-radius: 8px; }
        .message { padding: 10px; margin-bottom: 20px; border-radius: 4px; }
        .success-message { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error-message { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>

    <div class="container">
        <?php echo $message; // Toon succes- of foutmeldingen ?>

        <?php if ($driverDetails && $driverId): ?>
            <h1>Details van Coureur: <?php echo htmlspecialchars($driverDetails['first_name'] . ' ' . $driverDetails['last_name']); ?></h1>

            <?php if (!empty($driverDetails['image'])): ?>
                <img src="<?php echo htmlspecialchars($driverDetails['image']); ?>" alt="Foto van <?php echo htmlspecialchars($driverDetails['first_name'] . ' ' . $driverDetails['last_name']); ?>" class="driver-image">
            <?php endif; ?>

            <form method="POST" action="">
                <input type="hidden" name="driver_id" value="<?php echo htmlspecialchars($driverDetails['driver_id']); ?>">

                <div class="form-group">
                    <label for="first_name">Voornaam:</label>
                    <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($driverDetails['first_name'] ?? ''); ?>" required readonly>
                </div>

                <div class="form-group">
                    <label for="last_name">Achternaam:</label>
                    <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($driverDetails['last_name'] ?? ''); ?>" required readonly>
                </div>

                <div class="form-group">
                    <label for="nationality">Nationaliteit:</label>
                    <input type="text" id="nationality" name="nationality" value="<?php echo htmlspecialchars($driverDetails['nationality'] ?? ''); ?>" required readonly>
                </div>

                <div class="form-group">
                    <label for="date_of_birth">Geboortedatum:</label>
                    <input type="date" id="date_of_birth" name="date_of_birth" value="<?php echo htmlspecialchars($driverDetails['date_of_birth'] ?? ''); ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="driver_number">Coureursnummer:</label>
                    <input type="number" id="driver_number" name="driver_number" value="<?php echo htmlspecialchars($driverDetails['driver_number'] ?? ''); ?>" min="1" max="99" readonly>
                </div>

                <div class="form-group">
                    <label for="team_name">Teamnaam (huidig):</label>
                    <select id="team_name" name="team_name" disabled>
                        <option value="">-- Geen Team --</option>
                        <?php foreach ($teams as $team): ?>
                            <option value="<?php echo htmlspecialchars($team); ?>"
                                <?php if (isset($driverDetails['team_name']) && $driverDetails['team_name'] === $team) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($team); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="championships_won">Kampioenschappen gewonnen:</label>
                    <input type="number" id="championships_won" name="championships_won" value="<?php echo htmlspecialchars($driverDetails['championships_won'] ?? 0); ?>" min="0" readonly>
                </div>

                <div class="form-group">
                    <label for="career_points">Carrièrepunten:</label>
                    <input type="number" id="career_points" name="career_points" value="<?php echo htmlspecialchars($driverDetails['career_points'] ?? 0.00); ?>" step="0.01" min="0" readonly>
                </div>

                <div class="form-group">
                    <label for="image">Image URL:</label>
                    <input type="text" id="image" name="image" value="<?php echo htmlspecialchars($driverDetails['image'] ?? ''); ?>" readonly>
                </div>

                <div class="form-group">
                    <input type="checkbox" id="is_active" name="is_active" <?php echo ($driverDetails['is_active'] ?? 0) ? 'checked' : ''; ?> disabled>
                    <label for="is_active">Momenteel actief?</label>
                </div>

                <div class="button-group">
                    <button type="button" id="editButton">Bewerk</button>
                    <button type="submit" id="saveButton">Opslaan</button>
                    <a href="drivers.php" class="back-link">Terug naar Overzicht</a>
                </div>
            </form>

        <?php else: ?>
            <p>De details van deze coureur konden niet worden geladen of de coureur bestaat niet.</p>
            <a href="drivers.php" class="back-link">Terug naar Overzicht</a>
        <?php endif; ?>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const editButton = document.getElementById('editButton');
            const saveButton = document.getElementById('saveButton');
            const inputs = form.querySelectorAll('input:not([type="hidden"]), select'); // Selecteer alle relevante velden

            // Functie om velden bewerkbaar te maken
            function setEditable(isEditable) {
                inputs.forEach(input => {
                    if (input.type === 'checkbox') {
                        input.disabled = !isEditable; // Checkboxen gebruiken 'disabled'
                    } else if (input.tagName === 'SELECT') {
                        input.disabled = !isEditable; // Selects gebruiken 'disabled'
                    } else {
                        input.readOnly = !isEditable; // Text/number/date inputs gebruiken 'readonly'
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

            // Initialiseer alle velden als niet-bewerkbaar bij het laden van de pagina
            setEditable(false);

            // Event listener voor de Bewerk knop
            editButton.addEventListener('click', function() {
                setEditable(true);
            });

            // Wanneer het formulier wordt ingediend (via de Opslaan knop),
            // zorgen we ervoor dat de velden niet langer disabled/readonly zijn,
            // zodat hun waarden worden meegestuurd naar de server.
            form.addEventListener('submit', function() {
                setEditable(true); // Maak alles bewerkbaar zodat waarden worden gepost
            });
        });
    </script>

</body>
</html>
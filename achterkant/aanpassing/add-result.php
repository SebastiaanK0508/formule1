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
$message = '';
$circuits = [];
$drivers = [];
$pointsSystem = []; // Variabele voor het puntensysteem

try {
    $pdo = new PDO($dsn, $user, $pass, $options);

    // Haal alle circuits op voor de dropdown (gesorteerd op kalendervolgorde)
    $stmt_circuits = $pdo->query("SELECT circuit_key, grandprix FROM circuits ORDER BY calendar_order ASC");
    $circuits = $stmt_circuits->fetchAll();

    // Haal alle coureurs op voor de dropdown
    $stmt_drivers = $pdo->query("SELECT driver_id, first_name, last_name FROM drivers ORDER BY last_name ASC");
    $drivers = $stmt_drivers->fetchAll();

    // Haal het puntensysteem op uit de database
    $stmt_points = $pdo->query("SELECT position, points FROM points_system ORDER BY position ASC");
    $pointsSystem = $stmt_points->fetchAll(PDO::FETCH_KEY_PAIR); // Haalt op als [positie => punten]

} catch (\PDOException $e) {
    die("Verbindingsfout: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Haal algemene racegegevens op
    $circuit_key = $_POST['circuit_key'] ?? '';
    $race_year = $_POST['race_year'] ?? '';
    $race_type = $_POST['race_type'] ?? '';

    // Haal de arrays met coureur-specifieke gegevens op
    $driver_ids = $_POST['driver_ids'] ?? [];
    $positions = $_POST['positions'] ?? []; // Dit is de vaste positie 1-20
    $laps_completed_arr = $_POST['laps_completed_arr'] ?? [];
    $finish_statuses = $_POST['finish_statuses'] ?? [];
    $awarded_points = $_POST['awarded_points'] ?? []; // De automatisch toegekende punten (verborgen veld)

    // Optionele velden die voor één coureur van toepassing zijn (bijv. snelste ronde, pole)
    $fastest_lap_time = $_POST['fastest_lap_time'] ?? null;
    $time_offset = $_POST['time_offset'] ?? null;
    $pole_position_driver_id = $_POST['pole_position_driver_id'] ?? null; // ID van de coureur met pole

    // Eenvoudige validatie voor algemene racegegevens
    if (empty($circuit_key) || empty($race_year) || empty($race_type)) {
        $message = "<p class='error-message'>Vul de algemene racegegevens (jaar, Grand Prix, type) in.</p>";
    } else {
        $resultsInserted = 0;
        $errors = [];

        // Loop door de ingediende coureurgegevens
        // We gebruiken de 'positions' array als basis, aangezien deze altijd 1-20 zal zijn
        foreach ($positions as $index => $pos) {
            $driver_id = $driver_ids[$index] ?? null;
            $position = (int)$pos; // De vaste positie (1-20)
            $laps_completed = $laps_completed_arr[$index] ?? null;
            $finish_status = $finish_statuses[$index] ?? null;
            $points = $awarded_points[$index] ?? 0.00;

            // Alleen invoegen als een coureur is geselecteerd voor deze positie
            if (!empty($driver_id) && $driver_id !== '0') { // '0' kan de waarde zijn voor 'Kies een coureur'
                try {
                    // Server-side validatie van punten: haal de punten opnieuw op uit het puntensysteem
                    // Dit voorkomt manipulatie via de client-side
                    $actual_points = $pointsSystem[$position] ?? 0.00;

                    // Controleer of deze coureur de pole position had
                    $is_pole_position = ($pole_position_driver_id == $driver_id) ? 1 : 0;

                    $sql = "INSERT INTO race_results (circuit_key, driver_id, race_year, race_type, position, points, laps_completed, finish_status, fastest_lap_time, time_offset, pole_position)
                            VALUES (:circuit_key, :driver_id, :race_year, :race_type, :position, :points, :laps_completed, :finish_status, :fastest_lap_time, :time_offset, :is_pole_position)";

                    $stmt = $pdo->prepare($sql);

                    $stmt->bindParam(':circuit_key', $circuit_key);
                    $stmt->bindParam(':driver_id', $driver_id, PDO::PARAM_INT);
                    $stmt->bindParam(':race_year', $race_year, PDO::PARAM_INT);
                    $stmt->bindParam(':race_type', $race_type);
                    $stmt->bindParam(':position', $position, PDO::PARAM_INT);
                    $stmt->bindParam(':points', $actual_points); // Gebruik de server-side berekende punten
                    $stmt->bindParam(':laps_completed', $laps_completed, PDO::PARAM_INT);
                    $stmt->bindParam(':finish_status', $finish_status);
                    $stmt->bindParam(':fastest_lap_time', $fastest_lap_time); // Deze wordt alleen voor de eerste coureur ingevoerd
                    $stmt->bindParam(':time_offset', $time_offset); // Deze wordt alleen voor de eerste coureur ingevoerd
                    $stmt->bindParam(':is_pole_position', $is_pole_position, PDO::PARAM_INT);

                    $stmt->execute();
                    $resultsInserted++;

                } catch (\PDOException $e) {
                    // Specifieke foutafhandeling voor unieke constraint (dubbele invoer)
                    if ($e->getCode() == 23000) {
                        $errors[] = "Uitslag voor coureur " . htmlspecialchars($driver_ids[$index]) . " op positie " . $position . " is al ingevoerd.";
                    } else {
                        $errors[] = "Databasefout voor coureur " . htmlspecialchars($driver_ids[$index]) . " op positie " . $position . ": " . $e->getMessage();
                    }
                }
            }
        }

        if ($resultsInserted > 0) {
            $message = "<p class='success-message'>" . $resultsInserted . " uitslag(en) succesvol toegevoegd!</p>";
        }
        if (!empty($errors)) {
            $message .= "<div class='error-message'><ul>";
            foreach ($errors as $error) {
                $message .= "<li>" . $error . "</li>";
            }
            $message .= "</ul></div>";
        }
        if ($resultsInserted == 0 && empty($errors)) {
             $message = "<p class='error-message'>Geen uitslagen ingevoerd. Zorg dat er minstens één coureur is geselecteerd.</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Race Uitslagen Toevoegen</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f4f4f4; color: #333; }
        .container { max-width: 900px; margin: 20px auto; background-color: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        h1 { color: #0056b3; text-align: center; margin-bottom: 30px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #555; }
        input[type="text"],
        input[type="number"],
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        input[type="checkbox"] {
            margin-right: 10px;
        }
        button[type="submit"] {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 20px;
            width: auto; /* Maak de knop niet 100% breed */
        }
        button[type="submit"]:hover { background-color: #0056b3; }
        .message { padding: 10px; margin-bottom: 20px; border-radius: 4px; }
        .success-message { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error-message { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

        .results-grid {
            display: grid;
            grid-template-columns: 50px 1fr 100px 120px 80px; /* Pos, Coureur, Ronden, Status, Punten */
            gap: 10px;
            margin-top: 20px;
            align-items: center;
        }
        .results-grid-header {
            font-weight: bold;
            padding-bottom: 5px;
            border-bottom: 1px solid #ccc;
        }
        .results-grid input, .results-grid select {
            width: 100%;
            padding: 5px;
            box-sizing: border-box;
        }
        .results-grid input[type="number"] {
            text-align: center;
        }
        .results-grid input[readonly] {
            background-color: #e9e9e9;
        }
        .results-grid .position-col {
            text-align: center;
            font-weight: bold;
        }
        .optional-fields {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Race Uitslagen Toevoegen</h1>
        <?php echo $message; ?>

        <form method="POST">
            <h2>Algemene Race Gegevens</h2>
            <div class="form-group">
                <label for="race_year">Race Jaar:</label>
                <input type="number" id="race_year" name="race_year" min="1950" max="<?php echo date('Y') + 1; ?>" value="<?php echo date('Y'); ?>" required>
            </div>

            <div class="form-group">
                <label for="circuit_key">Grand Prix:</label>
                <select id="circuit_key" name="circuit_key" required>
                    <option value="">-- Kies een Grand Prix --</option>
                    <?php foreach ($circuits as $circuit): ?>
                        <option value="<?php echo htmlspecialchars($circuit['circuit_key']); ?>">
                            <?php echo htmlspecialchars($circuit['grandprix']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="race_type">Type Race:</label>
                <select id="race_type" name="race_type" required>
                    <option value="Race">Hoofd Race</option>
                    <option value="Sprint">Sprint Race</option>
                </select>
            </div>

            <h2>Coureur Uitslagen (Top 20)</h2>
            <div class="results-grid">
                <div class="results-grid-header">Pos</div>
                <div class="results-grid-header">Coureur</div>
                <div class="results-grid-header">Ronden</div>
                <div class="results-grid-header">Status</div>
                <div class="results-grid-header">Punten</div>

                <?php for ($i = 1; $i <= 20; $i++): ?>
                    <div class="position-col"><?php echo $i; ?></div>
                    <input type="hidden" name="positions[]" value="<?php echo $i; ?>">
                    <div>
                        <select name="driver_ids[]" class="driver-select" data-position="<?php echo $i; ?>">
                            <option value="0">-- Kies coureur --</option>
                            <?php foreach ($drivers as $driver): ?>
                                <option value="<?php echo htmlspecialchars($driver['driver_id']); ?>">
                                    <?php echo htmlspecialchars($driver['first_name'] . ' ' . $driver['last_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <input type="number" name="laps_completed_arr[]" placeholder="Ronden" min="0">
                    </div>
                    <div>
                        <input type="text" name="finish_statuses[]" value="Finished" placeholder="Finished, DNF, etc.">
                    </div>
                    <div>
                        <input type="text" name="awarded_points[]" class="points-output" value="0.00" readonly>
                    </div>
                <?php endfor; ?>
            </div>

            <div class="optional-fields">
                <h2>Specifieke Race Details (Optioneel)</h2>
                <div class="form-group">
                    <label for="fastest_lap_time">Snelste Rondetijd (bijv. 1:23.456):</label>
                    <input type="text" id="fastest_lap_time" name="fastest_lap_time" placeholder="Bijv. 1:23.456">
                </div>

                <div class="form-group">
                    <label for="time_offset">Tijdverschil met Winnaar (bijv. +1.234s of 1 lap):</label>
                    <input type="text" id="time_offset" name="time_offset" placeholder="Bijv. +1.234s of 1 Lap">
                </div>

                <div class="form-group">
                    <label for="pole_position_driver_id">Coureur met Pole Position:</label>
                    <select id="pole_position_driver_id" name="pole_position_driver_id">
                        <option value="">-- Geen pole --</option>
                        <?php foreach ($drivers as $driver): ?>
                            <option value="<?php echo htmlspecialchars($driver['driver_id']); ?>">
                                <?php echo htmlspecialchars($driver['first_name'] . ' ' . $driver['last_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <button type="submit">Uitslagen Opslaan</button>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Punten systeem van PHP naar JavaScript
            const pointsSystem = <?php echo json_encode($pointsSystem); ?>;

            // Selecteer alle coureur-select elementen
            const driverSelects = document.querySelectorAll('.driver-select');

            driverSelects.forEach(selectElement => {
                selectElement.addEventListener('change', function() {
                    const position = parseInt(this.dataset.position); // Haal de positie op uit het data-attribuut
                    const pointsOutput = this.closest('div').nextElementSibling.nextElementSibling.querySelector('.points-output');

                    if (!isNaN(position) && position >= 1 && position <= 20) {
                        const awardedPoints = pointsSystem[position] !== undefined ? parseFloat(pointsSystem[position]) : 0.00;
                        pointsOutput.value = awardedPoints.toFixed(2); // Formatteer naar 2 decimalen
                    } else {
                        pointsOutput.value = '0.00'; // Reset naar 0 als ongeldige positie
                    }
                });

                // Trigger de change event bij laden om initiële punten in te vullen als er al een coureur geselecteerd is (niet van toepassing bij nieuwe form)
                // selectElement.dispatchEvent(new Event('change'));
            });
        });
    </script>
</body>
</html>

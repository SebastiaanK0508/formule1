<?php
require_once 'db_config.php';

$pdo = null;
$circuitDetails = null; // Voor de details van het circuit
$message = ''; // Voor succes- of foutmeldingen

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("Verbindingsfout: " . $e->getMessage());
}

// Haal de circuit_key op uit de URL (GET parameter)
$circuitKey = isset($_GET['key']) ? htmlspecialchars($_GET['key']) : null;

// Als het een POST-verzoek is, probeer dan de gegevens bij te werken
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $circuitKey) {
    try {
        $title = $_POST['title'] ?? '';
        $grandprix = $_POST['grandprix'] ?? '';
        $location = $_POST['location'] ?? '';
        $mapUrl = $_POST['map_url'] ?? null;
        $firstGpYear = $_POST['first_gp_year'] ?? null;
        $lapCount = $_POST['lap_count'] ?? null;
        $circuitLengthKm = $_POST['circuit_length_km'] ?? 0.000;
        $raceDistanceKm = $_POST['race_distance_km'] ?? 0.000;
        $lapRecord = $_POST['lap_record'] ?? null;
        $description = $_POST['description'] ?? null;
        $highlights = $_POST['highlights'] ?? null;
        $calendarOrder = $_POST['calendar_order'] ?? null; // Nieuwe kolom

        // --- UPDATE query voor circuits tabel ---
        $sql = "UPDATE circuits SET
                    title = :title,
                    grandprix = :grandprix,
                    location = :location,
                    map_url = :map_url,
                    first_gp_year = :first_gp_year,
                    lap_count = :lap_count,
                    circuit_length_km = :circuit_length_km,
                    race_distance_km = :race_distance_km,
                    lap_record = :lap_record,
                    description = :description,
                    highlights = :highlights,
                    calendar_order = :calendar_order -- Nieuwe kolom
                WHERE circuit_key = :circuit_key";

        $stmt = $pdo->prepare($sql);

        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':grandprix', $grandprix);
        $stmt->bindParam(':location', $location);
        $stmt->bindParam(':map_url', $mapUrl);
        $stmt->bindParam(':first_gp_year', $firstGpYear, PDO::PARAM_INT);
        $stmt->bindParam(':lap_count', $lapCount, PDO::PARAM_INT);
        $stmt->bindParam(':circuit_length_km', $circuitLengthKm);
        $stmt->bindParam(':race_distance_km', $raceDistanceKm);
        $stmt->bindParam(':lap_record', $lapRecord);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':highlights', $highlights);
        $stmt->bindParam(':calendar_order', $calendarOrder, PDO::PARAM_INT); // Nieuwe kolom
        $stmt->bindParam(':circuit_key', $circuitKey); // Gebruik de oorspronkelijke key voor de WHERE clausule

        if ($stmt->execute()) {
            $message = "<p class='success-message'>Circuitgegevens succesvol bijgewerkt!</p>";
            // Herlaad de circuitDetails na de update om de nieuwste gegevens te tonen
            $stmt = $pdo->prepare("SELECT * FROM circuits WHERE circuit_key = :circuit_key");
            $stmt->bindParam(':circuit_key', $circuitKey);
            $stmt->execute();
            $circuitDetails = $stmt->fetch();
        } else {
            $message = "<p class='error-message'>Fout bij het bijwerken van circuitgegevens.</p>";
        }

    } catch (\PDOException $e) {
        $message = "<p class='error-message'>Databasefout bij bijwerken: " . $e->getMessage() . "</p>";
    }
}

// Haal de circuit details op voor weergave (ook na een POST-update)
if ($circuitKey && !$circuitDetails) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM circuits WHERE circuit_key = :circuit_key");
        $stmt->bindParam(':circuit_key', $circuitKey);
        $stmt->execute();
        $circuitDetails = $stmt->fetch();

        if (!$circuitDetails) {
            $message = "<p class='error-message'>Circuit met sleutel " . htmlspecialchars($circuitKey) . " niet gevonden.</p>";
        }
    } catch (\PDOException $e) {
        $message = "<p class='error-message'>Fout bij het ophalen van circuitdetails: " . $e->getMessage() . "</p>";
    }
} else if (!$circuitKey) {
    $message = "<p class='error-message'>Geen geldige circuit-sleutel opgegeven.</p>";
}

// Zorg ervoor dat $circuitDetails een array is, zelfs als er geen data is
if (!is_array($circuitDetails)) {
    $circuitDetails = [];
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Details Circuit: <?php echo htmlspecialchars($circuitDetails['grandprix'] ?? 'Onbekend'); ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f4f4f4; color: #333; }
        .container { max-width: 800px; margin: 20px auto; background-color: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        h1 { color: #0056b3; text-align: center; margin-bottom: 30px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #555; }
        input[type="text"],
        input[type="number"],
        textarea { /* Ook textarea */
            width: calc(100% - 22px);
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            background-color: #e9e9e9; /* Standaard niet bewerkbaar */
        }
        input[type="number"][step="0.001"] { /* Specifieke stijl voor decimalen */
            width: calc(100% - 22px);
        }

        /* Styles voor bewerkbare velden */
        input[type="text"]:not([readonly]),
        input[type="number"]:not([readonly]),
        textarea:not([readonly]) {
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

        .circuit-map { max-width: 100%; height: auto; display: block; margin: 0 auto 20px auto; border-radius: 8px; }
        .message { padding: 10px; margin-bottom: 20px; border-radius: 4px; }
        .success-message { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error-message { background-color: #f8d7da; color: #721c24; border: 1px solid #721c24; }
    </style>
</head>
<body>

    <div class="container">
        <?php echo $message; // Toon succes- of foutmeldingen ?>

        <?php if ($circuitDetails && $circuitKey): ?>
            <h1>Details van Circuit: <?php echo htmlspecialchars($circuitDetails['grandprix'] ?? 'Onbekend'); ?></h1>

            <?php if (!empty($circuitDetails['map_url'])): ?>
                <img src="<?php echo htmlspecialchars($circuitDetails['map_url']); ?>" alt="Kaart van <?php echo htmlspecialchars($circuitDetails['grandprix']); ?>" class="circuit-map">
            <?php endif; ?>

            <form method="POST" action="">
                <input type="hidden" name="circuit_key_hidden" value="<?php echo htmlspecialchars($circuitDetails['circuit_key']); ?>">

                <div class="form-group">
                    <label for="title">Circuit Naam:</label>
                    <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($circuitDetails['title'] ?? ''); ?>" required readonly>
                </div>

                <div class="form-group">
                    <label for="grandprix">Grand Prix Naam:</label>
                    <input type="text" id="grandprix" name="grandprix" value="<?php echo htmlspecialchars($circuitDetails['grandprix'] ?? ''); ?>" required readonly>
                </div>

                <div class="form-group">
                    <label for="location">Locatie:</label>
                    <input type="text" id="location" name="location" value="<?php echo htmlspecialchars($circuitDetails['location'] ?? ''); ?>" required readonly>
                </div>

                <div class="form-group">
                    <label for="map_url">Kaart URL:</label>
                    <input type="text" id="map_url" name="map_url" value="<?php echo htmlspecialchars($circuitDetails['map_url'] ?? ''); ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="first_gp_year">Eerste GP Jaar:</label>
                    <input type="number" id="first_gp_year" name="first_gp_year" value="<?php echo htmlspecialchars($circuitDetails['first_gp_year'] ?? ''); ?>" min="1950" max="<?php echo date('Y'); ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="lap_count">Aantal Ronden:</label>
                    <input type="number" id="lap_count" name="lap_count" value="<?php echo htmlspecialchars($circuitDetails['lap_count'] ?? ''); ?>" min="1" readonly>
                </div>

                <div class="form-group">
                    <label for="circuit_length_km">Circuit Lengte (km):</label>
                    <input type="number" id="circuit_length_km" name="circuit_length_km" value="<?php echo htmlspecialchars($circuitDetails['circuit_length_km'] ?? 0.000); ?>" step="0.001" min="0" readonly>
                </div>

                <div class="form-group">
                    <label for="race_distance_km">Race Afstand (km):</label>
                    <input type="number" id="race_distance_km" name="race_distance_km" value="<?php echo htmlspecialchars($circuitDetails['race_distance_km'] ?? 0.000); ?>" step="0.001" min="0" readonly>
                </div>

                <div class="form-group">
                    <label for="lap_record">Ronderecord:</label>
                    <input type="text" id="lap_record" name="lap_record" value="<?php echo htmlspecialchars($circuitDetails['lap_record'] ?? ''); ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="description">Beschrijving:</label>
                    <textarea id="description" name="description" rows="5" readonly><?php echo htmlspecialchars($circuitDetails['description'] ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="highlights">Highlights (komma-gescheiden):</label>
                    <textarea id="highlights" name="highlights" rows="3" readonly><?php echo htmlspecialchars($circuitDetails['highlights'] ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="calendar_order">Kalender Volgorde:</label>
                    <input type="number" id="calendar_order" name="calendar_order" value="<?php echo htmlspecialchars($circuitDetails['calendar_order'] ?? ''); ?>" min="1" readonly>
                </div>

                <div class="button-group">
                    <button type="button" id="editButton">Bewerk</button>
                    <button type="submit" id="saveButton">Opslaan</button>
                    <a href="schedule.php" class="back-link">Terug naar Kalender</a>
                </div>
            </form>

        <?php else: ?>
            <p>De details van dit circuit konden niet worden geladen of het circuit bestaat niet.</p>
            <a href="schedule.php" class="back-link">Terug naar Kalender</a>
        <?php endif; ?>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const editButton = document.getElementById('editButton');
            const saveButton = document.getElementById('saveButton');
            // Selecteer alle relevante velden
            const inputs = form.querySelectorAll('input:not([type="hidden"]), textarea'); // Geen select elementen hier

            // Functie om velden bewerkbaar te maken
            function setEditable(isEditable) {
                inputs.forEach(input => {
                    input.readOnly = !isEditable; // Text/number/date inputs en textareas gebruiken 'readonly'
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
            // zorgen we ervoor dat de velden niet langer readonly zijn,
            // zodat hun waarden worden meegestuurd naar de server.
            form.addEventListener('submit', function() {
                setEditable(true); // Maak alles bewerkbaar zodat waarden worden gepost
            });
        });
    </script>

</body>
</html>
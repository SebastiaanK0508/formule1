<?php
require_once 'db_config.php';
/** @var PDO $pdo */ 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $circuitKey = $_POST['circuit_key'] ?? '';
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
        $calendarOrder = $_POST['calendar_order'] ?? null;
        $raceDatetime = $_POST['race_datetime'] ?? null;

        if (empty($circuitKey) || empty($title) || empty($grandprix) || empty($location) || empty($calendarOrder) || empty($raceDatetime)) {
            $message = "<p class='error-message'>Vul alle verplichte velden in (Circuit Sleutel, Naam, Grand Prix, Locatie, Kalender Volgorde, Race Datum/Tijd).</p>";
        } else {

            $sql = "INSERT INTO circuits (
                        circuit_key, title, grandprix, location, map_url, first_gp_year,
                        lap_count, circuit_length_km, race_distance_km, lap_record, description,
                        highlights, calendar_order, race_datetime
                    ) VALUES (
                        :circuit_key, :title, :grandprix, :location, :map_url, :first_gp_year,
                        :lap_count, :circuit_length_km, :race_distance_km, :lap_record, :description,
                        :highlights, :calendar_order, :race_datetime
                    )";

            $stmt = $pdo->prepare($sql);

            $stmt->bindParam(':circuit_key', $circuitKey);
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
            $stmt->bindParam(':calendar_order', $calendarOrder, PDO::PARAM_INT);
            $stmt->bindParam(':race_datetime', $raceDatetime);

            if ($stmt->execute()) {
                $message = "<p class='success-message'>Circuit succesvol toegevoegd!</p>";
                $_POST = array(); 
            } else {
                $message = "<p class='error-message'>Fout bij het toevoegen van het circuit.</p>";
            }
        }
    } catch (\PDOException $e) {
        if ($e->getCode() == 23000) { 
            $message = "<p class='error-message'>Fout: De circuit sleutel '" . htmlspecialchars($circuitKey) . "' bestaat al. Kies een unieke sleutel.</p>";
        } else {
            $message = "<p class='error-message'>Databasefout bij toevoegen: " . $e->getMessage() . "</p>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nieuw Circuit Toevoegen</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Nieuw Circuit Toevoegen</h1>
        <?php echo $message;?>
        <form method="POST" action="">
            <div class="form-group">
                <label for="circuit_key">Circuit Sleutel (uniek, bijv. 'zandvoort'):</label>
                <input type="text" id="circuit_key" name="circuit_key" value="<?php echo htmlspecialchars($_POST['circuit_key'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="title">Circuit Naam:</label>
                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="grandprix">Grand Prix Naam:</label>
                <input type="text" id="grandprix" name="grandprix" value="<?php echo htmlspecialchars($_POST['grandprix'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="location">Locatie:</label>
                <input type="text" id="location" name="location" value="<?php echo htmlspecialchars($_POST['location'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="map_url">Kaart URL:</label>
                <input type="text" id="map_url" name="map_url" value="<?php echo htmlspecialchars($_POST['map_url'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="first_gp_year">Eerste GP Jaar:</label>
                <input type="number" id="first_gp_year" name="first_gp_year" value="<?php echo htmlspecialchars($_POST['first_gp_year'] ?? ''); ?>" min="1950" max="<?php echo date('Y') + 1; ?>">
            </div>
            <div class="form-group">
                <label for="lap_count">Aantal Ronden:</label>
                <input type="number" id="lap_count" name="lap_count" value="<?php echo htmlspecialchars($_POST['lap_count'] ?? ''); ?>" min="1">
            </div>
            <div class="form-group">
                <label for="circuit_length_km">Circuit Lengte (km):</label>
                <input type="number" id="circuit_length_km" name="circuit_length_km" value="<?php echo htmlspecialchars($_POST['circuit_length_km'] ?? ''); ?>" step="0.001" min="0">
            </div>
            <div class="form-group">
                <label for="race_distance_km">Race Afstand (km):</label>
                <input type="number" id="race_distance_km" name="race_distance_km" value="<?php echo htmlspecialchars($_POST['race_distance_km'] ?? ''); ?>" step="0.001" min="0">
            </div>
            <div class="form-group">
                <label for="lap_record">Ronderecord:</label>
                <input type="text" id="lap_record" name="lap_record" value="<?php echo htmlspecialchars($_POST['lap_record'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="description">Beschrijving:</label>
                <textarea id="description" name="description" rows="5"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
            </div>
            <div class="form-group">
                <label for="highlights">Highlights (komma-gescheiden):</label>
                <textarea id="highlights" name="highlights" rows="3"><?php echo htmlspecialchars($_POST['highlights'] ?? ''); ?></textarea>
            </div>
            <div class="form-group">
                <label for="calendar_order">Kalender Volgorde:</label>
                <input type="number" id="calendar_order" name="calendar_order" value="<?php echo htmlspecialchars($_POST['calendar_order'] ?? ''); ?>" min="1" required>
            </div>
            <div class="form-group">
                <label for="race_datetime">Race Datum & Tijd:</label>
                <input type="datetime-local" id="race_datetime" name="race_datetime" value="<?php echo htmlspecialchars($_POST['race_datetime'] ?? ''); ?>" required>
            </div>
            <div class="button-group">
                <button type="submit" id="addButton">Circuit Toevoegen</button>
                <a href="../schedule.php" class="back-link">Terug naar Kalender</a>
            </div>
        </form>
    </div>

</body>
</html>

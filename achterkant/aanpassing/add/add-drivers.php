<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Webbair Framework</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../style.css">
    <link rel="icon" href="" type="image/x-icon">
</head>
<body>
    <header class="main-header">
        <div class="header-title">
            <h1>Formula 1 site - Add Drivers</h1>
        </div>
    </header>
    <main class="main-content-area"> 
        <section class="main-content-panel">
            <div class="headerhoofdpagina">
                <h2 class="main-title">Drivers</h2>
            </div>
            <div>
                <a href="../drivers.php"><button class="adddriverbutton">Back</button></a>
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
                <input type="number" id="driver_number" name="driver_number" min="1" max="999" class="">
            </div>

            <div>
                <label for="team_name" class="block text-sm font-medium text-gray-700 mb-1">Teamnaam (huidig):</label>
                <select class="" name="team_name" id="team_name">
                <option value="" disabled selected>--select--</option>
                <option value="mcLaren">McLaren</option>
                <option value="ferrari">Ferrari</option>
                <option value="redbull">Oracle Red Bull Racing</option>
                <option value="mercedes">Mercedes-AMG Petronas F1 Team</option>
                <option value="alpine">Alpine</option>
                <option value="aston martin">Aston Martin Aramco F1 Team</option>
                <option value="vcarb">Visa Cash App RB</option>
                <option value="williams">Williams</option>
                <option value="haas">Haas</option>
                <option value="sauber">Stake Sauber F1</option>
                <option value="nvt">N.V.T.</option>
                </select>
            </div>

            <div>
                <label for="championships_won" class="block text-sm font-medium text-gray-700 mb-1">Kampioenschappen gewonnen:</label>
                <input type="number" id="championships_won" name="championships_won" min="0" value="0" class="">
            </div>

            <div>
                <label for="career_points" class="block text-sm font-medium text-gray-700 mb-1">Carrièrepunten:</label>
                <input type="number" id="career_points" name="career_points" step="0.5" min="0" value="0.0" class="">
            </div>

            <div class="flex items-center">
                <input type="checkbox" id="is_active" name="is_active" checked class="">
                <label for="is_active" class="">Momenteel actief?</label>
            </div>

            <div>
                <button type="submit" class="adddriverbutton">Coureur Toevoegen</button>
            </div>
        </form>
            </div>
        </section>
    </main>

    <script>
        fetch("https://api.openf1.org/v1/drivers?driver_number=1&session_key=9158")
  .then((response) => response.json())
  .then((jsonContent) => console.log(jsonContent));
    </script>
</body>
</html>

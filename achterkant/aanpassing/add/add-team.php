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
            <h1>Formula 1 site - Team Toevoegen</h1>
        </div>
    </header>
    <main class="main-content-area">
        <section class="main-content-panel">
            <div class="headerhoofdpagina">
                <h2 class="main-title">Add new Team</h2>
            </div>
            <div>
                <a href="../teams.php"><button class="adddriverbutton">Back to Teams</button></a>
            </div>
                <div>
                    <form id="teamForm" class="space-y-4" action="add-teams-connect.php" method="POST">
                        <!-- Team Name -->
                        <div>
                            <label for="team_name" class="block text-sm font-medium text-gray-700 mb-1">Team Naam <span class="text-red-500">*</span></label>
                            <input type="text" id="team_name" name="team_name" required
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>

                        <!-- Team Color -->
                        <div>
                            <label for="team_color" class="block text-sm font-medium text-gray-700 mb-1">Team Kleur</label>
                            <input type="color" id="team_color" name="team_color" value="#000000"
                                class="mt-1 block w-full h-10 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <!-- Full Team Name -->
                        <div>
                            <label for="full_team_name" class="block text-sm font-medium text-gray-700 mb-1">Volledige Team Naam</label>
                            <input type="text" id="full_team_name" name="full_team_name"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>

                        <!-- Base Location -->
                        <div>
                            <label for="base_location" class="block text-sm font-medium text-gray-700 mb-1">Basis Locatie</label>
                            <input type="text" id="base_location" name="base_location"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>

                        <!-- Team Principal -->
                        <div>
                            <label for="team_principal" class="block text-sm font-medium text-gray-700 mb-1">Team Principal</label>
                            <input type="text" id="team_principal" name="team_principal"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>

                        <!-- Technical Director -->
                        <div>
                            <label for="technical_director" class="block text-sm font-medium text-gray-700 mb-1">Technisch Directeur</label>
                            <input type="text" id="technical_director" name="technical_director"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>

                        <!-- Championships Won -->
                        <div>
                            <label for="championships_won" class="block text-sm font-medium text-gray-700 mb-1">Kampioenschappen Gewonnen</label>
                            <input type="number" id="championships_won" name="championships_won" value="0" min="0"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>

                        <!-- First Entry Year -->
                        <div>
                            <label for="first_entry_year" class="block text-sm font-medium text-gray-700 mb-1">Eerste Deelname Jaar</label>
                            <input type="number" id="first_entry_year" name="first_entry_year" min="1900" max="2100"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>

                        <!-- Website URL -->
                        <div>
                            <label for="website_url" class="block text-sm font-medium text-gray-700 mb-1">Website URL</label>
                            <input type="url" NULL id="website_url" name="website_url" placeholder="https://www.example.com"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>

                        <!-- Logo URL -->
                        <div>
                            <label for="logo_url" class="block text-sm font-medium text-gray-700 mb-1">Logo URL</label>
                            <input type="url" NULL id="logo_url" name="logo_url" placeholder="https://www.example.com/logo.png"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>

                        <!-- Current Engine Supplier -->
                        <div>
                            <label for="current_engine_supplier" class="block text-sm font-medium text-gray-700 mb-1">Huidige Motorleverancier</label>
                            <input type="text" id="current_engine_supplier" name="current_engine_supplier"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>

                        <!-- Is Active -->
                        <div class="flex items-center">
                            <input type="checkbox" id="is_active" name="is_active" checked
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="is_active" class="ml-2 block text-sm font-medium text-gray-700">Actief</label>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit"
                                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150 ease-in-out">
                            Team Opslaan
                        </button>
                    </form>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
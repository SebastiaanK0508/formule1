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
    <!--formulier werkt nog niet!!-->
    <header class="main-header">
        <div class="header-title">
            <h1>Formula 1 site - Nieuws Toevoegen</h1>
        </div>
    </header>
    <main class="main-content-area">
        <section class="main-content-panel">
            <div class="headerhoofdpagina">
                <h2 class="main-title">Nieuw bericht Toevoegen</h2>
            </div>
            <div>
                <a href="../news.php"><button class="adddriverbutton">Terug naar nieuws Overzicht</button></a>
            </div>
            <div>
                <?php echo $message; // Toon status- of foutmeldingen ?>
                <form action="add-news-connect.php" method="POST" class="">
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Titel:</label>
                        <input type="text" id="title" name="title" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 p-2">
                    </div>
                    <div>
                        <label for="news_content" class="block text-sm font-medium text-gray-700 mb-1 mt-4">Nieuwscontent:</label>
                        <textarea id="news_content" name="news_content" required rows="5" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 p-2"></textarea>
                    </div>
                    <div>
                        <label for="image_url" class="block text-sm font-medium text-gray-700 mb-1 mt-4">Afbeeldings URL (optioneel):</label>
                        <input type="text" id="image_url" name="image_url" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 p-2">
                    </div>
                    <div>
                        <label for="keywords" class="block text-sm font-medium text-gray-700 mb-1 mt-4">Steekwoorden:</label>
                        <input type="text" id="keywords" name="keywords" placeholder="woorden afscheiden met een komma" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 p-2">
                    </div>
                    <div>
                        <label for="source" class="block text-sm font-medium text-gray-700 mb-1 mt-4">Bron:</label>
                        <input type="text" id="source" name="source" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 p-2">
                    </div>
                    <div>
                        <label for="date" class="block text-sm font-medium text-gray-700 mb-1 mt-4">Datum (optioneel):</label>
                        <input type="date" id="date" name="date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 p-2">
                    </div>
                    <div class="mt-6">
                        <button type="submit" class="adddriverbutton bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md">Nieuws Toevoegen</button>
                    </div>
                </form>
            </div>
        </section>
    </main>
</body>
</html>

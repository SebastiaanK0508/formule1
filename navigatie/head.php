<?php
$current_page = basename($_SERVER['PHP_SELF']);

$server = $_SERVER['SERVER_NAME'];
$requestUri = $_SERVER['REQUEST_URI'];

if ($server === 'localhost' || $server === '127.0.0.1') {
    $baseUrl = "http://localhost:8080/formule1/";
} else {
    $baseUrl = "https://f1site.nl/";
}
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?php echo isset($page_title) ? $page_title . " | F1SITE.NL" : "F1SITE.NL | Het laatste Formule 1 nieuws & statistieken"; ?></title>
    <meta name="description" content="Blijf op de hoogte van het laatste Formule 1 nieuws, uitslagen, standen en de volledige racekalender. Volg de actie op de voet bij F1SITE.NL.">
    <meta name="keywords" content="Formule 1, F1, Max Verstappen, Grand Prix, F1 uitslagen, F1 kalender, F1 stand">
    <meta name="author" content="Webius">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo $baseUrl; ?>">
    <meta property="og:title" content="F1SITE.NL - Alles over Formule 1">
    <meta property="og:description" content="Check de laatste resultaten en standen in het F1 kampioenschap.">
    <meta property="og:image" content="<?php echo $baseUrl; ?>afbeeldingen/logo/f1_icon.png">
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:title" content="F1SITE.NL - Alles over Formule 1">
    <meta property="twitter:description" content="Check de laatste resultaten en standen in het F1 kampioenschap.">
    <link rel="icon" type="image/x-icon" href="../afbeeldingen/logo/f1_icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../afbeeldingen/logo/f1_icon.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../afbeeldingen/logo/f1_icon.png">
    <link rel="apple-touch-icon" sizes="180x180" href="../afbeeldingen/logo/f1_icon.png">
    <link rel="manifest" href="/site.webmanifest">    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&family=Oswald:wght@400;700;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>     
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { 'sans': ['Inter', 'sans-serif'], 'oswald': ['Oswald', 'sans-serif'] },
                    colors: { 'f1-red': '#E10600', 'f1-dark': '#0b0b0f', 'f1-card': '#16161c' }
                }
            }
        }
    </script>
    <style>
        body { background-color: #0b0b0f; color: #fff; overflow-x: hidden; }
        .bg-pattern {
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
        .header-glass { background: rgba(11, 11, 15, 0.9); backdrop-filter: blur(15px); border-bottom: 1px solid rgba(225, 6, 0, 0.3); }
        .f1-border { position: relative; }
        .f1-border::before { content: ""; position: absolute; top: 0; left: 0; width: 45px; height: 4px; background: #E10600; z-index: 10; }
        
        #mobile-menu { transform: translateX(100%); transition: transform 0.4s ease-in-out; background: #0b0b0f; z-index: 101; }
        #mobile-menu.active { transform: translateX(0); }
    </style>
    <base href="<?php echo $baseUrl; ?>">
</head>
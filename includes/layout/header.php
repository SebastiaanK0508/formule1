<?php
/**
 * Page header. Expects an optional $page array:
 *   title, description, nav, canonical, image, charts (bool), schema (array), noindex (bool), body_class
 */
declare(strict_types=1);

$page = ($page ?? []) + [
    'title'       => null,
    'description' => 'Formula 1 results, standings, race calendar, driver and team profiles, statistics and the latest news.',
    'nav'         => '',
    'canonical'   => null,
    'image'       => url('assets/img/og-image.png'),
    'charts'      => false,
    'schema'      => null,
    'noindex'     => false,
    'body_class'  => '',
];
// Read flash messages before any output: starting a session needs to send a cookie header.
$flashes = take_flashes();
$siteName = 'F1SITE.NL';
$fullTitle = $page['title'] ? $page['title'] . ' | ' . $siteName : $siteName . ' — Formula 1 results, standings & stats';
$canonical = $page['canonical'] ?? url(
    current_page() === 'index.php' ? '' : current_page(),
    array_intersect_key($_GET, array_flip(['year', 'round', 'slug', 'id', 'key', 'page', 'd1', 'd2']))
);

$navItems = [
    'home'      => ['index.php', 'Home'],
    'schedule'  => ['kalender.php', 'Schedule'],
    'results'   => ['results.php', 'Results'],
    'standings' => ['standings.php', 'Standings'],
    'stats'     => ['stats.php', 'Stats'],
    'drivers'   => ['drivers.php', 'Drivers'],
    'teams'     => ['teams.php', 'Teams'],
    'compare'   => ['compare.php', 'Compare'],
    'quiz'      => ['quiz.php', 'Quiz'],
    'news'      => ['nieuws.php', 'News'],
];
$live = live_session();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($fullTitle) ?></title>
    <meta name="description" content="<?= e($page['description']) ?>">
    <link rel="canonical" href="<?= e($canonical) ?>">
    <?php if ($page['noindex']): ?><meta name="robots" content="noindex"><?php endif; ?>
    <meta property="og:site_name" content="<?= e($siteName) ?>">
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?= e($page['title'] ?? $siteName) ?>">
    <meta property="og:description" content="<?= e($page['description']) ?>">
    <meta property="og:url" content="<?= e($canonical) ?>">
    <meta property="og:image" content="<?= e($page['image']) ?>">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="theme-color" content="#0c0d10">
    <link rel="icon" href="<?= e(url('afbeeldingen/logo/f1_icon.png')) ?>" type="image/png">
    <link rel="apple-touch-icon" href="<?= e(url('afbeeldingen/logo/f1_icon.png')) ?>">
    <script>
        (function () {
            var t; try { t = JSON.parse(localStorage.getItem('theme')); } catch (e) {}
            if (t !== 'light' && t !== 'dark') t = matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'dark';
            document.documentElement.dataset.theme = t;
        })();
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@600;700;800&family=Oswald:wght@700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(asset('assets/css/app.css')) ?>">
    <script src="<?= e(asset('assets/js/app.js')) ?>" defer></script>
    <?php if ($page['charts']): ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js" defer></script>
    <?php endif; ?>
    <?php if ($page['schema']): ?>
    <script type="application/ld+json"><?= json_encode($page['schema'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?></script>
    <?php endif; ?>
</head>
<body class="<?= e($page['body_class']) ?>" data-ga="<?= e((string)config('ga_id', '')) ?>">
<a class="skip-link" href="#main">Skip to content</a>
<?php if ($live): ?>
<div class="live-bar" role="status">
    <div class="container">
        <span class="live-dot"></span>
        <strong><?= $live['started'] ? 'LIVE' : 'STARTING SOON' ?></strong>
        <span><?= e($live['race']['grandprix']) ?> — <?= e($live['label']) ?><?= $live['started'] ? ' is underway' : ' starts at ' . local_time($live['at'], 'time') ?></span>
        <a href="<?= e(url('circuit-details.php', ['key' => $live['race']['circuit_key'], 'year' => $live['race']['race_year']])) ?>">Weekend schedule</a>
    </div>
</div>
<?php endif; ?>

<header class="site-header">
    <div class="container header-inner">
        <a class="brand" href="<?= e(url()) ?>" aria-label="F1SITE.NL home">F1SITE<span>.NL</span></a>
        <nav class="main-nav" aria-label="Main">
            <?php foreach ($navItems as $key => [$href, $label]): ?>
                <a href="<?= e(url($href === 'index.php' ? '' : $href)) ?>"<?= $page['nav'] === $key ? ' aria-current="page"' : '' ?>><?= e($label) ?></a>
            <?php endforeach; ?>
        </nav>
        <div class="header-actions">
            <form class="header-search" action="<?= e(url('zoek.php')) ?>" method="get" role="search">
                <?= icon('search') ?>
                <label class="sr-only" for="q-desktop">Search</label>
                <input id="q-desktop" type="search" name="q" placeholder="Search…" value="<?= e($_GET['q'] ?? '') ?>" data-palette-open autocomplete="off">
                <span class="kbd" data-kbd>⌘K</span>
            </form>
            <a class="icon-btn search-btn-mobile" href="<?= e(url('zoek.php')) ?>" aria-label="Search" data-palette-open><?= icon('search') ?></a>
            <button class="icon-btn theme-toggle" type="button" data-theme-toggle aria-label="Toggle light/dark theme">
                <?= icon('moon', 'icon icon-moon') ?><?= icon('sun', 'icon icon-sun') ?>
            </button>
            <button class="icon-btn menu-toggle" type="button" data-drawer-open aria-controls="drawer" aria-expanded="false" aria-label="Open menu"><?= icon('menu') ?></button>
        </div>
    </div>
</header>

<div class="drawer" id="drawer" aria-hidden="true">
    <div class="drawer-backdrop" data-drawer-close></div>
    <div class="drawer-panel" role="dialog" aria-modal="true" aria-label="Menu">
        <div class="drawer-head">
            <a class="brand" href="<?= e(url()) ?>">F1SITE<span>.NL</span></a>
            <button class="icon-btn" type="button" data-drawer-close aria-label="Close menu"><?= icon('close') ?></button>
        </div>
        <form action="<?= e(url('zoek.php')) ?>" method="get" role="search">
            <label class="sr-only" for="q-mobile">Search</label>
            <input class="input" id="q-mobile" type="search" name="q" placeholder="Search…">
        </form>
        <nav class="drawer-nav" aria-label="Mobile">
            <?php foreach ($navItems as $key => [$href, $label]): ?>
                <a href="<?= e(url($href === 'index.php' ? '' : $href)) ?>"<?= $page['nav'] === $key ? ' aria-current="page"' : '' ?>><?= e($label) ?><?= icon('chevron-right', 'icon-sm') ?></a>
            <?php endforeach; ?>
        </nav>
    </div>
</div>

<div class="palette" id="palette" data-index="<?= e(url('api/search.php')) ?>" data-search-page="<?= e(url('zoek.php')) ?>" role="dialog" aria-modal="true" aria-label="Quick search">
    <div class="palette-box">
        <div class="palette-input"><?= icon('search') ?><input type="text" placeholder="Jump to a driver, team, race or page…" aria-label="Quick search" autocomplete="off" spellcheck="false"><span class="kbd">Esc</span></div>
        <ul class="palette-list" role="listbox"></ul>
        <div class="palette-foot"><span><span class="kbd">↑</span> <span class="kbd">↓</span> navigate</span><span><span class="kbd">↵</span> open</span><span><span class="kbd">Enter</span> on empty list searches everything</span></div>
    </div>
</div>

<main id="main">
<?php foreach ($flashes as $flash): ?>
    <div class="container mt-4"><div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div></div>
<?php endforeach; ?>

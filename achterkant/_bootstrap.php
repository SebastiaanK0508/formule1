<?php
/**
 * Admin bootstrap: loads the application, starts a hardened session and
 * provides authentication, layout and form helpers for the admin panel.
 */
declare(strict_types=1);

require dirname(__DIR__) . '/includes/bootstrap.php';
require INC_DIR . '/tasks.php';

start_session('f1admin');
header('X-Frame-Options: DENY');
header('X-Robots-Tag: noindex, nofollow');
header('Cache-Control: no-store');

const ADMIN_IDLE_TIMEOUT = 4 * 3600;

/** URL inside the admin panel. */
function admin_url(string $path = '', array $query = []): string
{
    $base = (string)config('admin_url', '');
    $base = $base !== '' ? rtrim($base, '/') . '/' : base_url() . 'achterkant/';
    $query = array_filter($query, static fn($v) => $v !== null && $v !== '');
    return $base . ltrim($path, '/') . ($query ? '?' . http_build_query($query) : '');
}

function is_logged_in(): bool
{
    if (empty($_SESSION['admin_id'])) {
        return false;
    }
    if (time() - (int)($_SESSION['admin_seen'] ?? 0) > ADMIN_IDLE_TIMEOUT) {
        $_SESSION = [];
        session_regenerate_id(true);
        return false;
    }
    $_SESSION['admin_seen'] = time();
    return true;
}

function require_login(): void
{
    if (!is_logged_in()) {
        redirect(admin_url('index.php', ['next' => $_SERVER['REQUEST_URI'] ?? null]));
    }
}

/** For state-changing requests: require POST + valid CSRF token. */
function require_post(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_valid()) {
        http_response_code(400);
        exit('Ongeldige aanvraag. Ga terug en probeer het opnieuw.');
    }
}

/** Convert a datetime-local value (Dutch time) to a UTC database value. */
function admin_to_utc(?string $local): ?string
{
    if (!$local) {
        return null;
    }
    try {
        return (new DateTimeImmutable($local, new DateTimeZone('Europe/Amsterdam')))->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');
    } catch (Exception) {
        return null;
    }
}

/** Convert a UTC database value to a datetime-local value (Dutch time). */
function admin_from_utc(?string $utc): string
{
    $dt = db_datetime($utc);
    return $dt ? $dt->setTimezone(new DateTimeZone('Europe/Amsterdam'))->format('Y-m-d\TH:i') : '';
}

function admin_header(string $title, string $active = ''): void
{
    $unread = (int)db_value('SELECT COUNT(*) FROM contact WHERE is_read = 0');
    $nav = [
        'dashboard' => ['dashboard.php', 'Dashboard', 'home'],
        'circuits'  => ['list.php?type=circuits', 'Kalender', 'calendar'],
        'drivers'   => ['list.php?type=drivers', 'Coureurs', 'users'],
        'teams'     => ['list.php?type=teams', 'Teams', 'shield'],
        'news'      => ['list.php?type=news', 'Nieuws', 'newspaper'],
        'messages'  => ['messages.php', 'Berichten', 'mail'],
        'tools'     => ['tools.php', 'Onderhoud', 'settings'],
    ];
    ?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($title) ?> · F1SITE beheer</title>
    <link rel="icon" href="<?= e(url('afbeeldingen/logo/f1_icon.png')) ?>" type="image/png">
    <script>
        (function () { var t; try { t = JSON.parse(localStorage.getItem('theme')); } catch (e) {}
        if (t !== 'light' && t !== 'dark') t = matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'dark';
        document.documentElement.dataset.theme = t; })();
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@600;700;800&family=Oswald:wght@700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(asset('assets/css/app.css')) ?>">
    <link rel="stylesheet" href="<?= e(admin_url('assets/admin.css')) ?>?v=<?= (int)@filemtime(__DIR__ . '/assets/admin.css') ?>">
    <script src="<?= e(asset('assets/js/app.js')) ?>" defer></script>
</head>
<body class="admin">
<?php if (is_logged_in()): ?>
<div class="admin-shell">
    <aside class="admin-side" id="admin-side">
        <a class="brand" href="<?= e(admin_url('dashboard.php')) ?>">F1SITE<span>.NL</span></a>
        <div class="text-xs subtle" style="margin: 2px 0 20px">Beheer</div>
        <nav class="admin-nav">
            <?php foreach ($nav as $key => [$href, $label, $ico]): ?>
                <a href="<?= e(admin_url($href)) ?>"<?= $active === $key ? ' aria-current="page"' : '' ?>>
                    <?= icon($ico) ?><span><?= e($label) ?></span>
                    <?php if ($key === 'messages' && $unread): ?><span class="badge badge-red" style="margin-left:auto"><?= $unread ?></span><?php endif; ?>
                </a>
            <?php endforeach; ?>
        </nav>
        <div class="admin-side-foot">
            <a class="btn btn-ghost btn-sm w-full" href="<?= e(url()) ?>" target="_blank" rel="noopener"><?= icon('external', 'icon-sm') ?> Bekijk website</a>
            <form method="post" action="<?= e(admin_url('logout.php')) ?>">
                <?= csrf_field() ?>
                <button class="btn btn-ghost btn-sm w-full" type="submit"><?= icon('logout', 'icon-sm') ?> Uitloggen (<?= e($_SESSION['admin_name'] ?? '') ?>)</button>
            </form>
            <button class="btn btn-ghost btn-sm w-full" type="button" data-theme-toggle><?= icon('moon', 'icon-sm') ?> Thema wisselen</button>
        </div>
    </aside>
    <div class="admin-main">
        <header class="admin-top">
            <button class="icon-btn admin-menu" type="button" onclick="document.getElementById('admin-side').classList.toggle('is-open')" aria-label="Menu"><?= icon('menu') ?></button>
            <h1><?= e($title) ?></h1>
        </header>
        <main class="admin-content">
            <?php foreach (take_flashes() as $f): ?>
                <div class="alert alert-<?= e($f['type']) ?> mb-4"><?= icon($f['type'] === 'success' ? 'check' : 'alert') ?><span><?= e($f['message']) ?></span></div>
            <?php endforeach; ?>
<?php else: ?>
<main>
<?php endif;
}

function admin_footer(): void
{
    if (is_logged_in()): ?>
        </main>
    </div>
</div>
<?php else: ?>
</main>
<?php endif; ?>
</body>
</html>
<?php
}

/** Delete button that posts with CSRF protection and asks for confirmation. */
function delete_form(string $action, array $hidden, string $label = 'Verwijderen', string $confirm = 'Weet je zeker dat je dit wilt verwijderen?'): string
{
    $html = '<form method="post" action="' . e($action) . '" onsubmit="return confirm(\'' . e(addslashes($confirm)) . '\')" style="display:inline">' . csrf_field();
    foreach ($hidden as $k => $v) {
        $html .= '<input type="hidden" name="' . e($k) . '" value="' . e($v) . '">';
    }
    return $html . '<button class="btn btn-ghost btn-sm" type="submit" style="color: var(--danger)">' . icon('trash', 'icon-sm') . ' ' . e($label) . '</button></form>';
}

<?php
declare(strict_types=1);
require __DIR__ . '/_bootstrap.php';

$next = (string)($_GET['next'] ?? $_POST['next'] ?? '');
// Only allow redirects back into the admin panel.
$safeNext = static fn(string $n): string => preg_match('#^/(?![/\\\\])#', $n) ? $n : admin_url('dashboard.php');

if (is_logged_in()) {
    redirect(admin_url('dashboard.php'));
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $attempts = $_SESSION['login_attempts'] ?? [];
    $attempts = array_filter($attempts, static fn($t) => $t > time() - 900);
    if (count($attempts) >= 5) {
        $error = 'Te veel mislukte pogingen. Probeer het over 15 minuten opnieuw.';
    } elseif (!csrf_valid()) {
        $error = 'Je sessie is verlopen. Probeer het opnieuw.';
    } else {
        $user = trim((string)($_POST['username'] ?? ''));
        $pass = (string)($_POST['password'] ?? '');
        $admin = $user !== '' ? db_one('SELECT id, username, password_hash FROM admin_users WHERE username = ? LIMIT 1', [$user]) : null;
        // Always run password_verify to keep response times uniform.
        $valid = password_verify($pass, $admin['password_hash'] ?? '$2y$12$invalidinvalidinvalidinvalidinvalidinvalidinvalidinvali');
        if ($admin && $valid) {
            if (password_needs_rehash($admin['password_hash'], PASSWORD_DEFAULT)) {
                db()->prepare('UPDATE admin_users SET password_hash = ? WHERE id = ?')->execute([password_hash($pass, PASSWORD_DEFAULT), $admin['id']]);
            }
            session_regenerate_id(true);
            $_SESSION = ['admin_id' => (int)$admin['id'], 'admin_name' => $admin['username'], 'admin_seen' => time()];
            redirect($safeNext($next));
        }
        $attempts[] = time();
        usleep(400_000);
        $error = 'Onjuiste gebruikersnaam of wachtwoord.';
    }
    $_SESSION['login_attempts'] = $attempts;
}

admin_header('Inloggen');
?>
<div class="login-wrap">
    <form class="card card-pad login-card stack" method="post" action="<?= e(admin_url('index.php')) ?>">
        <div>
            <span class="brand">F1SITE<span>.NL</span></span>
            <p class="muted text-sm mt-1">Log in om de website te beheren.</p>
        </div>
        <?php if ($error): ?><div class="alert alert-error"><?= icon('alert') ?><span><?= e($error) ?></span></div><?php endif; ?>
        <?= csrf_field() ?>
        <input type="hidden" name="next" value="<?= e($next) ?>">
        <div class="field"><label for="username">Gebruikersnaam</label><input class="input" id="username" name="username" autocomplete="username" required autofocus></div>
        <div class="field"><label for="password">Wachtwoord</label><input class="input" id="password" type="password" name="password" autocomplete="current-password" required></div>
        <button class="btn btn-primary w-full" type="submit">Inloggen</button>
    </form>
</div>
<?php admin_footer();

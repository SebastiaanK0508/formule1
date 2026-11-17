<?php
// Create or reset an admin account: php scripts/create-admin.php <username>
// The password is asked interactively and stored as a bcrypt/argon hash.
declare(strict_types=1);
require __DIR__ . '/_cli.php';

$user = trim((string)($argv[1] ?? ''));
if ($user === '' || !preg_match('/^[A-Za-z0-9_.\-]{3,50}$/', $user)) {
    fwrite(STDERR, "Usage: php scripts/create-admin.php <username>\n");
    exit(1);
}
echo 'Password (min. 12 characters): ';
system('stty -echo 2>/dev/null');
$pass = trim((string)fgets(STDIN));
system('stty echo 2>/dev/null');
echo PHP_EOL;
if (strlen($pass) < 12) {
    fwrite(STDERR, "Password too short.\n");
    exit(1);
}
db()->prepare('INSERT INTO admin_users (username, password_hash) VALUES (?, ?) ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash)')
    ->execute([$user, password_hash($pass, PASSWORD_DEFAULT)]);
cli_log(["Admin account '$user' saved."]);

<?php
/** Shared setup for command-line scripts: refuse web requests, load the app. */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Forbidden');
}
require dirname(__DIR__) . '/includes/bootstrap.php';
require INC_DIR . '/tasks.php';

// Report crashes of cron jobs to the private Discord channel (if configured).
set_exception_handler(static function (Throwable $e): void {
    $script = basename($_SERVER['argv'][0] ?? 'script');
    fwrite(STDERR, $e->getMessage() . PHP_EOL);
    error_log("$script failed: " . $e);
    discord_admin("⚠️ Cron job failed: $script", '```' . mb_substr($e->getMessage(), 0, 1500) . '```', [
        ['name' => 'Location', 'value' => basename($e->getFile()) . ':' . $e->getLine(), 'inline' => true],
    ]);
    exit(1);
});

function cli_log(array $lines): void
{
    foreach ($lines as $line) {
        echo '[' . date('Y-m-d H:i:s') . '] ' . $line . PHP_EOL;
    }
}

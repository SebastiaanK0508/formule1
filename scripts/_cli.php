<?php
/** Shared setup for command-line scripts: refuse web requests, load the app. */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Forbidden');
}
require dirname(__DIR__) . '/includes/bootstrap.php';
require INC_DIR . '/tasks.php';

function cli_log(array $lines): void
{
    foreach ($lines as $line) {
        echo '[' . date('Y-m-d H:i:s') . '] ' . $line . PHP_EOL;
    }
}

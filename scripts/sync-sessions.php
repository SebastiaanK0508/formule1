<?php
// Cron (daily) or manual: php scripts/sync-sessions.php [year]
declare(strict_types=1);
require __DIR__ . '/_cli.php';
cli_log(task_sync_sessions((int)($argv[1] ?? date('Y'))));

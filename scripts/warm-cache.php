<?php
// Cron (hourly, optional): php scripts/warm-cache.php — keeps API data cached for fast first page loads.
declare(strict_types=1);
require __DIR__ . '/_cli.php';
cli_log(task_warm_cache());

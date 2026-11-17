<?php
// Cron (daily): php scripts/prune-news.php [days=5]
declare(strict_types=1);
require __DIR__ . '/_cli.php';
cli_log(task_prune_news(max(1, (int)($argv[1] ?? 5))));

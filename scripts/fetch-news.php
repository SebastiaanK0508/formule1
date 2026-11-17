<?php
// Cron (e.g. every 2 hours): php scripts/fetch-news.php
declare(strict_types=1);
require __DIR__ . '/_cli.php';
cli_log(task_fetch_news());

<?php
// Cron (every 5 minutes): php scripts/discord-notify.php [--dry-run]
// Posts new results, weekend previews, session reminders and champions to Discord.
declare(strict_types=1);
require __DIR__ . '/_cli.php';
cli_log(discord_notify(in_array('--dry-run', $argv, true)));

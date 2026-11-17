<?php
/** Quick-search index for the ⌘K palette (small, cached by the browser). */
declare(strict_types=1);
require dirname(__DIR__) . '/includes/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: public, max-age=3600');
echo json_encode(search_index(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

<?php
/**
 * Application bootstrap. Every public page, API endpoint, admin page and CLI
 * script starts by requiring this file.
 */
declare(strict_types=1);

if (defined('F1_BOOTSTRAPPED')) {
    return;
}
define('F1_BOOTSTRAPPED', true);

define('ROOT_DIR', dirname(__DIR__));
define('INC_DIR', __DIR__);
define('STORAGE_DIR', ROOT_DIR . '/storage');
define('CACHE_DIR', STORAGE_DIR . '/cache');
define('DATA_DIR', ROOT_DIR . '/data');

/**
 * Read a configuration value using dot notation, e.g. config('db.host').
 * Environment variables (F1_DB_HOST, F1_BASE_URL, ...) override the file.
 */
function config(string $key, mixed $default = null): mixed
{
    static $config = null;
    if ($config === null) {
        $file = ROOT_DIR . '/config.local.php';
        $config = is_file($file) ? (array)require $file : [];
    }

    $env = getenv('F1_' . strtoupper(str_replace('.', '_', $key)));
    if ($env !== false && $env !== '') {
        return $env;
    }

    $value = $config;
    foreach (explode('.', $key) as $part) {
        if (!is_array($value) || !array_key_exists($part, $value)) {
            return $default;
        }
        $value = $value[$part];
    }
    return $value;
}

date_default_timezone_set((string)config('timezone', 'Europe/Amsterdam'));

$debug = filter_var(config('debug', false), FILTER_VALIDATE_BOOLEAN);
define('APP_DEBUG', $debug);
error_reporting(E_ALL);
ini_set('display_errors', $debug ? '1' : '0');
ini_set('log_errors', '1');

if (!is_dir(CACHE_DIR)) {
    @mkdir(CACHE_DIR, 0775, true);
}

require INC_DIR . '/helpers.php';
require INC_DIR . '/db.php';
require INC_DIR . '/http.php';
require INC_DIR . '/f1data.php';

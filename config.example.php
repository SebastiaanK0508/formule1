<?php
/**
 * F1SITE.NL configuration template.
 *
 * Copy this file to `config.local.php` (which is git-ignored) and fill in the
 * real values. Every key can also be supplied through an environment variable
 * (shown on the right), which takes precedence over this file.
 */
return [
    'db' => [
        'host'    => 'localhost',          // F1_DB_HOST
        'name'    => 'formule1',           // F1_DB_NAME
        'user'    => 'webuser',            // F1_DB_USER
        'pass'    => 'change-me',          // F1_DB_PASS
        'charset' => 'utf8mb4',
    ],

    // Public URL of the site, with trailing slash. Leave empty to auto-detect.
    'base_url'       => '',                // F1_BASE_URL

    // Public URL of the admin panel (achterkant), with trailing slash.
    'admin_url'      => '',                // F1_ADMIN_URL

    // Google Analytics 4 measurement id. Only loaded after analytics consent.
    'ga_id'          => '',                // F1_GA_ID

    // https://newsapi.org key used by scripts/fetch-news.php
    'newsapi_key'    => '',                // F1_NEWSAPI_KEY

    // Show PHP errors in the browser. Never enable this in production.
    'debug'          => false,             // F1_DEBUG

    'timezone'       => 'Europe/Amsterdam',
];

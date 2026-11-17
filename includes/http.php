<?php
/**
 * Cached HTTP client for external JSON APIs (Jolpica/Ergast, Open-Meteo, ...).
 *
 * - Fresh cache hits never touch the network.
 * - When a request fails, stale cache is served instead of an error.
 * - Failures are remembered briefly so a down API does not slow every page.
 * - Consecutive network calls are spaced out to respect API rate limits.
 */
declare(strict_types=1);

const HTTP_USER_AGENT = 'F1SITE.NL/2.0 (+https://f1site.nl)';
const HTTP_FAIL_BACKOFF = 120; // seconds

function http_json(string $url, int $ttl, ?string $cacheKey = null): ?array
{
    static $memo = [];
    static $lastNetworkCall = 0.0;
    static $warnedNotWritable = false;

    if (!$warnedNotWritable && !cache_writable()) {
        $warnedNotWritable = true;
        error_log('Cache directory is not writable: ' . CACHE_DIR . ' — every request goes to the API and will hit its rate limit. Fix the permissions of storage/.');
    }

    $key = $cacheKey !== null ? preg_replace('/[^a-z0-9_\-]/i', '_', $cacheKey) : 'url_' . sha1($url);
    if (array_key_exists($key, $memo)) {
        return $memo[$key];
    }

    $file = CACHE_DIR . '/' . $key . '.json';
    $failFile = $file . '.fail';
    $age = is_file($file) ? time() - (int)filemtime($file) : PHP_INT_MAX;

    if ($age < $ttl) {
        $data = json_decode((string)file_get_contents($file), true);
        if (is_array($data)) {
            return $memo[$key] = $data;
        }
    }

    $stale = static function () use ($file): ?array {
        if (!is_file($file)) {
            return null;
        }
        $data = json_decode((string)file_get_contents($file), true);
        return is_array($data) ? $data : null;
    };

    if (is_file($failFile) && time() - (int)filemtime($failFile) < HTTP_FAIL_BACKOFF) {
        return $memo[$key] = $stale();
    }
    // After a rate-limit response, leave the whole host alone for a while.
    // Remembered in memory too, so it works even when the cache is not writable.
    $host = (string)parse_url($url, PHP_URL_HOST);
    $limitFile = CACHE_DIR . '/.ratelimit_' . preg_replace('/[^a-z0-9.]/i', '', $host);
    if (http_rate_limited($host) || (is_file($limitFile) && time() - (int)filemtime($limitFile) < 90)) {
        return $memo[$key] = $stale();
    }

    // Space out live requests (Jolpica allows ~4 requests per second).
    $since = microtime(true) - $lastNetworkCall;
    $gap = PHP_SAPI === 'cli' ? 0.6 : 0.3;
    if ($since < $gap) {
        usleep((int)(($gap - $since) * 1_000_000));
    }
    $lastNetworkCall = microtime(true);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS      => 3,
        CURLOPT_CONNECTTIMEOUT => 3,
        CURLOPT_TIMEOUT        => 8,
        CURLOPT_USERAGENT      => HTTP_USER_AGENT,
        CURLOPT_HTTPHEADER     => ['Accept: application/json'],
        CURLOPT_ENCODING       => '',
    ]);
    $body = curl_exec($ch);
    $status = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);

    $data = ($body !== false && $status === 200) ? json_decode((string)$body, true) : null;
    if (!is_array($data)) {
        if ($status === 429) {
            http_rate_limited($host, true);
            error_log("Rate limited by $host (HTTP 429): pausing requests to this host for 90 seconds.");
            @touch($limitFile);
        } else {
            error_log("HTTP fetch failed ($status): $url");
            @touch($failFile);
        }
        return $memo[$key] = $stale();
    }

    $tmp = $file . '.' . bin2hex(random_bytes(4)) . '.tmp';
    if (@file_put_contents($tmp, (string)$body) !== false) {
        @rename($tmp, $file);
    }
    @unlink($failFile);
    return $memo[$key] = $data;
}

/** Whether a host rate-limited us during this request; pass $set = true to record it. */
function http_rate_limited(string $host, bool $set = false): bool
{
    static $hosts = [];
    if ($set) {
        $hosts[$host] = true;
    }
    return isset($hosts[$host]);
}

/** Whether the API cache can be written (if not, every page load hits the APIs). */
function cache_writable(): bool
{
    static $ok = null;
    return $ok ??= is_dir(CACHE_DIR) && is_writable(CACHE_DIR);
}

/** Remove cached API responses. Returns the number of files deleted. */
function http_cache_clear(): int
{
    $count = 0;
    foreach (glob(CACHE_DIR . '/*') ?: [] as $file) {
        if (is_file($file) && basename($file) !== '.htaccess' && basename($file) !== '.gitkeep' && @unlink($file)) {
            $count++;
        }
    }
    return $count;
}

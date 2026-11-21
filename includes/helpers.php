<?php
/**
 * Generic helpers: escaping, URLs, formatting, sessions/CSRF and icons.
 */
declare(strict_types=1);

/** HTML-escape a value for output. */
function e(mixed $value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Absolute base URL of the public site, always with a trailing slash. */
function base_url(): string
{
    static $base = null;
    if ($base !== null) {
        return $base;
    }
    $configured = (string)config('base_url', '');
    if ($configured !== '') {
        return $base = rtrim($configured, '/') . '/';
    }
    if (PHP_SAPI === 'cli') {
        return $base = 'https://f1site.nl/';
    }
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    // Derive the sub-directory the app lives in from this file's location.
    $docRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '') ?: '';
    $path = '/';
    if ($docRoot !== '' && str_starts_with(ROOT_DIR, $docRoot)) {
        $path = '/' . trim(substr(ROOT_DIR, strlen($docRoot)), '/') . '/';
        $path = str_replace('//', '/', $path);
    }
    return $base = ($https ? 'https' : 'http') . '://' . $host . $path;
}

/** Build an absolute URL to a page, with optional query parameters. */
function url(string $path = '', array $query = []): string
{
    $query = array_filter($query, static fn($v) => $v !== null && $v !== '');
    $qs = $query ? '?' . http_build_query($query) : '';
    return base_url() . ltrim($path, '/') . $qs;
}

/** URL to a static asset with a cache-busting version parameter. */
function asset(string $path): string
{
    $file = ROOT_DIR . '/' . ltrim($path, '/');
    $v = is_file($file) ? (string)filemtime($file) : '1';
    return base_url() . ltrim($path, '/') . '?v=' . $v;
}

function current_page(): string
{
    return basename($_SERVER['SCRIPT_NAME'] ?? '');
}

function redirect(string $to, int $status = 302): never
{
    header('Location: ' . $to, true, $status);
    exit;
}

/** Turn any text into a URL slug: "Max Verstappen" → "max-verstappen". */
function slugify(string $text): string
{
    $text = trim($text);
    $ascii = function_exists('transliterator_transliterate')
        ? (string)transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', $text)
        : strtolower((string)iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text));
    $ascii = preg_replace('/[^a-z0-9]+/', '-', strtolower($ascii)) ?? '';
    return trim($ascii, '-');
}

function driver_slug(array $driver): string
{
    return slugify(($driver['first_name'] ?? '') . ' ' . ($driver['last_name'] ?? ''));
}

function driver_url(array $driver): string
{
    return url('driver-details.php', ['slug' => driver_slug($driver)]);
}

function team_url(array $team): string
{
    return url('team-details.php', ['id' => (int)$team['team_id']]);
}

function race_url(int|string $year, int|string $round): string
{
    return url('race_result.php', ['year' => (int)$year, 'round' => (int)$round]);
}

/** Return a safe #rrggbb colour (falls back when the input is not a hex colour). */
function safe_color(?string $hex, string $fallback = '#6b7280'): string
{
    $hex = trim((string)$hex);
    if (preg_match('/^#?([0-9a-f]{6})$/i', $hex, $m)) {
        return '#' . strtolower($m[1]);
    }
    if (preg_match('/^#?([0-9a-f])([0-9a-f])([0-9a-f])$/i', $hex, $m)) {
        return '#' . strtolower($m[1] . $m[1] . $m[2] . $m[2] . $m[3] . $m[3]);
    }
    return $fallback;
}

/** Parse a datetime stored in the database (stored in UTC). */
function db_datetime(?string $value): ?DateTimeImmutable
{
    if ($value === null || $value === '' || str_starts_with($value, '0000-00-00')) {
        return null;
    }
    try {
        return new DateTimeImmutable($value, new DateTimeZone('UTC'));
    } catch (Exception) {
        return null;
    }
}

/** Parse an Ergast date + optional time ("2026-03-08", "04:00:00Z") in UTC. */
function api_datetime(?string $date, ?string $time = null): ?DateTimeImmutable
{
    if (!$date) {
        return null;
    }
    try {
        return new DateTimeImmutable($date . ' ' . ($time ? rtrim($time, 'Z') : '12:00:00'), new DateTimeZone('UTC'));
    } catch (Exception) {
        return null;
    }
}

/** Format a date in the site timezone. */
function fmt_date(DateTimeInterface|string|null $date, string $format = 'j M Y'): string
{
    if ($date === null || $date === '') {
        return '–';
    }
    if (is_string($date)) {
        try {
            $date = new DateTimeImmutable($date);
        } catch (Exception) {
            return '–';
        }
    }
    $local = DateTimeImmutable::createFromInterface($date)->setTimezone(new DateTimeZone(date_default_timezone_get()));
    return $local->format($format);
}

/**
 * Render a <time> element that JavaScript converts to the visitor's own
 * timezone. The server-side fallback uses the site timezone.
 *
 * @param string $style one of "datetime", "date", "time", "weekday-time"
 */
function local_time(?DateTimeInterface $dt, string $style = 'datetime'): string
{
    if (!$dt) {
        return '<span class="muted">TBC</span>';
    }
    $formats = ['datetime' => 'D j M, H:i', 'date' => 'j M Y', 'time' => 'H:i', 'weekday-time' => 'D H:i'];
    $iso = DateTimeImmutable::createFromInterface($dt)->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d\TH:i:s\Z');
    return '<time datetime="' . e($iso) . '" data-local="' . e($style) . '">' . e(fmt_date($dt, $formats[$style] ?? $formats['datetime'])) . '</time>';
}

function fmt_number(float|int|string|null $n, int $decimals = 0): string
{
    if ($n === null || $n === '') {
        return '–';
    }
    $n = (float)$n;
    if ($decimals === 0 && floor($n) != $n) {
        $decimals = 1;
    }
    return number_format($n, $decimals, '.', ',');
}

function age_from(?string $dob): ?int
{
    if (!$dob) {
        return null;
    }
    try {
        return (new DateTimeImmutable($dob))->diff(new DateTimeImmutable('today'))->y;
    } catch (Exception) {
        return null;
    }
}

/** CSS class for podium positions. */
function pos_class(int|string|null $pos): string
{
    return match ((string)$pos) {
        '1' => 'pos pos-1',
        '2' => 'pos pos-2',
        '3' => 'pos pos-3',
        default => 'pos',
    };
}

/** Clamp an integer request parameter. */
function int_param(string $name, int $default, int $min = PHP_INT_MIN, int $max = PHP_INT_MAX): int
{
    $raw = $_GET[$name] ?? null;
    if ($raw === null || $raw === '' || !preg_match('/^-?\d+$/', (string)$raw)) {
        return $default;
    }
    return max($min, min($max, (int)$raw));
}

function str_param(string $name, string $default = ''): string
{
    $raw = $_GET[$name] ?? $default;
    return is_string($raw) ? trim($raw) : $default;
}

/* ---------------------------------------------------------------------------
 * Sessions, CSRF and flash messages
 * ------------------------------------------------------------------------ */

function start_session(string $name = 'f1site'): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    session_name($name);
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function csrf_token(): string
{
    start_session();
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function csrf_valid(): bool
{
    start_session();
    $sent = $_POST['_csrf'] ?? '';
    return is_string($sent) && !empty($_SESSION['_csrf']) && hash_equals($_SESSION['_csrf'], $sent);
}

function flash(string $type, string $message): void
{
    start_session();
    $_SESSION['_flash'][] = ['type' => $type, 'message' => $message];
}

/** @return list<array{type:string,message:string}> */
function take_flashes(string $name = 'f1site'): array
{
    if (session_status() !== PHP_SESSION_ACTIVE && !isset($_COOKIE[$name])) {
        return [];
    }
    if (session_status() !== PHP_SESSION_ACTIVE && headers_sent()) {
        return []; // too late to start a session; the flash survives until the next page
    }
    start_session($name);
    $flashes = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);
    return $flashes;
}

/* ---------------------------------------------------------------------------
 * Icons (inline SVG, stroke-based, 24x24)
 * ------------------------------------------------------------------------ */

function icon(string $name, string $class = 'icon'): string
{
    static $paths = [
        'arrow-right'   => '<path d="M5 12h14M13 6l6 6-6 6"/>',
        'arrow-left'    => '<path d="M19 12H5M11 18l-6-6 6-6"/>',
        'chevron-down'  => '<path d="m6 9 6 6 6-6"/>',
        'chevron-left'  => '<path d="m15 18-6-6 6-6"/>',
        'chevron-right' => '<path d="m9 18 6-6-6-6"/>',
        'menu'          => '<path d="M4 7h16M4 12h16M4 17h16"/>',
        'close'         => '<path d="M6 6l12 12M18 6 6 18"/>',
        'search'        => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>',
        'sun'           => '<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/>',
        'moon'          => '<path d="M20 14.5A8 8 0 0 1 9.5 4 8 8 0 1 0 20 14.5z"/>',
        'star'          => '<path d="m12 3 2.8 5.7 6.2.9-4.5 4.4 1 6.2L12 17.3 6.5 20.2l1-6.2L3 9.6l6.2-.9z"/>',
        'calendar'      => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/>',
        'map-pin'       => '<path d="M12 21s-7-6.1-7-11a7 7 0 0 1 14 0c0 4.9-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/>',
        'clock'         => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'flag'          => '<path d="M5 21V4M5 4h11l-2 4 2 4H5"/>',
        'trophy'        => '<path d="M8 4h8v5a4 4 0 0 1-8 0zM8 6H5a3 3 0 0 0 3 4M16 6h3a3 3 0 0 1-3 4M12 13v4M8 21h8M9 17h6"/>',
        'timer'         => '<circle cx="12" cy="13" r="8"/><path d="M12 9v4l2 2M10 2h4"/>',
        'bolt'          => '<path d="M13 2 4 14h7l-1 8 9-12h-7z"/>',
        'users'         => '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20a6.5 6.5 0 0 1 13 0M16 4.5a3.5 3.5 0 0 1 0 7M18 14a6 6 0 0 1 3.5 6"/>',
        'external'      => '<path d="M14 4h6v6M20 4l-9 9M18 14v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h5"/>',
        'download'      => '<path d="M12 4v11M7 10l5 5 5-5M5 20h14"/>',
        'cloud'         => '<path d="M7 18a4 4 0 1 1 .6-7.96A5.5 5.5 0 0 1 18 11a3.5 3.5 0 0 1-.5 7z"/>',
        'mail'          => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>',
        'info'          => '<circle cx="12" cy="12" r="9"/><path d="M12 11v5M12 8h.01"/>',
        'check'         => '<path d="m5 12 5 5 9-10"/>',
        'alert'         => '<path d="M12 3 2 20h20zM12 10v4M12 17h.01"/>',
        'newspaper'     => '<path d="M4 5h13v14a1 1 0 0 0 1 1H6a2 2 0 0 1-2-2zM17 9h3v9a2 2 0 0 1-2 2M8 9h5M8 13h5M8 17h3"/>',
        'chart'         => '<path d="M4 20V4M4 20h16M8 16v-5M12 16V8M16 16v-3"/>',
        'swap'          => '<path d="M7 7h13M16 3l4 4-4 4M17 17H4M8 13l-4 4 4 4"/>',
        'shield'        => '<path d="M12 3 4 6v6c0 5 3.5 8 8 9 4.5-1 8-4 8-9V6z"/>',
        'grid'          => '<rect x="4" y="4" width="7" height="7" rx="1"/><rect x="13" y="4" width="7" height="7" rx="1"/><rect x="4" y="13" width="7" height="7" rx="1"/><rect x="13" y="13" width="7" height="7" rx="1"/>',
        'list'          => '<path d="M9 6h11M9 12h11M9 18h11M4 6h.01M4 12h.01M4 18h.01"/>',
        'edit'          => '<path d="M4 20h4L19 9l-4-4L4 16zM14 6l4 4"/>',
        'trash'         => '<path d="M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3"/>',
        'plus'          => '<path d="M12 5v14M5 12h14"/>',
        'refresh'       => '<path d="M20 11a8 8 0 0 0-14.5-4.5L4 8M4 4v4h4M4 13a8 8 0 0 0 14.5 4.5L20 16M20 20v-4h-4"/>',
        'logout'        => '<path d="M15 4h4a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1h-4M10 17l-5-5 5-5M5 12h11"/>',
        'home'          => '<path d="M4 11 12 4l8 7M6 9.5V20h12V9.5"/>',
        'settings'      => '<circle cx="12" cy="12" r="3"/><path d="M19 12a7 7 0 0 0-.1-1.2l2-1.6-2-3.4-2.4 1a7 7 0 0 0-2-1.2L14 3h-4l-.5 2.6a7 7 0 0 0-2 1.2l-2.4-1-2 3.4 2 1.6a7 7 0 0 0 0 2.4l-2 1.6 2 3.4 2.4-1a7 7 0 0 0 2 1.2L10 21h4l.5-2.6a7 7 0 0 0 2-1.2l2.4 1 2-3.4-2-1.6c.07-.4.1-.8.1-1.2z"/>',
        'eye'           => '<path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/>',
    ];
    $svg = $paths[$name] ?? $paths['info'];
    return '<svg class="' . e($class) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $svg . '</svg>';
}

/** Render a full 404 page and stop. */
function abort_404(string $message = 'The page you are looking for does not exist.'): never
{
    http_response_code(404);
    $notFoundMessage = $message;
    require ROOT_DIR . '/404.php';
    exit;
}

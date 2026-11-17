<?php
/**
 * iCalendar feed of all sessions in a season. Subscribe in Google Calendar,
 * Apple Calendar or Outlook: https://f1site.nl/calendar.ics.php
 */
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$years = db_season_years();
$year = int_param('year', in_array(current_season(), $years, true) ? current_season() : ($years[0] ?? current_season()), FIRST_SEASON, current_season() + 1);
$races = db_calendar($year);

$durations = ['Practice 1' => 60, 'Practice 2' => 60, 'Practice 3' => 60, 'Sprint Qualifying' => 45, 'Sprint' => 60, 'Qualifying' => 60, 'Race' => 120];
$escape = static fn(string $s): string => addcslashes(str_replace(["\r\n", "\n"], '\n', $s), ',;\\');
$fold = static function (string $line): string {
    $out = '';
    while (strlen($line) > 74) {
        $cut = 74;
        while ($cut > 0 && (ord($line[$cut]) & 0xC0) === 0x80) { $cut--; } // keep UTF-8 sequences intact
        $out .= substr($line, 0, $cut) . "\r\n ";
        $line = substr($line, $cut);
    }
    return $out . $line;
};

$lines = [
    'BEGIN:VCALENDAR',
    'VERSION:2.0',
    'PRODID:-//F1SITE.NL//Formula 1 calendar//EN',
    'CALSCALE:GREGORIAN',
    'METHOD:PUBLISH',
    "X-WR-CALNAME:Formula 1 $year · F1SITE.NL",
    'X-WR-TIMEZONE:UTC',
    'REFRESH-INTERVAL;VALUE=DURATION:PT12H',
    'X-PUBLISHED-TTL:PT12H',
];
$stamp = gmdate('Ymd\THis\Z');
foreach ($races as $r) {
    foreach (weekend_sessions($r) as $s) {
        $start = $s['at'];
        $end = $start->modify('+' . ($durations[$s['label']] ?? 60) . ' minutes');
        $lines[] = 'BEGIN:VEVENT';
        $lines[] = 'UID:' . $year . '-' . $r['circuit_key'] . '-' . $s['key'] . '@f1site.nl';
        $lines[] = 'DTSTAMP:' . $stamp;
        $lines[] = 'DTSTART:' . $start->format('Ymd\THis\Z');
        $lines[] = 'DTEND:' . $end->format('Ymd\THis\Z');
        $lines[] = $fold('SUMMARY:' . $escape('F1 ' . str_replace(' Grand Prix', ' GP', $r['grandprix']) . ' – ' . $s['label']));
        $lines[] = $fold('LOCATION:' . $escape($r['title'] . ', ' . $r['location']));
        $lines[] = $fold('URL:' . url('circuit-details.php', ['key' => $r['circuit_key'], 'year' => $year]));
        $lines[] = 'END:VEVENT';
    }
}
$lines[] = 'END:VCALENDAR';

header('Content-Type: text/calendar; charset=utf-8');
header('Content-Disposition: inline; filename="f1-' . $year . '.ics"');
header('Cache-Control: public, max-age=3600');
echo implode("\r\n", $lines) . "\r\n";

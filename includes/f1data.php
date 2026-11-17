<?php
/**
 * Formula 1 data layer.
 *
 * - Results, standings and history come from the Jolpica mirror of the
 *   Ergast API (https://api.jolpi.ca/ergast/f1/), cached on disk.
 * - The race calendar, session times, teams and drivers come from our own
 *   database, which is managed in the admin panel (achterkant).
 *
 * API rows are normalised into flat arrays and linked to database rows so
 * pages can show team colours, photos and profile links.
 */
declare(strict_types=1);

const ERGAST_BASE = 'https://api.jolpi.ca/ergast/f1/';
const FIRST_SEASON = 1950;

/* ===========================================================================
 * Seasons & cache lifetimes
 * ======================================================================== */

function current_season(): int
{
    return (int)date('Y');
}

/** True during a race weekend (Thursday → Monday), when data changes often. */
function is_race_weekend(): bool
{
    static $active = null;
    if ($active === null) {
        $active = (bool)db_value(
            'SELECT 1 FROM circuits WHERE race_datetime BETWEEN UTC_TIMESTAMP() - INTERVAL 30 HOUR AND UTC_TIMESTAMP() + INTERVAL 80 HOUR LIMIT 1'
        );
    }
    return $active;
}

/** Cache lifetime for data of a given season. */
function season_ttl(int $year): int
{
    if ($year < current_season()) {
        return 30 * 86400;
    }
    return is_race_weekend() ? 15 * 60 : 6 * 3600;
}

/* ===========================================================================
 * Raw Ergast access
 * ======================================================================== */

/** Fetch one Ergast endpoint and return its MRData block. */
function ergast(string $path, int $ttl, array $query = []): ?array
{
    $qs = $query ? '?' . http_build_query($query) : '';
    $url = ERGAST_BASE . trim($path, '/') . '.json' . $qs;
    $key = 'ergast_' . trim((string)preg_replace('/[^a-z0-9]+/i', '_', $path . '_' . http_build_query($query)), '_');
    $data = http_json($url, $ttl, $key);
    return $data['MRData'] ?? null;
}

/**
 * Fetch a paged RaceTable endpoint completely (Jolpica returns max 100 rows
 * per request) and merge the pages into one list of races keyed by round.
 */
function ergast_race_table(string $path, int $ttl, string $listKey): array
{
    $races = [];
    $offset = 0;
    $guard = 0;
    do {
        $data = ergast($path, $ttl, ['limit' => 100, 'offset' => $offset]);
        if (!$data) {
            break;
        }
        $total = (int)($data['total'] ?? 0);
        foreach ($data['RaceTable']['Races'] ?? [] as $race) {
            $round = (int)$race['round'];
            if (!isset($races[$round])) {
                $races[$round] = $race;
                $races[$round][$listKey] = [];
            }
            foreach ($race[$listKey] ?? [] as $row) {
                $races[$round][$listKey][] = $row;
            }
        }
        $offset += 100;
    } while ($offset < $total && ++$guard < 20);
    ksort($races);
    return $races;
}

/* ===========================================================================
 * Normalisers
 * ======================================================================== */

function f1_norm_driver(array $d): array
{
    $d['givenName'] = trim((string)($d['givenName'] ?? ''));
    $d['familyName'] = trim((string)($d['familyName'] ?? ''));
    return [
        'driverId'        => $d['driverId'] ?? '',
        'code'            => $d['code'] ?? strtoupper(substr($d['familyName'] ?? '', 0, 3)),
        'givenName'       => $d['givenName'] ?? '',
        'familyName'      => $d['familyName'] ?? '',
        'name'            => trim(($d['givenName'] ?? '') . ' ' . ($d['familyName'] ?? '')),
        'nationality'     => $d['nationality'] ?? '',
        'dob'             => $d['dateOfBirth'] ?? null,
        'permanentNumber' => $d['permanentNumber'] ?? null,
    ];
}

function f1_norm_constructor(array $c): array
{
    $id = $c['constructorId'] ?? '';
    $name = $c['name'] ?? '';
    $team = team_for_constructor($id, $name);
    return [
        'constructorId' => $id,
        'constructor'   => $team['team_name'] ?? $name,
        'color'         => team_color($id, $name),
        'team'          => $team,
    ];
}

function f1_norm_race(array $race): array
{
    $loc = $race['Circuit']['Location'] ?? [];
    $sessions = [];
    foreach ([
        'FirstPractice'    => 'Practice 1',
        'SecondPractice'   => 'Practice 2',
        'ThirdPractice'    => 'Practice 3',
        'SprintQualifying' => 'Sprint Qualifying',
        'SprintShootout'   => 'Sprint Shootout',
        'Sprint'           => 'Sprint',
        'Qualifying'       => 'Qualifying',
    ] as $key => $label) {
        if (!empty($race[$key]['date'])) {
            $sessions[] = ['label' => $label, 'at' => api_datetime($race[$key]['date'], $race[$key]['time'] ?? null)];
        }
    }
    $sessions[] = ['label' => 'Race', 'at' => api_datetime($race['date'] ?? null, $race['time'] ?? null)];

    return [
        'season'      => (int)($race['season'] ?? 0),
        'round'       => (int)($race['round'] ?? 0),
        'name'        => $race['raceName'] ?? '',
        'date'        => $race['date'] ?? null,
        'datetime'    => api_datetime($race['date'] ?? null, $race['time'] ?? null),
        'circuitId'   => $race['Circuit']['circuitId'] ?? '',
        'circuitName' => $race['Circuit']['circuitName'] ?? '',
        'locality'    => $loc['locality'] ?? '',
        'country'     => $loc['country'] ?? '',
        'lat'         => isset($loc['lat']) ? (float)$loc['lat'] : null,
        'long'        => isset($loc['long']) ? (float)$loc['long'] : null,
        'sessions'    => $sessions,
        'hasSprint'   => !empty($race['Sprint']),
    ];
}

function f1_norm_result(array $r): array
{
    $positionText = (string)($r['positionText'] ?? $r['position'] ?? '');
    return [
        'position'       => isset($r['position']) ? (int)$r['position'] : null,
        'positionText'   => $positionText,
        'classified'     => ctype_digit($positionText),
        'number'         => $r['number'] ?? ($r['Driver']['permanentNumber'] ?? ''),
        'points'         => (float)($r['points'] ?? 0),
        'grid'           => isset($r['grid']) ? (int)$r['grid'] : null,
        'laps'           => isset($r['laps']) ? (int)$r['laps'] : null,
        'status'         => $r['status'] ?? '',
        'time'           => $r['Time']['time'] ?? null,
        'fastestLapRank' => isset($r['FastestLap']['rank']) ? (int)$r['FastestLap']['rank'] : null,
        'fastestLapTime' => $r['FastestLap']['Time']['time'] ?? null,
        'fastestLapLap'  => $r['FastestLap']['lap'] ?? null,
        'q1'             => $r['Q1'] ?? null,
        'q2'             => $r['Q2'] ?? null,
        'q3'             => $r['Q3'] ?? null,
    ] + f1_norm_driver($r['Driver'] ?? []) + f1_norm_constructor($r['Constructor'] ?? ($r['Constructors'][0] ?? []));
}

/** "1:32:10.123", "+5.432s" or the retirement status. */
function result_time(array $row): string
{
    if (!empty($row['time'])) {
        return $row['time'];
    }
    return $row['status'] !== '' ? $row['status'] : '–';
}

/** Whether a race result counts as a retirement (DNF/DNS/DSQ). */
function is_dnf(array $row): bool
{
    if (!$row['classified']) {
        return true;
    }
    $s = strtolower($row['status']);
    return !($s === 'finished' || $s === 'lapped' || str_starts_with($s, '+'));
}

/* ===========================================================================
 * Database lookups (teams & drivers), linked to API ids
 * ======================================================================== */

function db_teams(): array
{
    static $teams = null;
    if ($teams === null) {
        $teams = [];
        foreach (db_all('SELECT * FROM teams ORDER BY team_name') as $t) {
            $teams[(int)$t['team_id']] = $t;
        }
    }
    return $teams;
}

function team_key(string $s): string
{
    $s = slugify(str_replace('_', ' ', $s));
    $s = (string)preg_replace('/-(f1-team|formula-1-team|racing|f1|team)$/', '', $s);
    return (string)preg_replace('/^(scuderia|team)-/', '', $s);
}

function team_for_constructor(string $constructorId, string $name = ''): ?array
{
    static $index = null;
    if ($index === null) {
        $index = [];
        foreach (db_teams() as $t) {
            foreach ([$t['team_name'], $t['full_team_name'] ?? ''] as $label) {
                if ($label !== '' && $label !== null) {
                    $index[team_key($label)] ??= $t;
                }
            }
        }
    }
    foreach ([$constructorId, $name] as $candidate) {
        $k = team_key($candidate);
        if ($k !== '' && isset($index[$k])) {
            return $index[$k];
        }
    }
    return null;
}

/** Team colour: from the database, then a list of historic teams, then grey. */
function team_color(string $constructorId, string $name = ''): string
{
    static $historic = [
        'sauber' => '#52e252', 'alphatauri' => '#5e8faa', 'toro_rosso' => '#469bff', 'alfa' => '#c92d4b',
        'racing_point' => '#f596c8', 'force_india' => '#f596c8', 'renault' => '#fff500', 'lotus_f1' => '#ffb800',
        'manor' => '#ed1b24', 'marussia' => '#6e0000', 'caterham' => '#005030', 'virgin' => '#c82e37',
        'hrt' => '#b2945e', 'brawn' => '#bfd447', 'bmw_sauber' => '#6cd3bf', 'toyota' => '#cc0000',
        'honda' => '#e0e0e0', 'jaguar' => '#0f5132', 'minardi' => '#191919', 'jordan' => '#eac435',
        'bar' => '#d3d3d3', 'super_aguri' => '#b81c1c', 'spyker' => '#ff6600', 'midland' => '#d50000',
        'benetton' => '#00a0de', 'tyrrell' => '#1b2a59', 'ligier' => '#0055a4', 'brabham' => '#002d62',
        'lotus' => '#003d1e', 'matra' => '#1f4e9b', 'cooper' => '#0b6623', 'vanwall' => '#0b5d1e',
        'maserati' => '#b30000', 'alfa_romeo' => '#c92d4b',
    ];
    $team = team_for_constructor($constructorId, $name);
    if ($team && !empty($team['team_color'])) {
        return safe_color($team['team_color']);
    }
    return $historic[$constructorId] ?? '#8a8f98';
}

function db_drivers(): array
{
    static $drivers = null;
    if ($drivers === null) {
        $drivers = [];
        foreach (db_all('SELECT d.*, t.team_name, t.team_color FROM drivers d LEFT JOIN teams t ON t.team_id = d.team_id WHERE d.first_name <> \'\'') as $d) {
            $drivers[(int)$d['driver_id']] = $d;
        }
    }
    return $drivers;
}

/** Find our database driver for an API driver (by name, then by unique surname). */
function driver_for_api(array $apiDriver): ?array
{
    static $byName = null, $bySurname = null;
    if ($byName === null) {
        $byName = $bySurname = [];
        foreach (db_drivers() as $d) {
            $byName[slugify($d['first_name'] . ' ' . $d['last_name'])] = $d;
            $s = slugify($d['last_name']);
            $bySurname[$s] = isset($bySurname[$s]) ? false : $d;
        }
    }
    $given = $apiDriver['givenName'] ?? '';
    $family = $apiDriver['familyName'] ?? '';
    $lastGiven = trim((string)strrchr(' ' . $given, ' '));
    foreach ([$given . ' ' . $family, $lastGiven . ' ' . $family] as $candidate) {
        $k = slugify($candidate);
        if (isset($byName[$k])) {
            return $byName[$k];
        }
    }
    $s = $bySurname[slugify($family)] ?? null;
    return $s ?: null;
}

/** Find the Ergast driverId for one of our database drivers. */
function api_driver_id(array $dbDriver): ?string
{
    $target = (int)$dbDriver['driver_id'];
    foreach ([current_season(), current_season() - 1, current_season() - 2] as $year) {
        $data = ergast("$year/drivers", season_ttl($year), ['limit' => 100]);
        foreach ($data['DriverTable']['Drivers'] ?? [] as $d) {
            $match = driver_for_api($d);
            if ($match && (int)$match['driver_id'] === $target) {
                return $d['driverId'];
            }
        }
    }
    return null;
}

/** Profile URL for an API driver when we have a page for them. */
function api_driver_url(array $row): ?string
{
    $d = driver_for_api($row);
    return $d ? driver_url($d) : null;
}

/** Render a driver name, linked to the profile page when available. */
function driver_link(array $row, bool $short = false): string
{
    $label = $short
        ? e($row['givenName'] !== '' ? mb_substr($row['givenName'], 0, 1) . '. ' . $row['familyName'] : $row['familyName'])
        : e($row['givenName']) . ' <strong>' . e($row['familyName']) . '</strong>';
    $href = api_driver_url($row);
    return $href ? '<a class="link-plain" href="' . e($href) . '">' . $label . '</a>' : $label;
}

/** Render a constructor name with colour swatch, linked when we have the team. */
function team_link(array $row): string
{
    $inner = '<span class="swatch" style="--c:' . e($row['color']) . '"></span>' . e($row['constructor']);
    return !empty($row['team'])
        ? '<a class="team-name link-plain" href="' . e(team_url($row['team'])) . '">' . $inner . '</a>'
        : '<span class="team-name">' . $inner . '</span>';
}

/* ===========================================================================
 * Calendar (database)
 * ======================================================================== */

function db_season_years(): array
{
    return array_map('intval', array_column(
        db_all('SELECT DISTINCT race_year FROM circuits WHERE race_datetime IS NOT NULL ORDER BY race_year DESC'),
        'race_year'
    ));
}

function db_calendar(int $year): array
{
    return db_all('SELECT * FROM circuits WHERE race_year = ? AND race_datetime IS NOT NULL ORDER BY calendar_order, race_datetime', [$year]);
}

/** The upcoming (or currently running) Grand Prix weekend. */
function db_next_race(): ?array
{
    return db_one(
        'SELECT * FROM circuits WHERE race_datetime >= UTC_TIMESTAMP() - INTERVAL 3 HOUR ORDER BY race_datetime LIMIT 1'
    );
}

/** Ordered list of weekend sessions for a calendar row. */
function weekend_sessions(array $row): array
{
    $isSprint = !empty($row['sprint_datetime']);
    $map = $isSprint
        ? ['fp1_datetime' => 'Practice 1', 'sprint_quali_datetime' => 'Sprint Qualifying', 'sprint_datetime' => 'Sprint', 'quali_datetime' => 'Qualifying', 'race_datetime' => 'Race']
        : ['fp1_datetime' => 'Practice 1', 'fp2_datetime' => 'Practice 2', 'fp3_datetime' => 'Practice 3', 'quali_datetime' => 'Qualifying', 'race_datetime' => 'Race'];
    $sessions = [];
    foreach ($map as $col => $label) {
        $at = db_datetime($row[$col] ?? null);
        if ($at) {
            $sessions[] = ['label' => $label, 'key' => $col, 'at' => $at];
        }
    }
    usort($sessions, static fn($a, $b) => $a['at'] <=> $b['at']);
    return $sessions;
}

/** Date range label for a race weekend, e.g. "6 – 8 Mar". */
function weekend_range(array $row): string
{
    $race = db_datetime($row['race_datetime'] ?? null);
    if (!$race) {
        return 'TBC';
    }
    $sessions = weekend_sessions($row);
    $start = $sessions[0]['at'] ?? $race->modify('-2 days');
    if ($start->format('Y-m-d') === $race->format('Y-m-d')) {
        $start = $race->modify('-2 days');
    }
    return $start->format('M') === $race->format('M')
        ? $start->format('j') . ' – ' . $race->format('j M')
        : $start->format('j M') . ' – ' . $race->format('j M');
}

/* ===========================================================================
 * Schedule, results & standings (API)
 * ======================================================================== */

/** @return array<int, array> races keyed by round */
function f1_schedule(int $year): array
{
    $ttl = $year < current_season() ? 30 * 86400 : 86400;
    $data = ergast((string)$year, $ttl, ['limit' => 100]);
    $races = [];
    foreach ($data['RaceTable']['Races'] ?? [] as $race) {
        $n = f1_norm_race($race);
        $races[$n['round']] = $n;
    }
    ksort($races);
    return $races;
}

/** Results for one race (or null when not available yet). */
function f1_race(int $year, int $round, string $type = 'results'): ?array
{
    $listKey = ['results' => 'Results', 'qualifying' => 'QualifyingResults', 'sprint' => 'SprintResults'][$type] ?? 'Results';
    $sched = f1_schedule($year)[$round] ?? null;
    $old = $sched && $sched['datetime'] && $sched['datetime'] < new DateTimeImmutable('-10 days');
    $data = ergast("$year/$round/$type", $old ? 30 * 86400 : season_ttl($year), ['limit' => 100]);
    $race = $data['RaceTable']['Races'][0] ?? null;
    if (!$race || empty($race[$listKey])) {
        return null;
    }
    return ['race' => f1_norm_race($race), 'rows' => array_map('f1_norm_result', $race[$listKey])];
}

function f1_pitstops(int $year, int $round): array
{
    $data = ergast("$year/$round/pitstops", season_ttl($year), ['limit' => 100]);
    return $data['RaceTable']['Races'][0]['PitStops'] ?? [];
}

/** All race (or sprint/qualifying) results of a season, keyed by round. */
function f1_season(int $year, string $type = 'results'): array
{
    $listKey = ['results' => 'Results', 'qualifying' => 'QualifyingResults', 'sprint' => 'SprintResults'][$type] ?? 'Results';
    $out = [];
    foreach (ergast_race_table("$year/$type", season_ttl($year), $listKey) as $round => $race) {
        $out[$round] = ['race' => f1_norm_race($race), 'rows' => array_map('f1_norm_result', $race[$listKey])];
    }
    return $out;
}

/** @return array{round:int, rows:list<array>} */
function f1_driver_standings(int $year, ?int $round = null): array
{
    $path = $round ? "$year/$round/driverStandings" : "$year/driverStandings";
    $data = ergast($path, season_ttl($year), ['limit' => 100]);
    $list = $data['StandingsTable']['StandingsLists'][0] ?? null;
    $rows = [];
    foreach ($list['DriverStandings'] ?? [] as $s) {
        $rows[] = [
            'position' => (int)($s['position'] ?? $s['positionText'] ?? 0),
            'points'   => (float)$s['points'],
            'wins'     => (int)$s['wins'],
        ] + f1_norm_driver($s['Driver']) + f1_norm_constructor(end($s['Constructors']) ?: []);
    }
    return ['round' => (int)($list['round'] ?? 0), 'rows' => $rows];
}

/** @return array{round:int, rows:list<array>} */
function f1_constructor_standings(int $year): array
{
    $data = ergast("$year/constructorStandings", season_ttl($year), ['limit' => 100]);
    $list = $data['StandingsTable']['StandingsLists'][0] ?? null;
    $rows = [];
    foreach ($list['ConstructorStandings'] ?? [] as $s) {
        $rows[] = [
            'position' => (int)($s['position'] ?? $s['positionText'] ?? 0),
            'points'   => (float)$s['points'],
            'wins'     => (int)$s['wins'],
        ] + f1_norm_constructor($s['Constructor']);
    }
    return ['round' => (int)($list['round'] ?? 0), 'rows' => $rows];
}

/** The most recent season that already has standings (handles the off-season). */
function latest_results_season(): int
{
    $year = current_season();
    return f1_driver_standings($year)['rows'] ? $year : $year - 1;
}

/**
 * Cumulative points per round for the top drivers or constructors.
 *
 * @return array{labels:list<string>, races:list<string>, series:list<array{name:string,color:string,points:list<float>}>}
 */
function f1_points_progression(int $year, int $top = 8, string $mode = 'drivers'): array
{
    $results = f1_season($year, 'results');
    $sprints = f1_season($year, 'sprint');
    if (!$results) {
        return ['labels' => [], 'races' => [], 'series' => []];
    }

    $idKey = $mode === 'constructors' ? 'constructorId' : 'driverId';
    $standings = $mode === 'constructors' ? f1_constructor_standings($year)['rows'] : f1_driver_standings($year)['rows'];
    $leaders = array_slice($standings, 0, $top);

    $totals = [];
    $series = [];
    foreach ($leaders as $l) {
        $series[$l[$idKey]] = [
            'name'   => $mode === 'constructors' ? $l['constructor'] : $l['familyName'],
            'color'  => $l['color'],
            'points' => [],
        ];
        $totals[$l[$idKey]] = 0.0;
    }

    $labels = $races = [];
    foreach ($results as $round => $race) {
        $labels[] = 'R' . $round;
        $races[] = $race['race']['name'];
        foreach (array_merge($race['rows'], $sprints[$round]['rows'] ?? []) as $row) {
            if (isset($totals[$row[$idKey]])) {
                $totals[$row[$idKey]] += $row['points'];
            }
        }
        foreach ($totals as $id => $pts) {
            $series[$id]['points'][] = round($pts, 1);
        }
    }

    // Constructors that share a colour (e.g. teammates' teams) stay distinguishable via dashes in JS.
    return ['labels' => $labels, 'races' => $races, 'series' => array_values($series)];
}

/**
 * A driver's season, round by round.
 *
 * @return array<int, array{race:array, result:?array, sprint:?array, quali:?array}>
 */
function f1_driver_season(int $year, string $driverId): array
{
    $results = f1_season($year, 'results');
    $sprints = f1_season($year, 'sprint');
    $quali = f1_season($year, 'qualifying');
    $find = static function (array $rows) use ($driverId): ?array {
        foreach ($rows as $r) {
            if ($r['driverId'] === $driverId) {
                return $r;
            }
        }
        return null;
    };
    $out = [];
    foreach ($results as $round => $race) {
        $result = $find($race['rows']);
        if (!$result) {
            continue;
        }
        $out[$round] = [
            'race'   => $race['race'],
            'result' => $result,
            'sprint' => isset($sprints[$round]) ? $find($sprints[$round]['rows']) : null,
            'quali'  => isset($quali[$round]) ? $find($quali[$round]['rows']) : null,
        ];
    }
    return $out;
}

/** Summary numbers for a driver season (from f1_driver_season()). */
function season_summary(array $season): array
{
    $s = ['races' => 0, 'points' => 0.0, 'wins' => 0, 'podiums' => 0, 'poles' => 0, 'dnfs' => 0, 'best' => null, 'fastestLaps' => 0, 'avgFinish' => null];
    $finishSum = $finishes = 0;
    foreach ($season as $r) {
        $res = $r['result'];
        $s['races']++;
        $s['points'] += $res['points'] + ($r['sprint']['points'] ?? 0);
        if ($res['position'] === 1 && $res['classified']) {
            $s['wins']++;
        }
        if ($res['classified'] && $res['position'] <= 3) {
            $s['podiums']++;
        }
        if (($r['quali']['position'] ?? null) === 1) {
            $s['poles']++;
        }
        if ($res['fastestLapRank'] === 1) {
            $s['fastestLaps']++;
        }
        if (is_dnf($res)) {
            $s['dnfs']++;
        } else {
            $finishSum += $res['position'];
            $finishes++;
        }
        if ($res['classified'] && ($s['best'] === null || $res['position'] < $s['best'])) {
            $s['best'] = $res['position'];
        }
    }
    $s['avgFinish'] = $finishes ? round($finishSum / $finishes, 1) : null;
    return $s;
}

/**
 * Head-to-head against every teammate in a season.
 *
 * @return list<array{teammate:array, races:int, raceAhead:int, raceBehind:int, qualiAhead:int, qualiBehind:int, points:float, teammatePoints:float}>
 */
function f1_teammate_h2h(int $year, string $driverId): array
{
    $results = f1_season($year, 'results');
    $quali = f1_season($year, 'qualifying');
    $sprints = f1_season($year, 'sprint');
    $h2h = [];

    foreach ($results as $round => $race) {
        $me = null;
        foreach ($race['rows'] as $r) {
            if ($r['driverId'] === $driverId) {
                $me = $r;
            }
        }
        if (!$me) {
            continue;
        }
        foreach ($race['rows'] as $mate) {
            if ($mate['driverId'] === $driverId || $mate['constructorId'] !== $me['constructorId']) {
                continue;
            }
            $id = $mate['driverId'];
            $h2h[$id] ??= ['teammate' => $mate, 'races' => 0, 'raceAhead' => 0, 'raceBehind' => 0, 'qualiAhead' => 0, 'qualiBehind' => 0, 'points' => 0.0, 'teammatePoints' => 0.0];
            $h2h[$id]['races']++;
            $me['position'] < $mate['position'] ? $h2h[$id]['raceAhead']++ : $h2h[$id]['raceBehind']++;
            $h2h[$id]['points'] += $me['points'];
            $h2h[$id]['teammatePoints'] += $mate['points'];
            foreach ($sprints[$round]['rows'] ?? [] as $sr) {
                if ($sr['driverId'] === $driverId) {
                    $h2h[$id]['points'] += $sr['points'];
                } elseif ($sr['driverId'] === $id) {
                    $h2h[$id]['teammatePoints'] += $sr['points'];
                }
            }
            $qMe = $qMate = null;
            foreach ($quali[$round]['rows'] ?? [] as $q) {
                if ($q['driverId'] === $driverId) {
                    $qMe = $q['position'];
                } elseif ($q['driverId'] === $id) {
                    $qMate = $q['position'];
                }
            }
            if ($qMe !== null && $qMate !== null) {
                $qMe < $qMate ? $h2h[$id]['qualiAhead']++ : $h2h[$id]['qualiBehind']++;
            }
        }
    }
    usort($h2h, static fn($a, $b) => $b['races'] <=> $a['races']);
    return $h2h;
}

/** Career totals for an API driver. */
function f1_driver_career(string $driverId): array
{
    $ttl = is_race_weekend() ? 86400 : 3 * 86400;
    $total = static fn(string $path) => (int)(ergast($path, $ttl, ['limit' => 1])['total'] ?? 0);
    $first = ergast("drivers/$driverId/results", $ttl, ['limit' => 1]);
    $debut = $first['RaceTable']['Races'][0] ?? null;

    $p1 = $total("drivers/$driverId/results/1");
    return [
        'races'   => (int)($first['total'] ?? 0),
        'wins'    => $p1,
        'podiums' => $p1 + $total("drivers/$driverId/results/2") + $total("drivers/$driverId/results/3"),
        'poles'   => $total("drivers/$driverId/qualifying/1"),
        'seasons' => $total("drivers/$driverId/seasons"),
        'debut'   => $debut ? ['season' => (int)$debut['season'], 'round' => (int)$debut['round'], 'name' => $debut['raceName']] : null,
    ];
}

/** Career totals for a constructor. */
function f1_constructor_career(string $constructorId): array
{
    $ttl = is_race_weekend() ? 86400 : 3 * 86400;
    $total = static fn(string $path) => (int)(ergast($path, $ttl, ['limit' => 1])['total'] ?? 0);
    return [
        'wins'    => $total("constructors/$constructorId/results/1"),
        'poles'   => $total("constructors/$constructorId/qualifying/1"),
        'seasons' => $total("constructors/$constructorId/seasons"),
    ];
}

/** Winners of past Grands Prix at a circuit, newest first. */
function f1_circuit_winners(string $circuitId): array
{
    if ($circuitId === '') {
        return [];
    }
    $data = ergast("circuits/$circuitId/results/1", 7 * 86400, ['limit' => 100]);
    $rows = [];
    foreach ($data['RaceTable']['Races'] ?? [] as $race) {
        $res = $race['Results'][0] ?? null;
        if ($res) {
            $rows[] = ['season' => (int)$race['season'], 'round' => (int)$race['round'], 'raceName' => $race['raceName']] + f1_norm_result($res);
        }
    }
    usort($rows, static fn($a, $b) => $b['season'] <=> $a['season']);
    return $rows;
}

/**
 * Find the API race matching one of our calendar rows. Round numbers can
 * differ between our calendar and the API (cancelled/added races), so match
 * on race date first and fall back to the round number.
 */
function api_race_for_calendar(array $row): ?array
{
    $year = (int)$row['race_year'];
    $date = db_datetime($row['race_datetime'] ?? null);
    $schedule = f1_schedule($year);
    if ($date) {
        foreach ($schedule as $race) {
            if ($race['datetime'] && abs($race['datetime']->getTimestamp() - $date->getTimestamp()) < 2 * 86400) {
                return $race;
            }
        }
    }
    return $schedule[(int)$row['calendar_order']] ?? null;
}

/** Find our calendar row for an API race (by date, see api_race_for_calendar()). */
function calendar_for_api_race(array $race): ?array
{
    if (!$race['datetime']) {
        return null;
    }
    return db_one(
        'SELECT * FROM circuits WHERE race_datetime BETWEEN ? - INTERVAL 2 DAY AND ? + INTERVAL 2 DAY ORDER BY race_datetime LIMIT 1',
        [$race['datetime']->format('Y-m-d H:i:s'), $race['datetime']->format('Y-m-d H:i:s')]
    );
}

/* ===========================================================================
 * Flags
 * ======================================================================== */

function flag_url(?string $iso2, int $width = 40): ?string
{
    $iso2 = strtolower((string)$iso2);
    return preg_match('/^[a-z]{2}$/', $iso2) ? "https://flagcdn.com/w{$width}/{$iso2}.png" : null;
}

function nationality_iso(string $nationality): ?string
{
    static $map = [
        'american' => 'us', 'argentine' => 'ar', 'argentinian' => 'ar', 'australian' => 'au', 'austrian' => 'at',
        'belgian' => 'be', 'brazilian' => 'br', 'british' => 'gb', 'canadian' => 'ca', 'chinese' => 'cn',
        'colombian' => 'co', 'danish' => 'dk', 'dutch' => 'nl', 'finnish' => 'fi', 'french' => 'fr',
        'german' => 'de', 'hungarian' => 'hu', 'indian' => 'in', 'indonesian' => 'id', 'irish' => 'ie',
        'italian' => 'it', 'japanese' => 'jp', 'malaysian' => 'my', 'mexican' => 'mx', 'monegasque' => 'mc',
        'new zealander' => 'nz', 'polish' => 'pl', 'portuguese' => 'pt', 'russian' => 'ru', 'south african' => 'za',
        'spanish' => 'es', 'swedish' => 'se', 'swiss' => 'ch', 'thai' => 'th', 'venezuelan' => 've',
        'chilean' => 'cl', 'uruguayan' => 'uy', 'czech' => 'cz', 'liechtensteiner' => 'li', 'rhodesian' => 'zw',
        'east german' => 'de', 'emirati' => 'ae', 'israeli' => 'il', 'estonian' => 'ee',
    ];
    return $map[strtolower(trim($nationality))] ?? null;
}

function country_iso(string $country): ?string
{
    static $map = [
        'australia' => 'au', 'austria' => 'at', 'azerbaijan' => 'az', 'bahrain' => 'bh', 'belgium' => 'be',
        'brazil' => 'br', 'canada' => 'ca', 'china' => 'cn', 'france' => 'fr', 'germany' => 'de',
        'hungary' => 'hu', 'italy' => 'it', 'japan' => 'jp', 'mexico' => 'mx', 'monaco' => 'mc',
        'netherlands' => 'nl', 'portugal' => 'pt', 'qatar' => 'qa', 'russia' => 'ru', 'saudi arabia' => 'sa',
        'singapore' => 'sg', 'spain' => 'es', 'turkey' => 'tr', 'uae' => 'ae', 'united arab emirates' => 'ae',
        'uk' => 'gb', 'united kingdom' => 'gb', 'usa' => 'us', 'united states' => 'us', 'malaysia' => 'my',
        'korea' => 'kr', 'india' => 'in', 'south africa' => 'za', 'argentina' => 'ar', 'sweden' => 'se',
        'switzerland' => 'ch', 'morocco' => 'ma', 'vietnam' => 'vn',
    ];
    return $map[strtolower(trim($country))] ?? null;
}

/** <img> tag for a flag, or an empty string when unknown. */
function flag_img(?string $iso2, string $label = ''): string
{
    $src = flag_url($iso2);
    return $src ? '<img class="flag" src="' . e($src) . '" alt="' . e($label) . '" width="20" height="15" loading="lazy">' : '';
}

/* ===========================================================================
 * Title fight, lap charts and season statistics
 * ======================================================================== */

/**
 * Who can still win the championship, and what the leader needs to clinch it.
 * Uses the current points system (25 for a win, 8 for a sprint win, no
 * fastest-lap point) — for constructors the top two finishes count.
 */
function f1_title_fight(int $year, string $mode = 'drivers'): ?array
{
    $standings = $mode === 'constructors' ? f1_constructor_standings($year) : f1_driver_standings($year);
    $rows = $standings['rows'];
    if (count($rows) < 2) {
        return null;
    }
    $now = new DateTimeImmutable('-2 hours');
    $remaining = [];
    foreach (f1_schedule($year) as $round => $race) {
        if ($round > $standings['round'] && $race['datetime'] && $race['datetime'] > $now) {
            $remaining[] = $race;
        }
    }
    $perRace = $mode === 'constructors' ? 43 : 25;
    $perSprint = $mode === 'constructors' ? 15 : 8;
    $maxFor = static fn(array $race) => $perRace + ($race['hasSprint'] ? $perSprint : 0);
    $maxRemaining = array_sum(array_map($maxFor, $remaining));
    $nextMax = $remaining ? $maxFor($remaining[0]) : 0;

    $leader = $rows[0];
    $contenders = [];
    foreach ($rows as $r) {
        $max = $r['points'] + $maxRemaining;
        if ($max < $leader['points']) {
            break;
        }
        $contenders[] = $r + ['gap' => $leader['points'] - $r['points'], 'maxPossible' => $max];
    }
    $gapToSecond = $leader['points'] - $rows[1]['points'];
    $afterNext = $maxRemaining - $nextMax;
    return [
        'year'         => $year,
        'mode'         => $mode,
        'round'        => $standings['round'],
        'remaining'    => $remaining,
        'maxRemaining' => $maxRemaining,
        'nextMax'      => $nextMax,
        'leader'       => $leader,
        'second'       => $rows[1],
        'contenders'   => $contenders,
        'decided'      => count($contenders) === 1,
        // Points the leader must outscore every rival by at the next event to clinch.
        'clinchMargin' => $remaining && count($contenders) > 1 ? max(0, $afterNext - $gapToSecond + 1) : null,
        'canClinchNext'=> $remaining && count($contenders) > 1 && ($afterNext - $gapToSecond + 1) <= $nextMax,
    ];
}

/**
 * Lap-by-lap positions for a race (available from 1996).
 *
 * @return array{laps:list<int>, drivers:list<array{code:string,name:string,color:string,positions:list<?int>}>}
 */
function f1_lap_positions(int $year, int $round): array
{
    $race = f1_race($year, $round);
    if (!$race || $year < 1996) {
        return ['laps' => [], 'drivers' => [], 'complete' => true];
    }
    $old = $race['race']['datetime'] && $race['race']['datetime'] < new DateTimeImmutable('-3 days');
    $ttl = $old ? 180 * 86400 : 3600;

    $laps = [];
    $offset = 0;
    $guard = 0;
    $complete = true;
    do {
        $data = ergast("$year/$round/laps", $ttl, ['limit' => 100, 'offset' => $offset]);
        if (!$data) {
            $complete = $offset > 0 ? false : $complete;
            break;
        }
        $total = (int)($data['total'] ?? 0);
        foreach ($data['RaceTable']['Races'][0]['Laps'] ?? [] as $lap) {
            foreach ($lap['Timings'] as $t) {
                $laps[(int)$lap['number']][$t['driverId']] = (int)$t['position'];
            }
        }
        $offset += 100;
    } while ($offset < $total && ++$guard < 40);
    ksort($laps);

    $lapNumbers = array_keys($laps);
    $drivers = [];
    foreach ($race['rows'] as $r) {
        $positions = [$r['grid'] ?: null];
        foreach ($lapNumbers as $n) {
            $positions[] = $laps[$n][$r['driverId']] ?? null;
        }
        $drivers[] = ['code' => $r['code'], 'name' => $r['name'], 'color' => $r['color'], 'positions' => $positions];
    }
    return ['laps' => $lapNumbers ? array_merge([0], $lapNumbers) : [], 'drivers' => $drivers, 'complete' => $complete];
}

/** Season-wide records and teammate battles. */
function f1_season_stats(int $year): array
{
    $results = f1_season($year, 'results');
    $quali = f1_season($year, 'qualifying');
    $sprints = f1_season($year, 'sprint');

    $drivers = [];
    $teams = [];
    $touch = static function (array $r) use (&$drivers): void {
        $drivers[$r['driverId']] ??= [
            'row' => $r, 'races' => 0, 'wins' => 0, 'podiums' => 0, 'poles' => 0, 'fastestLaps' => 0,
            'pointsFinishes' => 0, 'dnfs' => 0, 'gained' => 0, 'points' => 0.0, 'frontRow' => 0, 'finishes' => 0, 'finishSum' => 0, 'laps' => 0,
        ];
    };
    foreach ($results as $round => $race) {
        foreach ($race['rows'] as $r) {
            $touch($r);
            $d = &$drivers[$r['driverId']];
            $d['row'] = $r;
            $d['races']++;
            $d['points'] += $r['points'];
            $d['laps'] += (int)$r['laps'];
            if ($r['classified'] && $r['position'] === 1) { $d['wins']++; }
            if ($r['classified'] && $r['position'] <= 3) { $d['podiums']++; }
            if ($r['fastestLapRank'] === 1) { $d['fastestLaps']++; }
            if ($r['points'] > 0) { $d['pointsFinishes']++; }
            if (is_dnf($r)) { $d['dnfs']++; } else { $d['finishes']++; $d['finishSum'] += $r['position']; }
            if ($r['grid'] > 0 && $r['classified']) { $d['gained'] += $r['grid'] - $r['position']; }
            unset($d);

            $teams[$r['constructorId']] ??= ['row' => $r, 'points' => 0.0, 'wins' => 0, 'podiums' => 0, 'dnfs' => 0, 'starts' => 0];
            $teams[$r['constructorId']]['points'] += $r['points'];
            $teams[$r['constructorId']]['starts']++;
            if ($r['classified'] && $r['position'] === 1) { $teams[$r['constructorId']]['wins']++; }
            if ($r['classified'] && $r['position'] <= 3) { $teams[$r['constructorId']]['podiums']++; }
            if (is_dnf($r)) { $teams[$r['constructorId']]['dnfs']++; }
        }
        foreach ($quali[$round]['rows'] ?? [] as $q) {
            $touch($q);
            if ($q['position'] === 1) { $drivers[$q['driverId']]['poles']++; }
            if ($q['position'] <= 2) { $drivers[$q['driverId']]['frontRow']++; }
        }
        foreach ($sprints[$round]['rows'] ?? [] as $s) {
            $touch($s);
            $drivers[$s['driverId']]['points'] += $s['points'];
            if (isset($teams[$s['constructorId']])) { $teams[$s['constructorId']]['points'] += $s['points']; }
        }
    }
    foreach ($drivers as &$d) {
        $d['avgFinish'] = $d['finishes'] ? round($d['finishSum'] / $d['finishes'], 2) : null;
    }
    unset($d);

    // Teammate battles: every pair that raced together at least 3 times.
    $battles = [];
    foreach ($drivers as $id => $d) {
        foreach (f1_teammate_h2h($year, $id) as $h) {
            $mateId = $h['teammate']['driverId'];
            $key = $id < $mateId ? "$id|$mateId" : "$mateId|$id";
            if (isset($battles[$key]) || $h['races'] < 3) {
                continue;
            }
            $battles[$key] = ['a' => $d['row'], 'b' => $h['teammate']] + $h;
        }
    }
    usort($battles, static fn($x, $y) => strcmp($x['a']['constructor'], $y['a']['constructor']));
    uasort($teams, static fn($x, $y) => $y['points'] <=> $x['points']);

    return ['drivers' => $drivers, 'teams' => $teams, 'battles' => array_values($battles), 'rounds' => count($results)];
}

/** Every place you can jump to with the quick search (⌘K). */
function search_index(): array
{
    $items = [];
    foreach (['Home' => '', 'Race calendar' => 'kalender.php', 'Results' => 'results.php', 'Standings' => 'standings.php', 'Season stats' => 'stats.php', 'Drivers' => 'drivers.php', 'Teams' => 'teams.php', 'Compare drivers' => 'compare.php', 'Daily quiz' => 'quiz.php', 'News' => 'nieuws.php', 'Contact' => 'contact.php'] as $label => $href) {
        $items[] = ['t' => $label, 's' => 'Page', 'u' => url($href)];
    }
    foreach (db_drivers() as $d) {
        if ((int)$d['is_active'] === 1) {
            $items[] = ['t' => $d['first_name'] . ' ' . $d['last_name'], 's' => ($d['team_name'] ?? 'Driver') . ' · #' . $d['driver_number'], 'u' => driver_url($d), 'c' => safe_color($d['team_color'] ?? null), 'i' => $d['image']];
        }
    }
    foreach (db_teams() as $t) {
        if ((int)$t['is_active'] === 1) {
            $items[] = ['t' => $t['team_name'], 's' => 'Team · ' . ($t['full_team_name'] ?? ''), 'u' => team_url($t), 'c' => safe_color($t['team_color'])];
        }
    }
    $year = db_season_years()[0] ?? current_season();
    foreach (db_calendar($year) as $r) {
        $items[] = ['t' => $r['grandprix'] . ' ' . $year, 's' => 'Race · ' . $r['title'], 'u' => url('circuit-details.php', ['key' => $r['circuit_key'], 'year' => $year])];
    }
    for ($y = current_season(); $y >= current_season() - 10; $y--) {
        $items[] = ['t' => "$y standings", 's' => 'Season', 'u' => url('standings.php', ['year' => $y])];
        $items[] = ['t' => "$y results", 's' => 'Season', 'u' => url('results.php', ['year' => $y])];
    }
    return $items;
}

/** The session that is live right now (or starts within 15 minutes), if any. */
function live_session(): ?array
{
    static $cache = false;
    if ($cache !== false) {
        return $cache;
    }
    $next = db_next_race();
    $now = new DateTimeImmutable('now', new DateTimeZone('UTC'));
    foreach ($next ? weekend_sessions($next) : [] as $s) {
        $end = $s['at']->modify($s['label'] === 'Race' ? '+2 hours' : '+1 hour');
        if ($now >= $s['at']->modify('-15 minutes') && $now <= $end) {
            return $cache = $s + ['race' => $next, 'started' => $now >= $s['at']];
        }
    }
    return $cache = null;
}

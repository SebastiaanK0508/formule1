<?php
/**
 * Editable entities for the generic list/edit pages.
 *
 * Field definition: [label, type, options]
 *   types: text, textarea, number, decimal, date, datetime (Dutch time ↔ UTC), color, url, checkbox, select
 *   options: required, help, options (callable returning [value => label]), wide, max
 */
declare(strict_types=1);

function admin_team_options(): array
{
    $opts = ['' => '— Geen team —'];
    foreach (db_teams() as $t) {
        $opts[$t['team_id']] = $t['team_name'] . ($t['is_active'] ? '' : ' (inactief)');
    }
    return $opts;
}

function admin_entities(): array
{
    return [
        'drivers' => [
            'table'    => 'drivers',
            'pk'       => ['driver_id'],
            'singular' => 'coureur',
            'plural'   => 'Coureurs',
            'nav'      => 'drivers',
            'order'    => 'is_active DESC, last_name',
            'search'   => ['first_name', 'last_name', 'nationality'],
            'title'    => static fn(array $r) => trim($r['first_name'] . ' ' . $r['last_name']),
            'public'   => static fn(array $r) => driver_url($r),
            'columns'  => [
                'driver_number' => '#',
                'first_name'    => 'Voornaam',
                'last_name'     => 'Achternaam',
                'team_id'       => 'Team',
                'nationality'   => 'Nationaliteit',
                'is_active'     => 'Actief',
            ],
            'fields'   => [
                'first_name'        => ['Voornaam', 'text', ['required' => true, 'max' => 50]],
                'last_name'         => ['Achternaam', 'text', ['required' => true, 'max' => 50]],
                'driver_number'     => ['Startnummer', 'number', ['help' => 'Moet uniek zijn.']],
                'team_id'           => ['Team', 'select', ['options' => 'admin_team_options']],
                'nationality'       => ['Nationaliteit', 'text', ['required' => true, 'max' => 50, 'help' => 'In het Engels, bijv. Dutch.']],
                'date_of_birth'     => ['Geboortedatum', 'date', []],
                'place_of_birth'    => ['Geboorteplaats', 'text', ['max' => 255]],
                'championships_won' => ['Wereldtitels', 'number', []],
                'career_points'     => ['Carrièrepunten', 'decimal', []],
                'image'             => ['Foto (URL)', 'url', ['wide' => true, 'max' => 255]],
                'flag_url'          => ['Vlag (URL)', 'url', ['wide' => true, 'max' => 255]],
                'description'       => ['Biografie', 'textarea', ['wide' => true, 'max' => 255, 'help' => 'Maximaal 255 tekens.']],
                'is_active'         => ['Actief op de grid', 'checkbox', []],
            ],
        ],
        'teams' => [
            'table'    => 'teams',
            'pk'       => ['team_id'],
            'singular' => 'team',
            'plural'   => 'Teams',
            'nav'      => 'teams',
            'order'    => 'is_active DESC, team_name',
            'search'   => ['team_name', 'full_team_name', 'team_principal'],
            'title'    => static fn(array $r) => $r['team_name'],
            'public'   => static fn(array $r) => team_url($r),
            'columns'  => [
                'team_color'              => '',
                'team_name'               => 'Team',
                'full_team_name'          => 'Officiële naam',
                'team_principal'          => 'Teambaas',
                'current_engine_supplier' => 'Motor',
                'is_active'               => 'Actief',
            ],
            'fields'   => [
                'team_name'               => ['Teamnaam', 'text', ['required' => true, 'max' => 100, 'help' => 'Korte naam, bijv. Red Bull. Wordt gebruikt om API-resultaten te koppelen.']],
                'full_team_name'          => ['Officiële naam', 'text', ['max' => 255]],
                'team_color'              => ['Teamkleur', 'color', ['required' => true]],
                'base_location'           => ['Thuisbasis', 'text', ['max' => 100]],
                'team_principal'          => ['Teambaas', 'text', ['max' => 100]],
                'technical_director'      => ['Technisch directeur', 'text', ['max' => 100]],
                'chassis'                 => ['Chassis', 'text', ['max' => 255]],
                'current_engine_supplier' => ['Motorleverancier', 'text', ['max' => 100]],
                'first_entry_year'        => ['Eerste deelname (jaar)', 'number', []],
                'championships_won'       => ['Constructeurstitels', 'number', []],
                'website_url'             => ['Website', 'url', ['wide' => true, 'max' => 255]],
                'logo_url'                => ['Logo (URL)', 'url', ['wide' => true, 'max' => 255]],
                'is_active'               => ['Actief op de grid', 'checkbox', []],
            ],
        ],
        'circuits' => [
            'table'    => 'circuits',
            'pk'       => ['circuit_key', 'race_year'],
            'singular' => 'race',
            'plural'   => 'Kalender',
            'nav'      => 'circuits',
            'order'    => 'calendar_order, race_datetime',
            'filter'   => 'race_year',
            'search'   => ['grandprix', 'title', 'location'],
            'title'    => static fn(array $r) => $r['grandprix'] . ' ' . $r['race_year'],
            'public'   => static fn(array $r) => url('circuit-details.php', ['key' => $r['circuit_key'], 'year' => $r['race_year']]),
            'columns'  => [
                'calendar_order'  => 'Ronde',
                'grandprix'       => 'Grand Prix',
                'title'           => 'Circuit',
                'race_datetime'   => 'Race (NL-tijd)',
                'sprint_datetime' => 'Sprint',
                'fp1_datetime'    => 'Sessies',
            ],
            'fields'   => [
                'circuit_key'           => ['Circuit-sleutel', 'text', ['required' => true, 'max' => 50, 'key' => true, 'help' => 'Unieke code, bijv. zandvoort. Niet meer te wijzigen na aanmaken.']],
                'race_year'             => ['Seizoen', 'number', ['required' => true, 'key' => true]],
                'calendar_order'        => ['Ronde', 'number', ['required' => true]],
                'grandprix'             => ['Naam Grand Prix', 'text', ['required' => true, 'max' => 100]],
                'title'                 => ['Circuitnaam', 'text', ['required' => true, 'max' => 100]],
                'location'              => ['Plaats', 'text', ['required' => true, 'max' => 100]],
                'fp1_datetime'          => ['Vrije training 1', 'datetime', []],
                'fp2_datetime'          => ['Vrije training 2', 'datetime', []],
                'fp3_datetime'          => ['Vrije training 3', 'datetime', []],
                'sprint_quali_datetime' => ['Sprintkwalificatie', 'datetime', []],
                'sprint_datetime'       => ['Sprintrace', 'datetime', ['help' => 'Vul in bij een sprintweekend.']],
                'quali_datetime'        => ['Kwalificatie', 'datetime', []],
                'race_datetime'         => ['Race', 'datetime', ['required' => true]],
                'lap_count'             => ['Aantal ronden', 'number', []],
                'circuit_length_km'     => ['Lengte circuit (km)', 'decimal', []],
                'race_distance_km'      => ['Racedistantie (km)', 'decimal', []],
                'first_gp_year'         => ['Eerste Grand Prix (jaar)', 'number', []],
                'lap_record'            => ['Ronderecord', 'text', ['max' => 100]],
                'map_url'               => ['Circuitkaart (URL)', 'url', ['wide' => true, 'max' => 255]],
                'country_flag_url'      => ['Vlag (URL)', 'url', ['wide' => true, 'max' => 500]],
                'description'           => ['Beschrijving', 'textarea', ['wide' => true]],
                'highlights'            => ['Highlights', 'textarea', ['wide' => true, 'help' => 'Kommagescheiden.']],
            ],
        ],
        'news' => [
            'table'    => 'f1_nieuws',
            'pk'       => ['id'],
            'singular' => 'artikel',
            'plural'   => 'Nieuws',
            'nav'      => 'news',
            'order'    => 'publicatie_datum DESC, id DESC',
            'search'   => ['titel', 'source'],
            'title'    => static fn(array $r) => $r['titel'],
            'public'   => static fn(array $r) => $r['artikel_url'],
            'columns'  => [
                'titel'            => 'Titel',
                'source'           => 'Bron',
                'publicatie_datum' => 'Gepubliceerd',
            ],
            'fields'   => [
                'titel'            => ['Titel', 'text', ['required' => true, 'max' => 255, 'wide' => true]],
                'artikel_url'      => ['Artikel (URL)', 'url', ['required' => true, 'max' => 512, 'wide' => true]],
                'afbeelding_url'   => ['Afbeelding (URL)', 'url', ['max' => 512, 'wide' => true]],
                'source'           => ['Bron', 'text', ['max' => 255]],
                'publicatie_datum' => ['Publicatiedatum', 'datetime', ['required' => true]],
            ],
        ],
    ];
}

function admin_entity(string $type): array
{
    $entities = admin_entities();
    if (!isset($entities[$type])) {
        http_response_code(404);
        exit('Onbekend type.');
    }
    return $entities[$type] + ['type' => $type];
}

/** Validate and convert submitted values. Returns [values, errors]. */
function admin_collect(array $entity, array $input, bool $isNew): array
{
    $values = $errors = [];
    foreach ($entity['fields'] as $col => [$label, $type, $opt]) {
        if (!empty($opt['key']) && !$isNew) {
            continue;
        }
        $raw = $input[$col] ?? null;
        $raw = is_string($raw) ? trim($raw) : $raw;
        if ($type === 'checkbox') {
            $values[$col] = $raw ? 1 : 0;
            continue;
        }
        if (($raw === null || $raw === '') && !empty($opt['required'])) {
            $errors[] = "$label is verplicht.";
            continue;
        }
        if ($raw === null || $raw === '') {
            $values[$col] = null;
            continue;
        }
        if (isset($opt['max']) && mb_strlen((string)$raw) > $opt['max']) {
            $errors[] = "$label mag maximaal {$opt['max']} tekens bevatten.";
            continue;
        }
        switch ($type) {
            case 'number':
                if (!preg_match('/^-?\d+$/', (string)$raw)) { $errors[] = "$label moet een geheel getal zijn."; continue 2; }
                $values[$col] = (int)$raw;
                break;
            case 'decimal':
                $raw = str_replace(',', '.', (string)$raw);
                if (!is_numeric($raw)) { $errors[] = "$label moet een getal zijn."; continue 2; }
                $values[$col] = $raw;
                break;
            case 'date':
                if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$raw)) { $errors[] = "$label is geen geldige datum."; continue 2; }
                $values[$col] = $raw;
                break;
            case 'datetime':
                $utc = admin_to_utc((string)$raw);
                if (!$utc) { $errors[] = "$label is geen geldige datum/tijd."; continue 2; }
                $values[$col] = $utc;
                break;
            case 'color':
                if (!preg_match('/^#[0-9a-f]{6}$/i', (string)$raw)) { $errors[] = "$label moet een kleur zijn (#rrggbb)."; continue 2; }
                $values[$col] = strtolower((string)$raw);
                break;
            case 'url':
                if (!preg_match('#^https?://#i', (string)$raw) || !filter_var($raw, FILTER_VALIDATE_URL)) { $errors[] = "$label moet een geldige http(s)-URL zijn."; continue 2; }
                $values[$col] = $raw;
                break;
            case 'select':
                $options = call_user_func($opt['options']);
                if (!array_key_exists($raw, $options)) { $errors[] = "$label heeft een ongeldige waarde."; continue 2; }
                $values[$col] = $raw === '' ? null : $raw;
                break;
            default:
                $values[$col] = $raw;
        }
    }
    if ($entity['type'] === 'circuits' && $isNew && isset($values['circuit_key']) && !preg_match('/^[a-z0-9_]+$/', (string)$values['circuit_key'])) {
        $errors[] = 'Circuit-sleutel mag alleen kleine letters, cijfers en _ bevatten.';
    }
    return [$values, $errors];
}

/** Read the primary key of the requested record from the query string. */
function admin_pk(array $entity, array $source): ?array
{
    $pk = [];
    foreach ($entity['pk'] as $col) {
        $v = $source[$col] ?? null;
        if ($v === null || $v === '' || is_array($v)) {
            return null;
        }
        $pk[$col] = $v;
    }
    return $pk;
}

function admin_find(array $entity, array $pk): ?array
{
    $where = implode(' AND ', array_map(static fn($c) => "`$c` = ?", array_keys($pk)));
    return db_one("SELECT * FROM `{$entity['table']}` WHERE $where", array_values($pk));
}

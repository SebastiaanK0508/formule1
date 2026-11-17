<?php
/**
 * Daily quiz: five questions about randomly picked historic races. Everyone
 * gets the same questions on the same day because the random generator is
 * seeded with the date.
 */
declare(strict_types=1);

/** @return list<array{text:string,answer:string,options:list<array>,reveal:string,race:array}> */
function daily_quiz(string $today): array
{
    mt_srand(crc32('f1quiz-' . $today));

    $questions = [];
    $usedRaces = [];
    $types = ['winner', 'pole', 'team', 'winner', 'pole'];
    $attempts = 0;
    while (count($questions) < 5 && $attempts++ < 15) {
        $year = mt_rand(1980, latest_results_season() - 1);
        $schedule = f1_schedule($year);
        if (!$schedule) {
            continue;
        }
        $round = array_keys($schedule)[mt_rand(0, count($schedule) - 1)];
        if (isset($usedRaces["$year-$round"])) {
            continue;
        }
        $race = f1_race($year, $round);
        if (!$race || count($race['rows']) < 6) {
            continue;
        }
        $usedRaces["$year-$round"] = true;
        $rows = $race['rows'];
        $meta = $race['race'];
        $type = $types[count($questions)];

        if ($type === 'team') {
            $teamsSeen = [];
            foreach ($rows as $r) {
                $teamsSeen[$r['constructorId']] ??= $r;
            }
            if (count($teamsSeen) < 4) {
                continue;
            }
            $correct = $rows[0];
            $pool = array_values(array_filter($teamsSeen, static fn($r) => $r['constructorId'] !== $correct['constructorId']));
            $options = array_merge([$correct], array_slice($pool, 0, 3));
            $q = [
                'text'    => "Which team won the {$year} {$meta['name']}?",
                'answer'  => $correct['constructorId'],
                'options' => array_map(static fn($r) => ['value' => $r['constructorId'], 'label' => $r['constructor'], 'color' => $r['color']], $options),
                'reveal'  => "{$correct['constructor']} — {$correct['name']} took the win" . ($correct['time'] ? " in {$correct['time']}" : '') . '.',
            ];
        } else {
            $correct = $rows[0];
            if ($type === 'pole') {
                $correct = null;
                foreach ($rows as $r) {
                    if ($r['grid'] === 1) { $correct = $r; }
                }
                if (!$correct) {
                    continue;
                }
            }
            $pool = array_values(array_filter(array_slice($rows, 0, 10), static fn($r) => $r['driverId'] !== $correct['driverId']));
            $picks = array_rand($pool, 3);
            $options = array_merge([$correct], array_map(static fn($i) => $pool[$i], (array)$picks));
            $q = [
                'text'    => $type === 'pole' ? "Who started from pole position at the {$year} {$meta['name']}?" : "Who won the {$year} {$meta['name']}?",
                'answer'  => $correct['driverId'],
                'options' => array_map(static fn($r) => ['value' => $r['driverId'], 'label' => $r['name'], 'color' => $r['color']], $options),
                'reveal'  => $type === 'pole'
                    ? "{$correct['name']} ({$correct['constructor']}) started on pole and finished " . ($correct['classified'] ? 'P' . $correct['position'] : 'as a retirement') . '.'
                    : "{$correct['name']} won for {$correct['constructor']}, ahead of {$rows[1]['name']} and {$rows[2]['name']}.",
            ];
        }
        // Shuffle the options deterministically.
        for ($i = count($q['options']) - 1; $i > 0; $i--) {
            $j = mt_rand(0, $i);
            [$q['options'][$i], $q['options'][$j]] = [$q['options'][$j], $q['options'][$i]];
        }
        $q['race'] = $meta;
        $questions[] = $q;
    }
    mt_srand();
    return $questions;
}

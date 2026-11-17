<?php
declare(strict_types=1);
require __DIR__ . '/_bootstrap.php';
require __DIR__ . '/_entities.php';
require_login();

$entity = admin_entity(str_param('type'));
$table = $entity['table'];
$q = mb_substr(str_param('q'), 0, 80);

$where = [];
$params = [];
$filterValue = null;
$filterOptions = [];
if (!empty($entity['filter'])) {
    $col = $entity['filter'];
    $filterOptions = array_map('intval', array_column(db_all("SELECT DISTINCT `$col` FROM `$table` ORDER BY `$col` DESC"), $col));
    $filterValue = int_param('year', in_array(current_season(), $filterOptions, true) ? current_season() : ($filterOptions[0] ?? current_season()));
    $where[] = "`$col` = ?";
    $params[] = $filterValue;
}
if ($q !== '') {
    $like = '%' . addcslashes($q, '%_\\') . '%';
    $where[] = '(' . implode(' OR ', array_map(static fn($c) => "`$c` LIKE ?", $entity['search'])) . ')';
    array_push($params, ...array_fill(0, count($entity['search']), $like));
}
$sql = "SELECT * FROM `$table`" . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . " ORDER BY {$entity['order']} LIMIT 500";
$rows = db_all($sql, $params);
$teams = db_teams();

$editUrl = static function (array $row) use ($entity): string {
    $pk = [];
    foreach ($entity['pk'] as $c) {
        $pk[$c] = $row[$c];
    }
    return admin_url('edit.php', ['type' => $entity['type']] + $pk);
};

admin_header($entity['plural'], $entity['nav']);
?>
<div class="toolbar">
    <form class="row-wrap" method="get" action="<?= e(admin_url('list.php')) ?>">
        <input type="hidden" name="type" value="<?= e($entity['type']) ?>">
        <?php if ($filterOptions): ?>
            <select class="select select-sm" name="year" data-autosubmit style="width: auto">
                <?php foreach ($filterOptions as $y): ?><option value="<?= $y ?>"<?= $y === $filterValue ? ' selected' : '' ?>><?= $y ?></option><?php endforeach; ?>
            </select>
        <?php endif; ?>
        <input class="input select-sm" type="search" name="q" value="<?= e($q) ?>" placeholder="Zoeken…" style="width: 240px">
        <button class="btn btn-secondary btn-sm" type="submit"><?= icon('search', 'icon-sm') ?></button>
    </form>
    <a class="btn btn-primary btn-sm" href="<?= e(admin_url('edit.php', ['type' => $entity['type'], 'race_year' => $filterValue])) ?>"><?= icon('plus', 'icon-sm') ?> Nieuwe <?= e($entity['singular']) ?></a>
</div>

<div class="card table-wrap">
    <table class="table">
        <thead><tr>
            <?php foreach ($entity['columns'] as $label): ?><th><?= e($label) ?></th><?php endforeach; ?>
            <th class="num"></th>
        </tr></thead>
        <tbody>
        <?php if (!$rows): ?>
            <tr><td colspan="<?= count($entity['columns']) + 1 ?>"><div class="empty">Geen resultaten.</div></td></tr>
        <?php endif; ?>
        <?php foreach ($rows as $r): ?>
            <tr>
                <?php foreach ($entity['columns'] as $col => $label): $v = $r[$col] ?? null; ?>
                    <td>
                        <?php if ($col === 'is_active'): ?>
                            <?= $v ? '<span class="badge badge-green">Actief</span>' : '<span class="badge">Inactief</span>' ?>
                        <?php elseif ($col === 'team_id'): ?>
                            <?php $t = $teams[(int)$v] ?? null; ?>
                            <?= $t ? '<span class="team-name"><span class="swatch" style="--c:' . e(safe_color($t['team_color'])) . '"></span>' . e($t['team_name']) . '</span>' : '<span class="subtle">—</span>' ?>
                        <?php elseif ($col === 'team_color'): ?>
                            <span class="swatch" style="--c: <?= e(safe_color($v)) ?>; height: 24px"></span>
                        <?php elseif ($col === 'fp1_datetime'): ?>
                            <?php $n = count(weekend_sessions($r)); ?>
                            <span class="badge<?= $n >= 5 ? ' badge-green' : '' ?>"><?= $n ?>/5</span>
                        <?php elseif ($col === 'sprint_datetime'): ?>
                            <?= $v ? '<span class="badge badge-red">Sprint</span>' : '' ?>
                        <?php elseif (str_ends_with($col, '_datetime') || $col === 'publicatie_datum'): ?>
                            <span class="nowrap"><?= e(($d = db_datetime($v)) ? fmt_date($d, 'D j M Y, H:i') : '—') ?></span>
                        <?php elseif ($col === array_key_first($entity['columns']) || in_array($col, ['titel', 'grandprix', 'last_name', 'team_name'], true)): ?>
                            <a class="fw-600 link-plain" href="<?= e($editUrl($r)) ?>"><?= e($v ?? '—') ?></a>
                        <?php else: ?>
                            <?= e($v ?? '—') ?>
                        <?php endif; ?>
                    </td>
                <?php endforeach; ?>
                <td class="num nowrap">
                    <a class="btn btn-ghost btn-sm" href="<?= e($editUrl($r)) ?>"><?= icon('edit', 'icon-sm') ?> Bewerken</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<p class="text-xs subtle mt-2"><?= count($rows) ?> rij<?= count($rows) === 1 ? '' : 'en' ?><?= count($rows) === 500 ? ' (maximaal 500 getoond — verfijn je zoekopdracht)' : '' ?>.</p>
<?php admin_footer();

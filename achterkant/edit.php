<?php
declare(strict_types=1);
require __DIR__ . '/_bootstrap.php';
require __DIR__ . '/_entities.php';
require_login();

$entity = admin_entity(str_param('type'));
$table = $entity['table'];
$pk = admin_pk($entity, $_GET);
$record = $pk ? admin_find($entity, $pk) : null;
if ($pk && !$record) {
    // For new circuits the year is passed as a default, not as an existing key.
    if ($entity['type'] === 'circuits' && empty($_GET['circuit_key'])) {
        $pk = null;
    } else {
        flash('error', 'Dit item bestaat niet (meer).');
        redirect(admin_url('list.php', ['type' => $entity['type']]));
    }
}
$isNew = $record === null;
$listUrl = admin_url('list.php', ['type' => $entity['type'], 'year' => $record['race_year'] ?? ($_GET['race_year'] ?? null)]);
$errors = [];
$values = $record ?? [];
if ($isNew && $entity['type'] === 'circuits') {
    $values['race_year'] = int_param('race_year', current_season());
}
if ($isNew && $entity['type'] === 'drivers') {
    $values['is_active'] = 1;
}
if ($isNew && $entity['type'] === 'teams') {
    $values['is_active'] = 1;
    $values['team_color'] = '#e10600';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_post();

    if (isset($_POST['delete']) && $record) {
        try {
            $where = implode(' AND ', array_map(static fn($c) => "`$c` = ?", array_keys($pk)));
            db()->prepare("DELETE FROM `$table` WHERE $where")->execute(array_values($pk));
            flash('success', ucfirst($entity['singular']) . ' verwijderd.');
        } catch (PDOException $e) {
            flash('error', 'Verwijderen is niet gelukt: dit item wordt nog ergens anders gebruikt.');
            redirect(admin_url('edit.php', ['type' => $entity['type']] + $pk));
        }
        redirect($listUrl);
    }

    [$data, $errors] = admin_collect($entity, $_POST, $isNew);
    if (!$errors) {
        try {
            if ($isNew) {
                $cols = array_keys($data);
                db()->prepare(sprintf(
                    'INSERT INTO `%s` (%s) VALUES (%s)',
                    $table,
                    implode(', ', array_map(static fn($c) => "`$c`", $cols)),
                    implode(', ', array_fill(0, count($cols), '?'))
                ))->execute(array_values($data));
                $newPk = [];
                foreach ($entity['pk'] as $c) {
                    $newPk[$c] = $data[$c] ?? db()->lastInsertId();
                }
                flash('success', ucfirst($entity['singular']) . ' aangemaakt.');
                redirect(admin_url('edit.php', ['type' => $entity['type']] + $newPk));
            }
            $set = implode(', ', array_map(static fn($c) => "`$c` = ?", array_keys($data)));
            $where = implode(' AND ', array_map(static fn($c) => "`$c` = ?", array_keys($pk)));
            db()->prepare("UPDATE `$table` SET $set WHERE $where")->execute([...array_values($data), ...array_values($pk)]);
            flash('success', 'Wijzigingen opgeslagen.');
            redirect(admin_url('edit.php', ['type' => $entity['type']] + $pk));
        } catch (PDOException $e) {
            error_log('Admin save failed: ' . $e->getMessage());
            $errors[] = $e->getCode() === '23000'
                ? 'Opslaan mislukt: een unieke waarde (zoals startnummer, naam of sleutel) bestaat al.'
                : 'Opslaan mislukt door een databasefout.';
        }
    }
    // Re-show the submitted values (datetimes are converted back for display).
    $values = array_merge($values, $_POST);
    foreach ($entity['fields'] as $col => [, $type]) {
        if ($type === 'checkbox') {
            $values[$col] = isset($_POST[$col]) ? 1 : 0;
        }
    }
}

$title = $isNew ? 'Nieuwe ' . $entity['singular'] : $entity['title']($record);
admin_header($title, $entity['nav']);
?>
<div class="toolbar">
    <a class="btn btn-ghost btn-sm" href="<?= e($listUrl) ?>"><?= icon('arrow-left', 'icon-sm') ?> Terug naar <?= e(strtolower($entity['plural'])) ?></a>
    <?php if (!$isNew): ?>
        <div class="row-wrap">
            <a class="btn btn-secondary btn-sm" href="<?= e($entity['public']($record)) ?>" target="_blank" rel="noopener"><?= icon('eye', 'icon-sm') ?> Bekijk op website</a>
            <?= delete_form(admin_url('edit.php', ['type' => $entity['type']] + $pk), ['delete' => 1], 'Verwijderen', 'Weet je zeker dat je "' . $title . '" wilt verwijderen? Dit kan niet ongedaan worden gemaakt.') ?>
        </div>
    <?php endif; ?>
</div>

<?php if ($errors): ?>
    <div class="alert alert-error mb-4"><?= icon('alert') ?><div><?php foreach ($errors as $err): ?><div><?= e($err) ?></div><?php endforeach; ?></div></div>
<?php endif; ?>

<form class="card card-pad" method="post">
    <?= csrf_field() ?>
    <div class="form-grid">
        <?php foreach ($entity['fields'] as $col => [$label, $type, $opt]):
            $v = $values[$col] ?? '';
            $locked = !empty($opt['key']) && !$isNew;
            $req = !empty($opt['required']) ? ' required' : '';
            $max = isset($opt['max']) ? ' maxlength="' . (int)$opt['max'] . '"' : '';
            $id = 'f_' . $col; ?>
            <div class="field<?= !empty($opt['wide']) || $type === 'textarea' ? ' wide' : '' ?>">
                <?php if ($type === 'checkbox'): ?>
                    <label class="check"><input type="checkbox" name="<?= e($col) ?>" value="1"<?= $v ? ' checked' : '' ?>> <?= e($label) ?></label>
                <?php else: ?>
                    <label for="<?= e($id) ?>"><?= e($label) ?><?= $req ? ' <span class="accent">*</span>' : '' ?><?= $type === 'datetime' ? ' <span class="subtle fw-600">(NL-tijd)</span>' : '' ?></label>
                    <?php if ($locked): ?>
                        <input class="input" id="<?= e($id) ?>" value="<?= e($v) ?>" disabled>
                    <?php elseif ($type === 'textarea'): ?>
                        <textarea class="textarea" id="<?= e($id) ?>" name="<?= e($col) ?>"<?= $req . $max ?>><?= e($v) ?></textarea>
                    <?php elseif ($type === 'select'): ?>
                        <select class="select" id="<?= e($id) ?>" name="<?= e($col) ?>"<?= $req ?>>
                            <?php foreach (call_user_func($opt['options']) as $ov => $ol): ?>
                                <option value="<?= e($ov) ?>"<?= (string)$ov === (string)$v ? ' selected' : '' ?>><?= e($ol) ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php elseif ($type === 'datetime'): ?>
                        <input class="input" id="<?= e($id) ?>" type="datetime-local" name="<?= e($col) ?>" value="<?= e(str_contains((string)$v, 'T') ? $v : admin_from_utc((string)$v)) ?>"<?= $req ?>>
                    <?php elseif ($type === 'color'): ?>
                        <div class="row"><input type="color" value="<?= e(safe_color((string)$v, '#e10600')) ?>" oninput="this.nextElementSibling.value = this.value" aria-label="Kleurkiezer">
                        <input class="input" id="<?= e($id) ?>" name="<?= e($col) ?>" value="<?= e($v) ?>" pattern="#[0-9a-fA-F]{6}"<?= $req ?>></div>
                    <?php else:
                        $htmlType = ['number' => 'number', 'decimal' => 'text', 'date' => 'date', 'url' => 'url'][$type] ?? 'text'; ?>
                        <input class="input" id="<?= e($id) ?>" type="<?= $htmlType ?>" name="<?= e($col) ?>" value="<?= e($v) ?>"<?= $req . $max ?><?= $type === 'decimal' ? ' inputmode="decimal"' : '' ?>>
                    <?php endif; ?>
                    <?php if ($type === 'url' && $v && preg_match('/(image|flag|logo|map|afbeelding)/', $col)): ?>
                        <img class="preview" src="<?= e($v) ?>" alt="" loading="lazy" onerror="this.remove()">
                    <?php endif; ?>
                <?php endif; ?>
                <?php if (!empty($opt['help'])): ?><small class="subtle text-xs"><?= e($opt['help']) ?></small><?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="spread mt-6">
        <span class="text-xs subtle">Velden met * zijn verplicht.</span>
        <button class="btn btn-primary" type="submit"><?= icon('check', 'icon-sm') ?> <?= $isNew ? 'Aanmaken' : 'Opslaan' ?></button>
    </div>
</form>
<?php admin_footer();

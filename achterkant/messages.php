<?php
declare(strict_types=1);
require __DIR__ . '/_bootstrap.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_post();
    $action = (string)($_POST['action'] ?? '');
    $id = (int)($_POST['id'] ?? 0);
    if ($action === 'delete' && $id) {
        db()->prepare('DELETE FROM contact WHERE ID = ?')->execute([$id]);
        flash('success', 'Bericht verwijderd.');
    } elseif ($action === 'unread' && $id) {
        db()->prepare('UPDATE contact SET is_read = 0 WHERE ID = ?')->execute([$id]);
        flash('success', 'Gemarkeerd als ongelezen.');
    } elseif ($action === 'read_all') {
        db()->exec('UPDATE contact SET is_read = 1');
        flash('success', 'Alle berichten gemarkeerd als gelezen.');
    }
    redirect(admin_url('messages.php'));
}

$current = null;
if (isset($_GET['id'])) {
    $current = db_one('SELECT * FROM contact WHERE ID = ?', [int_param('id', 0)]);
    if ($current && !$current['is_read']) {
        db()->prepare('UPDATE contact SET is_read = 1 WHERE ID = ?')->execute([$current['ID']]);
    }
}
$messages = db_all('SELECT * FROM contact ORDER BY ID DESC LIMIT 300');

admin_header('Berichten', 'messages');
?>
<div class="toolbar">
    <span class="muted text-sm"><?= count($messages) ?> bericht<?= count($messages) === 1 ? '' : 'en' ?></span>
    <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="read_all"><button class="btn btn-secondary btn-sm" type="submit"><?= icon('check', 'icon-sm') ?> Alles als gelezen markeren</button></form>
</div>
<div class="inbox">
    <div class="card inbox-list">
        <?php if (!$messages): ?><div class="empty"><?= icon('mail', 'icon-lg') ?><h3>Geen berichten</h3></div><?php endif; ?>
        <?php foreach ($messages as $m): ?>
            <a class="result-item<?= $current && (int)$current['ID'] === (int)$m['ID'] ? ' is-selected' : '' ?>" href="<?= e(admin_url('messages.php', ['id' => $m['ID']])) ?>">
                <span class="dot" style="--c: <?= $m['is_read'] ? 'var(--border-strong)' : 'var(--red)' ?>"></span>
                <div class="truncate" style="min-width: 0">
                    <div class="<?= $m['is_read'] ? '' : 'fw-700' ?> truncate"><?= e($m['contact_subject'] ?: '(geen onderwerp)') ?></div>
                    <div class="text-xs muted truncate"><?= e($m['contact_name']) ?> — <?= e(mb_substr((string)$m['contact_message'], 0, 80)) ?></div>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
    <div class="card card-pad inbox-view">
        <?php if ($current): ?>
            <div class="spread mb-4">
                <div>
                    <h2 class="text-lg"><?= e($current['contact_subject'] ?: '(geen onderwerp)') ?></h2>
                    <div class="muted text-sm mt-1"><?= e($current['contact_name']) ?> &lt;<a href="mailto:<?= e($current['contact_email']) ?>"><?= e($current['contact_email']) ?></a>&gt;<?= !empty($current['created_at']) ? ' · ' . e($current['created_at']) : '' ?></div>
                </div>
                <div class="row-wrap">
                    <a class="btn btn-primary btn-sm" href="mailto:<?= e($current['contact_email']) ?>?subject=<?= rawurlencode('Re: ' . $current['contact_subject']) ?>"><?= icon('mail', 'icon-sm') ?> Beantwoorden</a>
                    <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="unread"><input type="hidden" name="id" value="<?= (int)$current['ID'] ?>"><button class="btn btn-secondary btn-sm" type="submit">Markeer ongelezen</button></form>
                    <?= delete_form(admin_url('messages.php'), ['action' => 'delete', 'id' => (int)$current['ID']], 'Verwijderen', 'Dit bericht definitief verwijderen?') ?>
                </div>
            </div>
            <div class="prose" style="max-width: none; white-space: pre-wrap"><?= e($current['contact_message']) ?></div>
        <?php else: ?>
            <div class="empty"><?= icon('mail', 'icon-lg') ?><h3>Selecteer een bericht</h3></div>
        <?php endif; ?>
    </div>
</div>
<?php admin_footer();

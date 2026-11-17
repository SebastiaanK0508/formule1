<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';
start_session();

$errors = [];
$old = ['name' => '', 'email' => '', 'subject' => '', 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($old as $k => $_) {
        $old[$k] = trim((string)($_POST[$k] ?? ''));
    }

    if (!csrf_valid()) {
        $errors[] = 'Your session expired. Please submit the form again.';
    }
    // Honeypot field: real visitors never fill it in.
    if (!empty($_POST['website'])) {
        redirect(url('contact.php'));
    }
    if (time() - (int)($_SESSION['contact_last'] ?? 0) < 60) {
        $errors[] = 'Please wait a minute before sending another message.';
    }
    if (mb_strlen($old['name']) < 2 || mb_strlen($old['name']) > 100) {
        $errors[] = 'Please enter your name.';
    }
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($old['email']) > 200) {
        $errors[] = 'Please enter a valid email address.';
    }
    if (mb_strlen($old['subject']) < 2 || mb_strlen($old['subject']) > 150) {
        $errors[] = 'Please enter a subject.';
    }
    if (mb_strlen($old['message']) < 10 || mb_strlen($old['message']) > 5000) {
        $errors[] = 'Your message should be between 10 and 5,000 characters.';
    }

    if (!$errors) {
        try {
            db()->prepare('INSERT INTO contact (contact_name, contact_email, contact_subject, contact_message) VALUES (?, ?, ?, ?)')
                ->execute([$old['name'], $old['email'], $old['subject'], $old['message']]);
            $_SESSION['contact_last'] = time();
            discord_admin('📬 New contact message: ' . $old['subject'], mb_substr($old['message'], 0, 600) . (mb_strlen($old['message']) > 600 ? '…' : ''), [
                ['name' => 'From', 'value' => $old['name'], 'inline' => true],
            ], (string)config('admin_url', '') !== '' ? rtrim((string)config('admin_url'), '/') . '/messages.php' : null);
            flash('success', 'Thanks — your message has been sent. We usually reply within a few days.');
            redirect(url('contact.php'));
        } catch (PDOException $e) {
            error_log('Contact form failed: ' . $e->getMessage());
            $errors[] = 'Something went wrong while sending your message. Please try again later.';
        }
    }
}

$page = [
    'title'       => 'Contact',
    'description' => 'Questions, corrections or feedback about F1SITE.NL? Send us a message.',
];
require INC_DIR . '/layout/header.php';
?>
<div class="container" style="max-width: 760px">
    <header class="page-head">
        <div class="eyebrow">Get in touch</div>
        <h1 class="page-title">Contact</h1>
        <p class="page-lead">Spotted an error, have a suggestion or a business enquiry? Send a message and we will get back to you.</p>
    </header>

    <section class="section" style="padding-top: 16px">
        <?php if ($errors): ?>
            <div class="alert alert-error mb-4" role="alert">
                <?= icon('alert') ?>
                <div><?php foreach ($errors as $err): ?><div><?= e($err) ?></div><?php endforeach; ?></div>
            </div>
        <?php endif; ?>
        <form class="card card-pad stack" method="post" action="<?= e(url('contact.php')) ?>" novalidate>
            <?= csrf_field() ?>
            <div class="sr-only" aria-hidden="true"><label for="website">Website</label><input id="website" name="website" tabindex="-1" autocomplete="off"></div>
            <div class="grid-2" style="gap: 16px">
                <div class="field"><label for="name">Name</label><input class="input" id="name" name="name" value="<?= e($old['name']) ?>" required maxlength="100" autocomplete="name"></div>
                <div class="field"><label for="email">Email</label><input class="input" id="email" type="email" name="email" value="<?= e($old['email']) ?>" required maxlength="200" autocomplete="email"></div>
            </div>
            <div class="field"><label for="subject">Subject</label><input class="input" id="subject" name="subject" value="<?= e($old['subject']) ?>" required maxlength="150"></div>
            <div class="field"><label for="message">Message</label><textarea class="textarea" id="message" name="message" required minlength="10" maxlength="5000" rows="7"><?= e($old['message']) ?></textarea></div>
            <div class="spread">
                <p class="text-xs subtle">We only use your details to reply. See our <a href="<?= e(url('privacy-en.php')) ?>">privacy policy</a>.</p>
                <button class="btn btn-primary" type="submit"><?= icon('mail', 'icon-sm') ?> Send message</button>
            </div>
        </form>
    </section>
</div>
<?php require INC_DIR . '/layout/footer.php'; ?>

<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';

if (!headers_sent()) {
    http_response_code(404);
}
$page = ['title' => 'Page not found', 'noindex' => true];
require INC_DIR . '/layout/header.php';
?>
<div class="container">
    <section class="section">
        <div class="card empty" style="padding: 64px 24px">
            <div class="page-title accent" style="font-size: 96px">404</div>
            <h1 class="mt-3" style="font-size: 22px">Off track</h1>
            <p class="mt-2"><?= e($notFoundMessage ?? 'The page you are looking for does not exist or has been moved.') ?></p>
            <div class="row-wrap mt-6" style="justify-content: center">
                <a class="btn btn-primary" href="<?= e(url()) ?>">Back to home</a>
                <a class="btn btn-secondary" href="<?= e(url('zoek.php')) ?>">Search the site</a>
            </div>
        </div>
    </section>
</div>
<?php require INC_DIR . '/layout/footer.php'; ?>

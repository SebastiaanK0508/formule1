<?php
/**
 * Shared layout for legal pages. Expects $legal = [eyebrow, title, intro, updated, sections => [heading => html]].
 */
declare(strict_types=1);
$page = ['title' => $legal['title'], 'description' => $legal['intro']];
require INC_DIR . '/layout/header.php';
?>
<div class="container">
    <header class="page-head">
        <div class="eyebrow"><?= e($legal['eyebrow']) ?></div>
        <h1 class="page-title"><?= e($legal['title']) ?></h1>
        <p class="page-lead"><?= e($legal['intro']) ?></p>
    </header>
    <section class="section" style="padding-top: 8px">
        <div class="layout-sidebar layout-sidebar-left">
            <nav class="card card-pad hide-sm" aria-label="On this page" style="position: sticky; top: calc(var(--header-h) + 16px)">
                <div class="label mb-2">On this page</div>
                <ol class="text-sm" style="padding-left: 18px; margin: 0; display: grid; gap: 6px">
                    <?php foreach (array_keys($legal['sections']) as $heading): ?>
                        <li><a class="link-plain muted" href="#<?= e(slugify($heading)) ?>"><?= e($heading) ?></a></li>
                    <?php endforeach; ?>
                </ol>
            </nav>
            <article class="card card-pad prose" style="max-width: none">
                <?php foreach ($legal['sections'] as $heading => $html): ?>
                    <h2 id="<?= e(slugify($heading)) ?>"><?= e($heading) ?></h2>
                    <?= $html ?>
                <?php endforeach; ?>
                <p class="text-sm subtle mt-8">Last updated: <?= e($legal['updated']) ?></p>
            </article>
        </div>
    </section>
</div>
<?php require INC_DIR . '/layout/footer.php'; ?>

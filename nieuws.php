<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$perPage = 12;
$total = (int)db_value('SELECT COUNT(*) FROM f1_nieuws');
$pages = max(1, (int)ceil($total / $perPage));
$current = int_param('page', 1, 1, $pages);
$articles = db_all(
    'SELECT titel, artikel_url, publicatie_datum, afbeelding_url, source FROM f1_nieuws ORDER BY publicatie_datum DESC, id DESC LIMIT ' . $perPage . ' OFFSET ' . (($current - 1) * $perPage)
);

$page = [
    'title'       => 'Formula 1 News' . ($current > 1 ? " — page $current" : ''),
    'description' => 'The latest Formula 1 headlines from leading motorsport publications, updated throughout the day.',
    'nav'         => 'news',
];
require INC_DIR . '/layout/header.php';
?>
<div class="container">
    <header class="page-head">
        <div class="eyebrow">Headlines</div>
        <h1 class="page-title">Latest news</h1>
        <p class="page-lead">The most recent Formula 1 stories from the leading motorsport publications. Articles open on the publisher's website.</p>
    </header>

    <section class="section" style="padding-top: 16px">
        <?php if (!$articles): ?>
            <div class="card empty"><?= icon('newspaper', 'icon-lg') ?><h3>No news right now</h3><p>New headlines are added several times a day.</p></div>
        <?php else: ?>
            <div class="grid-3">
                <?php foreach ($articles as $article): require INC_DIR . '/partials/news-card.php'; endforeach; ?>
            </div>
            <?php if ($pages > 1): ?>
            <nav class="pagination" aria-label="Pagination">
                <?php if ($current > 1): ?><a href="<?= e(url('nieuws.php', ['page' => $current - 1])) ?>" aria-label="Previous page"><?= icon('chevron-left', 'icon-sm') ?></a><?php endif; ?>
                <?php for ($p = max(1, $current - 2); $p <= min($pages, $current + 2); $p++): ?>
                    <?php if ($p === $current): ?><span class="is-active" aria-current="page"><?= $p ?></span>
                    <?php else: ?><a href="<?= e(url('nieuws.php', ['page' => $p > 1 ? $p : null])) ?>"><?= $p ?></a><?php endif; ?>
                <?php endfor; ?>
                <?php if ($current < $pages): ?><a href="<?= e(url('nieuws.php', ['page' => $current + 1])) ?>" aria-label="Next page"><?= icon('chevron-right', 'icon-sm') ?></a><?php endif; ?>
            </nav>
            <?php endif; ?>
        <?php endif; ?>
    </section>
</div>
<?php require INC_DIR . '/layout/footer.php'; ?>

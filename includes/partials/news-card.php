<?php
/**
 * News card used on the home page and the news archive.
 */
declare(strict_types=1);

/**
 * @param array{titel:string, artikel_url:string, publicatie_datum:?string, afbeelding_url:?string, source:?string} $article
 *        A row from the f1_nieuws table.
 */
function news_card(array $article): void
{
    $published = $article['publicatie_datum'] ? db_datetime($article['publicatie_datum']) : null;
    ?>
    <a class="card card-link news-card" href="<?= e($article['artikel_url']) ?>" target="_blank" rel="noopener">
        <div class="thumb">
            <?php if (!empty($article['afbeelding_url'])): ?>
                <img src="<?= e($article['afbeelding_url']) ?>" alt="" loading="lazy" onerror="this.remove()">
            <?php endif; ?>
        </div>
        <div class="body">
            <div class="source">
                <span><?= e($article['source'] ?: 'News') ?></span>
                <?php if ($published): ?><?= local_time($published, 'date') ?><?php endif; ?>
            </div>
            <h3><?= e($article['titel']) ?></h3>
        </div>
    </a>
    <?php
}

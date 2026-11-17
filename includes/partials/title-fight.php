<?php
/**
 * Championship battle card used on the home and standings pages.
 */
declare(strict_types=1);

/** @param array|null $fight Result of f1_title_fight(); nothing is rendered for null. */
function title_fight_card(?array $fight): void
{
    if (!$fight) {
        return;
    }
    $isTeams = $fight['mode'] === 'constructors';
    $leader = $fight['leader'];
    $nameOf = static fn(array $r): string => $isTeams ? e($r['constructor']) : driver_link($r);
    $left = count($fight['remaining']);
    ?>
    <div class="card title-fight accent-top" style="--c: <?= e($leader['color']) ?>">
        <?php if ($fight['decided']): ?>
            <div class="card-pad champion">
                <div class="champion-trophy"><?= icon('trophy', 'icon-lg') ?></div>
                <div>
                    <div class="eyebrow" style="margin-bottom: 4px"><?= e($fight['year']) ?> <?= $isTeams ? "Constructors'" : 'World' ?> Champion<?= $left ? ' · clinched' : '' ?></div>
                    <div class="champion-name"><?= $isTeams ? e($leader['constructor']) : e($leader['givenName']) . ' <strong>' . e($leader['familyName']) . '</strong>' ?></div>
                    <p class="muted text-sm mt-1">
                        <?= e(fmt_number($leader['points'])) ?> points · <?= (int)$leader['wins'] ?> wins<?php if ($left): ?> · <?= e(fmt_number($leader['points'] - $fight['second']['points'])) ?> clear with only <?= (int)$fight['maxRemaining'] ?> points left to win in <?= $left ?> race<?= $left === 1 ? '' : 's' ?><?php endif; ?>.
                    </p>
                </div>
            </div>
        <?php else: ?>
            <div class="card-head">
                <div>
                    <h2><?= $isTeams ? "Constructors'" : 'Title' ?> fight</h2>
                    <div class="meta"><?= $left ?> race<?= $left === 1 ? '' : 's' ?> left · up to <?= (int)$fight['maxRemaining'] ?> points still available</div>
                </div>
                <span class="badge badge-red"><?= count($fight['contenders']) ?> in contention</span>
            </div>
            <div class="card-pad stack" style="gap: 14px">
                <?php foreach ($fight['contenders'] as $c):
                    $reach = $c['maxPossible'] > 0 ? min(100, round($c['points'] / $c['maxPossible'] * 100)) : 0; ?>
                    <div class="contender">
                        <div class="spread text-sm">
                            <span class="row" style="gap: 8px"><span class="swatch" style="--c: <?= e($c['color']) ?>"></span><?= $nameOf($c) ?></span>
                            <span><strong class="tabular"><?= e(fmt_number($c['points'])) ?></strong> <span class="subtle">/ max <?= e(fmt_number($c['maxPossible'])) ?></span></span>
                        </div>
                        <div class="fight-bar" title="Leader currently on <?= e(fmt_number($leader['points'])) ?>">
                            <span style="--w: <?= $reach ?>%; --c: <?= e($c['color']) ?>"></span>
                            <i style="--x: <?= $c['maxPossible'] ? min(100, round($leader['points'] / $c['maxPossible'] * 100)) : 0 ?>%"></i>
                        </div>
                        <div class="text-xs subtle"><?= $c['gap'] > 0 ? '−' . e(fmt_number($c['gap'])) . ' to the leader' : 'Championship leader' ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="card-foot text-sm">
                <?php if ($fight['canClinchNext']): ?>
                    <?= icon('trophy', 'icon-sm') ?> <strong><?= $isTeams ? e($leader['constructor']) : e($leader['familyName']) ?></strong> can clinch the title at the <?= e($fight['remaining'][0]['name']) ?> by outscoring every rival by <strong><?= (int)$fight['clinchMargin'] ?></strong>+ points.
                <?php else: ?>
                    The fight goes on beyond the <?= e($fight['remaining'][0]['name'] ?? 'next race') ?> — nobody can clinch it there yet.
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php
}

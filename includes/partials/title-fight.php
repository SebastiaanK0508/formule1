<?php
/**
 * Championship battle card. Expects $fight (from f1_title_fight()).
 */
declare(strict_types=1);
if (!$fight) {
    return;
}
$tfTeams = $fight['mode'] === 'constructors';
$tfLeader = $fight['leader'];
$tfName = static fn(array $r): string => $tfTeams ? e($r['constructor']) : driver_link($r);
$tfLeft = count($fight['remaining']);
?>
<div class="card title-fight accent-top" style="--c: <?= e($tfLeader['color']) ?>">
    <?php if ($fight['decided']): ?>
        <div class="card-pad champion">
            <div class="champion-trophy"><?= icon('trophy', 'icon-lg') ?></div>
            <div>
                <div class="eyebrow" style="margin-bottom: 4px"><?= e($fight['year']) ?> <?= $tfTeams ? "Constructors'" : 'World' ?> Champion<?= $tfLeft ? ' · clinched' : '' ?></div>
                <div class="champion-name"><?= $tfTeams ? e($tfLeader['constructor']) : e($tfLeader['givenName']) . ' <strong>' . e($tfLeader['familyName']) . '</strong>' ?></div>
                <p class="muted text-sm mt-1">
                    <?= e(fmt_number($tfLeader['points'])) ?> points · <?= (int)$tfLeader['wins'] ?> wins<?php if ($tfLeft): ?> · <?= e(fmt_number($tfLeader['points'] - $fight['second']['points'])) ?> clear with only <?= (int)$fight['maxRemaining'] ?> points left to win in <?= $tfLeft ?> race<?= $tfLeft === 1 ? '' : 's' ?><?php endif; ?>.
                </p>
            </div>
        </div>
    <?php else: ?>
        <div class="card-head">
            <div>
                <h2><?= $tfTeams ? "Constructors'" : 'Title' ?> fight</h2>
                <div class="meta"><?= $tfLeft ?> race<?= $tfLeft === 1 ? '' : 's' ?> left · up to <?= (int)$fight['maxRemaining'] ?> points still available</div>
            </div>
            <span class="badge badge-red"><?= count($fight['contenders']) ?> in contention</span>
        </div>
        <div class="card-pad stack" style="gap: 14px">
            <?php foreach ($fight['contenders'] as $c):
                $reach = $c['maxPossible'] > 0 ? min(100, round($c['points'] / $c['maxPossible'] * 100)) : 0; ?>
                <div class="contender">
                    <div class="spread text-sm">
                        <span class="row" style="gap: 8px"><span class="swatch" style="--c: <?= e($c['color']) ?>"></span><?= $tfName($c) ?></span>
                        <span><strong class="tabular"><?= e(fmt_number($c['points'])) ?></strong> <span class="subtle">/ max <?= e(fmt_number($c['maxPossible'])) ?></span></span>
                    </div>
                    <div class="fight-bar" title="Leader currently on <?= e(fmt_number($tfLeader['points'])) ?>">
                        <span style="--w: <?= $reach ?>%; --c: <?= e($c['color']) ?>"></span>
                        <i style="--x: <?= $c['maxPossible'] ? min(100, round($tfLeader['points'] / $c['maxPossible'] * 100)) : 0 ?>%"></i>
                    </div>
                    <div class="text-xs subtle"><?= $c['gap'] > 0 ? '−' . e(fmt_number($c['gap'])) . ' to the leader' : 'Championship leader' ?></div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="card-foot text-sm">
            <?php if ($fight['canClinchNext']): ?>
                <?= icon('trophy', 'icon-sm') ?> <strong><?= $tfTeams ? e($tfLeader['constructor']) : e($tfLeader['familyName']) ?></strong> can clinch the title at the <?= e($fight['remaining'][0]['name']) ?> by outscoring every rival by <strong><?= (int)$fight['clinchMargin'] ?></strong>+ points.
            <?php else: ?>
                The fight goes on beyond the <?= e($fight['remaining'][0]['name'] ?? 'next race') ?> — nobody can clinch it there yet.
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

require INC_DIR . '/quiz.php';

// Answers and streaks are kept in the visitor's browser (localStorage).
$today = date('Y-m-d');
$questions = daily_quiz($today);

$page = [
    'title'       => 'Daily F1 Quiz',
    'description' => 'Five new Formula 1 history questions every day. Guess the winner, the pole sitter and the winning team — and keep your streak alive.',
    'nav'         => 'quiz',
];
require INC_DIR . '/layout/header.php';
?>
<div class="container" style="max-width: 820px">
    <header class="page-head">
        <div class="eyebrow">Daily quiz · <?= e(fmt_date($today, 'l j F Y')) ?></div>
        <h1 class="page-title">How well do you know F1?</h1>
        <p class="page-lead">Five races from Formula 1 history, new every day at midnight. One guess per question — no peeking at the results pages!</p>
    </header>

    <section class="section" style="padding-top: 16px">
        <?php if (!$questions): ?>
            <div class="card empty"><?= icon('alert', 'icon-lg') ?><h3>The quiz is warming up</h3><p>We couldn't load today's races. Please try again in a minute.</p></div>
        <?php else: ?>
        <div class="card quiz" data-quiz="<?= e($today) ?>">
            <?php foreach ($questions as $i => $q): ?>
                <div class="quiz-q" data-q="<?= $i ?>" data-answer="<?= e($q['answer']) ?>">
                    <div class="spread text-xs subtle" style="text-transform: uppercase; letter-spacing: .08em">
                        <span>Question <?= $i + 1 ?> of <?= count($questions) ?></span>
                        <span class="row" style="gap: 6px"><?= flag_img(country_iso($q['race']['country']), $q['race']['country']) ?><?= e($q['race']['circuitName']) ?></span>
                    </div>
                    <h2 class="mt-2" style="font-size: 20px"><?= e($q['text']) ?></h2>
                    <div class="quiz-options">
                        <?php foreach ($q['options'] as $o): ?>
                            <button class="quiz-option" type="button" data-value="<?= e($o['value']) ?>"><span class="swatch" style="--c: <?= e($o['color']) ?>"></span><?= e($o['label']) ?></button>
                        <?php endforeach; ?>
                    </div>
                    <p class="quiz-reveal text-sm muted mt-3" hidden><?= icon('info', 'icon-sm') ?> <?= e($q['reveal']) ?> <a href="<?= e(race_url($q['race']['season'], $q['race']['round'])) ?>">Full result</a></p>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="card card-pad quiz-result mt-6" hidden>
            <div class="eyebrow" style="justify-content: center">Today's score</div>
            <div class="quiz-score">0/5</div>
            <div class="quiz-grid"></div>
            <p class="muted text-sm" data-quiz-stats></p>
            <div class="row-wrap mt-4" style="justify-content: center">
                <button class="btn btn-primary" type="button" data-quiz-share><?= icon('external', 'icon-sm') ?> Share your score</button>
                <a class="btn btn-secondary" href="<?= e(url('stats.php')) ?>">Explore season stats</a>
            </div>
            <p class="text-xs subtle mt-4">New questions tomorrow. Come back to keep your streak going!</p>
        </div>
        <?php endif; ?>
    </section>
</div>
<?php require INC_DIR . '/layout/footer.php'; ?>

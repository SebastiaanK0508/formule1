<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$legal = [
    'eyebrow'  => 'Legal',
    'title'    => 'Cookie policy',
    'intro'    => 'Which cookies and browser storage F1SITE.NL uses, and how you can change your choice.',
    'updated'  => '17 November 2026',
    'sections' => [
        'What are cookies?' => '<p>Cookies are small text files stored by your browser. We also use your browser\'s local storage for preferences that never leave your device.</p>',
        'What we use' => '<div class="table-wrap"><table class="table" style="font-size: 14px"><thead><tr><th>Name</th><th>Type</th><th>Purpose</th><th>Retention</th></tr></thead><tbody>'
            . '<tr><td class="mono">f1_consent</td><td>Essential cookie</td><td>Remembers your cookie choice</td><td>12 months</td></tr>'
            . '<tr><td class="mono">f1site</td><td>Essential cookie</td><td>Session for the contact form (spam protection)</td><td>Session</td></tr>'
            . '<tr><td class="mono">theme, f1_fav_*</td><td>Local storage</td><td>Your theme and followed drivers/teams</td><td>Until cleared</td></tr>'
            . '<tr><td class="mono">_ga, _ga_*</td><td>Analytics cookie</td><td>Anonymous usage statistics (Google Analytics) — only with consent</td><td>Up to 14 months</td></tr>'
            . '</tbody></table></div>',
        'Change your preferences' => '<p>You can change your choice at any time. Clicking the button below shows the cookie banner again.</p><p><button class="btn btn-secondary" type="button" data-consent-reset>Change cookie settings</button></p><p>You can also delete cookies through your browser settings.</p>',
    ],
];
require INC_DIR . '/partials/legal.php';

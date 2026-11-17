<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$legal = [
    'eyebrow'  => 'Legal',
    'title'    => 'Privacy policy',
    'intro'    => 'How F1SITE.NL, operated by Webius, collects, uses and protects your personal data.',
    'updated'  => '17 November 2026',
    'sections' => [
        'Data we collect' => '<p><strong>Automatically:</strong> technical data such as your IP address, browser type and the pages you visit, stored in server log files for security. Only if you allow analytics cookies do we collect anonymous usage statistics through Google Analytics (with IP anonymisation).</p>'
            . '<p><strong>Data you provide:</strong> your name, email address and message when you use the contact form.</p>'
            . '<p><strong>In your browser only:</strong> the drivers and teams you follow and your light/dark theme preference are stored in your own browser (localStorage) and are never sent to us.</p>',
        'How we use your data' => '<p>We use your data to operate and secure the website, to understand which content is useful (only with consent) and to reply to messages sent through the contact form.</p>',
        'Sharing your data' => '<p>We do not sell your data. Data is only shared with our hosting provider, with Google when you consent to analytics, or when we are legally required to do so.</p>',
        'Retention' => '<p>Contact messages are kept for as long as needed to handle your request and deleted afterwards. Analytics data is retained for a maximum of 14 months.</p>',
        'Cookies' => '<p>See our <a href="' . e(url('cookiebeleid.php')) . '">cookie policy</a> for the cookies we use and how to change your preferences.</p>',
        'Your rights (GDPR)' => '<ul><li>Right of access, rectification and data portability.</li><li>Right to erasure and to object to processing.</li><li>Right to withdraw your consent at any time.</li><li>Right to lodge a complaint with the Dutch Data Protection Authority (Autoriteit Persoonsgegevens).</li></ul>',
        'Contact' => '<p>For questions about this policy or to exercise your rights, please use our <a href="' . e(url('contact.php')) . '">contact form</a>.</p>',
    ],
];
require INC_DIR . '/partials/legal.php';

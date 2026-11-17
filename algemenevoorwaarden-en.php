<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

legal_page([
    'eyebrow'  => 'Legal',
    'title'    => 'Terms of use',
    'intro'    => 'The conditions that apply when you visit and use F1SITE.NL.',
    'updated'  => '17 November 2026',
    'sections' => [
        'Definitions' => '<ul><li><strong>Website:</strong> F1SITE.NL and all of its pages.</li><li><strong>Webius:</strong> the owner and operator of the Website.</li><li><strong>User:</strong> anyone who visits or uses the Website.</li></ul>',
        'Applicability' => '<p>By using the Website you agree to these terms. Webius may change them at any time; continued use after a change means you accept the updated terms.</p>',
        'Use of the website' => '<p>Information on F1SITE.NL is provided for general information only. Results and statistics are retrieved from third-party sources (such as the Jolpica/Ergast API) and may occasionally be delayed or inaccurate. Automated copying or scraping of the Website without written permission is not allowed.</p>',
        'Intellectual property' => '<p>The design, code, texts and layout of the Website are the property of Webius. Photos, logos and trademarks of Formula 1, the FIA and the teams belong to their respective owners. F1SITE.NL is an independent fan site and is not affiliated with Formula 1 or any team.</p>',
        'Liability' => '<p>Webius is not liable for damage resulting from the use of the Website, from (temporary) unavailability, or from decisions made based on the information shown. External links are provided for convenience; we are not responsible for their content.</p>',
        'Privacy' => '<p>Processing of personal data is described in our <a href="' . e(url('privacy-en.php')) . '">privacy policy</a>.</p>',
        'Applicable law' => '<p>These terms are governed by Dutch law. Disputes are submitted to the competent court in the Netherlands.</p>',
    ],
]);

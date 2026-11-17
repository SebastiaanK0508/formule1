<?php declare(strict_types=1); ?>
</main>

<footer class="site-footer">
    <div class="container">
        <div class="footer-grid">
            <div>
                <a class="brand" href="<?= e(url()) ?>">F1SITE<span>.NL</span></a>
                <p class="mt-3" style="max-width: 360px">Independent Formula 1 statistics: live season standings, full race results back to 1950, driver and team profiles and the latest headlines.</p>
                <p class="mt-3 text-xs subtle">Not affiliated with Formula 1, the FIA or any team. Results data via the Jolpica/Ergast API.</p>
            </div>
            <div>
                <h3>Season</h3>
                <ul>
                    <li><a href="<?= e(url('kalender.php')) ?>">Schedule</a></li>
                    <li><a href="<?= e(url('results.php')) ?>">Results</a></li>
                    <li><a href="<?= e(url('standings.php')) ?>">Standings</a></li>
                    <li><a href="<?= e(url('stats.php')) ?>">Season stats</a></li>
                    <li><a href="<?= e(url('calendar.ics.php')) ?>">Add calendar (.ics)</a></li>
                </ul>
            </div>
            <div>
                <h3>Explore</h3>
                <ul>
                    <li><a href="<?= e(url('drivers.php')) ?>">Drivers</a></li>
                    <li><a href="<?= e(url('teams.php')) ?>">Teams</a></li>
                    <li><a href="<?= e(url('compare.php')) ?>">Compare drivers</a></li>
                    <li><a href="<?= e(url('quiz.php')) ?>">Daily quiz</a></li>
                    <li><a href="<?= e(url('nieuws.php')) ?>">News</a></li>
                    <li><a href="https://predict.f1site.nl" rel="noopener">Predict <?= icon('external', 'icon-sm') ?></a></li>
                </ul>
            </div>
            <div>
                <h3>About</h3>
                <ul>
                    <li><a href="<?= e(url('contact.php')) ?>">Contact</a></li>
                    <li><a href="<?= e(url('sitemap.php')) ?>">Sitemap</a></li>
                    <li><a href="<?= e(url('privacy-en.php')) ?>">Privacy</a></li>
                    <li><a href="<?= e(url('cookiebeleid.php')) ?>">Cookies</a></li>
                    <li><a href="<?= e(url('algemenevoorwaarden-en.php')) ?>">Terms</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <span>© <?= date('Y') ?> F1SITE.NL · Built by <a href="https://www.linkedin.com/in/sebastiaan-kamphuis-04256b222/" rel="noopener">Sebastiaan Kamphuis</a> · <a href="https://www.webius.nl" rel="noopener">Webius</a></span>
            <span>Times are shown in your local timezone<span class="subtle"> (<span data-timezone-name><?= e(date_default_timezone_get()) ?></span>)</span></span>
        </div>
    </div>
</footer>

<div class="cookie-banner" id="cookie-banner" role="dialog" aria-live="polite" aria-label="Cookie consent">
    <strong>Cookies</strong>
    <p>We use essential cookies to make this site work. With your permission we also use anonymous analytics to understand which pages are useful. <a href="<?= e(url('cookiebeleid.php')) ?>">Read more</a></p>
    <div class="row-wrap">
        <button class="btn btn-secondary btn-sm" type="button" data-consent="essential">Only essential</button>
        <button class="btn btn-primary btn-sm" type="button" data-consent="analytics">Allow analytics</button>
    </div>
</div>
</body>
</html>

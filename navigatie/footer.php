<?php
$current_page = basename($_SERVER['PHP_SELF']);

$server = $_SERVER['SERVER_NAME'];
$requestUri = $_SERVER['REQUEST_URI'];

if ($server === 'localhost' || $server === '127.0.0.1') {
    $baseUrl = (strpos($requestUri, '/~sebastiaanbaskamphuis') !== false)
        ? "http://localhost:8080/~sebastiaanbaskamphuis/formule1/"
        : "http://localhost/formule1/";
} else {
    $baseUrl = "https://f1site.nl/";
}
?>
<footer class="bg-black mt-24 py-16 border-t-2 border-f1-red">
    <div class="max-w-7xl mx-auto px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-12 text-left pb-12 border-b border-white/5">
            
            <div class="space-y-4 text-center md:text-left">
                <a href="<?php echo $baseUrl; ?>" class="inline-block">
                    <h3 class="text-2xl font-oswald font-black text-white italic tracking-tighter uppercase">F1SITE<span class="text-f1-red">.NL</span></h3>
                </a>
                <p class="text-gray-500 text-sm font-medium leading-relaxed max-w-xs mx-auto md:mx-0">
                    Your ultimate source for the latest Formula 1 news, live countdowns, statistics and race updates.
                </p>
            </div>

            <div class="text-center md:text-left">
                <h4 class="text-xs font-black text-f1-red mb-6 uppercase tracking-[0.3em]">Developer</h4>
                <ul class="space-y-4">
                    <li>
                        <a href="https://www.webius.nl" target="_blank" rel="noopener" class="text-gray-400 text-sm font-bold hover:text-white transition duration-200 block uppercase tracking-wider">Webius</a>
                    </li>
                </ul>
            </div>

            <div class="text-center md:text-left">
                <h4 class="text-xs font-black text-f1-red mb-6 uppercase tracking-[0.3em]">Navigatie & Info</h4>
                <ul class="space-y-4">
                    <?php
                    $nav_items = [
                        'sitemap.php' => 'Sitemap',
                        'privacy-en.html' => 'Privacy Policy',
                        'algemenevoorwaarden-en.html' => 'Terms & Conditions',
                        'contact.html' => 'Contact'
                    ];

                    foreach ($nav_items as $url => $label) {
                        $active_class = ($current_page === $url) ? 'text-f1-red' : 'text-gray-400';
                        echo "<li><a href=\"$url\" class=\"$active_class text-sm font-bold hover:text-white transition duration-200 block uppercase tracking-wider\">$label</a></li>";
                    }
                    ?>
                </ul>
            </div>
        </div>

        <div class="pt-10 flex flex-col md:flex-row justify-between items-center gap-4 text-center md:text-left">
            <p class="text-gray-600 text-[10px] font-black uppercase tracking-[0.5em] italic">
                &copy; <?php echo date('Y'); ?> WEBIUS. All rights reserved.
            </p>
            <div class="flex gap-6">
                <span class="text-f1-red opacity-20 text-xl font-oswald italic font-black uppercase" aria-hidden="true">Faster than light</span>
            </div>
        </div>
    </div>
</footer>
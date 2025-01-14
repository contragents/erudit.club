<?php
header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + (60 * 60)));
header('Ref: ' . $_SERVER['HTTP_REFERER'] ?? 'NA');

include_once __DIR__ . '/autoload_helper.php';

if (!isset($_COOKIE[CookieErudit::COOKIE_NAME])) {
    $_COOKIE = CookieErudit::setGetCook();
} elseif (rand(1, 100) <= 2) {
    $_COOKIE = CookieErudit::setGetCook($_COOKIE[CookieErudit::COOKIE_NAME]);
}

@ob_clean();

if (file_exists($_GET['file'])) {
    include($_GET['file']);
}

exit(0); 

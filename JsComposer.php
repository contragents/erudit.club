<?php
header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + (60 * 60)));
header('Ref: ' . $_SERVER['HTTP_REFERER'] ?? 'NA');

if (strpos($_SERVER['HTTP_REFERER'] ?? '', 'dev.html')) {
    include_once __DIR__ . '/dev1.0.1.2/php/autoload.php';
} elseif (strpos($_SERVER['HTTP_REFERER'] ?? '', 'private.html')) {
    include_once __DIR__ . '/dev1.0.1.5/php/autoload.php';
} else {
    include_once __DIR__ . '/yandex1.0.1.1/php/autoload.php';
}

if (!isset($_COOKIE['erudit_user_session_ID'])) {
    $_COOKIE = Cookie::setGetCook();
} elseif (rand(1, 100) <= 2) {
    $_COOKIE = Cookie::setGetCook($_COOKIE['erudit_user_session_ID']);
}

ob_clean();

if (file_exists($_GET['file'])) {
    include($_GET['file']);
}

exit(0); 

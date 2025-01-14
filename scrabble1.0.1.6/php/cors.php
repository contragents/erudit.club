<?php

if (isset($_SERVER['HTTP_ORIGIN']) && $_SERVER['HTTP_ORIGIN'] != '') {
    header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
    header('Access-Control-Allow-Credentials: true');
}

if (!isset($_COOKIE[CookieErudit::COOKIE_NAME])) {
    $_COOKIE = CookieErudit::setGetCook();
    print json_encode(['gameState' => $_REQUEST['gameState'] === 'cookieTest' ? 'useLocalStorage' : 'cookieTest', 'cookie' => $_COOKIE[CookieErudit::COOKIE_NAME] ?? '']);
    exit();
}

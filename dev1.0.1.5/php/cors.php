<?php

if (isset($_SERVER['HTTP_ORIGIN']) && $_SERVER['HTTP_ORIGIN'] != '') {
    header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
    header('Access-Control-Allow-Credentials: true');
}

if (!isset($_COOKIE['erudit_user_session_ID'])) {
    $_COOKIE = CookieErudit::setGetCook();
    print json_encode(['gameState' => 'cookieTest', 'cookie' => $_COOKIE[CookieErudit::COOKIE_NAME] ?? '']);
    exit();
}

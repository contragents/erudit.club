<?php

if (isset($_SERVER['HTTP_ORIGIN']) && $_SERVER['HTTP_ORIGIN'] != '') {
    header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
    header('Access-Control-Allow-Credentials: true');
}

if (!isset($_COOKIE['erudit_user_session_ID'])) {
    $_COOKIE = Cookie::setGetCook();
    print json_encode(['gameState' => 'cookieTest', 'cookie' => $_COOKIE[Cookie::COOKIE_NAME] ?? '']);
    exit();
}

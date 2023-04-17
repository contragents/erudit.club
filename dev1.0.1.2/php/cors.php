<?php
if (isset($_SERVER['HTTP_HOST']))
    $server_name = $_SERVER['HTTP_HOST'];
else
    $server_name = 'xn--d1aiwkc2d.club';

if( isset($_SERVER['HTTP_ORIGIN']) &&  $_SERVER['HTTP_ORIGIN']!='' ) {
    $server_name = str_replace('https://','',$_SERVER['HTTP_ORIGIN']);
    header('Access-Control-Allow-Origin: '.$_SERVER['HTTP_ORIGIN']);
    header('Access-Control-Allow-Credentials: true');
}

if (!isset($_COOKIE['erudit_user_session_ID'])) {
    include_once 'CookieLangProvider.php';
    $_COOKIE = Cookie::setGetCook();
    print json_encode(['gameState' => 'cookieTest', 'cookie' => $_COOKIE[Cookie::COOKIE_NAME] ?? '']);
    exit();
}

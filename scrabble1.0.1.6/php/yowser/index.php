<?php
require_once __DIR__ . '/../autoload.php';

$script = $_GET['script'];
$cookie = $_GET['cooki'];

if ($cookie == 'EMPTY_SESSION_ID') {
    if (isset($_SERVER['HTTP_ORIGIN']) && $_SERVER['HTTP_ORIGIN'] != '') {
        header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
        header('Access-Control-Allow-Credentials: true');
    }

    $cookie = $_COOKIE[Cookie::COOKIE_NAME] ?? md5($_SERVER['HTTP_USER_AGENT'] . time() . rand(1, 100));
    print json_encode(['gameState' => 'register', 'cookie' => $cookie]);

    exit();
} else {
    $_SERVER['HTTP_COOKIE'] = Cookie::COOKIE_NAME . "=$cookie";
    $_COOKIE = [Cookie::COOKIE_NAME => $cookie];
    include_once('../' . $script);
}

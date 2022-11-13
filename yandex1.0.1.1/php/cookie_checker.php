<?php
//ini_set("display_errors", 1); error_reporting(E_ALL);
if (isset($_SERVER['HTTP_HOST']))
    $server_name = $_SERVER['HTTP_HOST'];
else
    $server_name = 'xn--d1aiwkc2d.club';

if (isset($_SERVER['HTTP_ORIGIN']) && $_SERVER['HTTP_ORIGIN'] != '') {
    $server_name = str_replace('https://', '', $_SERVER['HTTP_ORIGIN']);
    header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
    header('Access-Control-Allow-Credentials: true');
}

//Устанавливаем домен в куки - эрудит или 5-5.су или яндекс..

if (!isset($_COOKIE['erudit_user_session_ID'])) {
    $cookie = md5(time() . rand(1, 100));
    print json_encode(['gameState' => 'register', 'cookie' => $cookie]);
    exit();
    //new scenario
} else {
    include_once 'EruditGame.php';
    $_GET['queryNumber'] = 1;
    $resp = ($obj = new Erudit\Game($server_name))->checkGameStatus();
    print $resp;
}
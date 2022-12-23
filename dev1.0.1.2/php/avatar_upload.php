<?php

//ini_set("display_errors", 1); error_reporting(E_ALL);
if (isset($_SERVER['HTTP_HOST'])) {
    $server_name = $_SERVER['HTTP_HOST'];
} else {
    $server_name = 'xn--d1aiwkc2d.club';
}

if (isset($_SERVER['HTTP_ORIGIN']) && $_SERVER['HTTP_ORIGIN'] != '') {
    $server_name = str_replace('https://', '', $_SERVER['HTTP_ORIGIN']);
    header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
    header('Access-Control-Allow-Credentials: true');
}

//Устанавливаем домен в куки - эрудит или 5-5.су или яндекс..

if (!isset($_COOKIE['erudit_user_session_ID'])) {
    include_once 'CookieLangProvider.php';

    $_COOKIE = Cookie::setGetCook();
    print json_encode(['gameState' => 'cookieTest', 'cookie' => $_COOKIE[Cookie::COOKIE_NAME] ?? '']);
    exit();
    //new scenario
}


spl_autoload_register(
    function ($class_name) {
        $Exploded_class = explode('\\', $class_name);
        include $Exploded_class[count($Exploded_class) - 1] . 'LangProvider.php';
    }
);

$resp = Dadata\Players::avatarUpload($_FILES, $_COOKIE['erudit_user_session_ID']);

print $resp;

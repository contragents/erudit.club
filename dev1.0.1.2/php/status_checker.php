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
    /*setcookie('erudit_user_session_ID',
		$cook = md5(time().rand(1,100)),
		['expires' => strtotime('+10 year'),
			'secure' => true,
			'httponly' => true,
			'samesite' => 'none',
			'path' => '/']
	);
    $_COOKIE = ['erudit_user_session_ID' => $cook];*/
    include_once 'CookieLangProvider.php';
    /*Cookie::createCookie(
        Cookie::COOKIE_NAME,
        $cook = md5(time() . rand(1, 100)),
        Cookie::TTL,
        '/',
        '',
        true,
        false
    );
    */
    $_COOKIE = Cookie::setGetCook();
    print json_encode(['gameState' => 'cookieTest', 'cookie' => $_COOKIE[Cookie::COOKIE_NAME] ?? '']);
    exit();
    //new scenario
}

if (isset($_GET['page_hidden']) && $_GET['page_hidden'] == 'true') {
    sleep(10);
}


include_once 'EruditGame.php';
$resp = ($obj = new Erudit\Game($server_name))->checkGameStatus();

print $resp;

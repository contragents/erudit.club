<?php
header('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', time() + (60 * 60)));

if (isset($_SERVER['HTTP_HOST']))
    $server_name = $_SERVER['HTTP_HOST'];
else
    $server_name = 'xn--d1aiwkc2d.club';
//НЕ устанавливаем домен в куки - эрудит или 5-5.су

if (!isset($_COOKIE['erudit_user_session_ID'])) {
    /*setcookie('erudit_user_session_ID', $cook = md5(time().rand(1,100)), ['expires' => strtotime('+10 year'), 'secure' => true, 'httponly' => true, 'samesite' => 'none', 'path' => '/']);
    $_COOKIE = ['erudit_user_session_ID' => $cook];
    */
    include_once 'yandex1.0.1.1/php/CookieLangProvider.php';
    $_COOKIE = Cookie::setGetCook();
} elseif (rand(1,100) <= 2) {
    include_once 'yandex1.0.1.1/php/CookieLangProvider.php';
    $_COOKIE = Cookie::setGetCook($_COOKIE['erudit_user_session_ID']);
}

ob_clean();

if (file_exists($_GET['file']))
    include($_GET['file']);

exit(0); 

<?php
ini_set("display_errors", 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../autoload.php';

$script = $_GET['script'];
$cookie = $_GET['cooki'];



if ($cookie) {
    $_SERVER['HTTP_COOKIE'] = CookieErudit::COOKIE_NAME . "=$cookie";
    $_COOKIE = [CookieErudit::COOKIE_NAME => $cookie];
}

include_once '../cors.php';

    //print "including $script";
    $scriptName = str_replace('.php', '', $script);
    return include_once('../index.php');


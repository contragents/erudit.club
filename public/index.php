<?php

//print 'hello world!'; exit;
//ini_set("display_errors", 1); error_reporting(E_ALL);
include_once (__DIR__) . '/../autoload.php';

print_r(Config::$config);
// Обработка роутов (postback и т.п.)
include __DIR__ . '/../route.php';

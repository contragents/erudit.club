<?php

if (strpos(__DIR__, 'dev1.0.1.2')) {
    ini_set("display_errors", 1);
    error_reporting(E_ALL);
}

require_once __DIR__ . '/autoload.php';
include_once 'cors.php';
require_once 'index_functions.php';

if (!isset($scriptName)) {
    throw new BadRequest('Wrong query');
}

if (function_exists(SCRIPTS[$scriptName])) {
    if(!Tg::authorize()) {
        Yandex::authorize();
    }

    $func = SCRIPTS[$scriptName];
    return $func();
} else {
    throw new BadRequest('Wrong query');
}
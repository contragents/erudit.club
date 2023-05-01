<?php
ini_set("display_errors", 1); error_reporting(E_ALL);
require_once 'autoload.php';
Config::$config = ['cache' => ['HOST' => 'localhost', 'PORT' => 6389]];
require_once 'yandex1.0.1.1/php/EruditGame.php';

foreach (Cache::hgetall(Erudit\Game::LOG_BOT_ERRORS_KEY) ?: [] as $error) {
    print_r($error);
}

foreach (Cache::hgetall(Erudit\Game::BOT_ERRORS_KEY) ?: [] as $error) {
    print_r($error);
}

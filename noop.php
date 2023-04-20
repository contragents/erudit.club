<?php

require_once 'autoload.php';
Config::$config = ['cache' => ['HOST' => 'localhost', 'PORT' => 6389]];
require_once 'dev1.0.1.2/php/EruditGame.php';
foreach (Cache::hgetall(Erudit\Game::BOT_ERRORS_KEY) as $error) {
    print_r($error);
}

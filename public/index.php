<?php

//print 'hello world!'; exit;

include_once (__DIR__) . '/../autoload.php';

//print_r(Config::$config);
//Array ( [env] => PROD [cache] => Array ( [HOST] => 127.0.0.1 [PORT] => 6379 ) [db] => Array ( [SQL_HOST] => localhost [SQL_USER] => ili [SQL_PASSWORD] => Aw!gP!mx_Jh6M.V [SQL_DB_NAME] => erudit ) [debug_info] => )
// Обработка роутов (postback и т.п.)
//print_r(Cache::hgetall('erudit.games_' . date('Y_m_d') . '_locks'));
//print_r($_SERVER);
include __DIR__ . '/../route.php';

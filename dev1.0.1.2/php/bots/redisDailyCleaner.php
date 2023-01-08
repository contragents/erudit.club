<?php
//error_reporting(E_ALL & ~E_NOTICE); ini_set('display_errors', 0);
define("BOTSNUM",100);
$minutesToGo = 5;
$_SERVER['DOCUMENT_ROOT'] = '/var/www/erudit.club';
set_time_limit ( $minutesToGo*60 + 5 );
$start_script_time = date('U');
$script_work_time = $minutesToGo*60 - 5;

include_once(__DIR__.'/../CacheLangProvider.php');
include_once(__DIR__.'/../DBLangProvider.php');

$red = \Dadata\Cache::getInstance();
$lastDays = 1;

while  ( (date('U') - $start_script_time) < $script_work_time) {
    $lastDay = date('Y_m_d',strtotime("-$lastDays day"));

    if ( $red->redis->del("erudit.games_{$lastDay}_locks") ) {
        $lastDays++;
        print $lastDay . " success\n";
    } else {
        print $lastDay . " not found\n";
        exit();
    }

}

// Чистим лог ошибок сохранения статистики игры
$red->redis->del('del erudit.games_statistics_failed');

exit();
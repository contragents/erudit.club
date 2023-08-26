<?php

include_once(__DIR__ . '/../autoload.php');

use Erudit\Game;

$minutesToGo = 5;
set_time_limit($minutesToGo * 60 + 5);
$start_script_time = date('U');
$script_work_time = $minutesToGo * 60 - 5;

$lastDays = 1;

while ((date('U') - $start_script_time) < $script_work_time) {
    $lastDay = date('Y_m_d', strtotime("-$lastDays day"));

    if (Cache::del("erudit.games_{$lastDay}_locks")) {
        $lastDays++;
        print $lastDay . " success\n";
    } else {
        print $lastDay . " not found\n";

        break;
    }
}

print 'Чистим лог ошибок сохранения статистики игры' . PHP_EOL;
Cache::del(Game::STATS_FAILED);

print 'Чистим лог ходов на 0 очков' . PHP_EOL;
Cache::del(Game::BAD_COMBINATIONS_HSET);
ini_set("display_errors", 1);
error_reporting(E_ALL);
print 'Уменьшаем рейтинг игрокам, которые не играли сутки' . PHP_EOL;
PlayerModel::decreaseRatings();

exit();
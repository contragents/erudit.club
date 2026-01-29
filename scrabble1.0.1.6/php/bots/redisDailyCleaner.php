<?php

include_once(__DIR__ . '/../autoload.php');

$minutesToGo = 5;
set_time_limit($minutesToGo * 60 + 5);
$start_script_time = date('U');
$script_work_time = $minutesToGo * 60 - 5;

$lastDays = 1;

while ((date('U') - $start_script_time) < $script_work_time) {
    $lastDay = date('Y_m_d', strtotime("-$lastDays day"));

    if (Cache::del(Game::GAMES_KEY . "{$lastDay}_locks")) {
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
//print 'Уменьшаем рейтинг игрокам, которые не играли сутки' . PHP_EOL;
// todo уменьшать рейтинг через модель CommonIdRating
//PlayerModel::decreaseRatings();

clearHintsAction();

function clearHintsAction()
{
    $res=[];
    for ($i = 1; $i <= 50000; $i++) {
        $keyPrefix = 'erudit.hint_' . substr(md5($i),0,2);
        print $keyPrefix.PHP_EOL;
        $playerHintsKeys = Cache::keys($keyPrefix . '*');
        if(!is_array($playerHintsKeys)) {
            continue;
        }
        print "$i: найдено ключей: " . count($playerHintsKeys) . "\n";
        foreach ($playerHintsKeys as $key) {
            $res[$i] = ($res[$i] ?? 0) + Cache::del($key);
        }

        print 'Удалено ключей: ' . $res[$i] . PHP_EOL;
    }

    $keyPrefix = 'erudit.hint_' . 'bot';
    print $keyPrefix.PHP_EOL;
    $playerHintsKeys = Cache::keys($keyPrefix . '*');
    if(!is_array($playerHintsKeys)) {
       return;
    }
    print "bot: найдено ключей: " . count($playerHintsKeys) . "\n";
    foreach ($playerHintsKeys as $key) {
        $res['bot'] = ($res['bot'] ?? 0) + Cache::del($key);
    }

    print 'Удалено ключей: ' . $res['bot'] . PHP_EOL;

    //return $res;
}

exit();
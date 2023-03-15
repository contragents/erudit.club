<?php
//error_reporting(E_ALL & ~E_NOTICE); ini_set('display_errors', 0);
define("BOTSNUM",47);
$minutesToGo = 5;
$numRounds = 2;//Число проходов по полю
$_SERVER['DOCUMENT_ROOT'] = '/var/www/erudit.club';
set_time_limit ( $minutesToGo*60 + 5 );
$start_script_time = date('U');
$script_work_time = $minutesToGo*60 - 5;
$secondsToBotRefresh = 10;

include_once(__DIR__.'/../CacheLangProvider.php');
include_once(__DIR__.'/../DBLangProvider.php');

include(__DIR__.'/findWordSlevaFunction.php');
include(__DIR__.'/findWordSverhuFunction.php');
include(__DIR__.'/findWordSpravaFunction.php');
include(__DIR__.'/findWordVnizFunction.php');
include(__DIR__.'/checkWordFishkiFunction.php');
include(__DIR__.'/maxToFunctions.php');

$red = \Dadata\Cache::getInstance();

$botsTurns = [];
$botTimes = [];

while  ( (date('U') - $start_script_time) < $script_work_time) {
    if ( $Bot = $red->redis->lpop('erudit.bot_games') ) {
        $_COOKIE['erudit_user_session_ID'] = $Bot;

        $resp = ['gameState' => 1];
        $zaprosNum = 3;

        
        $_GET['queryNumber'] = $zaprosNum++;
        ob_start();
        $resp = include __DIR__.'/../status_checker.php';
        $resp = json_decode(ob_get_contents(), true);
        ob_end_clean();
        print_r($resp); //sleep(4);
        
        if ( ($resp['gameState'] == 'gameResults')) {
            ob_start();
            $resp = include __DIR__.'/../exit_game.php';
            ob_end_clean();
            //Не будем анализировать ответы!)) - просто новая игра
            unset($botsTurns[$Bot]);
            unset($botTimes[$Bot]);
            //$red->redis->hdel('erudit.bot_v1_list',$Bot);
            $red->redis->hdel('erudit.bot_v3_list',$Bot);
            continue;
            
        }
        else {
            //ob_end_clean();
            print $resp['gameState'];
            print "\n";
            print $resp['query_number']; 
            print "\n";
            //ob_start();
            
            if ($resp['gameState'] == 'myTurn')
                if (
                    ($resp['turnTime'] == 120
                        && (
                            (
                                ($resp['minutesLeft'] <= 1)
                                && 
                                ($resp['secondsLeft'] < 40)
                            )
                            || 
                                ($resp['minutesLeft'] < 1)
                            )
                    )
                    ||
                    ($resp['turnTime'] == 90
                        && (
                            (
                                ($resp['minutesLeft'] <= 1)
                                && 
                                ($resp['secondsLeft'] < 10)
                            )
                            || 
                                ($resp['minutesLeft'] < 1)
                            )
                    )
                    ||
                    (
                    ($resp['turnTime'] == 60)
                    &&
                    ($resp['secondsLeft'] < 40)
                    &&
                    ($resp['secondsLeft'] > 0)
                    )
                )
                {
                    $secondsToThink = $resp['minutesLeft']* 60 + $resp['secondsLeft'];
                    $thinkStartTime = date('U');
                    $thinkEndTime = $thinkStartTime + $secondsToThink;
                    $botsTurns[$Bot] = $resp['gameSubState'];

                    $turn_submit = sendResponse($resp);
                    //$turn_submit = json_decode($turn_submit,true);
                    //Ответ не нужен))
                }
                $red->redis->rpush('erudit.bot_games',$Bot);
                //Вернули бота в список игроков        
            
             if (isset($botTimes[$Bot])) {
                if ( ($time = date('U') - $botTimes[$Bot]) < $secondsToBotRefresh )
                    sleep($secondsToBotRefresh - $time);
            }
            $botTimes[$Bot] = date('U');
            
        }
        
    }
    
    if ($botTimes == [])
        sleep(10);
    else
        sleep(1);  
    print 'next!';    
}

exit();


function changeFishki(&$data) {
   foreach($data['fishki'] as $fishka) {
            $kf++;
            if ($fishka != 999)
            $_POST['fishka_'.$kf.'_'.$fishka] = 'on';
        }
        ob_start();
        $resp = include __DIR__.'/../change_fishki.php';
        ob_end_clean();
        return $resp; 
}

function sendResponse(&$data) {
    if (isset($data['desk'])) {
        $_POST['cells'] = $data['desk'];
        $slovaPlayed = (new Erudit\Game())->gameWordsPlayed();
    }
    else
    {
        $_POST['cells'] = \Lang\Ru::init_desk();
        $slovaPlayed = [];
    }

    //error_reporting(E_ALL & ~E_NOTICE); ini_set('display_errors', 0);
    if (make_turn($_POST['cells'], $data['fishki'], $slovaPlayed)) {
        $_POST['cells'] = json_encode($_POST['cells']);
        ob_start();
        $resp = include __DIR__.'/../turn_submitter.php';
        ob_end_clean();
        return $resp;
    }
    
    else return changeFishki($data);
        
}






function make_turn(&$desk, &$fishki, &$slovaPlayed) {
   // error_reporting(E_ALL & ~E_NOTICE);  ini_set('display_errors', 0);
    GLOBAL $thinkEndTime;
    GLOBAL $numRounds;
    ob_end_clean();
    $fishki1 = $fishki;
    $word = '';
    /*Забираем звезды с поля...
    for ($j=0;$j<=14;$j++)
        for ($i=0;$i<=14;$i++)
            if ($desk[$i][$j][0] && ($desk[$i][$j][1] > 999))
                foreach ($fishki as $num => $fishka)
                    if ( ($fishka + 999 + 1) === $desk[$i][$j][1]) {
                        $desk[$i][$j][2] = $fishka;
                        $fishki[$num] = 999 + 1 + $fishka;
                        
                        break;
                    }
                   Пока нахуй звезды с поля*/
                    //Собрали звезды с поля
                    
    for ($k = 0; $k<$numRounds; $k++) {// $numRounds проходов
        //$j - строки, $i - столбцы
        for ($j=0;$j<=14;$j++)
            for ($i=0;$i<=14;$i++) {
                if ( ($i == 7) && ($j == 7) && !$desk[$i][$j][0]) {
                    if (date('U') < $thinkEndTime)
                        findWordSleva($i, $j, $desk, $fishki, $slovaPlayed);
                    
                }
                
                if ( !$desk[$i][$j][0] && isset($desk[$i][$j-1]) && $desk[$i][$j-1][0] && (!isset($desk[$i][$j+1]) || !$desk[$i][$j+1][0]) ) {
                    //print $i.$j.$desk[$i+1][$j][1];
                    $ff='';//для временного отключения поиска слов вниз
                    if (date('U') < $thinkEndTime)
                        findWordVniz($i, $j, $desk, $fishki, $slovaPlayed);
                    //Ищем слова по вертикали (пока) начинающиеся на $j-1...
                }
                
                if (!$desk[$i][$j][0] && isset($desk[$i+1][$j]) && $desk[$i+1][$j][0] && (!isset($desk[$i-1][$j]) || !$desk[$i-1][$j][0]) ) {
                    //print $i.$j.$desk[$i+1][$j][1];
                    $ff='';//для временного отключения поиска слов слева
                    if (date('U') < $thinkEndTime)
                        findWordSleva($i, $j, $desk, $fishki, $slovaPlayed);
                    //Ищем слова по горизонтали (пока) заканчивающиеся на $i+1...
                }
                
                if ( !$desk[$i][$j][0] && isset($desk[$i][$j+1]) && $desk[$i][$j+1][0] && (!isset($desk[$i][$j-1]) || !$desk[$i][$j-1][0]) ) {
                    //print $i.$j.$desk[$i+1][$j][1];
                    $ff='';//для временного отключения поиска слов сверху
                    if (date('U') < $thinkEndTime)
                        findWordSverhu($i, $j, $desk, $fishki, $slovaPlayed);
                    //Ищем слова по вертикали (пока) заканчивающиеся на $j+1...
                }
                
                if (!$desk[$i][$j][0] && isset($desk[$i-1][$j]) && $desk[$i-1][$j][0] && (!isset($desk[$i+1][$j]) || !$desk[$i+1][$j][0])) {
                    //print $i.$j.$desk[$i+1][$j][1];
                    $ff='';//для временного отключения поиска слов справа
                    if (date('U') < $thinkEndTime)
                        findWordSprava($i, $j, $desk, $fishki, $slovaPlayed);
                    //Ищем слова по горизонтали (пока) заканчивающиеся на $i+1...
                    //break 2;
                }
                
            }
        if (count($fishki) && (count($fishki1)!=count($fishki))) 
            continue;//На следующий круг
        
        else
            break;
    }
    if (count($fishki1) == count($fishki)) {
        $fishki = $fishki1;
        return FALSE;
    }
    ELSE return TRUE;
    

}










function printr(&$cells) {
    for ($j=0;$j<=14;$j++) {
        for ($i=0;$i<=14;$i++)
            if (($i == $j) && !$cells[$i][$j][0])
                print ($i % 10);
            elseif ($cells[$i][$j][0])
                    print \Lang\Ru::$bukvy[$cells[$i][$j][1] < 999 ? $cells[$i][$j][1] : $cells[$i][$j][1] - 999 - 1][0];
                else print '.';
        print "\n";
    }
    //sleep (1);
}




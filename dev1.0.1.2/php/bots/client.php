<?php
define("BOTSNUM",20);
$minutesToGo = 2;
set_time_limit ( $minutesToGo*60 + 5 );
$start_script_time = date('U');
$script_work_time = $minutesToGo*60 - 5;
//error_reporting(E_ALL & ~E_NOTICE); ini_set('display_errors', 0);
include_once(__DIR__.'/../../../vendor/deprecated/xcache_functions.php');
include_once(__DIR__.'/../CacheLangProvider.php');
include(__DIR__.'/findWordSlevaFunction.php');
include(__DIR__.'/findWordSverhuFunction.php');
include(__DIR__.'/findWordSpravaFunction.php');
include(__DIR__.'/findWordVnizFunction.php');
include(__DIR__.'/checkWordFishkiFunction.php');
include(__DIR__.'/maxToFunctions.php');
$red = \Dadata\Cache::getInstance();
$allBots = $red->redis->hgetall('erudit.bot_v1_list');
$botsTurns = [];
//print_r($allBots); //exit();
foreach($allBots as $bot => $num)
    if ( !($currentGame = $red->redis->get('erudit.get_game_'.$bot)) ) {
        $red->redis->hdel('erudit.bot_v1_list',$bot);
        unset($allBots[$bot]);
        //Очистили список ботов вне игры
    }
    else 
        $botsTurns[$bot] = 0;
        //Устанавливаем номер хода в 0 для играющих ботов
        
//print_r($allBots); 

while  ( (date('U') - $start_script_time) < $script_work_time) {
    foreach($allBots as $Bot => $numBot) {
        $_COOKIE['erudit_user_session_ID'] = $Bot;

        $resp = ['gameState' => 1];
        $zaprosNum = 1;

        ob_start();
        $_GET['queryNumber'] = $zaprosNum++;
        $resp = include __DIR__.'/../status_checker.php';
        //$obj->__destruct();
        $resp = json_decode(ob_get_contents(), true);
        ob_end_clean();

        ob_start();//Не будем анализировать ответы!)) - просто новая игра
        if ( ($resp['gameState'] == 'gameResults')) {
            $resp = include __DIR__.'/../exit_game.php';
            unset($botsTurns[$Bot]);
            //$obj->__destruct();
            $red->redis->hdel('erudit.bot_v1_list',$Bot);
            unset($allBots[$Bot]);
        }
        else {
            
            ob_end_clean();

            print $resp['gameState'];
            print "\n";
            print $resp['query_number']; 
            print "\n";
            ob_start();
            if ($resp['gameState'] == 'myTurn') {
                if ($botsTurns[$Bot] === $resp['gameSubState']) {
                    $dt = ['fishki' => $resp['fishki']];
                    $change_fishki = changeFishki($dt);
                }
                else {
                    $botsTurns[$Bot] = $resp['gameSubState'];
                    $turn_submit = sendResponse($resp);
                    $turn_submit = json_decode($turn_submit,true);
                }
                
            }
            
        }
        sleep(1);
    }
    if ($waiting_game = $red->redis->lpop('erudit.games_waiting')) {
        $waiting_game=unserialize($waiting_game);
            if (count($waiting_game) == 1) {
                foreach($waiting_game as $user => $time)
                    if (strstr($waiting_game[$user],'bot#')) {
                        unset($allBots[$waiting_game[$user]]);
                        $red->redis->hdel('erudit.bot_v1_list',$waiting_game[$user]);
                        unset($waiting_game[$user]);
                    }
                    //else $red->redis->lpush('erudit.games_waiting',serialize($waiting_game));
            }
            if ( (count($waiting_game) <= 3) && (count($waiting_game)>=1) ) {
                    $red->redis->lpush('erudit.games_waiting',serialize($waiting_game));
                    $noBots = true;
                    foreach($waiting_game as $user => $time)
                        if ( substr($waiting_game[$user],0,4) == 'bot#' )
                            $noBots = false;
                    if ($noBots)
                        if ($newBot = genNewBot($allBots)) {
                            
                            ob_start();//Не будем анализировать ответы!)) - просто новая игра
                            $_COOKIE['erudit_user_session_ID'] = $newBot;
                            $botsTurns[$bot] = 0;
                            $resp = include __DIR__.'/../status_checker.php';//game_starter.php';
                            //$obj->__destruct();
                            $allBots[$newBot] = 1;
                            $red->redis->hset('erudit.bot_v1_list',$newBot,1);
                            
                        }
                        
            }
            /*
            else
                    $red->redis->lpush('erudit.games_waiting',serialize($waiting_game));
                Не сохраняем игру из 0 игроков*/
    }  
    sleep(5);    
}
ob_end_clean();
exit();

function genNewBot($allBots) {
    $red=\Dadata\Cache::getInstance();
    $botNum = 'bot#' . ($red->redis->incr('erudit.max_v1_bot') % BOTSNUM) ;
    $i=0;
    while (isset($allBots[$botNum]) && (++$i<=BOTSNUM))
        $botNum = 'bot#' . ($red->redis->incr('erudit.max_v1_bot') % BOTSNUM) ;
    if ($i >= BOTSNUM) 
        return false;
    else
        return $botNum;
}

function changeFishki(&$data) {
   foreach($data['fishki'] as $fishka) {
            $kf++;
            if ($fishka != 999)
            $_POST['fishka_'.$kf.'_'.$fishka] = 'on';
        }
        ob_start();
        $resp = include __DIR__.'/../change_fishki.php';
        //$obj->__destruct();
        return $resp; 
}

function sendResponse(&$data) {
    if (isset($data['desk']))
        $_POST['cells'] = $data['desk'];
    else
        $_POST['cells'] = \Lang\Ru::init_desk();
    //error_reporting(E_ALL & ~E_NOTICE);  ini_set('display_errors', 0);
    if (make_turn($_POST['cells'], $data['fishki'])) {
        $_POST['cells'] = json_encode($_POST['cells']);
        ob_start();
        $resp = include __DIR__.'/../turn_submitter.php';
        //$obj->__destruct();
        return $resp;
    }
    else return changeFishki($data);/*{
        foreach($data['fishki'] as $fishka) {
            $kf++;
            if ($fishka != 999)
            $_POST['fishka_'.$kf.'_'.$fishka] = 'on';
        }
        ob_start();
        $resp = include __DIR__.'/../change_fishki.php';
        //$obj->__destruct();
        return $resp;
    }*/
        
}






function make_turn(&$desk, &$fishki) {
    //error_reporting(E_ALL & ~E_NOTICE); ini_set('display_errors', 0);
    ob_end_clean();
    $fishki1 = $fishki;
    $word = '';
    for ($j=0;$j<=14;$j++)
        for ($i=0;$i<=14;$i++)
            if ($desk[$i][$j][0] && ($desk[$i][$j][1] > 999))
                foreach ($fishki as $num => $fishka)
                    if ( ($fishka + 999 + 1) === $desk[$i][$j][1]) {
                        $desk[$i][$j][2] = $fishka;
                        $fishki[$num] = 999 +1 + $fishka;//$desk[$i][$j][1];
                        
                        break;
                    }
                    //Собрали звезды с поля
                    
    for ($k = 0; $k<2; $k++) {// два прохода
        //$j - строки, $i - столбцы
        for ($j=0;$j<=14;$j++)
            for ($i=0;$i<=14;$i++) {
                if ( ($i == 7) && ($j == 7) && !$desk[$i][$j][0]) {
                    
                    findWordSleva($i,$j,$desk,$fishki);
                    
                }
                
                if ( !$desk[$i][$j][0] && ( isset($desk[$i][$j-1]) && $desk[$i][$j-1][0] ) ) {
                    //print $i.$j.$desk[$i+1][$j][1];
                    $ff='';//для временного отключения поиска слов вниз
                    findWordVniz($i,$j,$desk,$fishki);
                    //Ищем слова по вертикали (пока) начинающиеся на $j-1...
                }
                
                if (!$desk[$i][$j][0] && $desk[$i+1][$j][0] ) {
                    //print $i.$j.$desk[$i+1][$j][1];
                    $ff='';//для временного отключения поиска слов слева
                    findWordSleva($i,$j,$desk,$fishki);
                    //Ищем слова по горизонтали (пока) заканчивающиеся на $i+1...
                }
                
                if ( !$desk[$i][$j][0] && ( isset($desk[$i][$j+1]) && $desk[$i][$j+1][0] ) ) {
                    //print $i.$j.$desk[$i+1][$j][1];
                    $ff='';//для временного отключения поиска слов сверху
                    findWordSverhu($i,$j,$desk,$fishki);
                    //Ищем слова по вертикали (пока) заканчивающиеся на $j+1...
                }
                
                if (!$desk[$i][$j][0] && $desk[$i-1][$j][0] ) {
                    //print $i.$j.$desk[$i+1][$j][1];
                    $ff='';//для временного отключения поиска слов справа
                    findWordSprava($i,$j,$desk,$fishki);
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
    sleep (1);    
}




<?php
set_time_limit ( 10*60 + 30 );
$start_script_time = date('U');
$script_work_time = 10*60 - 20;
error_reporting(E_ALL & ~E_NOTICE);
ini_set('display_errors', 0);
include_once(__DIR__.'/../../../vendor/deprecated/xcache_functions.php');
include('findWordSlevaFunction.php');
include('findWordSverhuFunction.php');
include('findWordSpravaFunction.php');
include('findWordVnizFunction.php');
include('checkWordFishkiFunction.php');
include('maxToFunctions.php');
$_COOKIE['erudit_user_session_ID'] = 'bot#2';

$resp = ['gameState' => 1];
$zaprosNum = 1;

ob_start();
//$resp = include '../new_game.php';
//$obj->__destruct();
//$resp = include '../game_starter.php';
//$obj->__destruct();

while ( ($resp['gameState'] !== 'gameResults') && ((date('U') - $start_script_time) < $script_work_time)) {
    ob_end_clean();
    ob_start();
    $_GET['queryNumber'] = $zaprosNum++;
    $resp = include __DIR__.'/../status_checker.php';
    $obj->__destruct();
    $resp = json_decode(ob_get_contents(), true);
    ob_end_clean();

    ob_start();//Не будем анализировать ответы!)) - просто новая игра
    if ( ($resp['gameState'] == 'gameResults')) {
        $resp = include __DIR__.'/../new_game.php';
        $obj->__destruct();
        $resp = include __DIR__.'/../game_starter.php';
        $obj->__destruct();
    }
    else {
        ob_end_clean();

        print $resp['gameState'];
        print "\n";
        print $resp['query_number']; 
        print "\n";
        ob_start();
        if ($resp['gameState'] == 'myTurn') {
            //ob_end_clean();
            //print_r($resp);
            $turn_submit = sendResponse($resp);
            $turn_submit = json_decode($turn_submit,true);
            
        }
        
    }
    sleep(10);
}
ob_end_clean();
exit();

function sendResponse(&$data) {
    if (isset($data['desk']))
        $_POST['cells'] = $data['desk'];
    else
        $_POST['cells'] = \Lang\Ru::init_desk();
    error_reporting(E_ALL & ~E_NOTICE);
    ini_set('display_errors', 0);
    if (make_turn($_POST['cells'], $data['fishki'])) {
        $_POST['cells'] = json_encode($_POST['cells']);
        ob_start();
        $resp = include __DIR__.'/../turn_submitter.php';
        $obj->__destruct();
        return $resp;
    }
    else {
        foreach($data['fishki'] as $fishka) {
            $kf++;
            if ($fishka != 999)
            $_POST['fishka_'.$kf.'_'.$fishka] = 'on';
        }
        ob_start();
        $resp = include __DIR__.'/../change_fishki.php';
        $obj->__destruct();
        return $resp;
    }
        
}






function make_turn(&$desk, $fishki) {
    ob_end_clean();
    $fishki1 = $fishki;
    $word = '';
    
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
    if (count($fishki1) == count($fishki))
        return FALSE;
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
}




<?php
define("BOTSNUM",100);

//error_reporting(E_ALL & ~E_NOTICE);
ini_set('display_errors', 0);
include_once(__DIR__.'/../../../vendor/deprecated/xcache_functions.php');
include_once(__DIR__.'/../CacheLangProvider.php');

class BotGen
{
    public $p;
    public function  __construct() {
        $minutesToGo = 5;
        $_SERVER['DOCUMENT_ROOT'] = '/var/www/erudit.club';
        set_time_limit ( $minutesToGo*60 + 5 );
        $start_script_time = date('U');
        $script_work_time = $minutesToGo*60 - 5;

        $this->p = \Dadata\Cache::getInstance();
        $config = include("{$_SERVER['DOCUMENT_ROOT']}/configs/conf.php");
        
        $red = \Dadata\Cache::getInstance();

        $botsTurns = [];

        while  ( (date('U') - $start_script_time) < $script_work_time) {
            
            if( $llen=$red->redis->llen('erudit.games_waiting') )
                for($i=0;$i<$llen;$i++)
                    if ($waiting_game = $red->redis->lpop('erudit.games_waiting')) {
                        $waiting_game=unserialize($waiting_game);
                        print_r($waiting_game); print  $config['gameWaitLimit'];//sleep(4);
                            if (count($waiting_game) == 1) {
                                foreach($waiting_game as $user => $time)
                                    if (substr($user,0,4) == 'bot#') {
                                        $red->redis->hdel('erudit.bot_v1_list',$user);
                                        unset($waiting_game[$user]);
                                    }
                            }
                            if ( (count($waiting_game) <= 3) && (count($waiting_game)>=1) ) {
                                    
                                    $noBots = true;
                                    $countBots=0;
                                    $minTime=9999999999;
                                    foreach($waiting_game as $user => $time) {
                                        if ( substr($user,0,4) == 'bot#' ) {
                                            $noBots = false;
                                            if (++$countBots>1) {
                                                $red->redis->hdel('erudit.bot_v1_list',$user);
                                                unset($waiting_game[$user]);
                                            }
                                        }
                                        if ($time < $minTime)
                                            $minTime = $time;
                                    }
                                    
                                    if (count($waiting_game))
                                        $red->redis->rpush('erudit.games_waiting',serialize($waiting_game));
                                    
                                    if ($noBots)
                                        if ( (date('U') - $minTime + 5) > $config['gameWaitLimit'] )
                                            //Подключаем бота в конце подбора игры
                                            if ($newBot = genNewBot()) {
                                                //Не будем анализировать ответы!)) - просто новая игра
                                                $_COOKIE['erudit_user_session_ID'] = $newBot;
                                                $botsTurns[$newBot] = 0;
                                                ob_start();
                                                $resp = include __DIR__.'/../status_checker.php';
                                                ob_end_clean();
                                                //Пинганули сервер, что есть новый игрок
                                                $red->redis->rpush('erudit.bot_games',$newBot);
                                                //Сохранили нового бота в список игроков
                                            }
                                        
                            }
                            /*
                            else
                                    $red->redis->lpush('erudit.games_waiting',serialize($waiting_game));
                                Не сохраняем игру из 0 игроков*/
                    }  
            sleep(5);  
            print 'next!';    
        }
    }
}


new BotGen();



function genNewBot() {
    $red=\Dadata\Cache::getInstance();
    
    for ($num = 0; $num < BOTSNUM; $num++)
        if ( ($incr = $red->redis->hincrBy( 'erudit.bot_v1_list', $botNum = ('bot#'.$num), 1 )) == 1) 
            return $botNum;
        else
            if ($incr > 200) {
                $red->redis->hset( 'erudit.bot_v1_list', $botNum = ('bot#'.$num),1);
                return $botNum;
            }
    
    return false;
}











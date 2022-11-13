<?php
define("BOTSNUM",47);//число ников)

//error_reporting(E_ALL & ~E_NOTICE); ini_set('display_errors', 0);

include_once(__DIR__.'/../CacheLangProvider.php');

class BotGenV3
{
    public $p;
    private $config;
    
    public function  __construct() {
        $minutesToGo = 5;
        $_SERVER['DOCUMENT_ROOT'] = '/var/www/erudit.club';
        set_time_limit ( $minutesToGo*60 + 5 );
        $start_script_time = date('U');
        $script_work_time = $minutesToGo*60 - 5;

        $this->p = \Dadata\Cache::getInstance();
        $this->config = include("{$_SERVER['DOCUMENT_ROOT']}/configs/conf.php");

        while  ( (date('U') - $start_script_time) < $script_work_time) {
            if ($this->players4Waiting()) {
                if ($this->timeToMake4Game()) {
                    if ($this->noBots4Waiting())
                        if ($newBot = $this->genNewBot()) {
                            $this->storeTo4Players($newBot);
                        //Не будем анализировать ответы!)) - просто новая игра
                            $_COOKIE['erudit_user_session_ID'] = $newBot;
                            ob_start();
                            $resp = include __DIR__.'/../status_checker.php';
                            ob_end_clean();
                            //Пинганули сервер, что есть новый игрок
                            $this->p->redis->rpush('erudit.bot_games',$newBot);
                            //Сохранили нового бота в список игроков
                        }
                        else
                            print 'не удалось создать бота genNewBot'."\n";
                }
                else
                    print ' '.date('U').'- не подошло время создавать бота'."\n";
            }
            if ($this->players2Waiting()) 
                    if ($this->timeToMake2Game())
                        if ($this->noBots2Waiting())
                            if ($newBot = $this->genNewBot()) {
                                $this->storeTo2Players($newBot);
                                $this->startGame($newBot);
                            }
                            
           
            sleep(5);  
            print 'next!';
        }            
        
    }

    
    private function startGame($botName) {
        //Не будем анализировать ответы!)) - просто новая игра
        $_COOKIE['erudit_user_session_ID'] = $botName;
        ob_start();
        $resp = include __DIR__.'/../status_checker.php';
        ob_end_clean();
        //Пинганули сервер, что есть новый игрок
        $this->p->redis->rpush('erudit.bot_games',$botName);
        //Сохранили нового бота в список игроков
    }
    
    private function storeTo2Players($User) {
        //print "storeto 2!!!";
        if (isset($this->POST['ochki_num']))
            $options = $this->POST;
        else
            $options = FALSE;
        if (!$this->p->redis->hget('erudit.2players_waiters',$User))
            $this->p->redis->hset(
                'erudit.2players_waiters',
                $User,
                serialize(['time'=>date('U'),
                            'options' => $options])
            );
    }
    
    private function storeTo4Players($User) {
        
        if (!$this->p->redis->hget('erudit.4players_waiters',$User)) {
            if (isset($this->POST['ochki_num']))
                $options = $this->POST;
            else
                $options = FALSE;
            
            $this->p->redis->hset(
                'erudit.4players_waiters',
                $User,
                serialize(['time'=>date('U'),
                            'options' => $options])
            );
        }
    }
    
    private function noBots2Waiting() {
        $allPlayers2Waiting = $this->p->redis->hgetall('erudit.2players_waiters');
        foreach($allPlayers2Waiting as $player => $serializedData)
            if ( strpos($player,'otV3#') !== false)
                return FALSE;
        return TRUE;
    }
    
    private function timeToMake2Game() {
        $waitingPlayers = $this->p->redis->hgetall('erudit.2players_waiters');
        $maxTimeWaiting = 0;
        foreach($waitingPlayers as $player => $data) {
            $data = unserialize($data);
            if ( (date('U') - $data['time']) > $maxTimeWaiting)
                $maxTimeWaiting = date('U') - $data['time'];
        }
        if ($maxTimeWaiting < $this->config['gameWaitLimit'])
            return FALSE;
        else return TRUE;
    }
    
    private function players2Waiting() {
        $cnt = $this->p->redis->hlen('erudit.2players_waiters');
        if ( $cnt >= 1 )
            return true;
        return false;
    }
    
    private function genNewBot() {
        for ($num = rand(0,BOTSNUM - 1); $num < BOTSNUM*2; $num++)
            if ( ($incr = $this->p->redis->hincrBy( 'erudit.bot_v3_list', $botNum = ('botV3#'.($num % BOTSNUM)), 1 )) == 1)
                return $botNum;
        else
            if ($incr > 200) {
                $this->p->redis->hset( 'erudit.bot_v3_list', $botNum = ('botV3#'.$num),1);
                return $botNum;
            }
    
    return false;
}
    
    private function noBots4Waiting() {
        $allPlayers4Waiting = $this->p->redis->hgetall('erudit.4players_waiters');
        foreach($allPlayers4Waiting as $player => $serializedData) {
            print "!!!!!!!!".$player;
            if ( strpos($player,'otV3#') !== false )
                return FALSE;
        }
        return TRUE;
    }
    
    private function timeToMake4Game() {
        $waitingPlayers = $this->p->redis->hgetall('erudit.4players_waiters');
        $maxTimeWaiting = 0;
        foreach($waitingPlayers as $player => $data) {
            $data = unserialize($data);
            if ( (date('U') - $data['time']) > $maxTimeWaiting)
                $maxTimeWaiting = date('U') - $data['time'];
        }
        if ($maxTimeWaiting < ($this->config['gameWaitLimit'] > 15 ? 20 : $this->config['gameWaitLimit']))
            return FALSE;
        else return TRUE;
    }
    
    private function players4Waiting() {
        $cnt = $this->p->redis->hlen('erudit.4players_waiters');
        print $cnt;
        if ( ($cnt>=1) && ($cnt<4) )    
            return true;
        return false;
    }
}


new BotGenV3();















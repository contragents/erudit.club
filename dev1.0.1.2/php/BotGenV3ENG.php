<?php

class BotGenV3ENG
{
    const BOTSNUM = 47; // число ботов)
    const MINUTES_TO_GO = 5;
    const LANG = 'EN';

    public $p;
    private $config;

    const BOT_GAMES = 'erudit.botEN_games';
    const WAITERS_2_PLAYERS_QUEUE = 'erudit.2ENplayers_waiters';
    const WAITERS_4_PLAYERS_QUEUE = 'erudit.4ENplayers_waiters';
    const BOT_LIST = 'erudit.bot_v3_list';
    const BOT_TPL = 'botV3#';

    public function __construct()
    {
        $_SERVER['DOCUMENT_ROOT'] = '/var/www/erudit.club';
        set_time_limit(self::MINUTES_TO_GO * 60 + 5);
        $start_script_time = date('U');
        $script_work_time = self::MINUTES_TO_GO * 60 - 5;

        $this->p = Cache::getInstance();
        $this->config = include __DIR__ . '/../../configs/conf.php';

        while ((date('U') - $start_script_time) < $script_work_time) {
            if ($this->players4Waiting()) {
                if ($this->timeToMake4Game()) {
                    if ($this->noBots4Waiting()) {
                        if ($newBot = $this->genNewBot()) {
                            $this->storeTo4Players($newBot);
                            $this->startGame($newBot);
                        } else {
                            print 'не удалось создать бота genNewBot' . "\n";
                        }
                    }
                } else {
                    print ' ' . date('U') . '- не подошло время создавать бота' . "\n";
                }
            }

            if ($this->players2Waiting()) {
                if ($this->timeToMake2Game()) {
                    if ($this->noBots2Waiting()) {
                        if ($newBot = $this->genNewBot()) {
                            $this->storeTo2Players($newBot);
                            $this->startGame($newBot);
                        }
                    }
                }
            }

            sleep(5);
            print 'next!';
        }
    }


    private function startGame($botName)
    {
        //Не будем анализировать ответы!)) - просто новая игра
        $_COOKIE[Cookie::COOKIE_NAME] = $botName;
        $_GET['lang'] = static::LANG;
        ob_start();
        $resp = include __DIR__ . '/status_checker.php';
        ob_end_clean();
        //Пинганули сервер, что есть новый игрок
        $this->p->redis->rpush(static::BOT_GAMES, $botName);
        //Сохранили нового бота в список игроков
    }

    private function storeTo2Players($User)
    {
        $options = false;

        if (!$this->p->redis->hget(static::WAITERS_2_PLAYERS_QUEUE, $User)) {
            $this->p->redis->hset(
                static::WAITERS_2_PLAYERS_QUEUE,
                $User,
                serialize(
                    [
                        'time' => date('U'),
                        'options' => $options
                    ]
                )
            );
        }
    }

    private function storeTo4Players($User)
    {
        if (!$this->p->redis->hget(static::WAITERS_4_PLAYERS_QUEUE, $User)) {
            $options = false;

            $this->p->redis->hset(
                static::WAITERS_4_PLAYERS_QUEUE,
                $User,
                serialize(
                    [
                        'time' => date('U'),
                        'options' => $options
                    ]
                )
            );
        }
    }

    private function noBots2Waiting()
    {
        $allPlayers2Waiting = $this->p->redis->hgetall(static::WAITERS_2_PLAYERS_QUEUE);
        foreach ($allPlayers2Waiting as $player => $serializedData) {
            if (strpos($player, self::BOT_TPL) !== false) {
                return false;
            }
        }
        return true;
    }

    private function timeToMake2Game()
    {
        $waitingPlayers = $this->p->redis->hgetall(static::WAITERS_2_PLAYERS_QUEUE);
        $maxTimeWaiting = 0;
        foreach ($waitingPlayers as $player => $data) {
            $data = unserialize($data);
            if ((date('U') - $data['time']) > $maxTimeWaiting) {
                $maxTimeWaiting = date('U') - $data['time'];
            }
        }
        if ($maxTimeWaiting < $this->config['gameWaitLimit']) {
            return false;
        } else {
            return true;
        }
    }

    private function players2Waiting()
    {
        $cnt = $this->p->redis->hlen(static::WAITERS_2_PLAYERS_QUEUE);
        if ($cnt >= 1) {
            return true;
        }
        return false;
    }

    private function genNewBot()
    {
        for ($num = rand(0, self::BOTSNUM - 1); $num < self::BOTSNUM * 2; $num++) {
            if (($incr = $this->p->redis->hincrBy(
                    self::BOT_LIST,
                    $botNum = (self::BOT_TPL . ($num % self::BOTSNUM)),
                    1
                )) == 1) {
                return $botNum;
            } else {
                if ($incr > 200) {
                    $this->p->redis->hset(self::BOT_LIST, $botNum = (self::BOT_TPL . ($num % self::BOTSNUM)), 1);
                    return $botNum;
                }
            }
        }

        return false;
    }

    private function noBots4Waiting()
    {
        $allPlayers4Waiting = $this->p->redis->hgetall(static::WAITERS_4_PLAYERS_QUEUE);
        foreach ($allPlayers4Waiting as $player => $serializedData) {
            print "!!!!!!!!" . $player;
            if (strpos($player, self::BOT_TPL) !== false) {
                return false;
            }
        }
        return true;
    }

    private function timeToMake4Game()
    {
        $waitingPlayers = $this->p->redis->hgetall(static::WAITERS_4_PLAYERS_QUEUE);
        $maxTimeWaiting = 0;
        foreach ($waitingPlayers as $player => $data) {
            $data = unserialize($data);
            if ((date('U') - $data['time']) > $maxTimeWaiting) {
                $maxTimeWaiting = date('U') - $data['time'];
            }
        }
        if ($maxTimeWaiting < $this->config['gameWaitLimit']) {
            return false;
        } else {
            return true;
        }
    }

    private function players4Waiting()
    {
        $cnt = $this->p->redis->hlen(static::WAITERS_4_PLAYERS_QUEUE);
        print $cnt;
        if (($cnt >= 1) && ($cnt < 4)) {
            return true;
        }
        return false;
    }
}
















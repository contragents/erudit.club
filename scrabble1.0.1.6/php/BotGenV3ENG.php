<?php

class BotGenV3ENG
{
    const BOTSNUM = 47; // число ботов)
    const MINUTES_TO_GO = 5;
    const LANG = 'EN';

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

        $this->config = include __DIR__ . '/../../configs/conf.php';

        while ((date('U') - $start_script_time) < $script_work_time) {
            if ($this->players2Waiting()) {
                if ($this->timeToMake2Game()) {
                    if ($this->noBots2Waiting()) {
                        if ($newBot = $this->genNewBot()) {
                            $this->storeTo2Players($newBot);
                            $prevLlen = Cache::llen(static::BOT_GAMES);
                            Cache::rpush(static::BOT_GAMES, $newBot);
                        }
                    }
                }
            }

            sleep(5);

            if (Cache::llen(static::BOT_GAMES) == 0) {
                self::releaseBots();
            }

            print 'next!';
        }
    }


    protected static function releaseBots() {
        $botsInUse = Cache::hgetall(self::BOT_LIST) ?: [];

        foreach($botsInUse as $bot => $nothing) {
            // проверим бота на участие в играх или в очереди подбора
            if(Game::isInGame($bot, (bool)(date('U') % 10))) {
                continue;
            }

            if(Queue::isUserInQueue($bot)) {
                continue;
            }

            // удаляем бота из списка занятых ботов
            Cache::hdel(self::BOT_LIST, $bot);
        }
    }

    private function storeTo2Players($User)
    {
        $options = false;

        if (!Cache::hget(static::WAITERS_2_PLAYERS_QUEUE, $User)) {
            Cache::hset(
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

    private function noBots2Waiting()
    {
        $allPlayers2Waiting = Cache::hgetall(static::WAITERS_2_PLAYERS_QUEUE);
        foreach ($allPlayers2Waiting as $player => $serializedData) {
            if (strpos($player, self::BOT_TPL) !== false) {
                return false;
            }
        }
        return true;
    }

    private function timeToMake2Game()
    {
        $waitingPlayers = Cache::hgetall(static::WAITERS_2_PLAYERS_QUEUE);
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
        $cnt = Cache::hlen(static::WAITERS_2_PLAYERS_QUEUE);
        if ($cnt >= 1) {
            return true;
        }

        return false;
    }

    private function genNewBot()
    {
        for ($num = rand(0, self::BOTSNUM - 1); $num < self::BOTSNUM * 2; $num++) {
            if (($incr = Cache::hincrBy(
                    self::BOT_LIST,
                    $botNum = (self::BOT_TPL . ($num % self::BOTSNUM)),
                    1
                )) == 1) {
                return $botNum;
            } else {
                if ($incr > 200) {
                    Cache::hset(self::BOT_LIST, $botNum = (self::BOT_TPL . ($num % self::BOTSNUM)), 1);
                    return $botNum;
                }
            }
        }

        return false;
    }
}
















<?php

class BotGenV3ENG
{
    const BOTSNUM = 47; // число ботов)
    const MINUTES_TO_GO = 5;
    const LANG = T::EN_LANG;

    private $config;

    const BOT_GAMES = 'erudit.botEN_games';
    const WAITERS_2_PLAYERS_QUEUE = 'erudit.2ENplayers_waiters';
    const BOT_LIST = 'erudit.bot_v3_list';
    const BOT_TPL = Game::BOT_TPL;

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
                            Cache::rpush(static::BOT_GAMES, $newBot);
                        }
                    }
                }
            }

            sleep(5);

            // Проверяем и освобождаем незанятых ботов
            self::releaseBots();

            print 'next!';
        }
    }


    /**
     * Проверяет BOT_LIST и освобождает незанятых ботов или возвращает выпавших в стек
     * @return void
     */
    protected static function releaseBots(): void
    {
        $botsInUse = Cache::hgetall(self::BOT_LIST) ?: [];
        $botsInStack = Cache::lrange(static::BOT_GAMES, 0, -1);

        foreach ($botsInUse as $bot => $nothing) {
            // проверим бота на участие в играх или в очереди подбора
            if ($lang = Game::isInGame($bot, mt_rand(1, 20) <= 2)) {
                if($lang === static::LANG && !in_array($bot, $botsInStack)) {
                    // Бот в игре, но вылетел из стека - ставим обратно
                    Cache::rpush(static::BOT_GAMES, $bot);
                }

                continue;
            }

            if (Queue::isUserInQueueByLang($bot, static::LANG)) {
                if(!in_array($bot, $botsInStack)) {
                    // Бот в очереди, но вылетел из стека - ставим обратно
                    Cache::rpush(static::BOT_GAMES, $bot);
                }

                continue;
            }

            if (Queue::isUserInQueue($bot)) {
                // Бот в очереди, но на другом языке - пропускаем
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

    private function noBots2Waiting(): bool
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
















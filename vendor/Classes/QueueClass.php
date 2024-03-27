<?php

class Queue
{
    const QUEUES = [
        'erudit.rating_waiters' => 'erudit.rating_waiters',
        'erudit.ratingEN_waiters' => 'erudit.ratingEN_waiters',
        'erudit.2players_waiters' => 'erudit.2players_waiters',
        'erudit.2ENplayers_waiters' => 'erudit.2ENplayers_waiters',
        'erudit.4players_waiters' => 'erudit.4players_waiters',
        'erudit.4ENplayers_waiters' => 'erudit.4ENplayers_waiters',
        'erudit.inviteplayers_waiters' => 'erudit.inviteplayers_waiters',
        'erudit.inviteENplayers_waiters' => 'erudit.inviteENplayers_waiters',
    ];

    const QUEUE_NUMS = [
        'invite' => 'invite',
    ];

    const MAX_INVITE_WAIT_TIME = 60;
    const PREFERENCES_TTL = 30 * 24 * 60 * 60;
    const PREFS_KEY = 'erudit.user_preference_';
    const CURRENT_GAME_KEY = 'erudit.current_game_';
    const GAMES_COUNTER = 'erudit.num_games';
    const GET_GAME_KEY = 'erudit.get_game_';
    const GAME_KEY = 'erudit.game_';

    protected $User;
    protected $userTime;
    protected $semaphoreLocked = false;

    protected bool $initGame = false;
    protected bool $userInInitStatus = false;
    const USER_STATUS_PREFIX = 'erudit.user_status_';

    protected Game $caller;
    protected array $POST;
    protected $lang;

    public function __construct($User, Game $caller, array $POST)
    {
        $this->User = $User;
        $this->caller = $caller;
        $this->POST = $POST;

        $this->userInInitStatus = $this->checkPlayerStatus();

        $this->lang = $this->POST['lang'] ?? '';

        if (isset($this->POST['ochki_num'])) {
            Cache::setex(
                static::PREFS_KEY . $this->User,
                static::PREFERENCES_TTL,
                $this->POST
            );
            //В начале игры сохраняем предпочтения игрока для игры по приглашению
        }
    }

    protected function checkPlayerStatus(): bool
    {
        $initGame = $this->POST['init_game'] ?? (bool)($this->POST['ochki_num'] ?? false);

        if (!$initGame && !Cache::get(static::USER_STATUS_PREFIX . $this->User)) {
            return false;
        }

        Cache::setex(static::USER_STATUS_PREFIX . $this->User, 60, Game::INIT_GAME_STATE);
        return true;
    }

    public static function isUserInInviteQueue(string $user)
    {
        if (Cache::hget(static::QUEUES['erudit.inviteplayers_waiters'], $user)) {
            return true;
        }

        if (Cache::hget(static::QUEUES['erudit.inviteENplayers_waiters'], $user)) {
            return true;
        }

        return false;
    }

    public static function isUserInQueue(string $user): bool
    {
        foreach (static::QUEUES as $queue) {
            if (Cache::hget($queue, $user)) {
                return true;
            }
        }

        return false;
    }

    public function doSomethingWithThisStuff($lang = '')
    {
        if (!$this->userInInitStatus) {
            $chooseGameParams = [
                'gameState' => 'chooseGame',
                'gameSubState' => 'choosing',
                'players' => $this->caller->onlinePlayers(),
                'prefs' => Cache::get(Queue::PREFS_KEY . $this->User)
            ];

            return $this->caller
                ->makeResponse($chooseGameParams);
        }

        if ($lang) {
            $this->lang = $lang;
        }

        if ($this->checkInviteQueue()) {
            if ($this->inviteQueueFull()) {
                if ($this->tryNewGameSemaphore()) {
                    return $this->makeGame($this->User, static::QUEUE_NUMS['invite']);
                }
            }

            return $this->caller
                ->makeResponse(
                    [
                        'gameState' => Game::INIT_GAME_STATE,
                        'gameSubState' => Cache::hlen(static::QUEUES["erudit.invite{$this->lang}players_waiters"]),
                        'gameWaitLimit' => $this->caller->gameWaitLimit
                    ]
                );
        }

        if ($this->lang === '') {
            // Только для игры на Русском
            if ($ratingWanted = $this->waitRatingPlayer($this->User)) //Сразу помещает в спецочередь
            {
                if (($ratingPlayer = $this->findRatingPlayer($ratingWanted)) !== false) {
                    if ($this->tryNewGameSemaphore()) {
                        return $this->makeRatingGame($this->User, $ratingPlayer);
                    }
                }
                if ($this->timeToWaitRatingPlayerOver($this->User)) {
                    return $this->storeToCommonQueue($this->User);
                }

                return $this->stillWaitRatingPlayer();
            }

            if (strpos($this->User, 'ot') === false) {
                if (($curPlayerRating = $this->caller->getRatings($this->User)[0]['rating'] ?? 0) > 1900) {
                    if (is_array($ratingPlayer = $this->findWaitingRaitingPlayer($curPlayerRating))) {
                        if ($this->tryNewGameSemaphore()) {
                            return $this->makeReverseRatingGame($ratingPlayer);
                        }
                    }
                }
            }
        }

        if ($this->want4Players($this->User)) {
            if ($this->players4Waiting($this->User)) {
                if ($this->timeToMake4Game() && $this->tryNewGameSemaphore()) {
                    return $this->makeGame($this->User, 4);
                } else {
                    return $this->storeTo4Players($this->User);
                }
            } else {
                if ($this->players2Waiting($this->User)) {
                    if ($this->tryNewGameSemaphore()) {
                        return $this->makeGame($this->User, 2);
                    }
                }

                return $this->storeTo4Players($this->User);
            }
        } else {
            if ($this->players2Waiting($this->User)) {
                if ($this->tryNewGameSemaphore()) {
                    return $this->makeGame($this->User, 2);
                }
            }

            return $this->storeTo2Players($this->User);
        }
    }

    protected function inviteQueueFull()
    {
        if (Cache::hlen(static::QUEUES["erudit.invite{$this->lang}players_waiters"]) < 2) {
            return false;
        }

        // Проверяем очередь ждущих реванша на наличие просроченных заявок от игроков
        $waitingPlayers = Cache::hgetall(static::QUEUES["erudit.invite{$this->lang}players_waiters"]);
        foreach ($waitingPlayers as $player => $playerInfo) {
            if ($playerInfo && $player != $this->User) {
                $player_data = @unserialize($playerInfo);
                if (isset($player_data['time']) && (date('U') - $player_data['time']) > self::MAX_INVITE_WAIT_TIME) {
                    self::cleanUp($player);
                }
            }
        }

        // Если очередь все еще длиной не менее 2х игроков - возвращаем тру
        if (Cache::hlen(static::QUEUES["erudit.invite{$this->lang}players_waiters"]) >= 2) {
            return true;
        }

        return false;
    }

    protected function checkInviteQueue()
    {
        $playerInfo = Cache::hget(static::QUEUES["erudit.invite{$this->lang}players_waiters"], $this->User);

        if ($playerInfo) {
            if (isset($playerInfo['time']) && (date('U') - $playerInfo['time']) > static::MAX_INVITE_WAIT_TIME) {
                self::cleanUp($this->User);

                return false;
            } else {
                return true;
            }
        }

        return false;
    }

    protected function waitRatingPlayer($User)
    {
        if (strpos($User, 'ot') !== false) {
            return false;
        }

        if (isset($this->POST['from_rating']) && ($this->POST['from_rating'] == 0)) {
            return false;
        }

        if (($this->POST['from_rating'] ?? 0) > 0) {
            if (isset($this->POST['ochki_num'])) {
                $options = $this->POST;
            } else {
                $options = false;
            }

            $this->userTime = date('U');
            Cache::hset(
                static::QUEUES["erudit.rating{$this->lang}_waiters"],
                $User,
                [
                    'time' => $this->userTime,
                    'rating' => $this->POST['from_rating'],
                    'options' => $options
                ]
            );

            return $this->POST['from_rating'];
        }

        if ($waiterData = Cache::hget(static::QUEUES["erudit.rating{$this->lang}_waiters"], $User)) {
            $this->userTime = $waiterData['time'];

            return $waiterData['rating'];
        }

        return false;
    }

    protected function findRatingPlayer($ratingWanted)
    {
        if (($players2Waiting = Cache::hgetall(static::QUEUES["erudit.2{$this->lang}players_waiters"]))) {
            foreach ($players2Waiting as $player => $data) {
                if (
                    !strpos($player, 'ot')
                    &&
                    ($this->caller->getRatings(['cookie' => $player])['rating'] ?? 0) >= $ratingWanted
                ) {
                    return [$player => 2];
                }
            }
        }

        if (($players4Waiting = Cache::hgetall(static::QUEUES["erudit.4{$this->lang}players_waiters"]))) {
            foreach ($players4Waiting as $player => $data) {
                if (
                    !strpos($player, 'ot')
                    &&
                    ($this->caller->getRatings(['cookie' => $player])['rating'] ?? 0) >= $ratingWanted
                ) {
                    return [$player => 4];
                }
            }
        }

        if (($playersRatingWaiting = Cache::hgetall(static::QUEUES["erudit.rating{$this->lang}_waiters"]))) {
            foreach ($playersRatingWaiting as $player => $data) {
                if ($player != $this->User) {
                    $playerInfo = unserialize($data);

                    if (
                        ($this->caller->getRatings(['cookie' => $player])['rating'] ?? 0) >= $ratingWanted
                        &&
                        ($this->caller->getRatings(['cookie' => $this->User])['rating'] ?? 0) >= $playerInfo['rating']
                    ) {
                        return [$player => 'ratingQueue'];
                    }
                }
            }
        }

        return false;
    }

    protected function findWaitingRaitingPlayer($curPlayerRating)
    {
        if (($playersRatingWaiting = Cache::hgetall(static::QUEUES["erudit.rating{$this->lang}_waiters"]))) {
            foreach ($playersRatingWaiting as $player => $data) {
                if ($player != $this->User) {
                    $data = unserialize($data);
                    if ($curPlayerRating >= $data['rating']) {
                        return ['cookie' => $player, 'options' => $data['options']];
                    }
                }
            }
        }

        return false;
    }

    protected function gatherUserData()
    {
        if (isset($this->POST['ochki_num'])) {
            return $this->POST;
        }

        if ($players2Queue = Cache::hget(
            static::QUEUES["erudit.2{$this->lang}players_waiters"],
            $this->User
        )
        ) {
            return $players2Queue['options'];
        }

        if ($players4Queue = Cache::hget(
            static::QUEUES["erudit.4{$this->lang}players_waiters"],
            $this->User
        )
        ) {
            return $players4Queue['options'];
        }

        return ['num_players' => 4, 'ochki_num' => rand(200, 300), 'turn_time' => rand(60, 120)];
    }

    protected function makeReverseRatingGame(array $ratingPlayer)
    {
        $thisUserOptions = $this->gatherUserData();

        Cache::hset(static::QUEUES["erudit.{$thisUserOptions['players_count']}{$this->lang}players_waiters"],
            $ratingPlayer['cookie'],
            [
                'time' => date('U'),
                'options' => $ratingPlayer['options']
            ]
        );
        //Поместили ожидающего рейтинг игрока в очередь текущего игрока
        Cache::hdel(static::QUEUES["erudit.rating{$this->lang}_waiters"], $ratingPlayer['cookie']);
        //Удалили ожидающего рейтинг игрока из очереди рейтинга

        Cache::hset(static::QUEUES["erudit.{$thisUserOptions['players_count']}{$this->lang}players_waiters"],
            $this->User,
            [
                'time' => date('U'),
                'options' => $thisUserOptions
            ]
        );
        //Поместили текущего игрока в очередь текущего игрока))

        return $this->{"makeGame"}($this->User, $thisUserOptions['players_count']);
        //Начали игру
    }

    protected function makeRatingGame($User, array $ratingPlayer)
    {
        $waiter = Cache::hget(static::QUEUES["erudit.rating{$this->lang}_waiters"], $User);
        $waiterData = $waiter;

        foreach ($ratingPlayer as $player => $numPlayersQueue) {
            if ($numPlayersQueue == 'ratingQueue') {
                $numPlayersQueue = 4;
                $playerData = Cache::hget(static::QUEUES["erudit.rating{$this->lang}_waiters"], $player);
                Cache::hset(static::QUEUES["erudit.{$numPlayersQueue}{$this->lang}players_waiters"],
                    $player,
                    [
                        'time' => $playerData['time'],
                        'options' => $playerData['options']
                    ]
                );

                Cache::hdel(static::QUEUES["erudit.rating{$this->lang}_waiters"], $player);
            }

            Cache::hset(
                static::QUEUES["erudit.{$numPlayersQueue}{$this->lang}players_waiters"],
                $User,
                [
                    'time' => $waiterData['time'],
                    'options' => $waiterData['options']
                ]
            );

            break;
        }

        Cache::hdel(static::QUEUES["erudit.rating{$this->lang}_waiters"], $User);
        return $this->{"makeGame"}($User, $numPlayersQueue);
    }

    protected function timeToWaitRatingPlayerOver($User)
    {
        $waiterData = Cache::hget(static::QUEUES["erudit.rating{$this->lang}_waiters"], $User);

        if ((date('U') - $waiterData['time']) > $this->caller->ratingGameWaitLimit) {
            return true;
        }

        return false;
    }

    protected function storeToCommonQueue($User)
    {
        $waiterData = Cache::hget(static::QUEUES["erudit.rating{$this->lang}_waiters"], $User);
        Cache::hdel(static::QUEUES["erudit.rating{$this->lang}_waiters"], $User);

        if (isset($waiterData['options']) && isset($waiterData['options']['players_count'])) {
            Cache::hset(
                static::QUEUES["erudit.{$waiterData['options']['players_count']}{$this->lang}players_waiters"],
                $User,
                [
                    'time' => $waiterData['time'],
                    'options' => $waiterData['options']
                ]
            );

            return $this->caller->makeResponse(
                [
                    'gameState' => Game::INIT_GAME_STATE,
                    'gameSubState' => Cache::hlen(
                        static::QUEUES["erudit.{$waiterData['options']['players_count']}{$this->lang}players_waiters"]
                    ),
                    'gameWaitLimit' => $this->caller->gameWaitLimit
                ]
            );
        }

        Cache::hset(
            static::QUEUES["erudit.2{$this->lang}players_waiters"],
            $User,
            [
                'time' => date('U'),
                'options' => false
            ]
        );
        return $this->caller->makeResponse(
            [
                'gameState' => Game::INIT_GAME_STATE,
                'gameSubState' => Cache::hlen(static::QUEUES["erudit.2{$this->lang}players_waiters"]),
                'gameWaitLimit' => $this->caller->gameWaitLimit
            ]
        );
    }

    protected function stillWaitRatingPlayer()
    {
        return $this->caller->makeResponse(
            [
                'gameState' => Game::INIT_RATING_GAME_STATE,
                'gameSubState' => 0,
                'timeWaiting' => date('U') - $this->userTime,
                'ratingGameWaitLimit' => $this->caller->ratingGameWaitLimit,
                'comments' => '<h6>Поиск игрока с указанным рейтингом</h6>'
            ]
        );
    }

    /**
     * Removes User from all queues
     * @param $User
     * @return void
     */
    public static function cleanUp($User): void
    {
        foreach (static::QUEUES as $eachQueue) {
            Cache::hdel($eachQueue, $User);
        }
    }

    protected function want4Players($User)
    {
        // todo отключил 4ю очередь, чтобы меньше ждали
        return false;

        if (isset($this->POST['players_count']) && ($this->POST['players_count'] == 4)) {
            return true;
        }

        if (isset($this->POST['players_count']) && ($this->POST['players_count'] == 2)) {
            self::cleanUp($User);
            return false;
        }

        if (Cache::hget(static::QUEUES["erudit.4{$this->lang}players_waiters"], $User)) {
            self::cleanUp($User);
            return true;
        }

        if (Cache::hget(static::QUEUES["erudit.2{$this->lang}players_waiters"], $User)) {
            return false;
        }


        if ((Cache::hlen(static::QUEUES["erudit.2{$this->lang}players_waiters"]) == 1) && (strpos($User, 'ot') !== false)) {
            return false;
        }

        return true;
    }

    protected function players4Waiting($User)
    {
        if ($cnt = Cache::hlen(static::QUEUES["erudit.4{$this->lang}players_waiters"])) {
            if (!Cache::hget(static::QUEUES["erudit.4{$this->lang}players_waiters"], $User)) {
                return true;
            } else {
                if ($cnt > 1) {
                    return true;
                }
            }
        }
        return false;
    }

    protected function timeToMake4Game()
    {
        $waitingPlayers = Cache::hgetall(static::QUEUES["erudit.4{$this->lang}players_waiters"]);
        $maxTimeWaiting = 0;
        foreach ($waitingPlayers as $player => $data) {
            $data = unserialize($data);
            if ((date('U') - $data['time']) > $maxTimeWaiting) {
                $maxTimeWaiting = date('U') - $data['time'];
            }
        }
        if ($maxTimeWaiting < $this->caller->gameWaitLimit) {
            return false;
        } else {
            return true;
        }
    }

    protected function tryNewGameSemaphore()
    {
        if ($this->semaphoreLocked) {
            Cache::set('semaphore_waiting', 1);

            return true;
        }

        if (($sem = Cache::incr('semaphore_waiting')) == 1) {
            $this->semaphoreLocked = true;

            return true;
        } else {
            if ($sem > 20) {
                $this->semaphoreLocked = true;
                Cache::set('semaphore_waiting', 1);

                return true;
            }
        }

        return false;
    }

    protected function makeGame($User, $queue)
    {
        Cache::setex(
            static::CURRENT_GAME_KEY . $this->caller->currentGame = Cache::incr(static::GAMES_COUNTER),
            $this->caller->cacheTimeout,
            false
        );

        $this->caller->gameStatus['desk'] = false;
        //Создали состояние доски

        $game_users = [];
        $this->caller->currentGameUsers = [];
        $waitingPlayers = Cache::hgetall(static::QUEUES["erudit.{$queue}{$this->lang}players_waiters"]);
        $prefs = Cache::get(self::PREFS_KEY . $User);

        if (!isset($waitingPlayers[$User])) {
            $options = isset($this->POST['ochki_num'])
                ? $this->POST
                : ($prefs ?: false);
        } else {
            $waitingPlayers[$User] = unserialize($waitingPlayers[$User]);

            $options = isset($waitingPlayers[$User]['options']['ochki_num'])
                ? $waitingPlayers[$User]['options']
                : ($prefs ?: false);

            unset($waitingPlayers[$User]);
        }

        // Прописываем текущему юзеру - добавление в игру,  номер игры, удаляем из очереди ждунов
        $game_users[] = ['userCookie' => $User, 'options' => $options];
        self::cleanUp($User);
        Cache::setex(static::GET_GAME_KEY . $User, $this->caller->cacheTimeout, $this->caller->currentGame);

        $this->caller->currentGameUsers[] = $User;
        $numUsers = 1;

        foreach ($waitingPlayers as $player => $data) {
            $prefs = Cache::get(static::PREFS_KEY . $player);
            $data = unserialize($data);

            //Прописываем юзерам - удаление из очереди и номер игры
            self::cleanUp($player);
            Cache::setex(
                static::GET_GAME_KEY . $player,
                $this->caller->cacheTimeout,
                $this->caller->currentGame
            );

            $options = isset($data['options']['ochki_num'])
                ? $data['options']
                : ($prefs ?: false);
            $game_users[] = ['userCookie' => $player, 'options' => $options];

            //Заполняем массив игроков
            $this->caller->currentGameUsers[] = $player;

            $numUsers++;
            if ($numUsers == ($queue == static::QUEUE_NUMS['invite'] ? 4 : $queue)) {
                break;
            }
        }

        $this->caller->gameStatus['lang'] = ($this->lang == 'EN' ? 'EN' : 'RU');
        //Прописали Язык игры
        $this->caller->gameStatus['lngClass'] = "\Lang\\" . ($this->lang == 'EN' ? 'Eng' : 'Ru');
        //Класс для работы с языком

        foreach ($game_users as $num => $user) {
            $this->caller->gameStatus['users'][$num] = [
                'ID' => $user['userCookie'],
                'common_id' => PlayerModel::getPlayerID($user['userCookie'], true),
                'status' => Game::START_GAME_STATUS,
                'isActive' => true,
                'score' => 0,
                'username' => 'Игрок' . ($num + 1),
                'avatarUrl' => false,
            ];
            //Прописали игроков в состояние игры

            if ($user['options'] !== false) {
                $this->caller->gameStatus['users'][$num]['wishOchkiNum'] = $user['options']['ochki_num'];
                $this->caller->gameStatus['users'][$num]['wishTurnTime'] = $user['options']['turn_time'];
                //Заполнили пожелания игроков к времени хода и очкам для выигрыша
            }

            $this->caller->gameStatus[$user['userCookie']] = $num;
            // Заполнили массив нормеров игроков
            $this->caller->updateUserStatus(Game::START_GAME_STATUS, $user['userCookie']);
            // Назначили статусы всем игрокам
        }

        // Сохраняем список игроков в игре
        Cache::setex(
            static::GAME_KEY . "{$this->caller->currentGame}_users",
            $this->caller->cacheTimeout,
            $this->caller->currentGameUsers
        );

        $res = $this->caller->gameStarted(true);

        $this->unlockSemaphore();

        return $res;
    }

    protected function unlockSemaphore()
    {
        Cache::set('semaphore_waiting', 0);
    }

    public function storePlayersToQueue($User, $count = 2)
    {
        if (!Cache::hget(static::QUEUES["erudit.{$count}{$this->lang}players_waiters"], $User)) {
            if (isset($this->POST['ochki_num'])) {
                $options = $this->POST;
            } else {
                $options = false;
            }

            Cache::hset(
                static::QUEUES["erudit.{$count}{$this->lang}players_waiters"],
                $User,
                [
                    'time' => date('U'),
                    'options' => $options
                ]
            );
        }

        return $this->caller->makeResponse(
            [
                'gameState' => Game::INIT_GAME_STATE,
                'gameSubState' => Cache::hlen(static::QUEUES["erudit.{$count}{$this->lang}players_waiters"]),
                'gameWaitLimit' => $this->caller->gameWaitLimit
            ]
        );
    }

    public function storeTo4Players($User)
    {
        if (!Cache::hget(static::QUEUES["erudit.4{$this->lang}players_waiters"], $User)) {
            if (isset($this->POST['ochki_num'])) {
                $options = $this->POST;
            } else {
                $options = false;
            }

            Cache::hset(
                static::QUEUES["erudit.4{$this->lang}players_waiters"],
                $User,
                [
                    'time' => date('U'),
                    'options' => $options
                ]
            );
        }

        return $this->caller->makeResponse(
            [
                'gameState' => Game::INIT_GAME_STATE,
                'gameSubState' => Cache::hlen(static::QUEUES["erudit.4{$this->lang}players_waiters"]),
                'gameWaitLimit' => $this->caller->gameWaitLimit
            ]
        );
    }

    protected function players2Waiting($User)
    {
        if ($cnt = Cache::hlen(static::QUEUES["erudit.2{$this->lang}players_waiters"])) {
            if (!Cache::hget(static::QUEUES["erudit.2{$this->lang}players_waiters"], $User)) {
                return true;
            } else {
                if ($cnt > 1) {
                    return true;
                }
            }
        }
        return false;
    }

    protected function storeTo2Players($User)
    {
        if (isset($this->POST['ochki_num'])) {
            $options = $this->POST;
        } else {
            $options = false;
        }
        if (!Cache::hget(static::QUEUES["erudit.2{$this->lang}players_waiters"], $User)) {
            Cache::hset(
                static::QUEUES["erudit.2{$this->lang}players_waiters"],
                $User,
                [
                    'time' => date('U'),
                    'options' => $options
                ]
            );
        }

        return $this->caller->makeResponse(
            [
                'gameState' => Game::INIT_GAME_STATE,
                'gameSubState' => Cache::hlen(static::QUEUES["erudit.2{$this->lang}players_waiters"]),
                'gameWaitLimit' => $this->caller->gameWaitLimit
            ]
        );
    }
}


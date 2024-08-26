<?php

class Queue
{
    const QUEUES = [
        'erudit.rating_waiters' => 'erudit.rating_waiters',
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

    const SEMAPHORE_KEY = 'semaphore_waiting';

    const MAX_INVITE_WAIT_TIME = 60;
    const PREFERENCES_TTL = 30 * 24 * 60 * 60;
    const PREFS_KEY = 'erudit.user_preference_';
    const CURRENT_GAME_KEY = 'erudit.current_game_';
    const GAMES_COUNTER = 'erudit.num_games';
    const GET_GAME_KEY = 'erudit.get_game_';
    const GAME_KEY = 'erudit.game_';
    const RATING_QUEUE = 'ratingQueue';

    protected $User;
    protected $userTime;

    protected bool $initGame = false;
    protected bool $userInInitStatus = false;
    const USER_STATUS_PREFIX = 'erudit.user_status_';

    protected Game $caller;
    protected array $POST;
    protected $lang;

    const LANGS = ['RU' => '', 'EN' => 'EN', '' => ''];

    public function __construct($User, Game $caller, array $POST)
    {
        $this->User = $User;
        $this->caller = $caller;
        $this->POST = $POST;

        $this->userInInitStatus = $this->checkPlayerInitStatus();

        $this->lang = self::LANGS[$this->POST['lang'] ?? ''];

        if (isset($this->POST['ochki_num'])) {
            Cache::setex(
                static::PREFS_KEY . $this->User,
                static::PREFERENCES_TTL,
                $this->POST
            );
            //В начале игры сохраняем предпочтения игрока для игры по приглашению
        }
    }

    protected function checkPlayerInitStatus(): bool
    {
        $initGame = ($this->POST['init_game']) || self::isUserInQueue($this->User);

        if ($initGame) {
            self::setPlayerInitStatus($this->User); // todo возможно не надо помещать в кеш

            return true;
        }

        if(Cache::get(static::USER_STATUS_PREFIX . $this->User)) {
            return true;
        }

        return false;
    }

    public static function setPlayerInitStatus($User): void
    {
        Cache::setex(static::USER_STATUS_PREFIX . $User, 60, Game::INIT_GAME_STATE);
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

    protected static function getRuClass(): string
    {
        return Ru::class;
    }

    protected static function getEngClass(): string
    {
        return Eng::class;
    }

    protected function chooseGame(): string
    {
        $chooseGameParams = [
            'gameState' => 'chooseGame',
            'gameSubState' => 'choosing',
            'players' => $this->caller->onlinePlayers(),
            'prefs' => Cache::get(static::PREFS_KEY . $this->User)
        ];

        return $this->caller
            ->makeResponse($chooseGameParams);
    }

    public function doSomethingWithThisStuff(string $lang = '')
    {
        $this->lang = self::LANGS[$lang];

        if (!$this->userInInitStatus) { // todo разобраться почему при подборе игры выкидывает в выбор параметров
            return $this->chooseGame();
        }

        if ($this->checkInviteQueue()) {
            if ($this->inviteQueueFull()) {
                if (Cache::waitLock(static::SEMAPHORE_KEY)) {
                    return $this->makeGame(static::QUEUE_NUMS['invite'], 4);
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

        if ($this->lang == '') {
            // Только для игры на Русском
            if ($ratingWanted = $this->waitRatingPlayer($this->User)) // Сразу помещает в спецочередь
            {
                if ($ratingPlayer = $this->findRatingPlayer($ratingWanted)) {
                    if (Cache::waitLock(static::SEMAPHORE_KEY)) {
                        return $this->makeRatingGame($ratingPlayer) ?: $this->stillWaitRatingPlayer();
                    }
                }

                if ($this->timeToWaitRatingPlayerOver($this->User)) {
                    return $this->storeToCommonQueue($this->User) ?: $this->stillWaitRatingPlayer();
                }

                return $this->stillWaitRatingPlayer();
            }

            $curPlayerRating = PlayerModel::getRatingByCookie($this->User);
            if ($curPlayerRating > 1900 && ($ratingPlayer = $this->findWaitingRaitingPlayer($curPlayerRating))) {
                if (Cache::waitLock(static::SEMAPHORE_KEY)) {
                    return $this->makeReverseRatingGame($ratingPlayer) ?: $this->storeTo2Players($this->User);
                }
            }
        }

        if ($this->players2Waiting($this->User)) {
            if (Cache::waitLock(static::SEMAPHORE_KEY)) {
                return $this->makeGame('2');
            }
        }

        return $this->storeTo2Players($this->User);
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

            if(self::addToQueue('erudit.rating_waiters', $User, $options, ['from_rating' => $this->POST['from_rating']])) {
                return $this->POST['from_rating'];
            }
        } elseif ($waiterData = Cache::hget(static::QUEUES["erudit.rating_waiters"], $User)) {
            $this->userTime = $waiterData['time'];

            return $waiterData['from_rating'];
        }

        return false;
    }

    protected function findRatingPlayer($ratingWanted)
    {
        if (($players2Waiting = Cache::hgetall(static::QUEUES["erudit.2{$this->lang}players_waiters"]))) {
            foreach ($players2Waiting as $player => $data) {
                $playerRating = PlayerModel::getRatingByCookie($player);
                if ($playerRating >= $ratingWanted) {
                    return [
                        'cookie' => $player,
                        'options' => @unserialize($data)['options'] ?? false,
                        'queue' => 2,
                        'rating' => $playerRating
                    ];
                }
            }
        }

        if (($playersRatingWaiting = Cache::hgetall(static::QUEUES["erudit.rating_waiters"]))) {
            foreach ($playersRatingWaiting as $player => $data) {
                if ($player != $this->User) {
                    $playerInfo = unserialize($data);
                    $playerRating = PlayerModel::getRatingByCookie($player);
                    if (
                        $playerRating >= $ratingWanted
                        &&
                        (PlayerModel::getRatingByCookie($this->User)) >= $playerInfo['from_rating']
                    ) {
                        return [
                            'cookie' => $player,
                            'options' => $data['options'] ?? false,
                            'queue' => self::RATING_QUEUE,
                            'rating' => $playerRating
                        ];
                    }
                }
            }
        }

        return false;
    }

    protected function findWaitingRaitingPlayer($curPlayerRating)
    {
        if (($playersRatingWaiting = Cache::hgetall(static::QUEUES["erudit.rating_waiters"]))) {
            foreach ($playersRatingWaiting as $player => $data) {
                if ($player != $this->User) {
                    $data = unserialize($data);
                    if ($curPlayerRating >= $data['from_rating']) {
                        return [
                            'cookie' => $player,
                            'options' => $data['options'] ?? false,
                            'queue' => static::RATING_QUEUE,
                            'rating' => PlayerModel::getRatingByCookie($player),
                        ];
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

        $players2Queue = Cache::hget(
            static::QUEUES["erudit.2{$this->lang}players_waiters"],
            $this->User
        );
        if ($players2Queue && ($players2Queue['options'] ?? false)) {
            return $players2Queue['options'];
        }

        return Cache::get(static::PREFS_KEY . $this->User)
            ?: ['num_players' => 2, 'ochki_num' => rand(200, 300), 'turn_time' => rand(60, 120)];
    }

    protected function makeReverseRatingGame(array $ratingPlayer)
    {
        $thisUserOptions = $this->gatherUserData();

        if (
            self::cleanUp($ratingPlayer['cookie'])
            //Удалили ожидающего рейтинг игрока из очереди рейтинга
            &&
            self::addToQueue("erudit.2{$this->lang}players_waiters", $ratingPlayer['cookie'], $ratingPlayer['options'])
            //Поместили ожидающего рейтинг игрока в очередь текущего игрока
            &&
            self::cleanUp($this->User)
            &&
            self::addToQueue("erudit.2{$this->lang}players_waiters", $this->User, $thisUserOptions)
        ) {
            return $this->makeGame('2', 2, $ratingPlayer['rating']);
        } else {
            return false;
        }
    }

    protected function makeRatingGame(array $ratingPlayer)
    {
        $waiterData = Cache::hget(static::QUEUES["erudit.rating_waiters"], $this->User);

        if ($ratingPlayer['queue'] == self::RATING_QUEUE) {
            $playerData = Cache::hget(static::QUEUES["erudit.rating_waiters"], $ratingPlayer['cookie']);

            if (!(
                self::cleanUp($ratingPlayer['cookie'])
                &&
                self::addToQueue("erudit.2{$this->lang}players_waiters", $ratingPlayer['cookie'], $playerData['options'])
                )) {
                return false;
            }
        }

        if(
        self::cleanUp($this->User)
        &&
        self::addToQueue(
            "erudit.2{$this->lang}players_waiters",
            $this->User,
            $waiterData['options'],
            ['time' => $waiterData['time']]
        )
        ){
            return $this->makeGame('2', 2, $ratingPlayer['rating']);
        } else {
            return false;
        }
    }

    protected function timeToWaitRatingPlayerOver($User)
    {
        $waiterData = Cache::hget(static::QUEUES["erudit.rating_waiters"], $User);

        if ((date('U') - $waiterData['time']) > $this->caller->ratingGameWaitLimit) {
            return true;
        }

        return false;
    }

    protected function storeToCommonQueue($User)
    {
        $waiterData = Cache::hget(static::QUEUES["erudit.rating_waiters"], $User) ?: [];
        if (self::cleanUp($User)) {
            return $this->storeTo2Players($User, $waiterData['options'] ?? []) ?: $this->chooseGame();
        }

        return false;
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
     * @return bool
     */
    public static function cleanUp($User): bool
    {
        foreach (static::QUEUES as $eachQueue) {
            if (Cache::hdel($eachQueue, $User, ['lock' => $User]) === false) {
                return false;
            }
        }

        return true;
    }

    protected function makeGame($queue, $maxNumUsers = 2, $wishRating = null)
    {
        $newGameId = Cache::incr(static::GAMES_COUNTER);

        if ($newGameId == 1) {
            $newGameId = GamesModel::getLastID() + 1;
            Cache::set(static::GAMES_COUNTER, $newGameId);
        }

        $this->caller->currentGame = $newGameId;
        Cache::setex(
            static::CURRENT_GAME_KEY . $this->caller->currentGame,
            $this->caller->cacheTimeout,
            false
        );

        $this->caller->gameStatus['desk'] = false;
        //Создали состояние доски

        $game_users = [];
        $this->caller->currentGameUsers = [];

        $waitingPlayers = Cache::hgetall(static::QUEUES["erudit.{$queue}{$this->lang}players_waiters"]);
        $prefs = Cache::get(static::PREFS_KEY . $this->User);

        if (!isset($waitingPlayers[$this->User])) {
            $options = isset($this->POST['ochki_num'])
                ? $this->POST
                : ($prefs ?: false);
        } else {
            $waitingPlayers[$this->User] = unserialize($waitingPlayers[$this->User]);

            $options = isset($waitingPlayers[$this->User]['options']['ochki_num'])
                ? $waitingPlayers[$this->User]['options']
                : ($prefs ?: false);

            unset($waitingPlayers[$this->User]);
            reset($waitingPlayers);
        }

        // Прописываем текущему юзеру - добавление в игру,  номер игры, удаляем из очереди ждунов
        $game_users[] = ['userCookie' => $this->User, 'options' => $options];

        self::cleanUp($this->User);
        Cache::setex(static::GET_GAME_KEY . $this->User, $this->caller->cacheTimeout, $this->caller->currentGame);

        $this->caller->currentGameUsers[] = $this->User;

        foreach ($waitingPlayers as $player => $data) {
            if($wishRating && PlayerModel::getRatingByCookie($player) != $wishRating) {
                continue;
            }

            $prefs = Cache::get(static::PREFS_KEY . $player);
            $data = unserialize($data);

            //Прописываем юзерам - удаление из очереди и номер игры
            if (!self::cleanUp($player)) {
                continue;
            }

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

            if (count($game_users) >= $maxNumUsers) {
                break;
            }
        }

        if (count($game_users) < 2) {
            // игра не собралась - отменяем, помещаем игрока обратно в очередь 2
            return $this->storeTo2Players($this->User, $game_users[0]['options'] ?? []) ?: $this->chooseGame();
        }

        $this->caller->gameStatus['lang'] = ($this->lang == 'EN' ? 'EN' : 'RU');
        //Прописали Язык игры
        $this->caller->gameStatus['lngClass'] = ($this->lang == 'EN' ? static::getEngClass() : static::getRuClass());
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

        return $res;
    }

    public function storePlayerToInviteQueue($User)
    {
        if (!Cache::hget(static::QUEUES["erudit.invite{$this->lang}players_waiters"], $User)) {
            if (isset($this->POST['ochki_num'])) {
                $options = $this->POST;
            } else {
                $options = false;
            }

            self::addToQueue("erudit.invite{$this->lang}players_waiters", $User, $options);
        }

        return $this->caller->makeResponse(
            [
                'gameState' => Game::INIT_GAME_STATE,
                'gameSubState' => Cache::hlen(static::QUEUES["erudit.invite{$this->lang}players_waiters"]),
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

    protected function storeTo2Players($User, $options = [])
    {
        if (empty($options)) {
            if (isset($this->POST['ochki_num'])) {
                $options = $this->POST;
            } else {
                $options = false;
            }
        }

        if (!Cache::hget(static::QUEUES["erudit.2{$this->lang}players_waiters"], $User)) {
            if(!self::addToQueue("erudit.2{$this->lang}players_waiters", $User, $options)) {
                return false;
            }
        }

        return $this->caller->makeResponse(
            [
                'gameState' => Game::INIT_GAME_STATE,
                'gameSubState' => Cache::hlen(static::QUEUES["erudit.2{$this->lang}players_waiters"]),
                'gameWaitLimit' => $this->caller->gameWaitLimit
            ]
        );
    }

    protected function addToQueue(string $queue, string $user, $options, array $params = []): bool
    {
        return (bool)Cache::hset(
            static::QUEUES[$queue],
            $user,
            array_merge(
                [
                    'time' => date('U'),
                    'options' => $options
                ],
                $params
            ),
            ['lock' => $user]
        );
    }
}


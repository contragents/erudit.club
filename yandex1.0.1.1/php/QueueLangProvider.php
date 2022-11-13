<?php

namespace Erudit;

//ini_set("display_errors", 1); error_reporting(E_ALL);

class Queue
{
    const QUEUES = [
        'erudit.rating_waiters',
        'erudit.ratingEN_waiters',
        'erudit.2players_waiters',
        'erudit.2ENplayers_waiters',
        'erudit.4players_waiters',
        'erudit.4ENplayers_waiters',
        'erudit.inviteplayers_waiters',
        'erudit.inviteENplayers_waiters'
    ];

    const QUEUE_NUMS = [
        'invite' => 'invite',
    ];

    const MAX_INVITE_WAIT_TIME = 20;

    private $User;
    private $userTime;
    private $userWishPlayers;
    private $userWishScore;
    private $userWishTurnTime;

    public \Erudit\Game $caller;
    private $p;
    private array $POST;
    private $lang;

    public function __construct($User, \Erudit\Game $caller, \Dadata\Cache $p, array $POST)
    {
        $this->User = $User;
        $this->caller = $caller;
        $this->p = $p;
        $this->POST = $POST;
        //$this->lang = '';

        $this->lang = $this->POST['lang'] ?? '';

        if (isset($this->POST['ochki_num'])) {
            $this->p->redis->setex(
                'erudit.user_preference_' . $this->User,
                $caller->cacheTimeout,
                serialize($this->POST)
            );
            //В начале игры сохраняем предпочтения игрока для игры по приглашению
        }
    }

    public function doSomethingWithThisStuff($lang = '')
    {
        if ($lang != '') {
            $this->lang = $lang;
        }

        if ($this->checkInviteQueue()) {
            if ($this->inviteQueueFull()) {
                if ($this->tryNewGameSemaphore()) {
                    return $this->makeGame($this->User, 'invite');
                }
            }

            return $this->caller
                ->makeResponse(
                    [
                        'gameState' => 'initGame',
                        'gameSubState' => $this->p->redis->hlen("erudit.invite{$this->lang}players_waiters"),
                        'gameWaitLimit' => $this->caller->gameWaitLimit
                    ]
                );
        }
        if ($this->lang === '') {//Только для игры на Русском
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

                return $this->stillWaitRatingPlayer($this->User);
            }

            if (strpos($this->User, 'ot') === false) {
                if (($curPlayerRating = $this->caller->getRatings($this->User)[0]['rating']) > 1900) {
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
                    } else {
                        return $this->storeTo4Players($this->User);
                    }
                } else {
                    return $this->storeTo4Players($this->User);
                }
            }
        } else {
            if ($this->players2Waiting($this->User)) {
                if ($this->tryNewGameSemaphore()) {
                    return $this->makeGame($this->User, 2);
                } else {
                    return $this->storeTo2Players($this->User);
                }
            } else {
                return $this->storeTo2Players($this->User);
            }
        }
    }

    private function inviteQueueFull()
    {
        if ($this->p->redis->hlen("erudit.invite{$this->lang}players_waiters") < 2) {
            return false;
        }

        // Проверяем очередь ждущих реванша на наличие просроченных заявок от игроков
        $waitingPlayers = $this->p->redis->hgetall("erudit.invite{$this->lang}players_waiters");
        foreach ($waitingPlayers as $player => $playerInfo) {
            if ($playerInfo && $player != $this->User) {
                $player_data = @unserialize($playerInfo);
                if (isset($player_data['time']) && (date('U') - $player_data['time']) > self::MAX_INVITE_WAIT_TIME) {
                    $this->cleanUp($player, self::QUEUE_NUMS['invite']);
                }
            }
        }

        // Если очередь все еще длиной не менее 2х игроков - возвращаем тру
        if ($this->p->redis->hlen("erudit.invite{$this->lang}players_waiters") >= 2) {
            return true;
        }

        return false;

        // Старая версия - проверяет очереди на всех языках вместо текущего языка
        /*
        if ($this->p->redis->hlen("erudit.inviteplayers_waiters") >= 2) {
            $this->lang = '';

            return true;
        }

        if ($this->p->redis->hlen("erudit.inviteENplayers_waiters") >= 2) {
            $this->lang = 'EN';

            return true;
        }

        return false;
        */
    }

    private function checkInviteQueue()
    {
        $playerInfo = $this->p->redis->hget("erudit.invite{$this->lang}players_waiters", $this->User);

        if ($playerInfo) {
            $player_data = @unserialize($playerInfo);
            if (isset($player_data['time']) && (date('U') - $player_data['time']) > self::MAX_INVITE_WAIT_TIME) {
                $this->cleanUp($this->User, self::QUEUE_NUMS['invite']);

                return false;
            }
            return true;
        }

        return false;
    }

    private function waitRatingPlayer($User)
    {
        if (strpos($User, 'ot') !== false) {
            return false;
        }

        if (isset($this->POST['from_rating']) && ($this->POST['from_rating'] == 0)) {
            return false;
        }

        if (isset($this->POST['from_rating']) && ($this->POST['from_rating'] > 0)) {
            if (isset($this->POST['ochki_num'])) {
                $options = $this->POST;
            } else {
                $options = false;
            }
            $this->userTime = date('U');
            $this->p->redis->hset(
                "erudit.rating{$this->lang}_waiters",
                $User,
                serialize(
                    [
                        'time' => $this->userTime,
                        'rating' => $this->POST['from_rating'],
                        'options' => $options
                    ]
                )
            );

            return $this->POST['from_rating'];
        }

        if ($waiter = $this->p->redis->hget("erudit.rating{$this->lang}_waiters", $User)) {
            $waiterData = unserialize($waiter);
            $this->userTime = $waiterData['time'];

            return $waiterData['rating'];
        }

        return false;
    }

    private function findRatingPlayer($ratingWanted)
    {
        if (($players2Waiting = $this->p->redis->hgetall("erudit.2{$this->lang}players_waiters"))) {
            foreach ($players2Waiting as $player => $data) {
                if (!strpos($player, 'ot') && $this->caller->getRatings(
                        ['cookie' => $player]
                    )['rating'] >= $ratingWanted) {
                    return [$player => 2];
                }
            }
        }


        if (($players4Waiting = $this->p->redis->hgetall("erudit.4{$this->lang}players_waiters"))) {
            foreach ($players4Waiting as $player => $data) {
                if (!strpos($player, 'ot') && $this->caller->getRatings(
                        ['cookie' => $player]
                    )['rating'] >= $ratingWanted) {
                    return [$player => 4];
                }
            }
        }

        if (($playersRatingWaiting = $this->p->redis->hgetall("erudit.rating{$this->lang}_waiters"))) {
            foreach ($playersRatingWaiting as $player => $data) {
                if ($player != $this->User) {
                    if ($this->caller->getRatings(['cookie' => $player])['rating'] >= $ratingWanted) {
                        return [$player => 'ratingQueue'];
                    }
                }
            }
        }


        return false;
    }

    private function findWaitingRaitingPlayer($curPlayerRating)
    {
        if (($playersRatingWaiting = $this->p->redis->hgetall("erudit.rating{$this->lang}_waiters"))) {
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

    private function gatherUserData()
    {
        if (isset($this->POST['ochki_num'])) {
            return $this->POST;
        }

        if ($players2Queue = $this->p->redis->hget(
            "erudit.2{$this->lang}players_waiters",
            $this->User
        )
        ) {
            return unserialize($players2Queue)['options'];
        }

        if ($players4Queue = $this->p->redis->hget(
            "erudit.4{$this->lang}players_waiters",
            $this->User
        )
        ) {
            return unserialize($players4Queue)['options'];
        }

        return ['num_players' => 4, 'ochki_num' => rand(200, 300), 'turn_time' => rand(60, 120)];
    }

    private function makeReverseRatingGame(array $ratingPlayer)
    {
        $thisUserOptions = $this->gatherUserData();

        $this->p->redis->hset(
            "erudit.{$thisUserOptions['players_count']}{$this->lang}players_waiters",
            $ratingPlayer['cookie'],
            serialize(
                [
                    'time' => date('U'),
                    'options' => $ratingPlayer['options']
                ]
            )
        );
        //Поместили ожидающего рейтинг игрока в очередь текущего игрока
        $this->p->redis->hdel("erudit.rating{$this->lang}_waiters", $ratingPlayer['cookie']);
        //Удалили ожидающего рейтинг игрока из очереди рейтинга

        $this->p->redis->hset(
            "erudit.{$thisUserOptions['players_count']}{$this->lang}players_waiters",
            $this->User,
            serialize(
                [
                    'time' => date('U'),
                    'options' => $thisUserOptions
                ]
            )
        );
        //Поместили текущего игрока в очередь текущего игрока))

        return $this->{"makeGame"}($this->User, $thisUserOptions['players_count']);
        //Начали игру
    }

    private function makeRatingGame($User, array $ratingPlayer)
    {
        $waiter = $this->p->redis->hget("erudit.rating{$this->lang}_waiters", $User);
        $waiterData = unserialize($waiter);

        foreach ($ratingPlayer as $player => $numPlayersQueue) {
            if ($numPlayersQueue == 'ratingQueue') {
                $numPlayersQueue = 4;
                $playerData = unserialize($this->p->redis->hget("erudit.rating{$this->lang}_waiters", $player));
                $this->p->redis->hset(
                    "erudit.{$numPlayersQueue}{$this->lang}players_waiters",
                    $player,
                    serialize(
                        [
                            'time' => $playerData['time'],
                            'options' => $playerData['options']
                        ]
                    )
                );
                $this->p->redis->hdel("erudit.rating{$this->lang}_waiters", $player);
            }

            $this->p->redis->hset(
                "erudit.{$numPlayersQueue}{$this->lang}players_waiters",
                $User,
                serialize(
                    [
                        'time' => $waiterData['time'],
                        'options' => $waiterData['options']
                    ]
                )
            );
            break;
        }

        $this->p->redis->hdel("erudit.rating{$this->lang}_waiters", $User);
        return $this->{"makeGame"}($User, $numPlayersQueue);
    }

    private function timeToWaitRatingPlayerOver($User)
    {
        $waiter = $this->p->redis->hget("erudit.rating{$this->lang}_waiters", $User);
        $waiterData = unserialize($waiter);
        if ((date('U') - $waiterData['time']) > $this->caller->ratingGameWaitLimit) {
            return true;
        }

        return false;
    }

    private function storeToCommonQueue($User)
    {
        $waiter = $this->p->redis->hget("erudit.rating{$this->lang}_waiters", $User);
        $this->p->redis->hdel("erudit.rating{$this->lang}_waiters", $User);
        $waiterData = unserialize($waiter);

        if (isset($waiterData['options']) && isset($waiterData['options']['players_count'])) {
            $this->p->redis->hset(
                "erudit.{$waiterData['options']['players_count']}{$this->lang}players_waiters",
                $User,
                serialize(
                    [
                        'time' => $waiterData['time'],
                        'options' => $waiterData['options']
                    ]
                )
            );
            return $this->
            caller->
            makeResponse(
                [
                    'gameState' => 'initGame',
                    'gameSubState' => $this->p->redis->hlen(
                        "erudit.{$waiterData['options']['players_count']}{$this->lang}players_waiters"
                    ),
                    'gameWaitLimit' => $this->caller->gameWaitLimit
                ]
            );
        }

        $this->p->redis->hset(
            "erudit.2{$this->lang}players_waiters",
            $User,
            serialize(
                [
                    'time' => date('U'),
                    'options' => false
                ]
            )
        );
        return $this->
        caller->
        makeResponse(
            [
                'gameState' => 'initGame',
                'gameSubState' => $this->p->redis->hlen("erudit.2{$this->lang}players_waiters"),
                'gameWaitLimit' => $this->caller->gameWaitLimit
            ]
        );
    }

    private function stillWaitRatingPlayer($User)
    {
        return $this->caller->makeResponse(
            [
                'gameState' => 'initRatingGame',
                'gameSubState' => 0,
                'timeWaiting' => date('U') - $this->userTime,
                'ratingGameWaitLimit' => $this->caller->ratingGameWaitLimit,
                'comments' => '<h6>Ожидаем игрока с указанным рейтингом</h6>'
            ]
        );
    }

    /**
     * Removes User from queue
     * @param $User
     * @param $queue
     * @return bool|int
     */
    private function cleanUp($User, $queue)
    {
        if (isset(self::QUEUE_NUMS[$queue])) {
            $searchQueue = $queue . $this->lang;

            foreach (self::QUEUES as $eachQueue) {
                if (strpos($eachQueue, $searchQueue) !== false) {
                    return $this->p->redis->hdel($eachQueue, $User);
                }
            }
        }

        return $this->p->redis->hdel('erudit.' . $queue . $this->lang . 'players_waiters', $User);
    }

    private function want4Players($User)
    {
        if (isset($this->POST['players_count']) && ($this->POST['players_count'] == 4)) {
            return true;
        }

        if (isset($this->POST['players_count']) && ($this->POST['players_count'] == 2)) {
            $this->cleanUp($User, 4);
            return false;
        }

        if ($this->p->redis->hget("erudit.4{$this->lang}players_waiters", $User)) {
            $this->cleanUp($User, 2);
            return true;
        }

        if ($this->p->redis->hget("erudit.2{$this->lang}players_waiters", $User)) {
            return false;
        }


        if (($this->p->redis->hlen("erudit.2{$this->lang}players_waiters") == 1) && (strpos($User, 'ot') !== false)) {
            return false;
        }

        return true;
    }

    private function players4Waiting($User)
    {
        if ($cnt = $this->p->redis->hlen("erudit.4{$this->lang}players_waiters")) {
            if (!$this->p->redis->hget("erudit.4{$this->lang}players_waiters", $User)) {
                return true;
            } else {
                if ($cnt > 1) {
                    return true;
                }
            }
        }
        return false;
    }

    private function timeToMake4Game()
    {
        $waitingPlayers = $this->p->redis->hgetall("erudit.4{$this->lang}players_waiters");
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

    private function tryNewGameSemaphore()
    {
        if (($sem = $this->p->redis->incr('semaphore_waiting')) == 1) {
            return true;
        } else {
            if ($sem > 20) {
                $this->p->redis->set('semaphore_waiting', 1);
                return true;
            }
        }

        return false;
    }

    private function makeGame($User, $numPlayers)
    {
        $this->p->redis->setex(
            'erudit.current_game_' . $this->caller->currentGame = $this->p->redis->incr('erudit.num_games'),
            $this->caller->cacheTimeout,
            false
        );

        $this->caller->gameStatus['desk'] = false;
        //Создали состояние доски

        $this->p->redis->setex('erudit.get_game_' . $User, $this->caller->cacheTimeout, $this->caller->currentGame);
        //Прописываем юзеру номер игры

        $game_users = [];
        $this->caller->currentGameUsers = [];
        $waitingPlayers = $this->p->redis->hgetall("erudit.{$numPlayers}{$this->lang}players_waiters");

        $prefs = $this->p->redis->get('erudit.user_preference_' . $User);
        if (!isset($waitingPlayers[$User])) {
            $options = isset($this->POST['ochki_num'])
                ? $this->POST
                : ($prefs
                    ? unserialize($prefs)
                    : false
                );
            $game_users[] = ['userCookie' => $User, 'options' => $options];
        } else {
            $waitingPlayers[$User] = unserialize($waitingPlayers[$User]);
            $options = isset($waitingPlayers[$User]['options']['ochki_num'])
                ? $waitingPlayers[$User]['options']
                : ($prefs
                    ? unserialize($prefs)
                    : false
                );
            $game_users[] = ['userCookie' => $User, 'options' => $options];
            unset($waitingPlayers[$User]);
            $this->p->redis->hdel("erudit.{$numPlayers}{$this->lang}players_waiters", $User);
            $this->p->redis->setex('erudit.get_game_' . $User, $this->caller->cacheTimeout, $this->caller->currentGame);
            //Прописываем юзеру номер игры
        }

        $this->caller->currentGameUsers[] = $User;
        $numUsers = 1;

        foreach ($waitingPlayers as $player => $data) {
            $prefs = $this->p->redis->get('erudit.user_preference_' . $player);
            $data = unserialize($data);
            $this->p->redis->hdel("erudit.{$numPlayers}{$this->lang}players_waiters", $player);
            $this->p->redis->setex(
                'erudit.get_game_' . $player,
                $this->caller->cacheTimeout,
                $this->caller->currentGame
            );
            //Прописываем юзерам номер игры
            $options = isset($data['options']['ochki_num'])
                ? $data['options']
                : ($prefs
                    ? unserialize($prefs)
                    : false
                );
            $game_users[] = ['userCookie' => $player, 'options' => $options];
            //Заполняем массив игроков
            $this->caller->currentGameUsers[] = $player;
            //Отдельный массив игроков, используется и хранится в редисе
            if (($k = ++$numUsers) == 4) {
                break;
            }
            //$numPlayers - использовали ранее, теперь до 4х игроков, чтобы все влезли
        }

        $this->caller->gameStatus['lang'] = ($this->lang == 'EN' ? 'EN' : 'RU');
        //Прописали Язык игры
        $this->caller->gameStatus['lngClass'] = "\Lang\\" . ($this->lang == 'EN' ? 'Eng' : 'Ru');
        //Класс для работы с языком

        foreach ($game_users as $num => $user) {
            $this->caller->gameStatus['users'][$num] = [
                'ID' => $user['userCookie'],
                'status' => 'startGame',
                'isActive' => true,
                'score' => 0,
                'username' => 'Игрок' . ($num + 1),
                'avatarUrl' => false,
            ];
            if ($user['options'] !== false) {
                $this->caller->gameStatus['users'][$num]['wishOchkiNum'] = $user['options']['ochki_num'];
                $this->caller->gameStatus['users'][$num]['wishTurnTime'] = $user['options']['turn_time'];
                //Заполнили пожелания игроков к времени хода и очкам для выигрыша
            }


            //Прописали игроков в состояние игры
            $this->caller->gameStatus[$user['userCookie']] = $num;
            //и заполнили массив нормеров игроков
            $this->caller->updateUserStatus('startGame', $user['userCookie']);
            //Назначили статусы всем игрокам
        }

        //Сохраняем список игроков в игре
        $this->p->redis->setex(
            "erudit.game_{$this->caller->currentGame}_users",
            $this->caller->cacheTimeout,
            serialize($this->caller->currentGameUsers)
        );

        $this->cleanUp($User, $numPlayers);
        $this->unlockSemaphore();
        return $this->caller->gameStarted(true);
    }

    private function unlockSemaphore()
    {
        $this->p->redis->set('semaphore_waiting', 0);
    }

    public function storePlayersToQueue($User, $count = 2)
    {
        if (!$this->p->redis->hget("erudit.{$count}{$this->lang}players_waiters", $User)) {
            if (isset($this->POST['ochki_num'])) {
                $options = $this->POST;
            } else {
                $options = false;
            }

            $this->p->redis->hset(
                "erudit.{$count}{$this->lang}players_waiters",
                $User,
                serialize(
                    [
                        'time' => date('U'),
                        'options' => $options
                    ]
                )
            );
        }

        return $this->caller->makeResponse(
            [
                'gameState' => 'initGame',
                'gameSubState' => $this->p->redis->hlen("erudit.{$count}{$this->lang}players_waiters"),
                'gameWaitLimit' => $this->caller->gameWaitLimit
            ]
        );
    }

    public function storeTo4Players($User)
    {
        if (!$this->p->redis->hget("erudit.4{$this->lang}players_waiters", $User)) {
            if (isset($this->POST['ochki_num'])) {
                $options = $this->POST;
            } else {
                $options = false;
            }

            $this->p->redis->hset(
                "erudit.4{$this->lang}players_waiters",
                $User,
                serialize(
                    [
                        'time' => date('U'),
                        'options' => $options
                    ]
                )
            );
        }

        return $this->caller->makeResponse(
            [
                'gameState' => 'initGame',
                'gameSubState' => $this->p->redis->hlen("erudit.4{$this->lang}players_waiters"),
                'gameWaitLimit' => $this->caller->gameWaitLimit
            ]
        );
    }

    private function players2Waiting($User)
    {
        if ($cnt = $this->p->redis->hlen("erudit.2{$this->lang}players_waiters")) {
            if (!$this->p->redis->hget("erudit.2{$this->lang}players_waiters", $User)) {
                return true;
            } else {
                if ($cnt > 1) {
                    return true;
                }
            }
        }
        return false;
    }

    private function storeTo2Players($User)
    {
        //print "storeto 2!!!";
        if (isset($this->POST['ochki_num'])) {
            $options = $this->POST;
        } else {
            $options = false;
        }
        if (!$this->p->redis->hget("erudit.2{$this->lang}players_waiters", $User)) {
            $this->p->redis->hset(
                "erudit.2{$this->lang}players_waiters",
                $User,
                serialize(
                    [
                        'time' => date('U'),
                        'options' => $options
                    ]
                )
            );
        }

        return $this->caller->makeResponse(
            [
                'gameState' => 'initGame',
                'gameSubState' => $this->p->redis->hlen("erudit.2{$this->lang}players_waiters"),
                'gameWaitLimit' => $this->caller->gameWaitLimit
            ]
        );
    }
}


<?php

use Dadata\Hints;
use Dadata\Players;
use Dadata\Prizes;
use Dadata\Stats;
use ViewHelper as VH;

class Game
{
    const GAME_STATUS_KEY = 'erudit.game_status_';
    const GAME_USER_KEY = 'erudit.user_';
    const CURRENT_GAME_KEY = 'erudit.current_game_';
    const NEW_PLAYER = 'new_player';

    public const SUDOKU = 'sudoku';
    public const ERUDIT = 'erudit';
    public const GOMOKU = 'gomoku';
    public const SCRABBLE = 'scrabble';

    public const GAME_LANG = [T::EN_LANG => self::SCRABBLE, T::RU_LANG => self::ERUDIT];
    const MIN_TOP_RATING = 1700;
    const SUDOKU_BALANCE = 'SUDOKU_BALANCE';
    const CHOOSE_GAME_STATUS = 'chooseGame';

    public static string $gameName = self::SCRABBLE;
    protected static array $playersInGames = [];
    public static array $players = []; // онлайн игроки

    public static ?int $commonID = null;

    const BOT_TPL = 'botV3#';

    protected $Queue = Queue::class;
    const GAMES_KEY = 'erudit.games_';
    public static $configStatic;
    public $serverName;
    public $config;
    public $User; // user cookie
    public $commonId; // user verified commonId
    public $currentGame;
    public $currentGameUsers = false;
    protected $gamePlayersWaiting = false; // Количество игроков, ожидающих начала игры
    public $gameWaitLimit; // Макс время ожидания начала игры
    public $ratingGameWaitLimit;// Время ожидания игрока с выбранным рейтингом
    public $cacheTimeout;
    public $ratingsCacheTimeout;
    protected $turnDeltaTime; // Разрешенное превышение длительности хода
    protected $activityTimeout = 30; // засунуть в конфиг
    protected $turnTime;
    protected $winScore;
    protected $chisloFishek;
    protected $numUser = false;
    protected $statusComments = [
        self::START_GAME_STATUS => 1,
        self::PRE_MY_TURN_STATUS => 1,
        self::MY_TURN_STATUS => 1,
        self::OTHER_TURN_STATUS => 1,
        'desync' => 1,
        self::INIT_GAME_STATE => 1
    ];
    public $gameStatus = [];/*[ 'desk' => [],
                            'users' =>[
                                0 => [
                                    'ID',
                                    'status',
                                    'lastActiveTime'
                                    'isActive' => TRUE,
                                    'score',
                                    'fishki' => [],
                                    'userName',
                                    'avatarUrl',
                                    'lostTurns' => 0
                                ]
                            ],
                            'lang'=> 'RU'|'EN'
                            'activeUser' => 0,
                            'gameBeginTime',
                            'turnBeginTime',
                            'turnTime' => 120,
                            'turnNumber',
                            'firstTurnUser',
                            'bankFishki' => [],
                            'wordsAccepted' => [],
                            'winScore' => 200,
                            'results' => [],
                            'gameLog' => []
                            ];*/

    const CHECK_STATUS_RESULTS_KEY = 'erudit.game_results_';
    const CHECK_STATUS_RESULTS_KEY_TTL = 24 * 60 * 60;

    const INIT_GAME_STATE = 'initGame';
    const INIT_RATING_GAME_STATE = 'initRatingGame';
    const INIT_STATES = [self::INIT_GAME_STATE, self::INIT_RATING_GAME_STATE];

    const BOT_ERRORS_KEY = 'erudit_bot_errors';
    const LOG_BOT_ERRORS_KEY = 'erudit_bot_log_errors';

    const BAD_COMBINATIONS_HSET = 'bad_combinations';

    const ERROR_STATUS = 'error';

    const GAMES_ENDED_KEY = 'erudit.games_ended';
    const STATS_FAILED = 'erudit.games_statistics_failed';
    const NUM_RATING_PLAYERS_KEY = 'erudit.num_rating_players';
    const NUM_COINS_PLAYERS_KEY = 'erudit.num_coins_players';

    const OCHKI_VARIANTS = [200 => 0, 300 => 0];
    const TIME_VARIANTS = [60 => 0, 90 => 0, 120 => 0];
    const ADD_TO_CHAT_STATE = 'addToChat';
    const WORD_QUERY_STATE = 'wordQuery';
    const IN_GAME_STATUSES = [
        self::MY_TURN_STATUS,
        self::OTHER_TURN_STATUS,
        self::PRE_MY_TURN_STATUS,
        self::START_GAME_STATUS,
    ];

    const MY_TURN_STATUS = 'myTurn';
    const OTHER_TURN_STATUS = 'otherTurn';
    const PRE_MY_TURN_STATUS = 'preMyTurn';
    const START_GAME_STATUS = 'startGame';
    const GAME_RESULTS_STATE = 'gameResults';

    protected $dir = __DIR__;

    public function __construct()
    {
        $this->config = include(__DIR__ . "/../../configs/conf.php");
        self::$configStatic = $this->config;
        $this->turnTime = $this->config['turnTime'];
        $this->winScore = $this->config['winScore'];
        $this->gameWaitLimit = $this->config['gameWaitLimit'];
        $this->ratingGameWaitLimit = $this->config['ratingGameWaitLimit'];
        $this->cacheTimeout = $this->config['cacheTimeout'];
        $this->ratingsCacheTimeout = $this->config['ratingsCacheTimeout'];
        $this->turnDeltaTime = $this->config['turnDeltaTime'];
        $this->activityTimeout = $this->config['activityTimeout'];
        $this->chisloFishek = $this->config['chisloFishek'];

        $this->User = $this->validateCookie($_COOKIE[CookieErudit::COOKIE_NAME]);

        $this->commonId = Tg::$commonId // авторизован через Телеграм или...
            ?? (Yandex::$commonId // авторизован через Яндекс или...
                ?? PlayerModel::getPlayerID($this->User, true));
        self::$commonID = $this->commonId;

        // Если не удалось дождаться лока по текущему игроку, то посылаем ошибку и выходим
        if (!Cache::waitLock($this->User, true)) {
            BadRequest::sendBadRequest(
                ['err_msg' => 'lock error'],
                $this->isBot()
            );
        }

        // todo CLUB-471 как ускорить, как проверять 1 раз
        $refererParams = BaseController::parseReferer();
        if (isset($refererParams['friend'])) {
            RefModel::register(
                $refererParams['friend'],
                $this->commonId,
                PlayerModel::getPlayerName(['ID' => $this->User, 'common_id' => $this->commonId])
            );
        }

        $this->currentGame = Cache::get($this->Queue::GET_GAME_KEY . $this->User);

        if (!$this->currentGame) {
            $this->currentGame = false;

            return $this;
        } else {
            $this->currentGameUsers = Cache::get($this->Queue::GET_GAME_KEY . "{$this->currentGame}_users");

            if (!Cache::waitLock($this->currentGame)) {
                //Вышли с Десинком, если не смогли получить Лок
                return $this->desync();
            }

            $this->gameStatus = Cache::get(static::GAME_STATUS_KEY . $this->currentGame);
            //Забрали статус игры из кэша

            try {
                if (!isset($this->gameStatus[$this->User])) {
                    print $this->newGame();

                    exit;
                }

                $this->numUser = $this->gameStatus[$this->User];
                //Номер пользователя по порядку

                if (isset($_GET['page_hidden']) && $_GET['page_hidden'] == 'true') {
                    if (
                        isset($_GET['queryNumber']) && $_GET['queryNumber'] > 1
                        && $_GET['queryNumber'] < ($this->gameStatus['users'][$this->numUser]['last_request_num'] ?? 0)
                    ) {
                        throw new BadRequest('Returned from page_hidden state');
                    }
                }
            } catch (BadRequest $e) {
                BadRequest::sendBadRequest(
                    [
                        'err_msg' => $e->getMessage(),
                        'err_file' => $e->getFile(),
                        'err_line' => $e->getLine(),
                        'err_context' => $e->getTrace(),
                    ],
                    $this->isBot()
                );
            }

            if (isset($_GET['queryNumber']) && $_GET['queryNumber'] > 5 && $_GET['queryNumber'] < ($this->gameStatus['users'][$this->numUser]['last_request_num'] ?? 0)) {
                // todo  при возврате десинка в игре проблемы с получением фишек. обычно при перезагрузке страницы
                BadRequest::sendBadRequest(
                    [
                        'message' => T::S('Server sync lost'),
                    ],
                    $this->isBot()
                );
            } else {
                $this->gameStatus['users'][$this->numUser]['last_request_num']
                    =
                    ($_GET['queryNumber'] ?? $this->gameStatus['users'][$this->numUser]['last_request_num']);
            }

            if (!(isset($_GET['page_hidden']) && $_GET['page_hidden'] == 'true')) {
                $this->gameStatus['users'][$this->numUser]['lastActiveTime'] = date('U');
                $this->gameStatus['users'][$this->numUser]['inactiveTurn'] = 1000;
                //Обновили время активности, если это не закрытие вкладки
            }
        }
    }

    protected function destruct()
    {
        if ($this->currentGame) {
            if (isset($this->gameStatus['results']['winner']) && !isset($this->gameStatus['isGameEndedSaved'])) {
                //Сохраняем результаты игры в список завершенных
                $this->gameStatus['isGameEndedSaved'] = true;
            }

            $this->gameStatus['users'][$this->numUser]['last_request_num'] = $_GET['queryNumber'] ?? 1000;

            Cache::setex(
                static::GAME_STATUS_KEY . $this->currentGame,
                $this->cacheTimeout,
                $this->gameStatus
            );
        }

        Cache::setex(static::GAME_USER_KEY . $this->User . '_last_activity', $this->cacheTimeout, date('U'));
    }

    public function onlineCoinPlayers()
    {
        if (empty(self::$players)) {
            $this->getPlayers();
        }

        $coinPlayers = array_combine(MonetizationService::BIDS, array_fill(0, count(MonetizationService::BIDS), 0));
        $thisPlayerBalance = BalanceModel::getBalance($this->commonId);

        // Чистим массив ставок от ставок больше баланса игрока
        foreach ($coinPlayers as $bid => $num) {
            if ($thisPlayerBalance < $bid) {
                unset($coinPlayers[$bid]);
            }

            // ...а также от мелких ставок
            if ($bid < ($thisPlayerBalance / 20)) {
                unset($coinPlayers[$bid]);
            }
        }

        foreach (self::$players as $num => $player) {
            $currentPlayerBalance = BalanceModel::getBalance($player['common_id']);

            // Отмечаем в массиве игроков на монеты число игроков с таким количеством монет
            foreach ($coinPlayers as $bid => $num) {
                if ($currentPlayerBalance >= $bid) {
                    $coinPlayers[$bid]++;
                }
            }
        }

        $coinPlayers['thisUserBalance'] = $thisPlayerBalance;

        Cache::setex(
            static::NUM_COINS_PLAYERS_KEY,
            $this->ratingsCacheTimeout,
            $coinPlayers
        );

        return $coinPlayers;
    }

    protected function getPlayers()
    {
        $lastGame = Cache::get($this->Queue::GAMES_COUNTER);

        for ($i = $lastGame; $i > ($lastGame - 50); $i--) {
            if ($game = Cache::get(static::GAME_STATUS_KEY . $i)) {
                if (!isset($game['results'])) {
                    foreach ($game['users'] as $num => $user) {
                        if (!isset($user['ID'])) {
                            continue;
                        }

                        if (strstr($user['ID'], self::BOT_TPL) === false) {
                            self::$players[$user['ID']] = [
                                'cookie' => $user['ID'],
                                'userID' => (isset($user['userID']) ? $user['userID'] : false),
                                'common_id' => $user['common_id'],
                            ];
                        }
                    }
                }
            }
        }
    }

    public function onlinePlayers()
    {
        if (!($rangedOnlinePlayers = Cache::get(
                static::NUM_RATING_PLAYERS_KEY
            )) || self::$gameName === self::SCRABBLE) {
            if (empty(self::$players)) {
                $this->getPlayers();
            }

            $rangedOnlinePlayers = [
                0 => 5, //0
                1900 => 0,
                2000 => 0,
                2100 => 0,
                2200 => 0,
                2300 => 0,
                2400 => 0,
                2500 => 0,
                2600 => 0,
                2700 => 0
            ];

            $coinPlayers = array_combine(MonetizationService::BIDS, array_fill(0, count(MonetizationService::BIDS), 0));
            $thisPlayerBalance = BalanceModel::getBalance($this->commonId);

            foreach (self::$players as $num => $player) {
                $rangedOnlinePlayers[0]++;

                $currentPlayerBalance = BalanceModel::getBalance($player['common_id']);

                // Отмечаем в массиве игроков на монеты число игроков с таким количеством монет
                foreach ($coinPlayers as $bid => $num) {
                    if ($thisPlayerBalance < $bid) {
                        unset($coinPlayers[$bid]);

                        continue;
                    }

                    if ($currentPlayerBalance >= $bid) {
                        $coinPlayers[$bid]++;
                    }
                }

                // Не выводим число рейтинговых игроков для скрабла, пока
                if (self::$gameName === self::SCRABBLE) {
                    continue;
                }

                if (($rating = CommonIdRatingModel::getRating(
                    $player['common_id'],
                    self::$gameName
                ))) {
                    if ($rating > 1900) {
                        $rangedOnlinePlayers[1900]++;
                    }
                    if ($rating > 2000) {
                        $rangedOnlinePlayers[2000]++;
                    }
                    if ($rating > 2100) {
                        $rangedOnlinePlayers[2100]++;
                    }
                    if ($rating > 2200) {
                        $rangedOnlinePlayers[2200]++;
                    }
                    if ($rating > 2300) {
                        $rangedOnlinePlayers[2300]++;
                    }
                    if ($rating > 2400) {
                        $rangedOnlinePlayers[2400]++;
                    }
                    if ($rating > 2500) {
                        $rangedOnlinePlayers[2500]++;
                    }
                    if ($rating > 2600) {
                        $rangedOnlinePlayers[2600]++;
                    }
                    if ($rating > 2700) {
                        $rangedOnlinePlayers[2700]++;
                    }
                }
            }

            if (self::$gameName === self::SCRABBLE) {
                $rangedOnlinePlayers[0] += mt_rand(5, 10);
            } else {
                Cache::setex(
                    static::NUM_RATING_PLAYERS_KEY,
                    $this->ratingsCacheTimeout,
                    $rangedOnlinePlayers
                );
            }
        }

        if ($rangedOnlinePlayers[1900]) {
            $cnt = Cache::hlen($this->Queue::QUEUES['erudit.rating_waiters']);
            if ($cnt < ($rangedOnlinePlayers[1900] / 2)) {
                $thisUserRating = CommonIdRatingModel::getRating(
                    $this->commonId,
                    self::$gameName
                );

                $rangedOnlinePlayers['thisUserRating'] = $thisUserRating;

                return $rangedOnlinePlayers;
            }
        }

        return $rangedOnlinePlayers;
    }

    protected function validateCookie($incomingCookie)
    {
        if (Tg::authorize()) {
            if (Tg::$tgUser) {
                return Tg::$tgUser['user']['id'];
            }
        }

        if (Yandex::authorize()) {
            if (Yandex::$yandexUser) {
                return Yandex::$yandexUser;
            }
        }

        if (strpos($incomingCookie, 'bot') !== false) {
            return $incomingCookie;
        } elseif (!isset($_SERVER['HTTP_COOKIE'])) {
            return ($sintCookie = (md5($_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT'])));
        } elseif (stristr($_SERVER['HTTP_COOKIE'], CookieErudit::COOKIE_NAME) === false) {
            return ($sintCookie = (md5($_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT'])));
        }

        return $incomingCookie;
    }

    public static function isInGame(string $cookie, bool $clearCache = false): ?string
    {
        if ($clearCache) {
            self::$playersInGames = [];
        } elseif (isset(self::$playersInGames[$cookie])) {
            return self::$playersInGames[$cookie];
        }

        $lastGame = Cache::get(Queue::GAMES_COUNTER);

        for ($i = $lastGame; $i > ($lastGame - 100); $i--) {
            if ($game = Cache::get(static::GAME_STATUS_KEY . $i)) {
                foreach ($game['users'] as $num => $user) {
                    if (!isset($user['ID'])) {
                        continue;
                    }
                    if (!isset($game['results'])) {
                        self::$playersInGames[$user['ID']] = $game['lang']; // игрок в игре
                    } elseif (!(self::$playersInGames[$user['ID']] ?? false)) {
                        self::$playersInGames[$user['ID']] = null; // игрок в игре, которая завершена
                    }
                }
            }
        }

        return self::$playersInGames[$cookie] ?? null;
    }


    private static function unauthorized()
    {
        return json_encode(
            [
                'result' => 'error',
                'message' => T::S('Authorization error')
            ],
            JSON_UNESCAPED_UNICODE
        );
    }

    private function checkCommonIdUnsafe($commonId): bool
    {
        return $commonId == PlayerModel::getPlayerID($this->User);
    }

    public function setInactive()
    {
        unset($this->gameStatus['users'][$this->numUser]['lastActiveTime']);
        $this->gameStatus['users'][$this->numUser]['inactiveTurn'] = $this->gameStatus['turnNumber'];
        $this->addToLog(T::S('Closed game window'), $this->numUser);

        return $this->makeResponse(
            [
                'gameState' => self::ADD_TO_CHAT_STATE,
                'message' => T::S('You closed the game window and became inactive!') . $this->numUser
            ]
        );
    }

    public function saveUserNameWithID($name, $commonId)
    {
        if (!$this->checkCommonIdUnsafe($commonId)) {
            return self::unauthorized();
        }

        $name = trim($name, "'\"");

        $res = UserModel::updateNickname($commonId, $name);

        return json_encode(
            [
                'result' => $res ? 'saved' : 'error',
                'message' => $res ? T::S('Nickname updated') : T::S('Error saving Nick change')
            ],
            JSON_UNESCAPED_UNICODE
        );
    }

    /**
     * @param array $user
     * @return false|mixed|string
     * @var Ru $bukvy
     */
    public function getPlayerName(array $user)
    {
        return PlayerModel::getPlayerName($user);

        if (strpos($user['ID'], 'bot') !== false) {
            return $this->config['botNames'][substr($user['ID'], (strlen($user['ID']) == 7 ? -1 : -2))];
        }

        $commonId = PlayerModel::getPlayerID($user['ID'], true);
        if ($commonId && ($commonIDName = UserModel::getNameByCommonId($commonId))) {
            return $commonIDName;
        }

        if (isset($user['userID'])) {
            $idSource = $user['userID'];
        } else {
            $idSource = $user['ID'];
        }

        if ($res = PlayerModel::getNameBySomeId($idSource)) {
            return $res;
        } else {
            $sintName = isset($user['userID'])
                ? md5($user['userID'])
                : $user['ID'];
            $letterName = '';

            foreach (str_split($sintName) as $index => $lowByte) {
                $letterNumber = base_convert("0x" . $lowByte, 16, 10)
                    + base_convert("0x" . substr($sintName, $index < 5 ? $index : 0, 1), 16, 10);

                if (!isset($this->gameStatus['lngClass'])) {
                    $this->gameStatus['lngClass'] = Ru::class;
                }

                if (!isset($this->gameStatus['lngClass']::$bukvy[$letterNumber])) {
                    //Английская версия
                    $letterNumber = number_format(round(34 + $letterNumber * (59 - 34 + 1) / 30, 0), 0);
                }

                /**
                 * @var Ru|Eng $bukvy
                 */

                if ($this->gameStatus['lngClass']::$bukvy[$letterNumber][3] == false) { // нет ошибки - класс неизвестен
                    $letterNumber = 31; // меняем плохую букву на букву Я
                }

                if ($letterName == '') {
                    if ($letterNumber == 28) {
                        continue; // Не ставим Ь в начало ника
                    }
                    $letterName = $this->gameStatus['lngClass']::$bukvy[$letterNumber][0];
                    $soglas = $this->gameStatus['lngClass']::$bukvy[$letterNumber][3];
                    continue;
                }

                if (mb_strlen($letterName) >= 6) {
                    break;
                }

                if ($this->gameStatus['lngClass']::$bukvy[$letterNumber][3] <> $soglas) {
                    $letterName .= $this->gameStatus['lngClass']::$bukvy[$letterNumber][0];
                    $soglas = $this->gameStatus['lngClass']::$bukvy[$letterNumber][3];

                    continue;
                }
            }

            return mb_strtoupper(mb_substr($letterName, 0, 1)) . mb_substr($letterName, 1);
        }
    }

    public function inviteNewGame()
    {
        // Если в игре останутся игроки, то ничего не делать
        if ($this->activeGameUsers() > 2) {
            return $this->makeResponse(
                ['gameState' => $this->getUserStatus(), 'message' => T::S('Request denied. Game is still ongoing')]
            );
        }

        // Если игрок решил сдаться и отправить вызов на реванш
        if (in_array($this->getUserStatus(), self::IN_GAME_STATUSES)) {
            $this->storeGameResults($this->lost3TurnsWinner($this->numUser, true));
        }

        $this->gameStatus['invite_accepted_users'][$this->User] = $this->User;

        if (!isset($this->gameStatus['invite'])) {
            $this->gameStatus['invite'] = $this->User;
            $message = 'Запрос на новую игру отправлен';
        } elseif ($this->gameStatus['invite'] == $this->User) {
            $message = T::S('Your new game request awaits players response');
        } else {
            $message = T::S('Request was aproved! Starting new game');
            $this->gameStatus['invite'] = 'newGameStarting';
        }

        $inviteStatus = ['inviteStatus' => $this->gameStatus['invite']];

        return $this->makeResponse(
            array_merge(['gameState' => self::GAME_RESULTS_STATE, 'message' => $message], $inviteStatus)
        );
    }

    public function playerCabinetInfo()
    {
        $message = [];

        $message['info'] = [];
        $message['info']['rating'] = CommonIdRatingModel::getRating($this->commonId, self::$gameName);
        $message['info']['top'] = CommonIdRatingModel::getTopByRating($message['info']['rating'], self::$gameName);
        $message['info'][self::SUDOKU_BALANCE] = BalanceModel::getBalanceFormatted($this->commonId);
        $message['info']['SUDOKU_TOP'] = BalanceModel::getTopByBalance(BalanceModel::getBalance($this->commonId));
        $message['info']['rewards'] = IncomeModel::getIncome($this->commonId) ?: '0.00';

        $refs = RefModel::find()
            ->where([RefModel::COMMON_ID_FIELD => $this->commonId, RefModel::IS_ACTIVE_FIELD => true])
            ->limit(10)
            ->order(RefModel::ID_FIELD, false)
            ->all();

        $message['refs'] = [];
        if ($refs) {
            foreach ($refs as $ref) {
                $message['refs'][] = [
                    UserModel::getNameByCommonId($ref->_ref_common_id)
                    ?? ($ref->_name ?? T::S('is_balance_hidden')),
                    MonetizationService::REWARD[AchievesModel::DAY_PERIOD] . '&nbsp;' . T::S('{{sudoku_icon_20}}')
                ];
            }

            if (true || count($refs) === 10) {
                $totalGamePlayed = RefModel::getNumGamesPlayedByCommonIdRefs($this->commonId);
                $totalRefsCount = RefModel::find()
                    ->where([RefModel::COMMON_ID_FIELD => $this->commonId, RefModel::IS_ACTIVE_FIELD => true])
                    ->count();
                $message['refs'][] = [
                    T::S('Newest 10 referrals are shown')
                    . VH::br()
                    . T::S('Total referrals count: [[value]]', [$totalRefsCount]),
                    T::S(AchievesModel::GAMES_PLAYED) . ": $totalGamePlayed"
                ];
            }
        }

        $transactions = PaymentModel::selectO(
            ['*'],
            ORM::where(PaymentModel::COMMON_ID_FIELD, '=', $this->commonId, true)
            //. ORM::andWhere(PaymentModel::STATUS_FIELD, '!=', PaymentModel::INIT_STATUS)
            ,
            ORM::orderBy(PaymentModel::UPDATED_AT_FIELD, false)
            . ORM::limit(10)
        );

        $message['transactions'] = VH::tagOpen(
            'table',
            '',
            ['class' => 'table']
        );

        $message['transactions'] .= VH::thead(
            VH::tr(
                VH::th(T::S('Date'), ['scope' => 'col'])
                . VH::th(T::S('Price'), ['scope' => 'col'])
                . VH::th(T::S('Status'), ['scope' => 'col']),
                ['class' => 'text-light']
            )
        );

        foreach ($transactions as $transaction) {
            $message['transactions'] .= VH::tr(
                VH::td($transaction->_updated_at)
                . VH::td($transaction->_summ)
                . VH::td(T::S($transaction->_status)),
                ['class' => 'text-light']
            );
        }
        $message['transactions'] .= VH::tagClose('table');

        $message['common_id'] = $this->commonId;

        $userData = UserModel::getOne($message['common_id']);

        $message['url'] = $userData['avatar_url'] ?? false;
        if (!$message['url']) {
            $message['url'] = AvatarModel::getDefaultAvatar($message['common_id']);
            $message['img_title'] = T::S('Default avatar is used');
        } else {
            $message['img_title'] = T::S('Avatar by provided link');
        }

        $message['name'] = $userData['name'] ?? PlayerModel::getPlayerName(
            ['ID' => $this->User, 'common_id' => $this->commonId]
        );

        $patreonStatusAchievement = Record::getPatreonAchievesByCommonId($this->commonId)[0] ?? null;
        if ($patreonStatusAchievement) {
            $message['patreon_description'] = 'Ваш текущий статус спонсора:';
            $message['patreon_description'] .= VH::renderCard($patreonStatusAchievement);
        } else {
            $fakePatreon = AchievesModel::new(
                [
                    AchievesModel::EVENT_TYPE_FIELD => AchievesModel::PATREON_TYPE,
                    AchievesModel::EVENT_PERIOD_FIELD => AchievesModel::DAY_PERIOD,
                    AchievesModel::EVENT_VALUE_FIELD => 0,
                    AchievesModel::REWARD_FIELD => 0,
                    AchievesModel::INCOME_FIELD => 0,
                ]
            );
            $message['patreon_description'] = 'Поддержите проект и получите карточку спонсора';
            $message['patreon_description'] .= VH::renderCard($fakePatreon);
        }

        $message['patreon_description_refs'] = $message['patreon_description'];

        $nextLevelPatreonPeriod = Record::nextLevel($patreonStatusAchievement->_event_period ?? null);
        $nextLevelRubles = MonetizationService::PATREON_LEVELS[$nextLevelPatreonPeriod] - ($patreonStatusAchievement->_event_value ?? 0);
        if ($nextLevelRubles > 0) {
            $message['patreon_description'] .= T::S(
                'Invest additional [[number]] [[ruble]] in the project\'s coins to obtain/upgrade your card to the [[value]] level with an income of [[value]] [[coin]] per day',
                [
                    $nextLevelRubles,
                    $nextLevelRubles,
                    T::upperFirst(
                        T::S(
                            'patreon_level_' . $nextLevelPatreonPeriod
                        )
                    ),
                    MonetizationService::PATREON_INCOME[$nextLevelPatreonPeriod],
                    MonetizationService::PATREON_INCOME[$nextLevelPatreonPeriod],
                ]
            );
            $message['patreon_description'] .= VH::br()
                . T::S(
                    'OR You could invite [[number]] more [[friend]] (referrals). Visit the Referrals tab for more information',
                    [(int)($nextLevelRubles / 100), (int)($nextLevelRubles / 100)]
                );
            $message['patreon_description_refs'] .= VH::br()
                . T::S(
                    'Invite [[number]] more [[friend]] (referrals) for next card level',
                    [(int)($nextLevelRubles / 100), (int)($nextLevelRubles / 100)]
                );
            $message['patreon_description'] .= VH::br(2) . 'Учитывается каждое пополнение - можно вносить частями.'
                . VH::br(2) . 'Карточка спонсора выдается навсегда';
        }

        $message['referral_link'] = T::S('invite_link') . $this->commonId;

        $message['text'] = '';
        $message['form'][] = [
            'prompt' => "Никнейм (id: {$this->commonId})",
            'inputName' => 'name',
            'inputId' => 'player_name',
            'onclick' => 'savePlayerName',
            'buttonCaption' => T::S('Set'),
            'placeholder' => T::S('new nickname')
        ];
        $message['form'][] = [
            'prompt' => '',
            'type' => 'hidden',
            'inputName' => 'cookie',
            'value' => $this->User, // todo что если это пользователь яндекса? или ТГ
        ];
        $message['form'][] = [
            'prompt' => '',
            'type' => 'hidden',
            'inputName' => 'MAX_FILE_SIZE',
            'value' => Players::MAX_UPLOAD_SIZE,
        ];
        $message['form'][] = Hints::IsNotAndroidApp()
            ? [
                'prompt' => T::S('Avatar loading'),
                'type' => 'file',
                'inputName' => 'url',
                'inputId' => 'player_avatar_file',
                'onclick' => 'savePlayerAvatar',
                'buttonCaption' => T::S('Send'),
                'required' => true,
            ]
            : [
                'prompt' => T::S('Avatar URL'),
                'inputName' => 'url',
                'inputId' => 'player_avatar_url',
                'onclick' => 'savePlayerAvatarUrl',
                'buttonCaption' => T::S('Apply'),
                'placeholder' => 'https://'
            ];

        $message['secret'] = $this->genKeyForCommonID($this->commonId);

        /*
        $message['form'][] = [
            'prompt' => 'Ключ учетной записи',
            'inputName' => 'keyForID',
            'inputId' => 'key_for_id',
            'onclick' => 'copyKeyForID',
            'buttonCaption' => 'В буфер',
            'value' => $this->genKeyForCommonID($message['common_id']),
            'readonly' => 'true'
        ];

        $message['form'][] = [
            'prompt' => 'Ключ основного аккаунта',
            'inputName' => 'key',
            'inputId' => 'old_account_key',
            'onclick' => 'mergeTheIDs',
            'buttonCaption' => 'Связать',
            'placeholder' => 'сохраненный ключ от старого аккаунта'
        ];
        */

        return $this->makeResponse(['message' => json_encode($message, JSON_UNESCAPED_UNICODE)]);
    }

    protected function genKeyForCommonID($ID)
    {
        $messageToEncrypt = $ID;
        $secretKey = Config::$envConfig['SALT'];
        $method = 'AES-128-CBC';
        $iv = base64_decode(Config::$envConfig['IV'] . '==');
        $encrypted_message = openssl_encrypt($messageToEncrypt, $method, $secretKey, 0, $iv);

        return $encrypted_message;
    }

    public function mergeTheIDs($encryptedMessage, $commonID, $secretKey = '')
    {
        $secretKey = $secretKey ?: Config::$envConfig['SALT'];
        $method = 'AES-128-CBC';
        $iv = base64_decode(Config::$envConfig['IV'] . '==');
        $decrypted_message = openssl_decrypt($encryptedMessage, $method, $secretKey, 0, $iv);

        if (!is_numeric($decrypted_message)) {
            if ($secretKey !== Config::$envConfig['OLD_SALT']) {
                return $this->mergeTheIDs($encryptedMessage, $commonID, Config::$envConfig['OLD_SALT']);
            }

            return json_encode(
                [
                    'result' => 'error_decryption' . ' ' . $decrypted_message,
                    'message' => T::S('Key transcription error')
                ],
                JSON_UNESCAPED_UNICODE
            );
        }

        $oldCommonID = UserModel::getCustom(
            'id',
            '=',
            $decrypted_message,
            false,
            false,
            ['id']
        )[0]['id'] ?? false;

        if ($oldCommonID === false) {
            return json_encode(
                [
                    'result' => 'error_query_oldID',
                    'message' => T::S("Player's ID NOT found by key")
                ],
                JSON_UNESCAPED_UNICODE
            );
        }

        if (PlayerModel::setParamMass(
            'common_id',
            $oldCommonID,
            [
                'field_name' => 'common_id',
                'condition' => '=',
                'value' => $commonID,
                'raw' => true
            ]
        )
        ) {
            return json_encode(
                [
                    'result' => 'save',
                    'message' => T::S('Accounts linked')
                ],
                JSON_UNESCAPED_UNICODE
            );
        } else {
            return json_encode(
                [
                    'result' => 'error_update ' . $oldCommonID . '->' . $commonID,
                    'message' => T::S('Accounts are already linked')
                ],
                JSON_UNESCAPED_UNICODE
            );
        }
    }

    public function getAvatarUrl($cookie)
    {
        $commonID = PlayerModel::getPlayerID($cookie, true);

        if ($commonID) {
            return PlayerModel::getAvatarUrl($commonID); // Players::getAvatarUrl($commonID);
        }

        return ''; // Тут добавить стандартный аватар из коллекции какойнибудь
    }

    public function addComplain($toNumUser = 'all')
    {
        $message = T::S('Report sent');

        if (count($this->gameStatus['users']) > 2 && $toNumUser == 'all') {
            return $this->makeResponse(['message' => T::S('Report declined! Please choose a player from the list')]);
        }

        if ($toNumUser == 'all') {
            $toUser = $this->numUser ? 0 : 1;
        } else {
            $toUser = $toNumUser;
        }

        $isSendSuccess = false;
        if (ComplainModel::add(
            PlayerModel::getPlayerID($this->User, true),
            PlayerModel::getPlayerID($this->gameStatus['users'][$toUser]['ID'], true),
            $this->gameStatus['chatLog'] ?? []
        )) {
            $respMessage = '<span style="align-content: center;"><strong>'
                . T::S('Your report accepted and will be processed by moderator')
                . '<br /><br /> '
                . T::S('If confirmed, the player will be banned')
                . '</strong></span>';
            $isSendSuccess = true;
        } else {
            $respMessage = '<span style="align-content: center;"><strong><span style="color:red;">'
                . T::S('Report declined!')
                . '</span><br /><br /> '
                . T::S('Only one complaint per each player per day can be sent. Total 24 hours complaints limit is')
                . ComplainModel::COMPLAINS_PER_DAY . '</strong></span>';
        }

        if ($isSendSuccess) {
            $this->gameStatus['chatLog'][] = [$this->numUser, $toNumUser, $message];

            if ($toNumUser == 'all') {
                foreach ($this->gameStatus['users'] as $num => $User) {
                    if ($num == $this->numUser) {
                        $this->gameStatus['users'][$num]['chatStack'][] = [T::S('You'), T::S('to all: ') . $message];
                    } else {
                        $this->gameStatus['users'][$num]['chatStack'][] = [
                            T::S('From player') . ($this->numUser + 1) . T::S(' (to all):'),
                            $message
                        ];
                    }
                }
            } else {
                $this->gameStatus['users'][$toNumUser]['chatStack'][] = [
                    T::S('From player') . ($this->numUser + 1) . ":",
                    $message
                ];
                $this->gameStatus['users'][$this->numUser]['chatStack'][] = [
                    T::S('You'),
                    T::S('To Player') . ($toNumUser + 1) . ': ' . $message
                ];
            }
        }

        return $this->makeResponse(
            [
                'message' => $respMessage,
                'gameState' => self::ADD_TO_CHAT_STATE,
            ]
        );
    }

    public function addToChat($message, $toNumUser = 'all', $needConfirm = true)
    {
        try {
            if ($toNumUser === Hints::TYPE_WORDS_QUERY) {
                return $this->makeResponse(
                    Hints::getWordHint($message)
                    + ['gameState' => self::WORD_QUERY_STATE]
                );
            }

            $commonIdFrom = PlayerModel::getPlayerID($this->User, true);

            $bannedTill = BanModel::isBannedTotal($commonIdFrom ?: 0);
            if ($bannedTill) {
                if ($needConfirm) {
                    return $this->makeResponse(
                        [
                            'message' => T::S('Message NOT sent - BAN until ') . date('d.m.Y', $bannedTill),
                            'gameState' => self::ADD_TO_CHAT_STATE
                        ]
                    );
                } else {
                    return true;
                }
            }


            $bannedBy = BanModel::bannedBy($commonIdFrom ?: 0);

            $this->gameStatus['chatLog'][] = [$this->numUser, $toNumUser, $message];

            if ($toNumUser == 'all') {
                foreach ($this->gameStatus['users'] as $num => $User) {
                    if ($num == $this->numUser) {
                        $this->gameStatus['users'][$num]['chatStack'][] = [T::S('You'), T::S('to all: ') . $message];
                    } elseif (!isset($bannedBy[PlayerModel::getCommonID($User['ID']) ?: 0])) {
                        $this->gameStatus['users'][$num]['chatStack'][] = [
                            T::S('From player') . ($this->numUser + 1) . T::S(' (to all):'),
                            $message
                        ];
                    }
                }
            } elseif ($toNumUser == 'adv') {
                foreach ($this->gameStatus['users'] as $num => $User) {
                    if (!isset($User['userID'])) {
                        $this->gameStatus['users'][$num]['chatStack'][] = [T::S('News'), $message];
                    }
                }
            } elseif (!isset($bannedBy[PlayerModel::getCommonID($this->gameStatus['users'][$toNumUser]['ID']) ?: 0])) {
                $this->gameStatus['users'][$toNumUser]['chatStack'][] = [
                    T::S('From player') . ($this->numUser + 1) . ":",
                    $message
                ];
                $this->gameStatus['users'][$this->numUser]['chatStack'][] = [
                    T::S('You'),
                    T::S('To Player') . ((int)$toNumUser + 1) . ': ' . $message
                ];
            } elseif ($needConfirm) {
                return $this->makeResponse(
                    [
                        'message' => '<strong>' . T::S(
                                'Message NOT sent - BAN from Player'
                            ) . ((int)$toNumUser + 1) . '</strong>',
                        'gameState' => self::ADD_TO_CHAT_STATE
                    ]
                );
            }

            if ($needConfirm) {
                return $this->makeResponse(['message' => T::S('Message sent'), 'gameState' => self::ADD_TO_CHAT_STATE]);
            } else {
                return true;
            }
        } catch (\Throwable $e) {
            return $this->makeResponse(['message' => $e->__toString(), 'gameState' => self::ADD_TO_CHAT_STATE]);
        }
    }

    public function addToLog($message, $numUser = false)
    {
        $this->gameStatus['gameLog'][] = [$numUser, $message];
        foreach ($this->gameStatus['users'] as $num => $User) {
            $this->gameStatus['users'][$num]['logStack'][] = [$numUser, $message];
        }
    }

    protected function getCommonID($cookie = false, $userID = false)
    {
        if ($cookie) {
            $res = PlayerModel::getPlayerID($cookie, true);
            if ($res) {
                return $res;
            }
        }

        if ($userID) {
            $res = PlayerModel::getCommonIdFromUserId($userID);
            if ($res) {
                return $res;
            }
        }

        return false;
    }

    public static function hash_str_2_int($str, $len = 16)
    {
        $hash_int = base_convert("0x" . substr(md5($str), 0, $len), 16, 10);

        return $hash_int;
    }


    protected function statusComments_startGame()
    {
        Stats::saveStats();

        $rating = CommonIdRatingModel::getRating($this->commonId, self::$gameName);

        $this->gameStatus['users'][$this->gameStatus[$this->User]]['rating'] = $rating ?: self::NEW_PLAYER;

        return T::S('New game has started!') . ' <br />'
            . T::S('Get')
            . ' <strong>'
            . $this->gameStatus['winScore'] . '</strong> '
            . T::S('score points')
            . '<br />' . $this->gameStatus['users'][$this->gameStatus['activeUser']]['username']
            . T::S(' is making a turn.')
            . '<br />'
            . T::S('Your current rank')
            . ' - <strong>' . $rating . '</strong>'
            . $this->coinsPrompt();
    }

    protected function coinsPrompt(): string
    {
        return ($this->gameStatus['bid'] ?? false
            ? (
                VH::br()
                . T::S('The bank of') . ' '
                . VH::strong(
                    number_format($this->gameStatus['bid'] * count($this->gameStatus['users']), 0, '.', ',')
                )
                . T::S('{{sudoku_icon_15}}') . ' '
                . T::S('will go to the winner')
            )
            : ''
        );
    }

    protected function statusComments_otherTurn()
    {
        return $this->gameStatus['users'][$this->gameStatus['activeUser']]['username'] . ' ходит'
            . Hints::getHint(
                $this->User,
                $this->gameStatus,
                $this->gameStatus['users'][$this->gameStatus[$this->User]]['rating'] ?? false
            );
    }

    protected function statusComments_desync()
    {
        return T::S('Server syncing..')
            . Hints::getHint(
                $this->User,
                $this->gameStatus,
                $this->gameStatus['users'][$this->gameStatus[$this->User]]['rating'] ?? false
            );
    }

    protected function statusComments_myTurn()
    {
        if ($this->gameStatus['turnNumber'] == 1) {
            Stats::saveStats();
        }

        if ($this->gameStatus['turnNumber'] == 1 || !isset($this->gameStatus['users'][$this->gameStatus[$this->User]]['rating'])) {
            $rating = CommonIdRatingModel::getRating($this->commonId, self::$gameName);

            $this->gameStatus['users'][$this->gameStatus[$this->User]]['rating'] = $rating ?: self::NEW_PLAYER;

            return T::S('Your turn!') . '<br />' . T::S(
                    'Game goal:'
                ) . '<strong> ' . $this->gameStatus['winScore'] . '</strong> '
                . T::S('score points')
                . '<br />' . T::S('Your current rank') . ' - <strong>' . $rating . '</strong>'
                . $this->coinsPrompt();
        } else {
            return $this->gameStatus['users'][$this->numUser]['username'] . ' - ' . T::S('Your turn!')
                . Hints::getHint(
                    $this->User,
                    $this->gameStatus,
                    $this->gameStatus['users'][$this->gameStatus[$this->User]]['rating'] ?? false
                );
        }
    }

    protected function statusComments_initGame()
    {
        if ($this->gamePlayersWaiting) {
            return '<strong>' . T::S('Looking for a new game...') . '</strong> <br />'
                . T::S('Players ready:') . ' <strong>' . $this->gamePlayersWaiting . '</strong>';
        } else {
            return '<strong>'
                . T::S('Looking for a new game...')
                . '</strong> <br />'
                . T::S('Waiting for other players');
        }
    }

    protected function statusComments_preMyTurn()
    {
        if ($this->gameStatus['turnNumber'] == 1) {
            $rating = CommonIdRatingModel::getRating($this->commonId, self::$gameName);
            $this->gameStatus['users'][$this->gameStatus[$this->User]]['rating'] = $rating;

            return T::S('Game goal:') . ' <strong>' . $this->gameStatus['winScore'] . '</strong> '
                . T::S('score points')
                . '<br />' . T::S('Your turn is next - get ready!')
                . '<br />' . T::S('Your current rank')
                . ' - <strong>' . $rating . '</strong>'
                . $this->coinsPrompt();
        } else {
            return $this->gameStatus['users'][$this->gameStatus['activeUser']]['username'] . ' ' . T::S(
                    ' is making a turn.'
                )
                . '<br />' . T::S('Your turn is next - get ready!')
                . Hints::getHint(
                    $this->User,
                    $this->gameStatus,
                    $this->gameStatus['users'][$this->gameStatus[$this->User]]['rating'] ?? false
                );
        }
    }

    public function changeFishki($fishkiToChange)
    {
        if ($this->getUserStatus() != self::MY_TURN_STATUS) {
            return $this->checkGameStatus();
        }

        if ($this->isBot()) {
            $fishkiToChange = $this->getBotFishkiToChange();
        }

        if (count($fishkiToChange)) {
            $this->addToLog(T::S('switches pieces and skips turn'), $this->numUser);

            foreach ($fishkiToChange as $newFishka => $on) {
                $fishkaCode = explode('_', $newFishka);
                $fishkaCode = $fishkaCode[2];

                foreach ($this->gameStatus['users'][$this->numUser]['fishki'] as $num => $code) {
                    if ($fishkaCode == $code) {
                        unset($this->gameStatus['users'][$this->numUser]['fishki'][$num]);

                        array_push($this->gameStatus['bankFishki'], $fishkaCode);

                        break;
                    }
                }
            }

            shuffle($this->gameStatus['bankFishki']);

            $addFishki = $this->giveFishki(
                $this->chisloFishek - count($this->gameStatus['users'][$this->numUser]['fishki'])
            );

            /** @var $hasReceivedTverdZnak bool Признак, что в выданных фишках есть Ъ */
            $hasReceivedTverdZnak = in_array(Ru::TZ_CODE, $addFishki);

            try {
                $this->gameStatus['users'][$this->numUser][AchievesModel::TVERD_NUM] += (int)$hasReceivedTverdZnak;

                if ($this->gameStatus['users'][$this->numUser][AchievesModel::TVERD_NUM]) {
                    $arr = Prizes::checkTverdNumRecord(
                        $this->gameStatus['users'][$this->numUser][AchievesModel::TVERD_NUM],
                        $this->gameStatus['users'][$this->numUser]['common_id'] ?? null
                    );

                    foreach ($arr as $period => $value) {
                        $this->addToLog(
                            T::S(
                                'устанавливает рекорд по числу твердых знаков, выпавших или сыгранных в одной партии за'
                            ) . " $period - <strong>{$this->gameStatus['users'][$this->numUser][AchievesModel::TVERD_NUM]}</strong>",
                            $this->numUser
                        );
                    }
                }
            } catch (Throwable $e) {
                LogModel::add(
                    [
                        LogModel::CATEGORY_FIELD => LogModel::CATEGORY_RECORD_ERROR,
                        LogModel::MESSAGE_FIELD => $e->getMessage(),
                    ]
                );
            }

            $this->gameStatus['users'][$this->numUser]['fishki'] = array_merge(
                $this->gameStatus['users'][$this->numUser]['fishki'],
                $addFishki
            );
        }
        $this->gameStatus['users'][$this->numUser]['lostTurns']++;
        //Увеличили число пропущенных подряд ходов

        if ($this->gameStatus['users'][$this->numUser]['lostTurns'] >= 3) {
            $this->storeGameResults($this->lost3TurnsWinner($this->numUser));
        } else {
            $this->nextTurn();
        }

        return $this->checkGameStatus();
    }

    protected function storeGameResults($winnerUser)
    {
        $results = [];

        foreach ($this->gameStatus['users'] as $num => $user) {
            if ($user['ID'] == $winnerUser) {
                $results['winner'] = $winnerUser;

                // Начисляем победителю монеты
                if ($this->gameStatus['bid'] ?? false) {
                    $sudokuCount = $this->gameStatus['bid'] * count($this->gameStatus['users']);

                    DB::transactionStart();

                    if (
                        !BalanceModel::changeBalance(
                            BalanceModel::SYSTEM_ID,
                            -1 * $sudokuCount,
                            $user['common_id'] . ' got winner reward',
                            BalanceHistoryModel::TYPE_IDS[BalanceHistoryModel::GAME_TYPE],
                            $this->currentGame
                        )
                        ||
                        !BalanceModel::changeBalance(
                            $user['common_id'],
                            $sudokuCount,
                            BalanceHistoryModel::WINNER_REWARD_TYPE,
                            BalanceHistoryModel::TYPE_IDS[BalanceHistoryModel::WINNER_REWARD_TYPE],
                            $this->currentGame
                        )) {
                        DB::transactionRollback();
                    } else {
                        DB::transactionCommit();

                        $this->addToLog(
                            T::S('gets a win') . ': ' . T::S('{{sudoku_icon_15}}') . $sudokuCount,
                            $num
                        );
                    }
                }
            } else {
                $results['lostUsers'][] = $user['ID'];
            }
        }

        $this->gameStatus['results'] = $results;
        // Новая версия

        foreach ($this->gameStatus['users'] as $num => $user) {
            $this->updateUserStatus(self::GAME_RESULTS_STATE, $user['ID']);

            if (
                !empty($user['ID'])
                && strstr($user['ID'], self::BOT_TPL) === false
                && !empty($this->gameStatus['users'][$num]['last_request_num'])
                && $this->gameStatus['users'][$num]['last_request_num'] > 10
            ) {
                Cache::setex(
                    self::CHECK_STATUS_RESULTS_KEY . $user['ID'],
                    self::CHECK_STATUS_RESULTS_KEY_TTL,
                    $this->gameStatus['users'][$num]['last_request_num']
                );
            }
        }

        $resultRatings = RatingService::processGameResult($this->gameStatus);
        foreach ($this->gameStatus['users'] as &$user) {
            $user['result_ratings'] = $resultRatings[$user['common_id']];

            $numGamesPlayed = RatingHistoryModel::getNumGamesPlayed($user['common_id']);
            if ($numGamesPlayed > 0 && ($numGamesPlayed % 100 === 0)) {
                // Начисляем бонус за каждые 100 игр
                BalanceModel::changeBalance(
                    $user['common_id'],
                    MonetizationService::REWARD[AchievesModel::DAY_PERIOD],
                    BalanceHistoryModel::BONUS_100_GAMES,
                    BalanceHistoryModel::TYPE_IDS[BalanceHistoryModel::MOTIVATION_TYPE],
                    $numGamesPlayed // Ставим ref = числу сыгранных игр для избежания повторного начисления
                );
            }
        }
    }

    protected function getUserStatus($user = false): string
    {
        return $this->gameStatus['users'][$this->gameStatus[($user ?: $this->User)]]['status'] ?? self::ERROR_STATUS;
    }

    public function updateUserStatus($newStatus, $user = false)
    {
        if (!$user) {
            $user = $this->User;
        }

        if (isset($this->gameStatus[$user])) {
            $this->gameStatus['users'][$this->gameStatus[$user]]['status'] = $newStatus;
            //Обновили статус по новой версии

            if ($newStatus == self::MY_TURN_STATUS) {
                $this->gameStatus['activeUser'] = $this->gameStatus[$user];
            }
            //Активный пользователь
        }
    }

    public static function staticGameWordsPlayed(string $cookie): array
    {
        $currentGame = Cache::get(Queue::GET_GAME_KEY . $cookie);

        if (!$currentGame) {
            return [];
        } else {
            $gameStatus = Cache::get(static::GAME_STATUS_KEY . $currentGame);
            //Забрали статус игры из кэша

            return $gameStatus['wordsAccepted'] ?? [];
        }
    }

    public function gameWordsPlayed(): array
    {
        return $this->gameStatus['wordsAccepted'] ?? [];
    }

    public function wordChecker()
    {
        if ($this->currentGame === false) {
            return 'Игра еще не начата!';
        }

        $check_statuses = [T::S("Word wasn't found"), T::S('Correct'), T::S('One-letter word'), T::S('Repeat')];
        $result = '';

        $desk = Cache::get(static::CURRENT_GAME_KEY . $this->currentGame);
        //Текущая доска
        $cells = json_decode($_POST['cells'], true);
        //Присланная доска

        /**
         * @method Ru|Eng submit()
         */
        $new_fishki = $this->gameStatus['lngClass']::submit(
            $cells,
            $desk,
            $this->gameStatus['users'][$this->numUser]['fishki'],
            $this->gameStatus['wordsAccepted']
        );
        $summa = 0;

        if (is_array($new_fishki) && !empty($new_fishki)) {
            foreach ($new_fishki['badWords'] as $badWord) {
                if (isset($this->gameStatus['wordsAccepted'][$badWord])) {
                    $result .= '<strong>' . $badWord . ' - ' . $check_statuses[3] . '</strong><br />';
                } elseif (mb_strlen($badWord, 'UTF-8') == 1) {
                    $result .= '<strong>' . $badWord . ' - ' . $check_statuses[2] . '</strong><br />';
                } elseif (mb_strlen($badWord, 'UTF-8') > 1) {
                    $result .= '<strong style="color:red;">' . $badWord . ' - ' . $check_statuses[0] . '</strong><br />';
                }
            }
            foreach ($new_fishki['words'] as $word => $price) {
                $result .= $word . ' - ' . T::S('costs') . ': ' . $price . '<br />';
                $summa += $price;
            }
            if (count($new_fishki['good']) == count($this->gameStatus['users'][$this->numUser]['fishki'])) {
                $summa += 15;
                $result .= T::S('+15 for all pieces used') . '<br />';
            }
        }

        if ($result !== '') {
            $result .= '<strong>' . T::S('TOTAL') . ': ' . $summa . '</strong>';
        } else {
            $result = T::S('You did not make any word');
        }

        return json_encode([$result], JSON_UNESCAPED_UNICODE);
    }

    public function botUnlock(): void
    {
        // todo как рахзлочить игру для бота в Cache::__destruct
    }

    protected function desync($queryNumber = false)
    {
        $this->updateUserStatus('desync');
        $arr = ['gameState' => 'desync', 'noDialog' => true];

        if ($queryNumber) {
            $arr['queryNumber'] = $queryNumber;
        }

        return $this->makeResponse($arr);
    }

    public function submitTurn()
    {
        if ($this->getUserStatus() != self::MY_TURN_STATUS) {
            $this->addToLog(
                T::S('is attempting to make a turn out of his turn (turn #') . $this->gameStatus['turnNumber'] . ')',
                $this->numUser
            );

            return $this->checkGameStatus();
        }

        try {
            $desk = Cache::get(static::CURRENT_GAME_KEY . $this->currentGame);
            // Текущая доска

            $saveDesk = $desk;
            // Сохранили доску во временную копию

            $saveWords = $this->gameStatus['wordsAccepted'];
            // сохранили сыгранные слова

            $cells = json_decode($_POST['cells'], true);
            // Присланная доска

            if (Ru::checkHasBadField($cells)) {
                LogModel::add(
                    [
                        LogModel::CATEGORY_FIELD => LogModel::CATEGORY_SUBMIT_ERROR,
                        LogModel::MESSAGE_FIELD => json_encode(
                            [
                                'game_status' => $this->gameStatus,
                                'User' => $this->User,
                                'cells' => $cells,
                                'desk' => $desk,
                            ],
                            JSON_UNESCAPED_UNICODE
                        )
                    ]
                );

                // Прислана доска с ошибкой в поле - ничего не обрабатываем
                return $this->checkGameStatus();
            }

            /** @var  $hasTverdZnakInFishki bool Признак наличия Ъ на руках у игрока */
            $hasTverdZnakInFishki = in_array(Ru::TZ_CODE, $this->gameStatus['users'][$this->numUser]['fishki']);

            /**
             * @method Ru|Eng submit()
             */
            $new_fishki = $this->gameStatus['lngClass']::submit(
                $cells,
                $desk,
                $this->gameStatus['users'][$this->numUser]['fishki'],
                $this->gameStatus['wordsAccepted']
            );
        } catch (\Throwable $e) {
            LogModel::add(
                [
                    LogModel::CATEGORY_FIELD => LogModel::CATEGORY_SUBMIT_ERROR,
                    LogModel::MESSAGE_FIELD => $e->__toString(),
                ]
            );

            $this->addToLog(
                T::S(' - turn processing error (turn #') . $this->gameStatus['turnNumber'] . ')',
                $this->numUser
            );

            return $this->checkGameStatus();
        }

        if (Ru::checkHasBadField($cells)) {
            LogModel::add(
                [
                    LogModel::CATEGORY_FIELD => LogModel::CATEGORY_RULANG_ERROR,
                    LogModel::MESSAGE_FIELD => json_encode($cells)
                ]
            );

            // После анализа присланной доски ошибка в поле - ничего не сохраняем
            return $this->checkGameStatus();
        }

        if ($new_fishki === false) {
            $this->addToLog(
                T::S("didn't make any word (turn #") . $this->gameStatus['turnNumber'] . ')',
                $this->numUser
            );

            $this->gameStatus['users'][$this->numUser]['lostTurns']++;
            if ($this->gameStatus['users'][$this->numUser]['lostTurns'] >= 3) {
                $this->storeGameResults($this->lost3TurnsWinner($this->numUser));

                return $this->checkGameStatus();
            }

            $this->nextTurn();
            $this->destruct();
            //Сохранили статус игры

            return json_encode(array_merge($saveDesk ?: [], [$this->gameStatus['users'][$this->numUser]['fishki']]));
            //Сделать через отправку статуса
        }

        try {
            $ochkiZaHod = 0;
            //Очки за ход пока=0

            if (is_array($new_fishki)) {
                //Проверяем забранные с поля звезды
                foreach ($this->gameStatus['users'][$this->numUser]['fishki'] as $num => $fishka) {
                    foreach ($new_fishki['good'] as $i => $good_fishka) {
                        if ($fishka === $good_fishka['replaced_code']) {
                            unset($new_fishki['good'][$i]);
                            unset($this->gameStatus['users'][$this->numUser]['fishki'][$num]);

                            break;
                        }
                    }
                }
                //Проверяем оставшиеся фишки
                foreach ($this->gameStatus['users'][$this->numUser]['fishki'] as $num => $fishka) {
                    foreach ($new_fishki['good'] as $i => $good_fishka) {
                        if (($fishka === $good_fishka['code']) || (($fishka == 999) && ($good_fishka['code'] > 999))) {
                            unset($new_fishki['good'][$i]);
                            unset($this->gameStatus['users'][$this->numUser]['fishki'][$num]);

                            break;
                        }
                    }
                }

                $vseFishki = false;
                if (count($this->gameStatus['users'][$this->numUser]['fishki']) === 0) {
                    $ochkiZaHod += 15;
                    $vseFishki = true;
                }

                $addFishki = $this->giveFishki(
                    $this->chisloFishek - count($this->gameStatus['users'][$this->numUser]['fishki'])
                );

                /** @var $hasReceivedTverdZnak bool Признак, что в выданных фишках есть Ъ */
                $hasReceivedTverdZnak = in_array(Ru::TZ_CODE, $addFishki);

                try {
                    // Проверяем достижение по числу слов за ход
                    $wordCount = count($new_fishki['words']);
                    $arr = Prizes::checkWordTurnRecord(
                        $wordCount,
                        $this->gameStatus['users'][$this->numUser]['common_id']
                    );
                    foreach ($arr as $period => $value) {
                        $this->addToLog(
                            T::S('устанавливает рекорд по числу составленных слов за ход за')
                            . " $period - <strong>$wordCount</strong>",
                            $this->numUser
                        );
                    }

                    // Проверяем достижение по твердым знакам
                    $this->gameStatus['users'][$this->numUser][AchievesModel::TVERD_NUM] +=
                        (int)$hasReceivedTverdZnak
                        + ($hasTverdZnakInFishki ? 0 : self::checkTZCount($new_fishki['words']));
                    if ($this->gameStatus['users'][$this->numUser][AchievesModel::TVERD_NUM]) {
                        $arr = Prizes::checkTverdNumRecord(
                            $this->gameStatus['users'][$this->numUser][AchievesModel::TVERD_NUM],
                            $this->gameStatus['users'][$this->numUser]['common_id'] ?? null
                        );

                        foreach ($arr as $period => $value) {
                            $this->addToLog(
                                T::S(
                                    'устанавливает рекорд по числу твердых знаков, выпавших или сыгранных в одной партии за'
                                ) . " $period - <strong>{$this->gameStatus['users'][$this->numUser][AchievesModel::TVERD_NUM]}</strong>",
                                $this->numUser
                            );
                        }
                    }
                } catch (Throwable $e) {
                    LogModel::add(
                        [
                            LogModel::CATEGORY_FIELD => LogModel::CATEGORY_RECORD_ERROR,
                            LogModel::MESSAGE_FIELD => $e->getMessage(),
                        ]
                    );
                }


                $this->gameStatus['users'][$this->numUser]['fishki'] = array_merge(
                    $this->gameStatus['users'][$this->numUser]['fishki'],
                    $addFishki
                );

                foreach ($new_fishki['words'] as $word => $price) {
                    $ochkiZaHod += $price;
                    //Добавили очков за ход
                    $this->gameStatus['wordsAccepted'][$word] = $word;
                    //Добавили слово в список сыгранных слов

                    $wordLen = mb_strlen($word, 'UTF-8');
                    // Оборачиваем начисление призов в try-catch
                    try {
                        $arr = Prizes::checkWordLenRecord(
                            $wordLen,
                            $this->gameStatus['users'][$this->numUser]['common_id'] ?? null,
                            $word
                        );
                        foreach ($arr as $period => $value) {
                            $this->addToLog(
                                T::S('set word lenght record for') . " $period - <strong>$word ({$wordLen})</strong>",
                                $this->numUser
                            );
                        }

                        $arr = Prizes::checkWordPriceRecord(
                            $price,
                            $this->gameStatus['users'][$this->numUser]['common_id'] ?? null,
                            $word
                        );
                        foreach ($arr as $period => $nothing) {
                            $this->addToLog(
                                T::S('set word cost record for') . " $period - <strong>$word - $price</strong>",
                                $this->numUser
                            );
                        }
                    } catch (Throwable $e) {
                        LogModel::add(
                            [
                                LogModel::CATEGORY_FIELD => LogModel::CATEGORY_RECORD_ERROR,
                                LogModel::MESSAGE_FIELD => $e->getMessage(),
                            ]
                        );
                    }
                }
            }

            $this->gameStatus['users'][$this->numUser]['score'] += $ochkiZaHod;
            if ($ochkiZaHod == 0) {
                $this->gameStatus['users'][$this->numUser]['lostTurns']++;
            } else {
                // Оборачиваем проверку рекордов в try-catch
                try {
                    $playerTurnPriceRecordsArr = Prizes::checkTurnPriceRecord(
                        $ochkiZaHod,
                        $this->gameStatus['users'][$this->numUser]['common_id'] ?? null
                    );
                    foreach ($playerTurnPriceRecordsArr as $period => $nothing) {
                        $this->addToLog(
                            T::S('set record for turn cost for') . " $period - <strong>$ochkiZaHod</strong>",
                            $this->numUser
                        );
                    }
                } catch (Throwable $e) {
                    LogModel::add(
                        [
                            LogModel::CATEGORY_FIELD => LogModel::CATEGORY_RECORD_ERROR,
                            LogModel::MESSAGE_FIELD => $e->getMessage(),
                        ]
                    );
                }
            }

            $this->addToLog(
                T::S('gets') . ' ' . $ochkiZaHod . ' ' . T::S('for turn #') . $this->gameStatus['turnNumber']
                . $this->logSlov($new_fishki['words'])
                . ($vseFishki ? (' ' . VH::span(
                        '(<strong>+15</strong>)',
                        [
                            'title' => T::S('For all pieces'),
                            'style' => 'color: #00ff00;'
                        ]
                    )) : ''),
                $this->numUser
            );

            if ($this->gameStatus['users'][$this->numUser]['score'] >= $this->gameStatus['winScore']) {
                $this->addToLog(
                    T::S('Wins with score ') . $this->gameStatus['users'][$this->numUser]['score'],
                    $this->numUser
                );

                // Оборачиваем проверку рекордов в try-catch
                try {
                    $arr = Prizes::checkGamePriceRecord(
                        $this->gameStatus['users'][$this->numUser]['score'],
                        $this->gameStatus['users'][$this->numUser]['common_id'] ?? null
                    );
                    foreach ($arr as $period => $value) {
                        $this->addToLog(
                            T::S(
                                'set record for gotten points in the game for'
                            ) . " $period - <strong>{$this->gameStatus['users'][$this->numUser]['score']}</strong>",
                            $this->numUser
                        );
                    }
                } catch (Throwable $e) {
                    LogModel::add(
                        [
                            LogModel::CATEGORY_FIELD => LogModel::CATEGORY_RECORD_ERROR,
                            LogModel::MESSAGE_FIELD => $e->getMessage(),
                        ]
                    );
                }

                $this->storeGameResults($this->User);
                //Обнаружен выигравший
            } elseif ($this->gameStatus['users'][$this->numUser]['lostTurns'] >= 3) {
                $this->storeGameResults($this->lost3TurnsWinner($this->numUser));
                //Обнаружен выигравший
            } elseif (count($this->gameStatus['users'][$this->numUser]['fishki']) === 0) {
                $this->addToLog(T::S('out of chips - end of game!'), $this->numUser);
                $this->storeGameResults($this->endOfFishki());
            } // Обнаружен выигравший
            elseif ($ochkiZaHod > 0) {
                $this->gameStatus['users'][$this->numUser]['lostTurns'] = 0;
                $this->nextTurn();
            } elseif ($ochkiZaHod == 0) {
                $this->nextTurn();
            }

            if (isset($this->gameStatus['results'])) {
                $arr = Prizes::checkGamesPlayedRecord(
                    array_column($this->gameStatus['users'], 'common_id')
                );

                foreach ($arr as $period => $record) {
                    $this->addToLog(
                        T::S('set record for number of games played for') . " $period - <strong>"
                        . reset($record)
                        . "</strong>",
                        $this->getNumPlayerByCommonId(key($record)) ?? false
                    );
                }
            }
        } catch (\Throwable $e) {
            LogModel::add(
                [
                    LogModel::CATEGORY_FIELD => LogModel::CATEGORY_SUBMIT_ERROR,
                    LogModel::MESSAGE_FIELD => $e->__toString(),
                ]
            );

            // $this->nextTurn();
            // $this->destruct();
            return $this->checkGameStatus();

            // TODO club-472 ПОКА НЕ ВЫКИДЫВЕМ 400Ю ОШИБКУ
            BadRequest::sendBadRequest(
                [
                    'message' => T::S('Data processing error!'),
                    'err_msg' => $e->getMessage(),
                    'err_file' => $e->getFile(),
                    'err_line' => $e->getLine(),
                    'err_context' => $e->getTrace(),
                ],
                $this->isBot()
            );
        }

        if ($ochkiZaHod === 0) {
            // Сохраняем в лог комбинацию на 0 очков
            LogModel::add(
                [
                    LogModel::CATEGORY_FIELD => LogModel::CATEGORY_BAD_COMBINATION,
                    LogModel::MESSAGE_FIELD => [
                        'new_fishki' => $new_fishki,
                        'old_cells' => json_decode($_POST['cells'], true),
                        'old_desk' => $saveDesk,
                        'new_desk' => $cells,
                        'saved_words' => $saveWords,
                        'new_played_words' => $new_fishki['words'] ?? [],
                    ]
                ]
            );
        } else {
            Cache::setex(static::CURRENT_GAME_KEY . $this->currentGame, $this->cacheTimeout, $cells);
            //Измененная Присланная доска -> текущая
        }

        $this->destruct();
        //Сохранили статус игры

        return json_encode(
            [
                'desk' => $ochkiZaHod
                    ? ($cells ?: [])
                    : ($saveDesk ?: []),
                'fishki' => $this->gameStatus['users'][$this->numUser]['fishki'],
            ]
        );
        //Сделать через отправку статуса
    }

    protected function logSlov($words)
    {
        $res = '<br />';
        foreach ($words as $word => $price) {
            $res .= " <a href=\"#\" onclick=\"event.preventDefault(); openWindowGlobal('" . urlencode(
                    $word
                ) . "');\">$word</a>-$price&nbsp;";
        }

        return $res;
    }

    protected function giveFishki($num = 7)
    {
        $fishki = [];
        $bankFishkiCount = count($this->gameStatus['bankFishki']);
        //Зафиксировали число фишек в банке

        for ($i = 0; ($i < $num) && ($i < $bankFishkiCount); $i++) {
            $fishki[] = (int)array_shift($this->gameStatus['bankFishki']);
        }

        return $fishki;
    }

    protected function activeGameUsers()
    {
        $numActive = 0;
        foreach ($this->gameStatus['users'] as $user) {
            if ($user['isActive']) {
                $numActive++;
            }
        }

        return $numActive;
    }

    public function initGame()
    {
        return (new $this->Queue($this->User, $this, $_POST + ['init_game' => true]))
            ->doSomethingWithThisStuff($_GET['lang'] ?? '');
    }

    public function getPrefs(): array
    {
        $prefs = Cache::get($this->Queue::PREFS_KEY . $this->User) ?: [];
        $balance = BalanceModel::getBalance($this->commonId);

        if ($balance > 0) {
            $prefsBid = $prefs['bid'] ?? 0;

            if ($prefsBid < ($balance / 20)) {
                foreach (MonetizationService::BIDS as $bid) {
                    if ($bid >= ($balance / 20)) {
                        $prefs['bid'] = $bid;

                        break;
                    }
                }
            } elseif ($prefsBid > $balance) {
                $bids = MonetizationService::BIDS;
                sort($bids);

                foreach ($bids as $bid) {
                    if ($bid < $balance) {
                        $prefs['bid'] = $bid;

                        break;
                    }
                }
            }
        } else {
            $prefs['bid'] = 0;
        }

        return $prefs;
    }

    public function checkGameStatus()
    {
        if (!$this->currentGame) {
            if ($this->Queue::isUserInQueue($this->User)) {
                return (new $this->Queue($this->User, $this, $_POST))->doSomethingWithThisStuff($_GET['lang'] ?? '');
            }

            if ($this->isBot()) {
                return $this->initGame();
            }

            $chooseGameParams = [
                'gameState' => self::CHOOSE_GAME_STATUS,
                'gameSubState' => 'choosing',
                'players' => $this->onlinePlayers(),
                'coin_players' => $this->onlineCoinPlayers(),
                'prefs' => $this->getPrefs(),
            ];

            return /*$this->isBot() ? $this->initGame() : */
                $this->makeResponse($chooseGameParams);
        }

        if ($this->activeGameUsers() < 2) {
            if (!isset($this->gameStatus['results'])) {
                $this->storeGameResults($this->User);
                $this->addToLog(T::S("is the only one left in the game - Victory!"), $this->numUser);
                //Пользователь остался в игре один и выиграл
            } else {
                $this->addToLog(T::S("is the only one left in the game! Start a new game"), $this->numUser);
            }
        }

        $desk = Cache::get(static::CURRENT_GAME_KEY . $this->currentGame);

        if ($this->getUserStatus() == self::GAME_RESULTS_STATE) {
            $result = $this->gameStatus['results'];
            if (isset($result['winner'])) {
                $ratingsChanged = $this->gameStatus['users'][$this->numUser]['result_ratings'];

                return $this->makeResponse(
                    [
                        'gameState' => self::GAME_RESULTS_STATE,
                        'comments' => self::playerGameResultsRendered(
                            $result['winner'] == $this->User,
                            $ratingsChanged
                        ),
                        ($desk ? 'desk' : 'nothing') => $desk
                    ]
                );
            }
        }

        //Поставим коррекцию времени начала хода для учета периодичности запросов пользователей
        if (
            $this->getUserStatus() == self::MY_TURN_STATUS
            &&
            !$this->gameStatus['aquiringTimes'][$this->gameStatus['turnNumber']]
        ) {
            if ((date('U') - $this->gameStatus['turnBeginTime']) < $this->turnDeltaTime) {
                $this->gameStatus['aquiringTimes'][$this->gameStatus['turnNumber']] = date('U');
            } else {
                $this->gameStatus['aquiringTimes'][$this->gameStatus['turnNumber']] = $this->gameStatus['turnBeginTime'];
            }
        }

        if ((date('U') - $this->gameStatus['turnBeginTime']) > ($this->gameStatus['turnTime'] + $this->turnDeltaTime)) {
            $this->addToLog(T::S('Time for the turn ran out'), $this->gameStatus['activeUser']);

            $this->gameStatus['users'][$this->gameStatus['activeUser']]['lostTurns']++;
            $this->gameStatus['users'][$this->gameStatus['activeUser']]['inactiveTurn'] = $this->gameStatus['turnNumber'];
            unset($this->gameStatus['users'][$this->gameStatus['activeUser']]['lastActiveTime']);
            //Помечаем игрока неактивным

            if ($this->gameStatus['users'][$this->gameStatus['activeUser']]['lostTurns'] >= 3) {
                $this->storeGameResults($this->lost3TurnsWinner($this->gameStatus['activeUser']));
                $result = $this->gameStatus['results'];

                if (isset($result['winner'])) {
                    $ratingsChanged = $this->gameStatus['users'][$this->numUser]['result_ratings'];
                    {
                        return $this->makeResponse(
                            [
                                'gameState' => self::GAME_RESULTS_STATE,
                                'comments' => self::playerGameResultsRendered(
                                    $result['winner'] == $this->User,
                                    $ratingsChanged
                                ),
                                ($desk ? 'desk' : 'nothing') => $desk
                            ]
                        );
                    }
                }
            } else {
                $this->nextTurn();
            }
        }

        $userStatus = $this->getUserStatus();

        if ($desk) {
            return $this->makeResponse(['gameState' => $userStatus, 'desk' => $desk]);
        } else {
            if ($userStatus && $userStatus != self::ERROR_STATUS) {
                return $this->makeResponse(['gameState' => $userStatus]);
            } //Вернули статус пользователя из кеша
            else { //Если статус ФАЛС то делаем новую игру
                return $this->newGame();
            }
        }
    }

    protected function playerGameResultsRendered(bool $isWinner, array $ratingsChanged): string
    {
        return
            VH::h2(
                $isWinner ? T::S('you_won') : T::S('you_lost'),
                $isWinner
                    ? ['style' => "color: #2ecc71; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 10px; text-shadow: 0 0 10px rgba(46, 204, 113, 0.5);"]
                    : ['style' => "color: #e74c3c; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 10px; text-shadow: 0 0 12px rgba(231, 76, 60, 0.4);"]
            )
            /*. VH::div(
                T::S('rating_changed') . "{$ratingsChanged['prev_rating']} "
                . VH::span('→ ', ['style' => "color: #fff; margin: 0 10px;"])
                . VH::span(
                    "{$ratingsChanged['new_rating']} (" . ($isWinner ? '+' : '') . "{$ratingsChanged['delta_rating']})",
                    $isWinner
                        ? ['style' => "color: #2ecc71; font-weight: bold;"]
                        : ['style' => "color: #e74c3c; font-weight: bold;"]
                )
            )*/
            . VH::div(
                T::S('rating_changed') . ": " .
                VH::span(
                    "{$ratingsChanged['prev_rating']} " .
                    VH::span('→ ', ['style' => "color: #fff; margin: 0 10px;"]) .
                    VH::span(
                        "{$ratingsChanged['new_rating']} (" . ($isWinner ? '+' : '') . "{$ratingsChanged['delta_rating']})",
                        $isWinner
                            ? ['style' => "color: #2ecc71; font-weight: bold;"]
                            : ['style' => "color: #e74c3c; font-weight: bold;"]
                    ),
                    ['style' => "display: inline-flex; align-items: center; white-space: nowrap;"]
                )
            )
            . VH::div(
                VH::div(
                    VH::p(
                        T::S('The bank of') . ' '
                        . VH::strong(
                            number_format($this->gameStatus['bid'] * count($this->gameStatus['users']), 0, '.', ','),
                            $isWinner ? ['style' => "color: #f1c40f;"] : ['style' => "color: #bdc3c7;"]
                        ),
                        $isWinner ? ['style' => "margin: 0; font-size: 1.2rem;"] : ['style' => "margin: 0; font-size: 1.1rem; color: #bdc3c7;"]
                    )
                    . VH::img(
                        [
                            'src' => "images/coin.png",
                            'alt' => "SUDOKU coin image",
                            'style' => $isWinner ? "height: 32px; filter: drop-shadow(0 0 8px #f1c40f); margin-bottom: 4px;" : "height: 28px; filter: grayscale(1) opacity(0.5); margin-bottom: 2px;"
                        ]
                    ),
                    $isWinner
                        ? ['style' => "display: flex; align-items: center; justify-content: center; gap: 10px; font-size: 1.3rem;"]
                        : ['style' => "display: flex; align-items: center; justify-content: center; gap: 8px; font-size: 1.2rem;"]
                )
                . VH::p(
                    $isWinner ? T::S('goes to you') : T::S('is taken by the opponent'),
                    ['style' => $isWinner ? "margin: 5px 0 0 0; color: #bdc3c7; font-size: 1rem;" : "margin: 5px 0 0 0; color: #e74c3c; font-weight: 500; font-size: 1rem;"]
                ),
                $isWinner
                    ? ['style' => "background: rgba(255,255,255,0.05); padding: 15px; border-radius: 10px; margin-bottom: 20px; display: flex; flex-direction: column; align-items: center;"]
                    : ['style' => "background: rgba(231, 76, 60, 0.05); padding: 15px; border-radius: 10px; border: 1px dashed rgba(231, 76, 60, 0.3); margin-bottom: 20px; display: flex; flex-direction: column; align-items: center;"]
            )
            . VH::div(
                $isWinner
                    ? VH::p(
                    T::S('start_new_game'),
                    ['style' => "color: #3498db; font-weight: bold; margin-bottom: 5px;"]
                )
                    : (VH::p('Не сдавайтесь!', ['style' => "color: #f39c12; font-weight: bold; margin-bottom: 5px;"])
                    . VH::p('Попробуйте отыграться прямо сейчас', ['style' => "opacity: 0.7; margin-bottom: 15px;"])),
                ['style' => "font-size: 0.95rem; line-height: 1.6;"]
            );
    }

    protected function endOfFishki()
    {
        $maxres = 0;
        $userWinner = 0;
        foreach ($this->gameStatus['users'] as $num => $user) {
            if ($maxres <= $user['score']) {
                $maxres = $user['score'];
                $userWinner = $num;
            }
        }

        foreach ($this->gameStatus['users'] as $num => $user) {
            if ($num != $userWinner) {
                $this->addToLog(
                    T::S("is left without any pieces! Winner - ")
                    . $this->gameStatus['users'][$userWinner]['username']
                    . T::S(" with score ") . $this->gameStatus['users'][$userWinner]['score'],
                    $num
                );
            } else {
                $this->addToLog(
                    T::S(
                        "is left without any pieces! You won with score "
                    ) . $this->gameStatus['users'][$userWinner]['score'],
                    $num
                );
            }
        }

        return $this->gameStatus['users'][$userWinner]['ID'];
    }

    protected function lost3TurnsWinner($numLostUser, bool $pass = false): string
    {
        $maxres = 0;
        $userWinner = 0;
        if ($this->gameStatus['users'][$numLostUser]['score'] === 0) {
            $this->gameStatus['users'][$numLostUser]['isActive'] = false;
        }

        foreach ($this->gameStatus['users'] as $num => $user) {
            if (($maxres <= $user['score']) && $user['isActive'] && !($pass && $num == $numLostUser)) {
                $maxres = $user['score'];
                $userWinner = $num;
            }
        }

        $this->addToLog(
            (
            $pass
                ? T::S('gave up! Winner - ')
                : T::S("skipped 3 turns! Winner - ")
            )
            . $this->gameStatus['users'][$userWinner]['username']
            . T::S(" with score ")
            . $this->gameStatus['users'][$userWinner]['score'],
            $numLostUser
        );

        return $this->gameStatus['users'][$userWinner]['ID'];
    }

    protected function nextTurn()
    {
        foreach ($this->gameStatus['users'] as $numUser => $user) {
            // Дали всем игрокам статус - другойХодит
            $this->updateUserStatus(self::OTHER_TURN_STATUS, $user['ID']);
        }

        $isActiveUserFound = false;

        $i = 0;

        while (!$isActiveUserFound && is_array($this->gameStatus['users']) && count($this->gameStatus['users']) > $i) {
            $nextActiveUser = ($this->gameStatus['activeUser'] + 1) % count($this->gameStatus['users']);
            $this->gameStatus['activeUser'] = $nextActiveUser;
            if ($this->gameStatus['users'][$nextActiveUser]['isActive']) {
                $isActiveUserFound = true;
            }

            $i++;
        }

        if (!$isActiveUserFound) {
            if (count($this->gameStatus['users'] ?? null)) {
                $nextActiveUser = $this->numUser;
                $this->gameStatus['activeUser'] = $nextActiveUser;
            } else {
                $this->storeGameResults($this->User);
                $this->addToLog(T::S('is the only one left in the game - Victory!'), $this->numUser);

                return; // todo что делать если нет ни одного юзера - заканчиваем игру
            }
        }

        $this->updateUserStatus(self::MY_TURN_STATUS, $this->gameStatus['users'][$nextActiveUser]['ID']);
        //Прописали статус новому активному пользователю

        $nextPreMyTurnUser = ($nextActiveUser + 1) % count($this->gameStatus['users']);

        $this->updateUserStatus(self::PRE_MY_TURN_STATUS, $this->gameStatus['users'][$nextPreMyTurnUser]['ID']);
        //Прописали статус следующему преМайТерн-юзеру

        $this->gameStatus['turnNumber']++;

        $this->gameStatus['turnBeginTime'] = date('U');
        $this->gameStatus['aquiringTimes'][$this->gameStatus['turnNumber']] = false;
    }

    public function exitGame($numuser = false, $commitState = true) // todo проверить, кто вызывает этот метод
    {
        if (!$numuser) {
            $numuser = $this->numUser;
        }

        Cache::del($this->Queue::GET_GAME_KEY . $this->gameStatus['users'][$numuser]['ID']);
        //Удалили указатель на текущую игру для пользователя

        $this->gameStatus['users'][$numuser]['isActive'] = false;
        //Игрок стал неактивен

        $this->addToLog(T::S('left game'), $numuser);

        if ($commitState) {
            $this->destruct();
        }
    }

    public function newGame() // todo проверить, кто вызывает этот метод
    {
        /** todo
         * Нужно доделать - уведомление остальных игроков,
         * поражение пользователю,
         * игра идет без ушедшего пользователя
         */

        $this->Queue::cleanUp($this->User);


        if ($this->currentGame && (in_array($_REQUEST['gameState'] ?? '', self::INIT_STATES))) {
            return $this->checkGameStatus();
            //Пользователь думает, что находится в подборе игры, но игра уже началась
        }

        Cache::del($this->Queue::GET_GAME_KEY . $this->User);
        //Удалили указатель на текущую игру для пользователя

        if ($this->currentGame) {
            $this->gameStatus['users'][$this->numUser]['isActive'] = false;
            //Игрок стал неактивен
            $this->addToLog(T::S('left game'), $this->numUser);
        }

        return (new $this->Queue($this->User, $this, $_POST))->chooseGame();
    }

    protected function makeWishWinscore(): int
    {
        $korzinaGolosov = [];
        foreach ($this->gameStatus['users'] as $user) {
            if (isset($user['wishOchkiNum'])) {
                $korzinaGolosov[] = $user['wishOchkiNum'];
            }
        }

        if (!count($korzinaGolosov)) {
            return $this->winScore;
        }

        $srednee = array_sum($korzinaGolosov) / count($korzinaGolosov);
        $variants = [];

        foreach (self::OCHKI_VARIANTS as $ochki => $delta) {
            $variants[$ochki] = abs($srednee - rand($ochki - 30, $ochki + 10));
        }

        asort($variants);

        foreach ($variants as $ochki => $delta) {
            return $ochki;
        }
        //Голосование проведено

        return 300;
    }

    protected function makeWishTime(): int
    {
        $korzinaGolosov = [];
        foreach ($this->gameStatus['users'] as $user) {
            if (isset($user['wishTurnTime'])) {
                $korzinaGolosov[] = $user['wishTurnTime'];
            }
        }

        if (!count($korzinaGolosov)) {
            return (is_array($this->turnTime) ? $this->turnTime[count($this->currentGameUsers)] : $this->turnTime);
        }

        $srednee = array_sum($korzinaGolosov) / count($korzinaGolosov);

        $variants = [];

        foreach (self::TIME_VARIANTS as $vremya => $delta) {
            $variants[$vremya] = abs($srednee - $vremya);
        }

        asort($variants);

        foreach ($variants as $vremya => $delta) {
            return $vremya;
        }
        //Голосование проведено

        return 90;
    }

    public function gameStarted($statusUpdateNeeded = false)
    {
        if ($statusUpdateNeeded) {
            $firstTurnUser = rand(0, count($this->currentGameUsers) - 1);
            $this->gameStatus['gameNumber'] = $this->currentGame;
            $this->gameStatus['users'][$firstTurnUser]['status'] = self::MY_TURN_STATUS;
            $this->gameStatus['activeUser'] = $firstTurnUser;
            $this->gameStatus['gameBeginTime'] = date('U');
            $this->gameStatus['turnBeginTime'] = $this->gameStatus['gameBeginTime'];
            $this->gameStatus['turnTime'] = $this->makeWishTime();
            $this->gameStatus['turnNumber'] = 1;
            $this->gameStatus['firstTurnUser'] = $firstTurnUser;
            $this->gameStatus['bankFishki'] = $this->gameStatus['lngClass']::generateBankFishki();
            $this->gameStatus['wordsAccepted'] = [];
            $this->gameStatus['winScore'] = $this->makeWishWinscore();
            $this->gameStatus['aquiringTimes'][$this->gameStatus['turnNumber']] = false;
            $this->updateUserStatus(self::MY_TURN_STATUS, $this->currentGameUsers[$firstTurnUser]);
            //Назначили ход случайному юзеру

            $ost = ($firstTurnUser - 1) % count($this->gameStatus['users']);
            if ($ost >= 0) {
                $preMyTurnUser = $ost;
            } else {
                $preMyTurnUser = count($this->gameStatus['users']) + $ost;
            }
            $this->updateUserStatus(self::PRE_MY_TURN_STATUS, $this->currentGameUsers[$preMyTurnUser]);
            //Вычислили игрока, идущего за первым и дали ему статус преМайТерн

            foreach ($this->gameStatus['users'] as $num => $user) {
                $this->gameStatus['users'][$num]['fishki'] = $this->giveFishki(7);
                //Раздали фишки игрокам

                /** @var $hasReceivedTverdZnak bool Признак, что в выданных фишках есть Ъ */
                $hasReceivedTverdZnak = in_array(Ru::TZ_CODE, $this->gameStatus['users'][$num]['fishki']);
                // Сохраняем количество полученных в игре Ъ
                $this->gameStatus['users'][$num][AchievesModel::TVERD_NUM] = (int)$hasReceivedTverdZnak;

                $this->gameStatus['users'][$num]['lostTurns'] = 0;
                $this->gameStatus['users'][$num]['inactiveTurn'] = 1000;
                //Сделали невозможным значение терна инактив

                $this->gameStatus['users'][$num]['common_id'] = PlayerModel::getPlayerID($user['ID'], true);
                $this->gameStatus['users'][$num]['rating'] = CommonIdRatingModel::getRating(
                    $this->gameStatus['users'][$num]['common_id'],
                    self::$gameName
                );
            }
            try {
                $this->addToLog(
                    T::S('New game has started!')
                    . ' <br />'
                    . T::S('Get')
                    . ' <strong>' . $this->gameStatus['winScore']
                    . '</strong> '
                    . T::S('score points')
                    . ($this->gameStatus['bid']
                        ? ('<br>' . T::S('Number of coins on the line') . ':<br>' . T::S(
                                '{{sudoku_icon_15}}'
                            ) . ($this->gameStatus['bid'] * count($this->gameStatus['users'])))
                        : '')
                );
            } catch (Throwable $e) {
                print $e->__toString();
            }
        }

        return $this->makeResponse(
            [
                'gameState' => $this->getUserStatus(),
                'usersInfo' => $this->currentGameUsers, //хз зачем этот ключ - нигде не используется
            ]
        );
    }

    protected function processInvites(&$arr)
    {
        $gameSubState = $this->gameStatus['invite'];

        if ($this->gameStatus['invite'] == 'newGameStarting') {
            $gameSubState .= rand(1, 100);
            $arr['gameSubState'] = $gameSubState;
            $arr['inviteStatus'] = 'newGameStarting';

            $this->exitGame($this->numUser);

            /** @var $Queue Queue */

            (new $this->Queue($this->User, $this, ['lang' => ($this->gameStatus['lang'] == 'EN' ? 'EN' : '')]))
                ->storePlayerToInviteQueue($this->User);

            return;
        }

        $numActiveUsers = 0;
        foreach ($this->gameStatus['users'] as $user) {
            if ($user['isActive'] && isset($user['lastActiveTime']) && $user['ID'] != $this->User) {
                $numActiveUsers++;
            } elseif (isset($this->gameStatus['invite_accepted_users'][$user['ID']]) && $user['ID'] != $this->User) {
                $numActiveUsers++;
            }
        }
        $gameSubState .= $numActiveUsers;
        if (!(isset($arr['inviteStatus']) && $arr['inviteStatus'] == 'newGameStarting')) {
            $arr['comments'] = $arr['comments'] ?? '';
            if ($this->gameStatus['invite'] == $this->User) {
                $arr['comments'] .= VH::div(
                    VH::p(T::S("Asking for adversaries' approval."), ['style' => "opacity: 0.8; margin-bottom: 5px;"])
                    . VH::p(
                        T::S('Remaining in the game:') . VH::span(" $numActiveUsers", ['style' => "color: #fff;"]),
                        ['style' => "font-size: 0.95rem; line-height: 1.6;"]
                    )
                );

                $arr['inviteStatus'] = 'waiting';
            } else {
                if ($numActiveUsers) {
                    $arr['comments'] .= VH::div(
                        VH::p(
                            T::S("You got invited for a rematch! - Accept?"),
                            ['style' => "opacity: 0.8; margin-bottom: 5px;"]
                        )
                    );
                } else {
                    $arr['comments'] .= VH::div(
                        VH::p(T::S('All players have left the game'), ['style' => "opacity: 0.8; margin-bottom: 5px;"])
                    );
                }
                $arr['inviteStatus'] = 'deciding';
            }
        }

        $arr['active_users'] = $numActiveUsers;
        $arr['gameSubState'] = $gameSubState . $arr['inviteStatus'] . $arr['active_users'];
    }

    public function makeResponse(array $arr)
    {
        if (!isset($arr['gameState'])) {
            return json_encode($arr, JSON_UNESCAPED_UNICODE);
        }

        $commonId = $this->gameStatus['users'][$this->numUser]['common_id'] ?? PlayerModel::getPlayerID(
            $this->User,
            true
        );

        $arr = array_merge(
            $arr,
            [
                'common_id' => $commonId,
                'common_id_hash' => PayController::getCommonIdHash($commonId),
            ]
        );

        if (isset($this->gameStatus[$this->User])) {
            if (isset($this->statusComments[$arr['gameState']])) {
                $arr = array_merge(
                    $arr,
                    ['comments' => call_user_func([$this, 'statusComments_' . $arr['gameState']])]
                );
            }

            // Возвращаем Десинк без сохранения состояния игры - разлочка в __destruct
            if ($arr['gameState'] == 'desync') {
                return json_encode($arr, JSON_UNESCAPED_UNICODE);
            }

            if (is_array($this->gameStatus['users'][$this->numUser]['fishki'])) {
                if (count($this->gameStatus['users'][$this->numUser]['fishki'])) {
                    $arr = array_merge($arr, ['fishki' => $this->gameStatus['users'][$this->numUser]['fishki']]);
                    $arr = array_merge($arr, ['num_bank_fishki' => count($this->gameStatus['bankFishki'])]);
                }
            }

            if (isset($this->gameStatus['activeUser'])) {
                $userNames_arr[$this->numUser] = $this->gameStatus['users'][$this->numUser]['username'];
                $score_arr[$this->numUser] = $this->gameStatus['users'][$this->numUser]['score'];
                $score = T::S(
                        "Your score"
                    ) . $this->gameStatus['users'][$this->numUser]['score'] . '/' . $this->gameStatus['winScore'];
                for ($i = 1; $i < count($this->gameStatus['users']); $i++) {
                    $nextUserNum = ($this->numUser + $i) % count($this->gameStatus['users']);
                    $score_arr[$nextUserNum] = $this->gameStatus['users'][$nextUserNum]['score'];

                    $userNames_arr[$nextUserNum] = ($this->gameStatus['users'][$nextUserNum]['isActive'] ? $this->gameStatus['users'][$nextUserNum]['username'] : '');
                    //Если игрок неактивен (слился, пропуски ходов), то его имя='' для фронта

                    $score .= ' ' . $this->gameStatus['users'][$nextUserNum]['username'] . ':' . $this->gameStatus['users'][$nextUserNum]['score'];
                }

                $arr = array_merge($arr, ['score' => $score]);
                $arr = array_merge($arr, ['score_arr' => $score_arr]);
                $arr = array_merge($arr, ['activeUser' => $this->gameStatus['activeUser']]);
                $arr = array_merge($arr, ['yourUserNum' => $this->numUser]);
                $arr = array_merge($arr, ['userNames' => $userNames_arr]);
                $arr = array_merge($arr, ['userInfo' => $this->User]);
                $arr = array_merge($arr, ['gameNumber' => $this->gameStatus['gameNumber']]);
                $arr = array_merge($arr, ['winScore' => $this->gameStatus['winScore']]);
                $arr = array_merge($arr, ['lang' => T::$lang]);
            }

            if (isset($this->gameStatus['bid']) && $arr['gameState'] !== self::CHOOSE_GAME_STATUS) {
                $bid = $this->gameStatus['bid'] ?: 0;
                $bank = $bid * count($this->gameStatus['users']);
                $arr = array_merge(
                    $arr,
                    [
                        'bid' => $bid,
                        'bank' => $bank,
                        'bank_string' => $bank < 1000
                            ? $bank
                            : ($bank < 1000000
                                ? ((string)(round($bank / 1000)) . 'K')
                                : ((string)(round($bank / 1000000)) . 'M'))
                    ]
                );
            }

            if ($this->gameStatus['aquiringTimes'][$this->gameStatus['turnNumber']] > 0) {
                $turnTimeLeft = ($this->gameStatus['aquiringTimes'][$this->gameStatus['turnNumber']] + $this->gameStatus['turnTime']) - date(
                        'U'
                    );
            } else {
                $turnTimeLeft = ($this->gameStatus['turnBeginTime'] + $this->gameStatus['turnTime']) - date('U');
            }

            if ($turnTimeLeft > 0) {
                $turnMinutesLeft = floor($turnTimeLeft / 60);
                $turnSecondsLeft = $turnTimeLeft % 60;
                if ($turnSecondsLeft < 10) {
                    $turnSecondsLeft = '0' . $turnSecondsLeft;
                }
                $turnTimeLeft = $turnMinutesLeft . ':' . $turnSecondsLeft;
            } else {
                $turnTimeLeft = '0:00';
                $turnSecondsLeft = 0;
                $turnMinutesLeft = 0;
            }

            $turnTimeLeft = T::S("Turn time") . ' ' . $turnTimeLeft;

            $arr = array_merge(
                $arr,
                [
                    'timeLeft' => $turnTimeLeft,
                    'secondsLeft' => (int)$turnSecondsLeft,
                    'minutesLeft' => $turnMinutesLeft
                ]
            );

            if ($arr['gameState'] == self::MY_TURN_STATUS) {
                $arr = array_merge($arr, ['turnTime' => $this->gameStatus['turnTime']]);
            }

            if (!isset($arr['desk']) && in_array(
                    $arr['gameState'],
                    [
                        self::MY_TURN_STATUS,
                        self::PRE_MY_TURN_STATUS,
                        self::OTHER_TURN_STATUS,
                        self::START_GAME_STATUS
                    ]
                )) {
                $arr['desk'] = $this->gameStatus['lngClass']::init_desk();
            }

            if (count($this->gameStatus['users']) === 2) {
                $numOpponent = $this->numUser === 1 ? 0 : 1;
                $arr['is_opponent_active'] = (!isset($this->gameStatus['users'][$numOpponent]['lastActiveTime']) || !$this->gameStatus['users'][$numOpponent]['isActive']) ? false : true;
            }

            if ($arr['gameState'] == self::GAME_RESULTS_STATE && isset($this->gameStatus['invite'])) {
                $this->processInvites($arr);
            }

            if (isset($this->gameStatus['users'][$this->numUser]['logStack'])) {
                if (count($this->gameStatus['users'][$this->numUser]['logStack'])) {
                    $log = [];
                    while ($logRecord = array_shift($this->gameStatus['users'][$this->numUser]['logStack'])) {
                        $log[] = trim(
                            ($logRecord[0] !== false ? $this->gameStatus['users'][$logRecord[0]]['username'] : '') . ' ' . $logRecord[1]
                        );
                    }

                    $arr = array_merge($arr, ['log' => $log]);
                    //Добавили лог событий в ответ юзеру
                }
            }
            if ($arr['gameState'] != self::ADD_TO_CHAT_STATE) {
                if (isset($this->gameStatus['users'][$this->numUser]['chatStack'])) {
                    if (count($this->gameStatus['users'][$this->numUser]['chatStack'])) {
                        $log = [];
                        while ($logRecord = array_shift($this->gameStatus['users'][$this->numUser]['chatStack'])) {
                            $log[] = trim(
                                ($logRecord[0] !== false
                                    ? (isset($this->gameStatus['users'][$logRecord[0]])
                                        ? $this->gameStatus['users'][$logRecord[0]]['username']
                                        : $logRecord[0])
                                    : '')
                                . ' '
                                . $logRecord[1]
                            );
                        }
                        $arr = array_merge($arr, ['chat' => $log]);
                        //Добавили лог чата в ответ юзеру
                    }
                }
            }

            if (!isset($arr['gameSubState'])) {
                $arr = array_merge($arr, ['gameSubState' => $this->gameStatus['turnNumber']]);
            }
        } elseif (isset($this->statusComments[$arr['gameState']])) {
            $arr = array_merge($arr, ['comments' => call_user_func([$this, 'statusComments_' . $arr['gameState']])]);
            //Игра еще не создана, но комменты для статуса раздаем
        }

        $this->destruct();
        //Сохранили статус игры

        if (isset($_GET['queryNumber'])) {
            return json_encode(array_merge($arr, ['query_number' => $_GET['queryNumber']]), JSON_UNESCAPED_UNICODE);
        } else {
            return json_encode($arr, JSON_UNESCAPED_UNICODE);
        }
    }

    protected function isUserInCabinet()
    {
        $cabinetKeys = [
            'oldKey',
            'MAX_FILE_SIZE',
            'url',
            'keyForID',
            'key',
            'name',
            'players_count',
            'ochki_num',
            'turn_time',
            'from_rating',
            'gameState',
            '12',
            12
        ];

        foreach ($cabinetKeys as $key) {
            if (isset($_REQUEST[$key])) {
                return true;
            }
        }

        return false;
    }

    protected function isBot(): bool
    {
        return !(strstr($this->User, self::BOT_TPL) === false);
    }

    public static function isBotStatic(string $User): bool
    {
        return !(strstr($User, self::BOT_TPL) === false);
    }

    protected function getBotFishkiToChange(): array
    {
        $fishkiToChange = [];

        $fishki = $this->gameStatus['users'][$this->numUser]['fishki'];

        foreach ($fishki as $num => $fishka) {
            if ($fishka < 999) {
                $fishkiToChange["fishka_{$num}_$fishka"] = 'on';
            }
        }

        return $fishkiToChange;
    }

    private function getNumPlayerByCommonId(int $commonId): ?int
    {
        foreach ($this->gameStatus['users'] as $num => $user) {
            if ($user['common_id'] === $commonId) {
                return $num;
            }
        }

        return null;
    }

    /** Подсчитывает количество Ъ в составленных словах */
    private static function checkTZCount($words): int
    {
        return mb_substr_count(implode('', array_keys((array)$words)), 'ъ');
    }

}

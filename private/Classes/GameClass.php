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
    const GAME_RESULTS_STATUS = 'gameResults';

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

        T::$lang = $_GET['lang'] ?? T::RU_LANG;

        $this->User = $this->validateCookie($_COOKIE['erudit_user_session_ID']);

        $this->commonId = Tg::$commonId // авторизован через Телеграм или...
            ?? PlayerModel::getPlayerID($this->User, true);

        // Если не удалось дождаться лока по текущему игроку, то посылаем ошибку и выходим
        if(!Cache::waitLock($this->User, true)) {
            BadRequest::sendBadRequest(
                ['err_msg' => 'lock error'],
                $this->isBot()
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
                    if (isset($_GET['queryNumber']) && $_GET['queryNumber'] < ($this->gameStatus['users'][$this->numUser]['last_request_num'] ?? 0)) {
                        throw new BadRequest('Num packet error when returned from page_hidden state');
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
                        'message' => 'Потеря синхронизации с сервером',
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
                Cache::rpush(static::GAMES_ENDED_KEY, $this->gameStatus);
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

    public function onlinePlayers()
    {
        if (!($rangedOnlinePlayers = Cache::get(static::NUM_RATING_PLAYERS_KEY))) {
            $lastGame = Cache::get($this->Queue::GAMES_COUNTER);
            $players = [];
            for ($i = $lastGame; $i > ($lastGame - 50); $i--) {
                if ($game = Cache::get(static::GAME_STATUS_KEY . $i)) {
                    if (!isset($game['results'])) {
                        foreach ($game['users'] as $num => $user) {
                            if (!isset($user['ID'])) {continue;}
                            if (strstr($user['ID'], 'botV3#') === false) {
                                $players[$user['ID']] = [
                                    'cookie' => $user['ID'],
                                    'userID' => (isset($user['userID']) ? $user['userID'] : false)
                                ];
                            }
                        }
                    }
                }
            }

            $rangedOnlinePlayers = [
                0 => 0,
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

            foreach ($players as $num => $player) {
                $rangedOnlinePlayers[0]++;

                if (($rating = $this->getRatings($player))) {
                    $players[$num]['rating'] = $rating;

                    if ($rating['rating'] > 1900) {
                        $rangedOnlinePlayers[1900]++;
                    }
                    if ($rating['rating'] > 2000) {
                        $rangedOnlinePlayers[2000]++;
                    }
                    if ($rating['rating'] > 2100) {
                        $rangedOnlinePlayers[2100]++;
                    }
                    if ($rating['rating'] > 2200) {
                        $rangedOnlinePlayers[2200]++;
                    }
                    if ($rating['rating'] > 2300) {
                        $rangedOnlinePlayers[2300]++;
                    }
                    if ($rating['rating'] > 2400) {
                        $rangedOnlinePlayers[2400]++;
                    }
                    if ($rating['rating'] > 2500) {
                        $rangedOnlinePlayers[2500]++;
                    }
                    if ($rating['rating'] > 2600) {
                        $rangedOnlinePlayers[2600]++;
                    }
                    if ($rating['rating'] > 2700) {
                        $rangedOnlinePlayers[2700]++;
                    }
                }
            }

            Cache::setex(
                static::NUM_RATING_PLAYERS_KEY,
                $this->ratingsCacheTimeout,
                $rangedOnlinePlayers
            );
        }

        if ($rangedOnlinePlayers[1900]) {
            $cnt = Cache::hlen($this->Queue::QUEUES['erudit.rating_waiters']);
            if ($cnt < ($rangedOnlinePlayers[1900] / 2)) {
                $thisUserRating = $this->getRatings(['cookie' => $this->User, 'userID' => false]);
                if (($thisUserRating !== false) && ($thisUserRating['rating'] > 1750)) {
                    $rangedOnlinePlayers['waiters_count'] = $cnt;
                    $rangedOnlinePlayers['thisUserRating'] = $thisUserRating['rating'];
                    return $rangedOnlinePlayers;
                }
            }
        }

        return [];
    }

    protected function validateCookie($incomingCookie)
    {
        if (Tg::authorize()) {
            if (Tg::$tgUser) {
                return Tg::$tgUser['user']['id'];
            }
        }

        if (strpos($incomingCookie, 'bot') !== false) {
            return $incomingCookie;
        } elseif (!isset($_SERVER['HTTP_COOKIE'])) {
            return ($sintCookie = (md5($_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT'])));
        } elseif (stristr($_SERVER['HTTP_COOKIE'], 'erudit_user_session_ID') === false) {
            return ($sintCookie = (md5($_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT'])));
        }

        return $incomingCookie;
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
        $this->addToLog("Закрыл вкладку с игрой", $this->numUser);

        return $this->makeResponse(
            ['gameState' => self::ADD_TO_CHAT_STATE, 'message' => 'Вы закрыли вкладку с игрой и стали Неактивным!' . $this->numUser]
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
            return $this->makeResponse(['gameState' => $this->getUserStatus(), 'message' => 'Запрос отклонен. Игра еще продолжается.']);
        }

        // Если игрок решил сдаться и отправить вызов на реванш
        if(in_array($this->getUserStatus(), self::IN_GAME_STATUSES)) {
            $this->storeGameResults($this->lost3TurnsWinner($this->numUser, true));
        }

        $this->gameStatus['invite_accepted_users'][$this->User] = $this->User;

        if (!isset($this->gameStatus['invite'])) {
            $this->gameStatus['invite'] = $this->User;
            $message = 'Запрос на новую игру отправлен';
        } elseif ($this->gameStatus['invite'] == $this->User) {
            $message = 'Ваш запрос на новую игру ожидает ответа игроков';
        } else {
            $message = 'Запрос принят! Начинаем новую игру';
            $this->gameStatus['invite'] = 'newGameStarting';
        }

        $inviteStatus = ['inviteStatus' => $this->gameStatus['invite']];

        return $this->makeResponse(array_merge(['gameState' => self::GAME_RESULTS_STATUS, 'message' => $message], $inviteStatus));
    }

    public function playerCabinetInfo()
    {
        $message = [];

        /*1.3. Рейтинг, Место в рейтинге

        Имеются сейчас в базе, но в кабинет не передается. Добавим позже.
        Нужно отобразить любой рейтинг, любое место.
        Обработку ответа сервера потом сделаем. - добавить в ответе сервера при вызове профиля

        1.4. Баланс, Рейтинг по монетам - пока нет в базе,
        предусмотреть поля для вывода данных из базы - добавить в ответ сервера рандомные фейковые данные

        3 Описание блока "Рефералы"
        Пока в разработке, фразу об этом включаем.
        Предусмотреть отражение множества строк по кол-ву фактических рефералов текущего пользователя,
        одна строка = один реферал с его именем и бонусом.
        - выводим массив фейковых рефов (3 штуки)
        */
        /*ini_set("display_errors", 1);
        error_reporting(E_ALL);*/
        $message['summary'] = [];
        $message['summary']['rating'] = CommonIdRatingModel::getRating($this->commonId);
        $message['summary']['top'] = CommonIdRatingModel::getTopByRating($message['summary']['rating']);
        $message['summary']['SUDOKU_BALANCE'] = 100500;
        $message['summary']['SUDOKU_TOP'] = 365;

        $message['refs'] = [['Peter Pervyy', 10], ['Nickolay Vtoroy', 10], ['Aleksey Tretiy', 10]];

        $message['common_id'] = $this->commonId;
        // нахуй
        // $this->gameStatus['users'][$this->numUser]['common_id'] ?? PlayerModel::getPlayerID($this->User, false);

        $userData = UserModel::getOne($message['common_id']);

        $message['url'] = $userData['avatar_url'] ?? false;
        if (!$message['url']) {
            $message['url'] = AvatarModel::getDefaultAvatar($message['common_id']);
            $message['img_title'] = "Используется аватар по умолчанию";
        } else {
            $message['img_title'] = "Аватар по предоставленной ссылке";
        }
        $message['name'] = $userData['name'] ?? '';

        $message['text'] = '';
        $message['form'][] = [
            'prompt' => "Никнейм (id: {$message['common_id']})",
            'inputName' => 'name',
            'inputId' => 'player_name',
            'onclick' => 'savePlayerName',
            'buttonCaption' => 'Задать',
            'placeholder' => 'новый Ник'
        ];
        $message['form'][] = [
            'prompt' => '',
            'type' => 'hidden',
            'inputName' => 'cookie',
            'value' => $this->User,
        ];
        $message['form'][] = [
            'prompt' => '',
            'type' => 'hidden',
            'inputName' => 'MAX_FILE_SIZE',
            'value' => Players::MAX_UPLOAD_SIZE,
        ];
        $message['form'][] = Hints::IsNotAndroidApp()
            ? [
                'prompt' => 'Загрузка Аватара',
                'type' => 'file',
                'inputName' => 'url',
                'inputId' => 'player_avatar_file',
                'onclick' => 'savePlayerAvatar',
                'buttonCaption' => 'Отправить',
                'required' => true,
            ]
            : [
                'prompt' => 'URL аватара',
                'inputName' => 'url',
                'inputId' => 'player_avatar_url',
                'onclick' => 'savePlayerAvatarUrl',
                'buttonCaption' => 'Применить',
                'placeholder' => 'https://'
            ];

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

    public function mergeTheIDs($encryptedMessage, $commonID)
    {
        $secretKey = Config::$envConfig['SALT'];
        $method = 'AES-128-CBC';
        $iv = base64_decode(Config::$envConfig['IV'] . '==');
        $decrypted_message = openssl_decrypt($encryptedMessage, $method, $secretKey, 0, $iv);

        if (!is_numeric($decrypted_message)) {
            return json_encode(
                [
                    'result' => 'error_decryption' . ' ' . $decrypted_message,
                    'message' => 'Ошибка расшифровки ключа'
                ]
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
                    'message' => 'ID игрока по ключу НЕ найден'
                ]
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
                    'message' => 'Учетные записи связаны'
                ]
            );
        } else {
            return json_encode(
                [
                    'result' => 'error_update ' . $oldCommonID . '->' . $commonID,
                    'message' => 'Аккаунты уже связаны'
                ]
            );
        }
    }

    public function playersInfo()
    {
        $ratings = $this->getRatings(); // todo рейтинги получать через модель CommonIdRating
        $message = include($this->dir . '/tpl/ratingsTableHeader.php'); // todo переделать include на классы - а то не работает так
        if (!isset($this->gameStatus['users'])) {
            return $this->makeResponse(['message' => "Игра не начата"]);
        }

        $commonId = $this->gameStatus['users'][$this->numUser]['common_id'];
        $thisPlayerHasBanned = BanModel::hasBanned($commonId);

        foreach ($this->gameStatus['users'] as $num => $user) {
            $deltaRating = PlayerModel::getDeltaRating($user['common_id']);

            $ratingFound = false;
            foreach ($ratings as $rating) {
                if ($user['ID'] == $rating['cookie']) {
                    $ratingFound = true;
                    break;
                }
            }

            if (!$ratingFound) {
                $rating['rating'] = '1700 (новый игрок)';
                $rating['games_played'] = 0;
                $rating['win_percent'] = 0;
                $rating['inactive_percent'] = 'N/A';
                $rating['top'] = '';
            }

            $rating['playerName'] = $this->getPlayerName($user);
            $rating['playerAvatarUrl'] = $this->getAvatarUrl($user['ID']);
            $rating['isActive'] = (!isset($user['lastActiveTime']) || !$user['isActive']) ? false : true;

            $canDeleteBan = false;
            if ($user != $this->User) {
                $currentUserCommonId = PlayerModel::getCommonID($user['ID']);
                if ($currentUserCommonId && !empty($thisPlayerHasBanned[$currentUserCommonId])) {
                    $canDeleteBan = true;
                }
            }

            $message .= include($this->dir . '/tpl/ratingsTableRow.php');

            $recImgs = '';
            $records = Prizes::playerCurrentRecords($user['ID']);
            $recordsShown = 0;
            foreach ($records as $record) {
                // todo переделать на ViewHelper
                $recImgs .= "<img 
								style=\"
									cursor: pointer; 
									margin-left: " . ($recordsShown ? -20 : 0) . "px; padding: 0;
									margin-top: -10px;
									z-index: 50;
								\" 
								title=\"Кликните для увеличения изображения\" 
								id=\"{$record['type']}\" 
								onclick=\"showFullImage('{$record['type']}', 500, 100);\" 
								src=\"/{$record['link']}\" width=\"100px\" />";
                $recordsShown++;
                if ($recordsShown >= 3) {
                    break;
                }
            }

            if ($user['ID'] == $this->User) {
                $message .= include($this->dir . '/tpl/ratingsTableNicknameFormRow.php');
            }

            if ($recordsShown) {
                $message .= include($this->dir . '/tpl/ratingsTablePrizesRow.php');
            }
        }
        $message .= include($this->dir . '/tpl/ratingsTableFooter.php');

        return $this->makeResponse(['message' => $message]);
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
        $message = 'Отправлена жалоба';

        if (count($this->gameStatus['users']) > 2 && $toNumUser == 'all') {
            return $this->makeResponse(['message' => 'Жалоба не принята! Пожалуйста, выберите игрока из списка']);
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
            $respMessage = '<span style="align-content: center;"><strong>Ваше обращение принято и будет рассмотрено модератором<br /><br /> В случае подтверждения к игроку будут применены санкции</strong></span>';
            $isSendSuccess = true;
        } else {
            $respMessage = '<span style="align-content: center;"><strong><span style="color:red;">Ваше обращение НЕ принято!</span><br /><br /> В течение суток можно отправлять только одну жалобу на одного и того же игрока. Всего за сутки не более ' . ComplainModel::COMPLAINS_PER_DAY . '</strong></span>';
        }

        if ($isSendSuccess) {
            $this->gameStatus['chatLog'][] = [$this->numUser, $toNumUser, $message];

            if ($toNumUser == 'all') {
                foreach ($this->gameStatus['users'] as $num => $User) {
                    if ($num == $this->numUser) {
                        $this->gameStatus['users'][$num]['chatStack'][] = ['Вы', 'всем: ' . $message];
                    } else {
                        $this->gameStatus['users'][$num]['chatStack'][] = [
                            "От Игрока" . ($this->numUser + 1) . " (всем):",
                            $message
                        ];
                    }
                }
            } else {
                $this->gameStatus['users'][$toNumUser]['chatStack'][] = [
                    "От Игрока" . ($this->numUser + 1) . ":",
                    $message
                ];
                $this->gameStatus['users'][$this->numUser]['chatStack'][] = [
                    'Вы',
                    'Игроку' . ($toNumUser + 1) . ': ' . $message
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
            $commonIdFrom = PlayerModel::getPlayerID($this->User, true);

            $bannedTill = BanModel::isBannedTotal($commonIdFrom ?: 0);
            if ($bannedTill) {
                if ($needConfirm) {
                    return $this->makeResponse(
                        [
                            'message' => 'Сообщение НЕ отправлено - БАН до ' . date('d.m.Y', $bannedTill),
                            'gameState' => self::ADD_TO_CHAT_STATE
                        ]
                    );
                } else {
                    return true;
                }
            }

            if ($toNumUser == Hints::TYPE_WORDS_QUERY) {
                return $this->makeResponse(
                    Hints::getWordHint($message)
                    + ['gameState' => self::WORD_QUERY_STATE]
                );
            }

            $bannedBy = BanModel::bannedBy($commonIdFrom ?: 0);

            $this->gameStatus['chatLog'][] = [$this->numUser, $toNumUser, $message];
            if ($toNumUser !== 'all' && $toNumUser !== 'adv') {
                {
                }
            }

            if ($toNumUser == 'all') {
                foreach ($this->gameStatus['users'] as $num => $User) {
                    if ($num == $this->numUser) {
                        $this->gameStatus['users'][$num]['chatStack'][] = ['Вы', 'всем: ' . $message];
                    } elseif (!isset($bannedBy[PlayerModel::getCommonID($User['ID']) ?: 0])) {
                        $this->gameStatus['users'][$num]['chatStack'][] = [
                            "От Игрока" . ($this->numUser + 1) . " (всем):",
                            $message
                        ];
                    }
                }
            } elseif ($toNumUser == 'adv') {
                foreach ($this->gameStatus['users'] as $num => $User) {
                    if (!isset($User['userID'])) {
                        $this->gameStatus['users'][$num]['chatStack'][] = ["Новости:", $message];
                    }
                }
            } elseif (!isset($bannedBy[PlayerModel::getCommonID($this->gameStatus['users'][$toNumUser]['ID']) ?: 0])) {
                $this->gameStatus['users'][$toNumUser]['chatStack'][] = [
                    "От Игрока" . ($this->numUser + 1) . ":",
                    $message
                ];
                $this->gameStatus['users'][$this->numUser]['chatStack'][] = [
                    'Вы',
                    'Игроку' . ((int)$toNumUser + 1) . ': ' . $message
                ];
            } elseif ($needConfirm) {
                return $this->makeResponse(
                    [
                        'message' => '<strong>Сообщение НЕ отправлено - БАН от Игрока' . ((int)$toNumUser + 1) . '</strong>',
                        'gameState' => self::ADD_TO_CHAT_STATE
                    ]
                );
            }

            if ($needConfirm) {
                return $this->makeResponse(['message' => 'Сообщение отправлено', 'gameState' => self::ADD_TO_CHAT_STATE]);
            } else {
                return true;
            }
        } catch (\Throwable $e) {
            return $this->makeResponse(['message' => $e->__toString(), 'gameState' => self::ADD_TO_CHAT_STATE]);
        }
    }

    protected function addToLog($message, $numUser = false)
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

    protected function normalizeRatings(&$ratings)
    {
        $result = [];

        foreach ($ratings as $cook => $rate) {
            if (isset($rate[0])) {
                if ($rate[0]['cookie'] !== $cook) {
                    $rate[0]['cookie'] = $cook;
                }
                $result[] = $rate[0];
            }
        }

        return $result;
    }

    protected function getRatingWithCommonID($commonID = false, $cookie = false, $userID = false)
    {
        if (!($ratingInfo = PlayerModel::getRating($commonID, $cookie, $userID))) {
            return false;
        }

        if ($ratingInfo[0]['rating'] >= 1700) {
            // Коррекция для ТОП
            $ratingInfo[0]['top'] = PlayerModel::getTop($ratingInfo[0]['rating']);
        }

        $cacheValues = array_merge(
            $commonID ? [$commonID] : [],
            $cookie ? [$cookie] : [],
            $userID ? [$userID] : []
        );

        PlayerModel::saveRatingToCache($cacheValues, $ratingInfo);

        return $ratingInfo;
    }

    public static function hash_str_2_int($str, $len = 16)
    {
        $hash_int = base_convert("0x" . substr(md5($str), 0, $len), 16, 10);
        return $hash_int;
    }

    public function getRatings($userCookie = false)
    {
        if (!$userCookie) {
            if (isset($this->gameStatus['users'])) {
                foreach ($this->gameStatus['users'] as $user) {
                    $ratings[$user['ID']] = $this->getRatingWithCommonID(
                        $this->getCommonID(
                            $user['ID'],
                            isset($user['userID']) ? self::hash_str_2_int($user['userID']) : false
                        ),
                        $user['ID'],
                        isset($user['userID'])
                            ? self::hash_str_2_int($user['userID'])
                            : false
                    );
                }

                return $this->normalizeRatings($ratings);
            }
        }

        if (!$userCookie) {
            if (!($ratings[$this->User] = PlayerModel::getRatingFromCache($this->User))) {
                $ratings[$this->User] = $this->getRatingWithCommonID(
                    $this->getCommonID(
                        $this->User,
                        false
                    ),
                    $this->User,
                    false
                );
            }

            return $this->normalizeRatings($ratings);
        }

        if (!is_array($userCookie)) {
            $ratings = [];
            $ratings[$userCookie] = false;
            if (!$ratings[$userCookie]) {
                $userID = isset($this->gameStatus) &&
                isset($this->gameStatus[$userCookie]) &&
                isset($this->gameStatus['users'][$this->gameStatus[$userCookie]]['userID'])
                    ? self::hash_str_2_int($this->gameStatus['users'][$this->gameStatus[$userCookie]]['userID'])
                    : false;

                $ratings[$userCookie] = $this->getRatingWithCommonID(
                    $this->getCommonID(
                        $userCookie,
                        $userID
                    ),
                    $userCookie,
                    $userID
                );
            }

            return $this->normalizeRatings($ratings);
        } else {
            $ratings = $this->getRatingWithCommonID(
                $this->getCommonID(
                    $userCookie['cookie'],
                    isset($userCookie['userID']) ? self::hash_str_2_int($userCookie['userID']) : false
                ),
                $userCookie['cookie'],
                isset($userCookie['userID']) ? self::hash_str_2_int($userCookie['userID']) : false
            );

            return count($ratings ?: []) ? $ratings[0] : false;
        }
    }

    protected function statusComments_startGame()
    {
        Stats::saveStats();

        $ratings = $this->getRatings($this->User);

        $this->gameStatus['users'][$this->gameStatus[$this->User]]['rating'] = $ratings ? $ratings[0]['rating'] : self::NEW_PLAYER;

        return 'Новая игра начата! <br />Набери <strong>' . $this->gameStatus['winScore'] . '</strong> очков' . '<br />' . $this->gameStatus['users'][$this->gameStatus['activeUser']]['username'] . ' ходит' . '<br />Ваш текущий рейтинг - <strong>' . $ratings[0]['rating'] . '</strong>';
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
        return 'Синхронизируемся с сервером..'
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
            $ratings = $this->getRatings($this->User);

            $this->gameStatus['users'][$this->gameStatus[$this->User]]['rating'] = $ratings[0]['rating'] ?? self::NEW_PLAYER;

            return 'Ваш ход! <br />Игра до <strong>' . $this->gameStatus['winScore'] . '</strong> очков' . '<br />Ваш текущий рейтинг - <strong>' . $ratings[0]['rating'] . '</strong>';
        } else {
            return $this->gameStatus['users'][$this->numUser]['username'] . ', Ваш ход!'
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
            return '<strong>Подбор игры!</strong> <br />Готово игроков: <strong>' . $this->gamePlayersWaiting . '</strong>';
        } else {
            return '<strong>Подбор игры!</strong> <br />Поиск других игроков';
        }
    }


    protected function statusComments_preMyTurn()
    {
        if ($this->gameStatus['turnNumber'] == 1) {
            $ratings = $this->getRatings($this->User);
            return 'Игра до <strong>' . $this->gameStatus['winScore'] . '</strong> очков<br />Ваш ход следующий - приготовьтесь!' . '<br />Ваш текущий рейтинг - <strong>' . $ratings[0]['rating'] . '</strong>';
        } else {
            return $this->gameStatus['users'][$this->gameStatus['activeUser']]['username'] . ' ходит. <br />Ваш ход следующий - приготовьтесь!'
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

        if (count($fishkiToChange)) {
            $this->addToLog('меняет фишки и пропускает ход', $this->numUser);

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

        foreach ($this->gameStatus['users'] as $user) {
            if ($user['ID'] == $winnerUser) {
                $results['winner'] = $winnerUser;
            } else {
                $results['lostUsers'][] = $user['ID'];
            }
        }

        $this->gameStatus['results'] = $results;
        // Новая версия

        foreach ($this->gameStatus['users'] as $num => $user) {
            $this->updateUserStatus(self::GAME_RESULTS_STATUS, $user['ID']);

            if (
                !empty($user['ID'])
                && strstr($user['ID'], 'botV3#') === false
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

        // todo CLUB-384 testing save rating to CommonIdRatingModel = rating history
        try {
            $resultRatings = RatingService::processGameResult($this->gameStatus);
            foreach ($this->gameStatus['users'] as &$user) {
                $user['result_ratings'] = $resultRatings[$user['common_id']];
            }
        } catch(Throwable $e) {
            Cache::setex(self::LOG_BOT_ERRORS_KEY . 'ratings', 3600, $e->__toString());
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

    public function gameWordsPlayed()
    {
        return $this->gameStatus['wordsAccepted'];
    }

    public function wordChecker()
    {
        if ($this->currentGame === false) {
            return 'Игра еще не начата!';
        }
        $check_statuses = ['Слово не найдено', 'Корректно', 'Слово из одной буквы', 'Повтор'];
        $result = '';
        $desk = Cache::get(static::CURRENT_GAME_KEY . $this->currentGame);
        //Текущая доска
        $cells = json_decode($_POST['cells'], true);
        //Присланная доска

        $new_fishki = $this->gameStatus['lngClass']::submit($cells, $desk, $this->gameStatus);
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
                $result .= $word . ' - стоимость: ' . $price . '<br />';
                $summa += $price;
            }
            if (count($new_fishki['good']) == count($this->gameStatus['users'][$this->numUser]['fishki'])) {
                $summa += 15;
                $result .= '+15 за все фишки ';
            }
        }

        if ($result !== '') {
            $result .= '<strong>ИТОГО: ' . $summa . '</strong>';
        } else {
            $result = 'Вы не составили ни одного слова';
        }

        return json_encode($result, JSON_UNESCAPED_UNICODE);
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
                'пытается сделать ход не в свою очередь (ход #' . $this->gameStatus['turnNumber'] . ')',
                $this->numUser
            );

            return $this->checkGameStatus();
        }

        try {
            $desk = Cache::get(static::CURRENT_GAME_KEY . $this->currentGame);
            // Текущая доска

            $saveDesk = $desk;
            // Сохранили доску

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

            /**
             * @method Ru|Eng submit()
             */
            $new_fishki = $this->gameStatus['lngClass']::submit($cells, $desk, $this->gameStatus);
        } catch (\Throwable $e) {
            BadRequest::logBadRequest(
                [
                    'message' => 'Ошибка обработки данных!',
                    'err_msg' => $e->getMessage(),
                    'err_file' => $e->getFile(),
                    'err_line' => $e->getLine(),
                    'err_context' => $e->getTrace(),
                    'received_desk' => $cells,
                    'game_desk' => $saveDesk
                ]
            );

            $this->addToLog(
                ' - ошибка обработки хода (ход #' . $this->gameStatus['turnNumber'] . ')',
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
                'не составил ни одного слова (ход #' . $this->gameStatus['turnNumber'] . ')',
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
                        if ($fishka === $good_fishka[3]) {
                            unset($new_fishki['good'][$i]);
                            unset($this->gameStatus['users'][$this->numUser]['fishki'][$num]);
                            break;
                        }
                    }
                }
                //Проверяем оставшиеся фишки
                foreach ($this->gameStatus['users'][$this->numUser]['fishki'] as $num => $fishka) {
                    foreach ($new_fishki['good'] as $i => $good_fishka) {
                        if (($fishka === $good_fishka[2]) || (($fishka == 999) && ($good_fishka[2] > 999))) {
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

                $this->gameStatus['users'][$this->numUser]['fishki'] = array_merge(
                    $this->gameStatus['users'][$this->numUser]['fishki'],
                    $addFishki
                );

                foreach ($new_fishki['words'] as $word => $price) {
                    $ochkiZaHod += $price;
                    //Добавили очков за ход
                    $this->gameStatus['wordsAccepted'][$word] = $word;
                    //Добавили слово в список сыгранных слов

                    $arr = Prizes::checkDayWordLenRecord($word, $this->User);
                    foreach ($arr as $period => $value) {
                        $this->addToLog(
                            "устанавливает рекорд по длине слова за $period - <strong>$word</strong>",
                            $this->numUser
                        );
                    }

                    $arr = Prizes::checkDayWordPriceRecord($word, $price, $this->User);
                    foreach ($arr as $period => $value) {
                        $this->addToLog(
                            "устанавливает рекорд по стоимости слова за $period - <strong>$word - $price</strong>",
                            $this->numUser
                        );
                    }
                }
            }

            $this->gameStatus['users'][$this->numUser]['score'] += $ochkiZaHod;
            if ($ochkiZaHod == 0) {
                $this->gameStatus['users'][$this->numUser]['lostTurns']++;
            } else {
                $arr = Prizes::checkDayTurnPriceRecord($ochkiZaHod, $this->User);
                foreach ($arr as $period => $value) {
                    $this->addToLog(
                        "устанавливает рекорд по стоимости хода за $period - <strong>$ochkiZaHod</strong>",
                        $this->numUser
                    );
                }
            }

            $this->addToLog(
                'зарабатывает ' . $ochkiZaHod . ' за ход #' . $this->gameStatus['turnNumber'] . $this->logSlov(
                    $new_fishki['words']
                ) . ($vseFishki ? ' <span title="За все фишки" style="color: green;">(<strong>+15</strong>)</span>' : ''),
                $this->numUser
            );

            if ($this->gameStatus['users'][$this->numUser]['score'] >= $this->gameStatus['winScore']) {
                $this->addToLog(
                    'Побеждает со счетом ' . $this->gameStatus['users'][$this->numUser]['score'],
                    $this->numUser
                );

                $arr = Prizes::checkDayGamePriceRecord($this->gameStatus['users'][$this->numUser]['score'], $this->User);
                foreach ($arr as $period => $value) {
                    $this->addToLog(
                        "устанавливает рекорд набранных очков в игре за $period - <strong>{$this->gameStatus['users'][$this->numUser]['score']}</strong>",
                        $this->numUser
                    );
                }

                $this->storeGameResults($this->User);
                //Обнаружен выигравший
            } elseif ($this->gameStatus['users'][$this->numUser]['lostTurns'] >= 3) {
                $this->storeGameResults($this->lost3TurnsWinner($this->numUser));
                //Обнаружен выигравший
            } elseif (count($this->gameStatus['users'][$this->numUser]['fishki']) === 0) {
                $this->addToLog('закончились фишки - конец игры!', $this->numUser);
                $this->storeGameResults($this->endOfFishki());
            } // Обнаружен выигравший
            elseif ($ochkiZaHod > 0) {
                $this->gameStatus['users'][$this->numUser]['lostTurns'] = 0;
                $this->nextTurn();
            } elseif ($ochkiZaHod == 0) {
                $this->nextTurn();
            }

            if (isset($this->gameStatus['results'])) {
                $arr = Prizes::checkDayGamesPlayedRecord(
                    array_map(
                        function ($user) {
                            return $user['ID'];
                        },
                        $this->gameStatus['users']
                    )
                );

                foreach ($arr as $period => $record) {
                    $this->addToLog(
                        "устанавливает рекорд по числу сыгранных партий за $period - <strong>" . reset(
                            $record
                        ) . "</strong>",
                        $this->gameStatus[key($record)]
                    );
                }
            }
        } catch (\Throwable $e) {
            BadRequest::sendBadRequest(
                [
                    'message' => 'Ошибка обработки данных!',
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
            Cache::hset(
                self::BAD_COMBINATIONS_HSET,
                microtime(true),
                [
                    'new_fishki' => $new_fishki,
                    'old_cells' => json_decode($_POST['cells'], true),
                    'old_desk' => $saveDesk,
                    'new_desk' => $cells,
                    'saved_words' => $saveWords,
                    'new_played_words' => $new_fishki['words'] ?? [],
                ]
            );
        } else {
            Cache::setex(static::CURRENT_GAME_KEY . $this->currentGame, $this->cacheTimeout, $cells);
            //Измененная Присланная доска -> текущая
        }

        $this->destruct();
        //Сохранили статус игры

        print json_encode(
            array_merge(
                $ochkiZaHod ? ($cells ?: []) : ($saveDesk ?: []),
                [$this->gameStatus['users'][$this->numUser]['fishki']]
            )
        );
        //Сделать через отправку статуса
    }

    protected function logSlov($words)
    {
        $res = '<br />';
        foreach ($words as $word => $price) {
            $res .= " <a href=\"#\" onclick=\"event.preventDefault(); openWindowGlobal('" . urlencode($word)
                . "').then((data1) => { var openWindow = window.open('about:blank', 'Слово', 'location=no');setTimeout(function () {openWindow.document.body.innerHTML = data1;} , 1000) });\">$word</a>-$price&nbsp;";
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

    public function checkGameStatus()
    {
        if (!$this->currentGame) {
            if ($this->Queue::isUserInQueue($this->User)) {
                return (new $this->Queue($this->User, $this, $_POST))->doSomethingWithThisStuff($_GET['lang'] ?? '');
            }

            $chooseGameParams = [
                'gameState' => 'chooseGame',
                'gameSubState' => 'choosing',
                'players' => $this->onlinePlayers(),
                'prefs' => Cache::get($this->Queue::PREFS_KEY . $this->User)
            ];

            return $this->isBot() ? $this->initGame() : $this->makeResponse($chooseGameParams);
        }

        if ($this->activeGameUsers() < 2) {
            if (!isset($this->gameStatus['results'])) {
                $this->storeGameResults($this->User);
                $this->addToLog('остался в игре один - Победа!', $this->numUser);
                //Пользователь остался в игре один и выиграл
            } else {
                $this->addToLog('остался в игре один! Начните новую игру', $this->numUser);
            }
        }

        $desk = Cache::get(static::CURRENT_GAME_KEY . $this->currentGame);

        if ($this->getUserStatus() == self::GAME_RESULTS_STATUS) {


            $result = $this->gameStatus['results'];
            if (isset($result['winner'])) {
                $ratingsChanged = $this->gameStatus['users'][$this->numUser]['result_ratings'];

                return $this->makeResponse(
                    [
                        'gameState' => self::GAME_RESULTS_STATUS,
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
            $this->addToLog('Время хода истекло', $this->gameStatus['activeUser']);

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
                                'gameState' => self::GAME_RESULTS_STATUS,
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
            VH::tag(
                'strong',
                $isWinner ? T::S('you_won') : T::S('you_lost'),
                ['style' => 'color:' . ($isWinner ? 'green' : 'red') . ';']
            )

            . VH::br()
            . T::S('rating_changed')
            . "{$ratingsChanged['prev_rating']} -> "
            . VH::tag(
                'strong',
                "{$ratingsChanged['new_rating']} (" . ($isWinner ? '+' : '') . "{$ratingsChanged['delta_rating']})",
                ['style' => 'color:' . ($isWinner ? 'green' : 'red') . ';']
            )
            . VH::br()
            . T::S('start_new_game');
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
                    'остался без фишек! Победитель - ' . $this->gameStatus['users'][$userWinner]['username'] . ' со счетом ' . $this->gameStatus['users'][$userWinner]['score'],
                    $num
                );
            } else {
                $this->addToLog(
                    'остался без фишек! Вы победили со счетом ' . $this->gameStatus['users'][$userWinner]['score'],
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
            $pass
                ? 'сдался'
                : 'пропустил 3 хода! Победитель - ' . $this->gameStatus['users'][$userWinner]['username'] . ' со счетом ' . $this->gameStatus['users'][$userWinner]['score'],
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
                $this->addToLog('остался в игре один - Победа!', $this->numUser);

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

        $this->addToLog("покинул игру", $numuser);

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

        Cache::del($this->Queue::GET_GAME_KEY . $this->User);
        //Удалили указатель на текущую игру для пользователя

        if ($this->currentGame && (in_array($_REQUEST['gameState'] ?? '', self::INIT_STATES))) {
            return $this->checkGameStatus();
            //Пользователь думает, что находится в подборе игры, но игра уже началась
        }

        if ($this->currentGame) {
            $this->gameStatus['users'][$this->numUser]['isActive'] = false;
            //Игрок стал неактивен
            $this->addToLog("покинул игру", $this->numUser);
        }

        return $this->makeResponse(['gameState' => 'chooseGame', 'gameSubState' => 'choosing']);
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
                $this->gameStatus['users'][$num]['lostTurns'] = 0;
                $this->gameStatus['users'][$num]['inactiveTurn'] = 1000;
                //Сделали невозможным значение терна инактив


                $this->gameStatus['users'][$num]['common_id'] = PlayerModel::getPlayerID($user['ID'], true);
                if (!($this->gameStatus['users'][$num]['rating'] = CommonIdRatingModel::getRating($this->gameStatus['users'][$num]['common_id']))) {
                    $userRating = $this->getRatings($user['ID']);
                    $this->gameStatus['users'][$num]['rating'] = $userRating ? $userRating[0]['rating'] : self::NEW_PLAYER;
                }
                //Прописали рейтинг и common_id игрока в статусе игры - только для games_statistic.php
            }

            $this->addToLog(
                'Новая игра начата! <br />Набери <strong>' . $this->gameStatus['winScore'] . '</strong> очков'
            );
        }

        return $this->makeResponse(
            [
                'gameState' => $this->getUserStatus(),
                'usersInfo' => $this->currentGameUsers,//хз зачем этот ключ - нигде не используется
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
            // $this->Queue::setPlayerInitStatus($this->User);

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
                $arr['comments'] .= "<br />Запрашиваем подтверждение соперников.<br />В игре осталось: $numActiveUsers";
                $arr['inviteStatus'] = 'waiting';
            } else {
                if ($numActiveUsers) {
                    $arr['comments'] .= '<br />Вас пригласили на Реванш - Согласны?';
                } else {
                    $arr['comments'] .= '<br />Все игроки покинули игру';
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
        $salt = Config::$envConfig['SALT'];
        $arr = array_merge(
            $arr,
            [
                'common_id' => $commonId,
                'common_id_hash' => md5($commonId . $salt),
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
                $score = 'Ваши очки:' . $this->gameStatus['users'][$this->numUser]['score'] . '/' . $this->gameStatus['winScore'];
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
                /** todo После релиза убрать substr(strtoupper($this->gameStatus['lang']),0,2), оставить только $this->gameStatus['lang'] */
                $arr = array_merge($arr, ['lang' => substr(strtoupper($this->gameStatus['lang']), 0, 2)]);
                $arr = array_merge($arr, ['langTest' => $this->gameStatus['lang']]);
                //Добавили в респонс очки игроков
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

            $turnTimeLeft = "Время на ход " . $turnTimeLeft;

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

            if ($arr['gameState'] == self::GAME_RESULTS_STATUS && isset($this->gameStatus['invite'])) {
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
        return !(strstr($this->User, 'botV3#') === false);
    }
}

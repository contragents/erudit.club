<?php

namespace Erudit;

use AvatarModel;
use BanModel;
use \Cache;
use ComplainModel;
use Dadata\DB;
use Dadata\Hints;
use Dadata\Players;
use Dadata\Prizes;
use Dadata\Stats;
use Lang\Eng;
use Lang\Ru;
use LogModel;
use PlayerModel;
use UserModel;

class Game
{
    const BAD_COMBINATIONS_HSET = 'bad_combinations';

    const ERROR_STATUS = 'error';
    const BOT_ERRORS_KEY = 'erudit_bot_errors';
    const LOG_BOT_ERRORS_KEY = 'erudit_bot_log_errors';
    const MAX_ERRORS = 100;
    const GAMES_ENDED_KEY = 'erudit.games_ended';
    const STATS_FAILED = 'erudit.games_statistics_failed';
    const NUM_RATING_PLAYERS_KEY = 'erudit.num_rating_players';
    const GET_GAME_KEY = 'erudit.get_game_';

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

    public static $configStatic;
    public $serverName;
    public $config;
    public $p;
    public $User;
    public $currentGame;
    public $currentGameUsers = false;
    private $isStateLocked = false;
    private $isGameEndedSaved = false;
    private $gamePlayersWaiting = false;//Количество игроков, ожидающих начала игры
    public $gameWaitLimit; //Макс время ожидания начала игры
    public $ratingGameWaitLimit;//Время ожидания игрока с выбранным рейтингом
    public $cacheTimeout;
    public $ratingsCacheTimeout;
    private $turnDeltaTime;//Разрешенное превышение длительности хода
    private $activityTimeout = 30;//засунуть в конфиг
    private $turnTime;
    private $winScore;
    private $chisloFishek;
    private $numUser = false;
    private $statusComments = [
        self::START_GAME_STATUS => 1,
        self::PRE_MY_TURN_STATUS => 1,
        self::MY_TURN_STATUS => 1,
        self::OTHER_TURN_STATUS => 1,
        'desync' => 1,
        'initGame' => 1
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

    public function __construct($server_name = '')
    {
        $this->p = Cache::getInstance();


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

        $this->User = $this->validateCookie($_COOKIE['erudit_user_session_ID']);

        $this->currentGame = Cache::get(self::GET_GAME_KEY . $this->User);

        if (!$this->currentGame) {
            $this->currentGame = false;

            if (!empty($this->User)) {
                $checkEndGame = Cache::get(self::CHECK_STATUS_RESULTS_KEY . $this->User);

                if ($checkEndGame && ($checkEndGame < $_GET['queryNumber'])) {
                    // Проверяем если вкладка с игрой осталась открытой, но игра уже стерта из кеша
                    print $this->makeResponse(['gameState' => 'noGame']);
                    exit;
                } else {
                    Cache::del(self::CHECK_STATUS_RESULTS_KEY . $this->User);
                }

                if (($_GET['gameNumber'] ?? 0) > 0) {
                    print $this->makeResponse(
                        ['gameState' => 'noGame', 'comments' => 'Игра закончена. Начните новую игру!']
                    );
                }

                if (($_GET['queryNumber'] ?? 1) >= 10 && !$this->isUserInQueue() && !$this->isUserInCabinet()) {
                    print $this->makeResponse(
                        ['gameState' => 'noGame', 'comments' => 'Игра закончена. Начните новую игру!']
                    );
                    exit;
                }
            }
        } else {
            $this->currentGameUsers = Cache::get("erudit.game_{$this->currentGame}_users");

            if (!$this->lockTry()) {
                //Вышли с Десинком, если не смогли получить Лок
                return $this->desync();
            }

            $this->gameStatus = Cache::get('erudit.game_status_' . $this->currentGame);
            //Забрали статус игры из кэша
            try {
                if (!isset($this->gameStatus[$this->User])) {
                    $this->newGame();
                }

                $this->numUser = $this->gameStatus[$this->User];
                //Номер пользователя по порядку

                if (isset($_GET['page_hidden']) && $_GET['page_hidden'] == 'true') {
                    if (isset($_GET['queryNumber']) && $_GET['queryNumber'] < $this->gameStatus['users'][$this->numUser]['last_request_num']) {
                        $this->unlock();
                        throw new \BadRequest('Num packet error when returned from page_hidden state');
                    }
                }
            } catch (\BadRequest $e) {
                \BadRequest::sendBadRequest(
                    [
                        'err_msg' => $e->getMessage(),
                        'err_file' => $e->getFile(),
                        'err_line' => $e->getLine(),
                        'err_context' => $e->getTrace(),
                    ]
                );
            }

            if (isset($_GET['queryNumber']) && $_GET['queryNumber'] > 5 && $_GET['queryNumber'] < ($this->gameStatus['users'][$this->numUser]['last_request_num'] ?? 0)) {
                // todo  при возврате десинка в игре проблемы с получением фишек. обычно при перезагрузке страницы
                \BadRequest::sendBadRequest(
                    [
                        'message' => 'Потеря синхронизации с сервером',
                    ]
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
            if (isset($_POST['g']) && (strlen(strval($_POST['g'])) > 3)) {
                $this->gameStatus['users'][$this->numUser]['userID'] = $_POST['g'];
            }
            unset($_POST['g']);
        }
    }

    private function validateCookie($incomingCookie)
    {
        if (strpos($incomingCookie, 'bot') !== false) {
            return $incomingCookie;
        } elseif (!isset($_SERVER['HTTP_COOKIE'])) {
            return ($sintCookie = (md5($_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT'])));
        } elseif (stristr($_SERVER['HTTP_COOKIE'], 'erudit_user_session_ID') === false) {
            return ($sintCookie = (md5($_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT'])));
        }

        return $incomingCookie;
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

    public function saveUserNameWithID($name, $commonID)
    {
        if (!$commonID) {
            $commonID = $this->getCommonID($this->User);
        }

        $name = trim($name, "'\"");

        $setUserNameQuery = "UPDATE users
                SET 
                    name = '" . DB::escapeString($name) . "'
                WHERE 
                    id = $commonID";

        if (DB::queryInsert($setUserNameQuery)) {
            return json_encode(
                [
                    'result' => 'saved',
                    'message' => 'Ник пользователя сохранен'
                ]
            );
        } else {
            return json_encode(
                [
                    'result' => 'error ' . $setUserNameQuery,
                    'message' => 'Ошибка сохранения Ника!'
                ]
            );
        }
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

        $commonId = PlayerModel::getPlayerID($user['ID'], true);//PlayerModel::getCommonID($user['ID']);
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
        if( in_array($this->getUserStatus(), self::IN_GAME_STATUSES)) {
            $this->storeGameResults($this->lost3TurnsWinner($this->numUser, true));
        }

        $this->gameStatus['invite_accepted_users'][$this->User] = $this->User;

        $inviteStatus = [];
        if (!isset($this->gameStatus['invite'])) {
            $this->gameStatus['invite'] = $this->User;
            $message = 'Запрос на новую игру отправлен';
        } elseif ($this->gameStatus['invite'] == $this->User) {
            $message = 'Ваш запрос на новую игру ожидает ответа игроков';
        } else {
            $message = 'Запрос принят! Начинаем новую игру';
            $this->gameStatus['invite'] = 'newGameStarting';
            $inviteStatus = ['inviteStatus' => $this->gameStatus['invite']];
        }

        return $this->makeResponse(array_merge(['gameState' => 'gameResults', 'message' => $message], $inviteStatus));
    }

    public function playerCabinetInfo()
    {
        $message = [];
        $playerID = PlayerModel::getPlayerID($this->User, true);

        $userData = UserModel::getOne($playerID);

        // todo remove after model test
        //"SELECT * FROM users WHERE id = $playerID";
        //$userData = DB::queryArray($getaDataFromUsersQuery);
        $message['url'] = $userData['avatar_url'];
        if (!$message['url']) {
            $message['url'] = AvatarModel::getDefaultAvatar($playerID);
            $message['img_title'] = "Используется аватар по умолчанию";
        } else {
            $message['img_title'] = "Аватар по предоставленной ссылке";
        }
        $message['name'] = $userData['name'];
        $message['common_id'] = $playerID;
        $message['text'] = '';
        $message['form'][] = [
            'prompt' => "Никнейм (id: $playerID)",
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
            'value' => $this->genKeyForCommonID($playerID),
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

        return $this->makeResponse(['message' => json_encode($message)]);
    }

    private function genKeyForCommonID($ID)
    {
        $messageToEncrypt = $ID;
        $secretKey = 'eruditforever';
        $method = 'AES-128-CBC';
        $ivLength = openssl_cipher_iv_length($method);
        $iv = base64_decode('x/bazHpEqMpxpLfVWD9dhA==');//openssl_random_pseudo_bytes($ivLength);//$this->config['iv'];
        $encrypted_message = openssl_encrypt($messageToEncrypt, $method, $secretKey, 0, $iv);

        return $encrypted_message;
    }

    public function mergeTheIDs($encryptedMessage, $commonID)
    {
        $secretKey = 'eruditforever';
        $method = 'AES-128-CBC';
        $iv = base64_decode('x/bazHpEqMpxpLfVWD9dhA==');
        $decrypted_message = openssl_decrypt($encryptedMessage, $method, $secretKey, 0, $iv);

        if (!is_numeric($decrypted_message)) {
            return json_encode(
                [
                    'result' => 'error_decryption' . ' ' . $decrypted_message,
                    'message' => 'Ошибка расшифровки ключа'
                ]
            );
        }

        // todo delete after model
        /*$commonIDSearchQuery = "SELECT id
        FROM
        users
        WHERE
        id = $decrypted_message;";*/

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
                    'message' => 'Ошибка привязки - аккаунты уже связаны'
                ]
            );
        }
    }

    public function playersInfo()
    {
        $ratings = $this->getRatings();
        $message = include(__DIR__ . '/tpl/ratingsTableHeader.php');
        if (!isset($this->gameStatus['users'])) {
            return $this->makeResponse(['message' => "Игра не начата"]);
        }

        $commonId = PlayerModel::getCommonID($this->User);
        if ($commonId) {
            $thisPlayerHasBanned = BanModel::hasBanned($commonId);
        }

        foreach ($this->gameStatus['users'] as $num => $user) {
            if (isset($user['userID'])) {
                if (!($deltaRating = $this->getDeltaRating(self::hash_str_2_int($user['userID'])))) {
                    $deltaRating = $this->getDeltaRating($user['ID']);
                }
            } else {
                $deltaRating = $this->getDeltaRating($user['ID']);
            }

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

            $message .= include(__DIR__ . '/tpl/ratingsTableRow.php');

            $recImgs = '';
            $records = Prizes::playerCurrentRecords($user['ID'], $rating['playerName']);
            $recordsShown = 0;
            foreach ($records as $record) {
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
								src=\"https://xn--d1aiwkc2d.club/{$record['link']}\" width=\"100px\" />";
                $recordsShown++;
                if ($recordsShown >= 3) {
                    break;
                }
            }

            if ($user['ID'] == $this->User) {
                $message .= include(__DIR__ . '/tpl/ratingsTableNicknameFormRow.php');
            }

            if ($recordsShown) {
                $message .= include(__DIR__ . '/tpl/ratingsTablePrizesRow.php');
            }
        }
        $message .= include(__DIR__ . '/tpl/ratingsTableFooter.php');

        return $this->makeResponse(['message' => $message]);
    }

    public function getAvatarUrl($cookie)
    {
        $commonID = PlayerModel::getPlayerID($cookie, true);//PlayerModel::getCommonID($cookie);

        if ($commonID) {
            return Players::getAvatarUrl($commonID);
        }

        return ''; // Тут добавить стандартный аватар из коллекции какойнибудь
    }

    public function __destruct()
    {
        if ($this->isStateLocked) {
            $this->unlock();
        }
    }

    private function destruct()
    {
        if ($this->currentGame) {
            if (isset($this->gameStatus['results']['winner']) && !isset($this->gameStatus['isGameEndedSaved'])) {
                Cache::rpush(self::GAMES_ENDED_KEY, $this->gameStatus);
                //Сохраняем результаты игры в список завершенных
                $this->gameStatus['isGameEndedSaved'] = true;
            }

            $this->gameStatus['users'][$this->numUser]['last_request_num'] = $_GET['queryNumber'] ?? 1000;

            Cache::setex(
                'erudit.game_status_' . $this->currentGame,
                $this->cacheTimeout,
                $this->gameStatus
            );
        }

        Cache::setex('erudit.user_' . $this->User . '_last_activity', $this->cacheTimeout, date('U'));
        $this->unlock();
        //Разлочили сохранение состояния
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

    private function addToLog($message, $numUser = false)
    {
        $this->gameStatus['gameLog'][] = [$numUser, $message];
        foreach ($this->gameStatus['users'] as $num => $User) {
            $this->gameStatus['users'][$num]['logStack'][] = [$numUser, $message];
        }
    }

    public function getDeltaRating($key)
    {
        if ($delta = Cache::get(PlayerModel::DELTA_RATING_KEY_PREFIX . $key)) {
            return $delta;
        } else {
            return false;
        }
    }

    private function getCommonID($cookie = false, $userID = false)
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

    private function normalizeRatings(&$ratings)
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

    private function getRatingWithCommonID($commonID = false, $cookie = false, $userID = false)
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

    private function statusComments_startGame()
    {
        Stats::saveStats();

        $ratings = $this->getRatings($this->User);

        $this->gameStatus['users'][$this->gameStatus[$this->User]]['rating'] = $ratings ? $ratings[0]['rating'] : 'new_player';

        return 'Новая игра начата! <br />Набери <strong>' . $this->gameStatus['winScore'] . '</strong> очков' . '<br />' . $this->gameStatus['users'][$this->gameStatus['activeUser']]['username'] . ' ходит' . '<br />Ваш текущий рейтинг - <strong>' . $ratings[0]['rating'] . '</strong>';
    }

    private function statusComments_otherTurn()
    {
        return $this->gameStatus['users'][$this->gameStatus['activeUser']]['username'] . ' ходит'
            . Hints::getHint(
                $this->User,
                $this->gameStatus,
                $this->gameStatus['users'][$this->gameStatus[$this->User]]['rating'] ?? false
            );
    }

    private function statusComments_desync()
    {
        return 'Синхронизируемся с сервером..'
            . Hints::getHint(
                $this->User,
                $this->gameStatus,
                $this->gameStatus['users'][$this->gameStatus[$this->User]]['rating'] ?? false
            );
    }

    private function statusComments_myTurn()
    {
        if ($this->gameStatus['turnNumber'] == 1) {
            Stats::saveStats();
        }

        if ($this->gameStatus['turnNumber'] == 1 || !isset($this->gameStatus['users'][$this->gameStatus[$this->User]]['rating'])) {
            $ratings = $this->getRatings($this->User);

            $this->gameStatus['users'][$this->gameStatus[$this->User]]['rating'] = $ratings[0]['rating'] ?? 'new_player';

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

    private function statusComments_initGame()
    {
        if ($this->gamePlayersWaiting) {
            return '<strong>Подбор игры!</strong> <br />Готово игроков: <strong>' . $this->gamePlayersWaiting . '</strong>';
        } else {
            return '<strong>Подбор игры!</strong> <br />Поиск других игроков';
        }
    }


    private function statusComments_preMyTurn()
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


    private function storeGameResults($winnerUser)
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
            $this->updateUserStatus('gameResults', $user['ID']);

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
    }

    private function getUserStatus($user = false): string
    {
        return $this->gameStatus['users'][$this->gameStatus[($user ?: $this->User)]]['status'] ?? self::ERROR_STATUS;
    }

    public function updateUserStatus($newStatus, $user = false)
    {
        if ($user == false) {
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
        $desk = Cache::get('erudit.current_game_' . $this->currentGame);
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

        return json_encode($result);
    }

    public function lockTry(): bool
    {
        if ($this->isStateLocked) {
            return true;
        }

        $cycleBeginTime = date('U');

        //Будем ждать освобождения семафора, не более $this->turnDeltaTime
        while ((date('U') - $cycleBeginTime) <= $this->turnDeltaTime) {
            // Получаем время блокировки
            $lockTime = Cache::hget(
                'erudit.games_' . date('Y_m_d') . '_locks',
                $this->currentGame . '_lock_time'
            ) ?: 0;

            if (
                Cache::hincrby('erudit.games_' . date('Y_m_d') . '_locks', $this->currentGame . '_lock', 1) === 1
                ||
                (date('U') - $lockTime) > $this->turnDeltaTime
            ) {
                // Обновляем время блокировки
                Cache::hset(
                    'erudit.games_' . date('Y_m_d') . '_locks',
                    $this->currentGame . '_lock_time',
                    date('U')
                );

                // ставим блокировку
                Cache::hset('erudit.games_' . date('Y_m_d') . '_locks', $this->currentGame . '_lock', 1);

                $this->isStateLocked = true;

                return true;
                //Семафор освободился
            }

            usleep(100000);
        }

        //Ждали слишком долго - возвращаем десинхрон
        return false;
    }

    public function botUnlock(): void
    {
        $this->unlock();
    }

    private function unlock()
    {
        Cache::hset('erudit.games_' . date('Y_m_d') . '_locks', $this->currentGame . '_lock', 0);
        Cache::hdel(
            'erudit.games_' . date('Y_m_d') . '_locks',
            $this->currentGame . '_lock_time'
        );

        $this->isStateLocked = false;
    }

    private function desync($queryNumber = false)
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
            $desk = Cache::get('erudit.current_game_' . $this->currentGame);
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
                        LogModel::MESSAGE_FIELD => json_encode([
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
            \BadRequest::logBadRequest(
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

                    $arr = Prizes::checkDayWordLenRecord($word);
                    foreach ($arr as $period => $value) {
                        $this->addToLog(
                            "устанавливает рекорд по длине слова за $period - <strong>$word</strong>",
                            $this->numUser
                        );
                    }

                    $arr = Prizes::checkDayWordPriceRecord($word, $price);
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
                $arr = Prizes::checkDayTurnPriceRecord($ochkiZaHod);
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

                $arr = Prizes::checkDayGamePriceRecord($this->gameStatus['users'][$this->numUser]['score']);
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
            \BadRequest::sendBadRequest(
                [
                    'message' => 'Ошибка обработки данных!',
                    'err_msg' => $e->getMessage(),
                    'err_file' => $e->getFile(),
                    'err_line' => $e->getLine(),
                    'err_context' => $e->getTrace(),
                ]
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
            Cache::setex('erudit.current_game_' . $this->currentGame, $this->cacheTimeout, $cells);
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

    private function logSlov($words)
    {
        $res = '<br />';
        foreach ($words as $word => $price) {
            $res .= " <a href=\"#\" onclick=\"event.preventDefault(); openWindowGlobal('" . urlencode($word)
                . "').then((data1) => { var openWindow = window.open('about:blank', 'Слово','location=no');setTimeout(function () {openWindow.document.body.innerHTML = data1;} , 1000) });\">$word</a>-$price&nbsp;";
        }

        return $res;
    }

    private function giveFishki($num = 7)
    {
        $fishki = [];
        $bankFishkiCount = count($this->gameStatus['bankFishki']);
        //Зафиксировали число фишек в банке

        for ($i = 0; ($i < $num) && ($i < $bankFishkiCount); $i++) {
            $fishki[] = (int)array_shift($this->gameStatus['bankFishki']);
        }

        return $fishki;
    }

    private function activeGameUsers()
    {
        $numActive = 0;
        foreach ($this->gameStatus['users'] as $user) {
            if ($user['isActive']) {
                $numActive++;
            }
        }

        return $numActive;
    }

    private function onlinePlayers()
    {
        if (!($rangedOnlinePlayers = Cache::get(self::NUM_RATING_PLAYERS_KEY))) {
            $lastGame = Cache::get('erudit.num_games');
            $players = [];
            for ($i = $lastGame; $i > ($lastGame - 50); $i--) {
                if ($game = Cache::get("erudit.game_status_" . $i)) {
                    if (!isset($game['results'])) {
                        foreach ($game['users'] as $num => $user) {
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
                self::NUM_RATING_PLAYERS_KEY,
                $this->ratingsCacheTimeout,
                $rangedOnlinePlayers
            );
        }

        if ($rangedOnlinePlayers[1900]) {
            $cnt = Cache::hlen(Queue::QUEUES['erudit.rating_waiters']);
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

    private function isUserInInviteQueue()
    {
        if (Cache::hget(Queue::QUEUES['erudit.inviteplayers_waiters'], $this->User)) {
            return true;
        }

        if (Cache::hget(Queue::QUEUES['erudit.inviteENplayers_waiters'], $this->User)) {
            return true;
        }

        return false;
    }

    public function checkGameStatus()
    {
        if (!$this->currentGame) {
            if ($this->isUserInInviteQueue()) {
                return $this->startGame();
            } else {
                if ($this->isUserInQueue()) {
                    return $this->startGame();
                }

                $chooseGameParams = [
                    'gameState' => 'chooseGame',
                    'gameSubState' => 'choosing',
                    'players' => $this->onlinePlayers(),
                    'prefs' => Cache::get(Queue::PREFS_KEY . $this->User)
                ];

                if (isset($_GET['queryNumber']) && ($_GET['queryNumber'] == 1)) {
                    return $this->makeResponse($chooseGameParams);
                } elseif (!isset($_POST['players_count']) && !$this->isBot()) {
                    return $this->makeResponse($chooseGameParams);
                } else {
                    return $this->startGame();
                }
            }
        }

        if ($this->activeGameUsers() == 1) {
            if (!isset($this->gameStatus['results'])) {
                $this->storeGameResults($this->User);
                $this->addToLog('остался в игре один - Победа!', $this->numUser);
                //Пользователь остался в игре один и выиграл
            } else {
                $this->addToLog('остался в игре один! Начните новую игру', $this->numUser);
            }
        }

        if ($this->getUserStatus() == 'gameResults') {
            $desk = Cache::get('erudit.current_game_' . $this->currentGame);

            $result = $this->gameStatus['results'];
            if (isset($result['winner'])) {
                if ($result['winner'] == $this->User) {
                    return $this->makeResponse(
                        [
                            'gameState' => 'gameResults',
                            'comments' => "<strong style=\"color:green;\">Вы выиграли!</strong><br/>Начните новую игру",
                            ($desk ? 'desk' : 'nothing') => $desk
                        ]
                    );
                } else {
                    return $this->makeResponse(
                        [
                            'gameState' => 'gameResults',
                            'comments' => "<strong style=\"color:red;\">Вы проиграли!</strong><br/>Начните новую игру",
                            ($desk ? 'desk' : 'nothing') => $desk
                        ]
                    );
                }
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
                // Начало фрагмента для объединения - чего с чем?

                $desk = Cache::get('erudit.current_game_' . $this->currentGame);

                $result = $this->gameStatus['results'];
                if (isset($result['winner'])) {
                    if ($result['winner'] == $this->User) {
                        return $this->makeResponse(
                            [
                                'gameState' => 'gameResults',
                                'comments' => "<strong style=\"color:green;\">Вы выиграли!</strong><br/>Начните новую игру",
                                ($desk ? 'desk' : 'nothing') => $desk
                            ]
                        );
                    } else {
                        return $this->makeResponse(
                            [
                                'gameState' => 'gameResults',
                                'comments' => "<strong style=\"color:red;\">Вы проиграли!</strong><br/>Начните новую игру",
                                ($desk ? 'desk' : 'nothing') => $desk
                            ]
                        );
                    }
                    //Конец фрагмента для объединения
                }
            } else {
                $this->nextTurn();
            }
        }

        $userStatus = $this->getUserStatus();

        if (($desk = Cache::get(('erudit.current_game_' . $this->currentGame))) && $this->currentGame) {
            if ($userStatus == self::ERROR_STATUS) {
                Cache::hset(
                    self::BOT_ERRORS_KEY,
                    time() % self::MAX_ERRORS,
                    ['date' => date('Y-m-d H:i:s'), 'gameState' => $this->gameStatus]
                );
                return $this->makeResponse(
                    ['gameState' => $userStatus, 'desk' => $desk, 'gameStatus' => $this->gameStatus]
                );
            } else {
                return $this->makeResponse(['gameState' => $userStatus, 'desk' => $desk]);
            }
        } else {
            if ($userStatus != self::ERROR_STATUS && $userStatus) {
                return $this->makeResponse(['gameState' => $userStatus]);
            } //Вернули статус пользователя из кеша
            else {
                return $this->newGame();
            }
        }
        //Если статус ФАЛС то делаем новую игру
    }

    private function endOfFishki()
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

    private function lost3TurnsWinner($numLostUser, bool $pass = false): string
    {
        $maxres = 0;
        $userWinner = 0;
        if ($this->gameStatus['users'][$numLostUser]['score'] === 0) {
            $this->gameStatus['users'][$numLostUser]['isActive'] = false;
        }

        foreach ($this->gameStatus['users'] as $num => $user) {
            if (($maxres <= $user['score']) && $user['isActive']) {
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

    private function nextTurn()
    {
        foreach ($this->gameStatus['users'] as $numUser => $user) {
            // Дали всем игрокам статус - другойХодит
            $this->updateUserStatus(self::OTHER_TURN_STATUS, $user['ID']);

            if (
                ($this->gameStatus['turnNumber'] > $this->activeGameUsers())
                &&
                !isset($this->gameStatus['users'][$numUser]['lastActiveTime'])
                &&
                $this->gameStatus['turnNumber'] > ($this->gameStatus['users'][$numUser]['inactiveTurn'] + $this->activeGameUsers(
                    ))
            ) {
                $this->exitGame($numUser, false);
            }
            //Выпиливаем неактивного юзера
        }

        if ($this->activeGameUsers() < 2) {
            //Пользователь остался в игре один и выиграл
            return $this->checkGameStatus();
        }

        $isActiveUserFound = false;

        while (!$isActiveUserFound) {
            $nextActiveUser = ($this->gameStatus['activeUser'] + 1) % count($this->gameStatus['users']);
            $this->gameStatus['activeUser'] = $nextActiveUser;
            if ($this->gameStatus['users'][$nextActiveUser]['isActive']) {
                $isActiveUserFound = true;
            }
        }

        $this->updateUserStatus(self::MY_TURN_STATUS, $this->gameStatus['users'][$nextActiveUser]['ID']);
        //Прописали статус новому активному пользователю

        $nextPreMyTurnUser = ($nextActiveUser + 1) % count($this->gameStatus['users']);


        $this->updateUserStatus(self::PRE_MY_TURN_STATUS, $this->gameStatus['users'][$nextPreMyTurnUser]['ID']);
        //Прописали статус следующему преМайТерн-юзеру

        $this->gameStatus['turnNumber']++;

        //$this->adv2Chat(); // Показ рекламы в чате

        $this->gameStatus['turnBeginTime'] = date('U');
        $this->gameStatus['aquiringTimes'][$this->gameStatus['turnNumber']] = false;
    }

    private function adv2Chat()
    {
        if (($this->gameStatus['turnNumber'] == 2) && isset($this->config['advMessage']) && ($this->config['advMessage'] !== false)) {
            $this->addToChat($this->config['advMessage'], 'adv', false);
        }
        //Кидаем всем в чат рекламу (кроме пользователей яндекса)
    }

    public function exitGame($numuser = false, $commitState = true)
    {
        if (!$numuser) {
            $numuser = $this->numUser;
        }

        Cache::del(self::GET_GAME_KEY . $this->gameStatus['users'][$numuser]['ID']);
        //Удалили указатель на текущую игру для пользователя

        $this->gameStatus['users'][$numuser]['isActive'] = false;
        //Игрок стал неактивен

        $this->addToLog("покинул игру", $numuser);

        if ($commitState) {
            $this->destruct();
        }
    }

    public function newGame()
    {
        /** todo
         * Нужно доделать - уведомление остальных игроков,
         * поражение пользователю,
         * игра идет без ушедшего пользователя
         */

        foreach (Queue::QUEUES as $queue) {
            Cache::hdel($queue, $this->User);
        }

        Cache::del(self::GET_GAME_KEY . $this->User);
        //Удалили указатель на текущую игру для пользователя

        if ($this->currentGame && ($_POST['gameState'] == 'initGame')) {
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

    private function makeWishWinscore(): int
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

    private function makeWishTime(): int
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
            $this->updateUserStatus(self::PRE_MY_TURN_STATUS, $preMyTurnUser);
            //Вычислили игрока, идущего за первым и дали ему статус преМайТерн

            foreach ($this->gameStatus['users'] as $num => $user) {
                $this->gameStatus['users'][$num]['fishki'] = $this->giveFishki(7);
                //Раздали фишки игрокам
                $this->gameStatus['users'][$num]['lostTurns'] = 0;
                $this->gameStatus['users'][$num]['inactiveTurn'] = 1000;
                //Сделали невозможным значение терна инактив

                $userRating = $this->getRatings($user['ID']);
                $this->gameStatus['users'][$num]['rating'] = $userRating ? $userRating[0]['rating'] : 'new_player';
                $this->gameStatus['users'][$num]['common_id'] = PlayerModel::getPlayerID($user['ID'], true);
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

    public function startGame()
    {
        if ($this->currentGame && is_array($this->currentGameUsers)) {
            return $this->gameStarted(false);
            //Вернули статус начатой игры без обновления статусов в кеше
        }

        return (new Queue($this->User, $this, $_POST))->doSomethingWithThisStuff(
            (isset($_GET['lang']) && $_GET['lang'] == 'EN') ? $_GET['lang'] : ''
        );
    }

    private function processInvites(&$arr)
    {
        $gameSubState = $this->gameStatus['invite'];

        if ($this->gameStatus['invite'] == 'newGameStarting') {
            $gameSubState .= rand(1, 100);
            $arr['gameSubState'] = $gameSubState;
            $arr['inviteStatus'] = 'newGameStarting';

            (new Queue($this->User, $this, ['lang' => ($this->gameStatus['lang'] == 'EN' ? 'EN' : '')]))
                ->storePlayersToQueue($this->User, 'invite');

            $this->exitGame($this->numUser);

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
                $arr['comments'] .= "<br />Запрашиваем подтверждения соперников.<br />В игре осталось: $numActiveUsers";
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
            return json_encode($arr);
        }

        if (isset($this->gameStatus[$this->User])) {
            if (isset($this->statusComments[$arr['gameState']])) {
                $arr = array_merge(
                    $arr,
                    ['comments' => call_user_func([$this, 'statusComments_' . $arr['gameState']])]
                );
            }

            // Возвращаем Десинк без сохранения состояния игры - разлочка в __destruct
            if ($arr['gameState'] == 'desync') {
                return json_encode($arr);
            }

            if (is_array($this->gameStatus['users'][$this->numUser]['fishki'])) {
                if (count($this->gameStatus['users'][$this->numUser]['fishki'])) {
                    $arr = array_merge($arr, ['fishki' => $this->gameStatus['users'][$this->numUser]['fishki']]);
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

            if ($arr['gameState'] == 'gameResults' && isset($this->gameStatus['invite'])) {
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

    private function isUserInQueue()
    {
        foreach (Queue::QUEUES as $queue) {
            if (Cache::hget($queue, $this->User)) {
                return true;
            }
        }

        return false;
    }

    private function isUserInCabinet()
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

    private function isBot(): bool
    {
        return !(strstr($this->User, 'botV3#') === false);
    }
}


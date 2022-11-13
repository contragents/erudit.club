<?php

namespace Erudit;

use Dadata\Cache;
use Dadata\DB;
use Dadata\Hints;
use Dadata\Prizes;
use Dadata\Stats;

//ini_set("display_errors", 1); error_reporting(E_ALL);

class Game
{
    public $serverName;
    public $config;
    public $p;
    public $User;
    public $currentGame;
    public $currentGameUsers = FALSE;
    private $isStateLocked = FALSE;
    private $isGameEndedSaved = FALSE;
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
    private $statusComments = ['startGame' => 1, 'preMyTurn' => 1, 'myTurn' => 1, 'otherTurn' => 1, 'desync' => 1, 'initGame' => 1];
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
        spl_autoload_register(function ($class_name) {
            $Exploded_class = explode('\\', $class_name);
            include $Exploded_class[count($Exploded_class) - 1] . 'LangProvider.php';
        });
        $this->serverName = $server_name;
        $this->p = \Dadata\Cache::getInstance();


        $this->config = include("{$_SERVER['DOCUMENT_ROOT']}/configs/conf.php");//('../../configs/conf.php');
        $this->turnTime = $this->config['turnTime'];
        $this->winScore = $this->config['winScore'];
        $this->gameWaitLimit = $this->config['gameWaitLimit'];
        $this->ratingGameWaitLimit = $this->config['ratingGameWaitLimit'];
        $this->cacheTimeout = $this->config['cacheTimeout'];
        $this->ratingsCacheTimeout = $this->config['ratingsCacheTimeout'];
        $this->turnDeltaTime = $this->config['turnDeltaTime'];
        $this->activityTimeout = $this->config['activityTimeout'];
        $this->chisloFishek = $this->config['chisloFishek'];

        //$this->p->redis->setex('erudit.' . $_COOKIE['erudit_user_session_ID'], $this->cacheTimeout, serialize(array_merge($_SERVER, $_COOKIE)));
        //Временно собираем статистику по всем куки-запросам

        $this->User = $this->validateCookie($_COOKIE['erudit_user_session_ID']);

        //$this->p->redis->setex('erudit.' . $this->User, $this->cacheTimeout, serialize(array_merge($_SERVER, $_COOKIE)));
        //Временно собираем статистику по всем куки-запросам
        //print $this->User; //exit();
        $this->currentGame = $this->p->redis->get('erudit.get_game_' . $this->User);
        if (!$this->currentGame) {
            $this->currentGame = FALSE;

            if (!empty($this->User)) {
                $checkEndGame = $this->p->redis->get(self::CHECK_STATUS_RESULTS_KEY . $this->User);

                if ($checkEndGame && ($checkEndGame < $_GET['queryNumber'])) {
                    // Проверяем если вкладка с игрой осталась открытой, но игра уже стерта из кеша
                    print $this->makeResponse(['gameState' => 'noGame']);
                    exit;
                } else {
                    $this->p->redis->del(self::CHECK_STATUS_RESULTS_KEY . $this->User);
                }

            }

        } else {
            $this->currentGameUsers = unserialize($this->p->redis->get("erudit.game_{$this->currentGame}_users"));
            if (!$this->lockTry()) {
                print json_encode([
                    'gameState' => 'desync',
                    'comments' => call_user_func([$this, 'statusComments_desync'])
                ]);
                exit();
                //Вышли с Десинком, если не смогли получить Лок
            }
            $this->isStateLocked = TRUE;
            $this->gameStatus = unserialize($this->p->redis->get('erudit.game_status_' . $this->currentGame));
            //Забрали статус игры из кэша
            $this->numUser = $this->gameStatus[$this->User];
            //Номер пользователя по порядку
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
        } /*if (isset($_SERVER['HTTP_USER_AGENT']) && ((stripos($_SERVER['HTTP_USER_AGENT'], 'yabrowser') !== false) || (stripos($_SERVER['HTTP_USER_AGENT'], 'yowser') !== false)))
            return ($sintCookie = (md5($_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT'])));
        */

        elseif (!isset($_SERVER['HTTP_COOKIE'])) {
            return ($sintCookie = (md5($_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT'])));
        } elseif (stristr($_SERVER['HTTP_COOKIE'], 'erudit_user_session_ID') === false) {
            return ($sintCookie = (md5($_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT'])));
        }

        return $incomingCookie;


        //Далее логика, которая возможно пригодится в будущем для тонкой настройки

        if ($this->currentGame = $this->p->redis->get('erudit.get_game_' . $incomingCookie)) {
            return $incomingCookie;
        }

        if ($storedCookie = $this->p->redis->get($sintCookie = (md5($_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT'])))) {
            if ($storedCookie == $incomingCookie) {

                $this->currentGame = FALSE;
                return $incomingCookie;
            } else {
                $this->p->redis->setex('erudit.get_game_' . $sintCookie, $this->activityTimeout, $incomingCookie);
                if (!$this->currentGame = $this->p->redis->get('erudit.get_game_' . $sintCookie)) {
                    $this->currentGame = FALSE;
                }

                return $sintCookie;
            }
        } else {
            $this->p->redis->setex('erudit.get_game_' . $sintCookie, $this->activityTimeout, $incomingCookie);
            $this->currentGame = FALSE;

            return $incomingCookie;
        }

    }

    public function setInactive()
    {
        unset($this->gameStatus['users'][$this->numUser]['lastActiveTime']);
        $this->gameStatus['users'][$this->numUser]['inactiveTurn'] = $this->gameStatus['turnNumber'];
        $this->addToLog("Закрыл вкладку с игрой", $this->numUser);

        return $this->makeResponse(['gameState' => 'addToChat', 'message' => 'Вы закрыли вкладку с игрой и стали Неактивным!' . $this->numUser]);
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
            return json_encode(['result' => 'saved',
                'message' => 'Ник пользователя сохранен']);
        } else {
            return json_encode(['result' => 'error ' . $setUserNameQuery,
                'message' => 'Ошибка сохранения Ника!']);
        }
    }

    public function getPlayerName(array $user)
    {
        if (strpos($user['ID'], 'bot') !== false) {
            return $this->config['botNames'][substr($user['ID'], (strlen($user['ID']) == 7 ? -1 : -2))];
        }

        $commonId = $this->getCommonID($user['ID']);
        if (
        $commonIDName = DB::queryValue("SELECT name 
                    FROM users 
                    WHERE id=$commonId 
                    LIMIT 1")) {
            return $commonIDName;
        }

        if (isset($user['userID'])) {
            $idSource = $user['userID'];
        } else {
            $idSource = $user['ID'];
        }

        if (
        $res = DB::queryValue("SELECT name FROM player_names 
            WHERE
            some_id=" . $this->hash_str_2_int($idSource)
            . " LIMIT 1")
        ) {
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

                if (!$this->gameStatus['lngClass']::$bukvy[$letterNumber][3]) { // todo пофиксить ошибку
                    $letterNumber = 31;//меняем плохую букву на букву Я
                }

                if ($letterName == '') {
                    if ($letterNumber == 28) {
                        continue;//Не ставим Ь в начало ника
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
            /*старая версия
            if (isset($user['userID'])) {
                return substr(md5($user['userID']), 0, 6);
            } else {
                return substr($user['ID'], 0, 6);
            }
            */
        }
    }

    public function addUserAvatarUrl($url, $commonID)
    {
        if (!preg_match('/^https?:\/\//', $url)) {
            return json_encode(['result' => 'error',
                'message' => 'Неверный формат URL! <br />Должно начинаться с <strong>http(s)://</strong>']);
        }

        $avatarUpdateQuery = "UPDATE users
                SET 
                    avatar_url = '" . DB::escapeString($url) . "'
                WHERE 
                    id = $commonID";

        if (DB::queryInsert($avatarUpdateQuery)) {

            return json_encode(['result' => 'saved']);
        } else {
            return json_encode(['result' => 'saved', 'message' => 'Ссылка ранее уже была сохранена на сервере']);
        }
    }

    private function getPlayerID($cookie, $createIfNotExist = false)
    {
        if ($commonID = $this->getCommonID($cookie)) {
            return $commonID;
        }

        $findIDQuery = "SELECT p1.common_id AS cid1, p2.common_id AS cid2 
FROM players p1
LEFT JOIN players p2
ON p1.user_id = p2.user_id
AND
p2.common_id IS NOT NULL
WHERE 
p1.cookie='$cookie'
        LIMIT 1";

        $userIDArr = DB::queryArray($findIDQuery);
        if ($userIDArr) {

            if ($userIDArr[0]['cid2']) {
                return $userIDArr[0]['cid2'];
            }

            if ($createIfNotExist) {
                $cookieUpdateQuery = "UPDATE players
                SET 
                    common_id = id
                WHERE 
                    cookie = '$cookie'";

                if (DB::queryInsert($cookieUpdateQuery)) {
                    $userCreateQuery = "INSERT
                    INTO 
                        users 
                    SET 
                        id = (SELECT common_id FROM players WHERE cookie = '$cookie' LIMIT 1)";

                    if (DB::queryInsert($userCreateQuery)) {
                        return $this->getPlayerID($cookie);
                    }
                }
            }
        } elseif ($createIfNotExist) {

            $cookieInsertQuery = "INSERT 
                INTO 
                    players
                SET 
                    cookie = '$cookie',
                    user_id = conv(substring(md5('$cookie'),1,16),16,10)";

            if (DB::queryInsert($cookieInsertQuery)) {

                return $this->getPlayerID($cookie, 'createCommonID');
            }

        }

        return false;
    }

    public function inviteNewGame()
    {
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
        $playerID = $this->getPlayerID($this->User, 'createIfNotExist');

        $getaDataFromUsersQuery = "SELECT * FROM users WHERE id = $playerID";

        $userData = DB::queryArray($getaDataFromUsersQuery);
        $message['url'] = $userData[0]['avatar_url'];
        if (!$message['url']) {
            $message['url'] = $this->getDefaultAvatar($playerID);
            $message['img_title'] = "Используется аватар по умолчанию";
        } else {
            $message['img_title'] = "Аватар по предоставленной ссылке";
        }
        $message['name'] = $userData[0]['name'];
        $message['common_id'] = $playerID;
        $message['text'] = '';//"<span style=\"float:right;\">Параметры для редактирования ($playerID)</span>";
        $message['form'][] = ['prompt' => "Никнейм (id: $playerID)", 'inputName' => 'name', 'inputId' => 'player_name', 'onclick' => 'savePlayerName', 'buttonCaption' => 'Задать', 'placeholder' => 'новый Ник'];
        $message['form'][] = ['prompt' => 'URL аватара', 'inputName' => 'url', 'inputId' => 'player_avatar_url', 'onclick' => 'savePlayerAvatar', 'buttonCaption' => 'Применить', 'placeholder' => 'https://'];

        $message['form'][] = ['prompt' => 'Ключ учетной записи', 'inputName' => 'keyForID', 'inputId' => 'key_for_id', 'onclick' => 'copyKeyForID', 'buttonCaption' => 'В буфер', 'value' => $this->genKeyForCommonID($playerID), 'readonly' => 'true'];

        $message['form'][] = ['prompt' => 'Ключ основного аккаунта', 'inputName' => 'url', 'inputId' => 'old_account_key', 'onclick' => 'mergeTheIDs', 'buttonCaption' => 'Связать', 'placeholder' => 'сохраненный ключ от старого аккаунта'];

        return $this->makeResponse(['message' => json_encode($message)]);
    }

    private function getDefaultAvatar($playerID)
    {
        $maxImgId = 34768;
        $imgId = $playerID % $maxImgId;
        return DB::queryValue("SELECT concat(site,mini_url) FROM avatar_urls WHERE site_img_id >= $imgId LIMIT 1");
    }

    private function genKeyForCommonID($ID)
    {
        ini_set("display_errors", 1);
        error_reporting(E_ALL);
        $messageToEncrypt = $ID;
        $secretKey = 'eruditforever';//$this->config['secret_key'];
        $method = 'AES-128-CBC';//$this->config['encrypt_method'];
        $ivLength = openssl_cipher_iv_length($method);
        $iv = base64_decode('x/bazHpEqMpxpLfVWD9dhA==');//openssl_random_pseudo_bytes($ivLength);//$this->config['iv'];
        $encrypted_message = openssl_encrypt($messageToEncrypt, $method, $secretKey, 0, $iv);

        return $encrypted_message;

        $decrypted_message = openssl_decrypt($encrypted_message, $method, $secret_key, 0, $iv);
        echo $decrypted_message;
    }

    public function mergeTheIDs($encryptedMessage, $commonID)
    {
        $secretKey = 'eruditforever';//$this->config['secret_key'];
        $method = 'AES-128-CBC';//$this->config['encrypt_method'];
        $iv = base64_decode('x/bazHpEqMpxpLfVWD9dhA==');
        $decrypted_message = openssl_decrypt($encryptedMessage, $method, $secretKey, 0, $iv);

        if (!is_numeric($decrypted_message)) {
            return json_encode(['result' => 'error_decryption' . ' ' . $decrypted_message,
                'message' => 'Ошибка расшифровки ключа']);
        }

        $commonIDSearchQuery = "SELECT id
        FROM
        users
        WHERE
        id = $decrypted_message;";

        $oldCommonID = DB::queryValue($commonIDSearchQuery);

        if ($oldCommonID === false) {
            return json_encode(['result' => 'error_query_oldID',
                'message' => 'ID игрока по ключу НЕ найден']);
        }

        $mergeIDsQuery = "UPDATE
        players
        SET
        common_id = $oldCommonID
        WHERE
        common_id = $commonID;";

        if (DB::queryInsert($mergeIDsQuery)) {
            return json_encode(['result' => 'save',
                'message' => 'Учетные записи связаны']);
        } else {
            return json_encode(['result' => 'error_update ' . $oldCommonID . '->' . $commonID,
                'message' => 'Ошибка привязки - аккаунты уже связаны']);
        }
    }

    public function playersInfo()
    {
        $ratings = $this->getRatings();
        $message = include('tpl/ratingsTableHeader.php');
        if (!isset($this->gameStatus['users'])) {
            return $this->makeResponse(['message' => "Игра не начата"]);
        }
        foreach ($this->gameStatus['users'] as $num => $user) {

            if (isset($user['userID'])) {
                if (!($deltaRating = $this->getDeltaRating($this->hash_str_2_int($user['userID'])))) {
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
            $rating['playerAvatarUrl'] = $this->getAvatarUrl($user);
            $rating['isActive'] = (!isset($user['lastActiveTime']) || !$user['isActive']) ? FALSE : TRUE;

            $message .= include('tpl/ratingsTableRow.php');

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
                $message .= include('tpl/ratingsTableNicknameFormRow.php');
            }

            if ($recordsShown) {
                $message .= include('tpl/ratingsTablePrizesRow.php');
            }


        }
        $message .= include('tpl/ratingsTableFooter.php');

        return $this->makeResponse(['message' => $message]);
    }

    public function getAvatarUrl(&$user)
    {
        $commonID = $this->getCommonID($user['ID']);
        if ($commonID) {
            $avatarUrl = DB::queryValue("SELECT avatar_url FROM users WHERE id = $commonID");
            if ($avatarUrl) {
                return $avatarUrl;
            } else {
                return $this->getDefaultAvatar($commonID);
            }
        }

        return '';//Тут добавить стандартный аватар из коллекции какойнибудь
    }

    public function __destruct()
    {
        if ($this->isStateLocked) {
            $this->unlock();
        }
        return;
    }

    private function destruct()
    {
        if ($this->currentGame) {

            if (isset($this->gameStatus['results']['winner']) && !isset($this->gameStatus['isGameEndedSaved'])) {
                $this->p->redis->rpush('erudit.games_ended', serialize($this->gameStatus));
                //Сохраняем результаты игры в список завершенных
                $this->gameStatus['isGameEndedSaved'] = TRUE;
            }

            $this->gameStatus['users'][$this->numUser]['last_request_num'] = $_GET['queryNumber'] ?? 1000;

            $this->p->redis->setex('erudit.game_status_' . $this->currentGame, $this->cacheTimeout, serialize($this->gameStatus));
        }

        $this->p->redis->setex('erudit.user_' . $this->User . '_last_activity', $this->cacheTimeout, date('U'));
        $this->unlock();
        //Разлочили сохранение состояния

    }

    public function addToChat($message, $toNumUser = 'all', $needConfirm = true)
    {
        $this->gameStatus['chatLog'][] = [$this->numUser, $toNumUser, $message];
        if ($toNumUser == 'all') {
            foreach ($this->gameStatus['users'] as $num => $User) {
                if ($num == $this->numUser) {
                    $this->gameStatus['users'][$num]['chatStack'][] = ['Вы', 'всем: ' . $message];
                } else {
                    $this->gameStatus['users'][$num]['chatStack'][] = ["От Игрока" . ($this->numUser + 1) . " (всем):", $message];
                }
            }
        } elseif ($toNumUser == 'adv') {
            foreach ($this->gameStatus['users'] as $num => $User) {
                if (!isset($User['userID'])) {
                    $this->gameStatus['users'][$num]['chatStack'][] = ["Новости:", $message];
                }
            }
        } else {
            $this->gameStatus['users'][$toNumUser]['chatStack'][] = ["От Игрока" . ($this->numUser + 1) . ":", $message];
            $this->gameStatus['users'][$this->numUser]['chatStack'][] = ['Вы', 'Игроку' . ($toNumUser + 1) . ': ' . $message];
        }

        if ($needConfirm) {
            return $this->makeResponse(['message' => 'Сообщение отправлено', 'gameState' => 'addToChat']);
        } else {
            return true;
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
        if ($delta = $this->p->redis->get('erudit.delta_rating_' . $key)) {
            return unserialize($delta);
        } else {
            return false;
        }

    }

    public function getTopTable()
    {
        if (($topTableratings = unserialize($this->p->redis->get('erudit.rating_top_table_' . $this->gameStatus['lngClass'])))) {
            return $topTableratings;
        }

        $getTopTableQuery = "SELECT
CASE 
    WHEN pn.name IS NULL THEN SUBSTRING(MAX(cookie),1,6)
    ELSE pn.name
END AS name,
MAX(cookie) as cookie,
user_id,
MAX(rating) as rating,
MAX(games_played) as games_played,
CASE 
    WHEN MAX(win_percent) IS NULL THEN 0 
    ELSE MAX(win_percent) 
END as win_percent,
avg(inactive_percent) as inactive_percent,
(
SELECT
    CASE 
        WHEN SUM(num) IS NULL THEN 1 
        ELSE SUM(num)+1 
    END
FROM
    (SELECT 1 as num 
        FROM players
            WHERE rating>ps.rating 
            GROUP BY rating 
    ) dd
) as top
FROM players ps
LEFT JOIN player_names pn
ON some_id = user_id
WHERE rating >= 1800
GROUP BY user_id
ORDER BY top 
LIMIT 40";

        $topTableratings = DB::queryArray(getTopTableQuery);
        $this->p->redis->setex('erudit.rating_top_table_' . $this->gameStatus['lngClass'], round($this->cacheTimeout / 15), serialize($topTableratings));

        return $topTableratings;
    }

    private function getCommonID($cookie = false, $userID = false)
    {
        if ($cookie) {
            $commonIDQuery = "SELECT common_id FROM players WHERE cookie = '$cookie' LIMIT 1";
            if ($res = DB::queryValue($commonIDQuery)) {
                return $res;
            }
        }

        if ($userID) {
            $commonIDQuery = "SELECT common_id FROM players 
                                WHERE user_id = $userID
                                AND common_id IS NOT NULL 
                                LIMIT 1";
            if ($res = DB::queryValue($commonIDQuery)) {
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
        $cacheValues = [];

        $ratingQuery = 'SELECT 
        max(cookie) as cookie, 
        max(rating) as rating, 
        max(games_played) as games_played, 
        case when max(win_percent) is null then 0 else max(win_percent) END as win_percent,
        avg(inactive_percent) as inactive_percent,
        case 
        when max(rating)>=1700 
        then (
        select 
        case when sum(num) IS NULL THEN 1 ELSE sum(num)+1 END
        from 
        (select 1 as num from players where rating>ps.rating group by user_id, rating) dd
        ) 
        else \'Не в ТОПе\' 
        end as top
        FROM players ps
        WHERE FALSE ';

        if ($commonID) {
            $ratingQuery .= " OR user_id in (
                SELECT user_id FROM players WHERE common_id = $commonID
                AND user_id != 15284527576400310462
            ) ";
            $cacheValues[] = $commonID;
        }

        if ($cookie) {
            $ratingQuery .= " OR cookie = '$cookie' ";
            $cacheValues[] = $cookie;
        }

        if ($userID) {
            $ratingQuery .= " OR user_id = $userID ";
            $cacheValues[] = $userID;
        }

        $ratingQuery .= '  GROUP BY gruping LIMIT 1';
        //print $ratingQuery;
        if (!($ratingInfo = DB::queryArray($ratingQuery))) {
            return false;
        }

        if ($ratingInfo[0]['rating'] >= 1700) {
            $topQuery = "select 
                    case when sum(num) IS NULL THEN 1 ELSE sum(num)+1 END as top
                    from 
                    (select 1 as num from players where rating>{$ratingInfo[0]['rating']} group by user_id, rating) dd
            ";
            $top = DB::queryValue($topQuery);
            $ratingInfo[0]['top'] = $top;
            //Коррекция для ТОП
        }

        foreach ($cacheValues as $value) {
            $this->p->redis->setex('erudit.rating_cache_' . $value, round($this->cacheTimeout / 15), serialize($ratingInfo));
        }

        return $ratingInfo;
    }

    public function hash_str_2_int($str, $len = 16)
    {
        $hash_int = base_convert("0x" . substr(md5($str), 0, $len), 16, 10);
        return $hash_int;
    }

    public function getRatings($userCookie = false)
    {
        if (!$userCookie) {
            if (isset($this->gameStatus['users'])) {
                foreach ($this->gameStatus['users'] as $user) {
                    //if (!($ratings[$user['ID']] = unserialize($this->p->redis->get('erudit.rating_cache_' . $user['ID']))))
                    {
                        $ratings[$user['ID']] = $this->getRatingWithCommonID(
                            $this->getCommonID(
                                $user['ID'],
                                isset($user['userID']) ? $this->hash_str_2_int($user['userID']) : false
                            ),
                            $user['ID'],
                            isset($user['userID'])
                                ? $this->hash_str_2_int($user['userID'])
                                : false
                        );
                    }
                }

                return $this->normalizeRatings($ratings);
            }
        }

        if (!$userCookie) {
            if (!($ratings[$this->User] = unserialize($this->p->redis->get('erudit.rating_cache_' . $this->User)))) {
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
            $ratings[$userCookie] = false;//unserialize($this->p->redis->get('erudit.rating_cache_' . $userCookie));
            if (!$ratings[$userCookie]) {
                $userID = isset($this->gameStatus) &&
                isset($this->gameStatus[$userCookie]) &&
                isset($this->gameStatus['users'][$this->gameStatus[$userCookie]]['userID'])
                    ? $this->hash_str_2_int($this->gameStatus['users'][$this->gameStatus[$userCookie]]['userID'])
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
        }

        if (is_array($userCookie)) {
            $ratings = $this->getRatingWithCommonID(
                $this->getCommonID(
                    $userCookie['cookie'],
                    $this->hash_str_2_int($userCookie['userID'])
                ),
                $userCookie['cookie'],
                $this->hash_str_2_int($userCookie['userID'])
            );

            return count($ratings) ? $ratings[0] : false;
        }

        if (!($ratings[$userCookie] = unserialize($this->p->redis->get('erudit.rating_cache_' . $userCookie)))) {
            $ratings[$userCookie] = $this->getRatingWithCommonID(
                $this->getCommonID(
                    $userCookie
                ),
                $userCookie,
                false
            );
        }

        if ($userCookie && !$ratings[$userCookie]) {
            $ratings[$userCookie] = [0 => ['cookie' => $userCookie, 'rating' => 1700, 'games_played' => 0, 'win_percent' => 0, 'inactive_percent' => 'N/A', 'top' => 'Не в ТОПе']];
        }

        return $this->normalizeRatings($ratings);
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
            . Hints::getHint($this->User, $this->gameStatus, $this->gameStatus['users'][$this->gameStatus[$this->User]]['rating'] ?? false);
    }

    private function statusComments_desync()
    {
        return 'Синхронизируемся с сервером..'
            . Hints::getHint($this->User, $this->gameStatus, $this->gameStatus['users'][$this->gameStatus[$this->User]]['rating'] ?? false);
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
                . Hints::getHint($this->User, $this->gameStatus, $this->gameStatus['users'][$this->gameStatus[$this->User]]['rating'] ?? false);
        }
    }

    private function statusComments_initGame()
    {
        if ($this->gamePlayersWaiting) {
            return '<strong>Подбор игры!</strong> <br />Ожидает игроков: <strong>' . $this->gamePlayersWaiting . '</strong>';
        } else {
            return '<strong>Подбор игры!</strong> <br />Ожидаем других игроков';
        }
    }


    private function statusComments_preMyTurn()
    {
        if ($this->gameStatus['turnNumber'] == 1) {
            $ratings = $this->getRatings($this->User);
            return 'Игра до <strong>' . $this->gameStatus['winScore'] . '</strong> очков<br />Ваш ход следующий - приготовьтесь!' . '<br />Ваш текущий рейтинг - <strong>' . $ratings[0]['rating'] . '</strong>';
        } else {
            return $this->gameStatus['users'][$this->gameStatus['activeUser']]['username'] . ' ходит. <br />Ваш ход следующий - приготовьтесь!'
                . Hints::getHint($this->User, $this->gameStatus, $this->gameStatus['users'][$this->gameStatus[$this->User]]['rating'] ?? false);
        }
    }

    public function changeFishki($fishkiToChange)
    {
        //print_r($fishkiToChange); exit();
        if ($this->getUserStatus() != 'myTurn') {
            return $this->checkGameStatus();
        }

        if (count($fishkiToChange)) {
            $this->addToLog('меняет фишки и пропускает ход', $this->numUser);

            foreach ($fishkiToChange as $newFishka => $on) {
                $fishkaCode = explode('_', $newFishka);
                //print_r($fishkaCode); exit();
                $fishkaCode = $fishkaCode[2];

                foreach ($this->gameStatus['users'][$this->numUser]['fishki'] as $num => $code) {
                    if ($fishkaCode == $code) {
                        //print_r($fishkaCode); exit();
                        unset($this->gameStatus['users'][$this->numUser]['fishki'][$num]);

                        array_push($this->gameStatus['bankFishki'], $fishkaCode);

                        break;
                    }
                }
            }

            shuffle($this->gameStatus['bankFishki']);

            $addFishki = $this->giveFishki($this->chisloFishek - count($this->gameStatus['users'][$this->numUser]['fishki']));
            $this->gameStatus['users'][$this->numUser]['fishki'] = array_merge($this->gameStatus['users'][$this->numUser]['fishki'], $addFishki);


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
        //Новая версия

        foreach ($this->gameStatus['users'] as $num => $user) {
            $this->updateUserStatus('gameResults', $user['ID']);

            if (
                !empty($user['ID'])
                && strstr($user['ID'], 'botV3#') === false
                && !empty($this->gameStatus['users'][$num]['last_request_num'])
                && $this->gameStatus['users'][$num]['last_request_num'] > 10
            ) {
                $this->p->redis->setex(self::CHECK_STATUS_RESULTS_KEY . $user['ID'], self::CHECK_STATUS_RESULTS_KEY_TTL, $this->gameStatus['users'][$num]['last_request_num']);
            }
        }
    }

    private function getUserStatus($user = FALSE)
    {
        return $this->gameStatus['users'][$this->gameStatus[($user ? $user : $this->User)]]['status'];
        //новая версия
    }

    public function updateUserStatus($newStatus, $user = FALSE)
    {
        if ($user == FALSE) {
            $user = $this->User;
        }
        //print_r($user);
        if (isset($this->gameStatus[$user])) {
            $this->gameStatus['users'][$this->gameStatus[$user]]['status'] = $newStatus;
            //Обновили статус по новой версии

            if ($newStatus == 'myTurn') {
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
        $desk = unserialize($this->p->redis->get('erudit.current_game_' . $this->currentGame));
        //Текущая доска
        $cells = json_decode($_POST['cells'], true);
        //Присланная доска

        $new_fishki = $this->gameStatus['lngClass']::submit($cells, $desk, $this->gameStatus);
        $summa = 0;
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

        if ($result !== '') {
            $result .= '<strong>ИТОГО: ' . $summa . '</strong>';
        }
        return json_encode($result);
    }

    public function lockTry()
    {
        if ($this->isStateLocked) {
            return true;
        }

        $lockTime = $this->p->redis->hget('erudit.games_' . date('Y_m_d') . '_locks', $this->currentGame . '_lock_time');
        $cycleBeginTime = date('U');
        //Будем ждать освобождения семафора, не более $this->turnDeltaTime
        while ((date('U') - $cycleBeginTime) <= $this->turnDeltaTime) {

            if (
                (
                    $this->p
                        ->redis
                        ->hincrby('erudit.games_' . date('Y_m_d') . '_locks', $this->currentGame . '_lock', 1)
                    ==
                    1
                )
                ||
                (
                    (date('U') - $lockTime) > $this->turnDeltaTime
                )
            ) {
                $this->p->redis->hset('erudit.games_' . date('Y_m_d') . '_locks', $this->currentGame . '_lock', 0);
                $this->p->redis->hset('erudit.games_' . date('Y_m_d') . '_locks', $this->currentGame . '_lock_time', date('U'));
                $this->isStateLocked = TRUE;

                return true;
                //Семафор освободился
            }

            sleep(0.1);
        }
        //Ждали слишком долго - возвращаем десинхрон
        return false;
    }

    private function unlock()
    {
        $this->p->redis->hset('erudit.games_' . date('Y_m_d') . '_locks', $this->currentGame . '_lock', 0);
        $this->isStateLocked = FALSE;
    }

    private function desync()
    {
        $this->updateUserStatus('desync');

        return $this->makeResponse($arr = ['gameState' => 'desync']);
    }

    public function submitTurn()
    {
        if ($this->getUserStatus() != 'myTurn') {
            $this->addToLog('пытается сделать ход не в свою очередь (ход #' . $this->gameStatus['turnNumber'] . ')', $this->numUser);

            return $this->checkGameStatus();
        }

        $desk = unserialize($this->p->redis->get('erudit.current_game_' . $this->currentGame));
        //Текущая доска
        $cells = json_decode($_POST['cells'], true);
        //Присланная доска

        $new_fishki = $this->gameStatus['lngClass']::submit($cells, $desk, $this->gameStatus);
        if ($new_fishki === FALSE) {
            //$this->p->redis->rpush('erudit.bad_submits', $_POST['cells']);
            $this->addToLog('не составил ни одного слова (ход #' . $this->gameStatus['turnNumber'] . ')', $this->numUser);
            $this->nextTurn();
            $this->destruct();
            //Сохранили статус игры

            //return json_encode(array_merge($cells, [$this->gameStatus['users'][$this->numUser]['fishki']]));
            return json_encode(array_merge($desk, [$this->gameStatus['users'][$this->numUser]['fishki']]));
            //Сделать через отправку статуса
        }

        try{
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

                $addFishki = $this->giveFishki($this->chisloFishek - count($this->gameStatus['users'][$this->numUser]['fishki']));//count($new_fishki['new']));
                $this->gameStatus['users'][$this->numUser]['fishki'] = array_merge($this->gameStatus['users'][$this->numUser]['fishki'], $addFishki);

                foreach ($new_fishki['words'] as $word => $price) {
                    $ochkiZaHod += $price;
                    //Добавили очков за ход
                    $this->gameStatus['wordsAccepted'][$word] = $word;
                    //Добавили слово в список сыгранных слов

                    $arr = Prizes::checkDayWordLenRecord($word);
                    foreach ($arr as $period => $value) {
                        $this->addToLog("устанавливает рекорд по длине слова за $period - <strong>$word</strong>", $this->numUser);
                    }

                    $arr = Prizes::checkDayWordPriceRecord($word, $price);
                    foreach ($arr as $period => $value) {
                        $this->addToLog("устанавливает рекорд по стоимости слова за $period - <strong>$word - $price</strong>", $this->numUser);
                    }
                }
            }

            $this->gameStatus['users'][$this->numUser]['score'] += $ochkiZaHod;
            if ($ochkiZaHod == 0) {
                $this->gameStatus['users'][$this->numUser]['lostTurns']++;
            } else {
                $arr = Prizes::checkDayTurnPriceRecord($ochkiZaHod);
                foreach ($arr as $period => $value) {
                    $this->addToLog("устанавливает рекорд по стоимости хода за $period - <strong>$ochkiZaHod</strong>", $this->numUser);
                }
            }
            $this->addToLog('зарабатывает ' . $ochkiZaHod . ' за ход #' . $this->gameStatus['turnNumber'] . $this->logSlov($new_fishki['words']) . ($vseFishki ? ' <span title="За все фишки" style="color: green;">(<strong>+15</strong>)</span>' : ''), $this->numUser);

            if ($this->gameStatus['users'][$this->numUser]['score'] >= $this->gameStatus['winScore']) {
                $this->addToLog('Побеждает со счетом ' . $this->gameStatus['users'][$this->numUser]['score'], $this->numUser);

                $arr = Prizes::checkDayGamePriceRecord($this->gameStatus['users'][$this->numUser]['score']);
                foreach ($arr as $period => $value) {
                    $this->addToLog("устанавливает рекорд набранных очков в игре за $period - <strong>{$this->gameStatus['users'][$this->numUser]['score']}</strong>", $this->numUser);
                }

                $this->storeGameResults($this->User);
                //Обнаружен выигравший
            } elseif ($this->gameStatus['users'][$this->numUser]['lostTurns'] >= 3) {
                $this->storeGameResults($this->lost3TurnsWinner($this->numUser));
                //Обнаружен выигравший
            } elseif (count($this->gameStatus['users'][$this->numUser]['fishki']) === 0) {
                $this->addToLog('закончились фишки - конец игры!', $this->numUser);
                $this->storeGameResults($this->endOfFishki());
            } //Обнаружен выигравший
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
                    $this->addToLog("устанавливает рекорд по числу сыгранных партий за $period - <strong>" . reset($record) . "</strong>", $this->gameStatus[key($record)]);
                }
            }
        } catch (Exception $e){
            $this->p->redis->rpush('erudit.exceptions', serialize([
                'date' => date('U'),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]));
        }

        $this->p->redis->setex('erudit.current_game_' . $this->currentGame, $this->cacheTimeout, serialize($cells));
        //Измененная Присланная доска -> текущая

        $this->destruct();
        //Сохранили статус игры

        print json_encode(array_merge($cells, [$this->gameStatus['users'][$this->numUser]['fishki']]));
        //Сделать через отправку статуса

    }

    private function logSlov($words)
    {
        $res = '<br />';
        foreach ($words as $word => $price) {
            if (true)//( isset($_SERVER['HTTP_ORIGIN']) && ($_SERVER['HTTP_ORIGIN'] != 'https://xn--d1aiwkc2d.club') )
            {
                $res .= " <a href=\"#\" onclick=\"event.preventDefault(); openWindowGlobal('" . urlencode($word) . "').then((data1) => { var openWindow = window.open('about:blank', 'Слово','location=no');setTimeout(function () {openWindow.document.body.innerHTML = data1;} , 1000) });\">$word</a>-$price&nbsp;";
            } else {
                $res .= " <a href=\"https://gufo.me/search?term=" . urlencode($word) . "\" target=\"_blank\">$word</a>-$price&nbsp;";
            }
        }
        //gufo.me нахуй
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
        if (!($rangedOnlinePlayers = unserialize($this->p->redis->get('erudit.num_rating_players')))) {
            $lastGame = $this->p->redis->get('erudit.num_games');
            $players = [];
            for ($i = $lastGame; $i > ($lastGame - 50); $i--) {
                if ($game = unserialize($this->p->redis->get("erudit.game_status_" . $i))) {
                    if (!isset($game['results'])) {
                        foreach ($game['users'] as $num => $user) {
                            if (strstr($user['ID'], 'botV3#') === false) {
                                $players[$user['ID']] = ['cookie' => $user['ID'], 'userID' => (isset($user['userID']) ? $user['userID'] : false)];
                            }
                        }
                    }
                }
            }
            $rangedOnlinePlayers = [1900 => 0, 2000 => 0, 2100 => 0, 2200 => 0, 2300 => 0, 2400 => 0];
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

                }
            }

            $this->p->redis->setex('erudit.num_rating_players', $this->ratingsCacheTimeout, serialize($rangedOnlinePlayers));
        }

        if ($rangedOnlinePlayers[1900]) {
            $cnt = $this->p->redis->hlen('erudit.rating_waiters');
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
        if ($this->p->redis->hget("erudit.inviteplayers_waiters", $this->User)) {
            return true;
        }

        if ($this->p->redis->hget("erudit.inviteENplayers_waiters", $this->User)) {
            return true;
        }

        return false;
    }

    public function checkGameStatus()
    {
        if (!$this->currentGame) {
            if (isset($_GET['queryNumber']) && ($_GET['queryNumber'] == 1) && !$this->isUserInInviteQueue()) {
                return $this->makeResponse(['gameState' => 'chooseGame', 'gameSubState' => 'choosing', 'players' => $this->onlinePlayers()]);
            } else {
                return $this->startGame();
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
            if (!(($desk = $this->p->redis->get(('erudit.current_game_' . $this->currentGame))) && $this->currentGame)) {
                $desk = serialize([]);
            }

            $result = $this->gameStatus['results'];
            if (isset($result['winner'])) {
                if ($result['winner'] == $this->User) {
                    return $this->makeResponse(['gameState' => 'gameResults', 'comments' => "<strong style=\"color:green;\">Вы выиграли!</strong><br/>Начните новую игру", 'desk' => unserialize($desk)]);
                } else {
                    return $this->makeResponse(['gameState' => 'gameResults', 'comments' => "<strong style=\"color:red;\">Вы проиграли!</strong><br/>Начните новую игру", 'desk' => unserialize($desk)]);
                }
            }
        }


        //Поставим коррекцию времени начала хода для учета периодичности запросов пользователей
        if (($this->getUserStatus() == 'myTurn') && !$this->gameStatus['aquiringTimes'][$this->gameStatus['turnNumber']]) {
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
                //Начало фрагмента для объединения
                if (!(($desk = $this->p->redis->get(('erudit.current_game_' . $this->currentGame))) && $this->currentGame)) {
                    $desk = serialize([]);
                }
                $result = $this->gameStatus['results'];
                if (isset($result['winner'])) {
                    if ($result['winner'] == $this->User) {
                        return $this->makeResponse(['gameState' => 'gameResults', 'comments' => "<strong style=\"color:green;\">Вы выиграли!</strong><br/>Начните новую игру", 'desk' => unserialize($desk)]);
                    } else {
                        return $this->makeResponse(['gameState' => 'gameResults', 'comments' => "<strong style=\"color:red;\">Вы проиграли!</strong><br/>Начните новую игру", 'desk' => unserialize($desk)]);
                    }
                    //Конец фрагмента для объединения
                }
            } else {
                $this->nextTurn();
            }

        }

        if (($desk = $this->p->redis->get(('erudit.current_game_' . $this->currentGame))) && $this->currentGame) {
            return $this->makeResponse(['gameState' => $this->getUserStatus(), 'desk' => unserialize($desk)]);
        } else {
            if ($status = $this->getUserStatus()) {
                return $this->makeResponse(['gameState' => $status]);
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
                $this->addToLog('остался без фишек! Победитель - ' . $this->gameStatus['users'][$userWinner]['username'] . ' со счетом ' . $this->gameStatus['users'][$userWinner]['score'], $num);
            } else {
                $this->addToLog('остался без фишек! Вы победили со счетом ' . $this->gameStatus['users'][$userWinner]['score'], $num);
            }
        }

        return $this->gameStatus['users'][$userWinner]['ID'];
    }

    private function lost3TurnsWinner($numLostUser)
    {
        $maxres = 0;
        $userWinner = 0;
        if ($this->gameStatus['users'][$numLostUser]['score'] === 0) {
            $this->gameStatus['users'][$numLostUser]['isActive'] = FALSE;
        }

        foreach ($this->gameStatus['users'] as $num => $user) {
            if (($maxres <= $user['score']) && $user['isActive']) {
                $maxres = $user['score'];
                $userWinner = $num;
            }
        }
        $this->addToLog('пропустил 3 хода! Победитель - ' . $this->gameStatus['users'][$userWinner]['username'] . ' со счетом ' . $this->gameStatus['users'][$userWinner]['score'], $numLostUser);

        return $this->gameStatus['users'][$userWinner]['ID'];
    }

    private function nextTurn()
    {
        foreach ($this->gameStatus['users'] as $numUser => $user) {
            $this->updateUserStatus('otherTurn', $user['ID']);
            //Дали всем игрокам статус - другойХодит
            if (
                ($this->gameStatus['turnNumber'] > $this->activeGameUsers())
                &&
                !isset($this->gameStatus['users'][$numUser]['lastActiveTime'])
                &&
                $this->gameStatus['turnNumber'] > ($this->gameStatus['users'][$numUser]['inactiveTurn'] + $this->activeGameUsers())
            ) {
                $this->exitGame($numUser, FALSE);
            }
            //Выпиливаем неактивного юзера
        }

        if ($this->activeGameUsers() < 2) {
            //Пользователь остался в игре один и выиграл
            return $this->checkGameStatus();
        }

        $isActiveUserFound = FALSE;

        while (!$isActiveUserFound) {
            $nextActiveUser = ($this->gameStatus['activeUser'] + 1) % count($this->gameStatus['users']);
            $this->gameStatus['activeUser'] = $nextActiveUser;
            if ($this->gameStatus['users'][$nextActiveUser]['isActive']) {
                $isActiveUserFound = TRUE;
            }
        }

        $this->updateUserStatus('myTurn', $this->gameStatus['users'][$nextActiveUser]['ID']);
        //Прописали статус новому активному пользователю

        $nextPreMyTurnUser = ($nextActiveUser + 1) % count($this->gameStatus['users']);


        $this->updateUserStatus('preMyTurn', $this->gameStatus['users'][$nextPreMyTurnUser]['ID']);
        //Прописали статус следующему преМайТерн-юзеру

        $this->gameStatus['turnNumber']++;

        $this->adv2Chat();//Показ рекламы в чате

        $this->gameStatus['turnBeginTime'] = date('U');
        $this->gameStatus['aquiringTimes'][$this->gameStatus['turnNumber']] = false;

        return;
    }

    private function adv2Chat()
    {
        if (($this->gameStatus['turnNumber'] == 2) && isset($this->config['advMessage']) && ($this->config['advMessage'] !== false)) {
            $this->addToChat($this->config['advMessage'], 'adv', FALSE);
        }
        //Кидаем всем в чат рекламу (кроме пользователей яндекса)
    }

    public function exitGame($numuser = false, $commitState = TRUE)
    {
        if (!$numuser) {
            $numuser = $this->numUser;
        }

        $this->p->redis->del('erudit.get_game_' . $this->gameStatus['users'][$numuser]['ID']);
        //Удалили указатель на текущую игру для пользователя

        $this->gameStatus['users'][$numuser]['isActive'] = FALSE;
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

        Cache::del('erudit.get_game_' . $this->User);
        //Удалили указатель на текущую игру для пользователя

        if ($this->currentGame && ($_POST['gameState'] == 'initGame')) {
            return $this->checkGameStatus();
            //Пользователь думает, что находится в подборе игры, но игра уже началась
        }

        if ($this->currentGame) {
            $this->gameStatus['users'][$this->numUser]['isActive'] = FALSE;
            //Игрок стал неактивен
            $this->addToLog("покинул игру", $this->numUser);
        }

        return $this->makeResponse(['gameState' => 'chooseGame', 'gameSubState' => 'choosing']);
    }

    private function makeWishWinscore()
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

        $variants = [200 => 0, 300 => 0];

        foreach ($variants as $ochki => $delta) {
            $variants[$ochki] = abs($srednee - rand($ochki - 30, $ochki + 10));
        }

        asort($variants);

        foreach ($variants as $ochki => $delta) {
            return $ochki;
        }
        //Голосование проведено
    }

    private function makeWishTime()
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

        $variants = [60 => 0, 90 => 0, 120 => 0];

        foreach ($variants as $vremya => $delta) {
            $variants[$vremya] = abs($srednee - $vremya);
        }

        asort($variants);
        //print_r($variants);

        foreach ($variants as $vremya => $delta) {
            return $vremya;
        }
        //Голосование проведено
    }

    public function gameStarted($statusUpdateNeeded = FALSE)
    {
        if ($statusUpdateNeeded) {
            $firstTurnUser = rand(0, count($this->currentGameUsers) - 1);
            $this->gameStatus['gameNumber'] = $this->currentGame;
            $this->gameStatus['users'][$firstTurnUser]['status'] = 'myTurn';
            $this->gameStatus['activeUser'] = $firstTurnUser;
            $this->gameStatus['gameBeginTime'] = date('U');
            $this->gameStatus['turnBeginTime'] = $this->gameStatus['gameBeginTime'];
            $this->gameStatus['turnTime'] = $this->makeWishTime();//(is_array($this->turnTime) ? $this->turnTime[count($this->currentGameUsers)] : $this->turnTime);
            $this->gameStatus['turnNumber'] = 1;
            $this->gameStatus['firstTurnUser'] = $firstTurnUser;
            $this->gameStatus['bankFishki'] = $this->gameStatus['lngClass']::generateBankFishki();
            $this->gameStatus['wordsAccepted'] = [];
            $this->gameStatus['winScore'] = $this->makeWishWinscore();//$this->winScore;//300;//( (rand(1,12) % 2) ? 200 : 300);
            $this->gameStatus['aquiringTimes'][$this->gameStatus['turnNumber']] = false;
            //print '!!!!!!!!!!!!';print_r($this->currentGameUsers);print_r($this->gameStatus);
            $this->updateUserStatus('myTurn', $this->currentGameUsers[$firstTurnUser]);
            //Назначили ход случайному юзеру

            $ost = ($firstTurnUser - 1) % count($this->gameStatus['users']);
            if ($ost >= 0) {
                $preMyTurnUser = $ost;
            } else {
                $preMyTurnUser = count($this->gameStatus['users']) + $ost;
            }
            $this->updateUserStatus('preMyTurn', $preMyTurnUser);
            //Вычислили игрока, идущего за первым и дали ему статус преМайТерн

            foreach ($this->gameStatus['users'] as $num => $user) {
                $this->gameStatus['users'][$num]['fishki'] = $this->giveFishki(7);
                //Раздали фишки игрокам
                $this->gameStatus['users'][$num]['lostTurns'] = 0;
                $this->gameStatus['users'][$num]['inactiveTurn'] = 1000;
                //Сделали невозможным значение терна инактив

                $userRating = $this->getRatings($user['ID']);
                $this->gameStatus['users'][$num]['rating'] = $userRating ? $userRating[0]['rating'] : 'new_player';
                $this->gameStatus['users'][$num]['common_id'] = $this->getPlayerID($user['ID'], 'createCommonID');
                //Прописали рейтинг и common_id игрока в статусе игры - только для games_statistic.php
            }
            $this->addToLog('Новая игра начата! <br />Набери <strong>' . $this->gameStatus['winScore'] . '</strong> очков');
        }


        return $this->makeResponse(['gameState' => $this->getUserStatus(),
            'usersInfo' => serialize($this->currentGameUsers)]);
    }

    private function userLastActivity($user = FALSE)
    {
        $lastAct = $this->p->redis->get('erudit.user_' . ($user ? $user : $this->User) . '_last_activity');
        if (!$lastAct) {
            return FALSE;
        }
        //Если кеш сгорел, возвращаем неактивность

        if ((date('U') - $lastAct) > $this->activityTimeout) {
            return FALSE;
        }
        //Если неактивен долго, возвращаем неактивность

        return TRUE;
    }


    private function addToGame($waiting_game, $timeWaiting = false)
    {
        $time = date('U') - ($timeWaiting ? $timeWaiting : 0);
        $waiting_game = array_merge($waiting_game, [$this->User => ['time' => $time, 'options' => (isset($_POST['ochki_num']) ? $_POST : false)]]);
        $this->p->redis->rpush('erudit.games_waiting_v2', serialize($waiting_game));
        //Поместили список игроков в начало очереди
        $this->gamePlayersWaiting = count($waiting_game);
        $this->updateUserStatus('initGame');
        if ($timeWaiting) {
            return $this->makeResponse(['gameState' => 'initGame', 'gameSubState' => $this->gamePlayersWaiting, 'timeWaiting' => $timeWaiting, 'gameWaitLimit' => $this->gameWaitLimit]);
        } else {
            return $this->makeResponse(['gameState' => 'initGame', 'gameSubState' => $this->gamePlayersWaiting, 'gameWaitLimit' => $this->gameWaitLimit]);
        }
    }

    private function cleanUp($User, $queue)
    {
        $this->p->redis->hdel('erudit.' . $queue . 'players_waiters', $User);
    }

    public function startGame()
    {
        if ($this->currentGame && is_array($this->currentGameUsers)) {
            return $this->gameStarted(FALSE);
            //Вернули статус начатой игры без обновления статусов в кеше
        }

        return (new \Erudit\Queue($this->User, $this, $this->p, $_POST))->doSomethingWithThisStuff((isset($_GET['lang']) && $_GET['lang'] == 'EN') ? $_GET['lang'] : '');
    }

    private function processInvites(&$arr)
    {
        $gameSubState = $this->gameStatus['invite'];

        if ($this->gameStatus['invite'] == 'newGameStarting') {
            //ini_set("display_errors", 1); error_reporting(E_ALL);
            $gameSubState .= rand(1, 100);
            $arr['gameSubState'] = $gameSubState;
            $arr['inviteStatus'] = 'newGameStarting';

            (new Queue($this->User, $this, $this->p, ['lang' => ($this->gameStatus['lang'] == 'EN' ? 'EN' : '')]))
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
            if ($this->gameStatus['invite'] == $this->User) {
                $arr['comments'] .= "<br />Ожидаем подтверждения соперников. В игре осталось: $numActiveUsers";
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

        $arr['gameSubState'] = $gameSubState . $arr['inviteStatus'];
    }

    public function makeResponse(array $arr)
    {
        if (!isset($arr['gameState'])) {
            return json_encode($arr);
        }

        if (isset($this->gameStatus[$this->User])) {
            if (isset($this->statusComments[$arr['gameState']])) {
                $arr = array_merge($arr, ['comments' => call_user_func([$this, 'statusComments_' . $arr['gameState']])]);
            }
            if ($arr['gameState'] == 'desync') {
                return json_encode($arr);
            }
            if (is_array($this->gameStatus['users'][$this->numUser]['fishki'])) {
                if (count($this->gameStatus['users'][$this->numUser]['fishki'])) {
                    $arr = array_merge($arr, ['fishki' => $this->gameStatus['users'][$this->numUser]['fishki']]);
                }
            }

            //if ($this->gameStatus['turnNumber'] >= 0)
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
                $turnTimeLeft = ($this->gameStatus['aquiringTimes'][$this->gameStatus['turnNumber']] + $this->gameStatus['turnTime']) - date('U');
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

            $arr = array_merge($arr, ['timeLeft' => $turnTimeLeft, 'secondsLeft' => (int)$turnSecondsLeft, 'minutesLeft' => $turnMinutesLeft]);

            if ($arr['gameState'] == 'myTurn') {
                $arr = array_merge($arr, ['turnTime' => $this->gameStatus['turnTime']]);
            }

            if ($arr['gameState'] == 'gameResults' && isset($this->gameStatus['invite'])) {
                $this->processInvites($arr);
            }

            if (isset($this->gameStatus['users'][$this->numUser]['logStack'])) {
                if (count($this->gameStatus['users'][$this->numUser]['logStack'])) {
                    $log = [];
                    while ($logRecord = array_shift($this->gameStatus['users'][$this->numUser]['logStack'])) {
                        $log[] = trim(($logRecord[0] !== false ? $this->gameStatus['users'][$logRecord[0]]['username'] : '') . ' ' . $logRecord[1]);
                    }
                    $arr = array_merge($arr, ['log' => $log]);
                    //Добавили лог событий в ответ юзеру
                }
            }
            if ($arr['gameState'] != 'addToChat') {
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
            return json_encode(array_merge($arr, ['query_number' => $_GET['queryNumber']]));
        } else {
            return json_encode($arr);
        }
    }
}


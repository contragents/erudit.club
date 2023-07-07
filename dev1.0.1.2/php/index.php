<?php
if (strpos(__DIR__, 'dev1.0.1.2')) {
    ini_set("display_errors", 1);
    error_reporting(E_ALL);
}

if (!isset($scriptName)) {
    throw new BadRequest('Wrong query');
}

const PAGE_HIDDEN_SLEEP_TIME = 10;
const SCRIPTS = [
    'status_checker' => 'statusChecker',
    'turn_submitter' => 'turnSubmitter',
    'new_game' => 'newGame',
    'word_checker' => 'wordChecker',
    'players_ratings' => 'playersRatings',
    'complain' => 'complain',
    'send_chat_message' => 'sendChatMessage',
    'change_fishki' => 'changeFishki',
    'cookie_checker' => 'cookieChecker',
    'invite_to_new_game' => 'inviteToNewGame',
    'player_cabinet' => 'playerCabinet',
    'set_player_name' => 'setPlayerName',
    'avatar_upload' => 'avatarUpload',
    'set_player_avatar_url' => 'setAvatarUrl',
    'merge_the_ids' => 'mergeTheIds',
    'set_inactive' => 'setInactive',
    'word' => 'word'
];

const CATCH_REFERER_KEY = 'erudit.catched_referer';

require_once 'autoload.php';
include_once 'cors.php';

if (function_exists(SCRIPTS[$scriptName])) {
    $func = SCRIPTS[$scriptName];
    return $func();
} else {
    throw new BadRequest('Wrong query');
}

function statusChecker()
{
    if ($_GET['page_hidden'] ?? false === 'true') {
        sleep(PAGE_HIDDEN_SLEEP_TIME);
    }

    print (new Erudit\Game())->checkGameStatus();
}

function turnSubmitter()
{
    print (new Erudit\Game())->submitTurn();
}

function newGame()
{
    print (new Erudit\Game())->newGame();
}

function wordChecker()
{
    print (new Erudit\Game())->wordChecker();
}

function playersRatings()
{
    print (new Erudit\Game())->playersInfo();
}

function complain()
{
    $resp = json_encode(['message' => 'Ошибка отправки сообщения']);


    if (isset($_POST['chatTo'])) {
        include_once 'EruditGame.php';

        if (isset($_POST['chatTo'])) {
            $resp = ($obj = new Erudit\Game())->addComplain($_POST['chatTo']);
        }
    }

    print $resp;
}

function sendChatMessage()
{
    $resp = json_encode(['message' => 'Ошибка отправки сообщения']);


    if (isset($_POST['messageText'])) {
        include_once 'EruditGame.php';

        if (isset($_POST['chatTo'])) {
            $resp = (new Erudit\Game())->addToChat($_POST['messageText'], $_POST['chatTo']);
        }
    }

    print $resp;
}

function changeFishki()
{
    print (new Erudit\Game())->changeFishki($_POST);
}

function cookieChecker()
{
    $_GET['queryNumber'] = 1;
    $resp = (new Erudit\Game())->checkGameStatus();
    print $resp;
}

function inviteToNewGame()
{
    print (new Erudit\Game())->inviteNewGame();
}

function playerCabinet()
{
    // todo remove after app check
    $nothing = Config::$config;
    Cache::hset(CATCH_REFERER_KEY, time() % 100, $_SERVER['HTTP_REFERER'] ?? 'NA');

    print (new Erudit\Game())->playerCabinetInfo();
}

function setPlayerName()
{
    print (new Erudit\Game())->saveUserNameWithID($_POST['name'], $_POST['commonID'] ?? false);
}

function avatarUpload()
{
    print Dadata\Players::avatarUpload($_FILES, $_COOKIE['erudit_user_session_ID']);
}

function setAvatarUrl() {
    if (isset($_POST['avatar']) && $_POST['avatar'] != '' && isset($_POST['commonID']) && $_POST['commonID'] != '') {
        print Dadata\Players::addUserAvatarUrl($_POST['avatar'], $_POST['commonID']);
    } else {
        print  json_encode(['result' => 'Ошибка сохранения URL', 'message' => 'Ошибка сохранения URL']);
    }
}

function mergeTheIds()
{
    if (isset($_POST['oldKey']) && $_POST['oldKey'] != '' && isset($_POST['commonID']) && $_POST['commonID'] != '') {
        print (new Erudit\Game())->mergeTheIDs($_POST['oldKey'], $_POST['commonID']);
    } else {
        print  json_encode(['result' => 'Ошибка Объединения аккаунтов']);
    }
}

function setInactive()
{
    print (new Erudit\Game())->setInactive();
}

function word(): bool
{
    $CONTENT_SELECT = "SELECT 
content COLLATE utf8_general_ci, 
content_perevod COLLATE utf8_general_ci
FROM 
gufo_me 
WHERE 
slovo = '" . urldecode($_GET['word']) . "'
UNION
SELECT
comment COLLATE utf8_general_ci as content,
substring(comment,1,0) COLLATE utf8_general_ci as content_perevod
FROM
dict_cambrige
WHERE 
slovo = '" . urldecode($_GET['word']) . "'
UNION
SELECT
comment COLLATE utf8_general_ci as content,
substring(comment,1,0) COLLATE utf8_general_ci as content_perevod
FROM
dict
WHERE 
slovo = '" . urldecode($_GET['word']) . "';";

    $res = DB::queryArray($CONTENT_SELECT);
    if (!is_array($res) || empty($res)) {
        print "Слово не найдено.";
        return false;
    }

    $row = current($res);
    if (!is_array($row) || empty($row)) {
        print "Слово не найдено.";
        return false;
    }

    foreach ($row as $field => $value) {
        if ($spacePos = strpos($field, ' ')) {
            $row[substr($field, 0, $spacePos)] = $value;
        }
    }

    if (strstr($_SERVER['HTTP_REFERER'] ?? '', 'andex') || strstr($_SERVER['HTTP_REFERER'] ?? '', '-5.su')) {
        $row['content'] = str_replace('href=', '', $row['content']);
        $row['content_perevod'] = str_replace('href=', '', $row['content_perevod']);
    }

    $row['content'] = str_ireplace(
        $_GET['word'] . ' noun',
        '<h2>' . strtoupper($_GET['word']) . ' noun</h2>',
        $row['content']
    );

    print str_replace(
        ["\r\n", "\n"],
        '<br />',
        str_replace('href="', 'href="https://xn--d1aiwkc2d.club', $row['content'] . $row['content_perevod'])
    );

    return true;
}
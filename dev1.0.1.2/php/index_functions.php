<?php

const PAGE_HIDDEN_SLEEP_TIME = 10;
const SCRIPTS = [
    'init_game' => 'initGame',
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

function initGame()
{
    $res = (new Erudit\Game())->initGame();
    print $res;
    return $res;
}

function statusChecker()
{
    if ($_GET['page_hidden'] ?? false === 'true') {
        sleep(PAGE_HIDDEN_SLEEP_TIME);
    }
    $res = (new Erudit\Game())->checkGameStatus();
    print $res;
    return $res;
}

function turnSubmitter()
{
    $res = (new Erudit\Game())->submitTurn();
    print $res;
    return $res;
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
    $resp = json_encode(['message' => 'Ошибка отправки жалобы<br><br>Выберите игрока']);

    if (isset($_POST['chatTo']) && $_POST['chatTo'] !== 'words') {
        $resp = ($obj = new Erudit\Game())->addComplain($_POST['chatTo']);
    }

    print $resp;
}

function sendChatMessage()
{
    $resp = json_encode(['message' => 'Ошибка отправки сообщения']);

    if (!empty($_POST['messageText']) && isset($_POST['chatTo'])) {
        $resp = (new Erudit\Game())->addToChat($_POST['messageText'], $_POST['chatTo']);
    }

    print $resp;
}

function changeFishki()
{
    $resp = (new Erudit\Game())->changeFishki($_POST);
    print $resp;

    return $resp;
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

function setAvatarUrl()
{
    if (isset($_POST['avatar']) && $_POST['avatar'] != '' && isset($_POST['commonID']) && $_POST['commonID'] != '') {
        print Dadata\Players::addUserAvatarUrl($_POST['avatar'], $_POST['commonID']);
    } else {
        print  json_encode(['result' => 'Ошибка сохранения URL', 'message' => 'Ошибка сохранения URL']);
    }
}

function mergeTheIds()
{
    if (isset($_POST['oldKey']) && $_POST['oldKey'] != '' && isset($_POST['commonID']) && $_POST['commonID'] != '') {
        print (new Erudit\Game())->mergeTheIDs(base64_decode($_POST['oldKey']), $_POST['commonID']);
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
    $content = '';
    $result = true;

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
        $content .= "Слово не найдено.";
        $result = false;
    } else {
        $row = current($res);
        if (!is_array($row) || empty($row)) {
            $content .= "Слово не найдено.";
            $result = false;
        }

        foreach ($row as $field => $value) {
            if ($spacePos = strpos($field, ' ')) {
                $row[substr($field, 0, $spacePos)] = $value;
            }
        }
    }

    // убираем всякую херню после парсинга
    if (strstr($_SERVER['HTTP_REFERER'] ?? '', 'andex') || strstr($_SERVER['HTTP_REFERER'] ?? '', '-5.su')) {
        $row['content'] = str_replace('href=', '', $row['content']);
        $row['content_perevod'] = str_replace('href=', '', $row['content_perevod']);
    }

    $row['content'] = str_ireplace(
        $_GET['word'] . ' noun',
        '<h2>' . strtoupper($_GET['word']) . ' noun</h2>',
        $row['content']
    );

    $content .= str_replace(
        ["\r\n", "\n"],
        '<br />',
        str_replace('href="', 'href="https://xn--d1aiwkc2d.club', $row['content'] . $row['content_perevod'])
    );

    $content = preg_replace('/googletag\.cmd\.push\(.{0,400}\}\);/','', $content);

    if (($_GET['ingame'] ?? '') !== 'yes' && !isAndroidApp()) {
        $title = "Игра Эрудит.CLUB :: Словарь | " . $_GET['word'];
        $description = strip_tags($content);
        $description = mb_substr($description, 0, 500);
        $description = str_replace('"', "'", $description);

        $description = str_replace('  ', " ", $description);
        $description = str_replace('  ', " ", $description);
        $description = str_replace('  ', " ", $description);
        $description = str_replace('  ', " ", $description);
        $description = str_replace('  ', " ", $description);
        $description = str_replace('  ', " ", $description);
        $canonical = isset($_GET['voc'])
            ? ('<link rel="canonical" href="https://эрудит.club/dict/' . urlencode($_GET['word']) . '" />')
            : '';
        include(__DIR__ . '/../../tpl/main_header.php');
        print "<h1>{$_GET['word']}</h1>";
    }

    print $content;

    return $result;
}

function isAndroidApp(): bool
{
    if (isset($_COOKIE['DEVICE']) && $_COOKIE['DEVICE'] == 'Android') {
        return true;
    }

    if (isset($_COOKIE['PRODUCT']) && $_COOKIE['PRODUCT'] == 'RocketWeb') {
        return true;
    }

    if (strpos($_SERVER['HTTP_REFERER'] ?? '', 'app=1')) {
        return true;
    }

    return false;
}
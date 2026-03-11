<?php

const PAGE_HIDDEN_SLEEP_TIME = 10;
const SCRIPTS = [
    'init_game' => 'initGame',
    'status_checker' => 'statusChecker',
    'turn_submitter' => 'turnSubmitter',
    'new_game' => 'newGame',
    'word_checker' => 'wordChecker',
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
    'word' => 'word',
    'test' => 'test',
];

const CATCH_REFERER_KEY = 'erudit.catched_referer';

function test()
{
    try {
        $res = (new Erudit\Game())->checkGameStatus();
        print $res;
    } catch (Throwable $e) {
        print $e->__toString();
    }
}

function initGame()
{
    $res = (new Erudit\Game())->initGame();
    print $res;
}

function statusChecker()
{
    if (($_GET['page_hidden'] ?? false) === 'true' && ($_GET['queryNumber'] ?? 2) != 1) {
        sleep(PAGE_HIDDEN_SLEEP_TIME);
    }
    try {
        $res = (new Erudit\Game())->checkGameStatus();
        print $res;
    } catch (Throwable $e) {
        Cache::set('yandex_error', $e->getMessage());

        print json_encode($e);
    }
}

function turnSubmitter()
{
    $res = (new Erudit\Game())->submitTurn();
    print $res;
}

function newGame()
{
    print (new Erudit\Game())->newGame();
}

function wordChecker()
{
    print (new Erudit\Game())->wordChecker();
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
    print (new Erudit\Game())->playerCabinetInfo();
}

function setPlayerName()
{
    print (new Erudit\Game())->saveUserNameWithID($_POST['name'], $_POST['commonID'] ?? false);
}

function avatarUpload()
{
    print Dadata\Players::avatarUpload(
        $_FILES,
        Tg::$tgUser['user']['id'] ?? (Yandex::$yandexUser ?? $_COOKIE[CookieErudit::COOKIE_NAME])
    );
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
slovo = '" . urldecode($_REQUEST['word']) . "'
UNION
SELECT
comment COLLATE utf8_general_ci as content,
substring(comment,1,0) COLLATE utf8_general_ci as content_perevod
FROM
dict_cambrige
WHERE 
slovo = '" . urldecode($_REQUEST['word']) . "'
UNION
SELECT
comment COLLATE utf8_general_ci as content,
substring(comment,1,0) COLLATE utf8_general_ci as content_perevod
FROM
dict
WHERE 
slovo = '" . urldecode($_REQUEST['word']) . "';";

    $res = DB::queryArray($CONTENT_SELECT);
    if (!is_array($res) || empty($res)) {
        $content .= T::S("Слово не найдено.");
        $result = false;
    } else {
        $row = current($res);
        if (!is_array($row) || empty($row)) {
            $content .= T::S("Слово не найдено.");
            $result = false;
        } else {
            foreach ($row as $field => $value) {
                if ($spacePos = strpos($field, ' ')) {
                    $row[substr($field, 0, $spacePos)] = $value;
                }
            }
        }
    }

    if (!$result && ($_REQUEST['ingame'] ?? '') !== 'yes' && !isAndroidApp()) {
        header("HTTP/1.1 301 Moved Permanently");
        header("Location: " . Config::$config['domain'] . "/blog/");
        header("Connection: close");

        exit;
    }

    // убираем всякую херню после парсинга
    if (strstr($_SERVER['HTTP_REFERER'] ?? '', 'andex') || strstr($_SERVER['HTTP_REFERER'] ?? '', '-5.su')) {
        $row['content'] = str_replace('href=', '', $row['content']);
        $row['content_perevod'] = str_replace('href=', '', $row['content_perevod']);
    }

    $row['content'] = str_ireplace(
        $_REQUEST['word'] . ' noun',
        '<h2>' . strtoupper($_REQUEST['word']) . ' noun</h2>',
        $row['content']
    );

    $content .= str_replace(
        ["\r\n", "\n"],
        '<br />',
        str_replace('href="', 'href="' . Config::$config['domain'], $row['content'] . $row['content_perevod'])
    );

    $content = preg_replace('/googletag\.cmd\.push\(.{0,400}\}\);/', '', $content);

    if (($_REQUEST['ingame'] ?? '') !== 'yes' && !isAndroidApp()) {
        $title = "Игра Эрудит.CLUB :: Словарь | " . $_REQUEST['word'];
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
            ? ('<link rel="canonical" href="https://эрудит.club/dict/' . urlencode($_REQUEST['word']) . '" />')
            : '';
        $articleModel = ArticleModel::new(['title' => $title, 'desc' => $description, 'text' => $content]);
        $featuredArticles = ArticleModel::find()
            ->order('rand()')
            ->limit(3)
            ->all();

        $content = new BlogContent();
        $content->model = $articleModel;
        $content->featuredArticles = $featuredArticles;

        echo BlogController::renderStatic('Blog', $content);

        exit;
    } elseif (($_REQUEST['ingame'] ?? '') === 'yes') {
        echo json_encode(['result' => $content]);
    } else {
        echo $content;
    }

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
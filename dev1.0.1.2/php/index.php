<?php
//ini_set("display_errors", 1); error_reporting(E_ALL);
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
    'merge_the_ids' => 'mergeTheIds',
];

include_once 'autoload.php';
include_once 'cors.php';

if (function_exists(SCRIPTS[$scriptName])) {
    $func = SCRIPTS[$scriptName];
    $func();
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

function mergeTheIds()
{
    if (isset($_POST['oldKey']) && $_POST['oldKey'] != '' && isset($_POST['commonID']) && $_POST['commonID'] != '') {
        print (new Erudit\Game())->mergeTheIDs($_POST['oldKey'], $_POST['commonID']);
    } else {
        print  json_encode(['result' => 'Ошибка Объединения аккаунтов']);
    }
}
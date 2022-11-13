<?php
if (isset($_SERVER['HTTP_ORIGIN']) && $_SERVER['HTTP_ORIGIN'] != '') {
    header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
    header('Access-Control-Allow-Credentials: true');
}

if (!isset($_COOKIE['erudit_user_session_ID']))
    exit(0);

include 'EruditGame.php';
if (isset($_POST['avatar']) && $_POST['avatar'] != '' && isset($_POST['commonID']) && $_POST['commonID'] != '') {
    print (new Erudit\Game())->addUserAvatarUrl($_POST['avatar'], $_POST['commonID']);
} else {
    print  json_encode(['result' => 'Ошибка сохранения URL']);
}

exit();
<?php
if (isset($_SERVER['HTTP_ORIGIN']) && $_SERVER['HTTP_ORIGIN'] != '') {
    header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
    header('Access-Control-Allow-Credentials: true');
}

if (!isset($_COOKIE['erudit_user_session_ID']))
    exit(0);

include 'EruditGame.php';
if (isset($_POST['oldKey']) && $_POST['oldKey'] != '' && isset($_POST['commonID']) && $_POST['commonID'] != '') {
    print (new Erudit\Game())->mergeTheIDs($_POST['oldKey'], $_POST['commonID']);
} else {
    print  json_encode(['result' => 'Ошибка Объединения аккаунтов']);
}

exit();
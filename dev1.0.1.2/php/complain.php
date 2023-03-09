<?php
//ini_set("display_errors", 1); error_reporting(E_ALL);
require 'cors.php';

$resp = json_encode(['message' => 'Ошибка отправки сообщения']);


if (isset($_POST['chatTo'])) {
    include_once 'EruditGame.php';

    if (isset($_POST['chatTo'])) {
        $resp = ($obj = new Erudit\Game($server_name))->addComplain($_POST['chatTo']);
    }
}

print $resp;
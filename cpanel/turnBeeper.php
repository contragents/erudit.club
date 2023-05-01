<?php

$table = '';
$beepTime = 5;
include_once __DIR__ . '/../autoload.php';
if (isset($_GET['gameNumber']) && isset($_GET['userInfo'])) {
    if ($game = Cache::get("erudit.game_status_" . $_GET['gameNumber'])) {
        $table .= $game['users'][$game[$_GET['userInfo']]]['status'];
        if ($game['users'][$game[$_GET['userInfo']]]['status'] == 'myTurn') {
            $table .= include('tpl/yourTurn.php');
            if ((date('U') - $game['turnBeginTime']) > ($game['turnTime'] - 30)) {
                $table .= include('tpl/2yourTurn.php');
            }
            $beepTime = 25;
        } elseif ($game['users'][$game[$_GET['userInfo']]]['status'] == 'preMyTurn') {
            $table .= include('tpl/otherTurn.php');
            $beepTime = 5;
        } elseif ($game['users'][$game[$_GET['userInfo']]]['status'] == 'otherTurn') {
            $table .= include('tpl/otherTurn.php');
            $beepTime = 20;
        } else {
            $table .= include('tpl/otherTurn.php');
            $beepTime = 20;
        }
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Контрольная панель Эрудит</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="refresh" content="<?= $beepTime ?>">

</head>

<body>
<a style="margin:0; padding:0;font-size:10px" href='#'>Включить звук</a>
<?= $table ?>
</body>
</html>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Контрольная панель Эрудит</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="refresh" content="20">
    <link rel="stylesheet" type="text/css" href="/vendor/bootstrap_4.5.2/bootstrap.min.css">


    <!-- JS dependencies -->
    <script src="/vendor/jquery_1.12.4/jquery.min.js"></script>
    <!-- Bootstrap 4 dependency -->
    <script src="/vendor/popper_2.4.4/popper.min.js"></script>
    <script src="/vendor/bootstrap_4.5.2/bootstrap.min.js"></script>

    <!-- bootbox code -->
    <script src="/vendor/bootbox_5.4.0/bootbox.min.js"></script>
    <script src="/vendor/bootbox_5.4.0/bootbox.locales.min.js"></script>

</head>

<body>
<?php
include_once __DIR__ . '/../autoload.php';
$lastGame = Cache::get('erudit.num_games');
$table = include('tpl/gamesTableHeader.php');
for ($i = $lastGame; $i > ($lastGame - 200); $i--) {
    if ($game = Cache::get("erudit.game_status_" . $i)) {
        $table .= include('tpl/gamesTableRow.php');
    }
}
$table .= include('tpl/gamesTableFooter.php');
print $table;
?>
</body>
</html>

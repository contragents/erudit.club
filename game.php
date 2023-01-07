<?php
//ini_set("display_errors", 1); error_reporting(E_ALL);

$title = 'Игра №' . $_GET['game_id'];
include ('tpl/main_header.php');
include('yandex1.0.1.1/php/game_page.php');
include ('tpl/main_footer.php');
exit();

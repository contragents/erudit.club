<?php
include_once(__DIR__ . '/autoload_helper.php');

if (!empty($_GET['search'])) {
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: " . Config::$config['domain'] . "/dict/" . $_GET['search']);
    header("Connection: close");
}

if ($_GET['word'] == '') {
    include(__DIR__ . '/dict_2_letters.php');

    exit();
}

include(__DIR__ . '/yandex1.0.1.1/php/word.php');
include(__DIR__ . '/private/MVC/View/Tpl/main_footer.php');

exit();

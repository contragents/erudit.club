<?php

if (!empty($_GET['search'])) {
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: https://xn--d1aiwkc2d.club/dict/".$_GET['search']);
    header("Connection: close");
}

if ($_GET['word'] == '') {
    include('dict_2_letters.php');

    exit();
}

include('yandex1.0.1.1/php/word.php');
include (__DIR__ . '/private/MVC/View/Tpl/main_footer.php');

exit();

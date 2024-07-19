<?php

/**
 * Загружаем классы, относящиеся к версии игры - dev, yandex(основной эрудит), scrabble, private
 */
if (strpos($_SERVER['HTTP_REFERER'] ?? '', 'dev.html')) {
    include_once __DIR__ . '/dev1.0.1.2/php/autoload.php';
} elseif (strpos($_SERVER['HTTP_REFERER'] ?? '', 'private.html')) {
    //ini_set("display_errors", 1);
    //error_reporting(E_ALL);
    include_once __DIR__ . '/dev1.0.1.5/php/autoload.php';
} elseif  (strpos($_SERVER['HTTP_REFERER'] ?? '', 'scrabble.html')) {
    //ini_set("display_errors", 1);
    //error_reporting(E_ALL);
    include_once __DIR__ . '/scrabble1.0.1.6/php/autoload.php';
} else {
    include_once __DIR__ . '/yandex1.0.1.1/php/autoload.php';
}

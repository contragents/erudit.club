<?php

/**
 * Загружаем классы, относящиеся к версии игры - dev, yandex(основной эрудит), scrabble, private
 */

if (strpos($_SERVER['HTTP_REFERER'] ?? '', 'dev.html')) {
    include_once __DIR__ . '/dev1.0.1.2/php/autoload.php';
} elseif (strpos($_SERVER['HTTP_REFERER'] ?? '', 'private.html')) {
    include_once __DIR__ . '/dev1.0.1.5/php/autoload.php';
} elseif  (strpos($_SERVER['HTTP_REFERER'] ?? '', 'scramble.html')) {
    include_once __DIR__ . '/scrabble1.0.1.6/php/autoload.php';
} else {
    include_once __DIR__ . '/yandex1.0.1.1/php/autoload.php';
}


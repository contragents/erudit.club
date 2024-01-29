<?php

if (strstr($_SERVER['HTTP_REFERER'] ?? '','dev.html')) {
    include_once __DIR__.'/../../dev1.0.1.2/php/EruditGame.php';
} elseif (strstr($_SERVER['HTTP_REFERER'] ?? '','private.html')) {
    include_once __DIR__.'/../../dev1.0.1.5/php/EruditGame.php';
} else {
    include_once __DIR__.'/../../yandex1.0.1.1/php/EruditGame.php';
}
<?php

spl_autoload_register(
    function ($class_name) {
        $Exploded_class = explode('\\', $class_name);

        if (strpos($class_name, 'Controller')) {
            $classFileName = __DIR__ . '/private/MVC/Controller/' . $Exploded_class[count(
                    $Exploded_class
                ) - 1] . '.php';
            if (file_exists($classFileName)) {
                include $classFileName;

                return true;
            }
        }

        if (strpos($class_name, 'Model')) {
            $classFileName = __DIR__ . '/private/MVC/Model/' . $Exploded_class[count($Exploded_class) - 1] . '.php';
            if (file_exists($classFileName)) {
                include $classFileName;

                return true;
            }
        }

        if (strpos($class_name, 'View')) {
            $classFileName = __DIR__ . '/private/MVC/View/' . $Exploded_class[count($Exploded_class) - 1] . '.php';
            if (file_exists($classFileName)) {
                include $classFileName;

                return true;
            }
        }

        $classFileName = __DIR__ . '/private/Classes/' . $Exploded_class[count($Exploded_class) - 1] . 'Class' . '.php';
        if (file_exists($classFileName)) {
            include $classFileName;

            return true;
        }

        return false;
    }
);

define('LOG_KEY', 'log_errors'); // ключ логов в keydb
define('LOG_TTL', 15 * 60); // время хранения логов в keydb

Config::$config = include __DIR__ . '/config.php';
// Парсим параметры из .env
Config::parseEnv();

// Создаем окружение (ДЕВ/ПРОД)
Config::makeEnvironment();

// Проверяем режим дебага
Config::checkDebugFlag();

//include __DIR__ . '/vendor/autoload.php';

/**
 * Глобальная функция вывода отладочной инфо, либо логирования (пока в keydb)
 * @param $data
 * @param string $comment
 * @param string $class
 * @param bool $ignoreCli
 */
function mp($data, string $comment = '', string $class = 'NA'): void
{
    Cache::setex(
            implode('_',[LOG_KEY, $class, date('c'), microtime(true)]),
            LOG_TTL,
            ['data' => $data, 'comment' => $comment, 'class' => $class]
        );
}
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

        $classFileName = __DIR__ . '/vendor/Classes/' . $Exploded_class[count($Exploded_class) - 1] . 'Class' . '.php';
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
function mp($data, string $comment = '', string $class = 'NA', $ignoreCli = false): void
{
    /** todo restore on prod when time come
     * if (Config::$config['env'] !== Config::DEV) {
     * return;
     * }
     */

    if (Config::isDebug() && !empty(Config::LOGGING_METHODS[$class])) {
        Cache::hset(
            Config::$config['debug_info']['debug_key'],
            microtime(true),
            ['data' => $data, 'comment' => $comment, 'method' => $class]
        );
    }

    if (!isset($_REQUEST['cli_mode']) || $ignoreCli) {
        Cache::setex(
            Tracker::combineKeys([LOG_KEY, $class, date('c'), microtime(true)]),
            LOG_TTL,
            ['data' => $data, 'comment' => $comment, 'class' => $class]
        );

        if (!$ignoreCli) {
            return;
        }
    }

    $repeatChar = array_rand(['!' => '!', '#' => '#', '+' => '+']);
    print "\n" . $comment . str_repeat($repeatChar, 10) . "\n";

    if (is_array($data)) {
        print_r($data);
    } else {
        print $data;
    }

    print "\n" . str_repeat($repeatChar, 10) . "\n";
}
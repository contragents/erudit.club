<?php


class Config
{
    const DEV = 'DEV';
    const PROD = 'PROD';

    public static array $config = [];
    public static array $envConfig = [];

    // Соответствие $config-параметров и .env-параметров для замены
    const REPLACE_PARAMS = [
        'domain' => ['key' => 'DOMAIN'],
        'env' => ['key' => 'YII_ENV', 'modify' => 'strtoupper'],
        'url' => [
            'RECEIVE_URL' => ['key' => 'TRACKER_RECEIVE_URL'],
            'IMG_URL' => ['key' => 'BASE_IMG_URL']
        ],
        'cache' => [
            'HOST' => ['key' => 'KEYDB_HOST'],
            'PORT' => ['key' => 'KEYDB_PORT']
        ],
        'cache_replica' => [
            'HOST' => ['key' => 'KEYDB_HOST_SLAVE'],
            'PORT' => ['key' => 'KEYDB_PORT_SLAVE']
        ],
        'db' => [
            'SQL_HOST' => ['key' => 'MYSQL_HOST'],//'mariadb',
            'SQL_USER' => ['key' => 'MYSQL_USER'],//'root',
            'SQL_PASSWORD' => ['key' => 'MYSQL_PASSWORD'],//'root',
            'SQL_DB_NAME' => ['key' => 'MYSQL_DATABASE'],//'teaser'
        ],
        'db_psql' => [
            'SQL_HOST' => ['key' => 'PSQL_HOST'],//'postgres',
            'SQL_USER' => ['key' => 'PSQL_USER'],//'root',
            'SQL_PASSWORD' => ['key' => 'PSQL_PASSWORD'],//'root',
            'SQL_DB_NAME' => ['key' => 'PSQL_DATABASE'],//'teaser'
            'SQL_DB_PORT' => ['key' => 'PSQL_PORT'],
        ],
    ];

    public static function parseEnv()
    {
        $envArr = file(__DIR__ . '/../../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($envArr as $string) {
            $string = trim($string);

            if (substr($string, 0, 1) != '#') {
                $paramValue = explode('=', $string);

                if (is_array($paramValue) && count($paramValue) == 2) {
                    self::$envConfig[$paramValue[0]] = $paramValue[1];
                } elseif (is_array($paramValue) && count($paramValue) > 2) {
                    // Найдена строка с несколькими знаками '='
                    $keyValue = $paramValue[0];
                    unset($paramValue[0]);
                    self::$envConfig[$keyValue] = implode('=', $paramValue);
                }
            }
        }
    }

    public static function makeEnvironment()
    {
        foreach (self::REPLACE_PARAMS as $param => $envParamData) {
            if (!empty(self::$envConfig[$envParamData['key'] ?? 'NON EXISTING KEY'] ?? false)) {
                self::$config[$param] = (
                isset($envParamData['modify'])
                    ? $envParamData['modify'](self::$envConfig[$envParamData['key']])
                    : self::$envConfig[$envParamData['key']]
                );
            } elseif (is_array($envParamData)) {
                // Параметры 2го уровня вложенности
                foreach ($envParamData as $subParam => $subEnvParamData) {
                    if (!empty(self::$envConfig[$subEnvParamData['key']])) {
                        self::$config[$param][$subParam] = (
                        isset($subEnvParamData['modify'])
                            ? $subEnvParamData['modify'](self::$envConfig[$subEnvParamData['key']])
                            : self::$envConfig[$subEnvParamData['key']]
                        );
                    }
                }
            }
        }
    }
}
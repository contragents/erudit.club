<?php

/** Класс DB для работы с MariaDB */
class DB
{
    private static bool $is_transaction_started = false;
    private static int $transactionNestLevel = 0;
    private static bool $isLogBegin = false;
    private static int $logCounter = 0;

    private static $_instance = null;

    public static ?mysqli $DBConnect = null;

    private function __construct()
    {
        self::connect();
    }

    public static function isTransactionStarted(): bool
    {
        return self::$is_transaction_started;
    }

    private static function transactionLog(string $method)
    {
        $logKey = 'transaction_log';

        if (!self::$isLogBegin) {
            Cache::del($logKey);
        }

        self::$isLogBegin = true;

        Cache::rpush(
            $logKey,
            [
                'method' => $method,
                'counter' => ++self::$logCounter,
                'transaction_started' => self::$is_transaction_started,
                'nest_level' => self::$transactionNestLevel
            ]
        );
    }

    public static function transactionRollback()
    {
        // self::transactionLog(__METHOD__);

        self::$transactionNestLevel = 0; // Сбросили уровень вложенности

        if (!self::$is_transaction_started) {
            // Попытка вызвать роллбек на неначатой транзакции

            return false;
        }

        self::$is_transaction_started = false;

        if (self::$DBConnect === null) {
            return false;
        } else {
            return mysqli_rollback(self::$DBConnect);
        }
    }

    public static function transactionCommit()
    {
        // self::transactionLog(__METHOD__);

        if (!self::$is_transaction_started) {
            // Попытка вызвать коммит на неначатой транзакции
            self::$transactionNestLevel = 0; // Сбросили уровень вложенности

            return false;
        }

        if (--self::$transactionNestLevel > 0) {
            return true; // Вложенная транзакция - не коммитим
        }

        self::$is_transaction_started = false;

        if (self::$DBConnect === null) {
            self::$transactionNestLevel = 0; // Соединение разорвалось - сбрасываем уровень вложенности транзакций

            return false;
        } else {
            return mysqli_commit(self::$DBConnect);
        }
    }

    public static function transactionStart(): bool
    {
        // self::transactionLog(__METHOD__);

        self::$transactionNestLevel++;

        if (self::$is_transaction_started) { // внешняя транзакция уже начата
            return true;
        }

        if (self::$DBConnect === null) {
            self::connect();
        }

        self::$is_transaction_started = mysqli_begin_transaction(self::$DBConnect);

        if (!self::$is_transaction_started) {
            self::$transactionNestLevel = 0; // Сбрасываем уровень вложенности, если транзакция не стартанула

            return false;
        }

        return true;
    }

    private static function connect()
    {
        $connection = mysqli_init();
        $connection->options(MYSQLI_OPT_CONNECT_TIMEOUT, 100);
        $connection->options(MYSQLI_OPT_READ_TIMEOUT, 100);
        //$connection->options(MYSQLI_SET_CHARSET_NAME, 'utf8mb4');

        $connection->real_connect(
            Config::$config['db']['SQL_HOST'],
            Config::$config['db']['SQL_USER'],
            Config::$config['db']['SQL_PASSWORD'],
            Config::$config['db']['SQL_DB_NAME']
        );
        mysqli_set_charset($connection, "utf8mb4");
        self::$DBConnect = $connection;
    }

    /**
     * Каждая строка результата обернута в кавычки
     * @param array $strArr
     * @return array
     */
    public static function escapeStringArr(array $strArr): array
    {
        if (self::$DBConnect === null) {
            self::connect();
        }

        $res = [];
        foreach ($strArr as $str) {
            $res[] = "'" . mysqli_real_escape_string(self::$DBConnect, $str) . "'";
        }

        return $res;
    }

    public static function escapeString($str)
    {
        if (self::$DBConnect === null) {
            self::connect();
        }

        return mysqli_real_escape_string(self::$DBConnect, $str);
    }

    /**
     * @param $mysqlQuery string raw query
     * @return false|int records matched|affected or false
     */
    public static function queryInsert($mysqlQuery)
    {
        if (self::$DBConnect === null) {
            self::connect();
        }

        $res = mysqli_query(self::$DBConnect, $mysqlQuery);
        $affectedRows = mysqli_affected_rows(self::$DBConnect);

        preg_match_all('/(\S[^:]+): (\d+)/', mysqli_info(self::$DBConnect), $matches);
        $info = array_combine($matches[1], $matches[2]);

        return $affectedRows > 0
            ? $affectedRows
            : (($info['Rows matched'] ?? false) ?: false);
    }

    public static function insertID()
    {
        return (int)mysqli_insert_id(self::$DBConnect);
    }

    public static function queryArray($mysqlQuery)
    {
        if (self::$DBConnect === null) {
            self::connect();
        }

        if ($res = mysqli_query(self::$DBConnect, $mysqlQuery)) {
            $rows = [];
            while ($row = mysqli_fetch_assoc($res)) {
                $rows[] = $row;
            }

            return $rows;
        } else {
            return false;
        }
    }

    /**
     * @param $mysqlQuery string
     * @return false|string result
     */
    public static function queryValue($mysqlQuery)
    {
        if (self::$DBConnect === null) {
            self::connect();
        }

        if ($res = mysqli_query(self::$DBConnect, $mysqlQuery)) {
            $row = mysqli_fetch_assoc($res);
            if ($row) {
                foreach ($row as $key => $value) {
                    return $value;
                }
            }
        }

        return false;
    }

    public static function status()
    {
        if (self::$DBConnect === null) {
            return 'Not connected';
        } else {
            return mysqli_stat(self::$DBConnect);
        }
    }

    public static function getInstance()
    {
        if (self::$_instance != null) {
            return self::$_instance;
        }

        self::$_instance = new self;
        return self::$_instance;
    }
}
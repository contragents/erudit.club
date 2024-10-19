<?php
    /** Класс DB для работы с MariaDB */
class DB
{
    private static bool $is_transaction_started =  false;

    private static $_instance = null;

    public static ?mysqli $DBConnect = null;

    private function __construct()
    {
        self::connect();
    }

    public static function transactionRollback() {
        self::$is_transaction_started = false;

        if (self::$DBConnect === null) {
            return false;
        } else {
            return mysqli_rollback(self::$DBConnect);
        }
    }

    public static function transactionCommit() {
        self::$is_transaction_started = false;

        if (self::$DBConnect === null) {
            return false;
        } else {
            return mysqli_commit(self::$DBConnect);
        }
    }

    public static function transactionStart(): bool {
        if (self::$is_transaction_started) {
            return true;
        }

        if (self::$DBConnect === null) {
            self::connect();
        }

        if (!mysqli_begin_transaction(self::$DBConnect)) {
            self::connect();

            return self::$is_transaction_started = mysqli_begin_transaction(self::$DBConnect);
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

    public static function escapeString($str)
    {
        if (self::$DBConnect === null)
            self::connect();

        return mysqli_real_escape_string(self::$DBConnect, $str);
    }

    /**
     * @param $mysqlQuery string raw query
     * @return false|int records affected or false
     */
    public static function queryInsert($mysqlQuery)
    {
        if (self::$DBConnect === null)
            self::connect();

        $res = mysqli_query(self::$DBConnect, $mysqlQuery);
        $affectedRows = mysqli_affected_rows(self::$DBConnect);

        preg_match_all ('/(\S[^:]+): (\d+)/', mysqli_info(self::$DBConnect), $matches);
        $info = array_combine ($matches[1], $matches[2]);

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
        if (self::$DBConnect === null)
            self::connect();

        if ($res = mysqli_query(self::$DBConnect, $mysqlQuery)) {
            $rows = [];
            while ($row = mysqli_fetch_assoc($res))
                $rows[] = $row;

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
        if (self::$DBConnect === null)
            self::connect();

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
        if (self::$DBConnect === null)
            return 'Not connected';
        else
            return mysqli_stat(self::$DBConnect);
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
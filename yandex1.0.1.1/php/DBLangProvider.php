<?php

namespace Dadata;

class DB
{
    private static $_instance = null;

    public $redis;
    public static $DBConnect = false;
    const SQL_HOST = 'localhost';
    const SQL_USER = 'ili';
    const SQL_PASSWORD = 'sveta2883';
    const ORACLE_SQL_PASSWORD = 'Aw!gP!mx_Jh6M.V';
    const SQL_DB = 'erudit';

    private function __construct()
    {
        self::connect();
    }

    private static function connect()
    {
        $connection = mysqli_init();
        $connection->options(MYSQLI_OPT_CONNECT_TIMEOUT, 100);
        $connection->options(MYSQLI_OPT_READ_TIMEOUT, 100);
        $connection->real_connect(self::SQL_HOST,
            self::SQL_USER, self::ORACLE_SQL_PASSWORD,
            /**todo убрать isset($_SERVER['SERVER_ADDR'])
                        ? ($_SERVER['SERVER_ADDR'] == '167.71.60.229'
                            ? self::SQL_PASSWORD
                            : self::ORACLE_SQL_PASSWORD)
                        : (isset($_SERVER['argv'][1]) && $_SERVER['argv'][1] == 'oracle'
                            ? self::ORACLE_SQL_PASSWORD
                            : self::SQL_PASSWORD),*/
            self::SQL_DB);
        self::$DBConnect = $connection;
        //print "!!!!!!!!!!!!connected!!!!";
    }

    public static function escapeString($str)
    {
        if (self::$DBConnect === false)
            self::connect();

        return mysqli_real_escape_string(self::$DBConnect, $str);
    }

    public static function queryInsert($mysqlQuery)
    {
        if (self::$DBConnect === false)
            self::connect();
        $res = mysqli_query(self::$DBConnect, $mysqlQuery);
        $affectedRows = mysqli_affected_rows(self::$DBConnect);

        return $affectedRows > 0 ? $affectedRows : false;
    }

    public static function insertID()
    {
        return mysqli_insert_id(self::$DBConnect);
    }

    public static function queryArray($mysqlQuery)
    {
        if (self::$DBConnect === false)
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

    public static function queryValue($mysqlQuery)
    {
        if (self::$DBConnect === false)
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
        if (self::$DBConnect === false)
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
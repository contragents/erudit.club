<?php


    class DB
    {
        public static $DBConnect = false;

        public function __construct()
        {
            self::connect();
        }

        public static function transactionRollback()
        {
            if (self::$DBConnect === false) {
                return false;
            } else {
                return self::$DBConnect->rollBack();
            }
        }

        public static function transactionCommit()
        {
            if (self::$DBConnect === false) {
                return false;
            } else {
                return self::$DBConnect->commit();
            }
        }

        public static function transactionStart(): bool
        {
            if (self::$DBConnect === false) {
                self::connect();
            }

            if (!self::$DBConnect->beginTransaction()) {
                self::connect();
                return self::$DBConnect->beginTransaction();
            }

            return true;
        }

        private static function connect()
        {
            $dsn = 'pgsql:host=' . Config::$config['db_psql']['SQL_HOST'] . ';port='. Config::$config['db_psql']['SQL_DB_PORT'] . ';dbname=' . Config::$config['db_psql']['SQL_DB_NAME'];
            self::$DBConnect = new PDO($dsn, Config::$config['db_psql']['SQL_USER'], Config::$config['db_psql']['SQL_PASSWORD'], array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
        }

        public static function escapeString($str)
        {
            if (self::$DBConnect === false)
                self::connect();

            return $str;
        }

        /**
         * @param $mysqlQuery string raw query
         * @return false|int records affected or false
         */
        public static function queryInsert($mysqlQuery)
        {
            if (self::$DBConnect === false)
                self::connect();

            $affectedRows = self::$DBConnect->exec($mysqlQuery);

            return $affectedRows > 0 ? $affectedRows : false;
        }

        public static function insertID($sequence = false)
        {
            if (!$sequence) {
                return self::$DBConnect->lastInsertId();
            } else {
                return self::$DBConnect->lastInsertId($sequence);
            }
        }

        public static function queryArray($mysqlQuery)
        {
            if (self::$DBConnect === false)
                self::connect();

            $res = self::$DBConnect->prepare($mysqlQuery);
            if ($res->execute()) {
                $rows = [];
                while ($row = $res->fetch(PDO::FETCH_ASSOC))
                    $rows[] = $row;

                return $rows;
            } else {
                return false;
            }
        }

        public static function queryNum($mysqlQuery)
        {
            if (self::$DBConnect === false)
                self::connect();

            $res = self::$DBConnect->prepare($mysqlQuery);
            if ($res->execute()) {
                $rows = [];
                foreach ($res->fetchAll(PDO::FETCH_NUM) as $row)
                    $rows[] = $row[0];
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
            if (self::$DBConnect === false)
                self::connect();

            $res = self::$DBConnect->prepare($mysqlQuery);
            if ($res->execute()) {

                $row = $res->fetch(PDO::FETCH_ASSOC);
                if ($row) {
                    foreach ($row as $key => $value) {
                        return $value;
                    }
                }
            }

            return false;
        }
    }
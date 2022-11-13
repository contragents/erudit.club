<?php
namespace DB;
class DB
{
	private static $_instance = null;
	
	public $redis;
    public static $DBConnect = false;
    const SQL_HOST      = 'localhost';
    const SQL_USER      = 'ili';
    const SQL_PASSWORD  = 'sveta2883';
    const SQL_DB        = 'erudit';
    
	private function __construct () {
		self::connect();
	}
    
    private static function connect()
    {
        $connection = mysqli_init();
        $connection->options(MYSQLI_OPT_CONNECT_TIMEOUT, 100);
        $connection->options(MYSQLI_OPT_READ_TIMEOUT, 100);
        $connection->real_connect(self::SQL_HOST, self::SQL_USER, self::SQL_PASSWORD, self::SQL_DB);
        self::$DBConnect = $connection;
        //print "!!!!!!!!!!!!connected!!!!";
    }
    
    public static function escapeString($string) {
        if (self::$DBConnect === false)
            self::connect();
        return mysqli_real_escape_string(self::$DBConnect,$string);
    }
    
    public static function queryArray($mysqlQuery)
    {
        //print $mysqlQuery;
        if (self::$DBConnect === false)
            self::connect();
        if( $res = mysqli_query(self::$DBConnect,$mysqlQuery) )
        {
            $rows = [];
            while ( $row = mysqli_fetch_assoc($res) )
                $rows[] = $row;
            
            if (count($rows) == 0)
                return mysqli_affected_rows(self::$DBConnect);
            
            return $rows;
        }
        else 
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
/*
print DB::status();
//$db = DB::getInstance();

print_r(DB::queryArray("SELECT * from players where cookie like 'bot#%' order by games_played DESC limit 30"));


print_r(DB::queryArray("SELECT rating, games_played from players where not (cookie like 'bot#%') order by games_played DESC limit 30"));

print DB::Status();
*/
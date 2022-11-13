<?php

namespace InfoProvider;
ini_set("display_errors", 1); error_reporting(E_ALL);


class Player {
    
    public static function getPlayerInfo($cookie) {
        return self::getPlayerInfoHelper($cookie);
    }
    
    private static function getPlayerInfoHelper($cookie, $last = false) {
        if (mysql_num_rows($res=mysql_query("SELECT * FROM dict.players WHERE cookie='$cookie' LIMIT 1;")))
            return mysql_fetch_assoc($res);
        elseif (!$last)
            return self::createPlayer($cookie);
        else return false;
    }
    
    private static function createPlayer($cookie) {
        mysql_query($INSERT = "INSERT INTO erudit.players SET
                        cookie='$cookie',
                        first_played=UNIX_TIMESTAMP(),
                        rating=1700,
                        rating_changed_date=UNIX_TIMESTAMP()
                        ");
        if (mysql_affected_rows())
            return self::getPlayerInfoHelper($cookie, true);
    }
    
    
}



<?php
namespace Dadata;
//ini_set("display_errors", 1); error_reporting(E_ALL);

class Player
{
    public static function getPlayerID($cookie, $createIfNotExist = false)
    {
        $findIDQuery = "SELECT 
            p1.common_id AS cid1, 
            p2.common_id AS cid2 
            FROM players p1
            LEFT JOIN players p2
            ON p1.user_id = p2.user_id
            AND
            p2.common_id IS NOT NULL
            WHERE 
            p1.cookie='$cookie'
            LIMIT 1";

        $userIDArr = DB::queryArray($findIDQuery);
        if ($userIDArr) {

            if ($userIDArr[0]['cid2']) {
                return $userIDArr[0]['cid2'];
            }

            if ($createIfNotExist) {
                $cookieUpdateQuery = "UPDATE players
                SET 
                    common_id = id
                WHERE 
                    cookie = '$cookie'";

                if (DB::queryInsert($cookieUpdateQuery)) {
                    $userCreateQuery = "INSERT
                    INTO 
                        users 
                    SET 
                        id = (SELECT common_id FROM players WHERE cookie = '$cookie' LIMIT 1)";

                    if (DB::queryInsert($userCreateQuery)) {
                        return self::getPlayerID($cookie);
                    }
                }
            }
        } elseif ($createIfNotExist) {

            $cookieInsertQuery = "INSERT 
                INTO 
                    players
                SET 
                    cookie = '$cookie',
                    user_id = conv(substring(md5('$cookie'),1,16),16,10)";

            if (DB::queryInsert($cookieInsertQuery)) {

                return self::getPlayerID($cookie, 'createCommonID');
            }

        }

        return false;
    }

}
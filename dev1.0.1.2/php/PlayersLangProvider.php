<?php

namespace Dadata;

use Erudit\Game;
use Lang\Ru;

class Players
{
    public static function getCommonIDByCookie($cookie)
    {
        $commonIDQuery = "SELECT CASE WHEN common_id IS NULL THEN id ELSE common_id END FROM players WHERE cookie = '$cookie' LIMIT 1";
        if ($res = DB::queryValue($commonIDQuery)) {
            return $res;
        } else {
            return false;
        }
    }

    public static function getAvatarUrl(int $commonID)
    {
        $avatarUrl = DB::queryValue("SELECT avatar_url FROM users WHERE id = $commonID");

        if ($avatarUrl) {
            return $avatarUrl;
        } else {
            return self::getDefaultAvatar($commonID);
        }
    }

    private static function getDefaultAvatar($commonID)
    {
        $maxImgId = 34768;
        $imgId = $commonID % $maxImgId;
        return DB::queryValue("SELECT concat(site,mini_url) FROM avatar_urls WHERE site_img_id >= $imgId LIMIT 1");
    }

    public static function getPlayerName(array $user = ['ID' => 'cookie', 'common_id' => 15, 'userID' => 'user_ID'])
    {
        if (strpos($user['ID'], 'bot') !== false) {
            return Game::$configStatic['botNames'][substr($user['ID'], (strlen($user['ID']) == 7 ? -1 : -2))];
        }

        $commonId = $user['common_id'];
        if (
        $commonIDName = DB::queryValue(
            "SELECT name 
                    FROM users 
                    WHERE id=$commonId 
                    LIMIT 1"
        )) {
            return $commonIDName;
        }

        if (isset($user['userID'])) {
            $idSource = $user['userID'];
        } else {
            $idSource = $user['ID'];
        }

        if (
        $res = DB::queryValue(
            "SELECT name FROM player_names 
            WHERE
            some_id=" . Game::hash_str_2_int($idSource)
            . " LIMIT 1"
        )
        ) {
            return $res;
        } else {
            $sintName = isset($user['userID'])
                ? md5($user['userID'])
                : $user['ID'];
            $letterName = '';

            foreach (str_split($sintName) as $index => $lowByte) {
                $letterNumber = base_convert("0x" . $lowByte, 16, 10)
                    + base_convert("0x" . substr($sintName, $index < 5 ? $index : 0, 1), 16, 10);

                if (!isset(Ru::$bukvy[$letterNumber])) {
                    //Английская версия
                    $letterNumber = number_format(round(34 + $letterNumber * (59 - 34 + 1) / 30, 0), 0);
                }

                if (Ru::$bukvy[$letterNumber][3] == false) { // нет ошибки - класс неизвестен
                    $letterNumber = 31;//меняем плохую букву на букву Я
                }

                if ($letterName == '') {
                    if ($letterNumber == 28) {
                        continue;//Не ставим Ь в начало ника
                    }
                    $letterName = Ru::$bukvy[$letterNumber][0];
                    $soglas = Ru::$bukvy[$letterNumber][3];
                    continue;
                }

                if (mb_strlen($letterName) >= 6) {
                    break;
                }

                if (Ru::$bukvy[$letterNumber][3] <> $soglas) {
                    $letterName .= Ru::$bukvy[$letterNumber][0];
                    $soglas = Ru::$bukvy[$letterNumber][3];
                    continue;
                }
            }

            return mb_strtoupper(mb_substr($letterName, 0, 1)) . mb_substr($letterName, 1);
        }
    }

}
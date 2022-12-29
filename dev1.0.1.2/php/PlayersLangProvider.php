<?php

namespace Dadata;

//ini_set("display_errors", 1); error_reporting(E_ALL);

use Erudit\Game;
use Lang\Ru;

class Players
{
    const UPLOAD_DIR = '/img/upload/';
    const BASE_UPLOAD_FILE_URL = 'https://xn--d1aiwkc2d.club/img/upload/';
    const ENABLE_UPLOAD_EXT = [
        'jpg' => 'jpg',
        'jpeg' => 'jpeg',
        'png' => 'png',
        'gif' => 'gif',
        'svg' => 'svg',
        'ico' => 'ico',
        'bmp' => 'bmp'
    ];

    const MAX_UPLOAD_SIZE = 2 * 1024 * 1024;

    public static function getTopPlayer($numTop = 1): array
    {
        $topQuery = "SELECT
	max( rating ) AS rating,
	max( rating_changed_date ) AS updated_at,
	common_id,
	max( user_id ) AS user_id,
    max( users.`name` ) AS name,
	max( users.avatar_url ) AS avatar_url 
FROM
	( SELECT * FROM players WHERE common_id > 0 ORDER BY rating DESC LIMIT " . ($numTop * 10) . " ) AS p1
	LEFT JOIN player_names ON some_id = user_id
	LEFT JOIN users ON users.id = common_id 
GROUP BY
	common_id 
ORDER BY
	max( rating ) DESC 
	LIMIT $numTop";

        $res = DB::queryArray($topQuery);

        return array_map(
            function (array $playerInfo) {
                return [
                    'rating' => $playerInfo['rating'],
                    'updated_at' => $playerInfo['updated_at'],
                    'common_id' => $playerInfo['common_id'],
                    'user_id' => $playerInfo['user_id'],
                    'name' => $playerInfo['name'] ?: self::getPlayerName(
                        ['ID' => 'someID', 'common_id' => $playerInfo['common_id'], 'userID' => $playerInfo['user_id']]
                    ),
                    'avatar_url' => $playerInfo['avatar_url'] ?: self::getAvatarUrl($playerInfo['common_id'])
                ];
            },
            $res
        );
    }

    public static function avatarUpload($files, $cookie): string
    {
        $status = 'error';

        $parts = explode(".", $files['url']['name']);
        $extension = strtolower(end($parts));
        $filename = $cookie . '.' . $extension;
        if (isset(self::ENABLE_UPLOAD_EXT[$extension]) && $files['url']['size'] < self::MAX_UPLOAD_SIZE) {
            if (move_uploaded_file(
                $files['url']['tmp_name'],
                $_SERVER['DOCUMENT_ROOT'] . self::UPLOAD_DIR . $filename
            )) {
                $avatarAddRes = self::addUserAvatarUrl(
                    self::BASE_UPLOAD_FILE_URL . $filename,
                    self::getCommonIDByCookie($cookie)
                );

                return $avatarAddRes;
            }
        }

        return json_encode(
            [
                'result' => $status,
                'message' => '<strong>Ошибка загрузки файла!</strong><br /> Проверьте:<br /> <ul><li>размер файла (не более <strong>'
                    . round(
                        self::MAX_UPLOAD_SIZE / 1024 / 1024,
                        1
                    )
                    . 'MB</strong>)</li><li>разрешение - <strong>'
                    . implode('</strong> или <strong>', self::ENABLE_UPLOAD_EXT)
                    . '</strong></li></ul>'
            ]
        );
    }

    public static function addUserAvatarUrl($url, $commonID): string
    {
        if (!preg_match('/^https?:\/\//', $url)) {
            return json_encode(
                [
                    'result' => 'error',
                    'message' => 'Неверный формат URL! <br />Должно начинаться с <strong>http(s)://</strong>'
                ]
            );
        }

        $avatarUpdateQuery = "UPDATE users
                SET 
                    avatar_url = '" . DB::escapeString($url) . "'
                WHERE 
                    id = $commonID";

        if (DB::queryInsert($avatarUpdateQuery)) {
            return json_encode(['result' => 'saved', 'url' => $url]);
        } else {
            return json_encode(['result' => 'saved', 'message' => 'Файл перезаписан', 'url' => $url]);
        }
    }

    public
    static function getCommonIDByCookie(
        $cookie
    ) {
        $commonIDQuery = "SELECT CASE WHEN common_id IS NULL THEN id ELSE common_id END FROM players WHERE cookie = '$cookie' LIMIT 1";
        if ($res = DB::queryValue($commonIDQuery)) {
            return $res;
        } else {
            return false;
        }
    }

    public
    static function getUserIDByCookie(
        $cookie
    ) {
        $userIDQuery = "SELECT user_id FROM players WHERE cookie = '$cookie' LIMIT 1";
        if ($res = DB::queryValue($userIDQuery)) {
            return $res;
        } else {
            return false;
        }
    }

    public
    static function getAvatarUrl(
        int $commonID
    ) {
        $avatarUrl = DB::queryValue("SELECT avatar_url FROM users WHERE id = $commonID");

        if ($avatarUrl) {
            return $avatarUrl;
        } else {
            return self::getDefaultAvatar($commonID);
        }
    }

    private
    static function getDefaultAvatar(
        $commonID
    ) {
        $maxImgId = 34768;
        $imgId = $commonID % $maxImgId;
        return DB::queryValue("SELECT concat(site,mini_url) FROM avatar_urls WHERE site_img_id >= $imgId LIMIT 1");
    }

    public
    static function getPlayerName(
        array $user = ['ID' => 'cookie', 'common_id' => 15, 'userID' => 'user_ID']
    ) {
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

    public
    static function getPlayerID(
        $cookie,
        $createIfNotExist = false
    ) {
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
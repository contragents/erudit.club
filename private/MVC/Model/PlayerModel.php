<?php

use Erudit\Game;

class PlayerModel extends BaseModel
{
    const TABLE_NAME = 'players';
    const PLAYER_NAMES_TABLE_NAME = 'player_names';

    const RATING_CACHE_PREFIX = 'erudit.rating_cache_';

    const COOKIE_NOT_LINKED_STATUS = 'not_linked';

    public static function getPlayerID(string $cookie, bool $createIfNotExist = false)
    {
        if ($commonID = self::getCommonID($cookie)) {
            return $commonID;
        }

        // Пробуем найти связанный common_id у другого плеера по user_id
        $userIDArr = self::getCrossingCommonIdByCookie($cookie);
        // Если связь есть
        if ($userIDArr !== self::COOKIE_NOT_LINKED_STATUS) {
            // .. и если common_id установлен - возвращаем
            if ($userIDArr) {
                return $userIDArr;
            }

            // ..а если common_id не установлен - создаем
            if ($createIfNotExist) {
                if (self::setParamMass(
                    'common_id',
                    new ORM('id'),
                    [
                        'field_name' => 'cookie',
                        'condition' => '=',
                        'value' => $cookie,
                        'raw' => false
                    ]
                )) {
                    if (UserModel::add(
                        [
                            'id' => new ORM(
                                '('
                                . ORM::select(['common_id'], self::TABLE_NAME)
                                . ORM::where('cookie', '=', $cookie)
                                . ORM::limit(1)
                                . ')'
                            )
                        ]
                    )) {
                        return self::getPlayerID($cookie);
                    }
                }
            }
        } elseif ($createIfNotExist) {
            if (self::add(
                ['cookie' => $cookie, 'user_id' => new ORM("conv(substring(md5('$cookie'),1,16),16,10)")]
            )) {
                return self::getPlayerID($cookie, true);
            }
        }

        return false;
    }

    public static function getNameBySomeId(string $someId)
    {
        return DB::queryValue(
            ORM::select(['name'], self::PLAYER_NAMES_TABLE_NAME)
            . ORM::where('some_id', '=', Game::hash_str_2_int($someId), true)
            . ORM::limit(1)
        );
    }

    /**
     * Finds common_id by comparing user_id and cookies between different players
     * @param $cookie
     * @return array|false
     */
    public static function getCrossingCommonIdByCookie($cookie)
    {
        $findIDQuery = ORM::select(['p1.common_id AS cid1', 'p2.common_id AS cid2'], PlayerModel::TABLE_NAME . ' p1')
            . ORM::leftJoin(PlayerModel::TABLE_NAME . ' p1')
            . ORM::on('p1.user_id', '=', 'p2.user_id', true)
            . ORM::andWhere('p2.common_id', 'IS', 'NOT NULL', true)
            . ORM::where('p1.cookie', '=', $cookie)
            . ORM::limit(1);

        return (DB::queryArray($findIDQuery)[0]['cid2'] ?? self::COOKIE_NOT_LINKED_STATUS) ?: false;
    }

    public static function getCommonID($cookie = false, $userID = false)
    {
        if ($cookie) {
            $res = self::getCommonIdFromCookie($cookie);
            if ($res) {
                return $res;
            }
        }

        if ($userID) {
            $res = self::getCommonIdFromUserId($userID);
            if ($res) {
                return $res;
            }
        }

        return false;
    }

    public static function getCommonIdFromCookie(string $cookie)
    {
        $commonIDQuery = ORM::select(['common_id'], self::TABLE_NAME)
            . ORM::where('cookie', '=', $cookie)
            . ORM::limit(1);

        return DB::queryValue($commonIDQuery);
    }

    public static function getCommonIdFromUserId($userId)
    {
        // todo remove after model
        /*$commonIDQuery = "SELECT common_id FROM players
                                WHERE user_id = $userId
                                AND common_id IS NOT NULL 
                                LIMIT 1";*/

        $commonIDQuery = ORM::select(['common_id'], self::TABLE_NAME)
            . ORM::where('user_id', '=', $userId, true)
            . ORM::andWhere('common_id', 'IS', 'NOT NULL', true)
            . ORM::limit(1);

        return DB::queryValue($commonIDQuery);
    }

    public static function getRating($commonID = false, $cookie = false, $userID = false)
    {
        $ratingQuery = self::getRatingBaseQuery()
            . ($commonID
                ? " OR user_id in (
                SELECT user_id FROM players WHERE common_id = $commonID
                AND user_id != 15284527576400310462
            ) "
                : '')
            . ($cookie
                ? " OR cookie = '$cookie' "
                : '')
            . ($userID
                ? " OR user_id = $userID "
                : '')
            . '  GROUP BY gruping LIMIT 1';

        return DB::queryArray($ratingQuery);
    }

    private static function getRatingBaseQuery(): string
    {
        return 'SELECT 
        max(cookie) as cookie, 
        max(rating) as rating, 
        max(games_played) as games_played, 
        case when max(win_percent) is null then 0 else max(win_percent) END as win_percent,
        avg(inactive_percent) as inactive_percent,
        case 
        when max(rating)>=1700 
        then (
        select 
        case when sum(num) IS NULL THEN 1 ELSE sum(num)+1 END
        from 
        (select 1 as num from players where rating>ps.rating group by user_id, rating) dd
        ) 
        else \'Не в ТОПе\' 
        end as top
        FROM players ps
        WHERE FALSE ';
    }

    public static function getTop($rating)
    {
        $topQuery = ORM::select(
            ['case when sum(num) IS NULL THEN 1 ELSE sum(num) + 1 END as top']
            ,
            "(select 1 as num from players where rating > $rating group by user_id, rating) dd"
        );

        return DB::queryValue($topQuery);
    }

    public static function getRatingFromCache($someId)
    {
        return Cache::get(self::RATING_CACHE_PREFIX . $someId);
    }

    public static function saveRatingToCache(array $idArray, $ratingInfo): void
    {
        foreach ($idArray as $value) {
            Cache::setex(
                'erudit.rating_cache_' . $value,
                round(Game::$configStatic['cacheTimeout'] / 15),
                $ratingInfo
            );
        }
    }
}
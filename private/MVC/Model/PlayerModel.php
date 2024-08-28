<?php

use Dadata\Players;
use Erudit\Game;

class PlayerModel extends BaseModel
{
    const TABLE_NAME = 'players';
    const PLAYER_NAMES_TABLE_NAME = 'player_names';

    const RATING_CACHE_PREFIX = 'erudit.rating_cache_';

    const COOKIE_NOT_LINKED_STATUS = 'not_linked';
    const DELTA_RATING_KEY_PREFIX = 'erudit.delta_rating_';
    const RATING_CACHE_TTL = 7 * 24 * 60 * 60;

    const RATING_FIELD = 'rating';
    const COMMON_ID_FIELD = 'common_id';
    const COOKIE_FIELD = 'cookie';

    const TOP_10 = 10;
    const MIN_TOP_RATING = 2100; // Рейтинг, ниже которого ТОП не рассчитывается в некоторых запросах
    const TOP_PLAYERS_CACHE_TTL = 3600;


    /**
     * Определяет common_id по сложной схеме через связанные куки и ID от яндекса
     * @param string $cookie
     * @param bool $createIfNotExist
     * @return array|false|string
     */
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
                $id = self::getIdByCookie($cookie);
                if ($id) {
                    self::update($id, ['field' => self::COMMON_ID_FIELD, 'value' => $id, 'raw' => true]);
                }

                return $id;
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
     * @return int|false|string
     */
    public static function getCrossingCommonIdByCookie($cookie)
    {
        $findIDQuery = ORM::select(['p1.common_id AS cid1', 'p2.common_id AS cid2'], PlayerModel::TABLE_NAME . ' p1')
            . ORM::leftJoin(PlayerModel::TABLE_NAME . ' p2')
            . ORM::on('p1.user_id', '=', 'p2.user_id', true)
            . ORM::andWhere('p2.common_id', 'IS', 'NOT NULL', true)
            . ORM::where('p1.cookie', '=', $cookie)
            . ORM::limit(1);

        $result = DB::queryArray($findIDQuery);
        if (isset($result[0])) {
            return $result[0]['cid2'] ?: false;
        } else {
            return self::COOKIE_NOT_LINKED_STATUS;
        }
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
        $commonIDQuery = ORM::select(['common_id'], self::TABLE_NAME)
            . ORM::where('user_id', '=', $userId, true)
            . ORM::andWhere('common_id', 'IS', 'NOT NULL', true)
            . ORM::limit(1);

        return DB::queryValue($commonIDQuery);
    }

    public static function getRatingByCookie(string $cookie): int
    {
        return self::getOneCustom(self::COOKIE_FIELD, $cookie)[self::RATING_FIELD] ?? 0;
    }

    public static function getRating($commonID = false, $cookie = false, $userID = false)
    {
        // todo переделать на ОРМ
        $ratingQuery = self::getRatingBaseQuery()
            . ($commonID
                ? (' OR ( true'
                    . ORM::andWhere(
                        'user_id',
                        'in',
                        '('
                        . ORM::select(['user_id'], self::TABLE_NAME)
                        . ORM::where('common_id', '=', $commonID, true)
                        . ORM::andWhere('user_id', '!=', new ORM('15284527576400310462'), true)
                        . ')',
                        true
                    )
                    . ')')
                : '')
            . ($cookie
                ? " OR cookie = '$cookie' "
                : '')
            . ($userID
                ? " OR user_id = $userID "
                : '')
            . ORM::groupBy(['gruping'])
            . ORM::limit(1);

        return DB::queryArray($ratingQuery);
    }

    private static function getRatingBaseQuery(): string
    {
        return
            ORM::select(
                [
                    'max(cookie) as cookie',
                    'max(rating) as rating',
                    'max(games_played) as games_played',
                    'case when max(win_percent) is null then 0 else max(win_percent) END as win_percent',
                    'avg(inactive_percent) as inactive_percent',
                    'case when max(rating) >= 1700 then('
                    . ORM::select(
                        ['case when sum(num) IS null THEN 1 else sum(num) + 1 END'],
                        '(select 1 as num from players where rating > ps . rating group by user_id, rating) dd'
                    )
                    . ') else \'Не в ТОПе\' END as top'
                ],
                self::TABLE_NAME . ' ps'
            )
            . ORM::where('false', '', '', true);
    }

    public static function getTopPlayersCached(int $top, ?int $topMax = null): array
    {
        $cacheKey = self::RATING_CACHE_PREFIX . "_top_{$top}_" . ($topMax ??  self::TOP_10);

        if ($topRatings = Cache::get($cacheKey)) {
            return $topRatings;
        }

        $topRatings = self::getTopPlayers($top, $topMax);
        self::enrichTopRatings($topRatings);

        Cache::setex($cacheKey, self::TOP_PLAYERS_CACHE_TTL, $topRatings);

        return $topRatings;
    }

    public
    static function getAvatarUrl(
        int $commonID
    ): string {
        $avatarUrl = UserModel::getOne($commonID)['avatar_url'] ?? false;

        if (!empty($avatarUrl)) {
            return $avatarUrl;
        } else {
            return AvatarModel::getDefaultAvatar($commonID);
        }
    }

    public
    static function getPlayerName(
        array $user = ['ID' => 'cookie', 'common_id' => 15, 'userID' => 'user_ID']
    ) {
        if (strpos($user['ID'], 'bot') !== false) {
            return 'John Doe';
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
            some_id=" . \Game::hash_str_2_int($idSource)
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

                if (!isset(\Ru::$bukvy[$letterNumber])) {
                    //Английская версия
                    $letterNumber = number_format(round(34 + $letterNumber * (59 - 34 + 1) / 30, 0), 0);
                }

                if (\Ru::$bukvy[$letterNumber][3] == false) { // нет ошибки - класс неизвестен
                    $letterNumber = 31; // меняем плохую букву на букву Я
                }

                if ($letterName == '') {
                    if ($letterNumber == 28) {
                        continue; // Не ставим Ь в начало ника
                    }
                    $letterName = \Ru::$bukvy[$letterNumber][0];
                    $soglas = \Ru::$bukvy[$letterNumber][3];
                    continue;
                }

                if (mb_strlen($letterName) >= 6) {
                    break;
                }

                if (\Ru::$bukvy[$letterNumber][3] <> $soglas) {
                    $letterName .= \Ru::$bukvy[$letterNumber][0];
                    $soglas = \Ru::$bukvy[$letterNumber][3];
                    continue;
                }
            }

            return mb_strtoupper(mb_substr($letterName, 0, 1)) . mb_substr($letterName, 1);
        }
    }

    private static function enrichTopRatings(array $topRatings)
    {
        foreach ($topRatings as $num => &$playerArr) {
            foreach ($playerArr as $numPlayer => &$player) {
                $player['avatar_url'] = self::getAvatarUrl($player[self::COMMON_ID_FIELD]);
                $player['name'] = Players::getPlayerName(['common_id' => $player[self::COMMON_ID_FIELD]]);
            }
        }

        return $topRatings;
    }

    /**
     * @param int $top Номер в рейтинге - 1,2,3 ...
     * @param int $topMax Максимальный номер в рейтинге (для поиска ТОП10 задать 4,10)
     * @return array
     */
    public static function getTopPlayers(int $top, ?int $topMax = null): array
    {
        if ($top >= ($topMax ?? self::TOP_10)) {
            return [];
        }

        $topRatingsQuery = self::select([self::RATING_FIELD])
            . ORM::where(self::RATING_FIELD, '>', self::MIN_TOP_RATING, true)
            . ORM::groupBy([self::RATING_FIELD])
            . ORM::orderBy(self::RATING_FIELD, false)
            . ORM::limit($topMax ? $topMax - $top + 1 : 1, $top - 1);

        $topRatings = DB::queryArray($topRatingsQuery) ?: [];

        $resultRatings = [];

        for ($i = $top; $i <= $top + $topMax ?? 0; $i++) {
            $currentRating = $topRatings[$i-$top][self::RATING_FIELD] ?? false;
            if (!$currentRating) {
                break;
            }

            $resultRatings[$i] = DB::queryArray(
                self::select([self::COMMON_ID_FIELD, self::RATING_FIELD])
                . ORM::where(self::RATING_FIELD, '=', $currentRating, true)
                . ORM::andWhere(self::COMMON_ID_FIELD, 'IS', 'NOT NULL', true)
            ) ?: [];
        }

        return $resultRatings;
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

    private static function cacheDeltaRating(string $cookie = null, $userID, array $deltaArr)
    {
        if ($cookie) {
            Cache::setex(self::DELTA_RATING_KEY_PREFIX . $cookie, self::RATING_CACHE_TTL, $deltaArr);
        }


        if (($userID ?? 0 > 0)) {
            Cache::setex(self::DELTA_RATING_KEY_PREFIX . $userID, self::RATING_CACHE_TTL, $deltaArr);
        }
    }

    public static function getDeltaRating($key1, $key2)
    {
        if ($key1) {
            $key = Game::hash_str_2_int($key1);
            if ($delta = Cache::get(PlayerModel::DELTA_RATING_KEY_PREFIX . $key)) {
                return $delta;
            }
        }

        $key = $key2;

        if ($delta = Cache::get(PlayerModel::DELTA_RATING_KEY_PREFIX . $key)) {
            return $delta;
        } else {
            return false;
        }
    }

    public
    static function decreaseRatings()
    {
        $notPlayedPlayers = self::getCustomComplex(
            ['UNIX_TIMESTAMP() - UNIX_TIMESTAMP(rating_changed_date)', 'rating'],
            ['>', '>'],
            [24 * 60 * 60, 2300],
            true,
            false,
            ['user_id', 'cookie']
        );

        foreach ($notPlayedPlayers as $player) {
            $ratingDecreaseQuery = ORM::update(self::TABLE_NAME)
                . ORM::set(['field' => 'rating', 'value' => 'rating-1', 'raw' => true])
                . ORM::where('cookie', '=', $player['cookie'])
                . (
                    $player['user_id'] ?? 0 > 0
                        ? ORM::orWhere('user_id', '=', $player['user_id'], true)
                        : ''
                );

            if (DB::queryInsert($ratingDecreaseQuery)) {
                self::cacheDeltaRating($player['cookie'], $player['user_id'], ['delta' => -1,]);
            }
        }
    }

    private static function getIdByCookie(string $cookie): ?int
    {
        return self::getOneCustom(self::COOKIE_FIELD, $cookie)['id'] ?? null;
    }
}

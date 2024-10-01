<?php

class PrizesErudit
{
    protected const DAY_DISCOUNT = 0.5;
    protected const WEEK_DISCOUNT = 0.6;
    protected const MONTH_DISCOUNT = 0.7;
    protected const YEAR_DISCOUNT = 0.8;

    protected const DAY_TTL = 60 * 60 * 24 * 2;
    protected const WEEK_TTL = 60 * 60 * 24 * 7 * 2;
    protected const MONTH_TTL = 60 * 60 * 24 * 30 * 2;
    protected const YEAR_TTL = 60 * 60 * 24 * 365 * 2;

    protected const PERIODS = [
        'день' => 'day',
        'неделю' => 'week',
        'месяц' => 'month',
        'год' => 'year'
    ];

    const GAME_NAME = Game::ERUDIT;

    protected const ALL_RECORDS = 'erudit_all_records';

    protected const WORD_LEN_DAILY = 'erudit_word_len_daily_';
    protected const WORD_LEN_WEEKLY = 'erudit_word_len_weekly_';
    protected const WORD_LEN_MONTHLY = 'erudit_word_len_monthly_';
    protected const WORD_LEN_YEARLY = 'erudit_word_len_yearly_';

    protected const WORD_PRICE_DAILY = 'erudit_word_price_daily_';
    protected const WORD_PRICE_WEEKLY = 'erudit_word_price_weekly_';
    protected const WORD_PRICE_MONTHLY = 'erudit_word_price_monthly_';
    protected const WORD_PRICE_YEARLY = 'erudit_word_price_yearly_';

    protected const TURN_PRICE_DAILY = 'erudit_turn_price_daily_';
    protected const TURN_PRICE_WEEKLY = 'erudit_turn_price_weekly_';
    protected const TURN_PRICE_MONTHLY = 'erudit_turn_price_monthly_';
    protected const TURN_PRICE_YEARLY = 'erudit_turn_price_yearly_';

    protected const GAME_PRICE_DAILY = 'erudit_game_price_daily_';
    protected const GAME_PRICE_WEEKLY = 'erudit_game_price_weekly_';
    protected const GAME_PRICE_MONTHLY = 'erudit_game_price_monthly_';
    protected const GAME_PRICE_YEARLY = 'erudit_game_price_yearly_';

    protected const GAMES_PLAYED_DAILY = 'erudit_games_played_daily_';
    protected const GAMES_PLAYED_WEEKLY = 'erudit_games_played_weekly_';
    protected const GAMES_PLAYED_MONTHLY = 'erudit_games_played_monthly_';
    protected const GAMES_PLAYED_YEARLY = 'erudit_games_played_yearly_';

    public static function playerCurrentRecords($cookie = false)
    {
        $cookie = $cookie ?: $_COOKIE[Game::COOKIE_KEY];
        $allRecords = Cache::hgetall(static::ALL_RECORDS);
        $records = [];

        foreach ($allRecords as $type => $record) {
            $record = unserialize($record);
            if ($record['cookie'] == $cookie) {
                $records[$type] = array_merge($record, ['link' => AchievesModel::PRIZE_LINKS[$type], 'type' => $type]);
            }
        }

        if (count($records) > 1) {
            usort($records, ['self', 'recordsSort']);
        }

        return $records;
    }

    public static function getRandomRecord()
    {
        $allRecords = Cache::hgetall(static::ALL_RECORDS);

        foreach ($allRecords as $type => $record) {
            $record = unserialize($record);
            $record = array_merge(
                $record,
                [
                    'link' => AchievesModel::PRIZE_LINKS[$type],
                    'type' => $type,
                    'common_id' => PlayerModel::getPlayerID($record['cookie']) //getCommonIDByCookie($record['cookie'])
                ]
            );

            if ((rand(1, count($allRecords)) / count($allRecords)) <= 0.2) {
                break;
            }
        }

        return $record;
    }


    protected static function recordsSort($a, $b)
    {
        if (strpos($a['type'], 'year') && strpos($b['type'], 'year')) {
            return 0;
        }

        if (strpos($a['type'], 'year')) {
            return -1;
        }

        if (strpos($b['type'], 'year')) {
            return 1;
        }

        if (strpos($a['type'], 'month') && strpos($b['type'], 'month')) {
            return 0;
        }

        if (strpos($a['type'], 'month')) {
            return -1;
        }

        if (strpos($b['type'], 'month')) {
            return 1;
        }

        if (strpos($a['type'], 'week') && strpos($b['type'], 'week')) {
            return 0;
        }

        if (strpos($a['type'], 'week')) {
            return -1;
        }

        if (strpos($b['type'], 'week')) {
            return 1;
        }

        return 0;
    }

    protected static function saveHistory($eventType, $eventPeriod, array $arr)
    {
        $commonId = $arr['common_id'] ?? PlayerModel::getPlayerID($arr['cookie']);
        if (!$commonId) {
            return false;
        }

        if ($newId = AchievesModel::add(
            [
                AchievesModel::COMMON_ID_FIELD => $commonId,
                AchievesModel::DATE_ACHIEVED_FIELD => date('Y-m-d H:i:s', $arr['record_date']),
                AchievesModel::EVENT_TYPE_FIELD => $eventType,
                AchievesModel::EVENT_PERIOD_FIELD => $eventPeriod,
                AchievesModel::WORD_FIELD => $arr['word'] ?: '',
                AchievesModel::EVENT_VALUE_FIELD => $arr['value'],
                AchievesModel::IS_ACTIVE_FIELD => 1,
                AchievesModel::REWARD_FIELD => MonetizationService::REWARD[$eventPeriod],
                AchievesModel::INCOME_FIELD => MonetizationService::INCOME[$eventPeriod],
                AchievesModel::GAME_NAME_ID_FIELD => RatingHistoryModel::GAME_IDS[static::GAME_NAME],
            ]
        )) {
            return $newId;
        }

        return false;
    }

    protected
    static function saveAchieve(
        $cookie,
        $eventType,
        $eventPeriod,
        $eventValue,
        $word = false
    ): bool {
        $commonId = PlayerModel::getPlayerID($cookie);
        if (!$commonId) {
            return false;
        }

        \DB::transactionStart();

        AchievesModel::setParamMass(AchievesModel::IS_ACTIVE_FIELD,
                                    new ORM(0),
                                    [
                                        [
                                            'field_name' => AchievesModel::EVENT_TYPE_FIELD,
                                            'condition' => BaseModel::CONDITIONS['='],
                                            'value' => $eventType,
                                            'raw' => false,
                                        ],
                                        [
                                            'field_name' => AchievesModel::EVENT_PERIOD_FIELD,
                                            'condition' => BaseModel::CONDITIONS['='],
                                            'value' => $eventPeriod,
                                            'raw' => false,
                                        ],
                                        [
                                            'field_name' => AchievesModel::IS_ACTIVE_FIELD,
                                            'condition' => BaseModel::CONDITIONS['='],
                                            'value' => 1,
                                            'raw' => true,
                                        ],
                                        [
                                            'field_name' => AchievesModel::GAME_NAME_ID_FIELD,
                                            'condition' => BaseModel::CONDITIONS['='],
                                            'value' => RatingHistoryModel::GAME_IDS[static::GAME_NAME],
                                            'raw' => true,
                                        ],
                                    ]);

        if(!($newId = self::saveHistory(
            $eventType,
            $eventPeriod,
            [
                'common_id' => $commonId,
                'cookie' => $cookie ?: $_COOKIE[Game::COOKIE_KEY],
                'value' => $eventValue,
                'word' => $word,
                'record_date' => date('U'),
            ]
        ))) {
            \DB::transactionRollback();

            return false;
        };

        // todo здесь начислить reward на баланс монет
        if (!BalanceModel::changeBalance(
            $commonId,
            MonetizationService::REWARD[$eventPeriod],
            AchievesModel::getDescription($eventType, $eventPeriod, static::GAME_NAME),
            BalanceHistoryModel::TYPE_IDS[BalanceHistoryModel::ACHIEVE_TYPE],
            $newId
        )) {
            \DB::transactionRollback();

            return false;
        }

        // todo начислить income за первый час
        if (!IncomeModel::changeIncome(
            $commonId,
            MonetizationService::INCOME[$eventPeriod],
            AchievesModel::getDescription($eventType, $eventPeriod, static::GAME_NAME),
            IncomeHistoryModel::TYPE_IDS[IncomeHistoryModel::ACHIEVE_TYPE],
            $newId
        )) {
            \DB::transactionRollback();

            return false;
        }

        \DB::transactionCommit();

        Cache::hset(
            static::ALL_RECORDS,
            $eventType . '-' . $eventPeriod,
            [
                'cookie' => $cookie ? $cookie : $_COOKIE[Game::COOKIE_KEY],
                'value' => $eventValue,
                'word' => $word,
                'record_date' => date('U'),
            ]
        );

        return true;
    }

    public static function checkDayGamesPlayedRecord(array $players)
    {
        $todayRecord = Cache::get(static::GAMES_PLAYED_DAILY . strtotime('today'));

        if (!$todayRecord) {
            $yesterdayRecord = Cache::get(static::GAMES_PLAYED_DAILY . strtotime('-1 day'));
            $todayRecord = $yesterdayRecord
                ? ['number' => $yesterdayRecord['number'] * static::DAY_DISCOUNT]
                : ['number' => 2];
        }

        $weekRecord = Cache::get(static::GAMES_PLAYED_WEEKLY . date('W'));

        if (!$weekRecord) {
            $lastWeekRecord = Cache::get(static::GAMES_PLAYED_WEEKLY . (date('W') - 1));
            $weekRecord = $lastWeekRecord
                ? ['number' => $lastWeekRecord['number'] * static::WEEK_DISCOUNT]
                : ['number' => 20];
        }

        $monthRecord = Cache::get(static::GAMES_PLAYED_MONTHLY . date('n'));

        if (!$monthRecord) {
            $lastMonthRecord = Cache::get(static::GAMES_PLAYED_MONTHLY . (date('n') - 1));
            $monthRecord = $lastMonthRecord
                ? ['number' => $lastMonthRecord['number'] * static::MONTH_DISCOUNT]
                : ['number' => 70];
        }


        $yearRecord = Cache::get(static::GAMES_PLAYED_YEARLY . date('Y'));

        if (!$yearRecord) {
            $lastYearRecord = Cache::get(static::GAMES_PLAYED_YEARLY . (date('Y') - 1));
            $yearRecord = $lastYearRecord
                ? ['number' => $lastYearRecord['number'] * static::YEAR_DISCOUNT]
                : ['number' => 500];
        }

        $playersRecords = [];

        foreach (static::PERIODS as $period) {
            $activeAchieve = AchievesModel::getActive(
                    Game::$gameName,
                    'games_played',
                    $period
                )[0] ?? false;

            Cache::rpush('activeAchieve', $activeAchieve);
        }

        foreach ($players as $cookie) {
            $playerDailyPlayedGames = Cache::incr(static::GAMES_PLAYED_DAILY . $cookie . strtotime('today'));
            Cache::setex(
                static::GAMES_PLAYED_DAILY . $cookie . strtotime('today'),
                static::DAY_TTL,
                $playerDailyPlayedGames
            );
            //Просто пересохранили в кеше с ТТЛ

            if ($playerDailyPlayedGames > $todayRecord['number']) {
                Cache::setex(
                    static::GAMES_PLAYED_DAILY . strtotime('today'),
                    static::DAY_TTL,
                    ['number' => $playerDailyPlayedGames]
                );

                $activeAchieve = AchievesModel::getActive(Game::$gameName, 'games_played', static::PERIODS['день'])[0] ?? false;

                // Проверить, предыдущий рекорд принадлежит этому же игроку - просто обновить число игр
                if ($activeAchieve && ($activeAchieve[AchievesModel::COMMON_ID_FIELD] ?? 0) == PlayerModel::getPlayerID($cookie)) {
                    AchievesModel::setParam($activeAchieve[AchievesModel::ID_FIELD], AchievesModel::EVENT_VALUE_FIELD, $playerDailyPlayedGames, true);
                } else {
                    self::saveAchieve($cookie, 'games_played', static::PERIODS['день'], $playerDailyPlayedGames, false);
                }
                $todayRecord['number'] = $playerDailyPlayedGames;
                $playersRecords['день'] = [$cookie => $playerDailyPlayedGames];
            }

            if ($playerDailyPlayedGames > 1 || Cache::get(static::GAMES_PLAYED_WEEKLY . $cookie . date('W'))) {
                $playerWeeklyPlayedGames = Cache::incr(static::GAMES_PLAYED_WEEKLY . $cookie . date('W'));
                Cache::setex(static::GAMES_PLAYED_WEEKLY . $cookie . date('W'), static::WEEK_TTL, $playerWeeklyPlayedGames);
                //Просто пересохранили в кеше с ТТЛ

                if ($playerWeeklyPlayedGames > $weekRecord['number']) {
                    Cache::setex(
                        static::GAMES_PLAYED_WEEKLY . date('W'),
                        static::WEEK_TTL,
                        ['number' => $playerWeeklyPlayedGames]
                    );

                    $activeAchieve = AchievesModel::getActive(Game::$gameName, 'games_played', static::PERIODS['неделю'])[0] ?? false;

                    // Проверить, предыдущий рекорд принадлежит этому же игроку - просто обновить число игр
                    if ($activeAchieve && ($activeAchieve[AchievesModel::COMMON_ID_FIELD] ?? 0) == PlayerModel::getPlayerID($cookie)) {
                        AchievesModel::setParam($activeAchieve[AchievesModel::ID_FIELD], AchievesModel::EVENT_VALUE_FIELD, $playerWeeklyPlayedGames, true);
                    } else {
                        self::saveAchieve(
                            $cookie,
                            'games_played',
                            static::PERIODS['неделю'],
                            $playerWeeklyPlayedGames,
                            false
                        );
                    }

                    $weekRecord['number'] = $playerWeeklyPlayedGames;
                    $playersRecords['неделю'] = [$cookie => $playerWeeklyPlayedGames];
                }
            }

            if ($playerDailyPlayedGames > 3 || Cache::get(static::GAMES_PLAYED_MONTHLY . $cookie . date('n'))) {
                $playerMonthlyPlayedGames = Cache::incr(static::GAMES_PLAYED_MONTHLY . $cookie . date('n'));
                Cache::setex(
                    static::GAMES_PLAYED_MONTHLY . $cookie . date('n'),
                    static::MONTH_TTL,
                    $playerMonthlyPlayedGames
                );
                //Просто пересохранили в кеше с ТТЛ

                if ($playerMonthlyPlayedGames > $monthRecord['number']) {
                    Cache::setex(
                        static::GAMES_PLAYED_MONTHLY . date('n'),
                        static::MONTH_TTL,
                        ['number' => $playerMonthlyPlayedGames]
                    );

                    $activeAchieve = AchievesModel::getActive(Game::$gameName, 'games_played', static::PERIODS['месяц'])[0] ?? false;

                    if ($activeAchieve && ($activeAchieve[AchievesModel::COMMON_ID_FIELD] ?? 0) == PlayerModel::getPlayerID($cookie)) {
                        AchievesModel::setParam($activeAchieve[AchievesModel::ID_FIELD], AchievesModel::EVENT_VALUE_FIELD, $playerMonthlyPlayedGames, true);
                    } else {
                        self::saveAchieve(
                        $cookie,
                        'games_played',
                        static::PERIODS['месяц'],
                        $playerMonthlyPlayedGames,
                        false
                    );
                    }

                    $monthRecord['number'] = $playerMonthlyPlayedGames;
                    $playersRecords['месяц'] = [$cookie => $playerMonthlyPlayedGames];
                }
            }

            if ($playerDailyPlayedGames > 10 || Cache::get(static::GAMES_PLAYED_YEARLY . $cookie . date('Y'))) {
                $playerYearlyPlayedGames = Cache::incr(static::GAMES_PLAYED_YEARLY . $cookie . date('Y'));
                Cache::setex(static::GAMES_PLAYED_YEARLY . $cookie . date('Y'), static::YEAR_TTL, $playerYearlyPlayedGames);
                //Просто пересохранили в кеше с ТТЛ

                if ($playerYearlyPlayedGames > $yearRecord['number']) {
                    Cache::setex(
                        static::GAMES_PLAYED_YEARLY . date('Y'),
                        static::YEAR_TTL,
                        ['number' => $playerYearlyPlayedGames]
                    );

                    $activeAchieve = AchievesModel::getActive(Game::$gameName, 'games_played', static::PERIODS['год'])[0] ?? false;

                    // Проверить, предыдущий рекорд принадлежит этому же игроку - просто обновить число игр
                    if ($activeAchieve && ($activeAchieve[AchievesModel::COMMON_ID_FIELD] ?? 0) == PlayerModel::getPlayerID($cookie)) {
                        AchievesModel::setParam($activeAchieve[AchievesModel::ID_FIELD], AchievesModel::EVENT_VALUE_FIELD, $playerYearlyPlayedGames, true);
                    } else {
                        self::saveAchieve(
                            $cookie,
                            'games_played',
                            static::PERIODS['год'],
                            $playerYearlyPlayedGames,
                            false
                        );
                    }

                    $yearRecord['number'] = $playerYearlyPlayedGames;
                    $playersRecords['год'] = [$cookie => $playerYearlyPlayedGames];
                }
            }
        }

        return $playersRecords;
    }

    public static function checkDayGamePriceRecord(
        $price,
        $cookie
    ) {
        $todayRecord = Cache::get(static::GAME_PRICE_DAILY . strtotime('today'));

        if (!$todayRecord) {
            $yesterdayRecord = Cache::get(static::GAME_PRICE_DAILY . strtotime('-1 day'));
            $todayRecord = $yesterdayRecord
                ? ['price' => $yesterdayRecord['price'] * static::DAY_DISCOUNT]
                : ['price' => 10];
        }

        if ($price > $todayRecord['price']) {
            Cache::setex(static::GAME_PRICE_DAILY . strtotime('today'), static::DAY_TTL, ['price' => $price]);

            $res = array_merge(['день' => true], self::checkWeekGamePriceRecord($price));
            foreach ($res as $period => $value) {
                self::saveAchieve($cookie, 'game_price', static::PERIODS[$period], $price, false);
            }

            return $res;
        }

        return [];
    }

    public
    static function checkWeekGamePriceRecord(
        $price
    ) {
        $weekRecord = Cache::get(static::GAME_PRICE_WEEKLY . date('W'));

        if (!$weekRecord) {
            $preWeekRecord = Cache::get(static::GAME_PRICE_WEEKLY . (date('W') - 1));
            $weekRecord = $preWeekRecord
                ? ['price' => $preWeekRecord['price'] * static::WEEK_DISCOUNT]
                : ['price' => 10];
        }

        if ($price > $weekRecord['price']) {
            Cache::setex(static::GAME_PRICE_WEEKLY . date('W'), static::WEEK_TTL, ['price' => $price]);

            return array_merge(['неделю' => true], self::checkMonthGamePriceRecord($price));
        }

        return [];
    }

    public
    static function checkMonthGamePriceRecord(
        $price
    ) {
        $monthRecord = Cache::get(static::GAME_PRICE_MONTHLY . date('n'));

        if (!$monthRecord) {
            $preMonthRecord = Cache::get(static::GAME_PRICE_MONTHLY . (date('n') - 1));
            $monthRecord = $preMonthRecord
                ? ['price' => $preMonthRecord['price'] * static::MONTH_DISCOUNT]
                : ['price' => 10];
        }

        if ($price > $monthRecord['price']) {
            Cache::setex(static::GAME_PRICE_MONTHLY . date('n'), static::MONTH_TTL, ['price' => $price]);

            return array_merge(['месяц' => true], self::checkYearGamePriceRecord($price));
        }

        return [];
    }

    public
    static function checkYearGamePriceRecord(
        $price
    ) {
        $yearRecord = Cache::get(static::GAME_PRICE_YEARLY . date('Y'));

        if (!$yearRecord) {
            $preYearRecord = Cache::get(static::GAME_PRICE_YEARLY . (date('Y') - 1));
            $yearRecord = $preYearRecord
                ? ['price' => $preYearRecord['price'] * static::YEAR_DISCOUNT]
                : ['price' => 10];
        }

        if ($price > $yearRecord['price']) {
            Cache::setex(static::GAME_PRICE_YEARLY . date('Y'), static::YEAR_TTL, ['price' => $price]);

            return ['год' => true];
        }

        return [];
    }

    public
    static function checkDayTurnPriceRecord(
        $price,
        $cookie
    ) {
        $todayRecord = Cache::get(static::TURN_PRICE_DAILY . strtotime('today'));

        if (!$todayRecord) {
            $yesterdayRecord = Cache::get(static::TURN_PRICE_DAILY . strtotime('-1 day'));
            $todayRecord = $yesterdayRecord
                ? ['price' => $yesterdayRecord['price'] * static::DAY_DISCOUNT]
                : ['price' => 10];
        }

        if ($price > $todayRecord['price']) {
            Cache::setex(static::TURN_PRICE_DAILY . strtotime('today'), static::DAY_TTL, ['price' => $price]);

            $res = array_merge(['день' => true], self::checkWeekTurnPriceRecord($price));
            foreach ($res as $period => $value) {
                self::saveAchieve($cookie, 'turn_price', static::PERIODS[$period], $price, false);
            }

            return $res;
        }

        return [];
    }

    public
    static function checkWeekTurnPriceRecord(
        $price
    ) {
        $weekRecord = Cache::get(static::TURN_PRICE_WEEKLY . date('W'));

        if (!$weekRecord) {
            $preWeekRecord = Cache::get(static::TURN_PRICE_WEEKLY . (date('W') - 1));
            $weekRecord = $preWeekRecord
                ? ['price' => $preWeekRecord['price'] * static::WEEK_DISCOUNT]
                : ['price' => 10];
        }

        if ($price > $weekRecord['price']) {
            Cache::setex(static::TURN_PRICE_WEEKLY . date('W'), static::WEEK_TTL, ['price' => $price]);

            return array_merge(['неделю' => true], self::checkMonthTurnPriceRecord($price));
        }

        return [];
    }

    public
    static function checkMonthTurnPriceRecord(
        $price
    ) {
        $monthRecord = Cache::get(static::TURN_PRICE_MONTHLY . date('n'));

        if (!$monthRecord) {
            $preMonthRecord = Cache::get(static::TURN_PRICE_MONTHLY . (date('n') - 1));
            $monthRecord = $preMonthRecord
                ? ['price' => $preMonthRecord['price'] * static::MONTH_DISCOUNT]
                : ['price' => 10];
        }

        if ($price > $monthRecord['price']) {
            Cache::setex(static::TURN_PRICE_MONTHLY . date('n'), static::MONTH_TTL, ['price' => $price]);

            return array_merge(['месяц' => true], self::checkYearTurnPriceRecord($price));
        }

        return [];
    }

    public
    static function checkYearTurnPriceRecord(
        $price
    ) {
        $yearRecord = Cache::get(static::TURN_PRICE_YEARLY . date('Y'));

        if (!$yearRecord) {
            $preYearRecord = Cache::get(static::TURN_PRICE_YEARLY . (date('Y') - 1));
            $yearRecord = $preYearRecord
                ? ['price' => $preYearRecord['price'] * static::YEAR_DISCOUNT]
                : ['price' => 10];
        }

        if ($price > $yearRecord['price']) {
            Cache::setex(static::TURN_PRICE_YEARLY . date('Y'), static::YEAR_TTL, ['price' => $price]);

            return ['год' => true];
        }

        return [];
    }

    public
    static function checkDayWordPriceRecord(
        $word,
        $price,
        $cookie
    ) {
        $todayRecord = Cache::get(static::WORD_PRICE_DAILY . strtotime('today'));

        if (!$todayRecord) {
            $yesterdayRecord = Cache::get(static::WORD_PRICE_DAILY . strtotime('-1 day'));
            $todayRecord = $yesterdayRecord
                ? ['word' => $yesterdayRecord['word'], 'price' => $yesterdayRecord['price'] * static::DAY_DISCOUNT]
                : ['word' => 'эра', 'price' => 10];
        }

        if ($price > $todayRecord['price']) {
            Cache::setex(
                static::WORD_PRICE_DAILY . strtotime('today'),
                static::DAY_TTL,
                ['word' => $word, 'price' => $price]
            );

            $res = array_merge(['день' => true], self::checkWeekWordPriceRecord($word, $price));
            foreach ($res as $period => $value) {
                self::saveAchieve($cookie, 'word_price', static::PERIODS[$period], $price, $word);
            }

            return $res;
        }

        return [];
    }

    public
    static function checkWeekWordPriceRecord(
        $word,
        $price
    ) {
        $weekRecord = Cache::get(static::WORD_PRICE_WEEKLY . date('W'));

        if (!$weekRecord) {
            $preWeekRecord = Cache::get(static::WORD_PRICE_WEEKLY . (date('W') - 1));
            $weekRecord = $preWeekRecord
                ? ['word' => $preWeekRecord['word'], 'price' => $preWeekRecord['price'] * static::WEEK_DISCOUNT]
                : ['word' => 'эра', 'price' => 10];
        }

        if ($price > $weekRecord['price']) {
            Cache::setex(static::WORD_PRICE_WEEKLY . date('W'), static::WEEK_TTL, ['word' => $word, 'price' => $price]);

            return array_merge(['неделю' => true], self::checkMonthWordPriceRecord($word, $price));
        }

        return [];
    }

    public
    static function checkMonthWordPriceRecord(
        $word,
        $price
    ) {
        $monthRecord = Cache::get(static::WORD_PRICE_MONTHLY . date('n'));

        if (!$monthRecord) {
            $preMonthRecord = Cache::get(static::WORD_PRICE_MONTHLY . (date('n') - 1));
            $monthRecord = $preMonthRecord
                ? ['word' => $preMonthRecord['word'], 'price' => $preMonthRecord['price'] * static::MONTH_DISCOUNT]
                : ['word' => 'ерш', 'price' => 10];
        }

        if ($price > $monthRecord['price']) {
            Cache::setex(static::WORD_PRICE_MONTHLY . date('n'), static::MONTH_TTL, ['word' => $word, 'price' => $price]);

            return array_merge(['месяц' => true], self::checkYearWordPriceRecord($word, $price));
        }

        return [];
    }

    public
    static function checkYearWordPriceRecord(
        $word,
        $price
    ) {
        $yearRecord = Cache::get(static::WORD_PRICE_YEARLY . date('Y'));

        if (!$yearRecord) {
            $preYearRecord = Cache::get(static::WORD_PRICE_YEARLY . (date('Y') - 1));
            $yearRecord = $preYearRecord
                ? ['word' => $preYearRecord['word'], 'price' => $preYearRecord['price'] * static::YEAR_DISCOUNT]
                : ['word' => 'ерш', 'price' => 10];
        }

        if ($price > $yearRecord['price']) {
            Cache::setex(static::WORD_PRICE_YEARLY . date('Y'), static::YEAR_TTL, ['word' => $word, 'price' => $price]);

            return ['год' => true];
        }

        return [];
    }


    public
    static function checkDayWordLenRecord(
        $word,
        $cookie
    ) {
        $wordLen = mb_strlen($word, 'UTF-8');
        $todayRecord = Cache::get(static::WORD_LEN_DAILY . strtotime('today'));
        if (!$todayRecord) {
            $yesterdayRecord = Cache::get(static::WORD_LEN_DAILY . strtotime('-1 day'));
            $todayRecord = $yesterdayRecord
                ? ['word' => $yesterdayRecord['word'], 'length' => $yesterdayRecord['length'] * static::DAY_DISCOUNT]
                : ['word' => 'эра', 'length' => 3];
        }

        if ($wordLen > $todayRecord['length']) {
            Cache::setex(
                static::WORD_LEN_DAILY . strtotime('today'),
                static::DAY_TTL,
                ['word' => $word, 'length' => $wordLen]
            );

            $res = array_merge(['день' => true], self::checkWeekWordLenRecord($word));
            foreach ($res as $period => $value) {
                self::saveAchieve($cookie, 'word_len', static::PERIODS[$period], $wordLen, $word);
            }

            return $res;
        }

        return [];
    }

    public
    static function checkWeekWordLenRecord(
        $word
    ) {
        $wordLen = mb_strlen($word, 'UTF-8');
        $weekRecord = Cache::get(static::WORD_LEN_WEEKLY . date('W'));
        if (!$weekRecord) {
            $preWeekRecord = Cache::get(static::WORD_LEN_WEEKLY . (date('W') - 1));
            $weekRecord = $preWeekRecord
                ? ['word' => $preWeekRecord['word'], 'length' => mb_strlen($preWeekRecord['word'], 'UTF-8') * static::WEEK_DISCOUNT]
                : ['word' => 'эра', 'length' => 3];
        }

        if ($wordLen > $weekRecord['length']) {
            Cache::setex(static::WORD_LEN_WEEKLY . date('W'), static::WEEK_TTL, ['word' => $word, 'length' => $wordLen]);

            return array_merge(['неделю' => true], self::checkMonthWordLenRecord($word));
        }

        return [];
    }

    public
    static function checkMonthWordLenRecord(
        $word
    ) {
        $wordLen = mb_strlen($word, 'UTF-8');
        $monthRecord = Cache::get(static::WORD_LEN_MONTHLY . date('n'));
        if (!$monthRecord) {
            $preMonthRecord = Cache::get(static::WORD_LEN_MONTHLY . (date('n') - 1));
            $monthRecord = $preMonthRecord
                ? ['word' => $preMonthRecord['word'], 'length' => $preMonthRecord['length'] * static::MONTH_DISCOUNT]
                : ['word' => 'ерш', 'length' => 3];
        }

        if ($wordLen > $monthRecord['length']) {
            Cache::setex(static::WORD_LEN_MONTHLY . date('n'), static::MONTH_TTL, ['word' => $word, 'length' => $wordLen]);

            return array_merge(['месяц' => true], self::checkYearWordLenRecord($word));
        }

        return [];
    }

    public
    static function checkYearWordLenRecord(
        $word
    ) {
        $wordLen = mb_strlen($word, 'UTF-8');
        $yearRecord = Cache::get(static::WORD_LEN_YEARLY . date('Y'));
        if (!$yearRecord) {
            $preYearRecord = Cache::get(static::WORD_LEN_YEARLY . (date('Y') - 1));
            $yearRecord = $preYearRecord
                ? ['word' => $preYearRecord['word'], 'length' => $preYearRecord['length'] * static::YEAR_DISCOUNT]
                : ['word' => 'юра', 'length' => 3];
        }
        if ($wordLen > $yearRecord['length']) {
            Cache::setex(static::WORD_LEN_YEARLY . date('Y'), static::YEAR_TTL, ['word' => $word, 'length' => $wordLen]);

            return ['год' => true];
        }

        return [];
    }
}
<?php

class PrizesErudit
{
    protected const DAY_DISCOUNT = 0.5;
    protected const WEEK_DISCOUNT = 0.6;
    protected const MONTH_DISCOUNT = 0.7;
    protected const YEAR_DISCOUNT = 0.8;

    const PERIOD_DISCOUNTS = [
        Record::DAY_PERIOD => self::DAY_DISCOUNT,
        Record::WEEK_PERIOD => self::WEEK_DISCOUNT,
        Record::MONTH_PERIOD => self::MONTH_DISCOUNT,
        Record::YEAR_PERIOD => self::YEAR_DISCOUNT,
    ];

    protected const DAY = 'день';
    protected const WEEK = 'неделю';
    protected const MONTH = 'месяц';
    protected const YEAR = 'год';

    const PERIODS = [
        self::DAY => Record::DAY_PERIOD,
        self::WEEK => Record::WEEK_PERIOD,
        self::MONTH => Record::MONTH_PERIOD,
        self::YEAR => Record::YEAR_PERIOD,
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
    const DEFAULT_WEEK_GAME_PRICE_RECORD = 300;
    const DEFAULT_WEEK_WORD = 'эра';
    const DEFAULT_WEEK_WORD_LENGTH = 3;
    const DEFAULT_MONTH_WORD = 'ершик';
    const DEFAULT_MONTH_WORD_LENGTH = 5;
    const DEFAULT_YEAR_WORD = 'куртка';
    const DEFAULT_YEAR_WORD_LENGTH = 6;
    const DEFAULT_DAY_WORD = 'ар';
    const DEFAULT_DAY_WORD_LENGTH = 2;
    const DEFAULT_DAY_GAMES = 2;
    const DEFAULT_WEEK_GAMES = 20;
    const DEFAULT_MONTH_GAMES = 70;
    const DEFAULT_YEAR_GAMES = 500;
    const DEFAULT_DAY_TURN_PRICE = 50;
    const DEFAULT_WEEK_TURN_PRICE = 60;
    const DEFAULT_MONTH_TURN_PRICE = 70;
    const DEFAULT_YEAR_TURN_PRICE = 80;
    const DEFAULT_DAY_WORD_PRICE = 10;
    const DEFAULT_WEEK_WORD_PRICE = 20;
    const DEFAULT_MONTH_WORD_PRICE = 30;
    const DEFAULT_YEAR_WORD_PRICE = 40;

    const DEFAULT_GAME_RECORDS = [
        Record::DAY_PERIOD => self::DEFAULT_DAY_GAMES,
        Record::WEEK_PERIOD => self::DEFAULT_WEEK_GAMES,
        Record::MONTH_PERIOD => self::DEFAULT_MONTH_GAMES,
        Record::YEAR_PERIOD => self::DEFAULT_YEAR_GAMES,
    ];

    const DEFAULT_TURN_PRICE_RECORDS = [
        Record::TURN_PRICE => [
            Record::DAY_PERIOD => self::DEFAULT_DAY_TURN_PRICE,
            Record::WEEK_PERIOD => self::DEFAULT_WEEK_TURN_PRICE,
            Record::MONTH_PERIOD => self::DEFAULT_MONTH_TURN_PRICE,
            Record::YEAR_PERIOD => self::DEFAULT_YEAR_TURN_PRICE,
        ],
        Record::WORD_LEN => [
            Record::DAY_PERIOD => self::DEFAULT_DAY_WORD_LENGTH,
            Record::WEEK_PERIOD => self::DEFAULT_WEEK_WORD_LENGTH,
            Record::MONTH_PERIOD => self::DEFAULT_MONTH_WORD_LENGTH,
            Record::YEAR_PERIOD => self::DEFAULT_YEAR_WORD_LENGTH,
        ],
        Record::WORD_PRICE => [
            Record::DAY_PERIOD => self::DEFAULT_DAY_WORD_PRICE,
            Record::WEEK_PERIOD => self::DEFAULT_WEEK_WORD_PRICE,
            Record::MONTH_PERIOD => self::DEFAULT_MONTH_WORD_PRICE,
            Record::YEAR_PERIOD => self::DEFAULT_YEAR_WORD_PRICE,
        ],
    ];

    public static function restoreRecords(
        array $recordTypes = [
            AchievesModel::WORD_LEN,
            AchievesModel::GAME_PRICE,
            AchievesModel::TURN_PRICE,
            AchievesModel::WORD_PRICE,
        ]
    ): bool {
        return false;
    }

    // todo CLUB-466 получить рекорд из БД
    public
    static function getRandomRecord(): ?array
    {
        $allRecords = Cache::hgetall(static::ALL_RECORDS);

        if (!is_array($allRecords)) {
            return null;
        }

        foreach ($allRecords as $type => $record) {
            $record = unserialize($record);

            if (!is_array($record)) {
                return null;
            }

            $record = array_merge(
                $record,
                [
                    'link' => AchievesModel::PRIZE_LINKS[$type],
                    'type' => $type,
                    'common_id' => $record['common_id'] ?? PlayerModel::getPlayerID($record['cookie'])
                ]
            );

            if ((rand(1, count($allRecords)) / count($allRecords)) <= 0.2) {
                break;
            }
        }

        return $record;
    }


    protected
    static function recordsSort(
        $a,
        $b
    ) {
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

    protected
    static function saveHistory(
        $eventType,
        $eventPeriod,
        array $arr
    ) {
        $commonId = $arr['common_id'] ?? PlayerModel::getPlayerID($arr['cookie'] ?? '');
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
                AchievesModel::GAME_NAME_ID_FIELD => BaseModel::GAME_IDS[static::GAME_NAME],
            ]
        )) {
            return $newId;
        }

        return false;
    }

    protected
    static function saveAchieve(
        Record $record
    ): bool {
        $record->_common_id = $record->_common_id ?? PlayerModel::getPlayerID($record->cookie);
        if (!$record->_common_id) {
            return false;
        }

        \DB::transactionStart();

        AchievesModel::setParamMass(
            AchievesModel::IS_ACTIVE_FIELD,
            new ORM(0),
            [
                [
                    'field_name' => AchievesModel::EVENT_TYPE_FIELD,
                    'condition' => BaseModel::CONDITIONS['='],
                    'value' => $record->_event_type,
                    'raw' => false,
                ],
                [
                    'field_name' => AchievesModel::EVENT_PERIOD_FIELD,
                    'condition' => BaseModel::CONDITIONS['='],
                    'value' => $record->_event_period,
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
                    'value' => BaseModel::GAME_IDS[static::GAME_NAME],
                    'raw' => true,
                ],
            ]
        );

        if (!($newId = self::saveHistory(
            $record->_event_type,
            $record->_event_period,
            [
                'common_id' => $record->_common_id,
                'cookie' => $record->cookie ?? $_COOKIE[CookieErudit::COOKIE_NAME],
                'value' => $record->_event_value,
                'word' => $record->_word ?? null,
                'record_date' => date('U'),
            ]
        ))) {
            \DB::transactionRollback();

            return false;
        };

        if (!BalanceModel::changeBalance(
            $record->_common_id,
            MonetizationService::REWARD[$record->_event_period],
            AchievesModel::getDescription($record->_event_type, $record->_event_period, static::GAME_NAME),
            BalanceHistoryModel::TYPE_IDS[BalanceHistoryModel::ACHIEVE_TYPE],
            $newId
        )) {
            \DB::transactionRollback();

            return false;
        }

        if (!IncomeModel::changeIncome(
            $record->_common_id,
            MonetizationService::INCOME[$record->_event_period],
            AchievesModel::getDescription($record->_event_type, $record->_event_period, static::GAME_NAME),
            IncomeHistoryModel::TYPE_IDS[IncomeHistoryModel::ACHIEVE_TYPE],
            $newId
        )) {
            \DB::transactionRollback();

            return false;
        }

        \DB::transactionCommit();

        Cache::hset(
            static::ALL_RECORDS,
            $record->_event_type . '-' . $record->_event_period,
            $record
        );

        return true;
    }

    public
    static function checkDayGamesPlayedRecord(
        array $playerIds
    ): array {
        $playersRecords = [];

        // todo CLUB-466 удалить ключи в Redis  redis-cli keys erudit_games_played_* | xargs redis-cli DEL

        foreach (static::PERIODS as $localizedPeriod => $period) {
            $periodRecord = Record::getRecord(Record::GAMES_PLAYED, $period, static::GAME_NAME, true);
            if (!$periodRecord) {
                $lastPeriodRecord = Record::getRecord(Record::GAMES_PLAYED, $period, static::GAME_NAME, false);
                $periodRecord = new Record();
                $periodRecord->_event_value = $lastPeriodRecord
                    ? $lastPeriodRecord->_event_value * static::PERIOD_DISCOUNTS[$period]
                    : static::DEFAULT_GAME_RECORDS[$period];
            }

            switch ($period) {
                case Record::DAY_PERIOD:
                    $timestamp = date('Y-m-d');

                    break;
                case Record::WEEK_PERIOD:
                    $timestamp = date('Y-m-d', strtotime('monday this week'));

                    break;
                case Record::MONTH_PERIOD:
                    $timestamp = date('Y-m-01');

                    break;
                case Record::YEAR_PERIOD:
                    $timestamp = date('Y-01-01');

                    break;
                default:
                    return $playersRecords;
            }

            foreach ($playerIds as $commonId) {
                $playerPeriodPlayedGames = RatingHistoryModel::getNumGamesPlayed(
                    $commonId,
                    static::GAME_NAME,
                    $timestamp
                );

                if ($playerPeriodPlayedGames > $periodRecord->_event_value) {
                    $activeAchieve = Record::getRecord(
                        AchievesModel::GAMES_PLAYED,
                        $period,
                        static::GAME_NAME,
                        false,
                    );

                    // Проверить, предыдущий рекорд принадлежит этому же игроку - просто обновить число игр
                    if ($activeAchieve && ($activeAchieve->_common_id ?? 0) === (int)$commonId) {
                        $activeAchieve->_event_value = $playerPeriodPlayedGames;
                        $activeAchieve->save();
                    } else {
                        self::saveAchieve(
                            Record::new(
                                [
                                    Record::COMMON_ID_FIELD => $commonId,
                                    Record::EVENT_TYPE_FIELD => AchievesModel::GAMES_PLAYED,
                                    Record::EVENT_PERIOD_FIELD => $period,
                                    Record::EVENT_VALUE_FIELD => $playerPeriodPlayedGames,
                                ]
                            )
                        );
                    }

                    // Сохраняем с ключом $localizedPeriod для подстановки в локализованные строки на нужном языке
                    $playersRecords[$localizedPeriod] = [$commonId => $playerPeriodPlayedGames];
                }
            }
        }

        return $playersRecords;
    }

    public
    static function checkDayGamePriceRecord(
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
            Cache::set(static::GAME_PRICE_DAILY . strtotime('today'), ['price' => $price]);

            $res = array_merge([static::DAY => true], self::checkWeekGamePriceRecord($price));
            foreach ($res as $period => $value) {
                self::saveAchieve(
                    Record::new(
                        [
                            Record::COOKIE_PARAM => $cookie,
                            Record::EVENT_TYPE_FIELD => AchievesModel::GAME_PRICE,
                            Record::EVENT_PERIOD_FIELD => static::PERIODS[$period],
                            Record::EVENT_VALUE_FIELD => $price,
                        ]
                    )
                );
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
                : ['price' => self::DEFAULT_WEEK_GAME_PRICE_RECORD];
        }

        if ($price > $weekRecord['price']) {
            Cache::set(static::GAME_PRICE_WEEKLY . date('W'), ['price' => $price]);

            return array_merge([static::WEEK => true], self::checkMonthGamePriceRecord($price));
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
            Cache::set(static::GAME_PRICE_MONTHLY . date('n'), ['price' => $price]);

            return array_merge([static::MONTH => true], self::checkYearGamePriceRecord($price));
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
            Cache::set(static::GAME_PRICE_YEARLY . date('Y'), ['price' => $price]);

            return [static::YEAR => true];
        }

        return [];
    }

    /**
     * Общий метод проверки рекорда. Возвращает массив ['день' => [<common_id> => <значение>], ...]
     * @param int $checkValue Значение для проверки
     * @param int $commonId
     * @param string $type Тип рекорда для проверки
     * @param string|null $word Слово для статистики
     * @return array
     */
    public
    static function checkRecord(
        int $checkValue,
        int $commonId,
        string $type,
        ?string $word = null
    ): array {
        if (!in_array($type, Record::VALID_RECORD_TYPES)) {
            return [];
        }

        $playersRecords = [];

        // todo CLUB-466 удалить ключи в Redis  redis-cli keys erudit_turn_price_* | xargs redis-cli DEL

        foreach (static::PERIODS as $localizedPeriod => $period) {
            $periodRecord = Record::getRecord($type, $period, static::GAME_NAME, true);
            if (!$periodRecord) {
                $lastPeriodRecord = Record::getRecord($type, $period, static::GAME_NAME, false);
                $periodRecord = new Record();
                $periodRecord->_event_value = $lastPeriodRecord
                    ? $lastPeriodRecord->_event_value * static::PERIOD_DISCOUNTS[$period]
                    : static::DEFAULT_TURN_PRICE_RECORDS[$type][$period];
            }

            switch ($period) {
                case Record::DAY_PERIOD:
                    $timestamp = date('Y-m-d');

                    break;
                case Record::WEEK_PERIOD:
                    $timestamp = date('Y-m-d', strtotime('monday this week'));

                    break;
                case Record::MONTH_PERIOD:
                    $timestamp = date('Y-m-01');

                    break;
                case Record::YEAR_PERIOD:
                    $timestamp = date('Y-01-01');

                    break;
                default:
                    return $playersRecords;
            }

            // todo CLUB 466 убрать
            Cache::hset(
                "erudit_test_{$type}_records",
                "$period",
                ['$timestamp' => $timestamp, '$periodRecord' => $periodRecord]
            );

            if ($checkValue > $periodRecord->_event_value) {
                self::saveAchieve(
                    Record::new(
                        [
                            Record::COMMON_ID_FIELD => $commonId,
                            Record::EVENT_TYPE_FIELD => $type,
                            Record::EVENT_PERIOD_FIELD => $period,
                            Record::EVENT_VALUE_FIELD => $checkValue,
                        ]
                        + ($word ? [Record::WORD_FIELD => $word] : [])
                    )
                );

                // Сохраняем с ключом $localizedPeriod для подстановки в локализованные строки на нужном языке
                $playersRecords[$localizedPeriod] = [$commonId => $checkValue];
            } else {
                break;
            }
        }

        return $playersRecords;
    }

    public
    static function checkDayTurnPriceRecord(
        $price,
        $commonId
    ): array {
        $playersRecords = [];

        // todo CLUB-466 удалить ключи в Redis  redis-cli keys erudit_turn_price_* | xargs redis-cli DEL

        foreach (static::PERIODS as $localizedPeriod => $period) {
            $periodRecord = Record::getRecord(Record::TURN_PRICE, $period, static::GAME_NAME, true);
            if (!$periodRecord) {
                $lastPeriodRecord = Record::getRecord(Record::TURN_PRICE, $period, static::GAME_NAME, false);
                $periodRecord = new Record();
                $periodRecord->_event_value = $lastPeriodRecord
                    ? $lastPeriodRecord->_event_value * static::PERIOD_DISCOUNTS[$period]
                    : static::DEFAULT_TURN_PRICE_RECORDS[Record::TURN_PRICE][$period];
            }

            switch ($period) {
                case Record::DAY_PERIOD:
                    $timestamp = date('Y-m-d');

                    break;
                case Record::WEEK_PERIOD:
                    $timestamp = date('Y-m-d', strtotime('monday this week'));

                    break;
                case Record::MONTH_PERIOD:
                    $timestamp = date('Y-m-01');

                    break;
                case Record::YEAR_PERIOD:
                    $timestamp = date('Y-01-01');

                    break;
                default:
                    return $playersRecords;
            }

            Cache::hset(
                'erudit_test_turn_price_records',
                $period,
                ['$timestamp' => $timestamp, '$periodRecord' => $periodRecord]
            );

            if ($price > $periodRecord->_event_value) {
                self::saveAchieve(
                    Record::new(
                        [
                            Record::COMMON_ID_FIELD => $commonId,
                            Record::EVENT_TYPE_FIELD => AchievesModel::TURN_PRICE,
                            Record::EVENT_PERIOD_FIELD => $period,
                            Record::EVENT_VALUE_FIELD => $price,
                        ]
                    )
                );

                // Сохраняем с ключом $localizedPeriod для подстановки в локализованные строки на нужном языке
                $playersRecords[$localizedPeriod] = [$commonId => $price];
            } else {
                break;
            }
        }

        return $playersRecords;
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
            Cache::set(static::TURN_PRICE_WEEKLY . date('W'), ['price' => $price]);

            return array_merge([static::WEEK => true], self::checkMonthTurnPriceRecord($price));
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
            Cache::set(static::TURN_PRICE_MONTHLY . date('n'), ['price' => $price]);

            return array_merge([static::MONTH => true], self::checkYearTurnPriceRecord($price));
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
            Cache::set(static::TURN_PRICE_YEARLY . date('Y'), ['price' => $price]);

            return [static::YEAR => true];
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
            Cache::set(
                static::WORD_PRICE_DAILY . strtotime('today'),
                ['word' => $word, 'price' => $price]
            );

            $res = array_merge([static::DAY => true], self::checkWeekWordPriceRecord($word, $price));
            foreach ($res as $period => $value) {
                self::saveAchieve(
                    Record::new(
                        [
                            Record::COOKIE_PARAM => $cookie,
                            Record::EVENT_TYPE_FIELD => AchievesModel::WORD_PRICE,
                            Record::EVENT_PERIOD_FIELD => static::PERIODS[$period],
                            Record::EVENT_VALUE_FIELD => $price,
                            Record::WORD_FIELD => $word,
                        ]
                    )
                );
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
            Cache::set(static::WORD_PRICE_WEEKLY . date('W'), ['word' => $word, 'price' => $price]);

            return array_merge([static::WEEK => true], self::checkMonthWordPriceRecord($word, $price));
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
            Cache::set(static::WORD_PRICE_MONTHLY . date('n'), ['word' => $word, 'price' => $price]);

            return array_merge([static::MONTH => true], self::checkYearWordPriceRecord($word, $price));
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
            Cache::set(static::WORD_PRICE_YEARLY . date('Y'), ['word' => $word, 'price' => $price]);

            return [static::YEAR => true];
        }

        return [];
    }


    public
    static function checkDayWordLenRecord(
        $word,
        $cookie
    ) {
        $res = [];

        $wordLen = mb_strlen($word, 'UTF-8');
        $todayRecord = Cache::get(static::WORD_LEN_DAILY . strtotime('today'));
        if (!$todayRecord) {
            $yesterdayRecord = Cache::get(static::WORD_LEN_DAILY . strtotime('-1 day'));
            $todayRecord = $yesterdayRecord
                ? ['word' => $yesterdayRecord['word'], 'length' => $yesterdayRecord['length'] * static::DAY_DISCOUNT]
                : ['word' => static::DEFAULT_DAY_WORD, 'length' => mb_strlen(static::DEFAULT_DAY_WORD, 'UTF-8')];
        }

        if ($wordLen > $todayRecord['length']) {
            Cache::set(
                static::WORD_LEN_DAILY . strtotime('today'),
                ['word' => $word, 'length' => $wordLen]
            );

            $res = array_merge([static::DAY => true], self::checkWeekWordLenRecord($word));
            foreach ($res as $period => $value) {
                self::saveAchieve(
                    Record::new(
                        [
                            Record::COOKIE_PARAM => $cookie,
                            Record::EVENT_TYPE_FIELD => AchievesModel::WORD_LEN,
                            Record::EVENT_PERIOD_FIELD => static::PERIODS[$period],
                            Record::EVENT_VALUE_FIELD => $wordLen,
                            Record::WORD_FIELD => $word,
                        ]
                    )
                );
            }
        }

        return $res;
    }

    public static function checkWeekWordLenRecord($word)
    {
        $wordLen = mb_strlen($word, 'UTF-8');
        $weekRecord = Cache::get(static::WORD_LEN_WEEKLY . date('W'));
        if (!$weekRecord) {
            $preWeekRecord = Cache::get(static::WORD_LEN_WEEKLY . (date('W') - 1));
            $weekRecord = $preWeekRecord
                ? [
                    'word' => $preWeekRecord['word'],
                    'length' => mb_strlen($preWeekRecord['word'], 'UTF-8') * static::WEEK_DISCOUNT
                ]
                : ['word' => static::DEFAULT_WEEK_WORD, 'length' => mb_strlen(static::DEFAULT_WEEK_WORD, 'UTF-8')];
        }

        if ($wordLen > $weekRecord['length']) {
            Cache::set(static::WORD_LEN_WEEKLY . date('W'), ['word' => $word, 'length' => $wordLen]);

            return array_merge([static::WEEK => true], self::checkMonthWordLenRecord($word));
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
                : ['word' => static::DEFAULT_MONTH_WORD, 'length' => mb_strlen(static::DEFAULT_MONTH_WORD, 'UTF-8')];
        }

        if ($wordLen > $monthRecord['length']) {
            Cache::set(static::WORD_LEN_MONTHLY . date('n'), ['word' => $word, 'length' => $wordLen]);

            return array_merge([static::MONTH => true], self::checkYearWordLenRecord($word));
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
                : ['word' => static::DEFAULT_YEAR_WORD, 'length' => mb_strlen(static::DEFAULT_YEAR_WORD, 'UTF-8')];
        }
        if ($wordLen > $yearRecord['length']) {
            Cache::set(static::WORD_LEN_YEARLY . date('Y'), ['word' => $word, 'length' => $wordLen]);

            return [static::YEAR => true];
        }

        return [];
    }
}
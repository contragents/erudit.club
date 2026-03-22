<?php

class PrizesErudit
{
    protected const DAY_DISCOUNT = 0.49;
    protected const WEEK_DISCOUNT = 0.59;
    protected const MONTH_DISCOUNT = 0.69;
    protected const YEAR_DISCOUNT = 0.79;

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

    const DEFAULT_DAY_GAME_PRICE_RECORD = 350;
    const DEFAULT_WEEK_GAME_PRICE_RECORD = 360;
    const DEFAULT_MONTH_GAME_PRICE_RECORD = 370;
    const DEFAULT_YEAR_GAME_PRICE_RECORD = 380;

    const DEFAULT_DAY_WORD_PER_TURN = 5;
    const DEFAULT_WEEK_WORD_PER_TURN = 6;
    const DEFAULT_MONTH_WORD_PER_TURN = 7;
    const DEFAULT_YEAR_WORD_PER_TURN = 8;

    const DEFAULT_DAY_TVERD_NUM = 0;
    const DEFAULT_WEEK_TVERD_NUM = 1;
    const DEFAULT_MONTH_TVERD_NUM = 2;
    const DEFAULT_YEAR_TVERD_NUM = 3;
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

    const DEFAULT_RECORDS = [
        Record::TURN_PRICE => [
            Record::DAY_PERIOD => self::DEFAULT_DAY_TURN_PRICE,
            Record::WEEK_PERIOD => self::DEFAULT_WEEK_TURN_PRICE,
            Record::MONTH_PERIOD => self::DEFAULT_MONTH_TURN_PRICE,
            Record::YEAR_PERIOD => self::DEFAULT_YEAR_TURN_PRICE,
        ],
        Record::WORD_PER_TURN => [
            Record::DAY_PERIOD => self::DEFAULT_DAY_WORD_PER_TURN,
            Record::WEEK_PERIOD => self::DEFAULT_WEEK_WORD_PER_TURN,
            Record::MONTH_PERIOD => self::DEFAULT_MONTH_WORD_PER_TURN,
            Record::YEAR_PERIOD => self::DEFAULT_YEAR_WORD_PER_TURN,
        ],
        Record::TVERD_NUM => [
            Record::DAY_PERIOD => self::DEFAULT_DAY_TVERD_NUM,
            Record::WEEK_PERIOD => self::DEFAULT_WEEK_TVERD_NUM,
            Record::MONTH_PERIOD => self::DEFAULT_MONTH_TVERD_NUM,
            Record::YEAR_PERIOD => self::DEFAULT_YEAR_TVERD_NUM,
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
        Record::GAME_PRICE => [
            Record::DAY_PERIOD => self::DEFAULT_DAY_GAME_PRICE_RECORD,
            Record::WEEK_PERIOD => self::DEFAULT_WEEK_GAME_PRICE_RECORD,
            Record::MONTH_PERIOD => self::DEFAULT_MONTH_GAME_PRICE_RECORD,
            Record::YEAR_PERIOD => self::DEFAULT_YEAR_GAME_PRICE_RECORD,
        ],
        Record::GAMES_PLAYED => [
            Record::DAY_PERIOD => self::DEFAULT_DAY_GAMES,
            Record::WEEK_PERIOD => self::DEFAULT_WEEK_GAMES,
            Record::MONTH_PERIOD => self::DEFAULT_MONTH_GAMES,
            Record::YEAR_PERIOD => self::DEFAULT_YEAR_GAMES,
        ]
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

    public
    static function getRandomRecord(): ?Record
    {
        $allRecords = Record::getActiveO();

        if (empty($allRecords)) {
            return null;
        }

        shuffle($allRecords);
        $record = array_shift($allRecords);

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

        return true;
    }

    public
    static function checkGamesPlayedRecord(
        array $playerIds
    ): array {
        $playersRecords = [];

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

        foreach (static::PERIODS as $localizedPeriod => $period) {
            $periodRecord = Record::getRecord($type, $period, static::GAME_NAME, true);
            if (!$periodRecord) {
                $lastPeriodRecord = Record::getRecord($type, $period, static::GAME_NAME, false);
                $periodRecord = new Record();
                $periodRecord->_event_value = $lastPeriodRecord
                    ? $lastPeriodRecord->_event_value * static::PERIOD_DISCOUNTS[$period]
                    : static::DEFAULT_RECORDS[$type][$period];
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
}
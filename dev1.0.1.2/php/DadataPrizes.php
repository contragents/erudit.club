<?php

namespace Dadata;

use AchievesModel;

class Prizes
{
    private const DAY_DISCOUNT = 0.5;
    private const WEEK_DISCOUNT = 0.6;
    private const MONTH_DISCOUNT = 0.7;
    private const YEAR_DISCOUNT = 0.8;

    private const DAY_TTL = 60 * 60 * 24 * 2;
    private const WEEK_TTL = 60 * 60 * 24 * 7 * 2;
    private const MONTH_TTL = 60 * 60 * 24 * 30 * 2;
    private const YEAR_TTL = 60 * 60 * 24 * 365 * 2;

    private const PERIODS = [
        'день' => 'day',
        'неделю' => 'week',
        'месяц' => 'month',
        'год' => 'year'
    ];

    private const ALL_RECORDS = 'erudit_all_records';

    private const WORD_LEN_DAILY = 'erudit_word_len_daily_';
    private const WORD_LEN_WEEKLY = 'erudit_word_len_weekly_';
    private const WORD_LEN_MONTHLY = 'erudit_word_len_monthly_';
    private const WORD_LEN_YEARLY = 'erudit_word_len_yearly_';

    private const WORD_PRICE_DAILY = 'erudit_word_price_daily_';
    private const WORD_PRICE_WEEKLY = 'erudit_word_price_weekly_';
    private const WORD_PRICE_MONTHLY = 'erudit_word_price_monthly_';
    private const WORD_PRICE_YEARLY = 'erudit_word_price_yearly_';

    private const TURN_PRICE_DAILY = 'erudit_turn_price_daily_';
    private const TURN_PRICE_WEEKLY = 'erudit_turn_price_weekly_';
    private const TURN_PRICE_MONTHLY = 'erudit_turn_price_monthly_';
    private const TURN_PRICE_YEARLY = 'erudit_turn_price_yearly_';

    private const GAME_PRICE_DAILY = 'erudit_game_price_daily_';
    private const GAME_PRICE_WEEKLY = 'erudit_game_price_weekly_';
    private const GAME_PRICE_MONTHLY = 'erudit_game_price_monthly_';
    private const GAME_PRICE_YEARLY = 'erudit_game_price_yearly_';

    private const GAMES_PLAYED_DAILY = 'erudit_games_played_daily_';
    private const GAMES_PLAYED_WEEKLY = 'erudit_games_played_weekly_';
    private const GAMES_PLAYED_MONTHLY = 'erudit_games_played_monthly_';
    private const GAMES_PLAYED_YEARLY = 'erudit_games_played_yearly_';

    public static function playerCurrentRecords($cookie = false, $playerName = false)
    {
        $cookie = $cookie ?: $_COOKIE['erudit_user_session_ID'];
        $allRecords = Cache::hgetall(self::ALL_RECORDS);
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
        $allRecords = Cache::hgetall(self::ALL_RECORDS);

        foreach ($allRecords as $type => $record) {
            $record = unserialize($record);
            $record = array_merge(
                $record,
                [
                    'link' => AchievesModel::PRIZE_LINKS[$type],
                    'type' => $type,
                    'common_id' => Players::getCommonIDByCookie($record['cookie'])
                ]
            );
            if ((rand(1, count($allRecords)) / count($allRecords)) <= 0.2) {
                break;
            }
        }

        return $record;
    }


    private
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

    private
    static function saveHistory(
        $eventType,
        $eventPeriod,
        array $arr
    ) {
        $commonID = Players::getPlayerID($arr['cookie'], true);
        if (!$commonID) {
            return false;
        }

        if (
        DB::queryInsert(
            "INSERT INTO achieves
            SET
            common_id = $commonID,
            date_achieved = '" . date('Y-m-d H:i:s', $arr['record_date']) . "',
            event_type = '$eventType',
            event_period = '$eventPeriod',
            word = '" . ($arr['word'] ? $arr['word'] : '') . "',
            event_value = {$arr['value']}
            "
        )
        ) {
            return true;
        }

        return false;
    }

    private
    static function saveAchieve(
        $cookie,
        $eventType,
        $eventPeriod,
        $eventValue,
        $word = false
    ) {
        $lastRecord = Cache::hget(self::ALL_RECORDS, $eventType . '-' . $eventPeriod);
        if ($lastRecord) {
            self::saveHistory($eventType, $eventPeriod, $lastRecord);
        }

        Cache::hset(
            self::ALL_RECORDS,
            $eventType . '-' . $eventPeriod,
            [
                'cookie' => $cookie ? $cookie : $_COOKIE['erudit_user_session_ID'],
                'value' => $eventValue,
                'word' => $word,
                'record_date' => date('U'),
            ]
        );

        return true;
    }

    public
    static function checkDayGamesPlayedRecord(
        array $players
    ) {
        $todayRecord = Cache::get(self::GAMES_PLAYED_DAILY . strtotime('today'));

        if (!$todayRecord) {
            $yesterdayRecord = Cache::get(self::GAMES_PLAYED_DAILY . strtotime('-1 day'));
            $todayRecord = $yesterdayRecord
                ? ['number' => $yesterdayRecord['number'] * self::DAY_DISCOUNT]
                : ['number' => 2];
        }

        $weekRecord = Cache::get(self::GAMES_PLAYED_WEEKLY . date('W'));

        if (!$weekRecord) {
            $lastWeekRecord = Cache::get(self::GAMES_PLAYED_WEEKLY . (date('W') - 1));
            $weekRecord = $lastWeekRecord
                ? ['number' => $lastWeekRecord['number'] * self::WEEK_DISCOUNT]
                : ['number' => 20];
        }

        $monthRecord = Cache::get(self::GAMES_PLAYED_MONTHLY . date('n'));

        if (!$monthRecord) {
            $lastMonthRecord = Cache::get(self::GAMES_PLAYED_MONTHLY . (date('n') - 1));
            $monthRecord = $lastMonthRecord
                ? ['number' => $lastMonthRecord['number'] * self::MONTH_DISCOUNT]
                : ['number' => 70];
        }


        $yearRecord = Cache::get(self::GAMES_PLAYED_YEARLY . date('Y'));

        if (!$yearRecord) {
            $lastYearRecord = Cache::get(self::GAMES_PLAYED_YEARLY . (date('Y') - 1));
            $yearRecord = $lastYearRecord
                ? ['number' => $lastYearRecord['number'] * self::YEAR_DISCOUNT]
                : ['number' => 500];
        }

        $playersRecords = [];

        foreach ($players as $cookie) {
            $playerDailyPlayedGames = Cache::incr(self::GAMES_PLAYED_DAILY . $cookie . strtotime('today'));
            Cache::setex(
                self::GAMES_PLAYED_DAILY . $cookie . strtotime('today'),
                self::DAY_TTL,
                $playerDailyPlayedGames
            );
            //Просто пересохранили в кеше с ТТЛ

            if ($playerDailyPlayedGames > $todayRecord['number']) {
                Cache::setex(
                    self::GAMES_PLAYED_DAILY . strtotime('today'),
                    self::DAY_TTL,
                    ['number' => $playerDailyPlayedGames]
                );
                self::saveAchieve($cookie, 'games_played', self::PERIODS['день'], $playerDailyPlayedGames, false);
                $todayRecord['number'] = $playerDailyPlayedGames;
                $playersRecords['день'] = [$cookie => $playerDailyPlayedGames];
            }

            if ($playerDailyPlayedGames > 1 || Cache::get(self::GAMES_PLAYED_WEEKLY . $cookie . date('W'))) {
                $playerWeeklyPlayedGames = Cache::incr(self::GAMES_PLAYED_WEEKLY . $cookie . date('W'));
                Cache::setex(self::GAMES_PLAYED_WEEKLY . $cookie . date('W'), self::WEEK_TTL, $playerWeeklyPlayedGames);
                //Просто пересохранили в кеше с ТТЛ

                if ($playerWeeklyPlayedGames > $weekRecord['number']) {
                    Cache::setex(
                        self::GAMES_PLAYED_WEEKLY . date('W'),
                        self::WEEK_TTL,
                        ['number' => $playerWeeklyPlayedGames]
                    );
                    self::saveAchieve(
                        $cookie,
                        'games_played',
                        self::PERIODS['неделю'],
                        $playerWeeklyPlayedGames,
                        false
                    );
                    $weekRecord['number'] = $playerWeeklyPlayedGames;
                    $playersRecords['неделю'] = [$cookie => $playerWeeklyPlayedGames];
                }
            }

            if ($playerDailyPlayedGames > 3 || Cache::get(self::GAMES_PLAYED_MONTHLY . $cookie . date('n'))) {
                $playerMonthlyPlayedGames = Cache::incr(self::GAMES_PLAYED_MONTHLY . $cookie . date('n'));
                Cache::setex(
                    self::GAMES_PLAYED_MONTHLY . $cookie . date('n'),
                    self::MONTH_TTL,
                    $playerMonthlyPlayedGames
                );
                //Просто пересохранили в кеше с ТТЛ

                if ($playerMonthlyPlayedGames > $monthRecord['number']) {
                    Cache::setex(
                        self::GAMES_PLAYED_MONTHLY . date('n'),
                        self::MONTH_TTL,
                        ['number' => $playerMonthlyPlayedGames]
                    );
                    self::saveAchieve(
                        $cookie,
                        'games_played',
                        self::PERIODS['месяц'],
                        $playerMonthlyPlayedGames,
                        false
                    );
                    $monthRecord['number'] = $playerMonthlyPlayedGames;
                    $playersRecords['месяц'] = [$cookie => $playerMonthlyPlayedGames];
                }
            }

            if ($playerDailyPlayedGames > 10 || Cache::get(self::GAMES_PLAYED_YEARLY . $cookie . date('Y'))) {
                $playerYearlyPlayedGames = Cache::incr(self::GAMES_PLAYED_YEARLY . $cookie . date('Y'));
                Cache::setex(self::GAMES_PLAYED_YEARLY . $cookie . date('Y'), self::YEAR_TTL, $playerYearlyPlayedGames);
                //Просто пересохранили в кеше с ТТЛ

                if ($playerYearlyPlayedGames > $yearRecord['number']) {
                    Cache::setex(
                        self::GAMES_PLAYED_YEARLY . date('Y'),
                        self::YEAR_TTL,
                        ['number' => $playerYearlyPlayedGames]
                    );
                    self::saveAchieve($cookie, 'games_played', self::PERIODS['год'], $playerYearlyPlayedGames, false);
                    $yearRecord['number'] = $playerYearlyPlayedGames;
                    $playersRecords['год'] = [$cookie => $playerYearlyPlayedGames];
                }
            }
        }

        return $playersRecords;
    }

    public
    static function checkDayGamePriceRecord(
        $price,
        $cookie = false
    ) {
        $todayRecord = Cache::get(self::GAME_PRICE_DAILY . strtotime('today'));

        if (!$todayRecord) {
            $yesterdayRecord = Cache::get(self::GAME_PRICE_DAILY . strtotime('-1 day'));
            $todayRecord = $yesterdayRecord
                ? ['price' => $yesterdayRecord['price'] * self::DAY_DISCOUNT]
                : ['price' => 10];
        }

        if ($price > $todayRecord['price']) {
            Cache::setex(self::GAME_PRICE_DAILY . strtotime('today'), self::DAY_TTL, ['price' => $price]);

            $res = array_merge(['день' => true], self::checkWeekGamePriceRecord($price));
            foreach ($res as $period => $value) {
                self::saveAchieve($cookie, 'game_price', self::PERIODS[$period], $price, false);
            }

            return $res;
        }

        return [];
    }

    public
    static function checkWeekGamePriceRecord(
        $price
    ) {
        $weekRecord = Cache::get(self::GAME_PRICE_WEEKLY . date('W'));

        if (!$weekRecord) {
            $preWeekRecord = Cache::get(self::GAME_PRICE_WEEKLY . (date('W') - 1));
            $weekRecord = $preWeekRecord
                ? ['price' => $preWeekRecord['price'] * self::WEEK_DISCOUNT]
                : ['price' => 10];
        }

        if ($price > $weekRecord['price']) {
            Cache::setex(self::GAME_PRICE_WEEKLY . date('W'), self::WEEK_TTL, ['price' => $price]);

            return array_merge(['неделю' => true], self::checkMonthGamePriceRecord($price));
        }

        return [];
    }

    public
    static function checkMonthGamePriceRecord(
        $price
    ) {
        $monthRecord = Cache::get(self::GAME_PRICE_MONTHLY . date('n'));

        if (!$monthRecord) {
            $preMonthRecord = Cache::get(self::GAME_PRICE_MONTHLY . (date('n') - 1));
            $monthRecord = $preMonthRecord
                ? ['price' => $preMonthRecord['price'] * self::MONTH_DISCOUNT]
                : ['price' => 10];
        }

        if ($price > $monthRecord['price']) {
            Cache::setex(self::GAME_PRICE_MONTHLY . date('n'), self::MONTH_TTL, ['price' => $price]);

            return array_merge(['месяц' => true], self::checkYearGamePriceRecord($price));
        }

        return [];
    }

    public
    static function checkYearGamePriceRecord(
        $price
    ) {
        $yearRecord = Cache::get(self::GAME_PRICE_YEARLY . date('Y'));

        if (!$yearRecord) {
            $preYearRecord = Cache::get(self::GAME_PRICE_YEARLY . (date('Y') - 1));
            $yearRecord = $preYearRecord
                ? ['price' => $preYearRecord['price'] * self::YEAR_DISCOUNT]
                : ['price' => 10];
        }

        if ($price > $yearRecord['price']) {
            Cache::setex(self::GAME_PRICE_YEARLY . date('Y'), self::YEAR_TTL, ['price' => $price]);

            return ['год' => true];
        }

        return [];
    }

    public
    static function checkDayTurnPriceRecord(
        $price,
        $cookie = false
    ) {
        $todayRecord = Cache::get(self::TURN_PRICE_DAILY . strtotime('today'));

        if (!$todayRecord) {
            $yesterdayRecord = Cache::get(self::TURN_PRICE_DAILY . strtotime('-1 day'));
            $todayRecord = $yesterdayRecord
                ? ['price' => $yesterdayRecord['price'] * self::DAY_DISCOUNT]
                : ['price' => 10];
        }

        if ($price > $todayRecord['price']) {
            Cache::setex(self::TURN_PRICE_DAILY . strtotime('today'), self::DAY_TTL, ['price' => $price]);

            $res = array_merge(['день' => true], self::checkWeekTurnPriceRecord($price));
            foreach ($res as $period => $value) {
                self::saveAchieve($cookie, 'turn_price', self::PERIODS[$period], $price, false);
            }

            return $res;
        }

        return [];
    }

    public
    static function checkWeekTurnPriceRecord(
        $price
    ) {
        $weekRecord = Cache::get(self::TURN_PRICE_WEEKLY . date('W'));

        if (!$weekRecord) {
            $preWeekRecord = Cache::get(self::TURN_PRICE_WEEKLY . (date('W') - 1));
            $weekRecord = $preWeekRecord
                ? ['price' => $preWeekRecord['price'] * self::WEEK_DISCOUNT]
                : ['price' => 10];
        }

        if ($price > $weekRecord['price']) {
            Cache::setex(self::TURN_PRICE_WEEKLY . date('W'), self::WEEK_TTL, ['price' => $price]);

            return array_merge(['неделю' => true], self::checkMonthTurnPriceRecord($price));
        }

        return [];
    }

    public
    static function checkMonthTurnPriceRecord(
        $price
    ) {
        $monthRecord = Cache::get(self::TURN_PRICE_MONTHLY . date('n'));

        if (!$monthRecord) {
            $preMonthRecord = Cache::get(self::TURN_PRICE_MONTHLY . (date('n') - 1));
            $monthRecord = $preMonthRecord
                ? ['price' => $preMonthRecord['price'] * self::MONTH_DISCOUNT]
                : ['price' => 10];
        }

        if ($price > $monthRecord['price']) {
            Cache::setex(self::TURN_PRICE_MONTHLY . date('n'), self::MONTH_TTL, ['price' => $price]);

            return array_merge(['месяц' => true], self::checkYearTurnPriceRecord($price));
        }

        return [];
    }

    public
    static function checkYearTurnPriceRecord(
        $price
    ) {
        $yearRecord = Cache::get(self::TURN_PRICE_YEARLY . date('Y'));

        if (!$yearRecord) {
            $preYearRecord = Cache::get(self::TURN_PRICE_YEARLY . (date('Y') - 1));
            $yearRecord = $preYearRecord
                ? ['price' => $preYearRecord['price'] * self::YEAR_DISCOUNT]
                : ['price' => 10];
        }

        if ($price > $yearRecord['price']) {
            Cache::setex(self::TURN_PRICE_YEARLY . date('Y'), self::YEAR_TTL, ['price' => $price]);

            return ['год' => true];
        }

        return [];
    }

    public
    static function checkDayWordPriceRecord(
        $word,
        $price,
        $cookie = false
    ) {
        $todayRecord = Cache::get(self::WORD_PRICE_DAILY . strtotime('today'));

        if (!$todayRecord) {
            $yesterdayRecord = Cache::get(self::WORD_PRICE_DAILY . strtotime('-1 day'));
            $todayRecord = $yesterdayRecord
                ? ['word' => $yesterdayRecord['word'], 'price' => $yesterdayRecord['price'] * self::DAY_DISCOUNT]
                : ['word' => 'эра', 'price' => 10];
        }

        if ($price > $todayRecord['price']) {
            Cache::setex(
                self::WORD_PRICE_DAILY . strtotime('today'),
                self::DAY_TTL,
                ['word' => $word, 'price' => $price]
            );

            $res = array_merge(['день' => true], self::checkWeekWordPriceRecord($word, $price));
            foreach ($res as $period => $value) {
                self::saveAchieve($cookie, 'word_price', self::PERIODS[$period], $price, $word);
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
        $weekRecord = Cache::get(self::WORD_PRICE_WEEKLY . date('W'));

        if (!$weekRecord) {
            $preWeekRecord = Cache::get(self::WORD_PRICE_WEEKLY . (date('W') - 1));
            $weekRecord = $preWeekRecord
                ? ['word' => $preWeekRecord['word'], 'price' => $preWeekRecord['price'] * self::WEEK_DISCOUNT]
                : ['word' => 'эра', 'price' => 10];
        }

        if ($price > $weekRecord['price']) {
            Cache::setex(self::WORD_PRICE_WEEKLY . date('W'), self::WEEK_TTL, ['word' => $word, 'price' => $price]);

            return array_merge(['неделю' => true], self::checkMonthWordPriceRecord($word, $price));
        }

        return [];
    }

    public
    static function checkMonthWordPriceRecord(
        $word,
        $price
    ) {
        $monthRecord = Cache::get(self::WORD_PRICE_MONTHLY . date('n'));

        if (!$monthRecord) {
            $preMonthRecord = Cache::get(self::WORD_PRICE_MONTHLY . (date('n') - 1));
            $monthRecord = $preMonthRecord
                ? ['word' => $preMonthRecord['word'], 'price' => $preMonthRecord['price'] * self::MONTH_DISCOUNT]
                : ['word' => 'ерш', 'price' => 10];
        }

        if ($price > $monthRecord['price']) {
            Cache::setex(self::WORD_PRICE_MONTHLY . date('n'), self::MONTH_TTL, ['word' => $word, 'price' => $price]);

            return array_merge(['месяц' => true], self::checkYearWordPriceRecord($word, $price));
        }

        return [];
    }

    public
    static function checkYearWordPriceRecord(
        $word,
        $price
    ) {
        $yearRecord = Cache::get(self::WORD_PRICE_YEARLY . date('Y'));

        if (!$yearRecord) {
            $preYearRecord = Cache::get(self::WORD_PRICE_YEARLY . (date('Y') - 1));
            $yearRecord = $preYearRecord
                ? ['word' => $preYearRecord['word'], 'price' => $preYearRecord['price'] * self::YEAR_DISCOUNT]
                : ['word' => 'ерш', 'price' => 10];
        }

        if ($price > $yearRecord['price']) {
            Cache::setex(self::WORD_PRICE_YEARLY . date('Y'), self::YEAR_TTL, ['word' => $word, 'price' => $price]);

            return ['год' => true];
        }

        return [];
    }


    public
    static function checkDayWordLenRecord(
        $word,
        $cookie = false
    ) {
        $wordLen = mb_strlen($word, 'UTF-8');
        $todayRecord = Cache::get(self::WORD_LEN_DAILY . strtotime('today'));
        if (!$todayRecord) {
            $yesterdayRecord = Cache::get(self::WORD_LEN_DAILY . strtotime('-1 day'));
            $todayRecord = $yesterdayRecord
                ? ['word' => $yesterdayRecord['word'], 'length' => $yesterdayRecord['length'] * self::DAY_DISCOUNT]
                : ['word' => 'эра', 'length' => 3];
        }

        if ($wordLen > $todayRecord['length']) {
            Cache::setex(
                self::WORD_LEN_DAILY . strtotime('today'),
                self::DAY_TTL,
                ['word' => $word, 'length' => $wordLen]
            );

            $res = array_merge(['день' => true], self::checkWeekWordLenRecord($word));
            foreach ($res as $period => $value) {
                self::saveAchieve($cookie, 'word_len', self::PERIODS[$period], $wordLen, $word);
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
        $weekRecord = Cache::get(self::WORD_LEN_WEEKLY . date('W'));
        if (!$weekRecord) {
            $preWeekRecord = Cache::get(self::WORD_LEN_WEEKLY . (date('W') - 1));
            $weekRecord = $preWeekRecord
                ? ['word' => $preWeekRecord['word'], 'length' => $preWeekRecord['word'] * self::WEEK_DISCOUNT]
                : ['word' => 'эра', 'length' => 3];
        }

        if ($wordLen > $weekRecord['length']) {
            Cache::setex(self::WORD_LEN_WEEKLY . date('W'), self::WEEK_TTL, ['word' => $word, 'length' => $wordLen]);

            return array_merge(['неделю' => true], self::checkMonthWordLenRecord($word));
        }

        return [];
    }

    public
    static function checkMonthWordLenRecord(
        $word
    ) {
        $wordLen = mb_strlen($word, 'UTF-8');
        $monthRecord = Cache::get(self::WORD_LEN_MONTHLY . date('n'));
        if (!$monthRecord) {
            $preMonthRecord = Cache::get(self::WORD_LEN_MONTHLY . (date('n') - 1));
            $monthRecord = $preMonthRecord
                ? ['word' => $preMonthRecord['word'], 'length' => $preMonthRecord['length'] * self::MONTH_DISCOUNT]
                : ['word' => 'ерш', 'length' => 3];
        }

        if ($wordLen > $monthRecord['length']) {
            Cache::setex(self::WORD_LEN_MONTHLY . date('n'), self::MONTH_TTL, ['word' => $word, 'length' => $wordLen]);

            return array_merge(['месяц' => true], self::checkYearWordLenRecord($word));
        }

        return [];
    }

    public
    static function checkYearWordLenRecord(
        $word
    ) {
        $wordLen = mb_strlen($word, 'UTF-8');
        $yearRecord = Cache::get(self::WORD_LEN_YEARLY . date('Y'));
        if (!$yearRecord) {
            $preYearRecord = Cache::get(self::WORD_LEN_YEARLY . (date('Y') - 1));
            $yearRecord = $preYearRecord
                ? ['word' => $preYearRecord['word'], 'length' => $preYearRecord['length'] * self::YEAR_DISCOUNT]
                : ['word' => 'юра', 'length' => 3];
        }
        if ($wordLen > $yearRecord['length']) {
            Cache::setex(self::WORD_LEN_YEARLY . date('Y'), self::YEAR_TTL, ['word' => $word, 'length' => $wordLen]);

            return ['год' => true];
        }

        return [];
    }
}
<?php
namespace Dadata;
ini_set("display_errors", 1); error_reporting(E_ALL);

class Prizes
{
    private const DAY_DISCOUNT = 0.5;
    private const WEEK_DISCOUNT = 0.6;
    
    private const DAY_TTL = 60 * 60 * 24 * 2;
    private const WEEK_TTL = 60 * 60 * 24 * 2 * 7;
    
    private const WORD_LEN_DAILY = 'erudit_word_len_daily_';
    private const WORD_LEN_WEEKLY = 'erudit_word_len_weekly_';

    public static function checkDayWordLenRecord($word)
    {
        $wordLen = mb_strlen($word, 'UTF-8');
        $todayRecord = unserialize(Cache::get(self::WORD_LEN_DAILY . strtotime('today')));
        if ($todayRecord) {
            if ($wordLen > $todayRecord['length']) {
                Cache::setex(self::WORD_LEN_DAILY . strtotime('today'), self::DAY_TTL, serialize(['word' => $word, 'length' => $wordLen]));
                
                return array_merge(['день' => true], self::checkWeekWordLenRecord($word));
            }
        } else {
            $yesterdayRecord = unserialize(Cache::get(self::WORD_LEN_DAILY . strtotime('-1 day')));
            $yesterdayRecord = $yesterdayRecord ? $yesterdayRecord : ['word' => 'эра','length'=>3];

            if ($wordLen > $yesterdayRecord['length'] * self::DAY_DISCOUNT) {
                Cache::setex(self::WORD_LEN_DAILY . strtotime('today'), self::DAY_TTL, serialize(['word' => $word, 'length' => $wordLen]));
                
                return array_merge(['день' => true], self::checkWeekWordLenRecord($word));
            }
        }

        return [];
    }
    
    public static function checkWeekWordLenRecord($word)
    {
        $wordLen = mb_strlen($word, 'UTF-8');
        $weekRecord = unserialize(Cache::get(self::WORD_LEN_WEEKLY . date('W')));
        if ($weekRecord) {
            if ($wordLen > $weekRecord['length']) {
                Cache::setex(self::WORD_LEN_WEEKLY . date('W'), self::WEEK_TTL, serialize(['word' => $word, 'length' => $wordLen]));
                
                return ['неделю' => true];
            }
        } else {
            $preWeekRecord = unserialize(Cache::get(self::WORD_LEN_WEEKLY . (date('W') - 1)));
            $preWeekRecord = $preWeekRecord ? $preWeekRecord : ['word' => 'эра','length'=>3];

            if ($wordLen > $preWeekRecord['length'] * self::WEEK_DISCOUNT) {
                Cache::setex(self::WORD_LEN_WEEKLY . date('W'), self::WEEK_TTL, serialize(['word' => $word, 'length' => $wordLen]));
                
                return ['неделю' => true];
            }
        }

        return [];
    }
}
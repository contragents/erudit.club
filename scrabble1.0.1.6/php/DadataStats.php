<?php

namespace Dadata;

use \Cache;
use Cookie;

class Stats
{
    const TTLS = [
        'cookie' => 3 * 60,
        'daily' => 7 * 24 * 60 * 60,
        'weekly' => 4 * 7 * 24 * 60 * 60,
        'monthly' => 12 * 4 * 7 * 24 * 60 * 60,
        'yearly' => 5 * 12 * 4 * 7 * 24 * 60 * 60,
    ];

    const DATES = [
        'daily' => 'Y.m.d',
        'weekly' => 'W',
        'monthly' => 'n',
        'yearly' => 'Y',
    ];

    const COOKIE_KEY = 'erudit.stats_cookie_';
    const REFERERS_HKEY = 'erudit.stats_referers';

    const STAT_PREFIXES = [
        'daily' => 'erudit.stats_daily_',
        'weekly' => 'erudit.stats_weekly_',
        'monthly' => 'erudit.stats_monthly_',
        'yearly' => 'erudit.stats_yearly_',
    ];

    const DEVICES = [
        'desktop' => 'isDesktopDevice',
        'mobile' => 'isMobileDevice',
    ];

    const APPS = [
        'club' => 'isClubApp',
        'vk' => 'isVkApp',
        'android' => 'isAndroidApp',
        'yandex' => 'isYandexApp',
    ];

    public static function saveStats()
    {
        if (strpos($_COOKIE[Cookie::COOKIE_NAME], 'bot') !== false) {
            return;
        }

        // Учитываем статистику только 1 раз для куки (1 раз за игру)
        if (self::incStat(self::COOKIE_KEY . $_COOKIE[Cookie::COOKIE_NAME], self::TTLS['cookie']) > 1) {
            return;
        }

        self::saveReferer();

        foreach (self::STAT_PREFIXES as $period => $prefix) {
            $prefix .= date(self::DATES[$period] . '_');
            self::incStat(trim($prefix, '_'), self::TTLS[$period]);

            foreach (self::DEVICES as $device => $fnc) {
                if (Hints::{$fnc}()) {
                    self::incStat($prefix . $device, self::TTLS[$period]);
                }
            }

            foreach (self::APPS as $app => $fnc) {
                if (Hints::{$fnc}()) {
                    self::incStat($prefix . $app, self::TTLS[$period]);
                }
            }
        }
    }

    private static function saveReferer()
    {
        $ref = $_SERVER['HTTP_REFERER'] ?? '';

        if (!$ref) {
            return;
        }

        $host = parse_url($ref, PHP_URL_HOST);

        if (strlen($host) > 4) {
            Cache::hincrby(self::REFERERS_HKEY, $host, 1);
        }
    }

    private static function incStat($key, $ttl)
    {
        $cachedValue = Cache::incr($key);
        if ($cachedValue == 1) {
            Cache::setex($key, $ttl, 1);
        }

        return $cachedValue;
    }

}
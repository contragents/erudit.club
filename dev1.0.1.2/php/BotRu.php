<?php

use Lang\Ru;

class BotRu extends BotEng
{
    public static $langClass = Ru::class;
    public static $lang = self::RU_LANG;

    const BOT_GAMES = 'erudit.bot_games';

    public static function zvezdaRegexp($numZvezd)
    {
        if ($numZvezd <= 1) {
            return 'эюшщфъчй';
        } else {
            return 'аимое';
        }
    }
}

<?php

use Lang\Ru;

class BotRu extends BotEng
{
    const BOTSNUM = 47;
    public static $langClass = Ru::class;
    public static $lang = 'RU';

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

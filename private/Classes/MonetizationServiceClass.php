<?php

class MonetizationService
{
    const DIVIDER = 2;

    const REWARD = [
        AchievesModel::YEAR_PERIOD => 1000 / self::DIVIDER,
        AchievesModel::MONTH_PERIOD => 250 / self::DIVIDER,
        AchievesModel::WEEK_PERIOD => 50 / self::DIVIDER,
        AchievesModel::DAY_PERIOD => 10 / self::DIVIDER,
    ];
    const INCOME = [
        AchievesModel::YEAR_PERIOD => 10 / self::DIVIDER,
        AchievesModel::MONTH_PERIOD => 5 / self::DIVIDER,
        AchievesModel::WEEK_PERIOD => 2 / self::DIVIDER,
        AchievesModel::DAY_PERIOD => 1 / self::DIVIDER,
    ];
}

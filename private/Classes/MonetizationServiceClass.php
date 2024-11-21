<?php

class MonetizationService
{
    const BIDS = [
        1,
        5,
        10,
        25,
        50,
        100,
        250,
        500,
        1000,
        2500,
        5000,
        10000,
        25000,
        50000,
        100000,
        250000,
        500000,
        1000000
    ];

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

    const SUDOKU_PRICE = 10;
}

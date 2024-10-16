<?php


class Macros
{
    const PATTERN = '{{';

    public const MACROSES = [
        'goldReward' => '{{gold_reward}}',
        'silverReward' => '{{silver_reward}}',
        'bronzeReward' => '{{bronze_reward}}',
        'stoneReward' => '{{stone_reward}}',
        'goldIncome' => '{{gold_income}}',
        'silverIncome' => '{{silver_income}}',
        'bronzeIncome' => '{{bronze_income}}',
        'stoneIncome' => '{{stone_income}}',
        'sudokuIcon' => '{{sudoku_icon}}',
    ];

    const SUDOKU_IMG_URL = '<img src="/images/coin.png" alt="SUDOKU coin image" width="30%">';

    public static function sudokuIcon(): string {
        return self::SUDOKU_IMG_URL;
    }

    public static function goldReward(): string {
        return MonetizationService::REWARD[AchievesModel::YEAR_PERIOD];
    }

    public static function silverReward(): string {
        return MonetizationService::REWARD[AchievesModel::MONTH_PERIOD];
    }

    public static function bronzeReward(): string {
        return MonetizationService::REWARD[AchievesModel::WEEK_PERIOD];
    }

    public static function stoneReward(): string {
        return MonetizationService::REWARD[AchievesModel::DAY_PERIOD];
    }

    public static function goldIncome(): string {
        return MonetizationService::INCOME[AchievesModel::YEAR_PERIOD];
    }

    public static function silverIncome(): string {
        return MonetizationService::INCOME[AchievesModel::MONTH_PERIOD];
    }

    public static function bronzeIncome(): string {
        return MonetizationService::INCOME[AchievesModel::WEEK_PERIOD];
    }

    public static function stoneIncome(): string {
        return MonetizationService::INCOME[AchievesModel::DAY_PERIOD];
    }

    public static function applyMacros($res): string
    {
        foreach (self::MACROSES as $method => $macros) {
            $res = str_replace($macros, call_user_func([Macros::class, $method]), $res);
        }

        return $res;
    }
}
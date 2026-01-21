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
        'sudokuIcon5' => '{{sudoku_icon_5}}',
        'sudokuIcon15' => '{{sudoku_icon_15}}',
        'sudokuIcon20' => '{{sudoku_icon_20}}',
        'yandexExclude' => ['macros' => '{{yandex_exclude}}', 'text_as_param' => true],
    ];

    const SUDOKU_IMG_URL = '<img src="images/coin.png" alt="SUDOKU coin image" width="30%">';

    public static function sudokuIcon(): string
    {
        return self::SUDOKU_IMG_URL;
    }

    public static function sudokuIcon5(): string
    {
        return str_replace('30%', '5%', self::SUDOKU_IMG_URL);
    }

    public static function sudokuIcon15(): string
    {
        return str_replace('30%', '15%', self::SUDOKU_IMG_URL);
    }

    public static function sudokuIcon20(): string
    {
        return str_replace('30%', '20%', self::SUDOKU_IMG_URL);
    }

    public static function goldReward(): string
    {
        return MonetizationService::REWARD[AchievesModel::YEAR_PERIOD];
    }

    public static function silverReward(): string
    {
        return MonetizationService::REWARD[AchievesModel::MONTH_PERIOD];
    }

    public static function bronzeReward(): string
    {
        return MonetizationService::REWARD[AchievesModel::WEEK_PERIOD];
    }

    public static function stoneReward(): string
    {
        return MonetizationService::REWARD[AchievesModel::DAY_PERIOD];
    }

    public static function goldIncome(): string
    {
        return MonetizationService::INCOME[AchievesModel::YEAR_PERIOD];
    }

    public static function silverIncome(): string
    {
        return MonetizationService::INCOME[AchievesModel::MONTH_PERIOD];
    }

    public static function bronzeIncome(): string
    {
        return MonetizationService::INCOME[AchievesModel::WEEK_PERIOD];
    }

    public static function stoneIncome(): string
    {
        return MonetizationService::INCOME[AchievesModel::DAY_PERIOD];
    }

    public static function applyMacros(string $text): string
    {
        foreach (self::MACROSES as $method => $macros) {
            if (is_array($macros) && ($macros['text_as_param'] ?? false) && strpos($text, $macros['macros']) !== false) {
                // метод доллжен обрабатывать все свои макросы в переданной строке
                $text = call_user_func([Macros::class, $method], $text);

                continue;
            } elseif(!is_array($macros)) {
                $text = str_replace($macros, call_user_func([self::class, $method]), $text);
            }
        }

        return $text;
    }

    public static function yandexExclude(string $text): string
    {
        preg_match_all('/({{yandex_exclude}})({{[\s\S]+?}})/', $text, $matches); // ? - отмена жадности, поиск до первых }}

        foreach ($matches[1] as $num => $match) {
            $text = str_replace($match, '', $text);
            $text = str_replace(
                $matches[2][$num],
                BaseController::isYandexApp()
                    ? ''
                    : trim($matches[2][$num], '{}'),
                $text
            );
        }

        return $text;
    }
}
<?php

use Dadata\Cache;

class BadCombinations
{
    const BAD_COMBINATIONS_KEY = 'bad_combinations';

    public static function renderOne()
    {
        $it = null;
        while (true) {
            $records = Cache::hscan(self::BAD_COMBINATIONS_KEY, $it, '*', 1);
            if (!empty($records)) {
                foreach ($records as $key => $value) {
                    $value = unserialize($value);
                    Cache::hdel(self::BAD_COMBINATIONS_KEY, $key);
                    return "New fishki:\n" . print_r($value['new_fishki'], true)
                        . "Old cells:\n" . self::printr($value['old_cells'])
                        . "Old desk:\n" . self::printr($value['old_desk'])
                        . "New_desk:\n" . self::printr($value['new_desk'])
                        . "OLD saved words:\n" . print_r($value['saved_words'] ?? [], true)
                        . "NEW saved words:\n" . print_r($value['new_played_words'] ?? [], true);
                }
            }
        }
    }

    private static function printr(&$cells)
    {
        $result = '';

        for ($j = 0; $j <= 14; $j++) {
            for ($i = 0; $i <= 14; $i++) {
                if (($i == $j) && !$cells[$i][$j][0]) {
                    $result .= ($i % 10);
                } elseif ($cells[$i][$j][0]) {
                    $result .= \Lang\Ru::$bukvy[$cells[$i][$j][1] < 999 ? $cells[$i][$j][1] : $cells[$i][$j][1] - 999 - 1][0];
                } else {
                    $result .= '.';
                }
            }
            $result .= "\n";
        }

        return $result;
    }
}

include_once 'autoload.php';

print BadCombinations::renderOne();
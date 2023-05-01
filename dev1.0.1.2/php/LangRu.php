<?php

namespace Lang;

include_once(__DIR__ . '/DadataDB.php');

use \Dadata\DB;

class Ru
{

    public static $multi = [0 => [0 => ['slovo' => 3], 3 => ['bukva' => 2], 7 => ['slovo' => 3], 11 => ['bukva' => 2], 14 => ['slovo' => 3]],
        1 => [1 => ['slovo' => 2], 5 => ['bukva' => 3], 9 => ['bukva' => 3], 13 => ['slovo' => 2]],
        2 => [2 => ['slovo' => 2], 6 => ['bukva' => 2], 8 => ['bukva' => 2], 12 => ['slovo' => 2]],
        3 => [0 => ['bukva' => 2], 3 => ['slovo' => 2], 7 => ['bukva' => 2], 11 => ['slovo' => 2], 14 => ['bukva' => 2]],
        4 => [4 => ['slovo' => 2], 10 => ['slovo' => 2]],
        5 => [1 => ['bukva' => 3], 13 => ['bukva' => 3]],
        6 => [2 => ['bukva' => 2], 6 => ['bukva' => 2], 8 => ['bukva' => 2], 12 => ['bukva' => 2]],
        7 => [0 => ['slovo' => 3], 3 => ['bukva' => 2], 11 => ['bukva' => 2], 14 => ['slovo' => 3]],
        8 => [2 => ['bukva' => 2], 6 => ['bukva' => 2], 8 => ['bukva' => 2], 12 => ['bukva' => 2]],
        9 => [1 => ['bukva' => 3], 13 => ['bukva' => 3]],
        10 => [4 => ['slovo' => 2], 10 => ['slovo' => 2]],
        11 => [0 => ['bukva' => 2], 3 => ['slovo' => 2], 7 => ['bukva' => 2], 11 => ['slovo' => 2], 14 => ['bukva' => 2]],
        12 => [2 => ['slovo' => 2], 6 => ['bukva' => 2], 8 => ['bukva' => 2], 12 => ['slovo' => 2]],
        13 => [1 => ['slovo' => 2], 5 => ['bukva' => 3], 9 => ['bukva' => 3], 13 => ['slovo' => 2]],
        14 => [0 => ['slovo' => 3], 3 => ['bukva' => 2], 7 => ['slovo' => 3], 11 => ['bukva' => 2], 14 => ['slovo' => 3]]];

    public static $bukvy = [
        0 => ['а', 1, 8, 'glas'],
        1 => ['б', 3, 2, 'soglas'],
        2 => ['в', 1, 4, 'soglas'],
        3 => ['г', 3, 2, 'soglas'],
        4 => ['д', 2, 4, 'soglas'],
        5 => ['е', 1, 9, 'glas'],
        6 => ['ж', 5, 1, 'soglas'],
        7 => ['з', 5, 2, 'soglas'],
        8 => ['и', 1, 6, 'glas'],
        9 => ['й', 4, 1, 'soglas'],
        10 => ['к', 2, 4, 'soglas'],
        11 => ['л', 2, 4, 'soglas'],
        12 => ['м', 2, 3, 'soglas'],
        13 => ['н', 1, 5, 'soglas'],
        14 => ['о', 1, 10, 'glas'],
        15 => ['п', 2, 4, 'soglas'],
        16 => ['р', 1, 5, 'soglas'],
        17 => ['с', 1, 5, 'soglas'],
        18 => ['т', 1, 5, 'soglas'],
        19 => ['у', 2, 4, 'glas'],
        20 => ['ф', 8, 1, 'soglas'],
        21 => ['х', 5, 1, 'soglas'],
        22 => ['ц', 5, 1, 'soglas'],
        23 => ['ч', 5, 1, 'soglas'],
        24 => ['ш', 8, 1, 'soglas'],
        25 => ['щ', 10, 1, 'soglas'],
        26 => ['ъ', 15, 1, false],
        27 => ['ы', 4, 2, 'glas'],
        28 => ['ь', 3, 2, 'glas'],
        29 => ['э', 8, 1, 'glas'],
        30 => ['ю', 8, 1, 'glas'],
        31 => ['я', 3, 2, 'glas'],
        32 => ['ё', 1, 0, false],
        999 => ['*', 0, 3, false]];

    private static $words = [];
    private static $badWords = [];
    private static $goodWords = [];
    private static $goodWordsLinks = [];

    private static $dictTable = 'dict';

    public static function generateBankFishki()
    {
        $num_bukvy = 0;
        $bankFishki = [];
        foreach (static::$bukvy as $code => $buk)
            for ($i = 0; $i < $buk[2]; $i++)
                $bankFishki[] = $code;
        shuffle($bankFishki);
        return $bankFishki;
    }

    private static function deskZvezda($gameFishki, &$desk, &$cells)
    {
        $zvezdy = [];
        for ($i = 0; $i <= 14; $i++)
            for ($j = 0; $j <= 14; $j++)
                if ($desk[$i][$j][0])
                    if ($desk[$i][$j][1] > 999)
                        if ($cells[$i][$j][2] !== false)
                            $zvezdy[] = [$i, $j, $cells[$i][$j][2]];
        return $zvezdy;
    }

    private static function validateFishki($fishki, &$cells, $gameFishki, &$desk)
    {
        $zvezdyTemporary = static::deskZvezda($gameFishki, $desk, $cells);
        $numZvezd = 0;
        foreach ($fishki as $fishka)
            if ($fishka[2] > 999)
                $numZvezd++;

        foreach ($gameFishki as $fishka)
            if ($fishka == 999)
                $numZvezd--;

        if ((count($zvezdyTemporary) - $numZvezd) < 0)
            return [];

        return $zvezdyTemporary;
    }

    public static function submit(&$cells, &$desk, &$gameStatus)
    {
        self::$words = [];
        self::$badWords = [];
        self::$goodWords = [];
        self::$goodWordsLinks = [];

        if (!$desk) {
            $desk = self::init_desk();
        }

        $bad_fishki = [];

        $fishki = self::compare_desks($desk, $cells, $gameStatus);
        if ($fishki === FALSE)
            return FALSE;
        else
            $zvezdy = self::validateFishki($fishki, $cells, $gameStatus['users'][$gameStatus['activeUser']]['fishki'], $desk);

        // Проверяем слова на корректность и удаляем лишние фишки
        $num_fishki = count($fishki);
        for ($f = 0; $f < $num_fishki; $f++)
            foreach ($fishki as $num => $fishka)
                if (!self::fishka_correct($fishka, $cells, $desk, $gameStatus)) {
                    $bad_fishki[] = $fishki[$num];
                    unset($fishki[$num]);
                    $cells[$fishka[0]][$fishka[1]][0] = false;
                    $cells[$fishka[0]][$fishka[1]][1] = false;
                }

        //Проверяем связность оставшихся новых фишек со старыми
        $num_fishki = count($fishki);

        for ($f = 0; $f < $num_fishki; $f++)
            foreach ($fishki as $num => $fishka)
                if (self::fishka_connected($fishka, $cells, $desk)) {
                    $fishki[$num]['connected'] = TRUE;
                    $desk[$fishka[0]][$fishka[1]][0] = TRUE;
                }

        foreach ($fishki as $num => $fishka)
            if (!isset($fishka['connected'])) {
                $cells[$fishka[0]][$fishka[1]][0] = false;
                $cells[$fishka[0]][$fishka[1]][1] = false;
                $bad_fishki[] = $fishki[$num];
                unset($fishki[$num]);
            }

        foreach ($bad_fishki as $bad_fishka) {
            if (isset(self::$words[$bad_fishka[0] . '-' . $bad_fishka[1]]))
                unset(self::$words[$bad_fishka[0] . '-' . $bad_fishka[1]]);
            //Убрали слова на плохой фишке

            if (isset(self::$goodWordsLinks[$bad_fishka[0] . '-' . $bad_fishka[1]]['hor'])) {
                if (isset(self::$goodWords[self::$goodWordsLinks[$bad_fishka[0] . '-' . $bad_fishka[1]]['hor']]['hor']))
                    unset(self::$goodWords[self::$goodWordsLinks[$bad_fishka[0] . '-' . $bad_fishka[1]]['hor']]['hor']);
                unset(self::$goodWordsLinks[$bad_fishka[0] . '-' . $bad_fishka[1]]['hor']);
                //Убрали слова, которые проходят через плохую фищку по горизонтали
            }

            if (isset(self::$goodWordsLinks[$bad_fishka[0] . '-' . $bad_fishka[1]]['vert'])) {
                if (isset(self::$goodWords[self::$goodWordsLinks[$bad_fishka[0] . '-' . $bad_fishka[1]]['vert']]['vert']))
                    unset(self::$goodWords[self::$goodWordsLinks[$bad_fishka[0] . '-' . $bad_fishka[1]]['vert']]['vert']);
                unset(self::$goodWordsLinks[$bad_fishka[0] . '-' . $bad_fishka[1]]['vert']);
                //Убрали слова, которые проходят через плохую фищку по вертикали
            }
        }

        $good_words = [];
        foreach (self::$goodWords as $h_v => $h_v_word) {
            $ij = explode('-', $h_v);
            if (isset($h_v_word['hor']))
                if (!isset($good_words[$h_v_word['hor']]))
                    $good_words[$h_v_word['hor']] = self::wordPrice($h_v_word['hor'], $ij[0], $ij[1], 'hor');
                else {
                    $price = self::wordPrice($h_v_word['hor'], $ij[0], $ij[1], 'hor');
                    if ($good_words[$h_v_word['hor']] < $price)
                        $good_words[$h_v_word['hor']] = $price;
                }

            if (isset($h_v_word['vert']))
                if (!isset($good_words[$h_v_word['vert']]))
                    $good_words[$h_v_word['vert']] = self::wordPrice($h_v_word['vert'], $ij[0], $ij[1], 'vert');
                else {
                    $price = self::wordPrice($h_v_word['vert'], $ij[0], $ij[1], 'vert');
                    if ($good_words[$h_v_word['vert']] < $price)
                        $good_words[$h_v_word['vert']] = $price;
                }
            //Посчитали очки для всех хороших слов
        }

        foreach ($fishki as $nn => $fishka)
            if ($fishka[3] !== false) {
                foreach ($zvezdy ?: [] as $num => $zvezda)
                    if ($zvezda[2] === $fishka[3]) {
                        $cells[$zvezda[0]][$zvezda[1]][2] = false;
                        $cells[$zvezda[0]][$zvezda[1]][1] = $fishka[3];
                        unset($zvezda[$num]);
                    }
            }
        //Обнулили и освободили сыгравшую забратую фишку

        foreach ($bad_fishki as $nn => $bfishka)
            if ($bfishka[3] !== false)
                foreach ($zvezdy ?: [] as $num => $zvezda)
                    if ($zvezda[2] === $bfishka[3]) {
                        $cells[$zvezda[0]][$zvezda[1]][2] = false;
                        unset($zvezda[$num]);
                    }
        //ТОЛЬКО освободили НЕсыгравшую забратую фишку

        return ['bad' => $bad_fishki, 'good' => $fishki, 'words' => $good_words, 'badWords' => self::$badWords, 'goodWords' => self::$goodWords, 'goodWordsLinks' => self::$goodWordsLinks];
    }

    public static function getLetterCode($letter)
    {
        foreach (static::$bukvy as $num => $bukva)
            if ($bukva[0] == $letter)
                return $num;
            else
                if ($bukva[0] == mb_strtolower($letter))
                    return $num + 999 + 1; //Буква под звездочкой

    }

    private static function wordPrice($word, $i, $j, $orientation = 'hor')
    {
        //print $i.' - '.$j;
        $price = 0;
        $word_multi = [];
        $I = $i;
        $J = $j;

        for ($k = 0; $k < mb_strlen($word, 'UTF-8'); $k++) {

            if ($orientation == 'hor')
                $I = $i + $k;
            else
                $J = $j + $k;


            $letterPrice = static::$bukvy[self::getLetterCode(mb_substr($word, $k, 1, 'UTF-8'))][1];

            if (isset(self::$multi[$I][$J]))
                if (isset (self::$multi[$I][$J]['bukva']))
                    $letterPrice = $letterPrice * self::$multi[$I][$J]['bukva'];
                else
                    $word_multi[] = self::$multi[$I][$J]['slovo'];

            //print $I.' - '.$J.' - '.$letterPrice.'<br />';

            $price += $letterPrice;

        }

        foreach ($word_multi as $multi)
            $price = $price * $multi;

        return $price;
    }


    public static function init_desk()
    {
        $dsc = [];
        //$j - строки, $i - столбцы
        for ($j = 0; $j <= 14; $j++)
            for ($i = 0; $i <= 14; $i++) {
                $dsc[$i][$j][0] = false;
                $dsc[$i][$j][1] = false;
                $dsc[$i][$j][2] = false;
            }
        return $dsc;
    }

    private static function word_correct($word, array &$wordsAccepted)
    {
        if (isset($wordsAccepted[$word]))
            return 0;

        return DB::queryValue("SELECT count(1) as cnt FROM " . self::$dictTable . " WHERE slovo='$word' AND deleted = 0 LIMIT 1;");
    }

    private static function compare_desks(&$desk, &$cells, &$gameStatus)
    {
        $fshki = [];
        //$j - строки, $i - столбцы
        for ($j = 0; $j <= 14; $j++)
            for ($i = 0; $i <= 14; $i++) {
                if ($desk[$i][$j][0] && ($cells[$i][$j][1] != $desk[$i][$j][1]))
                    return FALSE;
                elseif ($cells[$i][$j][0] != $desk[$i][$j][0])
                    $fshki[] = [$i, $j, $cells[$i][$j][1], $cells[$i][$j][2]];
            }

        if (count($fshki) > count($gameStatus['users'][$gameStatus['activeUser']]['fishki'])) {
            return false;
        }

        return $fshki;
    }

    private static function fishka_connected(&$fishka, &$cells, &$desk)
    {
        if ($fishka['connected'] ?? false)
                return TRUE;

        if (($fishka[0] == 7) && ($fishka[1] == 7))
            return TRUE;

        //Идем влево от буквы
        //$i - столбец
        $i = $fishka[0] - 1;
        if ($i >= 0)
            while ($cells[$i][$fishka[1]][0])
                if ($desk[$i][$fishka[1]][0])
                    return TRUE;
                else {
                    $i--;
                    if ($i < 0)
                        break;
                }

        //Идем вправо от буквы
        $i = $fishka[0] + 1;
        if ($i <= 14)
            while ($cells[$i][$fishka[1]][0])
                if ($desk[$i][$fishka[1]][0])
                    return TRUE;
                else {
                    $i++;
                    if ($i > 14)
                        break;
                }

        //Идем вверх от буквы
        //$j - строка
        $j = $fishka[1] - 1;
        if ($j >= 0)
            while ($cells[$fishka[0]][$j][0])
                if ($desk[$fishka[0]][$j][0])
                    return TRUE;
                else {
                    $j--;
                    if ($j < 0)
                        break;
                }

        //Идем вниз от буквы
        //$j - строка
        $j = $fishka[1] + 1;
        if ($j <= 14)
            while ($cells[$fishka[0]][$j][0])
                if ($desk[$fishka[0]][$j][0])
                    return TRUE;
                else {
                    $j++;
                    if ($j > 14)
                        break;
                }

        return FALSE;
    }

    private static function code($code)
    {
        return ($code < 999 ? $code : $code - 999 - 1);
    }

    private static function fishka_correct(&$fishka, &$cells, &$desk, &$gameStatus)
    {
        //Определяем слово по горизонтали
        //$i - столбец
        $hor_word = static::$bukvy[self::code($fishka[2])][0];
        $horWordStart = [$fishka[0], $fishka[1]];
        //Начало слова по горизонтали

        //Идем влево от буквы
        $i = $fishka[0] - 1;
        if ($i >= 0)
            while (($cells[$i][$fishka[1]][0]) && ($i >= 0) && ($i <= 14)) {
                $hor_word = static::$bukvy[self::code($cells[$i][$fishka[1]][1])][0] . $hor_word;
                $horWordStart = [$i, $fishka[1]];
                $i--;
                if ($i < 0)
                    break;
            }

        //Идем вправо от буквы
        $i = $fishka[0] + 1;
        if ($i <= 14)
            while (($cells[$i][$fishka[1]][0]) && ($i >= 0) && ($i <= 14)) {
                $hor_word .= static::$bukvy[self::code($cells[$i][$fishka[1]][1])][0];
                $i++;
                if ($i > 14)
                    break;
            }

        if (mb_strlen($hor_word, 'UTF-8') > 1)
            if (!self::word_correct($hor_word, $gameStatus['wordsAccepted'])) {
                self::$badWords[$hor_word] = $hor_word;
                return FALSE;
            }

        //Определяем слово по вертикали
        //$j - строка
        $vert_word = static::$bukvy[self::code($fishka[2])][0];
        $vertWordStart = [$fishka[0], $fishka[1]];
        //Начало слова по горизонтали

        //Идем вверх от буквы
        $j = $fishka[1] - 1;

        if ($j >= 0)
            while (($cells[$fishka[0]][$j][0]) && ($j >= 0) && ($j <= 14)) {
                $vert_word = static::$bukvy[self::code($cells[$fishka[0]][$j][1])][0] . $vert_word;
                $vertWordStart = [$fishka[0], $j];
                $j--;
                if ($j < 0)
                    break;
            }

        //Идем вниз от буквы
        $j = $fishka[1] + 1;
        if ($j <= 14)
            while (($cells[$fishka[0]][$j][0]) && ($j >= 0) && ($j <= 14)) {
                $vert_word .= static::$bukvy[self::code($cells[$fishka[0]][$j][1])][0];
                $j++;
                if ($j > 14)
                    break;
            }

        if ((mb_strlen($vert_word, 'UTF-8') == 1) && (mb_strlen($hor_word, 'UTF-8') == 1))
            return FALSE;

        if (mb_strlen($vert_word, 'UTF-8') > 1)
            if (!self::word_correct($vert_word, $gameStatus['wordsAccepted'])) {
                self::$badWords[$vert_word] = $vert_word;
                return FALSE;
            }

        if (mb_strlen($hor_word, 'UTF-8') > 1) {
            self::$words[$fishka[0] . '-' . $fishka[1]]['hor'] = $hor_word;
            self::$goodWords[$horWordStart[0] . '-' . $horWordStart[1]]['hor'] = $hor_word;
            self::$goodWordsLinks[$fishka[0] . '-' . $fishka[1]]['hor'] = $horWordStart[0] . '-' . $horWordStart[1];
        }

        if (mb_strlen($vert_word, 'UTF-8') > 1) {
            self::$words[$fishka[0] . '-' . $fishka[1]]['vert'] = $vert_word;
            self::$goodWords[$vertWordStart[0] . '-' . $vertWordStart[1]]['vert'] = $vert_word;
            self::$goodWordsLinks[$fishka[0] . '-' . $fishka[1]]['vert'] = $vertWordStart[0] . '-' . $vertWordStart[1];
        }
        //Сохранили собранные слова

        return TRUE;
    }

}
//error_reporting(E_ALL);ini_set('display_errors', 1);
//include 'EruditGame.php';


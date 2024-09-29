<?php


class Ru
{
    public static $multi = [
        0 => [
            0 => ['slovo' => 3],
            3 => ['bukva' => 2],
            7 => ['slovo' => 3],
            11 => ['bukva' => 2],
            14 => ['slovo' => 3]
        ],
        1 => [1 => ['slovo' => 2], 5 => ['bukva' => 3], 9 => ['bukva' => 3], 13 => ['slovo' => 2]],
        2 => [2 => ['slovo' => 2], 6 => ['bukva' => 2], 8 => ['bukva' => 2], 12 => ['slovo' => 2]],
        3 => [
            0 => ['bukva' => 2],
            3 => ['slovo' => 2],
            7 => ['bukva' => 2],
            11 => ['slovo' => 2],
            14 => ['bukva' => 2]
        ],
        4 => [4 => ['slovo' => 2], 10 => ['slovo' => 2]],
        5 => [1 => ['bukva' => 3], 13 => ['bukva' => 3]],
        6 => [2 => ['bukva' => 2], 6 => ['bukva' => 2], 8 => ['bukva' => 2], 12 => ['bukva' => 2]],
        7 => [0 => ['slovo' => 3], 3 => ['bukva' => 2], 11 => ['bukva' => 2], 14 => ['slovo' => 3]],
        8 => [2 => ['bukva' => 2], 6 => ['bukva' => 2], 8 => ['bukva' => 2], 12 => ['bukva' => 2]],
        9 => [1 => ['bukva' => 3], 13 => ['bukva' => 3]],
        10 => [4 => ['slovo' => 2], 10 => ['slovo' => 2]],
        11 => [
            0 => ['bukva' => 2],
            3 => ['slovo' => 2],
            7 => ['bukva' => 2],
            11 => ['slovo' => 2],
            14 => ['bukva' => 2]
        ],
        12 => [2 => ['slovo' => 2], 6 => ['bukva' => 2], 8 => ['bukva' => 2], 12 => ['slovo' => 2]],
        13 => [1 => ['slovo' => 2], 5 => ['bukva' => 3], 9 => ['bukva' => 3], 13 => ['slovo' => 2]],
        14 => [
            0 => ['slovo' => 3],
            3 => ['bukva' => 2],
            7 => ['slovo' => 3],
            11 => ['bukva' => 2],
            14 => ['slovo' => 3]
        ]
    ];

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
        999 => ['*', 0, 3, false]
    ];

    protected static $words = [];
    protected static $badWords = [];
    protected static $goodWords = [];
    protected static $goodWordsLinks = [];

    public static function generateBankFishki()
    {
        $bankFishki = [];
        foreach (static::$bukvy as $code => $buk) {
            for ($i = 0; $i < $buk[2]; $i++) {
                $bankFishki[] = $code;
            }
        }
        shuffle($bankFishki);

        return $bankFishki;
    }

    /**
     * Находим все звезды, которые были забраны с поля и использованы в присланном ходе
     * @param $desk - текущее поле
     * @param $cells - новое поле
     * @return array
     */
    private static function deskZvezda(&$desk, &$cells): array
    {
        $zvezdy = [];
        for ($i = 0; $i <= 14; $i++) {
            for ($j = 0; $j <= 14; $j++) {
                if ($desk[$i][$j][0]) {
                    if ($desk[$i][$j][1] > 999) {
                        if ($cells[$i][$j][2] !== false) // Звездочку забрали с поля?
                        {
                            $zvezdy[] = [
                                'i' => $i,
                                'j' => $j,
                                'letter_code' => $cells[$i][$j][2],
                                'is_correct' => ($desk[$i][$j][1] - 999 - 1 - $cells[$i][$j][2]) === 0 // код фишки, которая забрала звезду с поля
                            ];
                        }
                    }
                }
            }
        }

        return $zvezdy;
    }

    /**
     * @param array $fishki - фишки, полученные из сравнения полей cells, desk
     * @param array $playerFishki - фишки на руках у игрока
     * @param array $zvezdyTemporary - звезды, забранные с поля
     * @return array
     */
    private static function validateFishki(array $fishki, array $playerFishki, array $zvezdyTemporary): array
    {
        $numZvezd = 0;
        // Сколько звезд поставил на поле игрок в этом ходе
        foreach ($fishki as $cellFishka) {
            if ($cellFishka['code'] > 999) {
                $numZvezd++;
            }
        }

        // Отнимаем число звезд, бывших на руках у игрока
        foreach ($playerFishki as $playerFishka) {
            if ($playerFishka == 999) {
                $numZvezd--;
            }
        }

        // Проверяем, что игрок поставил не больше звезд, чем было на руках + он забрал с поля
        if ((count($zvezdyTemporary) - $numZvezd) < 0) {
            return [];
        }

        return $zvezdyTemporary;
    }

    public static function submit(&$cells, &$desk, array $playerFishki, array $wordsAccepted)
    {
        self::$words = [];
        self::$badWords = [];
        self::$goodWords = [];
        self::$goodWordsLinks = [];

        if (!$desk) {
            $desk = self::init_desk();
        }

        $bad_fishki = [];

        // Забранные с поля звезды (временные звезды, только на этот хлд)
        $zvezdyTemporary = static::deskZvezda($desk, $cells);

        //Проверяем полученный массив звезд на признак !is_correct
        if (!self::isCorrectZvezdy($zvezdyTemporary)) {
            return false;
        }

        $fishki = self::compare_desks($desk, $cells, $playerFishki);

        if ($fishki === false) {
            return false;
        } else {
            $zvezdy = self::validateFishki($fishki, $playerFishki, $zvezdyTemporary);
        }

        // Проверяем слова на корректность и удаляем лишние фишки
        $num_fishki = count($fishki);
        for ($f = 0; $f < $num_fishki; $f++) {
            foreach ($fishki as $num => $fishka) {
                if (!self::fishka_correct($fishka, $cells, $desk, $wordsAccepted)) {
                    $bad_fishki[] = $fishki[$num];
                    unset($fishki[$num]);
                    $cells[$fishka['i']][$fishka['j']][0] = false;
                    $cells[$fishka['i']][$fishka['j']][1] = false;
                }
            }
        }

        //Проверяем связность оставшихся новых фишек со старыми
        $num_fishki = count($fishki);

        for ($f = 0; $f < $num_fishki; $f++) {
            foreach ($fishki as $num => $fishka) {
                if (self::fishka_connected($fishka, $cells, $desk)) {
                    $fishki[$num]['connected'] = true;
                    $desk[$fishka['i']][$fishka['j']][0] = true; // todo обязательно трогать исходную доску?
                }
            }
        }
/*
        // Несвязанные фишки признаем плохими - bad_fishki
        foreach ($fishki as $num => $fishka) {
            if (!isset($fishka['connected'])) {
                $cells[$fishka['i']][$fishka['j']][0] = false;
                $cells[$fishka['i']][$fishka['j']][1] = false;
                $bad_fishki[] = $fishki[$num];
                unset($fishki[$num]);
            }
        }
*/
        foreach ($bad_fishki as $bad_fishka) {
            if (isset(self::$words[$bad_fishka['i'] . '-' . $bad_fishka['j']])) {
                unset(self::$words[$bad_fishka['i'] . '-' . $bad_fishka['j']]);
            }
            //Убрали слова на плохой фишке

            if (isset(self::$goodWordsLinks[$bad_fishka['i'] . '-' . $bad_fishka['j']]['hor'])) {
                if (isset(self::$goodWords[self::$goodWordsLinks[$bad_fishka['i'] . '-' . $bad_fishka['j']]['hor']]['hor'])) {
                    unset(self::$goodWords[self::$goodWordsLinks[$bad_fishka['i'] . '-' . $bad_fishka['j']]['hor']]['hor']);
                }
                unset(self::$goodWordsLinks[$bad_fishka['i'] . '-' . $bad_fishka['j']]['hor']);
                //Убрали слова, которые проходят через плохую фищку по горизонтали
            }

            if (isset(self::$goodWordsLinks[$bad_fishka['i'] . '-' . $bad_fishka['j']]['vert'])) {
                if (isset(self::$goodWords[self::$goodWordsLinks[$bad_fishka['i'] . '-' . $bad_fishka['j']]['vert']]['vert'])) {
                    unset(self::$goodWords[self::$goodWordsLinks[$bad_fishka['i'] . '-' . $bad_fishka['j']]['vert']]['vert']);
                }
                unset(self::$goodWordsLinks[$bad_fishka['i'] . '-' . $bad_fishka['j']]['vert']);
                //Убрали слова, которые проходят через плохую фищку по вертикали
            }
        }

        $good_words = [];
        foreach (self::$goodWords as $h_v => $h_v_word) {
            $ij = explode('-', $h_v);
            if (isset($h_v_word['hor'])) {
                if (!isset($good_words[$h_v_word['hor']])) {
                    $good_words[$h_v_word['hor']] = self::wordPrice($h_v_word['hor'], $ij[0], $ij[1], 'hor');
                } else {
                    $price = self::wordPrice($h_v_word['hor'], $ij[0], $ij[1], 'hor');
                    if ($good_words[$h_v_word['hor']] < $price) {
                        $good_words[$h_v_word['hor']] = $price;
                    }
                }
            }

            if (isset($h_v_word['vert'])) {
                if (!isset($good_words[$h_v_word['vert']])) {
                    $good_words[$h_v_word['vert']] = self::wordPrice($h_v_word['vert'], $ij[0], $ij[1], 'vert');
                } else {
                    $price = self::wordPrice($h_v_word['vert'], $ij[0], $ij[1], 'vert');
                    if ($good_words[$h_v_word['vert']] < $price) {
                        $good_words[$h_v_word['vert']] = $price;
                    }
                }
            }
            //Посчитали очки для всех хороших слов
        }

        foreach ($fishki as $nn => $fishka) {
            if ($fishka['replaced_code'] !== false) {
                foreach ($zvezdy ?: [] as $num => $zvezda) {
                    if ($zvezda['letter_code'] === $fishka['replaced_code']) {
                        $cells[$zvezda['i']][$zvezda['j']][1] = $fishka['replaced_code']; // в ячейку записали код фишки, которая забрала звездочку
                        $cells[$zvezda['i']][$zvezda['j']][2] = false;
                        unset($zvezdy[$num]);
                    }
                }
            }
        }
        //Обнулили и освободили сыгравшую забратую фишку

        foreach ($bad_fishki as $nn => $bfishka) {
            if ($bfishka['replaced_code'] !== false) {
                foreach ($zvezdy ?: [] as $num => $zvezda) {
                    if ($zvezda['letter_code'] === $bfishka['replaced_code']) {
                        $cells[$zvezda['i']][$zvezda['j']][2] = false;
                        unset($zvezda[$num]);
                    }
                }
            }
        }
        //ТОЛЬКО освободили НЕсыгравшую забратую фишку

        return [
            'bad' => $bad_fishki,
            'good' => $fishki,
            'words' => $good_words,
            'badWords' => self::$badWords,
            'goodWords' => self::$goodWords,
            'goodWordsLinks' => self::$goodWordsLinks
        ];
    }

    public static function getLetterCode($letter)
    {
        foreach (static::$bukvy as $num => $bukva) {
            if ($bukva[0] == $letter) {
                return $num;
            } else { // Буква под звездочкой?
                if ($bukva[0] == mb_strtolower($letter, 'UTF-8') || stripos($letter, $bukva[0]) !== false) {
                    return $num + 999 + 1;
                }
            }
        }

        return;
    }

    private static function wordPrice($word, $i, $j, $orientation = 'hor')
    {
        //print $i.' - '.$j;
        $price = 0;
        $word_multi = [];
        $I = $i;
        $J = $j;

        for ($k = 0; $k < mb_strlen($word, 'UTF-8'); $k++) {
            if ($orientation == 'hor') {
                $I = $i + $k;
            } else {
                $J = $j + $k;
            }


            $letterPrice = static::$bukvy[self::getLetterCode(mb_substr($word, $k, 1, 'UTF-8'))][1];

            if (isset(self::$multi[$I][$J])) {
                if (isset (self::$multi[$I][$J]['bukva'])) {
                    $letterPrice = $letterPrice * self::$multi[$I][$J]['bukva'];
                } else {
                    $word_multi[] = self::$multi[$I][$J]['slovo'];
                }
            }

            //print $I.' - '.$J.' - '.$letterPrice.'<br />';

            $price += $letterPrice;
        }

        foreach ($word_multi as $multi) {
            $price = $price * $multi;
        }

        return $price;
    }


    public static function init_desk()
    {
        $dsc = [];
        //$j - строки, $i - столбцы
        for ($j = 0; $j <= 14; $j++) {
            for ($i = 0; $i <= 14; $i++) {
                $dsc[$i][$j][0] = false; // Признак пустой клетки
                $dsc[$i][$j][1] = false; // Код фишки
                $dsc[$i][$j][2] = false; // Код фишки, которая забрала звезду с поля
            }
        }

        return $dsc;
    }

    private static function word_correct($word, array $wordsAccepted): bool
    {
        if (isset($wordsAccepted[$word])) {
            return false;
        }

        return DictModel::checkWord($word);
    }

    /**
     * Возвращает массив новых фишек на поле или false
     * @param array $desk - старая доска
     * @param array $cells - новая доска
     * @param array $playerFishki - фишки у игргока, приславшего хад на проверку
     * @return array|false
     */
    private static function compare_desks(array &$desk, array &$cells, array $playerFishki)
    {
        $fshki = [];
        //$j - строки, $i - столбцы
        for ($j = 0; $j <= 14; $j++) {
            for ($i = 0; $i <= 14; $i++) {
                // Проверки на консистентность присланной доски - пропала буква с поля?
                if ($desk[$i][$j][0] && !$cells[$i][$j][0]) {
                    return false;
                }

                // Проверки на консистентность присланной доски - букву заменили на другую?
                if ($desk[$i][$j][0] && ($cells[$i][$j][1] != $desk[$i][$j][1])) {
                    return false;
                }

                if ($cells[$i][$j][0] && !$desk[$i][$j][0]) {
                    $fshki[] = [
                        'i' => $i, // 0 => столбец
                        'j' => $j, // 1 => строка
                        'code' => $cells[$i][$j][1], // 2 => код фишки
                        'replaced_code' => $cells[$i][$j][2] // 3 => Код фишки, которой заменили звездочку
                    ];
                }
            }
        }

        // Проверки на соответствие новых фишек на поле и фишек у игрока из $playerFishki

        $tmpPlayerFishki = $playerFishki;
        $tmpCellsFishki = $fshki;
        foreach($tmpCellsFishki as $cellNum => $cellFishka) {
            foreach($tmpPlayerFishki as $playerNum => $playerFishka) {
                // ищем новую фишку с поля в массиве фишек игрока
                if (
                    $cellFishka['code'] == $playerFishka
                    || (
                        $cellFishka['replaced_code']
                        && $cellFishka['replaced_code'] == $playerFishka
                    )
                    || (
                        !$cellFishka['replaced_code']
                        && $cellFishka['code'] > 999
                        && $playerFishka == 999
                    )
                ) {
                    unset($tmpPlayerFishki[$playerNum]);
                    unset($tmpCellsFishki[$cellNum]);

                    break;
                }
            }
        }

        // Все фишки с поля должны быть найдены в массиве фишек игрока
        if (!empty($tmpCellsFishki)) {
            return false;
        }

        if (count($fshki) > count($playerFishki)) {
            return false;
        }

        return $fshki;
    }

    private static function fishka_connected(&$fishka, &$cells, &$desk)
    {
        if ($fishka['connected'] ?? false) {
            return true;
        }

        if (($fishka['i'] == 7) && ($fishka['j'] == 7)) {
            return true;
        }

        //Идем влево от буквы
        //$i - столбец
        $i = $fishka['i'] - 1;
        if ($i >= 0) {
            while ($cells[$i][$fishka['j']][0]) {
                if ($desk[$i][$fishka['j']][0]) {
                    return true;
                } else {
                    $i--;
                    if ($i < 0) {
                        break;
                    }
                }
            }
        }

        //Идем вправо от буквы
        $i = $fishka['i'] + 1;
        if ($i <= 14) {
            while ($cells[$i][$fishka['j']][0]) {
                if ($desk[$i][$fishka['j']][0]) {
                    return true;
                } else {
                    $i++;
                    if ($i > 14) {
                        break;
                    }
                }
            }
        }

        //Идем вверх от буквы
        //$j - строка
        $j = $fishka['j'] - 1;
        if ($j >= 0) {
            while ($cells[$fishka['i']][$j][0]) {
                if ($desk[$fishka['i']][$j][0]) {
                    return true;
                } else {
                    $j--;
                    if ($j < 0) {
                        break;
                    }
                }
            }
        }

        //Идем вниз от буквы
        //$j - строка
        $j = $fishka['j'] + 1;
        if ($j <= 14) {
            while ($cells[$fishka['i']][$j][0]) {
                if ($desk[$fishka['i']][$j][0]) {
                    return true;
                } else {
                    $j++;
                    if ($j > 14) {
                        break;
                    }
                }
            }
        }

        return false;
    }

    private static function code($code)
    {
        return ($code < 999 ? $code : $code - 999 - 1);
    }

    private static function fishka_correct(&$fishka, &$cells, &$desk, $wordsAccepted)
    {
        //Определяем слово по горизонтали
        //$i - столбец
        $hor_word = static::$bukvy[self::code($fishka['code'])][0];
        $horWordStart = [$fishka['i'], $fishka['j']];
        //Начало слова по горизонтали

        //Идем влево от буквы
        $i = $fishka['i'] - 1;
        if ($i >= 0) {
            while (($cells[$i][$fishka['j']][0]) && ($i >= 0) && ($i <= 14)) {
                $hor_word = static::$bukvy[self::code($cells[$i][$fishka['j']][1])][0] . $hor_word;
                $horWordStart = [$i, $fishka['j']];
                $i--;
                if ($i < 0) {
                    break;
                }
            }
        }

        //Идем вправо от буквы
        $i = $fishka['i'] + 1;
        if ($i <= 14) {
            while (($cells[$i][$fishka['j']][0]) && ($i >= 0) && ($i <= 14)) {
                $hor_word .= static::$bukvy[self::code($cells[$i][$fishka['j']][1])][0];
                $i++;
                if ($i > 14) {
                    break;
                }
            }
        }

        if (mb_strlen($hor_word, 'UTF-8') > 1) {
            if (!self::word_correct($hor_word, $wordsAccepted)) {
                self::$badWords[$hor_word] = $hor_word;

                return false;
            }
        }

        //Определяем слово по вертикали
        //$j - строка
        $vert_word = static::$bukvy[self::code($fishka['code'])][0];
        $vertWordStart = [$fishka['i'], $fishka['j']];
        //Начало слова по вертикали

        //Идем вверх от буквы
        $j = $fishka['j'] - 1;

        if ($j >= 0) {
            while (($cells[$fishka['i']][$j][0]) && ($j >= 0) && ($j <= 14)) {
                $vert_word = static::$bukvy[self::code($cells[$fishka['i']][$j][1])][0] . $vert_word;
                $vertWordStart = [$fishka['i'], $j];
                $j--;
                if ($j < 0) {
                    break;
                }
            }
        }

        //Идем вниз от буквы
        $j = $fishka['j'] + 1;
        if ($j <= 14) {
            while (($cells[$fishka['i']][$j][0]) && ($j >= 0) && ($j <= 14)) {
                $vert_word .= static::$bukvy[self::code($cells[$fishka['i']][$j][1])][0];
                $j++;
                if ($j > 14) {
                    break;
                }
            }
        }

        if ((mb_strlen($vert_word, 'UTF-8') == 1) && (mb_strlen($hor_word, 'UTF-8') == 1)) {
            return false;
        }

        if (mb_strlen($vert_word, 'UTF-8') > 1) {
            if (!self::word_correct($vert_word, $wordsAccepted)) {
                self::$badWords[$vert_word] = $vert_word;

                return false;
            }
        }

        if (mb_strlen($hor_word, 'UTF-8') > 1) {
            self::$words[$fishka['i'] . '-' . $fishka['j']]['hor'] = $hor_word;
            self::$goodWords[$horWordStart[0] . '-' . $horWordStart[1]]['hor'] = $hor_word;
            self::$goodWordsLinks[$fishka['i'] . '-' . $fishka['j']]['hor'] = $horWordStart[0] . '-' . $horWordStart[1];
        }

        if (mb_strlen($vert_word, 'UTF-8') > 1) {
            self::$words[$fishka['i'] . '-' . $fishka['j']]['vert'] = $vert_word;
            self::$goodWords[$vertWordStart[0] . '-' . $vertWordStart[1]]['vert'] = $vert_word;
            self::$goodWordsLinks[$fishka['i'] . '-' . $fishka['j']]['vert'] = $vertWordStart[0] . '-' . $vertWordStart[1];
        }
        //Сохранили собранные слова

        return true;
    }

    /**
     * @param array $cells
     * @return bool
     */
    public static function checkHasBadField(array $cells): bool
    {
        foreach ($cells as $row) {
            foreach ($row as $field) {
                // ошибка, если признак буквы - true, но кода буквы нет
                if ($field[0] === true && $field[1] === false) {
                    return true;
                } // ошибка, если признак буквы есть, но код буквы не найден в массивах букв Ру-Инг
                elseif (
                    $field[0] !== false
                    &&
                    !isset(Ru::$bukvy[$field[1]][1])
                    &&
                    !isset(Eng::$bukvy[$field[1]][1])
                    &&
                    !isset(Ru::$bukvy[$field[1] - 1 - 999][1])
                    &&
                    !isset(Eng::$bukvy[$field[1] - 1 - 999][1])
                ) {
                    return true;
                } // ошибка, если признак буквы - фолс, но буква есть
                elseif ($field[0] === false && $field[1] !== false) {
                    return true;
                }
            }
        }

        return false;
    }

    protected static function isCorrectZvezdy(array $zvezdyTemporary): bool
    {
        foreach($zvezdyTemporary as $zvezda) {
            if(!$zvezda['is_correct']){
                return false;
            }
        }

        return true;
    }
}
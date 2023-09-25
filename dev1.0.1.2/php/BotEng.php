<?php

use Lang\Eng;
use Lang\Ru;

class BotEng
{
    const MINUTES_TO_GO = 5;
    const ENG_LANG = 'EN';
    const RU_LANG = 'RU';
    public static $langClass = Eng::class;
    public static $lang = self::ENG_LANG;
    public static $thinkEndTime;

    const BOT_GAMES = 'erudit.botEN_games';

    public static function Run()
    {
        $_SERVER['DOCUMENT_ROOT'] = '/var/www/erudit.club';
        set_time_limit(self::MINUTES_TO_GO * 60 + 25);
        $start_script_time = date('U');
        $script_work_time = self::MINUTES_TO_GO * 60 - 5;
        $secondsToBotRefresh = 10;

        $botsTurns = [];
        $botTimes = [];
        while ((date('U') - $start_script_time) < $script_work_time) {
            if ($Bot = Cache::lpop(static::BOT_GAMES)) {
                $_COOKIE[Cookie::COOKIE_NAME] = $Bot;

                $resp = ['gameState' => 1];
                $zaprosNum = 3;

                $_GET['queryNumber'] = $zaprosNum;
                $_GET['lang'] = static::$lang;

                $resp = include __DIR__ . '/status_checker.php';

                $resp = json_decode($resp, true);

                if (($resp['gameState'] == 'gameResults') || ($resp['gameState'] == 'initGame')) {
                    ob_start();
                    $resp = include __DIR__ . '/exit_game.php';
                    ob_end_clean();
                    //Не будем анализировать ответы!)) - просто новая игра
                    unset($botsTurns[$Bot]);
                    unset($botTimes[$Bot]);
                    Cache::hdel('erudit.bot_v3_list', $Bot);

                    continue;
                } else {
                    Cache::rpush(static::BOT_GAMES, $Bot);
                    //Вернули бота в список игроков

                    print $resp['gameState'];


                    if ($resp['gameState'] == 'myTurn') {
                        if (
                            ($resp['turnTime'] == 120
                                && (
                                    (
                                        ($resp['minutesLeft'] <= 1)
                                        &&
                                        ($resp['secondsLeft'] < 40)
                                    )
                                    ||
                                    ($resp['minutesLeft'] < 1)
                                )
                            )
                            ||
                            ($resp['turnTime'] == 90
                                && (
                                    (
                                        ($resp['minutesLeft'] <= 1)
                                        &&
                                        ($resp['secondsLeft'] < 10)
                                    )
                                    ||
                                    ($resp['minutesLeft'] < 1)
                                )
                            )
                            ||
                            (
                                ($resp['turnTime'] == 60)
                                &&
                                ($resp['secondsLeft'] < 40)
                                &&
                                ($resp['secondsLeft'] > 0)
                            )
                        ) {
                            $secondsToThink = $resp['minutesLeft'] * 60 + $resp['secondsLeft'];
                            $thinkStartTime = date('U');
                            self::$thinkEndTime = $thinkStartTime + $secondsToThink;
                            print self::$thinkEndTime;
                            $botsTurns[$Bot] = $resp['gameSubState'];

                            $turn_submit = self::sendResponse($resp);
                        }
                    }
                }

                if (isset($botTimes[$Bot])) {
                    if (($time = date('U') - $botTimes[$Bot]) < $secondsToBotRefresh) {
                        sleep($secondsToBotRefresh - $time);
                    }
                }
                $botTimes[$Bot] = date('U');
            }

            if ($botTimes == []) {
                sleep(10);
            } else {
                sleep(1);
            }

            print 'next!';
        }


        exit();
    }


    public static function changeFishkiBot(&$data)
    {
        $kf = 1;

        foreach ($data['fishki'] as $fishka) {
            $kf++;
            if ($fishka != 999) {
                $_POST['fishka_' . $kf . '_' . $fishka] = 'on';
            }
        }
        ob_start();
        $resp = include __DIR__ . '/change_fishki.php';
        ob_end_clean();
        return $resp;
    }

    public static function sendResponse(&$data)
    {
        if (isset($data['desk'])) {
            $cells = $data['desk']; // $_POST['cells'] - готовим доску на отправку
            $obj = new Erudit\Game();
            $slovaPlayed = $obj->gameWordsPlayed();
            $obj->botUnlock(); // разблокировали состояние игры
        } else {
            $cells = static::$langClass::init_desk();
            $slovaPlayed = [];
        }

        error_reporting(E_ALL & ~E_NOTICE);
        ini_set('display_errors', 0);
        try {
            if (self::makeTurn($cells, $data['fishki'], $slovaPlayed)) {
                print "++++++++++Submiting turn...........";
                $_POST['cells'] = json_encode($cells);
                $resp = include __DIR__ . '/turn_submitter.php';
                print $resp;

                return $resp;
            } else {
                return self::changeFishkiBot($data);
            }
        } catch (Throwable $e) {
            print $e->__toString();
            LogModel::add(
                [
                    LogModel::CATEGORY_FIELD => LogModel::CATEGORY_BOT_ERROR,
                    LogModel::MESSAGE_FIELD => $e->__toString()
                ]
            );
        }
    }

    /**
     * @param array $desk - Поле для редактирования и отправки на сервер
     * @param array $fishki - фишки игрока
     * @param array $slovaPlayed - массив уже сыгранных слов
     * @return bool
     */
    public static function makeTurn(array &$desk, array &$fishki, array &$slovaPlayed): bool
    {
        $fishki1 = $fishki;
        $word = '';

        // CLUB-270 забираем звезды с поля
        for ($j = 0; $j <= 14; $j++) {
            for ($i = 0; $i <= 14; $i++) {
                if ($desk[$i][$j][0] && ($desk[$i][$j][1] > 999)) {
                    foreach ($fishki as $num => $fishka) {
                        if (($fishka + 999 + 1) === $desk[$i][$j][1]) {
                            $desk[$i][$j][2] = $fishka;
                            $fishki[$num] = $desk[$i][$j][1];

                            break;
                        }
                    }
                }
            }
        }
        // Собрали звезды с поля


        print '$k - cycle;';
        for ($k = 0; $k < 2; $k++) {// 2 прохода
            //$j - строки, $i - столбцы
            print 'j,i cycle; ';
            for ($j = 0; $j <= 14; $j++) {
                for ($i = 0; $i <= 14; $i++) {
                    if (($i == 7) && ($j == 7) && !$desk[$i][$j][0]) {
                        if (date('U') < self::$thinkEndTime) {
                            print 'sleva;';
                            self::findWordSleva($i, $j, $desk, $fishki, $slovaPlayed);
                        }
                    }

                    if (!$desk[$i][$j][0] && (isset($desk[$i][$j - 1]) && $desk[$i][$j - 1][0])) {
                        //print $i.$j.$desk[$i+1][$j][1];
                        $ff = '';//для временного отключения поиска слов вниз
                        if (date('U') < self::$thinkEndTime) {
                            print 'vniz';
                            self::findWordVniz($i, $j, $desk, $fishki, $slovaPlayed);
                        }
                        //Ищем слова по вертикали (пока) начинающиеся на $j-1...
                    }

                    if (!$desk[$i][$j][0] && ($desk[$i + 1][$j][0] ?? false)) {
                        $ff = '';//для временного отключения поиска слов слева
                        if (date('U') < self::$thinkEndTime) {
                            print 'sleva;';
                            self::findWordSleva($i, $j, $desk, $fishki, $slovaPlayed);
                        }
                        //Ищем слова по горизонтали (пока) заканчивающиеся на $i+1...
                    }

                    if (!$desk[$i][$j][0] && (isset($desk[$i][$j + 1]) && $desk[$i][$j + 1][0])) {
                        $ff = '';//для временного отключения поиска слов сверху
                        if (date('U') < self::$thinkEndTime) {
                            print 'sverhu;';
                            self::findWordSverhu($i, $j, $desk, $fishki, $slovaPlayed);
                        }
                        //Ищем слова по вертикали (пока) заканчивающиеся на $j+1...
                    }

                    if (!$desk[$i][$j][0] && ($desk[$i - 1][$j][0] ?? false)) {
                        $ff = '';//для временного отключения поиска слов справа
                        if (date('U') < self::$thinkEndTime) {
                            print 'sprava;';
                            self::findWordSprava($i, $j, $desk, $fishki, $slovaPlayed);
                        }
                        //Ищем слова по горизонтали (пока) заканчивающиеся на $i+1...
                        //break 2;
                    }
                }
            }

            if (count($fishki) && (count($fishki1) != count($fishki))) {
                continue;//На следующий круг
            } else {
                print 'go out from k cycle;';
                break;
            }
        }

        if (count($fishki1) == count($fishki)) {
            $fishki = $fishki1;
            return false;
        } else {
            return true;
        }
    }


    public static function printr(&$cells)
    {
        for ($j = 0; $j <= 14; $j++) {
            for ($i = 0; $i <= 14; $i++) {
                if (($i == $j) && !$cells[$i][$j][0]) {
                    print ($i % 10);
                } elseif ($cells[$i][$j][0]) {
                    print static::$langClass::$bukvy[self::getFishkaCode($cells[$i][$j][1])][0];
                } else {
                    print '.';
                }
            }
            print "\n";
        }
    }


    public static function findWordSleva($x, $y, &$desk, &$fishki, array &$slovaPlayed, $orientation = 'all')
    {
        if (!count($fishki)) {
            return '';
        }

        /**
         * @var Ru|Eng $bukvy
         */

        //пробуем влево от xy
        $maxLen = self::maxToLeft($x, $y, count($fishki), $desk);
        $maxWordLen = $maxLen;

        if (
            ($desk[$x][$y + 1][0] ?? false)
            ||
            ($desk[$x][$y - 1][0] ?? false)
        ) {
            return '';
        }
        //Не анализируем вертикальные примыкающие буквы

        $lastLetter = '';
        $step = 1;
        while ($desk[$x + $step][$y][0]) {
            // Собираем коды последних букв слова
            $lastLetter .= static::$langClass::$bukvy[self::getFishkaCode($desk[$x + $step][$y][1])][0];
            $maxWordLen++;
            $step++;
        }
        //print '-' . $x . '-' . $y . '-' . $lastLetter . '-';
        $regexp = self::makeRegexp($fishki);

        $zapros = "select slovo from dict where (slovo REGEXP \"^[$regexp]{0,$maxLen}$lastLetter";

        if (($maxRightAfterLen = self::maxToRight(
                $x + mb_strlen($lastLetter, 'UTF-8') + 1,
                $y,
                count($fishki),
                $desk
            )) || (($x == 7) && ($y == 7))) {
            $zapros .= "[$regexp]{0,$maxRightAfterLen}";
            $maxWordLen += $maxRightAfterLen;
        }

        if ($maxWordLen > 7) {
            $maxWordLen = 7;
        }
        // так работает индекс
        /*
         * Как идея ограничивать мах длину слова, но она будет почти всегда 8+
        if ( ($maxWordLen - $maxRightAfterLen - $maxLen + count($fishki)) < $maxWordLen )
            $maxWordLen = $maxWordLen - $maxRightAfterLen - $maxLen + count($fishki);
        */

        $zapros .= "$\") AND NOT deleted = 1 AND slovo != '$lastLetter' AND length<=$maxWordLen ORDER BY length ASC";
        print $zapros . 'SLEVA'; //sleep (5);

        if ($res = DB::queryArray($zapros)) {
            foreach ($res as $row) {
                if (!isset($slovaPlayed[$row['slovo'] = mb_strtolower($row['slovo'], 'UTF-8')])
                    &&
                    self::checkWordFishki($fishki, $row['slovo'], $lastLetter, $lettersZvezd)) {
                    $cells = $desk; // создали временную копию доски для попытки составить слово

                    $slovoNach = ($lastLetter === '' ? 0 : mb_strpos($row['slovo'], $lastLetter, 0, 'UTF-8'));
                    $lastLetterLen = ($lastLetter === '' ? 0 : mb_strlen($lastLetter, 'UTF-8'));
                    for ($k = 0; $k < $slovoNach; $k++) {
                        $cells[$x - $k][$y][0] = true;
                        $cells[$x - $k][$y][1] = static::$langClass::getLetterCode(
                            $letter = mb_substr($row['slovo'], $slovoNach - 1 - $k, 1, 'UTF-8')
                        );

                        if (isset($lettersZvezd[$letter])) {
                            // Указали на занятую звездочку
                            $cells[$x - $k][$y][2] = $lettersZvezd[$letter]['code'];
                            if (--$lettersZvezd[$letter]['count'] == 0) {
                                unset($lettersZvezd[$letter]);
                            }
                        }
                    }

                    if (($x == 7) && ($y == 7)) {
                        $delta = -1;
                    } else {
                        $delta = 0;
                    }

                    for ($k = $slovoNach + $lastLetterLen; $k < mb_strlen($row['slovo'], 'UTF-8'); $k++) {
                        $cells[$x + $k - $slovoNach + 1 + $delta][$y][0] = true;
                        $cells[$x + $k - $slovoNach + 1 + $delta][$y][1] = static::$langClass::getLetterCode(
                            $letter = mb_substr($row['slovo'], $k, 1, 'UTF-8')
                        );

                        if (isset($lettersZvezd[$letter])) {
                            // Указали на занятую звездочку
                            // todo 268 Не нужно отмечать [2] что атм звездочка - проверить
                            $cells[$x + $k - $slovoNach + 1 + $delta][$y][2] = $lettersZvezd[$letter]['code'];
                            if (--$lettersZvezd[$letter]['count'] == 0) {
                                unset($lettersZvezd[$letter]);
                            }
                        }
                    }

                    try {
                        if (Ru::checkHasBadField($cells)) {
                            throw new Exception('Bad $cells during ' . __METHOD__);
                        }

                        self::printr($cells);
                    } catch (Throwable $e) {
                        LogModel::add(
                            [
                                LogModel::CATEGORY_FIELD => LogModel::CATEGORY_BOT_ERROR,
                                LogModel::MESSAGE_FIELD => json_encode(
                                    [
                                        'error' => $e->__toString(),
                                        'cells' => $cells
                                    ]
                                )
                            ]
                        );

                        continue;
                    }

                    $desk = $cells;

                    return true;
                }
            }
        }

        return '';
    }

    public static function findWordSverhu($x, $y, &$desk, &$fishki, array &$slovaPlayed, $orientation = 'all')
    {
        if (!count($fishki)) {
            return '';
        }

        //пробуем вверх от xy
        $maxLen = self::maxToUp($x, $y, count($fishki), $desk);
        $maxWordLen = $maxLen;

        // Не анализируем, если есть горизонтальные примыкающие буквы
        if ($desk[$x + 1][$y][0] || $desk[$x - 1][$y][0]) {
            return '';
        }

        // Собираем буквы, которые примыкают к слову снизу
        $lastLetter = '';
        $step = 1;
        while ($desk[$x][$y + $step][0] ?? false) {
            $lastLetter .= static::$langClass::$bukvy[self::getFishkaCode($desk[$x][$y + $step][1])][0];
            $maxWordLen++;
            $step++;
        }

        $regexp = self::makeRegexp($fishki);
        $zapros = "select slovo from dict where (slovo REGEXP \"^[$regexp]{0,$maxLen}$lastLetter";
        if ($maxDownAfterLen = self::maxToDown(
            $x,
            $y + mb_strlen($lastLetter, 'UTF-8') + 1,
            count($fishki),
            $desk
        )) {
            $zapros .= "[$regexp]{0,$maxDownAfterLen}";
            $maxWordLen += $maxDownAfterLen;
        }

        if ($maxWordLen > 7) {
            $maxWordLen = 7;
        }//так работает индекс

        $zapros .= "$\") AND NOT deleted = 1  AND length<=$maxWordLen ORDER BY length ASC";;
        print $zapros . 'SVERHU'; //sleep (5);

        // Длина фрагмента из фишек уже на поле
        $lastLetterLen = ($lastLetter === '' ? 0 : mb_strlen($lastLetter, 'UTF-8'));
        // Строка, с которой начинается примыкающая сверху часть слова
        $yLastLetter = $y - $lastLetterLen;

        if ($res = DB::queryArray($zapros)) {
            foreach ($res as $row) {
                if (!isset(
                        $slovaPlayed[$row['slovo'] = mb_strtolower(
                            $row['slovo'],
                            'UTF-8'
                        )]
                    ) && self::checkWordFishki($fishki, $row['slovo'], $lastLetter, $lettersZvezd)) {
                    $cells = $desk;
                    $slovoNach = ($lastLetter === '' ? 0 : mb_strpos($row['slovo'], $lastLetter, 0, 'UTF-8'));

                    // Ставим фишки вверх от текущей позиции
                    for ($k = 0; $k < $slovoNach; $k++) {
                        $cells[$x][$y - $k][0] = true;
                        $cells[$x][$y - $k][1] = static::$langClass::getLetterCode(
                            $letter = mb_substr($row['slovo'], $slovoNach - 1 - $k, 1, 'UTF-8')
                        );

                        if (isset($lettersZvezd[$letter])) {
                            // Указали на занятую звездочку
                            $cells[$x][$y - $k][2] = $lettersZvezd[$letter]['code'];
                            if (--$lettersZvezd[$letter]['count'] == 0) {
                                unset($lettersZvezd[$letter]);
                            }
                        }
                    }

                    // Ставим фишки вниз от позиции ниже уже существующих фишек на поле
                    for ($k = $slovoNach + $lastLetterLen; $k < mb_strlen($row['slovo'], 'UTF-8'); $k++) {
                        $cells[$x][$y + $k - $slovoNach + 1][0] = true;
                        $cells[$x][$y + $k - $slovoNach + 1][1] = static::$langClass::getLetterCode(
                            $letter = mb_substr($row['slovo'], $k, 1, 'UTF-8')
                        );

                        if (isset($lettersZvezd[$letter])) {
                            // Указали на занятую звездочку
                            $cells[$x][$y + $k - $slovoNach + 1][2] = $lettersZvezd[$letter]['code'];
                            if (--$lettersZvezd[$letter]['count'] == 0) {
                                unset($lettersZvezd[$letter]);
                            }
                        }
                    }

                    try {
                        if (Ru::checkHasBadField($cells)) {
                            throw new Exception('Bad $cells during ' . __METHOD__);
                        }

                        self::printr($cells);
                    } catch (Throwable $e) {
                        LogModel::add(
                            [
                                LogModel::CATEGORY_FIELD => LogModel::CATEGORY_BOT_ERROR,
                                LogModel::MESSAGE_FIELD => json_encode(
                                    [
                                        'error' => $e->__toString(),
                                        'cells' => $cells
                                    ]
                                )
                            ]
                        );

                        continue;
                    }

                    $desk = $cells;
                    return true;
                    //return ['word'=>$row['slovo'],'lastLettersNum'=>mb_strlen($lastLetter,'UTF-8')];
                }
            }
        }

        return '';
    }

    public static function findWordSprava($x, $y, &$desk, &$fishki, array &$slovaPlayed, $orientation = 'all')
    {
        if (!count($fishki)) {
            return '';
        }
        //пробуем справа от xy
        $maxLen = self::maxToRight($x, $y, count($fishki), $desk);
        $maxWordLen = $maxLen;

        if (
            ($desk[$x][$y + 1][0] ?? false)
            ||
            ($desk[$x][$y - 1][0] ?? false)
        ) {
            return '';
        }
        //Не анализируем вертикальные примыкающие буквы
        $regexp = '';
        $lastLetter = '';
        $step = 1;
        while ($desk[$x - $step][$y][0]) {
            $lastLetter = static::$langClass::$bukvy[self::getFishkaCode($desk[$x - $step][$y][1])][0] . $lastLetter;
            $maxWordLen++;
            $step++;
        }
        //print '-' . $x . '-' . $y . '-' . $lastLetter . '-';
        $regexp = self::makeRegexp($fishki);

        $zapros = '';

        if ($maxLeftBeforeLen = self::maxToLeft($x - mb_strlen($lastLetter, 'UTF-8') - 1, $y, count($fishki), $desk)) {
            $zapros = "[$regexp]{0,$maxLeftBeforeLen}";
            $maxWordLen += $maxLeftBeforeLen;
        }

        if ($maxWordLen > 7) {
            $maxWordLen = 7;
        }//так работает индекс

        $zapros = "select slovo from dict where (slovo REGEXP \"^$zapros{$lastLetter}[$regexp]{0,$maxLen}";
        $zapros .= "$\") AND NOT deleted = 1  AND length<=$maxWordLen ORDER BY length ASC";

        print $zapros . 'SPRAVA';
        $xLastLetter = $x - mb_strlen($lastLetter, 'UTF-8');
        if ($res = DB::queryArray($zapros)) {
            foreach ($res as $row) {
                if (!isset(
                        $slovaPlayed[$row['slovo'] = mb_strtolower(
                            $row['slovo'],
                            'UTF-8'
                        )]
                    ) && self::checkWordFishki(
                        $fishki,
                        $row['slovo'],
                        $lastLetter,
                        $lettersZvezd
                    )) {
                    $cells = $desk;
                    $slovoNach = mb_strpos($row['slovo'], $lastLetter, 0, 'UTF-8');
                    $lastLetterLen = mb_strlen($lastLetter, 'UTF-8');
                    for ($k = 0; $k < $slovoNach; $k++) {
                        $cells[$xLastLetter - $k - 1][$y][0] = true;
                        $cells[$xLastLetter - $k - 1][$y][1] = static::$langClass::getLetterCode(
                            $letter = mb_substr($row['slovo'], $slovoNach - 1 - $k, 1, 'UTF-8')
                        );

                        if (isset($lettersZvezd[$letter])) {
                            // Указали на занятую звездочку
                            $cells[$xLastLetter - $k - 1][$y][2] = $lettersZvezd[$letter]['code'];
                            if (--$lettersZvezd[$letter]['count'] == 0) {
                                unset($lettersZvezd[$letter]);
                            }
                        }
                    }
                    for ($k = 0; $k <= mb_strlen($row['slovo'], 'UTF-8') - $slovoNach - $lastLetterLen - 1; $k++) {
                        $cells[$x + $k][$y][0] = true;
                        $cells[$x + $k][$y][1] = static::$langClass::getLetterCode(
                            $letter = mb_substr($row['slovo'], $slovoNach + $lastLetterLen + $k, 1, 'UTF-8')
                        );

                        if (isset($lettersZvezd[$letter])) {
                            // Указали на занятую звездочку
                            $cells[$x + $k][$y][2] = $lettersZvezd[$letter]['code'];
                            if (--$lettersZvezd[$letter]['count'] == 0) {
                                unset($lettersZvezd[$letter]);
                            }
                        }
                    }

                    try {
                        if (Ru::checkHasBadField($cells)) {
                            throw new Exception('Bad $cells during ' . __METHOD__);
                        }

                        self::printr($cells);
                    } catch (Throwable $e) {
                        LogModel::add(
                            [
                                LogModel::CATEGORY_FIELD => LogModel::CATEGORY_BOT_ERROR,
                                LogModel::MESSAGE_FIELD => json_encode(
                                    [
                                        'error' => $e->__toString(),
                                        'cells' => $cells
                                    ]
                                )
                            ]
                        );

                        continue;
                    }

                    $desk = $cells;

                    return true;
                }
            }
        }

        return '';
    }

    public static function findWordVniz($x, $y, &$desk, &$fishki, array &$slovaPlayed, $orientation = 'all')
    {
        if (!count($fishki)) {
            return '';
        }

        //пробуем вниз от xy
        $maxLen = self::maxToDown($x, $y, count($fishki), $desk);
        $maxWordLen = $maxLen;

        // Не анализируем, если есть горизонтальные примыкающие буквы
        if ($desk[$x + 1][$y][0] || $desk[$x - 1][$y][0]) {
            return '';
        }

        // Собираем буквы, которые примыкают к слову сверху
        $lastLetter = '';
        $step = 1;
        while ($desk[$x][$y - $step][0] ?? false) {
            $lastLetter = static::$langClass::$bukvy[self::getFishkaCode($desk[$x][$y - $step][1])][0] . $lastLetter;
            $maxWordLen++;
            $step++;
        }

        $regexp = self::makeRegexp($fishki);
        $zapros = '';

        if ($maxUpBeforeLen = self::maxToUp(
            $x,
            $y - mb_strlen($lastLetter, 'UTF-8') - 1,
            count($fishki),
            $desk
        )) {
            $zapros = "[$regexp]{0,$maxUpBeforeLen}";
            $maxWordLen += $maxUpBeforeLen;
        }

        if ($maxWordLen > 7) {
            $maxWordLen = 7;
        }//так работает индекс

        $zapros = "select slovo from dict where (slovo REGEXP \"^$zapros{$lastLetter}[$regexp]{0,$maxLen}";
        $zapros .= "$\") AND NOT deleted = 1  AND length<=$maxWordLen ORDER BY length ASC";;

        print $zapros . 'VNIZ';

        // Длина фрагмента из фишек уже на поле
        $lastLetterLen = mb_strlen($lastLetter, 'UTF-8');

        // Строка, с которой начинается примыкающая сверху часть слова
        $yLastLetter = $y - $lastLetterLen;

        if ($res = DB::queryArray($zapros)) {
            foreach ($res as $row) {
                if (!isset(
                        $slovaPlayed[$row['slovo'] = mb_strtolower(
                            $row['slovo'],
                            'UTF-8'
                        )]
                    ) && self::checkWordFishki($fishki, $row['slovo'], $lastLetter, $lettersZvezd)) {
                    $cells = $desk;
                    $slovoNach = mb_strpos($row['slovo'], $lastLetter, 0, 'UTF-8') ?: 0;

                    // Ставим фишки вверх от позиции выше уже существующих фишек на поле
                    for ($k = 0; $k < $slovoNach; $k++) {
                        $cells[$x][$yLastLetter - $k - 1][0] = true;
                        $cells[$x][$yLastLetter - $k - 1][1] = static::$langClass::getLetterCode(
                            $letter = mb_substr($row['slovo'], $slovoNach - 1 - $k, 1, 'UTF-8')
                        );

                        if (isset($lettersZvezd[$letter])) {
                            // Указали на занятую звездочку
                            $cells[$x][$yLastLetter - $k - 1][2] = $lettersZvezd[$letter]['code'];
                            if (--$lettersZvezd[$letter]['count'] == 0) {
                                unset($lettersZvezd[$letter]);
                            }
                        }
                    }

                    // Ставим фишки винз от текущей позиции
                    for ($k = 0; $k <= mb_strlen($row['slovo'], 'UTF-8') - $slovoNach - $lastLetterLen - 1; $k++) {
                        $cells[$x][$y + $k][0] = true;
                        $cells[$x][$y + $k][1] = static::$langClass::getLetterCode(
                            $letter = mb_substr($row['slovo'], $slovoNach + $lastLetterLen + $k, 1, 'UTF-8')
                        );

                        if (isset($lettersZvezd[$letter])) {
                            // Указали на занятую звездочку
                            $cells[$x][$y + $k][2] = $lettersZvezd[$letter]['code'];
                            if (--$lettersZvezd[$letter]['count'] == 0) {
                                unset($lettersZvezd[$letter]);
                            }
                        }
                    }

                    try {
                        if (Ru::checkHasBadField($cells)) {
                            throw new Exception('Bad $cells during ' . __METHOD__);
                        }

                        self::printr($cells);
                    } catch (Throwable $e) {
                        LogModel::add(
                            [
                                LogModel::CATEGORY_FIELD => LogModel::CATEGORY_BOT_ERROR,
                                LogModel::MESSAGE_FIELD => json_encode(
                                    [
                                        'error' => $e->__toString(),
                                        'cells' => $cells
                                    ]
                                )
                            ]
                        );

                        continue;
                    }

                    $desk = $cells;

                    return true;
                }
            }
        }

        return '';
    }

    public static function makeRegexp($fishki)
    {
        $regexp = '';
        $numZvezd = 0;
        foreach ($fishki as $num => $code) {
            if ($code < 999) {
                $regexp .= static::$langClass::$bukvy[$code][0];
            } else {
                $regexp .= static::zvezdaRegexp(++$numZvezd);
            }
        }

        return $regexp;
    }

    public static function zvezdaRegexp($numZvezd)
    {
        if ($numZvezd <= 1) {
            return 'qzjxk';
        } else {
            return 'aeiostr';
        }
    }

    public static function checkWordFishki(&$fishki, &$word, $lastLetter, &$lettersZvezd = [])
    {
        $fishki1 = $fishki;
        $word = mb_strtolower($word, 'UTF-8');
        $lettersLastLetter = mb_str_split($lastLetter, 1, 'UTF-8');
        $lettersWord = mb_str_split($word, 1, 'UTF-8');
        $lettersZvezd = [];

        // todo remove after CLUB-268
        print 'Слово:';
        print_r($lettersWord);
        print ' Буквы на поле:';
        print_r($lettersLastLetter);
        print ' Фишки:';
        print_r($fishki1);

        foreach ($lettersWord as $numLetter => $letter) {
            foreach ($lettersLastLetter as $num => $lastLetter) {
                if ($letter == $lastLetter) {
                    $lettersLastLetter[$num] = '';
                    $letter = '';
                }
            }

            if ($letter !== '') {
                foreach ($fishki1 as $num => $fishka) {
                    print '!!!-' . $fishka . '-!!!';
                    if ($letter === static::$langClass::$bukvy[self::getFishkaCode($fishka)][0]) {
                        unset($fishki1[$num]);
                        $letter = '';
                    }
                }
            }

            if ($letter !== '') {
                foreach ($fishki1 as $num => $fishka) {
                    if ($fishka >= 999) {
                        $lettersWord[$numLetter] = mb_strtoupper($letter, 'UTF-8');
                        if ($fishka > 999) {
                            if (isset($lettersZvezd[$lettersWord[$numLetter]])) {
                                $lettersZvezd[$lettersWord[$numLetter]]['count']++;
                            } else {
                                // У нас звезда под кодом буквы
                                $lettersZvezd[$lettersWord[$numLetter]]['code'] = $fishka - 999 - 1; // У нас звезда под кодом буквы
                                $lettersZvezd[$lettersWord[$numLetter]]['count'] = 1;
                            }
                        }

                        unset($fishki1[$num]);
                        //Признак что буква со звездочкой
                        $letter = '';
                        break;
                    }
                }
            }

            if ($letter !== '') {
                //print 'Лишняя буква: ' . $letter;
                //print 'Слово:';
                //print_r($lettersWord);
                //print ' Буквы на поле:';
                //print_r($lettersLastLetter);
                //print ' Фишки:';
                //print_r($fishki1);
                return false;
            }
        }
        //print 'Слово:';
        //print_r($lettersWord);
        //print ' Буквы на поле:';
        //print_r($lettersLastLetter);
        //print ' Фишки:';
        //print_r($fishki1);
        //$word = implode('', $lettersWord);
        $fishki = $fishki1;

        // Собираем обратно слово с большими буквами для определения звезд CLUB-268
        $word = implode('', $lettersWord);

        return true;
    }

    public static function maxToLeft($x, $y, $countFishki, &$desk)
    {
        $max = 0;
        //if ($x == 0) return 1;
        for (
            $i = $x;
            ($i >= 0)
            && ($i > 0 ? (!$desk[$i - 1][$y][0]) : true)
            && !$desk[$i][$y][0]
            && !($desk[$i][$y + 1][0] ?? true)
            && !($desk[$i][$y - 1][0] ?? true)
            && !($desk[$i - 1][$y - 1][0] ?? true)
            && !($desk[$i + 1][$y - 1][0] ?? true);
            $i--
        ) {
            $max++;
        }

        return ($max > $countFishki ? $countFishki : $max);
    }

    public static function maxToUp($x, $y, $countFishki, &$desk)
    {
        $max = 0;

        for (
            $j = $y;
            ($j >= 0)
            && (($j > 0) && ($desk[$x][$j - 1][0] == false))
            && !$desk[$x][$j][0]
            && !($desk[$x + 1][$j][0] ?? true)
            && !($desk[$x - 1][$j][0] ?? true)
            && !($desk[$x - 1][$j - 1][0] ?? true)
            && !($desk[$x + 1][$j - 1][0] ?? true);
            $j--
        ) {
            $max++;
        }

        return ($max > $countFishki ? $countFishki : $max);
    }

    public static function maxToRight($x, $y, $countFishki, &$desk)
    {
        //if ($x == 14) return 1;
        $max = 0;

        for (
            $i = $x;
            ($i <= 14)
            && !($desk[$i + 1][$y][0] ?? true)
            && !($desk[$i][$y][0] ?? true)
            && !($desk[$i + 1][$y][0] ?? true)
            && !($desk[$i][$y + 1][0] ?? true)
            && !($desk[$i][$y - 1][0] ?? true)
            && !($desk[$i + 1][$y + 1][0] ?? true)
            && !($desk[$i + 1][$y - 1][0] ?? true);
            $i++
        ) {
            $max++;
        }

        return ($max > $countFishki ? $countFishki : $max);
    }

    public static function maxToDown($x, $y, $countFishki, &$desk)
    {
        $max = 0;

        for (
            $j = $y;
            ($j <= 14)
            && !($desk[$x][$j + 1][0] ?? false) // $j==14 - точно нет буквы на 15й клетке
            && !$desk[$x][$j][0]
            && ((($desk[$x + 1][$j][0] ?? true) === false) || $x === 14)
            && ((($desk[$x - 1][$j][0] ?? true) === false) || $x === 0)
            && ((($desk[$x - 1][$j + 1][0] ?? true) === false) && $x > 0 && $j < 14)
            && ((($desk[$x + 1][$j + 1][0] ?? true) === false) && $x < 14 && $j < 14);
            $j++
        ) {
            $max++;
        }

        return ($max > $countFishki ? $countFishki : $max);
    }

    /**
     * Если фишка со звездочкой (>999), то возвращаем чистый код буквы - без зведочки
     * @param int $code
     * @return int
     */
    private static function getFishkaCode(int $code): int
    {
        return $code < 999
            ? $code
            : ($code - 999 - 1);
    }

}

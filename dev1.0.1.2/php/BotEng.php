<?php

use Lang\Eng;

class BotEng
{
    private static int $minutesToGo = 5;
    private static $langClass = Eng::class;
    private static $lang = 'EN';
    /**
     * @var false|float|int|mixed|string
     */
    private static $thinkEndTime;

    const BOT_GAMES = 'erudit.botEN_games';

    public static function Run()
    {
        $_SERVER['DOCUMENT_ROOT'] = '/var/www/erudit.club';
        set_time_limit(static::$minutesToGo * 60 + 25);
        $start_script_time = date('U');
        $script_work_time = static::$minutesToGo * 60 - 5;
        $secondsToBotRefresh = 10;

        $red = Cache::getInstance();

        $botsTurns = [];
        $botTimes = [];
        while ((date('U') - $start_script_time) < $script_work_time) {
            if ($Bot = $red->redis->lpop(static::BOT_GAMES)) {
                $_COOKIE[Cookie::COOKIE_NAME] = $Bot;

                $resp = ['gameState' => 1];
                $zaprosNum = 3;


                $_GET['queryNumber'] = $zaprosNum++;
                $_GET['lang'] = static::$lang;
                ob_start();
                $resp = include __DIR__ . '/status_checker.php';
                //$resp = json_decode(ob_get_contents(), true);
                ob_end_clean();

                $resp = json_decode($resp, true);
                print_r($resp); //sleep(4);

                if (($resp['gameState'] == 'gameResults') || ($resp['gameState'] == 'initGame')) {
                    ob_start();
                    $resp = include __DIR__ . '/exit_game.php';
                    ob_end_clean();
                    //Не будем анализировать ответы!)) - просто новая игра
                    unset($botsTurns[$Bot]);
                    unset($botTimes[$Bot]);
                    $red->redis->hdel('erudit.bot_v3_list', $Bot);

                    continue;
                } else {
                    $red->redis->rpush(static::BOT_GAMES, $Bot);
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
                            $botsTurns[$Bot] = $resp['gameSubState'];

                            $turn_submit = self::sendResponse($resp);
                            $turn_submit = json_decode($turn_submit, true);
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


    private static function changeFishkiBot(&$data)
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

    private static function sendResponse(&$data)
    {
        if (isset($data['desk'])) {
            $_POST['cells'] = $data['desk'];
            $slovaPlayed = ($obj = new Erudit\Game())->gameWordsPlayed();
            $obj->botUnlock(); // разблокировали состояние игры
        } else {
            $_POST['cells'] = static::$langClass::init_desk();
            $slovaPlayed = [];
        }

        error_reporting(E_ALL & ~E_NOTICE);
        ini_set('display_errors', 0);
        try {
            if (self::makeTurn($_POST['cells'], $data['fishki'], $slovaPlayed)) {
                print "++++++++++Submiting turn...........";
                $_POST['cells'] = json_encode($_POST['cells']);
                ob_start();
                $resp = include __DIR__ . '/turn_submitter.php';
                ob_end_clean();
                print $resp;

                return $resp;
            } else {
                return self::changeFishkiBot($data);
            }
        } catch (Throwable $e) {
            print $e->__toString();
        }
    }


    private static function makeTurn(&$desk, &$fishki, &$slovaPlayed): bool
    {
        // error_reporting(E_ALL & ~E_NOTICE);  ini_set('display_errors', 0);
        @ob_end_clean();
        $fishki1 = $fishki;
        $word = '';
        /* Не забирать звезды с поля
        for ($j = 0; $j <= 14; $j++) {
            for ($i = 0; $i <= 14; $i++) {
                if ($desk[$i][$j][0] && ($desk[$i][$j][1] > 999)) {
                    foreach ($fishki as $num => $fishka) {
                        if (($fishka + 999 + 1) === $desk[$i][$j][1]) {
                            $desk[$i][$j][2] = $fishka;
                            $fishki[$num] = 999 + 1 + $fishka;//$desk[$i][$j][1];

                            break;
                        }
                    }
                }
            }
        }
        //Собрали звезды с поля
        */

        for ($k = 0; $k < 2; $k++) {// 2 прохода
            //$j - строки, $i - столбцы
            for ($j = 0; $j <= 14; $j++) {
                for ($i = 0; $i <= 14; $i++) {
                    if (($i == 7) && ($j == 7) && !$desk[$i][$j][0]) {
                        if (date('U') < self::$thinkEndTime) {
                            self::findWordSleva($i, $j, $desk, $fishki, $slovaPlayed);
                        }
                    }

                    if (!$desk[$i][$j][0] && (isset($desk[$i][$j - 1]) && $desk[$i][$j - 1][0])) {
                        //print $i.$j.$desk[$i+1][$j][1];
                        $ff = '';//для временного отключения поиска слов вниз
                        if (date('U') < self::$thinkEndTime) {
                            self::findWordVniz($i, $j, $desk, $fishki, $slovaPlayed);
                        }
                        //Ищем слова по вертикали (пока) начинающиеся на $j-1...
                    }

                    if (!$desk[$i][$j][0] && ($desk[$i + 1][$j][0] ?? false)) {
                        $ff = '';//для временного отключения поиска слов слева
                        if (date('U') < self::$thinkEndTime) {
                            self::findWordSleva($i, $j, $desk, $fishki, $slovaPlayed);
                        }
                        //Ищем слова по горизонтали (пока) заканчивающиеся на $i+1...
                    }

                    if (!$desk[$i][$j][0] && (isset($desk[$i][$j + 1]) && $desk[$i][$j + 1][0])) {
                        $ff = '';//для временного отключения поиска слов сверху
                        if (date('U') < self::$thinkEndTime) {
                            self::findWordSverhu($i, $j, $desk, $fishki, $slovaPlayed);
                        }
                        //Ищем слова по вертикали (пока) заканчивающиеся на $j+1...
                    }

                    if (!$desk[$i][$j][0] && ($desk[$i - 1][$j][0] ?? false)) {
                        $ff = '';//для временного отключения поиска слов справа
                        if (date('U') < self::$thinkEndTime) {
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


    private static function printr(&$cells)
    {
        for ($j = 0; $j <= 14; $j++) {
            for ($i = 0; $i <= 14; $i++) {
                if (($i == $j) && !$cells[$i][$j][0]) {
                    print ($i % 10);
                } elseif ($cells[$i][$j][0]) {
                    print static::$langClass::$bukvy[$cells[$i][$j][1] < 999 ? $cells[$i][$j][1] : $cells[$i][$j][1] - 999 - 1][0];
                } else {
                    print '.';
                }
            }
            print "\n";
        }
        //sleep(1);
    }

    private static function findWordSleva($x, $y, &$desk, &$fishki, array &$slovaPlayed, $orientation = 'all')
    {
        if (!count($fishki)) {
            return '';
        }

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
        $regexp = '';
        $lastLetter = '';
        $step = 1;
        while ($desk[$x + $step][$y][0]) {
            $lastLetter .= static::$langClass::$bukvy[($desk[$x + $step][$y][1] < 999 ? $desk[$x + $step][$y][1] : $desk[$x + $step][$y][1] - 999 - 1)][0];
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
        }//так работает индекс
        /*Как идея ограничивать мах длину слова, но она будет почти всегда 8+
        if ( ($maxWordLen - $maxRightAfterLen - $maxLen + count($fishki)) < $maxWordLen )
            $maxWordLen = $maxWordLen - $maxRightAfterLen - $maxLen + count($fishki);
        */

        $zapros .= "$\") AND NOT deleted = 1 AND slovo != '$lastLetter' AND length<=$maxWordLen AND lng = 2 ORDER BY length ASC";
        //print $zapros . 'SLEVA'; //sleep (5);

        if ($res = DB::queryArray($zapros)) {
            foreach ($res as $row) {
                if (!isset($slovaPlayed[$row['slovo'] = mb_strtolower($row['slovo'], 'UTF-8')])
                    &&
                    self::checkWordFishki($fishki, $row['slovo'], $lastLetter, $lettersZvezd)) {
                    $cells = $desk;
                    $slovoNach = ($lastLetter === '' ? 0 : mb_strpos($row['slovo'], $lastLetter, 0, 'UTF-8'));
                    $lastLetterLen = ($lastLetter === '' ? 0 : mb_strlen($lastLetter, 'UTF-8'));
                    for ($k = 0; $k < $slovoNach; $k++) {
                        $cells[$x - $k][$y][0] = true;
                        $cells[$x - $k][$y][1] = static::$langClass::getLetterCode(
                            $letter = mb_substr($row['slovo'], $slovoNach - 1 - $k, 1, 'UTF-8')
                        );
                        if (isset($lettersZvezd[$letter])) {
                            $cells[$x - $k][$y][2] = $lettersZvezd[$letter];
                        }//Указали на занятую звездочку
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
                            $cells[$x + $k - $slovoNach + 1 + $delta][$y][2] = $lettersZvezd[$letter];
                        } // Указали на занятую звездочку
                    }
                    self::printr($cells);
                    //sleep(2);
                    $desk = $cells;

                    return true;
                    //return ['word'=>$row['slovo'],'lastLettersNum'=>mb_strlen($lastLetter,'UTF-8')];
                }
            }
        }

        return '';
    }

    private static function findWordSverhu($x, $y, &$desk, &$fishki, array &$slovaPlayed, $orientation = 'all')
    {
        if (!count($fishki)) {
            return '';
        }
        //пробуем вверх от xy
        $maxLen = self::maxToUp($x, $y, count($fishki), $desk);
        $maxWordLen = $maxLen;

        if ($desk[$x + 1][$y][0] || $desk[$x - 1][$y][0]) {
            return '';
        }
        //Не анализируем горизонтальные примыкающие буквы
        $regexp = '';
        $lastLetter = '';
        $step = 1;
        while ($desk[$x][$y + $step][0] ?? false) {
            $lastLetter .= static::$langClass::$bukvy[($desk[$x][$y + $step][1] < 999 ? $desk[$x][$y + $step][1] : $desk[$x][$y + $step][1] - 999 - 1)][0];
            $maxWordLen++;
            $step++;
        }
        //Собрали буквы, к которым примыкаем слово сверху

        //print '-' . $x . '-' . $y . '-' . $lastLetter . '-';

        $regexp = self::makeRegexp($fishki);
        $zapros = "select slovo from dict where (slovo REGEXP \"^[$regexp]{0,$maxLen}$lastLetter";
        if (($maxDownAfterLen = self::maxToDown(
                $x,
                $y + mb_strlen($lastLetter, 'UTF-8') + 1,
                count($fishki),
                $desk
            )) || (($x == 7) && ($y == 7))) {
            $zapros .= "[$regexp]{0,$maxDownAfterLen}";
            $maxWordLen += $maxDownAfterLen;
        }

        if ($maxWordLen > 7) {
            $maxWordLen = 7;
        }//так работает индекс

        $zapros .= "$\") AND NOT deleted = 1  AND length<=$maxWordLen AND lng = 2 ORDER BY length ASC";;
        //print $zapros . 'SVERHU'; //sleep (5);

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
                    $lastLetterLen = ($lastLetter === '' ? 0 : mb_strlen($lastLetter, 'UTF-8'));
                    for ($k = 0; $k < $slovoNach; $k++) {
                        $cells[$x][$y - $k][0] = true;
                        $cells[$x][$y - $k][1] = static::$langClass::getLetterCode(
                            $letter = mb_substr($row['slovo'], $slovoNach - 1 - $k, 1, 'UTF-8')
                        );
                        if (isset($lettersZvezd[$letter])) {
                            $cells[$x][$y - $k][2] = $lettersZvezd[$letter];
                        }//Указали на занятую звездочку
                    }
                    for ($k = $slovoNach + $lastLetterLen; $k < mb_strlen($row['slovo'], 'UTF-8'); $k++) {
                        $cells[$x][$y + $k - $slovoNach + 1][0] = true;
                        $cells[$x][$y + $k - $slovoNach + 1][1] = static::$langClass::getLetterCode(
                            $letter = mb_substr($row['slovo'], $k, 1, 'UTF-8')
                        );
                        if (isset($lettersZvezd[$letter])) {
                            $cells[$x][$y + $k - $slovoNach + 1][2] = $lettersZvezd[$letter];
                        }//Указали на занятую звездочку
                    }
                    self::printr($cells);
                    $desk = $cells;
                    return true;
                    //return ['word'=>$row['slovo'],'lastLettersNum'=>mb_strlen($lastLetter,'UTF-8')];
                }
            }
        }

        return '';
    }

    private static function findWordSprava($x, $y, &$desk, &$fishki, array &$slovaPlayed, $orientation = 'all')
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
            $lastLetter = static::$langClass::$bukvy[($desk[$x - $step][$y][1] < 999 ? $desk[$x - $step][$y][1] : $desk[$x - $step][$y][1] - 999 - 1)][0] . $lastLetter;
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
        $zapros .= "$\") AND NOT deleted = 1  AND length<=$maxWordLen AND lng = 2 ORDER BY length ASC";

        //print $zapros . 'SPRAVA';
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
                            $cells[$xLastLetter - $k - 1][$y][2] = $lettersZvezd[$letter];
                        }//Указали на занятую звездочку
                    }
                    for ($k = 0; $k <= mb_strlen($row['slovo'], 'UTF-8') - $slovoNach - $lastLetterLen - 1; $k++) {
                        $cells[$x + $k][$y][0] = true;
                        $cells[$x + $k][$y][1] = static::$langClass::getLetterCode(
                            $letter = mb_substr($row['slovo'], $slovoNach + $lastLetterLen + $k, 1, 'UTF-8')
                        );
                        if (isset($lettersZvezd[$letter])) {
                            $cells[$x + $k][$y][2] = $lettersZvezd[$letter];
                        }//Указали на занятую звездочку
                    }
                    self::printr($cells);
                    $desk = $cells;
                    return true;
                    //return ['word'=>$row['slovo'],'lastLettersNum'=>mb_strlen($lastLetter,'UTF-8')];
                }
            }
        }

        return '';
    }

    private static function findWordVniz($x, $y, &$desk, &$fishki, array &$slovaPlayed, $orientation = 'all')
    {
        if (!count($fishki)) {
            return '';
        }
        //пробуем вниз от xy
        $maxLen = self::maxToDown($x, $y, count($fishki), $desk);
        $maxWordLen = $maxLen;

        if ($desk[$x + 1][$y][0] || $desk[$x - 1][$y][0]) {
            return '';
        }
        //Не анализируем вертикальные примыкающие буквы
        $regexp = '';
        $lastLetter = '';
        $step = 1;
        while ($desk[$x][$y - $step][0]) {
            $lastLetter = static::$langClass::$bukvy[($desk[$x][$y - $step][1] < 999 ? $desk[$x][$y - $step][1] : $desk[$x][$y - $step][1] - 999 - 1)][0] . $lastLetter;
            $maxWordLen++;
            $step++;
        }
        //print '-' . $x . '-' . $y . '-' . $lastLetter . '-';
        $regexp = self::makeRegexp($fishki);
        $zapros = '';

        if ($maxUpBeforeLen = self::maxToUp($x, $y - mb_strlen($lastLetter, 'UTF-8') - 1, count($fishki), $desk)) {
            $zapros = "[$regexp]{0,$maxUpBeforeLen}";
            $maxWordLen += $maxUpBeforeLen;
        }

        if ($maxWordLen > 7) {
            $maxWordLen = 7;
        }//так работает индекс

        $zapros = "select slovo from dict where (slovo REGEXP \"^$zapros{$lastLetter}[$regexp]{0,$maxLen}";
        $zapros .= "$\") AND NOT deleted = 1  AND length<=$maxWordLen AND lng = 2 ORDER BY length ASC";;

        //print $zapros . 'VNIZ';
        $yLastLetter = $y - mb_strlen($lastLetter, 'UTF-8');
        if ($res = DB::queryArray($zapros)) {
            foreach ($res as $row) {
                if (!isset(
                        $slovaPlayed[$row['slovo'] = mb_strtolower(
                            $row['slovo'],
                            'UTF-8'
                        )]
                    ) && self::checkWordFishki($fishki, $row['slovo'], $lastLetter, $lettersZvezd)) {
                    $cells = $desk;
                    $slovoNach = mb_strpos($row['slovo'], $lastLetter, 0, 'UTF-8');
                    $lastLetterLen = mb_strlen($lastLetter, 'UTF-8');
                    for ($k = 0; $k < $slovoNach; $k++) {
                        $cells[$x][$yLastLetter - $k - 1][0] = true;
                        $cells[$x][$yLastLetter - $k - 1][1] = static::$langClass::getLetterCode(
                            $letter = mb_substr($row['slovo'], $slovoNach - 1 - $k, 1, 'UTF-8')
                        );
                        if (isset($lettersZvezd[$letter])) {
                            $cells[$x][$yLastLetter - $k - 1][2] = $lettersZvezd[$letter];
                        }//Указали на занятую звездочку
                    }
                    for ($k = 0; $k <= mb_strlen($row['slovo'], 'UTF-8') - $slovoNach - $lastLetterLen - 1; $k++) {
                        $cells[$x][$y + $k][0] = true;
                        $cells[$x][$y + $k][1] = static::$langClass::getLetterCode(
                            $letter = mb_substr($row['slovo'], $slovoNach + $lastLetterLen + $k, 1, 'UTF-8')
                        );
                        if (isset($lettersZvezd[$letter])) {
                            $cells[$x][$y + $k][2] = $lettersZvezd[$letter];
                        }//Указали на занятую звездочку
                    }
                    self::printr($cells);
                    $desk = $cells;
                    return true;
                    //return ['word'=>$row['slovo'],'lastLettersNum'=>mb_strlen($lastLetter,'UTF-8')];
                }
            }
        }

        return '';
    }

    private static function makeRegexp($fishki)
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

    private static function zvezdaRegexp($numZvezd)
    {
        if ($numZvezd <= 1) {
            return 'qzjxk';
        } else {
            return 'aeiostr';
        }
    }

    private static function checkWordFishki(&$fishki, &$word, $lastLetter, &$lettersZvezd = [])
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
                    if ($letter == static::$langClass::$bukvy[($fishka < 999 ? $fishka : ($fishka - 999 - 1))][0]) {
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
                            $lettersZvezd[$lettersWord[$numLetter]] = $fishka - 999 - 1; // У нас звезда под кодом буквы
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

    private static function maxToLeft($x, $y, $countFishki, &$desk)
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

    private static function maxToUp($x, $y, $countFishki, &$desk)
    {
        //if ( $y == 0 ) return 1;
        $max = 0;

        for (
            $j = $y;
            ($j >= 0)
            && (($j > 0) && ($desk[$x][$j - 1][0] == false))
            && (!$desk[$x][$j][0])
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

    private static function maxToRight($x, $y, $countFishki, &$desk)
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

    private static function maxToDown($x, $y, $countFishki, &$desk)
    {
        $max = 0;

        for (
            $j = $y;
            ($j <= 14)
            && !($desk[$x][$j + 1][0] ?? true)
            && !$desk[$x][$j][0]
            && !($desk[$x + 1][$j][0] ?? true)
            && !($desk[$x - 1][$j][0] ?? true)
            && !($desk[$x - 1][$j + 1][0] ?? true)
            && !($desk[$x + 1][$j + 1][0] ?? true)
            && !($desk[$x + 1][$j + 1][0] ?? true)
            && !($desk[$x - 1][$j + 1][0] ?? true);
            $j++
        ) {
            $max++;
        }

        return ($max > $countFishki ? $countFishki : $max);
    }

}

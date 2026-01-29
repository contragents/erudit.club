<?php

use Erudit\Game;

class BotEng
{
    const MINUTES_TO_GO = 10;
    const ENG_LANG = 'EN';
    const RU_LANG = 'RU';
    public static $langClass = Eng::class;
    public static $lang = self::ENG_LANG;
    public static $thinkEndTime;

    protected static string $botname = '';
    public const BOT_LOG_KEY = 'erudit.bot_log';

    const LNG_ID = 2;

    const HOST_NAME = 'https://xn--d1aiwkc2d.club/';
    const DIR = 'scrabble1.0.1.6/php/';
    const CHANGE_FISHKI_SCRIPT = 'change_fishki.php';
    const STATUS_CHECKER_SCRIPT = 'status_checker.php';
    const SUBMIT_SCRIPT = 'turn_submitter.php';

    const BOT_GAMES = 'erudit.botEN_games';

    const BOT_LIST = 'erudit.bot_v3_list';

    const SLEVA = 'Sleva';
    const SPRAVA = 'Sprava';
    const VNIZ = 'Vniz';
    const SVERHU = 'Sverhu';
    const SLEVA_SVERHU = self::SLEVA . self::SVERHU;
    const SPRAVA_SVERHU = self::SPRAVA . self::SVERHU;
    const SLEVA_VNIZ = self::SLEVA . self::VNIZ;
    const SPRAVA_VNIZ = self::SPRAVA . self::VNIZ;

    public static function Run()
    {
        Cache::del(static::BOT_LOG_KEY);

        $_SERVER['DOCUMENT_ROOT'] = '/var/www/erudit.club';
        set_time_limit(self::MINUTES_TO_GO * 60 + 25);
        $start_script_time = date('U');
        $script_work_time = self::MINUTES_TO_GO * 60 - 5;
        $secondsToBotRefresh = 10;

        $botsTurns = [];
        $botTimes = [];
        while ((date('U') - $start_script_time) < $script_work_time) {
            try {
                if ($Bot = Cache::lpop(static::BOT_GAMES)) {
                    static::$botname = $Bot;
                    $_COOKIE[CookieErudit::COOKIE_NAME] = $Bot;
                    print_r($_COOKIE);
                    $resp = ['gameState' => 1];
                    $zaprosNum = 3;

                    $_GET['queryNumber'] = $zaprosNum;
                    $_GET['lang'] = static::$lang;

                    $resp = self::makeRequest(
                        static::STATUS_CHECKER_SCRIPT,
                        $Bot,
                        ['queryNumber' => $zaprosNum, 'lang' => static::$lang]
                    );

                    $resp = json_decode($resp, true);

                    print ($resp['gameState'] ?? 'no State');

                    if ($resp['gameState'] === Game::GAME_RESULTS_STATE) {
                        // Игра окончена

                        // Удаляем ссылку на игру бота
                        Queue::botExitGame($Bot);
                        unset($botsTurns[$Bot]);
                        unset($botTimes[$Bot]);

                        // Удаляем бота из списка занятых ботов
                        Cache::hdel(self::BOT_LIST, $Bot);

                        continue;
                    } else {
                        Cache::rpush(static::BOT_GAMES, $Bot);
                        //Вернули бота в список игроков

                        // todo $resp может быть null или false, если makeRequest не сработал - нужно ли это анализировать?

                        if ($resp['gameState'] == Game::MY_TURN_STATUS) {
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

                                $turn_submit = self::sendResponse($resp, $Bot);
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
            } catch (Throwable $e) {
                BadRequest::sendBadRequest(['error' => $e->__toString()], true);

                continue;
            }
        }

        exit();
    }

    protected static function makeRequest(
        string $scriptName,
        string $cookie,
        array $params = [],
        array $post = []
    ): string {
        $opts = [
            "http" => [
                    "method" => (!empty($post)) ? "POST" : "GET",
                    "header" => "Accept-language: en\r\n"
                        . "Cookie: " . CookieErudit::COOKIE_NAME . "=$cookie\r\n"
                        . ($post ? "Content-Type: application/x-www-form-urlencoded\r\n" : ''),
                ]
                + ((!empty($post)) ? ['content' => http_build_query($post)] : [])
        ];

        $context = stream_context_create($opts);

        return file_get_contents(
            static::HOST_NAME
            . static::DIR
            . $scriptName
            . ($params
                ? ('?' . implode('&', array_map(fn($param, $value) => "$param=$value", array_keys($params), $params)))
                : ''
            ),
            false,
            $context
        );
    }

    public static function changeFishkiBot()
    {
        $resp = self::makeRequest(
            static::CHANGE_FISHKI_SCRIPT,
            $_COOKIE[CookieErudit::COOKIE_NAME],
            ['lang' => static::$lang]
        );

        return $resp;
    }

    public static function sendResponse(&$data, $Bot)
    {
        if (isset($data['desk'])) {
            $desk = $data['desk'];

            $slovaPlayed = Game::staticGameWordsPlayed($Bot);
            print '$slovaPlayed';
            print_r($slovaPlayed);
        } else {
            $desk = static::$langClass::init_desk();
            $slovaPlayed = [];
        }

        try {
            if (self::makeTurn($desk, $data['fishki'], $slovaPlayed)) {
                print "++++++++++Submiting turn...........";

                $resp = self::makeRequest(
                    static::SUBMIT_SCRIPT,
                    $_COOKIE[CookieErudit::COOKIE_NAME],
                    ['lang' => static::$lang],
                    ['cells' => json_encode($desk)]
                );

                return $resp;
            } else {
                return self::changeFishkiBot();
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
        for ($k = 0; $k < 7; $k++) { // 7 проходов макс
            //$j - строки, $i - столбцы
            print 'j,i cycle; ' . PHP_EOL;

            $candidateCellsArr = []; // Массив пустых клеток-кандидатов

            for ($j = 0; $j <= 14; $j++) {
                for ($i = 0; $i <= 14; $i++) {
                    if (($i == 7) && ($j == 7) && !$desk[$i][$j][0]) {
                        $candidateCellsArr[] = new CellBot(
                            $i, $j, 1000000, self::SLEVA
                        ); // Макс. приоритет на клетку посредине поля

                        break 2;
                    }

                    // Можно приставить буквы снизу?
                    if (!$desk[$i][$j][0] && (isset($desk[$i][$j - 1]) && $desk[$i][$j - 1][0])) {
                        $candidateCellsArr[] = new CellBot(
                            $i, $j, self::getK($i, $j, $desk, self::SVERHU), self::VNIZ
                        );

                        continue; // Переходим к следующей клетке
                    }

                    // Можно приставить буквы слева?
                    if (!$desk[$i][$j][0] && ($desk[$i + 1][$j][0] ?? false)) {
                        $candidateCellsArr[] = new CellBot(
                            $i, $j, self::getK($i, $j, $desk, self::SPRAVA), self::SLEVA
                        );
                        // continue; // Переходим к следующей клетке
                    }

                    // Можно приставить буквы сверху?
                    if (!$desk[$i][$j][0] && (isset($desk[$i][$j + 1]) && $desk[$i][$j + 1][0])) {
                        $candidateCellsArr[] = new CellBot(
                            $i, $j, self::getK($i, $j, $desk, self::VNIZ), self::SVERHU
                        );
                        // continue; // Переходим к следующей клетке
                    }

                    // Можно приставить буквы справа?
                    if (!$desk[$i][$j][0] && ($desk[$i - 1][$j][0] ?? false)) {
                        $candidateCellsArr[] = new CellBot(
                            $i, $j, self::getK($i, $j, $desk, self::SLEVA), self::SPRAVA
                        );

                        continue; // Переходим к следующей клетке
                    }

                    /* todo проблемы с вылетами игры - тестить
                    // Можно приставить буквы слева-сверху?
                    if (!$desk[$i][$j][0] && ($desk[$i + 1][$j][0] ?? false) && ($desk[$i][$j + 1][0] ?? false)) {
                        $candidateCellsArr[] = new CellBot(
                            $i,
                            $j,
                            self::getK($i, $j, $desk, self::SPRAVA) + self::getK($i, $j, $desk, self::VNIZ),
                            self::SLEVA_SVERHU
                        );

                        continue; // Переходим к следующей клетке
                    }
                    */
                }
            }

            // Упорядочиваем массив кандидатов по УМЕНЬШЕНИЮ K
            uasort($candidateCellsArr, fn($a, $b) => $a->K < $b->K ? 1 : -1);

            print 'Candidates: ' . print_r($candidateCellsArr, true) . PHP_EOL;
            // Проходим весь массив подходящих пустых клеток от лучшей к худшей
            foreach ($candidateCellsArr as $cell) {
                $funcName = 'findWord' . $cell->direction;
                if (self::{$funcName}($cell->i, $cell->j, $desk, $fishki, $slovaPlayed)) {
                    // Слово найдено - прерываем обход
                    break;
                }
            }

            // Проверяем время на обдумывание хода
            if (date('U') >= self::$thinkEndTime) {
                print "date('U') >= self::thinkEndTime";

                break;
            }

            if (count($fishki) && (count($fishki1) != count($fishki))) {
                continue; // На следующий круг
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
        $result = '';

        for ($j = 0; $j <= 14; $j++) {
            for ($i = 0; $i <= 14; $i++) {
                if (($i == $j) && !$cells[$i][$j][0]) {
                    print ($i % 10);
                    $result .= ($i % 10);
                } elseif ($cells[$i][$j][0]) {
                    print static::$langClass::$bukvy[self::getFishkaCode($cells[$i][$j][1])][0];
                    $result .= static::$langClass::$bukvy[self::getFishkaCode($cells[$i][$j][1])][0];
                } else {
                    print '.';
                    $result .= '.';
                }
            }
            print "\n";
            $result .= "\n";
        }
    }

    /**
     * Рассчитывает коэффициент полезности клетки - буквы справа
     * @param $x
     * @param $y
     * @param $desk
     * @return float|null
     */
    protected static function getKRight($x, $y, &$desk): ?float
    {
        // Собираем буквы, которые примыкают к слову справа
        $sumLetter = 0; // Сумма очков за буквы с учетом зеленых/желтых клеток
        $step = 1;
        $mult = static::$langClass::$multi[$x][$y]['slovo'] ?? 1; // Коэффициент для синих/красных клеток

        // Идем вправо, пока есть занятые клетки или не кончится доска
        while ($desk[$x + $step][$y][0] ?? false) {
            $codeFishka = self::getFishkaCode($desk[$x + $step][$y][1]);
            $sumLetter += static::$langClass::$bukvy[$codeFishka][1] * (static::$langClass::$multi[$x + $step][$y]['bukva'] ?? 1);
            $mult *= static::$langClass::$multi[$x + $step][$y]['slovo'] ?? 1;

            $step++;
        }

        return $sumLetter / ($step - 1) * $mult;
    }

    public static function findWordSlevaSverhu(
        $x,
        $y,
        &$desk,
        &$fishki,
        array &$slovaPlayed
    ): bool {
        if (!count($fishki)) {
            return false;
        }

        /**
         * @var Ru|Eng $bukvy
         */

        //пробуем поставить только одну букву на перекрестье
        $maxLen = min(1, count($fishki)); //self::maxToLeft($x, $y, count($fishki), $desk);
        $maxWordLen = $maxLen;

        $lastLetter = '';
        $step = 1;
        while ($desk[$x + $step][$y][0] ?? false) {
            // Собираем коды последних букв слова
            $lastLetter .= static::$langClass::$bukvy[self::getFishkaCode($desk[$x + $step][$y][1])][0];
            $maxWordLen++;
            $step++;
        }

        $regexp = self::makeRegexp($fishki);

        $zapros = "select slovo from dict where (slovo REGEXP \"^[$regexp]{0,$maxLen}$lastLetter";

        if ($maxRightAfterLen = self::maxToRight(
            $x + mb_strlen($lastLetter, 'UTF-8') + 1,
            $y,
            count($fishki),
            $desk
        )) {
            $zapros .= "[$regexp]{0,$maxRightAfterLen}";
            $maxWordLen += $maxRightAfterLen;
        }

        $andLng = 'AND lng=' . static::LNG_ID;
        $zapros .= "$\") AND NOT deleted = 1 AND slovo != '$lastLetter' AND length<=$maxWordLen $andLng ORDER BY word_score/length DESC";

        if ($res = DB::queryArray($zapros)) {
            foreach ($res as $row) {
                $fishkiLeft = $fishki;
                $cellsLeft = $desk; // создали временную копию доски для попытки составить слово

                $row['slovo'] = mb_strtolower($row['slovo'], 'UTF-8');
                if (
                    !isset($slovaPlayed[$row['slovo']])
                    &&
                    self::checkWordFishki($fishkiLeft, $row['slovo'], $lastLetter, $lettersZvezd)
                ) {
                    $slovoNach = ($lastLetter === '' ? 0 : mb_strpos($row['slovo'], $lastLetter, 0, 'UTF-8'));
                    $lastLetterLen = ($lastLetter === '' ? 0 : mb_strlen($lastLetter, 'UTF-8'));
                    for ($k = 0; $k < $slovoNach; $k++) {
                        $letter = mb_substr($row['slovo'], $slovoNach - 1 - $k, 1, 'UTF-8');

                        if ($cellsLeft[$x - $k][$y][0] === false) {
                            $cellsLeft[$x - $k][$y][0] = true;
                            $cellsLeft[$x - $k][$y][1] = static::$langClass::getLetterCode($letter);

                            if (
                                isset($lettersZvezd[$letter])
                                && count($lettersZvezd[$letter])
                            ) {
                                reset($lettersZvezd[$letter]);
                                // Указали на занятую звездочку
                                // $cells[$x - $k][$y][2] = current($lettersZvezd[$letter]);

                                unset($lettersZvezd[$letter][key($lettersZvezd[$letter])]);
                            }
                        }
                    }


                    for ($k = $slovoNach + $lastLetterLen; $k < mb_strlen($row['slovo'], 'UTF-8'); $k++) {
                        $letter = mb_substr($row['slovo'], $k, 1, 'UTF-8');

                        if ($cellsLeft[$x + $k - $slovoNach + 1][$y][0] === false) {
                            $cellsLeft[$x + $k - $slovoNach + 1][$y][0] = true;
                            $cellsLeft[$x + $k - $slovoNach + 1][$y][1] = static::$langClass::getLetterCode(
                                $letter
                            );

                            if (
                                isset($lettersZvezd[$letter])
                                && count($lettersZvezd[$letter])
                            ) {
                                reset($lettersZvezd[$letter]);
                                // Указали на занятую звездочку
                                //$cells[$x + $k - $slovoNach + 1][$y][2] = current($lettersZvezd[$letter]);

                                unset($lettersZvezd[$letter][key($lettersZvezd[$letter])]);
                            }
                        }
                    }

                    try {
                        if (Ru::checkHasBadField($cellsLeft)) {
                            throw new Exception('Bad $cells during ' . __METHOD__);
                        }

                        print PHP_EOL . 'Sleva->' . PHP_EOL;
                        self::printr($cellsLeft);
                    } catch (Throwable $e) {
                        LogModel::add(
                            [
                                LogModel::CATEGORY_FIELD => LogModel::CATEGORY_BOT_ERROR,
                                LogModel::MESSAGE_FIELD => json_encode(
                                    [
                                        'error' => $e->__toString(),
                                        'cells' => $cellsLeft
                                    ]
                                )
                            ]
                        );

                        continue;
                    }

                    // Готовим переменные для поиска слова сверху
                    $fishkiUp = [$cellsLeft[$x][$y][1] % 1000]; // Передаем для поиска только одну букву
                    // Подделаем поле, как будто клетки свободны.
                    // Чтобы использовать стандартный метод findWordSverhu
                    $cellsUp = $cellsLeft;
                    $cellsUp[$x + 1][$y][0] = false;
                    $cellsUp[$x + 1][$y][1] = false;
                    $cellsUp[$x - 1][$y][0] = false;
                    $cellsUp[$x - 1][$y][1] = false;

                    if (self::findWordSverhu($x, $y, $cellsUp, $fishkiUp, $slovaPlayed)) {
                        $desk = $cellsLeft;
                        $fishki = $fishkiLeft;

                        return true;
                    }
                }
            }
        }

        return false;
    }

    public static function findWordSleva($x, $y, &$desk, &$fishki, array &$slovaPlayed)
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
        while ($desk[$x + $step][$y][0] ?? false) {
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

        $andLng = 'AND lng=' . static::LNG_ID;
        $zapros .= "$\") AND NOT deleted = 1 AND slovo != '$lastLetter' AND length<=$maxWordLen $andLng ORDER BY word_score/length DESC";

        if ($res = DB::queryArray($zapros)) {
            foreach ($res as $row) {
                $row['slovo'] = mb_strtolower($row['slovo'], 'UTF-8');
                if (
                    !isset($slovaPlayed[$row['slovo']])
                    &&
                    self::checkWordFishki($fishki, $row['slovo'], $lastLetter, $lettersZvezd)
                ) {
                    $cells = $desk; // создали временную копию доски для попытки составить слово

                    $slovoNach = ($lastLetter === '' ? 0 : mb_strpos($row['slovo'], $lastLetter, 0, 'UTF-8'));
                    $lastLetterLen = ($lastLetter === '' ? 0 : mb_strlen($lastLetter, 'UTF-8'));
                    for ($k = 0; $k < $slovoNach; $k++) {
                        $letter = mb_substr($row['slovo'], $slovoNach - 1 - $k, 1, 'UTF-8');

                        if ($cells[$x - $k][$y][0] === false) {
                            $cells[$x - $k][$y][0] = true;
                            $cells[$x - $k][$y][1] = static::$langClass::getLetterCode($letter);

                            if (
                                isset($lettersZvezd[$letter])
                                && count($lettersZvezd[$letter])
                            ) {
                                reset($lettersZvezd[$letter]);
                                // Указали на занятую звездочку
                                // $cells[$x - $k][$y][2] = current($lettersZvezd[$letter]);

                                unset($lettersZvezd[$letter][key($lettersZvezd[$letter])]);
                            }
                        }
                    }

                    if (($x == 7) && ($y == 7)) {
                        $delta = -1;
                    } else {
                        $delta = 0;
                    }

                    for ($k = $slovoNach + $lastLetterLen; $k < mb_strlen($row['slovo'], 'UTF-8'); $k++) {
                        $letter = mb_substr($row['slovo'], $k, 1, 'UTF-8');

                        if ($cells[$x + $k - $slovoNach + 1 + $delta][$y][0] === false) {
                            $cells[$x + $k - $slovoNach + 1 + $delta][$y][0] = true;
                            $cells[$x + $k - $slovoNach + 1 + $delta][$y][1] = static::$langClass::getLetterCode(
                                $letter
                            );

                            if (
                                isset($lettersZvezd[$letter])
                                && count($lettersZvezd[$letter])
                            ) {
                                reset($lettersZvezd[$letter]);
                                // Указали на занятую звездочку
                                //$cells[$x + $k - $slovoNach + 1 + $delta][$y][2] = current($lettersZvezd[$letter]);

                                unset($lettersZvezd[$letter][key($lettersZvezd[$letter])]);
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

    /**
     * Рассчитывает коэффициент полезности клетки - буквы снизу
     * @param $x
     * @param $y
     * @param $desk
     * @return float|null
     */
    protected static function getKDown($x, $y, &$desk): ?float
    {
        // Собираем буквы, которые примыкают к слову снизу
        $sumLetter = 0; // Сумма очков за буквы с учетом зеленых/желтых клеток
        $step = 1;
        $mult = static::$langClass::$multi[$x][$y]['slovo'] ?? 1; // Коэффициент для синих/красных клеток

        // Идем вниз, пока есть занятые клетки или не кончится доска
        while ($desk[$x][$y + $step][0] ?? false) {
            $codeFishka = self::getFishkaCode($desk[$x][$y + $step][1]);
            $sumLetter += static::$langClass::$bukvy[$codeFishka][1] * (static::$langClass::$multi[$x][$y + $step]['bukva'] ?? 1);
            $mult *= static::$langClass::$multi[$x][$y + $step]['slovo'] ?? 1;

            $step++;
        }

        return $sumLetter / ($step - 1) * $mult;
    }

    public static function findWordSverhu(
        $x,
        $y,
        &$desk,
        &$fishki,
        array &$slovaPlayed
    ) {
        if (!count($fishki)) {
            return '';
        }

        // Не анализируем, если есть горизонтальные примыкающие буквы
        if (($desk[$x + 1][$y][0] ?? false) || ($desk[$x - 1][$y][0] ?? false)) {
            return '';
        }

        //пробуем вверх от xy
        $maxLen = self::maxToUp($x, $y, count($fishki), $desk);
        $maxWordLen = $maxLen;

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

        $andLng = 'AND lng=' . static::LNG_ID;
        $zapros .= "$\") AND NOT deleted = 1  AND length<=$maxWordLen $andLng ORDER BY word_score/length DESC";;

        // Длина фрагмента из фишек уже на поле
        $lastLetterLen = ($lastLetter === '' ? 0 : mb_strlen($lastLetter, 'UTF-8'));

        if ($res = DB::queryArray($zapros)) {
            foreach ($res as $row) {
                $row['slovo'] = mb_strtolower(
                    $row['slovo'],
                    'UTF-8'
                );
                if (!isset($slovaPlayed[$row['slovo']])
                    && self::checkWordFishki(
                        $fishki,
                        $row['slovo'],
                        $lastLetter,
                        $lettersZvezd
                    )) {
                    $cells = $desk;
                    $slovoNach = ($lastLetter === '' ? 0 : mb_strpos($row['slovo'], $lastLetter, 0, 'UTF-8'));

                    // Ставим фишки вверх от текущей позиции
                    for ($k = 0; $k < $slovoNach; $k++) {
                        if ($cells[$x][$y - $k][0] === false) {
                            $cells[$x][$y - $k][0] = true;
                            $cells[$x][$y - $k][1] = static::$langClass::getLetterCode(
                                $letter = mb_substr($row['slovo'], $slovoNach - 1 - $k, 1, 'UTF-8')
                            );

                            if (
                                isset($lettersZvezd[$letter])
                                && count($lettersZvezd[$letter])
                            ) {
                                reset($lettersZvezd[$letter]);
                                // Указали на занятую звездочку
                                // $cells[$x][$y - $k][2] = current($lettersZvezd[$letter]);

                                unset($lettersZvezd[$letter][key($lettersZvezd[$letter])]);
                            }
                        }
                    }

                    // Ставим фишки вниз от позиции ниже уже существующих фишек на поле
                    for ($k = $slovoNach + $lastLetterLen; $k < mb_strlen($row['slovo'], 'UTF-8'); $k++) {
                        $letter = mb_substr($row['slovo'], $k, 1, 'UTF-8');

                        if ($cells[$x][$y + $k - $slovoNach + 1][0] === false) {
                            $cells[$x][$y + $k - $slovoNach + 1][0] = true;
                            $cells[$x][$y + $k - $slovoNach + 1][1] = static::$langClass::getLetterCode($letter);

                            if (
                                isset($lettersZvezd[$letter])
                                && count($lettersZvezd[$letter])
                            ) {
                                reset($lettersZvezd[$letter]);
                                // Указали на занятую звездочку
                                // $cells[$x][$y + $k - $slovoNach + 1][2] = current($lettersZvezd[$letter]);

                                unset($lettersZvezd[$letter][key($lettersZvezd[$letter])]);
                            }
                        }
                    }

                    try {
                        if (Ru::checkHasBadField($cells)) {
                            throw new Exception('Bad $cells during ' . __METHOD__);
                        }

                        print PHP_EOL . 'Sverhu' . PHP_EOL;
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

    /**
     * Рассчитывает коэффициент полезности клетки - буквы слева
     * @param $x
     * @param $y
     * @param $desk
     * @return float|null
     */
    protected static function getKLeft($x, $y, &$desk): ?float
    {
        // Собираем буквы, которые примыкают к слову слева
        $sumLetter = 0; // Сумма очков за буквы с учетом зеленых/желтых клеток
        $step = 1;
        $mult = static::$langClass::$multi[$x][$y]['slovo'] ?? 1; // Коэффициент для синих/красных клеток

        // Идем влево, пока есть занятые клетки или не кончится доска
        while ($desk[$x - $step][$y][0] ?? false) {
            $codeFishka = self::getFishkaCode($desk[$x - $step][$y][1]);
            $sumLetter += static::$langClass::$bukvy[$codeFishka][1] * (static::$langClass::$multi[$x - $step][$y]['bukva'] ?? 1);
            $mult *= static::$langClass::$multi[$x - $step][$y]['slovo'] ?? 1;

            $step++;
        }

        return $sumLetter / ($step - 1) * $mult;
    }

    /**
     * @param $x
     * @param $y
     * @param $desk
     * @param $fishki
     * @param array $slovaPlayed
     * @param $orientation
     * @return string|true
     */
    public static function findWordSprava(
        $x,
        $y,
        &$desk,
        &$fishki,
        array &$slovaPlayed,
        $orientation = 'all'
    ) {
        if (!count($fishki)) {
            return '';
        }
        //пробуем справа от xy
        $maxLen = self::maxToRight($x, $y, count($fishki), $desk);
        $maxWordLen = $maxLen;

        //Не анализируем вертикальные примыкающие буквы
        if (
            ($desk[$x][$y + 1][0] ?? false)
            ||
            ($desk[$x][$y - 1][0] ?? false)
        ) {
            return '';
        }

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
        $andLng = 'AND lng=' . static::LNG_ID;
        $zapros .= "$\") AND NOT deleted = 1  AND length<=$maxWordLen $andLng ORDER BY word_score/length DESC";

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
                        $letter = mb_substr($row['slovo'], $slovoNach - 1 - $k, 1, 'UTF-8');

                        if ($cells[$xLastLetter - $k - 1][$y][0] === false) {
                            $cells[$xLastLetter - $k - 1][$y][0] = true;
                            $cells[$xLastLetter - $k - 1][$y][1] = static::$langClass::getLetterCode($letter);

                            if (
                                isset($lettersZvezd[$letter])
                                && count($lettersZvezd[$letter])
                            ) {
                                reset($lettersZvezd[$letter]);
                                // Указали на занятую звездочку
                                // $cells[$xLastLetter - $k - 1][$y][2] = current($lettersZvezd[$letter]);

                                unset($lettersZvezd[$letter][key($lettersZvezd[$letter])]);
                            }
                        }
                    }

                    for ($k = 0; $k <= mb_strlen($row['slovo'], 'UTF-8') - $slovoNach - $lastLetterLen - 1; $k++) {
                        $letter = mb_substr($row['slovo'], $slovoNach + $lastLetterLen + $k, 1, 'UTF-8');

                        if ($cells[$x + $k][$y][0] === false) {
                            $cells[$x + $k][$y][0] = true;
                            $cells[$x + $k][$y][1] = static::$langClass::getLetterCode($letter);

                            if (
                                isset($lettersZvezd[$letter])
                                && count($lettersZvezd[$letter])
                            ) {
                                reset($lettersZvezd[$letter]);
                                // Указали на занятую звездочку
                                // $cells[$x + $k][$y][2] = current($lettersZvezd[$letter]);

                                unset($lettersZvezd[$letter][key($lettersZvezd[$letter])]);
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

    /**
     * Рассчитывает коэффициент полезности клетки - буквы сверху
     * @param $x
     * @param $y
     * @param $desk
     * @return float|null
     */
    protected static function getK($x, $y, &$desk, string $dir = self::SVERHU): ?float
    {
        // Собираем буквы, которые примыкают к слову сверху
        $sumLetter = 0; // Сумма очков за буквы с учетом зеленых/желтых клеток
        $step = 1;
        $multSlovo = static::$langClass::$multi[$x][$y]['slovo'] ?? 1; // Коэффициент для синих/красных клеток
        $multBukva = static::$langClass::$multi[$x][$y]['bukva'] ?? 0; // Надбавка для зеленых/желтых клеток

        // Идем далее, пока есть занятые клетки или не кончится доска
        while ($desk[$xStep = self::x($x, $step, $dir)][$yStep = self::y($y, $step, $dir)][0] ?? false) {
            $codeFishka = self::getFishkaCode($desk[$xStep][$yStep][1]);
            $sumLetter += static::$langClass::$bukvy[$codeFishka][1] * (static::$langClass::$multi[$xStep][$yStep]['bukva'] ?? 1);
            $multSlovo *= static::$langClass::$multi[$xStep][$yStep]['slovo'] ?? 1;

            $step++;
        }

        return ($sumLetter + $multBukva) / $step * $multSlovo;
    }

    private static function x(int $x, int $step, string $dir): ?int
    {
        switch ($dir) {
            case BotEng::SVERHU:
            case BotEng::VNIZ:
                return $x;
            case BotEng::SLEVA:
                return $x - $step;
            case BotEng::SPRAVA:
                return $x + $step;
            default:
                return -1;
        }
    }

    private static function y(int $y, int $step, string $dir): ?int
    {
        switch ($dir) {
            case BotEng::SVERHU:
                return $y - $step;
            case BotEng::VNIZ:
                return $y + $step;
            case BotEng::SLEVA:
            case BotEng::SPRAVA:
                return $y;
            default:
                return -1;
        }
    }

    /**
     * Рассчитывает коэффициент полезности клетки - буквы сверху
     * @param $x
     * @param $y
     * @param $desk
     * @return float|null
     */
    protected static function getKUp($x, $y, &$desk): ?float
    {
        // Собираем буквы, которые примыкают к слову сверху
        $sumLetter = 0; // Сумма очков за буквы с учетом зеленых/желтых клеток
        $step = 1;
        $multSlovo = static::$langClass::$multi[$x][$y]['slovo'] ?? 1; // Коэффициент для синих/красных клеток
        $multBukva = static::$langClass::$multi[$x][$y]['bukva'] ?? 0; // Надбавка для зеленых/желтых клеток

        // Идем вверх, пока есть занятые клетки или не кончится доска
        while ($desk[$x][$y - $step][0] ?? false) {
            $codeFishka = self::getFishkaCode($desk[$x][$y - $step][1]);
            $sumLetter += static::$langClass::$bukvy[$codeFishka][1] * (static::$langClass::$multi[$x][$y - $step]['bukva'] ?? 1);
            $multSlovo *= static::$langClass::$multi[$x][$y - $step]['slovo'] ?? 1;

            $step++;
        }

        return ($sumLetter + $multBukva) / ($step - 1) * $multSlovo;
    }

    public static function findWordVniz(
        $x,
        $y,
        &$desk,
        &$fishki,
        array &$slovaPlayed,
        $orientation = 'all'
    ) {
        if (!count($fishki)) {
            return '';
        }

        //пробуем вниз от xy
        $maxLen = self::maxToDown($x, $y, count($fishki), $desk);
        $maxWordLen = $maxLen;

        // Не анализируем, если есть горизонтальные примыкающие буквы
        if (($desk[$x + 1][$y][0] ?? false) || ($desk[$x - 1][$y][0] ?? false)) {
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
        $andLng = 'AND lng=' . static::LNG_ID;
        $zapros .= "$\") AND NOT deleted = 1  AND length<=$maxWordLen $andLng ORDER BY word_score/length DESC";;

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
                    // todo добавить проход слова сначала или с конца

                    $cells = $desk;
                    $slovoNach = mb_strpos($row['slovo'], $lastLetter, 0, 'UTF-8') ?: 0;

                    // Ставим фишки вверх от позиции выше уже существующих фишек на поле
                    for ($k = 0; $k < $slovoNach; $k++) {
                        $letter = mb_substr($row['slovo'], $slovoNach - 1 - $k, 1, 'UTF-8');

                        if ($cells[$x][$yLastLetter - $k - 1][0] === false) {
                            $cells[$x][$yLastLetter - $k - 1][0] = true;
                            $cells[$x][$yLastLetter - $k - 1][1] = static::$langClass::getLetterCode($letter);

                            if (
                                isset($lettersZvezd[$letter])
                                && count($lettersZvezd[$letter])
                            ) {
                                reset($lettersZvezd[$letter]);
                                // Указали на занятую звездочку
                                // $cells[$x][$yLastLetter - $k - 1][2] = current($lettersZvezd[$letter]);

                                unset($lettersZvezd[$letter][key($lettersZvezd[$letter])]);
                            }
                        }
                    }

                    // Ставим фишки винз от текущей позиции
                    for ($k = 0; $k <= mb_strlen($row['slovo'], 'UTF-8') - $slovoNach - $lastLetterLen - 1; $k++) {
                        $letter = mb_substr($row['slovo'], $slovoNach + $lastLetterLen + $k, 1, 'UTF-8');

                        if ($cells[$x][$y + $k][0] === false) {
                            $cells[$x][$y + $k][0] = true;
                            $cells[$x][$y + $k][1] = static::$langClass::getLetterCode($letter);

                            if (
                                isset($lettersZvezd[$letter])
                                && count($lettersZvezd[$letter])
                            ) {
                                reset($lettersZvezd[$letter]);
                                // Указали на занятую звездочку
                                // cells[$x][$y + $k][2] = current($lettersZvezd[$letter]);

                                unset($lettersZvezd[$letter][key($lettersZvezd[$letter])]);
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

    /**
     * @param $fishki
     * @param $word - возвращает слово с заглавными буквами на месте звезды
     * @param string $lastLetter Часть слова, составленная до нашего слова
     * @param array $lettersZvezd
     * @return bool
     */
    public static function checkWordFishki(
        &$fishki,
        &$word,
        string $lastLetter,
        &$lettersZvezd = []
    ) {
        $fishki1 = $fishki;
        asort($fishki1);

        $word = mb_strtolower($word, 'UTF-8');
        $lettersLastLetter = mb_str_split($lastLetter, 1, 'UTF-8');
        $lettersWord = mb_str_split($word, 1, 'UTF-8');
        $lettersZvezd = [];

        // удалим из слова все старые буквы, поставленные до текуего хода
        foreach ($lettersWord as $numLetter => $letter) {
            foreach ($lettersLastLetter as $num => $lLetter) {
                if ($letter == $lLetter) {
                    // Буква поставлена до нашего хода
                    unset($lettersLastLetter[$num]);
                    $lettersWord[$numLetter] .= '_';

                    break;
                }
            }
        }

        foreach ($lettersWord as $numLetter => $letter) {
            if (strpos($letter, '_')) {
                // Буква поставлена до нашего хода
                continue;
            }


            foreach ($fishki1 as $num => $fishka) {
                if ($letter === static::$langClass::$bukvy[self::getFishkaCode($fishka)][0]) {
                    unset($fishki1[$num]);

                    continue 2;
                } elseif ($fishka >= 999) {
                    unset($fishki1[$num]);

                    //Признак что буква со звездочкой
                    $lettersWord[$numLetter] = mb_strtoupper($letter, 'UTF-8');

                    if ($fishka > 999) {
                        $lettersZvezd[$lettersWord[$numLetter]][] = $fishka - 999 - 1; // У нас звезда под кодом буквы
                    }

                    continue 2;
                }
            }

            // Сюда попадаем, если буква не найдена в фишках
            return false;
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
        $word = implode('', array_map(fn($letter) => trim($letter, '_'), $lettersWord));

        return true;
    }

    public static function maxToLeft($x, $y, $countFishki, &$desk)
    {
        $max = 0;

        for (
            $i = $x;
            ($i >= 0)
            && ($i === 0 || !$desk[$i - 1][$y][0])
            && !$desk[$i][$y][0]
            && !($desk[$i][$y + 1][0] ?? false)
            && !($desk[$i][$y - 1][0] ?? false);
            $i--
        ) {
            $max++;
        }

        return (min($max, $countFishki));
    }

    public static function maxToUp(
        $x,
        $y,
        $countFishki,
        &$desk
    ) {
        $max = 0;

        for (
            $j = $y;
            ($j >= 0)
            && ($j === 0 || !$desk[$x][$j - 1][0])
            && !$desk[$x][$j][0]
            && !($desk[$x + 1][$j][0] ?? false)
            && !($desk[$x - 1][$j][0] ?? false);
            $j--
        ) {
            $max++;
        }

        return (min($max, $countFishki));
    }

    public static function maxToRight(
        $x,
        $y,
        $countFishki,
        &$desk
    ) {
        $max = 0;

        for (
            $i = $x;
            ($i <= 14)
            && ($i === 14 || !$desk[$i + 1][$y][0])
            && !($desk[$i][$y][0] ?? true)
            && !($desk[$i][$y + 1][0] ?? false)
            && !($desk[$i][$y - 1][0] ?? false);
            $i++
        ) {
            $max++;
        }

        return (min($max, $countFishki));
    }

    public static function maxToDown(
        $x,
        $y,
        $countFishki,
        &$desk
    ) {
        $max = 0;

        for (
            $j = $y;
            ($j <= 14)
            && ($j === 14 || !$desk[$x][$j + 1][0])
            && !$desk[$x][$j][0]
            && !($desk[$x + 1][$j][0] ?? false)
            && !($desk[$x - 1][$j][0] ?? false);
            $j++
        ) {
            $max++;
        }

        return (min($max, $countFishki));
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

    protected static function mp(array $data): void
    {
        Cache::rpush(static::BOT_LOG_KEY, $data + ['bot' => static::$botname]);
    }
}

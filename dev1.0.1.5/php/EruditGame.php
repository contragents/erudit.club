<?php

namespace Erudit;

use BanModel;
use \Cache;
use Dadata\Hints;
use Dadata\Prizes;
use Dadata\Stats;
use Lang\Eng;
use Lang\Ru;
use LogModel;
use PlayerModel;
use \QueuePrivate as Queue;

class Game extends \Game
{
    const GAMES_KEY = 'erudit.private.games_';
    const GAME_STATUS_KEY = 'erudit.private.game_status_';
    const GAME_USER_KEY = 'erudit.private.user_';
    const CURRENT_GAME_KEY = 'erudit.private.current_game_';
    const GAMES_ENDED_KEY = 'erudit.private.games_ended';
    const STATS_FAILED = 'erudit.private.games_statistics_failed'; // статистика не используется в private
    const NUM_RATING_PLAYERS_KEY = 'erudit.private.num_rating_players';
    const GET_GAME_KEY = 'erudit.private.get_game_';

    const TURN_TIME = 30 * 60; // CLUB-296 30 мин на ход
    const WIN_SCORE = 10000; // CLUB-296 игра до последней фишки

    public function __construct()
    {
        $this->Queue = Queue::class;
        $this->dir = __DIR__;

        parent::__construct();
    }

    public function playersInfo() // private version
    {
        $ratings = $this->getRatings();
        $message = include(__DIR__ . '/tpl/ratingsTableHeader.php');
        if (!isset($this->gameStatus['users'])) {
            return $this->makeResponse(['message' => "Игра не начата"]);
        }

        $commonId = PlayerModel::getCommonID($this->User);
        if ($commonId) {
            $thisPlayerHasBanned = BanModel::hasBanned($commonId);
        }

        foreach ($this->gameStatus['users'] as $num => $user) {
            if (isset($user['userID'])) {
                if (!($deltaRating = $this->getDeltaRating(self::hash_str_2_int($user['userID'])))) {
                    $deltaRating = $this->getDeltaRating($user['ID']);
                }
            } else {
                $deltaRating = $this->getDeltaRating($user['ID']);
            }

            $ratingFound = false;
            foreach ($ratings as $rating) {
                if ($user['ID'] == $rating['cookie']) {
                    $ratingFound = true;
                    break;
                }
            }

            if (!$ratingFound) {
                $rating['rating'] = '1700 (новый игрок)';
                $rating['games_played'] = 0;
                $rating['win_percent'] = 0;
                $rating['inactive_percent'] = 'N/A';
                $rating['top'] = '';
            }

            $rating['playerName'] = $this->getPlayerName($user);
            $rating['playerAvatarUrl'] = $this->getAvatarUrl($user['ID']);
            $rating['isActive'] = (!isset($user['lastActiveTime']) || !$user['isActive']) ? false : true;

            $canDeleteBan = false;
            if ($user != $this->User) {
                $currentUserCommonId = PlayerModel::getCommonID($user['ID']);
                if ($currentUserCommonId && !empty($thisPlayerHasBanned[$currentUserCommonId])) {
                    $canDeleteBan = true;
                }
            }

            $message .= include(__DIR__ . '/tpl/ratingsTableRow.php');

            $recImgs = '';
            $records = Prizes::playerCurrentRecords($user['ID'], $rating['playerName']);
            $recordsShown = 0;
            foreach ($records as $record) {
                $recImgs .= "<img 
								style=\"
									cursor: pointer; 
									margin-left: " . ($recordsShown ? -20 : 0) . "px; padding: 0;
									margin-top: -10px;
									z-index: 50;
								\" 
								title=\"Кликните для увеличения изображения\" 
								id=\"{$record['type']}\" 
								onclick=\"showFullImage('{$record['type']}', 500, 100);\" 
								src=\"/{$record['link']}\" width=\"100px\" />";
                $recordsShown++;
                if ($recordsShown >= 3) {
                    break;
                }
            }

            if ($user['ID'] == $this->User) {
                $message .= include(__DIR__ . '/tpl/ratingsTableNicknameFormRow.php');
            }

            if ($recordsShown) {
                $message .= include(__DIR__ . '/tpl/ratingsTablePrizesRow.php');
            }
        }
        $message .= include(__DIR__ . '/tpl/ratingsTableFooter.php');

        return $this->makeResponse(['message' => $message]);
    }

    public function getDeltaRating($key) // private version
    {
        if ($delta = Cache::get(PlayerModel::DELTA_RATING_KEY_PREFIX . $key)) {
            return $delta;
        } else {
            return false;
        }
    }

    protected function statusComments_startGame()
    {
        Stats::saveStats();

        $ratings = $this->getRatings($this->User);

        $this->gameStatus['users'][$this->gameStatus[$this->User]]['rating'] = $ratings ? $ratings[0]['rating'] : 'new_player';

        return 'Новая игра начата! <br />Набери как можно больше очков' . '<br />' . $this->gameStatus['users'][$this->gameStatus['activeUser']]['username'] . ' ходит' . '<br />Ваш текущий рейтинг - <strong>' . $ratings[0]['rating'] . '</strong>';
    }

    protected function statusComments_otherTurn()
    {
        return $this->gameStatus['users'][$this->gameStatus['activeUser']]['username'] . ' ходит'
            . Hints::getHint(
                $this->User,
                $this->gameStatus,
                $this->gameStatus['users'][$this->gameStatus[$this->User]]['rating'] ?? false
            );
    }

    protected function statusComments_desync()
    {
        return 'Синхронизируемся с сервером..'
            . Hints::getHint(
                $this->User,
                $this->gameStatus,
                $this->gameStatus['users'][$this->gameStatus[$this->User]]['rating'] ?? false
            );
    }

    protected function statusComments_myTurn()
    {
        if ($this->gameStatus['turnNumber'] == 1) {
            Stats::saveStats();
        }

        if ($this->gameStatus['turnNumber'] == 1 || !isset($this->gameStatus['users'][$this->gameStatus[$this->User]]['rating'])) {
            $ratings = $this->getRatings($this->User);

            $this->gameStatus['users'][$this->gameStatus[$this->User]]['rating'] = $ratings[0]['rating'] ?? 'new_player';

            return 'Ваш ход! <br />Игра до последней фишки' . '<br />Ваш текущий рейтинг - <strong>' . $ratings[0]['rating'] . '</strong>';
        } else {
            return $this->gameStatus['users'][$this->numUser]['username'] . ', Ваш ход!'
                . Hints::getHint(
                    $this->User,
                    $this->gameStatus,
                    $this->gameStatus['users'][$this->gameStatus[$this->User]]['rating'] ?? false
                );
        }
    }

    protected function statusComments_initGame()
    {
        if ($this->gamePlayersWaiting) {
            return '<strong>Подбор игры!</strong> <br />Готово игроков: <strong>' . $this->gamePlayersWaiting . '</strong>';
        } else {
            return '<strong>Подбор игры!</strong> <br />Поиск других игроков';
        }
    }


    protected function statusComments_preMyTurn()
    {
        if ($this->gameStatus['turnNumber'] == 1) {
            $ratings = $this->getRatings($this->User);
            return 'Игра до последней фишки<br />Ваш ход следующий - приготовьтесь!' . '<br />Ваш текущий рейтинг - <strong>' . $ratings[0]['rating'] . '</strong>';
        } else {
            return $this->gameStatus['users'][$this->gameStatus['activeUser']]['username'] . ' ходит. <br />Ваш ход следующий - приготовьтесь!'
                . Hints::getHint(
                    $this->User,
                    $this->gameStatus,
                    $this->gameStatus['users'][$this->gameStatus[$this->User]]['rating'] ?? false
                );
        }
    }

    public function changeFishki($fishkiToChange) // private version
    {
        if ($this->getUserStatus() != self::MY_TURN_STATUS) {
            return $this->checkGameStatus();
        }

        if (count($fishkiToChange)) {
            $this->addToLog('меняет фишки и пропускает ход', $this->numUser);
            $limb = [];

            foreach ($fishkiToChange as $newFishka => $on) {
                $fishkaCode = explode('_', $newFishka);
                $fishkaCode = $fishkaCode[2];

                foreach ($this->gameStatus['users'][$this->numUser]['fishki'] as $num => $code) {
                    if ($fishkaCode == $code) {
                        unset($this->gameStatus['users'][$this->numUser]['fishki'][$num]);

                        $limb[] = $fishkaCode;

                        break;
                    }
                }
            }

            $numNeedFishki = $this->chisloFishek - count($this->gameStatus['users'][$this->numUser]['fishki']);

            if (count($this->gameStatus['bankFishki']) < $numNeedFishki) {
                array_push($this->gameStatus['bankFishki'], ...$limb);
                $limb = [];
            }

            shuffle($this->gameStatus['bankFishki']);

            $addFishki = $this->giveFishki($numNeedFishki);
            $this->gameStatus['users'][$this->numUser]['fishki'] = array_merge(
                $this->gameStatus['users'][$this->numUser]['fishki'],
                $addFishki
            );
        }

        if (count($limb)) {
            array_push($this->gameStatus['bankFishki'], ...$limb);
            shuffle($this->gameStatus['bankFishki']);
        }

        $this->nextTurn();

        return $this->checkGameStatus();
    }

    public function wordChecker() // private version
    {
        if ($this->currentGame === false) {
            return 'Игра еще не начата!';
        }
        $check_statuses = ['Слово не найдено', 'Корректно', 'Слово из одной буквы', 'Повтор'];
        $result = '';
        $desk = Cache::get(static::CURRENT_GAME_KEY . $this->currentGame);
        //Текущая доска
        $cells = json_decode($_POST['cells'], true);
        //Присланная доска

        $new_fishki = $this->gameStatus['lngClass']::submit($cells, $desk, $this->gameStatus);
        $summa = 0;
        if (is_array($new_fishki) && !empty($new_fishki)) {
            foreach ($new_fishki['badWords'] as $badWord) {
                if (isset($this->gameStatus['wordsAccepted'][$badWord])) {
                    $result .= '<strong>' . $badWord . ' - ' . $check_statuses[3] . '</strong><br />';
                } elseif (mb_strlen($badWord, 'UTF-8') == 1) {
                    $result .= '<strong>' . $badWord . ' - ' . $check_statuses[2] . '</strong><br />';
                } elseif (mb_strlen($badWord, 'UTF-8') > 1) {
                    $result .= '<strong style="color:red;">' . $badWord . ' - ' . $check_statuses[0] . '</strong><br />';
                }
            }
            foreach ($new_fishki['words'] as $word => $price) {
                $result .= $word . ' - стоимость: ' . $price . '<br />';
                $summa += $price;
            }
            if (count($new_fishki['good']) == count($this->gameStatus['users'][$this->numUser]['fishki'])) {
                $summa += 15;
                $result .= '+15 за все фишки ';
            }
        }

        if ($result !== '') {
            $result .= '<strong>ИТОГО: ' . $summa . '</strong>';
        } else {
            $result = 'Вы не составили ни одного слова';
        }

        return json_encode($result);
    }

    public function submitTurnPrivate() // есть ли отличия от основного метода - проестить
    {
        if ($this->getUserStatus() != self::MY_TURN_STATUS) {
            $this->addToLog(
                'пытается сделать ход не в свою очередь (ход #' . $this->gameStatus['turnNumber'] . ')',
                $this->numUser
            );

            return $this->checkGameStatus();
        }

        try {
            $desk = Cache::get(static::CURRENT_GAME_KEY . $this->currentGame);
            // Текущая доска

            $saveDesk = $desk;
            // Сохранили доску

            $saveWords = $this->gameStatus['wordsAccepted'];
            // сохранили сыгранные слова

            $cells = json_decode($_POST['cells'], true);
            // Присланная доска

            if (Ru::checkHasBadField($cells)) {
                LogModel::add(
                    [
                        LogModel::CATEGORY_FIELD => LogModel::CATEGORY_SUBMIT_ERROR,
                        LogModel::MESSAGE_FIELD => json_encode([
                            'game_status' => $this->gameStatus,
                            'User' => $this->User,
                            'cells' => $cells,
                            'desk' => $desk,
                            ],
                        JSON_UNESCAPED_UNICODE
                        )
                    ]
                );

                // Прислана доска с ошибкой в поле - ничего не обрабатываем
                return $this->checkGameStatus();
            }

            /**
             * @method Ru|Eng submit()
             */
            $new_fishki = $this->gameStatus['lngClass']::submit($cells, $desk, $this->gameStatus);
        } catch (\Throwable $e) {
            \BadRequest::logBadRequest(
                [
                    'message' => 'Ошибка обработки данных!',
                    'err_msg' => $e->getMessage(),
                    'err_file' => $e->getFile(),
                    'err_line' => $e->getLine(),
                    'err_context' => $e->getTrace(),
                    'received_desk' => $cells,
                    'game_desk' => $saveDesk
                ]
            );

            $this->addToLog(
                ' - ошибка обработки хода (ход #' . $this->gameStatus['turnNumber'] . ')',
                $this->numUser
            );

            return $this->checkGameStatus();
        }

        if (Ru::checkHasBadField($cells)) {
            LogModel::add(
                [
                    LogModel::CATEGORY_FIELD => LogModel::CATEGORY_RULANG_ERROR,
                    LogModel::MESSAGE_FIELD => json_encode($cells)
                ]
            );

            // После анализа присланной доски ошибка в поле - ничего не сохраняем
            return $this->checkGameStatus();
        }

        if ($new_fishki === false) {
            $this->addToLog(
                'не составил ни одного слова (ход #' . $this->gameStatus['turnNumber'] . ')',
                $this->numUser
            );

            $this->gameStatus['users'][$this->numUser]['lostTurns']++;
            if ($this->gameStatus['users'][$this->numUser]['lostTurns'] >= 3) {
                $this->storeGameResults($this->lost3TurnsWinner($this->numUser));

                return $this->checkGameStatus();
            }

            $this->nextTurn();
            $this->destruct();
            //Сохранили статус игры

            return json_encode(array_merge($saveDesk ?: [], [$this->gameStatus['users'][$this->numUser]['fishki']]));
            //Сделать через отправку статуса
        }

        try {
            $ochkiZaHod = 0;
            //Очки за ход пока=0

            if (is_array($new_fishki)) {
                //Проверяем забранные с поля звезды
                foreach ($this->gameStatus['users'][$this->numUser]['fishki'] as $num => $fishka) {
                    foreach ($new_fishki['good'] as $i => $good_fishka) {
                        if ($fishka === $good_fishka[3]) {
                            unset($new_fishki['good'][$i]);
                            unset($this->gameStatus['users'][$this->numUser]['fishki'][$num]);
                            break;
                        }
                    }
                }
                //Проверяем оставшиеся фишки
                foreach ($this->gameStatus['users'][$this->numUser]['fishki'] as $num => $fishka) {
                    foreach ($new_fishki['good'] as $i => $good_fishka) {
                        if (($fishka === $good_fishka[2]) || (($fishka == 999) && ($good_fishka[2] > 999))) {
                            unset($new_fishki['good'][$i]);
                            unset($this->gameStatus['users'][$this->numUser]['fishki'][$num]);
                            break;
                        }
                    }
                }

                $vseFishki = false;
                if (count($this->gameStatus['users'][$this->numUser]['fishki']) === 0) {
                    $ochkiZaHod += 15;
                    $vseFishki = true;
                }

                $addFishki = $this->giveFishki(
                    $this->chisloFishek - count($this->gameStatus['users'][$this->numUser]['fishki'])
                );

                $this->gameStatus['users'][$this->numUser]['fishki'] = array_merge(
                    $this->gameStatus['users'][$this->numUser]['fishki'],
                    $addFishki
                );

                foreach ($new_fishki['words'] as $word => $price) {
                    $ochkiZaHod += $price;
                    //Добавили очков за ход
                    $this->gameStatus['wordsAccepted'][$word] = $word;
                    //Добавили слово в список сыгранных слов

                    $arr = Prizes::checkDayWordLenRecord($word);
                    foreach ($arr as $period => $value) {
                        $this->addToLog(
                            "устанавливает рекорд по длине слова за $period - <strong>$word</strong>",
                            $this->numUser
                        );
                    }

                    $arr = Prizes::checkDayWordPriceRecord($word, $price);
                    foreach ($arr as $period => $value) {
                        $this->addToLog(
                            "устанавливает рекорд по стоимости слова за $period - <strong>$word - $price</strong>",
                            $this->numUser
                        );
                    }
                }
            }

            $this->gameStatus['users'][$this->numUser]['score'] += $ochkiZaHod;
            if ($ochkiZaHod == 0) {
                $this->gameStatus['users'][$this->numUser]['lostTurns']++;
            } else {
                $arr = Prizes::checkDayTurnPriceRecord($ochkiZaHod);
                foreach ($arr as $period => $value) {
                    $this->addToLog(
                        "устанавливает рекорд по стоимости хода за $period - <strong>$ochkiZaHod</strong>",
                        $this->numUser
                    );
                }
            }

            $this->addToLog(
                'зарабатывает ' . $ochkiZaHod . ' за ход #' . $this->gameStatus['turnNumber'] . $this->logSlov(
                    $new_fishki['words']
                ) . ($vseFishki ? ' <span title="За все фишки" style="color: green;">(<strong>+15</strong>)</span>' : ''),
                $this->numUser
            );

            if ($this->gameStatus['users'][$this->numUser]['score'] >= $this->gameStatus['winScore']) {
                $this->addToLog(
                    'Побеждает со счетом ' . $this->gameStatus['users'][$this->numUser]['score'],
                    $this->numUser
                );

                $arr = Prizes::checkDayGamePriceRecord($this->gameStatus['users'][$this->numUser]['score']);
                foreach ($arr as $period => $value) {
                    $this->addToLog(
                        "устанавливает рекорд набранных очков в игре за $period - <strong>{$this->gameStatus['users'][$this->numUser]['score']}</strong>",
                        $this->numUser
                    );
                }

                $this->storeGameResults($this->User);
                //Обнаружен выигравший
            } elseif ($this->gameStatus['users'][$this->numUser]['lostTurns'] >= 3) {
                $this->storeGameResults($this->lost3TurnsWinner($this->numUser));
                //Обнаружен выигравший
            } elseif (count($this->gameStatus['users'][$this->numUser]['fishki']) === 0) {
                $this->addToLog('закончились фишки - конец игры!', $this->numUser);
                $this->storeGameResults($this->endOfFishki());
            } // Обнаружен выигравший
            elseif ($ochkiZaHod > 0) {
                $this->gameStatus['users'][$this->numUser]['lostTurns'] = 0;
                $this->nextTurn();
            } elseif ($ochkiZaHod == 0) {
                $this->nextTurn();
            }

            if (isset($this->gameStatus['results'])) {
                $arr = Prizes::checkDayGamesPlayedRecord(
                    array_map(
                        function ($user) {
                            return $user['ID'];
                        },
                        $this->gameStatus['users']
                    )
                );

                foreach ($arr as $period => $record) {
                    $this->addToLog(
                        "устанавливает рекорд по числу сыгранных партий за $period - <strong>" . reset(
                            $record
                        ) . "</strong>",
                        $this->gameStatus[key($record)]
                    );
                }
            }
        } catch (\Throwable $e) {
            \BadRequest::sendBadRequest(
                [
                    'message' => 'Ошибка обработки данных!',
                    'err_msg' => $e->getMessage(),
                    'err_file' => $e->getFile(),
                    'err_line' => $e->getLine(),
                    'err_context' => $e->getTrace(),
                ]
            );
        }

        if ($ochkiZaHod === 0) {
            // Сохраняем в лог комбинацию на 0 очков
            Cache::hset(
                self::BAD_COMBINATIONS_HSET,
                microtime(true),
                [
                    'new_fishki' => $new_fishki,
                    'old_cells' => json_decode($_POST['cells'], true),
                    'old_desk' => $saveDesk,
                    'new_desk' => $cells,
                    'saved_words' => $saveWords,
                    'new_played_words' => $new_fishki['words'] ?? [],
                ]
            );
        } else {
            Cache::setex(static::CURRENT_GAME_KEY . $this->currentGame, $this->cacheTimeout, $cells);
            //Измененная Присланная доска -> текущая
        }

        $this->destruct();
        //Сохранили статус игры

        print json_encode(
            array_merge(
                $ochkiZaHod ? ($cells ?: []) : ($saveDesk ?: []),
                [$this->gameStatus['users'][$this->numUser]['fishki']]
            )
        );
        //Сделать через отправку статуса
    }

    public function onlinePlayers() // private version
    {
        return [];
    }

    public function gameStarted($statusUpdateNeeded = false) // private version
    {
        if ($statusUpdateNeeded) {
            $firstTurnUser = rand(0, count($this->currentGameUsers) - 1);
            $this->gameStatus['gameNumber'] = $this->currentGame;
            $this->gameStatus['users'][$firstTurnUser]['status'] = self::MY_TURN_STATUS;
            $this->gameStatus['activeUser'] = $firstTurnUser;
            $this->gameStatus['gameBeginTime'] = date('U');
            $this->gameStatus['turnBeginTime'] = $this->gameStatus['gameBeginTime'];
            $this->gameStatus['turnTime'] = self::TURN_TIME; // CLUB-296
            $this->gameStatus['turnNumber'] = 1;
            $this->gameStatus['firstTurnUser'] = $firstTurnUser;
            $this->gameStatus['bankFishki'] = $this->gameStatus['lngClass']::generateBankFishki();
            $this->gameStatus['wordsAccepted'] = [];
            $this->gameStatus['winScore'] = self::WIN_SCORE; // CLUB-296
            $this->gameStatus['aquiringTimes'][$this->gameStatus['turnNumber']] = false;
            $this->updateUserStatus(self::MY_TURN_STATUS, $this->currentGameUsers[$firstTurnUser]);
            //Назначили ход случайному юзеру

            $ost = ($firstTurnUser - 1) % count($this->gameStatus['users']);
            if ($ost >= 0) {
                $preMyTurnUser = $ost;
            } else {
                $preMyTurnUser = count($this->gameStatus['users']) + $ost;
            }
            $this->updateUserStatus(self::PRE_MY_TURN_STATUS, $this->currentGameUsers[$preMyTurnUser]);
            //Вычислили игрока, идущего за первым и дали ему статус преМайТерн

            foreach ($this->gameStatus['users'] as $num => $user) {
                $this->gameStatus['users'][$num]['fishki'] = $this->giveFishki(7);
                //Раздали фишки игрокам
                $this->gameStatus['users'][$num]['lostTurns'] = 0;
                $this->gameStatus['users'][$num]['inactiveTurn'] = 1000;
                //Сделали невозможным значение терна инактив

                $userRating = $this->getRatings($user['ID']);
                $this->gameStatus['users'][$num]['rating'] = $userRating ? $userRating[0]['rating'] : 'new_player';
                $this->gameStatus['users'][$num]['common_id'] = PlayerModel::getPlayerID($user['ID'], true);
                //Прописали рейтинг и common_id игрока в статусе игры - только для games_statistic.php
            }
            $this->addToLog(
                'Новая игра начата! <br />Набери как можно больше очков'
            );
        }


        return $this->makeResponse(
            [
                'gameState' => $this->getUserStatus(),
                'usersInfo' => $this->currentGameUsers,//хз зачем этот ключ - нигде не используется
            ]
        );
    }
}


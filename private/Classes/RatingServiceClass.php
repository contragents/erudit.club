<?php

class RatingService
{
    protected static array $playersUnchanged = [];

    public static function processGameResult(array &$Game): array {
        $players = self::getRanks($Game);

        // сохранили предыдущие рейтинги игроков в разбивке по common_id
        foreach($players as $player) {
            self::$playersUnchanged[$player['common_id']] = [
                'prev_rating' => $player['rating'],
                'new_rating' => $player['rating'],
                'delta_rating' => 0,
                'is_winner' => $player['is_winner'],
            ];
        }

        self::changeRatings($players);

        // todo сделать после отключения games_statistics - в статистике отключено
        self::saveGameStats($Game, $players);

        if(!self::saveRatings($players, $Game['gameNumber'])) {
            return self::$playersUnchanged; // todo отдать рейтинги без изменений
        } else {
            foreach ($players as $player) {
                self::deleteRatingsFromCache($player);
                self::addDeltaRatingsToCache($player, $Game);
            }
        }

        // todo запустить после отключения games_statistics - в статистике отключено
        self::saveGame($Game);

        self::saveRatingsPlayersTable($players);

        $result = [];
        foreach($players as $player) {
            $result[$player['common_id']] = [
                'prev_rating' => $player['rating'],
                'new_rating' => $player['rating'] + $player['deltaRating'],
                'delta_rating' => $player['deltaRating'],
                'is_winner' => $player['is_winner'],
            ];
        }

        return $result;
    }

    // todo CLUB-384 не использовать таблицу players для хранения рейтингов - поправить код во всех местах
    protected static function saveRatingsPlayersTable(&$players)
    {
        foreach ($players as $num => $player) {
            BaseModel::updateWhere(
                [PlayerModel::RATING_FIELD => $player['rating'] + $player['deltaRating'],],
                [PlayerModel::COMMON_ID_FIELD, '=', $player['common_id'], true]
            );
        }

        return;

        foreach ($players as $num => $player) {
            $UPDATE = "UPDATE erudit.players 
                    SET
                        rating=" . ($player['rating'] + $player['deltaRating']) . "
                        , win_percent = round(
                            (select sum(1) 
                            from games_stats 
                            where winner_player_id = players.id)*100/(games_played+1)
                            )
                        , games_played = games_played+1
                        , inactive_percent = CASE 
                        WHEN games_played = 0 
                        THEN inactive_percent 
                        ELSE ((games_played/100*inactive_percent" . ($player['isActive'] ? '' : '+1') . ")/(games_played+1)*100) 
                        END
                        , rating_changed_date = CURRENT_TIMESTAMP()
                        , user_id = " .
                ($player['userID']
                    ? $player['userID']
                    : Game::hash_str_2_int($player['found_cookie'])) .
                " WHERE 
                cookie = '{$player['cookie']}'
                OR
                cookie = '{$player['found_cookie']}'" .
                ($player['userID']
                    ? " OR user_id = {$player['userID']} "
                    : ''
                ) .
                (
                isset($player['common_id'])
                    ? " OR common_id = {$player['common_id']} "
                    : ''
                );

            DB::queryInsert($UPDATE);

            deleteRatingsFromCache($player);
            addDeltaRatingsToCache($player);
        }
    }

    protected static function saveGame(&$Game)
    {
        GamesModel::add(
            [
                GamesModel::ID_FIELD => $Game['gameNumber'] + GameController::GAME_ID_BASE_INC,
                GamesModel::GAME_DATA_FIELD => new ORM("compress('" . DB::escapeString(serialize($Game)) . "')")
            ]
        );
    }

    protected static function saveGameStats(&$Game, &$results)
    {
        GameStatsModel::add($queryParams =
            [
                GameStatsModel::GAME_ID_FIELD => $Game['gameNumber'] + GameController::GAME_ID_BASE_INC,
                GameStatsModel::PLAYERS_NUM_FIELD => count($results),
                GameStatsModel::GAME_ENDED_AT_FIELD => $Game['turnBeginTime'],
                GameStatsModel::WINNER_ID_FIELD => $results[0]['common_id']
            ]
            + [
                $Game[$results[0]['cookie']] + 1 . '_player_id' => $results[0]['common_id'],
                $Game[$results[0]['cookie']] + 1 . '_player_rating_delta' => $results[0]['deltaRating'],
                $Game[$results[0]['cookie']] + 1 . '_player_old_rating' => $results[0]['rating'],
            ]
            +
            [
                $Game[$results[1]['cookie']] + 1 . '_player_id' => $results[1]['common_id'],
                $Game[$results[1]['cookie']] + 1 . '_player_rating_delta' => $results[1]['deltaRating'],
                $Game[$results[1]['cookie']] + 1 . '_player_old_rating' => $results[1]['rating'],
            ]
            + (isset($results[2])
                ? [
                    $Game[$results[2]['cookie']] + 1 . '_player_id' => $results[2]['common_id'],
                    $Game[$results[2]['cookie']] + 1 . '_player_rating_delta' => $results[2]['deltaRating'],
                    $Game[$results[2]['cookie']] + 1 . '_player_old_rating' => $results[2]['rating'],
                ]
                : [])
            + (isset($results[3])
                ? [
                    $Game[$results[3]['cookie']] + 1 . '_player_id' => $results[3]['common_id'],
                    $Game[$results[3]['cookie']] + 1 . '_player_rating_delta' => $results[3]['deltaRating'],
                    $Game[$results[3]['cookie']] + 1 . '_player_old_rating' => $results[3]['rating'],
                ]
                : [])
        );

        if (!DB::insertID()) {
            Cache::rpush(
                Game::STATS_FAILED,
                [
                    'query' => $queryParams,
                    'game' => $Game,
                    'results' => $results
                ]
            );
        }
    }

    protected static function saveRatings(&$players, int $gameId): bool
    {
        $gameName = Game::$gameName;

        DB::transactionStart();

        foreach ($players as $player) {

            if (!RatingHistoryModel::addRatingChange(
                $player['common_id'],
                $player['rating'],
                $player['rating'] + $player['deltaRating'],
                $player['is_winner'],
                $gameId,
                $gameName
            )) {
                DB::transactionRollback();

                return false;
            }

            if (!CommonIdRatingModel::changeUserRating($player['common_id'], $player['rating'] + $player['deltaRating'], $gameName)) {
                DB::transactionRollback();

                return false;
            }
        }

        DB::transactionCommit();

        return true;
    }

    protected static function changeRatings(&$players)
    {
        $K = 30 / (count($players) - 1); // коэффициент крутизны игрока (10,20,30)
        for ($i = 0; $i < (count($players) - 1); $i++) {
            $players[$i]['deltaRating'] = $players[$i]['deltaRating'] ?? 0;

            for ($j = $i + 1; $j < count($players); $j++) {
                $ratingA = $players[$i]['rating'];
                $ratingB = $players[$j]['rating'];
                $EA = 1 / (1 + 10 ** (($ratingB - $ratingA) / 400));
                //print "EA: " . $EA . "\n";
                $ratingANew = round($ratingA + $K * (1 - $EA));
                $ratingBNew = $ratingB - ($ratingANew - $ratingA);
                $players[$i]['deltaRating'] += ($ratingANew - $ratingA);

                $players[$j]['deltaRating'] = $players[$j]['deltaRating'] ?? 0;
                $players[$j]['deltaRating'] += ($ratingBNew - $ratingB);
            }
        }
    }

    protected static function getRanks(&$Game)
    {
        $winner = [];
        $winner['cookie'] = $Game['results']['winner'];
        $winner['score'] = $Game['users'][$Game[$winner['cookie']]]['score'];
        $winner['isActive'] = true;
        $winner['is_winner'] = true;

        // todo userID remove
        $winner['userID'] = isset($Game['users'][$Game[$winner['cookie']]]['userID'])
            ? Game::hash_str_2_int($Game['users'][$Game[$winner['cookie']]]['userID'])
            : false;

        $winner['common_id'] = $Game['users'][$Game[$winner['cookie']]]['common_id'] ?? false;
        // рейтинг берем из БД, потом из игры, т.к. он мог поменяться в процессе
        $winner['rating'] = CommonIdRatingModel::getRating($winner['common_id'], Game::$gameName)
            ?: (
                ((int)$Game['users'][$Game[$winner['cookie']]]['rating'] ?? 0)
                ?: CommonIdRatingModel::INITIAL_RATING
            );

        $lostPlayers = [];

        foreach ($Game['results']['lostUsers'] as $num => $cookie) {
            $lostPlayers[$num]['cookie'] = $cookie;
            $lostPlayers[$num]['score'] = $Game['users'][$Game[$cookie]]['score'];
            $lostPlayers[$num]['isActive'] = isset($Game['users'][$Game[$cookie]]['lastActiveTime']);

            // todo userID remove
            $lostPlayers[$num]['userID'] = isset($Game['users'][$Game[$cookie]]['userID'])
                ? Game::hash_str_2_int($Game['users'][$Game[$cookie]]['userID'])
                : false;

            $lostPlayers[$num]['common_id'] = $Game['users'][$Game[$cookie]]['common_id'] ?? false;
            $lostPlayers[$num]['rating'] = CommonIdRatingModel::getRating($lostPlayers[$num]['common_id'], Game::$gameName)
                ?: (
                    ((int)$Game['users'][$Game[$cookie]]['rating'] ?? 0)
                    ?: CommonIdRatingModel::INITIAL_RATING
                );

            $lostPlayers[$num]['is_winner'] = false;
        }

        usort($lostPlayers, ['self', 'arComp']);
        $resultsArray = array_merge([$winner], $lostPlayers);

        return $resultsArray;
    }

    protected static function arComp($a, $b)
    {
        if ($a['isActive'] && $b['isActive']) {
            if ($a['score'] == $b['score']) {
                return 0;
            }
            return ($a['score'] > $b['score']) ? -1 : 1;
        } elseif ($a['isActive'] && !$b['isActive']) {
            return -1;
        } elseif (!$a['isActive'] && $b['isActive']) {
            return 1;
        } elseif (!$a['isActive'] && !$b['isActive']) {
            if ($a['score'] == $b['score']) {
                return 0;
            }
            return ($a['score'] > $b['score']) ? -1 : 1;
        }
    }

    protected static function addDeltaRatingsToCache($player, &$Game)
    {
        $deltaArr = [
            'delta' => $player['deltaRating'],
            'time' => $Game['turnBeginTime'],
            'game_number' => $Game['gameNumber']
        ];

        Cache::setex(PlayerModel::DELTA_RATING_KEY_PREFIX . $player['common_id'], PlayerModel::RATING_CACHE_TTL, $deltaArr);
    }

    protected static function deleteRatingsFromCache($player)
    {
        Cache::del(PlayerModel::RATING_CACHE_PREFIX . $player['cookie']);
        Cache::del(PlayerModel::RATING_CACHE_PREFIX . $player['found_cookie']);
        Cache::del(PlayerModel::RATING_CACHE_PREFIX . $player['common_id']);

        if (isset($player['userID']) && $player['userID'] > 0) {
            Cache::del(PlayerModel::RATING_CACHE_PREFIX . $player['cookie'] . $player['userID']);
            Cache::del(PlayerModel::RATING_CACHE_PREFIX . $player['found_cookie'] . $player['userID']);
        }
    }
}

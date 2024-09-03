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

        // todo сделать после отключения games_statistics
        //saveGameStats($Game, $players);

        if(!self::saveRatings($players, $Game['gameNumber'])) {
            return self::$playersUnchanged; // todo отдать рейтинги без изменений
        }

        foreach ($players as $player) {
            self::deleteRatingsFromCache($player);
            self::addDeltaRatingsToCache($player, $Game);
        }

        // todo запустить после отключения games_statistics
        // saveGame($Game, $players);

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

    protected static function saveRatings(&$players, int $gameId, string $gameName = BaseModel::ERUDIT): bool
    {
        DB::transactionStart();

        foreach ($players as $player) {

            if (!RatingHistoryModel::addRatingChange(
                $player['common_id'],
                $player['rating'],
                $player['rating'] + $player['deltaRating'],
                $player['is_winner'],
                $gameId,
                BaseModel::GAME_IDS[$gameName]
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
        $winner['rating'] = ((int)$Game['users'][$Game[$winner['cookie']]]['rating'] ?? 0)
            ?: CommonIdRatingModel::INITIAL_RATING;

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
            $lostPlayers[$num]['rating'] = ((int)$Game['users'][$Game[$cookie]]['rating'] ?? 0)
                ?: CommonIdRatingModel::INITIAL_RATING; // todo брать из БД, потом из игры

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



        Cache::setex(PlayerModel::DELTA_RATING_KEY_PREFIX . $player['cookie'], PlayerModel::RATING_CACHE_TTL, $deltaArr);
        Cache::setex(PlayerModel::DELTA_RATING_KEY_PREFIX . $player['found_cookie'], PlayerModel::RATING_CACHE_TTL, $deltaArr);
        Cache::setex(PlayerModel::DELTA_RATING_KEY_PREFIX . $player['common_id'], PlayerModel::RATING_CACHE_TTL, $deltaArr);


        if (isset($player['userID']) && $player['userID'] > 0) {
            Cache::setex(
                PlayerModel::DELTA_RATING_KEY_PREFIX . $player['cookie'] . $player['userID'],
                PlayerModel::RATING_CACHE_TTL,
                $deltaArr
            );
            Cache::setex(
                PlayerModel::DELTA_RATING_KEY_PREFIX . $player['found_cookie'] . $player['userID'],
                PlayerModel::RATING_CACHE_TTL,
                $deltaArr
            );
        }
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
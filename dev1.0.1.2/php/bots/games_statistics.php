<?php

include_once(__DIR__ . '/../CacheLangProvider.php');
include_once(__DIR__ . '/../hash_str_2_int.php');
include_once(__DIR__ . '/../DBLangProvider.php');

use Dadata\DB;

$red = \Dadata\Cache::getInstance();
$minutesToGo = 20;

$start_script_time = date('U');
$script_work_time = $minutesToGo * 60 - 5;

while ((date('U') - $start_script_time) < $script_work_time) {
    if (!($Game = $red->redis->lpop('erudit.games_ended'))) {
        sleep(10);
        continue;
    }
    $Game = unserialize($Game);
    if (!is_array($Game)) {
        continue;
    }

    $results = getRanks($Game);

    getRatings($results, $Game);
    changeRatings($results);
    saveGameStats($Game, $results);
    saveRatings($results);
    saveGame($Game, $results);
}

function saveGame(&$Game, &$results)
{
    $INSERTGAME = "INSERT INTO games
SET 
id={$Game['gameNumber']}+300000,
game_data = compress('" . DB::escapeString(serialize($Game)) . "')";

    if (DB::queryInsert($INSERTGAME)) {
        foreach ($Game['users'] as $num => $player) {
            $common_id = $player['common_id'];
            $user_id = isset($player['userID'])
                ? hash_str_2_int($player['userID'])
                : 0;
            $intCookie = hash_str_2_int($player['ID']);

            $INSERTPLAYER = "INSERT INTO game_players
SET 
game_id = {$Game['gameNumber']}+300000,
cookie = $intCookie,
user_id = $user_id,
common_id=$common_id";
            print $INSERTPLAYER;
            DB::queryInsert($INSERTPLAYER);
        }
    }
}

function saveGameStats(&$Game, &$results)
{
    $INSERTSTATS = "INSERT INTO games_stats
    SET
      game_id={$Game['gameNumber']}+300000
    , players_num = " . count($results) . "
    , game_ended_date = {$Game['turnBeginTime']}
    , winner_player_id = {$results[0]['player_id']}
    ";
    foreach ($results as $num => $player) {
        $INSERTSTATS .= "
        , " . ($Game[$player['cookie']] + 1) . "_player_id = {$player['player_id']}
        , " . ($Game[$player['cookie']] + 1) . "_player_rating_delta = {$player['deltaRating']}
        , " . ($Game[$player['cookie']] + 1) . "_player_old_rating = {$player['rating']}";
    }

    //print $INSERTSTATS;
    DB::queryInsert($INSERTSTATS);

    if (!DB::insertID()) {
        global $red;
        $red->redis->rpush(
            'erudit.games_statistics_failed',
            serialize(
                [
                    'query' => $INSERTSTATS,
                    'game' => $Game,
                    'results' => $results
                ]
            )
        );
    }
}

function addDeltaRatingsToCache($player)
{
    global $red;
    global $Game;

    $deltaArr = serialize(
        [
            'delta' => $player['deltaRating']
            ,
            'time' => $Game['turnBeginTime']
            ,
            'game_number' => $Game['gameNumber']
        ]
    );
    $cacheTime = 7 * 24 * 60 * 60;

    $red->redis->setex('erudit.delta_rating_' . $player['cookie'], $cacheTime, $deltaArr);
    $red->redis->setex('erudit.delta_rating_' . $player['found_cookie'], $cacheTime, $deltaArr);

    if (isset($player['userID']) && $player['userID'] > 0) {
        $red->redis->setex('erudit.delta_rating_' . $player['userID'], $cacheTime, $deltaArr);
        $red->redis->setex('erudit.delta_rating_' . $player['userID'], $cacheTime, $deltaArr);
    }
}

function deleteRatingsFromCache($player)
{
    global $red;

    $red->redis->del('erudit.rating_cache_' . $player['cookie']);
    $red->redis->del('erudit.rating_cache_' . $player['found_cookie']);

    if (isset($player['userID']) && $player['userID'] > 0) {
        $red->redis->del('erudit.rating_cache_' . $player['cookie'] . $player['userID']);
        $red->redis->del('erudit.rating_cache_' . $player['found_cookie'] . $player['userID']);
    }
}


function saveRatings(&$players)
{
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
                : hash_str_2_int($player['found_cookie'])) .
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
        //print $UPDATE;
        deleteRatingsFromCache($player);
        addDeltaRatingsToCache($player);
    }
}

function changeRatings(&$players)
{
    $K = 30 / (count($players) - 1);//коэффициент крутизны игрока (10,20,30)
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
            //print "Игрок {$i}: " . $ratingA . '->' . $ratingANew . "\n";

            //print "Игрок {$j}: " . $ratingB . '->' . $ratingBNew . "\n";
        }
    }
}

function addCookie(&$player)
{
    if (!is_array($player)) {
        return false;
    }
    $INSERTCOOKIE = "INSERT INTO players
                    SET
                    cookie='{$player['cookie']}',
                    user_id = " . ($player['userID']
            ? $player['userID']
            : hash_str_2_int($player['cookie'])) . ",
                    date_registered=UNIX_TIMESTAMP(),
                    first_played = UNIX_TIMESTAMP(),
                    rating={$player['rating']},
                    games_played={$player['games_played']}
                    ON DUPLICATE KEY
                    UPDATE
                    " . (isset($player['userID']) ?
            "user_id={$player['userID']},
                    date_registered=UNIX_TIMESTAMP(),"
            : "")
        . "rating={$player['rating']},
                    games_played={$player['games_played']}";
    //print $INSERTCOOKIE;
    DB::queryInsert($INSERTCOOKIE);
}

function getRatings(&$players, &$gameStatus)
{
    /**todo REFACTOR IT */
    $SELECTRATING = "SELECT rating, id as player_id, cookie, games_played FROM erudit.players WHERE cookie='";
    $SELECTRATING_REGISTERED1 = "SELECT 
                                    max(rating) as rating, 
                                    min(id) as player_id,
                                    CASE WHEN max(user_id) IS NULL THEN 0 ELSE max(user_id) END as user_id,
                                    max(cookie) as cookie,
                                    max(games_played) as games_played
                                    FROM erudit.players 
                                    WHERE cookie='";
    $SELECTRATING_REGISTERED2 = "' OR user_id = ";
    $SELECTRATING_REGISTERED3 = " GROUP BY gruping";

    foreach ($players as $num => $player) {
        if ($player['userID'] !== false) {
            $QUERY =
                $SELECTRATING_REGISTERED1 .
                $player['cookie'] .
                $SELECTRATING_REGISTERED2 .
                $player['userID'] .
                $SELECTRATING_REGISTERED3;
            //print $QUERY;
            $sel = DB::queryArray($QUERY)[0];
            if (!empty($sel['rating'])) {
                $players[$num]['rating'] = $sel['rating'];
                $players[$num]['player_id'] = $sel['player_id'];
                $players[$num]['user_id'] = $sel['user_id'];
                $players[$num]['found_cookie'] = $sel['cookie'];
                $players[$num]['games_played'] = $sel['games_played'];

                if (!$players[$num]['user_id']) {
                    $UPDATE = "UPDATE erudit.players SET
                            user_id = {$player['userID']}
                            , date_registered=UNIX_TIMESTAMP()
                            WHERE
                            cookie='{$players[$num]['found_cookie']}'
                            ";
                    DB::queryInsert($UPDATE);
                    //print $UPDATE; sleep(5);
                    $players[$num]['user_id'] = $player['userID'];
                }

                if ($players[$num]['found_cookie'] !== $player['cookie']) {
                    addCookie($players[$num]);
                }
            } else {
                $INSERT = "INSERT INTO erudit.players SET
                            cookie='{$player['cookie']}',
                            first_played = UNIX_TIMESTAMP(),
                            rating=1700,
                            rating_changed_date = CURRENT_TIMESTAMP(),
                            games_played = 1,
                            user_id = {$player['userID']},
                            date_registered = UNIX_TIMESTAMP() 
                            ";
                DB::queryInsert($INSERT);
                //print $INSERT;
                $players[$num]['rating'] = 1700;
                $players[$num]['player_id'] = DB::insertID();
                $players[$num]['user_id'] = $player['userID'];
                $players[$num]['found_cookie'] = $player['cookie'];
                $players[$num]['games_played'] = 1;
            }
        } else {
            $players[$num]['user_id'] = hash_str_2_int($player['cookie']);
            $QUERY = $SELECTRATING . $player['cookie'] . "'";
            //print $QUERY;
            $sel = DB::queryArray($QUERY)[0];
            if (!empty($sel['rating'])) {
                $players[$num]['rating'] = $sel['rating'];
                $players[$num]['player_id'] = $sel['player_id'];
                $players[$num]['found_cookie'] = $sel['cookie'];
                $players[$num]['games_played'] = $sel['games_played'];
            } else {
                $INSERT = "INSERT INTO erudit.players SET
                            cookie='{$player['cookie']}',
                            first_played=UNIX_TIMESTAMP(),
                            rating=1700,
                            rating_changed_date=CURRENT_TIMESTAMP(),
                            games_played = 1,
                            user_id = " . $players[$num]['user_id'];
                DB::queryInsert($INSERT);
                //print $INSERT;
                $players[$num]['rating'] = 1700;
                $players[$num]['player_id'] = DB::insertID();
                $players[$num]['found_cookie'] = $player['cookie'];
                $players[$num]['games_played'] = 1;
            }
        }
        if (isset($gameStatus['users'][$gameStatus[$player['cookie']]]['rating'])) {
            $rating = $gameStatus['users'][$gameStatus[$player['cookie']]]['rating'];
            if ($rating != 'new_player') {
                $players[$num]['rating'] = $rating;
            }
            //Коррекция рейтинга по данным из Игры
            $players[$num]['common_id'] = $gameStatus['users'][$gameStatus[$player['cookie']]]['common_id'];
        }
    }

    return $players;
}

function getRanks(&$Game)
{
    $winner = [];
    $winner['cookie'] = $Game['results']['winner'];
    $winner['score'] = $Game['users'][$Game[$winner['cookie']]]['score'];
    $winner['isActive'] = true;
    $winner['userID'] = isset($Game['users'][$Game[$winner['cookie']]]['userID'])
        ? hash_str_2_int($Game['users'][$Game[$winner['cookie']]]['userID'])
        : false;

    $lostPlayers = [];

    foreach ($Game['results']['lostUsers'] as $num => $cookie) {
        $lostPlayers[$num]['cookie'] = $cookie;
        $lostPlayers[$num]['score'] = $Game['users'][$Game[$cookie]]['score'];
        $lostPlayers[$num]['isActive'] = (isset($Game['users'][$Game[$cookie]]['lastActiveTime'])
            ? true
            : false);
        $lostPlayers[$num]['userID'] = isset($Game['users'][$Game[$cookie]]['userID'])
            ? hash_str_2_int($Game['users'][$Game[$cookie]]['userID'])
            : false;
    }

    usort($lostPlayers, 'arComp');
    $resultsArray = array_merge([$winner], $lostPlayers);
    return $resultsArray;
}


function arComp($a, $b)
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

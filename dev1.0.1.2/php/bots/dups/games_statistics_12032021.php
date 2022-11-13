<?php
include_once(__DIR__.'/../../../vendor/deprecated/xcache_functions.php');
include_once(__DIR__.'/../CacheLangProvider.php');
include_once(__DIR__.'/../hash_str_2_int.php');
$red = \Dadata\Cache::getInstance();
$minutesToGo = 20;

$start_script_time = date('U');
$script_work_time = $minutesToGo*60 - 5;

while  ( (date('U') - $start_script_time) < $script_work_time) {
    if(!($Game = $red->redis->lpop('erudit.games_ended')))
    {
        sleep(10);
        continue;
    }
    $Game = unserialize($Game);
    if (!is_array($Game))
        continue;        
    //print_r($Game);
    $results=getRanks($Game);
    file_put_contents('/var/www/erudit.club/ranks',print_r($results,true));
    getRatings($results);
    changeRatings($results);
    saveGameStats($Game,$results);
    saveRatings($results);
    
    print_r($results);
    //exit();
}

function saveGameStats(&$Game,&$results)
{
    $INSERTSTATS="INSERT INTO games_stats
    SET
      game_id={$Game['gameNumber']}+100000
    , players_num = ".count($results)."
    , game_ended_date = {$Game['turnBeginTime']}
    , winner_player_id = {$results[0]['player_id']}
    ";
    foreach($results as $num => $player)
        $INSERTSTATS .= "
        , ".($Game[$player['cookie']]+1)."_player_id = {$player['player_id']}
        , ".($Game[$player['cookie']]+1)."_player_rating_delta = {$player['deltaRating']}
        , ".($Game[$player['cookie']]+1)."_player_old_rating = {$player['rating']}";
   
    print $INSERTSTATS;
    mysql_query($INSERTSTATS);
}


function saveRatings(&$players)
{
    foreach($players as $num => $player) {
    $UPDATE = "UPDATE erudit.players 
                    SET
                          rating=".($player['rating']+$player['deltaRating'])."
                        , win_percent = round((select sum(1) from games_stats where        winner_player_id = players.id)*100/(games_played+1))
                        , games_played = games_played+1
                        , inactive_percent = CASE 
                        WHEN games_played = 0 
                        THEN inactive_percent 
                        ELSE ((games_played/100*inactive_percent".($player['isActive'] ? '' : '+1').")/(games_played+1)*100) 
                        END
                        , rating_changed_date = CURRENT_TIMESTAMP()
                        WHERE cookie = '{$player['cookie']}'
                        ";
       mysql_query($UPDATE);
print        $UPDATE;
    }
}
    
function changeRatings(&$players)
{
    $K=30/(count($players)-1);//коэффициент крутизны игрока (10,20,30)
    for ($i=0; $i<(count($players)-1); $i++)
        for($j=$i+1; $j<count($players); $j++) {
            $ratingA = $players[$i]['rating'];
            $ratingB = $players[$j]['rating'];
            $EA = 1/( 1 + 10**(($ratingB-$ratingA)/400)) ;
            print "EA: ".$EA."\n";
            $ratingANew = round($ratingA + $K*(1 - $EA));
            $ratingBNew = $ratingB - ($ratingANew - $ratingA);
            $players[$i]['deltaRating'] += ($ratingANew - $ratingA);
            $players[$j]['deltaRating'] += ($ratingBNew - $ratingB);
            print "Игрок {$i}: ".$ratingA.'->'.$ratingANew."\n";
           
            print "Игрок {$j}: ".$ratingB.'->'.$ratingBNew."\n";
        }
        
}    

function addCookie(&$player) {
    if (!is_array($player)
        return false;
    $INSERTCOOKIE="INSERT INTO players
                    SET
                    cookie='{$player['cookie']}',
                    ".(isset($player['userID']) ? 
                    "user_id={$player['userID']},
                    date_registered=UNIX_TIMESTAMP(),"
                    :"") 
                    ."first_played = UNIX_TIMESTAMP(),
                    rating={$player['rating']},
                    games_played={$player['games_played']}
                    ON DUPLICATE KEY
                    UPDATE
                    ".(isset($player['userID']) ? 
                    "user_id={$player['userID']},
                    date_registered=UNIX_TIMESTAMP(),"
                    :"") 
                    ."rating={$player['rating']},
                    games_played={$player['games_played']}";
    print $INSERTCOOKIE;
    mysql_query($INSERTCOOKIE);
}
    
function getRatings(&$players) {
    $SELECTRATING = "SELECT rating, id FROM erudit.players WHERE cookie='";
    $SELECTRATING_REGISTERED1 = "SELECT 
                                    max(rating) as rating, 
                                    max(id) as player_id,
                                    CASE WHEN max(user_id) IS NULL THEN 0 ELSE max(user_id) END as user_id,
                                    max(cookie) as cookie,
                                    max(games_played) as games_played
                                    FROM erudit.players 
                                    WHERE cookie='";
    $SELECTRATING_REGISTERED2=     "' OR user_id = ";
    $SELECTRATING_REGISTERED3=     " GROUP BY gruping";    
    foreach ($players as $num => $player) {
        if ($player['userID'] !== false) {
            $QUERY=
                              $SELECTRATING_REGISTERED1.
                              $player['cookie'].
                              $SELECTRATING_REGISTERED2.
                              $player['userID'].
                              $SELECTRATING_REGISTERED3;
            print $QUERY;
            if ($rating = mysql_result($sel=mysql_query($QUERY),0,0)) {
                $players[$num]['rating'] = $rating;
                $players[$num]['player_id'] = mysql_result($sel,0,1);
                $players[$num]['user_id'] = mysql_result($sel,0,2);
                $players[$num]['found_cookie'] = mysql_result($sel,0,3);
                $players[$num]['games_played'] = mysql_result($sel,0,4);
                
                if (!$players[$num]['user_id']) {
                    mysql_query($UPDATE = "UPDATE erudit.players SET
                            user_id = {$player['userID']}
                            , date_registered=UNIX_TIMESTAMP()
                            WHERE
                            cookie='{$players[$num]['found_cookie']}'
                            ");
                    //print $UPDATE; sleep(5);
                    $players[$num]['user_id'] = $player['userID'];
                }
                
                if ($players[$num]['found_cookie'] !== $player['cookie'])
                    addCookie($players[$num]);
            }
            if(!$rating) {
                
                mysql_query($INSERT = "INSERT INTO erudit.players SET
                            cookie='{$player['cookie']}',
                            first_played = UNIX_TIMESTAMP(),
                            rating=1700,
                            rating_changed_date = CURRENT_TIMESTAMP(),
                            user_id = {$player['userID']},
                            date_registered = UNIX_TIMESTAMP() 
                            ");
                print $INSERT;
                $players[$num]['rating'] = 1700;
                $players[$num]['player_id'] = mysql_insert_id();
                $players[$num]['user_id'] = $player['userID'];
            }
        }
        else {
            if ($rating = mysql_result($sel=mysql_query($QUERY = ($SELECTRATING.$player['cookie']."'")),0,0)) 
            {
                $players[$num]['rating'] = $rating;
                $players[$num]['player_id'] = mysql_result($sel,0,1);
                
            }
            print $QUERY;
            if(!$rating) {
                mysql_query($INSERT = "INSERT INTO erudit.players SET
                            cookie='{$player['cookie']}',
                            first_played=UNIX_TIMESTAMP(),
                            rating=1700,
                            rating_changed_date=CURRENT_TIMESTAMP()
                            ");
                print $INSERT;
                $players[$num]['rating'] = 1700;
                $players[$num]['player_id'] = mysql_insert_id();
            }
        }
        
    }
    return $players;
}

    function getRanks(&$Game) {
        $winner = [];
        $winner['cookie'] = $Game['results']['winner'];
        $winner['score'] = $Game['users'][$Game[$winner['cookie']]]['score'];
        $winner['isActive'] = TRUE;
        $winner['userID'] = isset($Game['users'][$Game[$winner['cookie']]]['userID']) 
            ? hash_str_2_int($Game['users'][$Game[$winner['cookie']]]['userID']) 
            : false;
        
        $lostPlayers=[];
        
        foreach($Game['results']['lostUsers'] as $num => $cookie) {
            $lostPlayers[$num]['cookie'] = $cookie;
            $lostPlayers[$num]['score'] = $Game['users'][$Game[$cookie]]['score'];
            $lostPlayers[$num]['isActive'] =    (isset($Game['users'][$Game[$cookie]]['lastActiveTime'])
                                                ? true 
                                                : false);
            $lostPlayers[$num]['userID'] = isset($Game['users'][$Game[$cookie]]['userID']) 
            ? hash_str_2_int($Game['users'][$Game[$cookie]]['userID']) 
            : false;
        }
        
        $resultsArray=array_merge($lostPlayers,[$winner]);
        usort($resultsArray,arComp);
        print_r($resultsArray);
        return $resultsArray;
    }


function arComp($a, $b)
{
    if($a['isActive'] && $b['isActive']) {
        if ($a['score'] == $b['score']) {
            return 0;
        }
        return ($a['score'] > $b['score']) ? -1 : 1;
    }
    
    elseif($a['isActive'] && !$b['isActive'])
        return -1;
        
    elseif(!$a['isActive'] && $b['isActive'])
        return 1;
        
    elseif (!$a['isActive'] && !$b['isActive']){
        if ($a['score'] == $b['score']) {
            return 0;
        }
        return ($a['score'] > $b['score']) ? -1 : 1;
    }
}

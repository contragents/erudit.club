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
      game_id={$Game['gameNumber']}
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
                        WHERE cookie = '{$player['cookie']}'
                        ";
       mysql_query($UPDATE); 
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
    
function getRatings(&$players) {
    $SELECTRATING = "SELECT rating, id FROM erudit.players WHERE cookie='";
    $SELECTRATING_REGISTERED1 = "SELECT 
                                    max(rating), 
                                    id, 
                                    CASE wHEN user_id IS NULL THEN 0 ELSE user_id END as user_id
                                    FROM erudit.players 
                                    WHERE cookie='";
    $SELECTRATING_REGISTERED2=     "' OR user_id = ";  
    foreach ($players as $num => $player) {
        if ($player['userID'] !== false) {
            if ($rating = mysql_result(
                              $sel=mysql_query($QUERY=
                              $SELECTRATING_REGISTERED1.
                              $player['cookie'].
                              $SELECTRATING_REGISTERED2.
                              $player['userID'])
                          ,0,0)) {
                $players[$num]['rating'] = $rating;
                $players[$num]['player_id'] = mysql_result($sel,0,1);
                $players[$num]['user_id'] = mysql_result($sel,0,2);
                
                if (!$players[$num]['user_id']) {
                    mysql_query($UPDATE = "UPDATE erudit.players SET
                            user_id = {$player['userID']}
                            , date_registered=UNIX_TIMESTAMP()
                            WHERE
                            cookie='{$player['cookie']}'
                            ");
                    //print $UPDATE; sleep(5);
                    $players[$num]['user_id'] = $player['userID'];
                }
            }
            //print $QUERY; sleep(5);
        }
        else {
            if ($rating = mysql_result($sel=mysql_query($SELECTRATING.$player['cookie']."'"),0,0)) 
            {
                $players[$num]['rating'] = $rating;
                $players[$num]['player_id'] = mysql_result($sel,0,1);
                
            }
            if(!$rating) {
                mysql_query($INSERT = "INSERT INTO erudit.players SET
                            cookie='{$player['cookie']}',
                            first_played=UNIX_TIMESTAMP(),
                            rating=1700,
                            rating_changed_date=UNIX_TIMESTAMP()
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
            $lostPlayers[$num]['isActive'] = $Game['users'][$Game[$cookie]]['isActive'];
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

<?php
include_once(__DIR__.'/../../../vendor/deprecated/xcache_functions.php');
include_once(__DIR__.'/../CacheLangProvider.php');
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
    getRatings($results);
    changeRatings($results);
    saveRatings($results);
    saveGameStats($Game,$results);
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
    foreach($players as $player)
       mysql_query($UPDATE = "UPDATE erudit.players SET
                        rating=".($player['rating']+$player['deltaRating'])."
                        , games_played = games_played+1
                        WHERE cookie = '{$player['cookie']}'
                        "); 
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
    $SELECTRATING = "SELECT rating,id FROM erudit.players WHERE cookie='";
    foreach ($players as $num => $player) {
        if ($rating = mysql_result($sel=mysql_query($SELECTRATING.$player['cookie']."'"),0,0)) 
        {
            $players[$num]['rating'] = $rating;
            $players[$num]['player_id'] = mysql_result($sel,0,1);
            
        }
        else {
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
    return $players;
}

    function getRanks(&$Game) {
        $winnerCookie = [];
        $winnerCookie['cookie'] = $Game['results']['winner'];
        $winnerCookie['score'] = $Game['users'][$Game[$winnerCookie['cookie']]]['score'];
        $lostCookies=[];
        
        foreach($Game['results']['lostUsers'] as $num => $cookie) {
            $lostCookies[$num]['cookie'] = $cookie;
            $lostCookies[$num]['score'] = $Game['users'][$Game[$cookie]]['score'];
        }
        
        $resultsArray=array_merge($lostCookies,[$winnerCookie]);
        usort($resultsArray,arComp);
        print_r($resultsArray);
        return $resultsArray;
    }


function arComp($a, $b)
{
    if ($a['score'] == $b['score']) {
        return 0;
    }
    return ($a['score'] > $b['score']) ? -1 : 1;
}

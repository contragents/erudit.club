<?php
require_once(__DIR__ . '/../private/deprecated/xcache_functions.php');
$time_to_work = 600;
$start_script_time = date('U');

while ( (date('U') - $start_script_time) < $time_to_work) {
    if ( !($slovo = getNextWord()) ) exit();
		$html=file_get_contents("https://gufo.me/search?term=".urlencode($slovo));
		if ($html == '') exit();
		//print substr($html,0,10000);
		$sl=[];
		if (preg_match_all('/<ol>[\s\S]+?<\/ol>/u',$html,$matches)) {
			//print($matches[0][0]);
            //print($matches[0][1]);
            saveWord($slovo,mysql_real_escape_string($matches[0][0]),mysql_real_escape_string($matches[0][1]));
            
            
		}
        else
            saveWord($slovo,'none','');
        print $slovo."\n";
        sleep(1);
		//exit();
	}

function getNextWord() {
    GLOBAL $UTMLink;
    $query="SELECT slovo FROM gufo_me WHERE content = '' LIMIT 1;";
	if ( !($res = mysql_query($query)) )
        return false;
    else return mysql_result($res,0,0);
    
}



function saveWord($word, $content, $perevod) {
	GLOBAL $UTMLink;
	
	$query="UPDATE gufo_me SET content='$content', content_perevod='$perevod' WHERE slovo='$word';";
	return mysql_query($query);
}
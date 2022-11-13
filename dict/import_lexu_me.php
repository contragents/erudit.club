<?php
require_once('/var/www/erudit.club/vendor/deprecated/xcache_functions.php');
$time_to_work = 600;
$start_script_time = date('U');
$letters = ['a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z'];
$baseUrl="http://lexu.me/index.php?word=";
/*
$example = "<a href='index.php?m_w=age&word=age'>age</a><br/><a href='index.php?m_w=aged&word=age'>aged</a><br/><a href='index.php?m_w=ageless&word=age'>ageless</a><br/><a href='index.php?m_w=age-limit&word=age'>age-limit</a><br/><a href='index.php?m_w=agency&word=age'>agency</a><br/><a href='index.php?m_w=agenda&word=age'>agenda</a><br/><a href='index.php?m_w=agent&word=age'>agent</a><br/>    </div>";
$index = 'age';
$spisokSlov=file_get_contents( $baseUrl.'age');
$spisokSlov = explode('<tr>',$spisokSlov);
print_r($spisokSlov);
foreach($spisokSlov as $slovs)
if (preg_match_all($pattern = '/word='.$index.'\'>([\s\S]{3,15})<\/a>/i',$slovs,$matches)) {
			print_r($matches);
            //print($matches[0][1]);
            
            //saveWords(mysql_real_escape_string($matches[0][0]));
                }
                exit();
*/
for ($i = 0; $i < count($letters); $i++)
    for ($j = 0; $j < count($letters); $j++)
        for ($k = 0; $k < count($letters); $k++) {
            if (!(($i==$j) && ($j==$k))) {
                $spisokSlov=file_get_contents( $baseUrl.($index = $letters[$i].$letters[$j].$letters[$k]) );
                
                //print $spisokSlov;
                
                if (preg_match_all($pattern = '/word='.$index.'\'>([\s\S]{3,15})<\/a>/i',$spisokSlov,$matches)) {
			print_r($matches);
            //print($matches[0][1]);
            
            saveWords($matches[1]);
            
                }
                else print $index."\n";
            }
        }
        exit();


while ( (date('U') - $start_script_time) < $time_to_work) {
    if ( !($slovo = getNextWord()) ) exit();
		$html=file_get_contents("http://lexu.me/index.php?word=".urlencode($slovo));
		if ($html == '') exit();
		//print substr($html,0,10000);
		$sl=[];
		if (preg_match_all('/<ol>[\s\S]+?<\/ol>/u',$html,$matches)) {
			//print($matches[0][0]);
            //print($matches[0][1]);
            saveWords($slovo,mysql_real_escape_string($matches[0][0]),mysql_real_escape_string($matches[0][1]));
            
            
		}
        
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


function saveWords($words) {
	GLOBAL $UTMLink;
	foreach($words as $word)
        if(strpos($word,'-') === false) {
	$query="INSERT into dict_lexu_me SET slovo='".strtolower($word)."';";
	return mysql_query($query);
        }
}


function saveWord($word, $content, $perevod) {
	GLOBAL $UTMLink;
	
	$query="UPDATE gufo_me SET content='$content', content_perevod='$perevod' WHERE slovo='$word';";
	return mysql_query($query);
}
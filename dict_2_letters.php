

<?php
if (isset($_GET['lng']) && $_GET['lng'] == 'EN') {
$lng = '?lng=EN';
$titleENG = 'на Английском ';
$slovar = '<a href="https://dictionary.cambridge.org/ru/%D1%81%D0%BB%D0%BE%D0%B2%D0%B0%D1%80%D1%8C/">Кембриджскй Англо-Русский словарь</a>';
$table = 'dict_cambrige';
} else {
$lng = '';
$titleENG = '';
$slovar ='';
$table = 'gufo_me';
}

$title="Игра {$titleENG}Эрудит.CLUB :: Слова из ".((isset($_GET['strlen']) && ($_GET['strlen']>2) ) ? $_GET['strlen'] : двух).' букв' ;

$descr = 'Эрудит - классическая настольная игра - теперь в онлайн-версии! Присоединяйтесь к Русскому или английскому столу с игрой. Не забудьте предварительно почитать основные словари, особенно слова из 2-х - 4-х букв';
include ('tpl/main_header.php');
//exit();
if (isset($_GET['strlen']) && $_GET['strlen']>2)
    $strlen = $_GET['strlen'];
else
    $strlen = 2;
if ($_SERVER['HTTP_REFERER'] != 'https://xn--d1aiwkc2d.club/')
    $play_erudit = "&nbsp;<span style=\"white-space:nowrap\"><a style=\"color:#60b442; text-decoration:none;\" href=\"/\" title=\"ИГРАТЬ!\" target=\"_blank\">ИГРАТЬ В ЭРУДИТ!</a></span>";
print '<H1 style="line-height:30px;">Словарь Эрудита '.$titleENG.'- слова из '.$strlen.($strlen<=4 ? '-х' : '-ти').' букв'.$play_erudit.'</H1>';


for ($i=2; $i<=6; $i++)
    if ($strlen != $i)
        print "<H3><a href=\"/dict$i/$lng\">Слова из $i".($i<=4 ? '-х' : '-ти')." букв</a></h3>";
require_once('vendor/deprecated/xcache_functions.php');


$CONTENT_SELECT="SELECT slovo FROM  $table where CHAR_LENGTH(slovo) = $strlen ;";
$res=mysql_query($CONTENT_SELECT);
$first_letter = '';
while ($row = mysql_fetch_assoc($res)) {
    if ($first_letter != mb_substr($row['slovo'],0,1,'UTF-8')) {
        print "<br />";
        $first_letter = mb_substr($row['slovo'],0,1,'UTF-8');
    }
    print "<a href=\"/dict/".urlencode($row['slovo'])."\" target=\"_blank\" title=\"Перейти к слову\">{$row['slovo']}</a>&nbsp;";
}
exit();
    
//print_r($_GET);
/*if (isset($_GET['voc']))
    $content = file_get_contents('https://gufo.me/dict/'.$_GET['voc'].'/'.urlencode($_GET['word']));
else
    $content = file_get_contents('https://gufo.me/search?term='.urlencode($_GET['word']));
*/
if (preg_match_all('/<section[\s\S]+?<\/section>/u',$content,$matches)) {
			//print($matches[0][0]);
            //print($matches[0][1]);
            foreach($matches[0] as $match)
                print str_replace(['Gufo.me','action="/search"','action="/user/word"'],['<a href="https://xn--d1aiwkc2d.club">Эрудит.CLUB</a>','action=""','action=""'],$match);
}
?>
</div>
</div>
</div>
</div>
</div>
</body>
</html>
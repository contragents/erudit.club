<?php
require_once('../vendor/deprecated/xcache_functions.php');

$ssilki=[
		'01'=>[101804,105342],
		'02'=>[105343,113133],
		'03'=>[113201,129507],
		'04'=>[129508,135919],
		'05'=>[135920,146037],
		'06'=>[146038,146815],
		'07'=>[146901,149005],
		'08'=>[149006,162532],
		'09'=>[162533,169627],
		'10'=>[169628,169645],
		'11'=>[200901,215723],
		'12'=>[215724,221240],
		'13'=>[221301,232121],
		'14'=>[232122,251716],
		'15'=>[251717,273640],
		'16'=>[300901,357415],
		'17'=>[357415,375017],
		'18'=>[400901,432832],
		'19'=>[432901,444111],
		'20'=>[444112,454710],
		'21'=>[454710,458931],
		'22'=>[459001,463130],
		'23'=>[463131,465011],
		'24'=>[465012,469603],
		'25'=>[469604,473825],
		'26'=>[473826,474428],
		'30'=>[474501,477228],
		'31'=>[477301,477624],
		'32'=>[477624,478729]
		];
foreach($ssilki as $dir => $numbers) 
	for ($i=$numbers[0];$i<=$numbers[1];$i++) {
		$html=mb_convert_encoding(file_get_contents("http://feb-web.ru/feb/mas/mas-abc/$dir/ma$i.htm?cmd=2&istext=1"),'UTF-8','windows-1251');
		if ($html == '') continue;
		//print substr($html,0,10000);
		$sl=[];
		if (preg_match('/<p><b>(.+)<\/b>([\s\S]+)[^а-яёА-ЯЁ](ж|м|ср|мн)\.([\s\S]+)<\/p>/u',$html,$matches)) {
			$sl['slovo'] = $matches[1];
			$sl['slovo'] = clear_slovo($sl['slovo']);
			$sl['rod'] = $matches[3];
			$sl['descr'] = $matches[4];
			$sl['ssylka'] = check_ssylka($sl['descr']);
			$sl['descr'] = clear_descr($sl['descr']);
		}
		if ($sl['slovo'] !='')
			print save_word($sl['slovo'], $sl['descr'], $sl['rod'], $sl['ssylka']) . '-'.$i." {$sl['slovo']}\n";
	}



function clear_descr($descr) {
	$descr = strip_tags($descr);
	return $descr;
}

function clear_slovo($slovo) {
	$replaces=['&#x301;','<sup>','</sup>','1','2','3','4','5','6','7','8','9','0'];
	$slovo = str_replace($replaces,'',$slovo);
	if (preg_match('/^[а-яёА-ЯЁ]{2,15}$/u',$slovo))
		return mb_strtolower($slovo);
	else
		return FALSE;
}

function check_ssylka($descr) {
	if (preg_match('/<a href(.+)>([а-яёА-ЯЁ]{2,15})</u',$descr,$matches))
		return mb_strtolower($matches[2]);
	else return '';
}

function parse_buf($buf) {
	$parsed_buf = FALSE;
	if (preg_match('/(м|ж|ср|мн)\./u',$buf[1],$rod)) {
		$parsed_buf = [];
		$parsed_buf['word'] = $buf[0];
		$parsed_buf['rod'] = $rod[1];
		if (preg_match('/см\. ([абвгдеёжзиклмнопрстуфхцчшщыьъэюя]{2,15})/u',$buf[2].'.',$matches))
			$parsed_buf['ssylka'] = $matches[1];
		//else 
			for($i=2;$i<count($buf);$i++)
				$parsed_buf['descr'] .= $buf[$i];
	}	
	return $parsed_buf;
}

function save_word($word, $descr, $rod, $ssylka = '') {
	GLOBAL $UTMLink;
	$descr = mysql_real_escape_string($descr);
	$query="INSERT INTO dict SET slovo='$word', comment='$descr', rod='$rod', ssylka='$ssylka';";
	if (!mysql_query($query))
		var_dump($UTMLink);
	else return 1;
}
<?php
require_once('../vendor/deprecated/xcache_functions.php');
$f=fopen('efremova.txt','r');
$i=0;
$buf = [];
while(!feof($f)) {
	$buf [] = trim(($str = mb_convert_encoding(fgets($f),'UTF-8','Windows-1251')));
	if (trim($str) == '') {
		unset($buf[count($buf)-1]);
		if ($parsed_buf = parse_buf($buf))
			save_word($parsed_buf['word'], $parsed_buf['descr'], $parsed_buf['rod'], $parsed_buf['ssylka']);
		print_r($buf) ; print "\n\n\n";
		$buf = [];
		$i++;
	}
	//if ($i>100) exit();
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
	$descr = mysql_real_escape_string($descr);
	return mysql_query("INSERT INTO dict SET slovo='$word', comment='$descr', rod='$rod', ssylka='$ssylka';");
}
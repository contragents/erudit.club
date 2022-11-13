<?php
require_once('/var/www/erudit.club/vendor/deprecated/xcache_functions.php');
$time_to_work = 600;
$start_script_time = date('U');
$letters = ['a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z'];
$baseUrl="https://dictionary.cambridge.org/ru/%D0%BF%D1%80%D0%BE%D1%81%D0%BC%D0%B0%D1%82%D1%80%D0%B8%D0%B2%D0%B0%D1%82%D1%8C/%D0%B0%D0%BD%D0%B3%D0%BB%D0%BE-%D1%80%D1%83%D1%81%D1%81%D0%BA%D0%B8%D0%B9/";
$letters = file_get_contents($baseUrl);
preg_match_all($pattern = '/class=\'hbtn hbtn-tab-b tc-d tb  bw \'>([\s\S]{1})<\/a>/i',$letters,$matches);
print_r($matches);
//exit();
$example='<a class="dil tcbd" href="https://dictionary.cambridge.org/ru/%D0%BF%D1%80%D0%BE%D1%81%D0%BC%D0%B0%D1%82%D1%80%D0%B8%D0%B2%D0%B0%D1%82%D1%8C/%D0%B0%D0%BD%D0%B3%D0%BB%D0%BE-%D1%80%D1%83%D1%81%D1%81%D0%BA%D0%B8%D0%B9/a/a/"
                                        title="a ... abattoir">';
                                        
foreach($matches[1] as $letter) {
    $letterWords = file_get_contents($baseUrl.$letter.'/');
    
    preg_match_all($pattern = '/dil tcbd" href="([\s\S]{160,240})"/i',$letterWords,$matches);
print_r($matches);
foreach($matches[1] as $wordsLink) {
    $wordLinks = file_get_contents($wordsLink);
    if (preg_match_all('/tc\-bd" href="([\s\S]{115,145})"/i',$wordLinks,$matches1))
        foreach($matches1[1] as $wordLink) {
            if (preg_match('/event or thing\.">noun/i',$wordContents = file_get_contents('https://dictionary.cambridge.org'.$wordLink)))
                if ((preg_match('/class="hw dhw">([\s\S]{2,15})<\/span><\/span><\/div>/i',$wordContents,$word))) {
                    $word=$word[1];
                    
                    preg_match('/pr x lbb lb\-cm([\s\S]{50,30000})lbt lb\-cm lpb\-10 lpt\-10 lpb\-25 lmb\-10 ddef had hdb/i',$wordContents,$mask);
                    $mask[1] = xss_clean($mask[1]);
                    $mask[1] = strip_tags($mask[1]);
                    $mask[1] = str_replace("Your browser doesn't support HTML5 audio",'',$mask[1]);
                    for ($i=0;$i<10;$i++)
                        $mask[1] = str_replace(["  ","\r\r","\n\n","\r\n\r\n",'">'],'',$mask[1]);
                    $mask[1] = str_replace($word,' '.$word.' ',$mask[1]);

                    
                    print $mask[1];
                  
                    saveWord($word, $mask[1]);

                    
                }
                else
                print $wordLink."\n";
        }
}
}
exit();


function xss_clean($data)
{
// Fix &entity\n;
$data = str_replace(array('&amp;','&lt;','&gt;'), array('&amp;amp;','&amp;lt;','&amp;gt;'), $data);
$data = preg_replace('/(&#*\w+)[\x00-\x20]+;/u', '$1;', $data);
$data = preg_replace('/(&#x*[0-9A-F]+);*/iu', '$1;', $data);
$data = html_entity_decode($data, ENT_COMPAT, 'UTF-8');

// Remove any attribute starting with "on" or xmlns
$data = preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+>#iu', '$1>', $data);

// Remove javascript: and vbscript: protocols
$data = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2nojavascript...', $data);
$data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2novbscript...', $data);
$data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u', '$1=$2nomozbinding...', $data);

// Only works in IE: <span style="width: expression(alert('Ping!'));"></span>
$data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[\'"]*.*?expression[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
$data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[\'"]*.*?behaviour[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
$data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#iu', '$1>', $data);

// Remove namespaced elements (we do not need them)
$data = preg_replace('#</*\w+:\w[^>]*+>#i', '', $data);
do
{
    // Remove really unwanted tags
    $old_data = $data;
    $data = preg_replace('#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i', '', $data);
}
while ($old_data !== $data);

// we are done...
return $data;
}



function saveWord($word, $content) {
	GLOBAL $UTMLink;
	
	$query="insert into dict_cambrige SET slovo = '$word', comment=\"$content\"
    ON DUPLICATE KEY
    UPDATE
    comment = \"$content\";";
	return mysql_query($query);
}
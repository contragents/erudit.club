<?php
if (isset($_SERVER['HTTP_ORIGIN']) && $_SERVER['HTTP_ORIGIN'] != '') {
    header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
    header('Access-Control-Allow-Credentials: true');
}

include_once(__DIR__ . '/DBLangProvider.php');

$CONTENT_SELECT = "SELECT 
content COLLATE utf8_general_ci, 
content_perevod COLLATE utf8_general_ci
FROM 
gufo_me 
WHERE 
slovo = '" . urldecode($_GET['word']) . "'
UNION
SELECT
comment COLLATE utf8_general_ci as content,
substring(comment,1,0) COLLATE utf8_general_ci as content_perevod
FROM
dict_cambrige
WHERE 
slovo = '" . urldecode($_GET['word']) . "';";

//print $CONTENT_SELECT;
$res = \Dadata\DB::queryArray($CONTENT_SELECT);
if (!is_array($res)) {
    print "Слово не найдено.";
    return false;
}

$row = $res[0];

foreach ($row as $field => $value)
    if ($spacePos = strpos($field, ' '))
        $row[substr($field, 0, $spacePos)] = $value;

if (strstr($_SERVER['HTTP_REFERER'], 'andex') || strstr($_SERVER['HTTP_REFERER'], '-5.su')) {
    $row['content'] = str_replace('href=', '', $row['content']);
    $row['content_perevod'] = str_replace('href=', '', $row['content_perevod']);
}

$row['content'] = str_ireplace($_GET['word'] . ' noun', '<h2>' . strtoupper($_GET['word']) . ' noun</h2>', $row['content']);

print str_replace(["\r\n", "\n"], '<br />',
    str_replace('href="', 'href="https://xn--d1aiwkc2d.club', $row['content'] . $row['content_perevod'])
);

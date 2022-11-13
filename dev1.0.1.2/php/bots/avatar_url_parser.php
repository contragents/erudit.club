<?php
include_once(__DIR__ . '/../DBLangProvider.php');

use \Dadata\DB;

$minutesToGo = 60;
$site_donor = "https://avatarko.ru";
$min_img_id = 2;
$max_img_id = 34768;

$start_script_time = date('U');
$script_work_time = $minutesToGo * 60 - 5;
$cur_img_id = get_cur_img_id($site_donor) ?: $min_img_id;

while ((date('U') - $start_script_time) < $script_work_time){

    //print $cur_img_id; exit();
    $urls = parse_page($site_donor . '/kartinka/' . $cur_img_id);

    if ($urls != []) {
        add_to_table($urls, $cur_img_id, $site_donor);
    }

    if ($cur_img_id == $max_img_id) {
        exit();
    }

    sleep(1);
    $cur_img_id++;

}

function add_to_table($imgs, $kartinka_id, $site)
{
    foreach ($imgs as $type => $img_set){
        foreach ($img_set as $img){
            if ($img['id'] == 0 && $type != 'kartinka'){
                continue;
            }

            $INSERTQUERY = "INSERT IGNORE INTO avatar_urls
                SET 
                site = '$site',
                site_img_id = " . ($type == 'kartinka' ? $kartinka_id : $img['id']) . ",
                mini_url = '" . str_replace('kartinka', 'avatar', $img['url']) . "',
                full_url = '" . str_replace('avatar', 'kartinka', $img['url']) . "',
                queued = " . ($type == 'kartinka' ? 1 : 0) ."
                ON DUPLICATE KEY UPDATE
                queued = ". ($type == 'kartinka' ? 1 : 0);
            print "Вставлено записей: " . DB::queryInsert($INSERTQUERY) . " для ID " . $img['id'] ?: $kartinka_id . PHP_EOL;

        }
    }
}

function parse_page($url)
{
    $page = file_get_contents($url);
    //print $page; exit();
    if (!$page){
        return [];
    }

    if (!preg_match_all('/src="([\/a-zA-Z0-9_]{15,1000}\.jpg)/', $page, $matches)){
        return [];
    }
    //print_r($matches); exit();

    $result = [];
    foreach ($matches[1] as $img){
        $keys = explode('/', $img);
        $result[$keys[2]][] = ['id' => get_id_from_url($img), 'url' => $img];
    }

    return $result;
}

function get_id_from_url($url)
{
    if (preg_match('/_([\d]{1,5})\.jpg$/', $url, $match)){
        return $match[1];
    }
    else{
        return 0;
    }
}

function get_cur_img_id($site_donor)
{
    $query_img = "SELECT max(site_img_id) 
        FROM avatar_urls
        WHERE site = '$site_donor'
        AND queued = 1";

    if ($res = DB::queryValue($query_img)) {
        return $res + 1;
    }

    return FALSE;
}




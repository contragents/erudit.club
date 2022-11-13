<?php
//ini_set("display_errors", 1); error_reporting(E_ALL);
if (isset($_SERVER['HTTP_HOST']))
    $server_name = $_SERVER['HTTP_HOST'];
else
    $server_name = 'xn--d1aiwkc2d.club';

if( isset($_SERVER['HTTP_ORIGIN']) &&  $_SERVER['HTTP_ORIGIN']!='' ) {
    $server_name = str_replace('https://','',$_SERVER['HTTP_ORIGIN']);
    header('Access-Control-Allow-Origin: '.$_SERVER['HTTP_ORIGIN']);
    header('Access-Control-Allow-Credentials: true');
}
    
include_once 'EruditGame.php';
$resp = ($obj = new Erudit\Game($server_name))->setInactive();

print $resp;

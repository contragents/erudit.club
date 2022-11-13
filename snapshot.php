<?php
if( isset($_SERVER['HTTP_ORIGIN']) &&  $_SERVER['HTTP_ORIGIN']!='' ) {
    header('Access-Control-Allow-Origin: '.$_SERVER['HTTP_ORIGIN']);
    header('Access-Control-Allow-Credentials: true');
}

$p = new Redis();
//$res = $p->pconnect("localhost", 6379);
$p->pconnect("127.0.0.1", 6379);
$md5=md5(microtime(true).rand());
$key='snapshots_'.$md5;
$p->setex($key,3*24*60*60,$_POST[png]);
print $md5;
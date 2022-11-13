<?php
if( isset($_SERVER['HTTP_ORIGIN']) &&  $_SERVER['HTTP_ORIGIN']!='' ) {
    header('Access-Control-Allow-Origin: '.$_SERVER['HTTP_ORIGIN']);
    header('Access-Control-Allow-Credentials: true');
}

if (!isset($_COOKIE['erudit_user_session_ID'])) 
    exit(0);

include 'EruditGame.php';

print (new Erudit\Game())->playersInfo();

exit();
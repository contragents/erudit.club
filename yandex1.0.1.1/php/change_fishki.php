<?php
if( isset($_SERVER['HTTP_ORIGIN']) &&  $_SERVER['HTTP_ORIGIN']!='' ) {
    header('Access-Control-Allow-Origin: '.$_SERVER['HTTP_ORIGIN']);
    header('Access-Control-Allow-Credentials: true');
}

if (!isset($_COOKIE['erudit_user_session_ID'])) 
    exit(0);

include_once 'EruditGame.php';
include_once 'RuLangProvider.php';
print ($obj = new Erudit\Game())->changeFishki($_POST);

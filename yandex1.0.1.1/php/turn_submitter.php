<?php
if( isset($_SERVER['HTTP_ORIGIN']) &&  $_SERVER['HTTP_ORIGIN']!='' ) {
    header('Access-Control-Allow-Origin: '.$_SERVER['HTTP_ORIGIN']);
    header('Access-Control-Allow-Credentials: true');
}
//error_reporting(E_ALL);ini_set('display_errors', 1);

if (!isset($_COOKIE['erudit_user_session_ID'])) 
    exit(0);

include_once 'EruditGame.php';
include_once 'RuLangProvider.php';
print ($obj = new Erudit\Game())->submitTurn();

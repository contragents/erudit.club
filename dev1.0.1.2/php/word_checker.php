<?php
if( isset($_SERVER['HTTP_ORIGIN']) &&  $_SERVER['HTTP_ORIGIN']!='' ) {
    header('Access-Control-Allow-Origin: '.$_SERVER['HTTP_ORIGIN']);
    header('Access-Control-Allow-Credentials: true');
}

if (!isset($_COOKIE['erudit_user_session_ID'])) 
    exit(0);

include 'EruditGame.php';
include 'RuLangProvider.php';

print (new Erudit\Game())->wordChecker();

exit();
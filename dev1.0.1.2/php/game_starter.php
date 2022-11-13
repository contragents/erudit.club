<?php
include_once 'EruditGame.php';
//print (new Erudit\Game())->startGame();
$resp = ($obj = new Erudit\Game())->startGame();

print $resp;
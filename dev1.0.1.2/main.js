/*<?php
preg_match('/((scrabble|release|yandex|dev)(\d\.\d\.\d\.\d))/', __DIR__, $matches);
$dir = $matches[1];
$gameMode = $matches[2];
//Определяем каталог версии разработки
?>*/
//

//<?php include('tg.js')?>

var UIScene = new Phaser.Class({
   
    Extends: Phaser.Scene,

    initialize:
    //<?php include('initializeFunction.js')?>
    ,

    preload: 
    //<?php include('preloadFunction.js')?>
    ,

    create: 
    //<?php include('createFunction.js')?>
    ,
    
    update : 
    //<?php include('updateFunction.js')?>
});

//<?php include('globalVars.js.php')?>

//<?php include('config.js')?>

//<?php include('globalFunctions.js')?>

var game = new Phaser.Game(config);

document.body.style.backgroundColor = "#dddddd";

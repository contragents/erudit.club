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

//<?php include('globalFunctions.js.php')?>

var game = new Phaser.Game(config);

document.body.style.backgroundColor = "#2C3C6C";
document.body.style.backgroundImage = "url('/img/back2.svg')";
document.body.style.backgroundSize = 'cover';
document.body.style.backgroundSize = '100% 500%';

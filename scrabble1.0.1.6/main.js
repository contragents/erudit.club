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
    //<?php include('preloadFunction_4.js')?>
    ,

    create: 
    //<?php include('createFunction.js')?>
    ,
    
    update : 
    //<?php include('updateFunction.js')?>
});

//<?php include('globalVars_erudit_2.js.php')?>

//<?php include('config.js')?>

//<?php include('globalFunctions.js.php')?>

var game = new Phaser.Game(config);

document.body.style.backgroundImage = screenOrient === HOR ? ("url('" + BASE_URL + "img/back_gorizont_2.svg')") : "url('img/back2.svg')";
document.body.style.backgroundSize = 'cover';
document.body.style.backgroundPosition = 'center';
document.body.style.backgroundOrigin = 'border-box';

//CLUB-421
document.addEventListener('contextmenu', event => event.preventDefault());
document.addEventListener('dragstart', event => {
    if (event.target.tagName === 'IMG') event.preventDefault();
});

//<?php include('ysdk_4.js')?>
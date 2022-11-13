/*<?php
preg_match('/((release|yandex|dev)(\d\.\d\.\d\.\d))/',__DIR__,$matches);
$dir=$matches[1];
//Определяем каталог версии разработки
?>*/
//
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

//<?php include('globalVars.js')?>

//<?php include('config.js')?>

//<?php include('globalFunctions.js')?>

var game = new Phaser.Game(config);

document.body.style.backgroundColor = "#dddddd";

//<?php include('ysdk.js')?>


/*
<?php
if (isset($_GET['lang']) && $_GET['lang'] == 'en') {
?>
//*/
var lang = 'EN';
/*
<?php
} else {
?>
//*/
var lang = 'RU';
/*
<?php
}
?>
*/

var turnAutocloseDialog = false;

var reloadInervalNumber = false;

const windowInnerWidth = window.innerWidth;
const windowInnerHeight = window.innerHeight;

const standardVerticalWidth = 500 * 2;
const standardVerticalHeight = 800 * 2;
const standardHorizontalWidth = 960 * 2;
const standardHorizontalHeight = standardVerticalWidth;

const donateLink='https://pay.cloudtips.ru/p/9844e694';

var graphics;
var letterMin = 0;
var letterMax = 31;
var chooseFishka = false;
var fullscreenButtonSize = 64;
var FullScreenButton = false;

var buttonWidth = 240 * 2;
var buttonStepY = 50 * 2;

var requestSended = false;
var requestTimestamp = (new Date()).getTime();
const normalRequestTimeout = 500;
var noNetworkImg = false;
var propKoef = 1;
var buttonHeightKoef = 1;
var fishkaScale = 1;

if (windowInnerWidth > windowInnerHeight) {
    var screenOrient = 'horizontal';
    var gameWidth = standardHorizontalWidth;
    var gameHeight = standardHorizontalHeight;
    var knopkiWidth = gameWidth - gameHeight;
    var lotokX = 30 * 2;
    var lotokY = 30 * 2;
    var lotokCellStep = 40 * 2;
    var lotokCellStepY = lotokCellStep;
    var lotokCapacityX = 10;
    var lotokCapacityY = 2;
    var fullscreenXY = {x: gameWidth - gameHeight - fullscreenButtonSize / 2, y: fullscreenButtonSize / 2 + 16};
    var backY = (gameHeight - 2000) * Math.random();
    var backX = (gameWidth - 2000) * Math.random();

} else {
    var screenOrient = 'vertical';
    //alert(window.outerHeight + ' ' + window.screen.availHeight + ' ' + screen.height + "\n" + window.outerWidth + ' ' + window.screen.availWidth + ' ' + screen.width);
    if (isYandexAppGlobal()) {
        propKoef = window.outerHeight / window.outerWidth;
    } else if (isIOSDevice()) {
        //console.log('IOS!!!');
        propKoef = window.innerHeight / window.innerWidth;
    } else {
        const outerHeight = (window.screen.availHeight - window.outerHeight) / 2 + window.outerHeight;
        propKoef = outerHeight / window.outerWidth;
    }

    buttonHeightKoef = propKoef / (standardVerticalHeight / standardVerticalWidth);
    console.log(propKoef+'-'+buttonHeightKoef);
    if (buttonHeightKoef < 1) {
        buttonHeightKoef = 1;
    }
    var gameWidth = standardVerticalWidth;
    var gameHeight = buttonHeightKoef <= 1 ? standardVerticalHeight : (gameWidth * propKoef);
    console.log(gameHeight);
    var knopkiWidth = gameWidth;
    var lotokX = 30 * 2;
    var lotokY = 530 * 2;

    if (buttonHeightKoef == 1) {
        var fishkaScale = 1.2;
        var lotokCellStep = 40 * 2;
        var lotokCapacityX = 9;
    }
    else {
        var fishkaScale = buttonHeightKoef;
        var lotokCellStep = 40 * 2 * buttonHeightKoef;
        var lotokCapacityX = 9;
    }

    var lotokCellStepY = lotokCellStep * buttonHeightKoef;
    buttonStepY = buttonStepY * buttonHeightKoef;

    var lotokCapacityY = 1;
    var fullscreenXY = {x: gameWidth - fullscreenButtonSize / 2 - 8, y: gameHeight - fullscreenButtonSize / 2 - 8};
    var backY = 100 + (gameWidth - 50) * Math.random();
    var backX = -1 * gameWidth * Math.random();
    var backScale = 1; // не используем, хз как работает setscale в Фазере
}

var lotokCells = [];

var stepX = 0;
var stepY = 0;

var cells = [];

var newCells = [];

var fixedContainer = [];

var container = [];

var yacheikaWidth = 32 * 2;
var correctionX = 6 * 2;
var correctionY = -7 * 2;

var gameScene = 0;

var submitButton = false;
var submitButton = false;
var submitButton = false;
var submitButton = false;

var dialog = false;

var winScore = '';
var ochki = false;
var ochki_arr = false;
var myUserNum = false;

var canOpenDialog = true;
var canCloseDialog = true;

var data = [];
var responseData = [];
var lastflor = 0;
var gameLog = [];
var chatLog = [];
var intervalId = 0;
var vremia = false;
var vremiaMinutes = false;
var vremiaSeconds = false;
var lastTimeCorrection = 0;
var vremiaFontSizeDefault = 24 * 2;
var vremiaFontSizeDelta = 8;
var vremiaFontSize = vremiaFontSizeDefault;

var tWaiting = false;
var gWLimit = false;

var pageActive = 'visible';
var fullImgID = false;
var fullImgWidth = 0;

var useLocalStorage = localStorage.erudit_user_session_ID ? true : false;

var instruction = '';

//<?php if (lang == 'EN') include('globals/instruction_eng.js'); else include('globals/instruction.js'); ?>

//<?php include('globals/buttonSettingsGlobal.js')?>
//<?php include('globals/gameStates.js')?>
//<?php include('globals/letterPrices.js')?>
//<?php include('globals/rusLetters.js')?>
//<?php include('globals/wav.js')?>
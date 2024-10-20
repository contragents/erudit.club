
//<?php $lang = T::$lang = T::GAME_MODE_LANG[$gameMode]; ?>

var lang = '<?= T::$lang ?>';

const chooseFile = "'<?= T::S('Choose file') ?>'";
document.documentElement.style.setProperty('--choose-file', chooseFile);

const INVITE_FRIEND_PROMPT = '<?= T::getInviteFriendPrompt() ?>';
const GAME_BOT_URL = '<?= T::PHRASES['game_bot_url'][T::$lang] ?>';
const LOADING_TEXT = '<?= T::PHRASES['loading_text'][T::$lang] ?>';
const errorServerMessage = '<?= T::S('Server connecting error. Please try again')?>';

var preloaderObject = false;

const GROUND_FILE = '<?= T::PHRASES['ground_file'][T::$lang] ?>';
const DEFAULT_FISHKA_SET = 'default';
const MAXS_FISHKA_SET = 'MaxS';
const GIRL_FISHKA_SET = 'Girl';
const FISHKA_AVAILABLE_SETS = {MaxS: 30, Girl: 30};
const FISHKA_SET_NAMES = [MAXS_FISHKA_SET, GIRL_FISHKA_SET];
var fishkiLoaded = {};
var userFishkaSet = (lang === 'EN' ? GIRL_FISHKA_SET : MAXS_FISHKA_SET);
const CODES = {
    'RU': [999, 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31],
    'EN': [999, 34, 35, 36, 37, 38, 39, 40, 41, 42, 43, 44, 45, 46, 47, 48, 49, 50, 51, 52, 53, 54, 55, 56, 57, 58, 59]
}

const SUBMIT_SCRIPT = 'turn_submitter.php';
const WORD_CHECKER_SCRIPT = 'word_checker.php';
const STATUS_CHECKER_SCRIPT = 'status_checker.php';
const INIT_GAME_SCRIPT = 'init_game.php';
const CHAT_SCRIPT = 'send_chat_message.php';
const COMPLAIN_SCRIPT = 'complain.php';
const SET_INACTIVE_SCRIPT = 'set_inactive.php';
const MERGE_IDS_SCRIPT = 'merge_the_ids.php';
const SET_PLAYER_NAME_SCRIPT = 'set_player_name.php';
const DELETE_BAN_URL = 'mvc/ban/remove?common_id=';
const STATS_URL = 'mvc/stats/viewV2/?common_id='
const NEW_GAME_SCRIPT = 'new_game.php';
const PLAYER_RATING_SCRIPT = 'mvc/players/info/';//'players_ratings.php';
const CHANGE_FISHKI_SCRIPT = 'change_fishki.php';
const COOKIE_CHECKER_SCRIPT = 'cookie_checker.php';
const CABINET_SCRIPT = 'player_cabinet.php';
const INVITE_SCRIPT = 'invite_to_new_game.php';
const AVATAR_UPLOAD_SCRIPT = 'avatar_upload.php';
const SET_AVATAR_SCRIPT = 'set_player_avatar_url.php';
const HOR = 'horizontal';
const VERT = 'vertical';

const ALARM_MODE = 'Alarm';
const OTJAT_MODE = 'Otjat';

const MY_TURN_STATE = 'myTurn';
const PRE_MY_TURN_STATE = 'preMyTurn';
const OTHER_TURN_STATE = 'otherTurn';

const BAD_REQUEST = 400;
const PAGE_NOT_FOUND = 404;

const CHECK_BUTTON_INACTIVE_CLASS = 'disable-check-button';
const SUBMIT_BUTTON_INACTIVE_CLASS = 'disable-submit-button';
const TOP_PERCENT = 0.15;
const FISHKI_PERCENT = 0.15;
const BOTTOM_PERCENT = 0.7;

const INACTIVE_USER_ALPHA = 0.2;

var activeUser = false;
var commonId = false;
var commonIdHash = false;
var isUserBlockActive = false;
var playerScores = {
    youBlock: {mode: OTJAT_MODE, digit3: 0, digit2: 0, digit1: 0},
    player1Block: {mode: OTJAT_MODE, digit3: 0, digit2: 0, digit1: 0},
    player2Block: {mode: OTJAT_MODE, digit3: 0, digit2: 0, digit1: 0},
    player3Block: {mode: OTJAT_MODE, digit3: 0, digit2: 0, digit1: 0},
    player4Block: {mode: OTJAT_MODE, digit3: 0, digit2: 0, digit1: 0},
}

var timerState = {
    mode: OTJAT_MODE,
    digit3: 0,
    digit2: 0,
    digit1: 0
}

var dialogTurn = false;

var turnAutocloseDialog = false;
var timeToCloseDilog = false;
var automaticDialogClosed = false;

var requestToServerEnabled = true;
var requestToServerEnabledTimeout = false;
var isSubmitResponseAwaining = false;
const GENERAL_REQUEST_TIMEOUT = 500;
const SUBMIT_REQUEST_TIMEOUT = 1000;

var reloadInervalNumber = false;

const windowInnerWidth = window.innerWidth;
const windowInnerHeight = window.innerHeight;

const standardVerticalWidth = 500 * 2;
const standardVerticalHeight = 800 * 2;
const standardHorizontalWidth = 960 * 2;
const standardHorizontalHeight = standardVerticalWidth;

var gameNumber = false;
var graphics;
var letterMin = 0;
var letterMax = 31;
var chooseFishka = false;
var fullscreenButtonSize = 64;
var FullScreenButton = false;

var buttonWidth = 120 * 2;
var buttonStepX = 10 * 2;
var buttonStepY = 50 * 2;

var requestSended = false;
var requestTimestamp = (new Date()).getTime();
const normalRequestTimeout = 500;
var noNetworkImg = false;
var propKoef = 1;
var buttonHeightKoef = 1;
var fishkaScale = 1;

var cells = [];
var newCells = [];
var fixedContainer = [];
var container = [];
var yacheikaWidth = 32 * 2;
var correctionX = 6 * 2;
var correctionY = -7 * 2;

if (windowInnerWidth > windowInnerHeight) {
    var screenOrient = HOR;
    var gameWidth = standardHorizontalWidth;
    var gameHeight = standardHorizontalHeight;
    var knopkiWidth = gameWidth - gameHeight;

    var topHeight = gameHeight * TOP_PERCENT;
    var fishkiHeight = gameHeight * FISHKI_PERCENT;
    var botHeight = gameHeight * BOTTOM_PERCENT;
    var topXY = {x: 0, y: 0};
    var fishkiXY = {x: 0, y: topHeight};
    var botXY = {x: 0, y: topHeight + fishkiHeight};

    var lotokX = fishkiXY.x + 30 * 2;
    var lotokY = fishkiXY.y + 20 * 2;
    var lotokCellStep = 40 * 2;
    var lotokCellStepY = lotokCellStep;
    var lotokCapacityX = 10;
    var lotokCapacityY = 2;
    var fullscreenXY = {x: gameWidth - gameHeight - fullscreenButtonSize / 2, y: fullscreenButtonSize / 2 + 16};
    var backY = (gameHeight - 2000) * Math.random();
    var backX = (gameWidth - 2000) * Math.random();
} else {
    var screenOrient = VERT;
    if (isYandexAppGlobal()) {
        propKoef = window.outerHeight / window.outerWidth;
    } else if (isIOSDevice()) {
        propKoef = window.innerHeight / window.innerWidth;
    } else {
        const outerHeight = (window.screen.availHeight - window.outerHeight) / 2 + window.outerHeight;
        propKoef = outerHeight / window.outerWidth;

        propKoef = window.innerHeight / window.innerWidth;
    }

    buttonHeightKoef = propKoef / (standardVerticalHeight / standardVerticalWidth);

    var gameWidth = standardVerticalWidth;
    var gameHeight = (gameWidth * propKoef);

    var knopkiWidth = gameWidth; // size of buttons block

    var topHeight = (gameHeight - gameWidth) * TOP_PERCENT;
    var fishkiHeight = (gameHeight - gameWidth) * FISHKI_PERCENT;
    var botHeight = (gameHeight - gameWidth) * BOTTOM_PERCENT;
    var topXY = {x: 0, y: 0};
    var fishkiXY = {x: 0, y: topHeight + gameWidth};
    var botXY = {x: 0, y: topHeight + gameWidth + fishkiHeight};

    var lotokX = fishkiXY.x + 30 * 2;
    var lotokY = fishkiXY.y + 20 * buttonHeightKoef * 2;

    if (buttonHeightKoef == 1) {
        fishkaScale = 1.2;
        var lotokCellStep = 40 * 2;
        var lotokCapacityX = 9;
    } else {
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

var buttonHeight = topHeight;

var lotokCells = [];

var stepX = 0;
var stepY = 0;

var gameScene = 0;

var submitButton = false;

var dialog = false;
var dialogResponse = false;

var winScore = false;
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
var hasIncomingMessages = false;
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

var soundPlayed = false;
var instruction = `<?= T::S('faq_rules') ?>`;

//<?php /*if (T::$lang === T::EN_LANG) include('globals/instruction_eng.js'); else include('globals/instruction.js'); */?>

//<?php include('globals/tgGlobalFunction.js')?>
//<?php include('globals/buttonSettingsGlobal.js')?>
//<?php include('globals/gameStates.js.php')?>
//<?php include('globals/letterPrices.js')?>
//<?php include('globals/rusLetters.js')?>
//<?php include('globals/wav.js')?>
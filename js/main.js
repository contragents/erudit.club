/**/
//

//
function sessionStorageSet(key, value) {
    try {
        window.sessionStorage.setItem('__telegram__' + key, JSON.stringify(value));
        return true;
    } catch (e) {}
    return false;
}

function sessionStorageGet(key) {
    try {
        return JSON.parse(window.sessionStorage.getItem('__telegram__' + key));
    } catch (e) {}
    return null;
}

var appTgVersion = 7.7;

var initParams = sessionStorageGet('initParams');
if (initParams) {
    if (!initParams.tgWebAppVersion) {
        initParams.tgWebAppVersion = appTgVersion;
    }
} else {
    initParams = {
        tgWebAppVersion: appTgVersion
    };
}

sessionStorageSet('initParams', initParams);

if (window.Telegram == undefined) {
    var webAppInitDataUnsafe = {};
    var TG = {};
    var WebView = {postEvent: function(p1,p2,p3){return;}};
} else {
    var TG = window.Telegram.WebApp;
    TG.disableVerticalSwipes();

    var Utils = window.Telegram.Utils;
    var WebView = window.Telegram.WebView;
    var initParams = WebView.initParams;
    var isIframe = WebView.isIframe;

    WebView.postEvent('web_app_expand');

    var webAppInitData = '';
    var webAppInitDataUnsafe = {};

    if (initParams.tgWebAppData && initParams.tgWebAppData.length) {
        webAppInitData = initParams.tgWebAppData;
        webAppInitDataUnsafe = Utils.urlParseQueryString(webAppInitData);
        for (var key in webAppInitDataUnsafe) {
            var val = webAppInitDataUnsafe[key];
            try {
                if (val.substr(0, 1) == '{' && val.substr(-1) == '}' ||
                    val.substr(0, 1) == '[' && val.substr(-1) == ']') {
                    webAppInitDataUnsafe[key] = JSON.parse(val);
                }
            } catch (e) {
            }
        }
    }
}

function isTgBot() {
    return ('user' in webAppInitDataUnsafe) && ('id' in webAppInitDataUnsafe.user);
}

// Adjusting max-height of bootbox for Telegram. Used in CSS
let vh = window.innerHeight * 0.01;
document.documentElement.style.setProperty('--vh', `${vh}px`);

window.addEventListener('resize', () => {
    // We execute the same script as before
    let vh = window.innerHeight * 0.01;
    document.documentElement.style.setProperty('--vh', `${vh}px`);
});

var UIScene = new Phaser.Class({
   
    Extends: Phaser.Scene,

    initialize:
    //
function UIScene () {
        Phaser.Scene.call(this, { key: 'UIScene', active: true });
    }    ,

    preload: 
    ////
function () {

    var progressBar = this.add.graphics();
    var progressBox = this.add.graphics();
    progressBox.fillStyle(0x222222, 0.3);
    progressBox.fillRect(gameWidth / 2 - 320 / 2, gameHeight / 2 - 50 / 5, 320, 50);

    var showCaution = false;

    var textWidth = this.cameras.main.width;
    var textHeight = this.cameras.main.height;
    var loadingText = this.make.text({
        x: textWidth / 2,
        y: textHeight / 2 - 50,
        text: LOADING_TEXT,
        style: {
            font: '20px monospace',
            fill: '#000000'
        }
    });
    loadingText.setOrigin(0.5, 0.5);

    this.load.on('progress', function (value) {
        progressBar.clear();
        progressBar.fillStyle(0xffffff, 1);
        progressBar.fillRect(gameWidth / 2 + 10 - 320 / 2, gameHeight / 2 - 30 / 2 + 15, 300 * value, 30);
    });

    this.load.on('complete', function () {
        progressBar.destroy();
        progressBox.destroy();
        loadingText.destroy();
        if (showCaution) {
            androidText1.destroy();
            androidText2.destroy();
        }
    });

    preloaderObject = this;

    this.load.image('no_network', '/img/no_network_transparent.png');
    this.load.svg('ground', '/img/' + GROUND_FILE, {'width': 513 * 2, 'height': 500 * 2});
    this.load.svg('donate', '/img/donate.svg');
    this.load.image('zvezda', '/img/star_transparent.png');
    this.load.svg('zvezdaVerh', '/img/star_transparent_2.svg', {scale: 0.025 * 2});
    this.load.svg('zvezdaCenter', '/img/star_transparent_2.svg', {scale: 0.06 * 2});
    this.load.atlas('fishka_empty', '/img/fishka_empty.png', '/img/fishka_empty.json');
    this.load.atlas('megaset', '/img/letters.png', '/img/letters.json');
    this.load.atlas('digits', '/img/letters.png', '/img/nums.json');
    this.load.atlas('megaset_english', '/img/letters_english.png', '/img/letters_english.json');
    this.load.svg('back', '/img/back2.svg', {'height': 1980, 'width': 1080});

    for (let k in buttons) {
        if ('modes' in buttons[k])
            buttons[k]['modes'].forEach(mode => this.load.svg(k + mode, '/img/' + mode.toLowerCase() + '/' + buttons[k]['filename'] + '.svg',
                'width' in buttons[k]
                    ? {
                        'width': buttons[k]['width'],
                        'height': 'height' in buttons[k] ? buttons[k].height : buttonHeight,
                    }
                    : {
                        'height': 'height' in buttons[k] ? buttons[k].height : buttonHeight,
                    }
            ));
        else
            modes.forEach(mode => this.load.svg(k + mode, '/img/' + mode.toLowerCase() + '/' + buttons[k]['filename'] + '.svg?ver=2',
                'width' in buttons[k]
                    ? {
                        'width': buttons[k]['width'],
                        'height': 'height' in buttons[k] ? buttons[k].height : buttonHeight,
                    }
                    : {
                        'height': 'height' in buttons[k] ? buttons[k].height : buttonHeight,
                    }
            ));
    }

    for (let k in players) {
        playerBlockModes.forEach(mode => this.load.svg(k + mode, '/img/' + mode.toLowerCase() + '/' + players[k]['filename'] + '.svg',
            'width' in players[k]
                ? {
                    'width': players[k]['width'],
                    'height': 'height' in players[k] ? players[k].height : buttonHeight,
                }
                : {
                    'height': 'height' in players[k] ? players[k].height : buttonHeight,
                }
        ));
    }


    playerBlockModes.forEach(mode => {
        for (let k in digits.playerDigits[mode]) {
            this.load.svg(mode + '_' + 'player_' + k, '/img/' + mode.toLowerCase() + '/' + digits.playerDigits[mode][k]['filename'] + '.svg',
                {'height': buttonHeight * 0.5 / (buttonHeightKoef < 1 ? 0.5 : 1), 'width': buttonHeight * 0.23 * 0.5 / (buttonHeightKoef < 1 ? 0.5 : 1)}
            );

            this.load.svg(mode + '_' + 'timer_' + k, '/img/' + mode.toLowerCase() + '/' + digits.timerDigits[mode][k]['filename'] + '_' + modesColors[mode] + '.svg',
                {'height': buttonHeight * 0.5 / (buttonHeightKoef < 1 ? 0.8 : 1), 'width': buttonHeight * 0.4 * 0.5 / (buttonHeightKoef < 1 ? 0.8 : 1)}
            );
        }

        this.load.svg(mode + '_' + 'dvoetoch', '/img/' + mode.toLowerCase() + '/numbers/' + 'dvoetoch'  + '_' + modesColors[mode]+ '.svg',
            {'height': buttonHeight * 0.5 / (buttonHeightKoef < 1 ? 0.8 : 1), 'width': buttonHeight * 0.15 * 0.5 / (buttonHeightKoef < 1 ? 0.8 : 1)}
        );
    });

    loadFishkiSet(userFishkaSet);
}
    ,

    create: 
    ////
function () {
    var letters = [];
    var atlasTexture = this.textures.get('megaset');

    var frames = atlasTexture.getFrameNames();


    noNetworkImg = this.add.image(200, 200, 'no_network');

    var back = this.add.sprite(gameWidth / 2, gameHeight / 2, 'back');
    back.displayWidth = this.sys.canvas.width;
    back.displayHeight = this.sys.canvas.height;

    WebView.postEvent('web_app_set_header_color', false, {
        color: '#2C3C6C',
    });

    var ground = this.add.image(385, 375, 'ground');
    ground.setOrigin(0, 0);
    ground.x = game.config.width - ground.width;
    ground.y = screenOrient === HOR
        ? 0
        : topHeight;
    ground.setCrop(16 * 2, 3 * 2, 550 * 2, 550 * 2);

    stepX = game.config.width - ground.width;
    stepY = (screenOrient === HOR) ? 0 : topHeight;
    initLotok();

    initCellsGlobal();

    for (let k in buttons) {

        if ('preCalc' in buttons[k])
            buttons[k]['preCalc']();

        buttons[k]['svgObject'] = getSVGButton(buttons[k]['x'], buttons[k]['y'], k, this);

        buttons[k]['svgObject'].on('pointerup', function () {
            buttons[k]['svgObject'].bringToTop(buttons[k]['svgObject'].getByName(k + OTJAT_MODE));
            if ('pointerupFunction' in buttons[k])
                buttons[k]['pointerupFunction']();
        });

        buttons[k]['svgObject'].on('pointerdown', function () {
            buttons[k]['svgObject'].bringToTop(buttons[k]['svgObject'].getByName(k + 'Najatie'));
        });

        buttons[k]['svgObject'].on('pointerover', function () {
            if (k == 'chatButton') {
                if (buttons['chatButton']['svgObject'].getByName('chatButton' + ALARM_MODE).getData('alarm') !== true)
                    buttons[k]['svgObject'].bringToTop(buttons[k]['svgObject'].getByName(k + 'Navedenie'));
            } else
                buttons[k]['svgObject'].bringToTop(buttons[k]['svgObject'].getByName(k + 'Navedenie'));
        });

        buttons[k]['svgObject'].on('pointerout', function () {
            if (k == 'chatButton') {
                if (buttons['chatButton']['svgObject'].getByName('chatButton' + ALARM_MODE).getData('alarm') !== true)
                    buttons[k]['svgObject'].bringToTop(buttons[k]['svgObject'].getByName(k + OTJAT_MODE));
            } else if ('enabled' in buttons[k]) {
                if (gameState in buttons[k]['enabled'])
                    buttons[k]['svgObject'].bringToTop(buttons[k]['svgObject'].getByName(k + OTJAT_MODE));
            } else
                buttons[k]['svgObject'].bringToTop(buttons[k]['svgObject'].getByName(k + OTJAT_MODE));
        });
    }

    let numTopButtons = 0;
    let sumWidth = 0;
    for (let tbK in topButtons) {
        numTopButtons++;
        topButtons[tbK].displayWidth = buttons[tbK]['svgObject'].displayWidth;
        sumWidth += topButtons[tbK].displayWidth;
    }
    let stepXTopButtons = (knopkiWidth - sumWidth) / (numTopButtons + 1);

    let currentWidth = 0;
    for (let tbK in topButtons) {
        buttons[tbK]['svgObject'].x = stepXTopButtons + currentWidth + buttons[tbK]['svgObject'].displayWidth / 2;
        currentWidth += stepXTopButtons + buttons[tbK]['svgObject'].displayWidth;
    }

    buttons['razdvButton']['svgObject'].disableInteractive();
    buttons['razdvButton']['svgObject'].visible = false;

    if (buttons['submitButton']['svgObject'] !== false) {
        buttons['submitButton']['svgObject'].disableInteractive();
        buttons['submitButton']['svgObject'].bringToTop(buttons['submitButton']['svgObject'].getByName('submitButton' + 'Inactive'));
    }

    for (let k in players) {
        players[k]['svgObject'] = getSVGBlock(players[k]['x'], players[k]['y'], k, this, players[k].scalable, 'numbers' in players[k]);
        players[k]['svgObject'].bringToTop(players[k]['svgObject'].getByName(k + OTJAT_MODE));
        players[k]['svgObject'].getByName(k + ALARM_MODE).setVisible(false);
    }

//    
this.input.on('dragstart', function (pointer, gameObject) {
    gameObject.depth = 100;
    let cellX = Math.round((gameObject.x - stepX - correctionX) / yacheikaWidth) - 1;
    let cellY = Math.round((gameObject.y - stepY - correctionY) / yacheikaWidth) - 1;
    if ((cellX <= 14) && (cellX >= 0) && (cellY <= 14) && (cellY >= 0)) {
        cells[cellX][cellY][0] = false;
        cells[cellX][cellY][1] = false;
        cells[cellX][cellY][2] = false;
        cells[cellX][cellY][3] = DEFAULT_FISHKA_SET;

        gameObject.setData('cellX', false);
        gameObject.setData('cellY', false);

        gameObject.setData('oldCellX', cellX);
        gameObject.setData('oldCellY', cellY);
    } else {
        gameObject.setData('oldCellX', false);
        gameObject.setData('oldCellY', false);
    }

    if ((gameObject.getData('lotokX') !== false) && (gameObject.getData('lotokY') !== false)) {
        gameObject.setData('oldLotokX', gameObject.getData('lotokX'));
        gameObject.setData('oldLotokY', gameObject.getData('lotokY'));

        lotokFreeXY(gameObject.getData('lotokX'), gameObject.getData('lotokY'));

        gameObject.setData('lotokX', false);
        gameObject.setData('lotokY', false);
    } else {
        gameObject.setData('oldLotokX', false);
        gameObject.setData('oldLotokY', false);
    }
});

this.input.on('drag', function (pointer, gameObject, dragX, dragY) {
    gameObject.x = dragX;
    gameObject.y = dragY;
});

this.input.on('dragend', function (pointer, gameObject) {
    if (gameObject.x > stepX && gameObject.y < (ground.height + stepY)) {
        let cellX = Math.round((gameObject.x - stepX - correctionX) / yacheikaWidth) - 1;
        if (cellX < 0) {
            cellX = 0;
        }
        if (cellX > 14) {
            cellX = 14
        }

        let cellY = Math.round((gameObject.y - stepY - correctionY) / yacheikaWidth) - 1;
        if (cellY < 0) {
            cellY = 0;
        }
        if (cellY > 14) {
            cellY = 14
        }

        console.log('x', gameObject.x, 'y', gameObject.y, 'cellX', cellX, 'cellY', cellY);
        findPlaceGlobal(gameObject, gameObject.x, gameObject.y, cellX, cellY);
    } else {
        console.log('x', gameObject.x, 'y', gameObject.y, 'stepX', stepX, 'stepY', stepY);
        checkZvezdaGlobal(gameObject);
    }

    /*if (gameObject.x > stepX && gameObject.y < ground.height + topHeight && gameObject.y > topHeight) {
        let cellX = Math.round((gameObject.x - stepX - correctionX) / yacheikaWidth) - 1;
        let cellY = Math.round((gameObject.y - stepY - correctionY) / yacheikaWidth) - 1;
        findPlaceGlobal(gameObject, gameObject.x, gameObject.y, cellX, cellY);
    } else {
        checkZvezdaGlobal(gameObject);
    }*/

    gameObject.depth = 1;
});
//    
function getSVGButton(X, Y, buttonName, _this) {
    var elements = [];
    var elementNumber = 0;
    if ('modes' in buttons[buttonName]) {
        for (let mode in buttons[buttonName]['modes']) {
            elements[elementNumber] = _this.add.image(0, 0, buttonName + buttons[buttonName]['modes'][mode])
                .setName(buttonName + buttons[buttonName]['modes'][mode])
                .setScale(1, buttonHeightKoef);
            elementNumber++;
        }
    } else {
        for (let mode in modes) {
            elements[elementNumber] = _this.add.image(0, 0, buttonName + modes[mode])
                .setName(buttonName + modes[mode])
                .setScale(1, buttonHeightKoef);
            elementNumber++;
        }
    }

    var container = _this.add.container(X, Y, elements);
    container.setSize(elements[0].displayWidth, elements[0].displayHeight);
    container.setInteractive();

    return container;
}

function getSVGBlock(X, Y, buttonName, _this, scalable, hasDigits = false) {
    let elements = [];
    let elementNumber = 0;

    for (let mode in playerBlockModes) {
        elements[elementNumber] = _this.add.image(0, 0, buttonName + playerBlockModes[mode])
            .setName(buttonName + playerBlockModes[mode]);
        if(scalable) {
            elements[elementNumber].setScale(1, buttonHeightKoef);
        }
        elementNumber++;
    }

    if (hasDigits) {
        let imgName = 'numbersX3' in players[buttonName] ? 'timer_' : 'player_';
        let y = 'numbersY' in players[buttonName] ? players[buttonName].numbersY : 0;
        let x3 = 'numbersX3' in players[buttonName] ? players[buttonName].numbersX3 : elements[0].displayWidth * 0.75 * 0.5;
        let x2 = 'numbersX2' in players[buttonName] ? players[buttonName].numbersX2: elements[0].displayWidth * 0.6 * 0.5;
        let x1 = 'numbersX1' in players[buttonName] ? players[buttonName].numbersX1 : elements[0].displayWidth * 0.45 * 0.5;

        playerBlockModes.forEach(mode => {
            if ('dvoetochX' in players[buttonName]) {
                elements[elementNumber] = _this.add.image(
                    players[buttonName].dvoetochX
                    , y
                    , mode + '_' + 'dvoetoch')
                    .setName(mode + '_' + 'dvoetoch')
                    .setVisible(false);

                elementNumber++;
            }

            for (let k in digits.playerDigits[mode]) {
                elements[elementNumber] = _this.add.image(
                    x3
                    , y
                    , mode + '_' + imgName + k)
                    .setName(mode + '_' + k.replace('digit_', '') + '_3')
                    .setVisible(false);

                elementNumber++;
            }
        });

        playerBlockModes.forEach(mode => {
            for (let k in digits.playerDigits[mode]) {
                elements[elementNumber] = _this.add.image(
                    x2
                    , y
                    , mode + '_' + imgName + k)
                    .setName(mode + '_' + k.replace('digit_', '') + '_2')
                    .setVisible(false);

                if(scalable) {
                    elements[elementNumber].setScale(buttonHeightKoef, buttonHeightKoef);
                }

                elementNumber++;
            }
        });

        playerBlockModes.forEach(mode => {
            for (let k in digits.playerDigits[mode]) {
                elements[elementNumber] = _this.add.image(
                    x1
                    , y
                    , mode + '_' + imgName + k)
                    .setName(mode + '_' + k.replace('digit_', '') + '_1')
                    .setVisible(false);

                if(scalable) {
                    elements[elementNumber].setScale(buttonHeightKoef, buttonHeightKoef);
                }

                elementNumber++;
            }
        });
    }

    let container = _this.add.container(X, Y, elements);
    container.setSize(elements[0].displayWidth, elements[0].displayHeight);

    if (hasDigits) {
        container.setAlpha(INACTIVE_USER_ALPHA);
    }

    return container;
}


    ochki = this.add.text(lotokX - lotokCellStep / 2 + 5,
        buttons['newGameButton']['svgObject'].y + buttons['newGameButton']['svgObject'].height - 15,
        'Ваши очки:0',
        {
            color: 'black',
            font: 'bold ' + vremiaFontSize + 'px' + ' Courier',
        }).setVisible(false); // todo delete ochki

    vremia = this.add.text(ochki.x, ochki.y + ochki.height + 15, 'Время на ход 2:00',
        {
            color: 'black',
            font: 'bold ' + vremiaFontSize + 'px' + ' Courier',
        }).setVisible(false); // todo delete vremia
}    ,
    
    update : 
    ////
function (time, delta) {

    if (requestSended && ((new Date()).getTime() - requestTimestamp > normalRequestTimeout)) {
        if (noNetworkImg !== false) {
        noNetworkImg.visible = true;
        noNetworkImg.alpha = ((new Date()).getTime() - requestTimestamp) < (normalRequestTimeout * 2)
            ? ((new Date()).getTime() - requestTimestamp - normalRequestTimeout) / 1000
            : 1;
        }
    } else {
        if (noNetworkImg !== false) {
            noNetworkImg.visible = false;
        }
    }

    if (gameState == 'chooseGame' && (queryNumber > 1))
        return;
    if (newCells.constructor === Array && Array.isArray(newCells[15])) {

        for (k = 100; k >= 0; k--)
            if (k in container) {
                if (container[k].getData('lotokX') !== false)
                    lotokFreeXY(container[k].getData('lotokX'), container[k].getData('lotokY'));
                container[k].destroy();
                container.splice(k, 1);
            }

        if (newCells[15].length > 0) {
            for (var $fishkaNum = 0; $fishkaNum < newCells[15].length; $fishkaNum++)
                if (newCells[15][$fishkaNum] !== undefined) {
                    let lotokXY = lotokFindSlotXY();
                    container.push(getFishkaGlobal(newCells[15][$fishkaNum], lotokGetX(lotokXY[0], lotokXY[1]), lotokGetY(lotokXY[0], lotokXY[1]), this, true, userFishkaSet).setData('lotokX', lotokXY[0]).setData('lotokY', lotokXY[1]));
                }


        }
        newCells.splice(15, 1);
    }
    var flor = Math.floor(time / 1000);
    //if ( (Math.random() > (1-(1/gameStates[gameState]['refresh']/60))) || (queryNumber == 1) ) {
    if (
        (
            (flor > lastQueryTime)
            && ((flor % gameStates[gameState]['refresh']) === 0)
        )
        || (queryNumber === 1)
    ) {
        if (requestToServerEnabled) {
            lastQueryTime = flor;
            fetchGlobal(STATUS_CHECKER_SCRIPT)
                .then((data) => {
                    commonCallback(data);
                });
        }
    }

    if (gameState == 'myTurn' || gameState == 'preMyTurn' || gameState == 'otherTurn' || gameState == 'startGame') {
        if (flor > lastTimeCorrection) {
            lastTimeCorrection = flor;
            if ((vremiaMinutes > 0) || (vremiaSeconds > 0)) {
                vremiaSeconds--;
                if (vremiaSeconds < 0) {
                    vremiaMinutes--;
                    vremiaSeconds = 59;
                }

                displayTimeGlobal(+vremiaMinutes * 100 + +vremiaSeconds);
            }
        }
    }

    if (gameState == MY_TURN_STATE) {
        if ((vremiaMinutes === 0) && (vremiaSeconds <= 10) && buttons['submitButton']['svgObject'].input.enabled) {
            if ((flor % 2) === 0) {
                buttons['submitButton']['svgObject']
                    .bringToTop(buttons['submitButton']['svgObject']
                        .getByName('submitButton' + ALARM_MODE));
            } else {
                buttons['submitButton']['svgObject']
                    .bringToTop(buttons['submitButton']['svgObject']
                        .getByName('submitButton' + OTJAT_MODE));
            }
        }
    }

    if (gameState == MY_TURN_STATE || gameState == PRE_MY_TURN_STATE || gameState == OTHER_TURN_STATE) {
        let activeUserBlockName = (gameState == MY_TURN_STATE) ? 'youBlock' : ('player' + (+activeUser + 1) + 'Block');
        if ((flor % 2) === 0) {
            buttonSetModeGlobal(players, activeUserBlockName, ALARM_MODE);
        } else {
            buttonSetModeGlobal(players, activeUserBlockName, OTJAT_MODE);
        }
    }



    if (gameState == 'gameResults') {
        if ((flor % 2) === 0) {
            buttons['newGameButton']['svgObject']
                .bringToTop(buttons['newGameButton']['svgObject']
                    .getByName('newGameButton' + ALARM_MODE));
        } else {
            buttons['newGameButton']['svgObject']
                .bringToTop(buttons['newGameButton']['svgObject']
                    .getByName('newGameButton' + OTJAT_MODE));
        }
    }
}});

//
//
var lang = 'RU';

const INVITE_FRIEND_PROMPT = 'Присоединяйся к онлайн игре Эрудит в Telegram! Набери максимальный рейтинг, зарабатывай монеты и выводи токены на кошелек';
const GAME_BOT_URL = 'https://t.me/erudit_club_bot';
const LOADING_TEXT = 'Загружаем игру...';

var preloaderObject = false;

const GROUND_FILE = 'field_source_nd_20.svg';
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
const PLAYER_RATING_SCRIPT = 'players_ratings.php';
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
var instruction = `<h2 id="nav1">Об игре</h2>
                            <p>Эрудит &mdash; настольная игра со словами, в которую могут играть от 2 до 4 человек, выкладывая слова из имеющихся у них букв на поле размером 15x15.</p>
                            <div class="fon-right">
                                <h2 id="nav2">Игровое поле</h2>
                                <p>Игровое поле состоит из 15х15, то есть 225 квадратов, на которые участники игры выкладывают буквы, составляя тем самым&nbsp;слова. В начале игры каждый игрок получает 7 случайных букв (всего их в игре 102). 
                                <p>На середину игрового поля выкладывается первое&nbsp;слово. К этому слову по возможности, нужно приставить осташиеся буквы так, чтобы на пересечении получились новые слова.</p>
                                <p>Затем следующий игрок должен выставить свои&nbsp;буквы&nbsp;&laquo;на пересечение&raquo; или приставить их к уже составленным словам.</p>
                                <p>Слова&nbsp;выкладываются либо слева направо, либо сверху вниз.</p>
                            </div>
                            <div class="fon-right">
                                <h2 id="nav3">Словарь</h2>
                                <p>Разрешается использовать все&nbsp;слова, приведенные в стандартном словаре языка за исключением&nbsp;слов, пишущихся с прописных букв, сокращений, и слов, которые пишутся через апостроф или дефис.</p>
                                <p>Разрешено использовать только нарицательные имена существительные в именительном падеже и единственном числе (либо во множественном при отсутствии у слова формы единственного числа, ЛИБО, если слово во множественном числе содержится в одном из словарей Игры - см. значение слова в меню ЛОГ).</p>
                                <p>Чтобы посмотреть, какие слова составили игроки в предыдущих ходах, а также узнать их значение и &laquo;стоимость&raquo;, кликните на кнопку <img src="/img/otjat/log.svg" height="64"/></p>
                            </div>
                            <div class="fon-right">
                                <h2 id="nav4">Ход игры</h2>
                                <p>В начале игры каждому дается по 7 фишек. За один ход можно выложить несколько&nbsp;слов. Каждое новое&nbsp;слово&nbsp;должно соприкасаться (иметь общую букву или буквы) с ранее выложенными&nbsp;словами.&nbsp;Слова&nbsp;читаются только по горизонтали слева направо и по вертикали сверху вниз.</p>
                                <p>Первое выложенное&nbsp;слово&nbsp;должно проходить через центральную клетку.</p>
                                <p>
                                Отправить свою комбинацию можно, нажав кнопку <br /><img src="/img/otjat/otpravit.svg" width="80%"/>
                                <br />
                                Если в данный момент ход не Ваш - кнопка станет неактивной <br /><img src="/img/inactive/otpravit.svg" width="80%"/>
                                <br />
                                Если кнопка ОТПРАВИТЬ начала мигать красным - время Вашего хода заканчивается. Скорее отправляйте свою комбинацию!
                                </p>
                                <p>Если игрок не хочет или не может выложить ни одного слова, - он имеет право поменять любое количество своих букв, пропустив при этом ход.
                                <br /><img src="/img/otjat/pomenyat.svg" width="80%"/>
                                </p>
                                <p>Любая последовательность букв по горизонтали и вертикали должна являться&nbsp;словом. Т.е. в игре не допускается появление на поле случайных буквосочетаний, не представляющих собою&nbsp;слов, соответствующих вышеприведенным критериям.</p>
                                <p>После каждого хода необходимо добрать новых букв до 7.</p>
                                <p>Если за ход игрок использовал все 7 букв, то ему начисляются дополнительные 15 очков.</p>
                            </div>
                            <div class="fon-right">
                                <h2 id="nav5">Распределение фишек и стоимость букв</h2>
                                <table cellpadding="10" cellspacing="10">
                                    <tbody>
                                        <tr>
                                            <th>Буква</th>
                                            <th>Кол-во</th>
                                            <th>Цена</th>
                                        </tr>
                                        <tr>
                                            <td><strong>*</strong></td>
                                            <td>3 шт.</td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td>А</td>
                                            <td>8 шт.</td>
                                            <td>1 очко</td>
                                        </tr>
                                        <tr>
                                            <td>Б</td>
                                            <td>2 шт.</td>
                                            <td>3 очка</td>
                                        </tr>
                                        <tr>
                                            <td>В</td>
                                            <td>4 шт.</td>
                                            <td>1 очко</td>
                                        </tr>
                                        <tr>
                                            <td>Г</td>
                                            <td>2 шт.</td>
                                            <td>3 очка</td>
                                        </tr>
                                        <tr>
                                            <td>Д</td>
                                            <td>4 шт.</td>
                                            <td>2 очка</td>
                                        </tr>
                                        <tr>
                                            <td>Е</td>
                                            <td>9 шт.</td>
                                            <td>1 очко</td>
                                        </tr>
                                        <tr>
                                            <td>Ж</td>
                                            <td>1 шт.</td>
                                            <td>5 очков</td>
                                        </tr>
                                        <tr>
                                            <td>З</td>
                                            <td>2 шт.</td>
                                            <td>5 очков</td>
                                        </tr>
                                        <tr>
                                            <td>И</td>
                                            <td>6 шт.</td>
                                            <td>1 очко</td>
                                        </tr>
                                        <tr>
                                            <td>Й</td>
                                            <td>1 шт.</td>
                                            <td>4 очка</td>
                                        </tr>
                                        <tr>
                                            <td>К</td>
                                            <td>4 шт.</td>
                                            <td>2 очка</td>
                                        </tr>
                                        <tr>
                                            <td>Л</td>
                                            <td>4 шт.</td>
                                            <td>2 очка</td>
                                        </tr>
                                        <tr>
                                            <td>М</td>
                                            <td>3 шт.</td>
                                            <td>2 очка</td>
                                        </tr>
                                        <tr>
                                            <td>Н</td>
                                            <td>5 шт.</td>
                                            <td>1 очко</td>
                                        </tr>
                                        <tr>
                                            <td>О</td>
                                            <td>10 шт.</td>
                                            <td>1 очко</td>
                                        </tr>
                                        <tr>
                                            <td>П</td>
                                            <td>4 шт.</td>
                                            <td>2 очка</td>
                                        </tr>
                                        <tr>
                                            <td>Р</td>
                                            <td>5 шт.</td>
                                            <td>1 очко</td>
                                        </tr>
                                        <tr>
                                            <td>С</td>
                                            <td>5 шт.</td>
                                            <td>1 очко</td>
                                        </tr>
                                        <tr>
                                            <td>Т</td>
                                            <td>5 шт.</td>
                                            <td>1 очко</td>
                                        </tr>
                                        <tr>
                                            <td>У</td>
                                            <td>4 шт.</td>
                                            <td>2 очка</td>
                                        </tr>
                                        <tr>
                                            <td>Ф</td>
                                            <td>1 шт.</td>
                                            <td>8 очков</td>
                                        </tr>
                                        <tr>
                                            <td>Х</td>
                                            <td>1 шт.</td>
                                            <td>5 очков</td>
                                        </tr>
                                        <tr>
                                            <td>Ц</td>
                                            <td>1 шт.</td>
                                            <td>5 очков</td>
                                        </tr>
                                        <tr>
                                            <td>Ч</td>
                                            <td>1 шт.</td>
                                            <td>5 очков</td>
                                        </tr>
                                        <tr>
                                            <td>Ш</td>
                                            <td>1 шт.</td>
                                            <td>8 очков</td>
                                        </tr>
                                        <tr>
                                            <td>Щ</td>
                                            <td>1 шт.</td>
                                            <td>10 очков</td>
                                        </tr>
                                        <tr>
                                            <td>Ъ</td>
                                            <td>1 шт.</td>
                                            <td>15 очков</td>
                                        </tr>
                                        <tr>
                                            <td>Ы</td>
                                            <td>2 шт.</td>
                                            <td>4 очка</td>
                                        </tr>
                                        <tr>
                                            <td>Ь</td>
                                            <td>2 шт.</td>
                                            <td>3 очка</td>
                                        </tr>
                                        <tr>
                                            <td>Э</td>
                                            <td>1 шт.</td>
                                            <td>8 очков</td>
                                        </tr>
                                        <tr>
                                            <td>Ю</td>
                                            <td>1 шт.</td>
                                            <td>8 очков</td>
                                        </tr>
                                        <tr>
                                            <td>Я</td>
                                            <td>2 шт.</td>
                                            <td>3 очка</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="fon-right">
                                <h2 id="nav6">Подсчет очков и бонусы</h2>
                                <p>Каждой букве присвоено количество очков от 1 до 10. Некоторые квадраты на доске раскрашены в разные цвета. Количество очков, получаемых игроком за выложенное слово, подсчитывается следующим образом:</p>
                                <ul>
                                    <li>Если квадрат под буквой бесцветен, добавляется количество очков, написанное на букве</li>
                                    <li>Если квадрат <span style="background-color:green;color:white;">зеленый</span> - количество очков <strong>буквы</strong> умножается на <strong>2</strong></li>
                                    <li>Если квадрат <span style="background-color:yellow;color:black;">желтый</span> - количество очков <strong>буквы</strong> умножается на <strong>3</strong></li>
                                    <li>Если квадрат <span style="background-color:blue;color:white;">синий</span> - количество очков всего <strong>слова</strong> умножается на <strong>2</strong></li>
                                    <li>Если квадрат <span style="background-color:red;color:white;">красный</span> - количество очков всего <strong>слова</strong> умножается на <strong>3</strong></li>
                                </ul>
                                <p>Если слово использует множители обоего типа, то в удвоении (утроении) очков слова учитывается удвоение (утроение) очков букв.</p>
                            </div>
                            <div class="fon-right">
                                <h2 id="nav7">Звёздочка</h2>
                                <p>Также, в наборе фишек присутствуют три звёздочки. Такая фишка может быть использована как любая буква на выбор игрока. Например, игрок может выставить слово &laquo;ТЕ*ЕФОН&raquo;, где роль буквы &laquo;Л&raquo; будет играть звездочка.</p>
                                <p>Как только игрок выставит на поле звездочку, игра сразу предложит выбрать заменяемую ею букву. При перестановке звездочки выбор буквы будет предлагаться вновь.</p>
                                <p>Звездочка приносит столько очков, сколько бы принесла буква, роль которой она играет.&nbsp;</p>
                                <h3>Повторное использование звёздочки&nbsp;</h3>
                                <p>Если у любого из игроков есть буква, которую заменяет звёздочка на игровом поле, то он может заменить эту звёздочку своей буквой и использовать полученную звёздочку для составления слова, но только в текущий ход. Забрать звёздочку с поля "про запас" себе нельзя.</p>
                            </div>`;

//
//
function shareTgGlobal() {
    if (!commonId && !isTgBot()) {
        return;
    }

    botUrl = GAME_BOT_URL + '/?start=inv_'
    + (commonId ? commonId : ('id_' + webAppInitDataUnsafe.user.id));

    shareUrl = '/share/url?url='
        + encodeURIComponent(botUrl)
        + '&text=' + encodeURIComponent(INVITE_FRIEND_PROMPT);

    WebView.postEvent(
        'web_app_open_tg_link',
        false,
        {path_full: shareUrl,}
    );
}
////
var topButtons = {newGameButton: {displayWidth: 0}, instructButton: {displayWidth: 0}, prizesButton: {displayWidth: 0}, inviteButton: {displayWidth: 0}};

var modes = [OTJAT_MODE, ALARM_MODE, 'Inactive', 'Navedenie', 'Najatie'];

var buttons = {
    newGameButton: {
        filename: 'new_game2' +  (lang === 'RU' ? '_ru' : ''),
        x: topXY.x + lotokX + buttonWidth / 2 - lotokCellStep / 2 + 5,
        y: (topXY.y + topHeight) / 2,
        caption: 'New#Game',
        width: buttonWidth,
        object: false, svgObject: false,
        pointerupFunction: function () {
            newGameButtonFunction();
        }
    },
    instructButton: {
        filename: 'instrukt2',
        x: topXY.x + lotokX + buttonWidth / 2 - lotokCellStep / 2 + 5 + buttonWidth,
        y: (topXY.y + topHeight) / 2,
        caption: 'инструкция',
        //height:
        width: buttonWidth / 2,
        object: false,
        svgObject: false,
        pointerupFunction: function () {
            shareButtonFunction();
        }
    },
    prizesButton: {
        filename: 'prizes2',
        modes: [OTJAT_MODE, 'Navedenie', 'Najatie'],
        x: (topXY.x + knopkiWidth) / 2,
        y: (topXY.y + topHeight) / 2,
        caption: 'Prizes',
        width: buttonWidth / 2,
        //height: topHeight,
        object: false,
        svgObject: false,
        pointerupFunction: function () {
            return;
        }
    },
    inviteButton: {
        filename: 'invite2',
        modes: [OTJAT_MODE, 'Navedenie', 'Najatie'],
        x: topXY.x + knopkiWidth - buttonWidth,
        y: topXY.y + topHeight / 2,
        caption: 'Invite',
        width: buttonWidth / 2,
        object: false,
        svgObject: false,
        pointerupFunction: function () {
            {
                shareTgGlobal();

                return false;
            }
        }
    },
    submitButton: {
        filename: 'otpravit2' +  (lang === 'RU' ? '_ru' : ''),
        x: botXY.x + knopkiWidth - buttonWidth / 2 - buttonStepX,
        y: botXY.y + botHeight * 0.125,
        caption: 'отправить',
        width: buttonWidth,
        object: false, svgObject: false,
        enabled: {myTurn: 1},
        pointerupFunction: function () {
            submitButtonFunction();
        }
    },
    resetButton: {
        filename: 'steret2' +  (lang === 'RU' ? '_ru' : ''),
        x: botXY.x + knopkiWidth - buttonWidth / 2 - buttonStepX,
        y: botXY.y + botHeight * (0.25 + 0.125),
        caption: 'стереть',
        width: buttonWidth,
        object: false,
        svgObject: false,
        enabled: {myTurn: 1, preMyTurn: 1, otherTurn: 1},
        pointerupFunction: function () {
            resetButtonFunction();
        }
    },
    changeButton: {
        filename: 'pomenyat2' +  (lang === 'RU' ? '_ru' : ''),
        x: botXY.x + knopkiWidth - buttonWidth / 2 - buttonStepX,
        y: botXY.y + botHeight * (0.5 + 0.125),
        caption: 'поменять',
        width: buttonWidth,
        object: false,
        svgObject: false,
        enabled: {myTurn: 1},
        pointerupFunction: function () {
            changeButtonFunction();
        }
    },
    playersButton: {
        filename: 'igroki2',
        x: botXY.x + knopkiWidth - buttonWidth / 2 - buttonStepX,
        y: botXY.y + botHeight * (0.75 + 0.125),
        caption: 'игроки',
        width: buttonWidth / 2,
        object: false, svgObject: false,
        pointerupFunction: function () {
            playersButtonFunction();
        }
    },
    checkButton: {
        filename: 'proveryt2' +  (lang === 'RU' ? '_ru' : ''),
        x: botXY.x + knopkiWidth / 2,
        y: botXY.y + botHeight * 0.125,
        caption: 'проверить',
        width: buttonWidth,
        object: false,
        svgObject: false,
        enabled: {myTurn: 1, preMyTurn: 1, otherTurn: 1},
        pointerupFunction: function () {
            checkButtonFunction();
        }
    },
    chatButton: {
        filename: 'chat2',
        x: botXY.x + knopkiWidth / 2,
        y: botXY.y + botHeight * (0.75 + 0.125),
        caption: 'чат',
        width: buttonWidth / 2,
        object: false,
        svgObject: false,
        pointerupFunction: function () {
            chatButtonFunction();
        },
    },
    logButton: {
        filename: 'log2',
        x: botXY.x + buttonStepX + buttonWidth / 2,
        y: botXY.y + botHeight * (0.75 + 0.125),
        caption: 'лог',
        width: buttonWidth / 2,
        object: false,
        svgObject: false,
        pointerupFunction: function () {
            logButtonFunction();
        },
    },
    razdvButton: {
        filename: 'razdv2',
        modes: [OTJAT_MODE, 'Navedenie', 'Najatie'],
        x: fullscreenXY['x'],
        y: fullscreenXY['y'],
        caption: 'Во весь экран',
        //width: fullscreenButtonSize,
        object: false,
        svgObject: false,
        pointerupFunction: function () {
            document.body.requestFullscreen();
        }
    }
};

var playerBlockModes = [OTJAT_MODE, ALARM_MODE];
var digitPositions = [3, 2, 1];

var digits = {
    playerDigits: {
        Otjat: {
            digit_0: {filename: 'numbers/player_digit_0'},
            digit_1: {filename: 'numbers/player_digit_1'},
            digit_2: {filename: 'numbers/player_digit_2'},
            digit_3: {filename: 'numbers/player_digit_3'},
            digit_4: {filename: 'numbers/player_digit_4'},
            digit_5: {filename: 'numbers/player_digit_5'},
            digit_6: {filename: 'numbers/player_digit_6'},
            digit_7: {filename: 'numbers/player_digit_7'},
            digit_8: {filename: 'numbers/player_digit_8'},
            digit_9: {filename: 'numbers/player_digit_9'}
        },
        Alarm: {
            digit_0: {filename: 'numbers/player_digit_0'},
            digit_1: {filename: 'numbers/player_digit_1'},
            digit_2: {filename: 'numbers/player_digit_2'},
            digit_3: {filename: 'numbers/player_digit_3'},
            digit_4: {filename: 'numbers/player_digit_4'},
            digit_5: {filename: 'numbers/player_digit_5'},
            digit_6: {filename: 'numbers/player_digit_6'},
            digit_7: {filename: 'numbers/player_digit_7'},
            digit_8: {filename: 'numbers/player_digit_8'},
            digit_9: {filename: 'numbers/player_digit_9'}
        },
    },
    timerDigits: {
        Otjat: {
            digit_0: {filename: 'numbers/timer_digit_0'},
            digit_1: {filename: 'numbers/timer_digit_1'},
            digit_2: {filename: 'numbers/timer_digit_2'},
            digit_3: {filename: 'numbers/timer_digit_3'},
            digit_4: {filename: 'numbers/timer_digit_4'},
            digit_5: {filename: 'numbers/timer_digit_5'},
            digit_6: {filename: 'numbers/timer_digit_6'},
            digit_7: {filename: 'numbers/timer_digit_7'},
            digit_8: {filename: 'numbers/timer_digit_8'},
            digit_9: {filename: 'numbers/timer_digit_9'}
        },
        Alarm: {
            digit_0: {filename: 'numbers/timer_digit_0'},
            digit_1: {filename: 'numbers/timer_digit_1'},
            digit_2: {filename: 'numbers/timer_digit_2'},
            digit_3: {filename: 'numbers/timer_digit_3'},
            digit_4: {filename: 'numbers/timer_digit_4'},
            digit_5: {filename: 'numbers/timer_digit_5'},
            digit_6: {filename: 'numbers/timer_digit_6'},
            digit_7: {filename: 'numbers/timer_digit_7'},
            digit_8: {filename: 'numbers/timer_digit_8'},
            digit_9: {filename: 'numbers/timer_digit_9'}
        },
    }
}
var modesColors = {
    Alarm: 'red',
    Otjat: 'yellow',
}

var players = {
    youBlock: {
        filename: 'you' +  (lang === 'RU' ? '_ru' : ''),
        x: botXY.x + buttonStepX + buttonWidth / 2,
        y: botXY.y + botHeight * 0.75 * 0.1,
        width: buttonWidth,
        object: false,
        svgObject: false,
        numbers: true,
    },
    player1Block: {
        filename: 'player1' +  (lang === 'RU' ? '_ru' : ''),
        x: botXY.x + buttonStepX + buttonWidth / 2,
        y: botXY.y + botHeight * 0.75 * 0.1,
        width: buttonWidth,
        object: false,
        svgObject: false,
        numbers: true,
    },
    player2Block: {
        filename: 'player2' +  (lang === 'RU' ? '_ru' : ''),
        x: botXY.x + buttonStepX + buttonWidth / 2,
        y: botXY.y + botHeight * 0.75 * (0.2 + 0.1),
        width: buttonWidth,
        object: false,
        svgObject: false,
        numbers: true,
    },
    player3Block: {
        filename: 'player3' +  (lang === 'RU' ? '_ru' : ''),
        x: botXY.x + buttonStepX + buttonWidth / 2,
        y: botXY.y + botHeight * 0.75 * (0.4 + 0.1),
        width: buttonWidth,
        object: false,
        svgObject: false,
        numbers: true,
    },
    player4Block: {
        filename: 'player4' +  (lang === 'RU' ? '_ru' : ''),
        x: botXY.x + buttonStepX + buttonWidth / 2,
        y: botXY.y + botHeight * 0.75 * (0.6 + 0.1),
        width: buttonWidth,
        object: false,
        svgObject: false,
        numbers: true,
    },
    goalBlock: {
        filename: 'goal',
        x: botXY.x + buttonStepX + buttonWidth / 2,
        y: botXY.y + botHeight * 0.75 * (0.8 + 0.1),
        width: buttonWidth,
        object: false,
        svgObject: false,
    },
    timerBlock: {
        // todo цифры таймера нужно загружать отдельно с учетом вертикального коэффициента
        filename: 'timer',
        x: botXY.x + knopkiWidth / 2,
        y: botXY.y + botHeight * 0.75 * 0.5 + buttonHeight / 2,
        width: buttonWidth * 2,
        height: buttonHeight * 2,
        object: false,
        svgObject: false,
        scalable: false,
        numbers: true,
        numbersX1: 0 - buttonWidth * 2 / 2 * 0.15 * (buttonHeightKoef < 1 ? 1 : 1.4) + buttonWidth * 2 / 2 * 0.05,
        dvoetochX: 0 - buttonWidth * 2 / 2 * 0.025 * (buttonHeightKoef < 1 ? 1 : 2.1),
        numbersX2: 0 + buttonWidth * 2 / 2 * 0.05,
        numbersX3: buttonWidth * 2 / 2 * 0.1 * (buttonHeightKoef < 1 ? 1 : 1.4) + buttonWidth * 2 / 2 * 0.035,
        numbersY: buttonHeight * 2 / 5,
    },
};

function displayScoreGlobal(score, blockName, isActive = false)
{
    let mode = isActive ? ALARM_MODE : OTJAT_MODE;

    let container = players[blockName].svgObject;

    let thirdDigit = score % 10;

    let secondDigit = ((score - thirdDigit) % 100) / 10;
    let firstDigit = (score - secondDigit * 10 - thirdDigit) / 100;

    if(thirdDigit !== playerScores[blockName].digit3 || mode !== playerScores[blockName].mode) {
        container.getByName(playerScores[blockName].mode + '_' + playerScores[blockName].digit3 + '_' + '3').setVisible(false);
    }

    if(secondDigit !== playerScores[blockName].digit2 || mode !== playerScores[blockName].mode) {
        container.getByName(playerScores[blockName].mode + '_' + playerScores[blockName].digit2 + '_' + '2').setVisible(false);
    }

    if(firstDigit !== playerScores[blockName].digit1 || mode !== playerScores[blockName].mode) {
        container.getByName(playerScores[blockName].mode + '_' + playerScores[blockName].digit1 + '_' + '1').setVisible(false);
    }

    playerScores[blockName].mode = mode;
    playerScores[blockName].digit3 = thirdDigit;
    playerScores[blockName].digit2 = secondDigit;
    playerScores[blockName].digit1 = firstDigit;


    container.getByName(mode + '_' + thirdDigit + '_3').setVisible(true);

    if (secondDigit > 0 || firstDigit > 0) {
        container.getByName(mode + '_' + secondDigit + '_2').setVisible(true);
    }

    if (firstDigit > 0) {
        container.getByName(mode + '_' + firstDigit + '_1').setVisible(true);
    }
}

function displayTimeGlobal(time, forceShowAll = false)
{
    let mode = (time < 20) ? ALARM_MODE : OTJAT_MODE;
    let disabledMode = (!(time < 20)) ? ALARM_MODE : OTJAT_MODE;

    let container = players.timerBlock.svgObject;

    let thirdDigit = time % 10;

    let secondDigit = ((time - thirdDigit) % 100) / 10;
    let firstDigit = (time - secondDigit * 10 - thirdDigit) / 100;

    if (!container.getByName(mode + '_' + 'dvoetoch').visible) {
        container.getByName(mode + '_' + 'dvoetoch').setVisible(true);
    }

    if (container.getByName(disabledMode + '_' + 'dvoetoch').visible) {
        container.getByName(disabledMode + '_' + 'dvoetoch').setVisible(false);
    }

    if(thirdDigit !== timerState.digit3 || mode !== timerState.mode || forceShowAll) {
        container.getByName(timerState.mode + '_' + timerState.digit3 + '_' + '3').setVisible(false);
        container.getByName(mode + '_' + thirdDigit + '_3').setVisible(true);
    }

    if(secondDigit !== timerState.digit2 || mode !== timerState.mode || forceShowAll) {
        container.getByName(timerState.mode + '_' + timerState.digit2 + '_' + '2').setVisible(false);
        container.getByName(mode + '_' + secondDigit + '_2').setVisible(true);
    }

    if(firstDigit !== timerState.digit1 || mode !== timerState.mode || forceShowAll) {
        container.getByName(timerState.mode + '_' + timerState.digit1 + '_' + '1').setVisible(false);
        container.getByName(mode + '_' + firstDigit + '_1').setVisible(true);
    }

    timerState.mode = mode;
    timerState.digit3 = thirdDigit;
    timerState.digit2 = secondDigit;
    timerState.digit1 = firstDigit;
}

function buttonSetModeGlobal(objectSet, objectName, mode)
{
    let svgObject = objectSet[objectName].svgObject;
    svgObject.bringToTop(svgObject.getByName(objectName + mode));

    if (mode === ALARM_MODE) {
        svgObject.getByName(objectName + ALARM_MODE).setVisible(true);
        svgObject.getByName(objectName + OTJAT_MODE).setVisible(false);
    } else {
        svgObject.getByName(objectName + ALARM_MODE).setVisible(false);
        svgObject.getByName(objectName + OTJAT_MODE).setVisible(true);
    }
}






////
var gameStates = {
    register: {
        1: 'waiting',
        refresh: 1,
        action: function (data) {
            useLocalStorage = true;
            if (!('erudit_user_session_ID' in localStorage)) {
                localStorage.erudit_user_session_ID = data['cookie'];
            }
            queryNumber = 1;
        }
    },
    cookieTest: {
        1: 'waiting',
        refresh: 10000000,
        action: function (data) {
            fetchGlobal(COOKIE_CHECKER_SCRIPT, '', '12=12')
                .then((data) => {
                    if ('gameState' in data) {
                        if (data.gameState == 'register') {
                            gameStates.register.action(data);
                        } else {
                            //queryNumber = 1;
                            commonCallback(data);
                        }
                    } else {
                        var responseText = 'Ошибка';
                        alert(responseText);
                        queryNumber = 1;
                    }
                })
            ;
        },
    },
    desync: {
        1: 'waiting', 2: 'done',
        refresh: 5,
        noDialog: true,
        action: function (data) {
            gameState = gameOldState;
            gameSubState = gameOldSubState;
            enableButtons();
            if ('queryNumber' in data) {
                queryNumber = data['queryNumber'];
            }
        },
        //message: 'Синхронизация с сервером...'
    },
    noGame: {
        1: 'waiting', 2: 'done',
        noDialog: true,
        refresh: 10,
    },
    startGame: {
        1: 'waiting', 2: 'done',
        message: 'Игра начата!',
        refresh: 10,
        action: function (data) {
            buttons['submitButton']['svgObject'].disableInteractive();
            buttons['submitButton']['svgObject'].bringToTop(buttons['submitButton']['svgObject'].getByName('submitButton' + 'Inactive'));

            gameStates['myTurn']['from_noGame'](data);
            gameStates['gameResults']['action'](data);
        },
        from_initGame: function () {
            while (fixedContainer.length)
                fixedContainer.pop().destroy();
            cells = [];
            newCells = [];
            initCellsGlobal();
        },
        from_initRatingGame: function () {
            gameStates['startGame']['from_initGame']();
        }
    },
    chooseGame: {
        1: 'choosing',
        2: 'done',
        refresh: 1000000,
        message: '',
        noDialog: true,
        action: function (data) {
            /*data = {
                players: {0: 30, 1900: 25, 2000:20, 2100:15, thisUserRating: 2400},
                prefs:{from_rating: 2100}
            };*/

            let under1800 = 'Только для игроков с рейтингом 1800+';
            let noRatingPlayers = 'Недостаточно игроков с рейтингом 1900+ онлайн';
            let haveRatingPlayers = 'Выберите минимальный рейтинг соперников';
            let title = '';
            let onlinePlayers = '';
            let chooseDisabled = '';
            if ('players' in data
            ) {
                if (
                    'thisUserRating' in data['players'] &&
                    data['players']['thisUserRating'] < 1800
                ) {
                    chooseDisabled = 'disabled';
                    title = under1800;
                } else {
                    title = haveRatingPlayers;
                }

                if (!(1900 in data['players']) || data['players'][1900] == 0) {
                    title = noRatingPlayers;
                }

                let checked_0 = 'checked';

                if (
                    'prefs' in data &&
                    data['prefs'] !== false &&
                    'from_rating' in data['prefs'] &&
                    data['prefs']['from_rating'] > 0
                ) {
                    checked_0 = '';
                }

                /* ----------------------------------- NEW ---------------------------------- */
                const ratingRadio = (props) => {
                    const {
                        title = '',
                        text = '',
                        inputValue = 0,
                        inputId = '0',
                        isChecked = false,
                        isDisabled = false,
                        extraClass = '',
                        extraInputAttrString = '',
                    } = props;

                    const html = `
									<div title="${title}"
										class="form-check form-check-inline ${extraClass}">
										<input class="form-check-input" type="radio" id="${inputId}" name="from_rating"
										value="${inputValue}"
										${isChecked ? `checked` : ''}
										${isDisabled ? `disabled` : ''}
										${extraInputAttrString ? extraInputAttrString : ''}
											/>
										<label class="form-check-label" for="${inputId}">${text}</label>
									</div>`;

                    return html;
                };

                // ratingValues: number[] ([2000, 2100, 2200, ...])
                const getRatingList = (ratingValues = [], data = {}) => {
                    let resultHtml = '';
                    ratingValues.forEach((ratingValue) => {
                        if (
                            'players' in data &&
                            ratingValue in data['players'] &&
                            data['players'][ratingValue] > 0
                        ) {
                            let isChecked = false;
                            if (
                                'prefs' in data &&
                                data['prefs'] !== false &&
                                'from_rating' in data['prefs'] &&
                                data['prefs']['from_rating'] == ratingValue
                            ) {
                                isChecked = true;
                            }
                            resultHtml += ratingRadio({
                                title: data['players'][ratingValue] + ' в игре',
                                text: `OT ${ratingValue} (${data['players'][ratingValue]})`,
                                inputValue: ratingValue,
                                inputId: `from_${ratingValue}`,
                                isChecked,
                                isDisabled: chooseDisabled.toString(),
                            });
                        }
                    });

                    return resultHtml;
                };

                onlinePlayers = `<div class="box-title-wrap">
												<span>Рейтинг соперника</span>
											</div>`;

                onlinePlayers += `<div class="label-row">
												<div class="form-check">`;
                onlinePlayers += ratingRadio({
                    title: title,
                    text: 'Любой (' +  (0 in data['players'] ? data['players'][0] : '0') + '&nbsp;онлайн)',
                    inputValue: 0,
                    inputId: 'from_0',
                    isChecked: checked_0,
                    isDisabled: chooseDisabled.toString(),
                });

                const ratings = Object.keys(data.players).filter(
                    (item) => !isNaN(Number(item)) && data.players[item] > 0
                );
                // console.log(Object.keys(data.players), ratings);

                ratings.shift();
                onlinePlayers += getRatingList(
                    ratings.slice(0, ratings.length / 2),
                    data
                );

                onlinePlayers += `</div>`; // end col
                onlinePlayers += `	<div class="form-check">`;

                if (ratings.slice(ratings.length / 2).length > 0) {
                    // console.log(ratings.slice(ratings.length / 2));
                    onlinePlayers += getRatingList(
                        ratings.slice(ratings.length / 2),
                        data
                    );
                }

                onlinePlayers += `	</div>`; // end col
                onlinePlayers += `</div>`; // end label-row

                onlinePlayers = `<div class="box box-rating">${onlinePlayers}</div>`;

                /* --------------------------------- END NEW -------------------------------- */
            } // end if 'players'

            let radioButtons =
                '<div style="display:none;" class="form-check form-check-inline"><input class="form-check-input" type="radio" id="twoonly" name="players_count" value="2" checked> <label class="form-check-label" for="twoonly">Только два игрока</label></div>';
            radioButtons +=
                '<div style="display:none;" class="form-check form-check-inline"><input class="form-check-input" type="radio" id="twomore" name="players_count" value="4"> <label class="form-check-label" for="twomore">До четырех игроков</label></div>';

            let wish = '';

            let checked_200 = 'checked';
            let checked_300 = '';

            if (
                'prefs' in data &&
                data['prefs'] !== false &&
                'ochki_num' in data['prefs']
            ) {
                checked_200 = data['prefs']['ochki_num'] == 200 ? 'checked' : '';
                checked_300 = data['prefs']['ochki_num'] == 300 ? 'checked' : '';
            }

            /* ----------------------------------- NEW ---------------------------------- */
            let radioOchki = `
                            <div class="box">
                                <div class="box-title-wrap">
                                    <span>Игра до</span>
                                </div>
                                <div class="label-row">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" id="dvesti" name="ochki_num"
                                            value="200" ${checked_200} />
                                        <label class="form-check-label text-accent" for="dvesti">
                                            <div class="d-inline-flex align-items-center align-middle">
                                                <i class="icon icon-arrow"></i>200
                                            </div>
                                        </label>
                                    </div>

                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" id="trista" name="ochki_num"
                                            value="300" ${checked_300}/>
                                        <label class="form-check-label text-accent" for="trista">
                                            <div class="d-inline-flex align-items-center align-middle">
                                                <i class="icon icon-arrow"></i>300
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>
			`;

            /* --------------------------------- END NEW -------------------------------- */

            // let wishTime = '<br /><br /><strong>Время на ход:</strong><br />';
            let wish_120 = 'checked';
            let wish_60 = '';

            if (
                'prefs' in data &&
                data['prefs'] !== false &&
                'turn_time' in data['prefs']
            ) {
                wish_120 = data['prefs']['turn_time'] == 120 ? 'checked' : '';
                wish_60 = data['prefs']['turn_time'] == 60 ? 'checked' : '';
            }

            /* ----------------------------------- NEW ---------------------------------- */
            let wishTime = `
				            <div class="box pb-1">
                                <div class="box-title-wrap mb-0">
                                    <span>Время на ход</span>
                                </div>

                                <div class="label-row">
									<div class="form-check form-check-inline">

                                        <input class="form-check-input" type="radio" id="dve" name="turn_time"
                                            value="120" ${wish_120} />
                                        <label class="form-check-label text-accent" for="dve">2 минуты</label>
                                    </div>

									<div class="time-img-wrap">
                                        <img src="./images/time.png" class="d-block img-fluid" alt="">
                                    </div>

                                    <div class="form-check form-check-inline">

                                        <input class="form-check-input" type="radio" id="odna" name="turn_time"
                                            value="60" ${wish_60} />
                                        <label class="form-check-label text-accent" for="odna">1 минута</label>
                                    </div>
                                </div>
                            </div>
			`;

            let formHead = `<div class="box">
                            <div class="d-flex flex-row align-items-center">

                                <span>ПОДБОР ИГРЫ ПО ПАРАМЕТРАМ</span>

                                <div class="ml-auto"><a href="#" id="btn-faq" class="btn">FAQ</a>
                                </div>
                            </div>
                        </div>
			`;

            window.modalData = { instruction };

            /* --------------------------------- END NEW -------------------------------- */

            // let formHead = '<h5>Параметры игры (будут учтены при подборе)</h5>';

            let gameform =
                formHead +
                '<form onsubmit="return false" id="myGameForm">' +
                radioButtons +
                wish +
                radioOchki +
                wishTime +
                onlinePlayers +
                '</form>';

            dialog = bootbox.dialog({
                title: gameStates['chooseGame']['message'],
                message: gameform,
                className: 'modal-settings',
                size: 'medium',
                onEscape: false,
                closeButton: false,
                buttons: {
                    cabinet: {
                        label: 'Профиль',
                        className: 'btn-outline-success',
                        callback: function () {
                            setTimeout(function () {
                                fetchGlobal(CABINET_SCRIPT, '', 12).then((dataCabinet) => {
                                    if (dataCabinet == '') var responseText = 'Ошибка';
                                    else var responseArr = JSON.parse(dataCabinet['message']);

                                    /* ------------------------------ PROFILE DATA ------------------------------ */
                                    const profileData = {
                                        name: responseArr.name ? responseArr.name : 'Nickname',
                                        common_id: responseArr.common_id, // id игрока
                                        imageUrl: responseArr.url, // url картинки
                                        imageTitle: responseArr.img_title, // альт картинки
                                        rating: responseArr.info.rating ? responseArr.info.rating : 0, // рейтинг
                                        placement: responseArr.info.top, // место в рейтинге
                                        balance: responseArr.info.SUDOKU_BALANCE, // баланс
                                        ratingByCoins: responseArr.info.SUDOKU_TOP, // рейтинг по монетам
                                        tgWallet: '', // telegram wallet
                                        bonusAccrual: responseArr.info.rewards, // начисление бонусов
                                        balanceSudoku: responseArr.info.SUDOKU_BALANCE, // баланс SUDOKU
                                        referrals: responseArr.refs ? responseArr.refs : [],
                                    };

                                    profileData.cookie = responseArr.form.filter(
                                        (item) => item.inputName === 'cookie',
                                    );
                                    profileData.MAX_FILE_SIZE = responseArr.form.filter(
                                        (item) => item.inputName === 'MAX_FILE_SIZE',
                                    );

                                    // делаем верстку из массива referrals
                                    let referralList = '';
                                    if ('referrals' in profileData && profileData.referrals.length > 0) {
                                        referralList = profileData.referrals
                                            .map(
                                                (ref) => `
								<li class="box">
									<span class="name d-block">${ref[0]}</span>
									<div class="pill-wrap"><span class="pill">${ref[1]}</span></div>
								</li>
						`,
                                            )
                                            .join('');

                                        referralList = `
								<ul class="referral-list">
									${referralList}
								</ul>
						`;
                                    }

                                    function getProfileModal(profileData) {
                                        return fetch('/profile-modal-tpl.html'+ '?ver=' + Math.floor(Date.now()))
                                            .then((response) => response.text())
                                            .then((template) => {
                                                // Заменяем маркеры в шаблоне реальными данными
                                                let message = template
                                                    .replaceAll('{{Profile}}', 'Профиль')
                                                    .replaceAll('{{Wallet}}', 'Кошелек')
                                                    .replaceAll('{{Referrals}}', 'Рефералы')
                                                    .replaceAll('{{Player ID}}', 'ID Игрока')
                                                    .replaceAll('{{Save}}', 'Сохранить')
                                                    .replaceAll('{{Input new nickname}}', 'Задайте новый ник')
                                                    .replaceAll('{{Your rank}}', 'Ваш Рейтинг')
                                                    .replaceAll('{{Ranking number}}', 'Позиция в ТОП')
                                                    .replaceAll('{{Balance}}', 'Баланс')
                                                    .replaceAll('{{Rating by coins}}', 'Рейтинг по монетам')
                                                    .replaceAll('{{Link}}', 'Привязать') // Привязать
                                                    .replaceAll('{{Bonuses accrued}}', 'Начислено бонусов') // Начислено бонусов
                                                    .replaceAll('{{SUDOKU Balance}}', 'Баланс SUDOKU') // Баланс SUDOKU
                                                    .replaceAll('{{Claim}}', 'Забрать<br>(скоро)') // Забрать
                                                    .replaceAll('{{Name}}', 'Имя')
                                                    //.replaceAll('{{Profile}}', 'Профиль')



                                                    .replaceAll('{{MAX_FILE_SIZE}}', profileData.MAX_FILE_SIZE[0].value)
                                                    .replaceAll('{{cookie}}', profileData.cookie[0].value)
                                                    .replaceAll('{{common_id}}', profileData.common_id)
                                                    .replaceAll('{{name}}', profileData.name)
                                                    .replaceAll('{{imageUrl}}', profileData.imageUrl)
                                                    .replaceAll('{{imageTitle}}', profileData.imageTitle)
                                                    .replaceAll('{{rating}}', profileData.rating)
                                                    .replaceAll('{{placement}}', profileData.placement)
                                                    .replaceAll('{{balance}}', profileData.balance)
                                                    .replaceAll('{{ratingByCoins}}', profileData.ratingByCoins)
                                                    .replaceAll('{{tgWallet}}', profileData.tgWallet)
                                                    .replaceAll('{{bonusAccrual}}', profileData.bonusAccrual)
                                                    .replaceAll('{{bonusAccrual}}', profileData.bonusAccrual)
                                                    .replaceAll('{{balanceSudoku}}', profileData.balanceSudoku)
                                                    .replaceAll('{{referralList}}', referralList);

                                                return message;
                                            })
                                            .catch((error) =>
                                                console.error('Ошибка загрузки profile-modal:', error),
                                            );
                                    }
                                    /* ---------------------------- END PROFILE DATA ---------------------------- */

                                    getProfileModal(profileData).then((html) => {
                                        // document.getElementById('test-tpl').innerHTML = html;

                                        dialog = bootbox.alert({
                                            title: '',
                                            message: html,
                                            locale: 'ru',
                                            // size: 'large',
                                            className: 'modal-settings modal-profile',
                                            closeButton: false,
                                            buttons: {
                                                ok: {
                                                    label: 'Назад',
                                                    className: 'btn-sm ml-auto mr-0',
                                                },
                                            },
                                            onShown: function (e) {
                                                profileModal.onProfileModalLoaded();
                                                // document.addEventListener("DOMContentLoaded", profileModal.onProfileModalLoaded);
                                            },
                                            callback: function () {
                                                gameStates['chooseGame']['action'](data);
                                            },
                                        });
                                    });

                                    return false;
                                });
                            }, 100);
                        },
                    },
                    /*cabinet: {
                        label: 'Профиль',
                        className: 'btn-outline-success',
                        callback: function () {
                            setTimeout(function () {
                                fetchGlobal(CABINET_SCRIPT, '', 12).then(
                                    (dataCabinet) => {
                                        if (dataCabinet == '')
                                            var responseText = 'Ошибка';
                                        else
                                            var responseArr = JSON.parse(
                                                dataCabinet['message']
                                            );
                                        var message = '<form id="superForm" >';
                                        for (k in responseArr['form']) {
                                            message +=
                                                '<div class="form-group"' +
                                                ('type' in responseArr['form'][k] &&
                                                responseArr['form'][k]['type'] ===
                                                'hidden'
                                                    ? ' style="display:none" '
                                                    : '') +
                                                '><div class="col-sm-6">' +
                                                '<label for="' +
                                                responseArr['form'][k]['inputId'] +
                                                '">' +
                                                responseArr['form'][k]['prompt'] +
                                                '</label>' +
                                                '</div>';
                                            message +=
                                                '<div class="form-row align-items-center">' +
                                                '<div class="col-sm-8">' +
                                                '<input ';

                                            if ('value' in responseArr['form'][k]) {
                                                message +=
                                                    'value="' +
                                                    responseArr['form'][k][
                                                        'value'
                                                        ] +
                                                    '"';
                                                if (
                                                    'readonly' in
                                                    responseArr['form'][k]
                                                ) {
                                                    message += ' readonly ';
                                                }
                                            } else {
                                                message +=
                                                    'placeholder="' +
                                                    responseArr['form'][k][
                                                        'placeholder'
                                                        ] +
                                                    '"';
                                            }

                                            message +=
                                                ('type' in responseArr['form'][k]
                                                    ? 'type="' +
                                                    responseArr['form'][k][
                                                        'type'
                                                        ] +
                                                    '"'
                                                    : 'type="text"') +
                                                ' class="form-control" name="' +
                                                responseArr['form'][k][
                                                    'inputName'
                                                    ] +
                                                '" id="' +
                                                responseArr['form'][k]['inputId'] +
                                                '" ' +
                                                ('required' in
                                                responseArr['form'][k]
                                                    ? ' required '
                                                    : '') +
                                                '></div>';
                                            message += !(
                                                'type' in responseArr['form'][k] &&
                                                responseArr['form'][k]['type'] ===
                                                'hidden'
                                            )
                                                ? '<div class="col-sm-4 col-form-label">' +
                                                '<button type="submit" class="form-control btn btn-outline-secondary" onclick="' +
                                                responseArr['form'][k][
                                                    'onclick'
                                                    ] +
                                                "($('#" +
                                                responseArr['form'][k][
                                                    'inputId'
                                                    ] +
                                                "').val()," +
                                                responseArr['common_id'] +
                                                ');return false;">' +
                                                responseArr['form'][k][
                                                    'buttonCaption'
                                                    ] +
                                                '</button></div>'
                                                : '' + '</div>';
                                            message += '</div>';
                                        }
                                        message += '</form>';
                                        dialog = bootbox.alert({
                                            title:
                                                'Ваш профиль, <span id="playersNikname">' +
                                                responseArr['name'] +
                                                '</span>' +
                                                '<span id="playersAvatar">&nbsp;' +
                                                '<img style="cursor: pointer;" title="' +
                                                responseArr['img_title'] +
                                                '" src="' +
                                                responseArr['url'] +
                                                '" width="100px" max-height = "100px" />' +
                                                '</span>',
                                            message: responseArr['text'] + message,
                                            locale: 'ru',
                                            size: 'large',
                                            callback: function () {
                                                gameStates['chooseGame']['action'](
                                                    data
                                                );
                                            },
                                        });
                                        return false;
                                    }
                                );
                            }, 100);
                        },
                    },*/

                    // пока скроем через d-none
                    instruction: {
                        label: 'FAQ',
                        className: 'btn-outline-success d-none',
                        callback: function () {
                            dialog = bootbox
                                .alert({
                                    message: instruction,
                                    locale: 'ru',
                                })
                                .off('shown.bs.modal');

                            return false;
                        },
                    },

                    beginGame: {
                        label: 'Начать',
                        className: 'btn-primary',
                        callback: function () {
                            activateFullScreenForMobiles();
                            gameState = 'noGame';
                            fetchGlobal(
                                INIT_GAME_SCRIPT,
                                '',
                                $('.bootbox-body #myGameForm').serialize()
                            ).then((data) => {
                                if (data == '') var responseText = 'Ошибка';
                                else {
                                    commonCallback(data);
                                }
                            });

                            return true;
                        },
                    },
                    stats: {
                        label: 'Статистика',
                        className: 'btn-outline-success',
                        callback: function() {
                            activateFullScreenForMobiles();
                            getStatPageGlobal().then(data => {
                                console.log(data);
                                dialog = bootbox
                                    .dialog({
                                        message: data.message,
                                        locale: lang === 'RU' ? 'ru' : 'en',
                                        className: 'modal-settings  modal-stats',
                                        callback: function () {
                                            console.log('stats loaded');
                                        },
                                        buttons: {
                                            removeFilter: {
                                                label: 'Снять фильтр',
                                                className: 'js-remove-filter btn btn-sm btn-auto mr-0 d-none',
                                                callback: function (e) {
                                                    e.preventDefault();
                                                    return false;
                                                },
                                            },
                                            ok: {
                                                label: 'Назад',
                                                className: 'btn-sm ml-auto mr-0',
                                                callback: function () {
                                                    //gameStates['chooseGame']['action'](data);
                                                    fetchGlobal(STATUS_CHECKER_SCRIPT)
                                                        .then((data) => {
                                                            commonCallback(data);
                                                            gameStates['chooseGame']['action'](data)
                                                        });
                                                }
                                            },
                                        }
                                    })
                                    .off('shown.bs.modal')
                                    .on('shown.bs.modal', function() {
                                        // Вызовите onLoad после того, как модальное окно будет показано
                                        if (data.onLoad && typeof data.onLoad === 'function') {
                                            data.onLoad();
                                        }
                                    })
                                    .find('.modal-content')
                                    .css({
                                        'background-color': 'rgba(230, 255, 230, 1)',
                                    });

                                return false;
                            }).catch(error => {
                                console.error(error);
                            });

                        },
                    },
                    ...(!isTgBot() && {
                        telegram: {
                            label: 'Играть в',
                            className: 'btn-tg',
                            callback: function () {
                                document.location = GAME_BOT_URL + '/?start='
                                    + ((commonId && commonIdHash) ? (commonId + '_' + commonIdHash) : '');

                                return false;
                            },
                        },
                    }),
                    ...(isTgBot() && {
                        invite: {
                            label: 'Пригласить друга',
                            className: 'btn-danger',
                            callback: function () {
                                shareTgGlobal();

                                return false;
                            },
                        },
                    }),
                },
            });
        },
    },
    initGame: {
        1: 'waiting', 2: 'done',
        action: function (data) {
            buttons['submitButton']['svgObject'].disableInteractive();
            buttons['submitButton']['svgObject'].bringToTop(buttons['submitButton']['svgObject'].getByName('submitButton' + 'Inactive'));
        },
        message: 'Подбор игры - ожидайте',
        refresh: 10
    },
    initRatingGame: {
        1: 'waiting', 2: 'done',
        action: function (data) {
            buttons['submitButton']['svgObject'].disableInteractive();
            buttons['submitButton']['svgObject'].bringToTop(buttons['submitButton']['svgObject'].getByName('submitButton' + 'Inactive'));
        },
        message: 'Подбор игры - ожидайте',
        refresh: 10
    },

    myTurn: {
        1: 'thinking', 2: 'checking', 3: 'submiting', 4: 'done',
        message: 'Ваш ход!',
        refresh: 15,
        action: function (data) {
            gameStates['gameResults']['action'](data);
            buttons['submitButton']['svgObject'].setInteractive();
            buttons['submitButton']['svgObject'].bringToTop(buttons['submitButton']['svgObject'].getByName('submitButton' + OTJAT_MODE));
        },
        from_initRatingGame: function (data) {
            gameStates['startGame']['from_initGame']();
            gameStates['myTurn']['from_noGame'](data);
        },
        from_initGame: function (data) {
            gameStates['startGame']['from_initGame']();
            gameStates['myTurn']['from_noGame'](data);
        },
        from_noGame: function (data) {
            if ('fishki' in data)
                placeFishki(data['fishki']);
        },
        from_desync: function (data) {
            if ('fishki' in data)
                placeFishki(data['fishki']);
        },
        from_gameResults: function () {
            gameStates['startGame']['from_initGame']();
        },
        from_preMyTurn: function () {
            resetButtonFunction(true);
            gameStates['startGame']['from_initGame']();
        },
        from_startGame: function () {
            resetButtonFunction(true);
            gameStates['startGame']['from_initGame']();
        }
    },
    preMyTurn: {
        1: 'waiting', 2: 'done',
        message: 'Приготовьтесь - Ваш ход следующий!',
        refresh: 5,
        action: function (data) {
            gameStates['gameResults']['action'](data);

            buttons['submitButton']['svgObject'].disableInteractive();
            buttons['submitButton']['svgObject'].bringToTop(buttons['submitButton']['svgObject'].getByName('submitButton' + 'Inactive'));
        },
        from_desync: function (data) {
            if ('fishki' in data)
                placeFishki(data['fishki']);
        },
        from_initRatingGame: function (data) {
            gameStates['startGame']['from_initGame']();
            gameStates['myTurn']['from_noGame'](data);
        },
        from_initGame: function (data) {
            gameStates['startGame']['from_initGame']();
            gameStates['myTurn']['from_noGame'](data);
        },
        from_noGame: function (data) {
            gameStates['myTurn']['from_noGame'](data)
        },
        from_myTurn: function (data) {
            gameStates['myTurn']['from_noGame'](data)
        },
        from_otherTurn: function (data) {
            gameStates['myTurn']['from_noGame'](data)
        },
        from_gameResults: function () {
            gameStates['startGame']['from_initGame']()
        },
    },
    otherTurn: {
        1: 'waiting', 2: 'done', message: 'Отдохните - Ваш ход через один',
        refresh: 5,
        action: function (data) {
            gameStates['gameResults']['action'](data);

            gameStates['myTurn']['from_noGame'](data);
            buttons['submitButton']['svgObject'].disableInteractive();
            buttons['submitButton']['svgObject'].bringToTop(buttons['submitButton']['svgObject'].getByName('submitButton' + 'Inactive'));

        },
        from_desync: function (data) {
            if ('fishki' in data)
                placeFishki(data['fishki']);
        },
        from_initRatingGame: function (data) {
            gameStates['startGame']['from_initGame']();
        },
        from_initGame: function (data) {
            gameStates['startGame']['from_initGame']();
        },
        from_gameResults: function () {
            gameStates['startGame']['from_initGame']();
        }
    },
    gameResults: {
        1: 'waiting', 2: 'done',
        messageFunction: function (mes) {
            return mes;
        },
        refresh: 10,
        action: function (data) {
            if ("desk" in data && data.desk.length > 0) {
                parseDeskGlobal(data['desk']);
            }
            if ("score" in data) {
                userScores(data);
            }
            if ('activeUser' in data) {
                activeUser = data.activeUser;
            }
        },
        results: function (data) {
            if (dialog && canCloseDialog)
                dialog.modal('hide');
            var okButtonCaption = 'Отказаться';
            if ('inviteStatus' in data && data['inviteStatus'] == 'waiting') {
                var okButtonCaption = 'OK';
            }

            dialog = bootbox.dialog({
                //title: 'Игра завершена',
                message: data['comments'],
                //size: 'small',
                onEscape: false,
                closeButton: false,
                buttons: {
                    invite: {
                        label: 'Предложить игру',
                        className: 'btn-primary',
                        callback: function () {
                            setTimeout(function () {
                                fetchGlobal(INVITE_SCRIPT, '', 12)
                                    .then((dataInvite) => {
                                        if (dataInvite == '')
                                            var responseText = 'Запрос отклонен';
                                        else
                                            var responseText = dataInvite['message'];
                                        if ('inviteStatus' in dataInvite) {
                                            if (dataInvite['inviteStatus'] == 'newGameStarting')
                                                document.location.reload(true);
                                        }
                                        dialogResponse = bootbox.alert({
                                            message: responseText,
                                            locale: 'ru',
                                            size: 'small',
                                            callback: function () {
                                                dialogResponse.modal('hide');
                                                dataInvite['comments'] = data['comments'];
                                                gameStates['gameResults']['results'](dataInvite);
                                            }
                                        });

                                        setTimeout(
                                            function () {
                                                dialogResponse.find(".bootbox-close-button").trigger("click");
                                            }
                                            , 2000
                                        );

                                        return false;
                                    });
                            }, 100);
                        }
                    },
                    ok: {
                        label: okButtonCaption,
                        className: 'btn-info',
                        callback: function () {
                            return true;
                        }
                    },
                    new: {
                        label: 'Новая игра',
                        className: 'btn-danger',
                        callback: function () {
                            newGameButtonFunction(true);
                        }
                    }
                }
            });
        },
        decision: function (data) {
            if (dialog && canCloseDialog) {
                dialog.modal('hide');
            }
            if (dialogResponse) {
                dialogResponse.modal('hide');
            }

            dialog = bootbox.dialog({
                //title: 'Игра завершена',
                message: data['comments'],
                //size: 'small',
                onEscape: false,
                closeButton: false,
                buttons: {
                    invite: {
                        label: 'Принять приглашение',
                        className: 'btn-primary',
                        callback: function () {
                            setTimeout(function () {
                                fetchGlobal(INVITE_SCRIPT, '', 12)
                                    .then((dataInvite) => {
                                        if (dataInvite == '') {
                                            var responseText = 'Запрос отклонен';
                                        } else {
                                            var responseText = dataInvite['message'];
                                        }
                                        if ('inviteStatus' in dataInvite) {
                                            if (dataInvite['inviteStatus'] == 'newGameStarting')
                                                document.location.reload(true);
                                        }
                                        dialogResponse = bootbox.alert({
                                            message: responseText,
                                            locale: 'ru',
                                            size: 'small',
                                            callback: function () {
                                                dialogResponse.modal('hide');
                                                dataInvite['comments'] = data['comments'];
                                            }
                                        });

                                        setTimeout(
                                            function () {
                                                dialogResponse.find(".bootbox-close-button").trigger("click");
                                            }
                                            , 2000
                                        );

                                        return false;
                                    });
                            }, 100);
                        }
                    },
                    ok: {
                        label: 'Отказаться',
                        className: 'btn-info',
                        callback: function () {
                            return true;
                        }
                    },
                    new: {
                        label: 'Новая игра',
                        className: 'btn-danger',
                        callback: function () {
                            newGameButtonFunction(true);
                        }
                    }
                }
            });
        }
    },
    afterSubmit: {refresh: 1}
}

var gameState = 'noGame';
var gameSubState = 'waiting';
var queryNumber = 1;
var lastQueryTime = 0;
var gameOldState = '';

function commonCallback(data) {
    if (('gameState' in data) && !(data['gameState'] in gameStates)) {
        return;
    }

    if ('http_status' in data && (data['http_status'] === BAD_REQUEST || data['http_status'] === PAGE_NOT_FOUND)) {
        console.log(data['message']);
        return;
    }

    if ('query_number' in data && data['query_number'] != (queryNumber - 1)) {
        return;
    }

    gameOldState = gameState;
    gameOldSubState = gameSubState;

    if ('gameState' in data && gameState != data['gameState']) {
        gameState = data['gameState'];

        if('gameNumber' in data) {
            gameNumber = data['gameNumber'];
        }
    }

    if (gameOldState != gameState) {
        soundPlayed = false;
    }

    if (gameState == 'myTurn') {
        if (pageActive == 'hidden') {
            snd.play();
            soundPlayed = true;
        } else if (!soundPlayed) {
            snd.play();
            soundPlayed = true;
        }
    }

    if ('lang' in data && data['lang'] != lang) {
        lang = data['lang'];
        if (lang === 'EN') {
            // ToDo not working under Yandex
            asyncCSS('/css/choose_css.css');
        }
    }

    if ('common_id' in data && !commonId) {
        commonId = data.common_id;
    }

    if ('common_id_hash' in data && !commonIdHash) {
        commonIdHash = data.common_id_hash;
    }

    if (myUserNum === false)
        if ('yourUserNum' in data)
            myUserNum = data['yourUserNum']

    if ('gameSubState' in data)
        gameSubState = data['gameSubState'];

    console.log(gameOldState + '->' + gameState);

    if ((gameOldState != gameState) || (gameOldSubState != gameSubState)) {
        if ('active_users' in data && data['active_users'] == 0) {
            clearTimeout(requestToServerEnabledTimeout);
            requestToServerEnabled = false;
        }

        if (dialog && canCloseDialog)
            dialog.modal('hide');
        if (intervalId) {
            clearInterval(intervalId);
            intervalId = 0;
        }
        if (canOpenDialog) {
            if (gameState == 'initGame' || gameState == 'initRatingGame') {
                dialog = bootbox.confirm({
                    message: ('comments' in data) ? data['comments'] : gameStates[gameState]['message'],
                    size: 'small',
                    buttons: {
                        confirm: {
                            label: 'Ok',
                        },
                        cancel: {
                            label: 'Новая игра',
                            className: 'btn-danger'
                        }
                    },
                    callback: function (result) {
                        if (!result) {
                            newGameButtonFunction(true);
                        }
                    }
                });
                if ('gameWaitLimit' in data) {
                    dialog.init(function () {
                        intervalId = setInterval(function () {
                            var igrokiWaiting = '';
                            if ('gameSubState' in data)
                                igrokiWaiting = "<br />Найдено игроков: " + data['gameSubState'];


                            if ('timeWaiting' in data) {
                                if (!tWaiting) {
                                    tWaiting = data['timeWaiting'];
                                }
                                if (!gWLimit) {
                                    gWLimit = data['gameWaitLimit'];
                                }
                            } else {
                                if (!gWLimit) {
                                    gWLimit = data['gameWaitLimit'];
                                }
                                if (!tWaiting) {
                                    tWaiting = 0
                                }
                            }

                            let content = data['comments'] + igrokiWaiting + '<br />Время подбора: ' + (tWaiting++) + '<br />Среднее время ожидания: ' + (gWLimit) + 'с';
                            dialog.find('.bootbox-body').html(content);
                        }, 1000);
                    });
                } else if ('ratingGameWaitLimit' in data)
                    dialog.init(function () {
                        intervalId = setInterval(function () {
                            if ('timeWaiting' in data)
                                if (!tWaiting)
                                    tWaiting = data['timeWaiting'];
                                else {
                                    data['timeWaiting'] = 0;
                                    if (!tWaiting)
                                        tWaiting = data['timeWaiting'];
                                }
                            dialog.find('.bootbox-body').html(data['comments'] +
                                '<br />Время подбора: ' +
                                (tWaiting++) +
                                'с' +
                                '<br />Лимит по времени: ' +
                                data['ratingGameWaitLimit'] +
                                'c' +
                                '<hr>Вы можете начать новую игру, если долго ждать..');
                        }, 1000);
                    });

            } else if (gameState == 'gameResults') {
                if ('inviteStatus' in data) {
                    if (data['inviteStatus'] == 'newGameStarting') {
                        document.location.reload(true);
                    } else if (data['inviteStatus'] == 'waiting') {
                        gameStates['gameResults']['results'](data);
                    } else {
                        gameStates['gameResults']['decision'](data);
                    }
                } else {
                    gameStates['gameResults']['results'](data);
                }
            } else if (!('noDialog' in gameStates[gameState])) {
                setTimeout(function () {
                        var message = '';
                        var cancelLabel = 'Закрывать через 5 секунд';

                        if ('comments' in data && (data['comments'] !== null)) {

                            if ('messageFunction' in gameStates[gameState]) {
                                message = gameStates[gameState]['messageFunction'](data['comments']);
                            } else {
                                message = data['comments'];
                            }
                        } else if ('message' in gameStates[gameState]) {
                            message = gameStates[gameState]['message'];
                        }

                        if (turnAutocloseDialog) {
                            if (timeToCloseDilog == 5) {
                                cancelLabel = 'Закрывать сразу';
                            } else {
                                cancelLabel = 'Закроется автоматически';
                            }
                        }

                        dialogTurn = bootbox.confirm({
                            message: message,
                            size: 'medium',
                            buttons: {
                                confirm: {
                                    label: 'OK',
                                    className: 'btn-primary'
                                },
                                cancel: {
                                    label: cancelLabel,
                                    className: 'btn btn-outline-secondary'
                                }
                            },
                            callback: function (result) {
                                if (!result) {
                                    turnAutocloseDialog = true;

                                    if (!timeToCloseDilog) {
                                        timeToCloseDilog = 5;
                                    } else if (!automaticDialogClosed) {
                                        timeToCloseDilog = 1.5;
                                    }

                                    automaticDialogClosed = false;
                                }
                                activateFullScreenForMobiles();
                            }
                        });
                        dialogTurn
                            .find('.modal-content').css({'background-color': 'rgba(255, 255, 255, 0.7)'})
                            .find('img').css('background-color', 'rgba(0, 0, 0, 0)');

                        if (turnAutocloseDialog) {
                            setTimeout(
                                function () {
                                    automaticDialogClosed = true;
                                    dialogTurn.find(".bootbox-close-button").trigger("click");
                                }
                                , timeToCloseDilog * 1000
                            );
                        }
                    }
                    , 500
                );
            }
        }

        enableButtons();

        if ('from_' + gameOldState in gameStates[gameState])
            gameStates[gameState]['from_' + gameOldState](data);

        if ('action' in gameStates[gameState])
            gameStates[gameState]['action'](data);
    }

    if ('timeLeft' in data) {
        vremia.text = data['timeLeft'];
        vremiaMinutes = data['minutesLeft'];
        vremiaSeconds = data['secondsLeft'];

        displayTimeGlobal(+vremiaMinutes * 100 + +vremiaSeconds, true);
    }

    if ('log' in data)
        for (k in data['log'])
            gameLog.unshift(data['log'][k]);

    if ('chat' in data) {
        for (k in data['chat']) {

            if (!
                (((data['chat'][k].indexOf('Вы') + 1) === 1)
                    ||
                    ((data['chat'][k].indexOf('Новости') + 1) === 1))
            ) {
                hasIncomingMessages = true;
                buttons['chatButton']['svgObject'].bringToTop(buttons['chatButton']['svgObject'].getByName('chatButton' + ALARM_MODE));
                buttons['chatButton']['svgObject'].getByName('chatButton' + ALARM_MODE).setData('alarm', true);
            }

            chatLog.unshift(data['chat'][k]);
        }
    }

    if ('winScore' in data) {
        if (!winScore) {
            buttonSetModeGlobal(players, 'goalBlock', data.winScore == 200 ? OTJAT_MODE : ALARM_MODE);
        }

        winScore = data.winScore;
    }

    responseData = data;

    if (pageActive == 'hidden' && gameState != 'chooseGame') {
        fetchGlobal(STATUS_CHECKER_SCRIPT)
            .then((data) => {
                commonCallback(data);
            });
    }
}

function userScores(data) {
    if ("score_arr" in data) {
        for (let k in data['score_arr']) {
            if (k == data['yourUserNum']) {
                let youBlock = players.youBlock.svgObject;

                if (!isUserBlockActive) {
                    let changeBlock = players['player' + (+k + 1) + 'Block'].svgObject;
                    if (changeBlock.visible) {
                        changeBlock.setVisible(false);
                    }

                    youBlock.x = changeBlock.x;
                    youBlock.y = changeBlock.y;
                    youBlock.setVisible(true);
                    youBlock.setAlpha(1);
                    players.timerBlock.svgObject.setAlpha(1);

                    isUserBlockActive = true;

                    noNetworkImg.setScale(youBlock.height / 232 / 4);
                    noNetworkImg.x = youBlock.x + youBlock.width / 2 + noNetworkImg.displayWidth / 2;
                    noNetworkImg.y = youBlock.y;
                    noNetworkImg.setDepth(10000);
                    noNetworkImg.visible = false;
                }

                displayScoreGlobal(data['score_arr'][k], 'youBlock', true);
                buttonSetModeGlobal(players, 'youBlock', gameState === MY_TURN_STATE ? ALARM_MODE : OTJAT_MODE);
            } else {
                let playerBlockName = 'player' + (+k + 1) + 'Block';

                displayScoreGlobal(data['score_arr'][k], playerBlockName, false);
                buttonSetModeGlobal(players, playerBlockName, k == data['activeUser'] ? ALARM_MODE : OTJAT_MODE);

                if (players[playerBlockName].svgObject.alpha < 1) {
                    players[playerBlockName].svgObject.setAlpha(1);
                }

                if (('userNames' in data) && (k in data['userNames']) && (data['userNames'][k] === '')) {
                    players[playerBlockName].svgObject.setAlpha(INACTIVE_USER_ALPHA);
                }
            }


        }

        if (ochki_arr === false) {
            ochki_arr = [];
            for (let k in data['score_arr']) {
                ochki_arr[k] = data['score_arr'][k];
            }
        }
    }
}
////
var letterPrices = new Map([
    [0, 1],
    [1, 3],
    [2, 1],
    [3, 3],
    [4, 2],
    [5, 1],
    [6, 5],
    [7, 5],
    [8, 1],
    [9, 4],
    [10, 2],
    [11, 2],
    [12, 2],
    [13, 1],
    [14, 1],
    [15, 2],
    [16, 1],
    [17, 1],
    [18, 1],
    [19, 2],
    [20, 8],
    [21, 5],
    [22, 5],
    [23, 5],
    [24, 8],
    [25, 10],
    [26, 15],
    [27, 4],
    [28, 3],
    [29, 8],
    [30, 8],
    [31, 3],
    [34, 1],
    [35, 3],
    [36, 3],
    [37, 2],
    [38, 1],
    [39, 4],
    [40, 2],
    [41, 4],
    [42, 1],
    [43, 8],
    [44, 5],
    [45, 1],
    [46, 3],
    [47, 1],
    [48, 1],
    [49, 3],
    [50, 10],
    [51, 1],
    [52, 1],
    [53, 1],
    [54, 1],
    [55, 4],
    [56, 4],
    [57, 8],
    [58, 4],
    [59, 10]
]);  ////
var rusLetters = new Map([
  ['а', 0],
  ['б', 1],
  ['в', 2],
  ['г', 3],
  ['д', 4],
  ['е', 5],
  ['ж', 6],
  ['з', 7],
  ['и', 8],
  ['й', 9],
  ['к', 10],
  ['л', 11],
  ['м', 12],
  ['н', 13],
  ['о', 14],
  ['п', 15],
  ['р', 16],
  ['с', 17],
  ['т', 18],
  ['у', 19],
  ['ф', 20],
  ['х', 21],
  ['ц', 22],
  ['ч', 23],
  ['ш', 24],
  ['щ', 25],
  ['ъ', 26],
  ['ы', 27],
  ['ь', 28],
  ['э', 29],
  ['ю', 30],
  ['я', 31],
  ['ё', 32],
  ['#', 33]
]);  ////
/*
var snd = new Audio("data:audio/mpeg;base64,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qJrx4KOQjahZxWbcZgztj2c49nKmkId44S71j0c8eV9yDK6uPRzx5X18eDvjvQ6yKo9ZSS6l//8elePK/Lf//IInrOF/FvDoADYAGBMGb7FtErm5MXMlmPAJQVgWta7Zx2go+8xJ0UiCb8LHHdftWyLJE0QIAIsI+UbXu67dZMjmgDGCGl1H+vpF4NSDckSIkk7Vd+sxEhBQMRU8j/12UIRhzSaUdQ+rQU5kGeFxm+hb1oh6pWWmv3uvmReDl0UnvtapVaIzo1jZbf/pD6ElLqSX+rUmOQNpJFa/r+sa4e/pBlAABoAAAAA3CUgShLdGIxsY7AUABPRrgCABdDuQ5GC7DqPQCgbbJUAoRSUj+NIEig0YfyWUho1VBBBA//uQZB4ABZx5zfMakeAAAAmwAAAAF5F3P0w9GtAAACfAAAAAwLhMDmAYWMgVEG1U0FIGCBgXBXAtfMH10000EEEEEECUBYln03TTTdNBDZopopYvrTTdNa325mImNg3TTPV9q3pmY0xoO6bv3r00y+IDGid/9aaaZTGMuj9mpu9Mpio1dXrr5HERTZSmqU36A3CumzN/9Robv/Xx4v9ijkSRSNLQhAWumap82WRSBUqXStV/YcS+XVLnSS+WLDroqArFkMEsAS+eWmrUzrO0oEmE40RlMZ5+ODIkAyKAGUwZ3mVKmcamcJnMW26MRPgUw6j+LkhyHGVGYjSUUKNpuJUQoOIAyDvEyG8S5yfK6dhZc0Tx1KI/gviKL6qvvFs1+bWtaz58uUNnryq6kt5RzOCkPWlVqVX2a/EEBUdU1KrXLf40GoiiFXK///qpoiDXrOgqDR38JB0bw7SoL+ZB9o1RCkQjQ2CBYZKd/+VJxZRRZlqSkKiws0WFxUyCwsKiMy7hUVFhIaCrNQsKkTIsLivwKKigsj8XYlwt/WKi2N4d//uQRCSAAjURNIHpMZBGYiaQPSYyAAABLAAAAAAAACWAAAAApUF/Mg+0aohSIRobBAsMlO//Kk4soosy1JSFRYWaLC4qZBYWFRGZdwqKiwkNBVmoWFSJkWFxX4FFRQWR+LsS4W/rFRb/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////VEFHAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAU291bmRib3kuZGUAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAMjAwNGh0dHA6Ly93d3cuc291bmRib3kuZGUAAAAAAAAAACU=");
*/
var snd = new Audio("data:audio/wav;base64,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hwBd/4UAVv+FAE//hgBJ/4gAQv+LAD3/jgA3/5AAMv+RAC3/kQAr/5AAKP+NACb/igAk/4cAIv+FAB7/gwAa/4MAF/+DABT/hQAR/4gAEf+KABL/iwAT/4sAFf+JABn/hQAd/4EAIv9+ACj/fAAu/3oANf95ADv/eABB/3YASP90AE//cABZ/20AZP9qAHD/ZQB8/2AAif9aAJb/VACj/00Asf9FAL//PgDO/zYA3v8tAO7/IwD+/xoADwAPACAAAwAxAPf/QQDt/1IA4/9kANj/dADO/4UAw/+VALf/pACr/7MAnv/CAJL/0ACF/94Aef/rAG//9wBk/wEBWv8JAVD/EQFH/xcBPv8dATb/IgEv/yYBKP8pASD/LAEY/y0BEv8tAQ3/LAEL/yoBCv8nAQz/IgEO/xsBEv8SARX/CAEX//0AGf/xABv/5QAd/9cAIf/JACb/uQAt/6gANf+XAD7/hQBI/3QAVP9jAGD/UgBs/0IAd/8yAID/IgCJ/xIAkf8BAJj/8P+g/9//qP/N/7P/vP+9/6r/yP+Z/9L/iP/b/3j/4/9q/+v/Xf/x/1H/9/9H//3/Pf8DADP/CAAr/w0AIv8TABr/GAAT/x8ADf8mAAj/KwAF/zAAAf8zAP/+NgD9/jgA/P46APz+PAD9/j8A/f5DAP7+RgD//koAAP9NAAL/UAAF/1IACv9VABD/WQAX/10AHv9hACX/ZAAs/2cAM/9qADr/bgBC/3EASv91AFP/eQBd/30AZ/+AAHH/gwB6/4cAhf+KAJD/jgCc/5IAqP+WALP/mgC+/50Ayf+eANX/nwDh/54A7v+eAPz/nQALAJ0AGgCdACkAnQA3AJ0ARQCeAFMAngBiAJ0AcACcAH4AmgCLAJUAmQCPAKYAiACxAIEAvQB6AMgAdADTAG8A3gBrAOgAZwDxAGMA+wBeAAQBVwAMAU4AFQFFAB4BOwAmATEALQEnADEBHQA0ARQANAENADMBBgAyAQEAMQH8/y4B9/8rAfL/JgHt/yEB5/8bAeD/FAHa/wsB1P8CAdH/+ADP/+0Az//fAM//0QDP/8IA0P+zAND/pQDR/5cA0f+IANH/eQDR/2cA0f9VANL/QgDT/y4A1f8aANf/BwDa//X/3v/k/+H/0//j/8T/5P+0/+X/pf/m/5j/5v+L/+j/ff/p/3D/6v9j/+r/Vv/q/0n/6f8+/+b/Nf/k/yz/4f8l/97/IP/a/xr/2P8V/9X/EP/R/wz/zv8J/8v/B//H/wf/wv8H/73/B/+3/wn/sP8L/6r/Df+j/xH/nf8U/5n/Gf+W/x3/lP8i/5L/J/+P/yz/i/8y/4b/Of+B/0D/fP9I/3f/T/9z/1f/cf9e/3H/Zf9y/2z/dP90/3j/ff97/4b/fv+R/4D/m/+B/6X/gf+v/4D/uP+A/8H/gv/K/4X/0v+J/9r/j//j/5X/6/+b//T/oP/9/6X/BwCp/xEArP8bALD/JQCz/y8At/83ALv/PgC//0UAxP9MAMn/UwDP/1oA0/9iANf/awDa/3MA3f96AN3/gQDd/4gA3/+OAOH/lADj/5kA5/+cAOz/oADx/6QA9f+oAPn/rAD9/7AAAAC1AAQAugAHAL4ACgDCAA0AxAARAMQAFQDEABoAwgAfAL8AJAC8ACkAtwAtALIAMgCtADgAqAA/AKIARwCbAE8AlABYAIwAYgCFAGwAfAB1AHMAfABpAIIAXwCKAFUAkQBLAJgAQACgADYAqAArALEAHwC6ABMAwgAIAMoA/P/RAPD/1wDm/9wA2//fANH/4QDG/+MAu//jALD/5ACn/+YAn//nAJj/6QCR/+sAjP/rAIf/6ACD/+QAgP/fAH7/2AB9/9EAe//IAHv/wAB6/7gAeP+wAHf/qQB3/6IAd/+bAHn/kwB8/4kAgP99AIb/cQCN/2QAk/9WAJn/SACg/zoApv8tAKz/IgCx/xgAtv8NALv/AgDB//j/x//u/87/5P/V/9v/3f/R/+X/yP/s/7//8f+3//b/sP/6/6r//f+k/wAAoP8FAJz/CgCY/w4Alf8UAJH/GgCO/yAAi/8nAIn/LACI/zIAh/82AIb/OQCF/zoAhP87AIP/PQCC/z0AgP89AID/PwCA/0IAgf9DAIH/RACC/0YAg/9HAIT/SACF/0oAhf9MAIT/TgCC/1AAf/9SAHv/VAB4/1UAdP9WAHL/VgBx/1YAcP9WAG//VgBt/1YAa/9VAGj/VQBl/1UAYf9VAF3/UwBZ/1IAVv9PAFX/SgBU/0UAVf9AAFf/OwBZ/zYAW/8yAF3/LgBg/ykAYf8kAGH/HgBi/xcAZf8OAGr/BABw//n/eP/u/4H/4f+M/9b/lv/L/6D/wP+q/7f/tP+u/77/pv/H/57/0P+W/9r/jP/k/4H/7/92//r/bP8GAGH/EQBY/x0AUf8nAEv/MQBG/zkAQv9BAD7/SgA7/1MAOf9cADf/ZgA0/3AAMv95ADL/gQAy/4gANP+OADb/kQA7/5MAQv+UAEr/lQBU/5UAXv+UAGj/kgBz/5AAfv+OAIr/iwCX/4gApP+DALH/fgC+/3kAzP90ANn/bwDn/2oA9f9mAAQAYgASAF4AIQBaADAAVAA/AE0ATgBFAFwAPQBrADUAeQAuAIcAKACTACMAngAfAKgAHACxABsAugAaAMIAGQDKABgA0gAXANkAFQDfABMA5AAQAOcADwDoAA8A6QARAOkAFADoABgA5wAcAOYAIADlACMA5AAlAOMAJgDhACcA3gApANoAKwDUAC4AzAAyAMMANgC5ADsArwBAAKUAQwCbAEcAkwBJAIsASgCEAEgAfQBGAHcARABvAEIAZwBAAF4APgBUAD4ASgA9AD8AOwA1ADkAKwA1ACEAMQAXACwADgAnAAUAIwD7/x8A8v8aAOj/FwDd/xMA0f8RAMb/DgC6/wsAsP8IAKX/BQCZ/wEAj//9/4X/+v97//f/cP/1/2f/8/9d//P/VP/z/0r/8v9B//D/N//v/y7/7v8m/+3/Hv/t/xb/7v8Q//H/Cv/0/wX/9/8B//v//f7///n+AwD2/gcA8/4KAPH+DADx/gwA8P4MAPH+DADz/gwA9v4NAPr+DgAA/xAAB/8SABD/EgAa/xEAJf8PADH/CgA9/wUASf8BAFb/+/9j//b/cP/x/33/7f+K/+j/mP/j/6f/3P+2/9T/yP/M/9z/wv/w/7f/BACr/xgAoP8tAJX/QACM/1MAg/9lAHz/dgB1/4cAbf+WAGb/pwBe/7cAVv/IAEz/1wBD/+cAOv/1ADP/AwEs/w8BJ/8ZAST/IgEh/yoBIP8xASH/NwEj/z0BJf9CASf/RQEq/0kBLf9LATH/SwE2/0kBPf9FAUb/PwFP/zgBWf8vAWP/JQFt/xsBeP8RAYH/BwGL//wAlf/xAKD/5QCr/9gAt//JAMT/ugDQ/6oA3f+YAOn/hwD1/3UAAABjAAsAUgAVAEEAIAAxACsAIQA1ABEAQQABAE0A8f9YAOD/YgDQ/2wAv/90ALD/egCg/38AkP+EAIH/iABy/40AY/+SAFX/mABI/54APP+kADH/qQAo/64AH/+yABf/tAAQ/7YACf+2AAL/tgD9/rYA+P63APL+uQDs/rwA5/6/AOP+wwDe/sYA2/7HANn+yADZ/scA2f7GANv+xADc/sMA3v7DAOH+wwDj/sMA5v7EAOv+xQDx/sYA+P7FAAD/xAAK/8EAFf+9AB//uAAr/7MANv+uAED/qgBK/6cAVP+kAF//ogBq/54AeP+aAIf/lQCY/5AAq/+IAL//gADS/3cA5f9uAPj/ZAAKAFoAGwBQACoARgA6ADsASwAvAFsAIQBrABMAfQAEAI4A9v+gAOf/sgDZ/8MAzf/UAMH/4wC2//EAqv/+AJ7/CgGQ/xQBgv8eAXP/KQFl/zMBVv88AUr/RQE//0wBNf9SAS3/VgEn/1gBIf9aARr/WQEV/1gBD/9VAQn/UQED/0oB/v5DAfr+OwH3/jIB9/4pAfj+HwH7/hUB/v4JAQL//gAH//IAC//lAA//2AAT/8oAGf+7ACD/qwAn/5kAMP+HADv/dABG/2IAUv9QAF7/QABr/zAAdv8gAID/EACK////k//u/53/3v+n/83/sv+8/77/rP/L/53/2P+N/+T/gP/v/3P/+f9o/wEAX/8JAFb/EABN/xgARP8gADv/KAAx/zAAJv84ABz/QAAT/0gAC/9PAAX/VgAA/1sA/f5gAPv+ZAD5/mgA9/5rAPf+bwD2/nIA9f51APT+eADz/noA8f57APD+ewDx/noA9P54APj+dwD9/ncAA/92AAj/dwAO/3gAE/97ABn/fQAf/34AJf9/ACv/fwAz/34AO/97AEP/dwBM/3MAVv9wAGH/bQBt/2sAef9qAIX/aACR/2YAnf9jAKr/XwC2/1oAwf9VAM7/UQDc/0wA6P9IAPb/QwADAD8AEQA6AB8ANgAuADAAPgAqAE4AJABeAB4AbgAXAH4ADwCNAAgAmwACAKkA/f+2APj/wQD0/80A8f/YAO3/4wDo/+0A4//2AN7//wDY/wcB0v8PAc7/FQHK/xoBxv8eAcT/IQHD/yIBw/8kAcP/JgHC/ycBwf8pAcD/KQG//ygBvv8lAb7/IQG//xoBwf8RAcX/BgHK//oAz//uANX/4QDa/9QA3v/IAOL/uwDl/7AA6P+lAOz/mgDw/40A9f+AAPn/cQD+/2IAAwBSAAgAQQAMADAAEQAgABQAEQAXAAIAGQD0/xsA5v8cANj/HgDL/yAAvv8iALD/JQCj/ycAlv8pAIr/KgB//ykAdP8nAGv/JQBj/yIAXP8gAFX/HQBO/xoASP8YAEL/FQA8/xIAN/8PADL/CwAu/wYAK/8BACj//P8l//b/Iv/x/yD/7f8f/+n/H//m/yD/5P8i/+H/JP/f/yb/2/8p/9f/LP/T/zD/0P80/83/OP/L/zv/y/9A/8z/RP/N/0j/zf9N/87/U//Q/1v/0P9i/8//av/P/3L/zv95/8z/gP/L/4b/y/+N/83/lP/P/5z/0/+k/9f/rv/a/7j/3P/E/93/0f/d/+D/3P/u/9z//P/c/wkA3v8VAOD/IADi/yoA5f8zAOj/PgDq/0kA6/9UAOz/XwDs/2sA6v92AOj/ggDm/44A5f+aAOT/pQDk/68A5f+5AOb/wQDn/8kA5//QAOb/1gDl/9wA5f/hAOX/5gDl/+kA5v/rAOj/7QDr/+wA7f/rAPH/6QD1/+cA+f/jAPz/3gAAANkAAgDTAAQAzQAGAMUACQC9AA0AtQASAKsAFwCgAB0AlQAjAIgAKAB7AC4AbQAyAF8ANwBRADwARABCADUASAAmAE4AGABVAAkAXAD7/2IA7f9oAOH/bQDV/3EAyP90AL3/dwCy/3oApv99AJr/gACP/4QAhf+JAHv/jgBz/5EAa/+UAGT/lABe/5MAWf+QAFf/jABV/4cAU/+CAFP/fgBU/3oAVP93AFX/dABX/3IAWf9vAF3/agBh/2QAZv9dAGz/VABy/0sAeP9DAH//OgCG/zIAjf8rAJT/JQCb/yAAo/8bAKr/FACy/w0Auv8EAMP/+//M//D/1f/n/97/3f/n/9T/7//N//f/x////8H/BgC8/w0At/8VALL/HQCt/yQAp/8sAKD/MwCa/zoAlf9BAJD/RwCN/0wAiv9RAIj/VQCH/1kAh/9cAIX/XwCC/2IAgP9mAH3/agB6/20AeP9wAHf/cgB3/3QAdv90AHX/dAB1/3QAc/90AHH/cwBv/3MAbv9yAG3/cQBs/3AAbf9uAG//bABx/2kAdP9mAHj/YgB7/14Aff9ZAH7/VAB+/08Afv9KAH7/RAB//z4Agv83AIf/LwCN/ycAlP8eAJv/FgCi/w0Aqf8FAK7//P+z//P/t//r/7v/4v/A/9n/xf/Q/8z/xv/V/7z/4P+y/+v/p//3/5z/AgCT/w0Aiv8VAIH/HQB5/yYAc/8uAGz/NwBk/0AAXf9KAFb/VQBP/2EASf9sAET/dwBA/4EAPv+KADz/kQA8/5cAPP+cADz/oQA9/6UAPv+pAED/rQBD/7EAR/+1AEv/uQBR/7wAWP+9AF//vQBo/70Acv+7AH3/uQCJ/7YAlP+0AJ//sQCr/64Atv+pAML/owDP/50A3f+VAOr/jAD4/4IABQB5ABMAbwAgAGUALQBdADkAVQBFAEwAUQBDAFwAOgBoAC8AdAAkAH8AGACKAA4AlwADAKIA+v+sAPL/tQDq/70A5P/FAN7/ywDX/9EA0f/XAMv/3ADE/+EAvf/kALb/5wCw/+kAq//pAKf/6ACl/+gApf/nAKX/5QCl/+QApv/jAKb/4ACl/94ApP/bAKL/2ACg/9QAn//PAJ//yQCg/8IAov+6AKb/sgCq/6oArv+jALL/mgC1/5EAt/+JALn/gAC6/3YAvP9rAMD/YADD/1UAyP9JAM3/PwDT/zMA1/8oANv/HQDe/xIA4P8HAOP/+//l//D/5//j/+j/1v/r/8f/7/+5//P/q//3/57//P+S/wIAhv8GAHv/CwBw/w4AZv8RAFv/EwBQ/xYARv8ZAD3/HQA0/yIAK/8nACL/KwAa/y8AEv8yAAv/NQAF/zgAAP86APv+PQD2/kAA8v5DAO/+RQDt/kgA7P5KAOz+TADu/k4A8f5OAPb+TQD7/kwAAf9KAAj/SQAP/0cAF/9HACD/SAAp/0gAM/9IAD3/RgBI/0MAVP8/AF//OABq/zEAdv8rAIP/IwCQ/xwAnf8VAKv/DwC5/wkAx/8DANb//f/l//f/9P/w/wQA6P8SAN7/IADU/y0Ayv86AMD/RwC3/1MAsP9fAKn/awCj/3YAnv+AAJj/igCS/5QAiv+eAIP/pgB8/64Adv+2AHD/vQBt/8IAav/HAGj/ywBn/9AAZ//VAGf/2QBn/9wAZv/fAGX/4QBk/+EAZP/hAGX/4ABm/90Aav/bAG//2QB1/9UAfP/RAIP/zQCL/8kAkf/FAJf/wACe/70Apf+5AK3/tQC2/7AAvv+rAMf/pQDR/54A2/+XAOT/jwDu/4cA9v9/AP7/dQAGAGwADgBiABcAVwAgAE0AKgBDADQAOQBAAC8ASwAlAFQAGwBdABEAZAAIAGsA//9wAPX/dQDr/3oA4P9/ANX/hgDJ/40Avf+UALH/mgCm/6AAm/+kAJD/pwCE/6oAef+rAG7/qwBh/6sAVv+rAEz/qwBE/6wAO/+tADT/rgAt/64AJ/+uACL/qwAd/6gAGP+kABb/oAAU/5wAEv+ZABD/lgAO/5QADf+RAA3/jgAP/4sAEv+GABX/gAAa/3oAIP9zACj/awAw/2UAOf9eAEP/WABO/1MAWf9OAGX/SQBx/0MAfv87AIz/NACb/ysAqv8hALj/FwDH/w4A1/8GAOX////z//n/AQD0/xAA7v8fAOj/LgDi/z4A2/9NANL/XADJ/2oAwf95ALn/hgCy/5MAq/+gAKf/rQCj/7gAn//DAJv/zACX/9QAkf/cAIv/4wCE/+kAfv/uAHj/8QBz//MAcP/0AG7/9ABu//QAbf/0AG3/9ABt//MAbf/zAGv/8QBp/+4AaP/rAGb/5wBl/+EAZP/bAGX/1QBn/84Aav/GAG3/vQBw/7MAcv+pAHT/ngB3/5MAev+HAH3/fACB/3AAhf9kAIr/WQCQ/00Alv9BAJz/NgCi/ywAqP8iAK3/GACz/w0Auf8CAMH/9//J/+z/0v/h/93/1v/o/8v/8f+///r/s/8CAKj/BwCd/wwAkf8QAIf/FAB9/xkAcv8fAGf/JgBd/y0AU/80AEr/PABC/0IAO/9HADX/SwAu/00AKP9OACH/UAAa/1EAE/9SAAz/VAAG/1YAAv9aAP3+XAD5/l4A9v5eAPT+XQDy/lwA8f5ZAPH+VADy/k8A9f5NAPj+SwD8/koAAP9KAAX/SwAL/0oAEf9KABj/SAAh/0UAKv9AADX/PAA//zcASv8yAFb/LgBi/ysAbv8oAHv/JgCK/yUAmP8jAKj/IAC4/x4Ayf8bANv/GADu/xUAAAATABEAEQAiAA8ANAANAEUACwBWAAkAZwAHAHgABQCIAAIAlwAAAKUA/f+zAPv/vwD6/8wA+f/ZAPj/5QD4//EA9//8APf/BgH2/w4B9f8WAfP/HAHx/yIB7/8oAe7/LQHu/zAB7/8yAfL/MwH0/zIB9/8wAff/LQH4/yoB9v8mAfT/IQHx/xsB7v8UAev/DAHp/wMB6f/6AOn/8ADr/+YA7v/cAPD/0QDy/8YA8/+7APT/sAD0/6QA9P+YAPT/jAD0/34A9v9wAPn/YQD9/1MAAgBFAAgAOAAMACkAEAAcABMADwAVAAEAFwDz/xkA5f8aANj/HQDL/yEAv/8mALP/LACn/zIAm/84AI7/PgCD/0IAeP9FAG7/SABk/0oAW/9MAFP/TgBJ/1AAQP9TADf/VQAu/1YAJv9XACD/VgAa/1UAFf9TABH/UQAP/04ADP9KAAn/RwAH/0QAB/9AAAb/PAAH/zgAB/8zAAn/LgAL/ycADv8fABH/FwAV/xAAG/8JACD/AgAm//v/Lv/1/zb/7/8//+n/Sf/i/1P/2/9e/9L/av/J/3b/v/+C/7b/j/+t/5z/pv+q/6D/t/+c/8X/mf/T/5f/4v+U//D/j//+/4n/DQCD/xwAfP8rAHT/OQBv/0YAav9SAGf/XgBm/2kAZv91AGf/gABp/4sAav+XAGv/ogBs/60Abf+2AG7/vgBw/8UAc//MAHb/0QB7/9YAgP/aAIb/3QCL/98AkP/gAJT/4ACY/98Am//dAJ7/2wCh/9gApf/UAKz/zwCz/8kAuv/CAML/ugDL/7MA0v+sANj/pADd/5sA4f+SAOX/iQDp/38A7/9zAPX/aAD9/10ABABSAAwARgATADoAGgAvAB8AIwAkABgAKAANAC0AAgAzAPf/OQDr/0AA4P9IANX/UQDL/1kAwf9gALn/ZQCw/2oAp/9vAKD/cwCY/3cAkP98AIj/gwCB/4oAev+SAHX/mwBw/6MAbP+rAGj/sQBk/7UAYf+4AF//uQBd/7oAXP+6AFv/uwBa/70AWf+/AFj/wQBY/8MAWP/FAFr/xQBd/8MAYP++AGT/uABo/7IAa/+rAG7/pQBx/58AdP+aAHj/lQB9/5AAg/+JAIj/gQCO/3cAlf9sAJv/YQCh/1QAqf9IALH/PAC4/zEAwP8nAMn/HQDT/xIA3f8HAOf/+//x/+//+//i/wUA1P8OAMf/GAC6/yEArv8rAKL/NACY/z4AkP9HAIj/UQCA/1oAeP9jAHD/bABo/3YAYP+AAFn/iQBT/5IAT/+bAE3/owBM/6oATP+xAE3/tgBO/7wATv/BAE7/xgBN/8kATf/KAE7/zABQ/8wAUv/LAFf/yQBe/8YAZf/FAGz/wwBz/8EAev++AID/ugCF/7QAiv+tAI//pQCU/50Am/+VAKL/jQCq/4MAs/96ALr/cADC/2YAyv9dAND/UgDU/0YA1/87ANr/LwDe/yMA4f8WAOX/CQDr//z/8f/w//f/5P/9/9j/AwDM/wcAwf8KALb/DACs/w0Ao/8PAJn/EgCP/xYAhf8bAHz/IABz/ygAa/8uAGP/MgBd/zgAV/89AFL/QQBO/0UASf9KAEX/TgBB/1QAP/9aAD3/YQA9/2gAPv9uAED/cwBC/3gARP97AEf/fgBK/4EATP+DAFD/hQBV/4gAWv+LAF7/jwBk/5IAaf+VAG7/lwB0/5gAe/+YAIL/lgCJ/5MAkv+RAJr/jgCj/4sAq/+JALT/iAC9/4UAxv+CAM//fgDY/3cA4v9vAOv/ZgDz/1sA+v9SAAIASAAKAD4AEgA2ABoALgAjACUALAAcADYAEgBAAAgASgD+/1MA8v9bAOb/YgDa/2kAz/9wAMT/dgC6/3wAsf+DAKr/igCi/5AAmv+WAJL/nACK/6AAgv+kAHn/qABw/6sAaf+uAGP/sABe/7IAXP+zAFv/swBa/7QAW/+0AFz/swBc/7IAXP+xAF3/rwBe/6wAYP+nAGT/oQBq/5oAcf+UAHj/jgCA/4cAh/9/AI7/eACU/3EAmf9oAJ7/XwCk/1YAq/9MALP/QQC7/zgAxf8uAM//JADZ/xoA4f8PAOn/BADv//n/9P/t//n/4v/9/9f/AQDL/wUAwP8JALX/DwCq/xYAn/8cAJX/IQCK/yYAgP8pAHb/KwBu/ywAZv8tAF7/LgBY/zAAUv8zAE3/NwBI/zsAQ/9AAED/RAA+/0UAPP9GADr/RwA5/0YAOP9EADf/RAA3/0QAN/9FADj/RwA8/0oAQP9MAEb/TwBM/1EAVP9TAFz/UwBj/1QAa/9VAHP/VgB7/1cAg/9YAIz/WgCW/1sAoP9dAKv/XgC2/14AwP9eAMz/XQDX/1sA4v9ZAO3/VwD4/1UAAwBTAA4AUgAZAFEAJABQAC4ATgA6AEwARQBHAFAAQgBaADwAZgA2AHEAMQB6ACwAggAoAIoAJACQACEAlwAdAJ4AGACmABEArQAIALMA//+5APX/vwDq/8MA4P/GANf/yADQ/8oAyv/MAMT/zQC//84Auv/NALX/zACv/8wAqf/KAKL/xwCb/8QAlP/BAJD/vQCN/7gAiv+zAIj/rQCI/6cAif+fAIr/lgCK/44Aiv+FAIr/fACK/3IAi/9pAIz/YACP/1cAlP9NAJv/QwCi/zcAqf8sALH/IQC3/xYAvP8JAMH//f/G//D/y//j/9H/1f/Y/8j/3/+7/+X/r//s/6X/8/+b//r/kv8AAIr/BQCB/woAeP8PAG7/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IgBz/yoAa/8yAGT/OABe/z0AWP9CAFP/RgBO/0sASf9PAET/VABA/1sAPf9hADv/ZwA6/2wAOf9yADn/dgA6/3kAO/97ADz/fAA9/30AP/9+AEH/gABF/4MASf+FAE7/iABU/4wAWv+PAF//kgBl/5MAav+UAHH/kwB4/5EAgP+OAIf/jACQ/4kAmP+GAKH/hQCq/4QAsv+BALr/fwDE/3wAzv93ANj/cQDi/2wA7P9mAPb/YQAAAFwACwBYABYAVAAhAE8ALABJADgAQwBEADoATgAwAFcAJgBgABwAaAASAHAACAB3AP//fwD3/4YA7/+OAOf/lQDg/5wA2v+iANL/pgDK/6sAw/+vALv/tACy/7gAq/+7AKT/vgCd/78AmP/AAJT/wACQ/74AjP+8AIj/uQCD/7YAf/+yAHv/rQB3/6gAdf+iAHT/nAB0/5QAdv+NAHn/hQB8/3wAf/9zAIH/awCD/2EAhf9XAIf/TACJ/0EAjf80AJP/KQCZ/x0An/8SAKb/BgCt//v/tP/w/7n/5f+//9n/xf/O/8r/w//Q/7n/1/+w/97/qP/m/6D/7/+Y//j/kf8CAIn/DACC/xUAev8cAHT/IgBu/ygAav8tAGb/MQBj/zYAYf87AGD/QgBf/0gAX/9OAF//UwBg/1cAYv9aAGT/XABn/10Aa/9eAG//YAB0/2IAeP9kAH3/ZwCD/2oAi/9sAJL/bQCZ/20Aof9tAKj/agCv/2gAtv9lAL3/YgDE/18AzP9dANT/XADd/1kA5v9WAO//UwD4/1AAAABMAAgARwARAEIAGQA9ACIAOAAqADQAMwAwADwALABEACkASwAmAFMAIgBaABwAYAAWAGYADwBsAAcAcgD//3gA+P99APL/ggDt/4UA6P+JAOP/iwDf/44A2f+PANP/kQDN/5IAxf+SAL7/kwC3/5MAsf+TAKz/kwCo/5IApf+RAKL/kACf/44Am/+MAJf/iQCS/4UAjP+AAIb/ewCA/3UAfP9wAHn/agB3/2UAd/9gAHn/WgB8/1IAfv9KAIH/QgCE/zgAhv8vAIj/JQCJ/xwAjP8TAI7/CQCR////lf/1/5r/6v+f/+D/pf/V/6r/y/+v/8H/s/+2/7j/rP++/6L/xP+X/8z/jf/U/4T/3f96/+b/cv/w/2v/+P9j/wAAW/8HAFT/DQBM/xQARf8bAD7/IgA4/ykAMv8xAC3/OAAo/0AAJf9HACL/TgAf/1QAHv9aAB7/XwAe/2QAIP9pACL/bQAl/3MAK/94ADD/fgA2/4MAPP+IAEP/iwBK/44AUv+PAFr/jgBj/40Abv+LAHn/iQCG/4gAkv+IAJ7/iACr/4kAuP+IAMX/hwDT/4UA4f+BAO7/ewD8/3UACgBuABcAaAAjAGIAMABdAD4AWQBNAFUAXABRAGoATAB4AEYAhQBAAJEAOACdADAApwAoALAAIAC6ABgAwwARAMsACQDSAAMA2QD+/94A+f/jAPT/5wDv/+kA6f/rAOT/7QDe/+4A2P/vANL/8ADN//AAyP/uAMT/7gDB/+wAvf/qALr/5wC1/+IAr//dAKr/2ACm/9EAov/LAKD/wwCe/7kAnf+xAJz/qACb/54Am/+VAJv/jgCa/4UAmP98AJf/cwCW/2oAlv9gAJb/VQCX/0wAmf9CAJv/OACd/y4An/8jAKD/GACg/wwAoP8BAKH/9f+i/+r/o//f/6b/1v+q/83/r//E/7T/u/+5/7H/v/+p/8T/oP/H/5f/yv+O/8z/hv/N/33/z/91/9L/bf/W/2X/3P9d/+P/Vv/q/1D/8f9K//f/RP/8/z7/AAA5/wQANf8IADH/CwAt/w8AKv8UACj/GgAo/yAAKP8nACn/LQAr/zMALv84ADD/PQAz/0AANf9DADn/RgA8/0kAQP9MAEX/TwBM/1MAU/9XAFr/WwBj/18Aa/9hAHL/YgB6/2IAgv9iAIz/YgCW/2IAof9hAK3/YQC4/2EAxP9hAND/XwDd/10A6f9aAPb/VwAEAFMAEQBOAB0ASQApAEYANABDAEAAQABLAD0AVgA7AGEANwBrADMAdAAuAH4AKQCGACMAjQAdAJUAGACcABIAogAOAKcACwCtAAcAsgADALYAAAC6APz/vQD4/74A8/+/AO7/vwDp/74A4/+9AN//uwDb/7kA2P+3ANb/tQDW/7IA1P+tANL/qADO/6MAy/+dAMb/lgDC/5AAvf+KALr/ggC5/3wAuP91ALn/bQC6/2QAvP9cAL3/UwC9/0oAvf9BALz/OAC6/y4Auf8jALj/GQC4/w8Auf8FALz//f+///b/wv/u/8T/5v/I/9//yv/X/8v/zf/N/8T/zv+7/9D/s//T/6r/1v+i/9r/mv/e/5H/4v+J/+f/gf/r/3n/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DwD+/wsA+/8IAPn/BgD3/wMA9v8BAPX////0//3/8//6//L/9//y//T/8v/w//L/7P/y/+n/8v/m//L/4v/x/+D/8v/e//L/3P/y/9n/8f/X//H/1f/y/9L/8v/P//L/zP/z/8n/9P/H//X/xv/2/8T/9v/D//f/wv/3/8H/+P/A//n/vv/5/73/+f+8//n/vP/5/7v/+v+7//r/vP/6/73/+/++//z/v//9/8D//v/B//7/w////8X////H////yP///8r////N////0P8BANT/AgDX/wIA2/8DAOD/BADk/wUA6f8FAO7/BQDx/wQA9P8EAPn/BAD9/wQAAQAEAAYABAALAAQAEAAFABUABQAZAAUAHQAFACEABQAlAAQAKAACACsAAQAvAAAAMwD//zYA/f85AP3/PQD9/z8A/P9AAPv/QQD5/0IA+P9DAPb/RAD1/0UA8v9EAO//RADs/0QA6v9EAOj/QwDl/0MA5P9CAOH/QADg/z4A3/88AN3/OADc/zUA2/8xANv/LgDa/yoA2P8nANj/JQDX/yEA1v8eANX/GgDV/xUA1P8RANP/DADT/wgA0/8EANT/AADU//3/1P/6/9X/9//W//T/2P/x/9r/7f/b/+n/3v/m/+D/4v/i/97/5P/b/+f/2f/q/9f/7f/V//D/0//z/9L/9v/S//j/0f/7/9D//P/P//7/zv8BAM3/BADM/wcAy/8KAMv/DgDL/xEAzP8UAM3/GADN/xoAzv8dAM//HwDP/yAA0P8hAND/IgDQ/yMA0f8kANP/JQDV/ycA1/8oANr/KQDc/yoA3f8rAN7/LADg/ysA4v8rAOP/KwDl/yoA6P8pAOr/KQDt/ykA7/8pAPL/KQD1/ykA9/8qAPn/KQD8/ygA//8nAAMAJQAGACMACgAhAA4AHwASAB4AFgAcABoAGwAeABoAIQAaACQAGQAnABkAKQAZACsAGQAtABkAMAAZADMAGQA2ABgAOQAXADwAFgBAABUAQgAUAEQAEwBGABMASAASAEkAEgBKABAASwAPAEwADgBOAA0ATgAMAE8ACwBPAAoATgAIAE0ABwBKAAUARwAEAEUAAgBDAAAAQAD+/z4A/f87APz/OAD6/zYA+f8yAPb/LwDz/ysA8f8nAO//IgDs/xwA6f8WAOb/EADl/woA4/8EAOL//v/h//n/4P/0/97/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+P/+//b//f/0//z/8v/8//D//P/u//3/7P/9/+r//f/o//3/5v/+/+P//v/h//7/3////9z////b////2f///9j////W/wAA1f8AANT/AQDT/wIA0v8CANH/AgDP/wMAzv8EAM3/BQDN/wUAzf8GAM3/BgDO/wYAzv8GAM7/BwDN/wcAzP8IAMv/CgDK/wsAyv8NAMn/DgDJ/xAAyP8RAMn/EgDJ/xMAyf8TAMn/EwDK/xMAy/8TAMz/EwDN/xMAzv8UAND/FADS/xUA1f8XANf/GQDZ/xoA2/8bAN3/HADf/xwA4f8cAOP/HADm/xwA6P8aAOr/GQDs/xkA8P8YAPP/FgD3/xUA+v8UAP7/FAACABMABQASAAgAEQALAA8ADgAOABEADQAUAAwAGAALABoACwAdAAsAIAALACIACgAkAAkAJQAIACYABwAoAAUAKQADACoAAQArAP//LAD9/y0A+/8vAPn/LwD4/y8A9v8vAPX/LgD0/y0A8/8sAPH/KgDv/ygA7f8mAOv/JADq/yEA6P8fAOb/HQDl/xsA5P8ZAOL/FgDh/xMA4P8RAN//DwDf/w0A3v8LAN3/CQDd/wgA3f8HANz/BQDc/wMA2/8BANv/AADb//7/2//7/9v/+v/c//j/3f/3/97/9v/f//X/4P/1/+L/9P/j//T/4//0/+T/9P/l//T/5v/0/+f/9f/o//X/6v/2/+z/+P/t//j/7//5//L/+v/0//v/9//8//n//f/8//7//f/+/////f8BAP7/AwD+/wQA//8HAAAACQACAAwAAwAOAAQADwAEABAABQASAAUAEwAEABUABAAWAAUAFwAFABkABgAbAAYAHQAGAB8ABgAhAAcAIwAHACUABwAmAAcAJwAHACgABwAoAAYAJwAHACYABwAmAAgAJQAIACQACAAjAAkAIgAIACEACAAgAAkAHwAIAB4ACQAdAAkAGwAJABoACQAYAAoAFgALABQACwARAAsADwAMAA4ADAAMAAwACQAMAAcADAAFAAwAAwAMAAEADAD//w0A/P8OAPr/DwD4/w8A9v8PAPT/DgDy/w4A8P8NAO7/CwDt/woA6/8JAOr/CADp/wcA5/8GAOX/BQDj/wMA4f8CAOD/AQDe////3f/9/9v//P/a//r/2f/4/9j/9//X//b/2P/1/9j/9P/Y//L/2P/x/9j/7v/Y/+z/2P/q/9j/6P/Z/+b/2f/k/9r/4//a/+L/2//h/93/4f/e/+D/4P/f/+L/3//k/97/5v/e/+f/3f/p/93/6//d/+3/3f/v/93/8v/d//T/3v/3/97/+v/e//3/3/8AAN//AwDg/wYA4f8IAOH/CwDj/w4A5f8RAOf/EwDp/xUA6v8YAOv/GwDt/x4A7v8hAPD/JADx/yYA8f8pAPP/KwD0/y0A9f8vAPf/MQD5/zMA+/81AP3/NwD//zkAAAA7AAEAPQACAD8AAwBAAAQAQAAFAEIABgBDAAcAQwAIAEIACgBCAAsAQQAMAD8ADAA+AA0APQANADwADQA6AAwAOAANADcADQA1AA4ANAAPADIAEAAwABEALQASACoAEwAoABMAJQATACIAEwAeABQAGwAUABcAFAATABYAEAAWAA0AFgAJABYABgAXAAMAGAAAABgA/P8ZAPj/GgD0/xoA8f8aAO3/GgDq/xoA5/8bAOT/GwDh/xsA3v8bANv/GwDY/xsA1f8aANL/GgDP/xoAzP8aAMn/GwDH/xwAxP8cAML/HADA/xsAv/8aAL3/GQC8/xkAu/8YALn/FwC4/xUAt/8TALb/EgC1/xEAtP8RALP/EACy/w8Asv8OALL/DQCz/wsAs/8KALX/CAC2/wYAt/8FALj/BAC5/wMAuv8CALz/AQC9/wAAv////8L//f/E//v/x//6/8r/+P/N//X/0P/0/9P/8//X//H/2v/w/97/7//h/+//5P/t/+f/7f/q/+z/7f/r//D/6f/0/+j/+P/n//z/5v8AAOX/BADk/wgA5P8NAOX/EQDl/xUA5f8ZAOb/HQDn/yEA5/8lAOf/KADo/ywA6P8vAOj/MgDp/zYA6v86AOv/PADs/z8A7f9CAO7/QwDu/0YA7/9IAPD/SgDx/0sA8/9NAPT/TgD0/1AA9v9SAPb/UwD3/1QA9/9VAPj/VgD4/1YA+P9VAPn/UwD5/1IA+v9RAPv/TwD9/00A//9LAP//SQAAAEYAAABDAAAAQQAAAD4AAAA7AP//OAD+/zUA//8xAP//LQD//yoA//8mAP//IgD//x4A//8bAP7/FwD+/xQA/v8QAP3/DAD8/wgA/P8EAP3/AAD+//z////4////9P8AAPH/AQDv/wEA7P8AAOn////n//7/5f/+/+L//f/g//z/3f/8/9v//f/Y//3/1v/+/9T//v/R////z////87/AADM/wAAy/8AAMn/AQDJ/wIAyf8CAMn/AwDJ/wQAyf8EAMn/BQDJ/wUAyf8FAMn/BQDI/wUAyf8GAMr/BgDK/wcAyv8IAMr/CADL/wgAzP8JAM3/CgDO/woAz/8KAND/CwDR/wsA0/8LANX/DADW/wwA2P8MANr/DADd/w0A4P8MAOP/DADl/wwA6P8MAOr/CwDt/wsA7v8LAPD/DADz/w0A9f8PAPf/EAD6/xEA/f8SAAAAEgADABMABgASAAkAEgAMABIADgASABEAEgATABIAFQASABgAEgAaABIAHQATACAAEwAjABMAJQATACcAEwAqABMALAATAC0AEwAuABMALwATAC8AFAAwABQAMAAUADAAEwAxABMAMgARADIAEAAyABAAMgAOADEADAAwAAsAMAAJAC8ACAAtAAcAKwAGACkABAAmAAIAIwABACAA//8dAP7/GgD9/xcA+/8VAPr/EgD5/w8A+P8MAPb/CgD0/wcA8/8EAPH/AQDu//7/7P/7/+r/+f/p//f/5//0/+X/8f/j/+//4//t/+H/6//g/+j/3//m/97/5P/e/+L/3f/g/93/3//c/97/3P/d/9z/3f/c/97/3P/e/9z/3//c/+D/3P/i/9v/4//c/+P/3P/j/9z/4v/d/+L/3f/i/9//4v/g/+L/4v/j/+T/5f/m/+b/6P/o/+n/6f/r/+r/7P/r/+3/7P/u/+3/7//u//D/7//x//D/8//x//T/8v/0//L/9f/0//f/9v/4//f/+f/4//r/+f/8//r//P/7//7/+/8BAPz/AwD8/wQA/P8GAP3/BwD+/wgAAAAJAAEACgACAAoABAALAAYADAAHAA0ACAANAAkADgALABAADAARAA0AEwANABQADgAVABAAFwARABkAEwAbABUAHAAWAB0AFwAeABkAHwAbACAAHQAiAB4AIwAfACMAIAAkACAAJAAhACQAIgAjACMAIwAkACQAJAAkACUAJAAmACUAJgAlACUAJQAkACUAIwAkACEAJAAgACMAHgAiABwAIQAaACAAGAAfABYAHQAVABsAFAAaABIAGQARABcAEAAVAA4AEgALABAACAAOAAUADAACAAoA//8IAPv/BgD3/wQA9f8DAPL/AADv//7/7f/8/+v/+v/p//j/5v/1/+T/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9v/O//f/zf/4/8z/+P/L//j/y//4/8v/+P/L//j/y//4/8v/+P/L//n/y//6/8v/+//L//v/yv/8/8n//P/I//3/yP/+/8f//v/H////x/8AAMj/AQDI/wIAyv8DAMv/BADN/wQAzv8EAM//BQDR/wUA0v8FANT/BgDV/wYA1v8HANj/CADa/wkA3P8KAN7/DADh/w0A5P8PAOb/EADo/xEA6/8SAO7/EwDw/xQA8/8WAPX/FwD5/xkA/P8aAAAAHAAEAB0ACAAfAAwAIAAQACEAEwAiABYAIwAZACQAGwAmAB4AJwAgACgAIgApACQAKwAmACwAKAAsACoALAAsACwALQAsAC8ALAAwACsAMQArADEAKgAxACgAMAAnADAAJgAvACQALQAiACwAIAAsAB0AKwAbACkAGQAnABcAJQAVACMAEgAhAA8AHgANABsACwAYAAgAFQAGABMAAwAQAP//DgD7/wsA+P8JAPT/BwDx/wUA7f8DAOr/AQDn////5P/+/+H//P/f//n/3P/3/9r/9f/Z//T/2P/z/9b/8f/U//H/0//x/9L/8P/R//H/0P/x/8//8f/O//H/zv/y/87/8//N//P/zP/z/8z/9P/M//X/zf/2/83/9v/O//f/z//4/9D/+f/R//r/0f/7/9L/+//T//v/1f/7/9b/+//W//r/2P/6/9r/+//b//z/3P/8/97//v/g////4v8AAOP/AADk/wEA5v8BAOf/AQDo/wEA6v8BAOz/AQDt/wEA7/8CAPH/AwD0/wMA9f8DAPb/BAD4/wUA+f8GAPr/BwD7/wcA/f8IAP//CAABAAgAAwAJAAUACgAHAAwACQANAAsADwANABEADgATABAAFAASABYAFAAYABUAGQAXABoAGQAbABoAHAAbAB0AHQAeAB8AHwAgACAAIQAhACMAIwAkACQAJQAkACYAJQAnACYAKAAmACkAJgAqACYAKwAmACwAJAAsACMALQAhAC0AIQAtACAALQAeAC4AHAAuABoALgAZAC0AFgAtABQALAARACwADgAqAAoAKQAGACcAAwAmAP//JAD7/yMA+P8hAPX/IADx/x4A7v8cAOr/GgDm/xgA4v8WAN//FADb/xEA1/8PANP/DQDQ/wwAzf8KAMr/CADI/wYAxv8EAMX/AwDE/wAAwv/+/8H//P/A//n/v//4/73/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BwDp/wgA6f8JAOr/CgDs/wsA7f8LAO7/DADv/w0A8f8NAPL/DgD0/w4A9f8OAPf/DwD5/xAA+/8QAP3/EAD//xAAAAASAAIAEgAEABIABQATAAYAEwAIABMACQAUAAoAFAALABQADQAUAA4AFQAQABYAEgAVABMAFAAUABQAFgAUABcAFAAYABMAGQATABoAEgAbABEAGwARABwAEQAcABAAHAAPABwADgAcAA0AGwAMABsACgAaAAkAGgAIABkABwAYAAUAGAAFABgABAAXAAIAFgABABYA//8VAP7/FAD9/xMA+/8SAPn/EAD4/w8A9v8OAPX/DADz/wsA8/8JAPL/CADy/wYA8P8EAO//AwDv/wEA7v8AAOz//v/r//3/6//8/+v/+//q//r/6v/5/+n/+f/p//j/6f/3/+n/9//p//b/6f/1/+r/9P/q//T/6v/z/+v/8//s//L/7f/x/+7/8P/v//D/7//v/+//7//v/+//8P/v//H/7//x/+7/8v/u//P/7v/0/+//9f/v//b/7//3/+7/+P/u//n/7v/5/+7/+v/u//v/7v/9/+7//v/v////7/8AAO//AQDw/wIA8P8DAPH/AwDx/wQA8v8EAPP/BQDz/wUA9P8GAPT/BgD2/wcA9/8IAPj/CAD5/wkA+/8JAPz/CQD8/woA/v8KAP//CgD//woAAAALAAEACwACAAsAAwAMAAQADAAGAA0ABwANAAgADgAIAA0ACgANAAsADgAMAA4ADQAOAA4ADgAQAA4AEQAOABEADgASAA4AEwAOABMADgATAA4AFAAOABUADQAVAA0AFQANABYADQAWAAwAFQAMABUADAAVAAsAFQALABQACgAUAAkAFAAJABQACAATAAcAEgAGABEABQARAAQAEAADAA8AAgAOAAIADAAAAAoA//8IAP7/BgD9/wQA/P8DAPv/AQD5/wAA+P////f//f/3//v/9v/6//b/+P/2//b/9f/1//T/9P/z//L/8v/w//L/7v/y/+3/8v/r//L/6v/y/+n/8v/o//L/6P/y/+f/8f/m//H/5v/y/+X/8v/l//H/5f/y/+T/8v/l//L/5f/z/+b/8//n//T/5//0/+j/9f/q//b/6v/2/+v/9//s//j/7f/4/+//+f/w//r/8f/7//P//P/0//3/9v/+//j//v/6/////P8AAP7/AAD//wEAAgACAAMAAgAFAAMABgADAAgABAAJAAQACgAFAAsABgAMAAcADQAHAA4ABwAPAAgAEQAIABIACAASAAgAEgAJABIACQATAAgAEwAJABMACQAUAAoAFAAKABQACgAUAAoAFAALABUACwAVAAsAFQALABUACwAWAAsAFQALABQADAAUAAwAEwALABIACwARAAsAEQALABEADAAQAAwAEAAMABAADQAQAA0ADwAOAA8ADgAPAA4ADgANAA4ADQANAA0ADQANAA0ADAAMAAwACwAMAAsADAALAAsACgALAAoACwAKAAoACQAJAAgACQAHAAcABgAGAAUABQAFAAQABAADAAMAAQADAAEAAgD//wEA/v8AAP3/AAD7////+v/+//j//f/4//z/9v/6//X/+f/0//f/9P/2//P/9f/y//P/8f/x//H/7//w/+7/8P/s/+//6//u/+n/7f/o/+3/5//t/+b/7P/k/+z/4//s/+H/7P/g/+z/4P/s/9//6//e/+v/3f/s/9z/7P/c/+3/2//u/9r/7v/a/+7/2v/v/9r/8P/a//H/2//x/9z/8v/c//P/3v/z/9//9P/h//X/4//1/+T/9v/m//f/6P/4/+r/+P/s//n/7f/5/+//+f/x//r/8//6//X/+//4//v/+//8//3//f8AAP7/BAD//wYA//8JAAAACwAAAA4AAQAQAAEAEwABABUAAgAXAAMAGQAEABsABQAdAAYAHgAGACAABwAhAAcAIwAIACQACAAlAAgAJgAJACcACQAnAAoAJwAKACgADAApAA0AKgANACoADQAqAA0AKgAOACoAEAApABAAKQAQACgAEQAnABIAJgATACUAFAAlABUAJAAVACIAFgAgABYAHwAWAB0AFgAcABYAGwAWABkAFgAYABUAFgAVABQAFQASABUADwAVAA0AFAALABMACgASAAgAEQAGABAABAAPAAIADwD//w4A/f8NAPv/DAD4/wsA9v8KAPP/CQDx/wgA7/8GAO3/BQDr/wQA6v8DAOj/AQDm////5f/+/+T//f/j//v/4v/5/+H/+P/f//f/3v/1/9z/9P/c//P/2//y/9n/8f/Z//H/2P/w/9j/8P/Y/+//2P/v/9j/7v/Y/+7/2f/u/9n/7f/Z/+3/2v/t/9z/7f/c/+3/3v/u/9//7v/g/+//4f/v/+P/8P/k//H/5f/x/+f/8v/o//L/6v/z/+v/8//t//T/7//1//H/9f/0//b/9//3//n/+P/8//n////5/wIA+v8EAPv/BgD7/wgA+/8LAPz/DAD9/w4A/v8QAP//EgD//xQAAAAWAAAAGAAAABoAAQAbAAEAHQABAB8AAQAhAAEAIQAAACIAAAAjAAEAJAABACQAAQAlAAEAJgABACcAAQAnAAEAKAABACgAAQAoAAEAJwACACcAAwAmAAMAJgAEACUABAAkAAQAIwAFACIABQAhAAUAHwAFAB4ABgAdAAYAHAAGABoABwAZAAcAGAAHABYACAAUAAkAEgAJABAACQANAAkACwAJAAkACgAHAAoABQAKAAMACgABAAoA//8KAP3/CgD7/woA+f8JAPf/CQD1/wgA8/8IAPH/BwDv/wYA7v8FAOz/BQDq/wUA6P8EAOf/AwDl/wIA5P8BAOP/AADh////4P/+/97//f/c//z/2//7/9v/+v/a//r/2v/5/9n/+f/Z//j/2f/3/9n/9//Z//b/2f/1/9n/9f/Z//X/2v/0/9r/9P/a//T/2//0/9v/9P/c//T/3f/1/93/9f/e//b/4P/2/+H/9v/j//f/5P/4/+b/+P/n//n/6f/7/+v/+//s//z/7v/9//H//v/z////9f8AAPf/AQD6/wIA/P8DAP7/BAAAAAYAAwAHAAUACAAHAAgACQAKAAwACgAOAAsADwALABEACwAUAAsAFwAMABgADAAaAAsAHAALAB4ACwAgAAsAIQALACIACwAjAAoAJQAKACYACQAnAAkAKAAIACkACAAqAAcAKgAHACoABgAqAAYAKgAEACoAAwAqAAMAKQACACkAAQAoAAAAKAAAACcA//8mAP7/JQD+/yUA/v8jAP3/IgD9/yAA/f8fAPz/HQD8/xsA/P8ZAPv/FwD7/xUA+/8TAPz/EgD9/xAA/f8OAP3/DAD9/woA/f8JAP3/BwD9/wQA/f8CAP7/AAD+//7//v/7//7/+f////f////1/wAA8/8AAPH/AADw/wAA7/8AAO3////s////6v///+n////o////5v///+X////k////4////+L////h//7/4f/+/+H//f/h//3/4P/8/+D/+//g//v/4P/7/+D/+v/g//n/4P/5/+D/+f/h//j/4f/4/+L/+P/j//f/5P/4/+X/+P/m//f/5//4/+j/+P/p//j/6v/4/+v/+f/r//r/7f/6/+7/+v/w//v/8f/8//P//f/1//7/9/////j////6/wAA+/8BAP3/AgD//wMAAAAEAAIABAADAAQABAAGAAYABwAHAAgACQAJAAsACgAMAAoADgALAA8ACwAQAAwAEQAMABIADAATAAwAEwANABQADQAVAA0AFgAOABYADgAXAA4AFwANABgADQAZAA0AGQAMABkACwAZAAoAGQAJABkACAAYAAcAGAAHABcABgAWAAUAFgAFABYAAwAWAAIAFQABABUAAAAUAP//EwD9/xMA/f8RAPz/EAD7/w8A+v8OAPn/DAD4/woA+f8KAPj/CAD3/wYA9/8FAPf/BQD3/wQA9/8CAPf/AAD3////9v/9//f/+//3//n/9//3//f/9v/3//T/9//y//j/8f/4//D/+P/u//n/7P/6/+v/+v/q//v/6f/8/+f//P/m//z/5f/8/+T//P/k//z/4//9/+L//f/i//3/4v/9/+L//v/i//7/4f/+/+H//f/h//3/4P/8/+D//P/h//z/4f/7/+H/+//i//v/5P/7/+X/+//m//v/5//7/+n/+v/r//n/7P/5/+7/+f/w//n/8f/5//P/+P/0//n/9v/6//j/+f/6//n//P/6//7/+v8BAPv/AwD8/wUA/f8HAP3/CQD9/wsA/v8MAP7/DgAAABAAAAASAAEAEwACABUAAwAYAAQAGQAFABoABgAcAAcAHgAIAB8ACgAhAAsAIgAMACIADQAkAA4AJQAPACUAEAAlABEAJgARACcAEwAoABMAKAATACgAEwAoABQAKAAUACgAEwAoABMAJwATACYAEwAlABIAJAASACMAEQAiABEAIQAQACAADwAfAA8AHgAOAB0ADAAbAAsAGQALABcACgAWAAgAFAAHABIABgAQAAUADgAEAA0AAwALAAEACAABAAcAAAAFAP//AwD+/wAA/f////z//f/6//r/+v/4//r/9v/4//P/9//x//f/7//4/+7/9//t//f/6//3/+n/9//n//f/5v/3/+T/9v/j//b/4f/2/+D/9v/f//b/3f/2/9z/9v/a//b/2v/2/9n/9v/Y//X/1//1/9f/9f/W//X/1v/1/9b/9f/W//T/1v/1/9b/9f/W//X/1//1/9f/9f/X//X/2P/0/9r/9P/b//T/3P/0/93/9P/f//T/3//0/+H/9f/i//X/5P/1/+X/9f/n//X/6f/1/+v/9v/t//b/8P/2//P/9//1//f/9//3//r/+P/8//n//v/6/wAA+v8CAPv/BQD8/wcA/f8JAP3/CwD+/w0AAAAQAAEAEgACABQAAwAWAAQAGAAFABoABgAbAAcAHQAIAB8ACQAgAAkAIQAKACIACwAjAAwAJQANACUADQAlAA4AJQAPACUAEAAlABAAJQARACUAEgAkABIAIwASACMAEgAiABEAIQASACAAEgAfABIAHgARAB0AEQAcABEAGwAQABkADwAYAA4AFgAOABQADgASAAwAEAAMAA0ACwALAAoACgAIAAgABwAGAAYABAAFAAIAAwAAAAIA//8BAP3/AAD7////+v/+//j//v/2//3/9f/8//T/+//y//v/8f/6//D/+f/v//n/7v/4/+3/9//s//f/7P/3/+v/9v/q//b/6v/2/+n/9f/p//X/6f/1/+r/9f/q//X/6v/1/+r/9f/q//X/6v/1/+v/9v/r//b/6//2/+v/9v/s//b/7P/1/+3/9f/u//X/7//0//D/9P/x//T/8v/0//P/9f/0//X/9f/1//b/9f/4//X/+f/1//r/9f/7//X//P/1//3/9f/+//b/AAD2/wEA9v8CAPb/AwD3/wUA9/8FAPj/BgD4/wYA+f8HAPn/CAD6/wkA+v8KAPv/CwD8/wwA/f8NAP3/DwD+/xAA//8RAAAAEgABABMAAQAUAAIAFAADABUAAwAWAAQAFgAGABcABwAYAAgAGAAJABgACgAZAAwAGgANABoADgAZAA8AGQAPABoAEAAaABEAGgASABoAEgAaABMAGgATABkAFAAYABUAGAAVABYAFQAWABUAFQAWABQAFgASABYAEgAWABAAFgAOABYADAAVAAoAFAAIABMABgATAAUAEQADABAAAQAPAP//DgD9/w0A+/8MAPn/CwD2/woA9f8JAPP/BwDw/wYA7v8EAOz/AwDq/wEA6P8AAOf////m//3/5P/8/+P/+//i//r/4f/6/9//+v/e//n/3f/4/9z/9//b//f/2//2/9r/9f/a//X/2v/1/9v/9f/b//b/2//1/9v/9f/b//b/3P/2/93/9f/d//X/3v/1/+D/9v/h//f/4//3/+T/+P/m//n/5//6/+n/+v/r//r/7f/7/+//+//w//v/8v/7//T//P/2//z/+P/8//n//P/7//3//f/8/////P8BAPz/AgD8/wUA/P8HAPv/CAD7/woA+/8LAPv/DQD7/w4A+v8QAPr/EQD5/xMA+f8UAPj/FQD3/xYA9/8XAPb/GAD1/xkA9P8aAPT/GgDz/xsA8/8bAPP/HADy/xwA8v8cAPL/HQDy/x0A8v8eAPH/HgDy/x4A8v8fAPL/HwDy/x8A8/8eAPP/HgD0/x4A9f8dAPb/HQD2/xwA9/8cAPj/HAD5/xwA+f8bAPr/GgD7/xoA/P8ZAP7/GQD+/xcA//8VAAAAFAABABMAAgASAAMAEAADAA4ABAANAAUADAAGAAoABwAJAAcACAAIAAYACAAEAAgAAgAJAAAACQD+/wkA/P8JAPr/CQD4/wkA9/8JAPX/CQDz/woA8f8KAO//CgDt/woA6/8KAOn/CQDo/wkA5v8IAOX/CADj/wgA4v8JAOH/CADf/wgA3v8IAN7/BwDd/wgA3f8IAN3/CADc/wcA3P8IANz/CADc/wgA3P8IANz/CADd/wkA3v8JAN//CgDg/wsA4f8LAOL/CwDk/wsA5v8LAOj/DADq/wwA7f8NAO//DQDx/w4A8/8PAPX/DwD3/w8A+f8QAPz/EAD//xAAAQAQAAQAEAAGABEACAARAAoAEQANABEADgARABAAEQASABAAFAAQABUADwAXAA4AGAAOABoADQAbAAwAHQAKAB4ACQAfAAkAHwAHAB8ABgAfAAUAHwADAB8AAQAfAAAAHwD//x4A/v8eAP3/HQD7/x0A+v8cAPn/HAD4/xsA9/8ZAPX/GAD0/xcA8/8WAPL/FQDw/xMA7/8TAO//EgDu/xAA7f8PAO3/DgDt/wwA7f8KAO3/CQDt/wgA7f8GAO3/BQDt/wMA7f8CAO7/AADu////7//+//D//f/x//z/8f/7//H/+v/y//n/8//4//P/9//z//b/9P/0//T/8//1//L/9f/x//b/8f/2//D/9//v//f/7v/4/+7/+P/t//j/7P/4/+z/+P/s//j/7P/5/+v/+P/q//j/6v/5/+r/+P/p//j/6f/4/+n/9//p//b/6P/2/+j/9v/o//X/6P/1/+f/9f/n//X/5v/1/+b/9f/m//X/5v/1/+b/9f/n//b/6P/2/+j/9v/p//f/6v/3/+v/9//s//f/7f/4/+3/+f/u//r/7//7//D/+//x//z/8v/9//T//v/2////9/8AAPn/AgD7/wQA/f8FAP//BwABAAgAAwAJAAUACgAIAAsACgAMAAwADQANAA4AEAAPABEAEAATABEAFQASABYAEwAYABQAGQAUABsAFQAcABUAHQAVAB4AFQAeABYAHwAXACAAFwAgABYAIQAWACEAFgAiABYAIgAVACIAFAAiABMAIQATACEAEgAhABEAIAAPAB4ADgAdAA4AHAANABsADAAZAAwAGAALABcACwAVAAkAFAAIABIACAAQAAcADwAGAA4ABgAMAAYACwAFAAkABAAHAAQABQADAAMAAgABAAIAAAACAP7/AQD9/wEA+/8CAPr/AgD4/wIA9v8CAPT/AgDy/wIA8f8CAPD/AgDv/wIA7/8CAO7/AQDs/wEA7P8CAOz/AQDr/wEA6v8BAOn/AQDp/wAA6P8AAOj////o//3/5//9/+f//P/n//v/5//6/+f/+f/o//n/6P/4/+j/9//p//b/6f/1/+n/9P/p//P/6v/y/+r/8f/q//D/6//v/+z/7v/t/+3/7f/t/+7/7f/v/+z/8P/s//H/7P/y/+z/8//s//T/7P/1/+v/9v/r//f/7f/4/+7/+v/u//v/7v/9/+/////w/wAA8f8CAPL/AwDz/wUA9P8GAPX/CAD3/wkA+P8KAPr/DAD7/w0A/P8PAP7/EAD//xEAAQATAAMAFAAEABYABQAYAAcAGQAIABoACQAbAAoAHAALAB0ADAAeAAwAHgANAB8ADQAgAA0AIAANACAADQAhAA0AIQANACAADgAfAA4AHwANAB8ADQAeAAwAHQALAB0ACwAcAAoAGwAIABoACAAYAAcAGAAGABcABQAVAAUAFAADABMAAgARAAIADwABAA0A//8KAP7/CQD9/wcA/P8EAPv/AgD6/wAA+f/+//n//P/5//r/+f/4//j/9v/5//T/+f/y//j/8P/4/+//+P/t//j/6v/5/+n/+f/o//n/5v/6/+X/+//j//z/4v/9/+H//f/g//7/3v/+/97////e/wAA3f8BANv/AgDb/wMA2/8DANv/BADb/wUA3P8FANz/BQDc/wUA3P8GAN3/BgDd/wYA3v8GAN7/BwDf/wcA4P8HAOH/BgDi/wYA4/8GAOT/BgDl/wUA5/8FAOj/BADp/wMA6/8DAO3/AgDu/wEA7/8BAPH/AADz/wAA9P////X//v/3//3/+f/9//z//P/+//v////7/wEA+/8DAPv/BQD6/wcA+v8JAPr/CwD6/w0A+v8PAPv/EAD7/xIA/P8VAP7/FwD+/xgA/v8aAAAAHAABAB0AAgAfAAMAIQADACIABAAjAAUAJAAGACUABwAlAAgAJgAJACgACQAoAAoAKQALACoACwAqAAwAKgANACoADgAqAA4AKQAPACkADgAoAA4AJwAOACcADwAmAA4AJQAOACMADgAiAA0AIQAMAB8ACwAeAAoAHAAJABoACAAZAAcAFwAFABUABAASAAMAEQADAA4AAQAMAAAACgD+/wgA/f8FAPz/AgD6/wAA+f/+//j/+//3//n/9v/2//X/9P/1//L/9P/v//P/7f/y/+v/8v/p//L/5//y/+b/8f/k//H/4//x/+H/8f/g//H/3//x/97/8v/d//P/3P/z/9z/8//b//T/2//0/9v/9P/b//X/3P/2/9z/9//c//f/3P/4/93/+f/e//r/3v/6/9//+//g//z/4f/8/+L//P/k//z/5f/9/+f//v/o//7/6f///+v////t////7v///+/////x////8v////T//v/1//3/9//9//n//f/7//3//P/9//3//f////z/AAD8/wEA/P8CAPz/BAD7/wUA+/8HAPr/CAD6/wkA+f8LAPn/DAD6/w0A+v8PAPr/DwD6/xAA+v8RAPr/EgD6/xMA+/8UAPv/FQD8/xYA/f8WAP7/FwD//xgAAAAZAAEAGgACABsAAwAbAAUAGwAGABsACAAcAAkAGwAKABsACwAbAAwAGgANABsADgAbAA8AGwAQABoAEQAaABIAGQATABgAEwAYABQAGAAUABcAFQAWABUAFQAWABQAFgATABYAEQAWAA8AFgAOABUADQAUAAsAFAAKABMACQASAAcAEAAFAA8ABAAOAAMADQABAAwA//8LAP3/CgD7/wgA+f8HAPf/BgD2/wQA9P8EAPL/AwDw/wEA7/8BAO7/AADs////6v/+/+r//f/o//v/5v/7/+X/+v/k//n/4//4/+L/9//i//f/4v/3/+H/9v/h//b/4P/2/+D/9v/g//b/4P/2/+D/9v/h//f/4f/3/+H/9//j//f/4//3/+T/+P/k//n/5v/5/+f/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/v/5////+f8AAPr/AgD6/wMA+/8FAPv/BgD8/wcA/f8JAP7/CgD//wsAAAANAAIADgADAA8ABAAQAAUAEgAGABMABwATAAgAFAAJABQACgAVAAoAFgAMABcADQAXAA4AGAAOABgADwAXAA8AGAAQABgAEAAXABAAFgARABYAEgAWABIAFQATABUAFAAVABUAFAAVABMAFQASABUAEQAWABAAFQAPABUADgAVAA0AFQAMABQACwAUAAoAFAAJABIACAARAAgAEQAHABAABgAPAAUADwAFAA4ABAANAAMADAACAAsAAQAKAAEACQABAAgAAAAHAP//BQD//wQA//8CAP7/AQD9/wAA/f/+//3//f/9//z//f/6//3/+f/9//j//f/3//z/9f/8//T//f/z//z/8//8//L//P/y//v/8v/6//H/+v/w//r/8P/6/+//+v/u//r/7v/6/+3/+f/s//n/7P/5/+z/+P/s//f/6//3/+z/9//s//b/7P/2/+z/9v/t//b/7f/2/+3/9f/t//X/7f/0/+3/9P/u//T/7v/0/+//9f/v//T/8P/0//D/9f/x//X/8v/1//L/9f/y//X/8v/1//L/9v/z//b/9P/2//T/9v/1//f/9v/4//f/+P/4//n/+f/6//r/+v/7//r//P/7//3//P/9//3//v/9//7//v//////AAD//wEAAAACAAEAAgACAAMAAgAEAAIABQADAAYABAAIAAQACQAEAAkABQAKAAYADAAFAA0ABgAOAAcADwAHABAABwARAAcAEQAHABIABgATAAYAFAAGABUABgAVAAYAFgAGABYABgAWAAYAFwAGABcABQAXAAUAFwAEABgABAAYAAMAGAACABgAAgAYAAIAGAABABgAAQAYAAEAFwABABcAAAAWAAAAFQAAABQA//8UAP//EwD+/xEA/v8RAP7/EAD9/w8A/f8OAP3/DgD9/w0A/f8LAP3/CgD+/wkA/f8IAP3/BgD9/wUA/v8EAP7/AwD+/wIA/v8AAP7///////7////8////+v////n////4////9/////b////1/wAA9P8AAPT/AADz/wAA8v8BAPH/AQDx/wEA8P8BAO7/AQDt/wIA7f8CAO3/AgDs/wIA6/8CAOr/AwDq/wMA6v8DAOr/AgDp/wIA6v8DAOr/AwDp/wIA6f8DAOn/BADq/wQA6f8DAOn/BADq/wUA6/8FAOv/BQDr/wUA7P8FAO3/BQDu/wUA7v8FAO//BQDw/wYA8f8GAPL/BgDy/wYA8/8HAPT/BwD1/wcA9v8HAPj/BwD5/wgA+v8HAPv/BwD8/wcA/f8IAP7/CQAAAAkAAQAJAAIACQADAAkABAAJAAUACQAFAAkABgAJAAgACQAJAAgACgAHAAsACAANAAgADQAHAA0ABgAOAAYADgAGAA4ABgAPAAUADwAEAA8ABAAQAAMAEQACABEAAQARAAEAEgABABIAAAASAP//EgD+/xIA/P8RAPv/EQD6/xEA+f8QAPj/EAD3/xAA9v8QAPT/DwDz/w8A8v8OAPL/DQDx/wwA8f8LAPD/CgDv/wkA7/8IAO7/BwDu/wYA7f8FAO3/BQDt/wMA7f8DAOz/AgDr/wEA6/8AAOv////r//7/6//9/+v//P/r//z/7P/7/+3/+v/t//r/7v/6/+//+f/v//n/8P/5//H/+f/x//j/8f/3//L/+P/z//j/9P/3//X/9//2//f/9//3//j/9//5//b/+f/1//r/9v/8//f//f/3//3/9//+//f/AAD4/wEA+P8CAPj/AwD5/wQA+f8FAPn/BgD4/wcA+f8HAPn/CAD6/wkA+v8JAPv/CgD8/wsA/P8LAPz/DAD8/w0A/P8NAPv/DgD7/w4A+/8OAPz/DwD8/w8A/f8QAP7/EAD//xEA//8RAP//EQAAABIAAAASAAAAEgAAABIAAQASAAIAEgACABIAAwASAAMAEgAEABIABQASAAYAEgAGABIABgARAAcAEgAIABIACQARAAkAEQAJABEACQARAAoAEAALABAACwAQAAwAEAAMAA8ADQAOAAwADQAMAA0ADAAMAAwADAAMAAsADQAKAA0ACQANAAgADAAHAAwABwALAAYACgAFAAoABAALAAMACwACAAoAAQAKAAEACgD//wkA/v8IAP7/BwD9/wcA/P8GAPv/BQD6/wMA+f8CAPj/AQD4/wEA9v8AAPb////1//7/9P/+//T//P/0//z/8//7//P/+//y//n/8v/4//L/9//x//f/8f/3//H/9v/x//b/8v/1//L/9f/x//T/8v/z//L/8//z//L/8//y//P/8f/z//H/8//w//T/8f/1//L/9v/y//b/8v/2//L/9//y//j/8//5//P/+f/z//n/9P/6//T/+v/0//v/9P/8//X//P/1//3/9f/9//X//f/1//3/9v/+//f//f/3//3/9//+//j//v/4//7/+f/+//n//v/6//7/+//+//z//v/9/////f/+//3//v/9//7//v/9//7//f/+//3////8////+/8AAPv/AQD7/wEA+v8BAPv/AgD7/wIA+v8CAPn/AwD5/wQA+f8EAPn/BQD6/wcA+f8HAPn/BwD5/wcA+f8HAPn/BwD6/wcA+v8IAPr/CAD7/wgA+/8IAPv/CQD7/woA/P8KAPz/CgD8/woA/f8KAP3/CwD9/wwA/f8LAP7/CwD//wwA//8MAP//CwAAAAsAAAALAAEACwABAAsAAgAKAAIACgACAAkAAgAIAAIACAACAAcAAgAHAAIABgACAAYAAgAGAAMABQADAAUAAwAFAAQABAAEAAQABAADAAQAAgAFAAIABQABAAUAAAAGAAAABgD//wYA//8GAP7/BwD+/wcA/f8IAP3/CAD8/wgA/P8JAPz/CQD8/woA/P8KAPz/CwD8/wsA/P8MAP3/DQD9/w4A/f8PAP7/EAD9/xEA/f8SAP3/EwD+/xMA/v8UAP7/FAD+/xQA//8VAP//FgAAABYAAAAWAAEAFgABABcAAgAYAAMAGAADABcABAAXAAQAFwAFABgABQAYAAUAGAAFABcABQAXAAYAFgAFABUABQAVAAYAEwAGABIABgASAAYAEQAHAA8ABwAOAAcADQAHAAsABgAKAAYACAAGAAcABQAEAAUAAgAFAAEABQAAAAUA/v8EAP3/AwD7/wIA+v8CAPn/AgD5/wEA9/////X//v/0//7/8v/9//H//f/w//3/7v/8/+3//P/s//v/6//7/+v/+v/q//r/6f/5/+n/+P/p//j/6P/3/+f/9//n//b/5//2/+b/9v/m//b/5v/1/+b/9f/l//T/5f/0/+X/9P/k//T/5P/0/+T/9P/k//T/5P/0/+T/9f/k//X/5P/1/+T/9f/l//X/5f/1/+X/9f/m//b/5v/2/+b/9v/n//b/5//2/+f/9//n//f/6P/3/+j/9//o//f/6P/3/+j/9//p//j/6v/4/+r/+f/r//n/7P/5/+3/+f/v//r/8P/6//H/+v/z//r/9P/7//X/+//2//v/9//8//j//P/6//3/+//9//z//f/+//7/AQD//wIA//8EAP//BQD//wgAAAAKAAEACwABAA0AAgAOAAIAEAADABMABAAVAAUAFwAFABkABQAbAAUAHAAFAB0ABQAeAAYAIAAHACEACAAiAAgAIgAJACMACgAjAAoAJAALACUADAAlAA0AJgANACcADgAmAA4AJgAPACYADwAmABAAJQAQACQAEAAkABEAIwASACIAEQAhABEAHwARAB4AEgAcABIAGwASABoAEgAYABIAFwASABUAEgAUABIAEgASABAAEQAOABIADQASAAsAEgAJABEACAAQAAcADwAFAA8ABAAPAAIADgABAA0AAAAMAP//DAD+/wwA/P8KAPr/CQD6/wgA+f8HAPf/BgD2/wYA9v8FAPX/AwD1/wIA9P8BAPT/AAD0//7/8//9//P//P/y//v/8v/6//H/+P/w//f/8P/3//D/9//w//b/8P/0//D/8//w//L/8P/x/+//8P/v//D/7//v/+//7v/u/+3/7v/s/+7/7P/u/+z/7v/s/+7/7P/v/+v/7v/r/+//6//v/+v/7//q/+//6//v/+v/7//s/+//7P/w/+3/8P/t//D/7v/x/+7/8v/u//L/7//z/+//9P/v//T/8f/2//L/9v/y//f/8//4//T/+f/1//r/9v/6//f/+//4//z/+P/9//j////5////+v8AAPr/AgD8/wMA/f8EAP3/BQD+/wYA//8HAAAACAAAAAkAAQAKAAMACgADAAsABAAMAAQADAAFAAwABgAMAAcADAAHAAwACAAMAAgADAAIAAwACQALAAkACgAJAAkACQAJAAoACAAKAAcACgAHAAsABgAMAAYADAAFAAsABAALAAMADAACAAwAAQAMAAAADAD//w0A/v8MAPz/DAD8/w0A+/8NAPr/DQD6/wwA+f8MAPj/DAD3/wwA9v8MAPb/DAD2/wwA9f8MAPX/DAD1/wwA9f8MAPX/CwD2/wsA9v8LAPf/CgD3/woA9/8KAPj/CgD4/woA+f8JAPn/CAD5/wgA+v8HAPv/BwD8/wcA/P8GAP3/BQD+/wQA/v8DAP//AgAAAAEAAAABAAEAAAABAAAAAgD//wMA/v8EAP3/BAD8/wQA+/8EAPv/BQD6/wUA+f8GAPj/BgD3/wYA9v8HAPX/BwD1/wcA9P8HAPT/CAD0/wgA8/8IAPP/CADz/wgA8/8IAPL/CADz/wgA8/8IAPL/CQDz/wkA8/8JAPP/CQDy/wkA8/8JAPP/CQD0/wkA9P8JAPT/CgD1/woA9f8LAPX/CwD3/wsA9/8MAPj/DQD4/wwA+f8NAPv/DQD7/w0A+/8NAP3/DQD+/w4A/v8OAP//DwAAAA4AAQAOAAEADwACAA8AAwAPAAMADgAEAA4ABQAOAAYADQAHAA0ABwAMAAgADAAJAAsACQAKAAoACQALAAgACwAIAAwABwAMAAUACwAEAAwAAwAMAAEADAAAAAwA//8MAP3/DAD8/wsA+/8LAPn/CgD3/woA9v8JAPT/CQDz/wgA8v8IAPD/BwDv/wcA7v8GAO3/BQDs/wUA6/8EAOr/AwDp/wIA6P8CAOj/AQDo/wAA5////+f//v/o//3/5//9/+j//P/p//z/6f/7/+n/+v/q//r/6//6/+v/+f/r//j/7f/5/+7/+P/u//f/8P/3//H/+P/y//j/8//3//T/9v/2//b/9//3//f/9v/4//f/+v/3//v/9//8//f//f/4//7/+P/+//j////5/wAA+f8BAPn/AgD6/wIA+/8DAPv/AwD8/wQA/P8EAP3/BQD+/wYA/v8GAP7/BgD//wYA//8GAAAABgAAAAYAAQAGAAEABgABAAYAAgAGAAMABgAEAAYABAAGAAUABgAFAAYABQAGAAUABgAGAAcABwAHAAcABwAHAAcACAAHAAgABwAIAAcACAAHAAgABwAIAAcABwAIAAcACAAHAAgABwAIAAcACAAHAAgACAAJAAkACgAJAAoACAAJAAgACgAJAAsACAALAAgACwAIAAsACAAKAAgACwAHAAwACAAMAAcADAAHAAwABwALAAYACwAGAAoABgAKAAUACQAFAAkABAAJAAQACAAEAAgABAAHAAMABgACAAUAAgAFAAEABAAAAAMA//8CAP//AQD+/wEA/P////z//v/7//7/+//9//r//P/5//v/+P/6//j/+v/3//n/9v/5//b/+P/2//j/9f/3//T/9//0//f/9P/3//P/9//y//f/8v/3//L/9//x//j/8P/4//D/+P/w//j/8P/4//D/+f/x//r/8f/6//H/+//y//v/8v/8//L//P/z//7/9P/+//T//v/z////9P8BAPX/AQD1/wEA9v8CAPf/AwD3/wQA+P8EAPr/BAD6/wQA+/8FAPz/BQD9/wYA/v8GAP7/BgD//wYAAAAFAAEABQADAAUABAAGAAQABQAFAAUABgAEAAcAAwAHAAMACAADAAkAAwAJAAIACgABAAsAAQALAAAADAD//w0A//8NAP7/DgD9/w4A/f8OAP3/DgD8/w8A/P8PAPv/DwD7/w8A+/8PAPv/EAD6/xAA+v8PAPr/DwD6/w8A+v8OAPr/DgD6/w4A+v8OAPr/DQD6/w0A+v8NAPr/DQD6/wwA+/8LAPv/CgD7/woA+/8KAPv/CgD7/wkA+/8JAPv/CQD8/wkA+/8IAPz/BwD9/wYA/f8FAP3/BAD9/wQA/f8EAP3/AgD8/wEA/P8BAPz/AQD8/wEA+/8AAPz////8/////P/+//z//v/8//3//P/8//z//P/7//z/+//7//v/+v/7//n/+//5//v/+f/7//n/+//4//z/9//8//f//P/3//v/9v/8//b//P/2//3/9v/9//X//v/2//7/9v////X/AAD1/wAA9v8AAPX/AAD1/wIA9f8DAPX/AwD1/wQA9f8GAPX/BwD0/wcA9f8IAPX/CAD2/wkA9v8KAPb/CgD2/wsA9v8MAPf/DQD3/w0A9/8OAPf/DwD4/xAA+P8RAPj/EgD5/xIA+f8SAPn/EwD6/xMA+v8TAPv/EwD7/xMA/P8TAP3/EwD+/xMA//8TAP//EwAAABIAAAASAAAAEQABABAAAQAPAAIADgADAA4AAwANAAQADAAEAAwABQALAAUACgAFAAkABgAIAAYABwAGAAUABgAEAAcAAgAHAAEABwAAAAgA/v8IAP3/CAD8/wgA+/8JAPr/CQD5/wgA+P8JAPf/CAD3/wgA9v8IAPX/CQD0/wgA9P8IAPP/CADy/wgA8v8JAPH/CQDx/wgA8P8IAO//CADv/wgA7/8HAO//BwDu/wcA7/8HAO//BwDu/wYA7v8GAO7/BgDu/wUA7f8EAO3/BADt/wQA7v8DAO7/AgDu/wIA7v8BAO7/AQDu/wAA7v8AAO7////v//7/7v/9/+7//P/v//z/8P/8//D/+//w//r/8P/6//H/+v/x//n/8v/4//L/+P/z//f/9P/2//T/9v/1//b/9f/1//b/9f/3//X/+P/1//n/9f/6//X/+v/1//v/9f/8//X//v/2////9v////b/AAD2/wEA9/8CAPf/AwD3/wUA+P8GAPj/BwD4/wgA+f8KAPn/CwD6/wwA+/8NAPv/DgD8/w8A/f8QAP7/EQD+/xIA//8TAAAAFAABABQAAgAUAAIAFQACABYAAwAWAAMAFwAEABgABQAYAAUAGAAFABkABgAZAAcAGAAHABkACAAZAAgAGAAIABgACQAXAAkAFwAJABcACQAWAAoAFQAKABUACgAUAAoAFAAKABIACgASAAoAEQAKABAACgAPAAoADgAJAA0ACQALAAkACgAKAAkACgAIAAkACAAKAAYACgAFAAoAAwAJAAIACQACAAkAAQAJAP//CAD+/wgA/f8JAPz/CQD8/wgA+/8IAPr/CAD6/wcA+f8HAPn/BwD3/wYA9/8GAPb/BgD2/wUA9f8FAPX/BQD1/wUA9P8EAPT/AwD0/wMA9P8CAPP/AgDz/wEA8v8AAPL////y////8v/+//L//f/y//z/8v/7//L/+//y//r/8v/4//L/+P/y//f/8v/1//L/9P/y//T/8v/z//L/8//y//L/8v/x//L/8P/y//D/8f/v//L/7//y/+7/8v/u//L/7f/z/+7/8//t//P/7f/0/+7/9P/u//T/7v/0/+7/9f/u//X/7v/2/+7/9v/v//f/7//4//D/+f/x//n/8v/6//P/+//0//z/9f/8//X//f/3//3/+P/+//j////6////+/8AAPz/AQD9/wIA//8DAAAAAwABAAQAAgAFAAQABQAEAAYABAAHAAYABwAIAAcACAAHAAkACAAKAAkACwAJAAwACQANAAkADgAJAA4ACQAOAAkADgAJAA4ACQAPAAgADwAIAA8ACAAPAAgADwAHAA8ABwAPAAcADwAGAA4ABgAOAAYADgAFAA0ABQANAAUADAADAAwAAgALAAIACwACAAoAAQAJAAEACQABAAgAAQAHAAAABwAAAAcAAAAGAAAABQABAAUAAQAEAAAABAABAAQAAAAEAAAAAwABAAIAAAACAAAAAgAAAAEAAQABAAEAAQABAAEAAQAAAAIAAAADAAEAAwAAAAMAAAAEAAAABQD//wUA//8FAP7/BgD+/wYA/v8GAP7/BwD+/wgA/v8JAP7/CQD+/wkA/v8JAP3/CQD8/wkA/P8JAPz/CQD7/wkA+/8JAPv/CQD7/wkA+v8JAPr/CAD6/wgA+f8IAPn/CAD4/wcA+P8HAPj/BwD3/wYA9/8GAPf/BQD3/wUA9/8EAPf/BAD3/wMA9/8DAPf/AwD3/wIA9/8BAPf/AQD4/wAA+P8AAPj////5////+f////r////6//7/+v/+//v//v/7//7//P/9//z//f/9//3//v/8/////P8AAPz/AQD8/wIA+/8CAPv/AwD7/wQA+/8EAPv/BQD6/wUA+v8FAPr/BgD6/wcA+v8HAPr/BwD6/wgA+v8JAPr/CQD6/wkA+v8KAPr/CgD5/wkA+f8JAPn/CQD4/wkA+P8JAPj/CQD3/wgA9/8IAPf/BwD3/wcA9/8GAPb/BQD2/wUA9f8EAPX/AwD2/wMA9f8CAPT/AgD0/wEA9f8BAPX/AAD1/wAA9f////T//v/0//7/9P/9//T//P/0//z/9P/8//X//P/2//z/9v/7//b/+//3//v/9//7//f/+//4//v/+f/8//r//P/6//v/+//8//3//P/9//z//v/9/////v////7/AAD+/wEA/v8CAP//AgD//wMA//8EAAAABQAAAAYAAAAHAAAACAABAAkAAAAJAAAACQAAAAoAAAALAP//DAD//wwAAAALAP//DAD//wwAAAAMAAAADAD//wwA//8MAP//DAD+/wwA/f8MAP3/DAD8/wwA/P8LAPz/CwD9/woA/P8KAPz/CgD7/woA+/8JAPv/CQD6/wgA+v8IAPr/BwD6/wcA+v8GAPr/BQD6/wUA+/8EAPv/AwD7/wMA/P8DAP3/AgD9/wIA/f8CAP7/AQD+/wEA//8AAAAAAAAAAAAAAQAAAAMA//8DAP//BAAAAAUAAAAGAP//BgD//wcA//8IAP//CQD//woA//8LAP//CwD//wsA//8MAP//DQD//w0A//8NAP7/DQD//w4A//8OAP7/DgD//w0A//8NAP//DQD//w0A//8NAP//DAD//wwA//8LAP7/CgD+/wkA/v8JAP3/CAD9/wcA/P8GAPz/BQD8/wUA/P8FAPv/AwD8/wIA/P8BAPv/AAD7////+//+//v//f/7//3/+v/7//r/+v/6//r/+//6//v/+f/7//f/+//3//v/9v/7//X//P/1//z/9f/9//T//f/z//7/9P/+//T//v/z/wAA8/8BAPP/AgDz/wMA8/8DAPL/BADy/wQA8v8FAPP/BQDz/wYA8/8GAPT/BwD0/wgA9f8IAPX/CAD1/wkA9v8JAPb/CgD2/wsA9/8KAPf/CgD4/woA+f8LAPn/CwD6/wsA+v8LAPv/CgD7/woA/P8KAPz/CgD8/wkA/P8IAP3/CAD9/wcA/v8HAP7/BgD//wUA//8EAP//AwD//wIAAAABAAEAAAABAP//AgD//wMA/v8DAP3/AwD8/wMA/P8EAPv/BQD6/wUA+f8GAPn/BgD4/wcA9/8IAPb/CAD1/wkA9P8JAPP/CgDy/woA8v8LAPH/CwDx/wwA8f8MAPH/DADw/w0A7/8NAO//DgDv/w4A7/8PAPD/DwDv/w8A7/8PAO//DwDw/w8A8f8PAPD/DgDx/w4A8f8OAPH/DgDx/w4A8f8NAPL/DADy/wwA8/8LAPP/CgD0/woA9f8JAPb/BwD2/wYA9/8FAPf/BAD3/wMA+P8DAPn/AgD5/wIA+v8BAPr/AAD7/////P/+//z//f/9//z//f/7//7/+v8AAPn/AAD4/wEA9/8CAPf/AgD3/wMA9/8EAPb/BQD2/wYA9v8GAPX/BwD0/wgA9P8JAPT/CgDz/wsA8/8MAPT/DQD0/w4A9P8OAPT/DwD1/w8A9f8QAPb/EQD2/xIA9/8TAPf/FAD4/xQA+P8VAPn/FgD4/xcA+f8XAPr/GAD7/xgA+/8YAPv/GAD8/xkA/f8YAP3/GAD9/xgA/v8YAP7/FwD+/xcA/v8XAP7/FwD+/xYA/v8WAP//FQD//xUA//8UAP//EwD//xMA//8SAP//EQD//xAA//8PAP//DgD//w0A//8MAP//CwD//wkA//8IAP//BwD//wUA//8EAP//BAAAAAMAAAABAAAA//8BAP7/AAD9/wAA/P8BAPv/AQD6/wEA+v8CAPj/AgD3/wMA9/8EAPX/BAD0/wQA8/8FAPL/BgDx/wYA8P8GAPD/BgDw/wYA7/8HAO7/BwDt/wgA7P8IAOv/CQDr/wkA6/8JAOv/CQDr/wkA6/8JAOr/CQDp/wkA6f8JAOr/CQDp/wkA6f8JAOn/CQDq/wkA6v8JAOr/CADq/wcA6v8GAOv/BgDr/wUA6/8EAOz/BADs/wMA7f8CAO7/AQDu/wAA7////+/////w//7/8f/9//L//f/y//z/8//8//T/+//1//v/9f/6//b/+f/3//n/+P/4//n/9//6//f/+//2//z/9v/9//f//f/3//7/9/////b/AAD3/wEA+P8CAPj/AgD4/wMA+P8EAPn/BAD5/wUA+v8FAPv/BgD7/wcA/P8HAPz/BwD9/wcA/v8IAP//CQD//woA//8KAP//CgAAAAsAAQALAAEADAACAAwAAgAMAAIADAADAA0AAwANAAQADgAEAA4ABAAOAAQADgAEAA4ABAAOAAQADwAEAA8AAwAPAAMADwADAA8AAwAPAAMADwACAA8AAQAPAAEADgABAA8AAAAPAAAADgD//w4A//8OAP//DgD+/w4A/v8OAP7/DgD+/w4A/v8NAP7/DAD+/wwA/v8MAP7/DAD+/wsA/v8LAP7/CwD+/wsA/v8KAP//CQD//wkA//8JAP//CAD//wcAAAAHAAEABgABAAYAAQAFAAIABQADAAQAAwAEAAMAAwAEAAMABAACAAQAAQAEAAEABQAAAAUA//8FAP7/BgD9/wYA/P8GAPz/BgD8/wcA+/8GAPr/BwD6/wcA+f8GAPj/BgD4/wYA+P8GAPf/BQD2/wUA9v8FAPX/BQD1/wQA9f8DAPT/AwD0/wEA9P8AAPT/AAD0/wAA8/////P//v/0//7/9P/+//T//f/0//3/9P/8//T/+//0//v/9P/6//X/+v/1//r/9f/5//X/+f/2//n/9//5//f/+f/4//n/+P/5//j/+f/5//n/+f/5//n/+f/6//r/+v/6//r/+v/6//v/+//8//v//f/8//3//P/9//z//v/8/////f8AAP3/AAD9/wAA/f8BAP7/AgD+/wIA/v8CAP7/AwD//wMA//8DAAAABAAAAAQAAAAEAAAABAAAAAQAAAAEAAAABAABAAMAAQAEAAIABAACAAMAAwADAAMAAgADAAIAAwACAAMAAQACAAEAAgABAAMAAQADAAAABAAAAAQAAAADAP//AwD//wQA//8DAP7/AwD9/wQA/P8EAPz/AwD8/wMA/P8EAPz/BAD8/wQA/P8FAPz/BQD8/wUA/P8EAPz/BAD8/wQA/P8EAPz/BAD9/wQA/f8EAP3/BAD+/wQA//8FAP//BQAAAAUAAQAEAAEABQABAAUAAgAEAAIABAACAAQAAwAEAAMABAAEAAQABQADAAUAAwAGAAMABgADAAYAAwAFAAMABgACAAYAAgAGAAIABgACAAUAAgAFAAIABAACAAQAAgAEAAEAAwABAAMAAQACAAEAAgABAAEAAQABAAEAAQABAAAAAAAAAAAAAAABAP7/AQD+/wEA/f8AAPz/AQD8/wEA/P8AAPv/AAD6/wEA+v8BAPv/AAD6/wAA+v8AAPr/AAD6/wAA+v8BAPr/AQD6/wAA+/8AAPv/AAD8/wAA/f8AAP7////+/wAA//8AAAAA//8AAP//AQD//wEA//8CAP7/AgD+/wMA/v8DAP7/BQD+/wUA/v8GAP7/BgD+/wcA/v8HAP7/CAD+/wgA/f8IAP3/CQD9/wkA/P8JAP3/CQD9/wkA/f8JAP3/CAD9/wgA/f8IAP3/CAD9/wcA/f8HAPz/BgD8/wUA/P8FAPz/BAD8/wMA/P8CAPz/AQD8/wAA/P8AAP3////9//7//P/9//3//f/+//z//v/7//3/+v/9//r//v/5//7/+P////j////4//7/9//+//f////3////9/////f////3/wAA+P8AAPn/AAD5/wAA+f8AAPn/AAD5/wAA+f8AAPr/AQD6/wEA+v8BAPv/AQD8/wIA/P8CAP3/AgD9/wMA/v8CAP7/AgD//wIA//8DAAAAAwABAAMAAQACAAEAAgACAAIAAgADAAMAAwADAAMAAwADAAMAAwADAAMAAgADAAIAAwABAAMAAQADAAEAAwAAAAMAAAADAAAAAgAAAAIA//8CAP//AgD//wIA//8BAP//AQD//wEA/v8AAP7/AQD9/wEA/f8AAPz////8/////P8AAP3/AAD9/////f////3////+/////v/+//7//v////7////+/////f////3/AAD9/wAA/v8BAP7/AgD+/wIA/v8DAP7/BAD+/wQA/v8EAP//BQD+/wYA/v8GAP//BgAAAAcAAAAHAAAABwAAAAgAAAAIAAAACQABAAkAAQAJAAEACQABAAkAAQAJAAEACQABAAkAAQAJAAIACAACAAcAAgAHAAIABgACAAYAAgAFAAMABQADAAQAAwADAAMAAwADAAIAAwACAAMAAgADAAAAAwD//wMA//8DAP7/AgD9/wIA/f8CAPz/AgD8/wIA+/8CAPv/AgD6/wIA+v8BAPn/AQD5/wIA+f8CAPj/AQD4/wEA+P8BAPj/AQD4/wEA+P8BAPn/AQD5/wAA+f8BAPn/AAD6/wAA+v8AAPr/AAD6/wAA+v8AAPr/AQD7/wEA+/8AAPv/AAD8/wAA/f8AAP3/AAD9/wAA/f8AAP3/AQD+/wAA/v8AAP7/AQD+/wEA//8AAP//AAD+/wAA/////////////////v///////v////7//////////v////7////+//7//f/+//3//v/9//7//P/9//z//f/8//3//P/9//v//f/7//3/+//+//r//v/6//7/+v/+//n//v/5//7/+f/+//j//v/4////+P////j////4/wAA9/8BAPf/AgD3/wIA+P8DAPj/BAD4/wQA+P8EAPj/BgD4/wcA+f8IAPn/CAD5/wgA+f8JAPr/CgD6/woA+/8LAPv/CwD8/wsA/f8MAP7/DAD+/wwA//8MAAAADAAAAAwAAAANAAEADQACAA0AAgANAAMADQAEAAwABAAMAAQACwAFAAsABgAJAAYACQAGAAgABwAIAAcABwAHAAYACAAFAAgABAAJAAMACQACAAkAAQAJAAEACQAAAAoA//8JAP7/CgD9/woA/P8KAPv/CgD7/woA+v8KAPr/CgD5/woA+f8KAPj/CQD4/wkA+P8IAPj/CAD4/wgA+P8HAPj/BwD4/wcA+P8GAPj/BgD4/wUA+P8FAPj/BQD4/wQA+f8EAPn/BAD5/wQA+v8EAPr/BAD6/wQA+/8DAPv/AwD7/wIA/P8CAPz/AQD8/wAA/P8AAPz/AAD9/wAA/f8AAPz/AAD8/wAA/f8AAPz/AAD8/wAA/f8AAPz////8/////P////z////7////+/////v//v/7//7/+v/+//r//v/5//7/+f/+//n//f/5//3/+P/9//j//f/4//3/+f/+//n//f/5//3/+f/9//n//f/5//z/+v/8//r//P/6//z/+//7//v/+v/7//r//P/7//3/+//+//r//v/6////+v////n/AAD5/wEA+f8BAPn/AwD6/wQA+v8FAPr/BQD6/wcA+v8IAPv/CAD7/wkA+/8KAPv/CgD7/wsA+/8LAPv/DAD7/wwA/P8MAPz/DAD8/w0A/f8NAP3/DQD+/wwA/v8MAP7/DAD+/wwA//8LAP//CwD//woA//8KAAAACQAAAAkAAAAIAAEABwABAAcAAgAGAAEABQACAAQAAgAEAAIAAgABAAIAAQABAAEAAQAAAAAA//////////8AAP7/AAD+/////f/+//z////8/////P/+//z//v/7//7/+//+//v//f/7//3/+//9//z//P/8//z//P/8//z/+//8//v//f/7//3/+//9//r//f/6//7/+v////r////6/wAA+v8AAPr/AQD7/wEA+/8CAPr/AgD7/wIA+/8DAPz/AwD8/wQA/P8DAP3/AwD9/wMA/v8DAP//BAD//wQAAAAEAAEABAACAAMAAwADAAQAAwAFAAMABgADAAYAAgAHAAIABwACAAgAAQAIAAEACQABAAoAAAAKAP//CgD+/wsA/v8LAP//CwD+/wwA/f8MAP3/DQD8/w0A/P8NAPv/DQD7/w0A+/8NAPv/DQD6/w0A+/8NAPv/DAD6/wwA+/8LAPv/CwD7/woA+/8KAPv/CgD7/wkA+/8JAPv/CAD8/wcA/P8HAPz/BgD9/wYA/f8FAP3/BQD9/wUA/v8EAP7/BAD+/wQA/v8DAP7/AwD//wMA//8DAP//AwD//wMA//8CAP//AgD//wEA//8BAP//AQD//wEA/v8BAP7/AQD+/wEA/v8BAP7/AAD9/wAA/f8BAP3/AQD9/wEA/f8BAP3/AAD9/wAA/f8AAPz/AAD8/////P////z////8////+//+//v//v/7//3/+//9//v//P/7//z/+//8//v/+//8//v//P/6//3/+f/9//n//f/4//7/9//+//f////2////9v////T/AAD0/wAA8/8BAPL/AQDy/wIA8f8CAPH/AwDw/wQA8P8EAO//BQDv/wYA7v8HAO7/BwDv/wgA7/8JAO7/CQDv/wkA7/8KAO//CgDw/wsA8P8LAPD/CwDx/wsA8v8LAPL/DADz/wwA8/8LAPX/CwD2/wwA9/8LAPf/CwD4/woA+f8KAPr/CgD7/wkA/f8IAP7/CAD//wgAAAAHAAEABgACAAUAAwAFAAQABAAFAAQABgADAAYAAgAHAAIACAACAAgAAQAIAAEACQABAAkAAAAJAP//CgD//woA//8KAP7/CQD+/wkA/f8JAP3/CQD9/wkA/f8IAPz/CAD8/wgA/P8IAP3/CAD8/wcA/P8HAPz/BwD8/wYA/P8GAPv/BgD7/wYA+/8FAPv/BQD7/wUA/P8FAPz/BAD8/wUA/P8EAPz/BAD8/wUA/P8FAPz/BQD8/wUA/P8FAPz/BQD8/wUA/P8FAPz/BgD8/wYA+/8GAPv/BgD7/wcA+/8HAPv/CAD7/wgA+v8IAPv/CQD7/wkA+/8JAPr/CgD6/woA+/8KAPr/CgD6/woA+v8KAPr/CgD6/woA+v8KAPr/CQD6/wkA+v8JAPr/CAD6/wgA+/8HAPv/BwD7/wYA+/8FAPv/BAD8/wQA/P8CAPz/AgD9/wEA/f8AAP7////+//7//v/+/////f8AAPz/AQD7/wEA+/8BAPr/AgD5/wMA+P8DAPj/AwD3/wQA9/8FAPb/BQD2/wUA9f8FAPb/BgD2/wcA9f8HAPX/BwD1/wgA9v8IAPb/CAD2/wgA9v8IAPf/CAD3/wgA+P8IAPj/CAD5/wgA+v8IAPr/CAD7/wcA/P8HAPz/BwD9/wcA/f8HAP7/BgD+/wYA//8GAP//BgAAAAUAAQAFAAIABQACAAUAAQAEAAIABAACAAQAAgAEAAIAAwADAAMAAwACAAMAAgADAAMAAgACAAIAAgACAAEAAgABAAEAAAABAAEAAQAAAAAAAAAAAP////////////////7//v/+//7//v/+//7//f/9//3//f/8//3//P/9//z//f/8//z//P/7//v/+//7//r/+//6//v/+v/7//r/+//6//v/+f/7//n/+//5//z/+f/8//j//P/4//z/+P/9//j//v/4//7/+P////f////3////+P////j/AAD4/wAA+P8AAPn/AQD5/wEA+P8CAPn/AgD5/wMA+f8DAPn/AwD6/wMA+v8EAPr/AwD7/wMA+/8DAPv/AwD8/wMA/P8CAP3/AgD+/wIA/v8CAP7/AgD//wEAAAABAAEAAAACAAAAAwD//wMA/v8EAP7/BQD9/wUA/f8GAPz/BwD8/wgA+/8JAPv/CQD7/wkA+/8KAPv/CgD7/wsA+/8LAPv/CwD6/wwA+/8MAPv/DAD7/wwA/P8MAPz/DAD9/wwA/v8MAP7/DAD+/wwA//8LAAAACwABAAsAAQALAAIACgADAAoABAAJAAUACAAGAAgABwAIAAgABwAJAAYACgAFAAsABQALAAUADAAEAAwAAwANAAIADQACAA0AAQANAAAADgAAAA4A//8OAP//DgD+/w8A/v8OAP3/DgD9/w0A/f8NAPz/DAD8/wwA+/8LAPv/CgD7/wkA+/8IAPr/BwD6/wYA+f8FAPn/BAD5/wMA+P8DAPj/AgD3/wEA9/8AAPf////4//7/+P/9//f//P/3//z/9//7//f/+v/3//n/9v/4//f/+P/3//j/9v/3//b/9//2//f/9v/2//b/9v/2//b/9v/2//X/9v/2//f/9v/3//b/9v/2//f/9//3//f/9//2//j/9//5//f/+f/3//n/9//5//j/+v/4//r/+P/7//j/+//5//v/+f/7//r//P/6//3/+//9//v//f/8//3//f/+//3//v/+//3/AAD+/wAA/v8BAP7/AgD+/wMA/v8DAP7/BAD+/wUA/v8HAP7/BwD9/wgA/f8JAP7/CQD+/woA/v8LAP3/CwD9/wwA/f8NAP3/DQD9/w4A/v8OAP7/DwD+/w8A//8PAP//EAD//xAAAAAQAAAAEAAAAA8AAAAPAAEADwABAA8AAQAOAAIADgACAA4AAgANAAMADAAEAAsABQAKAAUACgAGAAkABwAIAAcACAAIAAcACQAGAAkABgAJAAUACgAEAAoAAwAKAAMACgACAAsAAQALAAAACwAAAAsA//8LAP7/CwD9/wsA/f8LAP3/CgD8/wgA+/8IAPv/CQD6/wgA+v8GAPn/BQD5/wUA+v8EAPn/BAD4/wMA+P8CAPj/AQD4/wAA+f////j//v/3//3/+P/8//j/+v/4//n/+P/5//n/+P/5//f/+f/2//n/9f/5//X/+v/0//r/8//6//P/+//y//v/8v/7//L/+//y//v/8v/7//L/+//y//z/8//8//P//P/z//z/9P/8//T//P/0//z/9f/8//b//P/2//v/9v/8//f//P/4//z/+f/8//r//P/7//z//P/9//3//f/9//3//v/9/////v8AAP7/AAD+/wAA/v8BAP7/AQD//wIA//8DAP//AwAAAAMAAAAEAAEABQABAAYAAQAGAAEABgACAAYAAwAHAAQABgAEAAcABAAHAAUABgAEAAYABQAHAAYABwAGAAcABgAHAAYABwAHAAcABwAIAAcACAAHAAcABwAHAAcACAAHAAcABwAHAAcACAAHAAgABgAIAAYACAAFAAgABQAIAAUACAAEAAgAAwAIAAMACAADAAgAAwAJAAIACQACAAkAAQAJAAAACQAAAAkAAAAJAP//CQD+/wkA/v8JAP3/CQD8/wgA/P8IAPz/BwD8/wcA+/8HAPv/BwD7/wYA+/8GAPr/BQD6/wQA+v8EAPv/BAD7/wMA+/8CAPv/AQD7/wAA/P////z////8//7//f/9//3//P/+//z//v/6//7/+f/+//j////4/wAA9/8BAPX/AQD1/wIA9P8DAPP/AwDy/wMA8v8EAPH/BADw/wQA8P8EAPD/BADv/wQA7/8FAO//BQDv/wUA7/8FAO//BgDv/wYA7/8GAPD/BgDw/wYA8P8GAPH/BQDx/wUA8v8FAPL/BQDz/wUA9P8EAPT/BAD2/wMA9/8DAPn/AgD6/wIA+/8BAPv/AQD9/wEA/v8AAP//AAAAAP//AQD//wIA//8DAP//BAD//wUA/v8GAP7/BwD+/wgA/v8IAP7/CQD+/wkA/f8LAP3/CwD9/wsA/v8MAP7/DAD+/wwA/v8NAP3/DQD9/w4A/v8NAP7/DgD+/w4A/v8OAP7/DQD+/w4A/v8NAP7/DQD9/w0A/f8OAP7/DQD+/wwA/v8MAP7/DAD+/wwA/v8MAP7/CwD+/woA/v8KAP3/CgD9/wkA/f8JAP7/CQD9/wgA/f8IAP3/CAD8/wcA/P8HAPz/BwD9/wYA/P8GAPz/BgD8/wUA/P8EAPv/BAD7/wMA+/8DAPv/AwD7/wIA+/8CAPv/AgD7/wAA+/////r////7/wAA/P////z//f/7//z//P/8//z/+//8//r//P/5//z/+f/9//j//f/4//3/9v/+//X////0////9P////P/AADz/wAA8v8AAPL/AQDx/wEA8P8BAPD/AgDv/wMA7v8DAO3/AwDt/wQA7f8FAOz/BQDs/wYA7f8GAOz/BgDs/wcA7P8IAOz/CADt/wgA7f8JAO3/CADu/wkA7v8JAO//CgDv/woA8P8KAPH/CgDy/woA8/8KAPT/CgD1/woA9/8KAPj/CgD5/wkA+v8KAPz/CgD9/woA/v8KAP//CgAAAAkAAgAJAAMACQAFAAkABgAJAAcACQAIAAgACgAIAAsABwALAAcADAAGAA4ABgAPAAYADwAFABAABAASAAQAEgAEABMABAATAAMAFAACABQAAgAUAAIAFAABABQAAAAUAAAAFQD//xUA//8UAP7/FAD9/xQA/f8UAPz/EwD7/xMA+f8SAPn/EgD5/xEA+P8QAPf/EAD3/xAA9/8OAPb/DQD1/w0A9f8MAPT/DADz/wwA8/8LAPL/CgDy/woA8v8JAPH/CADx/wgA8f8HAPH/BQDw/wUA8P8FAPD/BADw/wMA8P8DAPH/AgDx/wEA8f8AAPH////x////8v////L//v/z//z/8//8//P/+//0//r/9f/5//b/+f/2//j/9v/3//f/9//4//b/+f/2//r/9f/7//X/+//0//z/9P/9//P//v/y////8v8BAPH/AwDx/wQA8P8EAPD/BQDv/wcA7/8HAPD/BwDv/wgA7/8KAO//CgDv/wsA8P8MAPD/DADw/w0A8P8NAPD/DgDx/w8A8v8PAPP/DwDz/xAA9P8QAPT/DwD1/w8A9v8PAPb/EAD3/xAA+P8QAPj/DwD5/w8A+v8OAPv/DgD8/w4A/v8OAP//DQAAAA0AAQANAAIADAADAAsABAALAAQACwAFAAoABgAJAAcACAAHAAgACAAHAAkABwAKAAYACwAGAAsABQAMAAUADQAFAA0ABAANAAQADQADAA0AAgANAAEADQABAA0AAQANAAAADQD//w0A//8MAP7/DAD9/wwA/f8LAPz/CgD7/woA+/8JAPr/CAD5/wcA+f8HAPj/BgD4/wUA9/8EAPb/BAD2/wMA9f8DAPT/AQD0/wEA9P8AAPP/AADy////8v////L//v/y//3/8v/9//P//f/z//z/8v/7//L/+//y//v/8v/6//L/+v/z//r/8//5//L/+f/z//n/9P/5//T/+f/0//n/9f/5//b/+f/2//n/9v/5//f/+f/4//n/+P/4//n/+f/6//n/+//5//v/+f/8//n//f/5//7/+f/+//n//v/5/wAA+f8BAPn/AgD5/wMA+f8DAPr/BAD6/wYA+/8GAPv/BgD7/wcA/P8JAPz/CQD7/wkA/P8KAP3/CwD9/wwA/f8MAP7/DQD+/w4A//8PAP//DwD//w8AAAAQAAAAEAABABAAAgAQAAIAEAADABAAAwAQAAQAEAAFABAABgAQAAYADwAHAA8ABwAOAAgADgAJAA4ACQAOAAsADQALAAwADAALAAwACwANAAsADgAKAA4ACQAPAAgADwAHABAABgAQAAUAEQAFABEABAARAAMAEQACABEAAQARAAAAEQD//xAA//8QAP7/EAD9/xAA/P8PAPv/DwD6/w4A+f8OAPn/DQD4/wwA9/8LAPb/CgD2/wkA9f8IAPT/BwD0/wYA8/8FAPP/AwDz/wIA8/8BAPP/AADy//7/8v/9//L//P/y//r/8v/5//L/+P/y//f/8v/2//L/9f/y//T/8f/z//L/8v/y//H/8//x//P/8P/z/+//9P/v//T/7v/0/+7/9P/u//b/7f/3/+3/9//t//f/7f/4/+3/+P/u//n/7v/5/+7/+v/u//r/7//7/+//+//v//z/8P/8//H//f/x//3/8f/+//L//v/z//7/8/////T////1/wAA9v8AAPb/AQD3/wEA+P8CAPn/AwD5/wMA+v8EAPv/BAD7/wUA/P8FAP3/BQD+/wYA/v8GAP//BwAAAAcAAAAHAAEACAACAAgAAgAJAAMACQADAAoAAwAKAAQACgAEAAoABQALAAUACwAGAAwABgAMAAcADAAHAAwACAAMAAgADAAJAA0ACQANAAkADAAJAAwACgAMAAsADAAMAAwACwAMAAsADAALAAsADAALAA0ACwANAAsADQALAA0ACwANAAoADQAKAA0ACgAOAAkADgAJAA4ACQAOAAgADQAIAA0ABwAOAAcADgAGAA0ABgANAAUADQAEAAwABAALAAMACgACAAoAAQAJAAEACAAAAAcA//8GAP//BQD//wUA/v8DAP3/AgD9/wIA/P8CAPz/AQD7////+v/+//n//v/5//3/+f/7//j/+v/3//n/9//4//f/+P/3//f/9v/2//b/9f/2//X/9f/0//X/9P/1//P/9f/z//X/8v/1//P/9f/z//X/8//1//P/9v/z//b/8//2//L/9v/z//b/8//3//T/9//0//f/9P/3//X/9//1//f/9v/3//f/+P/3//j/+P/4//n/+f/6//n/+v/5//z/+v/9//v//v/7//7/+/////v/AAD7/wEA+/8CAPz/AwD8/wMA/P8EAP3/BQD+/wUA/v8GAP7/BgD//wcAAAAHAAEACAABAAgAAQAJAAEACQABAAkAAgAJAAIACQADAAkAAwAKAAQACgAEAAoABQAKAAUACgAFAAkABgAJAAYACQAGAAkABwAJAAcACAAHAAgABwAIAAgACAAJAAgACAAIAAkABwAJAAcACQAHAAgABgAIAAYACQAGAAkABgAJAAUACQAFAAkABQAIAAUACAAFAAgABQAIAAQABwAEAAcABAAHAAQABgADAAYAAwAFAAMABQADAAUAAgAEAAEABAABAAMAAAADAAAAAgD//wEA/v8BAP7/AAD9/wAA/f////z////7//7/+v/9//n//P/5//v/+P/7//j/+v/3//r/9v/6//X/+v/0//n/8//5//L/+f/y//n/8f/4//D/+P/w//j/8P/4/+//9//v//f/7v/3/+7/9//t//j/7v/4/+3/+f/t//n/7P/6/+3/+v/t//v/7f/7/+7/+//u//z/7//9/+///f/w//7/8f/+//L////z/wAA9P8BAPX/AgD2/wIA9/8CAPj/AwD6/wQA+/8FAPz/BQD+/wUAAAAGAAEABgACAAcAAwAHAAUABwAGAAgACAAIAAkACAAKAAkADAAJAA0ACQAOAAkADwAKABAACQARAAkAEgAJABMACQAUAAgAFQAIABYACAAWAAgAFgAHABcABwAXAAcAGAAHABkABwAZAAYAGAAGABgABQAYAAUAFwAEABcABAAXAAMAFgADABUAAgAVAAEAFAABABMAAAASAAAAEgD//xEA//8RAP//EAD+/w8A/f8PAP3/DgD8/w0A/P8MAPz/CwD8/woA+/8IAPv/BwD6/wcA+v8GAPr/BAD5/wIA+f8CAPn/AQD5/wAA+P////j//v/3//3/9//8//f/+//3//r/9//5//f/+P/2//b/9v/2//f/9f/3//T/9v/z//b/8v/2//H/9v/x//b/8P/2/+//9v/t//b/7P/2/+z/9v/s//f/6//2/+r/9v/p//f/6f/4/+n/9//o//f/6P/3/+j/+P/o//n/6P/5/+j/+f/o//r/5//8/+f//P/n//3/5//9/+j////o////6P8AAOn/AQDq/wEA6v8CAOv/AwDt/wQA7v8FAO//BgDv/wcA8f8IAPL/CQDz/woA9f8LAPb/CwD3/wwA+P8OAPr/DgD8/w8A/v8PAP7/EAAAABAAAgARAAQAEQAFABEABwARAAkAEQAKABEADAARAA0AEQAOABEAEAARABIAEAATABAAFAAQABUADwAXAA4AGAANABgADQAZAA0AGQALABkACgAaAAoAGgAJABoACAAaAAcAGgAFABoABAAaAAMAGgACABoAAQAaAAAAGgD//xkA/v8ZAP3/GAD8/xcA+/8WAPr/FQD5/xQA+P8TAPj/EgD4/xEA9/8QAPb/DwD2/w4A9f8MAPT/DAD0/wsA9P8KAPT/CQD0/wgA8/8HAPP/BQDz/wQA8/8EAPP/AwDz/wEA8/8AAPP/AAD0//7/9P/9//T//P/1//v/9f/6//X/+f/1//j/9f/3//X/9//2//b/9v/1//f/9P/3//T/9//0//f/8//3//L/+P/x//j/8f/4//D/+P/v//j/7//5/+7/+f/t//r/7P/7/+z/+//s//z/7P/7/+v//P/q//3/6v/9/+r//v/r//7/6v/+/+r////q////6/8AAOv/AQDs/wEA7P8CAOz/AwDs/wQA7P8FAO3/BQDt/wYA7v8HAO//CADv/wkA8P8JAPH/CgDy/woA8/8KAPT/CwD1/wwA9v8NAPf/DQD5/w0A+v8NAPz/DgD9/w4A/v8PAP//DwABAA8AAgAPAAQADwAFAA8ABwAQAAgADwAJAA8ACgAOAAsADgAMAA4ADQAOAA4ADQAPAA0AEAAMABAACwARAAsAEwAKABMACQAUAAkAFQAIABUABwAVAAUAFgAFABUABAAVAAMAFQACABUAAQAVAAAAFAAAABQA//8UAP7/EwD9/xMA/P8SAPv/EQD6/xEA+f8QAPj/DwD4/w4A9/8NAPf/DAD2/wsA9f8LAPX/CgD1/wkA9P8IAPT/BwD0/wYA8/8FAPT/BAD0/wMA9P8CAPT/AQD0/wAA9P////P////0//7/9P/9//T//P/0//v/9f/7//X/+v/1//r/9v/6//b/+f/2//n/9//4//f/9//4//f/+P/3//j/9v/5//b/+f/1//r/9f/5//T/+v/0//r/9P/6//T/+//0//v/9P/7//T/+//0//z/9P/8//P//P/z//3/8v/9//P//f/0//3/9P/+//T//v/0//7/9P/+//T////0////9P8AAPT/AQD1/wEA9v8CAPb/AgD2/wMA9/8EAPj/BAD4/wUA+f8FAPr/BgD7/wYA+/8HAPv/BwD8/wkA/f8JAP7/CgD//wsAAAALAAAADAABAAwAAgANAAMADgAEAA4ABQAPAAYADwAHABAABwAQAAgAEAAJABAACgAQAAoAEAALABAACwAQAAwAEAANABAADQAPAA4ADwAOAA4ADwAOAA8ADQAPAA0ADwAMABAACwAPAAsADwAJAA8ACAAOAAcADwAHAA4ABQANAAQADQAEAA0AAgAMAAAACwAAAAsA//8KAP3/CQD8/wgA+/8HAPn/BwD5/wYA+P8FAPb/BAD1/wMA9f8CAPT/AQDz/wAA8v////L//v/x//7/8P/9//D//P/w//v/8P/6/+//+v/v//n/7//4/+//+P/v//f/7//3/+//9v/w//b/8P/1//D/9f/w//T/8f/0//H/9P/y//T/8//z//P/8//z//P/9P/z//X/8//2//P/9//z//f/8//4//P/+f/z//r/8//6//T/+v/0//v/9P/8//T//f/0//7/9P/+//T//v/1////9f8AAPX/AAD2/wEA9v8CAPf/AwD3/wMA+P8EAPj/BQD5/wUA+f8FAPn/BgD6/wYA+v8HAPv/BwD7/wcA/P8IAPz/CQD9/woA/v8KAAAACgABAAsAAQAMAAIADAADAAwAAwANAAMADQAEAA0ABQAOAAYADgAHAA4ACAAOAAgADgAJAA8ACgAPAAsADwALAA8ADAAPAA0ADwANAA8ADgAPAA8ADgAPAA4AEAAPABAADgARAA0AEQANABIADQASAAwAEQALABEACgARAAoAEQAJABAACAAQAAcAEQAGABEABQAQAAQADwADAA8AAgAOAAEADgAAAA0A//8MAP7/CwD9/woA/P8JAPr/CAD6/wcA+f8GAPj/BQD2/wQA9v8DAPX/AgD0/wEA9P8AAPP////x//7/8f/9//L//P/x//v/8P/6/+//+v/v//n/7//4/+//9//v//f/7//2/+//9f/w//X/8P/1//H/9P/x//T/8v/0//L/9P/z//T/8//0//P/9P/0//T/9f/z//b/9P/2//T/9//0//j/9P/5//X/+v/2//v/9v/7//b//P/3//3/9//+//f//v/3////+P8AAPj/AQD4/wIA+P8DAPj/AwD5/wQA+f8EAPn/BAD6/wUA+v8FAPv/BQD8/wYA/P8HAPz/BwD8/wcA/f8IAP3/CAD9/wgA/f8IAP3/CAD+/wgA/v8IAP7/CQD+/wkA//8IAP7/CAD//wgAAAAIAAAACAAAAAcAAQAHAAEABwABAAcAAQAHAAIABwADAAcAAwAHAAMABgADAAYABAAHAAUABgAFAAYABgAGAAYABgAHAAYABwAFAAgABQAIAAQACQAEAAkABAAKAAQACgAEAAoABAALAAMADAADAAwAAgALAAEADAABAAwAAQALAAEADAAAAAwAAAALAAAACgD//woA//8KAP7/CQD+/wgA/v8IAP3/BwD8/wcA+/8GAPv/BQD6/wQA+v8DAPr/AgD5/wEA+f8AAPn/AAD4////+P/+//f//f/3//z/9//7//f/+v/3//n/9//4//j/9//3//b/+P/1//j/9P/4//T/+P/0//n/8//5//P/+f/z//r/8//7//P/+//z//z/8//9//T//f/z//7/8/////P/AAD0/wEA9P8CAPT/AwD0/wMA9f8EAPb/BQD3/wYA+P8GAPn/BwD5/wgA+v8JAPv/CQD8/woA/f8KAP7/CgD//wsA//8LAAAADAABAA0AAgAMAAMADAADAAwABAANAAUADQAFAAwABgAMAAcADAAIAAwACAALAAkACwAJAAoACQAKAAoACQAKAAgACwAHAAsABwALAAYACwAFAAsABAALAAMACwACAAsAAgAKAAEACwAAAAsA//8LAP//CgD+/woA/f8KAPz/CgD8/woA+/8JAPr/CQD6/wkA+f8IAPn/CAD4/wgA+P8IAPj/CAD3/wgA9v8IAPb/BwD2/wcA9v8HAPb/BwD2/wcA9f8GAPX/BgD1/wYA9f8GAPb/BQD1/wUA9f8FAPX/BQD1/wQA9f8EAPX/BAD1/wMA9f8CAPX/AgD2/wEA9f8BAPX/AAD1////9v////b//v/2//3/9v/8//b/+//2//r/9//6//f/+f/3//j/9//3//f/9//4//b/+P/1//n/9P/5//P/+P/z//n/8v/6//H/+v/w//v/8P/7//D/+//v//z/7//9/+7//v/u//7/7v///+7/AADt/wEA7v8CAO3/AwDt/wQA7v8FAO7/BgDu/wYA7/8HAPD/CQDw/wkA8P8KAPL/CwDz/wwA8/8NAPT/DgD0/w4A9v8QAPf/EQD4/xEA+f8RAPr/EQD8/xIA/f8SAP7/EwD//xMAAQAUAAIAFAADABQABAAUAAUAFAAGABQABwATAAgAEwAJABMACQASAAsAEgALABEADAAQAA0AEAANAA8ADgAOAA4ADgAOAA0ADgAMAA8ACwAPAAoADwAJAA8ACAAOAAYADgAFAA4ABAAOAAMADgACAA4AAQAOAAAADQD+/w0A/f8NAPz/DAD7/wwA+v8MAPn/CwD5/woA+P8KAPf/CgD2/wkA9v8JAPX/CQD0/wkA9P8IAPT/CADz/wcA8/8HAPL/BwDy/wYA8f8GAPL/BgDy/wUA8v8FAPH/BQDx/wUA8v8FAPL/BQDx/wQA8f8EAPH/BADy/wMA8v8DAPL/AwDz/wMA8/8CAPP/AgD0/wIA9P8BAPT/AQD1/wEA9f8AAPX/AAD1////9v/+//X//v/2//7/9//9//j//P/4//z/+P/8//j//P/5//r/+f/6//r/+f/7//n/+//4//v/9//8//f//P/2//z/9v/8//X//f/1//3/9f/+//T////z////8/////P/AADz/wAA8/8BAPP/AgD0/wMA8/8DAPP/BQDz/wUA8/8GAPP/BgDz/wYA9P8HAPT/CAD0/wgA9f8JAPb/CgD2/woA9/8LAPj/CwD5/wwA+f8MAPr/DQD7/w0A/P8NAPz/DgD9/w4A/f8PAP7/DwD+/w8A//8PAAAADwABABAAAgAQAAIADwADAA8ABAAPAAQADwAEAA4ABQANAAYADQAGAAwABwAMAAcACwAHAAsABwAKAAcACQAHAAgABwAHAAcABgAHAAYABwAFAAcABAAHAAMABwACAAcAAQAGAAAABgD//wYA/v8GAP3/BgD8/wUA+/8FAPv/BQD6/wUA+f8EAPj/BAD3/wQA9v8EAPb/BAD1/wUA9f8EAPT/AwD0/wMA8/8DAPP/AwDz/wMA8/8DAPP/AwDz/wIA8v8CAPL/AgDy/wIA8v8CAPL/AgDy/wIA8v8CAPP/AwDz/wMA8/8CAPT/AgD1/wIA9v8BAPb/AQD2/wIA9/8CAPf/AQD3/wEA+P8BAPn/AQD5/wAA+v8AAPv/AAD7/wAA/P8AAPz/AAD9/wAA/v///////v////7////+/wAA/v8BAP3/AgD9/wIA/f8DAP3/BAD8/wQA/P8FAPz/BgD8/wcA+/8HAPv/CAD7/wgA/P8IAPz/CQD8/woA/P8KAPz/CwD9/wsA/f8LAP3/DAD9/wwA/v8NAP7/DQD//w0A//8OAAAADwAAAA8AAAAPAAEADwABAA8AAQAPAAIADwADAA8AAwAPAAQADwAEAA8ABQAOAAUADQAFAA0ABQANAAUADQAFAAwABgALAAYACgAFAAoABQAJAAUACAAEAAcABAAGAAQABgAEAAUAAwAEAAMAAgADAAIAAgAAAAEA//8BAP7/AQD9/wAA+/////v////6//7/+f/+//j//f/3//z/9v/8//X/+//0//v/8//6//L/+f/x//n/8P/6//D/+f/v//j/7//4/+7/+P/u//j/7v/4/+3/+P/t//j/7f/4/+3/+P/t//j/7P/4/+3/+f/t//n/7v/5/+7/+f/u//r/7//6/+//+v/v//v/8P/7//L/+//y//z/8v/8//P//P/0//3/9f/+//b//v/2//7/9/////n////6////+v8AAPr/AAD8/wAA/f8AAP7/AAD+/wAA//8AAAEAAQACAAEAAgAAAAMAAQAEAAEABAABAAYAAQAHAAEABwABAAcAAQAJAAEACQACAAkAAgAKAAMACgADAAoAAwALAAMADAADAAwAAwAMAAMADQADAA4AAwAOAAQADgAEAA8ABAAQAAQAEAAFABAABQAQAAYAEAAGABAABwAQAAcAEQAHABEACAARAAgAEQAJABEACQARAAkAEQAJABEACgARAAoAEQAKABAACgAQAAsADwALAA8ACgAOAAsADgALAA0ACwAMAAsACwAKAAsACgAKAAsACgAKAAgACQAHAAkABgAJAAUACQAEAAgAAwAIAAIABwABAAUAAAAEAP//AwD+/wMA/f8CAPz/AQD7/wAA+v////r//v/4//7/9//9//b//P/1//v/8//7//L/+v/y//n/8f/4//D/9//v//b/7//2/+//9f/t//T/7f/0/+3/9P/t//T/7f/0/+3/8//t//P/7f/z/+3/8//t//T/7v/0/+7/8//u//T/7//0/+//9P/w//X/8P/1//H/9v/x//b/8v/3//P/9//0//f/9f/4//b/+f/3//n/+P/5//n/+v/6//v/+//7//z/+//+//z////8/////P8AAP3/AQD+/wIA/v8DAP7/BAD//wQA//8FAAAABwAAAAcAAAAHAAEACAABAAkAAQAJAAEACgABAAsAAQALAAIADAACAAwAAgAMAAIADAACAAwAAgAMAAIADQACAA0AAgANAAIADQACAAwAAwAMAAMADAADAAwABAAMAAQADAAEAAwABAALAAQACwAEAAsABAALAAUACwAFAAoABQAJAAUACQAFAAgABgAIAAcABwAHAAcABwAGAAcABgAHAAUACAAEAAgABAAIAAQACAADAAkAAgAJAAIACQABAAoAAQAKAAAACQD//wkA//8JAP//CQD+/wgA/v8IAP3/BwD9/wcA/P8HAPz/BgD6/wYA+f8GAPn/BQD5/wQA+P8DAPf/AwD3/wMA9/8CAPb/AQD2/wAA9f////X//v/1//3/9f/8//X/+//1//r/9P/5//T/+f/0//j/9P/4//T/9//0//f/8//2//P/9f/0//X/9P/0//T/9P/1//T/9f/0//X/9P/2//T/9//0//j/9P/4//X/+f/1//r/9f/7//b//P/2//3/9//+//f////4/wAA+P8BAPn/AgD6/wMA+/8EAPv/BAD9/wUA/v8FAP7/BgD+/wcA//8IAAEACQABAAoAAgALAAIADAADAAwAAwANAAQADQAFAA4ABQAOAAYADgAGAA8ABgAPAAcADwAHAA8ACAAOAAgADgAHAA4ACAAOAAgADQAJAA0ACAANAAgADAAIAAsACAALAAkACgAJAAoACAAJAAcACQAIAAkACAAIAAcABwAHAAcABwAGAAYABAAGAAQABwADAAYAAgAGAAEABgABAAYAAAAGAP//BQD//wUA/v8GAP3/BgD8/wUA/P8FAPz/BgD8/wUA+/8EAPr/BAD6/wQA+f8EAPn/BAD5/wQA+P8EAPj/BAD3/wQA+P8DAPf/AgD3/wMA9/8CAPf/AgD2/wIA9v8BAPb/AQD2/wAA9v8AAPb/AAD2////9v/+//X//f/1//z/9f/7//X/+//1//r/9f/5//X/+f/1//j/9f/3//X/9//1//b/9f/1//X/9f/1//T/9f/z//X/8//1//L/9f/y//b/8f/2//H/9v/w//b/8P/3//D/+P/w//j/8P/4//H/+f/x//r/8f/6//H/+v/y//v/8v/8//L//P/z//3/8//+//T////1/wAA9/8AAPf/AQD3/wIA+P8DAPn/BAD5/wYA+v8HAPv/CAD8/wkA/f8KAP7/CwD+/wwAAAANAAEADwABAA8AAgAPAAMAEAAEABIABQATAAYAEwAGABQABwAUAAcAFQAIABYACAAWAAgAFgAJABYACQAWAAkAFgAJABYACgAWAAoAFgAKABYACgAVAAoAFQAKABQACgATAAoAEgALABEACgAQAAkADwAJAA4ACgANAAoADAAJAAsACgAKAAkACAAIAAcACQAGAAkABAAIAAIACAABAAgAAAAIAP//CAD9/wgA/P8IAPv/CAD5/wgA9/8IAPb/CAD1/wgA9f8IAPP/CADy/wgA8f8IAPD/CADv/wgA7v8IAO7/BwDt/wgA7P8IAOz/BwDs/wcA6/8HAOv/BwDr/wcA6/8HAOv/BwDr/wYA6/8FAOr/BQDr/wUA6/8FAOz/BQDs/wQA6/8DAOz/AwDs/wIA7f8BAO7/AADu////7//+//D//f/x//z/8v/7//L/+//z//n/8//5//T/+f/1//j/9v/3//b/9v/3//X/+P/1//n/9P/5//P/+v/z//v/8//7//P//P/z//7/8v/+//H////y////8v8BAPL/AgDz/wMA8/8EAPP/BADz/wUA9P8GAPT/BwD0/wkA9f8JAPX/CgD2/woA9/8LAPj/DAD5/w0A+f8NAPn/DwD7/w8A/P8QAPz/EQD8/xIA/f8SAP7/EwD//xQA//8VAAAAFQABABYAAQAXAAIAFgADABYAAwAWAAQAFwAEABcABAAWAAQAFgAFABcABQAWAAUAFgAFABUABQAVAAUAFAAEABMABAATAAQAEgAEABIABAARAAMADwADAA4AAwAOAAMADQACAAwAAgALAAIACgACAAgAAgAGAAEABQABAAQAAAACAAAAAAAAAP//AAD9/////P////v////6////+f////f////2////9f8AAPT/AADz/wEA8v8AAPH/AADw/wEA7/8BAO//AQDu/wEA7f8CAOz/AgDs/wMA7P8DAOv/AwDr/wMA6/8EAOz/BADs/wQA6/8EAOz/BADs/wQA7f8EAO7/BADu/wQA7v8EAO//AwDw/wMA8P8DAPH/AwDx/wMA8v8CAPP/AQD0/wEA9f8BAPX/AAD2////9/////j//v/5//7/+v/+//v//f/8//3//P/8//3//P/+//3////8/////P8AAPz/AQD8/wEA/P8CAPz/AgD8/wIA/P8DAPz/BAD8/wQA/f8FAPz/BQD9/wYA/f8GAP7/BgD//wcA//8IAAAACAAAAAgAAQAJAAIACQACAAkAAwAKAAMACgAEAAoABQALAAYACwAHAAsACAALAAgACwAIAAsACQALAAoACwAJAAwACgAMAAoACwALAAsACwAMAAsADAALAAwACwALAAoACwAKAAsACgAKAAoACgAJAAkACQAJAAgACAAIAAgACAAIAAcABwAGAAYABQAGAAUABQAEAAUAAwAEAAIAAwABAAMAAAACAP//AgD+/wEA/f8AAPz/AAD8//7/+//9//r//f/5//3/+f/8//j/+//4//r/9//6//f/+f/3//j/9v/3//X/9//1//b/9v/2//X/9f/1//X/9f/1//b/9f/2//T/9v/0//f/9P/3//T/9//0//j/9P/4//T/+f/0//n/9P/6//T/+v/0//r/9f/7//X/+//2//z/9v/8//f//f/3//3/+P/9//j//f/5//3/+v/+//v//v/8//7//P/+//3//v////7/AAD+/wAA/v8AAP7/AQD+/wIA/v8DAP3/AwD9/wQA/f8EAP3/BQD8/wUA+/8GAPz/BwD7/wcA+/8IAPz/CAD8/wkA/P8IAPz/CAD7/wkA+/8KAPv/CgD7/woA/P8LAPz/CwD8/wsA/f8LAP3/CwD9/woA/v8KAP//CgD//woAAAAKAAEACgABAAkAAgAJAAMACQAEAAkABAAJAAUACQAGAAgABwAIAAgACAAIAAgACQAHAAoABwALAAYACwAGAAwABgAMAAUADQAFAA4ABAAOAAQADgADAA4AAwAPAAIADwACAA4AAgAPAAEADwABAA4AAQANAAEADgAAAA0A//8LAP7/CwD+/wsA/f8KAPz/CAD8/wcA+/8HAPv/BgD6/wUA+v8EAPn/AwD5/wIA+f8BAPj/AQD3////9v/+//b//v/2//3/9v/8//X/+//1//v/9f/7//T/+v/0//r/9P/5//T/+f/z//n/8//5//P/+P/0//j/8//4//P/+P/z//j/9P/5//T/+f/0//n/9P/6//X/+v/2//r/9v/7//f/+//4//v/+P/8//j//f/5//3/+v/9//r//f/7//7//P/+//z////9/////f////7/AAD//wAAAAAAAAAAAAABAAAAAgAAAAMAAAADAP//BAD//wUA//8GAP//BgD+/wcA/v8HAP7/BwD9/wcA/f8IAP3/CQD9/wkA/P8IAPz/CQD8/wkA+/8JAPv/CQD7/woA+v8KAPr/CgD6/woA+v8KAPr/CgD6/woA+v8JAPv/CgD7/woA+/8JAPv/CQD7/wkA+/8IAPz/CAD8/wcA/f8HAP7/BwD+/wcA//8GAAAABgABAAUAAgAFAAIABQADAAUAAwAFAAQABAAEAAQABQADAAYAAwAGAAMABwADAAcAAwAHAAIACAACAAgAAQAIAAAACAAAAAgA//8IAP//CAD+/wgA/v8IAP7/BwD9/wcA/f8HAP3/BwD9/wYA/P8GAPz/BgD8/wUA+/8EAPv/AwD7/wIA+/8CAPr/AgD6/wEA+v8AAPn/AAD5////+f////j//v/4//7/+P/+//n//f/5//z/+P/8//j//P/5//v/+f/7//n/+//5//v/+f/7//r/+//6//z/+v/8//v/+//7//v//P/8//z//P/9//3//f/+//7//v/+////////////AAAAAAAAAAABAAEAAgACAAIAAwACAAMAAwAEAAQABAAEAAUABAAFAAQABgAFAAYABQAGAAUABwAFAAcABQAHAAUACAAEAAgABQAJAAUACQAEAAkABAAJAAQACQADAAkAAwAJAAMACQADAAkAAgAJAAEACQAAAAgAAAAIAAAACAD//wcA/v8GAP7/BgD9/wYA/f8FAP3/BAD8/wMA+/8CAPv/AgD8/wEA+/8AAPv/AAD8////+/////v//v/7//7/+//9//v//f/7//z//P/7//v/+//8//v//P/6//3/+f/9//n//f/4//7/+P/+//f////3////9v8AAPb/AAD2/wAA9v8BAPb/AgD2/wIA9f8CAPX/AgD2/wMA9f8DAPX/AwD2/wMA9v8EAPX/BAD1/wQA9v8EAPf/BAD2/wQA9v8EAPb/AwD3/wMA9v8DAPb/AwD3/wMA9/8CAPf/AgD4/wEA+f8BAPn/AAD5////+v////r//v/7//7/+//9//z//f/9//z//f/8//3/+//+//v////6////+v8AAPr/AQD6/wIA+v8CAPr/AgD5/wMA+f8EAPr/BAD6/wQA+v8FAPr/BgD6/wcA+v8IAPv/CQD7/woA/P8KAPz/CwD9/wsA/f8MAP7/DAD//wwAAAANAAAADgABAA4AAQAOAAIADwADAA8ABAAQAAUAEAAFABAABQAQAAYAEQAGABEABwASAAcAEQAHABEABwARAAcAEQAHABEACAAQAAgAEAAIABAACAAPAAgADwAIAA4ABwANAAYADAAGAAwABgALAAYACgAFAAoABAAJAAQACAAEAAcABAAGAAMABQADAAQAAgADAAEAAgABAAEAAQAAAAEA//8BAP7/AAD8/wAA+/8AAPv/AAD6/wAA+P/+//f//v/3////9v////X//v/0//7/8/////L////y////8v////H////w////8P////D////v/wAA7v8AAO//AADu/wAA7f8AAO7/AADu/wEA7v8BAO7/AQDu/wEA7v8BAO7/AQDv/wEA7/8BAO//AQDv/wEA8P8BAPH/AQDx/wEA8f8AAPL/AADy/wEA8/8AAPT/AAD0////9f////b////2////9/////f//v/4////+f/+//n//v/6//3/+//8//z//P/9//z//v/7//7/+/////v/AQD7/wIA+v8CAPr/AgD6/wMA+v8EAPr/BQD6/wYA+v8GAPr/BwD6/wgA+v8JAPr/CQD6/woA+/8LAPv/CwD8/wsA/P8MAPz/DQD9/w0A/v8OAP7/DgD//w8A//8QAP//EAAAABEAAAARAAAAEQABABEAAQARAAEAEQABABEAAgASAAIAEQACABEAAgARAAIAEQACABAAAgAQAAIAEAACAA8AAgAPAAIADgADAA4AAwANAAIADQACAAwAAgAMAAIACwACAAoAAgAJAAEACAACAAcAAgAGAAEABQAAAAQAAAADAP//AQD//wAAAAAAAAAA/v////3////8/////P////v//v/6//7/+f/+//n//f/4//3/9//9//b//f/2//3/9f/9//X//v/0//7/8//+//L//v/y//7/8v/+//L////x////8f8AAPL////x////8f8AAPH/AADx/wEA8v8BAPL/AgDy/wIA8v8CAPP/AgD0/wIA9P8DAPT/AwD1/wQA9v8DAPb/AwD2/wQA9/8FAPj/BAD4/wQA+P8EAPn/BAD6/wUA+/8EAPz/BAD8/wQA/f8EAP7/BAD+/wQA/v8EAP//BAAAAAMAAAADAAAAAwABAAMAAQADAAIAAgADAAIAAwACAAQAAgAEAAIABAACAAUAAgAFAAIABQACAAYAAgAGAAIABwABAAcAAQAHAAIABwACAAcAAgAHAAIACAACAAgAAgAIAAIACAACAAgAAwAIAAMACAACAAgAAgAJAAMACAADAAgAAwAJAAMACQADAAgAAwAIAAMACQADAAkAAgAJAAIACQACAAkAAgAJAAIACQACAAkAAgAJAAIACAACAAcAAgAHAAIABgABAAYAAQAGAAAABQAAAAQAAAAEAP//AwD//wIA//8CAP7/AgD9/wEA/f8AAP3////8/////P////v//v/7//3/+//8//v/+//7//v/+v/6//r/+f/6//j/+v/4//r/9//5//f/+f/2//n/9f/5//X/+P/1//j/9f/5//T/+P/0//j/9P/4//X/+P/1//f/9P/3//T/+P/1//j/9f/4//X/+f/1//n/9f/5//X/+f/1//n/9v/6//f/+v/3//n/9//6//j/+v/6//v/+v/7//r//P/7//z//P/9//z//f/9//3//v/9/////v////7/AAD+/wEA//8BAP//AgD//wMA//8DAP//AwAAAAQAAQAFAAEABQABAAUAAQAGAAEABwABAAgAAQAIAAEACAABAAkAAQAJAAIACQACAAkAAgAJAAIACQACAAoAAgAKAAIACgACAAoAAgAKAAIACgACAAoAAwAKAAMACgADAAoAAwAKAAMACgAEAAoABAAKAAQACgAFAAoABQAKAAUACgAFAAkABQAIAAYACAAGAAgABgAIAAcACAAIAAgACAAHAAgABwAIAAcACAAHAAgABwAIAAYACAAFAAkABQAKAAUACQAFAAkABAAJAAMACQADAAoAAgAKAAIACQABAAkAAAAJAP//CQD//wkA/v8JAP7/CQD9/wkA/f8IAPz/CAD6/wcA+v8HAPn/BgD4/wYA+P8FAPf/BQD2/wQA9f8EAPX/AwD0/wIA8/8CAPP/AQDz/wEA8v8AAPH/AADw/wAA8P///+/////v//7/7//9/+7//f/u//3/7v/8/+7//P/v//v/8P/7//D/+v/w//r/8P/6//H/+v/y//r/8v/5//P/+f/z//n/9P/5//T/+f/1//j/9v/5//f/+f/4//n/+f/5//r/+P/7//j//P/4//3/+f////n/AAD5/wEA+P8CAPj/AwD5/wQA+v8FAPn/BgD5/wcA+f8IAPr/CQD5/wkA+v8KAPr/CwD6/wwA+v8MAPr/DQD6/w0A+/8OAPr/DgD6/w4A+/8PAPv/DwD7/xAA+/8PAPz/EAD8/xAA/P8QAPz/DwD8/w8A/P8PAP3/DwD9/w4A/f8OAP3/DgD+/w0A/v8NAP7/DAD+/wwA/v8LAP7/CwD//woA/v8KAP7/CQD+/wkA//8HAAAABwD//wcA//8GAAAABQAAAAQAAAAEAP//AwAAAAIAAAACAAEAAQABAAEAAQABAAEAAAACAAAAAgD//wIA//8CAP//AgD+/wIA/f8DAP3/BAD9/wQA/f8DAPz/AwD8/wQA/P8EAPv/BAD7/wQA+v8EAPr/BAD5/wQA+f8FAPn/BQD4/wUA+P8FAPj/BQD4/wYA+P8GAPf/BgD3/wYA9/8GAPf/BgD3/wYA9v8HAPb/BwD2/wYA9v8GAPb/BgD2/wYA9v8GAPb/BgD1/wYA9v8GAPf/BgD3/wUA9/8FAPf/BAD3/wQA+P8EAPj/BAD5/wQA+f8EAPr/BAD7/wMA/P8DAPz/BAD9/wQA/f8DAP7/AwD+/wMA//8CAAAAAgAAAAIAAQACAAIAAgADAAIAAwABAAMAAQAEAAEABQABAAUAAAAGAAAABwAAAAcAAAAHAAAACQAAAAoA//8KAP7/CgD//woA//8KAP7/CgD+/woA/v8LAP7/CgD9/woA/f8LAPz/CwD9/woA/f8KAP3/CQD9/wkA/f8KAP3/CQD9/wgA/f8IAP3/CAD9/wcA/f8GAPz/BgD8/wUA/f8EAP3/AwD9/wIA/f8CAPz/AQD8/wAA/P////z//v/8//3//P/8//3//P/9//v//f/5//3/+f/9//j//v/3//7/9v/+//b////1//7/9P/+//T//v/0//7/8/////P////z////8/////L//v/y//7/8v/+//P//v/y//7/8v/+//P////z//7/8//+//P////1////9f////X////2////9/////f////3////+P////r////7////+/////v////8/////f////7//v/+//7////+/wAA/v8AAP7/AQD+/wIA/v8DAP//BAD//wUA/v8FAP7/BgD//wcA//8IAP7/CQD//wkA/v8JAP7/CgD//wsA//8MAAAADAAAAA0AAAANAAAADgABAA4AAQAPAAEADwAAAA8AAAAPAAAAEAABABAAAgAQAAIAEAACABAAAwARAAMAEQADABEAAwARAAQAEQADABAAAgAQAAIAEAADABAAAwAQAAMADwADAA8AAwAPAAQADgAEAA0ABAANAAQADQAEAAwABQAMAAUACwAEAAoABAAKAAQACQAEAAgABAAHAAMABgADAAQAAwAEAAMAAwADAAIAAwABAAMAAAADAP//AwD+/wIA/P8CAPv/AgD6/wIA+f8CAPj/AgD3/wIA9v8CAPX/AgD0/wMA8/8DAPL/AwDx/wIA8P8CAO//AgDv/wIA7v8CAO3/AgDt/wIA6/8CAOr/AgDq/wEA6v8AAOn/AQDp/wEA6v8BAOn/AQDp/wEA6f8BAOr/AQDq/wAA6/8AAOv/AADs/wAA7P8AAOz/AADt////7v///+7////v////7//+//D//v/x//3/8v/9//P//f/0//3/9f/8//b/+//3//v/+f/7//r/+//6//v/+//8//3/+//+//v//v/7/wAA+/8BAPr/AgD6/wMA+f8EAPn/BAD5/wUA+v8GAPr/BwD6/wcA+/8IAPv/CQD7/wkA+/8KAPv/CwD8/wwA/P8MAPz/DQD8/w4A/f8OAP3/DwD9/w8A/v8PAP7/EAD+/w8A/v8PAP//EAD//xAA//8QAP//EAAAABEAAAARAAAAEQABABEAAQARAAEAEQACABEAAwARAAMAEQADABEAAwARAAMAEQADABAAAwAQAAMAEAADAA8AAwAPAAMADgADAA0AAwANAAMADQADAAwAAwALAAMACwADAAoAAwAKAAMACgADAAkAAwAIAAMABwACAAYAAgAFAAMABQACAAMAAgACAAIAAQACAAAAAgD//wIA//8CAP3/AQD8/wEA+/8BAPr/AQD5/wEA+f8AAPj/AAD3/wAA9v8AAPX/AAD1/wAA9P8AAPP/AADy/wAA8v8AAPH/AADx/wAA8f8AAPD////w////8P////D/AADw/wAA8P8AAPD/AQDw/wEA8f8AAPH/AADx/wAA8f8BAPL/AQDy/wEA8/8BAPT/AQD0/wEA9f8BAPX/AQD2/wAA9/8AAPf/AQD4/wEA+P8AAPn/AAD6/wAA+v8AAPv/AAD7/////P8AAP3/AAD+//////8AAAAAAAAAAAAAAQAAAAIAAAACAP//AgAAAAMAAAAEAAAABAAAAAUAAAAGAP//BgAAAAYAAAAGAP//BwD//wgA//8IAAAACAAAAAgAAQAJAAAACQAAAAkAAQAJAAEACgABAAoAAQAKAAIACgACAAoAAQAKAAEACgACAAoAAgAKAAIACQABAAkAAgAKAAEACQABAAkAAgAJAAIACQABAAgAAQAIAAEACAABAAgAAQAIAAIACAACAAcAAQAHAAEABwABAAYAAQAFAAAABQABAAUAAAAEAAAAAwAAAAMAAAADAAAAAgD//wEA//8BAAAAAQAAAAAA///+/////v////3////9//7//P////v////6////+v/+//n//v/5//7/+P/+//f//v/2//7/9f/+//X//v/0//7/9P/+//P//v/y//7/8v/9//L//f/y//7/8f/+//H//v/x//7/8f/+//H//v/x//7/8f/+//H//v/x//7/8v/+//P//v/z//7/8v/+//P//v/0//3/9P/9//X//f/3//3/9//9//j//f/5//3/+v/9//v//f/8//3//v/9//7//f////z/AAD8/wEA/P8CAP3/AwD9/wQA/f8FAP3/BgD8/wYA/f8HAP3/CAD9/wkA/P8KAP3/CwD9/wsA/f8MAP3/DQD9/w0A//8OAP//DwD//w8A//8QAAAAEAAAABAAAAAQAAEAEAABABEAAQARAAIAEQACABAAAgAQAAMAEAADABAAAwAQAAQAEAAFABAABQAPAAUADwAFAA8ABQAOAAUADQAGAA4ABgANAAYADAAGAAwABgALAAcACwAHAAoABgAJAAYACQAGAAgABgAHAAYABgAGAAYABgAGAAYABQAGAAQABgADAAcAAgAGAAEABgAAAAUAAAAFAP//BgD+/wUA/v8EAP3/BAD8/wQA/P8EAPv/AwD6/wMA+f8DAPj/AwD4/wIA9/8CAPf/AgD2/wIA9f8BAPT/AQD0/wEA9P8BAPP/AQDy/wAA8f8AAPH/AADw/wAA8P8AAO//AADv/wAA7/8AAO//AADu/wEA7v8AAO7/AADu/wAA7v8AAO//AADu////7v///+7////v////7/////D////w////8f////L/AADy/wAA8v////P/AAD0/wAA9f////b//v/3////+P////n////6//7/+v/+//z//v/9//7//v/9/////f8AAP3/AQD8/wIA/P8DAPz/BAD7/wUA+/8GAPv/BwD7/wgA+/8IAPv/CQD7/woA+/8KAPv/CwD7/wsA+v8MAPr/DAD6/w0A+v8OAPr/DgD6/w4A+v8OAPr/DwD6/w8A+v8PAPr/DgD6/w4A+v8OAPv/DgD7/w0A+/8NAPv/DgD7/w0A+/8NAPz/DAD8/wsA/P8LAPz/CwD8/wsA/P8KAP3/CgD9/wkA/f8IAP7/CAD+/wgA/v8IAP//BwD//wYA//8GAP//BgAAAAUAAAAEAAAAAwAAAAMAAQADAAEAAgACAAIAAgACAAIAAQACAAEAAgAAAAEAAAABAP//AQD+/wEA/v8CAP7/AgD9/wIA/f8CAP3/AgD8/wMA/P8DAPz/AwD7/wMA+/8DAPv/AgD6/wIA+v8DAPr/AwD5/wIA+P8CAPn/AgD5/wMA+P8DAPj/AgD4/wIA+P8CAPj/AwD3/wIA9/8CAPf/AgD3/wIA9/8CAPf/AwD3/wMA9/8EAPf/AwD3/wQA9/8EAPj/BAD4/wQA+P8FAPn/BAD5/wQA+f8FAPr/BQD6/wQA+/8EAPv/BQD8/wUA/P8FAP3/BQD9/wUA/f8FAP7/BQD//wUA//8EAP//BQAAAAUAAQAFAAEABQACAAUAAwAEAAMABAAEAAQABAAEAAUAAwAFAAMABgADAAYAAwAGAAIABgACAAYAAgAHAAIABwABAAcAAQAHAAEACAABAAcAAQAHAAIACAABAAcAAQAHAAEABwAAAAcAAAAHAAAABgD//wYA//8FAP7/BQD+/wUA/v8FAP7/BAD+/wMA/v8DAP7/AwD//wIA/v8CAP7/AQD+/wEA/v8BAP7/AAD+/////v////7////+//7//v/+//7//f/+//3//f/9//3//f/9//3//f/8//z//P/9//z//f/9//3//P/9//z//f/9//3//P/9//v//f/8//3//P/9//z//f/8//3//P/9//z//P/8//3//P/9//z//P/8//z//P/8//z//f/9//3//f/8//3//P/9//z//f/8//3//P/9//z//f/7//3/+//9//z//f/8//3//P/+//z//v/7//7/+//+//v//v/8/////P////z////8/////P////z////8/////f8AAP3/AAD8/wAA/P8AAP3/AQD9/wEA/f8BAP3/AgD+/wIA/v8DAP7/AwD+/wMA/v8EAP7/BAD+/wQA//8FAP//BQD//wUAAAAGAAAABwAAAAcAAAAHAAAACAABAAgAAQAIAAEACAACAAkAAgAJAAIACQACAAkAAgAKAAIACgADAAkABAAJAAQACQADAAkAAwAJAAMACAADAAgAAwAIAAMABwADAAcABAAGAAQABgAEAAUABAAEAAUABAAFAAQABAADAAUAAwAFAAIABQABAAUAAAAGAP//BQD+/wUA/f8FAPz/BgD8/wUA+/8FAPr/BQD5/wUA+f8FAPj/BQD3/wUA9/8FAPf/BQD2/wUA9v8FAPX/BQD1/wUA9P8FAPT/BQD0/wUA9P8EAPP/BAD0/wQA9P8EAPT/BADz/wQA9P8EAPT/AwD0/wMA9P8DAPT/AwD1/wIA9f8CAPX/AQD1/wEA9v8BAPf/AAD3/wAA+P8AAPn/AAD5/wEA+f8AAPr/AAD6////+v8AAPv/AAD7/////P////z////9/////f////7//v/+//7////+/////v////7////9/wAA/f8BAP3/AQD9/wIA/f8CAP3/AgD9/wMA/f8DAPz/AwD9/wQA/f8EAPz/BQD8/wUA/P8FAPz/BQD8/wYA+/8HAPv/BwD7/wcA/P8IAPz/CQD8/wkA/P8JAPz/CgD8/wsA/P8KAPz/CwD8/wwA/P8MAP3/DQD9/w0A/f8OAP3/DgD9/w4A/f8OAP3/DwD9/w8A/P8PAPz/DwD9/xAA/f8QAP3/EAD9/xAA/f8QAP7/DwD+/w8A/v8PAP7/DwD+/w4A//8OAP//DQD//wwA//8LAP//CwAAAAoAAAAJAAEACAABAAcAAQAGAAEABQAAAAQAAQADAAEAAgABAAIAAQAAAAEA//8BAP7/AgD9/wIA/P8DAPv/AwD6/wMA+P8DAPf/AwD2/wMA9f8DAPT/AwDz/wIA8/8CAPL/AwDx/wMA8f8CAO//AwDv/wMA7/8CAO//AgDu/wIA7f8CAO3/AQDt/wIA7f8BAO3/AQDu/wIA7v8CAO7/AgDu/wEA7/8BAO//AQDv/wAA7/8AAPD/AADx////8v////P/AADz////9P////X////2////9v////f////5////+f////r////6////+/////z////9/////v////7/////////AAD//wEA//8BAP//AgD//wIA//8DAP//BAD//wUAAAAFAAAABgAAAAYAAAAHAAAABwAAAAgAAAAIAAAACAABAAkAAQAJAAEACQABAAkAAQAJAAEACgABAAoAAQAKAAEACwABAAsAAQALAAEACwABAAwAAQAMAAEADAABAAwAAQAMAAIADAACAAwAAgAMAAIADAACAAwAAgAMAAIADAACAAwAAgALAAIACwACAAsAAgALAAIACgACAAoAAgAKAAEACQACAAkAAgAIAAEABwABAAYAAgAHAAIABgABAAUAAQAFAAEABAABAAMAAgACAAIAAQACAAAAAgD//wIA//8CAP7/AQD9/wEA/f8BAPz/AQD6/wEA+f8BAPn/AQD5/wEA+P8BAPf/AQD3/wEA9v8BAPX/AQD1/wEA9P8AAPP/AADz/wEA8/8BAPL/AQDy/wEA8/8AAPL/AADx/wAA8v8AAPL////y////8v////L/AADy/wAA8/////T/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AwD2/wMA9/8CAPf/AQD4/wEA+P8BAPn/AQD6/wEA+/8BAPz/AAD8/wAA/P8AAP3////9/////v///////v////7//////wAA/v8BAP7/AQD9/wIA/f8CAP3/AgD9/wMA/P8DAPz/BAD8/wQA/P8EAPv/BAD8/wUA/f8GAPz/BgD8/wYA/P8GAPz/BwD8/wcA/P8IAP3/CAD9/wkA/f8JAP3/CQD9/wkA/f8KAP7/CgD+/woA/v8KAP7/CgD//woA//8KAP//CgD//woA//8KAAAACgAAAAoA//8KAP//CgD//woAAAAKAAAACQAAAAkAAQAJAAEACQABAAgAAQAIAAEACAABAAgAAQAHAAIABwABAAYAAQAFAAEABQABAAUAAQAEAAIAAwACAAMAAQACAAIAAgACAAEAAgABAAIAAAACAP//AgD//wMA/v8DAP7/AwD9/wMA/f8DAPz/AwD7/wMA+/8DAPv/AwD6/wMA+v8EAPn/AwD5/wMA+P8EAPj/BAD3/wUA9/8EAPf/BAD3/wUA9/8FAPb/BAD1/wUA9v8FAPb/BQD2/wUA9v8FAPb/BAD2/wQA9v8EAPf/BAD3/wQA9/8EAPf/BAD3/wQA+P8DAPj/AwD5/wMA+f8DAPr/AwD6/wMA+/8CAPz/AQD8/wIA/f8BAP3/AAD9/wAA/v8BAAAAAAAAAAAAAAAAAAEAAAABAP//AgD+/wIA/v8DAP7/AwD+/wQA/v8FAP3/BQD9/wUA/f8GAP3/BgD9/wYA/f8GAP3/BwD8/wcA/P8HAP3/BwD9/wcA/f8HAP3/BwD9/wcA/f8HAP3/BwD9/wcA/f8HAP7/BwD+/wcA/v8HAP7/BgD+/wYA/v8GAP7/BgD+/wYA/v8GAP7/BgD//wYA//8GAP//BQD//wUA//8GAP//BQD//wUA//8FAP//BQD//wQA//8EAP//AwD//wMA/v8CAP//AgD//wEA/v8BAP7/AQD+/wAA/v////////////7//v/+//7//f/+//3//v/8//7//P/+//v//v/7//3/+v/9//r//f/5//3/+f/9//j//f/4//3/9//+//f////3////9/////b//v/2//7/9v/+//b//v/2////9v////b////2////9v8AAPb/AAD2/wAA9v8BAPb/AAD2/wAA9/8AAPb/AQD3/wEA+P8BAPj/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AQD2/wAA9/8AAPj////4////+f/+//n//v/6//7/+v/+//r//v/7//3//P/9//z//P/9//z//v/8//7//P////v////7////+/8AAPv/AQD7/wAA+/8BAPv/AQD7/wIA+/8CAPv/AgD7/wMA+/8DAPv/AwD7/wMA+/8DAPv/BAD7/wUA+/8FAPv/BQD7/wUA/P8FAPz/BQD8/wUA/P8GAPv/BgD7/wYA+/8HAPz/BwD7/wcA+/8HAPz/CAD7/wgA+/8IAPz/CAD8/wkA/P8JAPz/CQD8/woA/P8KAPz/CgD8/woA/P8KAP3/CwD9/woA/f8KAP3/CgD9/woA/f8JAP3/CQD+/wkA/v8IAP7/CAD+/wgA/v8HAP//BwAAAAcAAAAGAAAABQABAAUAAQAEAAIAAwACAAMAAwADAAMAAgADAAEABAAAAAUAAAAGAP//BgD9/wYA/P8GAPz/BwD8/wcA+/8IAPr/CAD6/wkA+f8JAPn/CQD4/wkA9/8KAPf/CgD3/woA9/8KAPb/CgD2/woA9v8KAPb/CgD2/woA9v8KAPb/CgD2/woA9v8KAPb/CQD2/wkA9v8JAPj/CAD4/wgA+P8IAPj/BwD5/wYA+f8GAPr/BgD7/wUA+/8EAPz/BAD9/wMA/f8CAP3/AgD//wEA//8AAAAA//8AAP//AAD//wEA/v8BAP3/AgD9/wIA/P8DAPv/AwD7/wQA+/8EAPv/BAD6/wQA+v8FAPn/BQD5/wUA+f8FAPn/BQD4/wUA+P8FAPj/BgD3/wUA9/8FAPf/BgD3/wYA9/8FAPf/BQD3/wUA9/8EAPf/BQD3/wUA9/8EAPf/BAD4/wUA+P8EAPj/BAD4/wQA+P8EAPj/AwD5/wQA+v8EAPr/AwD5/wMA+f8DAPr/BAD6/wMA+v8DAPr/AwD7/wMA+/8DAPz/AgD8/wIA/P8CAP3/AgD9/wIA/f8BAP3/AQD+/wEA/v8BAP//AQD//wAA//8AAAAAAAAAAAAAAAAAAAEA//8BAP7/AQD+/wIA//8CAP7/AwD9/wMA/f8EAPz/BQD8/wUA+/8FAPv/BQD7/wYA+v8GAPr/BgD6/wYA+f8HAPn/BwD5/wgA+P8IAPj/CAD4/wgA+P8IAPf/CAD3/wkA9/8JAPf/CQD3/wkA9/8JAPf/CQD3/wkA+P8JAPj/CQD4/wgA+f8IAPn/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///3////+P/+//j//v/5////+v/+//v//v/8//7//P/+//3//v/+//7////+/////v8AAP7/AQD+/wIA/v8CAP7/AwD+/wQA/v8EAP7/BAD//wUA//8GAP//BgD//wYA//8HAP7/BwD+/wgA//8IAP//CAD//wkA//8JAAAACQD//wkA//8JAP//CQD//woA//8KAP//CgD//woAAAAKAP//CwD//wsA//8LAP//CwD+/wsA/v8LAP7/CwD+/wsA/v8LAP3/CwD9/wsA/v8LAP7/CwD9/wsA/f8MAP7/DAD9/wsA/f8LAP7/CwD9/wsA/f8LAP3/CwD+/wsA/v8KAP7/CgD+/wkA/v8JAP7/CQD//wgAAAAIAP//BwD//wYAAAAFAAEABQABAAQAAQADAAIAAgADAAIAAgABAAMAAAADAP//AwD+/wMA/P8EAPz/BAD7/wQA+v8FAPn/BQD4/wUA9/8FAPb/BQD2/wYA9v8GAPX/BgD0/wYA9P8FAPP/BgDz/wYA8/8GAPL/BgDy/wUA8f8FAPL/BQDy/wUA8v8FAPL/BADy/wQA8v8EAPL/AwDz/wMA9P8CAPT/AgD1/wIA9v8CAPf/AgD3/wEA+P8AAPn/AAD5/wAA+v////v////8/////f/+//3//v/+//3////9/wAA/f8AAP3/AQD9/wIA/P8DAPz/AwD9/wQA/f8FAPz/BQD8/wYA/f8GAPz/BgD9/wcA/f8IAP3/CAD9/wgA/f8IAP7/CAD+/wgA/v8IAP7/CAD//wgA//8IAP//CAD//wcA//8HAP//BwAAAAcA//8HAP//BgAAAAYAAAAGAAAABgAAAAUAAAAFAAAABQAAAAUAAAAEAAAABAAAAAQAAAADAAAAAgAAAAMAAAACAAAAAgD//wIA//8CAP//AQD+/wEA/v8BAP7/AQD+/wIA/v8CAP7/AQD+/wAA/v8BAP7/AQD+/wEA/f8AAP7/AAD+/wAA/v8AAP3/AAD+/wAA/v8AAP7////+//////////7////+//7//v/+/////v////7////+/////f8AAPz/AQD8/wEA/P8BAPv/AQD7/wEA+v8BAPr/AgD6/wIA+f8CAPn/AgD4/wMA+P8DAPf/AwD3/wMA9/8DAPf/AwD2/wMA9v8DAPb/AwD2/wQA9/8EAPb/AwD2/wQA9v8EAPb/BAD2/wMA9v8DAPf/AgD3/wIA+P8CAPj/AQD5/wEA+v8CAPr/AQD7/wEA+/8BAP3/AQD+/wAA/v////7/////////AQD//wIA//8DAP//BAD+/wUA/v8FAP7/BgD+/wcA/v8HAP7/CAD+/wkA/v8KAP7/CgD+/wsA/v8LAP7/DAD+/wwA/v8NAP7/DQD+/w0A//8OAP//DQD+/w0A/v8NAP//DQAAAA0AAAANAAAADAD//wsAAAALAAAACwD//woA//8JAAAACQAAAAgAAAAIAAAABwAAAAYAAAAGAAAABQAAAAQAAAADAAAAAwAAAAIAAAACAAAAAQAAAAEAAAAAAAAA//8BAP//AAD+/wAA/v////3////9/////f////3////8/////P////z////8////+/////v////7////+/////z////7////+/8AAPv////8////+/////v/AAD8/wAA/P8AAPz/AAD7/wAA/P8BAPz/AQD8/wEA/P8BAPz/AgD8/wIA/P8CAPv/AgD7/wMA+/8DAPv/AwD7/wQA+v8EAPr/BAD6/wQA+v8EAPr/BAD5/wUA+f8FAPn/BQD5/wUA+f8FAPn/BQD5/wUA+f8FAPn/BQD5/wUA+f8EAPn/BAD5/wQA+f8EAPn/BAD6/wMA+v8CAPr/AwD7/wIA+/8CAPv/AQD8/wEA/f8BAP3/AAD+/wAA/v////7//v////7/AAD+/wAA/v8BAP7/AgD8/wIA/P8DAPz/AwD8/wQA/P8FAPv/BgD7/wYA+/8HAPv/CAD6/wgA+v8IAPr/CQD6/wkA+v8JAPr/CgD6/woA+v8JAPr/CgD6/woA+v8JAPr/CQD7/wkA+/8JAPv/CQD7/wkA/P8JAPz/CAD8/wgA/P8HAPz/BwD9/wYA/f8GAP7/BQD+/wQA/v8EAP7/BAD//wMA//8CAP//AQD//wEAAAABAAAAAQABAAAAAAD//wAA//8BAP7/AQD+/wEA/v8BAP7/AgD9/wIA/P8BAPz/AQD8/wEA/P8CAPz/AQD7/wEA+/8BAPv/AgD7/wIA+/8CAPv/AgD7/wIA+/8BAPv/AgD7/wIA/P8CAPz/AgD8/wMA/P8DAPz/AgD8/wMA/f8DAP3/AwD9/wMA/P8EAPz/BAD9/wQA/f8EAP3/BAD9/wQA/f8EAP3/BQD9/wUA/f8FAP3/BQD9/wUA/v8FAP3/BgD9/wYA/v8GAP7/BgD9/wYA/f8GAP7/BgD+/wYA/v8FAP7/BQD+/wUA//8FAP//BQD+/wQA//8EAP//BAAAAAMAAAADAAAAAwAAAAIAAAABAAEAAQABAAEAAgAAAAIAAAADAP//AwD+/wMA/v8EAP7/BQD9/wUA/f8FAPz/BgD7/wYA+/8HAPv/BwD6/wcA+v8HAPr/CAD6/wgA+f8IAPn/CAD5/wgA+f8JAPn/CQD5/wkA+P8JAPj/CQD4/wkA+f8JAPn/CAD5/wcA+f8HAPr/BwD6/wYA+v8GAPr/BQD6/wUA+/8FAPv/BAD8/wMA/P8CAPz/AgD9/wEA/f8AAP3/AAD9/////v/+/////v////3////8////+/8AAPv/AQD6/wAA+f8AAPn/AQD4/wIA+P8BAPf/AQD2/wIA9v8CAPb/AgD1/wIA9f8CAPb/AgD1/wIA9f8DAPX/AwD1/wIA9f8CAPX/AgD1/wIA9v8CAPb/AgD2/wIA9v8CAPf/AgD3/wIA9/8CAPj/AgD4/wIA+P8CAPn/AgD6/wIA+v8CAPv/AgD7/wIA/P8DAP3/AwD9/wIA/f8DAP7/AwD//wQA//8EAAAAAwAAAAMAAQADAAEABAABAAQAAgAEAAIABAADAAQAAgAEAAMABQAEAAQABAAEAAQABAAEAAQABQAEAAUABAAFAAQABQADAAUAAwAFAAMABgADAAYAAgAGAAMABgADAAYAAgAGAAIABgACAAcAAQAHAAEABwABAAgAAAAIAAAACAAAAAgA//8IAP//CAD//wkA//8JAP//CQD+/wkA/v8JAP7/CQD+/woA/f8KAPz/CQD8/wkA/f8JAPz/CQD8/wkA/P8IAPz/CAD8/wgA/f8IAPz/BwD8/wgA/f8IAP3/BwD8/wYA/P8GAP3/BQD9/wUA/v8FAP3/BAD9/wMA/v8DAP7/AgD//wIA/v8BAP7/AAD+///////+/////v////3////8////+/////v////5////+f////n/AAD4/wAA9/8AAPf/AAD3/wAA9v8AAPb////1////9f8AAPX////1////9P////T////0////9P////T////0//7/8//+//T//v/0//7/9P/+//X//v/1//7/9v/+//b//v/2//3/9//9//f//v/4//7/+P/+//n//v/6//7/+v////v////8//7//P////z/AAD9/wAA/v8AAP//AAD//wEA//8AAAAAAQABAAEAAQABAAEAAgACAAIAAgADAAIAAwADAAQABAAEAAQAAwAEAAMABAAEAAQABAAFAAUABQAEAAYABAAGAAQABgAFAAYABAAGAAQABgAEAAYABAAGAAQABgAEAAYABAAGAAQABgAEAAYABAAGAAMABgADAAYAAwAGAAIABgACAAYAAgAGAAIABgACAAYAAQAGAAEABQABAAUAAAAFAAAABQAAAAUA//8FAP//BQD//wUA//8FAP//BQD//wUA/v8EAP//BAD//wQA/v8EAP7/BAD+/wMA/v8DAP7/AwD+/wMA//8CAP7/AgD+/wIA//8BAP//AQD//wEAAAABAP//AAD//wAA//8AAP/////////////+/////v8AAP7/AAD+/wAA/f8AAP3/AAD8/wEA/P8BAPz/AAD7/wAA+/8AAPr/AAD7/wAA+/8AAPr/AAD6/wAA+v8AAPr////6////+v////r////6////+v////r//v/6//7/+v/+//r//v/6//7/+//+//v//v/7//7//P/+//z//f/8//7//P/+//3//v/9//7//f/+//7//v/+//7////+/////v8AAP7/AAD//wAA//8BAAAAAgAAAAIAAAACAAAAAgABAAIAAQADAAIAAwACAAMAAgADAAMABAADAAQAAwAEAAMAAwADAAQABAAEAAQABAAEAAQABAAEAAQABAAEAAQABAAEAAUABAAEAAQABAAEAAQABAAEAAQABAADAAMAAwADAAMAAwADAAMAAwACAAMAAgADAAIAAgABAAIAAQACAAAAAgAAAAIA//8CAP//AgD+/wIA/v8DAP7/AgD9/wEA/f8BAPz/AQD8/wEA/P8BAPz/AQD7/wAA+/8AAPv/AAD7/wAA+v////r////6////+v////r////6////+/////v//v/7//7/+//+//v//v/7//3/+//9//z//f/9//3//f/8//3//P/+//z//v/8//7/+/////v////7////+/8AAPv/AAD7/wAA+v8BAPv/AQD7/wEA+/8BAPr/AgD6/wIA+v8CAPr/AgD6/wIA+/8DAPv/AwD7/wMA+v8DAPv/AwD7/wIA/P8CAPv/AwD8/wMA/P8DAP3/AwD9/wMA/f8CAP7/AgD+/wIA/v8CAP//AgAAAAMAAAACAAEAAQABAAIAAgACAAIAAgACAAIAAgACAAMAAgAEAAEABAACAAUAAgAFAAIABgACAAYAAgAGAAIABgACAAcAAgAHAAIABwACAAcAAgAIAAIACAACAAgAAgAIAAIACAADAAgAAwAIAAMABwADAAcAAwAHAAMABwADAAcAAwAGAAMABgADAAYAAwAGAAMABgACAAUAAwAFAAMABQADAAQAAgAEAAMAAwACAAMAAgADAAIAAwACAAIAAgACAAEAAgABAAIAAAABAAAAAQAAAAAA/////////////////v////7//v/+//7//f/+//3//v/9//7//P/9//z//f/9//z//P/8//z//P/8//z//P/8//v/+//7//v/+//7//v/+//7//v//P/7//v/+//8//r//P/6//z/+v/8//r//f/6//3/+v/9//r//f/6//3/+v/9//r//f/6//7/+v/+//r//v/6//7/+v////r////6////+v8AAPr/AAD6/wAA+v8BAPv/AQD7/wEA+/8AAPr/AQD7/wEA/P8BAPv/AQD7/wEA/P8BAPz/AQD8/wEA/P8BAP3/AQD9/wIA/v8CAP7/AgD+/wEA/v8AAP//AQAAAAEAAAAAAAAAAAAAAAAAAQAAAAEAAAACAAAAAwAAAAMAAAADAAAABAAAAAUAAAAFAAAABQD//wUA//8GAP//BwAAAAcA//8HAP//CAAAAAgA//8IAP//CAD//wgA//8IAAAACAAAAAkA//8IAP//CAD//wgAAAAIAAAACAAAAAgAAAAIAAAACAAAAAgAAAAHAAAABwAAAAcAAAAHAAAABgAAAAYAAAAGAAAABQAAAAUAAAAEAAEABAABAAMAAQADAAAAAgAAAAIAAQACAAEAAQAAAAAAAQAAAAEAAAABAAAAAQD//wEA/v8BAP7/AQD+/wEA/f8BAP3/AQD9/wIA/P8BAPv/AQD7/wIA+v8BAPr/AQD6/wEA+v8BAPn/AQD5/wEA+f8BAPn/AQD4/wEA+P8BAPn/AQD5/wEA+P8BAPj/AgD4/wIA+P8BAPj/AQD4/wEA+f8BAPn/AQD5/wEA+f8BAPn/AQD5/wIA+f8CAPr/AQD6/wEA+v8CAPv/AgD7/wIA+/8CAPv/AgD8/wIA/P8CAPz/AwD8/wMA/f8CAP3/AgD9/wIA/f8CAP7/AgD+/wEA/v8BAP//AQAAAAEAAAABAAAAAQABAAEAAgABAAIAAQADAAEAAwABAAQAAAAFAAAABQAAAAUAAAAGAAAABgAAAAcAAAAHAP//BwD//wgAAAAIAAAACQD//wkA//8JAP//CQD//woA/v8KAP7/CgD+/woA/v8KAP7/CwD+/wsA/v8KAP7/CgD+/woA/v8KAP7/CgD+/woA//8KAP7/CQD+/wkA/v8JAP7/CAD+/wcA/v8GAP7/BgD//wYA/v8FAP7/BAD+/wQA/v8EAP7/AwD+/wIA/v8BAP7/AQD+/wAA/v8AAP7////+//7//v/9//7//f/+//z//v/7//7/+//+//r//v/5//7/+f/+//j//v/4//7/+P/9//f//v/2//7/9v/9//b//f/1//3/9f/+//X//v/1//7/9P/+//T//v/0//7/9P/+//T//v/0//7/9P////T////0//7/9P/+//X////1//7/9f/+//b////2////9v////f/AAD4/wAA+P8AAPn////5////+v8AAPr/AAD7/wAA+/8AAPv/AQD8/wEA/f8BAP7/AgD+/wIA//8CAP//AgD//wMAAAACAAEAAgACAAIAAgACAAIAAgADAAIABAACAAMAAgAEAAIABQACAAUAAwAFAAMABgADAAYAAwAGAAMABwADAAcABAAHAAQACAAEAAgAAwAIAAMACAAEAAkAAwAJAAMACgADAAoAAwAKAAMACgADAAsAAwALAAMACgADAAsAAwALAAMACwADAAsAAwALAAMACwADAAsAAwALAAMACwADAAoAAwAKAAMACgACAAkAAgAKAAIACgACAAkAAgAJAAIACQACAAgAAgAIAAIACAABAAcAAQAGAAEABgABAAUAAQAFAAEABAAAAAQAAAADAAAAAwD//wIA//8AAAAAAAD//wAA/v////7///////7//v/9//7//P/+//z//f/7//3/+v/9//r//v/5//3/+P/9//j//f/3//3/9//9//b//P/1//z/9P/8//X//P/1//z/9P/8//T//P/0//z/9P/8//T//P/1//z/9f/7//T//P/1//z/9f/8//X//P/1//z/9f/8//b//P/2//z/9v/8//b//f/3//3/9//9//j//f/5//3/+f/9//n//f/5//7/+v/+//v//v/8//7//P/+//z////9/////f////7////+//////8AAP//AAAAAAAAAAABAAAAAQABAAEAAQABAAEAAQABAAIAAgABAAIAAQADAAIAAwACAAMAAgADAAIAAwACAAQAAgAFAAIABAACAAQAAgAEAAIABQADAAUAAwAFAAMABQADAAUAAwAFAAMABQADAAYAAwAGAAMABgADAAYAAwAGAAMABgADAAYAAgAGAAIABgACAAYAAgAHAAIABwACAAcAAgAHAAIABwACAAcAAgAHAAEABwABAAcAAQAHAAEABgABAAcAAQAHAAEABgABAAYAAQAGAAEABgAAAAYAAAAGAAAABgAAAAUAAAAFAAAABAAAAAQAAAAEAAAABAAAAAMAAAADAAAAAwAAAAIAAAABAAAAAQAAAAAAAAD///////////7/AAD+/wAA/v////3////9/////f////z////7////+/////v////7////+v////n////5////+f////n////4////+P8AAPj////4////+P8AAPj////4////+P////n////5////+f////r////6////+/////v////7////+/////v//v/8/////P8AAP3/AAD9/////v8AAP//AAD/////////////AAAAAP//AAD//wAAAAAAAP//AQAAAAEAAAACAP//AgD//wIAAAACAAAAAgD//wIA//8DAAAAAwD//wMAAAADAAAAAwAAAAMA//8DAAAAAwAAAAMA//8DAP//AwAAAAMAAAADAAAAAwD//wMAAAADAP//AwD//wIA//8CAAAAAgD//wIAAAACAAAAAgAAAAIAAAABAAAAAQAAAAEAAAABAAAAAQAAAAEAAAABAAAAAQAAAAEAAAABAAAAAQAAAAAAAAABAAAAAQAAAAEAAAABAAAAAQABAAEAAQABAAEAAQAAAAEAAQABAAEAAQACAAEAAQABAAEAAQACAAEAAgABAAEAAQABAAEAAgABAAIAAQACAAAAAgAAAAIAAAACAAAAAgAAAAIAAAACAP//AgD//wIA//8CAP//AgD//wIA//8BAP7/AQD+/wIA/v8CAP7/AgD+/wIA/v8CAP3/AQD9/wEA/f8AAP3/AAD8/wEA/P8AAPz/AAD8/wAA/P8AAPz////8/////P8AAP3////8/////P////3////9//7//f/+//7//v/+/////v/+//7//v/+//7////+/wAA/v8AAP7/AAD+/wAA/v8BAP3/AQD9/wEA/v8CAP3/AgD9/wIA/v8CAP3/AwD9/wMA/v8EAP7/AwD+/wMA/v8DAP//AwD+/wMA/v8EAP7/BAD+/wQA//8EAP//BAD+/wQA//8EAP//BAD//wQA//8DAAAAAwAAAAMA//8DAAAAAgAAAAIAAQACAAEAAgABAAIAAAABAAEAAAAAAAAAAAAAAAEAAAABAAAAAAD//wEA//8BAP//AQD//wEA//8BAP//AQD+/wEA/v8CAP7/AQD9/wEA/f8BAP3/AgD9/wEA/f8BAP3/AQD9/wIA/f8CAP3/AQD9/wEA/f8BAP3/AgD9/wIA/f8BAP3/AQD9/wEA/v8BAP7/AQD+/wEA/v8CAP//AgD//wEA/v8BAP//AQD//wEA//8BAP//AQD//wEA//8BAAAAAgAAAAEAAAABAAAAAQAAAAEAAAABAAAAAQABAAEAAQABAAAAAQABAAAAAQABAAEAAQABAAAAAQAAAAEAAAACAAEAAgAAAAEAAQACAAAAAgAAAAEA//8BAP//AQD//wEA//8CAP//AgD+/wIA/v8CAP//AwD+/wIA/v8CAP7/AwD+/wIA/v8CAP7/AwD+/wIA/f8CAP7/AgD+/wMA/v8DAP7/AwD+/wIA/v8CAP7/AgD+/wMA/v8DAP7/AwD+/wMA/v8DAP7/AwD9/wMA/v8DAP7/AwD+/wMA/v8CAP7/AgD//wIA//8CAP//AgD//wIAAAACAAAAAQAAAAEAAAAAAAAAAAAAAAAAAQAAAAEAAAABAP//AQD//wIA//8BAP7/AQD+/wIA/v8CAP3/AQD9/wIA/f8CAPz/AgD8/wEA/P8BAPz/AQD7/wIA+/8CAPv/AQD7/wEA+v8CAPr/AgD6/wIA+v8CAPr/AQD5/wEA+f8BAPn/AQD5/wIA+f8BAPr/AQD6/wEA+v8BAPr/AQD6/wEA+v8BAPv/AQD7/wEA+/8BAPv/AQD8/wAA/P8AAPz/AQD9/wEA/f8BAP7/AQD+/wEA/v8BAP//AQD//wEA//8BAP//AQAAAAEAAAABAAAAAQABAAEAAgABAAIAAQACAAEAAgABAAIAAQADAAEAAwABAAMAAQADAAEAAwABAAMAAAAEAAAABAABAAQAAQAEAAEABQABAAUAAQAFAAAABQAAAAUAAAAFAAAABQAAAAUA//8FAAAABQAAAAYA//8GAP//BgD+/wYA//8GAP//BgD//wYA/v8GAP//BgD+/wYA/v8GAP3/BgD+/wYA/f8GAP3/BgD+/wYA/v8GAP3/BgD+/wYA/v8GAP7/BgD+/wYA/v8FAP7/BQD+/wUA/v8FAP7/BQD+/wUA//8EAP7/AwD+/wMA/v8DAP//AgD//wIAAAACAAAAAQD//wEA//8BAAAAAQAAAAAAAAAAAAAA//8BAP//AQD+/wAA/f8AAP3/AQD8/wEA/P8BAPz/AAD7/wEA+/8BAPv/AQD6/wEA+v8BAPn/AQD4/wEA+P8BAPj/AQD4/wEA9/8BAPf/AQD3/wEA9/8BAPf/AQD2/wAA9v8AAPf/AQD3/wAA9/8AAPf/AQD3/wAA9/8AAPf/AAD3/wAA+P8AAPj/AAD4/wAA+f8AAPn/AAD6////+v8AAPr/AAD6////+/8AAPv/AAD8/////P////z////9/////f////7////+/////v////////8AAAAAAQAAAAEAAAABAAAAAQAAAAEAAAACAAAAAgAAAAMAAAADAAAABAAAAAQAAAAEAAAABAAAAAQAAAAEAAAABQAAAAUAAAAFAAEABQABAAUAAQAFAAEABQABAAYAAQAGAAEABgABAAYAAQAGAAEABgABAAcAAQAHAAEABwABAAcAAQAIAAEACAABAAgAAQAIAAEACAABAAgAAQAIAAEACAABAAgAAQAIAAEACAABAAgAAQAIAAEACAABAAcAAgAHAAIABwABAAcAAgAHAAIABwABAAYAAQAGAAEABgABAAUAAQAFAAEABAABAAQAAQAEAAEAAwABAAMAAQACAAEAAgABAAEAAQABAAEAAQABAAAAAQD//wEA/v8CAP7/AgD9/wEA/P8BAPz/AQD7/wEA+v8BAPr/AQD5/wEA+P8BAPf/AAD3/wEA9/8BAPf/AQD2/wAA9v8AAPb/AQD2/wAA9f////X////1/wAA9f8AAPX/AAD1/wAA9f////X////1////9f/+//X//v/2////9v/+//f//v/3//7/+P/+//j//f/5//7/+f/+//r//f/6//7/+v/+//v//f/7//3//P/9//3//v/+//7////9/////f////3/AAD9/wAA/f8BAP3/AQD9/wIA/f8CAP3/AgD9/wMA/f8DAP7/BAD+/wQA/v8EAP7/BQD+/wUA/v8FAP//BgD+/wUA/v8FAP//BgD//wYA//8GAP//BgD//wcA/v8HAP//BwD//wcA//8HAAAABwD//wcA//8HAP//BwAAAAcA//8HAAAABwAAAAcA//8HAAAABwABAAcAAQAHAAEABwABAAcAAAAHAAAABwABAAcAAQAGAAEABgABAAYAAQAGAAEABgABAAYAAQAGAAEABgACAAYAAgAGAAIABgACAAUAAgAEAAMABAADAAQAAgADAAIAAgADAAIAAwACAAMAAgADAAEAAwABAAMAAAAEAAAAAwD//wMA/v8EAP7/AwD9/wMA/f8DAP3/AwD8/wMA+/8EAPv/AwD7/wMA+v8EAPn/AwD5/wMA+f8DAPj/AwD4/wMA+P8CAPf/AgD3/wIA9/8CAPf/AgD2/wIA9v8BAPb/AQD2/wEA9v8BAPf/AQD3/wAA9/8AAPf/AAD3/wAA+P8AAPj////5////+f////r////6//7/+v/+//v//v/7//7//P/+//z//f/8//7//f/+//7//v////7////+/wAA/f8AAP3/AAD9/wEA/f8BAP3/AgD9/wIA/f8DAP3/AwD9/wMA/f8EAP3/BAD9/wQA/f8FAP3/BQD+/wUA/v8FAP7/BgD+/wUA/v8FAP7/BQD+/wUA/v8FAP//BQD//wUA//8FAP//BQD//wUA//8EAAAABAAAAAQAAAAEAP//BAAAAAQAAQAEAAAAAwAAAAMAAAADAAAAAwAAAAMAAAADAAAAAwAAAAMAAAACAAAAAgABAAIAAQADAAAAAwAAAAMAAQACAAEAAgABAAIAAQACAAAAAgAAAAIAAQADAAEAAgAAAAIAAAACAAAAAgABAAEAAQABAAEAAgAAAAEAAAABAAEAAQABAAEAAQABAAEAAQABAAEAAQABAAEAAAABAAAAAQAAAAEA//8BAP//AQD//wEA//8BAP//AQD+/wAA/v8AAP3/AQD9/wEA/f8BAPz/AQD8/wEA+/8BAPv/AQD7/wEA+/8BAPr/AQD6/wEA+v8AAPr/AAD6/wAA+v8AAPn/AAD5/wAA+v8AAPr/AAD6/wAA+v8AAPr/AAD6/wAA+/8AAPv////7////+/////v////8/wAA/P////z////8/////f////7///////////////////8AAP//AQD//wEA//8BAP//AgD//wIA//8DAP//AwD//wMA//8EAP//BQD//wUA//8FAP//BQAAAAUAAAAFAAAABQD//wUA//8GAAAABgD//wYA//8GAAAABgAAAAUAAAAFAAAABQABAAUAAAAEAAAABAAAAAQAAAAEAAAAAwAAAAMAAQADAAEAAwABAAMAAAACAAAAAgABAAEAAQACAAEAAgAAAAEAAAABAAEAAQABAAEAAQAAAAEAAAABAAAAAQD//wEA//8BAAAAAQAAAAIAAAACAP//AQD//wIA//8CAAAAAQAAAAEAAAABAP//AQD//wEA//8CAAAAAgAAAAEAAAABAAAAAQAAAAIAAAACAP//AgD//wIA//8CAAAAAgD//wIA//8CAP//AgD+/wIA/v8CAP//AgD//wIA/v8CAP7/AgD//wEA//8BAP//AQD+/wEA/v8BAP7/AQD+/wEA/v8AAP3/AAD9/wAA/P8AAP3/AQD9/wEA/f8AAPz/AAD8/wAA/P8AAP3////8/////P////3////9/////f////3////9//7//f/+//3//v/9//7//v/+//7//f/+//3//v/9//7//f////3////9/wAA/f8AAPz/AQD9/wEA/f8BAPz/AQD8/wIA/P8DAPz/AwD8/wMA/P8EAPz/BAD8/wUA/P8FAP3/BQD9/wUA/f8FAP3/BQD9/wYA/f8FAP3/BQD+/wYA/v8GAP3/BQD+/wQA//8EAP//BAD//wQA/v8EAP7/BAD//wQA//8DAP//AwD//wMAAAADAP//AgD//wIAAAABAAAAAQAAAAEAAAABAAAAAAAAAP//AQD//wEA//8BAP7/AgD+/wEA/v8BAP3/AQD9/wEA/f8BAP3/AgD9/wEA/P8BAPz/AgD9/wIA/P8BAPz/AQD8/wIA/P8CAPz/AgD8/wIA/P8CAPz/AgD8/wIA/P8CAPz/AwD8/wIA/P8CAPz/AgD8/wIA/f8CAP3/AgD+/wIA/v8CAP3/AgD+/wIA/f8CAP3/AgD+/wIA//8CAP7/AgD//wIA//8CAP7/AgD+/wMA//8CAP//AgD//wIA//8CAP//AgAAAAIAAAACAP//AwAAAAIAAAACAAAAAgAAAAIAAAACAP//AQD//wIA//8CAP//AQAAAAEAAAABAP//AgAAAAEAAQABAAAAAQAAAAAAAQAAAAEAAAACAAAAAgAAAAIA//8CAAAAAwAAAAMA//8DAP//AwD//wMA//8EAP7/BQD+/wUA/v8FAP7/BQD9/wUA/v8FAP7/BgD+/wYA/v8GAP7/BgD9/wcA/f8HAP3/BwD+/wcA/v8HAP7/BwD+/wYA/v8HAP7/BwD+/wcA/v8GAP7/BgD+/wYA/v8GAP7/BQD//wUA//8FAP//BQD//wQA//8EAP//AwD//wMA//8CAP//AgD//wEA//8BAP//AAD//wAA//////////////7////9//7//f////z////8/wAA/P8AAPv/AAD7/wAA+/////r////5/wAA+f////n////5////+f8AAPj/AAD4/wAA+P8AAPj////4/wAA+P8AAPn/AAD4/wAA+P8AAPj////4////+P8AAPj/AQD4/wAA+P8AAPj/AAD5/wAA+f8BAPn/AAD6/wAA+v8BAPr/AQD6/wEA+/8BAPv/AgD7/wIA/P8BAPz/AQD8/wEA/f8CAP3/AQD9/wIA/f8CAP7/AQD+/wIA/v8CAP7/AgD+/wIA//8CAP//AgD//wIA//8CAAAAAgAAAAIAAQABAAEAAgABAAIAAQACAAIAAQACAAAAAwABAAMAAQACAAEAAwABAAQAAQAEAAEABAABAAQAAQAFAAEABQABAAUAAQAFAAAABgAAAAYAAAAGAAAABwD//wcA//8HAAAABwD//wgA//8IAP//CAD//wkA/v8JAP//CQD//wkA//8JAP//CQD//wkA//8JAP//CQD//wgA//8IAP//CAD//wgA//8HAAAABwD//wcA//8HAAAABgAAAAYAAAAGAAAABQAAAAUAAAAEAAAABAAAAAMAAQADAAEAAgABAAEAAQABAAEAAAACAAAAAgD//wIA//8CAP7/AgD+/wEA/f8BAP3/AQD9/wIA/P8CAPz/AgD8/wIA+/8CAPv/AgD6/wEA+v8BAPr/AQD5/wEA+f8BAPn/AQD5/wEA+f8BAPn/AAD5/wAA+f8AAPn/AAD5////+f////n/AAD5/wAA+f////n////5////+v////r////6////+v////r////7////+/////v//v/8//7//P/+//z//v/8//7//P////3////9//7//P/+//3//v/9/////v/+//7//v/+//7//////////////////////wAA//8AAP7/AAD//wAA//8AAP7/AAD//wEA//8BAP//AQD//wIAAAACAAAAAgD//wIAAAADAAAABAD//wMA//8DAAAABAAAAAQAAAAEAAAABAAAAAQAAAAEAP//BQD//wUA//8FAAAABQD//wUA//8FAAAABgD//wYA//8GAP//BgAAAAYAAAAGAP//BgAAAAYAAAAGAAAABgAAAAYAAAAFAAAABQAAAAYAAAAFAAAABQAAAAQAAAAEAAAABAAAAAQAAAADAAAAAwAAAAIAAAABAAEAAQABAAEAAQAAAAEAAAABAAAAAQD//wEA//8BAP7/AgD9/wIA/f8BAPz/AQD8/wIA/P8BAPz/AQD7/wIA+/8CAPv/AQD6/wEA+v8BAPr/AQD6/wEA+v8CAPr/AQD6/wEA+v8CAPr/AgD6/wEA+v8BAPr/AQD6/wEA+/8BAPv/AQD7/wEA/P8BAPz/AQD8/wEA/P8BAP3/AQD9/wAA/f8AAP3/AAD+/wAA/v8AAP3/AAD+/wAA//8AAP//AAD//wAA//8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAQAAAAEAAAACAAAAAgAAAAIAAAACAAAAAgAAAAIAAAACAAAAAgAAAAIAAQACAAEAAgABAAIAAQACAAEAAgABAAIAAQACAAEAAgACAAMAAQACAAEAAgABAAIAAgADAAEAAgABAAIAAQACAAEAAgABAAIAAQACAAIAAgABAAIAAQACAAIAAgABAAIAAQACAAEAAgABAAIAAQACAAAAAgAAAAMAAAADAAAAAwAAAAMAAAACAP//AwD//wMA//8DAP//AwD//wMA//8DAP7/AwD+/wMA/v8DAP7/AwD+/wIA/v8CAP7/AgD//wIA//8CAP7/AgD+/wEA//8BAP//AQD//wEA//8BAP//AQD+/wAA/v8AAP7/AAD+/////v////7////+/////////////v/+//7//v/9//7//f////3////9/////f/+//3//v/9//7//f/+//3//v/9//7//f////3////9//7//f////3////9//7//P/+//z//v/8//7//P/+//3////9/////f/+//3//v/9//7//v/+//7//v/+//7////+/////v////7////+////////////AAD//wAA//8AAP//AAAAAAEA//8BAP//AAAAAAAAAAABAAAAAQAAAAEAAAABAAAAAQAAAAEAAAABAAAAAQAAAAEAAAABAAEAAgABAAIAAQABAAIAAQACAAEAAgABAAIAAQACAAEAAgABAAIAAQACAAEAAwABAAMAAAADAAAAAwAAAAQAAAAEAAAABAAAAAQAAAAEAAAABAAAAAQAAAAEAAAABAAAAAQAAAAEAAAABAAAAAMA//8DAP//AwD//wQAAAADAAAAAwAAAAMAAAAEAAAAAwABAAMAAAADAAEAAgABAAIAAQACAAAAAgAAAAIAAQACAAEAAQAAAAEAAAACAAEAAgAAAAEAAAABAAAAAQAAAAAAAAAAAAAAAQAAAAAAAAAAAAAAAAD//wAA//8AAP//AAD//wAA//8AAP/////+/////v////7////+/////v////7//v/9//7//f/+//3//v/9//7//f/+//3//v/9//7//f/+//3//f/9//3//f/9//7//f/+//3//f/9//7//f/+//3//v/9//7//f////3////9/////f8AAP7/AAD+/wAA/v8AAP3/AAD+/wEA/v8BAP7/AgD+/wIA/v8CAP7/AwD+/wMA/v8DAP7/AwD+/wQA/v8EAP//BAD//wQA//8FAP//BQD//wUA//8GAP//BQD//wUA//8FAP//BgD//wYA/v8FAP7/BQD//wYA//8GAP//BQD//wYA//8GAP//BQD//wUA//8FAAAABQAAAAQA//8EAAAABAAAAAMAAAADAP//AwAAAAIAAAACAP//AQAAAAEAAAABAAEAAAABAAAAAQAAAAEA//8AAP//AAAAAAEA//8BAP//AQD//wEA//8BAP7/AQD+/wEA/v8BAP7/AgD9/wEA/P8BAP3/AgD9/wIA/f8CAP3/AgD9/wIA/P8CAPz/AwD9/wIA/P8CAPz/AgD9/wIA/f8CAP3/AwD9/wMA/P8CAPz/AwD8/wMA/P8DAPz/AgD8/wIA/P8CAPz/AgD8/wIA/P8CAPv/AgD8/wIA/P8CAPv/AgD7/wIA+/8DAPv/AgD7/wIA+/8BAPv/AgD8/wEA/P8BAPz/AQD8/wAA/P8AAPz/AAD8/wAA/P8BAPz/AAD8/wAA/P8AAPz/AAD9/////f////3/AAD9/wAA/v8AAP/////////////+//////8AAP//AAD//wEA//8BAP//AgD//wIA//8CAP//AgD//wMA/v8DAP//BAD//wQA//8EAP//BAD//wUA/v8FAP7/BgD//wYA//8GAP//BgD//wcA//8HAP//BwD//wcAAAAIAAAABwAAAAcAAAAHAP//BwD//wcAAAAHAAAABwAAAAYAAAAGAAAABgAAAAYAAAAGAAAABQAAAAUAAQAFAAAABAAAAAQAAAAEAAEAAwAAAAMAAAADAAAAAgAAAAEAAAABAAEAAQABAAAAAAAAAAAA//8BAP//AQD+/wAA/f8AAP3/AQD9/wEA/f8BAP3/AQD8/wAA/P8BAPz/AQD8/wEA/P8BAPz/AQD8/wAA+/8AAPv/AAD8/wAA/P////v/AAD8/wAA/P8AAPv////7////+/8AAPv/AAD8/////P////z/AAD8/wAA/P8AAPz/AAD8/wAA/f////3/AAD9/wAA/f8AAP3////9/////f////3////+//7///////7////+/////v/+//7//v/+//7//v/+/////v////7////+/////v////7////+/wAA/v8BAP7/AAD+/wAA/v8AAP7/AAD+/wAA/v8AAP7/AAD9/wAA/v8BAP7/AQD+/wEA/v8BAP7/AQD+/wIA//8CAP//AgD//wIA//8CAP//AgAAAAIAAAADAAAAAwAAAAMAAAAEAAEABAABAAQAAgAEAAEABAABAAUAAgAFAAIABQACAAUAAgAFAAIABQADAAUAAwAFAAMABQADAAUABAAFAAQABQAEAAUABAAFAAQABQAEAAQABAAEAAQABAAEAAQABAAEAAQAAwAEAAMABAADAAQAAwAEAAIABAACAAQAAgAEAAEABAAAAAMAAAADAAAAAwAAAAQAAAADAP//AgD//wIA//8CAP7/AgD+/wEA/v8BAP3/AQD9/wEA/P8BAPz/AQD8/wAA/P////z/AAD7/wAA+/////v////7////+/////r//v/7////+/////v////7//7/+/////v////7//7/+//+//v//v/7//7/+//+//v//v/8//7//P/9//v//f/7//7//P/+//z//f/8//3//f/9//3//f/9//3//f/9//3//v/9//3//f/9//7//f/+//3//v/9//7//f/+//3////+/////f////3////9/////f////3////9/////f8AAP3/AAD+/wAA/v8AAP3/AAD9/wAA/f8AAP3/AQD9/wEA/f8BAP7/AQD9/wEA/v8CAP7/AgD+/wIA/v8CAP//AgD//wMA//8DAP//AwD//wMAAAADAAAAAwD//wMAAAAEAAAABAABAAQAAAAEAAEABAABAAUAAgAFAAIABQACAAQAAgAFAAIABgACAAYAAwAGAAMABQACAAYAAgAGAAMABQADAAYABAAGAAQABQADAAUABAAEAAQABQAEAAUABAAFAAQABAADAAMAAwAEAAQABAAEAAQAAwADAAMAAwADAAIAAwACAAIAAgACAAIAAgABAAEAAQABAAAAAgAAAAIAAAACAP//AgD//wEA//8BAP7/AAD+/wEA//8BAP7/AAD+/wAA/f////7/AAD9/wAA/f8AAP7////9/////P////z////9/////P////z////8//7//P////z////8/////P////z////9/wAA/f8AAPz////8/wAA/P8AAPz/AAD9/////f8AAP3////9/////f8AAP3/AAD9/wAA/f8AAP3/AAD+/wAA/v8AAP7/AAD+/wEA/v8BAP7/AAD+/wAA/v8AAP//AAD//wAA//8AAP//AAD//wAAAAAAAAAA//8AAP//AAAAAAAAAAAAAP//AAD//wEA//8BAP//AAD//wAA//8AAP//AQD//wAA/v8BAP7/AgD+/wIA/v8BAP//AQD//wIA//8BAP//AQD//wIA/v8CAP7/AgD+/wIA/v8CAP//AgD//wIA//8CAP//AwD//wIA//8CAAAAAgAAAAIAAAABAAAAAgAAAAIAAAACAAAAAgAAAAIAAAACAAEAAgACAAIAAQACAAEAAQABAAEAAQACAAEAAgABAAEAAQABAAEAAQABAAEAAgAAAAIAAQACAAEAAgAAAAIA//8CAP//AgAAAAEAAAACAP//AgD//wIA//8CAP//AQD//wEA//8BAP//AQD+/wEA/v8AAP7/AAD9/wAA/f8AAP7/AAD+/wAA/f////3////9/////f////3////9/////P////3////9/////f/+//3//v/8//7//f/+//3////+/////v////3//v/+/////v////7//v/+/////v/////////+//7///////////8AAP//////////////////AAAAAAAA//8BAP//AQAAAAEA//8BAP//AgD//wIA//8CAP//AgAAAAMAAQADAAEAAgAAAAMAAAADAAEAAwABAAMAAQADAAAAAwAAAAQAAAAEAAAABAAAAAUAAAAEAAAABAAAAAQAAAAEAAAABAD//wQA//8EAAAABAABAAUAAQAEAAAAAwAAAAMAAAADAAAAAwAAAAMAAAADAAAABAAAAAMAAAADAAAAAgAAAAMAAAACAAAAAgAAAAEAAAABAAAAAgABAAIAAQACAAEAAQABAAEAAQAAAAEAAAACAAAAAgAAAAIA//8CAP//AgD//wIA//8CAP//AwD//wMA//8DAP7/AwD9/wMA/v8DAP7/AwD9/wMA/f8EAP3/AwD9/wMA/P8DAP3/AwD9/wMA/f8DAP3/AwD8/wIA/P8CAPz/AwD8/wMA/P8CAPz/AgD8/wIA/P8BAPz/AQD8/wEA/P8BAP3/AAD9/////P////3/AAD9/////f////z////8/////f/+//7//v/+//3//v/9//7//f////3////9/////f////z/AAD8/wAA/P8AAPz/AAD8/wAA/P8BAPz/AAD8/wAA/P8BAPz/AgD8/wIA/P8CAPz/AgD9/wMA/f8CAP3/AgD9/wMA/f8DAP7/AgD+/wIA/f8CAP3/AgD+/wIA//8CAP//AgD//wIA//8DAP//AgD//wIA//8CAP//AwD//wMA//8CAAAAAgAAAAIAAAADAAAAAgAAAAIAAAABAAAAAQAAAAIAAAACAAAAAQAAAAEAAAABAAAAAQAAAAIAAAABAP//AAAAAAAAAAAAAAAAAAD//wAAAAABAAEAAQAAAAEAAAAAAAAA//8BAAAAAAAAAAAAAAABAAAAAQD//wEA//8BAP//AQD//wEA//8BAP//AQD+/wEA/v8BAP7/AgD+/wIA//8BAP//AgD//wIA//8DAP7/AwD+/wIA/v8CAP7/AgD+/wIA/v8CAP3/AgD+/wIA/v8CAP7/AgD+/wIA/v8CAP7/AgD+/wMA/v8CAP3/AgD9/wIA/v8CAP7/AwD+/wMA/f8CAP3/AgD9/wIA/f8CAP7/AgD+/wIA/v8CAP7/AgD+/wIA/v8BAP7/AQD+/wEA//8BAP//AAD+/wAA//8AAP//AAAAAAAAAAAAAAAA//8AAP//AAD//wEA//8BAAAAAQD//wEA//8CAP7/AgD//wIA//8CAP7/AgD+/wIA//8DAP//AwD+/wMA/v8DAP//AwD//wQA/v8EAP//BAAAAAQA//8EAP//BAAAAAUAAAAFAAAABQAAAAUAAAAFAAAABQABAAUAAQAFAAAABAAAAAQAAQAEAAEABAAAAAQAAAAEAAEAAwABAAMAAQADAAEAAwAAAAMAAAADAAEAAwABAAIAAAACAAAAAgAAAAEAAAABAP//AQD//wEAAAAAAP//AQD//wAA//8AAP///////wAA//8AAP//AAD//////v////7//v/+//7//v/+//7//v/9//7//v/+//7//v/+//7//f/+//3//v/+//3//v/9//7//f/9//3//v/9//7//f/+//3//f/9//3//f/+//3//v/9//7//f/9//3//v/9//7//f/+//3//v/9//7//f////z////8//7//f/+//3////9/////f////3/AAD8/////P////3/AAD9/wAA/f8AAPz/AAD9/wEA/f8BAP3/AQD9/wAA/f8AAP3/AQD+/wEA/v8AAP3/AQD+/wIA/v8BAP7/AQD+/wIA/v8BAP//AQD//wEA/v8BAP7/AQD//wIAAAABAAAAAQAAAAEAAAACAAEAAgABAAEAAQABAAEAAQACAAEAAgABAAIAAQACAAIAAgACAAMAAQADAAEAAwABAAMAAQADAAEAAwABAAQAAQAEAAIABAADAAQAAwAEAAIABAACAAQAAwAEAAMABAACAAQAAgAEAAMABAACAAQAAgAEAAMABQACAAQAAgAEAAMABAACAAMAAwAEAAMAAwADAAMAAwACAAMAAwACAAMAAwACAAIAAgACAAEAAgABAAIAAgACAAEAAgABAAIAAQABAAAAAQAAAAEAAAABAAAAAQAAAAEAAAABAP//AAD//wAA/v8AAP7/AAD+/wAA/v8AAP7/AAD+/wAA/v////3////9/wAA/v////7////+/////v/+//3//v/9/////f////3//v/9//7//f/+//3////9/////P/+//3//v/9//7//f/+//3////+//7//v/+//3//v/9/////v////7//v/9//7//f/+//7//f/+//3//f/9//7//v////3////+/////v////3////9//7//f////3/AAD9/wAA/P////z////9/wAA/f8AAP3/AAD8/wAA/f8BAP3/AQD9/wEA/P8BAP3/AQD9/wIA/f8CAP3/AgD9/wIA/f8CAP3/AgD9/wIA/f8CAP7/AwD+/wMA/v8DAP7/AgD+/wMA//8DAP//BAD//wQA//8EAAAAAwAAAAMAAAAEAAAABAABAAQAAQAEAAEABAABAAQAAgAEAAEABAACAAQAAgAEAAMABAACAAMAAwADAAMAAwAEAAQAAwAEAAMAAwAEAAIABAACAAMAAwADAAIABAACAAQAAgAEAAEABAABAAQAAQAEAAEABAABAAMAAAADAP//AwAAAAMAAAADAP//AwD+/wMA/v8DAP//AgD+/wIA/f8CAP3/AwD+/wIA/f8CAP3/AgD+/wIA/f8CAP3/AgD9/wIA/f8BAPz/AQD8/wAA/P8BAP3/AQD9/wEA/P8BAPz/AQD8/wAA/P8AAPz/AAD8/wAA/P8AAP3/AAD9/////P////z/AAD9/wAA/f////3////9/////f////3/AAD+/////f8AAP3/AAD9/wAA/v8AAP//AAD+/wAA/v8AAP7/AAD//wAA//8AAP//AAAAAAAAAAAAAP////////////8AAP//AAD/////AAD+/wAA//8BAP//AAD+/wAA//8AAP//AAD//wAA//8BAP//AgD//wIA/v8BAP7/AQD+/wIA/v8DAP7/AgD+/wIA/v8CAP7/AgD+/wMA/v8DAP7/AwD//wIA//8CAP//AgD//wMA//8DAP//AwD//wMA//8DAP//AwAAAAMA//8DAP//AwAAAAMAAQADAAEAAgABAAIAAQADAAAAAgAAAAIAAQACAAIAAgABAAIAAQACAAEAAgACAAIAAgABAAIAAQACAAEAAgABAAIAAAABAAAAAQAAAAEAAAACAAAAAgD//wEA//8BAP//AQD+/wEA/v8BAP7/AQD+/wEA/v8BAP7/AAD9/wAA/f8AAP3/AAD9/////P8AAPz/AAD9/////P////z////8/////P////z//v/9//7//f////3////9//7//f/+//3//v/9//7//f/+//3//v/+//7//v/+//7//v/+//7////+//7//v/+/////////wAA/////////////wAA//8BAAAAAQD//wEA//8CAAAAAgAAAAIAAAACAAAAAgAAAAMAAAADAAEAAwABAAMAAAADAAAAAwABAAMAAQAEAAEABAABAAQAAQAEAAEABAABAAQAAQAEAAEABAABAAQAAQAEAAEABAABAAQAAQAEAAAAAwABAAMAAQADAAAAAwAAAAMAAAADAAAAAwAAAAMAAAACAP//AgD//wIAAAACAAAAAgAAAAIAAAACAAAAAQAAAAEAAAABAAAAAQAAAAEAAAABAAAAAQAAAAEAAAABAAAAAAAAAAAAAAAAAAEAAAABAP//AQD//wEAAAACAAAAAgAAAAIA//8CAP//AgD+/wIA//8CAP//AgD+/wIA/v8CAP7/AwD+/wMA/v8DAP7/AgD+/wIA/v8CAP7/AgD+/wIA/f8CAP3/AgD9/wIA/P8CAPz/AQD8/wEA/f8CAP3/AQD8/wEA/P8BAPz/AQD8/wEA/P8AAPz/AAD8/wAA+/8AAPv/AAD7/////P////z////7////+/////v//v/7//7/+//+//v//v/8//7//P/+//z//v/8//7//P/9//3//f/9//3//f/+//3//v/9//7//v/+/////v/+//7////+/////f////7////+/wAA/v8AAP7/AAD+/wEA/v8BAP7/AgD//wIA//8CAP//AwD//wMA//8DAP//AwD//wQA//8EAAAABAABAAQAAAAEAAAABAAAAAQAAAAEAAAABQAAAAUAAAAFAAAABQAAAAUAAAAFAAAABQAAAAQAAAAEAAEABAABAAQAAAAEAAAABAAAAAMAAAADAAAAAwD//wMAAAADAAAAAgD//wIA//8CAP//AgD//wIA//8CAAAAAQAAAAEAAAAAAP//AQD//wEA//8AAP//AAD//wAA//8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP//AAD//wEA//8BAP//AAD//wAA//8BAP//AAD//wEAAAABAAAAAQD//wEAAAABAAAAAQD//wEAAAABAAAAAQAAAAEAAAABAAAAAQAAAAEAAAABAP//AQD//wEAAAABAAAAAQD//wEA//8AAP//AAD//wAA//8BAP//AAD//wAA//8AAP//AAD//wEA/v8AAP//AAD///////8AAP//AAD//wAA/v////7/AAD+/wAA//8AAP//AAD+/wAA//8AAP//AAD+/wAA/v8AAP///////wAA//8AAP//AQD//wEA//8BAAAAAAD//wEA//8BAP//AgAAAAIAAQABAAAAAgAAAAIAAAADAAEAAgABAAIAAQACAAEAAgABAAIAAQACAAIAAwABAAMAAQADAAIAAgACAAMAAgADAAIAAwACAAMAAwADAAIAAwACAAMAAgADAAMAAgACAAIAAgACAAIAAgACAAIAAgABAAMAAQADAAEAAgABAAIAAAACAAAAAgAAAAEAAAACAP//AQD//wEA//8BAP//AQD+/wAA/v8AAP7/AAD+/wAA/f////3////9/////f////z//v/8//7//f/+//3//v/8//3//P/9//z//v/8//3//f/9//3//f/8//3//P/8//z//f/8//z//P/8//z//P/8//z//f/8//3//P/9//z//P/8//3//P/9//z//v/8//7//P/+//z//v/9//7//f/+//3//v/9//7//f/+//3//v/9/////f////3////9/////f////7//v/9//7//v////7//v////7//v/+//7//v/+/////v////////////7////+/////v//////AAD//wAA//8AAP//AAD//wEA//8BAP//AQD//wEAAAABAAAAAQABAAIAAAADAAAAAwAAAAMAAQADAAEAAwABAAMAAgAEAAIABAACAAQAAgAEAAMABQADAAUAAwAFAAMABQAEAAYABQAGAAQABgAEAAYABQAGAAUABgAFAAYABQAGAAUABgAFAAYABQAGAAUABgAFAAYABQAGAAUABgAFAAYABQAGAAUABgAFAAUABQAFAAUABQAFAAUABAAFAAQABQAEAAQABAAEAAQABAADAAMAAwADAAMAAwACAAIAAgACAAIAAgABAAEAAQABAAEAAQABAAEAAAAAAAAAAAAAAP//AAD//wAA///////////+/////v////7////9//7//f/+//3//v/8//7//P/+//z//v/8//7//P/+//v//v/7//7/+//+//v//v/6//3/+v/+//r//v/6//7/+v/+//r//v/6////+v////r//v/6//7/+v////r////6//7/+v/+//r////6////+/////v////7//7/+/////v////7////+/////z////8/////P/+//z//v/9//7//f/+//3//v/9//3//f/+//7//v/+//3//f/9//3//P/+//3//v/9//7//f////3////9/////f8AAP3/AAD9/wAA/f8AAP3/AQD9/wEA/f8BAP3/AQD8/wEA/f8CAP7/AgD+/wIA/v8CAP7/AwD+/wMA/v8DAP//AwD//wQA//8EAP//BAD//wQAAAAEAAAABAABAAUAAQAFAAEABQABAAUAAQAFAAIABQACAAUAAgAFAAIABQACAAUAAwAFAAMABQADAAUAAwAFAAMABQADAAUAAwAFAAQABAADAAQAAwAEAAMABAADAAQAAwADAAMAAwADAAMAAwADAAMAAwADAAIAAwACAAIAAgACAAEAAQABAAEAAQABAAEAAQABAAEAAAABAAAAAQAAAAAAAAAAAP//AQD//wAA//8AAP/////+/wAA/v8AAP7/AAD+/wAA/v8AAP7/AAD+/////v////7/AAD+/wAA/f8AAP3/AAD9/wAA/f8BAP7/AQD+/wEA/f8BAP7/AQD+/wAA/f8BAP3/AQD9/wIA/v8BAP3/AQD9/wIA/v8CAP7/AQD+/wIA/v8CAP7/AgD+/wIA/v8CAP7/AgD+/wIA/v8CAP7/AwD+/wMA/v8CAP7/AgD+/wEA/v8BAP7/AgD+/wIA/v8BAP7/AQD+/wEA/v8AAP7/AAD+/////v////7/AAD+/wAA///////////////////////////////////+/////v8AAP7/AAD+/wAA/f8AAP7/AQD+/wEA/v8AAP3/AQD9/wEA/f8BAP3/AQD9/wIA/f8CAP3/AgD+/wMA/v8DAP7/AwD+/wMA/v8DAP7/AwD+/wQA//8DAP//AwD//wQA//8EAP//BAD//wQA//8FAP//BAD//wQA//8EAP//BAAAAAQAAAAEAP//BAAAAAQAAAAEAP//BAD//wMAAAADAAAAAwD//wMA//8DAP//AgAAAAIAAAACAP//AQD//wEA//8BAP//AAD//wAA//8BAP//AAD+/wAA//8AAP/////+/////v////7////+/////v///////v////7////+//7//f////3////9/////f////3////9/wAA/f8AAPz/AAD9/////f8AAP3/AAD9/wEA/f8BAPz/AQD9/wIA/f8CAP3/AgD9/wIA/f8CAP3/AwD9/wMA/f8DAP3/BAD9/wQA/v8DAP3/BAD9/wUA/f8FAP7/BAD+/wMA/v8DAP7/AwD+/wQA//8EAP//BAD//wQA//8EAP//BAD+/wQA/v8DAP//AwD//wMA//8DAP7/AwD//wIA//8CAP//AgD//wIA//8CAP//AQD//wEA//8BAP//AAD+/wAA/v8AAP//AAD//////////wAA//8AAP//AAD//wAA//8AAP//AAD//wAA//8AAP//AQD//wEA//8BAP//AQD//wEA//8CAP7/AgD+/wIA//8CAP//AgD+/wMA/v8DAP//AgD//wIA//8DAP//AwD//wMA//8DAP//AwD//wMA//8DAP//AwAAAAMA//8EAP//BAD//wQA//8EAP//BAD//wMA//8DAP//AwD//wMA//8DAP//AwD//wMA//8DAP//AwD+/wIA//8CAP//AgD//wIA/v8BAP7/AQD+/wEA/v8BAP7/AQD+/wAA/v8AAP7/AAD+/wAA/f////7////+/wAA/v////7////9/////f////7//v///////////////v/+//7//v/+//7//v/+//7////+/////v8AAP7/AAD+/wAA/v8AAP7/AAD//wEA//8BAP//AQD+/wIA/v8CAP//AgD//wIA/v8DAP//AwAAAAMAAAADAAAAAwD//wMAAAADAAAAAwAAAAQAAAAEAAAABAAAAAQAAAAEAAEABAABAAQAAAAEAAAABAAAAAMAAAADAAEAAwABAAMAAQADAAEAAgABAAIAAQACAAEAAgAAAAEAAAABAAAAAgAAAAEA//8BAAAAAAAAAAAA//8AAAAAAAAAAP//AAD//wAA//8AAP////////////////7/AAD+/wAA/v////7////+/////v////7////+/////v////7////+/wAA/v8AAP7/AAD+/////v8AAP7////+/////v////7/AAD+/////v8AAP7/AAD+////////////AAD+/wAA/v///////////wAA//8AAP//AAD//wAA/v8AAP7/AQD//wAA//8BAP//AQD//wEA/v8AAP7/AQD+/wEA//8BAP//AQD//wEA//8BAP//AQD+/wEA/v8BAP7/AQD+/wEA/v8BAP//AQD+/wAA/v8AAP//AQD//wEA/v8BAP//AQD//wEAAAABAAAAAQD//wEA//8BAAAAAQAAAAEAAAAAAAEAAQABAAEAAQABAAEAAAACAAEAAgABAAIAAQACAAAAAgABAAMAAQADAAEAAwAAAAMAAAAEAAAABAAAAAQAAAAEAAEABQABAAUAAAAFAAAABQABAAUAAAAFAAAABQAAAAUAAAAFAAAABQAAAAUAAAAFAAAABAAAAAQAAAAEAAAABAAAAAQAAAAEAP//AwD//wMA//8DAP//AwD//wMA//8CAP//AgD//wIA//8BAP7/AQD//wEA//8AAP7/AAD+/wAA//8AAP////////7////+/////v/+//7//v/+//7//v/+//7//v/+//7//f/+//3//v/9//7//f/+//3//v/9//7//P/+//3////8/////P/+//z//v/8//7//P/+//z////9/////P////z////8/////f8AAP3/AAD9/wAA/f8AAP3/AAD9/wAA/f8AAP3/AAD9/wEA/f8AAP3/AAD9/wEA/f8BAP3/AQD9/wEA/f8BAP3/AQD9/wIA/f8CAP3/AQD9/wEA/v8BAP7/AgD+/wIA/v8CAP7/AgD+/wIA/v8CAP7/AgD+/wMA/v8CAP7/AgD//wMA//8CAP//AgD//wMA//8DAP//AgAAAAMAAAADAAAAAgAAAAIAAQACAAEAAwABAAMAAQACAAIAAgACAAIAAwADAAMAAwADAAIAAwADAAMAAwAEAAMABAADAAQAAwAFAAIABQACAAUAAgAFAAIABQACAAUAAgAFAAIABQACAAUAAQAFAAEABgABAAUAAQAFAAEABQABAAUAAAAFAAAABgAAAAUAAAAFAP//BQD//wUAAAAFAAAABAD//wQA//8DAP//AwD+/wMA/v8DAP7/AwD+/wIA/f8CAP3/AQD9/wEA/f8BAP3/AQD9/wAA/P8AAPz/AAD8/wAA/P////z//v/8/////P////z//v/8//7//P/9//z//f/8//3//P/9//z//f/8//3//P/8//z//P/8//z//f/8//3//P/9//z//f/8//3//P/9//z//f/8//7//P/+//z//v/8//7//P/+//z////8/////P////3////9/////f////3/AAD9/wAA/v8AAP7/AAD9/wAA/f8AAP7/AQD//wEA/v8BAP7/AAD+/wEA//8BAP//AQD//wEA//8BAP//AQAAAAEA//8CAP//AQD//wEAAAACAAAAAQAAAAEAAAABAAAAAgAAAAIAAAACAAAAAgAAAAIAAQACAAEAAgABAAIAAQACAAEAAwABAAIAAQACAAEAAgABAAIAAQADAAEAAwABAAMAAQADAAEAAwABAAMAAQADAAEAAwABAAMAAQADAAEAAwACAAMAAgADAAEAAwABAAMAAgADAAIAAwACAAMAAQADAAIAAwACAAMAAQADAAIAAwACAAIAAgACAAIAAgACAAMAAgACAAIAAgACAAEAAgABAAIAAQACAAEAAgAAAAIAAQACAAAAAQAAAAEAAAABAAAAAgD//wEA//8BAP//AAD//wEA//8BAP//AAD+/wAA/v8AAP7////9/wAA/f8AAP3/AAD9/wAA/f8AAP3////9/////f////3////9/////f////3////9/////f////3////9//7//f////3////9/////v////7////+/////v////7////+/////f////7//////wAA//8AAP//AAD//wAA//8AAP///////wAA//8AAP//AQD//wEA//8BAAAAAQAAAAEA//8BAAAAAQAAAAEAAAABAAAAAgAAAAEAAAABAAAAAgAAAAIAAAABAAAAAQAAAAIAAAACAAAAAgAAAAEAAAABAAAAAQAAAAEAAAABAAAAAQABAAEAAAAAAAAAAQAAAAEAAAABAAAAAQAAAAEAAAAAAAEAAAABAAAAAQAAAAEAAAABAP//AQD//wEAAAABAAAAAgD//wIA//8BAP7/AQD+/wEA//8BAP//AQD+/wIA/v8CAP7/AwD//wIA/v8CAP7/AgD+/wIA/v8CAP//AgD//wIA/v8CAP7/AgD//wIA//8CAP7/AQD+/wEA//8BAP//AQD+/wEA//8BAP//AQD//wEA//8BAP//AAD//wEA//8BAP//AAD///////8AAP//AAD//wAA///////////////////+//////////7////+/////v////7////+/////v////7////+/////v/+//7//v/+//7//v////7////+/////v////7////+/////v////7////+//////8AAP//AAD//wAA//8AAP////////////8AAP//AAD//wEA//8BAP//AQD//wEA//8BAAAAAQAAAAEAAAACAAAAAgD//wIAAAACAAAAAgAAAAIAAAADAAEAAwABAAMAAQADAAEAAwABAAMAAQADAAEAAwABAAMAAQADAAAAAwAAAAMAAQADAAEAAwABAAMAAAADAAAAAwAAAAMAAAADAAAAAwAAAAMAAAADAAEAAwABAAMAAQACAAAAAgAAAAIAAAACAAAAAgAAAAEAAQABAAAAAQAAAAEAAAABAAEAAQAAAAEAAAABAAAAAQAAAAEAAQABAAEAAAABAAAAAQAAAAEAAAABAAAAAQAAAAEAAAABAAAAAQAAAAIAAAABAAAAAQAAAAEAAAACAAAAAgAAAAIAAAACAAAAAgAAAAIAAAACAAAAAgAAAAIA//8CAP//AgD//wIA//8CAP//AQD//wEA//8CAP//AgAAAAIA//8BAP//AQD//wEA//8BAP7/AQD+/wEA/v8BAP7/AQD+/wEA/v8AAP7/AAD9/wAA/f8AAP3/AAD9/wAA/f8AAP3////8/////P8AAPz////8/////P////z////8/////P/+//z////8/////P////z////8//7//P/+//3////8/////P/+//z////9/////f////3////9/////v/+//7//v/+/////v8AAP7///////////8AAP////8AAP//AAD//wAA//8AAP//AQD//wEAAAABAAAAAgAAAAIAAAACAAAAAgAAAAMAAAADAAAAAwAAAAMAAAADAP//BAD//wUAAAAFAAAABAD//wQA//8EAP//BAD//wQA//8EAP//BAD//wQA//8EAP//BAD//wQA//8EAP//AwD//wMA//8EAP//AwD//wMA//8DAP//AwD+/wIA//8CAP//AwD//wIA/v8BAP7/AgD//wIA//8BAP//AQAAAAEA//8AAP//AAD/////AAAAAP//AAD//wAAAAD/////////////AAD//wAA/v////7////+/wAA/v8AAP7////+/wAA/f8AAP3////+/////v////3/AAD9/wAA/v8AAP7/AAD+/wAA/v8AAP7/AAD9/wAA/f8AAP3////9/////f8AAP3/AAD+/wAA/v8AAP7/AQD+/wAA/v8AAP7/AQD+/wEA/v8AAP//AQD//wAA//8AAP7/AQD+/wEA//8BAP//AgAAAAIAAAABAAAAAQAAAAIAAAACAAEAAgABAAIAAAACAAEAAgABAAIAAQADAAEAAwABAAMAAQADAAEAAwABAAMAAQADAAEAAwACAAMAAgAEAAIAAwACAAMAAgAEAAIABAACAAQAAwADAAMAAwADAAMAAwADAAMAAgADAAIAAwACAAMAAgAEAAMABAACAAMAAQADAAEAAwACAAMAAQADAAEAAwAAAAMAAAADAAAAAwAAAAIAAAACAAAAAgD//wIA//8CAP//AgD//wIA//8CAP7/AgD+/wIA/v8BAP7/AQD+/wEA/v8AAP7/AAD+/wEA/v8AAP7////+/////v8AAP3////9//7//v/+//7//v/+//7//v/+//7//f/+//3//v/9/////f/+//3//v/9//7//P/+//z//v/8//7//P/+//z//v/9//7//P/+//z////7/////P////z//v/8//7//P/+//z////8//7//P/+//3////9/////f////3////9//7//f////3////9/////f////3////9/////f////7////+/////v////7/////////////////////////////////////////AAD//wAA//8AAP//AAAAAAAAAAAAAP//AAD//wEA//8BAAAAAQAAAAEAAAABAP//AQAAAAIAAQACAAEAAQAAAAEAAQACAAEAAgAAAAIAAQACAAIAAgACAAIAAQACAAIAAgACAAMAAgACAAEAAgACAAIAAgADAAMAAwACAAIAAgADAAIAAgADAAIAAgACAAIAAwADAAMAAgACAAIAAgACAAIAAwADAAMAAwACAAIAAgADAAMAAwADAAMAAgACAAEAAgABAAIAAgACAAIAAwABAAMAAQADAAEAAgABAAIAAAADAAAAAgAAAAIAAAACAAAAAgAAAAIAAAACAAAAAgAAAAEAAAABAP//AQD//wEAAAABAAAAAQD//wEA//8BAP//AAD+/wAA/v8AAP//AAD+/wAA/v8AAP7/AAD+/////v8AAP///////////////////////////////////v////7////+/////////////////wAA/v8AAP7/AAD//wAA/v////7//////wAA//8AAP7////+/////v8AAP7/AAD+/////v//////AAD//wAA//8AAP7/AAD//wAA//8AAP7/AAD+/wAA//////7//////wAA//8AAP//AAD+//////8AAP//AQD//wEA//8AAP//AAD//wAA/v8AAP7/AQD//wEA//8AAP//AAD//wAA//8AAP//AAD//wAA//8AAP//AAD//wAA//8AAAAAAQAAAAEA//8BAP//AQD//wEA//8BAP//AQD//wEA//8BAP//AgAAAAEA//8BAP//AgAAAAIA//8CAP//AgD//wIAAAACAP//AgAAAAIAAAACAAAAAQAAAAEAAAACAAAAAgAAAAIAAAACAAAAAQABAAEAAQACAAEAAgABAAEAAQABAAEAAQABAAAAAQAAAAIAAQABAAAAAQAAAAIAAAACAAAAAgAAAAIAAAABAP//AQD//wEA/v8BAP//AQD//wEA//8BAP//AQD//wEA//8BAP7/AQD+/wAA/v8AAP7/AAD+/wAA/v8AAP3/AQD9/wEA/v8BAP3/AAD9/wAA/f8AAP7/AAD9/wAA/f8AAP7/AAD+/wAA/v////3////9/wAA/f8AAP7/AAD+/////v////7////+/wAA/v8AAP7////+/wAA/v8AAP7/AAD+/////v8AAP7////+/////v8AAP//AQD//wAA/v8AAP7/AQD//wEA//8AAP7/AQD//wEA//8AAP//AAAAAAAAAAABAAAAAQD//wIAAAACAAAAAQAAAAIAAAACAAEAAgAAAAIAAQACAAEAAQABAAEAAQACAAEAAgABAAIAAQABAAEAAQACAAEAAgABAAIAAQACAAEAAgABAAIAAAADAAAAAwAAAAMAAAADAAEAAwABAAMAAAADAAAAAwAAAAMAAAADAP//BAAAAAMAAAADAAAAAwAAAAQAAAAEAAAABAAAAAQAAAAEAAAABAAAAAMAAAADAAAAAwAAAAMAAAADAP//AwAAAAIAAAACAAAAAgAAAAIAAAACAP//AQAAAAEA//8BAP//AAAAAAAAAAABAAAAAAAAAAAAAAAAAAAAAAD///////////////////////////////////7////+/////v////7////+//7//v/+//7//v/+/////v/+//3//v/9//7//v/+//7//v/+//7//v/+//7//v/+//3//v/9//7//f/+//7//v/+//7//v/+//7////+/////v////7////+/////v/+//7//v/+/////v////7////+/////v//////AAD//wAA//8AAP//AAAAAAAAAAAAAAAAAAAAAAEAAAABAAEAAAABAAAAAQAAAAIAAAABAAAAAQAAAAIAAAACAAAAAgAAAAIAAAACAAAAAgAAAAMAAAADAAAAAwAAAAMAAQADAAAAAwAAAAMA//8DAAAAAwAAAAMAAQADAAAAAwAAAAMAAAADAAAAAwABAAMAAAADAP//AwD//wMAAAACAAAAAgAAAAIAAAACAAEAAgABAAIAAAACAAAAAgABAAIAAAABAAAAAQABAAEAAQABAAAAAQAAAAEAAAAAAAEAAAAAAAAAAAABAAEAAQABAAAAAQABAAEAAQABAAAAAQAAAAEAAQABAAEAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA//8AAP//AAD//wAA/v8AAP//AAD//wAA//8AAP//AAD//wAA//8AAP//AAD//wAA//////7///////////////7////////////+/////v////7////+/////v////7//v/9/////f////3/AAD9/wAA/f////3/AAD9/wAA/f8AAPz/AAD8/wEA/P8AAPz/AAD8/wAA/f8AAPz/AAD7/wEA/P8BAPz/AQD8/wEA/P8BAP3/AQD9/wEA/f8CAP3/AQD9/wEA/v8CAP7/AgD+/wIA/f8CAP7/AgD+/wIA/v8BAP//AQD//wIAAAACAP//AgAAAAIAAAACAAEAAQABAAEAAQABAAEAAQABAAEAAgABAAIAAQACAAEAAgABAAMAAAADAAEAAwABAAMAAAADAAAAAwAAAAMAAAADAAAAAwAAAAMAAQADAAEAAwABAAMAAQADAAEAAwABAAMAAAADAAAAAwABAAMAAQADAAAAAwAAAAMAAAADAAAAAgAAAAIAAAACAAAAAgAAAAIAAAACAAAAAgAAAAIAAAACAAAAAQAAAAEAAAABAAAAAQAAAAEAAQABAAAAAQAAAAEA//8BAP//AQAAAAEA//8BAAAAAQAAAAEAAAABAP//AQD//wIAAAABAP//AQD//wEA//8BAP//AQD//wEA//8BAP7/AQD+/wAA//8AAP//AQD//wEA/v8AAP7/AAD//wAA//8AAP7/AAD+/////v////7/AAD+/wAA/v8AAP7/AAD+/wAA///////////+/////v///////v////7////+/////v////3/AAD+/wAA/v8AAP7/AAD+/wAA/f8AAP3/AAD9/wAA/f8BAP3/AQD8/wEA/P8AAP3/AQD9/wEA/f8BAP3/AQD+/wEA/v8CAP7/AQD9/wEA/v8BAP7/AgD//wIA//8BAP7/AQD//wIA//8CAP//AgAAAAEAAAABAP//AAD//wEAAAABAAAAAAAAAAAAAAABAAAAAQAAAAEAAQABAAIAAQABAAAAAQABAAEAAQABAAAAAQAAAAIAAAACAAEAAQABAAEAAAABAAAAAgAAAAIA//8CAAAAAgAAAAIAAAACAAAAAgAAAAIAAAACAAAAAQAAAAEAAAAAAAAAAAAAAAEAAAABAAAAAAAAAAAAAAAAAAAA/////wAA//8AAAAAAAAAAAAAAAAAAP////////////////////////////////////////////////////////7////+//7////+/////v////7////////////////////////////+/////v////7////+/////v////7////+/////v////7////+//////8AAP//AAD//wAA//8AAP7/////////AAAAAP//AAD/////AAAAAAAAAAAAAAAAAAAAAAAAAQABAAEAAQAAAAAAAQAAAAEAAAACAAEAAgABAAEAAQABAAEAAQABAAEAAgACAAIAAgABAAIAAgACAAIAAgADAAIAAgACAAMAAwACAAMAAgACAAMAAgACAAIAAgACAAMAAwADAAIABAACAAQAAgAEAAIAAwACAAMAAgADAAIAAwACAAMAAgAEAAIABAACAAMAAQAEAAIABAACAAMAAQADAAEAAwABAAMAAQADAAEAAwABAAMAAQACAAAAAwAAAAIAAQABAAEAAQAAAAIAAAACAAAAAQAAAAEAAAABAAAAAAAAAAAAAAAAAAAAAAAAAP//AAD//wAA/v8AAP7///////////////7////+/////v////7////+/////v////7//v/+/////v////3//v/9//7//f////7//v/9//7//f/+//3//v/9//7//f/+//3//v/9//7//f/9//3//v/+//7//f/9//7//f/+//3//f/9//3//v/+//7//v/+//7//f/+//7//f/+//7//v/+//7////+//7//f/+//7////+/////v/+//7//v/+/////v/+/////v////7////+////////////AAD+//////8AAP//AAD+/wEA/v8AAP7/AQD//wEA/v8BAP7/AQD//wEA//8BAAAAAgD//wIA//8CAP//AgD//wIA//8CAP//AgAAAAIAAAACAAAAAgAAAAIAAAACAAAAAwAAAAMAAQACAAEAAgACAAMAAgADAAIAAwACAAMAAgACAAIAAgACAAIAAgACAAIAAgADAAEAAwABAAIAAQADAAEAAwABAAMAAQADAAEABAABAAQAAQAEAAEABAABAAQAAQAEAAEABAAAAAMAAAADAAAAAwAAAAIAAAADAAAAAwAAAAMAAAACAAAAAgAAAAIAAAABAAAAAQAAAAIAAAACAAAAAQAAAAEAAAAAAP//AAAAAAEAAAAAAP//AAAAAAAAAAAAAAAAAAAAAAAAAAD//////////wAAAAD//wAA/v8AAP//AAD//////v////7////+/////v////7//////wAA/v////7////+/////v////////8AAP/////+/////v////7////+/////v8AAP7/AAD+/////v////7/AAD+/wAA/v8AAP7/AAD+/wAA/v8AAP7/AQD+/wEA/v8AAP7/AAD+/wAA//8AAP7/AAD+/wEA//8AAP//AAD//wAA//8AAAAAAAAAAAAAAAAAAAAAAQAAAAAAAAAAAAAA//8AAP//AQAAAAEA//8BAP//AQD//wIA//8CAP//AgAAAAIAAAACAP//AgD//wIAAAADAAAAAwAAAAMAAAADAAAAAwAAAAMAAAADAAAAAwD//wMAAAADAAAAAwAAAAMA//8DAAAAAwAAAAMAAAADAAAAAwAAAAIAAAACAAEAAQABAAIAAQACAAAAAgAAAAIAAQABAAEAAQAAAAAAAAABAAEAAQAAAAAAAQAAAAEAAQAAAAAAAAAAAAEAAAABAAAAAQAAAAEAAAAAAP//AAD//wAAAAAAAP///////wAA//8AAP//AAD/////////////////////AAD////////+///////+/////v////7////+/////v////7////+/////f////7////+/////f/+//3////+/////v/+//7//v/+/////v////7////+/////v////7//v/+//7//v////7//v///////////////v////7/AAD//wAA//8AAP7/AAD//wAA//8BAP//AQD//wEA//8BAP//AQD+/wIA/v8BAP7/AQD+/wEA//8BAP//AQD+/wEA/v8CAP7/AgD//wIA//8CAP//AgAAAAIA//8CAP//AgAAAAIAAAACAAAAAgABAAIAAQACAAEAAgABAAMAAQADAAEAAgABAAIAAQACAAEAAgABAAIAAQACAAEAAgABAAIAAQACAAIAAgACAAIAAgACAAEAAgACAAIAAgADAAIAAgACAAIAAgACAAIAAgACAAIAAgACAAIAAgACAAIAAgACAAMAAgACAAIAAQACAAIAAQACAAIAAQACAAEAAgABAAEAAQABAAEAAgABAAEAAQABAAEAAgABAAIAAQABAAAAAQAAAAEAAQABAAEAAQAAAAEAAAABAAEAAAAAAAAAAAAAAAAAAAAAAP//AAD//wAA//8AAP///////wAA/v8AAP7////+/////f////3/AAD9/wAA/f8AAP3/AAD9/wAA/f8AAPz////8/wAA/P8AAPz/AAD8/wAA/P////v////7////+/////v////7/wAA+v8AAPr////7////+/////v////7//7//P////v////7/////P////z////8/////f////3////8/////f////3////9/////v////7//////////////////v////7//////wAA//8AAP//AQD//wEA//8BAP//AgAAAAIAAAABAP//AQD//wEA//8CAP//AgD//wMAAAACAAAAAgAAAAMAAAAEAAAAAwAAAAMAAAAEAAEABAABAAMAAQADAAEAAwABAAQAAQAEAAEABAABAAQAAQADAAEABAABAAQAAQAEAAIABAABAAQAAQAEAAEAAwABAAQAAQAEAAEAAwAAAAMAAQADAAEAAwABAAMAAAAEAAAAAwABAAMAAQACAAAAAgAAAAIAAAACAAAAAgAAAAEAAAACAAAAAgAAAAEAAAABAP//AQD//wEA//8BAAAAAAAAAAAAAAAAAAAAAAAAAAAA//8AAP7/AAD//wEA//8AAP//////////////////////////AAD+/wAA/v8AAP7/AAD+/wAA/v8AAP7/AAD+/wAA/f////3////+/wAA/v8AAP3/AAD9/wAA/f8BAP7/AAD+/wAA/v8BAP3/AQD8/wAA/f8BAP7/AQD+/wEA/v8AAP7/AAD+/wAA/v8AAP7/AQD+/wEA/v8AAP7/AAD+/wAA/v8BAP//AAD//wAA//8AAP7/AAD//wAA//8AAP//AAAAAAEAAAAAAAAAAAAAAAAAAAAAAAAAAQAAAAAAAQAAAAEAAAAAAAAAAAABAAEAAQABAAEAAgABAAEAAAABAAAAAgAAAAMAAQACAAAAAgAAAAIAAQACAAEAAgABAAIAAQACAAIAAgABAAIAAQACAAIAAwABAAIAAQACAAAAAgABAAIAAQACAAEAAQAAAAIAAAACAAEAAQABAAEAAQABAAEAAQABAAEAAAABAAEAAAAAAAAAAAABAAAAAAAAAP//AAD//wAA//8AAAAAAAAAAP///////////////////////////v////7//v/+//7//v/+//3//v/+//3//v/9//3//v/+//7//v/9//3//v/9//7//v/+//3//v/9//7//v/+//7//v/+//7//v/+//7//v/+//7//v/+//7//v/+//////////////////7///////////////7/AAD+/wAA//8AAP//AAD//wAA//8AAP//AQD//wEA//8BAAAAAQAAAAIAAAACAAAAAgAAAAIAAQACAAAAAgAAAAMAAQADAAEAAwAAAAMAAAADAAEAAgABAAMAAQADAAEAAwABAAMAAQADAAEAAwABAAIAAQACAAEAAgABAAIAAQABAAIAAQACAAIAAgABAAIAAQACAAEAAgABAAIAAQACAAEAAgABAAIAAQADAAEAAgAAAAIAAAACAAEAAgABAAIAAAADAAAAAgAAAAIAAAACAAAAAgAAAAIAAAACAAAAAwAAAAMAAAACAAAAAgAAAAIAAAACAAAAAgAAAAIAAAACAAAAAgAAAAIAAAACAAAAAgAAAAIAAAACAAAAAQAAAAEAAAAAAP//AQAAAAEAAAABAP//AQD//wAA//8AAAAAAAAAAAAA//8AAP//AAD//wAA//8AAP//AAD//wAA/v////7////+/////v////7////+//7//v/+//3//v/9//7//f/+//3//v/9//7//f/+//3//v/9//7//f/+//7//f/9//7//f/+//7//v/+//7//v/+//7//f/+//3//v/+//7//v/9//7//v/+//7//f////3//v/9/////v////3/AAD9/////f8AAP7/AAD9/wAA/f8AAP3/AAD9/wEA/v8BAP3/AQD9/wEA/v8CAP7/AgD9/wIA/f8CAP7/AgD+/wMA//8DAP7/AgD+/wIA/v8DAP//AwD+/wMA/v8DAP7/AwD//wMA//8DAP//AwD//wMAAAADAAAAAwAAAAMAAAADAAAAAwAAAAMAAAADAAEAAwABAAIAAQACAAEAAgACAAIAAQACAAEAAgACAAIAAgACAAIAAgACAAIAAgABAAMAAQADAAIAAwABAAMAAQADAAIAAwABAAMAAQADAAEAAwACAAMAAQAEAAAABAABAAQAAQAEAAEABAABAAQAAQAEAAAABAAAAAQAAQADAAAABAAAAAQAAAADAAAAAwAAAAMAAAADAAAAAwAAAAMAAAADAP//AwAAAAMA//8DAP//AgD//wIA//8CAP7/AgD+/wIA/v8BAP7/AQD+/wEA/f8AAP3/AAD9/wEA/f8AAP3/AAD8/wAA/P8AAPz////8/////P////z////7/////P/+//v//v/6//7/+//+//z//v/8//7/+//+//v//f/8//3//P/+//z//f/8//3//P/9//z//f/9//3//f/9//3//f/+//z//v/8//7//f/+//3////9/////f////3////9/////f8AAP3/AAD9/wAA/v8BAP7/AQD9/wEA/f8CAP3/AgD+/wIA/v8DAP7/AwD+/wMA/v8DAP7/AwD+/wQA//8EAP//BAD//wQA//8EAP//BAAAAAQAAAAEAAAABAAAAAQAAAAEAAEABQABAAQAAAAEAAAABAABAAQAAQAEAAEABAABAAQAAQAEAAEABAACAAMAAgADAAIAAwACAAIAAwADAAMAAgACAAIAAgACAAMAAwACAAIAAgACAAIAAQACAAEAAgABAAIAAQACAAEAAwABAAIAAQACAAAAAwAAAAIAAQACAAAAAgAAAAIAAAACAAAAAQAAAAIAAAABAAAAAQAAAAAA//8BAP//AAD//wAA//8AAP7/AAD+/////v/////////+/wAA/v8AAP7////9/////v/+//7//v/+/////v////7//v/9//7//f/+//3//v/9//7//f/+//3//f/8//3//P/+//z//f/8//3//P/+//z//v/7//3/+//+//z//f/8//3/+//9//z//f/8//3//P/+//z//v/8//7//P/+//z////9/////f/+//3//v/+/////////////////wAA////////////////AAD//wAAAAAAAAAAAQABAAEAAAACAAAAAgABAAIAAQADAAEAAgABAAMAAQADAAEABAABAAMAAgAEAAIABAACAAUAAgAFAAIABAACAAQAAgAEAAIABAACAAQAAwAFAAIABQACAAQAAwAEAAQABQADAAQAAwAEAAMABAAEAAQABAAEAAMABAADAAQAAwADAAQAAwAEAAMAAwADAAMAAgADAAIAAwACAAMAAQADAAEABAABAAMAAQADAAAAAgAAAAMAAAADAAAAAwD//wMA//8CAP//AwD//wIA//8BAP//AQD//wIA//8BAP7/AQD+/wEA/v8AAP7/AAD+/wEA/v8AAP7/AAD+/////v////7////+/////v/+//7//v/+//3//v/+//7//v/+//3//v/8//7//f/+//z//v/8//7//P/+//z//v/8//7//P/+//z//v/7////+//+//v//v/8//7//P/+//z////8////+/////v//v/7//7/+/////v////7/////P/+//v//v/7/////P////z//v/8//7//P////z////9//7//f////3////9/////f////7////+/////v////7/AAD+/wAA//8AAP//AAD//wAA//8AAAAAAAD//wAA//8AAAAAAQABAAEAAAAAAAEAAQABAAEAAQACAAEAAgACAAIAAgACAAEAAgABAAIAAgACAAMAAwACAAIAAgACAAMAAwADAAMAAgADAAIAAwADAAIAAwACAAMAAgADAAMAAwADAAMAAgADAAIABAACAAQAAgADAAIAAwACAAMAAgADAAIABAACAAQAAgADAAEAAwABAAQAAgAEAAEAAwAAAAQAAAADAAAAAwAAAAMAAAADAAAAAwAAAAMA//8DAAAAAwD//wMA//8DAAAAAwD//wMA//8DAP7/AwD+/wIA//8DAP7/AwD+/wIA/v8CAP7/AgD+/wEA/v8BAP//AQD+/wEA/v8AAP7/AAD//wAA//8AAP//AAD//////v////7/AAD///////////7//v///////////////v////7////+/wAA/v8AAP7/AAD+/wAA/v8AAP3////9/////v8AAP3/AAD9/wAA/f8AAP3/AAD9/wAA/f8BAP3/AQD+/wAA/v8AAP7/AAD+/wEA/v8AAP7/AAD+/wAA/v8AAP//AQD//wEA/v8AAP7/AAD//wAA//8AAP//AAD//wAAAAAAAP//AAD//wEAAAABAAAAAQD//wAAAAABAAAAAQABAAEAAQABAAAAAgABAAIAAQABAAAAAQAAAAEAAQABAAEAAgABAAIAAAACAAEAAQABAAEAAQABAAEAAQABAAEAAQABAAEAAQABAAEAAQACAAEAAQABAAEAAQABAAEAAQABAAEAAAAAAAEAAQABAAEAAAABAAAAAQAAAAEAAAAAAAAAAAAAAAAAAQAAAAAAAAAAAAAAAAD//wAA//8AAP7/AAD+/wAA/v8AAP7/AAD+/wAA/v8AAP7/AAD+/wAA/v////7/AAD+/wAA/v////3////9/////f////3////9/////f////3////9/////f////3//v/9/////f////3////9/////v////7////+//7//v/+//7//v/+//7////+/////v////7//v/+/////v////7/AAD+/wAA/v////7////+/wAA/v8AAP7/AAD+/wAA/v8AAP7/AAD+/wEA/v8BAP7/AQD+/wIA/v8BAP7/AQD+/wEA//8CAP//AgD+/wIA//8CAP//AgAAAAIAAAACAAAAAgAAAAMAAAACAAAAAgAAAAIAAQADAAEAAwABAAMAAgADAAIAAwACAAMAAgADAAIABAACAAQAAgADAAMAAwAEAAMAAwADAAMAAwAEAAMABAADAAQAAwAEAAMABAADAAUAAwAFAAIABQADAAUAAgAFAAIABQADAAUAAwAFAAMABQACAAUAAQAFAAIABQACAAUAAQAFAAEABQABAAQAAQAEAAAABQABAAQAAQAEAAAAAwAAAAMA//8DAAAAAwD//wMA//8CAP//AgD//wIA//8CAP//AQD//wEA/v8BAP7/AQD+/wEA/v8AAP3/AAD9/wAA/f8AAP3/AAD9/////f////3////9/////P////z//v/8//3//P/9//z//v/8//7//P/+//v//v/7//3//P/9//z//P/8//z//P/8//z//P/8//z//P/8//z//P/8//z//f/7//3/+//9//v//f/7//3/+//9//v//f/7//7/+//+//v//v/7//7/+/////v////6////+v////v/AAD7////+/////v/AAD7/wEA+/8BAPv/AQD7/wIA+/8BAPv/AQD8/wIA/P8CAPz/AgD8/wIA/P8CAP3/AgD9/wMA/f8DAP3/AwD+/wMA/v8EAP3/BAD+/wQA//8EAP7/BAD//wQA//8EAAAABAAAAAUAAAAFAAEABQABAAQAAgAEAAIABAACAAQAAgAFAAIABQADAAUAAwAFAAQABQAEAAQABAAEAAUABAAEAAQABAAEAAUABAAFAAQABQADAAUAAwAFAAMABQACAAYAAwAHAAMABgACAAYAAQAGAAIABgACAAYAAQAGAAEABgAAAAYAAAAGAAAABgAAAAYA//8FAP//BQD//wUA//8FAP//BQD+/wUA/v8FAP3/BAD9/wQA/f8DAPz/AwD9/wIA/P8CAPz/AwD8/wIA/P8CAPv/AgD7/wIA+/8BAPv/AQD7/wEA+/8AAPr/AAD6/wAA+/8AAPv////6////+/////v////8//7//P/9//z//f/8//7//P/+//v//f/8//3//P/+//3//f/8//3//f/9//3//f/+//3//v/8//3//P/9//z//v/8//7//P/+//z//v/7////+/////z/AAD8////+/////v/AAD8/wAA/f8BAP3/AQD8/wEA/f8CAP3/AQD9/wEA/f8CAP3/AgD8/wIA/f8CAP3/AgD8/wIA/f8CAP7/AwD+/wMA/v8DAP7/AwD+/wMA/v8DAP7/BAD//wQA//8DAP//AwD//wQA//8DAP//AwAAAAQAAAAEAAAABAD//wQA//8EAAAABAABAAQAAQADAAEABAABAAQAAQADAAEAAwABAAQAAgAEAAIAAwACAAMAAgAEAAMABAADAAMAAwADAAMAAgADAAMAAwADAAMAAgADAAIAAwACAAQAAgAEAAIABAABAAQAAQAEAAEAAwACAAQAAQAEAAEABAABAAMAAAADAAAABAAAAAMAAAADAP//AgD//wMA//8DAP//AwD+/wIA/v8CAP7/AwD+/wIA/f8BAP3/AgD9/wIA/P8BAP3/AAD9/wAA/P8BAPz/AAD8/wAA/P////z////8/////P////z////8//7//P/+//z//v/7//7/+//+//z//v/8//7/+//+//z//v/9//3//f/9//3//v/9//3//f/9//3//f/9//3//v/9//7//f/+//3////9/////v////7////9/////f////3/AAD+/wAA/v8AAP3/AQD+/wEA/v8CAP7/AQD//wEA//8BAP//AgD//wIA/v8CAP7/AgD//wIA//8CAP//AgD//wIA//8CAP//AgAAAAMAAAADAAEAAwABAAMAAAADAAEAAwACAAMAAQADAAEAAwABAAMAAQADAAIAAwABAAMAAQADAAEAAwABAAMAAQADAAEAAwABAAMAAQACAAIAAgACAAIAAgACAAMAAQADAAEAAgACAAIAAQADAAEAAwABAAIAAQADAAEAAwAAAAMAAQADAAEAAwABAAMAAAADAAAAAwAAAAMAAAADAAAAAwAAAAMA//8DAAAAAwD//wMA//8DAP7/AgD+/wIA//8DAP//AgD+/wIA/v8BAP7/AQD+/wEA/v8BAP7/AQD+/wEA/v8AAP7/AAD+/wEA/v8AAP3/AAD9/////v8AAP7////+/////v////3////+//7//v/9//7//f/+//3//v/9//7//f/+//3//v/9//7//P/+//3////9//7//P/+//z//v/8//7//P////v////8/////P////v////8/////P////v////8/////P8AAPz/AAD8/////P8AAP3////9/wAA/f8AAP3/AQD9/wAA/f8AAP3/AAD9/wAA/v8AAP7/AQD+/wEA/v8BAP7/AQD//wEA//8BAP//AgD//wIAAAACAAAAAgAAAAIAAAACAAAAAgAAAAIAAQADAAEAAwABAAMAAAACAAEAAwACAAQAAgADAAIAAwACAAMAAgADAAIAAwADAAMAAwADAAIAAgADAAMAAwADAAQAAwAEAAIABAACAAQAAwAEAAMABAACAAQAAgAEAAIAAwABAAQAAQAEAAIABAABAAQAAAAEAAAABAABAAQAAQAEAAAABAAAAAQA//8FAP//BQD//wQA//8EAP//BAD+/wQA/v8EAP//BAD+/wQA/v8DAP7/AwD9/wMA/f8DAP7/AwD+/wMA/f8DAP3/AwD+/wIA/v8CAP7/AgD9/wEA/v8BAP7/AQD9/wAA/f8AAP7/AAD+/wAA/v8AAP7////9/////f////7//v/+//7//f/+//7//v////3//v/9//7//f////z////8////+/////v/AAD7////+/////v/AAD6/wAA+/8AAPv/AQD6/wEA+/8BAPv/AQD6/wAA+v8AAPr/AQD7/wEA+/8BAPv/AAD7/wEA+/8CAPv/AQD7/wEA+/8BAPz/AgD8/wIA/P8BAP3/AgD9/wIA/f8CAP3/AgD9/wIA/v8BAP7/AQD+/wIA//8CAP//AgAAAAIAAAACAAAAAQAAAAIAAQABAAEAAQACAAEAAgABAAEAAQACAAAAAwABAAMAAQACAAEAAwAAAAMAAQAEAAEABAAAAAMAAAADAAAAAwAAAAQAAAADAAAAAwAAAAMAAAAEAAAABAAAAAMAAAADAAAABAAAAAMAAAADAAAAAwAAAAMAAAADAP//AwD//wQA//8DAP//AgD//wMA//8DAP//AgD//wIAAAADAP//AgD+/wIA/v8CAP//AQD//wEA/v8CAP7/AQD+/wEA/v8BAP7/AQD+/wEA/f8BAP7/AQD+/wAA/f8AAP3/AQD+/wEA/v8AAP3/AAD+/wAA/v////3/AAD+/wAA/v////7////9/////v////7////+/////v///////v////7//v/+/////v////7////+/////f////3////9/////f8AAP3/AQD9/wEA/P8BAP3/AQD9/wEA/f8BAP3/AQD9/wIA/f8CAP3/AgD9/wIA/P8CAP3/AgD9/wMA/P8CAP3/AgD9/wMA/v8EAP3/AwD+/wMA/v8EAP7/BAD+/wQA/v8DAP//BAD//wQA//8EAAAAAwAAAAMAAAADAAAAAwAAAAMAAQADAAEAAwABAAMAAQADAAIAAwACAAMAAgADAAIAAwACAAIAAgACAAMAAgADAAIABAACAAQAAgADAAEAAwABAAQAAQAEAAIABAABAAQAAQAEAAEABAAAAAQAAAADAAAAAwAAAAMA//8DAAAAAwD//wMA//8DAP//AwD//wMA//8DAP7/AwD+/wIA/v8DAP7/AwD+/wIA/v8CAP7/AgD+/wIA/v8CAP7/AQD+/wEA/f8BAP3/AQD9/wEA/f8BAP3/AAD9/wAA/P8AAPz/AAD8/wAA/P8AAPz/AAD8/wAA/P////z////8/wAA/P////z//v/8//7//P////z////8/////P/+//z//v/8//7//f/+//3//v/9//7//f/+//3//v/9//7//f/9//3//f/+//3//v/9//7//f/+//3////9/////P////z////9/wAA/P8AAPz////8/wAA/P8AAPz/AAD8/wEA/P8BAPv/AgD8/wIA/P8CAPz/AgD7/wIA/P8CAPz/AwD8/wMA+/8DAPz/AwD8/wQA/f8EAPz/AwD8/wMA/P8EAP3/BAD9/wQA/f8EAP7/BAD+/wQA//8EAP//BAD//wQA//8EAAAABAAAAAQAAQAEAAEABAABAAMAAgADAAIAAwACAAMAAwADAAMAAwADAAMAAwADAAQAAwAEAAMABAADAAUAAwAFAAIABQACAAYAAgAGAAIABgACAAYAAgAGAAEABgABAAYAAQAGAAEABgABAAYAAAAFAAAABQAAAAUAAAAFAAAABQD//wUA//8FAP//BQD//wQA/v8EAP//BAD//wQA/v8DAP7/AwD+/wMA/v8DAP3/AgD9/wIA/f8CAP3/AQD9/wIA/f8BAPz/AAD8/wAA/f8AAP3/AQD9/wAA/f////z/AAD9/wAA/f8AAP3////9/////f////3////9/////f////7////+/////f////7////+/////v////3//v/+//7//v/+/////v////7////+/////v////7////+/wAA/f8AAP7/AAD+/wAA/v8AAP7/AAD9/wEA/f8BAP3/AQD9/wEA/f8CAP3/AQD9/wEA/f8CAP3/AgD8/wEA/f8BAP3/AgD9/wMA/f8CAP3/AgD8/wMA/P8DAP3/AgD8/wIA/P8DAPz/AgD9/wIA/f8DAPz/AwD8/wIA/f8CAP3/AwD9/wMA/v8CAP7/AgD9/wIA/v8CAP//AgD//wIA/v8CAP//AQAAAAEAAAACAAAAAgAAAAIAAQABAAEAAQABAAEAAQABAAIAAQACAAEAAgABAAIAAQADAAAAAwAAAAMAAAAEAAAABAAAAAQAAAAEAAAABAD//wQA//8EAAAABAD//wQA//8EAP7/BAD//wQA//8EAP//BAD//wQA/v8DAP7/AwD9/wQA/f8EAP7/AwD+/wMA/v8DAP7/AgD+/wEA/v8BAP7/AgD+/wIA/v8BAP7/AQD+/wAA/v8AAP3////+/wAA/v////7////+/////v/+//3//v/+//7//v/+//7//v/+//7////+/////v////7////+/////v////7//v/+//7//v////7////+/wAA/v8AAP3/AAD+/////v////7/AAD+/wAA/v8AAP7/AAD+/wAA/v8BAP//AQD//wEA/v8BAP//AQD//wIAAAACAP//AgD//wIA//8CAP//AgAAAAIA//8CAP//AgD//wMAAAADAP//AwD//wMAAAADAAAAAwAAAAMA//8DAAAAAwAAAAMAAAADAAAAAwAAAAMAAAADAAAAAwAAAAMAAAADAAAAAwAAAAMAAAADAAAAAgABAAIAAQACAAEAAgABAAIAAQABAAAAAQABAAEAAQABAAEAAQABAAAAAgAAAAIAAAADAAAAAgD//wMA//8DAAAAAgD//wMA//8DAP//BAD//wMA//8DAP7/BAD+/wMA/v8DAP7/AwD+/wQA/v8EAP7/AwD+/wMA/v8DAP7/AwD9/wMA/v8DAP7/AwD+/wIA/f8CAP3/AgD9/wMA/v8CAP7/AQD+/wEA/v8CAP7/AQD+/wAA/v8AAP3/AAD+/wAA/v////7////+/////v/+/////v////3////9/////f/+//3//v/8/////P////z////8////+/////r////7////+/////v/AAD6/wAA+/8AAPr/AAD6/wAA+v8AAPv/AAD6/wAA+v8AAPv/AQD7/wEA+/8BAPv/AQD8/wAA/P8AAPz/AQD9/wEA/f8AAP3/AAD9/wEA/v8CAP7/AgD+/wEA//8BAP//AgAAAAIAAAACAAAAAgABAAEAAQACAAEAAwACAAIAAQACAAIAAgACAAIAAwADAAIAAgACAAIAAgACAAIAAgACAAMAAwADAAMAAgACAAIAAwACAAMAAgADAAIAAwACAAMAAgADAAIAAwACAAMAAwACAAIAAgACAAIAAQADAAEAAwABAAMAAQADAAIAAwACAAIAAQACAAEAAwABAAMAAQACAAAAAwAAAAMAAQADAAAAAgD//wMAAAADAAAAAwAAAAIA//8CAP//AgD//wMA/v8DAP7/AgD//wIA//8CAP//AgD//wIA/v8CAP7/AgD9/wIA/v8CAP7/AgD9/wIA/f8CAP7/AQD9/wEA/f8BAP3/AQD9/wEA/f8BAP3/AQD9/wAA/f8AAP3/AAD9/////P////3////9/////f/+//z//v/9//7//v/+//7//f/+//3//f/9//3//f/9//z//v/8//7//P////v////8////+/////v////7/wAA+/8AAPv/AAD7/wAA+/8BAPv/AQD6/wAA+/8BAPz/AQD8/wEA/P8BAPz/AgD8/wIA/P8CAPz/AgD8/wMA/f8DAP3/AwD9/wIA/v8DAP7/AwD//wMA//8DAP//AwD//wMAAAADAAEAAwABAAMAAQADAAEAAwACAAQAAgAEAAIABAADAAMAAwADAAMAAgADAAIABAADAAQAAgAEAAIABAADAAUAAwAFAAIABAACAAQAAgAEAAIABAABAAQAAQAEAAEABAABAAQAAQAEAAEAAwABAAMAAQADAAEAAwAAAAMAAAADAP//AgD//wIAAAACAP//AgD//wIA//8CAP//AgD//wEA/v8BAP7/AQD+/wAA//8AAP7/AAD9/wAA/v8AAP7/AAD9/wAA/f////3/AAD+/wAA/f////z////8/wAA/f8AAP3////9/////f8AAP3/AAD9/wAA/f8AAP3////9/////f8AAP3/AAD9/////f////7/AAD+/////v////7////+/wAA/v////7////+//7//v////7////+//7////+/////v////7////+/wAA/f////3////+/wAA/v8AAP3/AQD9/wAA/f8BAP3/AQD9/wIA/f8CAP3/AQD9/wEA/f8CAP3/AwD9/wIA/f8CAP3/AgD9/wIA/f8CAP3/AgD9/wMA/f8DAP3/BAD+/wQA/v8DAP3/AwD9/wMA/v8DAP7/BAD+/wMA//8DAP//AwAAAAMAAAAEAAAAAwAAAAMAAQADAAEAAwABAAQAAgAEAAIAAwACAAMAAwADAAMAAgADAAIAAwACAAMAAgADAAEAAwABAAQAAgAEAAIABAABAAQAAQAEAAAABAAAAAQAAQAEAAEABAAAAAQAAAAEAP//BAAAAAUAAAAEAP//BAD+/wQA/v8EAP//BAD//wMA/v8DAP7/AgD9/wMA/f8DAP7/AgD+/wIA/f8CAP3/AgD9/wIA/f8CAP3/AQD8/wEA/f8BAP3/AQD9/wEA/f8AAP3/AAD9/wAA/f8AAP3/AAD9/wAA/f8AAP3////9/////f////3////9/////f/+//3////9/////v////7////+//7//v/+//7//v/+//7//v/+//7//f////7////+/////f////3////+/////f8AAP3/AAD9/wAA/v8BAP7/AQD9/wAA/f8AAP3/AAD9/wEA/f8BAPz/AgD9/wIA/P8BAPz/AQD8/wIA/f8CAPz/AwD8/wIA/f8CAP3/AgD9/wIA/P8CAPz/AgD8/wIA/P8DAP3/AwD9/wMA/f8DAP3/AwD9/wMA/v8DAP7/AwD+/wMA//8DAP//AwD//wMA//8DAP//AwAAAAMAAAADAAEAAwABAAIAAQACAAEAAgABAAIAAQACAAIAAgACAAIAAgACAAMAAgADAAIABAACAAQAAQAEAAEABAABAAQAAQAEAAEABAABAAQAAQAEAAEABQAAAAQAAAAEAAAABAAAAAQAAAAEAAAABAAAAAQA//8EAP//BAD//wQA//8EAP7/BAD+/wMA/v8DAP7/AwD9/wIA/f8CAP3/AgD9/wIA/f8BAPz/AQD9/wEA/f8CAP3/AQD9/wEA/f8BAPz/AQD8/wEA/f8AAP3/AAD9/wAA/f8AAPz/AAD8/wAA/f8AAP3////9/wAA/P8AAP3/AAD9/////f////3////9/////v////7////+/////v////7////+///////////////+////////////////////AAD//wAA/v8AAP7/AQD+/wEA//8BAP//AQD+/wIA/v8CAP7/AgD+/wIA/v8BAP7/AQD+/wIA/v8CAP7/AgD+/wMA/v8DAP7/AwD+/wMA/v8DAP3/AwD9/wIA/f8DAP7/AwD+/wMA/f8DAP7/AwD+/wMA/v8EAP7/BAD+/wQA/v8DAP7/AwD+/wMA/v8DAP7/AwD//wIA//8CAP//AgD//wIA//8CAP//AgD//wIAAAACAAAAAQAAAAIAAAACAAEAAgABAAEAAgACAAIAAQACAAEAAgAAAAIAAAACAAEAAgAAAAMAAAADAAEAAwAAAAQAAAADAAAABAAAAAQA//8EAP7/BAD//wQA//8DAP7/AwD9/wMA/v8EAP7/BAD+/wMA/v8DAP7/BAD+/wMA/v8CAP7/AgD+/wIA/v8CAP7/AgD9/wIA/f8BAP3/AQD9/wAA/f8AAP3/AAD9/////f////3////+/////v/+//3//v/9/////v/+//7//f/9//3//f/+//7//f/+//3//v/9//7//f/+//3////9/////f////3/AAD9/wAA/v8AAP3////9/wAA/v8AAP7/AQD+/wEA/v8AAP3/AAD+/wAA/v8BAP//AQD+/wEA/v8BAP//AgD+/wIA/v8CAP//AgD//wIAAAACAAAAAgD//wIAAAACAAAAAgAAAAIAAAACAAAAAgAAAAIAAQACAAEAAwABAAMAAQADAAEAAwABAAIAAAACAAEAAgABAAMAAAADAAEAAwABAAMAAQADAAAAAgABAAIAAQACAAAAAgAAAAIAAQACAAEAAgAAAAMAAAADAAEAAgACAAEAAQABAAEAAgABAAIAAQACAAEAAQABAAEAAQABAAIAAQACAAEAAgAAAAIAAQACAAEAAwAAAAIAAAACAAAAAwAAAAMAAAADAP//AwD//wMA//8DAP//AwD//wMA//8DAP7/AwD+/wMA/v8CAP7/AgD+/wMA/v8DAP7/AgD+/wIA/f8DAP3/AgD9/wEA/f8BAPz/AgD8/wEA/P8BAP3/AQD9/wEA/P8AAPz/AAD8/wAA/P////z////8/////P////z//v/8//3//P/+//z//v/8//3//P/9//z//f/8//3//P/8//3//P/9//z//f/8//3//P/+//z//f/7//3/+//+//v//v/7////+/////v////7/wAA+/8AAPv/AAD8/wAA/P8AAPv/AQD8/wEA/P8BAPz/AQD8/wEA/P8CAPz/AgD8/wIA/f8DAP7/AwD9/wMA/f8EAP7/BAD//wQA//8EAP//BAD//wQA//8EAP//BQAAAAUAAAAFAAAABQAAAAUAAAAFAAAABQABAAUAAQAFAAIABQABAAQAAQAEAAIABAADAAUAAgAEAAIABAACAAQAAgAEAAIABAACAAQAAgADAAIAAwADAAMAAwADAAMABAADAAMAAwACAAMAAgADAAEAAwABAAMAAQADAAAAAwABAAMAAQADAAAAAwAAAAMAAAADAAAAAwD//wMA//8DAP7/AwD//wMA//8DAP7/AwD+/wMA/v8EAP7/AwD9/wIA/v8CAP7/AwD9/wMA/f8DAP3/AgD9/wIA/f8CAP3/AgD9/wMA/P8CAP3/AgD9/wIA/f8CAP3/AQD8/wEA/P8BAP3/AQD9/wEA/P8BAPz/AAD7/wEA/P8BAPz/AAD8/////P8AAPz/AAD8/////P////3////8/////P/+//3//v/8//7//f/+//3//v/+//7//f/+//3//f/+//3//v/9//7//f/+//3////9/////f////z////8/////f8AAP3/AAD9/wAA/f8AAP3/AQD9/wEA/f8BAP3/AQD9/wIA/f8CAP7/AgD+/wMA/v8DAP7/AwD+/wMA/v8DAP7/AwD+/wMA//8DAP//BAD//wMA//8DAP//AwD//wQAAAADAP//BAD//wQAAAAEAAAABAABAAUAAAAEAAAABAABAAQAAQAEAAAABAABAAQAAQADAAEAAwABAAMAAQADAAEAAwABAAMAAgADAAEAAwABAAIAAQACAAIAAgABAAIAAQABAAIAAQABAAEAAQAAAAEAAAACAAEAAQAAAAEA//8BAP//AQAAAAEAAAABAP//AQD//wEA/v8CAP7/AgD+/wIA/v8BAP7/AQD+/wEA/v8BAP7/AQD+/wEA/v8BAP3/AQD9/wIA/f8CAP7/AQD9/wEA/f8BAPz/AgD9/wIA/f8CAP3/AQD9/wEA/f8BAP3/AQD9/wEA/P8BAP3/AQD+/wEA/v8BAP7/AQD9/wEA/f8AAP7/AAD+/wAA/v8AAP7/AAD+/wAA/v8AAP7/AAD+///////+/////v///////////////v////7////+/////v8AAP7/AAD+/////v////3/AAD9/wAA/f8AAP7/AQD+/wEA/f8BAP3/AQD+/wEA/f8BAP3/AQD+/wEA/v8CAP3/AgD9/wIA/v8CAP7/AgD+/wIA/v8CAP//AgD//wIA//8DAP//AwD//wIA//8CAAAAAwAAAAMAAAACAAAAAwAAAAMAAQACAAAAAwABAAMAAQADAAEAAgABAAMAAgADAAEAAwABAAIAAgADAAIAAwACAAIAAgACAAIAAgACAAIAAgACAAIAAQACAAEAAgACAAIAAQACAAEAAgABAAIAAQACAAEAAgAAAAIAAAACAAAAAgAAAAIAAAACAAAAAgD//wIAAAACAP//AgD//wIA//8CAP7/AgD+/wIA//8CAP//AQD+/wEA/v8BAP7/AQD+/wEA/v8CAP7/AQD+/wEA/v8BAP7/AQD+/wEA/v8AAP7/AAD+/wAA/f8AAP3/AAD+/wAA/v8AAP7/AQD+/wAA/v8AAP7/AAD+/wAA/v8AAP7/AAD+/wAA/v8AAP7/AAD//wAA////////////////////////AAD////////+/////v//////AAD//wAA/v8AAP7/AAD+/wAA/f////7/AAD+/wEA/f8AAP3/AAD9/wAA/f8AAP3/AQD8/wEA/P8BAP3/AQD9/wEA/P8BAPz/AAD9/wAA/f8AAPz/AAD9/wEA/f8BAPz/AQD8/wEA/f8CAP3/AQD9/wEA/f8BAP3/AQD+/wIA/v8CAP7/AgD+/wIA/v8CAP//AgD//wEA//8BAAAAAgD//wIAAAACAAEAAgABAAIAAAACAAEAAgABAAIAAQACAAEAAgABAAIAAgACAAIAAgACAAIAAgACAAIAAgACAAIAAwABAAMAAQADAAEAAwABAAMAAQADAAEAAwAAAAMAAAADAAAAAwAAAAMAAAADAP//AgAAAAMAAAADAP//AwD//wIA//8DAP//AwD//wIA/v8CAP7/AwD+/wIA/v8CAP7/AQD+/wEA/f8BAP3/AQD9/wEA/v8BAP7/AQD9/wEA/f8BAP3/AQD9/wEA/f8BAP3/AQD9/wEA/f8BAP3/AQD+/wEA/v8AAP7/AAD+/wAA//8AAP7/AAD+/wEA/v8BAP//AAD+/wAA//8AAP//AAD//wAA//8BAAAAAQAAAAAA//8AAAAAAAAAAAAAAAAAAAAAAAABAAAAAQAAAAEAAAABAAAAAgAAAAEAAAABAP//AQD//wIA//8CAP//AgD//wIA//8CAP//AwD//wIA//8CAP//AwD+/wMA/v8CAP7/AwD+/wMA/v8CAP7/AgD+/wIA/v8CAP3/AgD+/wIA/v8CAP7/AgD+/wEA/v8BAP3/AQD+/wEA/v8BAP7/AQD9/wEA/f8BAP7/AQD+/wAA//8AAP7/AQD//wAA//8AAP//AAD//wEA//8AAAAAAAAAAAEAAAABAAAAAQAAAAEAAAAAAAEAAAABAP//AAD//wEAAAABAAAAAgAAAAEAAAABAAAAAgAAAAIA//8CAAAAAQAAAAEA//8CAP//AgD//wMA//8DAAAAAgAAAAIA//8CAP//AgD//wIA//8BAP//AQD//wIA/v8CAP7/AgD+/wEA/v8BAP7/AQD+/wEA/v8BAP7/AAD+/wEA/v8BAP7/AQD+/wAA/v8AAP3/AAD+/wAA/v////7////+/////v////7////+/wAA/v////3////+/wAA/v8AAP7/AAD+////////////////////////////AAD//wAA//8BAAAAAQAAAAAA//8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAQAAAAEAAAABAAAAAQAAAAEAAQABAAAAAQAAAAIAAAADAAAAAgABAAIAAQACAAAAAgAAAAIAAAADAAAAAgD//wIA//8DAAAAAwD//wMA//8DAP//AwD//wIA//8DAAAAAwD//wMA//8CAP//AgD+/wIA/v8CAP//AgD//wIA//8CAP//AgD//wMA/v8CAP7/AgD//wIA/v8CAP//AgD//wIA//8BAP7/AQD//wEA//8AAP//AAD//wEA//8AAP//AAD//wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP//AQD//wEA//8BAP7/AQD//wEA//8BAP7/AQD//wEA//8CAP7/AgD+/wIA/v8BAP7/AQD+/wIA/v8CAP7/AQD9/wEA/f8BAP7/AgD+/wEA/v8BAP7/AQD+/wEA/v8BAP//AQD+/wAA/v8AAP3/AQD+/wEA//8BAP//AAD//wEA//8BAP//AAD+/wAA/v8AAP//AAD//////v8AAP7/AAD///////8AAP7/AAD//////////wAA//8AAP//AAD+/wAA/v////////////////8AAP//AQD+/wAA//8AAP//AQD//wAA//8BAAAAAQAAAAEAAAABAAAAAQAAAAEAAAABAAAAAQAAAAEAAAABAAAAAQAAAAEAAAACAAAAAgAAAAIAAAACAAAAAQABAAEAAQABAAEAAgABAAIAAAABAAAAAgABAAIAAQACAAAAAQAAAAEAAQACAAAAAgAAAAEAAAABAAAAAQAAAAIAAAACAAAAAQAAAAEAAAACAAAAAQAAAAEAAAABAAAAAQAAAAEAAAAAAP//AAAAAAAAAAABAAAAAQAAAAAA//8AAP//AAD//wEA//8BAP//AAAAAAAAAAD//wEAAAABAAAAAAAAAAAAAAABAAAAAAAAAAAAAAAAAP//AQD+/wEA//8BAP//AQD//wEA//8BAP//AQD//wEA//8CAP//AQD//wEA/v8BAP//AQD//wEA//8BAP7/AQD+/wEA/v8BAP7/AQD+/wEA/v8BAP3/AQD+/wAA/v8AAP7/AQD+/wEA/v8BAP//AQD//wAA//8AAP7////+/wAA/v8AAP//AAD//wAA//8AAP//AAD//wAA//8AAP//AAD//////////////////////////wAA//8AAP//AQD//wEA//8AAP//AAD//wAA//8AAP//AQD//wEA//8CAP//AQD//wEA//8BAP//AgAAAAEAAAABAAAAAgAAAAIAAAACAAAAAgD//wIA//8DAAAAAgAAAAIAAAACAAEAAwABAAMAAQACAAAAAgAAAAIAAAACAAEAAgAAAAIAAAACAAAAAQAAAAEAAAACAAAAAQAAAAEAAAABAAAAAQAAAAEAAAABAAAAAQAAAAAAAAABAAAAAQD//wEA//8BAP//AQD//wEA//8AAAAAAAAAAAEAAAAAAP//AAD//wAA//8AAP//AAD//wAAAAAAAP//AAD/////AAAAAAAA////////AAD//wAA/////////////wAA//8AAP//AAD//wAA//8BAP//AAD//wAA//8AAP//AQD//wEA//8BAP//AAD//wAA/v8BAP7/AQD+/wEA/v8BAP7/AQD//wEA/v8BAP7/AQD+/wEA/v8BAP7/AQD+/wEA/v8BAP//AQD//wEA//8AAP7/AQD+/wEA//8BAP7/AAD+/wEA//8BAP//AQD//wEA//8BAAAAAQD//wAA//8AAP//AAAAAAAAAAABAAAAAAAAAAAAAAAAAAAAAAAAAP//AQAAAAEAAAABAP//AQAAAAEAAAABAAAAAQD//wEAAAABAAAAAQD//wIA//8CAP//AgAAAAIAAAACAAAAAgAAAAIAAAACAAAAAwABAAIAAAACAAAAAgABAAIAAAACAAAAAgAAAAIAAAACAAAAAgAAAAIAAAABAAAAAQABAAEAAQABAAAAAQAAAAEAAAABAAAAAQAAAAAAAAAAAAEAAAABAAAAAQAAAAAAAAAAAP///////wAA//8AAP////////////8AAP//AAD//wAA/v////7////+//7//v////7//v/+//7//v////7////+//7//v/+//7////+//7//v/+//7////+//7//v/+//7//v/+/////v////7////+/////v////7/////////AAD/////AAD//wAA/////////////wAA//8AAAAAAAAAAAAAAAABAAAAAQAAAAAAAAAAAAEAAQABAAEAAQAAAAAAAAABAAEAAgAAAAEAAAAAAAAAAQABAAEAAQABAAEAAQABAAIAAgACAAEAAQABAAEAAAACAAAAAgABAAIAAQACAAEAAQABAAEAAQACAAEAAgABAAIAAQACAAAAAQAAAAIAAQACAAEAAgABAAIAAAACAAAAAwABAAMAAAACAAAAAgAAAAIAAQADAAEAAgAAAAIAAAACAAAAAgAAAAIAAQACAAEAAgABAAIAAAABAAAAAgABAAIAAQACAAEAAgABAAIAAAABAAAAAQABAAAAAQABAAEAAQABAAEAAQAAAAAAAAAAAP//AAAAAAEAAAAAAP//AAD//wAA//8AAP//AAD//wAA//8AAP7/AAD+/wAA/v8AAP7/AAD+/wAA/v////7////9/////f////3////9/////f////3////9/////f////3////8/////P////z////8//7//P/+//z////8/////P/+//z//v/9/////f/+//z//v/8/////f////3////9/////f////7////+/////v////7////+/////v8AAP7/AAD+/wAA//8AAP//AAD//wAAAAAAAP//AAD//wEAAAABAAEAAQAAAAEAAAABAAEAAgACAAIAAgABAAEAAQACAAEAAwACAAMAAQACAAEAAgACAAIAAQACAAIAAgACAAMAAgADAAEAAwABAAMAAgADAAIAAwACAAMAAgADAAIAAwABAAMAAQADAAEAAwABAAMAAQADAAEAAwABAAMAAQAEAAAABAAAAAQAAAADAAAAAwAAAAMAAQADAAEAAwAAAAMAAAADAAEAAwABAAMAAQACAAEAAgAAAAIAAAACAAAAAgAAAAIA//8CAAAAAgAAAAIAAAACAAAAAQAAAAEAAAABAAAAAgAAAAEA//8BAP//AAAAAAEA//8BAP//AAD/////AAAAAAAAAAD//wAA//////////////////////////////7////+/////v////7////+/////v////3////9//7//f/+//3////9/////f/+//3//v/9/////f////z//v/8/////f////3//v/9//7//f////3////9/////f////z////9/////f////3////9/////f////3////9/////f8AAP3/AAD+/////v////7/AAD+/wAA/v8AAP//AAD+/wAA/v8BAP//AQD//wEA//8BAP//AQAAAAEAAAABAAAAAQAAAAEAAAABAAAAAgAAAAIAAAABAAEAAQAAAAIAAAADAAEAAwABAAIAAQACAAIAAgACAAIAAgACAAEAAgACAAIAAgACAAIAAgACAAIAAgACAAIAAgACAAIAAwABAAMAAQACAAIAAgACAAMAAQADAAEAAwABAAMAAQADAAEAAwABAAMAAQADAAAAAwAAAAMAAAADAAAABAAAAAMAAAADAAAAAgAAAAIAAAACAP//AgD//wIA//8CAAAAAgAAAAMA//8CAP//AQD//wEA//8CAP//AQD//wEA//8CAP//AQD//wEA//8BAP//AQD//wAA//8AAP//AAD//wAA/////wAAAAAAAAAA////////////////AAD////////////////////////////////+/////v////7////9/////f////7/AAD+/////f////3////9/wAA/f8AAP3////9/////f8AAP3////9/////f////3////9/////f////3////+/////f8AAP3/AAD+/////v////7/AAD+/wAA/////////////wAA//8AAP//AAD//wAA//8AAAAAAAAAAAAAAAD//wAA//8AAAAAAQAAAAAAAAAAAAAAAQABAAIAAAACAAAAAQD//wEAAAACAAAAAgABAAIAAQACAAEAAgAAAAIAAAACAAEAAwABAAMAAAACAAEAAgABAAIAAQACAAEAAgABAAIAAQACAAEAAgABAAMAAQACAAEAAgABAAIAAgADAAEAAgABAAIAAQACAAIAAgACAAIAAQACAAEAAgACAAIAAQABAAAAAQABAAEAAQACAAEAAQABAAEAAQABAAIAAQABAAEAAQAAAAAAAAABAAAAAQAAAAEAAQAAAAAAAQAAAAEAAAAAAAAAAQAAAAEAAAAAAP//AAD//wAA//8AAAAA//////////8AAP//AAD//wAA//8AAP7/AAD+/wAA/v8AAP7/AAD+/////v8AAP7/AAD+/////f////7////+/////v////3////+/////v////3////+/////v////3////9/wAA/v////7////+/////v////7//v/+//7//v////7////+//7//f/+//7//////////v/+//7////+///////+/////v///////////////////////////wAA//8AAP//AAD//wAA//8AAP//AAAAAAAAAAAAAAAAAQAAAAEAAAABAAAAAQAAAAIAAAACAAAAAQAAAAIAAAABAAAAAgABAAIAAQACAAEAAgABAAIAAQACAAEAAgABAAIAAQADAAEAAgABAAIAAgACAAIAAwACAAIAAgABAAIAAQABAAIAAQACAAEAAgABAAEAAQABAAEAAgABAAIAAQABAAEAAQAAAAEAAQABAAEAAQABAAEAAQABAAAAAQABAAEAAQABAAEAAQABAAAAAQABAAEAAQAAAAEAAAAAAAAAAQAAAAEA//8AAP//AAAAAAAAAAAAAP//AQD//wEA//8AAP//AAD//wAA//8AAP//////////AAAAAP//AAD///////8AAP////8AAP//AAAAAAAAAAD/////AAAAAAAA//8AAP//AAD//wEA//8AAP//AAD//wAAAAAAAP//AAD//wAA/v8BAP7/AAD+/wAA//8AAP//AAD//wAA/v8BAP//AQD//wAA/v8AAP7/AAD+/wAA//8AAP7/AAD//wAA//8AAP7/AAD+/wAA//8AAP7/AAD+//////8AAP//AAD//wAA//8AAP//AAD//wAA//8AAP//AAAAAAAA//8AAP//AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABAAAAAAD//wAAAAABAAAAAQAAAAEAAAABAAAAAQAAAAEAAAABAAAAAQAAAAEAAAACAAAAAgAAAAIAAAACAAAAAgAAAAEAAAACAAAAAgABAAIAAAABAAEAAQABAAEAAAABAAAAAQABAAEAAQABAAAAAQABAAEAAAABAAAAAAAAAAEAAAAAAAAAAAAAAAAAAAAAAAAAAAD//wAA//8AAAAAAAD//wAA//////////////////////////8AAP//AAD//////v////7////+/////v/+//7////+/////v/+//7//v/+//7//v/+//7//v/+//7///////7////+/////v/+//7////+//////////////////////8AAP//AAAAAAAA//8BAP//AQD//wAA//8BAAAAAQAAAAAAAAAAAAAAAAAAAAAAAQABAAEAAQAAAAEAAQABAAEAAQABAAEAAQACAAEAAQACAAEAAgACAAIAAgACAAIAAQACAAIAAQACAAEAAgACAAIAAgACAAEAAQABAAEAAgACAAEAAgABAAIAAQACAAIAAgACAAIAAQABAAEAAgAAAAIAAAACAAEAAgABAAIAAQACAAEAAgABAAIAAAACAAAAAgAAAAIAAAACAAAAAwAAAAIAAAACAAAAAwAAAAIAAAACAAAAAQAAAAIAAAABAAAAAQAAAAEAAAABAAAAAQAAAAEAAAAAAAAAAAAAAAAAAAAAAP//AAAAAAAAAAAAAP////8AAAAAAAAAAP////8AAP//AAD/////////////AAD//wAA/v8AAP7/AAD+/wAA/v8AAP3/AAD+/wAA/v8AAP3/AAD9/wAA/f8AAP3////9/////f/+//3////9/////f////3////9/////f////3////9//7//f/+//3////9/////f////3//v/8//7//f////3////9/////f////7////+/////v////7////+/////v8AAP7/AAD+/wEA//8AAP//AAD//wAA//8AAP//AQD//wEA//8BAAAAAQAAAAEAAAABAAAAAQABAAEAAQABAAEAAgABAAIAAQACAAEAAgABAAIAAQACAAIAAgACAAIAAQACAAEAAgACAAIAAgACAAIAAgACAAIAAwACAAIAAQACAAIAAwACAAMAAQACAAEAAwABAAMAAQADAAAAAwABAAMAAAADAAAAAgAAAAMAAQADAAAAAwAAAAMAAAADAAAAAwAAAAMA//8DAP//AwAAAAMA//8DAP//AwD//wMA//8DAP//AwD//wMA//8DAP//AwD+/wMA/v8DAP//AwD//wIA/v8CAP//AgD//wIA//8BAP7/AQD//wIA//8BAP//AAD+/wAA//8BAP//AQD//wAA////////AAD////////////////////////+/////v////3////+/////v////7////9/////f////3////9/////f////3////9/////f////3////9/////f////3////9/////f////3////9/wAA/f8AAP3/AAD9/////P////3/AAD+/wAA/v8AAP7/AAD+/wAA/v////3////+/wAA/v8AAP///////wAA//8AAP//AAAAAAAA//8AAP//AQD//wEAAAABAAAAAQAAAAEAAAABAAAAAgAAAAIAAAACAAAAAgAAAAIAAQADAAEAAgABAAIAAQACAAEAAwABAAMAAQACAAEAAwABAAMAAgADAAEAAwABAAMAAQADAAEAAwABAAMAAQADAAIAAwABAAIAAQADAAIAAwABAAMAAQACAAEAAwABAAIAAQACAAEAAgABAAEAAgABAAEAAQABAAIAAgABAAEAAQABAAEAAQABAAEAAQABAAEAAQABAAEAAQABAAAAAQAAAAEAAAABAAAAAQAAAAEA//8BAP//AQAAAAEA//8BAP//AQAAAAEAAAABAP//AQAAAAEAAAABAP//AQD+/wEA//8BAAAAAAD//wAA//8AAP//AAD//wEA//8AAP//AAD///////////7/AAD+///////////////+///////+/////v/+/////v////7//v/+//7//f/+//7//v/+//7//v/+//7//v/+//7//v/+//7//v/+//7//v/+//3//f/9//3//f/+//3//v/9//7//f/+//3//v/9//7//f/+//7//v/+//7//v/+//7//v/+/////f////7//v/+/////v////7////+/wAA//8AAP7/AAD+/wAA//8BAP//AAD//wAA//8BAP//AQD//wEAAAACAAAAAgABAAIAAQABAAEAAgABAAIAAQACAAEAAgACAAIAAgACAAIAAgACAAMAAgADAAIAAgACAAIAAwACAAMAAgADAAIAAwADAAMAAwADAAIAAwACAAMAAwADAAIAAwACAAMAAQADAAEAAwACAAMAAgADAAIAAgABAAIAAgACAAIAAwACAAIAAQACAAEAAgABAAIAAQACAAEAAgABAAEAAQABAAEAAQAAAAEAAAABAAEAAQABAAAAAAABAAAAAQAAAAAAAAAAAAAAAAAAAAAAAQAAAAAAAAAAAAAAAAAAAP//AAD/////AAD/////AAD//wAA///////////+/////v/////////+/////v////7////+/////v////7////+/////v////3////9/////f////3////9/////f////3/AAD9/////f////3////9/////f////3////9/////f////3////9/////f////3////9/////v////7////+/////v////7////+/////v////////////////////////8AAAAAAAD///////8AAAAAAAAAAAEAAAABAAAAAQAAAAEAAAABAAAAAQAAAAEAAQACAAEAAgAAAAIAAAACAAAAAgAAAAIAAAACAAEAAwABAAMAAQADAAEAAwAAAAMAAQADAAEAAwAAAAMAAQADAAEAAwABAAMAAAADAAEAAwABAAMAAQADAAEAAwABAAMAAAACAAEAAgABAAIAAAACAAAAAgABAAIAAQACAAAAAQAAAAEAAAACAAEAAQAAAAEAAAABAAAAAQAAAAEAAAAAAAAAAAAAAAEA//8AAP//AAD//wAAAAAAAP//AAD//wAA//8AAP//AAD/////////////AAD//wAA//8AAP//AAD//wAA//8AAP//////////////////////////////////////////AAD//wAA/v8AAP//AAD//wAA/v8AAP7/AAD//wAA//8BAP//AAD//wAA//8AAP7/AAD+/wEA//8BAP//AQD//wEA//8BAP//AQD//wEA//8BAP//AQD+/wEA//8BAP//AQD+/wEA/v8BAP//AQD//wEA//8BAAAAAQAAAAEAAAABAP//AQAAAAEA//8BAAAAAQAAAAEAAAAAAAAAAAAAAAAAAAAAAAAAAAABAAAAAQD//wAAAAABAAAAAQAAAAEAAAABAAAAAQAAAAEAAAABAAAAAgAAAAIAAAABAAAAAQAAAAEAAAACAAAAAQAAAAEAAAACAAAAAgAAAAEAAAABAAAAAQAAAAIAAAACAAAAAgAAAAEAAAAAAAAAAAAAAAEAAAABAAAAAQAAAAAAAAAAAAAAAQAAAAEA//8AAP//AAAAAP//AAD//wAA//8AAAAAAAAAAAAA//8AAP//AAD//wAA//8AAP//AAD+//////8AAP//AAD//wAA////////AAD//wAA////////AAD//wAAAAAAAP//AAD//wAA/v/////////+/////v////7//////wAA/////////////////////////////wAAAAD///////////////8AAP//AQD//wEA//8AAP//AAD//wAA//8AAP//AAD//wAA//8BAP//AQD//wEA//8AAP//AAD//wEA//8BAAAAAQAAAAEA//8BAP//AgAAAAIAAAABAP//AQAAAAEAAAABAAAAAQAAAAAAAAAAAAEAAQABAAEAAAABAAAAAAAAAAAAAQAAAAEAAAABAAAAAQAAAAEAAAABAAAAAgAAAAEAAAACAAAAAgAAAAIA//8CAP//AgD//wIAAAADAAAAAgAAAAIA//8DAP//AwD//wMA//8CAAAAAgAAAAIA//8DAP//AgD//wMAAAADAP//AwD//wMAAAADAP//AgD//wMAAAACAAAAAgAAAAMA//8CAAAAAQAAAAIAAQACAAEAAQABAAEAAQAAAAEAAAAAAAEAAAAAAAAAAAAAAAAAAAD/////////////AAAAAAAAAAAAAP//AAD+/////v8AAP//AAD//wAA//8AAP//AAD+/wAA/v8AAP3/AAD+/wAA/v////3////9/////v8AAP7/AAD+/wAA/v8AAP7/AAD+/wAA/f////7////+/////v////7////+/wAA/v8AAP7/AAD9/wAA/v////7////+/////v8AAP7/AAD+/wAA/v8AAP//AAD//wAA/v8AAP7/AAD+/wEA//8AAP//AAD+/wAA/v8BAP//AQD//wEA//8BAP//AQD//wEA//8BAP//AgD//wIA//8CAP//AgD//wEA//8CAP//AgAAAAEAAAABAAAAAQAAAAEAAAABAAAAAgAAAAIAAAACAAEAAQABAAEAAQABAAAAAQABAAEAAQABAAIAAQACAAEAAgABAAIAAAACAAAAAwABAAIAAQADAAAAAwAAAAMAAAADAAAAAwAAAAMAAAADAAAAAwAAAAMA//8DAP//AwAAAAMA//8DAP//AwAAAAMAAAADAP//AwAAAAMAAAADAAAAAwAAAAMAAAADAP//AwAAAAMAAAACAAAAAgD//wIA/v8CAP//AgD//wIA//8BAP//AQD//wEA//8AAP//AAD//wAAAAAAAAAAAAAAAP//AAD//////////////////////////////////////v/+//7////+//////////7////+/////v////3////9/////v////7////+//7//v/+//7////+/////v////7////+/////v////7////+/wAA/v8AAP7/AAD+/////v////7////+/wAA/v8AAP7/AAD+/////v8AAP//AAD//wEA//8BAP//AQD//wEA//8BAP//AQD//wEA//8BAAAAAQAAAAEA//8CAP//AgAAAAEAAAABAP//AQD//wEA//8BAAAAAgAAAAIAAAACAAAAAgABAAIAAAACAAAAAgABAAIAAQACAAAAAgABAAIAAgACAAEAAgABAAIAAQACAAEAAgABAAIAAQABAAEAAQABAAEAAQAAAAEAAAACAAAAAQAAAAIAAAACAAAAAwAAAAMA//8DAP//AgAAAAIAAAACAAAAAgAAAAMAAAADAAAAAgAAAAIAAAACAP//AgD//wIA//8CAP//AgD//wIA//8CAP//AQD//wIA//8BAP//AQD//wAA//8BAP//AAD//wAA//8AAP//AAD//wAA//8AAP////8AAP//AAAAAAAA//8AAP//AAD//wAA///////////////////////////+/////v////7////+/////v////7////+/wAA/v8AAP7/AAD9/wAA/f8AAP7/AAD+/wAA/v8AAP7/AAD+/wAA/v8AAP//AQD//wAA//8AAP7/AAD+/wAA//8AAP//AAD+/wAA//8BAP//AAD+/wAA//8AAP//AAD//wAA//8BAAAAAQAAAAAA//8AAP//AAAAAAAAAAAAAP//AAAAAAAAAAABAAAAAQAAAAAAAAAAAAAAAQAAAAAAAAAAAAEAAQAAAAEAAAABAAEAAAABAAAAAQAAAAEAAQACAAAAAQAAAAEAAAACAAEAAgAAAAIAAAACAAAAAgABAAIAAAACAAAAAgAAAAIAAAADAAAAAwAAAAMAAAACAAAAAwAAAAIAAAACAP//AgD//wIA//8CAP//AwD//wMAAAADAAAAAwD//wIA//8CAP//AgD//wIA//8CAAAAAgAAAAIA//8BAP//AgD//wIA//8BAP//AQD//wEA//8BAP//AQD//wAA//8AAAAAAAAAAAAAAAAAAP////////////8AAP//AAD/////////////////////AAD//wAA/v8AAP7/AAD+/wAA/v8AAP3/AAD9/wAA/v8AAP7/AQD+/wEA/f8BAP3/AQD9/wEA/f8BAP3/AAD+/wEA/v8BAP3/AQD9/wEA/v8BAP3/AQD9/wEA/f8BAP3/AQD9/wEA/f8BAP7/AgD+/wIA/v8BAP7/AQD+/wEA/v8BAP7/AQD+/wEA/v8BAP7/AQD+/wEA/v8BAP7/AAD+/wEA//8CAP//AQD//wEA//8BAP//AQAAAAAAAAAAAP//AAD//wAAAAAAAAAAAQAAAAEAAAABAAEAAAABAAAAAQAAAAEAAAABAAAAAQAAAAEAAAABAAAAAQD//wIA//8CAP//AgD//wEAAAACAP//AgD//wMA//8DAP//AwD//wIA//8CAP//AwD//wMA//8DAP//AwD//wMA//8DAP//AwD//wIA//8CAP//AgD//wIA//8CAP//AwD//wMA/v8DAP7/AgD//wMA//8DAP//AgD//wIA//8DAP7/AwD+/wIA//8CAP//AgD+/wIA//8BAP//AQD//wAA/v8BAP7/AQD//wAAAAAAAAAAAAAAAAAAAAAAAP///////////////wAA//////////////////8AAP//AAD//wAA/v8AAP7/AAD+/wAA/v8AAP7/AAD+/wAA/v8AAP7/AAD+/wAA/v8AAP7/AQD+/wEA/v8BAP7/AAD+/wEA/v8CAP7/AQD+/wEA/f8BAP7/AQD//wEA//8BAP7/AQD+/wEA//8BAP//AQD//wEA//8BAP//AQD//wEA//8BAAAAAQAAAAEAAAABAAAAAQD//wEAAAACAAAAAQAAAAEAAAABAAEAAQABAAEAAAABAAAAAQABAAEAAQABAAEAAQABAAAAAQABAAAAAQABAAEAAQAAAAEAAAABAAAAAQAAAAEAAAABAAAAAQAAAAEAAAABAAAAAQAAAAEAAAABAAAAAQD//wEAAAABAAAAAQAAAAEAAAABAAAAAQD//wEA//8BAP//AQD//wAA//8BAAAAAQD//wEA//8BAP//AQD//wAA//8AAAAAAQAAAAEA//8BAP//AAD//wAA//8BAP//AQD//wAA//8AAP//AAD//wEA//8BAAAAAQAAAAAAAAAAAAAA//8AAP////////////8AAP//AAAAAP//AAD//wAA/////wAA//8AAP//AAD///////8AAP//AAD//wAA//////7/AAD+/wAA/v8AAP7/AAD+/wAA/v8AAP7/AQD+/wEA/v8BAP7/AQD+/wEA/v8AAP7/AQD+/wAA/v8AAP//AQD//wEA//8BAP7/AAD+/wEA//8BAP//AQD//wEA//8AAP//AQD//wEAAAABAP//AAD//wEAAAABAAAAAQAAAAEAAAABAAEAAQABAAAAAAABAAEAAQABAAAAAQAAAAEAAQACAAEAAQABAAEAAQABAAEAAgABAAIAAAACAAAAAgAAAAIAAAACAAAAAgAAAAIAAAACAAAAAgAAAAIAAAACAP//AgAAAAIAAAACAP//AgD//wIAAAACAAAAAgD//wIAAAACAAAAAgD//wIAAAACAAAAAgD//wIA//8CAP//AgAAAAIA//8BAP//AQD//wEA//8CAP//AQD//wEA//8BAP//AQD//wEA//8BAP//AQD//wEA//8BAP//AQD+/wEA/v8AAP//AAD//wAA//8AAP//AAD//wAA//8AAP///////wAA//////7//////////////wAA//8AAP/////+//////8AAP//AAD+/////v////3/AAD+/wAA/v8AAP3/AAD9/wAA/f8AAP3////9/////f8AAP3/AAD9/wAA/f8BAP3/AQD9/wAA/f8AAP3/AQD9/wEA/f8BAP3/AQD9/wEA/f8BAP3/AQD9/wEA/f8BAP3/AQD+/wIA/v8BAP3/AgD+/wIA/v8BAP7/AQD+/wIA/v8CAP//AgD//wIA//8CAP//AgD//wIAAAACAAAAAgD//wIA//8CAAAAAgABAAEAAQABAAEAAQABAAEAAgABAAIAAQABAAEAAQABAAIAAQACAAEAAgABAAIAAAACAAAAAgAAAAMAAAADAAAAAgABAAIAAQACAAAAAgAAAAIAAAACAAAAAgD//wIA//8CAP//AgD//wMA//8DAP//AgD//wIA//8CAP//AgD//wIA/v8CAP7/AgD+/wIA//8CAP//AgD+/wIA/v8CAP//AgD//wIA/v8BAP//AgD//wIA/v8BAP7/AQD//wEA//8BAP7/AQD+/wEA/v8BAP//AQD//wEA//8BAP//AQD//wAA/v8AAP//AAD//wEAAAAAAP//AAD//wAA//8AAAAAAAAAAAAAAAD//wAA//8AAP///////wAA//8AAAAAAAD///////8AAP//AAD//wAA//8AAP7/AAD+/wAA/v8BAP7/AQD+/wAA/v8AAP7/AAD+/wAA/v8BAP7/AQD+/wEA/v8BAP7/AQD+/wEA/v8BAP7/AQD+/wEA/v8BAP7/AQD+/wEA/v8AAP7/AAD+/wEA/v8BAP7/AAD9/wAA/v8BAP//AQD//wEA/v8BAP//AQD//wAA//8AAP//AAD//wEA//8BAAAAAAAAAAEAAAABAAEAAQAAAAEAAQABAAEAAQABAAAAAQAAAAEAAAABAAAAAgAAAAIAAAACAAAAAgAAAAIAAAACAAAAAgAAAAIAAQACAAAAAgAAAAIAAAADAAEAAgAAAAIAAAACAAAAAwAAAAIAAAACAAAAAgAAAAIAAAACAAAAAgAAAAIA//8BAAAAAQAAAAEA//8BAP//AgAAAAIAAAABAAAAAQD//wEAAAABAAAAAQAAAAAA//8AAP//AAAAAAAAAAAAAAAAAAD//wAA/////wAAAAAAAP///////wAA//8AAAAAAAD//wAA//8AAP7/AAD+/wAA/v8AAP7/AQD+/wAA/v8AAP//AAD//wAA//8AAP//AAD+/wAA/v8AAP7/AQD+/wEA/v8BAP7/AAD+/wAA/v8BAP7/AQD+/wAA/v8AAP3/AAD+/wAA/v8AAP3/AAD+/wEA//8AAP7/AAD+/wAA/v8BAP//AQD+/wAA/v8AAP//AQD//wEA//8AAP//AQD//wAA//8AAP//AAD//wEA//8BAP//AAD//wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABAAAAAQAAAAEAAAABAAAAAQAAAAEAAAABAP//AQAAAAEAAAACAAAAAgAAAAIAAAABAAAAAgAAAAIAAAACAP//AgD//wIAAAADAAAAAwD//wIA//8CAP//AgD//wIA//8CAP//AgAAAAIAAAADAP//AgD//wIAAAADAP//AgD//wIA//8CAP//AgD//wIA/v8CAP//AgD//wIA//8CAP//AQD//wEA//8CAP7/AQD+/wEA//8CAP//AgD//wEA//8BAAAAAQAAAAEA//8BAP//AQAAAAEA//8AAP//AAD//wAA//8AAP//AAAAAP//AAD//wAAAAAAAAAAAAD//wAA//8AAP//AAD//wAA/v8AAP7/AQD//wEA/v8BAP7/AQD+/wEA/v8BAP7/AQD+/wEA/v8CAP7/AgD+/wIA/f8BAP3/AQD+/wIA/v8BAP7/AQD+/wEA/f8CAP3/AQD9/wEA/v8CAP7/AgD+/wEA/v8BAP7/AgD+/wIA/v8BAP7/AgD//wIA/v8BAP7/AgD//wIA//8BAP7/AQD+/wEA//8BAP//AQD+/wEA//8BAP//AQD//wAA//8AAP//AQAAAAEAAAABAAAAAAD//wEA//8AAAAAAAAAAAAAAQAAAAEAAAAAAAAAAAABAAEAAAAAAAAAAAAAAAEAAAABAAAAAQD//wEAAAABAP//AQD//wIA//8CAP//AgD//wIA//8CAP//AgAAAAIA//8CAP//AgD//wIAAAACAP//AgD//wIA//8CAAAAAwD//wMA//8CAP//AgD//wIA//8CAP//AgD//wIA//8CAP7/AQD+/wEA//8BAP//AgD+/wIA/v8BAP//AQD//wEA/v8AAP//AAD+/wEA/v8BAP7/AQD//wAA//8AAP//AAD//wAA//8AAP///////wAA//8AAP///////////////////////////////wAA//8AAP///////wAA//8AAP//AAD+/wAA/v8AAP7/AAD+/wAA/v8BAP7/AQD+/wAA/f8BAP7/AQD+/wAA/v8AAP7/AAD//wAA//8BAP//AQD//wEA//8BAP7/AQD//wIA//8BAP7/AQD//wEA//8BAAAAAQAAAAEA//8CAP//AgD//wEA//8CAP//AgD//wEA//8BAP//AgD//wIAAAABAAAAAAAAAAEAAAABAAEAAAABAAEAAQABAAEAAAABAAEAAQABAAEAAQABAAEAAQABAAEAAQACAAAAAQAAAAIAAAACAAAAAgAAAAEAAQACAAEAAgAAAAEAAAACAAAAAgAAAAMAAAADAAAAAgAAAAIAAAACAAAAAgAAAAIAAAACAAAAAgAAAAMAAAACAAAAAgABAAIAAAACAP//AgAAAAIAAAACAAAAAgD//wIAAAADAAAAAgD//wEAAAABAAAAAgAAAAEA//8BAP//AQAAAAEAAAABAAAAAQD//wEA//8AAAAAAAAAAAAA//8AAAAAAQAAAAAAAAAAAP//////////AAAAAAAA////////AAD+/wAA///////////+/wAA//8AAP/////+/////v8AAP7/AQD+/wEA/v8AAP7/AAD9/wAA/f8AAP3/AAD9/wAA/f8BAP3/AAD9/wAA/P8BAP3/AQD9/wEA/f8BAP3/AQD9/wEA/f8BAP3/AQD9/wEA/f8BAP3/AQD9/wEA/f8BAP3/AQD+/wEA/v8BAP7/AQD+/wEA//8BAP//AQD+/wAA/v8AAP//AAD//wAA//8AAP//AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP//AAAAAAAAAAABAP//AQD//wEA//8BAAAAAQD//wEA//8CAP7/AgD+/wIA/v8CAP//AgD//wIA//8CAP//AgD//wIA//8CAP7/AgD+/wIA/v8CAP//AwD//wMA//8DAP7/AgD+/wMA//8EAP//AwD//wMA//8DAP//AwD//wMA//8DAP7/AgD+/wIA//8DAP//AwD//wIA//8CAP//AgD//wIA/v8CAP//AgD//wIA//8CAAAAAgAAAAEA//8BAP//AgAAAAIAAAABAAAAAQD//wAAAAAAAAAAAAAAAAAAAAABAAEAAAAAAAAAAAD//wAAAAAAAP//AAD//wEA/v8BAP//AQD//wEA//8BAP//AgD+/wEA/v8CAP7/AgD+/wEA/v8BAP3/AgD9/wIA/f8CAP7/AQD+/wEA/f8CAP3/AwD9/wIA/v8CAP3/AgD9/wMA/f8CAP3/AgD9/wIA/f8DAP3/AgD+/wIA/v8CAP7/AgD9/wIA/v8CAP7/AgD+/wIA/v8CAP7/AgD+/wIA//8CAP//AgD+/wEA//8BAP//AgAAAAEA//8BAP//AQAAAAIAAAABAAAAAQAAAAAAAQAAAAEAAAAAAAAAAAABAAAAAAABAAAAAQD//wEA//8BAP//AQAAAAIAAAACAP//AgD+/wIA//8CAP//AgD//wIA//8CAP7/AgD+/wEA/v8CAP7/AgD+/wMA/v8CAP7/AgD+/wIA/v8DAP7/AgD9/wIA/v8CAP7/AgD+/wIA/v8CAP7/AgD+/wIA/v8CAP7/AQD+/wEA/v8CAP7/AgD+/wIA/f8CAP7/AQD+/wEA/v8CAP//AgD+/wEA/v8BAP7/AQD+/wEA/v8CAP7/AQD+/wEA//8AAAAAAQD//wAA//8AAP//AAD//wEAAAAAAP//AAD/////AAD//wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA//8AAP//AAD//wEA//8BAP//AAD+/wAA/v8BAP7/AQD+/wEA/v8CAP7/AgD+/wEA/f8BAP3/AgD+/wIA/v8BAP3/AQD9/wIA/f8CAP3/AQD9/wEA/f8CAP3/AwD+/wIA/v8CAP3/AgD9/wIA/v8CAP3/AgD9/wIA/v8CAP7/AgD+/wIA/v8CAP7/AgD+/wIA/v8CAP//AgD//wIA/v8CAP//AQD//wEA//8BAP//AQD//wEAAAABAAAAAQAAAAEAAAABAAAAAQABAAEAAQABAAEAAAABAAAAAQD//wIAAAACAAAAAgD//wEA//8CAP//AgAAAAIA//8CAP//AgD//wMA//8CAP//AgD//wMA//8DAP//AwD+/wMA/v8DAP//AwD//wMA//8DAP//AwD+/wMA/v8DAP7/AwD//wMA/v8CAP7/AgD+/wIA//8CAP//AgD//wIA/v8CAP7/AQD+/wIA/v8CAP//AgD//wIA//8BAP//AQD//wIA//8BAP//AQD//wAAAAABAAAAAQAAAAEA//8AAAAAAQAAAAAA//8AAP//AAD//wAAAAAAAAAAAAAAAAAAAAD//wAA/v8AAP//AAD//wAA//8AAP//AQD//wEA//8BAP7/AQD+/wEA/v8AAP7/AQD+/wEA/v8BAP7/AQD+/wEA/v8BAP7/AQD+/wEA/v8BAP7/AgD+/wIA/v8BAP7/AgD+/wIA/v8CAP7/AgD+/wIA/v8BAP3/AgD+/wIA/v8CAP7/AQD+/wIA/v8BAP7/AQD+/wEA/v8CAP7/AgD+/wIA/v8BAP7/AQD//wEA//8BAP7/AQD//wIA//8BAP//AQAAAAEAAAAAAAAAAAD//wEAAAABAAAAAQAAAAAAAAAAAAAAAAAAAAAAAAD//wAAAAABAAAAAQD//wIA//8BAP//AQD//wEA//8BAP//AQD+/wIA/v8CAP7/AgD+/wIA/v8CAP7/AgD+/wIA/v8CAP7/AgD+/wIA/v8CAP7/AgD+/wMA/v8DAP7/AgD+/wIA/v8DAP3/AgD+/wIA/v8CAP7/AgD+/wIA/v8CAP7/AgD//wIA//8CAP//AgD+/wIA//8CAP//AgD//wIA//8CAP//AQD//wEA//8BAAAAAQAAAAEAAAABAAAAAQAAAAAAAAAAAAEAAAAAAAAAAAAAAAAA//8BAP//AQD//wAA//8AAP//AQD//wEA/v8CAP7/AgD+/wEA/v8BAP7/AQD+/wEA/v8BAP7/AQD+/wEA/f8BAP7/AgD+/wIA/v8CAP7/AgD+/wIA/v8BAP3/AgD+/wIA/v8CAP7/AgD+/wIA/v8CAP7/AgD+/wIA/v8DAP7/AgD//wEA/v8BAP7/AgD//wIA//8CAP7/AgD+/wIA/v8CAP7/AgD//wIA//8CAAAAAQAAAAEA//8CAAAAAQAAAAEAAQABAAEAAQAAAAEAAAABAAEAAQABAAEAAQABAAEAAQABAAAAAQAAAAEAAAABAAEAAgAAAAIAAAABAAAAAQAAAAEAAAABAP//AgD//wIA//8CAP//AgD//wIA/v8CAP7/AgD+/wIA/v8CAP7/AQD+/wIA/v8CAP7/AgD+/wEA/v8BAP7/AQD+/wEA/v8CAP3/AgD9/wEA/f8BAP3/AQD9/wEA/v8CAP7/AgD9/wEA/f8BAP7/AQD+/wEA/v8BAP7/AQD+/wAA/v8AAP//AAD//wAA//8BAP//AAD+/wAA////////AAD//wAAAAD//wAAAAAAAAAAAAD//wAA//8AAP//AAD+/wAA/v8BAP//AQD//wEA/v8AAP//AAD//wAA/v8BAP7/AQD+/wEA/v8BAP7/AgD+/wIA/v8CAP7/AQD9/wIA/v8CAP7/AQD+/wEA/v8CAP7/AgD+/wEA/v8BAP7/AgD+/wIA/v8BAP7/AQD//wEA//8BAP7/AgD+/wIA//8BAP//AQD//wEAAAABAAAAAQAAAAEA//8BAAAAAQAAAAEAAAABAAAAAQABAAEAAAAAAAEAAAABAAAAAQAAAAEAAAABAAEAAgABAAEAAAABAAAAAgAAAAIAAQABAAEAAQAAAAIAAAACAAAAAgAAAAMAAAADAAAAAwAAAAIAAAACAAAAAwABAAMAAAACAAAAAgAAAAIAAAADAP//AgD//wIAAAADAP//AgD//wIA//8CAP//AgD//wIA//8CAP//AgD+/wEA/v8CAP//AgD//wEA/v8BAP7/AAD+/wEA//8BAP//AQD//wAA/v8AAP//AAD//wEA//8BAP//AAD+/wAA////////AAD+/wAA////////AAAAAAAA/////////////////////wAA//8AAP//AAD//wAA//8AAP//AAD//wAA/v8BAP7/AQD+/wEA/v8BAP7/AQD+/wEA/v8BAP7/AQD+/wEA/f8CAP3/AgD+/wIA/v8CAP7/AgD+/wIA/v8CAP7/AgD+/wIA/f8BAP3/AQD+/wEA/v8CAP3/AQD9/wEA/f8BAP3/AgD+/wIA/v8CAP3/AQD+/wEA/v8BAP3/AAD9/wAA/v8BAP7/AAD+/wAA/v8AAP7/AQD+/wAA//8AAP7/AAD//wAA//8AAP//AAAAAP//AAD//wAAAAAAAAAAAQD//wAA//8AAP//AQD//wIA//8BAP//AQD//wEA//8CAP7/AgD+/wIA/v8CAP//AwD//wMA//8DAP7/AwD+/wMA//8DAP//AwD+/wMA//8EAP//BQD+/wQA/v8DAP//AwD//wQA/v8EAP//BAD//wQA/v8DAP7/AwD//wQA//8EAP7/AwD//wMA//8DAP//AgD//wIA//8CAP//AgD+/wIA//8CAP//AQD//wEA//8BAP//AQD//wAA//8AAP//AAD//wAA//8AAP//AAAAAAAAAAAAAP//AAAAAAAAAAD//wAA//8AAP//AQD//wEA/v8BAP7/AQD//wEA//8BAP7/AQD+/wEA/f8BAP7/AQD//wIA//8CAP//AgD//wIA/v8CAP7/AwD//wIA//8CAP7/AgD+/wMA/v8DAP//AgD//wMA/v8DAP7/AwD+/wMA/v8DAP7/AwD+/wMA//8CAP//AwD+/wMA//8DAP//AgD+/wIA//8DAP//AgD+/wIA/v8DAP//AgD//wIA//8BAP//AgD//wIA/v8BAP//AQD//wAA/v8BAP//AQD//wAA//8AAP7/AAD//wAA/////////////wAA//8AAP//////////AAD//wAA//8AAP7/AAD+/wAA//8AAP7/AQD+/wAA/v8AAP//AAD+/wEA/v8BAP7/AQD//wEA/v8BAP7/AQD+/wIA/v8CAP7/AgD+/wIA/v8CAP7/AgD+/wIA/v8CAP7/AgD//wIA//8CAP7/AgD+/wIA/v8CAP7/AgD+/wMA//8DAP//AgD+/wIA/v8BAP7/AgD//wEA//8BAP//AQD//wEA//8BAP//AAD//wEA//8BAP//AAD//wAA////////AAD//wAA//8AAP//AAD////////////////////////+//////8AAP//AAD+/////v8AAP7/AAD+/wAA/v8AAP7/AAD+/wEA/v8AAP7/AAD+/wEA/v8BAP7/AQD9/wIA/v8BAP7/AQD+/wIA/v8CAP7/AQD9/wIA/v8CAP//AgD+/wIA/v8CAP//AgD+/wIA/v8CAP7/AgD//wMA//8EAP//AwAAAAIA//8CAP//AwAAAAMAAAADAAAAAwAAAAMA//8CAP//AgAAAAIAAAACAAAAAgAAAAIAAAABAAAAAQABAAEAAQABAAEAAQABAAEAAQABAAEAAAABAAAAAQAAAAEAAAABAAAAAQD//wIAAAACAP//AgD//wEA//8CAAAAAwD//wIA/v8CAP//AgD//wIA//8CAP//AgD+/wMA/v8CAP7/AgD+/wMA/v8DAP7/AwD+/wMA/v8DAP7/AwD+/wIA/v8DAP7/AwD+/wMA/v8CAP7/AgD+/wIA/v8CAP7/AwD+/wIA//8CAP//AgD+/wEA//8BAP//AQD//wEA/v8BAP//AgD//wEAAAABAAAAAQD//wEA//8BAAAAAAAAAAAA//8AAAAA//8AAP//AAD///////8AAP//AAD+/wAA//8AAP//AAD+/wAA/v8AAP7/AAD+/wAA/v8AAP7/AAD9/wAA/f8AAP3/AQD9/wAA/f8AAP3/AAD9/wEA/f8BAP3/AAD9/wEA/f8BAPz/AQD8/wEA/P8BAP3/AQD9/wEA/f8BAP3/AgD9/wIA/f8CAP3/AgD9/wIA/f8CAP3/AgD+/wIA/v8CAP3/AgD9/wIA/v8CAP7/AgD//wIA/v8CAP7/AgD//wIA//8CAP//AgD//wIA//8BAAAAAgAAAAIAAAACAAAAAQAAAAEAAAACAAAAAgAAAAEAAQAAAAEAAAABAAEAAgABAAEAAAABAAAAAgD//wEA//8BAP//AgD//wIA//8CAP//AgD//wIA//8CAP7/AgD+/wIA/v8CAP7/AgD+/wIA/f8CAP3/AgD+/wIA/v8CAP3/AwD9/wIA/f8CAP3/AgD9/wIA/f8CAP3/AgD9/wIA/f8CAP3/AgD9/wIA/v8DAP7/AgD9/wIA/v8CAP7/AgD+/wIA/v8BAP3/AQD+/wIA/v8CAP//AgD+/wEA//8BAP//AgD//wEA//8BAP//AQD//wEA//8AAAAAAAAAAAEAAAABAAAAAQAAAAEAAQAAAAEAAAABAAAAAQAAAAEA//8BAP//AgD//wEA//8BAP//AQD//wEA//8BAP7/AQD//wIA//8CAP//AQD+/wEA/v8BAP3/AgD+/wIA/v8CAP7/AgD+/wIA/v8CAP7/AgD9/wIA/f8DAP7/AgD+/wIA/v8DAP7/AgD9/wIA/f8CAP7/AwD+/wIA/v8BAP7/AgD+/wIA/v8CAP7/AwD//wIA//8CAP7/AgD//wIA//8CAP//AgD//wIAAAACAAAAAQAAAAEAAAABAAAAAQAAAAIAAAACAAEAAQABAAEAAQABAAEAAQABAAAAAAAAAAEAAQABAAEAAgAAAAIAAAABAP//AQAAAAIAAAACAP//AgD//wEA//8CAP7/AgD+/wIA//8BAP7/AQD+/wIA/v8CAP7/AgD9/wIA/f8CAP3/AgD9/wEA/f8CAP3/AgD9/wIA/f8CAP7/AgD+/wIA/v8CAP3/AQD9/wEA/f8BAP3/AQD9/wEA/f8BAP3/AQD9/wEA/f8AAP3/AQD9/wEA/v8BAP7/AQD+/wEA/v8AAP3/AAD+/wAA/v8AAP7/AAD+/wAA/v8AAP7/AAD/////////////AAD//wAA//8AAAAA//8AAP//AQD+/wEA/v8BAP7/AAD//wAA//8BAP//AQD//wEA/v8BAP7/AgD+/wIA/v8BAP7/AQD+/wEA/v8BAP7/AgD+/wIA/v8CAP7/AgD+/wIA/v8DAP7/AgD9/wIA/f8DAP7/AwD+/wMA/v8CAP7/AgD+/wMA/v8DAP7/AgD//wIA//8CAP//AwD//wIA//8BAP//AgAAAAIAAAACAAAAAQD//wIAAAACAAAAAQAAAAEAAAACAAEAAQABAAEAAAABAAEAAgABAAIAAQACAAIAAQABAAEAAgABAAIAAAACAAAAAgABAAIAAQACAAAAAgAAAAIAAAADAP//AwD//wMAAAADAAAAAgD//wIAAAADAP//AwD//wMA//8DAP//AgD//wIA//8CAP//AwD//wIA//8CAP//AwD+/wMA/v8CAP7/AgD+/wMA/f8CAP3/AgD+/wEA/v8CAP7/AgD+/wEA/v8BAP7/AQD+/wEA/v8AAP7/AAD+/wAA/v8AAP7/AAD+/wAA/v8AAP7/AAD+/wAA//8AAP//AAD///////////////////////////////8AAP///////////v/////////+/wAA/v8AAP//AQD//wAA//8BAP//AQD//wIA/v8BAP7/AQD//wEA//8BAP7/AQD+/wIA//8CAP7/AgD+/wIA/v8DAP7/AwD+/wMA/v8DAP7/AwD+/wIA/v8CAP7/AwD+/wMA/v8DAP7/AwD+/wIA/v8CAP3/AgD+/wIA/v8CAP7/AgD+/wIA/v8CAP7/AgD+/wIA//8BAP//AQD//wEA//8BAP//AQD//wAA//8AAP//AAD//wAAAAAAAAAAAAAAAAAA//8AAAAAAAAAAP//AAD//wAA//8BAP//AQD//wAAAAAAAP//AQD//wEA//8BAP7/AgD+/wIA//8CAP//AgD+/wIA/v8CAP7/AwD//wMA//8DAP//AwD//wMA/v8CAP//AgD//wMA/v8DAP7/AwD+/wMA/v8DAP7/AwD//wIA/v8CAP7/AgD//wMA//8DAP7/AgD+/wIA/v8DAP7/AgD//wIA//8CAP7/AgD//wEA//8BAP//AAD//wAA//8BAP//AQD//wAA/v8AAP7/AAD//wAA//8AAP////////////8AAAAAAAAAAAAA//////////8AAP//AAD+/wAA/v8AAP7/AAD//wAA/v8AAP7/AQD//wEA/v8AAP7/AQD+/wEA//8CAP7/AgD+/wIA/v8BAP7/AQD+/wIA//8DAP//AgD//wIA//8DAP//AwD//wIA//8CAP//AgD//wIAAAADAAAAAwD//wMA/v8DAP//AgAAAAIAAAACAP//AwD//wIA//8CAP//AgD//wMAAAACAP//AgD//wIA//8CAAAAAgAAAAIAAAABAP//AQD//wIAAAABAAAAAAAAAAAAAAABAAEAAQABAAAAAAAAAAAAAAAAAAAAAAAAAAEA//8BAP//AQAAAAEAAAABAP//AQD+/wEA/v8BAP//AQD+/wEA/v8BAP//AgD+/wIA/v8CAP//AgD+/wIA//8CAP//AgD//wIA/v8CAP7/AgD+/wIA/v8BAP7/AQD+/wIA//8BAP//AQD+/wIA/v8CAP7/AQD//wEA/v8CAP7/AgD+/wEA/v8BAP7/AAD+/wAA//8BAP//AAD//wAA//8AAP//AAD//wAA//8AAP//////////////////AAD+/////v/////////////////+/////v////7////+/////v////7////+/wAA/v8AAP3/AAD9/wAA/v8AAP7////9/wAA/v////7////9/wAA/f8AAP7/AAD+/wAA/f8BAP7/AQD+/wEA/v8BAP//AQD//wEA//8BAP//AQD//wEA//8BAP//AQD//wIA//8CAP//AQD//wEA//8CAAAAAgAAAAIAAAACAAAAAgAAAAIAAAACAAAAAgABAAMAAQACAAEAAgAAAAIAAQACAAEAAgABAAIAAAACAAEAAgABAAIAAgABAAIAAQABAAEAAQABAAEAAgABAAEAAQABAAEAAQACAAAAAgABAAIAAQACAAAAAgABAAIAAAACAAAAAgD//wIAAAACAAAAAgD//wIA//8CAAAAAgD//wIA//8CAP//AgD//wIA//8CAP//AgD//wIA//8CAP7/AgD//wIA//8CAP//AgD//wIA//8CAP//AgD+/wIA//8CAP//AwD//wIA//8CAP7/AQD+/wEA//8BAP//AQD+/wEA/v8BAP//AQD//wEA//8AAP//AAAAAAAA//8AAP//AAAAAAAAAAAAAAAAAAD/////AAD//wAA///////////+/wAA//8AAP//AAD+/wAA//8BAP7/AAD+/wAA/f8AAP7/AQD+/wEA/v8BAP3/AQD9/wAA/P8BAPz/AQD8/wEA/P8BAP3/AQD9/wEA/f8BAPz/AQD9/wAA/P8AAP3/AQD9/wAA/f8AAPz/AQD9/wEA/f8BAP3/AQD+/wEA/v8AAP7/AAD9/wEA/v8BAP7/AQD+/wEA/v8BAP7/AQD//wEA//8AAP//AAD//wEAAAABAAAAAQAAAAAAAAAAAAAAAQABAAEAAQAAAAAAAQAAAAEAAAAAAAEAAQABAAEAAQAAAAEAAAABAAEAAgAAAAIAAAABAAAAAgAAAAIAAAACAAAAAgD//wIA//8CAP//AgD//wIA//8CAP//AgD//wIA//8CAP//AgD//wIA//8CAP7/AgD+/wIA//8CAP//AgD//wIA//8CAP//AgD+/wIA/v8DAP3/AgD+/wIA//8CAP//AgD//wIA/v8CAP7/AgD+/wIA//8DAP//AgD//wIA//8CAP//AQD//wEA//8CAP//AgD//wEA//8BAP//AgAAAAIA//8BAP//AQAAAAEAAAABAP//AAAAAAAAAAABAAAAAAABAAAAAQAAAAEAAAABAAAAAQAAAAEAAAAAAAAAAQD//wEA//8CAP//AgD//wIA//8BAP7/AQD+/wEA/v8BAP3/AQD9/wEA/v8BAP3/AgD9/wIA/v8CAP3/AgD9/wIA/f8CAP3/AgD9/wMA/f8CAP3/AgD9/wIA/f8CAP7/AgD+/wIA/f8CAP7/AgD+/wIA/v8CAP7/AgD+/wIA/v8BAP//AQD//wEA//8CAP//AQD//wEA//8BAP//AQD//wEA//8BAAAAAQAAAAAA//8AAAAAAQABAAAAAQAAAAAAAAABAAAAAQAAAAEAAAACAAAAAgAAAAEAAAACAAAAAgAAAAIAAAACAP//AgD//wEAAAACAAAAAgAAAAIA//8BAP//AQD+/wEA/v8CAP7/AgD//wIA/v8CAP7/AgD+/wEA/v8CAP7/AgD+/wEA/v8BAP7/AgD9/wIA/f8CAP7/AgD+/wEA/v8BAP3/AgD+/wIA/v8CAP7/AgD+/wEA/v8AAP7/AAD+/wEA/v8BAP7/AAD+/wAA/f8BAP7/AQD+/wAA/v8AAP7/AAD+/wAA//8AAP//AAD//wAA//8AAP///////wAA//8AAP//////////AAD//wAA/v8AAP7/AAD//wAA//8AAP//AQD//wEA//8AAP//AQD+/wEA/v8BAP7/AQD+/wEA/v8BAP7/AQD+/wEA/v8CAP7/AQD9/wEA/f8BAP7/AgD+/wIA/f8CAP3/AgD9/wMA/f8CAP7/AgD//wIA//8CAP7/AgD+/wIA//8CAP7/AgD//wIA//8CAP//AQD//wIAAAACAP//AgD//wIAAAACAAAAAQAAAAEAAAABAAEAAQABAAEAAAABAAEAAQABAAEAAgABAAIAAQACAAEAAgAAAAIAAAACAAEAAgAAAAIAAAACAAAAAgAAAAIAAAACAAAAAgAAAAIAAAACAAAAAgD//wIAAAACAAAAAgD//wIA//8CAP//AgD//wIA//8CAP//AgD//wIA//8BAP//AgD//wIA//8CAP7/AgD//wEA//8CAP//AgD//wIA//8CAP//AQD//wEA//8CAP//AQD+/wEA//8BAP//AQD//wEA//8BAP//AAD//wAA/v8AAP//AAD//wAA//8AAP////////////8AAAAAAAD///////8AAP//AAD//////////wAAAAAAAP///////wAA/v8AAP//AAD//wAA//8AAP//AAD//wAA/v8AAP7/AAD+/wAA/v8AAP7/AAD+/wAA/v8BAP7/AQD+/wIA//8BAP//AQD+/wIA/v8CAP//AgD+/wIA/v8CAP7/AQD+/wEA/v8CAP7/AgD+/wEA/v8BAP7/AgD+/wIA//8BAP//AQD+/wEA//8CAP//AgD+/wIA/v8BAP//AQD//wEA//8BAP//AQD+/wEA//8BAP//AQAAAAAAAAAAAAAAAQAAAAAA//8AAP//AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAEAAAABAP//AAD//wAA//8BAP//AQD//wEA/v8BAP7/AgD+/wEA/v8BAP7/AgD+/wIA/v8CAP//AgD+/wIA/v8CAP7/AgD//wIA/v8BAP7/AgD//wIA//8CAP7/AQD+/wIA/v8CAP7/AgD+/wEA//8BAP//AgD//wIA//8BAP//AQD//wEA//8BAP7/AQD+/wEA/v8BAP7/AQD//wEA//8BAP7/AQD+/wAA//8AAP//AAD//wAA//8AAP//AAAAAAEA//8AAP//AAAAAP//AAD//wEAAAAAAAAAAAD//wAA//8BAAAAAAAAAAAA//8BAAAAAQD//wAA//8AAP//AQAAAAEA//8BAP//AQAAAAEAAAABAP//AQAAAAEA//8BAP//AQD//wIAAAACAP//AgD//wIAAAACAP//AgD//wMAAAACAAAAAgD//wIA//8DAP//AgD//wIAAAACAP//AgD//wMA//8CAP//AgD//wIAAAACAP//AgD//wIA//8CAP//AgD//wEA//8BAP//AgD//wIAAAACAP//AQD//wEA//8BAP//AQD//wEA//8BAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP//AAD//wAA//8BAP//AQD//wEA//8AAP//AAD//wEA//8BAP//AQD//wIA//8CAP7/AQD+/wEA/v8CAP7/AgD+/wIA//8BAP7/AQD+/wEA//8CAP7/AQD+/wEA/v8BAP//AQD//wEA/v8BAP7/AAD//wEA//8BAP7/AAD+/wAA//8BAP7/AAD+/wAA//8AAP//AAD+/wAA//8AAP//AAD//wAA//8AAP//AAD+/////v//////AAD//wAA/////////v8AAP///////////v//////AAD//////////////////wAA//8AAP//AAD//wAA//8AAP//AAD//wAA//8AAP//AAD+/wAA/v8AAP//AQD//wAA//8AAP//AAD//wAA//8AAP7/AAD//wEA//8AAP//AAD//wAA//8BAP//AAD//wAA/v8BAP//AQD//wEA//8BAP//AQD//wEA//8BAAAAAQAAAAIA//8CAP//AgAAAAIAAAACAAAAAQAAAAEAAAABAAAAAQABAAEAAAABAAAAAQAAAAIAAQACAAEAAQABAAIAAQACAAAAAgAAAAEAAQABAAEAAgABAAEAAQABAAEAAQABAAEAAQABAAIAAQACAAEAAgABAAIAAQACAAAAAwAAAAIA//8CAAAAAgAAAAMAAAADAAAAAwAAAAIA//8CAP//AgD//wMA//8DAP//AwD//wMA//8DAP//AwD//wMA//8DAP7/AgD//wIA//8CAP//AgD+/wIA//8CAP//AQD//wIA//8BAP//AQD//wEA//8BAP//AQD//wEA//8AAP//AAD//wAA//8BAP//AAD//wAA//8AAP////8AAP//AAD+//////8AAP7/AAD+/wAA/v8AAP7/AAD+/wAA/f8AAP3/AAD+/wAA/v8AAP3/AAD9/wAA/v8AAP7/AAD+/wEA/v8BAP3/AAD9/wAA/f8AAP3/AAD9/wAA/f8BAP3/AQD+/wEA/v8BAP3/AQD9/wEA/v8AAP7/AAD+/wEA/v8BAP3/AQD9/wEA/v8BAP7/AQD+/wEA//8BAP//AQD+/wEA/v8BAP//AQD//wAA//8BAP//AQAAAAAAAAAAAAAAAAAAAAAAAAABAAAAAgAAAAEAAAABAAAAAAAAAAEAAAABAAEAAQABAAEAAAAAAAAAAAAAAAAAAAABAAEAAQABAAEAAQABAAEAAQABAAAAAgAAAAEAAAABAAAAAQAAAAEAAAABAAAAAQAAAAEAAAABAAAAAQAAAAEAAAABAP//AgD//wIA//8CAP//AgD//wIA//8CAP//AgD//wIA//8CAP//AgD//wIA//8CAP//AgD//wIA//8CAP7/AgD//wIA//8CAP//AgD+/wIA/v8CAP//AgD+/wEA/v8CAP//AgD//wEA//8BAP//AQD//wEA//8BAP//AQD//wAA//8AAP//AAD//wAA//8AAP//AQD//wAA//8AAP//AAD///////////////8AAAAAAAD//wAA//8AAP//AAD//wAA/v8AAP//AAD//wAA/v8AAP7/AAD+/wAA/v8AAP7/AQD+/wEA/v8BAP7/AQD+/wEA/v8CAP3/AQD9/wEA/v8CAP7/AgD+/wEA/v8BAP7/AgD+/wIA/v8CAP7/AQD+/wIA//8BAP//AQD//wIA//8CAP//AQD//wEA//8CAP//AgAAAAEA//8BAP//AgD//wEA//8BAP//AgAAAAEA//8BAP//AQAAAAEAAAABAAAAAQAAAAEAAAABAAAAAQABAAEAAQABAAAAAAAAAAAAAAAAAAAAAQABAAEAAQAAAAAAAAAAAAAAAAAAAAEAAAABAAAAAgD//wEA//8BAAAAAQAAAAIA//8BAP//AQD//wEA//8BAP7/AQD//wEA//8BAP7/AQD+/wEA//8BAP//AQD//wEA//8BAP7/AQD+/wEA/v8BAP//AQD+/wEA/v8BAP//AQD//wEA/v8AAP//AQD//wEA//8BAP7/AAD//wAA//8AAP7/AAD+/wAA/v8BAP//AAD//wAA//8BAP//AAD//wAA//8BAP//AQD//wAA//8AAP//AAAAAAEA//8BAAAAAQAAAAEA//8AAP//AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP//AQAAAAEA//8AAP//AQD//wEAAAABAP//AQD//wIA//8BAP//AQD//wEA//8BAP//AQAAAAEA//8BAP//AQD//wEA//8BAP//AQD//wEAAAABAP//AQD//wEA//8BAP//AQD//wIA//8BAP//AQD+/wEA//8BAAAAAQD//wEA//8CAP//AQD//wEA//8BAP//AQAAAAAA//8AAP//AAAAAAEAAAABAAAAAAAAAAAAAAAAAAAAAQAAAAAAAAAAAAEAAAAAAAAAAAAAAAEAAAABAAAAAAAAAAEA//8AAP//AAAAAAEA//8BAP//AQD//wEA//8BAP//AQD//wEAAAABAAAAAQD//wIA//8CAP//AgD//wIA//8CAP7/AQD+/wIA//8CAP7/AgD+/wEA/v8CAP//AgD+/wEA/v8BAP7/AgD+/wIA/v8BAP7/AQD+/wAA//8AAP//AAD+/wAA/v8AAP//AAD//wAA//////////8AAP//AAD//wAA//8AAP//AAD///////////////////////8AAP//AAD//wAAAAAAAP//AAD//wEA//8AAP//AAD+/wEA/v8BAP//AQD//wEA/v8CAP7/AQD//wEA//8CAP//AgD+/wIA//8CAP//AgD//wIA/v8CAP7/AgD//wIA//8CAP//AgD//wIA//8CAP//AgAAAAIAAAACAAAAAgD//wIAAAABAAAAAQD//wEA//8CAAAAAQAAAAAA//8BAAAAAQAAAAEAAAABAAAAAQAAAAAA//8AAAAAAQAAAAAAAAAAAAAAAAAAAAAA////////AAAAAAAAAAAAAAAAAAAAAAAA/////wAAAAABAAAAAQD//wAAAAAAAAAAAQD//wAA//8AAP//AQD//wAA//8AAP//AQD//wEA//8BAP//AQD//wEA//8AAP//AQD//wEA/v8BAP7/AQD//wEA//8BAP7/AQD+/wEA/v8CAP7/AgD+/wEA//8BAP//AgD+/wEA/v8BAP//AQD//wIA//8CAP7/AQD//wEA//8CAP//AgD//wIA//8CAP//AgD//wIA//8BAP//AQD//wEA//8BAP//AQD//wAA//8BAP//AQD//wAAAAD//wAA//8AAAAAAAD//wEA//8BAP//AQD//wAA//8AAP7/AQD+/wEA//8BAP//AQD//wEA//8BAP//AQD+/wEA/v8CAP7/AgD+/wEA/v8BAP7/AgD+/wIA/v8BAP7/AQD+/wEA/v8CAP7/AgD+/wIA//8BAP7/AgD+/wIA/v8BAP7/AQD//wIAAAACAAAAAQAAAAEAAAACAP//AgD//wIAAAACAAAAAQAAAAEAAAABAAAAAQAAAAEAAAABAAAAAQAAAAEAAAABAAEAAQABAAAAAQABAAEAAAABAAAAAQAAAAEAAAABAP//AQD//wIA//8CAAAAAQD//wEAAAACAP//AQD//wEA//8BAP//AQAAAAEAAAABAP//AQD//wAA//8AAAAAAQD//wEA//8AAP//AAD//wAA//8AAP//AAAAAAEA//8BAP//AAD//wEA//8BAP//AQD//wEA//8BAP//AQD//wEA//8BAP//AQD+/wEA/v8AAP//AQD//wEA//8AAP//AAD//wAAAAAAAP//AAD//wAA//8AAP//AAD//wAA//8AAP////8AAAAA//8AAP//AAD///////8AAAAAAAAAAP//AAD//wAA//8AAP//AAD//wAA//8AAP//////////AAAAAP//AAD//wAA/v8BAP7/AAD//wAA//8AAP//AAD//wEA/v8AAP7/AQD+/wEA//8AAP//AQD//wEA//8BAP//AQD//wEA//8CAP//AQD//wEA//8CAP//AQD//wEA//8CAP//AgAAAAEAAAABAP//AQD//wEAAAACAAAAAgAAAAIAAAABAAAAAQABAAEAAQABAAEAAQABAAIAAQABAAEAAQABAAEAAgABAAIAAAABAAAAAgABAAIAAQACAAEAAQABAAIAAQACAAEAAwABAAIAAAACAAEAAgABAAIAAAACAAAAAgAAAAIAAAACAAAAAgAAAAIAAAABAAAAAQAAAAEA//8BAP//AQAAAAEAAAABAP//AAAAAAEAAAABAP//AAD//wAA//8AAP//AAD//wAA//8AAP////////////8AAP7/AAD+////////////AAD+/wAA/v8AAP//AAD//////v///////////////v//////////////////////AAD//wAA//8AAP7////+//////////////////////8AAP//AAD//////////////////wAA////////////////AAD/////AAAAAP//AAD/////////////AAAAAAAA//8AAP//AAD//wAA/v8AAP//AAD//wAA//8AAP7/AAD+/wAA//8BAP//AQD//wEA//8BAP//AQD+/wEA/v8CAP//AgD//wIA//8BAP//AgD//wIA//8BAP//AQD//wIA//8CAP//AgAAAAIA//8CAP//AgD//wEAAAACAAAAAgAAAAEAAAABAAAAAgABAAEAAQABAAEAAQABAAIAAQABAAEAAQABAAAAAQAAAAIAAAACAAEAAQAAAAEAAAACAAEAAgABAAIAAAACAAAAAgD//wIAAAACAAAAAgD//wIA//8BAP//AgD//wIA//8BAAAAAQD//wEA//8BAP//AQAAAAEAAAABAAAAAQAAAAEAAAAAAP//AAD//wEA//8BAP//AAD//wAA//8AAAAAAAD//wAA//8AAP//AAD//wAA//8AAP//AAD//wAA/////////////////v8AAP7/AAD+/wAA//8AAP//AAD/////AAAAAP//AAD+/////v//////AAD//wAA//8AAP//AAAAAAAAAAAAAAAAAQAAAAEA//8AAAAAAQAAAAEAAAABAAAAAQAAAAEA//8AAP//AAAAAAEAAAABAAAAAQAAAAEAAAABAAAAAAABAAAAAQABAAEAAAABAAAAAAAAAAAAAAABAAAAAQAAAAEAAAABAAAAAQAAAAIAAAABAP//AQD//wIA//8BAP//AQD//wIA//8CAP//AgD//wIA//8CAP//AgD//wIA//8BAP//AgD//wIA//8BAP//AQD//wIAAAACAAAAAQD//wEA//8CAAAAAQAAAAEAAAABAAAAAQD//wEAAAABAAAAAQAAAAEAAAAAAAAAAQABAAAAAQAAAAEAAAABAAAAAAAAAAAAAQABAAEAAQAAAAEAAAABAAAAAQAAAAEAAAABAAAAAQD//wEAAAABAAAAAQAAAAEAAAABAAAAAQD//wEA//8BAP//AAD//wAAAAABAAAAAQD//wAA//8AAP///////wAA//8AAP//AAD//wAA/////////////wAA/////////////////v////7//v///////////////v////7////////////////////////////+//////////////////7//v//////AAD/////////////AAD//////////wAAAAD//wAA////////AAAAAAEA//8BAP//AAAAAAAAAAAAAP//AAD//wEA//8AAP//AAD//wEA//8AAP//AAAAAAAAAAABAAAAAQAAAAAAAAAAAAAAAAAAAAEAAAABAAEAAQABAAEAAQABAAAAAQABAAEAAQABAAEAAAABAAAAAgAAAAEAAAABAAAAAgAAAAIAAAABAP//AQD//wIAAAACAAAAAgAAAAIAAAABAAAAAQD//wIAAAACAAAAAgAAAAIAAAACAAEAAQAAAAEAAAABAAEAAQAAAAEAAAABAAAAAQABAAAAAAAAAAAAAQABAAEAAAABAAAAAQAAAAEAAQABAAEAAQABAAAAAQAAAAAAAQAAAAEAAQAAAAAAAAAAAAAAAQAAAAEA//8BAP//AQAAAAAA//8AAP//AQD//wEA//8AAP//AAAAAAEAAAAAAP//AAD//wAA//8AAP//AAD//wAA//////////8AAP//AAD/////////////AAD//wAA/v8AAP///////////v8AAP7/AAD///////8AAP//AAD//wAA////////////////////////AAD//wAA//8AAP//AAD//wAA//8AAP//AAD//wAAAAAAAAAA//8AAP//AAD///////8AAAAAAAAAAAEAAAAAAAAAAQAAAAAAAAAAAAAAAAAAAAEAAAABAAAAAQAAAAEAAAABAAAAAgAAAAEAAAABAAEAAQABAAEAAAABAAAAAQAAAAEAAAABAAAAAQAAAAEAAQABAAEAAQAAAAEAAAABAAEAAQABAAEAAAABAAEAAQABAAEAAQABAAEAAQABAAEAAAABAAAAAAABAAAAAQAAAAAAAAAAAAEAAAABAAAAAAAAAAEAAAABAAAAAAAAAAAAAQABAAEAAAAAAAAAAAAAAAAAAAAAAAAA//8AAP//AAD//wAA//8AAAAAAAD//wAA//8AAP//AAD//wAAAAAAAP//AAD//wAA//8AAP//AAD//wAAAAAAAP//AAD//wAAAAD/////////////AAAAAAAAAAD//wAA/////wAAAAAAAP///////wAAAAAAAP//AAD///////8AAAAAAAD//////v////7//////wAA/v8AAP7/AAD//wAA//8AAP7/AAD+/wEA//8AAP7/AAD//wAA//8AAP//AAD+/wAA//8BAP//AQD+/wAA//8BAP//AQD//wAA//8BAP//AQAAAAEA//8BAP//AQAAAAAA//8AAP//AQAAAAEAAAABAAAAAAABAAAAAQAAAAAAAAAAAAAAAQAAAAEAAAAAAAAAAQABAAEAAAABAAAAAQAAAAEAAQABAAAAAQAAAAIAAAACAAEAAgABAAIAAAABAAEAAQAAAAIAAAABAAAAAQABAAEAAQABAAAAAQABAAEAAQACAAEAAgAAAAEAAAABAAEAAQABAAEAAAABAAAAAQABAAEAAQABAAEAAQAAAAAAAAABAAAAAQABAAEAAQAAAAEAAAABAAEAAQABAAEAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP//AAAAAAAAAAAAAP//AAD///////8AAAAAAQAAAAAAAAAAAP//AAD//wAA/v8AAP7/AAD//wAA//8AAP//AAD//wAA/////////////wAA/////////////wAA//8AAP////////////8AAP//////////////////AAD//////v/////////////////+/////v8AAP//AAD//wAA/v8AAP//AAD//wAA//8AAP//AAD//wAA//8AAP//AAD//wAA/v8AAP7/AAD//wAA/v8AAP7/AQD//wEA//8AAP7/AQD+/wAA//8AAP//AQD//wEA//8AAP//AAD//wAAAAABAP//AAD//wAA//8BAAAAAQAAAAAAAAAAAAEAAQAAAAEAAAABAAAAAAAAAAAAAAAAAAAAAAAAAAEAAQABAAEAAQABAAEAAQABAAEAAAABAAEAAQABAAEAAQABAAEAAQABAAEAAQACAAEAAgABAAEAAQABAAEAAgAAAAEAAAABAAEAAgABAAIAAAABAAEAAQABAAIAAQABAAEAAQABAAEAAQABAAEAAQABAAEAAQABAAEAAAABAAAAAQABAAEAAQAAAAAAAAAAAAAAAAABAAEAAQABAAEAAQABAAEAAAAAAAAAAAABAAAAAAAAAAAAAAAAAAAAAAAAAP//AAD//wAAAAAAAAAAAAD//wAAAAAAAAAAAAD//wAA//8AAP//AAD//wAA//8BAP//AQD//wAA//8AAP//AAD//wAA//8AAP//AAD//wAA//8AAP//AAD//wAA//8AAP//AAD//wAA//8AAP////////////////////8AAP//AAD//wAA//////7////+/wAA//8AAP///////////////////////wAA//8AAP//AAD//wAA//8AAP//AAD//wAA//8AAP//AAD//wAA//8BAP//AAD//wAA//8AAP//AAD//wAA//8BAP//AAAAAAAAAAAAAAAAAAAAAAAAAAABAAAAAAABAAAAAQABAAAAAQABAAAAAQAAAAEAAAABAAEAAQABAAEAAQABAAAAAQAAAAEAAQABAAEAAgAAAAIAAQACAAEAAQAAAAEAAAABAAEAAQABAAEAAAABAAAAAQABAAEAAAABAAAAAQAAAAEAAAABAAAAAQABAAEAAQABAAAAAQAAAAEAAAABAAAAAAAAAAAAAQABAAEAAAABAAAAAAABAAAAAQAAAAAAAQAAAAEAAAABAAAAAAAAAAAAAAABAAAAAAD//wAAAAAAAP//AQD//wEAAAABAAAAAAD//wAA//8AAP//AAD//wAAAAAAAP//AAD//wAA//8AAAAAAAAAAP////////////8AAP////8AAP//AAAAAP//AAD////////////////////////////////+/////v//////AAD//wAA/v////7////+/////////////////wAA/////////////wAAAAAAAP//////////AAD//wAA//8AAP//AAD//wAA//8AAP//AQD//wEAAAAAAAAAAAD//wAAAAABAAAAAAD//wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABAAAAAQAAAAAAAAABAAAAAQABAAEAAAAAAAAAAQABAAAAAQAAAAAAAQABAAEAAQAAAAEAAAABAAAAAQAAAAIAAAABAAAAAQAAAAIAAQABAAEAAQAAAAEAAAACAAAAAQAAAAEAAAABAAAAAQAAAAEAAAABAAAAAQAAAAEAAAABAAAAAQAAAAEAAAABAAEAAQAAAAAAAAAAAAAAAAABAAAAAAAAAAAAAAAAAAAAAQAAAAEA//8AAP//AAAAAAEAAAABAAAAAAD//wAA//8BAP//AQD//wEAAAABAP//AAD//wEA//8BAP7/AAD//wEA//8BAP//AAD+/wAA//8AAP//AAD//wEA//8BAP//AAAAAAAAAAAAAP//AAD//wAA//8AAP//AAD//wAA//8AAAAA/////wAAAAAAAAAA//8AAP//AAD//wAA//8AAP//AAD//wAA//8BAP//AQD//wEA//8AAP//AAD//wEAAAABAP//AAD//wAA//8AAP//AAD//wAA//8AAP//AAAAAAAAAAAAAAAAAAD//wAAAAAAAAAAAAD/////AAAAAAAAAAD/////AAD//wEAAAABAP//AAAAAAEAAAAAAAAAAAD//wEAAAABAAAAAAAAAAAA//8BAAAAAQAAAAEA//8BAAAAAQAAAAEAAAABAAAAAQABAAEAAAAAAAAAAQAAAAEAAQABAAAAAAAAAAEAAQABAAEAAAAAAAAAAQABAAEAAAABAAAAAQAAAAEAAAABAAAAAgAAAAEAAAABAAAAAQAAAAEAAAABAAAAAQAAAAEAAAABAAAAAQAAAAEAAAABAAAAAQD//wEAAAABAAAAAQAAAAAAAAAAAP//AAAAAAEAAAAAAAAAAAAAAP//AAD//wAA//8AAAAAAAAAAAAA//8AAP//AAD//wAA//8AAP//AAD//wAA//8AAP//AAD//wAA//8AAP//AAD+/wAA/v8AAP7/AAD//wAA/v8AAP7/AAD+/wAA/v8AAP///////wAA//8AAP//AAD+/wAA//8AAAAAAAAAAAAAAAAAAP///////wAA//8AAAAAAAD///////8AAAAAAAAAAAAAAAD//wAAAAABAAAAAQAAAAAA//8AAAAAAAAAAAEAAAABAP//AAAAAAAAAAABAAAAAAD//wAA//8AAAAAAQAAAAEAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAQAAAAAAAQAAAAEAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAQAAAAEAAAABAAAAAQAAAAEAAAAAAAAAAQAAAAEAAAABAAAAAQAAAAEAAAACAAAAAQAAAAEAAAABAP//AQAAAAEAAAABAAAAAQAAAAEAAAABAAAAAQAAAAAAAAABAAAAAgAAAAEAAAABAAAAAAAAAAEAAAABAAAAAQAAAAEAAAAAAAAAAAAAAP//AAAAAAAAAAAAAAAAAQAAAAEAAAABAAAAAQD//wEA//8AAP7/AQD//wEA//8AAP//AAD+/wEA//8BAP//AQD//wEA//8BAP//AQD+/wEA/v8BAP//AQD//wEA//8AAP7/AAD+/wAA/v8BAP//AQD//wEA//8AAP//AQD//wEA//8BAP//AAD//wAA//8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA//8AAP//AAD//wAAAAABAAAAAQAAAAEA//8BAP//AQAAAAEAAAAAAAAAAQAAAAEAAAABAAAAAQAAAAAAAAAAAAAAAAAAAAAA//8AAP//AAAAAAEAAAAAAAAAAAAAAAEAAAABAP//AAAAAAEAAAABAAEAAQABAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABAAEAAAAAAAAAAAAAAAAAAQABAAAAAQAAAAAAAAABAAAAAQAAAAAAAAAAAAAAAAAAAAEAAAAAAAAAAAAAAAEAAAABAP//AQAAAAEAAAABAP//AQAAAAAAAAAAAAAAAAD//wAAAAAAAAAAAAD//wAA//8AAAAAAAD//wAAAAABAAAAAQD//wEA//8BAAAAAQAAAAEA//8AAP//AAAAAAEA//8AAP////8AAAAAAAAAAP//AAAAAP//AAAAAP//AAAAAAAAAAAAAAAA//8AAP//AAD//wAA//8AAP//AAD//wAA//8AAP//AAD//wAA//8AAP//AAD//wEA//8BAP//AAD//wAA/v8AAP7/AAD//wAA//8BAP//AQD+/wAA//8BAAAAAQAAAAAAAAAAAP//AAAAAAAAAAAAAAAAAAAAAAEAAAAAAAAAAAD//wAAAAAAAAEAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAEAAAABAAAAAQAAAAAAAAABAAAAAQAAAAEAAAABAAAAAAAAAAAAAQABAAAAAAAAAAAAAAAAAAAAAQAAAAEAAAABAAAAAQAAAAEAAAABAAAAAQAAAAAAAAAAAAEAAAAAAAEAAAABAAAAAAAAAAAAAAAAAAAAAAAAAAAAAQAAAAAA//8AAAAAAAAAAAAAAAABAAAAAQAAAAAAAAAAAAAAAQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAQAAAAAA//8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD//wAA//8AAP//AAAAAP//AAAAAAAAAAD//////////wAAAAD//wAA//8AAP//AAD//wAA/v////7/AAD//wAA///////////+/wAA/v///////////wAA//8AAP//AAD//wAA//8AAP//AAAAAAAAAAAAAP//AAD/////AAD//wAAAAD//wAA/////wAA/////wAA//8AAP//////////////////AAAAAAAAAAAAAAAAAAABAAAAAQAAAAAAAAAAAAEAAAAAAAEAAAABAAEAAQABAAAAAQABAAEAAQAAAAAAAAAAAAEAAQABAAEAAQABAAEAAQABAAAAAQAAAAEAAQABAAEAAgAAAAEAAAABAAAAAQAAAAAAAAABAAEAAQABAAEAAAABAAAAAQAAAAAAAAABAAAAAQAAAAEAAAAAAAEAAQABAAEAAQABAAAAAAABAAAAAQAAAAEAAAAAAP//AAAAAAEAAAAAAAAAAAD//wAAAAAAAAAAAQD//wEAAAABAAAAAAD//wAAAAABAAAAAAAAAAAA//8AAAAAAAAAAAAA//8AAP//AAD//wAA//8AAP///////wAAAAAAAAAAAAD//wAA//8AAAAA//////////8AAP//AAAAAAAA//8AAP//AAAAAAAA////////AAD//wAAAAD//wAAAAD//wAAAAD//wAA//8AAP//AAD///////8AAAAAAAAAAAAAAAD//wAAAAAAAAAA//8AAP//AAD//wAA//8AAAAA//8AAAAA//8AAP////8AAP//AAAAAAAAAAAAAAAAAAAAAAEAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABAAEAAQABAAAAAAAAAAAAAAABAAAAAQABAAAAAAAAAAAAAQABAAEAAQABAAEAAQAAAAEAAQABAAAAAQABAAEAAQABAAEAAQABAAEAAAABAAAAAQABAAEAAQABAAAAAQAAAAEAAQABAAEAAQAAAAEAAAABAAAAAQD//wEA//8BAAAAAQAAAAEAAAAAAAAAAAAAAAEAAAABAAAAAAAAAAEAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP////8AAAAAAAAAAAAAAAD/////AAAAAAAAAAAAAAAAAAAAAAAA//////////8AAP//AAD//wAA//8AAAAAAAD///////8AAP//AAD//wAAAAAAAAAAAAD/////////////AAD//wAA//8AAAAAAAAAAAAA//8AAP//AAD//wAA////////AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA//8AAP//AAAAAAAAAAAAAAAAAAD//wAAAAAAAAAAAAAAAAEAAAAAAP//AAD//wAA//8BAP//AAD//wAAAAAAAAAAAAAAAAEAAAABAAAAAAAAAAEAAAABAAAAAQAAAAAAAAABAP//AAD//wAAAAAAAAAAAQAAAAAAAAABAAAAAQAAAAAA//8AAP//AQAAAAEAAQAAAAAAAAAAAAAAAQAAAAEAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAEAAAABAP//AQD//wEAAAABAAAAAQD//wAA//8AAP//AAD//wAA//8BAAAAAQAAAAEAAAABAAAAAQD//wEA//8BAAAAAQAAAAAA//8BAAAAAQAAAAEA//8BAP//AQD//wEAAAABAAAAAQAAAAAA/////wAA//8AAAAA//////////8AAP//AAD//wAA//8AAP//AAD//wAAAAAAAAAAAAAAAAAAAAAAAAAAAQD//wAA//8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABAAAAAQAAAAAA//8AAP//AAAAAAAA////////AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD//wAA//8AAAAA//8AAP//AAAAAAEAAAABAAAAAAAAAAAAAQABAAAAAAAAAAAAAAABAAAAAAAAAAAAAAABAAAAAQAAAAEAAQABAAAAAAAAAAAAAAABAAEAAQAAAAAAAQAAAAEAAQAAAAAAAAAAAAEAAQAAAAAAAAAAAAEAAAABAAAAAAAAAAAAAAABAAAAAQAAAAAAAAAAAAAAAQAAAAEAAAABAAEAAQABAAEAAQABAAAAAQAAAAEAAAAAAP//AAAAAAEAAAAAAAEAAAAAAAAAAAABAAAAAAAAAAAAAAAAAAAAAAAAAAEAAAABAAEAAAAAAAAAAAAAAAEAAAABAAAAAQAAAAEAAAABAAAAAQAAAAAAAAAAAP//AAD//wAAAAAAAAAAAAAAAAAA//8AAP//AAD//wAA//8AAP//AAD//wAA//8AAP//AAAAAP//AAAAAP//AAD///////////////////////////////8AAP//AAD//////////////////wAA//8AAP//AAD/////////////AAD//wAA//8AAP//AAD//wAA//8AAP7/AAD//wAA//8AAP//AQD//wAA//8AAP//AAD//wAAAAAAAP//AAD//wAA//8AAAAAAAAAAAEAAAABAP//AAD//wAAAAAAAAAAAAAAAAAAAAD//wAAAAAAAAAAAQAAAAEAAAAAAAAAAAABAAAAAQAAAAEAAAAAAAAAAAAAAAEAAAAAAAAAAAAAAAAAAAABAAAAAAAAAAAAAAABAAEAAAAAAAEAAAABAAEAAQABAAEAAAABAAEAAQABAAEAAQABAAEAAQAAAAEAAAABAAEAAQABAAEAAAAAAAEAAAABAAAAAAABAAEAAAABAAAAAAAAAAAAAAABAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA//8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP//AAD//wAA//8AAP//AAD//wAA//8AAP7/AAD//wAA//8AAP//AAD//wAA//8AAP//AAD//wAA//8AAP///////wAA//8BAP//AAD///////8AAP7/AAD//wAA////////AAD//////////////////////////wAAAAD//////////wAA/////////////wAA/////////////wAA//8AAAAAAAAAAAAAAAAAAP//AQD//wEAAAABAAAAAAD//wAA//8BAAAAAQD//wEAAAABAAAAAQD//wEA//8BAAAAAQD//wEA//8BAAAAAQAAAAIAAQACAAAAAQAAAAEAAAACAAEAAQABAAEAAAABAAAAAQAAAAEAAQABAAEAAQABAAEAAQABAAIAAAABAAEAAQABAAEAAQABAAEAAQABAAEAAQACAAAAAQAAAAEAAQABAAEAAQABAAEAAQABAAEAAQAAAAEAAAABAAAAAQAAAAEAAQABAAEAAQAAAAEAAQABAAEAAAAAAAAAAAAAAAAAAAAAAP//AAAAAAEAAAAAAAAAAAD//wAAAAAAAAAAAAAAAAAAAAAAAAAAAAD//wAA//8AAP//AAD//wAA//8AAP//AAAAAAAA//////7/AAD/////AAD//////////wAA///////////////////////////////////+/////v//////////////////////////////////////AAD//wAA////////////////////////AAD//wAA//////7//////wAA//8AAAAA//8AAP//AAAAAP//AAD//wAAAAAAAAAAAAD//wAAAAAAAAAAAQD//wEA//8AAAAAAAAAAAAAAAAAAAAAAAABAAEAAQABAAEAAQABAAAAAQABAAEAAQAAAAEAAAABAAAAAAAAAAAAAAAAAAAAAQAAAAEAAQABAAEAAQABAAAAAQAAAAEAAQABAAEAAQABAAEAAQABAAEAAQABAAEAAAABAAAAAQABAAEAAQABAAAAAAABAAAAAQAAAAEAAAAAAAAAAAABAAEAAAABAAAAAQABAAEAAAABAAAAAQAAAAAAAAAAAAAAAQABAAEAAAAAAAAAAAAAAAEAAQAAAAAA//8AAP//AAAAAAAAAAAAAAAAAAD//wAA//8BAP//AAD//wAAAAAAAAAAAAAAAAAAAAAAAP//AAD//////////wAAAAAAAP//////////AAAAAAAA//////////8AAAAAAAAAAP///////wAA//8AAP//AAD//wAA//8AAP//AAD//wAA//8AAP///////wAA//8AAP//AAD///////8AAP//AAD//wAA//8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP//AAD//wAAAAAAAAAAAAAAAAAAAAABAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAQAAAAAAAAAAAAAAAAAAAAAAAQAAAAEAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAQABAAEAAAAAAAAAAAABAAAAAAAAAAAAAAABAAAAAAAAAAAAAAAAAAAAAAABAAAAAAAAAAAAAQABAAAAAQAAAAAAAAAAAAAAAQAAAAAAAAAAAAAAAAAAAAAAAAABAAAAAAD//wAAAAABAAAAAQAAAAAAAQABAAAAAQD//wAAAAAAAAAAAQAAAAEAAQAAAAEAAAAAAAAAAAABAAAAAQAAAAAA//8BAAAAAQAAAAAA//8AAP//AAD//wAAAAAAAAAAAAAAAAAAAAAAAP//AAD//wAA//8AAP///////wAA//8AAAAA//////////8AAAAAAAD//wAA//8AAAAAAAAAAAAAAAD/////AAAAAAAAAAAAAP//AAAAAAAAAAD//wAA//8AAP//AAD//wAAAAAAAAAAAAD//wAA//////////8AAAAA//8AAP////8AAP//AAD//wAAAAD//wAA//8AAP//AAD//wAAAAAAAAAAAAAAAAEAAAABAAAAAAAAAAAAAAAAAAAAAAAAAAAA//8BAAAAAQAAAAEAAAABAAAAAQAAAAEAAAABAAAAAAAAAAAAAQABAAEAAAAAAAAAAAABAAAAAQABAAAAAAAAAAAAAQAAAAEAAAABAAAAAQAAAAAAAAABAAAAAQAAAAEAAAAAAAAAAQABAAAAAQAAAAEAAAABAAEAAAABAAAAAQABAAEAAQAAAAEAAQABAAEAAQAAAAEAAAAAAAEAAQABAAEAAQABAAAAAAAAAAEAAAABAAEAAQAAAAEAAAAAAAEAAAABAAAAAQAAAAEAAAAAAAAAAAAAAAEAAQABAAEAAAABAAAAAQAAAAEAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA/////////////wAA//8AAAAA//8AAAAAAAAAAAAAAAD///////8AAP///////////v8AAP////////////8AAP//AAD//////////////////////////////////////v////7/AAD////////////////+////////////AAD//wAA/////////////wAA//8AAP//AAAAAAAAAAAAAAAAAAD//wAAAAAAAAAAAAAAAP//AAD//wAAAAAAAP///////wAAAAABAAEAAQAAAAEA//8AAAAAAAAAAAAAAAAAAAAAAAAAAAEAAAABAAEAAQABAAEAAAABAAAAAQAAAAEAAAABAAAAAQABAAEAAAABAAAAAgAAAAIAAQABAAAAAgAAAAIAAAABAAEAAQABAAEAAQABAAEAAQAAAAEAAAABAAEAAgABAAIAAAABAAAAAQABAAAAAAABAAAAAQAAAAEAAQABAAAAAAAAAAEAAQABAAEAAAAAAAAAAAABAAAAAAAAAAAAAAABAAAAAAD//wAA//8AAAAAAQAAAAEAAAABAAAAAAAAAAAA//8AAAAAAAAAAP//AAAAAAAAAAD//wAAAAD//wAA/////wAA//8AAAAAAAAAAP///////wAAAAAAAP//AAD//wAA//8AAAAAAAD//wAA//8AAAAA//8AAP////8AAAAAAAD/////////////AAAAAP//////////AAD//wAA//8AAP//AAAAAAAA//8AAP//AAAAAAAA//8AAP//AAAAAP////8AAP//AAAAAAAAAAAAAP//AAD//wEAAAAAAAAAAAD//wAAAAAAAAAAAQAAAAEAAAABAAAAAQD//wAA//8AAAAAAQABAAEAAAABAAAAAAAAAAEAAQABAAEAAAAAAAAAAAABAAEAAQABAAAAAAABAAAAAAABAAAAAQABAAEAAQABAAEAAQABAAEAAQABAAEAAQABAAEAAQABAAEAAQAAAAEAAAAAAAEAAQABAAEAAQABAAAAAAAAAAEAAAABAAEAAAABAAAAAAAAAAAAAQABAAEAAAABAAAAAAAAAAAAAQAAAAEAAQAAAAEAAQAAAAEAAQABAAEAAAAAAP///////wAAAAAAAAAAAAAAAAAAAAAAAAAA//////////8AAP////8AAP//AAD//wAAAAD//wAA//////////8AAAAA//////////8AAP//AAAAAP//AAD//////////wAA//8AAAAA////////AAAAAAAA//8AAP///////////////wAAAAAAAAAAAAD//wAA//8AAP//AAD//wAA//8AAP//AAAAAAAA//8AAP//////////AAAAAP//AAD//wAAAAAAAP//AAAAAAAAAAAAAAAAAAD//wAA//8AAAAAAAD//wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA//8BAAAAAAAAAAAAAAAAAAAAAQAAAAEAAAABAAAAAAAAAAAAAQAAAAAAAAAAAAAAAQAAAAEAAAABAAAAAAAAAAEAAQACAAAAAQAAAAAAAAABAAEAAQAAAAEAAAABAAEAAQABAAEAAQABAAEAAgABAAEAAQABAAEAAQABAAEAAQABAAEAAQABAAEAAQABAAEAAQAAAAEAAQAAAAEAAQABAAEAAQABAAIAAQABAAEAAAABAAAAAQABAAAAAAAAAAAAAQAAAAAAAAAAAAAAAAAAAAEAAAAAAAAAAAABAAAAAQAAAAAAAAAAAAAAAAAAAAAAAAD//wAAAAAAAAAAAAAAAP//AAAAAAAAAAD/////AAD//wAA////////AAD//wAA//8AAP//AAD/////////////AAD//wAA////////AAD/////AAD//wAA//////////////7///////////////////////////////7////+/wAA////////////////////////AAAAAAAA/////////////wAAAAD//////////wAA//////////////////8AAAAA//8AAP////8AAP//AAAAAP//////////AAAAAP////8AAP//AAD//wAAAAD//wAA//8AAAAAAAAAAAAAAAAAAAEAAAAAAAEAAAABAAAAAQAAAAEAAAABAAAAAAAAAAAAAAABAAAAAQAAAAEAAQACAAEAAQAAAAEAAQABAAAAAQAAAAEAAAABAAEAAQAAAAEAAAACAAEAAQABAAEAAQABAAAAAgAAAAIAAAABAAAAAQABAAEAAQABAAEAAQABAAEAAQABAAAAAQAAAAEAAQABAAAAAQAAAAAAAQAAAAAAAQAAAAAAAAAAAAAAAAABAAAAAQAAAAEAAAABAAAAAQAAAAAAAAAAAAAAAQAAAAAAAAAAAP//AAD//wAA//8AAAAAAQAAAAAAAAAAAAAAAQD//wEA//8BAP//AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD//wAAAAAAAAAAAAAAAP////8AAAAAAAAAAAAAAAD//////////wAAAAAAAAAA////////AAAAAAAA//////////8AAAAAAAAAAP//////////////////////////AAD/////////////AAAAAAAA//8AAP////////////8AAAAAAAD//////////wAA//8AAP//AAD//wAA//////////8AAP///////////////wAA//8AAP////////////8AAAAAAQAAAAEAAAAAAAAAAAABAAAAAAAAAP//AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAQAAAAEAAAABAAAAAQABAAEAAAABAAAAAQAAAAEAAAABAAAAAQAAAAEAAAABAAEAAQABAAEAAAABAAAAAQABAAEAAQABAAAAAQABAAAAAAAAAAAAAAAAAAEAAAABAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABAAEAAAAAAP//AAD//wAA//8AAAAAAAAAAAEAAAABAAAAAAD//wEAAAABAAAAAAAAAAEAAAAAAP///////wAA//8AAP//AAD//wAA//8AAAAAAAD//wAA/////////////wAA//8AAP//AAD///////8AAAAAAAAAAAAA//8AAAAAAAD//wAA//8AAP//AAAAAP//AAAAAAAAAAAAAAAAAAAAAAAAAAD//wAAAAAAAAAAAAD//wAAAAD//wAA//8AAAAAAAAAAAAA//8AAAAAAAAAAAAAAAD//wAA//8AAAAAAAAAAAAAAAD///////8AAAAAAAD//wAA//8AAAAAAAAAAAAA/////wAAAAAAAP//AAAAAAAAAAAAAAAAAQAAAAAAAAAAAAAAAAAAAAAAAAAAAAEAAAABAAAAAQABAAEAAAABAAAAAQAAAAEAAAABAAAAAQAAAAEAAQABAAAAAgAAAAEAAAABAAEAAgABAAIAAQABAAAAAQABAAIAAQACAAEAAgAAAAIAAAACAAEAAgAAAAIAAAABAAAAAgAAAAIAAAABAAEAAQAAAAAAAAABAAEAAQABAAAAAAAAAAAAAQAAAAEAAQABAAEAAAAAAAAAAAAAAP//AAAAAAAAAAAAAAAAAAAAAP///////wAAAAAAAP///////wAAAAD//////////wAAAAAAAP//AAD//wAA//8AAP//AAD//wAA//8AAP//AAD/////////////AAD//wAA//8AAP//AAD///////8AAP//AAD//wAA//8AAP7/AAD/////////////AAD//wAAAAAAAP//AAAAAAAAAAD//wAAAAD//wAA//8AAAAAAAAAAAAAAAAAAP//AAAAAAAAAAAAAAAAAAAAAAAAAAAAAP//AAAAAP//AAD//wAAAAAAAAAAAQD//wAAAAAAAAAAAAAAAAEAAAABAAAAAAD//wAAAAABAAAAAAAAAAAAAAABAAAAAQAAAAAAAAABAAAAAQAAAAAAAAAAAAEAAAAAAAAAAAABAAAAAQABAAEAAQAAAAEAAAAAAAEAAAABAAAAAQAAAAEAAQAAAAAAAAAAAAEAAQABAAEAAQAAAAEAAAAAAAAAAAABAAAAAQABAAAAAQAAAAEAAAABAAAAAQABAAEAAQABAAAAAAAAAAAAAQABAAAAAQABAAAAAQABAAEAAQABAAAAAAABAAAAAAAAAAAAAAABAAAAAAAAAAEAAAABAAEAAQAAAAAAAAAAAP//AAD//wAA/////wAA//8AAAAAAAAAAAAAAAAAAP////8AAP//AAAAAAAAAAAAAAAA//8AAP//AAD//wAA//8AAP//AAD//wAA//8AAP//AAD//wAA//8AAP//AAD//wAA//8BAP//AAD//wAA//8AAP//AAAAAAAA//8AAP//AAD//wAAAAAAAAAAAAD//wAA//8AAP//AAD//wAAAAAAAAAAAAD//wAA//8AAAAAAAD//wAA//8BAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABAAAAAQABAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP//AAD//wAAAAAAAP//AAD//wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD//wAA//8AAAAAAAAAAAAAAAABAAAAAAAAAAAA//8AAAAAAQAAAAEA//8BAAAAAQAAAAEAAAABAAAAAQAAAAEAAAABAAAAAQAAAAEAAAABAAAAAQABAAEAAAABAAAAAQAAAAEAAAABAAAAAQAAAAEAAAABAAAAAQABAAAAAQABAAAAAQABAAAAAQAAAAEAAQABAAAAAQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD///////8AAP//AAD//wAA//8AAP//AAD//wAA/v////7/AAD//wAA////////AAD+/wAA/v8AAP7/AAD//wAA//8AAP//AAD//wAA//8AAP//AAD//wAAAAAAAP///////wAAAAAAAAAA/////wAAAAAAAAAAAAD//wAAAAAAAAAA/////wAAAAAAAAEAAAAAAAAA//8BAAAAAAAAAAAAAAABAAEAAQABAAEAAAAAAAAAAAAAAAEAAQABAAAAAQAAAAAAAQAAAAEAAQABAAAAAQAAAAEAAAAAAAEAAQABAAEAAQAAAAAAAQAAAAEAAAABAAAAAAAAAAAAAQABAAEAAQABAAAAAAAAAAEAAQABAAAAAQAAAAEAAQABAAAAAQAAAAEAAQABAAAAAQAAAAEAAQABAAEAAQAAAAEAAAAAAAEAAAAAAAAAAAAAAAAAAAABAAEAAQAAAAAAAAAAAAAAAQAAAAEAAAABAAAAAQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAQAAAAEAAAABAP//AAD//wAAAAABAAAAAQD//wAA//8AAAAAAAAAAAAA//8AAP//AAD//wAA//////////8AAP////8AAP//AAD/////AAD//wAAAAD/////AAD//wAA/////////////wAA//////////////////////7/AAD//wAA//////7/AAD//wAA///////////+/wAA/v8AAP7/AAD//wAA//8AAAAAAAD//wAA//8AAP//AAD//wAAAAAAAAAAAAAAAP//////////AAAAAP//AAD//wEAAAABAAAAAQAAAAAAAAAAAAAAAAABAAEAAQABAAAAAQAAAAAAAAABAAEAAQABAAEAAQABAAAAAQAAAAEAAQABAAEAAQAAAAEAAQABAAEAAQABAAEAAQABAAEAAQABAAEAAQAAAAEAAAABAAEAAQABAAEAAAAAAAEAAQABAAEAAQAAAAEAAQABAAEAAAAAAAAAAAABAAAAAAABAAAAAQAAAAAAAQAAAAAAAAAAAAAAAAABAAAAAQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAQAAAAAAAAAAAAAAAQAAAAAAAAAAAAAA//8AAAAAAAAAAAEAAAAAAAAAAAAAAAAAAAABAAAAAQAAAAAAAAAAAAAAAAAAAAAA/////////////wAAAAAAAAAAAAAAAP//////////AAAAAAAAAAD///////8AAAAAAAAAAP////8AAAAAAAAAAAAA//8AAP//AAAAAAAAAAAAAAAAAAAAAAAA//8AAP//AAD//wAA//8AAAAAAAAAAAAAAAAAAP//AAAAAAAA//8AAP//AAD//wAA//8AAP////////////8AAP//AAD//wAAAAAAAAAA//8AAP//AAAAAAAAAAAAAP//AAAAAAAAAAABAAAAAAAAAAAAAAAAAAAAAQAAAAEAAAABAAEAAQAAAAEAAAAAAAEAAQAAAAEAAAAAAAAAAAABAAAAAAAAAAAAAAABAAEAAAAAAAEAAQABAAEAAAAAAAAAAAAAAAAAAAAAAAAAAAABAAAAAAAAAAAAAAAAAAEAAAAAAAAA//8BAAAAAQAAAAAAAAAAAAAAAAAAAAAA//8BAP//AAAAAAAA//8AAP//AAD//wAA////////AAAAAAAAAAAAAP///////wAA//8AAAAAAAD//wAA//8AAAAA/////wAAAAAAAAAAAAAAAAAA//8AAP//AAAAAAAAAAAAAAAAAAAAAAAAAQAAAAAAAAAAAAAAAAAAAAEAAAAAAAAAAAAAAAEAAAABAAAAAQAAAAEAAAAAAAAAAAAAAAEAAAAAAAAAAQAAAAEAAAAAAAAAAAAAAAEAAAAAAAAAAAAAAAEAAAAAAAAAAAAAAAAAAAAAAAAAAQD//wAA//8AAAAAAQAAAAEAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD//wAAAAAAAAAAAAD//wAA//8AAP//AAD//wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAEAAQAAAAAAAAAAAAAAAAAAAAAAAQAAAAEAAQAAAAAAAAAAAAAAAQAAAAEAAAAAAAEAAAABAAAAAAAAAAEAAAABAAAAAAAAAAAAAAAAAAEAAAAAAAAAAAABAAEAAAAAAAAAAAABAAAAAAABAAAAAQABAAEAAQABAAEAAAABAAAAAQD//wEAAAAAAAAAAAAAAAAAAAAAAAAAAQAAAAEAAAAAAAAAAAAAAAEAAAAAAAAAAAAAAAEAAAABAAAAAAAAAAEA//8AAP//AAAAAP//AAAAAAAAAAAAAAAAAAAAAP//AAAAAAAAAAD//////////wAAAAAAAP///////wAAAAAAAAAA//8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA//8AAP//AAD//wAAAAAAAAAAAAD//wAA//8AAAAAAAAAAAEAAAAAAAAAAAAAAAAAAAAAAP////8AAP//AAAAAAAA//////////////////////////8AAP//AAAAAP//AAD//wAAAAD//wAAAAAAAAAAAAAAAAAAAAD//wAA//8AAAAA//8AAP////8AAP//AAAAAAAAAAD/////AAAAAAAAAAAAAP////8AAAAAAAAAAAEAAAABAP//AAD//wAAAAABAAAAAQAAAAEAAAAAAAAAAQABAAEAAAABAAAAAQAAAAAAAQABAAEAAQABAAEAAQABAAEAAQABAAAAAAAAAAEAAQABAAEAAAABAAAAAAABAAAAAQABAAAAAAABAAAAAQAAAAEAAAABAAAAAQAAAAAAAAAAAAAAAQABAAEAAQAAAAAAAAAAAAEAAQABAAAAAAAAAAAAAAAAAAAAAAD//wAA//8AAAAAAAAAAAAAAAAAAAAAAAAAAP////8AAP//AAAAAAAAAAAAAAAAAAAAAAAAAAD/////AAD//wAAAAAAAAAAAAD//wAA//8AAP////8AAAAAAAAAAAAAAAAAAP//AAAAAAAAAAAAAP//AAD/////AAD//wAAAAD//wAAAAAAAAAAAAAAAAAA//8AAAAAAAAAAAAAAAAAAAAAAAAAAAEAAAAAAP////8AAAAAAAAAAAAAAAAAAAAAAQAAAAEAAAABAAAAAQAAAAEAAQAAAAEAAAABAAAAAQABAAAAAAAAAAAA//8BAAAAAQAAAAEAAAABAAAAAQAAAAEAAAABAAAAAQAAAAEAAAAAAAAAAQAAAAEAAAABAAAAAAAAAAAAAAABAAEAAAABAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABAAAAAAAAAAAAAQAAAAAAAAAAAAAAAQABAAAAAQAAAAAAAAAAAAAAAQAAAAAAAAAAAAAAAAAAAAEAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAEA//8AAAAAAAAAAAAAAAAAAP//AAAAAAAAAAD//wAA//8AAAAAAAAAAP//AAD//wAA//8AAAAA//8AAAAA//8AAP//AAD///////8AAP//AAD//wAA//8AAP//AAD//wAAAAAAAP///////wAA//8AAP////////////8AAAAAAAAAAAAAAAAAAP//AAD/////////////AAAAAAAA//8AAP//AAD//wAAAAAAAAAAAAD//wAA//8AAAAAAAAAAAEAAAAAAP//AAD//wAAAAAAAAAAAAAAAAAA//8AAP//AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAQAAAAEAAAAAAAAAAAAAAAAAAAAAAAAAAAABAAAAAQAAAAAAAAAAAAAAAAAAAAEAAAABAAAAAQAAAAEAAAABAAAAAQAAAAIAAAACAP//AQD//wEAAAABAAAAAQAAAAEAAAABAAAAAQAAAAEAAAABAAAAAQAAAAEAAAABAAAAAQAAAAIAAAABAAAAAQAAAAEAAAAAAAAAAQAAAAEAAAABAAAAAAAAAAAAAQAAAAAAAQAAAAAAAAAAAAAAAAAAAAEAAAABAAAAAQABAAEAAQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAQAAAAAA//8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD///////8AAAAAAAAAAP///////////////wAA/////wAA//////////////////8AAP////////////8AAAAA//8AAP//////////////////AAAAAAAAAAD//////////////////wAA//8AAP//AAD//wAA//8AAP////8AAP//AAAAAP//////////AAAAAP//AAD//wAA//8AAP//AQAAAAEAAAAAAAAAAAAAAAAA//8AAP//AAAAAAEAAAABAP//AQAAAAEAAAABAAAAAQD//wAA//8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAEAAAABAAAAAAAAAAAAAAAAAAAAAAABAAAAAQABAAAAAQAAAAEAAAABAAAAAAAAAAAAAAAAAAAAAAAAAAAA//8AAAAAAQAAAAEAAAABAAAAAQAAAAAAAAABAAAAAQAAAAAA//8BAP//AQAAAAEAAAAAAAAAAAAAAAEAAAABAAAAAQAAAAAAAAAAAAAAAAAAAAEAAAABAAAAAQAAAAEAAAABAAAAAAAAAAEAAAABAAEAAAAAAAAAAAABAAAAAQAAAAAAAAAAAAAAAQAAAAAAAAAAAAAAAAAAAAEAAAABAAAAAAAAAAAAAAABAAAAAQAAAAAA//8AAP//AAD//wAAAAAAAAAAAAAAAAAAAAABAAAAAAAAAAAAAAD//wAA//8AAAAA//////////////////8AAP////////////8AAAAAAAAAAP//AAD//wAA/v8AAP//AAD//wAA//8AAP//AAD//wAAAAAAAAAA/////wAA//8AAAAA////////AAAAAAAAAAD//wAAAAABAAAAAQD//wAA//8AAP//AAD//wAA//8AAP//AQD//wAA//8AAP//AQD//wAA//8BAP//AQD//wAAAAAAAAAAAQAAAAIAAAABAAAAAQAAAAEAAAABAAAAAQAAAAEAAAABAAAAAQAAAAEAAAABAAAAAQAAAAEAAAABAAAAAQAAAAEAAAABAAEAAQABAAEAAAABAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAEAAQABAAEAAAAAAAAAAAAAAAEAAAABAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABAAAAAQAAAAAAAAAAAAAAAQAAAAAAAAAAAAAAAAAAAAEAAAABAAAAAAAAAAAAAAABAP//AQD//wAA//8AAP//AAD//wEA//8BAP//AAAAAAAAAAAAAAAAAAAAAAAAAAAAAP//AAD//wAA///////////+/wAA//8AAP//AAD+/wAA//8AAP//AAD//wAA//8AAP////////////////7////+/////v8AAP////////////8AAP//AAD///////////7///////////8AAAAAAAAAAP////8AAP//AAAAAP//AAD//wAA//8AAP///////wAAAAAAAAAAAQAAAAAA//8AAAAAAAAAAAAAAAAAAAAAAAAAAAEAAAABAAEAAQAAAAEAAAAAAAAAAAAAAAEAAQABAAEAAgAAAAEAAAABAAAAAQAAAAAAAAABAAEAAQAAAAEAAAABAAAAAQABAAEAAQABAAEAAQABAAEAAQAAAAEAAAABAAAAAQABAAIAAAABAAAAAQAAAAEAAQABAAEAAAABAAAAAQAAAAAAAQAAAAAAAAAAAAAAAAAAAAAAAQAAAAAAAAAAAAEAAAABAAEAAAAAAAEAAAABAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD//wAAAAAAAAAAAAAAAAAAAAAAAAAAAAD//wAA//8AAAAAAAAAAAAAAAAAAP//AAAAAAAAAAABAP//AAD//wAAAAAAAAAAAAAAAP//AAD//wAA//8AAP//AAD/////AAD//////////wAAAAD//////////wAAAAAAAAAAAAAAAAAA//8AAAAAAAD//wAA//8AAP//AAD//wAA////////////////AAD//wAA////////AAAAAAAAAAD//wAA//8AAP//////////AAAAAAAAAAAAAP//AAAAAAAAAAAAAAAAAAD//wAA//8AAAAAAAAAAAAA//8AAAAAAAAAAAAA//8AAP//AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAQAAAAEAAAABAP//AQAAAAEAAAABAAAAAQAAAAEAAAAAAAAAAAABAAEAAQABAAEAAAABAAAAAQABAAEAAQABAAAAAQABAAEAAQAAAAEAAQAAAAEAAAAAAAAAAAAAAAEAAQABAAEAAAAAAAEAAQABAAEAAQAAAAEAAAAAAAEAAQAAAAEAAAAAAAEAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABAAAAAQAAAAAAAAD//wAA//8AAAAAAAAAAAAAAAAAAAAAAQAAAAAAAAAAAAAA//8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP////8AAP//AAAAAAAA//8BAAAAAQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAQD//wEAAAABAAAAAAAAAAAA//8AAAAAAAAAAAEA//8BAAAAAQAAAAEAAAAAAAAAAAAAAAAAAAAAAP//AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAQAAAAEAAAAAAP//AAD//wAAAAAAAAAAAAD//wAA//8AAAAAAAAAAAAAAAAAAAAAAAAAAP////8AAAAAAAAAAP//AQAAAAEAAAABAP//AAAAAAAAAAABAAAAAQD//wAAAAABAAAAAQD//wAA//8AAAAAAQAAAAEAAAABAAAAAQAAAAEAAAABAAAAAAAAAAAAAAAAAAAAAQAAAAEA//8BAP//AQD//wEAAAABAAAAAQAAAAEA//8BAP//AQD//wAAAAAAAAAAAAAAAAAA//8AAP//AAAAAAEAAAAAAAAAAAD//wAAAAAAAAAAAAAAAAEAAAABAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA//////////8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA//8AAP//AAD//wAA//8AAP//AAD//wAA//8AAP//AAD//wAA//8AAP//AAD//wAA//8AAP//AQAAAAEA//8BAP//AQD//wEAAAABAAAAAQD//wEA//8BAP//AAD//wAAAAABAAAAAQD//wAA//8AAAAAAAAAAAAAAAABAAAAAAAAAAAA//8BAAAAAQAAAAEAAAABAAAAAQAAAAEAAAAAAP//AQAAAAEAAAAAAAEAAAAAAAEAAQABAAEAAAABAAAAAAABAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAQABAAEAAQAAAAAAAAAAAAAAAQAAAAEAAAABAAAAAQAAAAEAAAABAAAAAQAAAAEAAAABAAAAAQAAAAEAAAAAAAAAAAAAAAEAAAABAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP//AAD//wAAAAAAAAEAAAABAAAAAQAAAAAAAAAAAP//AAAAAAAAAAAAAAAAAAD//wAA//8AAAAA////////////////////////AAAAAAAA//8AAP///////////////////////wAA//8AAP///////////////////////wAA//8AAP///////////////wAA//8AAP///////////////wAA//8AAAAAAAD//wAA//8AAP//AAAAAAAA//8AAP//AAD//wAA//8AAP//AAD//wAAAAABAAAAAAAAAAAAAAAAAP//AAD//wAAAAAAAAAAAAAAAAAAAAABAAAAAQAAAAAAAAAAAAAAAAAAAAAAAQAAAAEAAQAAAAEAAAABAAEAAAABAAAAAAAAAAAAAAAAAAAAAQABAAEAAQABAAEAAQABAAAAAQABAAEAAQABAAAAAQAAAAEAAQABAAEAAQABAAEAAQABAAAAAQAAAAAAAAABAAEAAQAAAAAAAAAAAAEAAQAAAAEAAAAAAAEAAQABAAAAAAAAAAAAAAABAAEAAAAAAAAAAAAAAAAAAAAAAAAAAAABAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABAAEAAQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAEAAAABAAAAAAD//wAA//8BAP//AQD//wAA//8AAP//AAD//wAA//8AAAAA//8AAAAAAAAAAP//AQAAAAAAAAAAAP///////////////wAA//8AAP//AAAAAAAAAAAAAAAAAAAAAP//////////AAD/////AAD//wAAAAAAAAAA//////////8AAAAAAAAAAP//////////AAD//wAAAAD//wAAAAAAAAAAAAAAAAAAAAD/////AAD//wAAAAABAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAQAAAAEAAAAAAAAAAAABAAAAAAAAAAAAAAABAAAAAQABAAAAAQAAAAAAAAAAAAAAAAABAAAAAQABAAAAAQAAAAEAAAABAAAAAQAAAAAAAAAAAAAAAAAAAAEAAQABAAAAAAAAAAAAAAAAAAEAAQABAAAAAAAAAAAAAAABAAAAAAAAAAEAAAABAAEAAAABAAAAAAAAAAAAAAAAAAAAAAD//wAAAAAAAAAAAAD//wAAAAAAAAAAAAAAAAAAAAAAAAAAAAD//wAAAAAAAAAAAAD//wAA//8AAAAAAAAAAAAA//8AAP//AAAAAAAA//8AAP//AAAAAAAAAAAAAP///////////////wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP////////////8AAP////8AAP//AAD//wAA//////7/AAD//wAAAAAAAP/////+////////////AAD+/wAA//8AAP//AAD//wAA//8AAAAA//8AAAAAAAAAAAAAAAAAAP////8AAP//AAAAAP//AAAAAP//AAAAAAAAAAAAAP//AAD//wAAAAAAAP//AAD//wEAAAABAAAAAQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABAAEAAAABAAAAAAAAAAAAAAABAAEAAAAAAAAAAAABAAAAAQAAAAEAAQABAAAAAQAAAAEAAQABAAEAAAAAAAEAAAAAAAAAAAAAAAEAAQABAAEAAQAAAAAAAQABAAEAAQABAAEAAAABAAAAAQAAAAEAAAAAAAEAAAABAAAAAQAAAAAAAQABAAEAAQABAAAAAQAAAAEAAQABAAAAAAAAAAAAAAAAAAAAAAAAAAEAAQABAAEAAAABAAAAAAAAAAAAAAABAAAAAAD//wAA//8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAQAAAAEAAAAAAP//AQD//wEA//8AAAAAAAD//wAA//8BAP//AAAAAAEAAAABAP//AAD//wAA//8AAP////8AAP//AAAAAAAAAAAAAAAA//8AAP//AAAAAP//AAAAAAAAAAD/////AAD//wAA//8AAP//AAD///////8AAP//AAAAAAAAAAD/////AAD//wAA//8AAP//AAD//wAA////////////////AAD//wAAAAAAAAAA//8AAP///////wAAAAD//wAA//8AAAAAAAAAAAAA//8AAAAAAAAAAAAA//8AAAAAAAABAAAAAQAAAAAAAAAAAAAAAQAAAAEAAAAAAAAAAAAAAAAAAAABAAAAAQABAAEAAQAAAAEAAQABAAEAAQABAAAAAQABAAEAAQABAAAAAAABAAEAAQABAAAAAQAAAAEAAQABAAEAAQAAAAAAAAABAAEAAQABAAEAAAABAAAAAQABAAEAAQABAAAAAQAAAAAAAQAAAAEAAAAAAAEAAAABAAEAAAAAAAEAAAABAAAAAAAAAAAAAAABAAAAAQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD//wAAAAD//wAAAAAAAAEAAAAAAP//AAD/////AAAAAAAA//////////8AAAAAAAAAAAAAAAAAAAAAAAAAAAAA//8AAP//AAAAAAAAAAD//wAA//8AAAAAAQAAAAAAAAAAAAAA//////////8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP//AAD//wAAAAAAAAAAAAAAAAAA//8AAAAAAAAAAAAAAAD/////AAAAAAAAAAD//wAAAAAAAP//AAD//wAAAAAAAAAAAAD//wAA//8AAAAAAAAAAAAA//8AAP//AAD//wAA//8AAP//AAD//wEA//8BAAAAAQAAAAEA//8BAP//AAD//wAAAAABAAAAAQAAAAAAAAAAAAAAAQAAAAEAAAABAAAAAQAAAAEAAAABAAAAAQAAAAEAAAABAAAAAQAAAAEAAQABAAAAAQAAAAAAAQABAAEAAQAAAAEAAAAAAAAAAQABAAEAAQAAAAAAAAAAAAEAAAABAAEAAQAAAAEAAAABAAAAAAAAAAAAAAAAAAEAAQABAAEAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA//8AAP//AAAAAAAAAAAAAAAA////////AAAAAP//////////AAAAAP//AAD//wAA//8AAAAAAAAAAAAA//8AAAAAAAAAAAAA//8AAP//AAAAAAAAAAAAAP//AAAAAAAAAAAAAP//AAD//wAAAAAAAAAAAAD/////AAD//wEAAAAAAAAAAAAAAAEAAAABAP//AAD//wAA//8BAAAAAQAAAAAAAAABAAAAAQAAAAAA//8AAAAAAAAAAAAAAAAAAAAAAQAAAAEA//8BAP//AAAAAAAAAAAAAAAAAQAAAAAAAAAAAAAAAQAAAAEA//8AAP//AAD//wAAAAAAAAAAAAD//wAA//8AAAAAAQAAAAAAAAAAAAAAAAAAAAAAAAABAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAQAAAAEAAAAAAAAAAAAAAAEAAAABAAAAAAAAAAEAAAAAAAAAAAAAAAEAAAABAAAAAQABAAEAAAAAAAAAAAAAAAAAAAAAAAAAAQAAAAEAAAAAAAAAAAD//wAAAAAAAAAAAAAAAAAAAAAAAAEAAAAAAAAAAAAAAP//AAAAAAAAAAD//wAA//8AAAAAAAAAAAAAAAD//////////wAA//8AAP//AAD//wAA//8AAP//AAAAAAAA//8AAP7/AAD+//////8AAP//AAD//wAA//8AAP7/AAD//wAAAAAAAAAAAAD//wAA//8AAP//AAD//wAA//8AAAAAAAD//wAA//8AAAAAAAAAAAEAAAABAP//AAAAAAAAAAAAAAAAAAAAAAAAAAABAAAAAQAAAAEAAAABAAEAAAAAAAAAAAABAAAAAQABAAAAAAAAAAAAAQAAAAEAAAAAAP//AAAAAAEAAAABAAAAAAAAAAAAAAAAAAAAAQD//wEA//8BAAAAAQAAAAAAAAAAAAAAAAD//wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP//AAD/////AAD//wAAAAAAAAAAAAAAAP//AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABAAAAAAAAAAAAAAABAAAAAAAAAAAAAAABAAAAAQD//wEAAAAAAAAAAQD//wEAAAABAAAAAQAAAAEAAAABAAAAAQAAAAEA//8BAAAAAQD//wAA//8AAP//AQAAAAEAAAABAP//AQAAAAEAAAAAAP//AAAAAAEAAAABAAAAAAAAAAAAAAAAAAAAAAD//wAAAAAAAAAAAAD//wAA//8AAAAAAAAAAAAAAAAAAAAAAAD//wAA//8AAAAA//8AAP//AAD///////////////8AAP//AAAAAAAAAAAAAAAA////////AAD//wAA//8AAP////8AAAAAAAAAAP///////wAAAAAAAP//AAD//wAAAAAAAAAAAAAAAAAAAAAAAAAAAQAAAAAAAAAAAAEAAAABAAAAAAAAAAAAAAAAAAAAAAAAAAEAAQABAAEAAAAAAAAAAAABAAAAAQABAAAAAQAAAAEAAQABAAEAAAABAAAAAAAAAAAAAAABAAAAAQABAAEAAAAAAAAAAQAAAAEAAQAAAAAAAAAAAAAAAAABAAEAAAAAAAAAAAD//wEAAAABAAAAAAAAAAAAAAAAAAAAAAAAAAEA//8BAAAAAAAAAAAAAAABAAAAAQAAAAAAAAABAAAAAQD//wAAAAAAAAAAAQAAAAAAAAAAAAAAAAAAAAEAAAAAAP//AAAAAAAAAAABAAAAAAAAAAAAAAAAAP//AAD//wAAAAAAAAAAAAAAAAAAAAAAAAAAAAABAAAAAQAAAAEAAAAAAP//AAAAAAAAAAAAAAAA//8AAP//AAAAAAAAAAAAAAAAAAD//////////wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP//AAAAAP//AAAAAAAAAAD//wAAAAD//wAA//8AAAAAAAD//wAA//8AAP//AAAAAAAAAAD//wAA//8AAAAA//8AAAAAAQAAAAEAAAAAAAAAAAAAAAEAAQAAAAEAAAABAAAAAQAAAAAAAAAAAAEAAAABAAEAAQABAAAAAQAAAAEAAQAAAAAAAAAAAAEAAQABAAAAAQAAAAEAAAABAAAAAQAAAAEAAAACAAAAAQAAAAEAAAABAAEAAgAAAAEAAAABAAAAAQAAAAEAAAABAAEAAQAAAAEAAAABAAAAAQAAAAEAAAABAAAAAAAAAAAAAQAAAAEAAAAAAAEAAAABAAAAAQAAAAAAAAAAAAAAAQAAAAAAAAAAAAAAAQAAAAAAAAAAAP////8AAAAAAAAAAP////8AAAAAAAAAAP///////wAAAAAAAP//AAD/////AAAAAAAAAAAAAAAAAAAAAAAAAAD//wAA/////wAA//8AAP////8AAP//AAD//wAA//8AAP//AAD//////////wAAAAAAAP//////////AAD//wAAAAAAAAAAAAAAAP//AAD/////AAD//wAAAAD//wAA/////wAA/////wAA/////wAA//8AAAAA//8AAP//AAD//wAA//8AAP//AAD//wAA/////////////wAA//8AAP//AAD///////8AAP//AAD//wEA/v8AAP7/AAD//wEA//8BAP//AAD//wAA//8BAAAAAQD//wAA//8BAAAAAQD//wAA//8AAP//AQAAAAIAAAABAAAAAQABAAEAAAABAAAAAQAAAAEAAQABAAEAAQAAAAAAAAAAAAAAAAAAAAAAAQAAAAEAAQABAAEAAQABAAEAAQAAAAEAAQAAAAEAAAACAAEAAQABAAEAAAACAAEAAQAAAAEAAAABAAAAAQAAAAEAAQAAAAAAAQAAAAEAAQABAAAAAQAAAAEAAAABAAAAAQAAAAEAAAABAAAAAQAAAAAAAAABAAAAAQAAAAEA//8BAP//AQAAAAEAAAABAAAAAAD//wAA//8AAAAAAAAAAAAA//8AAP//AAAAAAEA//8AAP//AAAAAAAAAAAAAP//AAD//wAAAAAAAAAAAAD//wAAAAAAAAAAAAAAAAAAAAAAAAAAAQAAAAAAAAAAAAAA//8AAAAAAAD//wAA/////wAA//8AAAAAAAAAAAAA//8AAP//AAAAAAAAAAAAAAAAAAABAAAAAQAAAAAA//8AAP//AAAAAAAAAAAAAP//AAD//wAA//8AAP//AAD//wAAAAAAAP//AAD///////8AAAAAAAAAAAEA//8AAAAAAQAAAAEA//8AAP//AAAAAAAA//8AAP//AAD//wAA//8BAP//AQD//wAAAAAAAP//AAD//wAAAAAAAP//AAD//wAAAAABAAAAAAAAAAAAAAAAAAAAAQAAAAAAAAAAAP//AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAQAAAAEAAAAAAAAAAAAAAAAAAAABAAEAAQAAAAEAAAABAP//AQAAAAEAAAABAAAAAAAAAAAAAAABAAAAAAAAAP////8AAAAAAAAAAAAAAAAAAP//AAD//wAA/////////////wAA//8AAAAAAAAAAAAAAAAAAP///////wAA//8AAAAA//8AAP////8AAP//AAD//wAA//////////8AAAAAAAAAAAAAAAAAAP//AAD//wAAAAAAAAAAAAD//wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA//8AAAAAAAAAAAAAAAAAAAAAAQAAAAAAAAAAAAAAAAAAAAAAAQAAAAEAAQAAAAAAAAAAAAEAAAABAAAAAAAAAAAAAAAAAAAAAQAAAAEAAAABAAAAAAABAAAAAAABAAAAAQAAAAAAAAAAAAAAAAABAAEAAQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAQAAAAEAAAAAAAAAAAAAAAAAAAABAAAAAQAAAAEAAAABAAAAAQAAAAEA//8AAAAAAAAAAAEAAAABAAAAAAAAAAAAAQAAAAAAAAAAAAAAAQAAAAEAAAABAAAAAQAAAAEAAQABAAEAAQAAAAEAAAABAAAAAQABAAEAAAAAAAAAAQAAAAEAAQABAAEAAAABAAEAAAABAAAAAQAAAAAA//8AAP//AQD//wEA//8BAAAAAAD//wEA//8BAAAAAQAAAAAAAAAAAP//AQAAAAEAAAAAAAAAAAD//wAA//8AAP//AAD//wAA//8AAP//AQAAAAEA//8AAP////////////8AAP//AAAAAP//AAAAAP//AAD/////////////AAD//wAA////////////////AAD//wAA////////////////////////AAD//wAA/v8AAP7///////////8AAP//AAD//wAA//8AAP//AAD//wEA//8AAP//AAD/////AAAAAAAAAAD//wAA/////wAAAAAAAAEA//8AAP//AAAAAAEAAAABAP//AAD//wAAAAAAAAAAAAAAAAAAAAABAP//AQD//wEA//8AAAAAAAAAAAAAAAABAAAAAAAAAAAA//8AAAAAAQAAAAEAAAAAAAAAAAAAAAAAAAAAAAAAAQAAAAEAAAABAAAAAQAAAAEAAAABAAAAAAABAAAAAQAAAAEAAAABAAEAAQABAAEAAAABAAAAAAAAAAEAAQABAAEAAAAAAAAAAAABAAEAAQABAAAAAAAAAAAAAQAAAAEA//8BAP//AQAAAAIAAAABAAAAAAAAAAAA//8BAAAAAgAAAAEAAAABAAAAAQAAAAEA//8BAP//AQAAAAEAAAABAP//AQD//wEA//8BAAAAAQAAAAEA//8BAAAAAQAAAAAA//8AAAAAAQAAAAAA//8AAP//AAD//wEAAAAAAAAAAAD//wAA//8AAAAAAAAAAP////8AAP//AAAAAAAAAAD//////////wAAAAD//////////wAAAAAAAAAA//8AAP//AAAAAAEAAAABAP//AAD//wAA//8AAP//AQD//wAAAAAAAAAAAQD//wEA//8BAP//AAD//wEA//8BAP//AQD//wAA//8BAP//AQD//wEA//8BAP//AQD//wEAAAABAAAAAQD//wEA//8AAAAAAAAAAAAAAAABAAAAAQAAAAAA//8AAAAAAAAAAAAA//8AAP//AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAEAAQABAAAAAAAAAAEAAAABAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD//wEAAAAAAAAAAAD//wAAAAAAAAAAAAAAAAAAAAABAAAAAAD//wAA//8AAAAAAQAAAAEA//8AAP//AQAAAAEAAAAAAP//AQD//wEAAAAAAAAAAAAAAAAAAAAAAP//AAD//wAA//8AAAAAAAAAAAAA//8BAP//AAAAAAAAAAD/////AAD//wAA//8AAP//AAD//wAAAAABAAAAAAAAAP//AAAAAP//AAD//wAA//8AAAAAAAAAAAAA//8AAP//AAAAAP//AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD//wAA//8AAP//AAAAAAAAAAAAAP//AAD//wAA//8AAP//AQAAAAEAAAABAP//AAD//wAA//8BAAAAAQAAAAAA//8AAAAAAQAAAAIA//8BAAAAAQAAAAEAAQABAAAAAQAAAAEAAAABAAAAAQAAAAEAAAABAAAAAQAAAAEAAAABAAAAAQAAAAEAAAABAAAAAQAAAAIAAAABAAAAAAABAAAAAQABAAAAAQAAAAAAAAAAAAEAAAABAAAAAQAAAAAAAQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA//8AAP//AAAAAAAAAAABAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP//AAAAAAAAAAAAAAAAAQAAAAAAAAAAAP//AQD//wAAAAAAAAAAAAAAAAEA//8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD//wAAAAAAAAAAAAAAAAAAAAD//wAA//8AAAAAAAAAAAAA//8BAP//AAD//wAA//////////////////8AAP//AAAAAAAA//8AAP//AAD//wAAAAD//wAA//8AAAAAAAD///////////////8AAP//AAD//wAAAAD//wAA//8AAAAA//8AAAAA//8AAP//AAD///////8AAP//AAAAAAAA//8AAP//AAD//wAA//8AAP//AAD//wEA//8BAP//AAD//wAAAAAAAAAAAQD//wEA//8AAP//AQAAAAEAAAABAP//AAD//wAAAAAAAAAAAQAAAAEA//8BAAAAAQAAAAEAAAABAAEAAQABAAEAAAABAAAAAQABAAEAAQABAAAAAQAAAAEAAAABAAAAAQAAAAEAAQACAAEAAgABAAEAAAABAAAAAQAAAAEAAAABAAAAAQAAAAEAAAABAAAAAQAAAAEAAAABAAAAAAAAAAEAAQABAAAAAAAAAAAAAAAAAAEAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA//8AAAAAAQD//wEA//8AAP//AAD//wAA//8AAP//AQD//wAA//8AAAAAAAAAAAAAAAAAAAAAAAD//wAA//8BAP//AQD//wEA//8AAP//AAD//wAA//8AAP//AQAAAAEAAAABAP//AAD//wAAAAAAAAAAAAAAAAAA//8AAP//AAD//wEAAAAAAAAAAAAAAAAAAAABAAAAAAAAAAAAAAAAAAAAAAAAAAAA//8AAP//AAAAAAAAAAAAAP//AAD//wAAAAAAAAAA/////////////wAA//8AAP//AAAAAAAA//8AAP//AAD//wAA//8AAP//AQD//wEA//8BAP//AAD//wEA//8BAP//AAD//wAA//8BAP//AAAAAAAAAAAAAAAAAAD//wAA//8BAP//AQD//wEA//8AAP//AAD//wEA//8BAP//AQD//wEA//8BAAAAAQAAAAAA//8AAP//AAAAAAAAAQAAAAEAAAABAAAAAAAAAAAAAQAAAAAAAAAAAAAAAQAAAAEAAAAAAAAAAAABAAAAAQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP//AAAAAAAAAAAAAP////8AAAAAAAAAAP//AAD//wAAAAAAAAAAAQAAAAAAAAAAAP//AAD//wAA////////AAD//wAA//8BAP//AAD//wAA//8AAP//AAD//wEAAAAAAAAAAAD//wAA//8AAAAAAAAAAP////8AAP//AQAAAAAAAAD//wAAAAAAAAAAAAABAAAAAAAAAAAAAAABAAEAAQAAAAAAAAAAAAAAAAABAAAAAQAAAAEAAAABAAAAAAAAAAAAAAAAAAAAAQABAAAAAQAAAAAAAAAAAAAAAAAAAAAAAAAAAAEAAAAAAAAAAAAAAAAAAAABAAAAAAD//wAA//8AAP//AQD//wEAAAAAAAAAAAD//wAAAAABAAAAAQAAAAEAAAAAAAAAAAAAAAAAAAAAAAAAAAD//wAA//8AAP//AAAAAAEAAAABAAAAAAD//wAA//8AAP//AAD//wAAAAAAAAAAAAABAAAAAAD//wAA//8BAAAAAQAAAAAAAAAAAAAAAQAAAAEAAAABAAAAAAAAAAAAAAAAAP//AQD//wAA//8AAAAAAAAAAAEAAAAAAAAAAAAAAAEA//8AAP//AAD//wAAAAABAAAAAAAAAAAAAAABAAAAAAD//wAA/////wAA//8AAP//AAD/////AAAAAAAAAAD//wAA//8AAAAA//8AAP////8AAP//AAD///////8AAAAA////////////////AAD//////////wAAAAAAAAAAAAD//wAAAAAAAAAAAAAAAAAAAAD//wAA//8AAP//AAAAAAAAAAAAAAAAAAAAAAAAAAABAAAAAQAAAAEA//8AAP//AAAAAAEAAAABAAAAAAAAAAAAAAAAAP//AAAAAAEAAAABAAAAAAABAAEAAQABAAAAAQAAAAEAAAAAAAEAAAABAAEAAAABAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABAAEAAAABAAAAAAAAAAEAAQABAAAAAQAAAAAAAAAAAAAAAAAAAAAAAAAAAAEAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABAAAAAQAAAAAAAQAAAAEAAAAAAP//AAAAAAEAAAAAAAAAAAD//wEA//8BAP//AQD//wAAAAAAAAAAAAAAAAEA//8BAP//AAAAAAAAAAABAP//AQD//wAAAAAAAAAAAAD//wAA//8AAP//AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD//wAAAAAAAAAAAAAAAAAAAAD//wAA//8AAAAAAAAAAAAAAAAAAAAA/////wAA//8AAAAAAAAAAP////////////8AAAAAAAAAAP//AAD//wAA////////AAD//wAA//8AAP//AAAAAAAAAAAAAAAAAAD//wAA//8BAP//AAAAAAAA//8AAP//AQD//wAA//8AAP//AQAAAAEAAAABAP//AQAAAAEAAAABAP//AQD//wEAAAABAAAAAQAAAAEAAAABAAAAAQABAAEAAQABAAAAAQAAAAEAAAABAAAAAQAAAAEAAAABAAAAAAAAAAAAAAABAAAAAAAAAAAAAAABAAEAAQAAAAEAAAAAAAAAAAAAAAEAAAABAAAAAAAAAAAAAAD//wAA//8AAAAAAQAAAAAA/////////////wAAAAAAAAAAAAD//wAA//8AAAAAAAAAAP////8AAP//AAD//wAA////////AAD//wEA//8AAP//AAD///////8AAP7/AAD//wAA//8AAP//AAD//wEA//8AAP//AAD//wEAAAABAP//AAD//wAA//8BAAAAAAAAAAAA//8AAAAAAAAAAAAA//8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP//AAD//wAAAAAAAAAAAAD//wAAAAAAAAAA/////wAA//8AAAAAAAAAAP///////wAAAAD//wAA//8BAP//AAD//wAA//8AAP//AQD//wEA//8BAAAAAQD//wEA//8BAAAAAQAAAAAA//8AAP//AQAAAAEA//8AAAAAAAAAAAEAAAABAP//AQAAAAEAAQABAAAAAQAAAAEAAAABAAEAAQAAAAEAAAACAAEAAgABAAEAAAABAAEAAgABAAEAAQABAAEAAQABAAEAAQABAAEAAQABAAEAAAABAAEAAQABAAEAAQABAAEAAQABAAEAAQABAAEAAQABAAAAAAAAAAAAAAABAAAAAQAAAAEAAAABAAAAAQAAAAEAAAABAAAAAQABAAEAAQABAAAAAQAAAAEA//8AAAAAAAAAAAAAAAABAP//AQD//wEA//8AAP//AAD//wAA//8AAP//AAD//wAA//8BAP//AQD//wAA//8AAP//AAD//wAA//8AAP//AAD//wAA//8AAP//AAD//wEA//8AAP7/AAD//wEA//8AAP//AAD//wAA//8AAP//AAD//wAA//8AAP//AAD//wAA//8AAP//AAD//wAA//////////8AAAAAAAAAAP///////wAA//8AAP////////////8AAAAA//8AAP//////////AAAAAP//AAD/////////////AAD//wAA/////////////wAA/v8AAP7/AAD//wAA//8AAP//AAD//wEA//8BAP//AAD//wAA//8BAP//AAD//wAA//8BAP//AQAAAAEA//8BAP//AQD//wEAAAABAAAAAQAAAAEAAAABAAAAAQAAAAEAAAABAAAAAQAAAAIAAAACAAAAAQAAAAEAAAABAAEAAQABAAEAAAABAAAAAQABAAEAAQABAAEAAQABAAEAAQAAAAEAAQABAAEAAQABAAEAAAAAAAAAAAABAAEAAAABAAAAAQAAAAEAAAAAAAAAAAAAAAAAAAAAAAEAAAAAAAAAAAAAAAAAAAAAAAAA//8AAP//AAD//wAA//8AAP//AAAAAAAAAAAAAP//AAD//wAA//8AAP//AAD//wAA//8AAAAAAAD//wAA//8BAP//AAAAAAAAAAAAAP//AAD//wAA//8AAAAAAAAAAAEAAAABAAAAAAAAAAAA//8AAP//AAAAAAAAAAABAP//AAD//wAAAAAAAAAAAAD//wAA//8AAAAAAAAAAAAA//8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAQAAAAAAAAAAAAAAAAAAAAEAAAAAAAAAAAAAAAAAAAAAAP//AAD//wAA//8AAAAAAQAAAAEA//8AAAAAAAAAAAAAAAAAAP//AAAAAAEAAAABAP//AQD//wEAAAABAAAAAAD//wAAAAABAAAAAQD//wEA//8AAP//AAD//wAA//8AAP//AAAAAAAAAAAAAAAAAAAAAAAAAQAAAAEAAQAAAAEAAQAAAAAAAAAAAAAAAAAAAAEAAAAAAAAAAAABAAAAAQABAAEAAAABAAAAAQAAAAAAAQAAAAEAAQABAAEAAQAAAAEAAAABAAAAAQAAAAEAAAABAP//AAD//wAAAAAAAP//AQD//wEAAAAAAAAAAAD//wEA//8BAAAAAAAAAAAAAAABAP//AQD//wEA//8BAP//AAD//wAA////////AAD//wAA//8AAP////////////////////////////8AAP//AAD//wAAAAAAAP//AAD//wAA//8AAAAAAAD/////////////AAAAAAAAAAAAAP//AAD//wAAAAD//wAA//8AAP//AAD//wAA//8AAAAAAQAAAAAAAAAAAP//AAAAAAAAAAABAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD//wAAAAAAAAAAAAAAAAEAAAABAAAAAQAAAAAAAAAAAAAAAQAAAAEAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABAAAAAAAAAAAA//8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAEAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAQABAAAAAQAAAAAAAAAAAAEAAQAAAAEAAQAAAAEAAAAAAAAAAAAAAAEAAAABAAAAAAAAAAAA//8BAAAAAQAAAAAAAQAAAAAAAAAAAAEA//8BAAAAAAAAAAAAAAABAAAAAQD//wEA//8AAP//AAAAAAAAAAABAP//AAAAAAAA//8AAP//AAAAAAAA////////AAD//wAAAAD//////////wAA//8AAAAA//8AAP////8AAP//AAAAAAAAAAAAAP//////////////////AAAAAP//AAD//wAAAAAAAAAAAAD//wAA//8AAP//AAAAAAAA//8BAP//AQD//wAA//8AAP//AAD//wAA//8BAAAAAQAAAAAAAAAAAP//AQD//wEAAAABAAAAAQD//wEA//8BAAAAAQAAAAEAAAABAAAAAQAAAAAAAQABAAAAAQAAAAEAAAABAAAAAQAAAAAAAAAAAAAAAQABAAEAAQAAAAAAAAAAAAEAAAAAAAEAAAABAAEAAQABAAEAAQABAAAAAAABAAAAAQAAAAEAAAAAAAAAAAAAAAAAAAAAAAAAAQABAAEAAAAAAAAA//8BAAAAAQAAAAEAAAAAAP//AAD//wEAAAABAAAAAQAAAAAAAAABAAAAAQAAAAEA//8BAAAAAQAAAAEAAAAAAP//AAD//wEAAAAAAAAAAAAAAAAAAAAAAAAAAQD//wEA//8BAP//AQAAAAEAAAABAP//AQD//wEA//8BAAAAAQD//wAA//8AAAAAAAD//wAAAAAAAAAAAAD//wAA//8AAP//AAAAAAAA//8AAP////8AAP//AAD//wAAAAAAAP//AAD/////////////AAD//wAA/////////////wAA////////AAD//wAAAAAAAP////////////////////8AAAAA//8AAP//AAD//wAA//8AAP//AAD//wAA/v8AAP//AAAAAAAA//8AAP//AAD//wAA//8BAP//AAD//wAAAAAAAAAAAAD//wAA//8BAAAAAQD//wEA//8AAP//AAD//wEA//8BAAAAAQAAAAEAAAABAAAAAgABAAEAAAABAAAAAAAAAAEAAAABAAAAAQAAAAEAAQABAAAAAQAAAAEAAAABAAAAAQAAAAAAAAAAAAEAAQABAAEAAAAAAAAAAAAAAAAAAAAAAAAAAAABAAAAAQAAAAAAAAAAAAAAAQAAAAEAAAAAAP//AAAAAAAAAAAAAAAAAAAAAAAAAAABAAAAAQAAAAAA//8AAP//AAD//wEA//8AAP//AAD//wEAAAAAAP//AAD//wAAAAABAAAAAAAAAAAAAAAAAAAAAAAAAAAA//8AAP//AAAAAAEAAAABAAAAAAD//wAAAAAAAAAAAAD//wAA//8AAAAAAAAAAAAA//8BAP//AAAAAAAAAAAAAAAAAAAAAAAA//8AAP//AQD//wEA//8AAAAAAAAAAAAAAAAAAAAAAAD//////////wAAAAAAAAAA/////wAA//8AAP//AAAAAAAAAAABAP//AAD//wAAAAAAAP//AQD//wEAAAAAAAAAAAAAAAAA//8AAP//AAD//wAA//8BAP//AQD//wAAAAABAAAAAQD//wEA//8BAAAAAQAAAAEAAAABAAAAAQAAAAEAAAABAAAAAQAAAAEAAAABAAAAAQD//wEAAAABAAAAAQAAAAEAAAABAAAAAQAAAAEAAQACAAEAAQABAAEAAQABAAAAAQAAAAEAAQABAAEAAQAAAAEAAAAAAAEAAQABAAEAAAAAAAEAAAABAAAAAAAAAAAAAAABAAAAAQAAAAAAAAABAP//AQAAAAEA//8AAP//AQD//wEAAAAAAP//AQAAAAEAAAAAAP//AQD//wEA//8AAP//AAD+/wAA/v8AAP//AAD//wAA//8AAP7/AAD//wAA//8AAP7/AAD+/wEA//8BAP7/AAD//wAA//8AAP///////wAA/////////////wAA//8AAP//AAD///////8AAP//AAD/////AAD//wAA//8AAAAAAAAAAAAA/////////////wAA//8AAP//////////AAD//wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP//AAD/////AAD//wAAAAD//wAA//8AAAAAAAD//wEA//8AAP//AAD//wEA//8AAP//AAAAAAAAAAAAAAAAAAAAAAAAAAABAP//AAD//wAAAAAAAAAAAQAAAAAA//8AAP//AQD//wAAAAAAAAAAAQAAAAEAAAABAAAAAQABAAEAAAABAAEAAQABAAEAAAABAAAAAQAAAAEAAQABAAEAAQABAAEAAAACAAAAAQAAAAEAAQABAAAAAAAAAAAAAAAAAAEAAQABAAEAAQAAAAAAAAABAAAAAQABAAEAAAABAAEAAQABAAEAAAABAAAAAQABAAEAAQABAAEAAQABAAAAAAAAAAAAAAABAAAAAAAAAAAAAQD//wAA//8AAP//AQAAAAEAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP//AAD//wAAAAAAAAAAAAAAAAAAAAAAAP////8AAP//AAAAAAAAAAD//wAAAAAAAAAAAAAAAAAAAAAAAAAAAAD//wAAAAAAAAAA//8AAP//AAD//wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP//AAD//wAAAAAAAAAAAAAAAP//AAD//wAAAAAAAP////8AAAAAAAAAAP//AAD//wAAAAAAAAAA//8AAP//AAAAAAAAAAAAAP//AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA/////wAAAAAAAAAAAQAAAAAAAAAAAAEAAAAAAAAA//8AAAAAAAABAAEAAQAAAAEAAAABAAEAAQAAAAEAAAABAAEAAQAAAAEAAAABAAAAAQAAAAAAAQAAAAAAAAAAAAAAAAAAAAAAAAAAAAEAAQABAAAAAAAAAAEAAAABAAAAAAAAAAAA//8BAAAAAQAAAAEAAAABAP//AQD//wAA//8BAP//AQD//wAA//8BAP//AQD//wEA//8BAAAAAQD//wEAAAAAAAAAAAAAAAAA//8AAP//////////AAAAAAAAAAAAAAAA/////wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP//AAD//wAAAAAAAAAAAAAAAAAA//8AAP//AAD//wAAAAAAAAAA//8AAAAAAAD//wAAAAAAAAAA//8AAP//AAAAAAAA//8AAP//AAAAAAEAAAABAAAAAAAAAAAAAAABAAAAAQD//wAA//8AAP//AAD//wEAAAABAAAAAQAAAAAAAAABAAAAAQAAAAAAAAAAAAAAAAAAAAAA//8AAP//AAAAAAEAAAABAAAAAAAAAAAAAAABAAAAAQD//wAA//8BAP//AQD//wAAAAABAAAAAAAAAAAAAAAAAAAAAQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAEAAAABAAAAAQAAAAAAAAAAAAAAAAAAAAEAAQABAAAAAAAAAAEAAQABAAEAAQAAAAEAAAABAAAAAQAAAAAAAAAAAAAAAQABAAEAAQAAAAAAAAAAAAEAAAABAAAAAAAAAAAA//8BAAAAAQD//wEA//8BAAAAAQAAAAAAAAAAAAAAAQD//wAA//8AAP//AAAAAAEAAAAAAP//AAAAAAEAAAAAAP//AAD//wAA////////AAAAAAAA//8AAP/////+/wAA//8AAP//AAD//wAA/////////////wAA////////////////////////////////AAD//wAA//8AAP////8AAP//AAAAAP//AAAAAAAAAAD/////AAAAAAAA//8AAAAAAAAAAAAAAAABAAAAAAAAAAEAAAABAAAAAAAAAAAAAAAAAP//AAAAAAAAAAAAAAAAAAAAAAEAAAABAAAAAQAAAAAAAAAAAAAAAQAAAAEAAAABAAAAAQAAAAEAAAABAAAAAAAAAAAAAAAAAAAAAAD//wAAAAAAAAAAAQD//wEA//8BAAAAAQAAAAEAAAAAAAAAAAAAAAEAAAAAAAAAAAAAAAEAAAAAAAAAAAAAAAAAAAABAAAAAQAAAAAAAAABAAEAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAEAAAABAAAAAAAAAAAA//8BAAAAAQAAAAAA//8BAAAAAQAAAAAAAAAAAAAAAQAAAAAAAAAAAAAAAQAAAAEAAAAAAP//AQAAAAEAAAAAAP//AAAAAAAAAAAAAAAAAAAAAAEA//8AAP//AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD/////AAD//wEAAAAAAP//AAD///////////////8AAAAAAAAAAP////8AAP//AAD//////////wAAAAAAAP//////////AAD//wAA//8AAAAAAAAAAAAA//////////8AAAAA//8AAP//AAD//wEA//8BAAAAAAAAAAAAAAABAAAAAAD//wAA//8BAP//AQD//wEAAAABAAAAAQD//wEA//8BAAAAAQAAAAEAAAABAAAAAgAAAAIAAQABAAEAAQAAAAEAAAABAAAAAQAAAAEAAAABAAAAAQAAAAEAAAABAAAAAQABAAAAAQABAAAAAQAAAAAAAAAAAAAAAQAAAAEAAAAAAAEAAAAAAAAAAAAAAAAAAQABAAEAAQAAAAEA//8BAP//AQD//wEA//8BAAAAAQAAAAEAAAABAAAAAQD//wAAAAABAAAAAQAAAAAAAAAAAAAAAAD//wAA//8BAAAAAQAAAAEA//8BAAAAAAAAAAAA//8AAP//AAD//wAA//8AAP//AAD//wAA//8AAP//AAD//wEAAAABAAAAAAD//wAA//8AAP//AAD//wAA//8AAP//AAD//wAA//8AAP//AAAAAAAAAAAAAP//AAAAAAAAAAD//wAAAAD//wAA//8AAP//AAAAAAAAAAAAAP////////////8AAAAAAAD//wAA//8AAP//AAAAAAAA////////AAD//wAA/////wAA//8AAAAAAAAAAP////8AAP//AAAAAAEAAAAAAP//AQD//wEAAAABAAAAAAD//wAA//8BAAAAAAD//wAAAAAAAAAAAQAAAAEA//8BAP//AQD//wEAAAABAP//AQAAAAEAAAABAAEAAQAAAAEAAAABAP//AQD//wEAAAABAAAAAQAAAAEAAAABAAAAAQAAAAEAAAACAAEAAQAAAAEAAQABAAEAAQAAAAIA//8BAAAAAQAAAAEAAQAAAAEAAAABAAAAAQABAAEAAAABAAAAAAAAAAAAAQAAAAAAAAAAAAAAAAAAAAAAAQD//wEA//8AAP//AAD//wAA//8AAAAAAAD//wAA//8BAP//AQD//wEA//8BAAAAAAD//wAA//8AAP//AAD//wAA//8AAP//AQD//wAA//8AAP//AAD//wAA//8AAP//AAD//wAAAAAAAP//AAD//wAAAAAAAAAAAAAAAAAA//8AAAAAAAAAAAAAAAD//wAA//8AAAAAAAAAAAAAAAAAAP//AAAAAAAAAAD//wAA//8AAP//AAD//wAAAAAAAAAAAAAAAAAAAAAAAP//AAAAAAAAAAAAAAAA/////wAAAAAAAAAAAAAAAP////8AAAAAAAAAAP//AAD//wAAAAAAAAAAAAD//wAA//8AAAAAAQAAAAEAAAAAAAAAAAAAAAAAAQABAAAAAAAAAAAAAQABAAAAAQAAAAEAAAABAAAAAQAAAAEAAQABAAAAAQAAAAEAAAABAAAAAQABAAEAAQAAAAEAAAABAAEAAQABAAAAAQAAAAAAAQABAAEAAQABAAIAAAACAAAAAQABAAEAAAABAAAAAAAAAAAAAAABAAAAAQAAAAAAAAAAAAEAAAABAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAQAAAAEAAAAAAAAAAAD//wAAAAAAAAAA////////AAAAAAAAAAAAAP//AAAAAP//AAD//wAAAAAAAAAA//////////8AAAAA//8AAP////8AAP//AAD//wAA//8AAP//AAAAAAEAAAAAAP//AAD//wAA//8BAP//AAD//wAAAAAAAP//AAD//wAAAAAAAP//AQD//wAA//8AAP//AAD//wAAAAAAAAAA////////AAAAAAAAAAD/////AAD//wAAAAAAAAAAAAD//wAA//8AAAAAAAAAAAAA//8AAAAAAAAAAAEA//8AAP//AAAAAAAAAAAAAP//AAD///////8AAAAAAQAAAAAAAAAAAAAAAQD//wEA//8AAAAAAAAAAAEA//8AAP//AAAAAAAAAAABAAAAAQAAAAEAAAAAAAAAAAAAAAAAAAAAAAEAAAABAAAAAQAAAAAAAAAAAAAAAQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAEAAAABAAAAAAAAAAEAAAABAAAAAAAAAAAAAAABAAEAAQABAAEAAAABAAAAAQAAAAEAAAABAAAAAQAAAAEAAQAAAAAAAAAAAAEAAQABAAEAAAAAAAAAAAABAP//AQAAAAAAAAAAAAAAAAAAAAEA//8AAP//AAD//wAAAAAAAAAAAAAAAAAAAAAAAP//AAAAAAAAAAAAAAAAAAD//wAA//8AAP//AAAAAAAAAAAAAAAAAAAAAAAA////////AAAAAAAAAAAAAAAA/////wAA////////////////AAAAAAAAAAAAAP//AAD//wAAAAAAAAAAAAD//wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA//8AAAAAAAAAAAEAAAAAAAAAAAD//wAAAAAAAAAAAAAAAAEAAAAAAAEAAAAAAAAAAAAAAAAAAQAAAAEA//8AAAAAAAAAAAEAAAAAAAAAAAAAAAEAAAAAAP//AQAAAAAAAAAAAAAAAQAAAAEAAAABAAAAAQAAAAEAAAAAAAAAAAAAAAAAAAAAAAAAAAABAAEAAAAAAP//AAAAAAAAAQABAAEAAAAAAAAAAAAAAAAAAAABAAAAAQAAAAAAAAABAAAAAQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD//wAA//8BAP//AQD//wEA//8BAAAAAQD//wAA//8AAAAAAQAAAAEAAAABAP//AAD//wAAAAABAAAAAAAAAAAA//8AAAAAAQAAAAAAAAAAAP//AAD//wAAAAAAAAAAAAD//wAA//8AAP//AAAAAP////8AAP//AAAAAAAAAAD//wAA//////////8AAP//AAD///////8AAAAAAAAAAP//////////AAAAAP//AAD//wAAAAAAAAAAAAAAAAAAAAAAAAAA//8AAP//AAAAAAAAAAAAAAAAAAAAAP//AAD//wEAAAAAAAAAAAD//wAA//8BAAAAAAAAAAAAAAAAAAAAAAABAAEAAAABAP//AQAAAAEAAAABAAAAAAAAAAEA//8BAP//AQAAAAEAAAABAAEAAQABAAEAAQAAAAAAAQAAAAEAAAABAAAAAQAAAAEA//8BAP//AQAAAAEAAAABAAAAAQAAAAEAAAAAAAEAAAAAAAEAAAAAAAAAAAABAAEAAAABAP//AAD//wAAAAAAAAEAAAAAAAAAAAAAAAAAAAABAAAAAQAAAAAAAAAAAAAAAQAAAAAAAAAAAAAAAAAAAAAA//8AAP//AAAAAAAAAAAAAP//AAAAAAEAAAAAAP////8AAAAAAAAAAAAAAAD//wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA//8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP//AAAAAAAAAAD//wAA//8AAAAA//8AAAAAAAAAAAAAAAAAAAAA//8AAAAAAAAAAP//AAD///////8AAAAAAAD//wAA//8AAP//AAAAAP//AAAAAP//AAD//wAA//8AAAAAAAAAAAAAAAAAAAAAAAD//wAA//8AAAAAAQAAAAEAAAABAAAAAAD//wAA//8AAAAAAQAAAAEAAAAAAAAAAAAAAAEAAAABAP//AQAAAAEAAAABAAAAAQAAAAEAAAABAAAAAQAAAAEAAAABAAEAAAAAAAEAAAABAAEAAQAAAAEAAAABAAAAAQAAAAAAAAABAAAAAQAAAAAAAAABAAAAAQAAAAEAAAAAAAAAAQAAAAEAAAABAAEAAQABAAAAAAAAAAAAAAABAAAAAAAAAAAA//8AAAAAAQAAAAEAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAEA//8AAP//AAD//wAA//8BAP//AQD//wAA//8AAP//AAD//wEA//8BAAAAAQD//wAA//8AAP//AAAAAAAAAAAAAP//AAD//wAA//8BAP//AQAAAAAA////////AAAAAAAAAAAAAP//AAD//wAA////////AAD//wAAAAAAAP//AAD//wAAAAAAAAAA//////////8AAAAAAAD//wAA//8AAP//AAAAAP//AAAAAAAAAAAAAP///////wAAAAAAAAAAAAAAAAAA//8AAAAAAAAAAAAAAAAAAP//AAAAAAAAAAAAAAAAAAAAAAAAAAAAAP//AAAAAAEAAAAAAAAAAAD//wAAAAABAAAAAQAAAAAAAAAAAAAAAAAAAAAAAAABAAAAAQAAAAEAAAAAAAAAAQAAAAEA//8BAAAAAQABAAIAAAABAAAAAQAAAAEAAAABAAAAAQABAAEAAQABAAAAAQAAAAEAAQABAAEAAQABAAEAAQABAAEAAQAAAAEAAQABAAEAAQAAAAEAAAAAAAAAAAAAAAEAAQABAAEAAAABAAAAAAABAAAAAQABAAAAAQAAAAAAAAAAAAAAAAAAAAAA//8AAP//AAAAAAAAAAAAAAAAAAAAAAEAAAABAAAAAQD//wEAAAABAAAAAAAAAAAA//8AAP//AAAAAAAAAAAAAP//AAD//wAA//8AAP//AAD//wAA//////////8AAAAAAAAAAP//AAD+/wAA//8AAP//AAD//wAA////////AAD//wAA//8AAP//AAD//wAA//8AAP//AAD/////////////AAD//wAAAAAAAAAAAAD//wAA//8AAAAAAAAAAAAAAAAAAP//AAD//wAA//8AAAAAAAD//wAA//8AAP//AAAAAP//AAD//wAAAAAAAAAAAAAAAAAA//8AAP//AAAAAAAAAAAAAP//AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD//wAA//8AAAAAAAD//wAA//8BAAAAAQAAAAAAAAAAAP//AQAAAAEAAAABAAAAAQAAAAEAAAABAAAAAQD//wEAAAABAAAAAQABAAEAAAABAAAAAQAAAAEAAAABAAAAAgAAAAIAAAABAAAAAQAAAAEAAAABAAAAAAAAAAEAAAABAAEAAQABAAEAAQABAAEAAAABAAAAAAAAAAAAAAABAAAAAQABAAEAAQAAAAAAAQAAAAEAAAABAP//AQD//wEAAAAAAAAAAAAAAAAAAAAAAP//AQAAAAEAAAAAAAAAAAD//wAA//8BAP//AQD//wEA//8BAAAAAQD//wEA//8AAAAAAAD//wEA//8AAP///////wAA//8BAP//AAAAAAAA//8AAP//AAD//wAA//8AAP//AAAAAAAAAAD/////AAAAAAAA//8AAP//AAAAAAAA//8AAP//AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA//8AAP//AAD//wEA//8BAAAAAAAAAAAAAAAAAP//AQD//wAAAAAAAAAAAAD//wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA//8AAAAAAAAAAAEAAAAAAP//AAAAAAAAAAAAAP//AAAAAAEAAAAAAP//AAAAAAAAAQABAAAAAAAAAAAAAAABAAAAAQAAAAAAAAAAAAEAAQAAAAAA//8AAP//AQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAQAAAAEAAQABAAAAAAAAAAAAAQABAAAAAAAAAAAAAAABAAAAAQAAAAAAAAAAAAEAAQABAAAAAAAAAAAAAAAAAAAAAAAAAAEAAAABAAAAAAAAAAAAAAAAAAAAAQAAAAAAAAAAAAAAAAD//wEAAAAAAAAAAAD//wAAAAAAAAAAAAAAAAEA//8AAAAAAAD//wAA//8AAP7/AAD//wAA//8AAP7/AQD//wAA//8AAP//AAD//wAA//8AAP//AAD+/wAA//8AAP//AAAAAAAA/////////////wAAAAAAAP////8AAP//AAAAAAAAAAAAAAAAAAAAAAAAAAAAAP//AAAAAAAAAAAAAAAAAAAAAAAA//8AAAAAAAAAAAEA//8BAAAAAAAAAAAAAAAAAP//AQAAAAEAAAAAAAAAAAD//wAA//8AAP//AAD//wEAAAABAAAAAQAAAAAAAAAAAAAAAQAAAAEAAAAAAAAAAQD//wEA//8AAAAAAAAAAAAA//8AAAAAAAAAAAEAAAAAAAAAAAABAAEAAAAAAAAAAAABAAAAAQAAAAAAAAABAAAAAQAAAAEAAAAAAAEAAAABAAAAAAAAAAAAAAAAAAEAAAABAAAAAQABAAAAAQAAAAEAAAAAAAAAAAAAAAAAAAAAAAAAAQAAAAEAAAAAAAAAAQAAAAEAAAAAAAAAAQAAAAEAAAAAAAAAAAAAAAAA//8AAP//AAAAAAEAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA//8AAP//AAAAAAAAAAAAAAAA//8AAAAAAAAAAAAA//8BAAAAAQAAAAAA////////AAD//wAA//8AAP//////////////////AAD//wAA//8AAP7/AAD+/wAA//8AAP/////+/wAA//8AAP///////wAA//8AAP//AAD///////8AAP//AAD///////8AAAAAAAAAAP////8AAAAAAAAAAAAAAAD//wAAAAABAAEAAAAAAAAAAAAAAAAAAQAAAAAAAAAAAAAAAAAAAAEAAAABAAAAAQABAAEAAQABAAAAAQAAAAEAAAABAAAAAAAAAAAAAAABAAAAAQAAAAEAAAABAAEAAQABAAEAAAABAAAAAQAAAAEAAAABAAAAAQABAAAAAAAAAAAAAQAAAAEAAAABAAAAAQAAAAEAAAABAAAAAAAAAAEAAAABAAEAAAABAAAAAQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAQABAAAAAAAAAP//AAAAAAEAAAABAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA//8AAP//AAAAAAAAAAAAAP//AAAAAAAAAAAAAAAA//8AAP//AAAAAP//AAD//wAAAAAAAAAAAAD//wAAAAAAAAAAAAD//wAA//8AAAAA/////wAA//8AAP//AAD//wAA//8AAAAAAAAAAAAA//8AAP//AAAAAAAAAAAAAP//AAD//wAAAAAAAP//AAD//wAA/////////////wAAAAAAAP//AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA//8AAAAAAAAAAAAAAAAAAAAAAQAAAAAAAAAAAAEAAAABAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAEAAAABAAAAAQABAAEAAAABAAAAAQAAAAEAAAABAAAAAQAAAAEAAAABAP//AQD//wEAAAABAAAAAQD//wEA//8BAAAAAQD//wEAAAABAAAAAQAAAAAAAAAAAAAAAQAAAAAAAAAAAAAAAQAAAAEAAAAAAAAAAQABAAEAAAAAAAAAAAABAAEAAAAAAAAAAAAAAAAAAAAAAAAAAAABAAAAAQD//wAA//8AAP//AQAAAAAAAAAAAP//AAD//wAA//8AAP//AAD//wEA//8AAP//AAD//wAAAAAAAAAAAAAAAAAAAAABAAAAAQAAAAAAAAAAAAAAAAAAAAAAAAABAP//AAD//wAA//8AAP//AAAAAAEAAAABAAAAAAD///////8AAAAAAAAAAAAAAAAAAP//AAD/////AAD//wAA//8AAP//////////AAAAAAAAAAAAAP//AAAAAP//AAD//wAA//8AAAAAAAAAAAAAAAAAAP//AAD//wAA//////////8AAAAA//8AAP//AAAAAAAA//8AAAAAAAAAAAAA//8AAP//AAAAAAAAAAAAAAAAAAAAAAAA//8AAP//AAAAAAEAAAABAAAAAQAAAAAAAAABAAAAAQAAAAAAAAABAAAAAQAAAAEAAAABAAAAAQABAAEAAAABAP//AQAAAAEAAAABAAEAAQAAAAEAAAABAAEAAQABAAEAAQABAAAAAQAAAAEAAAABAAAAAQAAAAEAAQABAAEAAQAAAAAAAAAAAAAAAQAAAAEAAAABAAAAAAAAAAAAAAABAAAAAQAAAAAAAQAAAAEAAQAAAAAAAAAAAAAAAAABAAAAAQAAAAAA//8AAP//AQAAAAAAAAAAAAAAAAAAAAAA//8AAP//AAAAAAEAAAAAAP//AAD//wAA//8AAAAAAQAAAAEA//8BAP//AAAAAAAAAAAAAAAAAAD/////////////AAAAAAAAAAAAAP//AAD//wAAAAAAAAAAAAAAAP//AAAAAP//AAD//wEAAAAAAAAAAAAAAAAAAAD/////AAAAAAAAAAAAAAAA//8AAP////8AAAAA//8AAAAAAAAAAAAAAAAAAAAAAAAAAP//AAD//wAAAAAAAAAAAAAAAP//AAAAAAAAAAAAAP//AAD//wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD//wAAAAAAAAAAAAAAAAAAAAAAAAAAAQAAAAEA//8AAAAAAAAAAAAAAQAAAAEAAQAAAAEAAAAAAAAAAAAAAAEAAAABAAAAAQAAAAEAAAABAAAAAQAAAAEAAAABAAEAAQABAAEAAAABAAAAAQAAAAAAAQABAAAAAQAAAAAAAQAAAAEAAAABAAAAAAAAAAAAAAAAAAAAAQAAAAEAAAAAAAEAAAAAAAEAAAABAAAAAAAAAAAAAAABAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP//AAD//wEAAAABAAAAAAD//wAA//8AAP//AAAAAAAA//8AAP//AAD//wAAAAAAAAAA/////wAAAAAAAAAAAAD///////8AAAAAAAAAAP////8AAAAAAAAAAAAA//8AAP//AAAAAAAA//////////8AAP//AAAAAAAAAAAAAP//AAAAAAAAAAAAAP////8AAAAAAAAAAP//AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAEAAAABAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP//AAAAAAEA//8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAEAAAABAAAAAAAAAAAAAAABAAAAAAAAAAAAAAABAAEAAAAAAAAAAAABAAAAAQAAAAAAAAAAAAEAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAEAAAAAAAAAAAAAAAAAAQAAAAEAAAAAAAAA//8BAAAAAQAAAAAA////////AAAAAAAAAAAAAAAAAAD///////8AAAAAAAAAAAAA//8AAP//AAD//wAA////////AAD//wAAAAAAAAAAAAD//wAA////////AAD//wAAAAAAAP//AAD//wAA//8AAP//AAAAAAAAAAAAAAAA//8AAP//AAAAAP//////////AAD//wAAAAAAAAAAAAAAAAAAAAAAAAAAAQD//wAAAAAAAAAAAQD//wAA//8AAAAAAQAAAAAAAAAAAAAAAAAAAAEAAAAAAAAAAAAAAAEAAQABAAEAAAABAAAAAQABAAEAAQAAAAEAAAABAAAAAAAAAAAAAAABAAAAAQAAAAEAAAAAAAAAAQAAAAEAAAABAAAAAAAAAAAAAQABAAEAAQABAAEAAQABAAAAAAABAAAAAQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAEAAAABAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABAAAAAQAAAAAAAAD//wAA//8AAAEAAAABAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD//////////wAA//8AAAAAAAAAAP//AAAAAP//AAD//wAA//8AAP//AAD//wAAAAAAAAAAAAAAAAAA//8AAAAAAAAAAAAAAAAAAP//AAD//wAAAAAAAAAAAAD//wEA//8AAP//AAAAAAAA//8AAP///////wAAAAABAAAAAAD//wAA//8AAP//AAD//wAA//8AAAAAAAD///////8AAAAAAAAAAAAAAAD//wAA//8AAAAAAAAAAAAA//8AAP//AAAAAAAA//8BAP//AAAAAAAAAAABAP//AAD//wAAAAAAAAAAAAD//wAA//8BAP//AQD//wEAAAABAAAAAQD//wAA//8BAAAAAQAAAAEA//8AAP//AAAAAAEAAAABAAAAAAAAAAEAAAABAAAAAQAAAAEAAQABAAAAAQAAAAEAAAABAAEAAAABAAAAAAABAAEAAQABAAAAAQAAAAEAAQABAAEAAQAAAAAAAQAAAAEAAAAAAAEAAAABAAAAAAAAAAAAAAABAAAAAQAAAAAAAAAAAAAAAQABAAEAAQABAAAAAQAAAAEAAAABAAAAAAAAAAAAAAABAAAAAAAAAAAAAAAAAAAAAQAAAAAAAAABAAAAAQAAAAEAAAAAAAAAAAAAAAAAAAAAAAAA////////////////AAAAAAAAAAABAAAAAQD//wAA//8AAP//AAD/////AAD//wAAAAD//wAA//8AAAAAAAD//wAA//8AAAAAAAAAAAAA//8AAAAAAAD//wAA//8AAAAAAAAAAAAA//8AAP//AAAAAP//AAD/////AAAAAP//AAD/////AAD/////////////AAD//wAAAAD//wAAAAAAAAAA/////wAAAAAAAAAAAQAAAAAAAAAAAP//AQD//wAAAAAAAAAAAQD//wEAAAABAAAAAAD//wAAAAAAAAAAAQAAAAEAAAABAAAAAQABAAEAAAABAAAAAAD//wAAAAABAAEAAQAAAAEAAAABAAAAAQAAAAEAAAABAAAAAQAAAAEAAAABAAAAAQAAAAEAAAABAAEAAQAAAAAAAAAAAAAAAAABAAEAAQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAQABAAAAAAAAAAAAAAAAAAEAAAAAAAAAAAAAAAAAAAABAAAAAQD//wAA//8AAP//AAAAAAEAAAAAAP//AAAAAAAAAAAAAP//AAD//wEAAAAAAAAAAAD//wAAAAAAAAAAAAD//wAA//8AAP//AAAAAAEA//8BAP//AAD//wAAAAAAAAAAAAD//wAA/////////////wAA//////////////////8AAP//AAD//wAAAAAAAAAA/////wAA//8AAP//AAAAAAAAAAAAAAAAAAAAAAAA//8AAP//////////AAD/////AAD//wAA////////AAD//wAAAAAAAAAAAAD//wEAAAABAAAAAAD//wAAAAAAAAAAAAAAAAAAAAABAAAAAAAAAAAAAQABAAEAAQABAAAAAAD//wEAAAAAAAAAAQAAAAEAAAABAP//AQAAAAEAAAABAAAAAQAAAAEAAAACAAAAAgAAAAEAAAABAAAAAQAAAAEAAAABAAAAAQAAAAIAAAABAAAAAQAAAAEAAAABAAEAAQAAAAEAAAABAAAAAQAAAAAAAAAAAAAAAQABAAEAAAABAAAAAQAAAAEAAAAAAAAAAAAAAAAAAQABAAAAAAAAAAAAAAAAAAEAAAAAAAAAAAAAAAAA//8AAAAAAAD//wAA//8AAP//AAAAAAAA//8AAAAAAAAAAAAA//8AAP//AAD///////8AAP//AAD//wAAAAAAAAAAAAD///////8AAP//AAD//wAA//8AAP//AAD/////////////AAD/////////////AAAAAAAAAAD//////////wAA//8AAP//AAAAAAAAAAD//////////////////wAA//8AAP//AAAAAAAAAAAAAAAA////////AAD//wAAAAAAAAAA/////wAAAAAAAAAAAAAAAAAAAAAAAAAAAQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAEAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP//AAAAAAAAAAABAAEAAAAAAAAAAQABAAAAAQAAAAEAAAABAAAAAQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAQAAAAEAAAAAAAAAAAAAAAEAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABAAAAAQAAAAAAAAABAAAAAQAAAAEAAAAAAAAAAAAAAAAAAQAAAAAA//8AAAAAAAAAAAEAAAABAAAAAAAAAAAAAAABAAAAAQAAAAAAAAAAAP//AAD//wAAAAAAAAAAAAD//wAA//8AAP//AAAAAAAAAAAAAAAAAAAAAAAAAAAAAP///////wAAAAD//wAA/////wAAAAAAAAAAAAAAAAAAAAD//wAAAAAAAAAA//8AAP//AAD//wAAAAAAAP//AAAAAAAAAAAAAAAA//8AAAAAAAD//wAA//8AAAEAAAABAAAA//8AAP//AAAAAAAAAQAAAAAAAAD//wAAAAAAAAAAAAD//wAAAAAAAAAA//8AAP//AAAAAAAAAQAAAAEA//8AAAAAAAAAAAAAAQAAAAEAAAABAAAAAAAAAAAAAAAAAAAAAAAAAAEAAQAAAAAAAAAAAAAAAQABAAEAAAAAAAAAAAAAAAAAAAABAAEAAQABAAEAAAABAAEAAQABAAEAAAAAAAAAAAAAAAEAAAABAAAAAQAAAAAAAAAAAAEAAQAAAAAAAAAAAAAAAAAAAAEAAAAAAAAAAAAAAAEAAQABAAAAAAAAAAAAAAAAAAEAAQAAAAEAAAABAAEAAQAAAAEAAAAAAAAAAAAAAAEAAAAAAAAAAAABAAAAAQAAAAAAAAD/////AAAAAAEAAAAAAAAAAAAAAAAAAAABAP//AQD//wEAAAAAAAAAAAD//wAA//8AAAAAAAAAAAAAAAAAAAAAAAD//wAA//8AAP//AAD//wAA//8BAAAAAAAAAAAAAAAAAAAAAAAAAP////8AAP//AAAAAAAAAAD/////AAAAAAAAAAAAAAAA//8AAAAAAAAAAP//AAD///////8AAAAAAAD//wAA//8AAAAAAAAAAAAA//8AAAAA//8AAP//AAD/////AAAAAAAAAAAAAAAAAAAAAAAAAAD//wAA//8AAAAAAQAAAAAA//8AAP//AAAAAP//AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABAAAAAAD//wAAAAAAAAAAAAABAAAAAAAAAP//AAAAAAAAAAABAAAAAAAAAAAAAQAAAAAAAAAAAAEAAQABAAEAAAAAAAAAAQABAAEAAQAAAAAAAAAAAAAAAQABAAAAAQAAAAEAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAEAAQAAAAAAAAAAAAAAAAABAAAAAAAAAAAAAAABAAAAAQAAAAAA//8AAAAAAAAAAAAAAQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD//wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD//wAA//8AAAAAAAAAAAAAAAAAAAAA//8AAP///////wAAAAAAAAAA//8AAAAAAAAAAAAAAAAAAP//AAAAAAAAAAD//wAA/////wAAAAAAAAAA//8AAP//AAAAAAAAAAD//wAA//8AAP//AAAAAAAAAAD//wAA//8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD//wAA//8BAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP//AAD//wAAAAAAAP//AQD//wEAAAAAAP//AQD//wEA//8AAAAAAAAAAAAAAAABAAAAAQAAAAAAAAAAAAAAAAAAAAEAAAABAAAAAAAAAAAAAAABAAAAAQABAAEAAAAAAAAAAAAAAAEAAAAAAAAAAAAAAAAAAAAAAAAAAQAAAAEAAAAAAAAAAQAAAAEAAQAAAAAAAAAAAAAAAQAAAAEAAAAAAAAAAAAAAAEAAAABAAAAAAAAAAAAAAAAAAAAAQAAAAEAAAAAAAAAAAD//wAA//8BAAAAAAAAAAAA//8AAP//AQAAAAAAAAAAAP//AAAAAAAA//8AAP//AQAAAAAA//8AAP//AAD//wAA//8AAP//AQD//wAA//8AAP//AAD//wAAAAAAAP///////wAA//8AAP//AQD//wEAAAAAAP//AAD//wEAAAABAAAAAAD//wAA//8AAAAAAQD//wAA//8AAAAAAQD//wEA//8BAP//AQAAAAAAAAAAAP//AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA//8AAP//AAD//wAAAAABAAAAAAAAAP//AAAAAAEAAAABAAAAAAAAAAAAAAABAAAAAQD//wAAAAABAAAAAQAAAAAAAAABAAAAAQAAAAAAAAABAAAAAQD//wAA//8BAAAAAQD//wEA//8AAAAAAQAAAAEA//8BAAAAAQAAAAIA//8CAP//AgAAAAEAAAABAAAAAgAAAAEA//8AAAAAAAAAAAAA//8AAAAAAAAAAAAAAAAAAP//AAAAAAEAAAAAAP//AAAAAAAAAAAAAP//AAD//wAAAAAAAAAAAAAAAAAAAAABAAAAAAAAAAAAAAAAAAEAAAAAAAAA//8AAAAAAAD//wAA/////wAAAAAAAAAAAAD//wEA//8AAP///////wAAAAABAAAAAAD//wAAAAAAAAAAAAD///////8AAAAAAQD//wAA//8AAP//AAD//wAA//8AAP//AQAAAAAA//8AAP//AAAAAAAAAAAAAP//AQAAAAEAAAAAAP//AAD//wEAAAABAAAAAAD//wAA//8AAP//AAD//wAAAAABAAAAAAD/////AAD//wAAAAAAAAAAAAABAAAAAAAAAAAAAAAAAP//AAD//wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAEAAAAAAAAAAQAAAAEAAAAAAAAAAAAAAAEAAAABAAAAAAAAAAEAAAABAAAAAAAAAAAAAAAAAAAAAAAAAAEAAAABAAAAAQAAAAEAAAABAAAAAQAAAAEAAAABAAAAAQAAAAEAAAABAAAAAQAAAAEAAAABAAAAAAABAAEAAAABAP//AAD//wAAAAABAP//AQD//wEAAAABAP//AAD//wAAAAABAAAAAAAAAAAAAAAAAAAAAAAAAP//AAAAAAAAAAAAAAAAAQD//wAAAAD//wAA//8AAAAA//8AAP//AAD//wAA//8AAAAA//////////8AAAAAAQD//wAA//8AAAAAAAAAAAAA//8AAAAAAAAAAAAAAAAAAP//AAAAAAAA//8AAP//AAD//wAA//8AAP//AAAAAAAAAAAAAAAAAAAAAP//AAD/////AAAAAAAA//8AAP//AAD//wAA//8AAP//AAAAAAAAAAAAAP//AAAAAAAAAAAAAP//AAD//wAAAAABAP//AAD//wAAAAABAAAAAQAAAAAAAAAAAAAAAQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA//8AAP//AAAAAAAAAAABAAAAAQD//wEA//8BAAAAAAAAAAAAAAABAP//AQAAAAAAAAAAAAAAAAAAAAAA//8AAP//AAAAAAAAAAABAAAAAAAAAAAAAAAAAP//AAD//wEAAAABAAAAAQAAAAAAAAABAAAAAQD//wEA//8BAAAAAQAAAAEA//8AAAAAAAAAAAAAAAAAAAAAAAAAAAEAAQABAAAAAAABAAAAAQAAAAAAAAAAAAAAAAAAAAAA//8AAAAAAAABAAAAAAAAAP//AAAAAAAAAAAAAP//AAD//wEAAAABAAAAAAAAAAAAAAAAAAAAAQD//wEA//8AAP//AAD//wEAAAAAAP//AAD//wAAAAAAAP//AAD//wAA//8AAAAAAAAAAAAAAAABAAAAAQD//wAA//8AAP//AAAAAAAA//8AAP//AAAAAAAAAAAAAP//AAD/////AAAAAAAAAQAAAAEAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAEAAAABAAAAAAAAAAAAAAD//wAAAAAAAAAAAAAAAAAAAAAAAP//AAAAAAAAAAAAAAAAAAAAAAAAAAABAAAAAAAAAAAAAAABAAAAAAAAAAAAAAABAAAAAQD//wAAAAAAAAAAAQAAAAAAAAAAAAAAAQABAAAAAAAAAAAAAQAAAAAA//8AAAAAAAAAAAAAAAAAAAAAAQAAAAAAAAAAAAEAAAABAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAEAAAABAAAAAAAAAAAAAAAAAAAAAQAAAAEAAAAAAAAAAAAAAAAAAAAAAP//AAD//wEAAAAAAP//AAAAAAAAAAAAAAAAAAD//wAA//8BAAAAAAAAAP//AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP//AAD//wAAAAAAAP//AAD//wEA//8AAP///////wAAAAAAAAAAAAAAAAAAAAAAAAAA/////wAA//8AAP//AAD//wAA//8AAP//AAAAAP//AAAAAAAAAAAAAAAA//8AAP//AAD//wAA////////AAD//wAAAAAAAAAA//8AAP//AAD//wAA//8AAP//AAAAAAAAAAAAAAAA//8AAAAAAAAAAP///////wAA//8AAP//AAAAAAAAAAAAAP//AQD//wAAAAAAAAAAAQAAAAEA//8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAQABAAEAAAAAAAAAAAAAAAEAAAABAAAAAAAAAAEAAQABAAEAAAAAAAAAAQAAAAEAAAAAAAEAAAABAAAAAAAAAAAAAAAAAAEAAQABAAAAAQAAAAAAAQABAAEAAQAAAAEAAAABAAEAAAABAAAAAAAAAAAAAAAAAP//AAAAAAEAAQABAAEAAAABAAAAAQABAAEAAAAAAAEAAAABAAAAAAABAAAAAQAAAAAAAAAAAAAAAQAAAAEAAAABAP//AQAAAAAAAAAAAAAAAAAAAAAAAAAAAP//AAAAAAAAAAAAAAAAAAD//wAAAAAAAAAA/////wAA//8AAAAAAAAAAAAA//8AAP//AAD//wAA//8AAP//AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD//////////wAAAAAAAAAAAAAAAP//AQAAAAAAAAAAAP//AAD//wAAAAAAAP//AAD//wAAAAAAAAAA//8AAAAAAAD//wAA/////wAA//8AAAAAAAAAAAAAAAAAAAAA//8AAP//AAAAAAEAAAABAAAAAQAAAAEAAAAAAAAAAAAAAAAAAAABAAAAAAAAAAAAAAAAAP//AAD//wAAAAAAAAAAAQD//wEAAAABAAAAAQD//wAAAAAAAAAAAQAAAAEAAAABAAAAAQAAAAEAAAAAAAAAAAAAAAAAAAAAAP//AAAAAAAAAAAAAAAAAAAAAAAAAAABAP//AAAAAAAAAAAAAP//AAAAAAAAAAAAAAEAAAAAAAAAAQAAAAEAAAAAAAAAAAAAAAEAAAAAAP////8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAQD//wEAAAD//wAA/////wAAAAAAAAAAAAD//wAAAAABAAAAAAD//wAAAAABAAAAAQD//wEA//8BAAAAAQAAAAAAAAABAAAAAQAAAAAAAAAAAAAAAQAAAAEA//8AAP//AQAAAAEAAAAAAP//AAAAAAEAAAABAP//AQD//wAAAAAAAAAAAQAAAAEAAAAAAAAAAQAAAAEAAAABAP//AAD//wEAAAABAAAAAQD//wAA//8AAP//AQAAAAEAAAABAAAAAAAAAAAAAAAAAAAAAAD//wAAAAABAAEAAAABAAAAAQAAAAEAAAABAP//AAAAAAAAAAABAAAAAAAAAAAAAAABAAAAAQAAAAAA//8AAAAAAQAAAAEAAAABAAAAAQAAAAAAAAAAAAEAAQAAAAAA//8AAAAAAQAAAAAA//8AAP//AQAAAAEA//8AAAAAAQAAAAEA//8AAAAAAAAAAAEA//8BAP//AAAAAAEAAAABAP//AAD//wEA//8BAP//AAD//wAA//8AAP//AAAAAAAAAAAAAP//AQD//wAAAAAAAAAAAAD//wAA//8AAP//AQD//wAA//8AAAAAAAAAAAAAAAAAAAAAAAAAAAAA//8AAAAAAAAAAAAA/////wAA//8AAAAAAAAAAAAA//8AAP//AAAAAAEAAAABAAAAAAAAAAAA//8AAP//AAD//wEAAAAAAAAAAAD//wEA//8BAP//AAD//wEAAAABAAAAAAD//wEA//8BAAAAAAD//wAA//8AAAAAAAAAAAAAAAABAAAAAQAAAAEA//8BAP//AAD//wAA//8AAAAAAAD//wEA//8BAAAAAAAAAAAA//8BAAAAAAD//wAA//8AAAAAAAAAAAAA//8AAAAAAQAAAAAA//8AAP//AAAAAAAAAAAAAAAA/////wAA//8BAAAAAAAAAAAAAAAAAAEAAQAAAAEAAAAAAAAAAAABAP//AAD//wEAAAABAAAAAAAAAAAAAAABAAAAAQAAAAAAAAAAAAAAAQAAAAAAAAABAAAAAQAAAAEA//8AAP//AAAAAAEAAAABAAAAAQD//wEAAAABAAAAAQAAAAEAAAAAAAAAAAAAAAEA//8AAAAAAAAAAAEAAAAAAP//AAAAAAEAAAABAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABAAAAAAAAAAAAAAABAAAAAQAAAAAAAAABAAEAAQAAAAAAAAD//wAAAAAAAAAAAAAAAAAAAAAAAP//AAAAAAAAAAABAP//AQD//wAAAAAAAAAAAAAAAAAA//8AAAAAAAAAAAAAAAAAAAAAAQAAAAEAAAABAAAAAAAAAAAA//8AAAAAAAAAAAEAAAABAAAAAAAAAAAAAAABAP//AAD//wAAAAAAAAAAAQAAAAAAAAAAAAAAAAD//wAA//8BAP//AQAAAAAAAAAAAP//AAAAAAEAAAABAP//AAD//wAAAAAAAAAAAAD//////////wAAAAAAAP//AAD//wAAAAAAAP///////wAA//8AAP//AAD//wAA//8AAP//AQD//wAA//8AAP//AAD//wAA//8BAP//AAD//wAA//8AAP//AQD//wAAAAAAAAAAAQD//wEA//8AAP//AAD//wAA//8BAP//AQAAAAAA//8AAP//AQD//wEAAAABAP//AQD//wEAAAABAAAAAQAAAAEA//8BAP//AAAAAAAAAAABAAAAAgAAAAEA//8BAP//AQAAAAEAAAABAAAAAAABAAAAAAABAAAAAQAAAAAAAAABAAAAAAAAAAAAAAAAAAEAAQABAAAAAAAAAAAAAAAAAAAAAQAAAAAAAAAAAAAAAQAAAAAAAAAAAAAAAQAAAAEA//8AAP//AAAAAAEAAAAAAAAAAAAAAAAAAAAAAP//AQD//wEAAAAAAAAAAQD//wEAAAAAAAAAAAD//wEA//8BAAAAAAD//wAA//8AAAAAAQAAAAAAAAAAAAAAAQAAAAEAAAABAAAAAQAAAAAAAAAAAP//AAAAAAEAAAAAAAAAAAD//wAA//8AAAAAAAAAAAEA//8BAP//AAAAAAAAAAABAP//AQAAAAEAAAAAAP//AAD//wAAAAAAAAAAAQAAAAEAAAAAAAAAAAAAAAAAAAAAAAAA//8AAP////8AAAAAAAAAAAAAAAAAAAAAAAABAAAAAAD//wAAAAAAAAAAAAAAAAAA//8AAP//AAAAAAEAAAABAP//AAD//wAAAAAAAAAAAQD//wEA//8AAAAAAAD//wEA//8BAP//AQAAAAAAAAABAAAAAQD//wAAAAAAAAAAAQAAAAEA//8AAP//AQD//wEA//8AAAAAAAD//wEA//8AAP//AAAAAAEAAAABAP//AAAAAAEAAAAAAP//AAD//wAAAAAAAAAAAAAAAAAAAAAAAAAAAQAAAAAAAAAAAAEAAAABAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP//AQAAAAEAAAABAP//AAD//wAA//8AAAAAAAAAAAAA//8BAAAAAQAAAAAAAAAAAAAAAQD//wEA//8AAAAAAAAAAAAAAAABAAAAAQAAAAAA//8AAP//AAD//wAA//8AAP//AAAAAAEA//8AAAAAAQAAAAEAAQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA//8AAAAAAQAAAAEAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP//AAD//wAAAAAAAAAAAAAAAAAAAAAAAAAAAAD///////8AAAAAAAAAAAAA//8AAP//AAAAAAAAAAAAAP//AAD/////AAD//wAA//8AAAAAAAAAAAAAAAD//wAA/////wAAAAAAAAAA//8AAP//AAD//wAA//8AAP//AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=");
////
var config = {
    type: Phaser.AUTO,
    width: gameWidth,
    height: gameHeight,
    backgroundColor: 0xdddddd,
    parent: 'phaser-example',
    scene: UIScene,
    scale: {
        mode: Phaser.Scale.FIT,
        parent: 'phaser-example',
        autoCenter: Phaser.Scale.CENTER_BOTH,
        width: gameWidth,
        height: gameHeight
    }
};
////
function activateFullScreenForMobiles() {
    if (gameWidth < gameHeight) {
        if (!isIOSDevice()) {
            document.body.requestFullscreen();
        }
    }
}

document.addEventListener("fullscreenchange", function() {
    if (!document.fullscreenElement) {
        bootbox.confirm({
            size: 'small',
            message: 'Вернуться в полноэкранный режим?',
            locale: 'ru',
            callback: function(result) {
                if(result) {
                    document.body.requestFullscreen();
                }
            }
        });

    }
});

async function mobileShare() {
    thisUrl = window.location.href;
    thisTitle = document.title;
    shareObj = {
        title: thisTitle,
        url: thisUrl,
    }

    try {
        await navigator.share(shareObj);
    } catch (err) {
        console.log('share ERROR! ' + err);
    }
}

function genDivGlobal(i, isChange = false) {
    if (i <= 31) {
        return '<div class="letter_' + i + '"></div>';
    }

    let coords = {
        32: {x: 526, y: 640},
        33: {x: 182, y: 24},
        34: {x: 507, y: 259},
        35: {x: 598, y: 259},
        36: {x: 691, y: 259},
        37: {x: 786, y: 259},
        38: {x: 881, y: 259},
        39: {x: 964, y: 259},
        40: {x: 3, y: 385},
        41: {x: 101, y: 385},
        42: {x: 186, y: 385},
        43: {x: 247, y: 396},
        44: {x: 324, y: 385},
        45: {x: 411, y: 385},
        46: {x: 503, y: 380},
        47: {x: 612, y: 385},
        48: {x: 714, y: 385},
        49: {x: 812, y: 385},
        50: {x: 908, y: 384},
        51: {x: 1001, y: 385},
        52: {x: 1080, y: 385},
        53: {x: 1, y: 507},
        54: {x: 92, y: 507},
        55: {x: 186, y: 507},
        56: {x: 295, y: 507},
        57: {x: 408, y: 507},
        58: {x: 502, y: 507},
        59: {x: 592, y: 507}
    };
    let koef = 44 / 76;
    let imgWidth = Math.round(1187 * koef);
    let styleBeg = 'display: inline-block; background: url(/img/letters_english.png); background-color:grey;background-position:-';
    let divTpl = '<div onmouseover="this.style.backgroundColor=\'green\';" onmouseout="this.style.backgroundColor=\'grey\';" style="display: inline-block; background: url(/img/letters_english.png); background-color:grey;background-position:-';
    let styleEnd = ' background-size: ' + imgWidth + 'px;'
        + ' width: 44px;'
        + ' height: 54px;'
        + ' border-radius: 5px;';
    let divEnd = ' background-size: ' + imgWidth + 'px;'
        + ' width: 44px;'
        + ' height: 54px;'
        + ' border-radius: 5px;'
        + '"></div>';

    if (i == 53) {
        return (isChange ? styleBeg : divTpl)
            + Math.round(coords[i].x * koef) + 'px -'
            + Math.round(coords[i].y * koef) + 'px; '
            + (isChange ? styleEnd : divEnd);
    } else if (i == 43) {
        return (isChange ? styleBeg : divTpl)
            + Math.round(coords[i]['x'] * koef) + 'px -'
            + Math.round((coords[i]['y'] - 8) * koef) + 'px; '
            + (isChange ? styleEnd : divEnd);
    } else if (i == 46) {
        return (isChange ? styleBeg : divTpl)
            + Math.round(coords[i]['x'] * koef) + 'px -'
            + Math.round((coords[i]['y'] + 6) * koef) + 'px; '
            + (isChange ? styleEnd : divEnd);
    } else if (i == 40) {
        return (isChange ? styleBeg : divTpl)
            + Math.round(coords[i]['x'] * koef) + 'px -'
            + Math.round((coords[i]['y'] - 2) * koef) + 'px; '
            + (isChange ? styleEnd : divEnd);
    } else if (i == 56) {
        return (isChange ? styleBeg : divTpl)
            + Math.round(coords[i]['x'] * koef * 0.9) + 'px -'
            + Math.round((coords[i]['y'] - 2) * koef * 0.9) + 'px;'
            + ' background-size: ' + Math.round(imgWidth * 0.9) + 'px;'
            + ' width: 44px;'
            + ' height: 54px;'
            + ' border-radius: 5px;'
            + (isChange ? '' : '"></div>');
    }

    return ((isChange ? styleBeg : divTpl)
        + Math.round((coords[i]['x'] + 3) * koef) + 'px -'
        + Math.round(coords[i]['y'] * koef) + 'px; '
        + (isChange ? styleEnd : divEnd));

}

function asyncCSS(href) {
    var css = document.createElement('link');
    css.rel = "stylesheet";
    css.href = href;
    document.head.appendChild(css);
}

window.onbeforeunload = function () {
    if (gameState == 'myTurn'
        || gameState == 'preMyTurn'
        || gameState == 'otherTurn'
        || gameState == 'initGame'
        || gameState == 'initRatingGame') {
        fetchGlobal(SET_INACTIVE_SCRIPT, '', '');
        return "Вы в игре - уверены, что хотите выйти?";
    }
};

document.addEventListener("visibilitychange", function () {
    pageActive = document.visibilityState;

    if (gameState == 'myTurn'
        || gameState == 'preMyTurn'
        || gameState == 'otherTurn'
        || gameState == 'initGame'
        || gameState == 'initRatingGame') {
        if (pageActive == 'hidden') {
            fetchGlobal(STATUS_CHECKER_SCRIPT)
                .then((data) => {
                    commonCallback(data);
                });
        }
    }
});

function showFullImage(idImg, width, oldWidth = 198) {
    if ($('#' + idImg).width() < width) {
        if (fullImgID !== false) {
            $('#' + fullImgID).css('z-index', '50');
            $('#' + fullImgID).css('top', '0px');
            $('#' + fullImgID).css('left', '0px');
            $('#' + fullImgID).css('position', 'relative');
            $('#' + fullImgID).width(fullImgWidth);
        }
        fullImgID = idImg;
        fullImgWidth = oldWidth;
        $('#' + idImg).css('position', 'fixed');
        $('#' + idImg).css('top', (Math.round(window.innerHeight - width / 16 * 9) / 3) + 'px');
        $('#' + idImg).css('left', (Math.round(window.innerWidth - width) / 2) + 'px');
        $('#' + idImg).width(width);
        $('#' + idImg).css('z-index', '100');
    } else {
        fullImgID = false;
        fullImgWidth = 0;
        $('#' + idImg).css('z-index', '50');
        $('#' + idImg).css('top', '0px');
        $('#' + idImg).css('left', '0px');
        $('#' + idImg).css('position', 'relative');
        $('#' + idImg).width(oldWidth);
    }
}

function mergeTheIDs(oldKey, commonID) {
    if (oldKey.trim() == '') {
        let resp = {result: 'error', message: 'Задано пустое значение'};
        showCabinetActionResult(resp);

        return;
    }

    fetchGlobal(MERGE_IDS_SCRIPT, '', 'oldKey=' + btoa(oldKey) + '&commonID=' + commonID)
        .then((resp) => {
            showCabinetActionResult(resp);
        });
}

function showCabinetActionResult(response) {
    if ('message' in response) {
        let background = (response['result'].indexOf('error') + 1)
            ? '#f99'
            : '#9f9';
        cabinetAlert = bootbox.alert({
            message: response['message'],
            locale: 'ru',
            size: 'small',
            closeButton: false,
            centerVertical: true
        }).find('.modal-content').css({
            'background-color': background
        });
    }
}

function copyKeyForID(key, commonID = '') {
    $('#key_for_id').select();
    document.execCommand("copy");
}

function copyDonateKey() {
    $('#donate_id').select();
    document.execCommand("copy");
}

function deleteBan(commonID) {
    fetchGlobalMVC(DELETE_BAN_URL + commonID, '', 'commonID=' + commonID)
        .then((resp) => {
            showCabinetActionResult(resp);
        });
}

function savePlayerName(name, commonIdParam = '') {
    if (name.trim() == '') {
        let resp = {result: 'error', message: 'Задано пустое значение'};
        showCabinetActionResult(resp);

        return;
    }

    fetchGlobal(SET_PLAYER_NAME_SCRIPT, '', 'name=' + encodeURIComponent(name) + '&commonID=' + (commonIdParam != '' ? commonIdParam : commonId))
        .then((resp) => {
            if (resp['result'] == 'saved') {
                $('#playersNikname').text(name);
            }
            showCabinetActionResult(resp);
        });
}

function savePlayerAvatar(url, commonIdParam) {
    // складируем форму в ......форму))
    const checkElement = document.getElementById("player_avatar_file");
    if (!checkElement.checkValidity()) {
        showCabinetActionResult({
            result: 'error',
            message: 'Ошибка! Выберите файл-картинку размером не более 2MB'
        });

        return false;
    }

    var formData = new FormData($('#superForm')[0]);

    if (pageActive != 'hidden') {
        requestSended = true;
        requestTimestamp = (new Date()).getTime();
    }

    let URL = useLocalStorage
        ? (
            '/yandex1.0.1.1/php/yowser/index.php'
            + '?cooki='
            + localStorage.erudit_user_session_ID
            + '&script='
            + AVATAR_UPLOAD_SCRIPT
            + '&'
            + commonParams()
        )
        : (
            '/yandex1.0.1.1/php/'
            + AVATAR_UPLOAD_SCRIPT
            + '?'
            + commonParams()
        );

    $.ajax({
        url: URL,
        type: 'POST',
        data: formData,
        async: false,
        cache: false,
        contentType: false,
        processData: false,
        success: function (returndata) {
            resp = JSON.parse(returndata);

            if (resp['result'] === 'saved') {
                //$('#playersAvatar').html('<img src="' + resp['url'] + '" width="100px" max-height = "100px"/>');
                $('#playersAvatar').html('<img class="main-info-image" src="' + resp['url'] + '" alt="" />');
            }

            showCabinetActionResult(resp);

            return false;
        }
    });

    return false;
}

function refreshId(element_id, url) {
    let respMessage = 'Ошибка загрузки статистики';

    $.ajax({
        url: url,
        type: 'GET',
        async: false,
        cache: false,
        contentType: false,
        processData: false,
        success: function (returndata) {
            resp = JSON.parse(returndata);
            $('#' + element_id).html(resp.message + resp.pagination);
        }
    });
}

async function getStatPageGlobal() {
    let urlPart = STATS_URL + commonId + '&lang=' + lang;
    let respMessage = 'Ошибка загрузки статистики';

    if (commonId) {
        try {
            const response = await fetch('/' + urlPart, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                },
                cache: 'no-cache',
            });

            // Проверяем успешность запроса
            if (!response.ok) {
                throw new Error(`Ошибка запроса: ${response.status}`);
            }

            const returndata = await response.json(); // Получаем JSON

            const s = StatsPage({ json: returndata, BASE_URL: '' });
            const message = await s.buildHtml();

            return {
                message,
                onLoad: s.onLoad,
            };
        } catch (error) {
            console.error('Ошибка при загрузке статистики:', error.message);
            return {
                message: respMessage,
            };
        }
    } else {
        return {
            message: 'Для просмотра статистики сыграйте хотя бы одну партию',
        };
    }
}

function savePlayerAvatarUrl(url, commonID) {
    if (url.trim() == '') {
        let resp = {result: 'error', message: 'Задано пустое значение'};
        showCabinetActionResult(resp);

        return;
    }

    fetchGlobal(SET_AVATAR_SCRIPT, '', 'avatar=' + encodeURIComponent(url) + '&commonID=' + commonID)
        .then((resp) => {
            if (resp['result'] == 'saved')
                $('#playersAvatar').html('<img src="' + url + '" width="100px" max-height = "100px"/>');
            showCabinetActionResult(resp);
        });
}

function initLotok() {
    for (var i = 0; i < lotokCapacityY; i++) {
        lotokCells[i] = [];
        for (var j = 0; j < lotokCapacityX; j++)
            lotokCells[i][j] = false;
    }
}

function lotokFindSlotXY() {
    let XY = [];
    outer:
        for (var i = 0; i < lotokCapacityY; i++)
            for (var j = 0; j < lotokCapacityX; j++)
                if (lotokCells[i][j] === false) {
                    XY[0] = j;
                    XY[1] = i;
                    lotokCells[i][j] = true;
                    break outer;
                }
    return XY;
}

function lotokFindSlotReverseXY() {
    let XY = [];
    outer:
        for (var i = lotokCapacityY - 1; i >= 0; i--)
            for (var j = lotokCapacityX - 1; j >= 0; j--)
                if (lotokCells[i][j] === false) {
                    XY[0] = j;
                    XY[1] = i;
                    lotokCells[i][j] = true;
                    break outer;
                }
    return XY;
}

function lotokGetX(X, Y) {
    if (buttonHeightKoef == 1) {
        return lotokX + lotokCellStep * fishkaScale * X;
    } else
        return lotokX + lotokCellStep * X;
}

function lotokGetY(X, Y) {
    return lotokY + lotokCellStepY * Y;
}

function lotokFreeXY(X, Y) {
    lotokCells[Y][X] = false;
}

function placeToLotok(fishka) {
    if (fishka.getData('isTemporary')) {
        var slotXY = lotokFindSlotReverseXY();
    } else {
        var slotXY = lotokFindSlotXY();
    }

    fishka.setData('cellX', false);
    fishka.setData('cellY', false);
    fishka.setData('lotokX', slotXY[0]);
    fishka.setData('lotokY', slotXY[1]);
    fishka.x = lotokGetX(slotXY[0], slotXY[1]);
    fishka.y = lotokGetY(slotXY[0], slotXY[1]);
}

function disableButtons() {
    for (let k in buttons) {
        if (buttons[k]['svgObject'] !== false)
            buttons[k]['svgObject'].disableInteractive();
    }
}

function enableButtons() {
    //if (bootBoxIsOpenedGlobal()) return;
    for (let k in buttons)
        if ('enabled' in buttons[k]) {
            if (gameState in buttons[k]['enabled']) {
                if (buttons[k]['svgObject'] !== false) {
                    buttons[k]['svgObject'].setInteractive();
                    buttons[k]['svgObject']
                        .bringToTop(buttons[k]['svgObject']
                            .getByName(k + OTJAT_MODE));
                }

            } else {
                if (buttons[k]['svgObject'] !== false) {
                    buttons[k]['svgObject'].disableInteractive();
                    buttons[k]['svgObject']
                        .bringToTop(buttons[k]['svgObject']
                            .getByName(k + 'Inactive'));
                }

            }
        } else {
            if (buttons[k]['svgObject'] !== false)
                buttons[k]['svgObject'].setInteractive();
        }
}


function placeFishki(fishki) {
    var maxI = 0;
    for (var i in container) {
        if (i > maxI)
            maxI = i;

        if (container[i].getData('cellX')) {
            cells[container[i].getData('cellX')][container[i].getData('cellY')][0] = false;
            cells[container[i].getData('cellX')][container[i].getData('cellY')][1] = false;
        }

        if ((container[i].getData('lotokX') !== false) && (container[i].getData('lotokY') !== false)) {
            lotokFreeXY(container[i].getData('lotokX'), container[i].getData('lotokY'));
            container[i].setData('lotokX', false);
            container[i].setData('lotokY', false);
        }

        container[i].destroy();
        //container.splice(i,1);
    }

    for (let i = maxI; i >= 0; i--)
        if (i in container)
            container.splice(i, 1);

    for (let i = 0; i < fishki.length; i++) {
        let lotokXY = lotokFindSlotXY();

        container.push(getFishkaGlobal(fishki[i], lotokGetX(lotokXY[0], lotokXY[1]), lotokGetY(lotokXY[0], lotokXY[1]), this.game.scene.scenes[gameScene], true, userFishkaSet).setData('lotokX', lotokXY[0]).setData('lotokY', lotokXY[1]));
    }
}

////
function getFishkaGlobal(numLetter, X, Y, _this, draggable = true, fishkaSet = DEFAULT_FISHKA_SET) {
    if (fishkaSet != DEFAULT_FISHKA_SET) {
        console.log('Not default');
        console.log(numLetter);
        if (fishkaSet in fishkiLoaded && numLetter in fishkiLoaded[fishkaSet]) {
            let fishka = _this.add.image(0, 0, fishkiLoaded[fishkaSet][numLetter]);
            fishka.displayWidth = FISHKA_AVAILABLE_SETS[fishkaSet] * 2;
            fishka.displayHeight = FISHKA_AVAILABLE_SETS[fishkaSet] * 2;
            var container = _this.add.container(X, Y, [fishka]);
            container.setSize(fishka.displayWidth, fishka.displayHeight);
            container.setData('letter', numLetter);
            container.setData('cellX', false);
            container.setData('cellY', false);
            container.setInteractive();
            if (draggable) {
                _this.input.setDraggable(container);
                if (fishkaScale > 1) {
                    container.setScale(fishkaScale);
                }
            }

            return container;
        } else {
            if (!(fishkaSet in fishkiLoaded)) {
                loadFishkiSet(fishkaSet);
            } else {
                console.log(fishkaSet + " is NOT loaded yet");
            }
        }
    }

    let fishka = _this.add.image(0, 0, 'fishka_empty');
    fishka.displayWidth = 32 * 2;
    fishka.displayHeight = 32 * 2;
    const correction = 1.5;
    const correctionLetter = (numLetter % 1000 <= 33
        ? correction
        : correction * 1.05);

    if (numLetter == 999) {
        let starLetter = _this.add.image(0, 0, 'zvezdaCenter');
        starLetter.setOrigin(.5, .5);
        var container = _this.add.container(X, Y, [fishka, starLetter]);
    } else if (numLetter > 999) {
        let atlasTexture = _this.textures.get('megaset');
        let frames = atlasTexture.getFrameNames();

        let atlasTextureEnglish = _this.textures.get('megaset_english');
        let framesEnglish = atlasTextureEnglish.getFrameNames();

        let starLetter = _this.add.image(0, 0, 'zvezdaVerh');
        starLetter.setOrigin(.5, .5);
        starLetter.x = fishka.displayWidth - 13 * 2 - starLetter.displayWidth;
        starLetter.y = fishka.displayHeight - 27 * 2 - starLetter.displayHeight;

        let testLetter = (numLetter <= 1033
            ? _this.add.image(0, 0, 'megaset', frames[numLetter - 999 - 1])
            : _this.add.image(0, 0, 'megaset_english', framesEnglish[numLetter - 999 - 1]));

        testLetter.displayWidth = fishka.displayWidth / correctionLetter;
        testLetter.scaleY = testLetter.scaleX;
        testLetter.setOrigin(.5, .5);
        testLetter.x = fishka.displayWidth - 15 * 2 - testLetter.displayWidth;
        testLetter.y = fishka.displayHeight - 3 * 2 - testLetter.displayHeight;

        var container = _this.add.container(X, Y, [fishka, testLetter, starLetter]);
    } else {
        let atlasTexture = _this.textures.get('megaset');
        let frames = atlasTexture.getFrameNames();

        let atlasTextureEnglish = _this.textures.get('megaset_english');
        let framesEnglish = atlasTextureEnglish.getFrameNames();

        let testLetter = (numLetter <= 33
            ? _this.add.image(0, 0, 'megaset', frames[numLetter])
            : _this.add.image(0, 0, 'megaset_english', framesEnglish[numLetter]));

        testLetter.displayWidth = fishka.displayWidth / correctionLetter;
        testLetter.scaleY = testLetter.scaleX;
        testLetter.setOrigin(.5, .5);
        testLetter.x = fishka.displayWidth - 15 * 2 - testLetter.displayWidth;
        testLetter.y = fishka.displayHeight - 3 * 2 - testLetter.displayHeight;

        if ((numLetter !== 25) && (numLetter !== 26) && (numLetter !== 50) && (numLetter !== 59)) {
            let digitLetter = _this.add.image(0, 0, 'digits', letterPrices.get(numLetter));

            digitLetter.displayWidth = fishka.displayWidth / correction / 2;
            digitLetter.scaleY = digitLetter.scaleX;
            digitLetter.setOrigin(.5, .5);
            digitLetter.x = fishka.displayWidth - 13 * 2 - digitLetter.displayWidth;
            digitLetter.y = fishka.displayHeight - 11 * 2 - digitLetter.displayHeight;

            var container = _this.add.container(X, Y, [fishka, testLetter, digitLetter]);
        } else {
            let digit1Letter = _this.add.image(0, 0, 'digits', 1);
            let digit2Letter = _this.add.image(0, 0, 'digits', letterPrices.get(numLetter) - 10);

            digit1Letter.displayWidth = fishka.displayWidth / correction / 2;
            digit1Letter.scaleY = digit1Letter.scaleX;
            digit1Letter.setOrigin(.5, .5);
            digit1Letter.x = fishka.displayWidth - 18 * 2 - digit1Letter.displayWidth;
            digit1Letter.y = fishka.displayHeight - 27 * 2 - digit1Letter.displayHeight;

            digit2Letter.displayWidth = fishka.displayWidth / correction / 2;
            digit2Letter.scaleY = digit1Letter.scaleX;
            digit2Letter.setOrigin(.5, .5);
            digit2Letter.x = fishka.displayWidth - 13 * 2 - digit1Letter.displayWidth;
            digit2Letter.y = fishka.displayHeight - 27 * 2 - digit1Letter.displayHeight;

            var container = _this.add.container(X, Y, [fishka, testLetter, digit1Letter, digit2Letter]);
        }
    }

    container.setSize(fishka.displayWidth, fishka.displayHeight);
    container.setData('letter', numLetter);
    container.setData('cellX', false);
    container.setData('cellY', false);
    container.setInteractive();
    if (draggable) {
        _this.input.setDraggable(container);
        if (fishkaScale > 1) {
            container.setScale(fishkaScale);
        }
    }

    return container;
}

async function loadFishkiSet(fishkaSet) {
    fishkiLoaded[fishkaSet] = [];

    console.log(lang);

    CODES[lang].forEach(function (numLetter) {
        imgName = fishkaSet + numLetter;
        preloaderObject.load.svg(imgName, '/img/fishki_sets/' + fishkaSet + '/' + numLetter + '.svg');
        if (numLetter != 999) {
            let numfishka = numLetter + 999 + 1;
            imgName = fishkaSet + numfishka;
            preloaderObject.load.svg(imgName, '/img/fishki_sets/' + fishkaSet + '/' + numfishka + '.svg');
        }
    });

    preloaderObject.load.start();
    preloaderObject.load.on('complete', function () {
        CODES[lang].forEach(function (numLetter) {
            imgName = fishkaSet + numLetter;
            fishkiLoaded[fishkaSet][numLetter] = imgName;
            if (numLetter != 999) {
                let numfishka = numLetter + 999 + 1;
                imgName = fishkaSet + numfishka;
                fishkiLoaded[fishkaSet][numfishka] = imgName;
            }
        });
    });
}////
async function fetchGlobal(script, param_name = '', param_data = '') {
    if (pageActive == 'hidden' && gameState == 'chooseGame' && script === STATUS_CHECKER_SCRIPT) {
        return {message: "Выберите параметры игры", http_status: BAD_REQUEST, status: "error"};
    }

    if (!requestToServerEnabled && script === STATUS_CHECKER_SCRIPT) {
        return {message: "Ошибка связи с сервером. Пожалуйста, повторите", http_status: BAD_REQUEST, status: "error"};
    }

    if (script === SUBMIT_SCRIPT) {
        isSubmitResponseAwaining = true;
    }

    if (!commonId && script === STATUS_CHECKER_SCRIPT && isTgBot()) {
        param_name = '';
        param_data = 'tg_authorize=true&' + TG.initData;
    }

    requestToServerEnabled = false;
    requestToServerEnabledTimeout = setTimeout(
        function () {
            requestToServerEnabled = true;
            isSubmitResponseAwaining = false;
        },
        isSubmitResponseAwaining
            ? 1000
            : 500
    )

    if (pageActive != 'hidden') {
        requestSended = true;
        requestTimestamp = (new Date()).getTime();
    }

    if (useLocalStorage) {
        return await fetchGlobalYowser(script, param_name, param_data);
    } else {
        return await fetchGlobalNominal(script, param_name, param_data);
    }
}

async function fetchGlobalMVC(urlPart, param_name, param_data) {
    const response = await fetch(
        '/' + urlPart,
        {
            method: 'POST', // *GET, POST, PUT, DELETE, etc.
            mode: 'cors', // no-cors, *cors, same-origin
            cache: 'no-cache', // *default, no-cache, reload, force-cache, only-if-cached
            credentials: 'include', // include, *same-origin, omit
            headers: {
                //'Content-Type': 'application/json'
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: (param_name !== '' ? param_name + '=' + encodeURIComponent(JSON.stringify(param_data)) : param_data)
        }
    );

    requestSended = false;

    if (response.status === BAD_REQUEST || response.status === PAGE_NOT_FOUND) {
        return {message: response.statusText, status: "error", http_status: response.status};
    }

    if (!response.ok) {
        console.log(`An error has occured: ${response.status}`);
        return {message: "Ошибка связи с сервером. Попробуйте еще раз...", status: "error"};
    }

    return await response.json(); // parses JSON response into native JavaScript objects
}

function commonParams() {
    return 'queryNumber='
        + (queryNumber++)
        + '&lang='
        + lang
        + '&gameNumber='
        + (gameNumber ? gameNumber : 0)
        + '&gameState='
        + gameState
        + (pageActive == 'hidden' ? '&page_hidden=true' : '')
        + ('hash' in webAppInitDataUnsafe ? ('&tg_hash=' + webAppInitDataUnsafe.hash) : '')
        + ('user' in webAppInitDataUnsafe && 'id' in webAppInitDataUnsafe.user ? ('&tg_id=' + webAppInitDataUnsafe.user.id) : '') ;
}

async function fetchGlobalNominal(script, param_name, param_data) {
    const response = await fetch('/yandex1.0.1.1/php/'
        + script
        + '?'
        + commonParams(),
        {
            method: 'POST', // *GET, POST, PUT, DELETE, etc.
            mode: 'cors', // no-cors, *cors, same-origin
            cache: 'no-cache', // *default, no-cache, reload, force-cache, only-if-cached
            credentials: 'include', // include, *same-origin, omit
            headers: {
                //'Content-Type': 'application/json'
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            //redirect: 'follow', // manual, *follow, error
            //referrerPolicy: 'no-referrer', // no-referrer, *client
            body: (param_name != '' ? param_name + '=' + encodeURIComponent(JSON.stringify(param_data)) : param_data) //JSON.stringify(data) // body data type must match "Content-Type" header
        }
    );

    requestSended = false;

    if (response.status === BAD_REQUEST || response.status === PAGE_NOT_FOUND) {
        return {message: response.statusText, status: "error", http_status: response.status};
    }

    if (!response.ok) {
        console.log(`An error has occured: ${response.status}`);
        return {message: "Ошибка связи с сервером. Попробуйте еще раз...", status: "error"};
    }

    return await response.json(); // parses JSON response into native JavaScript objects
}

async function fetchGlobalYowser(script, param_name, param_data) {
    const response = await fetch('/yandex1.0.1.1/php/yowser/index.php'
        + '?cooki='
        + localStorage.erudit_user_session_ID
        + '&script='
        + script
        + '&'
        + commonParams(),
        {
            method: 'POST',
            mode: 'cors',
            cache: 'no-cache',
            credentials: 'include',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: (param_name != ''
                ? param_name + '=' + encodeURIComponent(JSON.stringify(param_data))
                : param_data)
        }
    );

    requestSended = false;

    if (response.status === BAD_REQUEST || response.status === PAGE_NOT_FOUND) {
        return {message: response.statusText, status: "error", http_status: response.status};
    }

    if (!response.ok) {
        console.log(`An error has occured: ${response.status}`);
        return {message: "Ошибка связи с сервером. Попробуйте еще раз...", status: "error"};
    }

    return await response.json();
}////
function parseDeskGlobal(newDesc) {


    newCells = newDesc;
    for (var i = 0; i <= 14; i++)
        for (var j = 0; j <= 14; j++) {
            cells[i][j][0] = false;//newCells[i][j][0];
            cells[i][j][1] = newCells[i][j][1];
            cells[i][j][2] = false;//newCells[i][j][2];
            cells[i][j][3] = newCells[i][j][3];
        }

    for (var k = 400; k >= 0; k--)
        if (k in fixedContainer) {
            fixedContainer[k].destroy();
            fixedContainer.splice(k, 1);
        }

    for (var i = 0; i <= 14; i++)
        for (var j = 0; j <= 14; j++)
            if (cells[i][j][1] !== false) {
                var fixFishka = getFishkaGlobal(cells[i][j][1], 1, 1, this.game.scene.scenes[gameScene], false, cells[i][j][3] ?? userFishkaSet).disableInteractive();
                findPlaceGlobal(fixFishka, 1, 1, i, j);
                fixFishka.setData('cellX', i);
                fixFishka.setData('cellY', j);
                fixedContainer.push(fixFishka);
            }
}
////
function initCellsGlobal() {
    var n = 15;
    for (var i = 0; i < n; i++) {
        cells[i] = [];
        for (var j = 0; j < n; j++) {
            cells[i][j] = [false, false, false, userFishkaSet];
        }
    }
}////
function checkZvezdaGlobal(gameObject) {
    if (gameObject.getData('letter') > '999') {

        switchFishkaGlobal(999, gameObject);
    }
}

function findPlaceGlobal(gameObject, oldX, oldY, cellX, cellY) {
    if ((cells[cellX][cellY][0] === false) && (oldX == 1) && (oldY == 1)) {
        oldX = 1;
    } else {
        mk = false;
        var n = 15;
        minQad = 100000;
        for (var i = 0; i < n; i++) {
            for (var j = 0; j < n; j++) {
                k = containerFishkaPresent(i, j);

                if (cells[i][j][0] !== false && (k !== false) && ((((i + 1) * yacheikaWidth + stepX + correctionX - oldX) ** 2 + ((j + 1) * yacheikaWidth + stepY + correctionY - oldY) ** 2) < minQad)) {
                    mk = k;
                    cellX = i;
                    cellY = j;
                    minQad = ((i + 1) * yacheikaWidth + stepX + correctionX - oldX) ** 2 + ((j + 1) * yacheikaWidth + stepY + correctionY - oldY) ** 2;
                } else if (cells[i][j][0] !== false && cells[i][j][2] === false && fixedZvezdaPresent(i, j, gameObject.getData('letter')) && ((((i + 1) * yacheikaWidth + stepX + correctionX - oldX) ** 2 + ((j + 1) * yacheikaWidth + stepY + correctionY - oldY) ** 2) < minQad)) {
                    cellX = i;
                    cellY = j;
                    minQad = ((i + 1) * yacheikaWidth + stepX + correctionX - oldX) ** 2 + ((j + 1) * yacheikaWidth + stepY + correctionY - oldY) ** 2;
                } else if (cells[i][j][0] === false && ((((i + 1) * yacheikaWidth + stepX + correctionX - oldX) ** 2 + ((j + 1) * yacheikaWidth + stepY + correctionY - oldY) ** 2) < minQad)) {
                    cellX = i;
                    cellY = j;
                    minQad = ((i + 1) * yacheikaWidth + stepX + correctionX - oldX) ** 2 + ((j + 1) * yacheikaWidth + stepY + correctionY - oldY) ** 2;
                }
            }
        }
    }

    if (cells[cellX][cellY][0] === false) {
        gameObject.x = stepX + (cellX + 1) * yacheikaWidth + correctionX;
        gameObject.y = stepY + (cellY + 1) * yacheikaWidth + correctionY;

        console.log('Found place: y ' + gameObject.y + ' StepY ' + stepY + ' cellY ' + cellY + ' correctionY ' + correctionY + ' yacheikaWidth ' + yacheikaWidth);

        gameObject.setData('cellX', cellX);
        gameObject.setData('cellY', cellY);
        cells[cellX][cellY][0] = true;
        cells[cellX][cellY][3] = userFishkaSet;

        if ((gameObject.getData('letter') >= '999') && (gameObject.x !== oldX || gameObject.y !== oldY) && !((oldX === 1) && (oldY === 1)))
            chooseLetterGlobal(gameObject);
        else {
            cells[cellX][cellY][1] = gameObject.getData('letter');
        }
    } else if (fixedZvezdaPresent(cellX, cellY, gameObject.getData('letter'))) {
        var _this = window.game.scene.scenes[gameScene];

        var newLetter = getFishkaGlobal(999, 300, 300, _this, true, userFishkaSet);
        newLetter.setData('lotokX', false);
        newLetter.setData('lotokY', false);
        newLetter.setData('isTemporary', true);
        newLetter.setData('zvezdaFrom', gameObject.getData('letter'));
        container.push(newLetter);
        placeToLotok(newLetter);
        gameObject.disableInteractive();

        cells[cellX][cellY][2] = gameObject.getData('letter');
        //cells[cellX][cellY][3] = userFishkaSet;

        gameObject.x = stepX + (cellX + 1) * yacheikaWidth + correctionX;
        gameObject.y = stepY + (cellY + 1) * yacheikaWidth + correctionY;
        gameObject.setData('cellXY', cellX + '-' + cellY);

    } else if ((mk !== false) && (container[mk].getData('cellX') === cellX) && (container[mk].getData('cellY') === cellY)) {
        if (gameObject.getData('oldCellX') !== false) {
            findPlaceGlobal(container[mk], 1, 1, gameObject.getData('oldCellX'), gameObject.getData('oldCellY'));
        } else {
            let slotXY = lotokFindSlotXY();
            container[mk].setData('cellX', false);
            container[mk].setData('cellY', false);
            container[mk].setData('lotokX', slotXY[0]);
            container[mk].setData('lotokY', slotXY[1]);
            container[mk].x = lotokGetX(slotXY[0], slotXY[1]);
            container[mk].y = lotokGetY(slotXY[0], slotXY[1]);
        }

        gameObject.x = stepX + (cellX + 1) * yacheikaWidth + correctionX;
        gameObject.y = stepY + (cellY + 1) * yacheikaWidth + correctionY;
        gameObject.setData('cellX', cellX);
        gameObject.setData('cellY', cellY);
        cells[cellX][cellY][0] = true;

        if ((gameObject.getData('letter') >= '999') && (gameObject.x !== oldX || gameObject.y !== oldY) && !((oldX === 1) && (oldY === 1)))
            chooseLetterGlobal(gameObject);
        else {
            cells[cellX][cellY][1] = gameObject.getData('letter');
        }
    }
}

function fixedZvezdaPresent(i, j, letter) {
    for (k in fixedContainer)
        if ((fixedContainer[k].getData('cellX') === i) && (fixedContainer[k].getData('cellY') === j) && ((fixedContainer[k].getData('letter') - 1 - 999) === (letter)))
            return true;

    return false;
}

function containerFishkaPresent(i, j) {
    for (let k in container)
        if ((container[k].getData('cellX') === i) && (container[k].getData('cellY') === j))
            return k;

    return false;
}

function chooseLetterGlobal(gameObject) {
    if (gameObject.getData('cellX') === false) return;
    if (gameObject.getData('cellY') === false) return;
    disableButtons();
    chooseFishka = gameObject;
    var bukvy = '';
    var buttons1 = {};

    if (lang == 'RU') {
        firstLetterCode = 0;
        lastLetterCode = 31;
    } else {
        firstLetterCode = 34;
        lastLetterCode = 59;
    }

    for (let i = firstLetterCode; i <= lastLetterCode; i++) {

        bukvy = genDivGlobal(i);
        buttons1[i] = {
            label: bukvy,
            className: lang == 'EN' ? 'button1' : 'button1',
            callback: function () {
                switchFishkaGlobal(i + 1 + 999, gameObject);
                chooseFishka = false;

                return;
            }
        }
    }
    dialog = bootbox.dialog({
        message: "Выберите букву",
        size: 'large',
        buttons: buttons1,
        closeButton: false
    });

    enableButtons();
}

function switchFishkaGlobal(letterNum, gameObject) {
    const _this = window.game.scene.scenes[gameScene];
    let newLetter = getFishkaGlobal(letterNum, gameObject.x, gameObject.y, _this, true, userFishkaSet);
    newLetter.setData('lotokX', false);
    newLetter.setData('lotokY', false);
    newLetter.setData('cellX', gameObject.getData('cellX'));
    newLetter.setData('cellY', gameObject.getData('cellY'));

    if (gameObject.getData('isTemporary') == true) {
        newLetter.setData('isTemporary', true);
        newLetter.setData('zvezdaFrom', gameObject.getData('zvezdaFrom'));
        if(newLetter.getData('cellX') !== false) {
            cells[0 + newLetter.getData('cellX')][0 + newLetter.getData('cellY')][2] = newLetter.getData('zvezdaFrom');
        }
    }

    if(newLetter.getData('cellX') !== false) {
        cells[0 + newLetter.getData('cellX')][0 + newLetter.getData('cellY')][1] = newLetter.getData('letter');
        cells[0 + newLetter.getData('cellX')][0 + newLetter.getData('cellY')][3] = userFishkaSet;
    }


    for (let k in container)
        if (container[k] == gameObject) {
            gameObject.destroy();
            container.splice(k, 1);
            break;
        }

    container.push(newLetter);
}
////
function changeFishkiGlobal(fishkiRequest) {
    fetchGlobal(CHANGE_FISHKI_SCRIPT,'',fishkiRequest)
            .then((data) => {
                commonCallback(data);
            });
            
    return;
}////
function bootBoxIsOpenedGlobal() {
    if (!canOpenDialog) {
        return true;
    }

    if (dialog) {
        if (dialog[0].clientHeight > 0) {
            return true;
        } else if ('ariaHidden' in dialog[0]) {
            if (dialog[0].ariaHidden !== "true") {
                return true;
            }
        }
    }

    if (dialogTurn) {
        if (dialogTurn[0].clientHeight > 0) {
            return true;
        } else if ('ariaHidden' in dialogTurn[0]) {
            if (dialogTurn[0].ariaHidden !== "true") {
                return true;
            }
        }
    }

    return false;
}////
async function openWindowGlobal(word){
    const response = await fetch('/yandex1.0.1.1/php/word.php?ingame=yes&word='+word, {
    method: 'POST', // *GET, POST, PUT, DELETE, etc.
    mode: 'cors', // no-cors, *cors, same-origin
    cache: 'no-cache', // *default, no-cache, reload, force-cache, only-if-cached
    credentials: 'include', // include, *same-origin, omit
    headers: {
      //'Content-Type': 'application/json'
      'Content-Type': 'application/x-www-form-urlencoded',
    },
    //redirect: 'follow', // manual, *follow, error
    //referrerPolicy: 'no-referrer', // no-referrer, *client
    body: '12=12' //JSON.stringify(data) // body data type must match "Content-Type" header
  });

  return await response.text(); // parses JSON response into native JavaScript objects
}

////
function submitButtonFunction() {
    if (!submitButtonActive()) {
        return;
    }

    if (bootBoxIsOpenedGlobal()) {
        return;
    }

    buttons['submitButton']['svgObject'].disableInteractive();
    buttons['submitButton']['svgObject'].bringToTop(buttons['submitButton']['svgObject'].getByName('submitButton' + 'Inactive'));

    setTimeout(function () {
        fetchGlobal(SUBMIT_SCRIPT, 'cells', cells)
            .then((data) => {
                if ('http_status' in data && (data['http_status'] === BAD_REQUEST || data['http_status'] === PAGE_NOT_FOUND)) {
                    buttons['submitButton']['svgObject'].setInteractive();
                    buttons['submitButton']['svgObject'].bringToTop(buttons['submitButton']['svgObject'].getByName('submitButton' + OTJAT_MODE));
                    dialog = bootbox.alert({
                        message: ('message' in data && data['message'] !== '')
                            ? (data['message'] + '<br /> Попробуйте отправить заново')
                            : '<strong>Ошибка связи с сервером!<br /> Попробуйте отправить заново</strong>',
                        size: 'small'
                    });
                } else {
                    gameState = 'afterSubmit';
                    parseDeskGlobal(data); // JSON data parsed by `response.json()` call
                }
            });
    }, 100);
}


function checkButtonFunction() {
    if (!checkButtonActive()) {
        return;
    }

    buttons['checkButton']['svgObject'].disableInteractive();
    buttons['checkButton']['svgObject'].bringToTop(buttons['checkButton']['svgObject'].getByName('checkButton' + 'Inactive'));

    setTimeout(function () {
        fetchGlobal(WORD_CHECKER_SCRIPT, 'cells', cells)
            .then((data) => {
                if (data == '')
                    var responseText = 'Вы не составили ни одного слова!';
                else
                    var responseText = data;
                dialog = bootbox.alert({
                    message: responseText,
                    size: 'small'
                });

                buttons['checkButton']['svgObject'].setInteractive();
                buttons['checkButton']['svgObject'].bringToTop(buttons['checkButton']['svgObject'].getByName('checkButton' + OTJAT_MODE));
            });
    }, 100);
};

function shareButtonFunction() {
    if (bootBoxIsOpenedGlobal())
        return;

    dialog = bootbox.alert({
        message: instruction,
        locale: 'ru'
    }).off("shown.bs.modal");
};

function newGameButtonFunction(ignoreDialog = false) {
    if (!ignoreDialog && bootBoxIsOpenedGlobal()) {
        return;
    }

    buttons['newGameButton']['svgObject'].disableInteractive();

    if (gameState == 'myTurn' || gameState == 'preMyTurn' || gameState == 'otherTurn' || gameState == 'startGame') {
        dialog = bootbox.dialog({
            // title: 'Требуется подтверждение',
            message: 'Вы проиграете, если выйдете из игры! ПРОДОЛЖИТЬ?',
            size: 'medium',
            // onEscape: false,
            closeButton: true,
            buttons: {
                cancel: {
                    label: 'Отмена',
                    className: 'btn-outline-success',//''btn btn-success',
                    callback: function () {
                        return true;
                    }
                },
                confirm: {
                    label: 'Подтвердить',
                    className: 'btn-primary',
                    callback: function () {
                        requestToServerEnabled = true;
                        fetchGlobal(NEW_GAME_SCRIPT, '', 'gameState=' + gameState)
                            .then((data) => {
                                document.location.reload(true);
                            });

                        buttons['newGameButton']['svgObject'].setInteractive();

                        return true;
                    }
                },
                invite: {
                    label: 'Реванш!',
                    className: 'btn-info',
                    callback: function () {
                        setTimeout(function () {
                            fetchGlobal(INVITE_SCRIPT, '', 'gameState=' + gameState)
                                .then((dataInvite) => {
                                    let responseText = 'Запрос отклонен';
                                    if (dataInvite != '') {
                                        responseText = dataInvite['message'];
                                    }

                                    dialogResponse = bootbox.alert({
                                        message: responseText,
                                        locale: 'ru',
                                        size: 'small',
                                        callback: function () {
                                            dialogResponse.modal('hide');
                                            gameStates['gameResults']['results'](dataInvite);
                                        }
                                    });

                                    setTimeout(
                                        function () {
                                            dialogResponse.find(".bootbox-close-button").trigger("click");
                                        }
                                        , 2000
                                    );

                                    buttons['newGameButton']['svgObject'].setInteractive();

                                });
                        }, 100);

                        return true;
                    }
                },
            }
        });
        /*
        dialog = bootbox.confirm({
            message: 'Вы проиграете, если выйдете из игры! ПРОДОЛЖИТЬ?',
            locale: 'ru',
            callback: function (result) {
                if (result) {
                    requestToServerEnabled = true;
                    fetchGlobal(NEW_GAME_SCRIPT, '', 'gameState=' + gameState)
                        .then((data) => {
                            document.location.reload(true);
                        });
                } else {
                    buttons['newGameButton']['svgObject'].setInteractive();
                }
            }
        });*/
    } else {
        let lastState = gameState;
        gameState = 'chooseGame';

        buttons['newGameButton']['svgObject'].bringToTop(buttons['newGameButton']['svgObject'].getByName('newGameButton' + 'Inactive'));

        fetchGlobal(NEW_GAME_SCRIPT, '', 'gameState=' + gameState)
            .then((data) => {
                document.location.reload(true);
                setTimeout(function () {
                    gameState = lastState;
                }, 100);

            });
    }
};

function resetButtonFunction(ignoreBootBox = false) {
    if (ignoreBootBox === false)
        if (bootBoxIsOpenedGlobal())
            return;

    for (let k = container.length + 10; k >= 0; k--)
        if (k in container) {
            if ((container[k].getData('lotokX') === false) && (container[k].getData('lotokY') === false)) {

                if ((container[k].getData('cellX') !== false) && (container[k].getData('cellY') !== false)) {
                    cells[container[k].getData('cellX')][container[k].getData('cellY')][0] = false;
                    cells[container[k].getData('cellX')][container[k].getData('cellY')][1] = false;
                    cells[container[k].getData('cellX')][container[k].getData('cellY')][3] = DEFAULT_FISHKA_SET;
                }

                container[k].setData('cellX', false);
                container[k].setData('cellY', false);
                container[k].setInteractive();
                placeToLotok(container[k]);
            }

            if (container[k].getData('isTemporary') === true) {
                for (let i = 0; i <= 14; i++)
                    for (let j = 0; j <= 14; j++)
                        cells[i][j][2] = false;
                container[k].destroy();
                container.splice(k, 1);
            }
        }

};

function changeButtonFunction() {
    if (bootBoxIsOpenedGlobal())
        return;

    canOpenDialog = false;
    canCloseDialog = false;

    let formHeader = '<form id="myForm" class="form-horizontal">';
    let formFooter = '</div></form>';
    var formInner = '<div class="form-group">';
    var zvezdaStyle = '999" title="Зачем?';
    for (let k in container)
        formInner += '<div style="display:inline-block;"><input type="checkbox" style="opacity:80%; transform: scale(2);" id="fishka_'
            +
            k
            +
            '_'
            + container[k].getData('letter')
            + '" name="fishka_'
            + k
            + '_'
            + container[k].getData('letter')
            + '"'
            + (container[k].getData('letter') < 999 ? 'checked' : '')
            + '><label for="fishka_'
            + k
            + '_'
            + container[k].getData('letter')
            + '"><div style="margin-left:-12px;margin-right:13px;' + (container[k].getData('letter') > 33 && container[k].getData('letter') < 999 ? genDivGlobal(container[k].getData('letter'), true) : '')
            + '" class="letter_'
            + (container[k].getData('letter') < 999 ? container[k].getData('letter') : zvezdaStyle)
            + '" onclick="$(\'#fishka_'
            + k
            + '_'
            + container[k].getData('letter')
            + '\').trigger(\'click\');return false;"></div></label></div>';

    dialog = bootbox.confirm({
        message: 'Выберите фишки для замены<br /><br />' + formHeader + formInner + formFooter,
        locale: 'ru',
        callback: function (result) {
            canOpenDialog = true;
            canCloseDialog = true;

            if (result)
                changeFishkiGlobal($(".bootbox-body #myForm").serialize());
        }
    });

};

function chatButtonFunction() {
    if (bootBoxIsOpenedGlobal())
        return;

    canOpenDialog = false;
    canCloseDialog = false;
    let msgSpan = '<span id="msg_span">';
    let message = '<ul style="margin-left:-30px;margin-right:-5px;">' + msgSpan + '</span>';
    let i = 0;
    for (k in chatLog) {
        if (i >= 10) break;
        message = message + '<li>' + chatLog[k] + "</li>";
        i++;
    }


    let noMsgSpan = '<span id="no_msg_span">';
    if (i == 0) {
        message += noMsgSpan + 'Сообщений пока нет' + '</span>';
    } else {
        message += noMsgSpan + '</span>';
    }
    message = message + '</ul>';
    let radioButtons = message + '';

    let isSelectedPlaced = false;
    if (ochki_arr.length > 1) {
        radioButtons += '<div class="form-check form-check-inline"><input class="form-check-input" type="radio" id="chatall" name="chatTo" value="all" checked> <label class="form-check-label" for="chatall">Для всех</label></div>';
        isSelectedPlaced = true;
    }

    for (k in ochki_arr) {
        if (k != myUserNum) {
            radioButtons += '<div class="form-check form-check-inline"><input class="form-check-input" type="radio" id="to_' + (k == 0 ? '0' : k) + '" name="chatTo" value="' + (k == 0 ? '0' : k) + '" ' + (isSelectedPlaced ? '' : ' checked ') + '> <label class="form-check-label" for="to_' + (k == 0 ? '0' : k) + '">Игроку ' + (parseInt(k, 10) + 1) + '</label></div>';
            isSelectedPlaced = true;
        }
    }

    radioButtons += '<div class="form-check form-check-inline"><input class="form-check-input" type="radio" id="to_words" name="chatTo" value="words" ' + (isSelectedPlaced ? '' : ' checked ') + '> <label class="form-check-label" for="to_words">Подбор слов</label></div>';

    let textInput = '<div class="input-group input-group-lg">  <div class="input-group-prepend"></div>  <input type="text" id="chattext" class="form-control" name="messageText"></div>';


    dialog = bootbox.dialog({
        title: '</h5>'
            + (
                !isYandexAppGlobal()
                    ? (
                        '<h6>Поддержка и чат игроков в <a target="_blank" title="Вступить в группу" href="'
                        + (gameWidth < gameHeight ? 'https://t.me/eruditclub' : 'https://web.telegram.org/#/im?p=@eruditclub')
                        + '">Telegram</a> </h6>'
                    )
                    : ''
            )
            + '<h5>Отправьте сообщение в игре',
        message: '<form onsubmit="return false" id="myChatForm">' + radioButtons + textInput + '</form>',
        locale: 'ru',
        size: 'large',
        closeButton: false,
        buttons: {
            confirm: {
                label: 'Отправить',
                className: 'btn-primary',
                callback: function () {
                    canOpenDialog = true;
                    canCloseDialog = true;

                    buttons['chatButton']['svgObject'].bringToTop(buttons['chatButton']['svgObject'].getByName('chatButton' + OTJAT_MODE));
                    buttons['chatButton']['svgObject'].getByName('chatButton' + ALARM_MODE).setData('alarm', false);

                    if ($(".bootbox-body #chattext").val() != '') {

                        buttons['chatButton']['svgObject'].disableInteractive();
                        buttons['chatButton']['svgObject'].bringToTop(buttons['chatButton']['svgObject'].getByName('chatButton' + 'Inactive'));

                        fetchGlobal(CHAT_SCRIPT, '', $(".bootbox-body #myChatForm").serialize())
                            .then((data) => {
                                    if (data == '')
                                        var responseText = 'Ошибка';
                                    else {
                                        var responseText = data['message'];

                                        if (data['message'] === 'Сообщение отправлено') {
                                            $('#no_msg_span').html('');
                                            $('#msg_span').html('<li>' + $('#chattext').val() + '</li>' + $('#msg_span').html());
                                        }

                                        $('#chattext').val('');
                                    }

                                    if (data['message'] !== 'Сообщение отправлено') {
                                        if (data['gameState'] == 'wordQuery') {
                                            $('#no_msg_span').html('');
                                            $('#msg_span').html('<li>' + data['message'] + '</li>');
                                        } else {
                                            dialog2 = bootbox.alert({
                                                message: responseText,
                                                size: 'small'
                                            });
                                            setTimeout(
                                                function () {
                                                    dialog2.find(".bootbox-close-button").trigger("click");
                                                }
                                                , 2000
                                            );
                                        }
                                    }

                                    buttons['chatButton']['svgObject'].setInteractive();
                                    buttons['chatButton']['svgObject'].bringToTop(buttons['chatButton']['svgObject'].getByName('chatButton' + OTJAT_MODE));
                                    buttons['chatButton']['svgObject'].getByName('chatButton' + ALARM_MODE).setData('alarm', false);
                                }
                            );
                    }

                    return false;
                }
            },
            cancel: {
                label: 'Выход',
                className: 'ml-5 btn-secondary btn-default bootbox-cancel',
                callback: function () {
                    canOpenDialog = true;
                    canCloseDialog = true;

                    return true;
                }
            },
            complain: {
                label: 'Пожаловаться',
                className: 'ml-5 ' + (hasIncomingMessages ? 'btn-danger' : 'btn-light'),
                callback: function () {
                    if (hasIncomingMessages) {
                        fetchGlobal(COMPLAIN_SCRIPT, '', $(".bootbox-body #myChatForm").serialize())
                            .then((data) => {
                                if (data == '')
                                    var responseText = 'Ошибка';
                                else
                                    var responseText = data['message'];
                                dialog2 = bootbox.alert({
                                    message: responseText,
                                    size: 'small'
                                });
                                setTimeout(
                                    function () {
                                        dialog2.find(".bootbox-close-button").trigger("click");
                                    }
                                    , 5000
                                );
                            });
                    }

                    return false;
                }
            }
        }
    });
}
;

function logButtonFunction() {
    if (bootBoxIsOpenedGlobal())
        return;

    canOpenDialog = false;
    canCloseDialog = false;

    let message = '<br /><ul style="margin-left:-30px;margin-right:-5px;">';
    let i = 0;
    for (k in gameLog) {
        if (i >= 10) break;
        message = message + '<li>' + gameLog[k] + "</li>";
        i++;
    }
    message = message + '</ul>';
    if (i == 0)
        message = message + 'Событий пока нет';

    notDialog = bootbox.dialog({
        message: message,
        size: 'small',
        onEscape: function () {
            activateFullScreenForMobiles();
            canOpenDialog = true;
            canCloseDialog = true;
        },
        buttons: {
            cancel: {
                label: "Играем до <strong>" + winScore + "</strong>",
                className: 'btn btn-outline-secondary',
                callback: function () {
                    return false;
                }
            },
            confirm: {
                label: "OK",
                className: 'btn-primary',
                callback: function () {
                    activateFullScreenForMobiles();
                    canOpenDialog = true;
                    canCloseDialog = true;
                    return true;
                }
            }
        },
        callback: function (result) {
            canOpenDialog = true;
            canCloseDialog = true;
        }
    })
        .off("shown.bs.modal")
        .find('button.btn.btn.btn-sm.btn-info')
        .prop('disabled', true);

    return;
};

function makeCheckButtonInactive(dialog) {
    dialog.addClass(CHECK_BUTTON_INACTIVE_CLASS);
}

function makeSubmitButtonInactive(dialog) {
    dialog.addClass(SUBMIT_BUTTON_INACTIVE_CLASS);
}

function checkButtonActive() {
    return !$('.' + CHECK_BUTTON_INACTIVE_CLASS).length;
}

function submitButtonActive() {
    return !$('.' + SUBMIT_BUTTON_INACTIVE_CLASS).length;
}

function playersButtonFunction() {
    if (bootBoxIsOpenedGlobal())
        return;

    if (window.innerWidth < window.innerHeight) {
        var orient = 'vertical';
    } else {
        var orient = 'horizontal';
    }

    buttons['playersButton']['svgObject'].disableInteractive();
    buttons['playersButton']['svgObject'].bringToTop(buttons['playersButton']['svgObject'].getByName('playersButton' + 'Inactive'));

    setTimeout(function () {
        fetchGlobal(PLAYER_RATING_SCRIPT, '', orient)
            .then((data) => {

                canOpenDialog = false;
                canCloseDialog = false;

                if (data == '')
                    var responseText = 'Ошибка';
                else
                    var responseText = data['message'];
                dialog = bootbox.alert({
                    title: 'Рейтинг соперников',
                    message: responseText,
                    size: 'large',
                    callback: function () {
                        canOpenDialog = true;
                        canCloseDialog = true;
                    }
                });
                dialog
                    .find('.modal-content').css({'background-color': 'rgba(255, 255, 255, 0.7)'})
                    .find('img').css('background-color', 'rgba(0, 0, 0, 0)');

                makeCheckButtonInactive(dialog);
                makeSubmitButtonInactive(dialog);
                
                buttons['playersButton']['svgObject'].setInteractive();
                buttons['playersButton']['svgObject'].bringToTop(buttons['playersButton']['svgObject'].getByName('playersButton' + OTJAT_MODE));

            });
    }, 100);

    setTimeout(function () {
        buttons['playersButton']['svgObject'].setInteractive();
        buttons['playersButton']['svgObject'].bringToTop(buttons['playersButton']['svgObject'].getByName('playersButton' + OTJAT_MODE));
    }, 3000);
}////
function isAndroidAppGlobal() {
    if (getCookieGlobal('DEVICE') === 'Android') {
        return true;
    }

    if (getCookieGlobal('PRODUCT') === 'RocketWeb') {
        return true;
    }

    return window.location.href.indexOf('app=1') > -1;
}


function isVerstkaTestGlobal() {
    return window.location.href.indexOf('verstka=1') > -1;
}

function isMobileDeviceGlobal() {
    const ua = navigator.userAgent;
    if (/(tablet|ipad|playbook|silk)|(android(?!.*mobi))/i.test(ua)) {
        return true;//tablet
    } else if (/Mobile|Android|iP(hone|od)|IEMobile|BlackBerry|Kindle|Silk-Accelerated|(hpw|web)OS|Opera M(obi|ini)/.test(ua)) {
        return true;//mobile
    }

    return false;//desktop
}

function isTabletDeviceGlobal() {
    const ua = navigator.userAgent;
    if (/(tablet|ipad|playbook|silk)|(android(?!.*mobi))/i.test(ua)) {
        return true;
    }

    return false;
}

function isVkAppGlobal() {
    if (document.referrer == 'https://vk.com/') {
        return true;
    }

    if (document.location.href.match('api_url')) {
        return true;
    }

    return false;
}

function isYandexAppGlobal() {
    if (document.location.href.match('yandex')) {
        return true;
    }

    if (window.location.href.indexOf('yandex') > -1) {
        return true;
    }

    return false;
}

function getCookieGlobal(cookieName) {
    var results = document.cookie.match('(^|;) ?' + cookieName + '=([^;]*)(;|$)');

    if (results)
        return (unescape(results[2]));
    else
        return false;
}

function isIOSDevice() {
    if (/iPhone|iPad|iPod/i.test(navigator.userAgent)) {
        return true;
    }

    return false;
}
////

// для копирования из input в буфер
function copyToClipboard(selector) {
    const element = document.querySelector(selector);
    element.select();
    element.setSelectionRange(0, 99999);
    document.execCommand('copy');
}

(function profileModal() {
    const selectors = {
        profileTabsId: 'profile-tabs',
        tabLink: '#profile-tabs a',
        tabContent: '.tab-content',
        tabContentWrap: '.tab-content-wrap',
        tabPane: '.tab-pane',
        copyBtn: '.js-btn-copy',
        setNicknameBtn: '.js-btn-set-nickname',
        setProfileImageBtn: '.js-btn-set-profile-image',
        nicknameInput: '#player_name',
        profileImageInput: '#player_avatar_file',
        userIdInput: '#user_id',
    };

    const setTabContentOffset = (tabsSelector) => {
        if (!document.getElementById(selectors.profileTabsId)) {
            return;
        }
        const targetId = document
            .querySelectorAll(`${selectors.tabLink}.active`)[0]
            .getAttribute('href');
        const tabContent = document.querySelector(targetId).closest(selectors.tabContent);
        const tabContentWrap = tabContent.closest(selectors.tabContentWrap);
        const tabPane = document.querySelector(targetId);
        tabContentWrap.style.height = tabContent.getBoundingClientRect().height + 'px';

        const index = [...tabContent.querySelectorAll(selectors.tabPane)].findIndex(
            (item) => {
                return item === tabPane;
            },
        );
        const width = tabContentWrap.getBoundingClientRect().width;

        const translateValue = index * -width;

        tabContent.style.cssText = `transform: translate(${translateValue}px, 0);`;
    };

    document.addEventListener('click', (event) => {
        if (event.target && event.target.closest(selectors.setNicknameBtn)) {
            event.preventDefault();
            const userId = document.querySelector(selectors.userIdInput).value;
            const value = document.querySelector(selectors.nicknameInput).value;
            savePlayerName(value, userId);
            return false;
        }
    });

    document.addEventListener('click', (event) => {
        if (event.target && event.target.closest(selectors.setProfileImageBtn)) {
            event.preventDefault();
            const userId = document.querySelector(selectors.userIdInput).value;
            const value = document.querySelector(selectors.profileImageInput).value;
            savePlayerAvatar(value, userId);
            return false;
        }
    });

    document.addEventListener('click', (event) => {
        if (event.target && event.target.closest(selectors.tabLink)) {
            if (!document.getElementById(selectors.profileTabsId)) {
                return;
            }
            event.preventDefault();
            document
                .querySelectorAll(selectors.tabLink)
                .forEach((item) => item.classList.remove('active'));
            event.target.classList.add('active');
            const targetId = event.target.getAttribute('href');

            setTabContentOffset(`#${selectors.profileTabsId}`);
        }
    });

    document.addEventListener('click', (event) => {
        if (event.target && event.target.closest(selectors.copyBtn)) {
            event.preventDefault();
            copyToClipboard(event.target.closest(selectors.copyBtn).getAttribute('href'));
        }
    });

    const onProfileModalLoaded = () => {
        if (!document.getElementById(selectors.profileTabsId)) {
            return;
        }

        const setWidthToPanes = () => {
            const tabPaneList = document.querySelectorAll(selectors.tabPane) || [];
            if (tabPaneList.length > 0) {
                const tabContentWrap = tabPaneList[0].closest(selectors.tabContentWrap);
                let width = tabContentWrap.getBoundingClientRect().width + 'px';

                for (let i = 0; i < tabPaneList.length; i++) {
                    tabPaneList[i].style.width = width;
                    const currentWidth = tabContentWrap.getBoundingClientRect().width + 'px';
                    if (currentWidth !== width) {
                        width = currentWidth;
                        i = 0;
                    }
                }
            }
        };

        setWidthToPanes();
        setTabContentOffset(`#${selectors.profileTabsId}`);

        if (!window.profileTabslistenerAttached) {
            window.addEventListener('resize', () => {
                if (!document.getElementById(selectors.profileTabsId)) {
                    return;
                }
                setWidthToPanes();
                setTabContentOffset(`#${selectors.profileTabsId}`);
            });
            window.profileTabslistenerAttached = true;
        }

        window.dispatchEvent(new Event('resize'));
    };

    window.profileModal = { onProfileModalLoaded };
})();

const btnFAQClickHandler = () => {
    bootbox.hideAll();
    
    getFAQModal().then(html => {

        dialog = bootbox
            .dialog({
                message: html,
                locale: lang === 'RU' ? 'ru' : 'en',
                className: 'modal-settings  modal-faq',
                closeButton: false,
                buttons: {
                    ok: {
                        label: lang === 'RU' ? 'Назад' : 'Back',
                        className: 'btn-sm ml-auto mr-0',
                        callback: function() {
                            
                            fetchGlobal(STATUS_CHECKER_SCRIPT).then((data) => {
                                commonCallback(data);
                                gameStates['chooseGame']['action'](data)
                            });
                        }
                    },
                }
            })
            .off('shown.bs.modal').on('shown.bs.modal', function() {
                if (tabsModule) {
                    tabsModule.initTabs();
                }
            }).find('.modal-content').css({
                'background-color': 'rgba(230, 255, 230, 1)',
            });


        }).catch(error => {
            console.error(error);
        });
    
    return false;
};

document.addEventListener('click', (e) => {
    if (e.target && e.target.matches('#btn-faq')) {
        e.preventDefault();
        btnFAQClickHandler();
    }
});

function StatsPage({ json, BASE_URL }) {
    // const BASE_URL = 'http://127.0.0.1:5500';

    const CardList = ({ list }) => {
        const types = {
            day: 'stone_card',
            week: 'bronze_card',
            month: 'silver_card',
            year: 'gold_card',
        };

        let result = list
            .map(
                ({
                     event_type,
                     event_period,
                     record_type_text,
                     event_type_text,
                     points_text,
                     reward,
                     income,
                     date_achieved,
                 }) => {
                    const date = new Date(date_achieved);
                    let strDate =
                        `0${date.getDate()}`.slice(-2) +
                        '.' +
                        `0${date.getMonth() + 1}`.slice(-2) +
                        '.' +
                        date.getFullYear();

                    return `
                <li>
                    <div class="card_item full_card ${types[event_period]}">
                        <h3 class="card_record">
                            ${record_type_text} <br>
                            ${event_type_text}
                        </h3>
                        <div class="card_points">
                            ${points_text}
                        </div>
                        <div class="card_get">
                            <p>Получена награда</p>
                            <div class="card_rewardInfo">
                                <p><img class="card_plus" src="./images/plus.png" alt=""></p>
                                <p><img class="card_rewardImage" src="./images/bigMoney.png" alt="money">
                                </p>
                                <span class="card_moneyCount">${reward}</span>
                            </div>
                        </div>
                        <p class="card_passive">Пассивный заработок</p>
                        <div class="card_hour">
                            <img class="card_hourImage" src="./images/smallMoney.png" alt="">
                            <span>x${income}/час</span>
                        </div>

                        <p class="card_effect">Начисляется, пока не перебито</p>
                    </div>
                    <span class="date">${strDate}</span>
                </li>
            `;
                },
            )
            .join('');

        result = `<ul class="card_list full_cards">${result}</ul>`;

        return result;
    };

    const GameList = ({ games }) => {
        let gameList = '';
        if (!games.length) {
            return gameList;
        }
        gameList = games
            .map((item) => {
                const matchResultClass = ['victory', 'победа'].includes(
                    item.your_result.toLocaleLowerCase(),
                )
                    ? 'match-history--win'
                    : 'match-history--lose';
                return `
                <li class="match-history-item ${matchResultClass} box d-flex">
                    <div class="match-history-date">${item.game_ended_date}</div>
                    <div class="match-history-result">
                        <div class="pill">${item.your_result}</div>
                    </div>
                    <div class="match-history-rating">
                        <div class="pill">${item.new_rating} <span>${item.delta_rating}</span></div>
                    </div>
                    <div class="match-history-opponent">
                        <figure class="figure">
                            <img src="${item.opponent_avatar_url}" class="figure-img rounded" alt="" />
                            <figcaption class="figure-caption text-start">
                                <a href="${item.opponent_filter_url}" title="${item.opponent_filter_title}">${item.opponent_name}</a>
                            </figcaption>
                        </figure>
                    </div>
                </li>
            `;
            })
            .join('');

        gameList = `<ul>${gameList}</ul>`;

        return gameList;
    };

    const Pagination = ({ pagination }) => {
        let result = '';
        for (const key in pagination) {
            if (Object.prototype.hasOwnProperty.call(pagination, key)) {
                const element = pagination[key];
                const isActive = !element.is_link;
                if (isActive) {
                    result += `<li class="active"><span>${key}</span></li>`;
                } else {
                    result += `<li><a href="${element.value}">${key}</a></li>`;
                }
            }
        }

        return `
                <nav class="pagination-wrap">
                    <ul class="pagination">
                        ${result}
                    </ul>
                </nav>
            `;
    };

    const OpponentStats = ({ opponent_stats }) => {
        const isPositiveWinRate = +opponent_stats[0].delta_rating > 0;
        const resultClass = isPositiveWinRate ? 'color-win' : 'color-lose';
        const prefix = isPositiveWinRate ? '+' : '';

        return `
				<div class="total box">

					<div class="col">
						<span>Всего<br>партий</span>
						<span>${opponent_stats[0].games_count}</span>
					</div>
					<div class="col">
						<span>Всего<br>побед</span>
						<span>${opponent_stats[0].wins}</span>
					</div>
					<div class="col">
						<span>Прибавка/потеря<br>в рейтинге</span>
						<span class="${resultClass}">${prefix}${opponent_stats[0].delta_rating}</span>
					</div>
					<div class="col">
						<span>% Побед</span>
						<span>${opponent_stats[0].win_percent}</span>
					</div>

				</div>
            `;
    };

    (function statsModal() {
        const selectors = {
            modal: '.modal-stats',
            paginationWrap: '.pagination-wrap',
            gameList: '.match-history-table ul',
            opponentStats: '.opponent-stats',
            opponentName: '.opponent-name',
            activeAwards: '#active-awards-tab-pane .card-list-wrap',
            pastAwards: '#past-awards-tab-pane .card-list-wrap',
            btnRemoveFilter: '.js-remove-filter',
        };




        const onStatsModalLoaded = () => {
            const modalContainer = document.querySelector(selectors.modal);
            const links = modalContainer.querySelectorAll(selectors.paginationWrap + ' a');
            const opponentLinks = modalContainer.querySelectorAll(selectors.gameList + ' a');
            const opponentStats = modalContainer.querySelector(selectors.opponentStats);
            const opponentName = modalContainer.querySelector(selectors.opponentName);
            const activeAwards = modalContainer.querySelector(selectors.activeAwards);
            const pastAwards = modalContainer.querySelector(selectors.past_achieves);
            const btnRemoveFilter = modalContainer.querySelector(selectors.btnRemoveFilter);

            const updateHtml = (json) => {
                const paginationEl = document.querySelector(selectors.paginationWrap);
                const gameListEl = document.querySelector(selectors.gameList);
                paginationEl.outerHTML = Pagination(json);
                gameListEl.outerHTML = GameList(json);

                tabsModule.update();

                if (Object.hasOwn(json, 'opponent_stats')) {
                    opponentStats.innerHTML = OpponentStats(json);
                    opponentName.innerHTML = json.games[0].opponent_name;
                    opponentName.parentElement.classList.remove('invisible');
                    opponentStats.classList.remove('d-none');

                    btnRemoveFilter.classList.remove('d-none');
                    btnRemoveFilter.setAttribute('data-url', json.games[0].opponent_filter_url);
                } else {
                    btnRemoveFilter.classList.add('d-none');
                    opponentName.parentElement.classList.add('invisible');
                    opponentStats.classList.add('d-none');
                }

                onStatsModalLoaded();
                tabsModule.update();
            };



            const linkHandler = (e) => {
                e.preventDefault();
                let url = e.target.getAttribute('href');
                if (!url) {
                    url = e.target.getAttribute('data-url');
                }

                if (url) {
                    // console.log(`${BASE_URL}/${url}`);

                    return fetch(`${BASE_URL}/${url}`, {
                        headers: { 'Content-Type': 'application/json' },
                    })
                        .then((response) => {
                            if (!response.ok) {
                                throw new Error(`Response status: ${response.status}`);
                            }
                            return response.json();
                        })
                        .then((json) => {
                            updateHtml(json);
                            // prevLink = link;
                        })
                        .catch((error) => console.error('Ошибка загрузки страницы:', error));
                }

            };


            [...links, ...opponentLinks, btnRemoveFilter].forEach((link) => {
                if (!link.getAttribute('data-attached')) {
                    link.setAttribute('data-attached', true);
                    link.addEventListener('click', linkHandler);
                }
            });


        };

        window.statsModal = { onStatsModalLoaded };
    })();

    function getStatsModal(json) {
        return fetch('/stats-modal-tpl.html')
            .then((response) => response.text())
            .then((template) => {
                // Заменяем маркеры в шаблоне реальными данными
                let message = template

                    .replaceAll('{{name}}', json.player_name)
                    .replaceAll('{{imageUrl}}', json.player_avatar_url)
                    .replaceAll('{{gameList}}', GameList({ games: json.games }))
                    .replaceAll('{{pagination}}', Pagination({ pagination: json.pagination }))
                    .replaceAll('{{activeAwards}}', CardList({ list: json.current_achieves }))
                    .replaceAll('{{pastAwards}}', CardList({ list: json.past_achieves }))

                    .replaceAll('{{Stats}}', 'Статистика')
                    .replaceAll('{{Past Awards}}', 'Прошлые награды')
                    .replaceAll('{{Parties_Games}}', 'Партии')
                    .replaceAll('{{Player Awards}}', 'Награды игрока')
                    .replaceAll('{{Player}}', 'Игрок')
                    .replaceAll('{{VS}}', 'Против')
                    .replaceAll('{{Date}}', 'Дата')
                    .replaceAll('{{Result}}', 'Результат')
                    .replaceAll('{{Rating}}', 'Рейтинг')
                    .replaceAll('{{Opponent}}', 'Соперник')
                    .replaceAll('{{Active Awards}}', 'Награды');

                return message;
            })
            .catch((error) => console.error('Ошибка загрузки stats-modal-tpl:', error));
    }

    // ON MODAL LOADED
    function init() {
        return getStatsModal(json).then((html) => {
            // document.getElementById('test-tpl').innerHTML = html;

            // profileModal.onProfileModalLoaded();
            statsModal.onStatsModalLoaded();
            tabsModule.initTabs();

            // document.addEventListener("DOMContentLoaded", profileModal.onProfileModalLoaded);
        });
    }

    function onLoad() {
        statsModal.onStatsModalLoaded();
        tabsModule.initTabs();
    }

    // document.addEventListener('DOMContentLoaded', (e) => {
    // 	getStatsModal(json).then((html) => {
    // 		// document.getElementById('test-tpl').innerHTML = html;

    // 		// profileModal.onProfileModalLoaded();
    // 		statsModal.onStatsModalLoaded();
    // 		tabsModule.initTabs();

    // 		// document.addEventListener("DOMContentLoaded", profileModal.onProfileModalLoaded);
    // 	});
    // });

    return {
        buildHtml: () => getStatsModal(json),
        onLoad,
    };
}
/* ------------------------------- END OF FILE ------------------------------ */
(function tabsModule() {
    // on modal loaded
    // document.addEventListener("DOMContentLoaded", initTabs);

    const selectors = {
        tabLink: 'a[data-toggle="tab"]',
        tabContent: '.tab-content',
        tabContentWrap: '.tab-content-wrap',
        tabPane: '.tab-pane',
    };

    const setTabContentOffset = (i = 0) => {
        if (!document.querySelectorAll(`${selectors.tabLink}.active`).length) {
            return;
        }
        const targetId = document
            .querySelectorAll(`${selectors.tabLink}.active`)[0]
            .getAttribute('href');
        const tabContent = document.querySelector(targetId).closest(selectors.tabContent);
        const tabContentWrap = tabContent.closest(selectors.tabContentWrap);
        const tabPane = document.querySelector(targetId);

        if (!tabContent || !targetId || !tabContentWrap || !tabPane) {
            return;
        }

        const activeTabContentWrapHeight = tabContent.getBoundingClientRect().height + 'px';
        tabContentWrap.style.height = activeTabContentWrapHeight;

        // показываем каждый таб в полный размер
        [...tabContent.querySelectorAll(selectors.tabPane)].forEach((item) => {
            item.style.height = 'auto';
            item.style.visibility = 'hidden';
            item.closest(selectors.tabContentWrap).style.height =
                item.closest(selectors.tabContent).getBoundingClientRect().height + 'px';
        });

        const maxModalBody = document.querySelector('.modal-body').getBoundingClientRect().height;

        // скрываем
        [...tabContent.querySelectorAll(selectors.tabPane)].forEach((item) => {
            if (!item.matches('.active')) {
                item.style.height = '0px';
                item.closest(selectors.tabContentWrap).style.height = '';
            } else {
                item.style.visibility = 'visible';
            }
        });

        [...tabContent.querySelectorAll(selectors.tabPane)].forEach((item) => {
            item.style.height = '';
        });

        tabContentWrap.style.height = activeTabContentWrapHeight;

        document.querySelector('.modal-body').style.minHeight = maxModalBody + 'px';

        const index = [...tabContent.querySelectorAll(selectors.tabPane)].findIndex((item) => {
            return item === tabPane;
        });
        const width = tabContentWrap.getBoundingClientRect().width;

        const translateValue = index * -width;

        tabContent.style.cssText = `transform: translate(${translateValue}px, 0);`;

        document.querySelectorAll(selectors.tabPane).forEach((item) => {
            const width =
                item.closest(selectors.tabContentWrap).getBoundingClientRect().width + 'px';
            item.style.width = width;
        });

        const diff = Math.abs(
            tabContentWrap.getBoundingClientRect().height - tabContent.scrollHeight,
        );
        // console.log(i, diff);

        if (diff > 1 && i < 100) {
            i++;
            // console.log(i, diff);

            setTimeout(setTabContentOffset, 100, i);
        }
    };

    const update = () => {
        setTimeout(() => setTabContentOffset(), 100);
        onImagesLoaded(document.querySelector('.modal-settings'), setTabContentOffset);
    };

    document.addEventListener('click', (event) => {
        if (event.target && event.target.closest(selectors.tabLink)) {
            event.preventDefault();
            document
                .querySelectorAll(selectors.tabLink)
                .forEach((item) => item.classList.remove('active'));
            event.target.classList.add('active');

            setTabContentOffset();
        }
    });

    const initTabs = () => {
        document
            .querySelectorAll(selectors.tabPane)
            .forEach(
                (item) =>
                    (item.style.width =
                        item.closest(selectors.tabContentWrap).getBoundingClientRect().width +
                        'px'),
            );

        if (!window.tabslistenerAttached) {
            window.addEventListener('resize', (event) => {
                document.querySelectorAll(selectors.tabPane).forEach((item) => {
                    const width =
                        item.closest(selectors.tabContentWrap).getBoundingClientRect().width + 'px';
                    item.style.width = width;
                });
                setTabContentOffset();
            });
            window.tabslistenerAttached = true;
        }

        setTimeout(() => {
            window.dispatchEvent(new Event('resize'));
            setTimeout(() => {
                update();
            }, 500);
        }, 100);
    };

    window.tabsModule = { initTabs, update };

    // return {
    //     onProfileModalLoaded
    // }
})();

function onImagesLoaded(container, event) {
    var images = container.getElementsByTagName('img');
    var loaded = images.length;
    for (var i = 0; i < images.length; i++) {
        if (images[i].complete) {
            loaded--;
        } else {
            images[i].addEventListener('load', function () {
                loaded--;
                if (loaded == 0) {
                    event();
                }
            });
        }
        if (loaded == 0) {
            event();
        }
    }
}


function getInstructions(lang) {
    const url = 'https://эрудит.club/mvc/faq/getAll?lang=' + lang;
    // const url = 'faq.json?lang=' + lang;

    return fetch(url)
       .then(response => {
            if (response.ok) {
                return response.json();
            } else {
                throw new Error('Ошибка при получении инструкций');
            }
        }).catch(error => console.error('Ошибка загрузки instructions:', error));
}

function getFAQModal() {
    return fetch('/faq-modal-tpl.html')
        .then(response => response.text())
        .then(template => {
            
            return new Promise((resolve, reject) => {
                let message = document.createElement('div');

                getInstructions(lang).then(instructions => {

                    message.innerHTML = template;

                    ['faq_rules', 'faq_rating', 'faq_rewards', 'faq_coins'].forEach(item => {
                        if (item in instructions) {
                            message.querySelector(`#${item}`).innerHTML = instructions[item];
                        }
                    });

                    resolve(message);
                }).catch(error => reject(error));
            });
        })
        .catch(error => console.error('Ошибка загрузки faq-modal:', error));
}

var game = new Phaser.Game(config);

document.body.style.backgroundColor = "#dddddd";

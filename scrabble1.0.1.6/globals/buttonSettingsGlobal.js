//
var topButtons = {newGameButton: {displayWidth: 0}, instructButton: {displayWidth: 0}, prizesButton: {displayWidth: 0}, inviteButton: {displayWidth: 0}};

var modes = [OTJAT_MODE, ALARM_MODE, 'Inactive', 'Navedenie', 'Najatie'];

var buttons = {
    newGameButton: {
        filename: 'new_game2',
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
                if (commonId) {
                    botUrl = GAME_BOT_URL + '/?start=inv_' + commonId;
                    shareUrl = /*'https://t.me' + */'/share/url?url=' + encodeURIComponent(botUrl)
                        + '&text=' + encodeURIComponent(INVITE_FRIEND_PROMPT);
                    WebView.postEvent(
                        'web_app_open_tg_link',
                        false,
                        {path_full: shareUrl,}
                        );
                }

                return false;
            }
        }
    },
    submitButton: {
        filename: 'otpravit2',
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
        filename: 'steret2',
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
        filename: 'pomenyat2',
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
        filename: 'proveryt2',
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
        filename: 'you',
        x: botXY.x + buttonStepX + buttonWidth / 2,
        y: botXY.y + botHeight * 0.75 * 0.1,
        width: buttonWidth,
        object: false,
        svgObject: false,
        numbers: true,
    },
    player1Block: {
        filename: 'player1',
        x: botXY.x + buttonStepX + buttonWidth / 2,
        y: botXY.y + botHeight * 0.75 * 0.1,
        width: buttonWidth,
        object: false,
        svgObject: false,
        numbers: true,
    },
    player2Block: {
        filename: 'player2',
        x: botXY.x + buttonStepX + buttonWidth / 2,
        y: botXY.y + botHeight * 0.75 * (0.2 + 0.1),
        width: buttonWidth,
        object: false,
        svgObject: false,
        numbers: true,
    },
    player3Block: {
        filename: 'player3',
        x: botXY.x + buttonStepX + buttonWidth / 2,
        y: botXY.y + botHeight * 0.75 * (0.4 + 0.1),
        width: buttonWidth,
        object: false,
        svgObject: false,
        numbers: true,
    },
    player4Block: {
        filename: 'player4',
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

    /*for (let digit = 0; digit <= 9; digit++) {
        for (let mod in playerBlockModes) {
            for (let pos in digitPositions) {
                container.getByName(playerBlockModes[mod] + '_' + digit + '_' + digitPositions[pos]).setVisible(false);
            }
        }
    }*/

    if(thirdDigit !== playerScores[blockName].digit3 || mode !== playerScores[blockName].mode) {
        console.log(playerScores[blockName].mode + '_' + playerScores[blockName].digit3 + '_' + '3');
        container.getByName(playerScores[blockName].mode + '_' + playerScores[blockName].digit3 + '_' + '3').setVisible(false);
    }

    if(secondDigit !== playerScores[blockName].digit2 || mode !== playerScores[blockName].mode) {
        console.log(playerScores[blockName].mode + '_' + playerScores[blockName].digit2 + '_' + '2');
        container.getByName(playerScores[blockName].mode + '_' + playerScores[blockName].digit2 + '_' + '2').setVisible(false);
    }

    if(firstDigit !== playerScores[blockName].digit1 || mode !== playerScores[blockName].mode) {
        console.log(playerScores[blockName].mode + '_' + playerScores[blockName].digit1 + '_' + '1');
        container.getByName(playerScores[blockName].mode + '_' + playerScores[blockName].digit1 + '_' + '1').setVisible(false);
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

function displayTimeGlobal(time)
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

    for (let digit = 0; digit <= 9; digit++) {
        for (let mod in playerBlockModes) {
            for (let pos in digitPositions) {
                container.getByName(playerBlockModes[mod] + '_' + digit + '_' + digitPositions[pos]).setVisible(false);
            }
        }
    }

    container.getByName(mode + '_' + thirdDigit + '_3').setVisible(true);
    //if (secondDigit > 0 || firstDigit > 0) {
        container.getByName(mode + '_' + secondDigit + '_2').setVisible(true);
    //}

    //if (firstDigit > 0) {
        container.getByName(mode + '_' + firstDigit + '_1').setVisible(true);
    //}
}

function buttonSetModeGlobal(objectSet, objectName, mode)
{
    let svgObject = objectSet[objectName].svgObject;
    svgObject.bringToTop(svgObject.getByName(objectName + mode));

    if (mode === ALARM_MODE) {
        svgObject.getByName(objectName + ALARM_MODE).setVisible(true);
    } else {
        svgObject.getByName(objectName + ALARM_MODE).setVisible(false);
    }
}







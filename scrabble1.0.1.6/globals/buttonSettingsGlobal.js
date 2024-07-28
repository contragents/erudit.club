//
var svgButtons = {
    checkButton: {filename: 'proveryt'},
    submitButton: {filename: 'otpravit'},
    instructButton: {filename: 'instrukt'},
    newGameButton: {filename: 'new_game'},
    resetButton: {filename: 'steret'},
    changeButton: {filename: 'pomenyat'},
    chatButton: {filename: 'chat'},
    logButton: {filename: 'log'},
    playersButton: {filename: 'igroki'}
};

var topButtons = {newGameButton: {displayWidth: 0}, instructButton: {displayWidth: 0}, prizesButton: {displayWidth: 0}, inviteButton: {displayWidth: 0}};

var modes = ['Otjat', 'Alarm', 'Inactive', 'Navedenie', 'Najatie'];

var buttons = {
    newGameButton: {
        filename: 'new_game2',
        x: topXY.x + lotokX + buttonWidth / 2 - lotokCellStep / 2 + 5,
        y: (topXY.y + topHeight) / 2,
        caption: 'New#Game',
        width: buttonWidth,
        object: false, svgObject: false,
        pointerupFunction: function () {
            newGameButtonFunction()
        }
    },
    instructButton: {
        filename: 'instrukt2',
        x: topXY.x + lotokX + buttonWidth / 2 - lotokCellStep / 2 + 5 + buttonWidth,
        y: (topXY.y + topHeight) / 2,
        caption: 'инструкция',
        //height:
        width: buttonWidth / 2,
        object: false, svgObject: false,
        enableTint: 0x00ff00,
        pointerupFunction: function () {
            shareButtonFunction()
        }
    },
    prizesButton: {
        filename: 'prizes2',
        modes: ['Otjat', 'Navedenie', 'Najatie'],
        x: (topXY.x + knopkiWidth) / 2,
        y: (topXY.y + topHeight) / 2,
        caption: 'Prizes',
        width: buttonWidth / 2,
        //height: topHeight,
        object: false,
        svgObject: false,
        enableTint: 0x00ff00,
        pointerupFunction: function () {
            return;
        }
    },
    inviteButton: {
        filename: 'invite2',
        modes: ['Otjat', 'Navedenie', 'Najatie'],
        x: topXY.x + knopkiWidth - buttonWidth,
        y: topXY.y + topHeight / 2,
        caption: 'Invite',
        width: buttonWidth / 2,
        object: false,
        svgObject: false,
        enableTint: 0x00ff00,
        pointerupFunction: function () {
            {
                if (commonId) {
                    botUrl = GAME_BOT_URL + '/?start=inv_' + commonId;
                    shareUrl = 'https://t.me/share/url?url=' + encodeURIComponent(botUrl)
                        + '&text=' + encodeURIComponent(INVITE_FRIEND_PROMPT);
                    console.log(shareUrl);
                    //location = shareUrl;
                    document.querySelector('#invite_link').click();
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
        enableTint: 0x00ff00,
        enabled: {myTurn: 1},
        pointerupFunction: function () {
            submitButtonFunction()
        }
    },
    resetButton: {
        filename: 'steret2',
        x: botXY.x + knopkiWidth - buttonWidth / 2 - buttonStepX,
        y: botXY.y + botHeight * (0.25 + 0.125),
        caption: 'стереть',
        width: buttonWidth,
        object: false, svgObject: false,
        enableTint: 0x00ff00,
        enabled: {myTurn: 1, preMyTurn: 1, otherTurn: 1},
        pointerupFunction: function () {
            resetButtonFunction()
        }
    },
    changeButton: {
        filename: 'pomenyat2',
        x: botXY.x + knopkiWidth - buttonWidth / 2 - buttonStepX,
        y: botXY.y + botHeight * (0.5 + 0.125),
        caption: 'поменять',
        width: buttonWidth,
        object: false, svgObject: false,
        enableTint: 0x00ff00,
        enabled: {myTurn: 1},
        pointerupFunction: function () {
            changeButtonFunction()
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
            playersButtonFunction()
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
        enableTint: 0x00ff00,
        enabled: {myTurn: 1, preMyTurn: 1, otherTurn: 1},
        pointerupFunction: function () {
            checkButtonFunction()
        }
    },
    chatButton: {
        filename: 'chat2',
        x: botXY.x + knopkiWidth / 2,
        y: botXY.y + botHeight * (0.75 + 0.125),
        caption: 'чат',
        width: buttonWidth / 2,
        object: false, svgObject: false,
        pointerupFunction: function () {
            chatButtonFunction()
        },
    },
    logButton: {
        filename: 'log2',
        x: botXY.x + buttonStepX + buttonWidth / 2,
        y: botXY.y + botHeight * (0.75 + 0.125),
        caption: 'лог',
        width: buttonWidth / 2,
        object: false, svgObject: false,
        pointerupFunction: function () {
            logButtonFunction()
        },
    },
    razdvButton: {
        filename: 'razdv2',
        modes: ['Otjat', 'Navedenie', 'Najatie'],
        x: fullscreenXY['x'],
        y: fullscreenXY['y'],
        caption: 'Во весь экран',
        //width: fullscreenButtonSize,
        object: false,
        svgObject: false,
        enableTint: 0x00ff00,
        pointerupFunction: function () {
            document.body.requestFullscreen();
        }
    }
};






//
var modes = [OTJAT_MODE, ALARM_MODE, 'Inactive', 'Navedenie', 'Najatie'];

var buttons = {
    razdvButton: {
        filename: 'razdv',
        modes: [OTJAT_MODE, 'Navedenie', 'Najatie'],
        x: fullscreenXY['x'],
        y: fullscreenXY['y'],
        caption: 'Во весь экран',
        width: fullscreenButtonSize,
        object: false, svgObject: false,
        enableTint: 0x00ff00,
        pointerupFunction: function () {
            document.body.requestFullscreen();
        }
    },
    checkButton: {
        filename: 'proveryt',
        x: lotokX + buttonWidth / 2 - lotokCellStep / 2 + 5,
        y: lotokY + lotokCellStepY * lotokCapacityY,
        caption: 'проверить',
        width: buttonWidth,
        object: false, svgObject: false,
        enableTint: 0x00ff00,
        enabled: {myTurn: 1, preMyTurn: 1, otherTurn: 1},
        pointerupFunction: function () {
            checkButtonFunction()
        }
    },
    submitButton: {
        filename: 'otpravit',
        x: 0,
        y: 0,
        caption: 'отправить',
        width: buttonWidth,
        object: false, svgObject: false,
        enableTint: 0x00ff00,
        enabled: {myTurn: 1},
        pointerupFunction: function () {
            submitButtonFunction()
        }
    },
    instructButton: {
        filename: 'instrukt',
        x: 0,
        y: 0,
        caption: 'инструкция',
        width: buttonWidth,
        object: false, svgObject: false,
        enableTint: 0x00ff00,
        pointerupFunction: function () {
            shareButtonFunction()
        }
    },
    newGameButton: {
        filename: 'new_game',
        x: 0,
        y: 0,
        caption: 'новая#игра',
        width: buttonWidth,
        object: false, svgObject: false,
        pointerupFunction: function () {
            newGameButtonFunction()
        }
    },
    resetButton: {
        filename: 'steret',
        x: 0,
        y: 0,
        caption: 'стереть',
        width: buttonWidth * 0.75,
        object: false, svgObject: false,
        enableTint: 0x00ff00,
        enabled: {myTurn: 1, preMyTurn: 1, otherTurn: 1},
        pointerupFunction: function () {
            resetButtonFunction()
        }
    },
    changeButton: {
        filename: 'pomenyat',
        x: 0,
        y: 0,
        caption: 'поменять',
        width: buttonWidth * 0.75,
        object: false, svgObject: false,
        enableTint: 0x00ff00,
        enabled: {myTurn: 1},
        pointerupFunction: function () {
            changeButtonFunction()
        }
    },
    chatButton: {
        filename: 'chat',
        x: 0,
        y: 0,
        caption: 'чат',
        width: buttonWidth * 0.4,
        object: false, svgObject: false,
        pointerupFunction: function () {
            chatButtonFunction()
        },
        preCalc: function () {
            this.x = buttons['changeButton']['x'] - buttons['changeButton']['svgObject'].width / 2 + buttons['changeButton']['svgObject'].width * this.width / buttons['changeButton']['width'] / 2;
            this.y = buttons['changeButton']['y'] + buttonStepY;
        }
    },
    logButton: {
        filename: 'log',
        x: 0,
        y: 0,
        caption: 'лог',
        width: buttonWidth * 0.3,
        object: false, svgObject: false,
        pointerupFunction: function () {
            logButtonFunction()
        },
        preCalc: function () {
            this.x = buttons['changeButton']['x'] + buttons['changeButton']['svgObject'].width / 2 - buttons['changeButton']['svgObject'].width * this.width / buttons['changeButton']['width'] / 2;
            this.y = buttons['chatButton']['y'];
        }
    },
    playersButton: {
        filename: 'igroki',
        x: 0,
        y: 0,
        caption: 'игроки',
        width: buttonWidth * 0.75,
        object: false, svgObject: false,
        pointerupFunction: function () {
            playersButtonFunction()
        },
        preCalc: function () {
            this.x = buttons['changeButton']['x'];
            this.y = buttons['newGameButton']['y'];
        }
    }
};

buttons['submitButton']['x'] = buttons['checkButton']['x'];
buttons['submitButton']['y'] = buttons['checkButton']['y'] + buttonStepY;

buttons['instructButton']['x'] = buttons['submitButton']['x'];
buttons['instructButton']['y'] = buttons['submitButton']['y'] + buttonStepY;

buttons['newGameButton']['x'] = buttons['instructButton']['x'];
buttons['newGameButton']['y'] = buttons['instructButton']['y'] + buttonStepY;

buttons['resetButton']['x'] = buttons['checkButton']['x'] + buttons['checkButton']['width'] + buttonStepY / 2 + (knopkiWidth - (buttons['checkButton']['x'] + buttons['checkButton']['width'] + buttons['resetButton']['width'])) / 2;
buttons['resetButton']['y'] = buttons['checkButton']['y'];

buttons['changeButton']['x'] = buttons['resetButton']['x'];
buttons['changeButton']['y'] = buttons['resetButton']['y'] + buttonStepY;





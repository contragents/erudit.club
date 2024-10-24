//
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

    if (gameState == 'chooseGame' && (queryNumber > 1)) {
        return;
    }

    var flor = Math.floor(time / 1000);

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
}
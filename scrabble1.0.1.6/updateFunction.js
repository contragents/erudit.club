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
}
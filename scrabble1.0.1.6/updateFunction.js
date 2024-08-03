//
function (time, delta) {

    if (requestSended && ((new Date()).getTime() - requestTimestamp > normalRequestTimeout)) {
        noNetworkImg.visible = true;
        noNetworkImg.alpha = ((new Date()).getTime() - requestTimestamp) < (normalRequestTimeout * 2)
            ? ((new Date()).getTime() - requestTimestamp - normalRequestTimeout) / 1000
            : 1;
        console.log('No Network');
    } else {
        noNetworkImg.visible = false;
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
            fetchGlobal(STATUS_CHECKER_SCRIPT, 'g', (uniqID === false) ? '0' : uniqID)
                .then((data) => {
                    commonCallback(data);
                });
        }
    }

    if (ochki_arr !== false) {
        if ('activeUser' in responseData) {
            for (k in ochki_arr) {
                if ((k == responseData['activeUser']) && (responseData['userNames'][k] !== '')) {
                    let x = ochki_arr[k].x;
                    if (((flor % 2) === 0) && (flor > lastflor)) {
                        ochki_arr[k].visible = false;
                    } else if (flor > lastflor) {
                        ochki_arr[k].visible = true;
                    }
                    lastflor = flor;
                } else if (responseData['userNames'][k] === '') {
                    ochki_arr[k].visible = false;
                }
            }
        }
    }

    if (gameState == 'myTurn' || gameState == 'preMyTurn' || gameState == 'otherTurn' || gameState == 'startGame')
        if (flor > lastTimeCorrection) {
            lastTimeCorrection = flor;
            if ((vremiaMinutes > 0) || (vremiaSeconds > 0)) {
                vremiaSeconds--;
                if (vremiaSeconds < 0) {
                    vremiaMinutes--;
                    vremiaSeconds = 59;
                }
                if (vremiaSeconds < 10)
                    vremia.text = vremia.text.substr(0, vremia.text.length - 4) + vremiaMinutes + ':' + '0' + vremiaSeconds;
                else
                    vremia.text = vremia.text.substr(0, vremia.text.length - 4) + vremiaMinutes + ':' + vremiaSeconds;
                if ((vremiaMinutes === 0) && (vremiaSeconds < 20)) {
                    if (vremiaSeconds > 10)
                        vremia.setColor('yellow');
                    else {
                        vremia.setColor('red');
                        if ((flor % 2) === 0)
                            vremiaFontSize = vremiaFontSizeDefault + vremiaFontSizeDelta;
                        else
                            vremiaFontSize = vremiaFontSizeDefault;
                    }
                } else {
                    vremia.setColor('black');
                    vremiaFontSize = vremiaFontSizeDefault;
                }

            } else if ((vremiaMinutes === 0) && (vremiaSeconds === 0)) {
                if ((flor % 2) === 0)
                    vremiaFontSize = vremiaFontSizeDefault + vremiaFontSizeDelta;
                else
                    vremiaFontSize = vremiaFontSizeDefault;
            }
            vremia.setFontSize(vremiaFontSize);
        }

    if (gameState == 'myTurn')
        if ((vremiaMinutes === 0) && (vremiaSeconds <= 10) && buttons['submitButton']['svgObject'].input.enabled)
            if ((flor % 2) === 0) {

                buttons['submitButton']['svgObject']
                    .bringToTop(buttons['submitButton']['svgObject']
                        .getByName('submitButton' + 'Alarm'));
            } else {

                buttons['submitButton']['svgObject']
                    .bringToTop(buttons['submitButton']['svgObject']
                        .getByName('submitButton' + 'Otjat'));
            }


    if (gameState == 'gameResults')
        if ((flor % 2) === 0) {

            buttons['newGameButton']['svgObject']
                .bringToTop(buttons['newGameButton']['svgObject']
                    .getByName('newGameButton' + 'Alarm'));
        } else {

            buttons['newGameButton']['svgObject']
                .bringToTop(buttons['newGameButton']['svgObject']
                    .getByName('newGameButton' + 'Otjat'));
        }


    return;

}
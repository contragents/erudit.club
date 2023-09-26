//
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
    console.log(gameObject.getData('cellX'), gameObject.getData('cellY'));
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
                //console.log('You choose '+i);
                var _this = window.game.scene.scenes[gameScene];
                var newLetter = getFishkaGlobal(i + 1 + 999, gameObject.x, gameObject.y, _this, true, userFishkaSet);
                newLetter.setData('lotokX', false);
                newLetter.setData('lotokY', false);
                newLetter.setData('cellX', gameObject.getData('cellX'));
                newLetter.setData('cellY', gameObject.getData('cellY'));

                if (gameObject.getData('isTemporary') == true) {
                    newLetter.setData('isTemporary', true);
                    newLetter.setData('zvezdaFrom', gameObject.getData('zvezdaFrom'));
                    cells[0 + newLetter.getData('cellX')][0 + newLetter.getData('cellY')][2] = newLetter.getData('zvezdaFrom');
                }

                cells[0 + newLetter.getData('cellX')][0 + newLetter.getData('cellY')][1] = newLetter.getData('letter');
                cells[0 + newLetter.getData('cellX')][0 + newLetter.getData('cellY')][3] = userFishkaSet;


                for (let k in container)
                    if (container[k] == gameObject) {
                        gameObject.destroy();
                        container.splice(k, 1);
                        break;
                    }
                container.push(newLetter);
                chooseFishka = false;
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

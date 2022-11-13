//
function parseDeskGlobal(newDesc) {


    newCells = newDesc;
    for (var i = 0; i <= 14; i++)
        for (var j = 0; j <= 14; j++) {
            cells[i][j][0] = false;//newCells[i][j][0];
            cells[i][j][1] = newCells[i][j][1];
            cells[i][j][2] = false;//newCells[i][j][2];
        }

    for (var k = 400; k >= 0; k--)
        if (k in fixedContainer) {
            fixedContainer[k].destroy();
            fixedContainer.splice(k, 1);
        }

    for (var i = 0; i <= 14; i++)
        for (var j = 0; j <= 14; j++)
            if (cells[i][j][1] !== false) {
                var fixFishka = getFishkaGlobal(cells[i][j][1], 1, 1, this.game.scene.scenes[gameScene], false).disableInteractive();
                findPlaceGlobal(fixFishka, 1, 1, i, j);
                fixFishka.setData('cellX', i);
                fixFishka.setData('cellY', j);
                fixedContainer.push(fixFishka);
            }
}

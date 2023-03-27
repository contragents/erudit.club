
function findPlace(gameObject, oldX, oldY, cellX, cellY) {
    //console.log(cells[cellX][cellY],cellX,cellY,oldX,oldY);
    if ((cells[cellX][cellY][0] === false) && (oldX == 1) && (oldY == 1))
        oldX = 1;
    else {
        var n = 15;
        minQad = 100000;
        for (var i = 0; i < n; i++)
            for (var j = 0; j < n; j++)
                if (cells[i][j][0] == false && ((((i + 1) * yacheikaWidth + stepX + correctionX - oldX) ** 2 + ((j + 1) * yacheikaWidth + stepY + correctionY - oldY) ** 2) < minQad)) {
                    minQad = ((i + 1) * yacheikaWidth + stepX + correctionX - oldX) ** 2 + ((j + 1) * yacheikaWidth + stepY + correctionY - oldY) ** 2;
                    cellX = i;
                    cellY = j;
                    console.log(minQad + ' ' + cellX + ' ' + cellY);
                }
    }

    gameObject.x = stepX + (cellX + 1) * yacheikaWidth + correctionX;
    gameObject.y = stepY + (cellY + 1) * yacheikaWidth + correctionY;
    gameObject.setData('cellX', cellX);
    gameObject.setData('cellY', cellY);
    cells[cellX][cellY][0] = true;
    cells[cellX][cellY][1] = gameObject.getData('letter');
    cells[cellX][cellY][3] = userFishkaSet;
    //console.log(cells[cellX][cellY],cellX,cellY);
}
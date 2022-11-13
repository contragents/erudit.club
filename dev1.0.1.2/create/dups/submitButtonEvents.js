/*
<?php
preg_match('/((release|yandex|dev)(\d\.\d\.\d\.\d))/',__DIR__,$matches);
$dir=$matches[1];
//Определяем каталог версии разработки
?>
*/
submitButton.on('pointerup', function () {
    submitButton.disableInteractive();
    submitButton.getRandom(0, 0).tint = 0x000000;

    setTimeout(function () {
            var xhr = new XMLHttpRequest();
            var body = 'cells=' + encodeURIComponent(JSON.stringify(cells));
            xhr.open("POST", '/<?=$dir?>/php/turn_submitter.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

            xhr.onreadystatechange = function (govno) {
                if (xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200) {
                    console.log(xhr.responseText);
                    parse_desk(xhr.responseText, container);
                    submitButton.setInteractive();
                    submitButton.getRandom(0, 0).tint = 0x00ff00;
                }
                ;
            };

            xhr.send(body);

        },
        100
    )
    ;
})
;

function parse_desk(newDesc, container) {
    newCells = JSON.parse(newDesc);
    if ('gameState' in newCells)
        return commonCallback(newCells);

    //console.log(newCells);
    for (var i = 0; i <= 14; i++)
        for (var j = 0; j <= 14; j++)
            if (cells[i][j][1] !== newCells[i][j][1]) {
                if (cells[i][j][1] !== false) {
                    console.log('!!!!!!!!!!' + i + ' ' + j);
                    for (var k = 0; k < container.length; k++)
                        if ((container[k].getData('cellX') === i) && (container[k].getData('cellY') === j)) {

                            container[k].setData('cellX', false);
                            container[k].setData('cellY', false);
                            container[k].x = 300;
                            container[k].y = 300;
                        }

                    cells[i][j][0] = newCells[i][j][0];
                    cells[i][j][1] = newCells[i][j][1];
                } else {
                    //console.log('+++++++++'+newCells[i][j][1]);
                    let newFishka = getFishkaGlobal(newCells[i][j][1], 1, 1, this.game.scene.scenes[gameScene], false).disableInteractive();
                    findPlace(newFishka, 1, 1, i, j);
                    //console.log(newFishka.x);
                    fixedContainer.push(newFishka);

                }
            } else
                for (var k = 0; k < container.length; k++)
                    if ((container[k].getData('cellX') === i) && (container[k].getData('cellY') === j)) {
                        container[k].disableInteractive();
                        fixedContainer.push(container[k]);
                        container.splice(k, 1);
                        break;
                    }

    for (var k = 0; k < container.length; k++)
        if ((container[k].getData('lotokX') === false) && (container[k].getData('lotokY') === false)) {
            contSlotXY = lotokFindSlotXY();
            container[k].x = lotokGetX(contSlotXY[0], contSlotXY[1]);
            container[k].y = lotokGetY(contSlotXY[0], contSlotXY[1]);
            container[k].setData('lotokX', contSlotXY[0]);
            container[k].setData('lotokY', contSlotXY[1]);
        }

}

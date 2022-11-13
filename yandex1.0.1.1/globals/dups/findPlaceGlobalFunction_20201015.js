function findPlaceGlobal(gameObject, oldX, oldY, cellX, cellY) {
    //console.log(cells[cellX][cellY],cellX,cellY,oldX,oldY);
    if ((cells[cellX][cellY][0] === false) && (oldX == 1) && (oldY == 1)) 
        oldX = 1;
    else {
        
        var n = 15;
        minQad=100000;
        for (var i = 0; i < n; i++)
            for (var j = 0; j < n; j++) {
                if ( cells[i][j][0]!=false && containerFishkaPresent(i,j) && ((((i+1)*yacheikaWidth+stepX+correctionX-oldX)**2 + ((j+1)*yacheikaWidth+stepY+correctionY-oldY)**2 ) < minQad) ) {
                    minQad = ((i+1)*yacheikaWidth+stepX+correctionX-oldX)**2 + ((j+1)*yacheikaWidth+stepY+correctionY-oldY)**2 ;
                    cellX = i;
                    cellY = j;
                    console.log('1');
                }
                if ( cells[i][j][0]!=false && cells[i][j][2]===false && fixedZvezdaPresent(i,j,gameObject.getData('letter')) && ((((i+1)*yacheikaWidth+stepX+correctionX-oldX)**2 + ((j+1)*yacheikaWidth+stepY+correctionY-oldY)**2 ) < minQad) ) {
                    minQad = ((i+1)*yacheikaWidth+stepX+correctionX-oldX)**2 + ((j+1)*yacheikaWidth+stepY+correctionY-oldY)**2 ;
                    cellX = i;
                    cellY = j;
                    console.log('2');
                }
                if ( cells[i][j][0]==false && ((((i+1)*yacheikaWidth+stepX+correctionX-oldX)**2 + ((j+1)*yacheikaWidth+stepY+correctionY-oldY)**2 ) < minQad) ) {
                    minQad = ((i+1)*yacheikaWidth+stepX+correctionX-oldX)**2 + ((j+1)*yacheikaWidth+stepY+correctionY-oldY)**2 ;
                    cellX = i;
                    cellY = j;
                    console.log('3');
                }
            }
    }

    if (cells[cellX][cellY][0] == false) {    
        gameObject.x = stepX + (cellX+1) * yacheikaWidth + correctionX;
        gameObject.y = stepY + (cellY+1) * yacheikaWidth + correctionY;
        gameObject.setData('cellX',cellX);
        gameObject.setData('cellY',cellY);
        cells[cellX][cellY][0] = true;
    
        if ((gameObject.getData('letter') >= '999') && (gameObject.x != oldX || gameObject.y != oldY) && !((oldX == 1) & (oldY == 1)))       
            chooseLetterGlobal(gameObject);
        else
            cells[cellX][cellY][1] = gameObject.getData('letter');
    }
    else
        if (fixedZvezdaPresent(cellX, cellY, gameObject.getData('letter'))) {
            var _this = window.game.scene.scenes[gameScene];
            
            var newLetter = getFishkaGlobal(999, 300,  300, _this);
            newLetter.setData('lotokX',false);
            newLetter.setData('lotokY',false);
            newLetter.setData('isTemporary',true);
            newLetter.setData('zvezdaFrom',gameObject.getData('letter'));
            container.push(newLetter);
            gameObject.disableInteractive();
            cells[cellX][cellY][2] = gameObject.getData('letter');
            gameObject.x = stepX + (cellX+1) * yacheikaWidth + correctionX;
            gameObject.y = stepY + (cellY+1) * yacheikaWidth + correctionY;
            gameObject.setData('cellXY',cellX+'-'+cellY);
            
        }
}

function fixedZvezdaPresent(i, j, letter) {
    for (k in fixedContainer)
        if ( (fixedContainer[k].getData('cellX') == i) && (fixedContainer[k].getData('cellY') == j) && ( (fixedContainer[k].getData('letter') - 1 - 999) === (letter) ) )
            return true;
    return false;
}

function containerFishkaPresent(i,j) {
    for (k in container)
        if ( (container[k].getData('cellX') == i) && (container[k].getData('cellY') == j))
            return true;
    return false;
}

function chooseLetterGlobal(gameObject) {
    console.log(gameObject.getData('cellX'),gameObject.getData('cellY'));
    if (gameObject.getData('cellX') === false) return;
    if (gameObject.getData('cellY') === false) return;
    
    chooseFishka = gameObject;
    var bukvy = '';
    var buttons1 = {}
    for (let i = 0; i<= 31; i++) {
    
        bukvy ='<div class="letter_'+i+'"></div>';
        buttons1[i] = {label: bukvy,
            className: 'button1',
            callback: function(){
                console.log('You choose '+i);
                var _this = window.game.scene.scenes[gameScene];
                var newLetter = getFishkaGlobal(i+1+999, gameObject.x,  gameObject.y, _this);
                newLetter.setData('lotokX',false);
                newLetter.setData('lotokY',false);
                newLetter.setData('cellX',gameObject.getData('cellX'));
                newLetter.setData('cellY',gameObject.getData('cellY'));
                if (gameObject.getData('isTemporary') == true) {
                    newLetter.setData('isTemporary',true);
                    newLetter.setData('zvezdaFrom',gameObject.getData('zvezdaFrom'));
                    cells[0+newLetter.getData('cellX')][0+newLetter.getData('cellY')][2] = newLetter.getData('zvezdaFrom');
                }
                cells[0+newLetter.getData('cellX')][0+newLetter.getData('cellY')][1] = newLetter.getData('letter');
                
                for (let k in container)
                    if (container[k] == gameObject) {
                        gameObject.destroy();
                        container.splice(k,1);
                        break;
                    }
                container.push(newLetter);
                chooseFishka = false;
            }}
        }
    bootbox.dialog({
    //title: 'Выберите букву',
    message: "Выберите букву",
    size: 'large',
    buttons: buttons1,
    closeButton: false
});
}
/*
function chooseLetterGlobal(gameObject) {
    var X = stepX+yacheikaWidth+correctionX;
    var Y = 1;
    var _this = this.game.scene.scenes[gameScene];
    if ( gameObject.y > (stepY+yacheikaWidth*3) )
        Y = gameObject.y - yacheikaWidth*3;
    else
        Y = gameObject.y + yacheikaWidth;

    for (var i = letterMin; i <= letterMax; i++) {
        chooseFishka[i] = getFishkaGlobal(i, X+yacheikaWidth*((i-letterMin) % 15), Y + yacheikaWidth*Math.floor((i-letterMin+1-0.1)/15), _this, false);
        chooseFishka[i].setData('letter',i);
        chooseFishka[i].setData('gameObject',gameObject);
        console.log(chooseFishka[i]);
        chooseFishka[i].on('pointerover', function () {
        this.getRandom(0,0).tint = 0x00ff00;
        });
        chooseFishka[i].on('pointerout', function () {
        this.getRandom(0,0).tint = 0xffffff;});
        chooseFishka[i].on('pointerup', function () {
            var _this = window.game.scene.scenes[gameScene];
            //var zvezda = this.getData('gameObject');
            var gameObject = getFishkaGlobal(this.getData('letter')+1+999, this.getData('gameObject').x,  this.getData('gameObject').y, _this);
            gameObject.setData('lotokX',false);
            gameObject.setData('lotokY',false);
            this.getData('gameObject').destroy();
            for (let i in chooseFishka)
                chooseFishka[i].destroy();
            //zvezda.destroy();
            });

        
    }
}
*/
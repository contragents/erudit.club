function parseDeskGlobal(newDesc) {
    
    
    newCells = newDesc;
    for (var i=0;i<=14;i++)
        for (var j=0;j<=14;j++) {
            cells[i][j][0] = false;//newCells[i][j][0];
            cells[i][j][1] = newCells[i][j][1];
            cells[i][j][2] = false;//newCells[i][j][2];
        }
        
    for (var k=400; k>=0; k--)
        if (k in fixedContainer) {
            fixedContainer[k].destroy();
            fixedContainer.splice(k,1);
        }
        
    for (var i=0;i<=14;i++)
        for (var j=0;j<=14;j++) 
            if (cells[i][j][1] !== false) {
                var fixFishka = getFishkaGlobal(cells[i][j][1], 1 , 1,this.game.scene.scenes[gameScene]).disableInteractive();
                findPlaceGlobal(fixFishka, 1, 1, i, j);
                fixFishka.setData('cellX',i);
                fixFishka.setData('cellY',j);
                fixedContainer.push(fixFishka);
                
            }
}
            
            
    
    
    /*//, container) {
            newCells = newDesc;
            console.log(newCells);
            for (var i=0;i<=14;i++)
                for (var j=0;j<=14;j++)
                    if (cells[i][j][1] !== newCells[i][j][1]) {
                        if (cells[i][j][1] !== false)
                            if (cells[i][j][1] === (newCells[i][j][1] + 999 + 1)) {
                                for (let k in fixedContainer)
                                    if ((fixedContainer[k].getData('cellX') === i) && (fixedContainer[k].getData('cellY') === j)) {
                                        fixedContainer[k].destroy();
                                        fixedContainer.splice(k,1);
                                        break;
                                    }
                                for (let k in container)
                                    if (container[k].getData('cellXY') == (i+'-'+j)) {
                                        container[k].setData('cellX') = i;
                                        container[k].setData('cellY') = j;
                                        cells[i][j][1] = newCells[i][j][1];
                                        fixedContainer.push(container[k]);
                                        container.splice(k,1);
                                        break;
                                    }
                            }
                            else {
                            console.log('!!!!!!!!!!'+i+' '+j);
                            for (var k=0;k<container.length;k++)
                                if ((container[k].getData('cellX')===i) && (container[k].getData('cellY')===j)){
                                
                                    container[k].setData('cellX',false);
                                    container[k].setData('cellY',false);
                                    container[k].x = 300;
                                    container[k].y = 300;
                                }
                                
                            cells[i][j][0] = newCells[i][j][0];
                            cells[i][j][1] = newCells[i][j][1];
                        }
                        else {
                            //console.log('+++++++++'+newCells[i][j][1]);
                            let newFishka = getFishkaGlobal(newCells[i][j][1], 1 , 1,this.game.scene.scenes[gameScene]).disableInteractive();
                            findPlaceGlobal(newFishka, 1, 1, i, j);
                            //console.log(newFishka.x);
                            fixedContainer.push(newFishka);
                            
                        }
                    }
                    else
                        for (var k=0;k<container.length;k++)
                            if ((container[k].getData('cellX')===i) && (container[k].getData('cellY')===j)) {
                                
                                    
                                    
                                        container[k].disableInteractive();
                                        fixedContainer.push(container[k]);
                                        container.splice(k,1);
                                        break;
                                    }   
                                
            for (var k=0;k<container.length;k++)
                if ((container[k].getData('lotokX') === false) && (container[k].getData('lotokY') === false)) {
                    contSlotXY = lotokFindSlotXY();
                    container[k].x = lotokGetX(contSlotXY[0],contSlotXY[1]);
                    container[k].y = lotokGetY(contSlotXY[0],contSlotXY[1]);
                    container[k].setData('lotokX',contSlotXY[0]);
                    container[k].setData('lotokY',contSlotXY[1]);
                    container[k].setInteractive();
                }
                
                           
        }
        */
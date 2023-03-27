
        this.input.on('dragstart', function (pointer, gameObject) {
            gameObject.depth = 100;
            let cellX = Math.round((gameObject.x-stepX-correctionX)/yacheikaWidth)-1;
            let cellY = Math.round((gameObject.y-stepY-correctionY)/yacheikaWidth)-1;
            if ( (cellX <= 14) && (cellX >= 0) && (cellY <= 14) && (cellY >=0) ) {
                cells[cellX][cellY][0] = false;
                cells[cellX][cellY][1] = false;
                cells[cellX][cellY][2] = false;
                cells[cellX][cellY][2] = DEFAULT_FISHKA_SET;

                gameObject.setData('cellX',false);
                gameObject.setData('cellY',false);
                
                gameObject.setData('oldCellX',cellX);
                gameObject.setData('oldCellY',cellY);
            }
            else {
                gameObject.setData('oldCellX',false);
                gameObject.setData('oldCellY',false);
            }
            
            if ((gameObject.getData('lotokX') !== false) && (gameObject.getData('lotokY') !== false)) {
                gameObject.setData('oldLotokX',gameObject.getData('lotokX'));
                gameObject.setData('oldLotokY',gameObject.getData('lotokY'));
                
                lotokFreeXY(gameObject.getData('lotokX'),gameObject.getData('lotokY'));
                
                gameObject.setData('lotokX',false);
                gameObject.setData('lotokY',false);
            }
            else {
                gameObject.setData('oldLotokX',false);
                gameObject.setData('oldLotokY',false);
            }
        });

        this.input.on('drag', function (pointer, gameObject, dragX, dragY) {
            gameObject.x = dragX;
            gameObject.y = dragY;
        });

        this.input.on('dragend', function (pointer, gameObject) {

            if (gameObject.x > stepX)
                if (gameObject.y < ground.height)
                    {
                        let cellX = Math.round((gameObject.x-stepX-correctionX)/yacheikaWidth)-1;
                        let cellY = Math.round((gameObject.y-stepY-correctionY)/yacheikaWidth)-1;
                        //findPlace(gameObject,gameObject.x,gameObject.y,cellX,cellY);
                        findPlaceGlobal(gameObject,gameObject.x,gameObject.y,cellX,cellY);
                        
                    }

            
            gameObject.depth = 1;
        });
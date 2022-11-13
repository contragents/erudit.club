function getFishkaGlobal(numLetter, X, Y, _this, draggable = true) {
            
            let fishka = _this.add.image(0, 0, 'fishka_empty');
            fishka.displayWidth = 32;
            fishka.displayHeight = 32;
            const correction=1.5;
            
            if (numLetter == 999) {
                let starLetter = _this.add.image(0, 0, 'zvezda');
                
                starLetter.displayWidth = fishka.displayWidth/correction;
                starLetter.scaleY = starLetter.scaleX;
                starLetter.setOrigin(.5, .5);
                starLetter.x=fishka.displayWidth-10-starLetter.displayWidth;
                starLetter.y=fishka.displayHeight-10-starLetter.displayHeight;
                
                var container = _this.add.container(X, Y, [fishka, starLetter]);
            }
            
            else if (numLetter > 999) {
                let atlasTexture = _this.textures.get('megaset');
                let frames = atlasTexture.getFrameNames();
                let starLetter = _this.add.image(0, 0, 'zvezda');
                
                starLetter.displayWidth = fishka.displayWidth/correction/2;
                starLetter.scaleY = starLetter.scaleX;
                starLetter.setOrigin(.5, .5);
                starLetter.x=fishka.displayWidth-13-starLetter.displayWidth;
                starLetter.y=fishka.displayHeight-27-starLetter.displayHeight;
                
                let testLetter = _this.add.image(0, 0, 'megaset', frames[numLetter - 999 - 1]);
            
                testLetter.displayWidth = fishka.displayWidth/correction;
                testLetter.scaleY = testLetter.scaleX;
                testLetter.setOrigin(.5, .5);
                testLetter.x=fishka.displayWidth-15-testLetter.displayWidth;
                testLetter.y=fishka.displayHeight-3-testLetter.displayHeight;
                
                var container = _this.add.container(X, Y, [fishka, testLetter, starLetter]);
            }
            
            else {
                let atlasTexture = _this.textures.get('megaset');
                let frames = atlasTexture.getFrameNames();
                let testLetter = _this.add.image(0, 0, 'megaset', frames[numLetter]);
            
                testLetter.displayWidth = fishka.displayWidth/correction;
                testLetter.scaleY = testLetter.scaleX;
                testLetter.setOrigin(.5, .5);
                testLetter.x=fishka.displayWidth-15-testLetter.displayWidth;
                testLetter.y=fishka.displayHeight-3-testLetter.displayHeight;
                if ((numLetter !== 25) && (numLetter !== 26)) {
                    let digitLetter = _this.add.image(0, 0, 'digits', letterPrices.get(numLetter));
                
                    digitLetter.displayWidth = fishka.displayWidth/correction/2;
                    digitLetter.scaleY = digitLetter.scaleX;
                    digitLetter.setOrigin(.5, .5);
                    digitLetter.x=fishka.displayWidth-13-digitLetter.displayWidth;
                    digitLetter.y=fishka.displayHeight-11-digitLetter.displayHeight;
                
                    var container = _this.add.container(X, Y, [fishka, testLetter, digitLetter]);
                }
                else {
                    let digit1Letter = _this.add.image(0, 0, 'digits', 1);
                    let digit2Letter = _this.add.image(0, 0, 'digits', letterPrices.get(numLetter) - 10);
                
                    digit1Letter.displayWidth = fishka.displayWidth/correction/2;
                    digit1Letter.scaleY = digit1Letter.scaleX;
                    digit1Letter.setOrigin(.5, .5);
                    digit1Letter.x=fishka.displayWidth-18-digit1Letter.displayWidth;
                    digit1Letter.y=fishka.displayHeight-27-digit1Letter.displayHeight;
                    
                    digit2Letter.displayWidth = fishka.displayWidth/correction/2;
                    digit2Letter.scaleY = digit1Letter.scaleX;
                    digit2Letter.setOrigin(.5, .5);
                    digit2Letter.x=fishka.displayWidth-13-digit1Letter.displayWidth;
                    digit2Letter.y=fishka.displayHeight-27-digit1Letter.displayHeight;
                
                    var container = _this.add.container(X, Y, [fishka, testLetter, digit1Letter, digit2Letter]);
                }
            }
            
            container.setSize(fishka.displayWidth,fishka.displayHeight);
            container.setData('letter',numLetter);
            container.setData('cellX',false);
            container.setData('cellY',false);
            container.setInteractive();
            if (draggable)
                _this.input.setDraggable(container);
            return container;
        }
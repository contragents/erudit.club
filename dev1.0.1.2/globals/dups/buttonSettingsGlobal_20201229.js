var buttons = {
    checkButton: {
        x: lotokX + buttonWidth/2 - lotokCellStep/2 +5,
        y: lotokY + lotokCellStep * lotokCapacityY,
        caption: 'проверить',
        width: buttonWidth,
        object: false,
        enableTint: 0x00ff00,
        enabled: {myTurn: 1, preMyTurn: 1, otherTurn: 1}
    },
    submitButton: {
        x: 0,
        y: 0,
        caption: 'отправить',
        width: buttonWidth,
        object: false,
        enableTint: 0x00ff00,
        enabled: {myTurn: 1}
        
    },
    shareButton: {
        x: 0,
        y: 0,
        caption: 'поделиться',
        width: buttonWidth,
        object: false,
        enableTint: 0x00ff00
    },
    newGameButton: {
        x: 0,
        y: 0,
        caption: 'новая#игра',
        width: buttonWidth,
        object: false
        
    },
    resetButton: {
        x: 0,
        y: 0,
        caption: 'стереть',
        width: buttonWidth*0.75,
        object: false,
        enableTint: 0x00ff00,
        enabled: {myTurn: 1, preMyTurn: 1, otherTurn: 1},
        clickEvent: function() {
            buttons['resetButton']['object'].on('pointerup', function () {
                if (bootBoxIsOpenedGlobal())
                    return;
                
                for (let k=container.length+10; k>=0; k--)
                    if (k in container) {
                        if ( (container[k].getData('lotokX') === false) && (container[k].getData('lotokY') === false)) {
                            
                            if ( (container[k].getData('cellX') !== false) && (container[k].getData('cellY') !== false) ) {
                                cells[container[k].getData('cellX')][container[k].getData('cellY')][0] = false;
                                cells[container[k].getData('cellX')][container[k].getData('cellY')][1] = false;
                            }
                            
                            container[k].setData('cellX',false);
                            container[k].setData('cellY',false);
                            /*if (container[k].getData('isTemporary') === true) {
                                for (let i =0; i<=14;i++)
                                    for (let j =0; j<=14;j++)
                                        cells[i][j][2] = false;
                                container[k].destroy();
                                container.splice(k,1);
                            }
                            else */{
                                container[k].setInteractive();
                                
                                placeToLotok(container[k]);
                                /*let contSlotXY = lotokFindSlotXY();
                                container[k].x = lotokGetX(contSlotXY[0],contSlotXY[1]);
                                container[k].y = lotokGetY(contSlotXY[0],contSlotXY[1]);
                                container[k].setData('lotokX',contSlotXY[0]);
                                container[k].setData('lotokY',contSlotXY[1]);
                                */
                            }
                        }
                        //else
                            if (container[k].getData('isTemporary') === true) {
                                for (let i =0; i<=14;i++)
                                    for (let j =0; j<=14;j++)
                                        cells[i][j][2] = false;
                                container[k].destroy();
                                container.splice(k,1);
                            }
                    }
                        
            });
        }
    },
    changeButton: {
        x: 0,
        y: 0,
        caption: 'поменять',
        width: buttonWidth*0.75,
        object: false,
        enableTint: 0x00ff00,
        enabled: {myTurn: 1},
        clickEvent: function() {
            buttons['changeButton']['object'].on('pointerup', function () {
                if (bootBoxIsOpenedGlobal())
                    return;
                
                let formHeader = '<form id="myForm" class="form-horizontal">';
                let formFooter = '</div></form>';
                var formInner = '<div class="form-group">';
                for (let k in container)
                    formInner += '<div style="display:inline-block;"><input type="checkbox" style="opacity:80%; transform: scale(2);" id="fishka_'+k+'_'+container[k].getData('letter')+'" name="fishka_'+k+'_'+container[k].getData('letter')+'"'+ (container[k].getData('letter') < 999 ? 'checked' : '')+'><label for="fishka_'+k+'_'+container[k].getData('letter')+'"><div style="margin-left:-12px;margin-right:13px;" class="letter_'+(container[k].getData('letter') < 999 ? container[k].getData('letter') : '999" title="Зачем?')+'" onclick="$(\'#fishka_'+k+'_'+container[k].getData('letter')+'\').trigger(\'click\');return false;"></div></label></div>';
                disableButtons();
                dialog = bootbox.confirm({
                    message: 'Выберите фишки для замены<br /><br />'+formHeader+formInner+formFooter,
                    locale: 'ru',
                    callback: function (result) {
                        if (result)
                            changeFishkiGlobal($(".bootbox-body #myForm").serialize());
                    }
                });
                enableButtons();
            })
        }
    },
    chatButton: {
        x: 0,
        y: 0,
        caption: 'чат',
        width: buttonWidth*0.4,
        object: false,
        clickEvent: function() {
            buttons['chatButton']['object'].on('pointerup', function () {
                if (bootBoxIsOpenedGlobal())
                    return;
                dialog = bootbox.alert({
                    message: 'Поддержка и чат игроков в <a target="_blank" title="Вступить в группу" href="'+(gameWidth < gameHeight ? 'https://t.me/eruditclub' : 'https://web.telegram.org/#/im?p=@eruditclub')+'">Telegram</a>',
                    locale: 'ru',
                });
                
            })
        },
        preCalc: function() {
            this.x = buttons['changeButton']['x'] - buttons['changeButton']['object'].width/2 + buttons['changeButton']['object'].width*this.width/buttons['changeButton']['width']/2;
            this.y = buttons['changeButton']['y'] + buttonStepY;
        }
    },
    logButton: {
        x: 0,
        y: 0,
        caption: 'лог',
        width: buttonWidth*0.3,
        object: false,
        clickEvent: function() {
            this['object'].on('pointerup', function () {
                if (bootBoxIsOpenedGlobal())
                    return;
                    
                    
                let message = '<br /><ul style="margin-left:-30px;margin-right:-5px;">';
                let i = 0;
                for (k in gameLog) {
                    if (i >= 10 ) break;
                    message = message + '<li>'+gameLog[k] + "</li>";
                    i++;
                }
                message = message + '</ul>';
                if (i == 0)
                    message = message + 'Событий пока нет';
                
                dialog = bootbox.alert({
                    message: message,
                    size: 'small'
                });
                return;
            })
        },
        preCalc: function() {
            this.x = buttons['changeButton']['x'] + buttons['changeButton']['object'].width/2 - buttons['changeButton']['object'].width*this.width/buttons['changeButton']['width']/2;
            this.y = buttons['chatButton']['y'];
        }
    },
    playersButton: {
        x: 0,
        y: 0,
        caption: 'игроки',
        width: buttonWidth*0.75,
        object: false,
        clickEvent: function() {
            buttons['playersButton']['object'].on('pointerup', function () {
                if (bootBoxIsOpenedGlobal())
                    return;
            
                    dialog = bootbox.alert({
                        message: 'Данная функция находится в процессе разработки',
                        locale: 'ru',
                    });
                
               })
        
        },
        preCalc: function() {
            this.x = buttons['changeButton']['x'];
            this.y = buttons['newGameButton']['y'];
        }
    }
};

buttons['submitButton']['x'] = buttons['checkButton']['x'];
buttons['submitButton']['y'] = buttons['checkButton']['y'] + buttonStepY;

buttons['shareButton']['x'] = buttons['submitButton']['x'];
buttons['shareButton']['y'] = buttons['submitButton']['y'] + buttonStepY;

buttons['newGameButton']['x'] = buttons['shareButton']['x'];
buttons['newGameButton']['y'] = buttons['shareButton']['y'] + buttonStepY;

buttons['resetButton']['x'] = buttons['checkButton']['x'] + buttons['checkButton']['width'] + buttonStepY/2 + (knopkiWidth - (buttons['checkButton']['x'] + buttons['checkButton']['width'] +buttons['resetButton']['width']) )/2;
buttons['resetButton']['y'] = buttons['checkButton']['y'];

buttons['changeButton']['x'] = buttons['resetButton']['x'];
buttons['changeButton']['y'] = buttons['resetButton']['y'] + buttonStepY;

//buttons['chatButton']['x'] = buttons['changeButton']['x'];
//buttons['chatButton']['y'] = buttons['changeButton']['y'] + buttonStepY;

//buttons['logButton']['x'] = buttons['chatButton']['object'].x + buttons['chatButton']['object'].width;
//buttons['logButton']['y'] = buttons['chatButton']['y'];




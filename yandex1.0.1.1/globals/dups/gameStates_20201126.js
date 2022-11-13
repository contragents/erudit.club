var gameStates = {
    desync: {
        1:'waiting',2:'done',
        refresh: 10,
        message:'Синхронизация с сервером...'
    },
    noGame: {
        1:'waiting',2:'done'
    },
    chooseGame: {
        1:'choosing',2:'done'
    },
    initGame: {
        1:'waiting',2:'done',
        /*waiting:function(){
            
            ajaxGetGlobal('game_starter.php','govno',gameStates);
            
        },*/
        action:function(data){
            submitButton.disableInteractive();
            submitButton.getRandom(0,0).tint = 0x000000;
        },/*
        waitingCallback:function(data){
            data = JSON.parse(data);
            console.log(data);
            if (gameState != data['gameState']) {
                gameState = data['gameState'];
                alert(gameStates[gameState]['message']);
                gameSubState = gameStates[gameState]['1'];
            }
            if (gameState == 'startGame') {
                submitButton.setInteractive();
                submitButton.getRandom(0,0).tint = 0x00ff00;
            }
            if (gameState == 'initGame') {
                submitButton.disableInteractive();
                submitButton.getRandom(0,0).tint = 0x000000;
            }
        },*/
        message:'Подбор игры - ожидайте',
        refresh: 10
    },
    startGame: {
        1:'waiting',2:'done',
        message:'Игра начата!',
        refresh: 10,
        action:function(data){
            submitButton.disableInteractive();
            submitButton.getRandom(0,0).tint = 0x000000;
            if ('fishki' in data)
                placeFishki(data['fishki']);
            if ("score" in data)
                ochki.text = data['score'];
            userScores();
        },
        from_initGame:function(){
            while (fixedContainer.length)
                fixedContainer.pop().destroy();
            cells = [];
            newCells = [];
            initCellsGlobal();
        }
    },
    myTurn: {
        1: 'thinking',2: 'checking',3: 'submiting',4: 'done',
        message:'Ваш ход!',
        refresh: 15,
        action:function(data){
            if ("desk" in data)
                parseDeskGlobal(data['desk']);//, container);
            if ("score" in data)
                ochki.text = data['score'];
            userScores();
            /*if ('fishki' in data)
                placeFishki(data['fishki']);
            */
            submitButton.setInteractive();
            submitButton.getRandom(0,0).tint = 0x00ff00;
        },
        from_initGame:function(data){
            gameStates['startGame']['from_initGame']();
            if ('fishki' in data)
                placeFishki(data['fishki']);
        },
        from_noGame:function(data){
            if ('fishki' in data) 
                placeFishki(data['fishki']);
        },
        from_gameResults:function(){
            gameStates['startGame']['from_initGame']();
        },
        from_preMyTurn:function(){
            
            //for (var k=0;k<container.length+10;k++)
            for (let k=container.length+10;k>=0;k--)
                if (k in container) {
                    if ( (container[k].getData('lotokX') === false) && (container[k].getData('lotokY') === false) ) 
                        if (container[k].getData('isTemporary') !== true) {
                            placeToLotok(container[k]);
                            /*let contSlotXY = lotokFindSlotXY();
                            container[k].x = lotokGetX(contSlotXY[0],contSlotXY[1]);
                            container[k].y = lotokGetY(contSlotXY[0],contSlotXY[1]);
                            container[k].setData('lotokX',contSlotXY[0]);
                            container[k].setData('lotokY',contSlotXY[1]);
                            container[k].setData('cellX',false);
                            container[k].setData('cellY',false);
                            */
                            container[k].setInteractive();
                    }
                    
                    if (container[k].getData('isTemporary') === true) {
                        container[k].destroy();
                        container.splice(k,1);
                    }
                }
                
            gameStates['startGame']['from_initGame']();
        }
    },
    preMyTurn: {
        1: 'waiting',2: 'done',
        message: 'Приготовьтесь - Ваш ход следующий!',
        refresh: 5,
        action:function(data){
            if ("desk" in data)
                parseDeskGlobal(data['desk']);//, container);
            if ("score" in data)
                ochki.text = data['score'];
            userScores();
            submitButton.disableInteractive();
            submitButton.getRandom(0,0).tint = 0x000000;
        },
        from_initGame:function(data){
            gameStates['startGame']['from_initGame']();
            if ('fishki' in data)
                placeFishki(data['fishki']);
        },
        from_noGame:function(data){
            if ('fishki' in data)
                placeFishki(data['fishki']);
        },
        from_myTurn:function(data){
            if ('fishki' in data)
                placeFishki(data['fishki']);
            
        },
        from_otherTurn:function(data){
            if ('fishki' in data)
                placeFishki(data['fishki']);
        },
        from_gameResults:function(){
            gameStates['startGame']['from_initGame']();
        }
    },
    otherTurn: {
        1:'waiting',2:'done',message: 'Отдохните - Ваш ход через один',
        refresh: 5,
        action:function(data){
            if ("desk" in data)
                parseDeskGlobal(data['desk']);//, container);
            if ("score" in data)
                ochki.text = data['score'];
            userScores();
            if ('fishki' in data)
                placeFishki(data['fishki']);
            submitButton.disableInteractive();
            submitButton.getRandom(0,0).tint = 0x000000;
        },
        from_initGame:function(data){
            gameStates['startGame']['from_initGame']();
            
        },
        
        from_gameResults:function(){
            gameStates['startGame']['from_initGame']();
        }
    },
    gameResults: {
        1:'waiting',2:'done',
        messageFunction: function(mes) {
            return mes;
        },
        refresh: 30,
        action:function(data){
            if ("desk" in data)
                parseDeskGlobal(data['desk']);
            if ("score" in data)
                ochki.text = data['score'];
            userScores();
            
        }
    },
    afterSubmit: {refresh: 1}
}

var gameState = 'noGame';
var gameSubState = 'waiting';
var gameOldState = '';

function commonCallback(data1) {
    data = JSON.parse(data1);
    gameOldState = gameState;
    gameOldSubState = gameSubState;
    
    if (gameState != data['gameState']) 
        gameState = data['gameState'];
    
    if ('gameSubState' in data) 
            gameSubState = data['gameSubState'];
        else
            gameSubState = gameStates[gameState]['1'];
    
    
    console.log(gameOldState+'->'+gameState);

    
    if ( (gameOldState != gameState) || (gameOldSubState != gameSubState) ) {
        if (dialog)
            dialog.modal('hide');
        if ('comments' in data)
            if (data['comments'] !== null)
                if ('messageFunction' in gameStates[gameState])
                    dialog = bootbox.alert({
                        message: gameStates[gameState]['messageFunction'](data['comments']),
                        size: 'small'
                    });
                 else
                     dialog = bootbox.alert({
                        message: data['comments'],
                        size: 'small'
                    });
        else
            if ('message' in gameStates[gameState])
                dialog = bootbox.alert({
                    message: gameStates[gameState]['message'],
                    size: 'small'
                });
        
        enableButtons();
        
        if ('from_'+gameOldState in gameStates[gameState])
            gameStates[gameState]['from_'+gameOldState](data);
            
        if ('action' in gameStates[gameState])
            gameStates[gameState]['action'](data);
    }
    
    if ('timeLeft' in data) {
        vremia.text = data['timeLeft'];
        vremiaMinutes = data['minutesLeft'];
        vremiaSeconds = data['secondsLeft'];
    }
	
	if ('log' in data)
		for (k in data['log'])
			gameLog.unshift(data['log'][k]);
}
function userScores() {
    if ("score_arr" in data) {
        if (ochki_arr === false) {
                    ochki_arr = [];
                    for (k in data['score_arr'])
                        ochki_arr[k] = window.game.scene.scenes[gameScene].add.text(0, 0, '', {color:'black'});
                }
                ochki.text = '';
                let x = ochki.x; 
                for (let k in data['score_arr']) {
                    if (k == data['yourUserNum']) 
                            ochki_arr[k].text = 'Ваши очки:'+data['score_arr'][k];
                    else 
                            ochki_arr[k].text = data['userNames'][k]+':'+data['score_arr'][k];
                    if (k == data['activeUser'])
                        ochki_arr[k].setColor('green');
                    else
                        ochki_arr[k].setColor('black');
                    ochki_arr[k].x = x;
                    ochki_arr[k].y = ochki.y;
                    ochki_arr[k].setFontSize(vremiaFontSizeDefault);
                    ochki_arr[k].visible = true;
                    x = ochki_arr[k].x + ochki_arr[k].width + 9 - Math.floor(data['score_arr'][k] / 100);

                }
            }
}
var queryNumber = 1;
var lastQueryTime = 0;

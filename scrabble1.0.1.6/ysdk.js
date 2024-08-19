//
var player = null;
var YaSDK = null;
var timerId = null;
var uniqID = false;
var tryCount = 5;


    timerId = setInterval(function() { 
        if ( (uniqID !== false) || (tryCount-- == 0) ) {
            clearInterval(timerId);
            return;
        }
        YaSDK = null;
        player = null;
        if (typeof YaGames != 'undefined')
            YaGames
                .init({
                    adv: {
                        onAdvClose: wasShown => {
                              console.info('adv closed!');
                        }
                    },
                    screen : {
                        orientation : {
                            value : (gameHeight > gameWidth ? 'portrait' : 'landscape')
                        }
                    }
                })
                .then(ysdk => {
                    YaSDK = ysdk;
                    YaSDK.getPlayer({ scopes: false })
                    .then(_player => {
                        player = _player;
                        uniqID = player.getUniqueID();
                    }).catch(err => {
                        console.log('USER_NOT_AUTHORIZED');
                    });
                });
         }, 3000); 
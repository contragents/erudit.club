//
var yaPlayer = null;
var uniqID = false;

if (isYandexAppGlobal()) {
    var tryCount = 5;
    var timerId = null;

    timerId = setInterval(function () {
        if ((uniqID !== false) || (tryCount-- == 0)) {
            clearInterval(timerId);
            return;
        }
        yaPlayer = null;
        if (typeof YaGames != 'undefined')
            YaGames
                .init({
                    adv: {
                        onAdvClose: wasShown => {
                            console.info('adv closed!');
                        }
                    },
                    screen: {
                        orientation: {
                            value: (gameHeight > gameWidth ? 'portrait' : 'landscape')
                        }
                    }
                })
                .then(ysdk => {
                    window.ysdk = ysdk;

                    window.ysdk.getPlayer({scopes: false})
                        .then(_player => {
                            yaPlayer = _player;
                            uniqID = yaPlayer.getUniqueID();
                        }).catch(err => {
                        console.log('USER_NOT_AUTHORIZED');
                    });
                });
    }, 3000);
}
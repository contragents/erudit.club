//
var yaPlayer = null;
var uniqID = false;

if (isYandexAppGlobal()) {
    var tryCount = 5;
    var timerId = null;

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
                /*if(!cookieStored) {
                    getLocalStorageValue('<?= Cookie::COOKIE_NAME ?>');
                }*/
            });
}

function setLocalStorageValue(key, value) {
    useLocalStorage = true;

    if (isYandexAppGlobal() && window.ysdk != 'undefined') {
        window.ysdk.getStorage()
            .then(safeStorage => Object.defineProperty(window, 'localStorage',
                {get: () => safeStorage}))
            .then(() => {
                localStorage.setItem(key, value);
                if (localStorage.getItem(key) == value) {
                    cookieStored = value;
                    useYandexStorage = true;
                } else {
                    cookieStored = FALL_BACK_COOKIE;
                }
            });
    }
}

function getLocalStorageValue(key) {
    if (isYandexAppGlobal() && window.ysdk != 'undefined') {
        window.ysdk.getStorage()
            .then(safeStorage => Object.defineProperty(window, 'localStorage',
                {get: () => safeStorage}))
            .then(() => {
                cookieStored = localStorage.getItem(key) ? localStorage.getItem(key) : FALL_BACK_COOKIE;
                useLocalStorage = true;
                useYandexStorage = true;
            });
    } else {
        useLocalStorage = true;
        cookieStored = FALL_BACK_COOKIE;
    }
}

function showStickyBannerYandex() {
    if (isYandexAppGlobal() && window.ysdk != 'undefined') {
        window.ysdk.adv.getBannerAdvStatus().then(({stickyAdvIsShowing, reason}) => {
            if (stickyAdvIsShowing) {
                // Реклама показывается
            } else if (reason) {
                // Реклама не показывается.
                console.log(reason)
            } else {
                // Реклама не показывается.
                window.ysdk.adv.showBannerAdv()
            }
        })
    }
}

function hideStickyBannerYandex() {
    if (isYandexAppGlobal() && window.ysdk != 'undefined') {
        window.ysdk.adv.getBannerAdvStatus().then(({stickyAdvIsShowing, reason}) => {
            if (stickyAdvIsShowing) {
                // Реклама показывается
                window.ysdk.adv.hideBannerAdv();
            } else if (reason) {
                // Реклама не показывается.
                console.log(reason)
            } else {
                // Реклама не показывается
            }
        })
    }
}
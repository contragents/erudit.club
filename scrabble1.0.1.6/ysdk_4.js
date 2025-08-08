//
var yaPlayer = false;
var uniqID = false;
var ysdk = false;

if (isYandexAppGlobal() && typeof YaGames != 'undefined') {
    YaGames
        .init()
        .then(_ysdk => {
            ysdk = _ysdk;

            ysdk.getPlayer({scopes: false})
                .then(_player => {
                    yaPlayer = _player;
                    uniqID = yaPlayer.getUniqueID();
                    console.log('USER ID:', uniqID);
                }).catch(err => {
                console.log('USER_NOT_AUTHORIZED');
            });

            ysdk.on('game_api_pause', pauseCallback);
            ysdk.on('game_api_resume', resumeCallback);

            // CLUB-440 #2
            let nothing = ysdk.environment.i18n.lang.toUpperCase();
        });
}

function setLocalStorageValue(key, value) {
    useLocalStorage = true;

    if (isYandexAppGlobal() && !!ysdk) {
        ysdk.getStorage()
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
    if (isYandexAppGlobal() && !!ysdk) {
        ysdk.getStorage()
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
    if (isYandexAppGlobal() && !!ysdk) {
        ysdk.adv.getBannerAdvStatus().then(({stickyAdvIsShowing, reason}) => {
            if (stickyAdvIsShowing) {
                // Реклама показывается
            } else if (reason) {
                // Реклама не показывается.
                console.log(reason)
            } else {
                // Реклама не показывается.
                ysdk.adv.showBannerAdv()
            }
        })
    }
}

function hideStickyBannerYandex() {
    if (isYandexAppGlobal() && !!ysdk) {
        ysdk.adv.getBannerAdvStatus().then(({stickyAdvIsShowing, reason}) => {
            if (stickyAdvIsShowing) {
                // Реклама показывается
                ysdk.adv.hideBannerAdv();
            } else if (reason) {
                // Реклама не показывается.
                console.log(reason)
            } else {
                // Реклама не показывается
            }
        })
    }
}

async function reportGameIsReadyYandex() {
    if (!isYandexAppGlobal()) {
        return;
    }

    if(typeof YaGames == 'undefined') {
        return;
    }

    if (!!ysdk) {
        ysdk.features.LoadingAPI?.ready();
    } else {
        setTimeout(() => reportGameIsReadyYandex(), 500);
    }
}

function reportGameStartYandex() {
    if (!isYandexAppGlobal()) {
        return;
    }

    if(typeof YaGames == 'undefined') {
        return;
    }

    if (!!ysdk) {
        ysdk.features.GameplayAPI?.start();
        hideStickyBannerYandex();
    } else {
        setTimeout(() => reportGameStartYandex(), 500);
    }
}

function reportGameStopYandex() {
    if (isYandexAppGlobal() && !!ysdk) {
        ysdk.features.GameplayAPI?.stop();
        showStickyBannerYandex();
    }
}

function reportVisibilityChangeYandex() {
    if (isYandexAppGlobal() && !!ysdk) {
        if(pageActive === 'hidden') {
            ysdk.features.GameplayAPI?.stop();
        } else {
            if ([MY_TURN_STATE, PRE_MY_TURN_STATE, OTHER_TURN_STATE, INIT_GAME_STATE, GAME_RESULTS_STATE].indexOf(gameState) >= 0) {
                ysdk.features.GameplayAPI?.start();
            }
        }
    }
}

const pauseCallback = () => {
    pageActive = 'hidden';
    onVisibilityChange();
    console.log('GAME PAUSED');
};

const resumeCallback = () => {
    pageActive = 'visible';
    onVisibilityChange();
    console.log('GAME RESUMED');

    hideStickyBannerYandex();
};

function sessionStorageSet(key, value) {
    try {
        window.sessionStorage.setItem('__telegram__' + key, JSON.stringify(value));
        return true;
    } catch (e) {}
    return false;
}

function sessionStorageGet(key) {
    try {
        return JSON.parse(window.sessionStorage.getItem('__telegram__' + key));
    } catch (e) {}
    return null;
}

var appTgVersion = 7.7;

var initParams = sessionStorageGet('initParams');
if (initParams) {
    if (!initParams.tgWebAppVersion) {
        initParams.tgWebAppVersion = appTgVersion;
    }
} else {
    initParams = {
        tgWebAppVersion: appTgVersion
    };
}

sessionStorageSet('initParams', initParams);


var TG = window.Telegram.WebApp;
TG.disableVerticalSwipes();

var Utils = window.Telegram.Utils;
var WebView = window.Telegram.WebView;
var initParams = WebView.initParams;
var isIframe = WebView.isIframe;

WebView.postEvent('web_app_expand');

var webAppInitData = '';
var webAppInitDataUnsafe = {};

if (initParams.tgWebAppData && initParams.tgWebAppData.length) {
    webAppInitData = initParams.tgWebAppData;
    webAppInitDataUnsafe = Utils.urlParseQueryString(webAppInitData);
    for (var key in webAppInitDataUnsafe) {
        var val = webAppInitDataUnsafe[key];
        try {
            if (val.substr(0, 1) == '{' && val.substr(-1) == '}' ||
                val.substr(0, 1) == '[' && val.substr(-1) == ']') {
                webAppInitDataUnsafe[key] = JSON.parse(val);
            }
        } catch (e) {}
    }
}

function isTgBot() {
    return ('user' in webAppInitDataUnsafe) && ('id' in webAppInitDataUnsafe.user);
}

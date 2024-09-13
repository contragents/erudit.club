
function shareTgGlobal() {
    if (!commonId && !isTgBot()) {
        return;
    }

    botUrl = GAME_BOT_URL + '/?start=inv_'
    + (commonId ? commonId : ('id_' + webAppInitDataUnsafe.user.id));

    shareUrl = '/share/url?url='
        + encodeURIComponent(botUrl)
        + '&text=' + encodeURIComponent(INVITE_FRIEND_PROMPT);

    WebView.postEvent(
        'web_app_open_tg_link',
        false,
        {path_full: shareUrl,}
    );
}

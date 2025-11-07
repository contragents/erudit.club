
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

function sudokuGlobal() {
    if (!commonId && !isTgBot()) {
        return;
    }

    window.location.href = isTgBot()
        ? ('https://t.me/' + SUDOKU_GAME_BOT_URL)
        : 'https://skipbo.eth.box/sudoku/?common_id=' + commonId + '&common_id_hash=' + commonIdHash;
}

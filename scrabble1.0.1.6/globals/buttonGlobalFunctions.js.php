//
function submitButtonFunction() {
    if (!submitButtonActive()) {
        return;
    }

    if (bootBoxIsOpenedGlobal()) {
        return;
    }

    buttons['submitButton']['svgObject'].disableInteractive();
    buttons['submitButton']['svgObject'].bringToTop(buttons['submitButton']['svgObject'].getByName('submitButton' + 'Inactive'));

    setTimeout(function () {
        fetchGlobal(SUBMIT_SCRIPT, 'cells', cells)
            .then((data) => {
                if ('http_status' in data && (data['http_status'] === BAD_REQUEST || data['http_status'] === PAGE_NOT_FOUND)) {
                    buttons['submitButton']['svgObject'].setInteractive();
                    buttons['submitButton']['svgObject'].bringToTop(buttons['submitButton']['svgObject'].getByName('submitButton' + OTJAT_MODE));
                    dialog = bootbox.alert({
                        message: ('message' in data && data['message'] !== '')
                            ? (data['message'] + '<br /> <?= T::S('Try sending again') ?>')
                            : '<strong><?= T::S('Error connecting to server!') ?><br /> <?= T::S('Try sending again') ?></strong>',
                        size: 'small'
                    });
                } else {
                    gameState = 'afterSubmit';

                    if ('desk' in data) {
                        parseDeskGlobal(data.desk);
                    }

                    if ('fishki' in data) {
                        placeFishki(data.fishki);
                    }

                    if ('gameState' in data) {
                        commonCallback(data);
                    }
                }
            });
    }, 100);
}


function checkButtonFunction() {
    if (!checkButtonActive()) {
        return;
    }

    buttons['checkButton']['svgObject'].disableInteractive();
    buttons['checkButton']['svgObject'].bringToTop(buttons['checkButton']['svgObject'].getByName('checkButton' + 'Inactive'));

    setTimeout(function () {
        fetchGlobal(WORD_CHECKER_SCRIPT, 'cells', cells)
            .then((data) => {
                if (data == '')
                    var responseText = '<?= T::S('You haven`t composed a single word!') ?>';
                else
                    var responseText = data;
                dialog = bootbox.alert({
                    message: responseText,
                    size: 'small'
                });

                buttons['checkButton']['svgObject'].setInteractive();
                buttons['checkButton']['svgObject'].bringToTop(buttons['checkButton']['svgObject'].getByName('checkButton' + OTJAT_MODE));
            });
    }, 100);
}

function newGameButtonFunction(ignoreDialog = false) {
    if (!ignoreDialog && bootBoxIsOpenedGlobal()) {
        return;
    }

    buttons['newGameButton']['svgObject'].disableInteractive();

    if (gameState == 'myTurn' || gameState == 'preMyTurn' || gameState == 'otherTurn' || gameState == 'startGame') {
        dialog = bootbox.dialog({
            // title: 'Требуется подтверждение',
            message: '<?= T::S('You will lose if you quit the game! CONTINUE?') ?>',
            size: 'medium',
            // onEscape: false,
            closeButton: true,
            buttons: {
                cancel: {
                    label: '<?= T::S('Cancel') ?>',
                    className: 'btn-outline-success',
                    callback: function () {
                        return true;
                    }
                },
                confirm: {
                    label: '<?= T::S('Confirm') ?>',
                    className: 'btn-primary',
                    callback: function () {
                        requestToServerEnabled = true;
                        fetchGlobal(NEW_GAME_SCRIPT, '', 'gameState=' + gameState)
                            .then((data) => {
                                document.location.reload(true);
                            });

                        buttons['newGameButton']['svgObject'].setInteractive();

                        return true;
                    }
                },
                invite: {
                    label: '<?= T::S('Revenge!') ?>',
                    className: 'btn-info',
                    callback: function () {
                        setTimeout(function () {
                            fetchGlobal(INVITE_SCRIPT, '', 'gameState=' + gameState)
                                .then((dataInvite) => {
                                    let responseText = '<?= T::S('Request rejected') ?>';
                                    if (dataInvite != '') {
                                        responseText = dataInvite['message'];
                                    }

                                    dialogResponse = bootbox.alert({
                                        message: responseText,
                                        locale: 'ru',
                                        size: 'small',
                                        callback: function () {
                                            dialogResponse.modal('hide');
                                            gameStates['gameResults']['results'](dataInvite);
                                        }
                                    });

                                    setTimeout(
                                        function () {
                                            dialogResponse.find(".bootbox-close-button").trigger("click");
                                        }
                                        , 2000
                                    );

                                    buttons['newGameButton']['svgObject'].setInteractive();

                                });
                        }, 100);

                        return true;
                    }
                },
            }
        });
    } else {
        buttons['newGameButton']['svgObject'].bringToTop(buttons['newGameButton']['svgObject'].getByName('newGameButton' + 'Inactive'));

        fetchGlobal(NEW_GAME_SCRIPT, '', 'gameState=' + gameState)
            .then((data) => {
                document.location.reload(true);
            });
    }
};

function resetButtonFunction(ignoreBootBox = false) {
    if (ignoreBootBox === false)
        if (bootBoxIsOpenedGlobal())
            return;

    for (let k = container.length + 10; k >= 0; k--)
        if (k in container) {
            if ((container[k].getData('lotokX') === false) && (container[k].getData('lotokY') === false)) {

                if ((container[k].getData('cellX') !== false) && (container[k].getData('cellY') !== false)) {
                    cells[container[k].getData('cellX')][container[k].getData('cellY')][0] = false;
                    cells[container[k].getData('cellX')][container[k].getData('cellY')][1] = false;
                    cells[container[k].getData('cellX')][container[k].getData('cellY')][3] = DEFAULT_FISHKA_SET;
                }

                container[k].setData('cellX', false);
                container[k].setData('cellY', false);
                container[k].setInteractive();
                placeToLotok(container[k]);
            }

            if (container[k].getData('isTemporary') === true) {
                for (let i = 0; i <= 14; i++)
                    for (let j = 0; j <= 14; j++)
                        cells[i][j][2] = false;
                container[k].destroy();
                container.splice(k, 1);
            }
        }

};

function changeButtonFunction() {
    if (bootBoxIsOpenedGlobal())
        return;

    canOpenDialog = false;
    canCloseDialog = false;

    let formHeader = '<form id="myForm" class="form-horizontal">';
    let formFooter = '</div></form>';
    var formInner = '<div class="form-group">';
    var zvezdaStyle = '999" title="Зачем?';
    for (let k in container)
        formInner += '<div style="display:inline-block;"><input type="checkbox" style="opacity:80%; transform: scale(2);" id="fishka_'
            +
            k
            +
            '_'
            + container[k].getData('letter')
            + '" name="fishka_'
            + k
            + '_'
            + container[k].getData('letter')
            + '"'
            + (container[k].getData('letter') < 999 ? 'checked' : '')
            + '><label for="fishka_'
            + k
            + '_'
            + container[k].getData('letter')
            + '"><div style="margin-left:-12px;margin-right:13px;' + (container[k].getData('letter') > 33 && container[k].getData('letter') < 999 ? genDivGlobal(container[k].getData('letter'), true) : '')
            + '" class="letter_'
            + (container[k].getData('letter') < 999 ? container[k].getData('letter') : zvezdaStyle)
            + '" onclick="$(\'#fishka_'
            + k
            + '_'
            + container[k].getData('letter')
            + '\').trigger(\'click\');return false;"></div></label></div>';

    dialog = bootbox.confirm({
        message: 'Выберите фишки для замены<br /><br />' + formHeader + formInner + formFooter,
        locale: 'ru',
        callback: function (result) {
            canOpenDialog = true;
            canCloseDialog = true;

            if (result)
                changeFishkiGlobal($(".bootbox-body #myForm").serialize());
        }
    });

};

function chatButtonFunction() {
    if (bootBoxIsOpenedGlobal())
        return;

    canOpenDialog = false;
    canCloseDialog = false;
    let msgSpan = '<span id="msg_span">';
    let message = '<ul style="margin-left:-30px;margin-right:-5px;">' + msgSpan + '</span>';
    let i = 0;
    for (k in chatLog) {
        if (i >= 10) break;
        message = message + '<li>' + chatLog[k] + "</li>";
        i++;
    }


    let noMsgSpan = '<span id="no_msg_span">';
    if (i == 0) {
        message += noMsgSpan + '<?= T::S('No messages yet') ?>' + '</span>';
    } else {
        message += noMsgSpan + '</span>';
    }
    message = message + '</ul>';
    let radioButtons = message + '';

    let isSelectedPlaced = false;
    if (ochki_arr.length > 1) {
        radioButtons += '<div class="form-check form-check-inline"><input class="form-check-input" type="radio" id="chatall" name="chatTo" value="all" checked> <label class="form-check-label" for="chatall"><?= T::S('For everyone') ?></label></div>';
        isSelectedPlaced = true;
    }

    for (k in ochki_arr) {
        if (k != myUserNum) {
            radioButtons += '<div class="form-check form-check-inline"><input class="form-check-input" type="radio" id="to_' + (k == 0 ? '0' : k) + '" name="chatTo" value="' + (k == 0 ? '0' : k) + '" ' + (isSelectedPlaced ? '' : ' checked ') + '> <label class="form-check-label" for="to_' + (k == 0 ? '0' : k) + '"><?= T::S('To Player') ?>' + (parseInt(k, 10) + 1) + '</label></div>';
            isSelectedPlaced = true;
        }
    }

    radioButtons += '<div class="form-check form-check-inline"><input class="form-check-input" type="radio" id="to_words" name="chatTo" value="words" ' + (isSelectedPlaced ? '' : ' checked ') + '> <label class="form-check-label" for="to_words"><?= T::S('Word matching') ?></label></div>';

    let textInput = '<div class="input-group input-group-lg">  <div class="input-group-prepend"></div>  <input type="text" id="chattext" class="form-control" name="messageText"></div>';

    dialog = bootbox.dialog({
        title: '</h5>'
            + (
                !isYandexAppGlobal()
                    ? (
                        '<h6><?= T::S('Player support and chat at') ?> <a target="_blank" title="<?= T::S('Join group') ?>" href="'
                        + (gameWidth < gameHeight ? 'https://t.me/eruditclub' : 'https://web.telegram.org/#/im?p=@eruditclub')
                        + '">Telegram</a> </h6>'
                    )
                    : ''
            )
            + '<h5><?= T::S('Send an in-game message') ?>',
        message: '<form onsubmit="return false" id="myChatForm">' + radioButtons + textInput + '</form>',
        locale: 'ru',
        size: 'large',
        closeButton: false,
        buttons: {
            confirm: {
                label: '<?= T::S('Send') ?>',
                className: 'btn-primary',
                callback: function () {
                    canOpenDialog = true;
                    canCloseDialog = true;

                    buttons['chatButton']['svgObject'].bringToTop(buttons['chatButton']['svgObject'].getByName('chatButton' + OTJAT_MODE));
                    buttons['chatButton']['svgObject'].getByName('chatButton' + ALARM_MODE).setData('alarm', false);

                    if ($(".bootbox-body #chattext").val() != '') {

                        buttons['chatButton']['svgObject'].disableInteractive();
                        buttons['chatButton']['svgObject'].bringToTop(buttons['chatButton']['svgObject'].getByName('chatButton' + 'Inactive'));

                        fetchGlobal(CHAT_SCRIPT, '', $(".bootbox-body #myChatForm").serialize())
                            .then((data) => {
                                    if (data == '')
                                        var responseText = '<?= T::S('Error') ?>';
                                    else {
                                        var responseText = data['message'];

                                        if (data['message'] === '<?= T::S('Message sent') ?>') {
                                            $('#no_msg_span').html('');
                                            $('#msg_span').html('<li>' + $('#chattext').val() + '</li>' + $('#msg_span').html());
                                        }

                                        $('#chattext').val('');
                                    }

                                    if (data['message'] !== '<?= T::S('Message sent') ?>') {
                                        if (data['gameState'] == 'wordQuery') {
                                            $('#no_msg_span').html('');
                                            $('#msg_span').html('<li>' + data['message'] + '</li>');
                                        } else {
                                            dialog2 = bootbox.alert({
                                                message: responseText,
                                                size: 'small'
                                            });
                                            setTimeout(
                                                function () {
                                                    dialog2.find(".bootbox-close-button").trigger("click");
                                                }
                                                , 2000
                                            );
                                        }
                                    }

                                    buttons['chatButton']['svgObject'].setInteractive();
                                    buttons['chatButton']['svgObject'].bringToTop(buttons['chatButton']['svgObject'].getByName('chatButton' + OTJAT_MODE));
                                    buttons['chatButton']['svgObject'].getByName('chatButton' + ALARM_MODE).setData('alarm', false);
                                }
                            );
                    }

                    return false;
                }
            },
            cancel: {
                label: '<?= T::S('Exit') ?>',
                className: 'ml-5 btn-secondary btn-default bootbox-cancel',
                callback: function () {
                    canOpenDialog = true;
                    canCloseDialog = true;

                    return true;
                }
            },
            complain: {
                label: '<?= T::S('Appeal') ?>',
                className: 'ml-5 ' + (hasIncomingMessages ? 'btn-danger' : 'btn-light'),
                callback: function () {
                    if (hasIncomingMessages) {
                        fetchGlobal(COMPLAIN_SCRIPT, '', $(".bootbox-body #myChatForm").serialize())
                            .then((data) => {
                                if (data == '')
                                    var responseText = '<?= T::S('Error') ?>';
                                else
                                    var responseText = data['message'];
                                dialog2 = bootbox.alert({
                                    message: responseText,
                                    size: 'small'
                                });
                                setTimeout(
                                    function () {
                                        dialog2.find(".bootbox-close-button").trigger("click");
                                    }
                                    , 5000
                                );
                            });
                    }

                    return false;
                }
            }
        }
    });
}
;

function logButtonFunction() {
    if (bootBoxIsOpenedGlobal())
        return;

    canOpenDialog = false;
    canCloseDialog = false;

    let message = '<br /><ul style="margin-left:-30px;margin-right:-5px;">';
    let i = 0;
    for (k in gameLog) {
        if (i >= 10) break;
        message = message + '<li>' + gameLog[k] + "</li>";
        i++;
    }
    message = message + '</ul>';
    if (i == 0)
        message = message + '<?= T::S('There are no events yet') ?>';

    notDialog = bootbox.dialog({
        message: message,
        size: 'small',
        onEscape: function () {
            activateFullScreenForMobiles();
            canOpenDialog = true;
            canCloseDialog = true;
        },
        buttons: {
            cancel: {
                label: "<?= T::S('Playing to') ?> <strong>" + winScore + "</strong>",
                className: 'btn btn-outline-secondary',
                callback: function () {
                    return false;
                }
            },
            confirm: {
                label: "OK",
                className: 'btn-primary',
                callback: function () {
                    activateFullScreenForMobiles();
                    canOpenDialog = true;
                    canCloseDialog = true;
                    return true;
                }
            }
        },
        callback: function (result) {
            canOpenDialog = true;
            canCloseDialog = true;
        }
    })
        .off("shown.bs.modal")
        .find('button.btn.btn.btn-sm.btn-info')
        .prop('disabled', true);

    return;
};

function makeCheckButtonInactive(dialog) {
    dialog.addClass(CHECK_BUTTON_INACTIVE_CLASS);
}

function makeSubmitButtonInactive(dialog) {
    dialog.addClass(SUBMIT_BUTTON_INACTIVE_CLASS);
}

function checkButtonActive() {
    return !$('.' + CHECK_BUTTON_INACTIVE_CLASS).length;
}

function submitButtonActive() {
    return !$('.' + SUBMIT_BUTTON_INACTIVE_CLASS).length;
}

function playersButtonFunction() {
    if (bootBoxIsOpenedGlobal()) {
        return;
    }

    if (!gameNumber) {
        return;
    }

    if (window.innerWidth < window.innerHeight) {
        var orient = 'vertical';
    } else {
        var orient = 'horizontal';
    }

    buttons['playersButton']['svgObject'].disableInteractive();
    buttons['playersButton']['svgObject'].bringToTop(buttons['playersButton']['svgObject'].getByName('playersButton' + 'Inactive'));

    setTimeout(function () {
        (lang == 'EN'
            ? fetchGlobalMVC(PLAYER_RATING_SCRIPT + '?game_id=' + gameNumber + '&common_id=' + commonId + '&lang=' + lang, '', orient)
            : fetchGlobal(PLAYER_RATING_SCRIPT, '', orient))
            .then((data) => {

                canOpenDialog = false;
                canCloseDialog = false;

                if (data == '')
                    var responseText = '<?= T::S('Error') ?>';
                else
                    var responseText = lang == 'EN' ? JSON.stringify(data) : data['message'];
                dialog = bootbox.alert({
                    title: '<?= T::S('Rating of opponents') ?>',
                    message: responseText,
                    size: 'large',
                    callback: function () {
                        canOpenDialog = true;
                        canCloseDialog = true;
                    }
                });
                dialog
                    .find('.modal-content').css({'background-color': 'rgba(255, 255, 255, 0.7)'})
                    .find('img').css('background-color', 'rgba(0, 0, 0, 0)');

                makeCheckButtonInactive(dialog);
                makeSubmitButtonInactive(dialog);
                
                buttons['playersButton']['svgObject'].setInteractive();
                buttons['playersButton']['svgObject'].bringToTop(buttons['playersButton']['svgObject'].getByName('playersButton' + OTJAT_MODE));

            });
    }, 100);

    setTimeout(function () {
        buttons['playersButton']['svgObject'].setInteractive();
        buttons['playersButton']['svgObject'].bringToTop(buttons['playersButton']['svgObject'].getByName('playersButton' + OTJAT_MODE));
    }, 3000);
}
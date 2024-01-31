//
function submitButtonFunction() {

    buttons['submitButton']['svgObject'].disableInteractive();
    buttons['submitButton']['svgObject'].bringToTop(buttons['submitButton']['svgObject'].getByName('submitButton' + 'Inactive'));

    setTimeout(function () {
        fetchGlobal(SUBMIT_SCRIPT, 'cells', cells)
            .then((data) => {
                if ('http_status' in data && (data['http_status'] === BAD_REQUEST || data['http_status'] === PAGE_NOT_FOUND)) {
                    buttons['submitButton']['svgObject'].setInteractive();
                    buttons['submitButton']['svgObject'].bringToTop(buttons['submitButton']['svgObject'].getByName('submitButton' + 'Otjat'));
                    dialog = bootbox.alert({
                        message: ('message' in data && data['message'] !== '')
                            ? (data['message'] + '<br /> Попробуйте отправить заново')
                            : '<strong>Ошибка связи с сервером!<br /> Попробуйте отправить заново</strong>',
                        size: 'small'
                    });
                } else {
                    gameState = 'afterSubmit';
                    parseDeskGlobal(data); // JSON data parsed by `response.json()` call
                }
            });
    }, 100);
}


function checkButtonFunction() {
    //if (bootBoxIsOpenedGlobal())        return;

    buttons['checkButton']['svgObject'].disableInteractive();
    buttons['checkButton']['svgObject'].bringToTop(buttons['checkButton']['svgObject'].getByName('checkButton' + 'Inactive'));

    setTimeout(function () {
        fetchGlobal(WORD_CHECKER_SCRIPT, 'cells', cells)
            .then((data) => {
                if (data == '')
                    var responseText = 'Вы не составили ни одного слова!';
                else
                    var responseText = data;
                dialog = bootbox.alert({
                    message: responseText,
                    size: 'small'
                });

                buttons['checkButton']['svgObject'].setInteractive();
                buttons['checkButton']['svgObject'].bringToTop(buttons['checkButton']['svgObject'].getByName('checkButton' + 'Otjat'));
            });
    }, 100);
};

function shareButtonFunction() {
    if (bootBoxIsOpenedGlobal())
        return;

    dialog = bootbox.alert({
        message: instruction,
        locale: 'ru'
    }).off("shown.bs.modal");
};

function newGameButtonFunction(ignoreDialog = false) {
    if (!ignoreDialog && bootBoxIsOpenedGlobal()) {
        return;
    }

    buttons['newGameButton']['svgObject'].disableInteractive();

    if (gameState == 'myTurn' || gameState == 'preMyTurn' || gameState == 'otherTurn' || gameState == 'startGame') {
        dialog = bootbox.dialog({
            // title: 'Требуется подтверждение',
            message: 'Вы проиграете, если выйдете из игры! ПРОДОЛЖИТЬ?',
            size: 'medium',
            // onEscape: false,
            closeButton: true,
            buttons: {
                cancel: {
                    label: 'Отмена',
                    className: 'btn-outline-success',//''btn btn-success',
                    callback: function () {
                        return true;
                    }
                },
                confirm: {
                    label: 'Подтвердить',
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
                    label: 'Реванш!',
                    className: 'btn-info',
                    callback: function () {
                        setTimeout(function () {
                            fetchGlobal(INVITE_SCRIPT, '', 'gameState=' + gameState)
                                .then((dataInvite) => {
                                    let responseText = 'Запрос отклонен';
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
        /*
        dialog = bootbox.confirm({
            message: 'Вы проиграете, если выйдете из игры! ПРОДОЛЖИТЬ?',
            locale: 'ru',
            callback: function (result) {
                if (result) {
                    requestToServerEnabled = true;
                    fetchGlobal(NEW_GAME_SCRIPT, '', 'gameState=' + gameState)
                        .then((data) => {
                            document.location.reload(true);
                        });
                } else {
                    buttons['newGameButton']['svgObject'].setInteractive();
                }
            }
        });*/
    } else {
        let lastState = gameState;
        gameState = 'chooseGame';

        buttons['newGameButton']['svgObject'].bringToTop(buttons['newGameButton']['svgObject'].getByName('newGameButton' + 'Inactive'));

        fetchGlobal(NEW_GAME_SCRIPT, '', 'gameState=' + gameState)
            .then((data) => {
                document.location.reload(true);
                setTimeout(function () {
                    gameState = lastState;
                }, 100);

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
    formFooter += 'Количество фишек в банке: '
        + '<strong>' + numBankFishki + '</strong>'
        + '<br>Если вы отметите больше фишек на замену, чем осталось в банке, то <strong>ВСЕ</strong> ваши фишки будут помещены в банк <strong>перед раздачей</strong> и могут вам вернуться обратно'
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
        message += noMsgSpan + 'Сообщений пока нет' + '</span>';
    } else {
        message += noMsgSpan + '</span>';
    }
    message = message + '</ul>';
    let radioButtons = message + '';

    let isSelectedPlaced = false;
    if (ochki_arr.length > 1) {
        radioButtons += '<div class="form-check form-check-inline"><input class="form-check-input" type="radio" id="chatall" name="chatTo" value="all" checked> <label class="form-check-label" for="chatall">Для всех</label></div>';
        isSelectedPlaced = true;
    }

    for (k in ochki_arr)
        if (k != myUserNum) {
            radioButtons += '<div class="form-check form-check-inline"><input class="form-check-input" type="radio" id="to_' + (k == 0 ? '0' : k) + '" name="chatTo" value="' + (k == 0 ? '0' : k) + '" ' + (isSelectedPlaced ? '' : ' checked ') + '> <label class="form-check-label" for="to_' + (k == 0 ? '0' : k) + '">Игроку ' + (parseInt(k, 10) + 1) + '</label></div>';
            isSelectedPlaced = true;
        }

    radioButtons += '<div class="form-check form-check-inline"><input class="form-check-input" type="radio" id="to_words" name="chatTo" value="words" ' + (isSelectedPlaced ? '' : ' checked ') + '> <label class="form-check-label" for="to_words">Подбор слов</label></div>';

    let textInput = '<div class="input-group input-group-lg">  <div class="input-group-prepend"></div>  <input type="text" id="chattext" class="form-control" name="messageText"></div>';


    dialog = bootbox.dialog({
        title: '</h5>'
            + (
                !isYandexAppGlobal()
                    ? (
                        '<h6>Поддержка и чат игроков в <a target="_blank" title="Вступить в группу" href="'
                        + (gameWidth < gameHeight ? 'https://t.me/eruditclub' : 'https://web.telegram.org/#/im?p=@eruditclub')
                        + '">Telegram</a> </h6>'
                    )
                    : ''
            )
            + '<h5>Отправьте сообщение в игре',
        message: '<form onsubmit="return false" id="myChatForm">' + radioButtons + textInput + '</form>',
        locale: 'ru',
        size: 'large',
        closeButton: false,
        buttons: {
            confirm: {
                label: 'Отправить',
                className: 'btn-primary',
                callback: function () {
                    canOpenDialog = true;
                    canCloseDialog = true;

                    buttons['chatButton']['svgObject'].bringToTop(buttons['chatButton']['svgObject'].getByName('chatButton' + 'Otjat'));
                    buttons['chatButton']['svgObject'].getByName('chatButton' + 'Alarm').setData('alarm', false);

                    if ($(".bootbox-body #chattext").val() != '') {

                        buttons['chatButton']['svgObject'].disableInteractive();
                        buttons['chatButton']['svgObject'].bringToTop(buttons['chatButton']['svgObject'].getByName('chatButton' + 'Inactive'));

                        fetchGlobal(CHAT_SCRIPT, '', $(".bootbox-body #myChatForm").serialize())
                            .then((data) => {
                                    if (data == '')
                                        var responseText = 'Ошибка';
                                    else {
                                        var responseText = data['message'];

                                        if (data['message'] === 'Сообщение отправлено') {
                                            $('#no_msg_span').html('');
                                            $('#msg_span').html('<li>' + $('#chattext').val() + '</li>' + $('#msg_span').html());
                                        }

                                        $('#chattext').val('');
                                    }

                                    if (data['message'] !== 'Сообщение отправлено') {
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
                                    buttons['chatButton']['svgObject'].bringToTop(buttons['chatButton']['svgObject'].getByName('chatButton' + 'Otjat'));
                                    buttons['chatButton']['svgObject'].getByName('chatButton' + 'Alarm').setData('alarm', false);
                                }
                            );
                    }

                    return false;
                }
            },
            cancel: {
                label: 'Выход',
                className: 'ml-5 btn-secondary btn-default bootbox-cancel',
                callback: function () {
                    canOpenDialog = true;
                    canCloseDialog = true;
                    return true;
                }
            },
            complain: {
                label: 'Пожаловаться',
                className: 'ml-5 btn-danger',
                callback: function () {

                    fetchGlobal(COMPLAIN_SCRIPT, '', $(".bootbox-body #myChatForm").serialize())
                        .then((data) => {
                            if (data == '')
                                var responseText = 'Ошибка';
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
        message = message + 'Событий пока нет';

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
                label: "До последней фишки",
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

function playersButtonFunction() {
    if (bootBoxIsOpenedGlobal())
        return;

    if (window.innerWidth < window.innerHeight) {
        var orient = 'vertical';
    } else {
        var orient = 'horizontal';
    }

    buttons['playersButton']['svgObject'].disableInteractive();
    buttons['playersButton']['svgObject'].bringToTop(buttons['playersButton']['svgObject'].getByName('playersButton' + 'Inactive'));

    setTimeout(function () {
        fetchGlobal(PLAYER_RATING_SCRIPT, '', orient)
            .then((data) => {

                canOpenDialog = false;
                canCloseDialog = false;

                if (data == '')
                    var responseText = 'Ошибка';
                else
                    var responseText = data['message'];
                dialog = bootbox.alert({
                    title: 'Рейтинг соперников',
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

                buttons['playersButton']['svgObject'].setInteractive();
                buttons['playersButton']['svgObject'].bringToTop(buttons['playersButton']['svgObject'].getByName('playersButton' + 'Otjat'));

            });
    }, 100);

    setTimeout(function () {
        buttons['playersButton']['svgObject'].setInteractive();
        buttons['playersButton']['svgObject'].bringToTop(buttons['playersButton']['svgObject'].getByName('playersButton' + 'Otjat'));
    }, 3000);
}
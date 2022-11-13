//
function submitButtonFunction() {
    //if (bootBoxIsOpenedGlobal())        return;

    buttons['submitButton']['svgObject'].disableInteractive();
    buttons['submitButton']['svgObject'].bringToTop(buttons['submitButton']['svgObject'].getByName('submitButton' + 'Inactive'));

    setTimeout(function () {
        fetchGlobal('turn_submitter.php', 'cells', cells)
            .then((data) => {
                gameState = 'afterSubmit';
                parseDeskGlobal(data); // JSON data parsed by `response.json()` call
            });
    }, 100);
}
;


function checkButtonFunction() {
    //if (bootBoxIsOpenedGlobal())        return;

    buttons['checkButton']['svgObject'].disableInteractive();
    buttons['checkButton']['svgObject'].bringToTop(buttons['checkButton']['svgObject'].getByName('checkButton' + 'Inactive'));

    setTimeout(function () {
        fetchGlobal('word_checker.php', 'cells', cells)
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

function newGameButtonFunction() {
    if (bootBoxIsOpenedGlobal())
        return;

    buttons['newGameButton']['svgObject'].disableInteractive();

    if (gameState == 'myTurn' || gameState == 'preMyTurn' || gameState == 'otherTurn' || gameState == 'startGame') {
        dialog = bootbox.confirm({
            message: 'Вы проиграете, если выйдете из игры! ПРОДОЛЖИТЬ?',
            locale: 'ru',
            callback: function (result) {
                if (result) {
                    fetchGlobal('new_game.php', '', 'gameState=' + gameState)
                        .then((data) => {
                            document.location.reload(true);
                        });
                } else {
                    buttons['newGameButton']['svgObject'].setInteractive();
                }
            }
        });
    } else {
        let lastState = gameState;
        gameState = 'chooseGame';

        buttons['newGameButton']['svgObject'].bringToTop(buttons['newGameButton']['svgObject'].getByName('newGameButton' + 'Inactive'));

        fetchGlobal('new_game.php', '', 'gameState=' + gameState)
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

    let message = '<ul style="margin-left:-30px;margin-right:-5px;">';
    let i = 0;
    for (k in chatLog) {
        if (i >= 10) break;
        message = message + '<li>' + chatLog[k] + "</li>";
        i++;
    }

    if (i == 0)
        message = message + 'Сообщений пока нет';
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

    let textInput = '<div class="input-group input-group-lg">  <div class="input-group-prepend"></div>  <input type="text" id="chattext" class="form-control" name="messageText"></div>';


    dialog = bootbox.confirm({
        title: '</h5><h6>Поддержка и чат игроков в <a target="_blank" title="Вступить в группу" href="' + (gameWidth < gameHeight ? 'https://t.me/eruditclub' : 'https://web.telegram.org/#/im?p=@eruditclub') + '">Telegram</a> </h6><h5>Отправьте сообщение в игре',
        message: '<form onsubmit="return false" id="myChatForm">' + radioButtons + textInput + '</form>',
        locale: 'ru',
        size: 'large',
        callback: function (result) {
            canOpenDialog = true;
            canCloseDialog = true;

            buttons['chatButton']['svgObject'].bringToTop(buttons['chatButton']['svgObject'].getByName('chatButton' + 'Otjat'));
            buttons['chatButton']['svgObject'].getByName('chatButton' + 'Alarm').setData('alarm', false);

            if (result && $(".bootbox-body #chattext").val() != '') {

                buttons['chatButton']['svgObject'].disableInteractive();
                buttons['chatButton']['svgObject'].bringToTop(buttons['chatButton']['svgObject'].getByName('chatButton' + 'Inactive'));

                fetchGlobal('send_chat_message.php', '', $(".bootbox-body #myChatForm").serialize())
                    .then((data) => {
                        if (data == '')
                            var responseText = 'Ошибка';
                        else
                            var responseText = data['message'];
                        dialog = bootbox.alert({
                            //title: ,
                            message: responseText,
                            size: 'small'
                        });
                        setTimeout(
                            function () {
                                dialog.find(".bootbox-close-button").trigger("click");
                            }
                            , 1000
                        );
                        buttons['chatButton']['svgObject'].setInteractive();
                        buttons['chatButton']['svgObject'].bringToTop(buttons['chatButton']['svgObject'].getByName('chatButton' + 'Otjat'));
                        buttons['chatButton']['svgObject'].getByName('chatButton' + 'Alarm').setData('alarm', false);
                    });

                //console.log('This was logged in the callback: ' + result+ $(".bootbox-body #myChatForm").serialize());
            }
        }
    });
};

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
                label: "Играем до <strong>" + winScore + "</strong>",
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
        fetchGlobal('players_ratings.php', '', orient)
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
function submitButtonFunction() {
    if (bootBoxIsOpenedGlobal())
        return;

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
    if (bootBoxIsOpenedGlobal())
        return;

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
    buttons['newGameButton']['svgObject'].bringToTop(buttons['newGameButton']['svgObject'].getByName('newGameButton' + 'Inactive'));
    fetchGlobal('new_game.php', '', '')
        .then((data) => {
            document.location.reload(true);
        });
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

    let formHeader = '<form id="myForm" class="form-horizontal">';
    let formFooter = '</div></form>';
    var formInner = '<div class="form-group">';
    for (let k in container)
        formInner += '<div style="display:inline-block;"><input type="checkbox" style="opacity:80%; transform: scale(2);" id="fishka_' + k + '_' + container[k].getData('letter') + '" name="fishka_' + k + '_' + container[k].getData('letter') + '"' + (container[k].getData('letter') < 999 ? 'checked' : '') + '><label for="fishka_' + k + '_' + container[k].getData('letter') + '"><div style="margin-left:-12px;margin-right:13px;" class="letter_' + (container[k].getData('letter') < 999 ? container[k].getData('letter') : '999" title="Зачем?') + '" onclick="$(\'#fishka_' + k + '_' + container[k].getData('letter') + '\').trigger(\'click\');return false;"></div></label></div>';
    disableButtons();
    dialog = bootbox.confirm({
        message: 'Выберите фишки для замены<br /><br />' + formHeader + formInner + formFooter,
        locale: 'ru',
        callback: function (result) {
            if (result)
                changeFishkiGlobal($(".bootbox-body #myForm").serialize());
        }
    });
    enableButtons();
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
    //radioButtons += '</div>';

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
                            title: responseText,
                            message: ' ',
                            size: 'large'
                        });

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

    dialog = bootbox.alert({
        message: message,
        size: 'small'
    }).off("shown.bs.modal");
    return;
};

function playersButtonFunction() {
    if (bootBoxIsOpenedGlobal())
        return;
    canOpenDialog = false;
    canCloseDialog = false;

    buttons['playersButton']['svgObject'].disableInteractive();
    buttons['playersButton']['svgObject'].bringToTop(buttons['playersButton']['svgObject'].getByName('playersButton' + 'Inactive'));

    setTimeout(function () {
        fetchGlobal('players_ratings.php', '', 12)
            .then((data) => {
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

                buttons['playersButton']['svgObject'].setInteractive();
                buttons['playersButton']['svgObject'].bringToTop(buttons['playersButton']['svgObject'].getByName('playersButton' + 'Otjat'));

            });
    }, 100);


}
//
var gameStates = {
    register: {
        1: 'waiting',
        refresh: 1,
        action: function (data) {
            useLocalStorage = true;
            if (!('erudit_user_session_ID' in localStorage)) {
                localStorage.erudit_user_session_ID = data['cookie'];
            }
            queryNumber = 1;
        }
    },
    cookieTest: {
        1: 'waiting',
        refresh: 10000000,
        action: function (data) {
            fetchGlobal(COOKIE_CHECKER_SCRIPT, '', '12=12')
                .then((data) => {
                    if ('gameState' in data) {
                        if (data.gameState == 'register') {
                            gameStates.register.action(data);
                        } else {
                            //queryNumber = 1;
                            commonCallback(data);
                        }
                    } else {
                        var responseText = 'Ошибка';
                        alert(responseText);
                        queryNumber = 1;
                    }
                })
            ;
        },
    },
    desync: {
        1: 'waiting', 2: 'done',
        refresh: 5,
        noDialog: true,
        action: function (data) {
            gameState = gameOldState;
            gameSubState = gameOldSubState;
            enableButtons();
            if ('queryNumber' in data) {
                queryNumber = data['queryNumber'];
            }
        },
        //message: 'Синхронизация с сервером...'
    },
    noGame: {
        1: 'waiting', 2: 'done',
        noDialog: true,
        refresh: 10,
    },
    startGame: {
        1: 'waiting', 2: 'done',
        message: 'Игра начата!',
        refresh: 10,
        action: function (data) {
            buttons['submitButton']['svgObject'].disableInteractive();
            buttons['submitButton']['svgObject'].bringToTop(buttons['submitButton']['svgObject'].getByName('submitButton' + 'Inactive'));

            gameStates['myTurn']['from_noGame'](data);
            gameStates['gameResults']['action'](data);
        },
        from_initGame: function () {
            while (fixedContainer.length)
                fixedContainer.pop().destroy();
            cells = [];
            newCells = [];
            initCellsGlobal();
        },
        from_initRatingGame: function () {
            gameStates['startGame']['from_initGame']();
        }
    },
    chooseGame: {
        1: 'choosing', 2: 'done',
        refresh: 1000000,
        message: 'Начните игру',
        noDialog: true,
        action: function (data) {
            let under1800 = 'Только для игроков с рейтингом 1800+';
            let noRatingPlayers = 'Недостаточно игроков с рейтингом 1900+ онлайн';
            let haveRatingPlayers = 'Выберите минимальный рейтинг соперников';
            let title = '';
            let onlinePlayers = '';
            let chooseDisabled = '';
            if (('players' in data) && (lang != 'EN')) {
                if (('thisUserRating' in data['players']) && (data['players']['thisUserRating'] < 1800)) {
                    chooseDisabled = "disabled";
                    title = under1800;
                } else {
                    title = haveRatingPlayers;
                }
                if (!(1900 in data['players']) || (data['players'][1900] == 0)) {
                    title = noRatingPlayers;
                }
                let colPlayers = screenOrient === HOR ? "col-3" : "col-4";

                let checked_0 = 'checked';
                let checked_1900 = '';
                let checked_2000 = '';
                let checked_2100 = '';
                let checked_2200 = '';
                let checked_2300 = '';
                let checked_2400 = '';
                let checked_2500 = '';
                let checked_2600 = '';
                let checked_2700 = '';

                if ('prefs' in data && data['prefs'] !== false && 'from_rating' in data['prefs'] && data['prefs']['from_rating'] > 0) {
                    checked_0 = '';
                    checked_1900 = (data['prefs']['from_rating'] == 1900) ? 'checked' : '';
                    checked_2000 = (data['prefs']['from_rating'] == 2000) ? 'checked' : '';
                    checked_2100 = (data['prefs']['from_rating'] == 2100) ? 'checked' : '';
                    checked_2200 = (data['prefs']['from_rating'] == 2200) ? 'checked' : '';
                    checked_2300 = (data['prefs']['from_rating'] == 2300) ? 'checked' : '';
                    checked_2400 = (data['prefs']['from_rating'] == 2400) ? 'checked' : '';
                    checked_2500 = (data['prefs']['from_rating'] == 2500) ? 'checked' : '';
                    checked_2600 = (data['prefs']['from_rating'] == 2600) ? 'checked' : '';
                    checked_2700 = (data['prefs']['from_rating'] == 2700) ? 'checked' : '';
                }
                onlinePlayers = '<br /><br /><strong>Искать игроков с рейтингом:</strong><br />';
                onlinePlayers += '<div class="form-row">';
                onlinePlayers += '<div title="' + title + '" class="' + colPlayers + ' form-check form-check-inline"><input class="form-check-input" type="radio" id="from_0" name="from_rating" value="0" '
                +  checked_0
                    + '> <label style="cursor: pointer;" class="form-check-label" for="from_0">Любой</label></div>';
                if ((1900 in data['players']) && (data['players'][1900] > 0)) {
                    onlinePlayers += '<div title="' + data['players'][1900] + ' в игре" class="form-check form-check-inline"><input class="form-check-input" type="radio" id="from_1900" name="from_rating" value="1900" ' + chooseDisabled.toString()
                        + checked_1900
                        + ' > <label style="cursor: pointer;" class="form-check-label" for="from_1900">ОТ 1900 (' + data['players'][1900] + ' онлайн)</label></div>';
                }
                    onlinePlayers += '</div>';

                if ((2000 in data['players']) && (data['players'][2000] > 0)) {
                    onlinePlayers += '<div class="form-row">';
                    onlinePlayers += '<div title="' + data['players'][2000] + ' в игре" class="' + colPlayers + ' form-check form-check-inline"><input class="form-check-input" type="radio" id="from_2000" name="from_rating" value="2000"' + chooseDisabled.toString()
                        + checked_2000
                        + ' > <label style="cursor: pointer;" class="form-check-label" for="from_2000">ОТ 2000 (' + data['players'][2000] + ')</label></div>';

                    if ((2100 in data['players']) && (data['players'][2100] > 0))
                        onlinePlayers += '<div title="' + data['players'][2100] + ' в игре" class="form-check form-check-inline"><input class="form-check-input" type="radio" id="from_2100" name="from_rating" value="2100"' + chooseDisabled
                            + checked_2100
                            + ' > <label style="cursor: pointer;" class="form-check-label" for="from_2100">ОТ 2100 (' + data['players'][2100] + ')</label></div>';
                    onlinePlayers += '</div>';
                }

                if ((2200 in data['players']) && (data['players'][2200] > 0)) {
                    onlinePlayers += '<div class="form-row">';
                    onlinePlayers += '<div title="' + data['players'][2200] + ' в игре" class="' + colPlayers + ' form-check form-check-inline"><input class="form-check-input" type="radio" id="from_2200" name="from_rating" value="2200"' + chooseDisabled
                        + checked_2200
                        + ' > <label style="cursor: pointer;" class="form-check-label" for="from_2200">ОТ 2200 (' + data['players'][2200] + ')</label></div>';
                    if ((2300 in data['players']) && (data['players'][2300] > 0)) {
                        onlinePlayers += ('<div title="' + data['players'][2300] + ' в игре" class="form-check form-check-inline"><input class="form-check-input" type="radio" id="from_2300" name="from_rating" value="2300"' + chooseDisabled
                            + checked_2300
                            + ' > <label style="cursor: pointer;" class="form-check-label" for="from_2300">ОТ 2300 (' + data['players'][2300] + ')</label></div>');
                    }
                    onlinePlayers += '</div>';
                }

                if ((2400 in data['players']) && (data['players'][2400] > 0)) {
                    onlinePlayers += '<div class="form-row">';
                    onlinePlayers += '<div title="' + data['players'][2400] + ' в игре" class="' + colPlayers + ' form-check form-check-inline"><input class="form-check-input" type="radio" id="from_2400" name="from_rating" value="2400"' + chooseDisabled
                        + checked_2400
                        + ' > <label style="cursor: pointer;" class="form-check-label" for="from_2400">ОТ 2400 (' + data['players'][2400] + ')</label></div>';
                    if ((2500 in data['players']) && (data['players'][2500] > 0))
                        onlinePlayers += '<div title="' + data['players'][2500] + ' в игре" class="form-check form-check-inline"><input class="form-check-input" type="radio" id="from_2500" name="from_rating" value="2500"' + chooseDisabled
                            + checked_2500
                            + ' > <label style="cursor: pointer;" class="form-check-label" for="from_2500">ОТ 2500 (' + data['players'][2500] + ')</label></div>';
                    onlinePlayers += '</div>';
                }

                if ((2600 in data['players']) && (data['players'][2600] > 0)) {
                    onlinePlayers += '<div class="form-row">';
                    onlinePlayers += '<div title="' + data['players'][2600] + ' в игре" class="' + colPlayers + ' form-check form-check-inline"><input class="form-check-input" type="radio" id="from_2600" name="from_rating" value="2600"' + chooseDisabled
                        + checked_2600
                        + ' > <label style="cursor: pointer;" class="form-check-label" for="from_2600">ОТ 2600 (' + data['players'][2600] + ')</label></div>';
                    if ((2700 in data['players']) && (data['players'][2700] > 0))
                        onlinePlayers += '<div title="' + data['players'][2700] + ' в игре" class="form-check form-check-inline"><input class="form-check-input" type="radio" id="from_2700" name="from_rating" value="2700"' + chooseDisabled
                            + checked_2700
                            + ' > <label style="cursor: pointer;" class="form-check-label" for="from_2700">ОТ 2700 (' + data['players'][2700] + ')</label></div>';
                    onlinePlayers += '</div>';
                }
            }

            let radioButtons = '<div style="display:none;" class="form-check form-check-inline"><input class="form-check-input" type="radio" id="twoonly" name="players_count" value="2" checked> <label class="form-check-label" for="twoonly">Только два игрока</label></div>';
            radioButtons += '<div style="display:none;" class="form-check form-check-inline"><input class="form-check-input" type="radio" id="twomore" name="players_count" value="4"> <label class="form-check-label" for="twomore">До четырех игроков</label></div>';

            let wish = ''; //'<br /><br /><h6>Желательно:</h6>';
            let colOchki = screenOrient === HOR ? "col-5" : "col-6";

            let checked_200 = 'checked';
            let checked_300 = '';

            if ('prefs' in data && data['prefs'] !== false && 'ochki_num' in data['prefs']) {
                checked_200 = (data['prefs']['ochki_num'] == 200) ? 'checked' : '';
                checked_300 = (data['prefs']['ochki_num'] == 300) ? 'checked' : '';
            }

            let radioOchki = '<br /><div class="' + colOchki + ' form-check form-check-inline"><input class="form-check-input" type="radio" id="dvesti" name="ochki_num" value="200" ' + checked_200 + '> <label class="form-check-label" for="dvesti">Игра до 200 очков</label></div>';
            radioOchki += '<div class="form-check form-check-inline"><input class="form-check-input" type="radio" id="trista" name="ochki_num" value="300" ' + checked_300 + '> <label class="form-check-label" for="trista">Игра до 300 очков</label></div>';

            let wishTime = '<br /><br />'; //'<br /><br />Время на ход:<br />';
            let wish_120 = 'checked';
            let wish_60 = '';

            if ('prefs' in data && data['prefs'] !== false && 'turn_time' in data['prefs']) {
                wish_120 = (data['prefs']['turn_time'] == 120) ? 'checked' : '';
                wish_60 = (data['prefs']['turn_time'] == 60) ? 'checked' : '';
            }

            let radioTime = '<div class="' + colOchki + '  form-check form-check-inline"><input class="form-check-input" type="radio" id="dve" name="turn_time" value="120" ' + wish_120 + '> <label class="form-check-label" for="dve">2 минуты на ход</label></div>';
            radioTime += '<div class="form-check form-check-inline"><input class="form-check-input" type="radio" id="odna" name="turn_time" value="60" ' + wish_60 + '> <label class="form-check-label" for="odna">1 минута на ход</label></div>';

            let formHead = '<h5>Параметры игры (будут учтены при подборе)</h5>';
            let gameform = formHead + '<form onsubmit="return false" id="myGameForm">' + radioButtons + wish + radioOchki + wishTime + radioTime + onlinePlayers + '</form>';

            dialog = bootbox.dialog({
                title: gameStates['chooseGame']['message'],
                message: gameform,
                size: 'medium',
                onEscape: false,
                closeButton: false,
                buttons: {
                    cabinet: {
                        label: 'Личный кабинет',
                        className: 'btn-outline-success',//''btn btn-success',
                        callback: function () {
                            setTimeout(function () {
                                fetchGlobal(CABINET_SCRIPT, '', 12)
                                    .then((dataCabinet) => {
                                        if (dataCabinet == '')
                                            var responseText = 'Ошибка';
                                        else
                                            var responseArr = JSON.parse(dataCabinet['message']);
                                        console.log(responseArr);
                                        var message = '<form id="superForm" >';
                                        for (k in responseArr['form']) {
                                            message += '<div class="form-group"'
                                                + (('type' in responseArr['form'][k] && responseArr['form'][k]['type'] === 'hidden')
                                                    ? ' style="display:none" '
                                                    : '')
                                                + '><div class="col-sm-6">' +
                                                '<label for="' + responseArr['form'][k]['inputId'] + '">'
                                                + responseArr['form'][k]['prompt']
                                                + '</label>'
                                                + '</div>';
                                            message += '<div class="form-row align-items-center">'
                                                + '<div class="col-sm-8">'
                                                + '<input ';

                                            if ('value' in responseArr['form'][k]) {
                                                message += 'value="' + responseArr['form'][k]['value'] + '"';
                                                if ('readonly' in responseArr['form'][k]) {
                                                    message += ' readonly ';
                                                }
                                            } else {
                                                message += 'placeholder="' + responseArr['form'][k]['placeholder'] + '"';
                                            }

                                            message += (('type' in responseArr['form'][k])
                                                ? 'type="' + responseArr['form'][k]['type'] + '"'
                                                : 'type="text"')
                                                + ' class="form-control" name="'
                                                + responseArr['form'][k]['inputName']
                                                + '" id="'
                                                + responseArr['form'][k]['inputId']
                                                + '" '
                                                + ('required' in responseArr['form'][k]
                                                    ? ' required '
                                                    : '')
                                                + '></div>';
                                            message += !('type' in responseArr['form'][k] && responseArr['form'][k]['type'] === 'hidden')
                                                ? (
                                                    '<div class="col-sm-4 col-form-label">'
                                                    + '<button type="submit" class="form-control btn btn-outline-secondary" onclick="'
                                                    + responseArr['form'][k]['onclick']
                                                    + '($(\'#' + responseArr['form'][k]['inputId']
                                                    + '\').val(),'
                                                    + responseArr['common_id']
                                                    + ');return false;">'
                                                    + responseArr['form'][k]['buttonCaption']
                                                    + '</button></div>'
                                                )
                                                : ('')
                                                +
                                                '</div>';
                                            message += '</div>';
                                        }
                                        message += '</form>';
                                        dialog = bootbox.alert({
                                            title: 'Ваш личный кабинет, <span id="playersNikname">' +
                                                responseArr['name'] +
                                                '</span>' +
                                                '<span id="playersAvatar">&nbsp;' +
                                                '<img style="cursor: pointer;" title="' + responseArr['img_title'] + '" src="' + responseArr['url'] + '" width="100px" max-height = "100px" />' +
                                                '</span>',
                                            message: responseArr['text'] + message,
                                            locale: 'ru',
                                            size: 'large',
                                            callback: function () {
                                                gameStates['chooseGame']['action'](data);
                                            }
                                        });
                                        return false;
                                    });
                            }, 100);
                        }
                    },
                    instruction: {
                        label: '&nbsp;Инструкция&nbsp;',
                        className: 'btn-outline-success',//'btn-primary',
                        callback: function () {
                            dialog = bootbox.alert({
                                message: instruction,
                                locale: 'ru'
                            }).off("shown.bs.modal");

                            return false;
                        }
                    },
                    beginGame: {
                        label: 'Начать игру!',
                        className: 'btn-primary',
                        callback: function () {
                            activateFullScreenForMobiles();
                            gameState = 'noGame';
                            fetchGlobal(STATUS_CHECKER_SCRIPT, '', $(".bootbox-body #myGameForm").serialize())
                                .then((data) => {
                                    if (data == '')
                                        var responseText = 'Ошибка';
                                    else {
                                        commonCallback(data);
                                    }
                                });

                            return true;
                        }
                    },
                    engGame: {
                        label: '&nbsp;&nbsp;In English!&nbsp;&nbsp;&nbsp;',
                        className: 'btn-danger',
                        callback: function () {
                            activateFullScreenForMobiles();
                            gameState = 'noGame';
                            lang = 'EN';
                            //for avoiding errors in IDE
                            //<?php include('instruction_eng.js'); ?>
                            /** todo not working on yandex*/
                            asyncCSS('https://xn--d1aiwkc2d.club/css/choose_css.css');
                            fetchGlobal(STATUS_CHECKER_SCRIPT, '', $(".bootbox-body #myGameForm").serialize())
                                .then((data) => {
                                    if (data == '')
                                        var responseText = 'Ошибка';
                                    else {
                                        commonCallback(data);
                                    }
                                });

                            return true;
                        }
                    }
                }
            });
        }
    },
    initGame: {
        1: 'waiting', 2: 'done',
        action: function (data) {
            buttons['submitButton']['svgObject'].disableInteractive();
            buttons['submitButton']['svgObject'].bringToTop(buttons['submitButton']['svgObject'].getByName('submitButton' + 'Inactive'));
        },
        message: 'Подбор игры - ожидайте',
        refresh: 10
    },
    initRatingGame: {
        1: 'waiting', 2: 'done',
        action: function (data) {
            buttons['submitButton']['svgObject'].disableInteractive();
            buttons['submitButton']['svgObject'].bringToTop(buttons['submitButton']['svgObject'].getByName('submitButton' + 'Inactive'));
        },
        message: 'Подбор игры - ожидайте',
        refresh: 10
    },

    myTurn: {
        1: 'thinking', 2: 'checking', 3: 'submiting', 4: 'done',
        message: 'Ваш ход!',
        refresh: 15,
        action: function (data) {
            gameStates['gameResults']['action'](data);
            buttons['submitButton']['svgObject'].setInteractive();
            buttons['submitButton']['svgObject'].bringToTop(buttons['submitButton']['svgObject'].getByName('submitButton' + 'Otjat'));
        },
        from_initRatingGame: function (data) {
            gameStates['startGame']['from_initGame']();
            gameStates['myTurn']['from_noGame'](data);
        },
        from_initGame: function (data) {
            gameStates['startGame']['from_initGame']();
            gameStates['myTurn']['from_noGame'](data);
        },
        from_noGame: function (data) {
            if ('fishki' in data)
                placeFishki(data['fishki']);
        },
        from_desync: function (data) {
            if ('fishki' in data)
                placeFishki(data['fishki']);
        },
        from_gameResults: function () {
            gameStates['startGame']['from_initGame']();
        },
        from_preMyTurn: function () {
            resetButtonFunction(true);
            gameStates['startGame']['from_initGame']();
        },
        from_startGame: function () {
            resetButtonFunction(true);
            gameStates['startGame']['from_initGame']();
        }
    },
    preMyTurn: {
        1: 'waiting', 2: 'done',
        message: 'Приготовьтесь - Ваш ход следующий!',
        refresh: 5,
        action: function (data) {
            gameStates['gameResults']['action'](data);

            buttons['submitButton']['svgObject'].disableInteractive();
            buttons['submitButton']['svgObject'].bringToTop(buttons['submitButton']['svgObject'].getByName('submitButton' + 'Inactive'));
        },
        from_desync: function (data) {
            if ('fishki' in data)
                placeFishki(data['fishki']);
        },
        from_initRatingGame: function (data) {
            gameStates['startGame']['from_initGame']();
            gameStates['myTurn']['from_noGame'](data);
        },
        from_initGame: function (data) {
            gameStates['startGame']['from_initGame']();
            gameStates['myTurn']['from_noGame'](data);
        },
        from_noGame: function (data) {
            gameStates['myTurn']['from_noGame'](data)
        },
        from_myTurn: function (data) {
            gameStates['myTurn']['from_noGame'](data)
        },
        from_otherTurn: function (data) {
            gameStates['myTurn']['from_noGame'](data)
        },
        from_gameResults: function () {
            gameStates['startGame']['from_initGame']()
        },
    },
    otherTurn: {
        1: 'waiting', 2: 'done', message: 'Отдохните - Ваш ход через один',
        refresh: 5,
        action: function (data) {
            gameStates['gameResults']['action'](data);

            gameStates['myTurn']['from_noGame'](data);
            buttons['submitButton']['svgObject'].disableInteractive();
            buttons['submitButton']['svgObject'].bringToTop(buttons['submitButton']['svgObject'].getByName('submitButton' + 'Inactive'));

        },
        from_desync: function (data) {
            if ('fishki' in data)
                placeFishki(data['fishki']);
        },
        from_initRatingGame: function (data) {
            gameStates['startGame']['from_initGame']();
        },
        from_initGame: function (data) {
            gameStates['startGame']['from_initGame']();
        },
        from_gameResults: function () {
            gameStates['startGame']['from_initGame']();
        }
    },
    gameResults: {
        1: 'waiting', 2: 'done',
        messageFunction: function (mes) {
            return mes;
        },
        refresh: 10,
        action: function (data) {
            if ("desk" in data && data.desk.length > 0)
                parseDeskGlobal(data['desk']);
            if ("score" in data)
                ochki.text = data['score'];
            userScores(data);
        },
        results: function (data) {
            if (dialog && canCloseDialog)
                dialog.modal('hide');
            var okButtonCaption = 'Отказаться';
            if ('inviteStatus' in data && data['inviteStatus'] == 'waiting') {
                var okButtonCaption = 'OK';
            }

            dialog = bootbox.dialog({
                //title: 'Игра завершена',
                message: data['comments'],
                //size: 'small',
                onEscape: false,
                closeButton: false,
                buttons: {
                    invite: {
                        label: 'Предложить игру',
                        className: 'btn-primary',
                        callback: function () {
                            setTimeout(function () {
                                fetchGlobal(INVITE_SCRIPT, '', 12)
                                    .then((dataInvite) => {
                                        if (dataInvite == '')
                                            var responseText = 'Запрос отклонен';
                                        else
                                            var responseText = dataInvite['message'];
                                        if ('inviteStatus' in dataInvite) {
                                            if (dataInvite['inviteStatus'] == 'newGameStarting')
                                                document.location.reload(true);
                                        }
                                        dialogResponse = bootbox.alert({
                                            message: responseText,
                                            locale: 'ru',
                                            size: 'small',
                                            callback: function () {
                                                dialogResponse.modal('hide');
                                                dataInvite['comments'] = data['comments'];
                                                gameStates['gameResults']['results'](dataInvite);
                                            }
                                        });

                                        setTimeout(
                                            function () {
                                                dialogResponse.find(".bootbox-close-button").trigger("click");
                                            }
                                            , 2000
                                        );

                                        return false;
                                    });
                            }, 100);
                        }
                    },
                    ok: {
                        label: okButtonCaption,
                        className: 'btn-info',
                        callback: function () {
                            return true;
                        }
                    },
                    new: {
                        label: 'Новая игра',
                        className: 'btn-danger',
                        callback: function () {
                            newGameButtonFunction(true);
                        }
                    }
                }
            });
        },
        decision: function (data) {
            if (dialog && canCloseDialog) {
                dialog.modal('hide');
            }
            if (dialogResponse) {
                dialogResponse.modal('hide');
            }

            dialog = bootbox.dialog({
                //title: 'Игра завершена',
                message: data['comments'],
                //size: 'small',
                onEscape: false,
                closeButton: false,
                buttons: {
                    invite: {
                        label: 'Принять приглашение',
                        className: 'btn-primary',
                        callback: function () {
                            setTimeout(function () {
                                fetchGlobal(INVITE_SCRIPT, '', 12)
                                    .then((dataInvite) => {
                                        if (dataInvite == '') {
                                            var responseText = 'Запрос отклонен';
                                        } else {
                                            var responseText = dataInvite['message'];
                                        }
                                        if ('inviteStatus' in dataInvite) {
                                            if (dataInvite['inviteStatus'] == 'newGameStarting')
                                                document.location.reload(true);
                                        }
                                        dialogResponse = bootbox.alert({
                                            message: responseText,
                                            locale: 'ru',
                                            size: 'small',
                                            callback: function () {
                                                dialogResponse.modal('hide');
                                                dataInvite['comments'] = data['comments'];
                                                //gameStates['gameResults']['decision'](dataInvite);
                                            }
                                        });

                                        setTimeout(
                                            function () {
                                                dialogResponse.find(".bootbox-close-button").trigger("click");
                                            }
                                            , 2000
                                        );

                                        return false;
                                    });
                            }, 100);
                        }
                    },
                    ok: {
                        label: 'Отказаться',
                        className: 'btn-info',
                        callback: function () {
                            return true;
                        }
                    },
                    new: {
                        label: 'Новая игра',
                        className: 'btn-danger',
                        callback: function () {
                            newGameButtonFunction(true);
                        }
                    }
                }
            });
        }
    },
    afterSubmit: {refresh: 1}
}

var gameState = 'noGame';
var gameSubState = 'waiting';
var queryNumber = 1;
var lastQueryTime = 0;
var gameOldState = '';

function commonCallback(data) {
    if (('gameState' in data) && !(data['gameState'] in gameStates)) {
        return;
    }

    if ('http_status' in data && (data['http_status'] === BAD_REQUEST || data['http_status'] === PAGE_NOT_FOUND)) {
        console.log(data['message']);
        return;
    }

    if ('query_number' in data && data['query_number'] != (queryNumber - 1)) {
        return;
    }

    gameOldState = gameState;
    gameOldSubState = gameSubState;

    if ('gameState' in data && gameState != data['gameState']) {
        gameState = data['gameState'];

        if('gameNumber' in data) {
            gameNumber = data['gameNumber'];
        }
    }

    if (NUM_BANK_FISHKI_PARAM in data) {
        numBankFishki = data[NUM_BANK_FISHKI_PARAM];
    }

    if (gameOldState != gameState) {
        soundPlayed = false;
    }

    if (gameState == 'myTurn') {
        if (pageActive == 'hidden') {
            snd.play();
            soundPlayed = true;
        } else if (!soundPlayed) {
            snd.play();
            soundPlayed = true;
        }
    }

    if ('lang' in data && data['lang'] != lang) {
        lang = data['lang'];
        if (lang == 'EN') {
            // ToDo not working under Yandex
            asyncCSS('https://xn--d1aiwkc2d.club/css/choose_css.css');
            //for avoiding errors in IDE
            //<?php include('instruction_eng.js'); ?>
        }
    }

    if (myUserNum === false)
        if ('yourUserNum' in data)
            myUserNum = data['yourUserNum']

    if ('gameSubState' in data)
        gameSubState = data['gameSubState'];
    //else gameSubState = gameStates[gameState]['1'];


    console.log(gameOldState + '->' + gameState);


    if ((gameOldState != gameState) || (gameOldSubState != gameSubState)) {
        if ('active_users' in data && data['active_users'] == 0) {
            clearTimeout(requestToServerEnabledTimeout);
            requestToServerEnabled = false;
        }

        if (dialog && canCloseDialog)
            dialog.modal('hide');
        if (intervalId) {
            clearInterval(intervalId);
            intervalId = 0;
        }
        if (canOpenDialog) {
            if (gameState == 'initGame' || gameState == 'initRatingGame') {
                dialog = bootbox.confirm({
                    message: ('comments' in data) ? data['comments'] : gameStates[gameState]['message'],
                    size: 'small',
                    buttons: {
                        confirm: {
                            label: 'Ok',
                        },
                        cancel: {
                            label: 'Новая игра',
                            className: 'btn-danger'
                        }
                    },
                    callback: function (result) {
                        if (!result) {
                            newGameButtonFunction(true);
                        }
                    }
                });
                if ('gameWaitLimit' in data) {
                    dialog.init(function () {
                        intervalId = setInterval(function () {
                            var igrokiWaiting = '';
                            if ('gameSubState' in data)
                                igrokiWaiting = "<br />Найдено игроков: " + data['gameSubState'];


                            if ('timeWaiting' in data) {
                                if (!tWaiting) {
                                    tWaiting = data['timeWaiting'];
                                }
                                if (!gWLimit) {
                                    gWLimit = data['gameWaitLimit'];
                                }
                            } else {
                                if (!gWLimit) {
                                    gWLimit = data['gameWaitLimit'];
                                }
                                if (!tWaiting) {
                                    tWaiting = 0
                                }
                            }

                            let content = data['comments'] + igrokiWaiting + '<br />Время подбора: ' + (tWaiting++) + '<br />Среднее время ожидания: ' + (gWLimit) + 'с';
                            dialog.find('.bootbox-body').html(content);
                        }, 1000);
                    });
                } else if ('ratingGameWaitLimit' in data)
                    dialog.init(function () {
                        intervalId = setInterval(function () {
                            if ('timeWaiting' in data)
                                if (!tWaiting)
                                    tWaiting = data['timeWaiting'];
                                else {
                                    data['timeWaiting'] = 0;
                                    if (!tWaiting)
                                        tWaiting = data['timeWaiting'];
                                }
                            dialog.find('.bootbox-body').html(data['comments'] +
                                '<br />Время подбора: ' +
                                (tWaiting++) +
                                'с' +
                                '<br />Лимит по времени: ' +
                                data['ratingGameWaitLimit'] +
                                'c' +
                                '<hr>Вы можете начать новую игру, если долго ждать..');
                        }, 1000);
                    });

            } else if (gameState == 'gameResults') {
                if ('inviteStatus' in data) {
                    if (data['inviteStatus'] == 'newGameStarting') {
                        document.location.reload(true);
                    } else if (data['inviteStatus'] == 'waiting') {
                        gameStates['gameResults']['results'](data);
                    } else {
                        gameStates['gameResults']['decision'](data);
                    }
                } else {
                    gameStates['gameResults']['results'](data);
                }
            } else if (!('noDialog' in gameStates[gameState])) {
                setTimeout(function () {
                        var message = '';
                        var cancelLabel = 'Закрывать через 5 секунд';

                        if ('comments' in data && (data['comments'] !== null)) {

                            if ('messageFunction' in gameStates[gameState]) {
                                message = gameStates[gameState]['messageFunction'](data['comments']);
                            } else {
                                message = data['comments'];
                            }
                        } else if ('message' in gameStates[gameState]) {
                            message = gameStates[gameState]['message'];
                        }

                        if (turnAutocloseDialog) {
                            if (timeToCloseDilog == 5) {
                                cancelLabel = 'Закрывать сразу';
                            } else {
                                cancelLabel = 'Закроется автоматически';
                            }
                        }

                        dialog = bootbox.confirm({
                            message: message,
                            size: 'medium',
                            buttons: {
                                confirm: {
                                    label: 'OK',
                                    className: 'btn-primary'
                                },
                                cancel: {
                                    label: cancelLabel,
                                    className: 'btn btn-outline-secondary'
                                }
                            },
                            callback: function (result) {
                                if (!result) {
                                    turnAutocloseDialog = true;

                                    if (!timeToCloseDilog) {
                                        timeToCloseDilog = 5;
                                    } else if (!automaticDialogClosed) {
                                        timeToCloseDilog = 1.5;
                                    }

                                    automaticDialogClosed = false;
                                }
                                activateFullScreenForMobiles();
                            }
                        });
                        dialog
                            .find('.modal-content').css({'background-color': 'rgba(255, 255, 255, 0.7)'})
                            .find('img').css('background-color', 'rgba(0, 0, 0, 0)');

                        if (turnAutocloseDialog) {
                            setTimeout(
                                function () {
                                    automaticDialogClosed = true;
                                    dialog.find(".bootbox-close-button").trigger("click");
                                }
                                , timeToCloseDilog * 1000
                            );
                        }
                    }
                    , 500
                );
            }
        }

        enableButtons();

        if ('from_' + gameOldState in gameStates[gameState])
            gameStates[gameState]['from_' + gameOldState](data);

        if ('action' in gameStates[gameState])
            gameStates[gameState]['action'](data);
    }

    if ('timeLeft' in data) {
        vremia.text = data['timeLeft'];
        vremiaMinutes = data['minutesLeft'];
        vremiaSeconds = data['secondsLeft'];
    }

    if ('log' in data)
        for (k in data['log'])
            gameLog.unshift(data['log'][k]);

    if ('chat' in data) {
        for (k in data['chat']) {

            if (!
                (((data['chat'][k].indexOf('Вы') + 1) === 1)
                    ||
                    ((data['chat'][k].indexOf('Новости') + 1) === 1))
            ) {
                buttons['chatButton']['svgObject'].bringToTop(buttons['chatButton']['svgObject'].getByName('chatButton' + 'Alarm'));
                buttons['chatButton']['svgObject'].getByName('chatButton' + 'Alarm').setData('alarm', true);
            }

            chatLog.unshift(data['chat'][k]);
        }
    }

    if ('winScore' in data) {
        winScore = data['winScore'];
    }

    responseData = data;

    if (pageActive == 'hidden' && gameState != 'chooseGame') {
        fetchGlobal(STATUS_CHECKER_SCRIPT, 'g', (uniqID === false) ? '0' : uniqID)
            .then((data) => {
                commonCallback(data);
            });
    }
}

function userScores(data) {
    if ("score_arr" in data) {
        if (ochki_arr === false) {
            ochki_arr = [];
            for (k in data['score_arr']) {
                if (k == data['yourUserNum']) {
                    ochki_arr[k] = window.game.scene.scenes[gameScene].add.text(0, 0, '', {
                        color: 'black',
                        font: 'bold ' + vremiaFontSize + 'px' + ' Courier'
                    });
                    ochki_arr[k].y = ochki.y;
                } else {

                    fontSize = vremiaFontSize - vremiaFontSizeDelta;
                    ochki_arr[k] = window.game.scene.scenes[gameScene].add.text(0, 0, '', {
                        color: 'black',
                        font: 'bold ' + fontSize + 'px' + ' Courier'
                    });
                    ochki_arr[k].y = ochki.y + vremiaFontSizeDelta / 1.3;
                }
            }
        }
        ochki.text = '';
        let x = ochki.x;
        for (let k in data['score_arr']) {
            if (k == data['yourUserNum'])
                ochki_arr[k].text = 'Ваши очки:' + data['score_arr'][k];
            else if (k == 0) {
                ochki_arr[k].text = data['userNames'][k] + ':' + data['score_arr'][k];
            } else if ((data['yourUserNum'] > 1 || k > 1) && data['score_arr'].length == 4) {
                let num = Number(k) + 1;
                ochki_arr[k].text = 'И' + num + ':' + data['score_arr'][k];
            } else {
                ochki_arr[k].text = data['userNames'][k] + ':' + data['score_arr'][k];
            }
            if (k == data['activeUser'])
                ochki_arr[k].setColor('green');
            else
                ochki_arr[k].setColor('black');
            ochki_arr[k].x = x;
            //ochki_arr[k].y = ochki.y;
            //ochki_arr[k].setFontSize(vremiaFontSizeDefault);
            ochki_arr[k].visible = true;
            x = ochki_arr[k].x + ochki_arr[k].width + 9 - Math.floor(data['score_arr'][k] / 100);

        }
    }
}

//
function activateFullScreenForMobiles() {
    if (gameWidth < gameHeight) {
        if (!isIOSDevice()) {
            document.body.requestFullscreen();
        }
    }
}

async function mobileShare() {
    thisUrl = window.location.href;
    thisTitle = document.title;
    shareObj = {
        title: thisTitle,
        url: thisUrl,
    }

    try {
        await navigator.share(shareObj);
        console.log('share SUCCESS!');
    } catch (err) {
        console.log('share ERROR! ' + err);
    }
}

function genDivGlobal(i, isChange = false) {
    if (i <= 31) {
        return '<div class="letter_' + i + '"></div>';
    }

    let coords = {
        32: {x: 526, y: 640},
        33: {x: 182, y: 24},
        34: {x: 507, y: 259},
        35: {x: 598, y: 259},
        36: {x: 691, y: 259},
        37: {x: 786, y: 259},
        38: {x: 881, y: 259},
        39: {x: 964, y: 259},
        40: {x: 3, y: 385},
        41: {x: 101, y: 385},
        42: {x: 186, y: 385},
        43: {x: 247, y: 396},
        44: {x: 324, y: 385},
        45: {x: 411, y: 385},
        46: {x: 503, y: 380},
        47: {x: 612, y: 385},
        48: {x: 714, y: 385},
        49: {x: 812, y: 385},
        50: {x: 908, y: 384},
        51: {x: 1001, y: 385},
        52: {x: 1080, y: 385},
        53: {x: 1, y: 507},
        54: {x: 92, y: 507},
        55: {x: 186, y: 507},
        56: {x: 295, y: 507},
        57: {x: 408, y: 507},
        58: {x: 502, y: 507},
        59: {x: 592, y: 507}
    };
    let koef = 44 / 76;
    let imgWidth = Math.round(1187 * koef);
    let styleBeg = 'display: inline-block; background: url(/img/letters_english.png); background-color:grey;background-position:-';
    let divTpl = '<div onmouseover="this.style.backgroundColor=\'green\';" onmouseout="this.style.backgroundColor=\'grey\';" style="display: inline-block; background: url(/img/letters_english.png); background-color:grey;background-position:-';
    let styleEnd = ' background-size: ' + imgWidth + 'px;'
        + ' width: 44px;'
        + ' height: 54px;'
        + ' border-radius: 5px;';
    let divEnd = ' background-size: ' + imgWidth + 'px;'
        + ' width: 44px;'
        + ' height: 54px;'
        + ' border-radius: 5px;'
        + '"></div>';

    if (i == 53) {
        return (isChange ? styleBeg : divTpl)
            + Math.round(coords[i].x * koef) + 'px -'
            + Math.round(coords[i].y * koef) + 'px; '
            + (isChange ? styleEnd : divEnd);
    } else if (i == 43) {
        return (isChange ? styleBeg : divTpl)
            + Math.round(coords[i]['x'] * koef) + 'px -'
            + Math.round((coords[i]['y'] - 8) * koef) + 'px; '
            + (isChange ? styleEnd : divEnd);
    } else if (i == 46) {
        return (isChange ? styleBeg : divTpl)
            + Math.round(coords[i]['x'] * koef) + 'px -'
            + Math.round((coords[i]['y'] + 6) * koef) + 'px; '
            + (isChange ? styleEnd : divEnd);
    } else if (i == 40) {
        return (isChange ? styleBeg : divTpl)
            + Math.round(coords[i]['x'] * koef) + 'px -'
            + Math.round((coords[i]['y'] - 2) * koef) + 'px; '
            + (isChange ? styleEnd : divEnd);
    } else if (i == 56) {
        return (isChange ? styleBeg : divTpl)
            + Math.round(coords[i]['x'] * koef * 0.9) + 'px -'
            + Math.round((coords[i]['y'] - 2) * koef * 0.9) + 'px;'
            + ' background-size: ' + Math.round(imgWidth * 0.9) + 'px;'
            + ' width: 44px;'
            + ' height: 54px;'
            + ' border-radius: 5px;'
            + (isChange ? '' : '"></div>');
    }

    return ((isChange ? styleBeg : divTpl)
        + Math.round((coords[i]['x'] + 3) * koef) + 'px -'
        + Math.round(coords[i]['y'] * koef) + 'px; '
        + (isChange ? styleEnd : divEnd));

}

function asyncCSS(href) {
    var css = document.createElement('link');
    css.rel = "stylesheet";
    css.href = href;
    document.head.appendChild(css);
}

window.onbeforeunload = function () {
    if (gameState == 'myTurn'
        || gameState == 'preMyTurn'
        || gameState == 'otherTurn'
        || gameState == 'initGame'
        || gameState == 'initRatingGame') {
        fetchGlobal(SET_INACTIVE_SCRIPT, '', '');
        return "Вы в игре - уверены, что хотите выйти?";
    }
};

document.addEventListener("visibilitychange", function () {
    pageActive = document.visibilityState;

    if (gameState == 'myTurn'
        || gameState == 'preMyTurn'
        || gameState == 'otherTurn'
        || gameState == 'initGame'
        || gameState == 'initRatingGame') {
        if (pageActive == 'hidden') {
            fetchGlobal(STATUS_CHECKER_SCRIPT, 'g', (uniqID == false) ? '0' : uniqID)
                .then((data) => {
                    commonCallback(data);
                });
        }
    }
});

function showFullImage(idImg, width, oldWidth = 198) {
    if ($('#' + idImg).width() < width) {
        if (fullImgID !== false) {
            $('#' + fullImgID).css('z-index', '50');
            $('#' + fullImgID).css('top', '0px');
            $('#' + fullImgID).css('left', '0px');
            $('#' + fullImgID).css('position', 'relative');
            $('#' + fullImgID).width(fullImgWidth);
        }
        fullImgID = idImg;
        fullImgWidth = oldWidth;
        $('#' + idImg).css('position', 'fixed');
        $('#' + idImg).css('top', (Math.round(window.innerHeight - width / 16 * 9) / 3) + 'px');
        $('#' + idImg).css('left', (Math.round(window.innerWidth - width) / 2) + 'px');
        $('#' + idImg).width(width);
        $('#' + idImg).css('z-index', '100');
    } else {
        fullImgID = false;
        fullImgWidth = 0;
        $('#' + idImg).css('z-index', '50');
        $('#' + idImg).css('top', '0px');
        $('#' + idImg).css('left', '0px');
        $('#' + idImg).css('position', 'relative');
        $('#' + idImg).width(oldWidth);
    }
}

function mergeTheIDs(oldKey, commonID) {
    if (oldKey.trim() == '') {
        let resp = {result: 'error', message: 'Задано пустое значение'};
        showCabinetActionResult(resp);

        return;
    }

    fetchGlobal(MERGE_IDS_SCRIPT, '', 'oldKey=' + btoa(oldKey) + '&commonID=' + commonID)
        .then((resp) => {
            showCabinetActionResult(resp);
        });
}

function showCabinetActionResult(response) {
    if ('message' in response) {
        let background = (response['result'].indexOf('error') + 1)
            ? '#f99'
            : '#9f9';
        cabinetAlert = bootbox.alert({
            message: response['message'],
            locale: 'ru',
            size: 'small',
            closeButton: false,
            centerVertical: true
        }).find('.modal-content').css({
            'background-color': background
        });
    }
}

function copyKeyForID(key, commonID = '') {
    $('#key_for_id').select();
    document.execCommand("copy");
}

function copyDonateKey() {
    $('#donate_id').select();
    document.execCommand("copy");
}

function deleteBan(commonID) {
    fetchGlobalMVC(DELETE_BAN_URL + commonID, '', 'commonID=' + commonID)
        .then((resp) => {
            showCabinetActionResult(resp);
        });
}

function savePlayerName(name, commonID = '') {
    if (name.trim() == '') {
        let resp = {result: 'error', message: 'Задано пустое значение'};
        showCabinetActionResult(resp);

        return;
    }

    fetchGlobal(SET_PLAYER_NAME_SCRIPT, '', 'name=' + encodeURIComponent(name) + (commonID != '' ? '&commonID=' + commonID : ''))
        .then((resp) => {
            if (resp['result'] == 'saved') {
                $('#playersNikname').text(name);
            }
            showCabinetActionResult(resp);
        });
}

function savePlayerAvatar(url, commonID) {
    // складируем форму в ......форму))
    const checkElement = document.getElementById("player_avatar_file");
    if (!checkElement.checkValidity()) {
        /*
        <?php
            // include_once 'php/PlayersLangProvider.php';
        ?>
        */
        showCabinetActionResult({
            result: 'error',
            message: 'Ошибка! Выберите файл-картинку размером не более <?= round(Dadata\Players::MAX_UPLOAD_SIZE / 1024 / 1024, 2); ?>MB'
        });

        return false;
    }

    var formData = new FormData($('#superForm')[0]);

    if (pageActive != 'hidden') {
        requestSended = true;
        requestTimestamp = (new Date()).getTime();
    }

    let URL = useLocalStorage
        ? (
            '/<?=$dir?>/php/yowser/index.php'
            + '?cooki='
            + localStorage.erudit_user_session_ID
            + '&script='
            + AVATAR_UPLOAD_SCRIPT
            + '&queryNumber='
            + (queryNumber++)
            + '&lang=' + lang
            + (pageActive === 'hidden' ? '&page_hidden=true' : '')
        )
        : (
            '/<?=$dir?>/php/'
            + AVATAR_UPLOAD_SCRIPT
            + '?queryNumber=' + (queryNumber++)
            + '&lang=' + lang
            + (pageActive === 'hidden' ? '&page_hidden=true' : '')
        );

    $.ajax({
        url: URL,
        type: 'POST',
        data: formData,
        async: false,
        cache: false,
        contentType: false,
        processData: false,
        success: function (returndata) {
            resp = JSON.parse(returndata);

            if (resp['result'] === 'saved') {
                $('#playersAvatar').html('<img src="' + resp['url'] + '" width="100px" max-height = "100px"/>');
            }

            showCabinetActionResult(resp);

            return false;
        }
    });

    return false;
}

function refreshId(element_id, url) {
    let respMessage = 'Ошибка загрузки статистики';

    $.ajax({
        url: url,
        type: 'GET',
        async: false,
        cache: false,
        contentType: false,
        processData: false,
        success: function (returndata) {
            resp = JSON.parse(returndata);
            $('#' + element_id).html(resp.message + resp.pagination);
        }
    });
}

function getStatPageGlobal() {
    let urlPart = STATS_URL + commonId;
    let respMessage = 'Ошибка загрузки статистики';

    if (commonId) {
        $.ajax({
            url: '/' + urlPart,
            type: 'GET',
            async: false,
            cache: false,
            contentType: false,
            processData: false,
            success: function (returndata) {
                resp = JSON.parse(returndata);
                respMessage = resp.message + resp.pagination;
            }
        });
    } else {
        respMessage = 'Для просмотра статистики сыграйте хотя бы одну партию';
    }

    return respMessage;
}

function savePlayerAvatarUrl(url, commonID) {
    if (url.trim() == '') {
        let resp = {result: 'error', message: 'Задано пустое значение'};
        showCabinetActionResult(resp);

        return;
    }

    fetchGlobal(SET_AVATAR_SCRIPT, '', 'avatar=' + encodeURIComponent(url) + '&commonID=' + commonID)
        .then((resp) => {
            if (resp['result'] == 'saved')
                $('#playersAvatar').html('<img src="' + url + '" width="100px" max-height = "100px"/>');
            showCabinetActionResult(resp);
        });
}

function initLotok() {
    for (var i = 0; i < lotokCapacityY; i++) {
        lotokCells[i] = [];
        for (var j = 0; j < lotokCapacityX; j++)
            lotokCells[i][j] = false;
    }
}

function lotokFindSlotXY() {
    let XY = [];
    outer:
        for (var i = 0; i < lotokCapacityY; i++)
            for (var j = 0; j < lotokCapacityX; j++)
                if (lotokCells[i][j] === false) {
                    XY[0] = j;
                    XY[1] = i;
                    lotokCells[i][j] = true;
                    break outer;
                }
    return XY;
}

function lotokFindSlotReverseXY() {
    let XY = [];
    outer:
        for (var i = lotokCapacityY - 1; i >= 0; i--)
            for (var j = lotokCapacityX - 1; j >= 0; j--)
                if (lotokCells[i][j] === false) {
                    XY[0] = j;
                    XY[1] = i;
                    lotokCells[i][j] = true;
                    break outer;
                }
    return XY;
}

function lotokGetX(X, Y) {
    if (buttonHeightKoef == 1) {
        return lotokX + lotokCellStep * fishkaScale * X;
    } else
        return lotokX + lotokCellStep * X;
}

function lotokGetY(X, Y) {
    return lotokY + lotokCellStepY * Y;
}

function lotokFreeXY(X, Y) {
    lotokCells[Y][X] = false;
}

function placeToLotok(fishka) {
    if (fishka.getData('isTemporary')) {
        var slotXY = lotokFindSlotReverseXY();
    } else {
        var slotXY = lotokFindSlotXY();
    }

    fishka.setData('cellX', false);
    fishka.setData('cellY', false);
    fishka.setData('lotokX', slotXY[0]);
    fishka.setData('lotokY', slotXY[1]);
    fishka.x = lotokGetX(slotXY[0], slotXY[1]);
    fishka.y = lotokGetY(slotXY[0], slotXY[1]);
}

function disableButtons() {
    for (let k in buttons) {
        if (buttons[k]['svgObject'] !== false)
            buttons[k]['svgObject'].disableInteractive();
    }
}

function enableButtons() {
    //if (bootBoxIsOpenedGlobal()) return;
    for (let k in buttons)
        if ('enabled' in buttons[k]) {
            if (gameState in buttons[k]['enabled']) {
                if (buttons[k]['svgObject'] !== false) {
                    buttons[k]['svgObject'].setInteractive();
                    buttons[k]['svgObject']
                        .bringToTop(buttons[k]['svgObject']
                            .getByName(k + 'Otjat'));
                }

            } else {
                if (buttons[k]['svgObject'] !== false) {
                    buttons[k]['svgObject'].disableInteractive();
                    buttons[k]['svgObject']
                        .bringToTop(buttons[k]['svgObject']
                            .getByName(k + 'Inactive'));
                    console.log('Inactive');
                }

            }
        } else {
            if (buttons[k]['svgObject'] !== false)
                buttons[k]['svgObject'].setInteractive();
        }
}


function placeFishki(fishki) {
    var maxI = 0;
    for (var i in container) {
        if (i > maxI)
            maxI = i;
        console.log('!!!!!!' + i);
        if (container[i].getData('cellX')) {
            cells[container[i].getData('cellX')][container[i].getData('cellY')][0] = false;
            cells[container[i].getData('cellX')][container[i].getData('cellY')][1] = false;
        }

        if ((container[i].getData('lotokX') !== false) && (container[i].getData('lotokY') !== false)) {
            lotokFreeXY(container[i].getData('lotokX'), container[i].getData('lotokY'));
            container[i].setData('lotokX', false);
            container[i].setData('lotokY', false);
        }

        container[i].destroy();
        //container.splice(i,1);
    }

    for (let i = maxI; i >= 0; i--)
        if (i in container)
            container.splice(i, 1);

    for (let i = 0; i < fishki.length; i++) {
        let lotokXY = lotokFindSlotXY();

        container.push(getFishkaGlobal(fishki[i], lotokGetX(lotokXY[0], lotokXY[1]), lotokGetY(lotokXY[0], lotokXY[1]), this.game.scene.scenes[gameScene], true, userFishkaSet).setData('lotokX', lotokXY[0]).setData('lotokY', lotokXY[1]));
    }
}

//<?php include('globals/getFishkaGlobalFunction.js')?>
//<?php include('globals/ajaxGetGlobalFunction.js')?>
//<?php include('globals/parseDeskGlobalFunction.js')?>
//<?php include('globals/initCellsGlobalFunction.js')?>
//<?php include('globals/findPlaceGlobalFunction.js')?>
//<?php include('globals/changeFishkiGlobalFunction.js')?>
//<?php include('globals/bootBoxIsOpenedGlobalFunction.js')?>
//<?php include('globals/openWindowGlobalFunction.js')?>
//<?php include('globals/buttonGlobalFunctions.js')?>
//<?php include('globals/gagetTypeFunctions.js')?>
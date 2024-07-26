//
function () {
    var letters = [];
    var atlasTexture = this.textures.get('megaset');

    var frames = atlasTexture.getFrameNames();

    noNetworkImg = this.add.image(200, 200, 'no_network');
    noNetworkImg.setScale(2);
    noNetworkImg.x = game.config.width / 2;
    noNetworkImg.y = game.config.height / 2;
    noNetworkImg.setDepth(10000);
    noNetworkImg.visible = false;

    var back = this.add.image(backX, backY, 'back');
    back.alpha = 0.3;
    back.setOrigin(0, 0);

    // back.setScale(backScale); // todo непонятно как работает, картинка съезжает хз куда


    var ground = this.add.image(385, 375, 'ground');
    ground.setOrigin(0, 0);
    ground.x = game.config.width - ground.width;
    ground.y = screenOrient === HOR
        ? 0
        : topHeight;
    ground.setCrop(16 * 2, 3 * 2, 550 * 2, 550 * 2);

    // Past-adjusting back-image
    if (backY > ground.height) {
        back.y = ground.height - 30;
    } else if ((backY + ground.height) > game.config.height) {
        back.y = game.config.height - back.height;
    }

    stepX = game.config.width - ground.width;
    stepY = 0;
    initLotok();

    initCellsGlobal();

    for (let k in buttons) {

        if ('preCalc' in buttons[k])
            buttons[k]['preCalc']();

        buttons[k]['svgObject'] = getSVGButton(buttons[k]['x'], buttons[k]['y'], k, this);

        buttons[k]['svgObject'].on('pointerup', function () {
            buttons[k]['svgObject'].bringToTop(buttons[k]['svgObject'].getByName(k + 'Otjat'));
            if ('pointerupFunction' in buttons[k])
                buttons[k]['pointerupFunction']();
        });

        buttons[k]['svgObject'].on('pointerdown', function () {
            buttons[k]['svgObject'].bringToTop(buttons[k]['svgObject'].getByName(k + 'Najatie'));
        });

        buttons[k]['svgObject'].on('pointerover', function () {
            if (k == 'chatButton') {
                if (buttons['chatButton']['svgObject'].getByName('chatButton' + 'Alarm').getData('alarm') !== true)
                    buttons[k]['svgObject'].bringToTop(buttons[k]['svgObject'].getByName(k + 'Navedenie'));
            } else
                buttons[k]['svgObject'].bringToTop(buttons[k]['svgObject'].getByName(k + 'Navedenie'));
        });

        buttons[k]['svgObject'].on('pointerout', function () {
            if (k == 'chatButton') {
                if (buttons['chatButton']['svgObject'].getByName('chatButton' + 'Alarm').getData('alarm') !== true)
                    buttons[k]['svgObject'].bringToTop(buttons[k]['svgObject'].getByName(k + 'Otjat'));
            } else if ('enabled' in buttons[k]) {
                if (gameState in buttons[k]['enabled'])
                    buttons[k]['svgObject'].bringToTop(buttons[k]['svgObject'].getByName(k + 'Otjat'));
            } else
                buttons[k]['svgObject'].bringToTop(buttons[k]['svgObject'].getByName(k + 'Otjat'));
        });
    }

    let numTopButtons = 0;
    let sumWidth = 0;
    for (let tbK in topButtons) {
        numTopButtons++;
        topButtons[tbK].displayWidth = buttons[tbK]['svgObject'].displayWidth;
        sumWidth += topButtons[tbK].displayWidth;
    }
    let stepXTopButtons = (knopkiWidth - sumWidth) / (numTopButtons + 1);

    let currentWidth = 0;
    for (let tbK in topButtons) {
        buttons[tbK]['svgObject'].x = stepXTopButtons + currentWidth + buttons[tbK]['svgObject'].displayWidth / 2;
        currentWidth += stepXTopButtons + buttons[tbK]['svgObject'].displayWidth;
    }

    buttons['razdvButton']['svgObject'].disableInteractive();
    buttons['razdvButton']['svgObject'].visible = false;

    if (buttons['submitButton']['svgObject'] !== false) {
        buttons['submitButton']['svgObject'].disableInteractive();
        buttons['submitButton']['svgObject'].bringToTop(buttons['submitButton']['svgObject'].getByName('submitButton' + 'Inactive'));
    }

//    <?php include('create/fishkaDragEvents.js')?>

//    <?php include('create/getSVGButtonFunction.js')?>


    ochki = this.add.text(lotokX - lotokCellStep / 2 + 5,
        buttons['newGameButton']['svgObject'].y + buttons['newGameButton']['svgObject'].height - 15,
        'Ваши очки:0',
        {
            color: 'black',
            font: 'bold ' + vremiaFontSize + 'px' + ' Courier',
        });

    vremia = this.add.text(ochki.x, ochki.y + ochki.height + 15, 'Время на ход 2:00',
        {
            color: 'black',
            font: 'bold ' + vremiaFontSize + 'px' + ' Courier',
        });
}
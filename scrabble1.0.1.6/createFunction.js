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

    var back = this.add.sprite(gameWidth / 2, gameHeight / 2, 'back');
    back.displayWidth = this.sys.canvas.width;
    back.displayHeight = this.sys.canvas.height;

    WebView.postEvent('web_app_set_header_color', false, {
        color: '#2C3C6C',
    });

    var ground = this.add.image(385, 375, 'ground');
    ground.setOrigin(0, 0);
    ground.x = game.config.width - ground.width;
    ground.y = screenOrient === HOR
        ? 0
        : topHeight;
    ground.setCrop(16 * 2, 3 * 2, 550 * 2, 550 * 2);

    stepX = game.config.width - ground.width;
    stepY = (screenOrient === HOR) ? 0 : topHeight;
    initLotok();

    initCellsGlobal();

    for (let k in buttons) {

        if ('preCalc' in buttons[k])
            buttons[k]['preCalc']();

        buttons[k]['svgObject'] = getSVGButton(buttons[k]['x'], buttons[k]['y'], k, this);

        buttons[k]['svgObject'].on('pointerup', function () {
            buttons[k]['svgObject'].bringToTop(buttons[k]['svgObject'].getByName(k + OTJAT_MODE));
            if ('pointerupFunction' in buttons[k])
                buttons[k]['pointerupFunction']();
        });

        buttons[k]['svgObject'].on('pointerdown', function () {
            buttons[k]['svgObject'].bringToTop(buttons[k]['svgObject'].getByName(k + 'Najatie'));
        });

        buttons[k]['svgObject'].on('pointerover', function () {
            if (k == 'chatButton') {
                if (buttons['chatButton']['svgObject'].getByName('chatButton' + ALARM_MODE).getData('alarm') !== true)
                    buttons[k]['svgObject'].bringToTop(buttons[k]['svgObject'].getByName(k + 'Navedenie'));
            } else
                buttons[k]['svgObject'].bringToTop(buttons[k]['svgObject'].getByName(k + 'Navedenie'));
        });

        buttons[k]['svgObject'].on('pointerout', function () {
            if (k == 'chatButton') {
                if (buttons['chatButton']['svgObject'].getByName('chatButton' + ALARM_MODE).getData('alarm') !== true)
                    buttons[k]['svgObject'].bringToTop(buttons[k]['svgObject'].getByName(k + OTJAT_MODE));
            } else if ('enabled' in buttons[k]) {
                if (gameState in buttons[k]['enabled'])
                    buttons[k]['svgObject'].bringToTop(buttons[k]['svgObject'].getByName(k + OTJAT_MODE));
            } else
                buttons[k]['svgObject'].bringToTop(buttons[k]['svgObject'].getByName(k + OTJAT_MODE));
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

    for (let k in players) {
        players[k]['svgObject'] = getSVGBlock(players[k]['x'], players[k]['y'], k, this, players[k].scalable, 'numbers' in players[k]);
        players[k]['svgObject'].bringToTop(players[k]['svgObject'].getByName(k + OTJAT_MODE));
        players[k]['svgObject'].getByName(k + ALARM_MODE).setVisible(false);
    }

//    <?php include('create/fishkaDragEvents.js')?>

//    <?php include('create/getSVGButtonFunction.js')?>


    ochki = this.add.text(lotokX - lotokCellStep / 2 + 5,
        buttons['newGameButton']['svgObject'].y + buttons['newGameButton']['svgObject'].height - 15,
        'Ваши очки:0',
        {
            color: 'black',
            font: 'bold ' + vremiaFontSize + 'px' + ' Courier',
        }).setVisible(false); // todo delete ochki

    vremia = this.add.text(ochki.x, ochki.y + ochki.height + 15, 'Время на ход 2:00',
        {
            color: 'black',
            font: 'bold ' + vremiaFontSize + 'px' + ' Courier',
        }).setVisible(false); // todo delete vremia
}
//
function () {

    var progressBar = this.add.graphics();
    var progressBox = this.add.graphics();
    progressBox.fillStyle(0x222222, 0.3);
    progressBox.fillRect(gameWidth / 2 - 320 / 2, gameHeight / 2 - 50 / 5, 320, 50);

    var showCaution = false;

    var textWidth = this.cameras.main.width;
    var textHeight = this.cameras.main.height;
    var loadingText = this.make.text({
        x: textWidth / 2,
        y: textHeight / 2 - 50,
        text: LOADING_TEXT,
        style: {
            font: '20px monospace',
            fill: '#000000'
        }
    });
    loadingText.setOrigin(0.5, 0.5);

    this.load.on('progress', function (value) {
        progressBar.clear();
        progressBar.fillStyle(0xffffff, 1);
        progressBar.fillRect(gameWidth / 2 + 10 - 320 / 2, gameHeight / 2 - 30 / 2 + 15, 300 * value, 30);
    });

    this.load.on('complete', function () {
        progressBar.destroy();
        progressBox.destroy();
        loadingText.destroy();
        if (showCaution) {
            androidText1.destroy();
            androidText2.destroy();
        }
    });

    preloaderObject = this;

    this.load.image('no_network', '/img/no_network_transparent.png');
    this.load.svg('ground', '/img/' + GROUND_FILE, {'width': 513 * 2, 'height': 500 * 2});
    this.load.image('zvezda', '/img/star_transparent.png');
    this.load.svg('zvezdaVerh', '/img/star_transparent_2.svg', {scale: 0.025 * 2});
    this.load.svg('zvezdaCenter', '/img/star_transparent_2.svg', {scale: 0.06 * 2});
    this.load.atlas('fishka_empty', '/img/fishka_empty.png', '/img/fishka_empty.json');
    this.load.atlas('megaset', '/img/letters.png', '/img/letters.json');
    this.load.atlas('digits', '/img/letters.png', '/img/nums.json');
    this.load.atlas('megaset_english', '/img/letters_english.png', '/img/letters_english.json');
    this.load.svg('back', '/img/back2.svg', {'height': 1980, 'width': 1080});

    for (let k in buttons) {
        if ('modes' in buttons[k])
            buttons[k]['modes'].forEach(mode => this.load.svg(k + mode, '/img/' + mode.toLowerCase() + '/' + buttons[k]['filename'] + '.svg',
                'width' in buttons[k]
                    ? {
                        'width': buttons[k]['width'],
                        'height': 'height' in buttons[k] ? buttons[k].height : buttonHeight,
                    }
                    : {
                        'height': 'height' in buttons[k] ? buttons[k].height : buttonHeight,
                    }
            ));
        else
            modes.forEach(mode => this.load.svg(k + mode, '/img/' + mode.toLowerCase() + '/' + buttons[k]['filename'] + '.svg?ver=2',
                'width' in buttons[k]
                    ? {
                        'width': buttons[k]['width'],
                        'height': 'height' in buttons[k] ? buttons[k].height : buttonHeight,
                    }
                    : {
                        'height': 'height' in buttons[k] ? buttons[k].height : buttonHeight,
                    }
            ));
    }

    for (let k in players) {
        playerBlockModes.forEach(mode => this.load.svg(k + mode, '/img/' + mode.toLowerCase() + '/' + players[k]['filename'] + '.svg',
            'width' in players[k]
                ? {
                    'width': players[k]['width'],
                    'height': 'height' in players[k] ? players[k].height : buttonHeight,
                }
                : {
                    'height': 'height' in players[k] ? players[k].height : buttonHeight,
                }
        ));
    }


    playerBlockModes.forEach(mode => {
        for (let k in digits.playerDigits[mode]) {
            this.load.svg(mode + '_' + 'player_' + k, '/img/' + mode.toLowerCase() + '/' + digits.playerDigits[mode][k]['filename'] + '.svg',
                {'height': buttonHeight * 0.5 / (buttonHeightKoef < 1 ? 0.5 : 1), 'width': buttonHeight * 0.23 * 0.5 / (buttonHeightKoef < 1 ? 0.5 : 1)}
            );

            this.load.svg(mode + '_' + 'timer_' + k, '/img/' + mode.toLowerCase() + '/' + digits.timerDigits[mode][k]['filename'] + '_' + modesColors[mode] + '.svg',
                {'height': buttonHeight * 0.5 / (buttonHeightKoef < 1 ? 0.8 : 1), 'width': buttonHeight * 0.4 * 0.5 / (buttonHeightKoef < 1 ? 0.8 : 1)}
            );
        }

        this.load.svg(mode + '_' + 'dvoetoch', '/img/' + mode.toLowerCase() + '/numbers/' + 'dvoetoch'  + '_' + modesColors[mode]+ '.svg',
            {'height': buttonHeight * 0.5 / (buttonHeightKoef < 1 ? 0.8 : 1), 'width': buttonHeight * 0.15 * 0.5 / (buttonHeightKoef < 1 ? 0.8 : 1)}
        );
    });

    loadFishkiSet(userFishkaSet);
}
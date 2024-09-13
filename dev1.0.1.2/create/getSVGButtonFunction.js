
function getSVGButton(X, Y, buttonName, _this) {
    var elements = [];
    var elementNumber = 0;
    if ('modes' in buttons[buttonName]) {
        for (let mode in buttons[buttonName]['modes']) {
            elements[elementNumber] = _this.add.image(0, 0, buttonName + buttons[buttonName]['modes'][mode])
                .setName(buttonName + buttons[buttonName]['modes'][mode])
                .setScale(1, buttonHeightKoef);
            elementNumber++;
        }
    } else {
        for (let mode in modes) {
            elements[elementNumber] = _this.add.image(0, 0, buttonName + modes[mode])
                .setName(buttonName + modes[mode])
                .setScale(1, buttonHeightKoef);
            elementNumber++;
        }
    }

    var container = _this.add.container(X, Y, elements);
    container.setSize(elements[0].displayWidth, elements[0].displayHeight);
    container.setInteractive();

    return container;
}

function getSVGBlock(X, Y, buttonName, _this, scalable, hasDigits = false) {
    let elements = [];
    let elementNumber = 0;

    for (let mode in playerBlockModes) {
        elements[elementNumber] = _this.add.image(0, 0, buttonName + playerBlockModes[mode])
            .setName(buttonName + playerBlockModes[mode]);
        if(scalable) {
            elements[elementNumber].setScale(1, buttonHeightKoef);
        }
        elementNumber++;
    }

    if (hasDigits) {
        let imgName = 'numbersX3' in players[buttonName] ? 'timer_' : 'player_';
        let y = 'numbersY' in players[buttonName] ? players[buttonName].numbersY : 0;
        let x3 = 'numbersX3' in players[buttonName] ? players[buttonName].numbersX3 : elements[0].displayWidth * 0.75 * 0.5;
        let x2 = 'numbersX2' in players[buttonName] ? players[buttonName].numbersX2: elements[0].displayWidth * 0.6 * 0.5;
        let x1 = 'numbersX1' in players[buttonName] ? players[buttonName].numbersX1 : elements[0].displayWidth * 0.45 * 0.5;

        playerBlockModes.forEach(mode => {
            if ('dvoetochX' in players[buttonName]) {
                elements[elementNumber] = _this.add.image(
                    players[buttonName].dvoetochX
                    , y
                    , mode + '_' + 'dvoetoch')
                    .setName(mode + '_' + 'dvoetoch')
                    .setVisible(false);

                elementNumber++;
            }

            for (let k in digits.playerDigits[mode]) {
                elements[elementNumber] = _this.add.image(
                    x3
                    , y
                    , mode + '_' + imgName + k)
                    .setName(mode + '_' + k.replace('digit_', '') + '_3')
                    .setVisible(false);

                elementNumber++;
            }
        });

        playerBlockModes.forEach(mode => {
            for (let k in digits.playerDigits[mode]) {
                elements[elementNumber] = _this.add.image(
                    x2
                    , y
                    , mode + '_' + imgName + k)
                    .setName(mode + '_' + k.replace('digit_', '') + '_2')
                    .setVisible(false);

                if(scalable) {
                    elements[elementNumber].setScale(buttonHeightKoef, buttonHeightKoef);
                }

                elementNumber++;
            }
        });

        playerBlockModes.forEach(mode => {
            for (let k in digits.playerDigits[mode]) {
                elements[elementNumber] = _this.add.image(
                    x1
                    , y
                    , mode + '_' + imgName + k)
                    .setName(mode + '_' + k.replace('digit_', '') + '_1')
                    .setVisible(false);

                if(scalable) {
                    elements[elementNumber].setScale(buttonHeightKoef, buttonHeightKoef);
                }

                elementNumber++;
            }
        });
    }

    let container = _this.add.container(X, Y, elements);
    container.setSize(elements[0].displayWidth, elements[0].displayHeight);

    if (hasDigits) {
        container.setAlpha(INACTIVE_USER_ALPHA);
    }

    return container;
}

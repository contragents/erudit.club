
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
        playerBlockModes.forEach(mode => {
            for (let k in digits.playerDigits[mode]) {
                elements[elementNumber] = _this.add.image(elements[0].displayWidth * 0.75 * 0.5, 0, mode + '_' + 'player_' + k)
                    .setName(mode + '_' + k.replace('digit_', '') + '_3')
                    .setVisible(false);

                /*if(scalable) {
                    elements[elementNumber].setScale(buttonHeightKoef, buttonHeightKoef);
                }*/
                elementNumber++;
            }
        });

        playerBlockModes.forEach(mode => {
            for (let k in digits.playerDigits[mode]) {
                elements[elementNumber] = _this.add.image(elements[0].displayWidth * 0.6 * 0.5, 0, mode + '_' + 'player_' + k)
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
                elements[elementNumber] = _this.add.image(elements[0].displayWidth * 0.45 * 0.5, 0, mode + '_' + 'player_' + k)
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
    // container.setInteractive();

    return container;
}

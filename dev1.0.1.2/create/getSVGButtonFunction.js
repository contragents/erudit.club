
function getSVGButton(X, Y, buttonName, _this) {
    var elements = [];
    var elementNumber = 0;
    if ('modes' in buttons[buttonName]) {
        for (let mode in buttons[buttonName]['modes']) {
            elements[elementNumber] = _this.add.image(0, 0, buttonName+buttons[buttonName]['modes'][mode])
                .setName(buttonName+buttons[buttonName]['modes'][mode])
                .setScale(buttonHeightKoef , buttonHeightKoef);
            elementNumber++;
        }
    } else {
        for (let mode in modes) {
            elements[elementNumber] = _this.add.image(0, 0, buttonName+modes[mode])
                .setName(buttonName+modes[mode])
                .setScale(1 , buttonHeightKoef);
            elementNumber++;
        }
    }

    var container = _this.add.container(X, Y, elements);
    container.setSize(elements[0].displayWidth,elements[0].displayHeight);
    container.setInteractive();
    return container;
        }
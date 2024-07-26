
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
    /*
    elements[0] = _this.add.image(0, 0, buttonName+'Otjat').setName(buttonName+'Otjat').setScale(1 , buttonHeightKoef);
    elements[1] = _this.add.image(0, 0, buttonName+'Alarm').setName(buttonName+'Alarm').setScale(1 , buttonHeightKoef);
    elements[2] = _this.add.image(0, 0, buttonName+'Inactive').setName(buttonName+'Inactive').setScale(1 , buttonHeightKoef);
    elements[3] = _this.add.image(0, 0, buttonName+'Navedenie').setName(buttonName+'Navedenie').setScale(1 , buttonHeightKoef);
    elements[4] = _this.add.image(0, 0, buttonName+'Najatie').setName(buttonName+'Najatie').setScale(1 , buttonHeightKoef);
    */

    var container = _this.add.container(X, Y, elements);
    container.setSize(elements[0].displayWidth, elements[0].displayHeight);
    container.setInteractive();

    return container;
}
//
function getFishkaGlobal(numLetter, X, Y, _this, draggable = true, fishkaSet = DEFAULT_FISHKA_SET) {
    if (fishkaSet != DEFAULT_FISHKA_SET) {
        if (fishkaSet in fishkiLoaded && numLetter in fishkiLoaded[fishkaSet]) {
            let fishka = _this.add.image(0, 0, fishkiLoaded[fishkaSet][numLetter]);
            fishka.displayWidth = FISHKA_AVAILABLE_SETS[fishkaSet] * 2;
            fishka.displayHeight = FISHKA_AVAILABLE_SETS[fishkaSet] * 2;
            var container = _this.add.container(X, Y, [fishka]);
            container.setSize(fishka.displayWidth, fishka.displayHeight);
            container.setData('letter', numLetter);
            container.setData('cellX', false);
            container.setData('cellY', false);
            container.setInteractive();
            if (draggable) {
                _this.input.setDraggable(container);
                if (fishkaScale > 1) {
                    container.setScale(fishkaScale);
                }
            }

            return container;
        } else {
            if (!(fishkaSet in fishkiLoaded)) {
                loadFishkiSet(fishkaSet);
            }
        }
    }

    let fishka = _this.add.image(0, 0, 'fishka_empty');
    fishka.displayWidth = 32 * 2;
    fishka.displayHeight = 32 * 2;
    const correction = 1.5;
    const correctionLetter = (numLetter % 1000 <= 33
        ? correction
        : correction * 1.05);

    if (numLetter == 999) {
        let starLetter = _this.add.image(0, 0, 'zvezdaCenter');
        starLetter.setOrigin(.5, .5);
        var container = _this.add.container(X, Y, [fishka, starLetter]);
    } else if (numLetter > 999) {
        let atlasTexture = _this.textures.get('megaset');
        let frames = atlasTexture.getFrameNames();

        let atlasTextureEnglish = _this.textures.get('megaset_english');
        let framesEnglish = atlasTextureEnglish.getFrameNames();

        let starLetter = _this.add.image(0, 0, 'zvezdaVerh');
        starLetter.setOrigin(.5, .5);
        starLetter.x = fishka.displayWidth - 13 * 2 - starLetter.displayWidth;
        starLetter.y = fishka.displayHeight - 27 * 2 - starLetter.displayHeight;

        let testLetter = (numLetter <= 1033
            ? _this.add.image(0, 0, 'megaset', frames[numLetter - 999 - 1])
            : _this.add.image(0, 0, 'megaset_english', framesEnglish[numLetter - 999 - 1]));

        testLetter.displayWidth = fishka.displayWidth / correctionLetter;
        testLetter.scaleY = testLetter.scaleX;
        testLetter.setOrigin(.5, .5);
        testLetter.x = fishka.displayWidth - 15 * 2 - testLetter.displayWidth;
        testLetter.y = fishka.displayHeight - 3 * 2 - testLetter.displayHeight;

        var container = _this.add.container(X, Y, [fishka, testLetter, starLetter]);
    } else {
        let atlasTexture = _this.textures.get('megaset');
        let frames = atlasTexture.getFrameNames();

        let atlasTextureEnglish = _this.textures.get('megaset_english');
        let framesEnglish = atlasTextureEnglish.getFrameNames();

        let testLetter = (numLetter <= 33
            ? _this.add.image(0, 0, 'megaset', frames[numLetter])
            : _this.add.image(0, 0, 'megaset_english', framesEnglish[numLetter]));

        testLetter.displayWidth = fishka.displayWidth / correctionLetter;
        testLetter.scaleY = testLetter.scaleX;
        testLetter.setOrigin(.5, .5);
        testLetter.x = fishka.displayWidth - 15 * 2 - testLetter.displayWidth;
        testLetter.y = fishka.displayHeight - 3 * 2 - testLetter.displayHeight;

        if (letterPrices.get(numLetter) < 10) {
            let digitLetter = _this.add.image(0, 0, 'digits', letterPrices.get(numLetter));

            digitLetter.displayWidth = fishka.displayWidth / correction / 2;
            digitLetter.scaleY = digitLetter.scaleX;
            digitLetter.setOrigin(.5, .5);
            digitLetter.x = fishka.displayWidth - 13 * 2 - digitLetter.displayWidth;
            digitLetter.y = fishka.displayHeight - 11 * 2 - digitLetter.displayHeight;

            var container = _this.add.container(X, Y, [fishka, testLetter, digitLetter]);
        } else {
            let digit1Letter = _this.add.image(0, 0, 'digits', 1);
            let digit2Letter = _this.add.image(0, 0, 'digits', letterPrices.get(numLetter) - 10);

            digit1Letter.displayWidth = fishka.displayWidth / correction / 2;
            digit1Letter.scaleY = digit1Letter.scaleX;
            digit1Letter.setOrigin(.5, .5);
            digit1Letter.x = fishka.displayWidth - 18 * 2 - digit1Letter.displayWidth;
            digit1Letter.y = fishka.displayHeight - 27 * 2 - digit1Letter.displayHeight;

            digit2Letter.displayWidth = fishka.displayWidth / correction / 2;
            digit2Letter.scaleY = digit1Letter.scaleX;
            digit2Letter.setOrigin(.5, .5);
            digit2Letter.x = fishka.displayWidth - 13 * 2 - digit1Letter.displayWidth;
            digit2Letter.y = fishka.displayHeight - 27 * 2 - digit1Letter.displayHeight;

            var container = _this.add.container(X, Y, [fishka, testLetter, digit1Letter, digit2Letter]);
        }
    }

    container.setSize(fishka.displayWidth, fishka.displayHeight);
    container.setData('letter', numLetter);
    container.setData('cellX', false);
    container.setData('cellY', false);
    container.setInteractive();
    if (draggable) {
        _this.input.setDraggable(container);
        if (fishkaScale > 1) {
            container.setScale(fishkaScale);
        }
    }

    return container;
}

async function loadFishkiSet(fishkaSet) {
    fishkiLoaded[fishkaSet] = [];

    CODES[lang].forEach(function (numLetter) {
        imgName = fishkaSet + numLetter;
        preloaderObject.load.svg(imgName, '/img/fishki_sets/' + fishkaSet + '/' + numLetter + '.svg');

        if (numLetter != 999) {
            let numfishka = numLetter + 999 + 1;
            imgName = fishkaSet + numfishka;
            preloaderObject.load.svg(imgName, '/img/fishki_sets/' + fishkaSet + '/' + numfishka + '.svg');
        }
    });

    preloaderObject.load.start();
    preloaderObject.load.on('complete', function () {
        CODES[lang].forEach(function (numLetter) {
            imgName = fishkaSet + numLetter;
            fishkiLoaded[fishkaSet][numLetter] = imgName;
            if (numLetter != 999) {
                let numfishka = numLetter + 999 + 1;
                imgName = fishkaSet + numfishka;
                fishkiLoaded[fishkaSet][numfishka] = imgName;
            }
        });
    });
}
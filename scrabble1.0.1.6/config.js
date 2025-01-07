//
var config = {
    type: Phaser.AUTO,
    width: gameWidth,
    height: gameHeight,
    transparent: true,
    //backgroundColor: 0xdddddd,
    parent: 'phaser-example',
    scene: UIScene,
    scale: {
        mode: Phaser.Scale.FIT,
        parent: 'phaser-example',
        autoCenter: Phaser.Scale.CENTER_BOTH,
        width: gameWidth,
        height: gameHeight
    },
    loader:{
        //enableParallel: true,
        //maxParallelDownloads: isYandexAppGlobal() ? 4 : 32,
        maxRetries: 10, // from 3.85 version - using in Yandex version
        // imageLoadType: 'XHR',    // 'HTMLImageElement'
    },
};
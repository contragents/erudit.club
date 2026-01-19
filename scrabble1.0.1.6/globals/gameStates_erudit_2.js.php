//
var gameStates = {
    register: {
        1: 'waiting',
        refresh: 1,
        action: function (data) {
            useLocalStorage = true;
            if (localStorage != 'undefined') {
                if (!('<?= CookieErudit::COOKIE_NAME ?>' in localStorage)) {
                    localStorage['<?= CookieErudit::COOKIE_NAME ?>'] = data['cookie'];
                    if (localStorage['<?= CookieErudit::COOKIE_NAME ?>'] === data['cookie']) {
                        cookieStored = localStorage['<?= CookieErudit::COOKIE_NAME ?>'];
                    } else {
                        setLocalStorageValue('<?= CookieErudit::COOKIE_NAME ?>', data['cookie']);
                    }
                }
            } else {
                setLocalStorageValue('<?= CookieErudit::COOKIE_NAME ?>', data['cookie']);
            }

            queryNumber = 1;
            gameState = 'noGame';
        }
    },
    cookieTest: {
        1: 'waiting',
        refresh: 10000000,
        action: function (data) {
            fetchGlobal(COOKIE_CHECKER_SCRIPT, '', '12=12')
                .then((data) => {
                    if ('gameState' in data) {
                        if (data.gameState === 'useLocalStorage') {
                            gameStates.register.action(data);
                        } else {
                            //queryNumber = 1;
                            commonCallback(data);
                        }
                    } else {
                        var responseText = 'Ошибка';
                        alert(responseText);
                        queryNumber = 1;
                    }
                })
            ;
        },
    },
    desync: {
        1: 'waiting', 2: 'done',
        refresh: 5,
        noDialog: true,
        action: function (data) {
            gameState = gameOldState;
            gameSubState = gameOldSubState;
            enableButtons();
            if ('queryNumber' in data) {
                queryNumber = data['queryNumber'];
            }
        },
        //message: 'Синхронизация с сервером...'
    },
    noGame: {
        1: 'waiting', 2: 'done',
        noDialog: true,
        refresh: 10,
    },
    startGame: {
        1: 'waiting', 2: 'done',
        message: 'Игра начата!',
        refresh: 10,
        action: function (data) {
            buttons['submitButton']['svgObject'].disableInteractive();
            buttons['submitButton']['svgObject'].bringToTop(buttons['submitButton']['svgObject'].getByName('submitButton' + 'Inactive'));

            gameStates['myTurn']['from_noGame'](data);
            gameStates.gameResults.action(data);
        },
        from_initGame: function () {
            while (fixedContainer.length)
                fixedContainer.pop().destroy();
            cells = [];
            newCells = [];
            initCellsGlobal();
        },
        from_initRatingGame: function () {
            gameStates['startGame']['from_initGame']();
        },
        from_initCoinGame: function () {
            gameStates['startGame']['from_initGame']();
        }
    },
    chooseGame: {
        1: 'choosing',
        2: 'done',
        refresh: 1000000,
        message: '',
        noDialog: true,
        action: function (data) {
            gameLog = [];
            tWaiting = 0;
            isUserBlockActive = false;
            isOpponentBlockActive = false;
            winScore = false;
            gameBid = false;

            playerScores = {
                youBlock: {mode: OTJAT_MODE, digit3: 0, digit2: 0, digit1: 0},
                player1Block: {mode: OTJAT_MODE, digit3: 0, digit2: 0, digit1: 0},
                player2Block: {mode: OTJAT_MODE, digit3: 0, digit2: 0, digit1: 0},
                player3Block: {mode: OTJAT_MODE, digit3: 0, digit2: 0, digit1: 0},
                player4Block: {mode: OTJAT_MODE, digit3: 0, digit2: 0, digit1: 0},
            };
            initScoresGlobal();

            let under1800 = '<?= T::S('Only for players rated 1800+') ?>';
            let noRatingPlayers = '<?= T::S('Not enough 1900+ rated players online') ?>';
            let haveRatingPlayers = '<?= T::S('Select the minimum opponent rating') ?>';
            let title = '';
            let onlinePlayers = '';
            let chooseDisabled = '';
            if ('players' in data
            ) {
                if (
                    'thisUserRating' in data['players'] &&
                    data['players']['thisUserRating'] < 1800
                ) {
                    chooseDisabled = 'disabled';
                    title = under1800;
                } else {
                    title = haveRatingPlayers;
                }

                if (!(1900 in data['players']) || data['players'][1900] == 0) {
                    title = noRatingPlayers;
                }

                let checked_0 = 'checked';

                if (
                    'prefs' in data &&
                    data['prefs'] !== false &&
                    'from_rating' in data['prefs'] &&
                    data['prefs']['from_rating'] > 0
                ) {
                    checked_0 = '';
                }

                /* ----------------------------------- NEW ---------------------------------- */
                const ratingRadio = (props) => {
                    const {
                        title = '',
                        text = '',
                        inputValue = 0,
                        inputId = '0',
                        isChecked = false,
                        isDisabled = false,
                        name = 'from_rating',
                        extraClass = '',
                        extraInputAttrString = '',
                    } = props;

                    const html = `
									<div title="${title}"
										class="form-check form-check-inline ${extraClass}">
										<input class="form-check-input" type="radio" id="${inputId}" name="${name}"
										value="${inputValue}"
										${isChecked ? `checked` : ''}
										${isDisabled ? `disabled` : ''}
										${extraInputAttrString ? extraInputAttrString : ''}
											/>
										<label onClick="clickGlobal(this.id); return true;" id="div_${inputId}" class="form-check-label" for="${inputId}">${text}</label>
									</div>`;

                    return html;
                };

                const getBidList = (bidValues = [], data = {}) => {
                    let resultHtml = '';
                    bidValues.forEach((bidValue) => {
                        if (
                            'coin_players' in data &&
                            bidValue in data['coin_players'] &&
                            data['coin_players'][bidValue] >= 0
                        ) {
                            let isChecked = false;
                            if (
                                'prefs' in data &&
                                data['prefs'] !== false &&
                                'bid' in data['prefs'] &&
                                data['prefs']['bid'] == bidValue
                            ) {
                                isChecked = true;
                            }
                            resultHtml += ratingRadio({
                                title: data['coin_players'][bidValue] + ' <?= T::S('in game') ?>',
                                text: `<?= T::S('{{sudoku_icon_15}}') ?> ${bidValue}`,
                                inputValue: bidValue,
                                inputId: `bid_${bidValue}`,
                                isChecked,
                                isDisabled: '',
                                name: 'bid'
                            });
                        }
                    });

                    return resultHtml;
                };

                // ratingValues: number[] ([2000, 2100, 2200, ...])
                const getRatingList = (ratingValues = [], data = {}) => {
                    let resultHtml = '';
                    ratingValues.forEach((ratingValue) => {
                        if (
                            'players' in data &&
                            ratingValue in data['players'] &&
                            data['players'][ratingValue] > 0
                        ) {
                            let isChecked = false;
                            if (
                                'prefs' in data &&
                                data['prefs'] !== false &&
                                'from_rating' in data['prefs'] &&
                                data['prefs']['from_rating'] == ratingValue
                            ) {
                                isChecked = true;
                            }
                            resultHtml += ratingRadio({
                                title: data['players'][ratingValue] + ' <?= T::S('in game') ?>',
                                text: `<?= T::S('Above') ?> ${ratingValue} (${data['players'][ratingValue]})`,
                                inputValue: ratingValue,
                                inputId: `from_${ratingValue}`,
                                isChecked,
                                isDisabled: chooseDisabled.toString(),
                            });
                        }
                    });

                    return resultHtml;
                };

                onlinePlayers = `<div class="box-title-wrap">
												<span><?= T::S("Opponent's rating") ?></span>
											</div>`;

                onlinePlayers += `<div class="label-row">
												<div class="form-check">`;
                onlinePlayers += ratingRadio({
                    title: title,
                    text: '<?= T::S('Any') ?> (' + (0 in data['players'] ? data['players'][0] : '0')
                        + '&nbsp;<?= T::S('online')?>)',
                    inputValue: 0,
                    inputId: 'from_0',
                    isChecked: checked_0,
                    isDisabled: chooseDisabled.toString(),
                });

                const ratings = Object.keys(data.players).filter(
                    (item) => !isNaN(Number(item)) && data.players[item] > 0
                );

                ratings.shift();
                onlinePlayers += getRatingList(
                    ratings.slice(0, ratings.length / 2),
                    data
                );

                onlinePlayers += `</div>`; // end col
                onlinePlayers += `	<div class="form-check">`;

                if (ratings.slice(ratings.length / 2).length > 0) {
                    onlinePlayers += getRatingList(
                        ratings.slice(ratings.length / 2),
                        data
                    );
                }

                onlinePlayers += `	</div>`; // end col
                onlinePlayers += `</div>`; // end label-row

                onlinePlayers += `<div class="box-title-wrap">
												<span><?= T::S('Choose your MAX bet') ?></span>
											</div>`;

                const bids = Object.keys(data.coin_players).filter(
                    (item) => !isNaN(Number(item)) && data.coin_players[item] >= 0
                );

                onlinePlayers += `<div class="label-row">`;

                onlinePlayers += `<div class="form-check">`;

                onlinePlayers += ratingRadio({
                    title: '<?= T::S('Choose your MAX bet') ?>',
                    text: '<?= T::S('No coins') ?>',
                    inputValue: 0,
                    inputId: 'bid_0',
                    isChecked: false,
                    isDisabled: 'coin_players' in data && 'thisUserBalance' in data.coin_players && data.coin_players.thisUserBalance > 0,
                    name: 'bid'
                });

                onlinePlayers += getBidList(
                    bids.slice(0, bids.length / 2),
                    data
                );
                onlinePlayers += `	</div>`; // end col

                onlinePlayers += `	<div class="form-check">`;
                if (bids.slice(bids.length / 2).length > 0) {
                    onlinePlayers += getBidList(
                        bids.slice(bids.length / 2),
                        data
                    );
                }
                onlinePlayers += `	</div>`; // end col

                onlinePlayers += `</div>`; // end label-row

                onlinePlayers = `<div class="box box-rating">${onlinePlayers}</div>`;

                /* --------------------------------- END NEW -------------------------------- */
            } // end if 'players'

            let radioButtons =
                `<div style="display:none;" class="form-check form-check-inline"><input class="form-check-input" type="radio" id="twoonly" name="players_count" value="2" checked> <label class="form-check-label" for="twoonly">
                <?= T::S('Just two players') ?>
                </label></div>`;
            radioButtons +=
                `<div style="display:none;" class="form-check form-check-inline"><input class="form-check-input" type="radio" id="twomore" name="players_count" value="4"> <label class="form-check-label" for="twomore">
                <?= T::S('Up to four players') ?>
                </label></div>`;

            let wish = '';

            let checked_200 = 'checked';
            let checked_300 = '';

            if (
                'prefs' in data &&
                data['prefs'] !== false &&
                'ochki_num' in data['prefs']
            ) {
                checked_200 = data['prefs']['ochki_num'] == 200 ? 'checked' : '';
                checked_300 = data['prefs']['ochki_num'] == 300 ? 'checked' : '';
            }

            /* ----------------------------------- NEW ---------------------------------- */
            let radioOchki = `
                            <div class="box">
                                <div class="box-title-wrap">
                                    <span><?= T::S('Game goal') ?> ${isYandexAppGlobal() ? '<?= T::S(
                'game_goal_descr'
            ) ?>' : ''}</span>
                                </div>
                                <div class="label-row">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" id="dvesti" name="ochki_num"
                                            value="200" ${checked_200} />
                                        <label class="form-check-label text-accent" for="dvesti">
                                            <div class="d-inline-flex align-items-center align-middle">
                                                <i class="icon icon-arrow"></i>200
                                            </div>
                                        </label>
                                    </div>

                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" id="trista" name="ochki_num"
                                            value="300" ${checked_300}/>
                                        <label class="form-check-label text-accent" for="trista">
                                            <div class="d-inline-flex align-items-center align-middle">
                                                <i class="icon icon-arrow"></i>300
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>
			`;

            /* --------------------------------- END NEW -------------------------------- */

            // let wishTime = '<br /><br /><strong>Время на ход:</strong><br />';
            let wish_120 = 'checked';
            let wish_60 = '';

            if (
                'prefs' in data &&
                data['prefs'] !== false &&
                'turn_time' in data['prefs']
            ) {
                wish_120 = data['prefs']['turn_time'] == 120 ? 'checked' : '';
                wish_60 = data['prefs']['turn_time'] == 60 ? 'checked' : '';
            }

            /* ----------------------------------- NEW ---------------------------------- */
            let wishTime = `
				            <div class="box pb-1">
                                <div class="box-title-wrap mb-0">
                                    <span><?= T::S('Turn time') ?> ${isYandexAppGlobal() ? '<?= T::S(
                'turn_time_descr'
            ) ?>' : ''}</span>
                                </div>

                                <div class="label-row">
									<div class="form-check form-check-inline">

                                        <input class="form-check-input" type="radio" id="dve" name="turn_time"
                                            value="120" ${wish_120} />
                                        <label class="form-check-label text-accent" for="dve">2 <?= T::S('minutes') ?></label>
                                    </div>

									<div class="time-img-wrap">
                                        <img src="./images/time.png" class="d-block img-fluid" alt="">
                                    </div>

                                    <div class="form-check form-check-inline">

                                        <input class="form-check-input" type="radio" id="odna" name="turn_time"
                                            value="60" ${wish_60} />
                                        <label class="form-check-label text-accent" for="odna">1 <?= T::S('minute') ?></label>
                                    </div>
                                </div>
                            </div>
			`;

            let formHead = `<div class="box">
                            <div class="d-flex flex-row align-items-center">

                                <span><?= T::S('CHOOSE GAME OPTIONS') ?></span>

                                <div class="ml-auto"><a href="#" id="btn-faq" class="btn">${(isYandexAppGlobal() && lang === 'RU') ? 'ЧАВО' : 'FAQ'}</a>
                                </div>
                            </div>
                        </div>
			`;

            window.modalData = {instruction};

            /* --------------------------------- END NEW -------------------------------- */

            // let formHead = '<h5>Параметры игры (будут учтены при подборе)</h5>';

            let gameform =
                formHead +
                '<form onsubmit="return false" id="myGameForm">' +
                radioButtons +
                wish +
                radioOchki +
                wishTime +
                onlinePlayers +
                '</form>';

            bootbox.hideAll();

            reportGameStopYandex();

            dialog = bootbox.dialog({
                title: gameStates['chooseGame']['message'],
                message: gameform,
                className: 'modal-settings',
                size: 'medium',
                onEscape: false,
                closeButton: false,
                buttons: {
                    cabinet: {
                        label: '<?= T::S('Profile') ?>',
                        className: 'btn-outline-success',
                        callback: function () {
                            setTimeout(function () {
                                fetchGlobal(CABINET_SCRIPT, '', 12).then((dataCabinet) => {
                                    if (dataCabinet == '') var responseText = '<?= T::S('Error') ?>';
                                    else var responseArr = JSON.parse(dataCabinet['message']);

                                    /* ------------------------------ PROFILE DATA ------------------------------ */
                                    const profileData = {
                                        name: responseArr.name ? responseArr.name : 'Nickname',
                                        common_id: responseArr.common_id, // id игрока
                                        imageUrl: responseArr.url, // url картинки
                                        imageTitle: responseArr.img_title, // альт картинки
                                        rating: responseArr.info.rating ? responseArr.info.rating : 0, // рейтинг
                                        placement: responseArr.info.top, // место в рейтинге
                                        balance: responseArr.info.SUDOKU_BALANCE, // баланс
                                        ratingByCoins: responseArr.info.SUDOKU_TOP, // рейтинг по монетам
                                        tgWallet: '', // telegram wallet
                                        secret: responseArr.secret,
                                        bonusAccrual: responseArr.info.rewards, // начисление бонусов
                                        balanceSudoku: responseArr.info.SUDOKU_BALANCE, // баланс SUDOKU
                                        transactionList: responseArr.transactions,
                                        referrals: responseArr.refs ? responseArr.refs : [],
                                    };

                                    profileData.cookie = responseArr.form.filter(
                                        (item) => item.inputName === 'cookie',
                                    );
                                    profileData.MAX_FILE_SIZE = responseArr.form.filter(
                                        (item) => item.inputName === 'MAX_FILE_SIZE',
                                    );

                                    // делаем верстку из массива referrals
                                    let referralList = '';
                                    if ('referrals' in profileData && profileData.referrals.length > 0) {
                                        referralList = profileData.referrals
                                            .map(
                                                (ref) => `
								<li class="box">
									<span class="name d-block">${ref[0]}</span>
									<div class="pill-wrap"><span class="pill-nopill">${ref[1]}</span></div>
								</li>
						`,
                                            )
                                            .join('');

                                        referralList = `
								<ul class="referral-list">
									${referralList}
								</ul>
						`;
                                    }

                                    function getProfileModal(profileData) {
                                        return fetch(BASE_URL + (!isYandexAppGlobal() ? 'profile-modal-tpl_1.html' : 'profile-modal-tpl_yandex.html') + '?ver=' + Date.now())
                                            .then((response) => response.text())
                                            .then((template) => {
                                                let message = template
                                                    .replaceAll('{{Profile}}', '<?= T::S('Profile') ?>')
                                                    .replaceAll('{{Wallet}}', '<?= T::S('Wallet') ?>')
                                                    .replaceAll('{{Referrals}}', '<?= T::S('Referrals') ?>')
                                                    .replaceAll('{{Player ID}}', '<?= T::S('Player ID') ?>')
                                                    .replaceAll("{{Player's ID}}", "<?= T::S("Player's ID") ?>")
                                                    .replaceAll('{{Save}}', '<?= T::S('Save') ?>')
                                                    .replaceAll('{{Input new nickname}}',
                                                        '<?= T::S('Input new nickname') ?>')
                                                    .replaceAll('{{Your rank}}', '<?= T::S('Your rank') ?>')
                                                    .replaceAll('{{Ranking number}}',
                                                        '<?= T::S('Ranking number') ?>')
                                                    .replaceAll('{{Balance}}', '<?= T::S('Balance') ?>')
                                                    .replaceAll('{{Rating by coins}}',
                                                        '<?= T::S('Rating by coins') ?>')
                                                    .replaceAll('{{Secret key}}',
                                                        '<?= T::S('Secret key') ?>')
                                                    .replaceAll('{{secret}}', profileData.secret)
                                                    .replaceAll('{{secret_prompt}}',
                                                        '<?= T::S('secret_prompt') ?>')
                                                    .replaceAll('{{Link}}', '<?= T::S('Link') ?>') // Привязать
                                                    .replaceAll('{{Bonuses accrued}}',
                                                        '<?= T::S('Bonuses accrued') ?>')
                                                    .replaceAll('{{SUDOKU Balance}}',
                                                        '<?= T::S('SUDOKU Balance') ?>') // Баланс SUDOKU
                                                    .replaceAll('{{COIN Balance}}',
                                                        '<?= T::S('COIN Balance') ?>')
                                                    .replaceAll('{{Claim}}', '<?= T::S('Claim') ?>') // Забрать
                                                    .replaceAll('{{Name}}', '<?= T::S('Name') ?>')

                                                    .replaceAll('{{MAX_FILE_SIZE}}', profileData.MAX_FILE_SIZE[0].value)
                                                    .replaceAll('{{cookie}}', profileData.cookie[0].value)

                                                    .replaceAll('{{common_id}}', profileData.common_id)
                                                    .replaceAll('{{name}}', profileData.name)
                                                    .replaceAll('{{imageUrl}}', profileData.imageUrl)
                                                    .replaceAll('{{imageTitle}}', profileData.imageTitle)
                                                    .replaceAll('{{rating}}', profileData.rating)
                                                    .replaceAll('{{placement}}', profileData.placement)
                                                    .replaceAll('{{balance}}', profileData.balance)
                                                    .replaceAll('{{ratingByCoins}}', profileData.ratingByCoins)
                                                    .replaceAll('{{tgWallet}}', profileData.tgWallet)
                                                    .replaceAll('{{bonusAccrual}}', profileData.bonusAccrual)
                                                    .replaceAll('{{balanceSudoku}}', profileData.balanceSudoku)
                                                    .replaceAll('{{referralList}}', referralList)

                                                    .replaceAll('{{Buy_SUDOKU}}', '<?= T::S('Buy_SUDOKU') ?>')
                                                    .replaceAll('{{SUDOKU_amount}}', '<?= T::S('SUDOKU_amount') ?>')
                                                    .replaceAll('{{enter_amount}}', '<?= T::S('enter_amount') ?>')
                                                    .replaceAll('{{The_price}}', '<?= T::S('The_price') ?>')
                                                    .replaceAll('{{calc_price}}', '<?= T::S('calc_price') ?>')
                                                    .replaceAll('{{Check_price}}', '<?= T::S('Check_price') ?>')
                                                    .replaceAll('{{Replenish}}', '<?= T::S('Replenish') ?>')
                                                    .replaceAll('{{Support in Telegram}}', '<?= T::S(
                                                        'Support in Telegram'
                                                    ) ?>')
                                                    .replaceAll('{{Last transactions}}', '<?= T::S(
                                                        'Last transactions'
                                                    ) ?>')
                                                    .replaceAll('{{transaction_list}}', profileData.transactionList)
                                                ;

                                                return message;
                                            })
                                            .catch((error) =>
                                                console.error('Ошибка загрузки profile-modal:', error),
                                            );
                                    }

                                    /* ---------------------------- END PROFILE DATA ---------------------------- */

                                    getProfileModal(profileData).then((html) => {
                                        dialog = bootbox.alert({
                                            title: '',
                                            message: html,
                                            locale: 'ru',
                                            className: 'modal-settings modal-profile',
                                            buttons: {
                                                ok: {
                                                    label: '<?= T::S('Back') ?>',
                                                    className: 'btn-sm ml-auto mr-0',
                                                },
                                            },
                                            onShown: function (e) {
                                                profileModal.onProfileModalLoaded();
                                            },
                                            callback: function () {
                                                gameStates['chooseGame']['action'](data);
                                            },
                                        });
                                    });

                                    return false;
                                });
                            }, 100);
                        },
                    }
                    ,
                    instruction: {
                        label: (isYandexAppGlobal() && lang === 'RU') ? 'ЧАВО' : 'FAQ',
                        className: 'btn-outline-success d-none',
                        callback: function () {
                            dialog = bootbox
                                .alert({
                                    message: instruction,
                                    locale: 'ru',
                                })
                                .off('shown.bs.modal');

                            return false;
                        },
                    },
                    beginGame: {
                        label: '<?= T::S('Start') ?>',
                        className: 'btn-red' /*'btn-primary'*/,
                        callback: function () {
                            reportGameStartYandex();

                            activateFullScreenForMobiles();

                            gameState = 'noGame';
                            fetchGlobal(
                                INIT_GAME_SCRIPT,
                                '',
                                $('.bootbox-body #myGameForm').serialize()
                            ).then((data) => {
                                if (data == '') var responseText = '<?= T::S('Error') ?>';
                                else {
                                    commonCallback(data);
                                }
                            });

                            return true;
                        },
                    },
                    stats: {
                        label: '<?= T::S('Stats') ?>',
                        className: 'btn-outline-success',
                        callback: function () {
                            activateFullScreenForMobiles();
                            getStatPageGlobal().then(data => {
                                dialog = bootbox
                                    .dialog({
                                        message: data.message,
                                        locale: lang === 'RU' ? 'ru' : 'en',
                                        className: 'modal-settings  modal-stats',
                                        callback: function () {
                                            console.log('stats loaded');
                                        },
                                        buttons: {
                                            removeFilter: {
                                                label: '<?= T::S('Remove filter') ?>',
                                                className: 'js-remove-filter btn btn-sm btn-auto mr-0 d-none',
                                                callback: function (e) {
                                                    e.preventDefault();
                                                    return false;
                                                },
                                            },
                                            ok: {
                                                label: '<?= T::S('Back') ?>',
                                                className: 'btn-sm ml-auto mr-0',
                                                callback: function () {
                                                    //gameStates['chooseGame']['action'](data);
                                                    fetchGlobal(STATUS_CHECKER_SCRIPT)
                                                        .then((data) => {
                                                            commonCallback(data);
                                                            gameStates['chooseGame']['action'](data)
                                                        });
                                                }
                                            },
                                        }
                                    })
                                    .off('shown.bs.modal')
                                    .on('shown.bs.modal', function () {
                                        if (data.onLoad && typeof data.onLoad === 'function') {
                                            data.onLoad();
                                        }
                                    })
                                    .find('.modal-content')
                                    .css({
                                        'background-color': 'rgba(230, 255, 230, 1)',
                                    });

                                return false;
                            }).catch(error => {
                                console.error(error);
                            });

                        },
                    },
                    ...(!isTgBot() && !isYandexAppGlobal() && {
                        telegram: {
                            label: '<?= T::S('Play on') ?>',
                            className: 'btn-tg',
                            callback: function () {
                                document.location = GAME_BOT_URL + '/?start='
                                    + ((commonId && commonIdHash) ? (commonId + '_' + commonIdHash) : '');

                                return false;
                            },
                        },
                    }),
                    ...(false && isTgBot() && !isYandexAppGlobal() && {
                        invite: {
                            label: '<?= T::S('Invite a friend') ?>',
                            className: 'btn-danger',
                            callback: function () {
                                shareTgGlobal();

                                return false;
                            },
                        },
                    }),
                    ...(!isYandexAppGlobal() /*isTgBot() && !isYandexAppGlobal()*/ && {
                        sudoku: {
                            label: '<?= T::S('Sudoku with friends') ?>',
                            className: 'btn-danger',
                            callback: function () {
                                sudokuGlobal();

                                return false;
                            },
                        },
                    }),
                    ...(isYandexAppGlobal() && {
                        oferta: {
                            label: '<?= T::S('Agreement') ?>',
                            className: 'btn-outline-success',
                            callback: function () {
                                async function getOfertaModal() {
                                    return fetch(BASE_URL + 'oferta' + (lang === 'EN' ? '_EN' : '') + '.html' + version(true))
                                        .then((response) => response.text());
                                };

                                getOfertaModal().then((html) => {
                                    dialog = bootbox.alert({
                                        title: '',
                                        message: html,
                                        locale: 'ru',
                                        className: 'modal-settings modal-profile',
                                        buttons: {
                                            ok: {
                                                label: '<?= T::S('Back') ?>',
                                                className: 'btn-sm ml-auto mr-0',
                                            },
                                        },
                                        callback: function () {
                                            gameStates.chooseGame.action(data);
                                        },
                                    })
                                });
                            },
                        }
                    }),
                },
            });
        },
    },
    initGame: {
        1: 'waiting', 2: 'done',
        action: function (data) {
            buttons['submitButton']['svgObject'].disableInteractive();
            buttons['submitButton']['svgObject'].bringToTop(buttons['submitButton']['svgObject'].getByName('submitButton' + 'Inactive'));
        },
        message: '<?= T::S('Game selection - please wait') ?>',
        refresh: 10
    },
    initRatingGame: {
        1: 'waiting', 2: 'done',
        action: function (data) {
            buttons['submitButton']['svgObject'].disableInteractive();
            buttons['submitButton']['svgObject'].bringToTop(buttons['submitButton']['svgObject'].getByName('submitButton' + 'Inactive'));
        },
        message: '<?= T::S('Game selection - please wait') ?>',
        refresh: 10
    },
    initCoinGame: {
        1: 'waiting', 2: 'done',
        action: function (data) {
            buttons['submitButton']['svgObject'].disableInteractive();
            buttons['submitButton']['svgObject'].bringToTop(buttons['submitButton']['svgObject'].getByName('submitButton' + 'Inactive'));
        },
        message: '<?= T::S('Game selection - please wait') ?>',
        refresh: 10
    },
    myTurn: {
        1: 'thinking', 2: 'checking', 3: 'submiting', 4: 'done',
        message: '<?= T::S('Your turn!') ?>',
        refresh: 15,
        action: function (data) {
            gameStates.gameResults.action(data);
            buttons.submitButton.svgObject.setInteractive();
            buttons.submitButton.svgObject.bringToTop(buttons.submitButton.svgObject.getByName('submitButton' + OTJAT_MODE));
        },
        from_initRatingGame: function (data) {
            gameStates.startGame.from_initGame();
            gameStates.myTurn.from_noGame(data);
        },
        from_initCoinGame: function (data) {
            gameStates.startGame.from_initGame();
            gameStates.myTurn.from_noGame(data);
        },
        from_initGame: function (data) {
            gameStates.startGame.from_initGame();
            gameStates.myTurn.from_noGame(data);
        },
        from_noGame: function (data) {
            if ('fishki' in data)
                placeFishki(data.fishki);
        },
        from_desync: function (data) {
            if ('fishki' in data)
                placeFishki(data.fishki);
        },
        from_gameResults: function () {
            gameStates.startGame.from_initGame();
        },
        from_preMyTurn: function () {
            resetButtonFunction(true);
            gameStates.startGame.from_initGame();
        },
        from_startGame: function () {
            resetButtonFunction(true);
            gameStates.startGame.from_initGame();
        }
    },
    preMyTurn: {
        1: 'waiting', 2: 'done',
        message: '<?= T::S('Get ready - your turn is next!') ?>',
        refresh: 5,
        action: function (data) {
            gameStates.gameResults.action(data);

            buttons.submitButton.svgObject.disableInteractive();
            buttons.submitButton.svgObject.bringToTop(buttons.submitButton.svgObject.getByName('submitButton' + 'Inactive'));
        },
        from_desync: function (data) {
            if ('fishki' in data)
                placeFishki(data.fishki);
        },
        from_initRatingGame: function (data) {
            gameStates.startGame.from_initGame();
            gameStates.myTurn.from_noGame(data);
        },
        from_initCoinGame: function (data) {
            gameStates.startGame.from_initGame();
            gameStates.myTurn.from_noGame(data);
        },
        from_initGame: function (data) {
            gameStates.startGame.from_initGame();
            gameStates.myTurn.from_noGame(data);
        },
        from_noGame: function (data) {
            gameStates.myTurn.from_noGame(data)
        },
        from_myTurn: function (data) {
            gameStates.myTurn.from_noGame(data)
        },
        from_otherTurn: function (data) {
            gameStates.myTurn.from_noGame(data)
        },
        from_gameResults: function () {
            gameStates.startGame.from_initGame()
        },
    },
    otherTurn: {
        1: 'waiting', 2: 'done', message: '<?= T::S('Take a break - your move in one') ?>',
        refresh: 5,
        action: function (data) {
            gameStates.gameResults.action(data);

            gameStates.myTurn.from_noGame(data);
            buttons.submitButton.svgObject.disableInteractive();
            buttons.submitButton.svgObject.bringToTop(buttons.submitButton.svgObject.getByName('submitButton' + 'Inactive'));
        },
        from_desync: function (data) {
            if ('fishki' in data)
                placeFishki(data.fishki);
        },
        from_initRatingGame: function (data) {
            gameStates.startGame.from_initGame();
        },
        from_initCoinGame: function (data) {
            gameStates.startGame.from_initGame();
        },
        from_initGame: function (data) {
            gameStates.startGame.from_initGame();
        },
        from_gameResults: function () {
            gameStates.startGame.from_initGame();
        }
    },
    gameResults: {
        1: 'waiting', 2: 'done',
        messageFunction: function (mes) {
            return mes;
        },
        refresh: 10,
        action: function (data) {
            reportGameStartYandex();

            if ("desk" in data && data.desk.length > 0) {
                parseDeskGlobal(data['desk']);
            }
            if ("score" in data) {
                userScores(data);
            }
            if ('activeUser' in data) {
                activeUser = data.activeUser;
            }
        },
        results: function (data) {
            if (dialog && canCloseDialog)
                dialog.modal('hide');
            var okButtonCaption = '<?= T::S('Refuse') ?>';
            if ('inviteStatus' in data && data['inviteStatus'] == 'waiting') {
                var okButtonCaption = 'OK';
            }

            bootbox.hideAll();

            dialog = bootbox.dialog({
                message: data['comments'],
                onEscape: false,
                closeButton: false,
                className: 'modal-settings modal-profile text-white',
                buttons: {
                    invite: {
                        label: '<?= T::S('Offer a game') ?>',
                        className: 'btn-primary',
                        callback: function () {
                            setTimeout(function () {
                                fetchGlobal(INVITE_SCRIPT, '', 12)
                                    .then((dataInvite) => {
                                        if (dataInvite == '')
                                            var responseText = '<?= T::S('Request rejected') ?>';
                                        else
                                            var responseText = dataInvite['message'];
                                        if ('inviteStatus' in dataInvite) {
                                            if (dataInvite['inviteStatus'] == 'newGameStarting')
                                                document.location.reload(true);
                                        }
                                        dialogResponse = bootbox.alert({
                                            message: responseText,
                                            locale: 'ru',
                                            size: 'small',
                                            className: 'modal-settings modal-profile text-white',
                                            callback: function () {
                                                dialogResponse.modal('hide');
                                                dataInvite['comments'] = data['comments'];
                                                gameStates.gameResults.results(dataInvite);
                                            }
                                        });

                                        setTimeout(
                                            function () {
                                                dialogResponse.find(".bootbox-close-button").trigger("click");
                                            }
                                            , 2000
                                        );

                                        return false;
                                    });
                            }, 100);
                        }
                    },
                    ok: {
                        label: okButtonCaption,
                        className: 'btn-info',
                        callback: function () {
                            return true;
                        }
                    },
                    new: {
                        label: '<?= T::S('New game') ?>',
                        className: 'btn-danger',
                        callback: function () {
                            newGameButtonFunction(true);
                        }
                    }
                }
            });
        },
        decision: function (data) {
            if (dialog && canCloseDialog) {
                dialog.modal('hide');
            }
            if (dialogResponse) {
                dialogResponse.modal('hide');
            }

            bootbox.hideAll();

            dialog = bootbox.dialog({
                message: data['comments'],
                onEscape: false,
                closeButton: false,
                className: 'modal-settings modal-profile text-white',
                buttons: {
                    invite: {
                        label: '<?= T::S('Accept invitation') ?>',
                        className: 'btn-primary',
                        callback: function () {
                            setTimeout(function () {
                                fetchGlobal(INVITE_SCRIPT, '', 12)
                                    .then((dataInvite) => {
                                        if (dataInvite == '') {
                                            var responseText = '<?= T::S('Request rejected') ?>';
                                        } else {
                                            var responseText = dataInvite['message'];
                                        }
                                        if ('inviteStatus' in dataInvite) {
                                            if (dataInvite['inviteStatus'] == 'newGameStarting')
                                                document.location.reload(true);
                                        }
                                        dialogResponse = bootbox.alert({
                                            message: responseText,
                                            locale: 'ru',
                                            size: 'small',
                                            className: 'modal-settings modal-profile text-white',
                                            callback: function () {
                                                dialogResponse.modal('hide');
                                                dataInvite['comments'] = data['comments'];
                                            }
                                        });

                                        setTimeout(
                                            function () {
                                                dialogResponse.find(".bootbox-close-button").trigger("click");
                                            }
                                            , 2000
                                        );

                                        return false;
                                    });
                            }, 100);
                        }
                    },
                    ok: {
                        label: '<?= T::S('Refuse') ?>',
                        className: 'btn-info',
                        callback: function () {
                            return true;
                        }
                    },
                    new: {
                        label: '<?= T::S('New game') ?>',
                        className: 'btn-danger',
                        callback: function () {
                            newGameButtonFunction(true);
                        }
                    }
                }
            });
        }
    },
    afterSubmit: {refresh: 1}
}

var gameState = 'noGame';
var gameSubState = 'waiting';
var queryNumber = 1;
var lastQueryTime = 0;
var gameOldState = '';

function commonCallback(data) {
    if (('gameState' in data) && !(data['gameState'] in gameStates)) {
        return;
    }

    if ('http_status' in data && (data['http_status'] === BAD_REQUEST || data['http_status'] === PAGE_NOT_FOUND)) {
        console.log(data['message']);
        return;
    }

    if ('query_number' in data && data['query_number'] != (queryNumber - 1)) {
        return;
    }

    if (noNetworkImgOpponent !== false && 'is_opponent_active' in data && !data.is_opponent_active) {
        noNetworkImgOpponent.visible = true;
    } else {
        noNetworkImgOpponent.visible = false;
    }

    gameOldState = gameState;
    gameOldSubState = gameSubState;

    if ('gameState' in data && gameState != data['gameState']) {
        gameState = data['gameState'];

        if ('gameNumber' in data) {
            gameNumber = data['gameNumber'];
        }
    }

    if (gameOldState != gameState) {
        soundPlayed = false;
    }

    if (gameState == 'myTurn') {
        if (pageActive == 'hidden') {
            if (!isYandexAppGlobal()) {
                snd.play();
            }
            soundPlayed = true;
        } else if (!soundPlayed) {
            if (!isYandexAppGlobal()) {
                snd.play();
            }
            soundPlayed = true;
        }
    }

    if ('lang' in data && data['lang'] != lang) {
        lang = data['lang'];
    }

    if ('common_id' in data && !commonId) {
        commonId = data.common_id;
    }

    if ('common_id_hash' in data && !commonIdHash) {
        commonIdHash = data.common_id_hash;
    }

    if (myUserNum === false) {
        if ('yourUserNum' in data) {
            myUserNum = data['yourUserNum'];
        }
    }

    if ('gameSubState' in data) {
        gameSubState = data['gameSubState'];
    }

    console.log(gameOldState + '->' + gameState);

    if ((gameOldState != gameState) || (gameOldSubState != gameSubState)) {
        if ('active_users' in data && data['active_users'] == 0) {
            clearTimeout(requestToServerEnabledTimeout);
            requestToServerEnabled = false;
        }

        tWaiting = false;
        gWLimit = false;

        if (dialog && canCloseDialog)
            dialog.modal('hide');
        if (intervalId) {
            clearInterval(intervalId);
            intervalId = 0;
        }

        if (canOpenDialog) {
            if ([INIT_RATING_GAME_STATE, INIT_GAME_STATE].indexOf(gameState) >= 0) {
                dialog = bootbox.dialog({
                    message: ('comments' in data) ? data['comments'] : gameStates[gameState]['message'],
                    size: 'small',
                    className: 'modal-settings modal-profile text-white',
                    onEscape: false,
                    closeButton: false,
                    buttons: {
                        // SUD-49
                        cancel: {
                            label: '<?= T::S('New game') ?>',
                            className: 'btn-danger',
                            callback: function () {
                                newGameButtonFunction(true);
                            }
                        }
                    },
                });
                if ('gameWaitLimit' in data) {
                    dialog.init(function () {
                        intervalId = setInterval(function () {
                            var igrokiWaiting = '';
                            if ('gameSubState' in data)
                                igrokiWaiting = "<br /><?= T::S('Players ready:') ?> " + data['gameSubState'];

                            if ('timeWaiting' in data) {
                                if (!tWaiting) {
                                    tWaiting = data.timeWaiting;
                                }
                                if (!gWLimit) {
                                    gWLimit = data.gameWaitLimit;
                                }
                            } else {
                                if (!gWLimit) {
                                    gWLimit = data.gameWaitLimit;
                                }
                                if (!tWaiting) {
                                    tWaiting = 0
                                }
                            }

                            let content = data['comments'] + igrokiWaiting
                                + '<br /><?= T::S('Time elapsed:') ?> '
                                + (tWaiting++)
                                + '<br /><?= T::S('Average waiting time:') ?> '
                                + (gWLimit)
                                + '<?= T::S('s') ?>';
                            dialog.find('.bootbox-body').html(content);
                        }, 1000);
                    });
                } else if ('ratingGameWaitLimit' in data)
                    dialog.init(function () {
                        intervalId = setInterval(function () {
                            if ('timeWaiting' in data) {
                                if (!tWaiting)
                                    tWaiting = data['timeWaiting'];
                                else {
                                    data['timeWaiting'] = 0;
                                    if (!tWaiting)
                                        tWaiting = data['timeWaiting'];
                                }
                            }
                            dialog.find('.bootbox-body').html(data['comments'] +
                                '<br /><?= T::S('Time elapsed:') ?> ' +
                                (tWaiting++) +
                                'с' +
                                '<br /><?= T::S('Time limit:') ?> ' +
                                data['ratingGameWaitLimit'] +
                                'c' +
                                '<hr><?= T::S('You can start a new game if you wait for a long time') ?>');
                        }, 1000);
                    });
            } else if (gameState === GAME_RESULTS_STATE) {
                if ('inviteStatus' in data) {
                    if (data.inviteStatus == 'newGameStarting') {
                        document.location.reload(true);
                    } else if (data.inviteStatus == 'waiting') {
                        gameStates.gameResults.results(data);
                    } else {
                        gameStates.gameResults.decision(data);
                    }
                } else {
                    gameStates.gameResults.results(data);
                }
            } else if (!('noDialog' in gameStates[gameState])) {
                setTimeout(function () {
                        closeDialogs();

                        var message = '';
                        var cancelLabel = '<?= T::S('Close in 5 seconds') ?>';

                        if ('comments' in data && (data['comments'] !== null)) {

                            if ('messageFunction' in gameStates[gameState]) {
                                message = gameStates[gameState]['messageFunction'](data['comments']);
                            } else {
                                message = data['comments'];
                            }
                        } else if ('message' in gameStates[gameState]) {
                            message = gameStates[gameState]['message'];
                        }

                        if (turnAutocloseDialog) {
                            if (timeToCloseDilog == 5) {
                                cancelLabel = '<?= T::S('Close immediately') ?>';
                            } else {
                                cancelLabel = '<?= T::S('Will close automatically') ?>';
                            }
                        }

                        dialogTurn = bootbox.confirm({
                            message: message,
                            size: 'medium',
                            className: 'modal-settings modal-profile text-white',
                            buttons: {
                                confirm: {
                                    label: 'OK',
                                    className: 'btn-primary'
                                },
                                cancel: {
                                    label: cancelLabel,
                                    className: 'btn btn-outline-secondary'
                                }
                            },
                            callback: function (result) {
                                if (!result) {
                                    turnAutocloseDialog = true;

                                    if (!timeToCloseDilog) {
                                        timeToCloseDilog = 5;
                                    } else if (!automaticDialogClosed) {
                                        timeToCloseDilog = 1.5;
                                    }

                                    automaticDialogClosed = false;
                                }
                                activateFullScreenForMobiles();
                            }
                        });
                        dialogTurn
                            .find('.modal-content').css({'background-color': 'rgba(255, 255, 255, 0.7)'})
                            .find('img').css('background-color', 'rgba(0, 0, 0, 0)');

                        if (turnAutocloseDialog) {
                            setTimeout(
                                function () {
                                    automaticDialogClosed = true;
                                    dialogTurn.find(".bootbox-close-button").trigger("click");
                                }
                                , timeToCloseDilog * 1000
                            );
                        }
                    }
                    , 500
                );
            }
        }

        enableButtons();

        if ('from_' + gameOldState in gameStates[gameState])
            gameStates[gameState]['from_' + gameOldState](data);

        if ('action' in gameStates[gameState])
            gameStates[gameState]['action'](data);
    }

    if ('timeLeft' in data) {
        vremiaMinutes = data['minutesLeft'];
        vremiaSeconds = data['secondsLeft'];

        displayTimeGlobal(+vremiaMinutes * 100 + +vremiaSeconds, true);
    }

    if ('bid' in data && gameBid === false) {
        gameBid = data.bid;
        gameBank = data.bank;
        gameBankString = data.bank_string;

        if (players.bankBlock.svgObject === false) {
            buttons.logButton.svgObject.x = buttons.chatButton.svgObject.x - (buttons.checkButton.svgObject.width - buttons.logButton.svgObject.width) / 2;
            buttons.playersButton.svgObject.x += (buttons.changeButton.svgObject.width - buttons.playersButton.svgObject.width) / 2
            buttons.chatButton.svgObject.x = buttons.logButton.svgObject.x + (buttons.playersButton.svgObject.x - buttons.logButton.svgObject.x) / 2;
        } else {
            while (players.bankBlock.svgObject.length) {
                players.bankBlock.svgObject.pop().setVisible(false).destroy();
            }
        }

        players.bankBlock.svgObject = [];

        let resourceName = 'bankBlock_' + gameBankString + '_' + Date.now();

        preloaderObject.load.reset();

        let ruModifier = (gameBank < 1000 && isYandexAppGlobal() && lang === 'RU')
            ? '_ru'
            : '';

        preloaderObject.load.svg(resourceName + OTJAT_MODE, `img/otjat/${players.bankBlock.filename}${gameBankString}${ruModifier}.svg`,
            {
                ...('width' in players.bankBlock && {
                    'width': players.bankBlock.width,
                }),
                'height':
                    'height' in players.bankBlock ? players.bankBlock.height : buttonHeight,
            }
        );

        preloaderObject.load.start();

        preloaderObject.load.on('complete', function () {
            playerBlockModes = [OTJAT_MODE];

            while (players.bankBlock.svgObject.length) {
                players.bankBlock.svgObject.pop().setVisible(false).destroy();
            }
            players.bankBlock.svgObject.push(getSVGBlockGlobal(players.bankBlock.x, players.bankBlock.y, resourceName, faserObject, players.bankBlock.scalable, false));

            playerBlockModes = [OTJAT_MODE, ALARM_MODE];
        });
    }

    if ('log' in data)
        for (k in data['log'])
            gameLog.unshift(data['log'][k]);

    if ('chat' in data) {
        for (k in data['chat']) {

            if (!
                (((data['chat'][k].indexOf('<?= T::S('You') ?>') + 1) === 1)
                    ||
                    ((data['chat'][k].indexOf('<?= T::S('News') ?>') + 1) === 1))
            ) {
                hasIncomingMessages = true;
                buttons['chatButton']['svgObject'].bringToTop(buttons['chatButton']['svgObject'].getByName('chatButton' + ALARM_MODE));
                buttons['chatButton']['svgObject'].getByName('chatButton' + ALARM_MODE).setData('alarm', true);
            }

            chatLog.unshift(data['chat'][k]);
        }
    }

    if ('winScore' in data) {
        // почемуто с if не работает... if (!winScore) {
        buttonSetModeGlobal(players, 'goalBlock', data.winScore == 200 ? OTJAT_MODE : ALARM_MODE);
        //}

        winScore = data.winScore;
    }

    responseData = data;

    if (pageActive == 'hidden' && gameState != 'chooseGame') {
        fetchGlobal(STATUS_CHECKER_SCRIPT)
            .then((data) => {
                commonCallback(data);
            });
    }
}

function userScores(data) {
    if ("score_arr" in data) {
        for (let k in data['score_arr']) {
            if (k == data.yourUserNum) {
                let youBlock = players.youBlock.svgObject;

                if (!isUserBlockActive) {
                    let changeBlock = players['player' + (+k + 1) + 'Block'].svgObject;
                    if (changeBlock.visible) {
                        changeBlock.setVisible(false);
                    }

                    youBlock.x = changeBlock.x;
                    youBlock.y = changeBlock.y;
                    youBlock.setVisible(true);
                    youBlock.setAlpha(1);
                    players.timerBlock.svgObject.setAlpha(1);

                    isUserBlockActive = true;

                    noNetworkImg.setScale(youBlock.height / 232 / 4);
                    noNetworkImg.x = youBlock.x + youBlock.width / 2 + noNetworkImg.displayWidth / 2;
                    noNetworkImg.y = youBlock.y;
                    noNetworkImg.setDepth(10000);
                    noNetworkImg.visible = false;
                }

                displayScoreGlobal(data['score_arr'][k], 'youBlock', true);
                buttonSetModeGlobal(players, 'youBlock', gameState === MY_TURN_STATE ? ALARM_MODE : OTJAT_MODE);
            } else {
                let playerBlockName = 'player' + (+k + 1) + 'Block';
                let opponentBlock = players[playerBlockName].svgObject;

                if (!opponentBlock.visible) {
                    opponentBlock.setVisible(true);
                }

                displayScoreGlobal(data['score_arr'][k], playerBlockName, false);
                buttonSetModeGlobal(players, playerBlockName, k == data['activeUser'] ? ALARM_MODE : OTJAT_MODE);

                if (opponentBlock.alpha < 1) {
                    opponentBlock.setAlpha(1);
                }

                if (('userNames' in data) && (k in data['userNames']) && (data['userNames'][k] === '')) {
                    opponentBlock.setAlpha(INACTIVE_USER_ALPHA);
                }

                if (!isOpponentBlockActive && (+k < 2)) {
                    isOpponentBlockActive = true;

                    noNetworkImgOpponent.setScale(opponentBlock.height / 232 / 4);
                    noNetworkImgOpponent.x = opponentBlock.x + opponentBlock.width / 2 + noNetworkImgOpponent.displayWidth / 2;
                    noNetworkImgOpponent.y = opponentBlock.y;
                    noNetworkImgOpponent.setDepth(10000);
                    noNetworkImgOpponent.visible = false;
                }
            }
        }

        if (ochki_arr === false) {
            ochki_arr = [];
            for (let k in data['score_arr']) {
                ochki_arr[k] = data['score_arr'][k];
            }
        }
    }
}

function clickGlobal(id) {
    return true;

    if (id.indexOf('bid') > 0) {
        bid = id.substring(8);
        if (+bid === 0) {
            return false;
        }

        const ratingInputs = document.querySelectorAll("input[name='from_rating']");
        ratingInputs.forEach(item => {
            if (item.id === 'from_0') {
                item.checked = true;
            } else {
                item.checked = false;
            }
        });
    }

    if (id.indexOf('from') > 0) {
        fromRating = id.substring(9);
        if (+fromRating === 0) {
            return false;
        }

        const bidInputs = document.querySelectorAll("input[name='bid']");

        bidInputs.forEach(item => {
            if (item.id === 'bid_0') {
                item.checked = true;
            } else {
                item.checked = false;
            }
        });
    }

    return true;
}

function RobokassaPaymentGlobal(actionType) {
    let input = $('#amount-to-buy');
    let amountToBuy = input.val();

    if (actionType === 'check') {
        isPayEnabled = false;
        let button = $('#replenish-button');
        let calcPriceElement = $('#calculated-price');

        if (!amountToBuy || amountToBuy < 0) {
            amountToBuy = 0;
            button.html('<?= T::S('Check_price') ?>');
            calcPriceElement.html('<?= T::S('calc_price') ?>');
        }

        if (amountToBuy > 1000) {
            amountToBuy = 1000;
        }

        amountToBuy = Math.ceil(amountToBuy / 10) * 10;
        input.val(amountToBuy);

        let price = amountToBuy * SUDOKU_PRICE; // 10 рублей за монету
        if (amountToBuy > 0) {
            calcPriceElement.html(price + ' &#8381;');
            button.html('<?= T::S('Pay') ?>' + '<br>' + price + ' &#8381;');

            setTimeout(() => isPayEnabled = true, 150);
        } else {
            button.html('<?= T::S('Check_price') ?>');
        }

        return;
    }

    if (actionType === 'pay' && amountToBuy > 0 && (amountToBuy % 10 === 0) && isPayEnabled) {
        let price = amountToBuy * 10;

        let orderParams = {
            common_id: commonId,
            summ: price,
        };
        orderParams['quickpay-form'] = 'button';

        fetch('/mvc/pay/pay', {
            method: "POST",
            body: getFormData(orderParams),
        }).then(response => response.json())
            .then(result => {
                console.log('Success:', result);
                if ('location' in result) {
                    location.href = result.location;
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }
}

function getFormData(object) {
    const formData = new FormData();
    Object.keys(object).forEach(key => formData.append(key, object[key]));
    return formData;
}

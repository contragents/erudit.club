//

// для копирования из input в буфер
function copyToClipboard(selector) {
    const element = document.querySelector(selector);
    element.select();
    element.setSelectionRange(0, 99999);
    document.execCommand('copy');
}

(function profileModal() {
    const selectors = {
        profileTabsId: 'profile-tabs',
        tabLink: '#profile-tabs a',
        tabContent: '.tab-content',
        tabContentWrap: '.tab-content-wrap',
        tabPane: '.tab-pane',
        copyBtn: '.js-btn-copy',
        setNicknameBtn: '.js-btn-set-nickname',
        setProfileImageBtn: '.js-btn-set-profile-image',
        nicknameInput: '#player_name',
        profileImageInput: '#player_avatar_file',
        userIdInput: '#user_id',
    };

    const setTabContentOffset = (tabsSelector) => {
        if (!document.getElementById(selectors.profileTabsId)) {
            return;
        }
        const targetId = document
            .querySelectorAll(`${selectors.tabLink}.active`)[0]
            .getAttribute('href');
        const tabContent = document.querySelector(targetId).closest(selectors.tabContent);
        const tabContentWrap = tabContent.closest(selectors.tabContentWrap);
        const tabPane = document.querySelector(targetId);
        tabContentWrap.style.height = tabContent.getBoundingClientRect().height + 'px';

        const index = [...tabContent.querySelectorAll(selectors.tabPane)].findIndex(
            (item) => {
                return item === tabPane;
            },
        );
        const width = tabContentWrap.getBoundingClientRect().width;

        const translateValue = index * -width;

        tabContent.style.cssText = `transform: translate(${translateValue}px, 0);`;
    };

    document.addEventListener('click', (event) => {
        if (event.target && event.target.closest(selectors.setNicknameBtn)) {
            event.preventDefault();
            const userId = document.querySelector(selectors.userIdInput).value;
            const value = document.querySelector(selectors.nicknameInput).value;
            savePlayerName(value, userId);
            return false;
        }
    });

    /* CLUB-431 document.addEventListener('click', (event) => {
        if (event.target && event.target.closest(selectors.setProfileImageBtn)) {
            event.preventDefault();
            const userId = document.querySelector(selectors.userIdInput).value;
            const value = document.querySelector(selectors.profileImageInput).value;
            savePlayerAvatar(value, userId);
            return false;
        }
    });*/

    document.addEventListener('click', (event) => {
        if (event.target && event.target.closest(selectors.tabLink)) {
            if (!document.getElementById(selectors.profileTabsId)) {
                return;
            }
            event.preventDefault();
            document
                .querySelectorAll(selectors.tabLink)
                .forEach((item) => item.classList.remove('active'));
            event.target.classList.add('active');
            const targetId = event.target.getAttribute('href');

            setTabContentOffset(`#${selectors.profileTabsId}`);
        }
    });

    document.addEventListener('click', (event) => {
        if (event.target && event.target.closest(selectors.copyBtn)) {
            event.preventDefault();
            copyToClipboard(event.target.closest(selectors.copyBtn).getAttribute('href'));
        }
    });

    const onProfileModalLoaded = () => {
        if (!document.getElementById(selectors.profileTabsId)) {
            return;
        }

        const setWidthToPanes = () => {
            const tabPaneList = document.querySelectorAll(selectors.tabPane) || [];
            if (tabPaneList.length > 0) {
                const tabContentWrap = tabPaneList[0].closest(selectors.tabContentWrap);
                let width = tabContentWrap.getBoundingClientRect().width + 'px';

                for (let i = 0; i < tabPaneList.length; i++) {
                    tabPaneList[i].style.width = width;
                    const currentWidth = tabContentWrap.getBoundingClientRect().width + 'px';
                    if (currentWidth !== width) {
                        width = currentWidth;
                        i = 0;
                    }
                }
            }
        };

        setWidthToPanes();
        setTabContentOffset(`#${selectors.profileTabsId}`);

        if (!window.profileTabslistenerAttached) {
            window.addEventListener('resize', () => {
                if (!document.getElementById(selectors.profileTabsId)) {
                    return;
                }
                setWidthToPanes();
                setTabContentOffset(`#${selectors.profileTabsId}`);
            });
            window.profileTabslistenerAttached = true;
        }

        window.dispatchEvent(new Event('resize'));
    };

    window.profileModal = {onProfileModalLoaded};
})();

const btnFAQClickHandler = (backButton = true) => {
    bootbox.hideAll();

    getFAQModal().then(html => {

        dialog = bootbox
            .dialog({
                message: html,
                locale: lang === 'RU' ? 'ru' : 'en',
                className: 'modal-settings  modal-faq',
                closeButton: false,
                buttons: {
                    ok: {
                        label: backButton ? '<?= T::S('Back')?>' : 'OK',
                        className: 'btn-sm ml-auto mr-0',
                        callback: function () {
                            if (backButton) {
                                fetchGlobal(STATUS_CHECKER_SCRIPT).then((data) => {
                                    commonCallback(data);
                                    gameStates['chooseGame']['action'](data)
                                });
                            } else {
                                closeDialogs();
                            }
                        }
                    },
                }
            })
            .off('shown.bs.modal').on('shown.bs.modal', function () {
                if (tabsModule) {
                    tabsModule.initTabs();
                }
            }).find('.modal-content').css({
                'background-color': 'rgba(230, 255, 230, 1)',
            });


    }).catch(error => {
        console.error(error);
    });

    return false;
};

document.addEventListener('click', (e) => {
    if (e.target && e.target.matches('#btn-faq')) {
        e.preventDefault();
        btnFAQClickHandler();
    }
});

function StatsPage({json, BASE_URL}) {
    const CardList = ({list}) => {
        const types = {
            day: 'stone_card',
            week: 'bronze_card',
            month: 'silver_card',
            year: 'gold_card',
        };

        let result = list
            .map(
                ({
                     event_type,
                     event_period,
                     record_type_text,
                     event_type_text,
                     points_text,
                     reward,
                     income,
                     date_achieved,
                 }) => {
                    const date = new Date(date_achieved);
                    let strDate =
                        `0${date.getDate()}`.slice(-2) +
                        '.' +
                        `0${date.getMonth() + 1}`.slice(-2) +
                        '.' +
                        date.getFullYear();

                    return `
                <li>
                    <div class="card_item full_card ${types[event_period]}">
                        <h3 class="card_record">
                            ${record_type_text} <br>
                            ${event_type_text}
                        </h3>
                        <div class="card_points">
                            ${points_text}
                        </div>
                        <div class="card_get">
                            <p><?= T::S('Got reward') ?></p>
                            <div class="card_rewardInfo">
                                <p><img class="card_plus" src="./images/plus.png" alt=""></p>
                                <p><img class="card_rewardImage" src="./images/bigMoney.png" alt="money">
                                </p>
                                <span class="card_moneyCount">${reward}</span>
                            </div>
                        </div>
                        <p class="card_passive"><?= T::S('Your passive income') ?></p>
                        <div class="card_hour">
                            <img class="card_hourImage" src="./images/smallMoney.png" alt="">
                            <span>x${income}/<?= T::S('per_hour') ?></span>
                        </div>

                        <p class="card_effect"><?= T::S('Effect lasts until beaten') ?></p>
                    </div>
                    <span class="date">${strDate}</span>
                </li>
            `;
                },
            )
            .join('');

        result = `<ul class="card_list full_cards">${result}</ul>`;

        return result;
    };

    const GameList = ({games}) => {
        let gameList = '';
        if (!games.length) {
            return gameList;
        }
        gameList = games
            .map((item) => {
                const matchResultClass = ['victory', 'победа'].includes(
                    item.your_result.toLocaleLowerCase(),
                )
                    ? 'match-history--win'
                    : 'match-history--lose';

                let deltaCoins = +item.delta_coins !== 0
                    ? ('<br>' + (item.delta_coins > 0 ? '+' : '') + item.delta_coins + ' <img src="images/coin.png" width="30%" alt="Sudoku coin image"/>')
                    : '';

                return `
                <li class="match-history-item ${matchResultClass} box d-flex">
                    <div class="match-history-date">${item.game_ended_date}</div>
                    <div class="match-history-result">
                        <div class="pill">${item.your_result}</div>
                    </div>
                    <div class="match-history-rating">
                        <div class="pill">${item.new_rating} <span>${item.delta_rating} ${deltaCoins}</span></div>
                    </div>
                    <div class="match-history-opponent">
                        <figure class="figure">
                            <img src="${item.opponent_avatar_url}" class="figure-img rounded" alt="" />
                            <figcaption class="figure-caption text-start">
                                <a href="${item.opponent_filter_url}" title="${item.opponent_filter_title}">${item.opponent_name}</a>
                            </figcaption>
                        </figure>
                    </div>
                </li>
            `;
            })
            .join('');

        gameList = `<ul>${gameList}</ul>`;

        return gameList;
    };

    const Pagination = ({pagination}) => {
        let result = '';
        for (const key in pagination) {
            if (Object.prototype.hasOwnProperty.call(pagination, key)) {
                const element = pagination[key];
                const isActive = !element.is_link;
                if (isActive) {
                    result += `<li class="active"><span>${key}</span></li>`;
                } else {
                    result += `<li><a href="${element.value}">${key}</a></li>`;
                }
            }
        }

        return `
                <nav class="pagination-wrap">
                    <ul class="pagination">
                        ${result}
                    </ul>
                </nav>
            `;
    };

    const OpponentStats = ({opponent_stats}) => {
        const isPositiveWinRate = +opponent_stats[0].delta_rating > 0;
        const resultClass = isPositiveWinRate ? 'color-win' : 'color-lose';
        const prefix = isPositiveWinRate ? '+' : '';

        return `
				<div class="total box">

					<div class="col">
						<span><?= T::S('Games<br>total') ?></span>
						<span>${opponent_stats[0].games_count}</span>
					</div>
					<div class="col">
						<span><?= T::S('Wins<br>total') ?></span>
						<span>${opponent_stats[0].wins}</span>
					</div>
					<div class="col">
						<span><?= T::S('Gain/loss<br>in ranking') ?></span>
						<span class="${resultClass}">${prefix}${opponent_stats[0].delta_rating}</span>
					</div>
					<div class="col">
						<span><?= T::S('% Wins') ?></span>
						<span>${opponent_stats[0].win_percent}</span>
					</div>

				</div>
            `;
    };

    (function statsModal() {
        const selectors = {
            modal: '.modal-stats',
            paginationWrap: '.pagination-wrap',
            gameList: '.match-history-table ul',
            opponentStats: '.opponent-stats',
            opponentName: '.opponent-name',
            activeAwards: '#active-awards-tab-pane .card-list-wrap',
            pastAwards: '#past-awards-tab-pane .card-list-wrap',
            btnRemoveFilter: '.js-remove-filter',
        };

        const onStatsModalLoaded = () => {
            const modalContainer = document.querySelector(selectors.modal);
            const links = modalContainer.querySelectorAll(selectors.paginationWrap + ' a');
            const opponentLinks = modalContainer.querySelectorAll(selectors.gameList + ' a');
            const opponentStats = modalContainer.querySelector(selectors.opponentStats);
            const opponentName = modalContainer.querySelector(selectors.opponentName);
            const activeAwards = modalContainer.querySelector(selectors.activeAwards);
            const pastAwards = modalContainer.querySelector(selectors.past_achieves);
            const btnRemoveFilter = modalContainer.querySelector(selectors.btnRemoveFilter);

            const updateHtml = (json) => {
                const paginationEl = document.querySelector(selectors.paginationWrap);
                const gameListEl = document.querySelector(selectors.gameList);
                paginationEl.outerHTML = Pagination(json);
                gameListEl.outerHTML = GameList(json);

                tabsModule.update();

                if (Object.hasOwn(json, 'opponent_stats')) {
                    opponentStats.innerHTML = OpponentStats(json);
                    opponentName.innerHTML = json.games[0].opponent_name;
                    opponentName.parentElement.classList.remove('invisible');
                    opponentStats.classList.remove('d-none');

                    btnRemoveFilter.classList.remove('d-none');
                    btnRemoveFilter.setAttribute('data-url', json.games[0].opponent_filter_url);
                } else {
                    btnRemoveFilter.classList.add('d-none');
                    opponentName.parentElement.classList.add('invisible');
                    opponentStats.classList.add('d-none');
                }

                onStatsModalLoaded();
                tabsModule.update();
            };

            const linkHandler = (e) => {
                e.preventDefault();
                let url = e.target.getAttribute('href');
                if (!url) {
                    url = e.target.getAttribute('data-url');
                }

                if (url) {
                    return fetch(`${BASE_URL}${url}`, {
                        method: 'GET',
                        mode: 'cors',
                        cache: 'no-cache',
                        credentials: 'include',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                    })
                        .then((response) => {
                            if (!response.ok) {
                                throw new Error(`Response status: ${response.status}`);
                            }
                            return response.json();
                        })
                        .then((json) => {
                            updateHtml(json);
                            // prevLink = link;
                        })
                        .catch((error) => console.error('Ошибка загрузки страницы:', error));
                }

            };


            [...links, ...opponentLinks, btnRemoveFilter].forEach((link) => {
                if (!link.getAttribute('data-attached')) {
                    link.setAttribute('data-attached', true);
                    link.addEventListener('click', linkHandler);
                }
            });


        };

        window.statsModal = {onStatsModalLoaded};
    })();

    function getStatsModal(json) {
        return fetch(BASE_URL + 'stats-modal-tpl.html')
            .then((response) => response.text())
            .then((template) => {
                // Заменяем маркеры в шаблоне реальными данными
                let message = template

                    .replaceAll('{{name}}', json.player_name)
                    .replaceAll('{{imageUrl}}', json.player_avatar_url)
                    .replaceAll('{{gameList}}', GameList({games: json.games}))
                    .replaceAll('{{pagination}}', Pagination({pagination: json.pagination}))
                    .replaceAll('{{activeAwards}}', CardList({list: json.current_achieves}))
                    .replaceAll('{{pastAwards}}', CardList({list: json.past_achieves}))

                    .replaceAll('{{Stats}}', '<?= T::S('Stats') ?>')
                    .replaceAll('{{Past Awards}}', '<?= T::S('Past Awards') ?>')
                    .replaceAll('{{Parties_Games}}', '<?= T::S('Parties_Games') ?>')
                    .replaceAll('{{Player Awards}}', '<?= T::S('Player Awards') ?>')
                    .replaceAll('{{Player}}', '<?= T::S('Player') ?>')
                    .replaceAll('{{VS}}', '<?= T::S('VS') ?>')
                    .replaceAll('{{Date}}', '<?= T::S('Date') ?>')
                    .replaceAll('{{Result}}', '<?= T::S('Result') ?>')
                    .replaceAll('{{Rating}}', '<?= T::S('Rating') ?>')
                    .replaceAll('{{Opponent}}', '<?= T::S('Opponent') ?>')
                    .replaceAll('{{Active Awards}}', '<?= T::S('Active Awards') ?>');

                return message;
            })
            .catch((error) => console.error('Ошибка загрузки stats-modal-tpl:', error));
    }

    // ON MODAL LOADED
    function init() {
        return getStatsModal(json).then((html) => {
            // document.getElementById('test-tpl').innerHTML = html;

            // profileModal.onProfileModalLoaded();
            statsModal.onStatsModalLoaded();
            tabsModule.initTabs();

            // document.addEventListener("DOMContentLoaded", profileModal.onProfileModalLoaded);
        });
    }

    function onLoad() {
        statsModal.onStatsModalLoaded();
        tabsModule.initTabs();
    }

    return {
        buildHtml: () => getStatsModal(json),
        onLoad,
    };
}
/* ------------------------------- END OF FILE ------------------------------ */
(function tabsModule() {
    const selectors = {
        tabLink: 'a[data-toggle="tab"]',
        tabContent: '.tab-content',
        tabContentWrap: '.tab-content-wrap',
        tabPane: '.tab-pane',
    };

    // Вспомогательный метод для поиска контекста, если контейнер не задан явным образом
    const getFallbackContainer = (element) => {
        if (element) {
            return element.closest('.modal') || element.closest('.bootbox') || document;
        }
        // Ищем любое открытое модальное окно на странице, если ничего не передано
        return document.querySelector('.modal.show, .modal.in, .bootbox') || document;
    };

    const setTabContentOffset = (ctx, i = 0) => {
        // РЕЖИМ 1 (Старый код): Если контейнер не передан, определяем его автоматически
        ctx = ctx || getFallbackContainer();

        if (!ctx.querySelectorAll(`${selectors.tabLink}.active`).length) {
            return;
        }
        const targetId = ctx
            .querySelectorAll(`${selectors.tabLink}.active`)[0]
            .getAttribute('href');

        const targetPane = ctx.querySelector(targetId);
        if (!targetPane) return;

        const tabContent = targetPane.closest(selectors.tabContent);
        const tabContentWrap = tabContent.closest(selectors.tabContentWrap);
        const tabPane = targetPane;
        const modalBody = ctx.querySelector('.modal-body') || ctx;

        if (!tabContent || !targetId || !tabContentWrap || !tabPane) {
            return;
        }

        const activeTabContentWrapHeight = tabContent.getBoundingClientRect().height + 'px';
        tabContentWrap.style.height = activeTabContentWrapHeight;

        // показываем каждый таб в полный размер
        [...tabContent.querySelectorAll(selectors.tabPane)].forEach((item) => {
            item.style.height = 'auto';
            item.style.visibility = 'hidden';
            item.closest(selectors.tabContentWrap).style.height =
                item.closest(selectors.tabContent).getBoundingClientRect().height + 'px';
        });

        const maxModalBody = modalBody.getBoundingClientRect().height;

        // скрываем
        [...tabContent.querySelectorAll(selectors.tabPane)].forEach((item) => {
            if (!item.matches('.active')) {
                item.style.height = '0px';
                item.closest(selectors.tabContentWrap).style.height = '';
            } else {
                item.style.visibility = 'visible';
            }
        });

        [...tabContent.querySelectorAll(selectors.tabPane)].forEach((item) => {
            item.style.height = '';
        });

        tabContentWrap.style.height = activeTabContentWrapHeight;

        if (modalBody && typeof modalBody.style !== 'undefined') {
            modalBody.style.minHeight = maxModalBody + 'px';
        }

        const index = [...tabContent.querySelectorAll(selectors.tabPane)].findIndex((item) => {
            return item === tabPane;
        });
        const width = tabContentWrap.getBoundingClientRect().width;

        const translateValue = index * -width;

        tabContent.style.cssText = `transform: translate(${translateValue}px, 0);`;

        tabContent.querySelectorAll(selectors.tabPane).forEach((item) => {
            const width =
                item.closest(selectors.tabContentWrap).getBoundingClientRect().width + 'px';
            item.style.width = width;
        });

        const diff = Math.abs(
            tabContentWrap.getBoundingClientRect().height - tabContent.scrollHeight,
        );

        if (diff > 1 && i < 100) {
            i++;
            setTimeout(setTabContentOffset, 100, ctx, i);
        }
    };

    const update = (ctx) => {
        // Защита на случай, если ctx не передали (для безопасности)
        if (!ctx) ctx = getFallbackContainer();

        // Передаем ctx напрямую в расчет смещений
        setTimeout(() => setTabContentOffset(ctx), 100);

        // Ищем настройки строго внутри переданного контекста
        const settings = ctx.querySelector('.modal-settings');
        if (settings && typeof onImagesLoaded === 'function') {
            onImagesLoaded(settings, () => setTabContentOffset(ctx));
        }
    };

    document.addEventListener('click', (event) => {
        const link = event.target && event.target.closest(selectors.tabLink);
        if (link) {
            event.preventDefault();

            // Определяем контекст клика: текущая модалка или документ
            const ctx = getFallbackContainer(link);

            ctx.querySelectorAll(selectors.tabLink).forEach((item) => item.classList.remove('active'));
            link.classList.add('active');

            // Вызываем расчет в контексте найденного контейнера
            setTabContentOffset(ctx === document ? null : ctx);
        }
    });

    const initTabs = (containerSelector = '', tabsMode = '') => {
        // Разрешаем контейнер: это может быть селектор, DOM-элемент или null
        const container = (typeof containerSelector === 'string' && containerSelector)
            ? document.querySelector(containerSelector)
            : containerSelector;

        const ctx = container || getFallbackContainer();

        ctx.querySelectorAll(selectors.tabPane).forEach((item) => {
            const wrap = item.closest(selectors.tabContentWrap);
            if (wrap) {
                item.style.width = wrap.getBoundingClientRect().width + 'px';
            }
        });

        if (!window[tabsMode + 'tabslistenerAttached']) {
            window.addEventListener('resize', () => {
                // Если мы в изолированном режиме, обновляем только этот контейнер,
                // иначе — ищем все открытые окна на странице
                const targets = container ? [container] : document.querySelectorAll('.modal, .bootbox');

                targets.forEach(target => {
                    if (target === document || target.offsetWidth > 0 || target.offsetHeight > 0) {
                        target.querySelectorAll(selectors.tabPane).forEach((item) => {
                            const wrap = item.closest(selectors.tabContentWrap);
                            if (wrap) {
                                item.style.width = wrap.getBoundingClientRect().width + 'px';
                            }
                        });
                        setTabContentOffset(target === document ? null : target);
                    }
                });
            });
            window[tabsMode + 'tabslistenerAttached'] = true;
        }

        setTimeout(() => {
            window.dispatchEvent(new Event('resize'));
            setTimeout(() => {
                update(ctx);
            }, 500);
        }, 100);
    };

    window.tabsModule = {initTabs, update};
})();

function onImagesLoaded(container, event) {
    var images = container.getElementsByTagName('img');
    var loaded = images.length;
    for (var i = 0; i < images.length; i++) {
        if (images[i].complete) {
            loaded--;
        } else {
            images[i].addEventListener('load', function () {
                loaded--;
                if (loaded == 0) {
                    event();
                }
            });
        }
        if (loaded == 0) {
            event();
        }
    }
}

function getInstructions(lang) {
    const url = BASE_URL + 'mvc/faq/getAll?lang=' + lang + version();

    return fetch(url)
        .then(response => {
            if (response.ok) {
                return response.json();
            } else {
                throw new Error('Ошибка при получении инструкций');
            }
        }).catch(error => console.error('Ошибка загрузки instructions:', error));
}

function getFAQModal() {
    return fetch(BASE_URL + 'faq-modal-tpl_' + lang + '.html')
        .then(response => response.text())
        .then(template => {

            return new Promise((resolve, reject) => {
                let message = document.createElement('div');

                getInstructions(lang).then(instructions => {

                    message.innerHTML = template;

                    ['faq_rules', 'faq_rating', 'faq_rewards', 'faq_coins'].forEach(item => {
                        if (item in instructions) {
                            message.querySelector(`#${item}`).innerHTML = instructions[item];
                        }
                    });

                    resolve(message);
                }).catch(error => reject(error));
            });
        })
        .catch(error => console.error('Ошибка загрузки faq-modal:', error));
}

function PlayersPage(json) {
    function Card({
                      event_type,
                      event_period,
                      record_type_text,
                      event_type_text,
                      points_text,
                      reward,
                      income,
                      date_achieved,
                  } = props) {
        const types = {
            day: 'stone_card',
            week: 'bronze_card',
            month: 'silver_card',
            year: 'gold_card',
            purple: 'purple_card'
        };

        const date = new Date(date_achieved);
        let strDate =
            `0${date.getDate()}`.slice(-2) +
            '.' +
            `0${date.getMonth() + 1}`.slice(-2) +
            '.' +
            date.getFullYear();

        let incomePeriod = event_type === 'patreon'
            ? '<?= T::S('per_day') ?>'
            : '<?= T::S('per_hour') ?>';
        let effectLastsText = event_type === 'patreon'
            ? '<?= T::S('Effect lasts forever') ?>'
            : '<?= T::S('Effect lasts until beaten') ?>';
        if (!reward) {
            reward = '-';
        }

        return `
				<div class="card_item card--big full_card ${types[event_period]}">
					<h3 class="card_record">
						${record_type_text} <br>
						${event_type_text}
					</h3>
					<div class="card_points">
						${points_text}
					</div>
					<div class="card_get">
						<p><?= T::S('Got reward') ?></p>
						<div class="card_rewardInfo">
							<p><img class="card_plus" src="./images/plus.png" alt=""></p>
							<p><img class="card_rewardImage" src="./images/bigMoney.png" alt="money">
							</p>
							<span class="card_moneyCount">${reward}</span>
						</div>
					</div>
					<p class="card_passive"><?= T::S('Your passive income') ?></p>
					<div class="card_hour">
						<img class="card_hourImage" src="./images/smallMoney.png" alt="">
						<span>x${income}/${incomePeriod}</span>
					</div>

					<p class="card_effect">${effectLastsText}</p>
				</div>
				<span class="date">${strDate}</span>

		`;
    }

    const CardCompact = (props) => {
        const {
            event_value,
            event_period,
            record_type_text,
            event_type_text,
            reward,
            record_type,
            event_type,
        } = props;

        const types = {
            day: 'stone_card',
            week: 'bronze_card',
            month: 'silver_card',
            year: 'gold_card',
            purple: 'purple_card',
        };

        return `
		<div class="award-wrap">
					<div class="card_item ${types[event_period]}"  data-props='${JSON.stringify(props)}'>
						<div class="card_record">
							${record_type_text}
							<hr class="divider">
							<div class="card_points">
                            ${event_type_text}
							</div>
						</div>
						<div class="card_get">
							<div class="card_rewardInfo">
								<img class="card_plus" src="./images/plus.png" alt="">
								<img class="card_rewardImage" src="./images/bigMoney.png" alt="money">
								<span class="card_moneyCount">x${reward}</span>
							</div>
						</div>
					</div>
				</div>
	`;
    };

    const PlayerBox = (props) => {
        const {
            common_id,
            you,
            nickname,
            avatar_url,
            stats_url,
            is_balance_hidden,
            balance,
            rating,
            rating_position,
            games_played,
            index,
            achieves = [],
            top_bage_url = '',
        } = props;

        if (!props) {
            return;
        }

        let boxLabel;
        let toggleBalanceVisibilityBtn = '';
        if (you) {
            boxLabel = '<?= T::S('You') ?>';
            toggleBalanceVisibilityBtn = `<a href="#" class="js-toggle-balance-visibility" data-user-id="${common_id}" data-hidden=${is_balance_hidden}><i class="icon icon-eye ${is_balance_hidden ? 'icon-eye--x' : ''} mx-2"></i></a>`;
        } else {
            boxLabel = '<?= T::S('Player') ?>' + (index + 1);
        }

        let awards;
        let cards = achieves
            .map((item) => {
                return CardCompact(item);
            })
            .join('');
        if (top_bage_url) {
            cards = `<div class="top-rating-img-wrap"><img src="${top_bage_url}" height="80" alt=""></div>${cards}`;
        }

        awards = `<div class="awards d-flex row-cols-3 flex-wrap">
				${cards}
			</div>`;

        return `
		<div class="box box-player">
			<div class="label box-heading text-center mx-auto fs-4">${boxLabel}</div>
			<div class="d-flex mb-2">
				<div class="nickname">${nickname}</div>
				<button class="btn btn-sm ml-auto js-modal-stats" data-user-id="${common_id}"><?= T::S('Stats') ?></button>
			</div>
			<div class="d-flex" style="margin-bottom: 5px;">
				<div class="img-col">
					<div class="img-wrap">
						<img src="${avatar_url}" class="img-fluid rounded" alt="avatar">
					</div>
				</div>
				<div class="info-col">
					<ul>
						<li>
							<div class="label"><?= T::S('Rating') ?></div>
							<div class="pill">${rating}</div>
						</li>
						<li>
							<div class="label"><?= T::S('Ranking number') ?></div>
							<div class="pill">${rating_position}</div>
						</li>
						<li>
							<div class="label d-flex align-items-center"><?= T::S('Balance') ?><i class="icon icon-coin ml-2"></i></div>
							<div class="pill-wrap d-flex ml-auto">
								${toggleBalanceVisibilityBtn}
                                <div class="pill">${balance}</div>
							</div>
						</li>
						<li>
							<div class="label"><?= T::S('Games Played') ?></div>
							<div class="pill">${games_played}</div>
						</li>
					</ul>
				</div>


			</div>
			${awards}
		</div>
	`;
    };

    const cardClickHandler = (e) => {
        if (e.target && e.target.closest('.modal-players .card_item')) {
            const props = e.target.closest('.modal-players .card_item').getAttribute('data-props');
            if (props) {
                const card = Card(JSON.parse(props));
                const modalHtml = `
							<div class="box d-flex align-items-center p-2 mb-2">
								<div class="box-heading text-center mx-auto fs-4"><?= T::S('Reward') ?></div>
							</div>
							<div class="box card-list-wrap">
								<div class="card_list">
									<div>${card}</div>
								</div>
							</div>`;

                dialog = bootbox.alert({
                    title: '',
                    message: modalHtml,
                    className: 'modal-settings modal-card modal--footer-compact',
                    buttons: {
                        ok: {
                            label: '<?= T::S('Back') ?>',
                            className: 'btn btn-sm ml-auto mr-0',
                        },
                    },
                    onShown: function (e) {
                    },
                    closeButton: false
                });
            }
        }
    };

    function init() {
        if (!window.cardCompactClickHandler) {
            window.cardCompactClickHandler = cardClickHandler;
            document.addEventListener('click', cardClickHandler);
        }

        const playerBoxes = json.map((element, i) => PlayerBox({...element, index: i})).join('');

        const html = `<div><div class="box d-flex align-items-center p-2 mb-2">
						<div class="box-heading text-center mx-auto fs-4"><?= T::S('Players') ?></div>
					</div>
					${playerBoxes}</div>`;

        return html;
    }

    function onLoad() {
        $('.modal-players .js-modal-stats').click(e => {
            e.preventDefault();
            const userId = e.target?.closest('.js-modal-stats').getAttribute('data-user-id');

            getStatPageGlobal(userId).then(data => {
                dialog = bootbox.dialog({
                    message: data.message,
                    locale: lang === 'RU' ? 'ru' : 'en',
                    className: 'modal-settings modal-stats',
                    callback: function () {
                        console.log('stats loaded');
                    },
                    onShow: function (e) {
                        $('.modal-players.show, .modal-players.show + .modal-backdrop.show').hide();
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
                                $('.modal-players.show, .modal-players.show + .modal-backdrop.show').show();
                            }
                        },
                    }
                }).off('shown.bs.modal').on('shown.bs.modal', function () {
                    if (data.onLoad && typeof data.onLoad === 'function') {
                        data.onLoad();
                    }
                }).find('.modal-content').css({
                    'background-color': 'rgba(230, 255, 230, 1)',
                });
            });
        });

        $('.js-toggle-balance-visibility').click((e) => {
            e.preventDefault();
            const btn = e.target?.closest('.js-toggle-balance-visibility');
            if (!btn) {
                return;
            }

            const $that = $(btn);
            const isHidden = (btn.getAttribute('data-hidden') === 'true');
            const userId = btn.getAttribute('data-user-id');
            const newVisibility = isHidden ? 'show' : 'hide';

            // todo use fetchGlobalMVC everywhere
            const url = BASE_URL + `mvc/players/hideBalance/?common_id=${userId}&hide=${newVisibility}`;

            fetch(url).then((response) => {
                    if (!response.ok) {
                        throw new Error(`Response status: ${response.status}`);
                    }
                    return response.json();
                }
            ).then((json) => {
                    if ('is_balance_hidden' in json) {
                        $that.data('balance', json.is_balance_hidden);
                        if (json.is_balance_hidden) {
                            $that.find('.icon').addClass('icon-eye--x');
                            btn.setAttribute('data-hidden', true);
                        } else {
                            btn.setAttribute('data-hidden', false);
                            $that.find('.icon').removeClass('icon-eye--x');
                        }

                        $that.parent().find('.pill').text(json.balance);
                    }

                }
            ).catch((error) => console.error('Ошибка ', error));

        });
    }

    return {
        buildHtml: init,
        onLoad,
    };
}

function prizesButtonHandler() {
    if (bootBoxIsOpenedGlobal()) {
        return;
    }

    disableButtons();

    const leaderboard = Leaderboard();
    window.leaderboard = leaderboard;
    leaderboard.getModal().then((html) => {
        dialog = bootbox.alert({
                title: '',
                message: html,
                // locale: 'ru',
                closeButton: false,
                className: 'modal-settings modal-leaderboard  modal--footer-compact',
                buttons: {
                    ok: {
                        label: '<?= T::S('Back') ?>', // lang === 'RU' ? 'Назад' : 'Back',
                        className: 'btn-sm ml-auto mr-0',
                    },
                },
                callback: function () {
                    closeDialogs();
                    enableButtons();
                },
            }).off('shown.bs.modal').on('shown.bs.modal', function () {
                if (tabsModule) {
                    tabsModule.initTabs();
                }

                const getNextRatingChunk = debounceAsync(leaderboard.getNextRatingChunk.bind(leaderboard), 500);
                const getNextCoinsChunk = debounceAsync(leaderboard.getNextCoinsChunk.bind(leaderboard), 500);

                document.querySelector('.modal-leaderboard .modal-body').addEventListener('scroll', (e) => {
                    const container = e.currentTarget;
                    const ratingListEl = container.querySelector('#leaderboard-rating');
                    const coinsListEl = container.querySelector('#leaderboard-coins');

                    if (container.scrollTop + container.offsetHeight >= container.scrollHeight * 0.95) {

                        if (ratingListEl.closest('.tab-pane.active')) {
                            getNextRatingChunk().then((list) => {
                                if (!list.length) return;
                                const ratingElements = list.map((r) => leaderboard.RatingItem(r),);
                                const wrapper = document.createElement('div');
                                wrapper.innerHTML = ratingElements.join('');
                                ratingListEl.append(...wrapper.childNodes);
                                setTimeout(() => {
                                    if (tabsModule) {
                                        tabsModule.initTabs();
                                    }
                                })
                            });
                        }

                        if (coinsListEl.closest('.tab-pane.active')) {
                            getNextCoinsChunk().then((list) => {
                                if (!list.length) return;
                                const coinsElements = list.map((r) => leaderboard.CoinsItem(r),);
                                const wrapper = document.createElement('div');
                                wrapper.innerHTML = coinsElements.join('');
                                coinsListEl.append(...wrapper.childNodes);
                                setTimeout(() => {
                                    if (tabsModule) {
                                        tabsModule.initTabs();
                                    }
                                })

                            });
                        }
                    }
                });
            }).find('.modal-content').css({
                'background-color': 'rgba(230, 255, 230, 1)',
            });
        }
    );

    return;
}

function Leaderboard() {
    const pageSize = 20;

    const parseFlatList = (data) => {
        const result = [];
        for (const position in data) {
            const users = data[position];
            users.forEach((user) => {
                result.push({
                    ...user,
                    position: Number(position),
                });
            });
        }
        return result;
    };

    const fetchData = async (params) => {
        const url = new URL(BASE_URL + 'mvc/stats/leaders');

        for (const key in params) {
            url.searchParams.set(key, params[key]);
        }

        url.searchParams.set('ver', Math.floor(Date.now()));
        url.searchParams.set('lang', lang);

        try {
            const response = await fetch(url.toString(), {
                method: 'GET',
                mode: 'cors',
                cache: 'no-cache',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
            });
            if (!response.ok) throw new Error('Ошибка загрузки данных');
            return await response.json();
        } catch (e) {
            console.error('Fetch error:', e);
            throw e;
        }
    };

    return {
        state: {
            pageSize,
            rating: {
                isError: false,
                isLoading: false,
                data: [],
                apiPage: 0,
                apiPageSize: 20,
                apiDone: false,
                page: 0,
                pageSize: 20,
                done: false,
            },
            coins: {
                isError: false,
                isLoading: false,
                data: [],
                apiPage: 0,
                apiPageSize: 20,
                apiDone: false,
                page: 0,
                pageSize: 20,
                done: false,
            },
            achieves: {
                isError: false,
                isLoading: false,
                data: [],
            },
        },

        async fetchDefault() {
            this.state.isLoading = true;
            this.state.isError = false;
            try {
                const data = await fetchData({});
                if (!data.rating) {
                    this.state.rating.apiDone = true;
                }
                this.state.rating.data = parseFlatList(data.rating);
                this.state.coins.data = parseFlatList(data.coins);
                if (!data.coins) {
                    this.state.coins.apiDone = true;
                }
                this.state.achieves.data = data.achieves;
            } catch (e) {
                this.state.isError = true;
            } finally {
                this.state.isLoading = false;
            }
        },

        async fetchRating(page = 0) {
            const rating = this.state.rating;

            rating.isLoading = true;
            rating.isError = false;

            const from = page * rating.pageSize + 1;
            const to = (page + 1) * rating.pageSize;

            try {
                const data = await fetchData({
                    data_type: 'rating',
                    rating_position_from: from,
                    rating_position_to: to,
                });

                if (!data.rating) {
                    rating.apiDone = true;
                    return;
                }

                if (!rating.apiPageSize) {
                    rating.apiPageSize = data.rating.length;
                }

                if (data.rating.length < rating.apiPageSize) {
                    rating.apiDone = true;
                }

                const list = parseFlatList(data.rating);

                rating.data.push(...list);
            } catch (e) {
                rating.isError = true;
            } finally {
                rating.isLoading = false;
            }
        },

        /**
         * Asynchronously retrieves the next chunk of rating data based on the current pagination state.
         * @async
         * @returns {Promise<Array>} A promise that resolves to the next chunk of rating data, or an empty array if no more data is available.
         */
        async getNextRatingChunk() {
            const rating = this.state.rating;
            const {pageSize} = rating;

            if (rating.done || rating.isLoading) return [];

            const start = rating.page * pageSize;
            const end = start + pageSize;


            if (rating.data.length >= end) {
                const chunk = rating.data.slice(start, end);
                rating.page++;

                return chunk;
            }

            // Если API ещё не закончил, догружаем
            if (!rating.apiDone) {
                rating.apiPage++;
                await this.fetchRating(rating.apiPage);

                return this.getNextRatingChunk();
            }

            // Остались хвостовые данные, меньше pageSize
            const chunk = rating.data.slice(start);
            if (chunk.length > 0) {
                rating.page++;
                rating.done = true;
                return chunk;
            }

            // Ничего не осталось
            rating.done = true;
            return [];
        },


        async fetchCoins(page = 0) {
            const coins = this.state.coins;

            coins.isLoading = true;
            coins.isError = false;

            const from = page * coins.pageSize + 1;
            const to = (page + 1) * coins.pageSize;

            try {
                const data = await fetchData({
                    data_type: 'coins',
                    coin_position_from: from,
                    coin_position_to: to,
                });

                if (!data.coins) {
                    coins.apiDone = true;
                    return;
                }

                if (!coins.apiPageSize) {
                    coins.apiPageSize = data.coins.length;
                }

                if (data.coins.length < coins.apiPageSize) {
                    coins.apiDone = true;
                }

                const list = parseFlatList(data.coins);

                coins.data.push(...list);
            } catch (e) {
                coins.isError = true;
            } finally {
                coins.isLoading = false;
            }
        },

        /**
         * Asynchronously retrieves the next chunk of coins data based on the current pagination state.
         * @async
         * @returns {Promise<Array>} A promise that resolves to the next chunk of coins data, or an empty array if no more data is available.
         */
        async getNextCoinsChunk() {
            const coins = this.state.coins;
            const {pageSize} = coins;

            if (coins.done || coins.isLoading) return [];

            const start = coins.page * pageSize;
            const end = start + pageSize;


            if (coins.data.length >= end) {
                const chunk = coins.data.slice(start, end);
                coins.page++;

                return chunk;
            }

            // Если API ещё не закончил, догружаем
            if (!coins.apiDone) {
                coins.apiPage++;
                await this.fetchCoins(coins.apiPage);
                return this.getNextCoinsChunk();
            }

            // Остались хвостовые данные, меньше pageSize
            const chunk = coins.data.slice(start);
            if (chunk.length > 0) {
                coins.page++;
                coins.done = true;
                return chunk;
            }

            // Ничего не осталось
            coins.done = true;
            return [];
        },


        async fetchRecords() {
            this.state.isLoading = true;
            this.state.isError = false;

            try {
                const data = await fetchData({data_type: 'achieves'});
                this.state.achieves = data.achieves;
            } catch (e) {
                this.state.isError = true;
            } finally {
                this.state.isLoading = false;
            }
        },

        getModal: function () {
            return fetch(BASE_URL + 'leaderboard-modal-tpl_' + lang + '.html')
                .then((response) => response.text())
                .then((template) => {
                    return this.fetchDefault().then(async () => {
                        const message = document.createElement('div');

                        const {isError, achieves} = this.state;
                        if (isError) throw new Error('Net Error');
                        try {
                            let ratingList = await this.getNextRatingChunk(
                                this.state
                            );

                            ratingList = ratingList.map((r) =>
                                this.RatingItem(r)
                            );

                            let coinsList = await this.getNextCoinsChunk(
                                this.state
                            );

                            coinsList = coinsList.map((r) =>
                                this.CoinsItem(r)
                            );


                            let recordList = Object.entries(achieves.data).map(section => {
                                let [heading, items] = section;
                                items = Object.values(items);
                                items = items.map(a => this.RecordsItem(a)).join('');
                                return `<span class="records-heading">${heading}</span>${items}`;
                            });

                            const ratingHtml = `
                                <div class="list-heading">
                                    <span>№</span>
                                    <span><?= T::S('Player')?></span>
                                    <span><?= T::S('Rating')?></span>
                                </div>
                                ${ratingList.join('')}
                            `;

                            const coinsHtml = `
                                <div class="list-heading">
                                    <span>№</span>
                                    <span><?= T::S('Player')?></span>
                                    <span><?= T::S('Balance')?></span>
                                </div>
                                ${coinsList.join('')}
                            `;

                            const tabs = {
                                'leaderboard-rating': ratingHtml,
                                'leaderboard-records': recordList.join(''),
                                'leaderboard-coins': coinsHtml,
                            };

                            message.innerHTML = template;
                            [
                                'leaderboard-rating',
                                'leaderboard-records',
                                'leaderboard-coins',
                            ].forEach((id) => {
                                if (tabs[id]) {
                                    message.querySelector(`#${id}`).innerHTML =
                                        tabs[id];
                                }
                            });
                            return message;
                        } catch (error) {
                            console.log(error);
                            return 'error';
                        }
                    });
                })
                .catch((error) =>
                    console.error('Ошибка загрузки leaderboard-modal:', error)
                );
        },

        RatingItem: ({avatar_url, card_type, rating, nickname, position, common_id}) => {
            const classMod = card_type ? ` card--${card_type}` : '';
            return /*html*/ `
                <div class="card${classMod}" onClick="showStatsForLeaders(${common_id})">
                    <span class="position">${position}</span>
                    <div class="avatar"><img src="${avatar_url}" alt="${nickname}"></div>
                    <span class="nickname">${nickname}</span>
                    <span class="rating">${rating}</span>
                </div>
            `;
        },

        CoinsItem: ({avatar_url, card_type, coins, nickname, position, common_id}) => {
            const classMod = card_type ? ` card--${card_type}` : '';
            const coinsFormatted = coins.toLocaleString('en-US', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).replaceAll('&nbsp;', ',');
            return /*html*/ `
                <div class="card${classMod}" onClick="${common_id} ? showStatsForLeaders(${common_id}) : ''">
                    <span class="position">${position}</span>
                    <div class="avatar"><img src="${avatar_url}" alt="${nickname}"></div>
                    <span class="nickname">${nickname}</span>
                    <span class="coins">${coinsFormatted}</span>
                </div>
            `;
        },
        RecordsItem: ({record_type, record_value, nickname, card_type, avatar_url, common_id}) => {
            const classMod = card_type ? ` card--${card_type}` : '';

            return /*html*/ `
                <div class="card record-card${classMod}" onClick="showStatsForLeaders(${common_id})">
                    <div class="desc">
                        <span>${record_type}</span>
                        <span>${record_value}</span>
                    </div>
                    <div class="avatar"><img src="${avatar_url}" alt="${nickname}"></div>
                    <span class="nickname">${nickname}</span>
                </div>
            `;
        },
    };
}

function debounce(func, wait) {
    let timeout;

    return function (...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), wait);
    };
}

function debounceAsync(func, wait) {
    let timeout;

    return function (...args) {
        const context = this;

        return new Promise((resolve, reject) => {
            clearTimeout(timeout);
            timeout = setTimeout(async () => {
                try {
                    const result = await func.apply(context, args);
                    resolve(result);
                } catch (error) {
                    reject(error);
                }
            }, wait);
        });
    };
}

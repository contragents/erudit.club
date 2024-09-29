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

    document.addEventListener('click', (event) => {
        if (event.target && event.target.closest(selectors.setProfileImageBtn)) {
            event.preventDefault();
            const userId = document.querySelector(selectors.userIdInput).value;
            const value = document.querySelector(selectors.profileImageInput).value;
            savePlayerAvatar(value, userId);
            return false;
        }
    });

    document.addEventListener('click', (event) => {
        if (event.target && event.target.closest(selectors.tabLink)) {
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
                setWidthToPanes();
                setTabContentOffset(`#${selectors.profileTabsId}`);
            });
            window.profileTabslistenerAttached = true;
        }

        window.dispatchEvent(new Event('resize'));
    };

    window.profileModal = { onProfileModalLoaded };
})();

const btnFAQClickHandler = () => {
    dialog = bootbox
        .alert({
            message: instruction,
            locale: 'ru',
        })
        .off('shown.bs.modal');

    return false;
};

document.addEventListener('click', (e) => {
    if (e.target && e.target.matches('#btn-faq')) {
        e.preventDefault();
        btnFAQClickHandler();
    }
});

function StatsPage({ json, BASE_URL }) {
    // const BASE_URL = 'http://127.0.0.1:5500';

    const CardList = ({ list }) => {
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
                            <p>Got reward</p>
                            <div class="card_rewardInfo">
                                <p><img class="card_plus" src="./images/plus.png" alt=""></p>
                                <p><img class="card_rewardImage" src="./images/bigMoney.png" alt="money">
                                </p>
                                <span class="card_moneyCount">x${reward}</span>
                            </div>
                        </div>
                        <p class="card_passive">Your passive income</p>
                        <div class="card_hour">
                            <img class="card_hourImage" src="./images/smallMoney.png" alt="">
                            <span>x${income}/hour</span>
                        </div>

                        <p class="card_effect">Effect lasts util beaten</p>
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

    const GameList = ({ games }) => {
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
                return `
                <li class="match-history-item ${matchResultClass} box d-flex">
                    <div class="match-history-date">${item.game_ended_date}</div>
                    <div class="match-history-result">
                        <div class="pill">${item.your_result}</div>
                    </div>
                    <div class="match-history-rating">
                        <div class="pill">${item.new_rating} <span>${item.delta_rating}</span></div>
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

    const Pagination = ({ pagination }) => {
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

    const OpponentStats = ({ opponent_stats }) => {
        const isPositiveWinRate = +opponent_stats[0].delta_rating > 0;
        const resultClass = isPositiveWinRate ? 'color-win' : 'color-lose';
        const prefix = isPositiveWinRate ? '+' : '';

        return `
				<div class="total box">

					<div class="col">
						<span>Всего<br>партий</span>
						<span>${opponent_stats[0].games_count}</span>
					</div>
					<div class="col">
						<span>Всего<br>побед</span>
						<span>${opponent_stats[0].wins}</span>
					</div>
					<div class="col">
						<span>Прибавка/потеря<br>в рейтинге</span>
						<span class="${resultClass}">${prefix}${opponent_stats[0].delta_rating}</span>
					</div>
					<div class="col">
						<span>% Побед</span>
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
                    // console.log(`${BASE_URL}/${url}`);

                    return fetch(`${BASE_URL}/${url}`, {
                        headers: { 'Content-Type': 'application/json' },
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

        window.statsModal = { onStatsModalLoaded };
    })();

    function getStatsModal(json) {
        return fetch('/stats-modal-tpl.html')
            .then((response) => response.text())
            .then((template) => {
                // Заменяем маркеры в шаблоне реальными данными
                let message = template

                    .replaceAll('{{name}}', json.player_name)
                    .replaceAll('{{imageUrl}}', json.player_avatar_url)
                    .replaceAll('{{gameList}}', GameList({ games: json.games }))
                    .replaceAll('{{pagination}}', Pagination({ pagination: json.pagination }))
                    .replaceAll('{{activeAwards}}', CardList({ list: json.current_achieves }))
                    .replaceAll('{{pastAwards}}', CardList({ list: json.past_achieves }))


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
                    .replaceAll('{{Active Awards}}', '<?= T::S('Active Awards') ?>')
                    .replaceAll('{{Статистика}}', '<?= T::S('Stats') ?>')
                ;
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

    // document.addEventListener('DOMContentLoaded', (e) => {
    // 	getStatsModal(json).then((html) => {
    // 		// document.getElementById('test-tpl').innerHTML = html;

    // 		// profileModal.onProfileModalLoaded();
    // 		statsModal.onStatsModalLoaded();
    // 		tabsModule.initTabs();

    // 		// document.addEventListener("DOMContentLoaded", profileModal.onProfileModalLoaded);
    // 	});
    // });

    return {
        buildHtml: () => getStatsModal(json),
        onLoad,
    };
}
/* ------------------------------- END OF FILE ------------------------------ */
(function tabsModule() {
    // on modal loaded
    // document.addEventListener("DOMContentLoaded", initTabs);

    const selectors = {
        tabLink: 'a[data-toggle="tab"]',
        tabContent: '.tab-content',
        tabContentWrap: '.tab-content-wrap',
        tabPane: '.tab-pane',
    };

    const setTabContentOffset = (i = 0) => {
        if (
            !document.querySelectorAll(`${selectors.tabLink}.active`)
            ||
            !(0 in document.querySelectorAll(`${selectors.tabLink}.active`))
        ) {
            return;
        }

        const targetId = document.querySelectorAll(`${selectors.tabLink}.active`)[0].getAttribute('href');
        const tabContent = document.querySelector(targetId).closest(selectors.tabContent);
        const tabContentWrap = tabContent.closest(selectors.tabContentWrap);
        const tabPane = document.querySelector(targetId);

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

        const maxModalBody = document.querySelector('.modal-body').getBoundingClientRect().height;

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

        document.querySelector('.modal-body').style.minHeight = maxModalBody + 'px';

        const index = [...tabContent.querySelectorAll(selectors.tabPane)].findIndex((item) => {
            return item === tabPane;
        });
        const width = tabContentWrap.getBoundingClientRect().width;

        const translateValue = index * -width;

        tabContent.style.cssText = `transform: translate(${translateValue}px, 0);`;

        document.querySelectorAll(selectors.tabPane).forEach((item) => {
            const width =
                item.closest(selectors.tabContentWrap).getBoundingClientRect().width + 'px';
            item.style.width = width;
        });

        const diff = Math.abs(
            tabContentWrap.getBoundingClientRect().height - tabContent.scrollHeight,
        );
        // console.log(i, diff);

        if (diff > 1 && i < 100) {
            i++;
            // console.log(i, diff);

            setTimeout(setTabContentOffset, 100, i);
        }
    };

    const update = () => {
        setTimeout(() => setTabContentOffset(), 100);
        onImagesLoaded(document.querySelector('.modal-settings'), setTabContentOffset);
    };

    document.addEventListener('click', (event) => {
        if (event.target && event.target.closest(selectors.tabLink)) {
            event.preventDefault();
            document
                .querySelectorAll(selectors.tabLink)
                .forEach((item) => item.classList.remove('active'));
            event.target.classList.add('active');

            setTabContentOffset();
        }
    });

    const initTabs = () => {
        document
            .querySelectorAll(selectors.tabPane)
            .forEach(
                (item) =>
                    (item.style.width =
                        item.closest(selectors.tabContentWrap).getBoundingClientRect().width +
                        'px'),
            );

        if (!window.tabslistenerAttached) {
            window.addEventListener('resize', (event) => {
                document.querySelectorAll(selectors.tabPane).forEach((item) => {
                    const width =
                        item.closest(selectors.tabContentWrap).getBoundingClientRect().width + 'px';
                    item.style.width = width;
                });
                setTabContentOffset();
            });
            window.tabslistenerAttached = true;
        }

        setTimeout(() => {
            window.dispatchEvent(new Event('resize'));
            setTimeout(() => {
                update();
            }, 500);
        }, 100);
    };

    window.tabsModule = { initTabs, update };

    // return {
    //     onProfileModalLoaded
    // }
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




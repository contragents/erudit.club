/* eslint-disable no-global-assign */
/* eslint-disable no-unused-vars */
/* global fetchGlobal, CABINET_SCRIPT, data, gameStates, dialog, bootbox   */

// для копирования из input в буфер
function copyToClipboard(selector) {
	const element = document.querySelector(selector);
	element.select();
	element.setSelectionRange(0, 99999);
	document.execCommand('copy');
}

(function profileModal() {
	// on modal loaded
	// document.addEventListener("DOMContentLoaded", onProfileModalLoaded);

	const selectors = {
		profileTabsId: 'profile-tabs',
		tabLink: '#profile-tabs a',
		tabContent: '.tab-content',
		tabContentWrap: '.tab-content-wrap',
		tabPane: '.tab-pane',
		copyBtn: '.js-btn-copy',
	};

	const setTabContentOffset = (tabsSelector) => {
		const targetId = document
			.querySelectorAll(`${selectors.tabLink}.active`)[0]
			.getAttribute('href');
		const tabContent = document.querySelector(targetId).closest(selectors.tabContent);
		const tabContentWrap = tabContent.closest(selectors.tabContentWrap);
		const tabPane = document.querySelector(targetId);
		tabContentWrap.style.height = tabContent.getBoundingClientRect().height + 'px';

		const index = [...tabContent.querySelectorAll(selectors.tabPane)].findIndex((item) => {
			return item === tabPane;
		});
		const width = tabContentWrap.getBoundingClientRect().width;

		const translateValue = index * -width;

		tabContent.style.cssText = `transform: translate(${translateValue}px, 0);`;
	};

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
		document
			.querySelectorAll(selectors.tabPane)
			.forEach(
				(item) =>
					(item.style.width =
						item.closest(selectors.tabContentWrap).getBoundingClientRect().width +
						'px'),
			);

		if (!window.profileTabslistenerAttached) {
			window.addEventListener('resize', (event) => {
				document.querySelectorAll(selectors.tabPane).forEach((item) => {
					const width =
						item.closest(selectors.tabContentWrap).getBoundingClientRect().width + 'px';
					item.style.width = width;
				});
				setTabContentOffset(`#${selectors.profileTabsId}`);
			});
			window.profileTabslistenerAttached = true;
		}

		window.dispatchEvent(new Event('resize'));
	};

	window.profileModal = { onProfileModalLoaded };

	// return {
	//     onProfileModalLoaded
	// }
})();

const fakeObject = {
	/* --------------------------- ОБНОВЛЕННОЕ cabinet -------------------------- */
	cabinet: {
		label: 'Профиль',
		className: 'btn-outline-success',
		callback: function () {
			setTimeout(function () {
				fetchGlobal(CABINET_SCRIPT, '', 12).then((dataCabinet) => {
					if (dataCabinet == '') var responseText = 'Ошибка';
					else var responseArr = JSON.parse(dataCabinet['message']);

					/* ------------------------------ PROFILE DATA ------------------------------ */
					const profileData = {
						name: responseArr.name ? responseArr.name : 'Nickname',
						common_id: responseArr.common_id, // id игрока
						imageUrl: responseArr.url, // url картинки
						imageTitle: responseArr.img_title, // альт картинки
						rating: responseArr.summary.rating ? responseArr.summary.rating : 0, // рейтинг
						placement: responseArr.summary.top, // место в рейтинге
						balance: 999, // баланс
						ratingByCoins: 33, // рейтинг по монетам
						tgWallet: '', // telegram wallet
						bonusAccrual: 124512, // начисление бонусов
						balanceSudoku: responseArr.summary.SUDOKU_BALANCE, // баланс SUDOKU
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
									<div class="pill-wrap"><span class="pill">${ref[1]}</span></div>
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
						return fetch('/profile-modal-tpl.html')
							.then((response) => response.text())
							.then((template) => {
								// Заменяем маркеры в шаблоне реальными данными
								let message = template
									.replace('{{MAX_FILE_SIZE}}', profileData.MAX_FILE_SIZE)
									.replace('{{cookie}}', profileData.cookie)
									.replace('{{common_id}}', profileData.common_id)
									.replace('{{name}}', profileData.name)
									.replace('{{imageUrl}}', profileData.imageUrl)
									.replace('{{imageTitle}}', profileData.imageTitle)
									.replace('{{rating}}', profileData.rating)
									.replace('{{placement}}', profileData.placement)
									.replace('{{balance}}', profileData.balance)
									.replace('{{ratingByCoins}}', profileData.ratingByCoins)
									.replace('{{tgWallet}}', profileData.tgWallet)
									.replace('{{bonusAccrual}}', profileData.bonusAccrual)
									.replace('{{bonusAccrual}}', profileData.bonusAccrual)
									.replace('{{balanceSudoku}}', profileData.balanceSudoku)
									.replace('{{referralList}}', referralList);

								return message;
							})
							.catch((error) =>
								console.error('Ошибка загрузки profile-modal:', error),
							);
					}
					/* ---------------------------- END PROFILE DATA ---------------------------- */

					getProfileModal(profileData).then((html) => {
						// document.getElementById('test-tpl').innerHTML = html;

						dialog = bootbox.alert({
							title: '',
							message: html,
							locale: 'ru',
							// size: 'large',
							className: 'modal-settings modal-profile',
							buttons: {
								ok: {
									label: 'Назад',
									className: 'btn-sm ml-auto mr-0',
								},
							},
							onShown: function (e) {
								profileModal.onProfileModalLoaded();
								// document.addEventListener("DOMContentLoaded", profileModal.onProfileModalLoaded);
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
	},
	/* --------------------------- END ОБНОВЛЕННОЕ cabinet -------------------------- */
};

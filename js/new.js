// const lang = 'ru';

// function getFAQModal(profileData) {

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
		};

		const date = new Date(date_achieved);
		let strDate =
			`0${date.getDate()}`.slice(-2) +
			'.' +
			`0${date.getMonth() + 1}`.slice(-2) +
			'.' +
			date.getFullYear();

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
						<p>Got reward</p>
						<div class="card_rewardInfo">
							<p><img class="card_plus" src="./images/plus.png" alt=""></p>
							<p><img class="card_rewardImage" src="./images/bigMoney.png" alt="money">
							</p>
							<span class="card_moneyCount">${reward}</span>
						</div>
					</div>
					<p class="card_passive">Your passive income</p>
					<div class="card_hour">
						<img class="card_hourImage" src="./images/smallMoney.png" alt="">
						<span>x${income}/hour</span>
					</div>

					<p class="card_effect">Effect lasts until beaten</p>
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
		if (you) {
			boxLabel = lang === 'ru' ? 'Вы' : 'You';
		} else {
			boxLabel = lang === 'ru' ? 'Игрок ' + index : 'Player ' + (index + 1);
		}

		let awards;
		if (top_bage_url) {
			let cards = achieves
				.slice(0, 2)
				.map((item) => {
					return CardCompact(item);
				})
				.join('');
			awards = `<div class="awards d-flex row-cols-3">
				<img src="${top_bage_url}" class="img-fluid rounded-1" alt="">
				${cards}
			</div>`;
		} else {
			let cards = achieves
				.slice(0, 3)
				.map((item) => {
					return CardCompact(item);
				})
				.join('');
			awards = `<div class="awards d-flex row-cols-3">${cards}</div>`;
		}

		return `
		
		<div class="box box-player">
			<div class="label box-heading text-center mx-auto fs-4">${boxLabel}</div>
			<div class="d-flex mb-2">
				<div class="nickname">${nickname}</div>
				<button class="btn btn-sm ml-auto">${lang === 'ru' ? 'Статистика' : 'Stats'}</button>
			</div>
			<div class="d-flex">
				<div class="img-col">
					<div class="img-wrap">
						<img src="${avatar_url}" class="img-fluid rounded" alt="avatar">
					</div>
				</div>
				<div class="info-col">
					<ul>
						<li>
							<div class="label">${lang === 'ru' ? 'Рейтинг' : 'Rating'}</div>
							<div class="pill">${rating}</div>
						</li>
						<li>
							<div class="label">${lang === 'ru' ? 'Позиция в ТОП' : 'Ranking number'}</div>
							<div class="pill">${rating_position}</div>
						</li>
						<li>
							<div class="label d-flex align-items-center">${
								lang === 'ru' ? 'Баланс' : 'Balance'
							} <i class="icon icon-coin ml-2"></i></div>
							<div class="pill-wrap d-flex ml-auto">
								<a href="#"><i class="icon icon-eye ${
									is_balance_hidden ? 'icon-eye--x' : ''
								} mx-2"></i></a><div class="pill">${balance}</div>
							</div>
						</li>
						<li>
							<div class="label">${lang === 'ru' ? 'Партии' : 'Games Played'}</div>
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
								<div class="box-heading text-center mx-auto fs-4">${lang === 'ru' ? 'Награда' : 'Reward'}</div>
							</div>
							<div class="box card-list-wrap">
								<div class="card_list">
									<div>${card}</div>
								</div>
							</div>`;

				// const m = $('.modal.show');
				// m.modal('hide');
				dialog = bootbox.alert({
					title: '',
					message: modalHtml,
					// locale: 'ru',
					// size: 'large',
					className: 'modal-settings modal-card modal--footer-compact',
					buttons: {
						ok: {
							label: lang === 'ru' ? 'Назад' : 'Back',
							className: 'btn btn-sm ml-auto mr-0',

						},
					},
					onShown: function (e) {},
					// callback: () => m.modal('show'),
					closeButton: false
				});
			}
		}
		// openModal(modal);
	};

	function init() {

		if (!window.cardCompactClickHandler) {
			window.cardCompactClickHandler = cardClickHandler;
			document.addEventListener('click', cardClickHandler);
		}

		const playerBoxes = json.map((element, i) => PlayerBox({ ...element, index: i })).join('');

		const html = `<div class="box d-flex align-items-center p-2 mb-2">
						<div class="box-heading text-center mx-auto fs-4">${lang === 'ru' ? 'Игроки' : 'Players'}</div>
					</div>
					${playerBoxes}`;

		// document.getElementById('test-tpl').innerHTML = q;

		return html;
	}



	return {
        buildHtml: init,
        // onLoad,
    };

}


/* -------------------------------------------------------------------------- */
/*                                      q                                     */
/* -------------------------------------------------------------------------- */

function getData() {
	// const url = 'https://эрудит.club/mvc/faq/getAll?lang=' + lang;
	const url = 'data.json';

	return fetch(url)
		.then((response) => {
			if (response.ok) {
				return response.json();
			} else {
				throw new Error('Ошибка при получении инструкций');
			}
		})
		.catch((error) => console.error('Ошибка загрузки instructions:', error));
}

document.addEventListener('DOMContentLoaded', () => {
	getData().then(json => {
		const html = PlayersPage(json).buildHtml();
		document.getElementById('test-tpl').innerHTML = html;
	});
})
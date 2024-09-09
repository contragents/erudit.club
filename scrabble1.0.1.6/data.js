chooseGame: {
	1: 'choosing',
	2: 'done',
	refresh: 1000000,
	message: '',
	noDialog: true,
	action: function (data) {
		let under1800 = 'Только для игроков с рейтингом 1800+';
		let noRatingPlayers = 'Недостаточно игроков с рейтингом 1900+ онлайн';
		let haveRatingPlayers = 'Выберите минимальный рейтинг соперников';
		let title = '';
		let onlinePlayers = '';
		let chooseDisabled = '';
		if ('players' in data && lang != 'EN') {
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
			let colPlayers = 'col-5';
			//screenOrient === HOR ? "col-3" : "col-4";

			let checked_0 = 'checked';
			let checked_1900 = '';
			let checked_2000 = '';
			let checked_2100 = '';
			let checked_2200 = '';
			let checked_2300 = '';
			let checked_2400 = '';
			let checked_2500 = '';
			let checked_2600 = '';
			let checked_2700 = '';

			if (
				'prefs' in data &&
				data['prefs'] !== false &&
				'from_rating' in data['prefs'] &&
				data['prefs']['from_rating'] > 0
			) {
				checked_0 = '';
				checked_1900 =
					data['prefs']['from_rating'] == 1900 ? 'checked' : '';
				checked_2000 =
					data['prefs']['from_rating'] == 2000 ? 'checked' : '';
				checked_2100 =
					data['prefs']['from_rating'] == 2100 ? 'checked' : '';
				checked_2200 =
					data['prefs']['from_rating'] == 2200 ? 'checked' : '';
				checked_2300 =
					data['prefs']['from_rating'] == 2300 ? 'checked' : '';
				checked_2400 =
					data['prefs']['from_rating'] == 2400 ? 'checked' : '';
				checked_2500 =
					data['prefs']['from_rating'] == 2500 ? 'checked' : '';
				checked_2600 =
					data['prefs']['from_rating'] == 2600 ? 'checked' : '';
				checked_2700 =
					data['prefs']['from_rating'] == 2700 ? 'checked' : '';
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
					extraClass = '',
					extraInputAttrString = '',
				} = props;

				const html = `
									<div title="${title}"
										class="form-check form-check-inline ${extraClass}">
										<input class="form-check-input" type="radio" id="${inputId}" name="from_rating"
										value="${inputValue}" 
										${isChecked ? `checked` : ''} 
										${isDisabled ? `disabled` : ''} 
										${extraInputAttrString ? extraInputAttrString : ''} 
											/>
										<label class="form-check-label" for="${inputId}">${text}</label>
									</div>`;

				return html;
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
							title: data['players'][ratingValue] + ' в игре',
							text: `OT ${ratingValue} (${data['players'][ratingValue]})`,
							inputValue: 0,
							inputId: `from_${ratingValue}`,
							isChecked,
							isDisabled: chooseDisabled.toString(),
						});
					}
				});

				return resultHtml;
			};

			onlinePlayers = `<div class="box-title-wrap">
												<span>Рейтинг соперника</span>
											</div>`;

			onlinePlayers += `<div class="row">
												<div class="col-6">`;
			onlinePlayers += ratingRadio({
				title: title,
				text: 'Любой (' + data['players'][0] + '&nbsp;онлайн)',
				inputValue: 0,
				inputId: 'from_0',
				isChecked: checked_0,
				isDisabled: chooseDisabled.toString(),
			});

			const ratings = Object.keys(data.players).filter(
				(item) => !isNaN(Number(item)) && data.players[item] > 0
			);
			// console.log(Object.keys(data.players), ratings);

			ratings.shift();
			onlinePlayers += getRatingList(
				ratings.slice(0, ratings.length / 2),
				data
			);

			onlinePlayers += `</div>`; // end col
			onlinePlayers += `	<div class="col-6">`;

			if (ratings.slice(ratings.length / 2).length > 0) {
				// console.log(ratings.slice(ratings.length / 2));
				onlinePlayers += getRatingList(
					ratings.slice(ratings.length / 2),
					data
				);
			}

			onlinePlayers += `	</div>`; // end col

			onlinePlayers += `</div>`; // end row

			onlinePlayers = `<div class="box box-rating">${onlinePlayers}</div>`;

			/* --------------------------------- END NEW -------------------------------- */
		} // end if 'players'

		let radioButtons =
			'<div style="display:none;" class="form-check form-check-inline"><input class="form-check-input" type="radio" id="twoonly" name="players_count" value="2" checked> <label class="form-check-label" for="twoonly">Только два игрока</label></div>';
		radioButtons +=
			'<div style="display:none;" class="form-check form-check-inline"><input class="form-check-input" type="radio" id="twomore" name="players_count" value="4"> <label class="form-check-label" for="twomore">До четырех игроков</label></div>';

		let wish = '';
		//'<br /><br /><h6>Желательно:</h6>';
		let colOchki = screenOrient === HOR ? 'col-5' : 'col-5';
		let colOchkiRow = screenOrient === HOR ? 'col-5' : 'col-5';

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
                                    <span>Игра до</span>
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
                                    <span>Время на ход</span>
                                </div>

                                <div class="label-row">

									<div class="form-check form-check-inline">

                                        <input class="form-check-input" type="radio" id="odna" name="turn_time"
                                            value="60" ${wish_60} />
                                        <label class="form-check-label text-accent" for="odna">1 минута</label>
                                    </div>

									<div class="time-img-wrap">
                                        <img src="./images/time.png" class="d-block img-fluid" alt="">
                                    </div>

                                    <div class="form-check form-check-inline">

                                        <input class="form-check-input" type="radio" id="dve" name="turn_time"
                                            value="120" ${wish_120} />
                                        <label class="form-check-label text-accent" for="dve">2 минуты</label>
                                    </div>
                                </div>
                            </div>
			`;

		let formHead = `<div class="box">
                            <div class="d-flex flex-row align-items-center">

                                <span>ПОДБОР ИГРЫ ПО ПАРАМЕТРАМ</span>

                                <div class="ml-auto"><a href="#" id="btn-faq class="btn">FAQ</a>
                                </div>
                            </div>
                        </div>
			`;

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

		dialog = bootbox.dialog({
			title: isVerstkaTestGlobal()
				? 'Тут title'
				: gameStates['chooseGame']['message'],
			message: isVerstkaTestGlobal() ? 'Тут message' : gameform,
			className: 'modal-settings',
			size: 'medium',
			onEscape: false,
			closeButton: false,
			buttons: {
				cabinet: {
					label: 'Профиль',
					className: 'btn-outline-success',
					callback: function () {
						setTimeout(function () {
							fetchGlobal(CABINET_SCRIPT, '', 12).then(
								(dataCabinet) => {
									if (dataCabinet == '')
										var responseText = 'Ошибка';
									else
										var responseArr = JSON.parse(
											dataCabinet['message']
										);
									var message = '<form id="superForm" >';
									for (k in responseArr['form']) {
										message +=
											'<div class="form-group"' +
											('type' in responseArr['form'][k] &&
											responseArr['form'][k]['type'] ===
												'hidden'
												? ' style="display:none" '
												: '') +
											'><div class="col-sm-6">' +
											'<label for="' +
											responseArr['form'][k]['inputId'] +
											'">' +
											responseArr['form'][k]['prompt'] +
											'</label>' +
											'</div>';
										message +=
											'<div class="form-row align-items-center">' +
											'<div class="col-sm-8">' +
											'<input ';

										if ('value' in responseArr['form'][k]) {
											message +=
												'value="' +
												responseArr['form'][k][
													'value'
												] +
												'"';
											if (
												'readonly' in
												responseArr['form'][k]
											) {
												message += ' readonly ';
											}
										} else {
											message +=
												'placeholder="' +
												responseArr['form'][k][
													'placeholder'
												] +
												'"';
										}

										message +=
											('type' in responseArr['form'][k]
												? 'type="' +
												  responseArr['form'][k][
														'type'
												  ] +
												  '"'
												: 'type="text"') +
											' class="form-control" name="' +
											responseArr['form'][k][
												'inputName'
											] +
											'" id="' +
											responseArr['form'][k]['inputId'] +
											'" ' +
											('required' in
											responseArr['form'][k]
												? ' required '
												: '') +
											'></div>';
										message += !(
											'type' in responseArr['form'][k] &&
											responseArr['form'][k]['type'] ===
												'hidden'
										)
											? '<div class="col-sm-4 col-form-label">' +
											  '<button type="submit" class="form-control btn btn-outline-secondary" onclick="' +
											  responseArr['form'][k][
													'onclick'
											  ] +
											  "($('#" +
											  responseArr['form'][k][
													'inputId'
											  ] +
											  "').val()," +
											  responseArr['common_id'] +
											  ');return false;">' +
											  responseArr['form'][k][
													'buttonCaption'
											  ] +
											  '</button></div>'
											: '' + '</div>';
										message += '</div>';
									}
									message += '</form>';
									dialog = bootbox.alert({
										title:
											'Ваш личный кабинет, <span id="playersNikname">' +
											responseArr['name'] +
											'</span>' +
											'<span id="playersAvatar">&nbsp;' +
											'<img style="cursor: pointer;" title="' +
											responseArr['img_title'] +
											'" src="' +
											responseArr['url'] +
											'" width="100px" max-height = "100px" />' +
											'</span>',
										message: responseArr['text'] + message,
										locale: 'ru',
										size: 'large',
										callback: function () {
											gameStates['chooseGame']['action'](
												data
											);
										},
									});
									return false;
								}
							);
						}, 100);
					},
				},
				// пока скроем через d-none
				instruction: {
					label: isVerstkaTestGlobal()
						? 'FAQ'
						: '&nbsp;Инструкция&nbsp;',
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
					// label: isVerstkaTestGlobal() ? 'Начать' : 'Начать игру!',
					label: 'Начать',
					className: 'btn-primary',
					callback: function () {
						activateFullScreenForMobiles();
						gameState = 'noGame';
						fetchGlobal(
							INIT_GAME_SCRIPT,
							'',
							$('.bootbox-body #myGameForm').serialize()
						).then((data) => {
							if (data == '') var responseText = 'Ошибка';
							else {
								commonCallback(data);
							}
						});

						return true;
					},
				},
				stats: {
					label: 'Статистика',
					className: 'btn-outline-success',
					callback: function () {
						dialog = bootbox
							.alert({
								message: getStatPageGlobal(),
								locale: 'ru',
							})
							.off('shown.bs.modal')
							.find('.modal-content')
							.css({
								'background-color':
									'rgba(230, 255, 230, 1)' /*, 'min-height' : '700px'*/,
							});

						return false;
					},
				},
				...(!isTgBot() && {
					telegram: {
						// label: isVerstkaTestGlobal() ? 'Играть в TG' : 'Перейти на Telegram',
						label: 'Играть в',
						className: 'btn-danger',
						callback: function () {
							document.location =
								'https://t.me/erudit_club_bot' +
								'/?start=' +
								(commonId && commonIdHash
									? commonId + '_' + commonIdHash
									: '');

							return false;
						},
					},
				}),
				...(isTgBot() && {
					invite: {
						label: 'Пригласить друга',
						className: 'btn-danger',
						callback: function () {
							// alert(webAppInitDataUnsafe.length + JSON.stringify(webAppInitDataUnsafe));

							shareTgGlobal();

							return false;
						},
					},
				}),
			},
		});
	},
};

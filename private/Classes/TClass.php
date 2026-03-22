<?php

class T
{
    const RU_LANG = 'RU';
    const EN_LANG = 'EN';
    const TR_LANG = 'TR';
    const FR_LANG = 'FR';
    const IT_LANG = 'IT';
    const DE_LANG = 'DE';
    const ES_LANG = 'ES';
    const PT_LANG = 'PT';
    const PT_BR_LANG = 'PT_BR';
    const ZH_CN_LANG = 'ZH_CN';
    const ZH_TW_LANG = 'ZH_TW';
    const KO_LANG = 'KO';
    const SUPPORTED_LANGS = [self::EN_LANG, self::RU_LANG];

    const PLURAL_PATTERN = '[[';

    public static string $lang = self::EN_LANG;

    public static function getInviteFriendPrompt(): string
    {
        return self::PHRASES['invite_friend_prompt'][self::$lang];
    }

    // Здесь задается язык и режим игры для папки запуска скрипта (и для игры scrabble, erudit)
    const GAME_MODE_LANG = [
        'scrabble' => self::EN_LANG,
        'yandex' => self::RU_LANG,
        'dev' => self::RU_LANG,
        'erudit' => self::RU_LANG,
        BaseModel::ALL_GAMES => self::RU_LANG,
    ];

    public static function setLangGame(string $lang, string $gameName)
    {
        self::$lang = $lang;
        Game::$gameName = $gameName;
    }

    public static function S($keyPhrase, ?array $params = null, ?string $forceLang = null): string
    {
        $lang = $forceLang ?? self::$lang;

        $res = self::PHRASES[$keyPhrase][self::$lang] ?? $keyPhrase;

        if (strpos($res, Macros::PATTERN) !== false) {
            $res = Macros::applyMacros($res);
        }

        if (strpos($res, self::PLURAL_PATTERN) !== false && $params) {
            $res = self::applyPlurals($res, $params, $lang);
        }

        return $res;
    }

    /**
     * @param string $stringPart
     * @param array $params
     * @param string $lang Язык указываем явно для комментов пользователям на разных языках
     * @return string
     */
    private static function applyPlurals(string $res, array $params, string $lang): string
    {
        $stringParts = explode('[[', $res);
        // Добавим коррекцию на номер параметра
        $firstParamOffset = $stringParts ? 1 : 0;
        foreach ($stringParts as $numOuter => &$stringPart) {
            preg_match_all('/^([a-zA-Z]+)]]/', $stringPart, $matches);

            try {
                foreach ($matches[1] as $nothing => $value) {
                    $num = $numOuter - $firstParamOffset;
                    if (is_callable([self::class, $value . 'Plural'])) {
                        $wordReplace = call_user_func([self::class, $value . 'Plural'], $params[$num]);
                    } else {
                        // Для разных языков и количества делаем отдельную поправку на расчетное количество
                        $numPlural = $lang === self::EN_LANG
                            ? $params[$num]
                            : (
                            ($params[$num] % 100) < 20
                                ? ($params[$num] % 20)
                                : ($params[$num] % 10)
                            );
                        for ($i = $numPlural; $i >= 0; $i--) {
                            if (isset(self::PLURALS[$value][$lang][$i])) {
                                $wordReplace = self::PLURALS[$value][$lang][$i];

                                break;
                            }
                        }
                    }

                    $stringPart = str_replace("$value]]", $wordReplace ?? $value, $stringPart);
                }
            } catch (\Throwable $e) {
                // $res = $e->__toString(); // todo log the error

                continue;
            }
        }

        return implode($stringParts);
    }

    public static function SA(array $keyPhraseArr): array
    {
        foreach ($keyPhraseArr as $key => $keyPhrase) {
            // исключаем зарезервированные ключи
            if (in_array($key, ['result'])) {
                continue;
            }

            $keyPhraseArr[$key] = is_array($keyPhrase)
                ? self::SA($keyPhrase)
                : self::S($keyPhrase);
        }

        return $keyPhraseArr;
    }

    const PHRASES = [
        'per day' => [self::RU_LANG => 'в день'],
        'Newest 10 referrals are shown' => [self::RU_LANG => 'Показаны только 10 последних рефералов'],
        'Total referrals count: [[value]]' => [self::RU_LANG => 'Всего ваших активных рефералов: [[value]]'],
        'Invite link for the Game' =>
            [self::RU_LANG => 'Ссылка приглашение в Эрудит.CLUB'],
        'Copy link to clipboard' => [self::RU_LANG => 'Скопировать ссылку в буфер обмена'],
        'Link copied to buffer' => [self::RU_LANG => 'Ссылка скопирована в буфер'],
        'Your link' => [self::RU_LANG => 'Ваша ссылка'],
        'Referral list' => [self::RU_LANG => 'Список рефералов'],
        'OR You could invite [[number]] more [[friend]] (referrals). Visit the Referrals tab for more information' => [
            self::RU_LANG => 'ИЛИ Вы могли бы пригласить еще [[number]] [[friend]] (рефералов). Перейдите во вкладку Рефералы для подробностей',
        ],
        'Invite [[number]] more [[friend]] (referrals) for next card level' => [
            self::RU_LANG => 'Для повышения уровня карточки спонсора пригласите еще [[number]] [[friend]] (рефералов)',
        ],
        'Invest additional [[number]] [[ruble]] in the project\'s coins to obtain/upgrade your card to the [[value]] level with an income of [[value]] [[coin]] per day' => [
            self::EN_LANG => 'Invest additional [[number]] [[ruble]] in the project\'s coins to obtain/upgrade your card to the [[value]] level with an income of [[value]] [[coin]] per day',
            self::RU_LANG => 'Вложите еще [[number]] [[ruble]] в монеты проекта, чтобы получить/обновить карточку до уровня [[value]] с доходом {{sudoku_icon_5}}[[value]] [[coin]] / день',
        ],
        'demo_expire_in_[[number]]_[[day]]' => [
            self::EN_LANG => 'This is a DEMO version of SUDOKU. The trial period will end in [[number]] [[day]].',
            self::RU_LANG => 'Это ДЕМО-версия СУДОКУ. Пробный период закончится через [[number]] [[day]]',
        ],
        'invite_link' => [
            self::EN_LANG => 'https://xn--d1aiwkc2d.club/scramble.html/?friend=',
            self::RU_LANG => 'https://эрудит.club/?friend='
        ],
        'Your invitation link has been copied to clipboard' =>
            [self::RU_LANG => 'Ваша ссылка-приглашение скопирована в буфер'],
        'System' => [self::RU_LANG => 'Система'],
        'Rating decrease' => [self::RU_LANG => 'Штраф за незаход'],
        'Agreement' => [
            self::RU_LANG => 'Оферта'
        ],
        'Слово не найдено.' => [
            self::EN_LANG => 'Word not found'
        ],
        'Success' => [
            self::RU_LANG => 'Успешно'
        ],
        'secret_prompt' => [
            self::EN_LANG => '&#42;Save this key for further account restoration in <a href="https://t.me/scrabble_online_bot">Telegram</a>',
            self::RU_LANG => '&#42;Сохраните ключ для восстановления аккаунта в <a href="https://t.me/erudit_club_bot">Telegram</a>'
        ],
        UserModel::BALANCE_HIDDEN_FIELD => [
            self::EN_LANG => 'User hidden',
            self::RU_LANG => 'Пользователь скрыт'
        ],
        PaymentModel::INIT_STATUS => [
            self::EN_LANG => 'Started',
            self::RU_LANG => 'Начата'
        ],
        PaymentModel::BAD_CONFIRM_STATUS => [
            self::EN_LANG => 'Bad confirmation',
            self::RU_LANG => 'Ошибка подтверждения'
        ],
        PaymentModel::COMPLETE_STATUS => [
            self::EN_LANG => 'Completed',
            self::RU_LANG => 'Исполнена'
        ],
        PaymentModel::FAIL_STATUS => [
            self::EN_LANG => 'Failed',
            self::RU_LANG => 'Ошибка'
        ],
        'Last transactions' => [
            self::RU_LANG => 'Последние операции'
        ],
        'Support in Telegram' => [
            self::RU_LANG => 'Техподдержка в Telegram'
        ],
        'Check_price' => [
            self::EN_LANG => 'Check price',
            self::RU_LANG => 'Узнать<br>цену'
        ],
        'Replenish' => [
            self::RU_LANG => 'Пополнить'
        ],
        'SUDOKU_amount' => [
            self::EN_LANG => 'Coin quantity',
            self::RU_LANG => 'Количество монет'
        ],
        'enter_amount' => [
            self::EN_LANG => 'amount',
            self::RU_LANG => ''
        ],
        'Buy_SUDOKU' => [
            self::EN_LANG => 'Buy SUDOKU coins',
            self::RU_LANG => 'Купить монеты' . '{{sudoku_icon_15}}'
        ],
        'The_price' => [
            self::EN_LANG => 'Price offer',
            self::RU_LANG => 'Стоимость монет'
        ],
        'calc_price' => [
            self::EN_LANG => 'price',
            self::RU_LANG => 'стоимость'
        ],
        'Pay' => [
            self::EN_LANG => 'Pay',
            self::RU_LANG => 'Оплатить'
        ],
        'Access denied' => [
            self::RU_LANG => 'Доступ запрещен'
        ],
        'Nothing to claim' => [
            self::RU_LANG => 'Забирать пока нечего'
        ],
        'connect_bot' => [
            self::EN_LANG => '{{yandex_exclude}}{{To access the full list, connect to our <a target="_blank" href="https://t.me/scrabble_online_bot">Telegram bot</a>}}',
            self::RU_LANG => '{{yandex_exclude}}{{Для доступа к полному списку подключитесь к нашему <a target="_blank" href="https://t.me/erudit_club_bot">Telegram-боту</a>}}'
        ],
        'Only 5 words are shown in random order' => [
            self::RU_LANG => 'Показаны только 5 слов в случайном порядке'
        ],
        'Congratulations to Player' => [
            self::RU_LANG => 'Поздравляем игрока'
        ],
        'Server sync lost' => [
            self::RU_LANG => 'Потеря синхронизации с сервером'
        ],
        'Server connecting error. Please try again' => [
            self::RU_LANG => 'Ошибка связи с сервером. Пожалуйста, повторите'
        ],
        'Error changing settings. Try again later' => [
            self::RU_LANG => 'Ошибка. Пожалуйста попробуйте позднее'
        ],
        'game_name' => [
            self::EN_LANG => 'Scramble',
            self::RU_LANG => 'Эрудит'
        ],
        'invite_friend_prompt' => [
            self::EN_LANG => 'Join the online game Scrabble on Telegram! Get the maximum rating, earn coins and withdraw tokens to your wallet',
            self::RU_LANG => 'Присоединяйся к онлайн игре Эрудит в Telegram! Набери максимальный рейтинг, зарабатывай монеты и выводи токены на кошелек'
        ],
        'game_bot_url' => [
            self::EN_LANG => 'https://t.me/scrabble_online_bot',
            self::RU_LANG => 'https://t.me/erudit_club_bot'
        ],
        'loading_text' => [
            self::EN_LANG => 'Game is loading...',
            self::RU_LANG => 'Загружаем игру...'
        ],
        'ground_file' => [
            self::EN_LANG => 'field_source_scrabble.svg',
            self::RU_LANG => 'field_source_nd_20.svg',//'field_source_nd_17.svg' //
        ],
        'switch_tg_button' => [
            self::EN_LANG => 'Switch to Telegram',
            self::RU_LANG => 'Перейти на Telegram'
        ],
        'Invite a friend' => [
            self::RU_LANG => 'Пригласить друга'
        ],
        'Sudoku with friends' => [
            self::RU_LANG => 'Судоку с друзьями'
        ],
        'you_lost' => [
            self::EN_LANG => 'You lost!',
            self::RU_LANG => 'Вы проиграли!'
        ],
        'you_won' => [
            self::EN_LANG => 'You won!',
            self::RU_LANG => 'Вы выиграли!'
        ],
        'start_new_game' => [
            self::EN_LANG => 'Start a new game',
            self::RU_LANG => 'Начните новую игру'
        ],
        'rating_changed' => [
            self::EN_LANG => 'Rating change: ',
            self::RU_LANG => 'Изменение рейтинга: '
        ],
        'Authorization error' => [
            self::RU_LANG => 'Ошибка авторизации'
        ],
        // Рекорды
        'Got reward' => [
            self::RU_LANG => 'Получена награда'
        ],
        'Your passive income' => [
            self::RU_LANG => 'Пассивный заработок'
        ],
        'will go to the winner' => [
            self::RU_LANG => 'достанется победителю'
        ],
        'Effect lasts until beaten' => [
            self::RU_LANG => 'Начисляется, пока не перебито'
        ],
        'Effect lasts forever' => [
            self::RU_LANG => 'Начисляется пожизненно'
        ],
        'per_hour' => [
            self::EN_LANG => 'hour',
            self::RU_LANG => 'час',
        ],
        'per_day' => [
            self::EN_LANG => 'day',
            self::RU_LANG => 'день',
        ],
        'rank position' => [
            self::RU_LANG => 'место в рейтинге'
        ],
        'record of the year' => [
            self::RU_LANG => 'рекорд года'
        ],
        'record of the month' => [
            self::RU_LANG => 'рекорд месяца'
        ],
        'record of the week' => [
            self::RU_LANG => 'рекорд недели'
        ],
        'record of the day' => [
            self::RU_LANG => 'рекорд дня'
        ],
        'patreon_level_year' => [
            self::EN_LANG => 'patron',
            self::RU_LANG => 'меценат'
        ],
        'patreon_level_month' => [
            self::EN_LANG => 'trustee',
            self::RU_LANG => 'попечитель'
        ],
        'patreon_level_week' => [
            self::EN_LANG => 'master',
            self::RU_LANG => 'магистр'
        ],
        'patreon_level_day' => [
            self::EN_LANG => 'partner',
            self::RU_LANG => 'партнер'
        ],
        AchievesModel::PATREON_TYPE => [
            self::EN_LANG => 'project sponsor',
            self::RU_LANG => 'спонсор проекта'
        ],
        AchievesModel::GAME_PRICE => [
            self::EN_LANG => 'game points',
            self::RU_LANG => 'очки за игру'
        ],
        AchievesModel::GAMES_PLAYED => [
            self::EN_LANG => 'games played',
            self::RU_LANG => 'сыграно партий'
        ],
        'Games Played' => [
            self::RU_LANG => 'Партии'
        ],
        'top' => [
            self::RU_LANG => 'топ'
        ],
        AchievesModel::TURN_PRICE => [
            self::EN_LANG => 'turn points',
            self::RU_LANG => 'очки за ход'
        ],
        AchievesModel::TVERD_NUM => [
            self::EN_LANG => 'выпало "Ъ" за игру',
            self::RU_LANG => 'выпало "Ъ" за игру'
        ],
        AchievesModel::WORD_PER_TURN => [
            self::EN_LANG => 'слов за ход',
            self::RU_LANG => 'слов за ход'
        ],
        AchievesModel::WORD_LEN => [
            self::EN_LANG => 'longest word',
            self::RU_LANG => 'самое длинное слово'
        ],
        AchievesModel::WORD_PRICE => [
            self::EN_LANG => 'word points',
            self::RU_LANG => 'очки за слово'
        ],
        'top_year' => [
            self::EN_LANG => 'TOP 1',
            self::RU_LANG => 'ТОП 1'
        ],
        'top_month' => [
            self::EN_LANG => 'TOP 2',
            self::RU_LANG => 'ТОП 2'
        ],
        'top_week' => [
            self::EN_LANG => 'TOP 3',
            self::RU_LANG => 'ТОП 3'
        ],
        'top_day' => [
            self::EN_LANG => 'BEST 10',
            self::RU_LANG => 'В десятке лучших'
        ],
        // Рекорды конец
        'Return to fullscreen mode?' => [
            self::RU_LANG => 'Вернуться в полноэкранный режим?'
        ],
        // Профиль игрока
        'Choose file' => [
            self::RU_LANG => 'Выберите файл'
        ],
        'Back' => [
            self::RU_LANG => 'Назад'
        ],
        'Wallet' => [
            self::RU_LANG => 'Кошелек'
        ],
        'Referrals' => [
            self::RU_LANG => 'Рефералы'
        ],
        'Player ID' => [
            self::RU_LANG => 'ID Игрока'
        ],
        "Player's ID" => [
            self::RU_LANG => 'Номер игрока'
        ],
        // complaints
        'Player is unbanned' => [
            self::RU_LANG => 'Игрок разблокирован'
        ],
        'Player`s ban not found' => [
            self::RU_LANG => 'Бан пользователя не найден'
        ],
        'Player not found' => [
            self::RU_LANG => 'Пользователь не найден'
        ],
        // end complaints
        'Save' => [
            self::RU_LANG => 'Сохранить'
        ],
        'new nickname' => [
            self::RU_LANG => 'новый Ник'
        ],
        'Input new nickname' => [
            self::RU_LANG => 'Задайте новый ник'
        ],
        'Your rank' => [
            self::RU_LANG => 'Ваш Рейтинг'
        ],
        'Ranking number' => [
            self::RU_LANG => 'Позиция в ТОП'
        ],
        'Balance' => [
            self::RU_LANG => 'Баланс'
        ],
        'Rating by coins' => [
            self::RU_LANG => 'Рейтинг по монетам'
        ],
        'Secret key' => [
            self::EN_LANG => 'Secret key&#42;',
            self::RU_LANG => 'Ключ&#42; восстановления'
        ],
        'Link' => [
            self::RU_LANG => 'Привязать'
        ],
        'Bonuses accrued' => [
            self::RU_LANG => 'Начислено бонусов'
        ],
        'SUDOKU Balance' => [
            self::RU_LANG => 'Баланс SUDOKU'
        ],
        'COIN Balance' => [
            self::RU_LANG => 'Баланс монет'
        ],
        'Claim' => [
            self::EN_LANG => 'Claim',
            self::RU_LANG => 'Забрать'
        ],
        'Name' => [
            self::RU_LANG => 'Имя'
        ],
        // Профиль игрока конец

        'Nickname updated' => [
            self::RU_LANG => 'Ник пользователя сохранен',
        ],
        'Error saving Nick change' => [
            self::RU_LANG => 'Ошибка сохранения Ника!',
        ],
        'Closed game window' => [
            self::EN_LANG => 'closed game window',
            self::RU_LANG => 'закрыл вкладку с игрой'
        ],
        "You closed the game window and became inactive!" => [
            self::RU_LANG => 'Вы закрыли вкладку с игрой и стали Неактивным!'
        ],
        "Request denied. Game is still ongoing" => [
            self::RU_LANG => 'Запрос отклонен. Игра еще продолжается'
        ],
        'Request rejected' => [
            self::RU_LANG => 'Запрос отклонен'
        ],
        'Enter the query: a-z, * - any letters, ? - ONE any letter' => [
            self::RU_LANG => 'Введите запрос: а-я, * - любые буквы, ? - ОДНА любая буква. Например, мам?, съе*'
        ],
        'No messages yet' => [
            self::RU_LANG => 'Сообщений пока нет'
        ],
        "New game request sent" => [
            self::RU_LANG => 'Запрос на новую игру отправлен'
        ],
        'Your new game request awaits players response' => [
            self::RU_LANG => 'Ваш запрос на новую игру ожидает ответа игроков'
        ],
        "Request was aproved! Starting new game" => [
            self::RU_LANG => 'Запрос принят! Начинаем новую игру'
        ],
        'Default avatar is used' => [
            self::RU_LANG => 'Используется аватар по умолчанию'
        ],
        "Avatar by provided link" => [
            self::RU_LANG => "Аватар по предоставленной ссылке"
        ],
        "Set" => [
            self::RU_LANG => 'Задать'
        ],
        "Avatar loading" => [
            self::RU_LANG => 'Загрузка Аватара'
        ],
        'Error! Choose image file with the size not more than' => [
            self::RU_LANG => 'Ошибка! Выберите файл-картинку размером не более'
        ],
        'Empty value is forbidden' => [
            self::RU_LANG => 'Задано пустое значение',
        ],
        'Send' => [
            self::RU_LANG => 'Отправить'
        ],
        'Avatar URL' => [
            self::RU_LANG => 'URL аватара'
        ],
        'Apply' => [
            self::RU_LANG => 'Применить'
        ],
        "Account key" => [
            self::RU_LANG => 'Ключ учетной записи'
        ],
        "Main account key" => [
            self::RU_LANG => 'Ключ основного аккаунта'
        ],
        "old account saved key" => [
            self::RU_LANG => 'сохраненный ключ от старого аккаунта'
        ],
        'Key transcription error' => [
            self::RU_LANG => 'Ошибка расшифровки ключа'
        ],
        "Player's ID NOT found by key" => [
            self::RU_LANG => 'ID игрока по ключу НЕ найден'
        ],
        'Accounts linked' => [
            self::RU_LANG => 'Учетные записи связаны'
        ],
        'Accounts are already linked' => [
            self::RU_LANG => 'Аккаунты уже связаны'
        ],
        'Game is not started' => [
            self::RU_LANG => 'Игра не начата'
        ],
        'Click to expand the image' => [
            self::RU_LANG => 'Кликните для увеличения изображения'
        ],
        'Report sent' => [
            self::RU_LANG => 'Отправлена жалоба'
        ],
        'Report declined! Please choose a player from the list' => [
            self::RU_LANG => 'Жалоба не принята! Пожалуйста, выберите игрока из списка'
        ],
        'Your report accepted and will be processed by moderator' => [
            self::RU_LANG => 'Ваше обращение принято и будет рассмотрено модератором'
        ],
        'If confirmed, the player will be banned' => [
            self::RU_LANG => 'В случае подтверждения к игроку будут применены санкции'
        ],
        'Report declined!' => [
            self::RU_LANG => 'Ваше обращение НЕ принято!'
        ],
        'Only one complaint per each player per day can be sent. Total 24 hours complaints limit is' => [
            self::RU_LANG => 'В течение суток можно отправлять только одну жалобу на одного и того же игрока. Всего за сутки не более'
        ],
        'From player' => [
            self::RU_LANG => 'От Игрока'
        ],
        'To Player' => [
            self::RU_LANG => 'Игроку'
        ],
        'Searching for players with selected rank' => [
            self::RU_LANG => 'Поиск игрока с указанным рейтингом'
        ],
        'Message NOT sent - BAN until ' => [
            self::RU_LANG => 'Сообщение НЕ отправлено - БАН до '
        ],
        'Message NOT sent - BAN from Player' => [
            self::RU_LANG => 'Сообщение НЕ отправлено - БАН от Игрока'
        ],
        'Message sent' => [
            self::RU_LANG => 'Сообщение отправлено'
        ],
        'Exit' => [
            self::RU_LANG => 'Выход'
        ],
        'Appeal' => [
            self::RU_LANG => 'Пожаловаться'
        ],
        'There are no events yet' => [
            self::RU_LANG => 'Событий пока нет'
        ],
        'Playing to' => [
            self::RU_LANG => 'Играем до'
        ],
        'Just two players' => [
            self::RU_LANG => 'Только два игрока'
        ],
        'Up to four players' => [
            self::RU_LANG => 'До четырех игроков'
        ],
        'Game selection - please wait' => [
            self::RU_LANG => 'Подбор игры - ожидайте'
        ],
        'Your turn!' => [
            self::RU_LANG => 'Ваш ход!'
        ],
        'Looking for a new game...' => [
            self::RU_LANG => 'Подбор игры!'
        ],
        'Get ready - your turn is next!' => [
            self::RU_LANG => 'Приготовьтесь - Ваш ход следующий!'
        ],
        'Take a break - your move in one' => [
            self::RU_LANG => 'Отдохните - Ваш ход через один'
        ],
        'Refuse' => [
            self::RU_LANG => 'Отказаться'
        ],
        'Offer a game' => [
            self::RU_LANG => 'Предложить игру'
        ],
        'Players ready:' => [
            self::RU_LANG => 'Готово играть:'
        ],
        'Players' => [
            self::RU_LANG => 'Игроки'
        ],
        'Try sending again' => [
            self::RU_LANG => 'Попробуйте отправить заново'
        ],
        'Error connecting to server!' => [
            self::RU_LANG => 'Ошибка связи с сервером!'
        ],
        'You haven`t composed a single word!' => [
            self::RU_LANG => 'Вы не составили ни одного слова!'
        ],
        'You will lose if you quit the game! CONTINUE?' => [
            self::RU_LANG => 'Вы проиграете, если выйдете из игры! ПРОДОЛЖИТЬ?'
        ],
        'Cancel' => [
            self::RU_LANG => 'Отмена'
        ],
        'Confirm' => [
            self::RU_LANG => 'Подтвердить'
        ],
        'Revenge!' => [
            self::RU_LANG => 'Реванш!'
        ],
        'Time elapsed:' => [
            self::RU_LANG => 'Время подбора:'
        ],
        'Time limit:' => [
            self::RU_LANG => 'Лимит по времени:'
        ],
        'You can start a new game if you wait for a long time' => [
            self::RU_LANG => 'Вы можете начать новую игру, если долго ждать..'
        ],
        'Close in 5 seconds' => [
            self::RU_LANG => 'Закрывать через 5 секунд'
        ],
        'Close immediately' => [
            self::RU_LANG => 'Закрывать сразу'
        ],
        'Will close automatically' => [
            self::RU_LANG => 'Закроется автоматически'
        ],
        's' => [
            self::RU_LANG => 'с'
        ],
        'Average waiting time:' => [
            self::RU_LANG => 'Среднее время ожидания:'
        ],
        'Waiting for other players' => [
            self::RU_LANG => 'Поиск других игроков'
        ],
        'Game goal:' => [
            self::RU_LANG => 'Игра до'
        ],
        'Game goal' => [
            self::RU_LANG => 'Игра до'
        ],
        'game_goal_descr' => [
            self::EN_LANG => '(will be applied when possible)',
            self::RU_LANG => '(учитывается голосованием игроков)'
        ],
        'turn_time_descr' => [
            self::EN_LANG => '(averaged out)',
            self::RU_LANG => '(выбирается среднее)'
        ],
        'Rating of opponents' => [
            self::RU_LANG => 'Рейтинг соперников'
        ],
        'new player' => [
            self::RU_LANG => 'новый игрок'
        ],
        'CHOOSE GAME OPTIONS' => [
            self::RU_LANG => 'ПОДБОР ИГРЫ ПО ПАРАМЕТРАМ'
        ],
        'Profile' => [
            self::RU_LANG => 'Профиль'
        ],
        'Error' => [
            self::RU_LANG => 'Ошибка'
        ],
        'Your profile' => [
            self::RU_LANG => 'Ваш профиль'
        ],
        'Start' => [
            self::RU_LANG => 'Начать'
        ],
        'Stats' => [
            self::RU_LANG => 'Статистика'
        ],
        'Play on' => [
            self::RU_LANG => 'Играть в'
        ],
        // Чат
        'You' => [
            self::RU_LANG => 'Вы'
        ],
        'to all: ' => [
            self::RU_LANG => 'всем: '
        ],
        ' (to all):' => [
            self::RU_LANG => ' (всем):'
        ],
        'For everyone' => [
            self::RU_LANG => 'Для всех'
        ],
        'Word matching' => [
            self::RU_LANG => 'Подбор слов'
        ],
        'Player support and chat at' => [
            self::RU_LANG => 'Поддержка и чат игроков в'
        ],
        'Join group' => [
            self::RU_LANG => 'Вступить в группу'
        ],
        'Word selection' => [
            self::RU_LANG => 'Подбор слов'
        ],
        'Send an in-game message' => [
            self::RU_LANG => 'Отправьте сообщение в игре'
        ],
        // Чат
        'News' => [
            self::RU_LANG => 'Новости:'
        ],
        // Окно статистика
        'Past Awards' => [
            self::RU_LANG => 'Прошлые награды'
        ],
        'Parties_Games' => [
            self::EN_LANG => 'Games',
            self::RU_LANG => 'Партии'
        ],
        'Player`s achievements' => [
            self::RU_LANG => 'Достижения игрока'
        ],
        'Player Awards' => [
            self::RU_LANG => 'Награды игрока'
        ],
        'Player' => [
            self::RU_LANG => 'Игрок'
        ],
        'VS' => [
            self::RU_LANG => 'Против'
        ],
        'Rating' => [
            self::RU_LANG => 'Рейтинг'
        ],
        'Opponent' => [
            self::RU_LANG => 'Соперник'
        ],
        'Active Awards' => [
            self::RU_LANG => 'Награды'
        ],
        'Remove filter' => [
            self::RU_LANG => 'Снять фильтр'
        ],
        // Окно статистика конец

        "Opponent's rating" => [
            self::RU_LANG => 'Рейтинг соперника'
        ],
        'Choose your MAX bet' => [
            self::RU_LANG => 'Выберите максимальную ставку'
        ],
        'Searching for players with corresponding bet' => [
            self::RU_LANG => 'Подбор игроков с соответствующей ставкой'
        ],
        'Coins written off the balance sheet' => [
            self::RU_LANG => 'Списано монет с баланса'
        ],
        'Number of coins on the line' => [
            self::RU_LANG => 'Количество монет на кону'
        ],
        'gets a win' => [
            self::RU_LANG => 'получает за победу'
        ],
        'The bank of' => [
            self::RU_LANG => 'Банк в размере'
        ],
        'goes to you' => [
            self::RU_LANG => 'достается вам'
        ],
        'is taken by the opponent' => [
            self::RU_LANG => 'забирает противник'
        ],
        'Bid' => [
            self::RU_LANG => 'Ставка'
        ],
        'No coins' => [
            self::RU_LANG => 'Без монет'
        ],
        'Any' => [
            self::RU_LANG => 'Любой'
        ],
        'online' => [
            self::RU_LANG => 'онлайн'
        ],
        'Above' => [
            self::RU_LANG => 'OT'
        ],
        'minutes' => [
            self::RU_LANG => 'минуты'
        ],
        'minute' => [
            self::RU_LANG => 'минута'
        ],
        'Select the minimum opponent rating' => [
            self::RU_LANG => 'Выберите минимальный рейтинг соперников'
        ],
        'Not enough 1900+ rated players online' => [
            self::RU_LANG => 'Недостаточно игроков с рейтингом 1900+ онлайн'
        ],
        'Only for players rated 1800+' => [
            self::RU_LANG => 'Только для игроков с рейтингом 1800+'
        ],
        'in game' => [
            self::RU_LANG => 'в игре'
        ],
        "score" => [
            self::RU_LANG => 'очков'
        ],
        'Your current rank' => [
            self::RU_LANG => 'Ваш текущий рейтинг'
        ],
        'Server syncing..' => [
            self::RU_LANG => 'Синхронизируемся с сервером..'
        ],
        ' is making a turn.' => [
            self::RU_LANG => ' ходит.'
        ],
        'Your turn is next - get ready!' => [
            self::RU_LANG => 'Ваш ход следующий - приготовьтесь!'
        ],
        'switches pieces and skips turn' => [
            self::RU_LANG => 'меняет фишки и пропускает ход'
        ],
        "Game still hasn't started!" => [
            self::RU_LANG => 'Игра еще не начата!'
        ],
        "Word wasn't found" => [
            self::RU_LANG => 'Слово не найдено'
        ],
        'Correct' => [
            self::RU_LANG => 'Корректно'
        ],
        'One-letter word' => [
            self::RU_LANG => 'Слово из одной буквы'
        ],
        'Repeat' => [
            self::RU_LANG => 'Повтор'
        ],
        'costs' => [
            self::RU_LANG => 'стоимость'
        ],
        '+15 for all pieces used' => [
            self::RU_LANG => '+15 за все фишки'
        ],
        'TOTAL' => [
            self::RU_LANG => 'ИТОГО'
        ],
        'You did not make any word' => [
            self::RU_LANG => 'Вы не составили ни одного слова'
        ],
        'is attempting to make a turn out of his turn (turn #' => [
            self::RU_LANG => 'пытается сделать ход не в свою очередь (ход #'
        ],
        'Data processing error!' => [
            self::RU_LANG => 'Ошибка обработки данных!'
        ],
        ' - turn processing error (turn #' => [
            self::RU_LANG => ' - ошибка обработки хода (ход #'
        ],
        "didn't make any word (turn #" => [
            self::RU_LANG => 'не составил ни одного слова (ход #'
        ],
        'set word lenght record for' => [
            self::RU_LANG => 'устанавливает рекорд по длине слова за'
        ],
        'set word cost record for' => [
            self::RU_LANG => 'устанавливает рекорд по стоимости слова за'
        ],
        'set record for turn cost for' => [
            self::RU_LANG => 'устанавливает рекорд по стоимости хода за'
        ],
        'gets' => [
            self::RU_LANG => 'зарабатывает'
        ],
        'for turn #' => [
            self::RU_LANG => 'за ход #'
        ],
        'For all pieces' => [
            self::RU_LANG => 'За все фишки'
        ],
        'Wins with score ' => [
            self::RU_LANG => 'Побеждает со счетом '
        ],
        'set record for gotten points in the game for' => [
            self::RU_LANG => "устанавливает рекорд набранных очков в игре за"
        ],
        'out of chips - end of game!' => [
            self::RU_LANG => 'закончились фишки - конец игры!'
        ],
        'set record for number of games played for' => [
            self::RU_LANG => 'устанавливает рекорд по числу сыгранных партий за'
        ],
        "is the only one left in the game - Victory!" => [
            self::RU_LANG => 'остался в игре один - Победа!'
        ],
        'left game' => [
            self::RU_LANG => 'покинул игру'
        ],
        "is the only one left in the game! Start a new game" => [
            self::RU_LANG => 'остался в игре один! Начните новую игру'
        ],
        "Time for the turn ran out" => [
            self::RU_LANG => 'Время хода истекло'
        ],
        "is left without any pieces! Winner - " => [
            self::RU_LANG => 'остался без фишек! Победитель - '
        ],
        " with score " => [
            self::RU_LANG => ' со счетом '
        ],
        "is left without any pieces! You won with score " => [
            self::RU_LANG => 'остался без фишек! Вы победили со счетом '
        ],
        "gave up! Winner - " => [
            self::RU_LANG => 'сдался'
        ],
        "skipped 3 turns! Winner - " => [
            self::RU_LANG => 'пропустил 3 хода! Победитель - '
        ],
        'New game has started!' => [
            self::RU_LANG => 'Новая игра начата!'
        ],
        'New game' => [
            self::RU_LANG => 'Новая игра'
        ],
        'Accept invitation' => [
            self::RU_LANG => 'Принять приглашение'
        ],
        'Get' => [
            self::RU_LANG => 'Набери'
        ],
        'score points' => [
            self::RU_LANG => 'очков'
        ],
        'Error sending message' => [
            self::RU_LANG => 'Ошибка отправки сообщения'
        ],
        "Asking for adversaries' approval." => [
            self::RU_LANG => "Запрашиваем подтверждение соперников."
        ],
        'Remaining in the game:' => [
            self::RU_LANG => 'В игре осталось:'
        ],
        "You got invited for a rematch! - Accept?" => [
            self::RU_LANG => 'Вас пригласили на Реванш - Согласны?'
        ],
        'All players have left the game' => [
            self::RU_LANG => 'Все игроки покинули игру'
        ],
        "Your score" => [
            self::RU_LANG => 'Ваши очки:'
        ],
        "Turn time" => [
            self::RU_LANG => "Время на ход"
        ],
        'Date' => [
            self::RU_LANG => 'Дата'
        ],
        'Price' => [
            self::RU_LANG => 'Сумма'
        ],
        'Status' => [
            self::RU_LANG => 'Статус'
        ],
        'Type' => [
            self::RU_LANG => 'Тип'
        ],
        'Period' => [
            self::RU_LANG => 'Период'
        ],
        'Word' => [
            self::RU_LANG => 'Слово'
        ],
        'Points/letters' => [
            self::RU_LANG => 'Очков/букв'
        ],
        'Result' => [
            self::RU_LANG => 'Результат'
        ],
        'Opponents' => [
            self::RU_LANG => 'Оппоненты'
        ],
        'Games<br>total' => [
            self::RU_LANG => 'Всего<br>партий',
        ],
        'Wins<br>total' => [
            self::RU_LANG => 'Всего<br>побед',
        ],
        'Gain/loss<br>in ranking' => [
            self::RU_LANG => 'Прибавка/потеря<br>в рейтинге',
        ],
        '% Wins' => [
            self::RU_LANG => '% Побед',
        ],
        'Games in total' => [
            self::RU_LANG => 'Всего партий'
        ],
        'Winnings count' => [
            self::RU_LANG => 'Всего побед'
        ],
        'Increase/loss in rating' => [
            self::RU_LANG => 'Прибавка/потеря в рейтинге'
        ],
        '% of wins' => [
            self::RU_LANG => '% побед'
        ],
        "GAME points - Year Record!" => [
            self::RU_LANG => 'Очки за ИГРУ - Рекорд Года!'
        ],
        "GAME points - Month Record!" => [
            self::RU_LANG => 'Очки за ИГРУ - Рекорд Месяца!'
        ],
        "GAME points - Week Record!" => [
            self::RU_LANG => 'Очки за ИГРУ - Рекорд Недели!'
        ],
        "GAME points - Day Record!" => [
            self::RU_LANG => 'Очки за ИГРУ - Рекорд Дня!'
        ],
        "TURN points - Year Record!" => [
            self::RU_LANG => 'Очки за ХОД - Рекорд Года!'
        ],
        "TURN points - Month Record!" => [
            self::RU_LANG => 'Очки за ХОД - Рекорд Месяца!'
        ],
        "TURN points - Week Record!" => [
            self::RU_LANG => 'Очки за ХОД - Рекорд Недели!'
        ],
        "TURN points - Day Record!" => [
            self::RU_LANG => 'Очки за ХОД - Рекорд Дня!'
        ],
        "WORD points - Year Record!" => [
            self::RU_LANG => 'Очки за СЛОВО - Рекорд Года!'
        ],
        "WORD points - Month Record!" => [
            self::RU_LANG => 'Очки за СЛОВО - Рекорд Месяца!'
        ],
        "WORD points - Week Record!" => [
            self::RU_LANG => 'Очки за СЛОВО - Рекорд Недели!'
        ],
        "WORD points - Day Record!" => [
            self::RU_LANG => 'Очки за СЛОВО - Рекорд Дня!'
        ],
        "Longest WORD - Year Record!" => [
            self::RU_LANG => 'Самое длинное СЛОВО - Рекорд Года!'
        ],
        "Longest WORD - Month Record!" => [
            self::RU_LANG => 'Самое длинное СЛОВО - Рекорд Месяца!'
        ],
        "Longest WORD - Week Record!" => [
            self::RU_LANG => 'Самое длинное СЛОВО - Рекорд Недели!'
        ],
        "Longest WORD - Day Record!" => [
            self::RU_LANG => 'Самое длинное СЛОВО - Рекорд Дня!'
        ],
        "GAMES played - Year Record!" => [
            self::RU_LANG => 'Сыграно ПАРТИЙ - Рекорд Года!'
        ],
        "GAMES played - Month Record!" => [
            self::RU_LANG => 'Сыграно ПАРТИЙ - Рекорд Месяца!'
        ],
        "GAMES played - Week Record!" => [
            self::RU_LANG => 'Сыграно ПАРТИЙ - Рекорд Недели!'
        ],
        "GAMES played - Day Record!" => [
            self::RU_LANG => 'Сыграно ПАРТИЙ - Рекорд Дня!'
        ],
        "Victory" => [
            self::RU_LANG => 'Победа'
        ],
        'Losing' => [
            self::RU_LANG => 'Проигрыш'
        ],
        "Go to player's stats" => [
            self::RU_LANG => 'Перейти к статистике игрока'
        ],
        'Filter by player' => [
            self::RU_LANG => 'Фильтровать по игроку'
        ],
        'Apply filter' => [
            self::RU_LANG => 'Фильтровать'
        ],
        'against' => [
            self::RU_LANG => 'против игрока'
        ],
        "File loading error!" => [
            self::RU_LANG => 'Ошибка загрузки файла!'
        ],
        "Check:" => [
            self::RU_LANG => 'Проверьте:'
        ],
        "file size (less than " => [
            self::RU_LANG => 'размер файла (не более '
        ],
        "resolution - " => [
            self::RU_LANG => 'разрешение - '
        ],
        "Incorrect URL format!" => [
            self::RU_LANG => 'Неверный формат URL!'
        ],
        "Must begin with " => [
            self::RU_LANG => 'Должно начинаться с '
        ],
        "Avatar updated" => [
            self::RU_LANG => 'Аватар обновлен'
        ],
        "Error saving new URL" => [
            self::RU_LANG => 'Ошибка сохранения нового URL'
        ],
        'faq_rules' => [
            self::EN_LANG => Faq::RULES[self::EN_LANG],
            self::RU_LANG => Faq::RULES[self::RU_LANG],
        ],
        'faq_rating' => [
            self::EN_LANG => Faq::RATING[self::EN_LANG],
            self::RU_LANG => Faq::RATING[self::RU_LANG],
        ],
        'faq_rewards' => [
            self::EN_LANG => Faq::REWARDS[self::EN_LANG],
            self::RU_LANG => Faq::REWARDS[self::RU_LANG],
        ],
        'Reward' => [
            self::RU_LANG => 'Награда'
        ],
        'faq_coins' => [
            self::EN_LANG => Faq::COINS[self::EN_LANG],
            self::RU_LANG => Faq::COINS[self::RU_LANG],
        ],
    ];

    public static function translit(string $text, bool $fromRU = true)
    {
        $cyr = self::CYR;
        $lat = [
            'a',
            'b',
            'v',
            'g',
            'd',
            'e',
            'io',
            'zh',
            'z',
            'i',
            'y',
            'k',
            'l',
            'm',
            'n',
            'o',
            'p',
            'r',
            's',
            't',
            'u',
            'f',
            'h',
            'ts',
            'ch',
            'sh',
            'sht',
            'a',
            'i',
            'y',
            'e',
            'yu',
            'ya',
            'A',
            'B',
            'V',
            'G',
            'D',
            'E',
            'Io',
            'Zh',
            'Z',
            'I',
            'Y',
            'K',
            'L',
            'M',
            'N',
            'O',
            'P',
            'R',
            'S',
            'T',
            'U',
            'F',
            'H',
            'Ts',
            'Ch',
            'Sh',
            'Sht',
            'A',
            'I',
            'Y',
            'e',
            'Yu',
            'Ya'
        ];
        if ($fromRU) {
            $text = str_replace($cyr, $lat, $text);
        }

        return $text;
    }

    const CYR = [
        'а',
        'б',
        'в',
        'г',
        'д',
        'е',
        'ё',
        'ж',
        'з',
        'и',
        'й',
        'к',
        'л',
        'м',
        'н',
        'о',
        'п',
        'р',
        'с',
        'т',
        'у',
        'ф',
        'х',
        'ц',
        'ч',
        'ш',
        'щ',
        'ъ',
        'ы',
        'ь',
        'э',
        'ю',
        'я',
        'А',
        'Б',
        'В',
        'Г',
        'Д',
        'Е',
        'Ё',
        'Ж',
        'З',
        'И',
        'Й',
        'К',
        'Л',
        'М',
        'Н',
        'О',
        'П',
        'Р',
        'С',
        'Т',
        'У',
        'Ф',
        'Х',
        'Ц',
        'Ч',
        'Ш',
        'Щ',
        'Ъ',
        'Ы',
        'Ь',
        'Э',
        'Ю',
        'Я'
    ];

    const PLURALS = [
        'day' => [
            self::EN_LANG => [0 => 'days', 1 => 'day', 2 => 'days'],
            self::RU_LANG => [0 => 'дней', 1 => 'день', 2 => 'дня', 5 => 'дней'],
            self::TR_LANG => [0 => 'gün', 1 => 'gün', 2 => 'gün'],
            self::FR_LANG => [0 => 'jours', 1 => 'jour', 2 => 'jours'],
            self::IT_LANG => [0 => 'giorni', 1 => 'giorno', 2 => 'giorni'],
            self::DE_LANG => [0 => 'Tage', 1 => 'Tag', 2 => 'Tage'],
            self::ES_LANG => [0 => 'días', 1 => 'día', 2 => 'días'],
            self::PT_LANG => [0 => 'dias', 1 => 'dia', 2 => 'dias'],
            self::PT_BR_LANG => [0 => 'dias', 1 => 'dia', 2 => 'dias'],
            self::ZH_CN_LANG => [0 => '天', 1 => '天', 2 => '天'],
            self::ZH_TW_LANG => [0 => '天', 1 => '天', 2 => '天'],
            self::KO_LANG => [0 => '일', 1 => '일', 2 => '일'],
        ],
        'point' => [
            self::EN_LANG => [0 => 'points', 1 => 'point', 2 => 'points'],
            self::RU_LANG => [0 => 'очков', 1 => 'очко', 2 => 'очка', 5 => 'очков'],
            self::TR_LANG => [0 => 'puan', 1 => 'puan', 2 => 'puan'],
            self::FR_LANG => [0 => 'points', 1 => 'point', 2 => 'points'],
            self::IT_LANG => [0 => 'punti', 1 => 'punto', 2 => 'punti'],
            self::DE_LANG => [0 => 'Punkte', 1 => 'Punkt', 2 => 'Punkte'],
            self::ES_LANG => [0 => 'puntos', 1 => 'punto', 2 => 'puntos'],
            self::PT_LANG => [0 => 'pontos', 1 => 'ponto', 2 => 'pontos'],
            self::PT_BR_LANG => [0 => 'pontos', 1 => 'ponto', 2 => 'pontos'],
            self::ZH_CN_LANG => [0 => '积分', 1 => '点', 2 => '点数'],
            self::ZH_TW_LANG => [0 => '點數', 1 => '點', 2 => '點數'],
            self::KO_LANG => [0 => '점', 1 => '점', 2 => '점'],
        ],
        'cell' => [
            self::EN_LANG => [0 => 'cells', 1 => 'cell', 2 => 'cells'],
            self::RU_LANG => [0 => 'клеток', 1 => 'клетку', 2 => 'клетки', 5 => 'клеток'],
            self::FR_LANG => [0 => 'cellules', 1 => 'cellule', 2 => 'cellules'],
            self::IT_LANG => [0 => 'celle', 1 => 'cella', 2 => 'celle'],
            self::DE_LANG => [0 => 'Zellen', 1 => 'Zelle', 2 => 'Zellen'],
            self::ES_LANG => [0 => 'celdas', 1 => 'celda', 2 => 'celdas'],
            self::PT_LANG => [0 => 'células', 1 => 'célula', 2 => 'células'],
            self::PT_BR_LANG => [0 => 'células', 1 => 'célula', 2 => 'células'],
            self::ZH_CN_LANG => [0 => '个牢房', 1 => '个单元', 2 => '个牢房'],
            self::ZH_TW_LANG => [0 => '細胞', 1 => '細胞', 2 => '細胞'],
            self::KO_LANG => [0 => '세포', 1 => '셀', 2 => '개 셀'],
        ],
        'key' => [
            self::EN_LANG => [0 => 'keys', 1 => 'key', 2 => 'keys'],
            self::RU_LANG => [0 => 'ключей', 1 => 'ключ', 2 => 'ключа', 5 => 'ключей'],
            self::FR_LANG => [0 => 'clés', 1 => 'clé', 2 => 'clés'],
            self::IT_LANG => [0 => 'chiavi', 1 => 'chiave', 2 => 'chiavi'],
            self::DE_LANG => [0 => 'Schlüssel', 1 => 'Schlüssel', 2 => 'Schlüssel'],
            self::ES_LANG => [0 => 'llaves', 1 => 'llave', 2 => 'llaves'],
            self::PT_LANG => [0 => 'chaves', 1 => 'chave', 2 => 'chaves'],
            self::PT_BR_LANG => [0 => 'chaves', 1 => 'chave', 2 => 'chaves'],
            self::ZH_CN_LANG => [0 => '钥匙', 1 => '把钥匙', 2 => '把钥匙'],
            self::ZH_TW_LANG => [0 => '鑰匙', 1 => '鑰匙', 2 => '鑰匙'],
            self::KO_LANG => [0 => '키', 1 => '개 키', 2 => '개의 키'],
        ],
        'Player' => [
            self::EN_LANG => [1 => 'Player1', 2 => 'Player2', 3 => 'Player3', 4 => 'Player4'],
            self::RU_LANG => [1 => 'Игрок1', 2 => 'Игрок2', 3 => 'Игрок3', 4 => 'Игрок4'],
            self::TR_LANG => [1 => 'Oyuncu1', 2 => 'Oyuncu2', 3 => 'Oyuncu3', 4 => 'Oyuncu1'],
            self::FR_LANG => [1 => 'Joueur1', 2 => 'Joueur2', 3 => 'Joueur3', 4 => 'Joueur4'],
            self::IT_LANG => [1 => 'Giocatore1', 2 => 'Giocatore2', 3 => 'Giocatore3', 4 => 'Giocatore4'],
            self::DE_LANG => [1 => 'Spieler1', 2 => 'Spieler2', 3 => 'Spieler3', 4 => 'Spieler4'],
            self::ES_LANG => [1 => 'Jugador1', 2 => 'Jugador2', 3 => 'Jugador3', 4 => 'Jugador4'],
            self::PT_LANG => [1 => 'Jogador1', 2 => 'Jogador2', 3 => 'Jogador3', 4 => 'Jogador4'],
            self::PT_BR_LANG => [1 => 'Jogador1', 2 => 'Jogador2', 3 => 'Jogador3', 4 => 'Jogador4'],
            self::ZH_CN_LANG => [1 => '玩家1', 2 => '玩家2', 3 => '玩家3', 4 => '玩家4'],
            self::ZH_TW_LANG => [1 => '玩家1', 2 => '玩家2', 3 => '玩家3', 4 => '玩家4'],
            self::KO_LANG => [1 => '플레이어1', 2 => '플레이어2', 3 => '플레이어3', 4 => '플레이어4'],
        ],
        'ruble' => [
            self::EN_LANG => [0 => 'rubles', 1 => 'ruble', 2 => 'rubles'],
            self::RU_LANG => [0 => 'рублей', 1 => 'рубль', 2 => 'рубля', 5 => 'рублей'],
        ],
        'coin' => [
            self::EN_LANG => [0 => 'coins', 1 => 'coin', 2 => 'coins'],
            self::RU_LANG => [0 => 'монет', 1 => 'монета', 2 => 'монеты', 5 => 'монет'],
        ],
        'friend' => [
            self::EN_LANG => [0 => 'friends', 1 => 'friend', 2 => 'friends'],
            self::RU_LANG => [0 => 'друзей', 1 => 'друга', 2 => 'друга', 5 => 'друзей'],
        ],
    ];

    public static function upperFirst(?string $str = null): string
    {
        if (!$str) {
            return '';
        }

        $fc = mb_strtoupper(mb_substr($str, 0, 1));

        return $fc . mb_substr($str, 1);
    }

    protected static function numberPlural(int $number): string
    {
        return (string)$number;
    }

    protected static function valuePlural($value): string
    {
        return (string)$value;
    }
}
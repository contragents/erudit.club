<?php

class T
{
    const RU_LANG = 'RU';
    const EN_LANG = 'EN';

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
    ];

    public static function setLangGame(string $lang, string $gameName) {
        self::$lang = $lang;
        Game::$gameName = $gameName;
    }

    public static function S($keyPhrase): string
    {
        $res = self::PHRASES[$keyPhrase][self::$lang] ?? $keyPhrase;

        if (strpos($res, Macros::PATTERN)) {
            return Macros::applyMacros($res);
        }

        return self::PHRASES[$keyPhrase][self::$lang] ?? $keyPhrase;
    }

    const PHRASES = [
        'Error changing settings. Try again later' => [
            self::RU_LANG => 'Ошибка. Пожалуйста попробуйте позднее'
        ],
        'game_name' => [
            self::EN_LANG => 'Scrabble',
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
            self::EN_LANG => 'Scrabble is loading...',
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
        'Got reward'  => [
            self::RU_LANG => 'Получена награда'
        ],
        'Your passive income' => [
            self::RU_LANG => 'Пассивный заработок'
        ],
        'Effect lasts until beaten' => [
            self::RU_LANG => 'Начисляется, пока не перебито'
        ],
        'per_hour' => [
            self::EN_LANG => 'hour',
            self::RU_LANG => 'час',
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
        'game_price' => [
            self::EN_LANG => 'game points',
            self::RU_LANG => 'очки за игру'
        ],
        'games_played' => [
            self::EN_LANG => 'games played',
            self::RU_LANG => 'сыграно партий'
        ],
        'top' => [
            self::RU_LANG => 'топ'
        ],
        'turn_price' => [
            self::EN_LANG => 'turn points',
            self::RU_LANG => 'очки за ход'
        ],
        'word_len' => [
            self::EN_LANG => 'word length',
            self::RU_LANG => 'длинное слово'
        ],
        'word_price' => [
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
        'Save' => [
            self::RU_LANG => 'Сохранить'
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
        'Link' => [
            self::RU_LANG => 'Привязать'
        ],
        'Bonuses accrued' => [
            self::RU_LANG => 'Начислено бонусов'
        ],
        'SUDOKU Balance' => [
            self::RU_LANG => 'Баланс SUDOKU'
        ],
        'Claim' => [
            self::EN_LANG => 'Claim <br>(soon)',
            self::RU_LANG => 'Забрать<br>(скоро)'
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
        'Lost server synchronization' => [
            'Потеря синхронизации с сервером',
        ],
        "Closed game window" => [
            self::RU_LANG => "Закрыл вкладку с игрой"
        ],
        "You closed the game window and became inactive!" => [
            self::RU_LANG => 'Вы закрыли вкладку с игрой и стали Неактивным!'
        ],
        "Request denied. Game is still ongoing." => [
            self::RU_LANG => 'Запрос отклонен. Игра еще продолжается.'
        ],
        "New game request sent" => [
            self::RU_LANG => 'Запрос на новую игру отправлен'
        ],
        "Your new game request awaits players' response" => [
            self::RU_LANG => 'Ваш запрос на новую игру ожидает ответа игроков'
        ],
        "Request was aproved! Starting new game" => [
            self::RU_LANG => 'Запрос принят! Начинаем новую игру'
        ],
        "Default avatar is used" => [
            self::RU_LANG => "Используется аватар по умолчанию"
        ],
        "Avatar by provided link" => [
            self::RU_LANG => "Аватар по предоставленной ссылке"
        ],
        "Set" => [
            self::RU_LANG => 'Задать'
        ],
        "Loading Avatar" => [
            self::RU_LANG => 'Загрузка Аватара'
        ],
        "Send" => [
            self::RU_LANG => 'Отправить'
        ],
        "Avatar's URL" => [
            self::RU_LANG => 'URL аватара'
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
        "Key transcription error" => [
            self::RU_LANG => 'Ошибка расшифровки ключа'
        ],
        "Player's ID NOT found by key" => [
            self::RU_LANG => 'ID игрока по ключу НЕ найден'
        ],
        "Accounts linked" => [
            self::RU_LANG => 'Учетные записи связаны'
        ],
        "Link error - accounts are already linked" => [
            self::RU_LANG => 'Ошибка привязки - аккаунты уже связаны'
        ],
        "Game is not started" => [
            self::RU_LANG => "Игра не начата"
        ],
        "Click to expand the image" => [
            self::RU_LANG => "Кликните для увеличения изображения"
        ],
        "Report sent" => [
            self::RU_LANG => 'Отправлена жалоба'
        ],
        "Report declined! Please choose a player from the list" => [
            self::RU_LANG => 'Жалоба не принята! Пожалуйста, выберите игрока из списка'
        ],
        "From player" => [
            self::RU_LANG => "От Игрока"
        ],
        "(to everyone):" => [
            self::RU_LANG => " (всем):"
        ],
        "Message NOT sent - BAN until " => [
            self::RU_LANG => 'Сообщение НЕ отправлено - БАН до '
        ],
        "Message NOT sent - BAN from Player" => [
            self::RU_LANG => 'Сообщение НЕ отправлено - БАН от Игрока'
        ],
        "Message sent" => [
            self::RU_LANG => 'Сообщение отправлено'
        ],
        "Your turn!" => [
            self::RU_LANG => 'Ваш ход!'
        ],
        'Game goal' => [
            self::RU_LANG => 'Игра до'
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

        // Окно статистика
        'Past Awards' => [
            self::RU_LANG => 'Прошлые награды'
        ],
        'Parties_Games' => [
            self::EN_LANG => 'Games',
            self::RU_LANG => 'Партии'
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
        "Your current rank - " => [
            self::RU_LANG => 'Ваш текущий рейтинг - '
        ],
        " making a turn." => [
            self::RU_LANG => ' ходит.'
        ],
        "Your turn is next - get ready!" => [
            self::RU_LANG => 'Ваш ход следующий - приготовьтесь!'
        ],
        "switches pieces and skips turn" => [
            self::RU_LANG => 'меняет фишки и пропускает ход'
        ],
        "Game still hasn't started!" => [
            self::RU_LANG => 'Игра еще не начата!'
        ],
        "Word wasn't found" => [
            self::RU_LANG => 'Слово не найдено'
        ],
        "Correct" => [
            self::RU_LANG => 'Корректно'
        ],
        "One-letter word" => [
            self::RU_LANG => 'Слово из одной буквы'
        ],
        "Repeat" => [
            self::RU_LANG => 'Повтор'
        ],
        "+15 for all pieces used " => [
            self::RU_LANG => '+15 за все фишки '
        ],
        "You did not make any word" => [
            self::RU_LANG => 'Вы не составили ни одного слова'
        ],
        "is attempting to make a turn out of his turn (turn #" => [
            self::RU_LANG => 'пытается сделать ход не в свою очередь (ход #'
        ],
        "Data processing error!" => [
            self::RU_LANG => 'Ошибка обработки данных!'
        ],
        "Turn processing error (turn #" => [
            self::RU_LANG => ' - ошибка обработки хода (ход #'
        ],
        "didn't make any word (turn #" => [
            self::RU_LANG => 'не составил ни одного слова (ход #'
        ],
        "set word lenght record for " => [
            self::RU_LANG => "устанавливает рекорд по длине слова за "
        ],
        "set word cost record for " => [
            self::RU_LANG => "устанавливает рекорд по стоимости слова за "
        ],
        "set record for turn cost for " => [
            self::RU_LANG => "устанавливает рекорд по стоимости хода за "
        ],
        "gets " => [
            self::RU_LANG => 'зарабатывает '
        ],
        " for turn #" => [
            self::RU_LANG => ' за ход #'
        ],
        "For all pieces" => [
            self::RU_LANG => "За все фишки"
        ],
        "Wins with score " => [
            self::RU_LANG => 'Побеждает со счетом '
        ],
        "set record for gotten points in the game for " => [
            self::RU_LANG => "устанавливает рекорд набранных очков в игре за "
        ],
        "pieces are finished - game ended!" => [
            self::RU_LANG => 'закончились фишки - конец игры!'
        ],
        "set record for number of games played for " => [
            self::RU_LANG => "устанавливает рекорд по числу сыгранных партий за "
        ],
        "is the only one left in the game - Victory!" => [
            self::RU_LANG => 'остался в игре один - Победа!'
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
        "gave up" => [
            self::RU_LANG => 'сдался'
        ],
        "skipped 3 turns! Winner - " => [
            self::RU_LANG => 'пропустил 3 хода! Победитель - '
        ],
        "New game has started!" => [
            self::RU_LANG => 'Новая игра начата!'
        ],
        "Get" => [
            self::RU_LANG => 'Набери'
        ],
        "score points" => [
            self::RU_LANG => 'очков'
        ],

        "Запрашиваем подтверждение соперников." => [
            self::RU_LANG => "Asking for adversaries' approval."
        ],
        "Left in game:" => [
            self::RU_LANG => 'В игре осталось:'
        ],
        "You got invited for a rematch! - Accept?" => [
            self::RU_LANG => 'Вас пригласили на Реванш - Согласны?'
        ],
        "Your score" => [
            self::RU_LANG => 'Ваши очки:'
        ],
        "Turn time" => [
            self::RU_LANG => "Время на ход"
        ],
        "Player's ID" => [
            self::RU_LANG => 'ID игрока'
        ],
        "Date" => [
            self::RU_LANG => 'Дата'
        ],
        "Result" => [
            self::RU_LANG => 'Результат'
        ],
        "Games in total" => [
            self::RU_LANG => 'Всего партий'
        ],
        "Increase/decrease in rank" => [
            self::RU_LANG => 'Прибавка/потеря в рейтинге'
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
        "Filter by player" => [
            self::RU_LANG => 'Фильтровать по игроку'
        ],
        "Remove the filter" => [
            self::RU_LANG => 'Снять фильтр'
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
        'faq_coins' => [
            self::EN_LANG => Faq::COINS[self::EN_LANG],
            self::RU_LANG => Faq::COINS[self::RU_LANG],
            ],
    ];

    public static function translit(string $text, bool $fromRU = true)
    {
        $cyr = [
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
}
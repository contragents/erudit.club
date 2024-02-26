<?php

use Dadata\Players;
use Erudit\Game;

class AchievesModel extends BaseModel
{
    const TABLE_NAME = 'achieves';

    const LIMIT = 10;

    const ID_FIELD = 'id_achieve';
    const COMMON_ID_FIELD = 'common_id';
    const DATE_ACHIEVED_FIELD = 'date_achieved';
    const EVENT_TYPE_FIELD = 'event_type';
    const EVENT_PERIOD_FIELD = 'event_period';
    const WORD_FIELD = 'word';
    const EVENT_VALUE_FIELD = 'event_value';

    const FIELDS = [
        self::ID_FIELD => self::TYPE_INT,
        self::COMMON_ID_FIELD => self::TYPE_INT,
        self::DATE_ACHIEVED_FIELD => self::TYPE_DATE,
        self::EVENT_TYPE_FIELD => self::TYPE_STRING,
        self::EVENT_PERIOD_FIELD => self::TYPE_STRING,
        self::WORD_FIELD => self::TYPE_STRING,
        self::EVENT_VALUE_FIELD => self::TYPE_INT,
    ];

    const ATTRIBUTE_LABELS = [
        self::COMMON_ID_FIELD => 'ID игрока',
        self::DATE_ACHIEVED_FIELD => 'Дата',
        self::EVENT_TYPE_FIELD => 'Тип',
        self::EVENT_PERIOD_FIELD => 'Период',
        self::WORD_FIELD => 'Слово',
        self::EVENT_VALUE_FIELD => 'Очков/букв',
        self::GAME_DATE_FIELD => 'Дата',
        self::YOUR_RESULT => 'Результат',
        self::OPPONENT_COMMON_ID => 'Оппоненты',
        self::YOUR_RATING_PROGRESS => 'Рейтинг',
    ];

    public const PRIZE_TITLES = [
        'game_price-year' => 'Очки за ИГРУ - Рекорд Года!',
        'game_price-month' => 'Очки за ИГРУ - Рекорд Месяца!',
        'game_price-week' => 'Очки за ИГРУ - Рекорд Недели!',
        'game_price-day' => 'Очки за ИГРУ - Рекорд Дня!',

        'turn_price-year' => 'Очки за ХОД - Рекорд Года!',
        'turn_price-month' => 'Очки за ХОД - Рекорд Месяца!',
        'turn_price-week' => 'Очки за ХОД - Рекорд Недели!',
        'turn_price-day' => 'Очки за ХОД - Рекорд Дня!',

        'word_price-year' => 'Очки за СЛОВО - Рекорд Года!',
        'word_price-month' => 'Очки за СЛОВО - Рекорд Месяца!',
        'word_price-week' => 'Очки за СЛОВО - Рекорд Недели!',
        'word_price-day' => 'Очки за СЛОВО - Рекорд Дня!',

        'word_len-year' => 'Самое длинное СЛОВО - Рекорд Года!',
        'word_len-month' => 'Самое длинное СЛОВО - Рекорд Месяца!',
        'word_len-week' => 'Самое длинное СЛОВО - Рекорд Недели!',
        'word_len-day' => 'Самое длинное СЛОВО - Рекорд Дня!',

        'games_played-year' => 'Сыграно ПАРТИЙ - Рекорд Года!',
        'games_played-month' => 'Сыграно ПАРТИЙ - Рекорд Месяца!',
        'games_played-week' => 'Сыграно ПАРТИЙ - Рекорд Недели!',
        'games_played-day' => 'Сыграно ПАРТИЙ - Рекорд Дня!',
    ];
    public const PRIZE_LINKS = [
        'game_price-year' => 'img/prizes/yearly/ochki_za_igru_year.svg',
        'game_price-month' => 'img/prizes/monthly/ochki_za_igru_month.svg',
        'game_price-week' => 'img/prizes/weekly/ochki_za_igru_week.svg',
        'game_price-day' => 'img/prizes/daily/ochki_za_igru_day.svg',

        'turn_price-year' => 'img/prizes/yearly/ochki_za_hod_year.svg',
        'turn_price-month' => 'img/prizes/monthly/ochki_za_hod_month.svg',
        'turn_price-week' => 'img/prizes/weekly/ochki_za_hod_week.svg',
        'turn_price-day' => 'img/prizes/daily/ochki_za_hod_day.svg',

        'word_price-year' => 'img/prizes/yearly/ochki_za_slovo_year.svg',
        'word_price-month' => 'img/prizes/monthly/ochki_za_slovo_month.svg',
        'word_price-week' => 'img/prizes/weekly/ochki_za_slovo_week.svg',
        'word_price-day' => 'img/prizes/daily/ochki_za_slovo_day.svg',

        'word_len-year' => 'img/prizes/yearly/dlinnoe_slovo_year.svg',
        'word_len-month' => 'img/prizes/monthly/dlinnoe_slovo_month.svg',
        'word_len-week' => 'img/prizes/weekly/dlinnoe_slovo_week.svg',
        'word_len-day' => 'img/prizes/daily/dlinnoe_slovo_day.svg',

        'games_played-year' => 'img/prizes/yearly/sygrano_partiy_year.svg',
        'games_played-month' => 'img/prizes/monthly/sygrano_partiy_month.svg',
        'games_played-week' => 'img/prizes/weekly/sygrano_partiy_week.svg',
        'games_played-day' => 'img/prizes/daily/sygrano_partiy_day.svg',
    ];

    const GAMES_STATS_TABLE = 'games_stats';
    const GAME_ID_FIELD = 'game_id';
    const PLAYERS_NUMBER_FIELD = 'players_num';
    const PLAYER1_ID_FIELD = '1_player_id';
    const PLAYER2_ID_FIELD = '2_player_id';
    const WINNER_ID_FIELD = 'winner_player_id';
    const GAME_DATE_FIELD = 'game_ended_date';
    const RATING_DELTA_1_FIELD = '1_player_rating_delta';
    const RATING_DELTA_2_FIELD = '2_player_rating_delta';
    const RATING_OLD_1_FIELD = '1_player_old_rating';
    const RATING_OLD_2_FIELD = '2_player_old_rating';
    const OPPONENT_COMMON_ID = 'opponent_common_id';
    const YOUR_RESULT = 'your_result';
    const YOUR_RATING_PROGRESS = 'your_progress';


    public static function getAchievesByCommonId(int $commonId, int $limit = 10, int $page = 1, array $filters = []) {
        $query = ORM::select(
                [
                    "substring(" . self::DATE_ACHIEVED_FIELD . ",1,10) as " . self::DATE_ACHIEVED_FIELD,
                    "concat_ws('-'," . self::EVENT_TYPE_FIELD . ", " . self::EVENT_PERIOD_FIELD . ") as " . self::EVENT_TYPE_FIELD,
                    self::WORD_FIELD,
                    self::EVENT_VALUE_FIELD
                ],
                self::TABLE_NAME
            )
            .ORM::where(self::COMMON_ID_FIELD, '=', $commonId, true)
            . ($filters[StatsController::NO_STONE_PARAM] ?? false ? ORM::andWhere(self::EVENT_PERIOD_FIELD, '!=', 'day') : '')
            . ($filters[StatsController::NO_BRONZE_PARAM] ?? false ? ORM::andWhere(self::EVENT_PERIOD_FIELD, '!=', 'week') : '')
            . ($filters[StatsController::NO_SILVER_PARAM] ?? false ? ORM::andWhere(self::EVENT_PERIOD_FIELD, '!=', 'month') : '')
            . ($filters[StatsController::NO_GOLD_PARAM] ?? false ? ORM::andWhere(self::EVENT_PERIOD_FIELD, '!=', 'year') : '')
            .ORM::orderBy(self::ID_FIELD, false)
            .ORM::limit($limit, ($page - 1) * $limit);

        $res = DB::queryArray($query);

        if (empty($res)) {
            $res = [
                0=>[
                    self::DATE_ACHIEVED_FIELD => '',
                    self::EVENT_TYPE_FIELD => '',
                    self::WORD_FIELD => '',
                    self::EVENT_VALUE_FIELD => '',
                ]
            ];
        }

        return $res;
    }

    public static function getAchievesByCommonIdCount(int $commonId, array $filters = [])
    {
        return DB::queryValue(
            ORM::select(['count(1)'], self::TABLE_NAME)
            .ORM::where(self::COMMON_ID_FIELD,'=', $commonId, true)
            . ($filters[StatsController::NO_STONE_PARAM] ?? false ? ORM::andWhere(self::EVENT_PERIOD_FIELD, '!=', 'day') : '')
            . ($filters[StatsController::NO_BRONZE_PARAM] ?? false ? ORM::andWhere(self::EVENT_PERIOD_FIELD, '!=', 'week') : '')
            . ($filters[StatsController::NO_SILVER_PARAM] ?? false ? ORM::andWhere(self::EVENT_PERIOD_FIELD, '!=', 'month') : '')
            . ($filters[StatsController::NO_GOLD_PARAM] ?? false ? ORM::andWhere(self::EVENT_PERIOD_FIELD, '!=', 'year') : '')
        );
    }

    public static function getGamesByCommonId(int $commonId, int $limit = 10, int $page = 1, array $filters = []) {
        $query = ORM::select(
                [
                    self::GAME_ID_FIELD,
                    self::PLAYER1_ID_FIELD,
                    self::PLAYER2_ID_FIELD,
                    self::WINNER_ID_FIELD,
                    self::GAME_DATE_FIELD,
                    self::RATING_DELTA_1_FIELD,
                    self::RATING_DELTA_2_FIELD,
                    self::RATING_OLD_1_FIELD,
                    self::RATING_OLD_2_FIELD
                ],
                self::GAMES_STATS_TABLE
            )
            // Пока строим статистику только для игр на 2 игрока
            . ORM::where(self::PLAYERS_NUMBER_FIELD, '=', 2, true)
            . ' AND ( '
            . ORM::getWhereCondition('1_player_id', '=', $commonId, true)
            . ORM::orWhere('2_player_id', '=', $commonId, true)
            . ' ) '
            . (
                $filters[StatsController::FILTER_PLAYER_PARAM] ?? false
                    ? (' AND ( '
                    . ORM::getWhereCondition('1_player_id', '=', StatsController::$Request[StatsController::FILTER_PLAYER_PARAM], true)
                    . ORM::orWhere('2_player_id', '=', StatsController::$Request[StatsController::FILTER_PLAYER_PARAM], true)
                    . ' ) ')
                    : ''
            )
            .ORM::orderBy(self::GAME_ID_FIELD, false)
            .ORM::limit($limit, ($page - 1) * $limit);

        $res = DB::queryArray($query);

        $gameStats = [];

        include_once('/var/www/erudit.club/yandex1.0.1.1/php/autoload.php');
        $instance = new Game();
        $instance->gameStatus['lngClass'] = "\Lang\\" . 'Ru';

        foreach($res as $row) {
            $opponentCommonId = $row[self::PLAYER1_ID_FIELD] != $commonId ? $row[self::PLAYER1_ID_FIELD] : $row[self::PLAYER2_ID_FIELD];
            $opponentCookie = PlayerModel::getOne($opponentCommonId)['cookie'] ?? '';

            $gameStats[] = [
                self::GAME_DATE_FIELD =>
                    ViewHelper::tag('span', date('Y-m-d', $row[self::GAME_DATE_FIELD]),[
                        'style' => 'white-space: nowrap;'
                    ]),
                self::YOUR_RESULT => $row[self::WINNER_ID_FIELD] == $commonId
                    ? ViewHelper::tag('span','Победа', ['class' => 'badge badge-success'])
                    : ViewHelper::tag('span','Проигрыш', ['class' => 'badge badge-warning']),
                self::YOUR_RATING_PROGRESS => $row[self::PLAYER1_ID_FIELD] == $commonId
                    ? ((string)($row[self::RATING_OLD_1_FIELD] + $row[self::RATING_DELTA_1_FIELD]) . ' ('. ($row[self::RATING_DELTA_1_FIELD] > 0 ? '+' : '') . $row[self::RATING_DELTA_1_FIELD] .')')
                    : ((string)($row[self::RATING_OLD_2_FIELD] + $row[self::RATING_DELTA_2_FIELD]) . ' ('. ($row[self::RATING_DELTA_2_FIELD] > 0 ? '+' : '') . $row[self::RATING_DELTA_2_FIELD] .')'),
                self::OPPONENT_COMMON_ID =>
                    ViewHelper::tag(
                        'img',
                        '',
                        [
                            'src' => Players::getAvatarUrl($opponentCommonId),
                            //'width' => '50px',
                            'style' => 'border-radius: 5px 5px 5px 5px; margin-bottom: 9px;',
                            'height' => '75px',
                            'max-width' => '100px',
                        ]
                    )
                    . ViewHelper::tagOpen('br')
                    . ViewHelper::tag(
                        'button',
                        $instance->getPlayerName(
                            [
                                'ID' => $opponentCookie,
                                'common_id' => $opponentCommonId
                            ]
                        ),
                        [
                            'class' => 'btn btn-sm ' . (StatsController::$Request[StatsController::FILTER_PLAYER_PARAM] ?? 0) == $opponentCommonId
                                    ? 'btn-outline-secondary'
                                    : 'btn-outline-primary',
                            'title' => (StatsController::$Request[StatsController::FILTER_PLAYER_PARAM] ?? 0) == $opponentCommonId
                                    ? 'Снять фильтр'
                                    : 'Фильтровать по игроку',
                            'onClick' => "refreshId('" . StatsAchievesGamesView::ACHIEVES_ELEMENT_ID . "', '"
                                . StatsController::getUrl(
                                    'games',
                                    [
                                        'common_id' => StatsController::$Request['common_id'] ?? '',
                                        'refresh' => '1',
                                        (StatsController::$Request[StatsController::FILTER_PLAYER_PARAM] ?? 0) == $opponentCommonId
                                            ? 'none'
                                            : StatsController::FILTER_PLAYER_PARAM
                                        => $opponentCommonId,
                                    ]
                                )
                                . "')"
                        ]
                    )
            ];
        }
        if (empty($gameStats)) {
            $gameStats = [
                0=>[
                    self::GAME_DATE_FIELD => '',
                    self::YOUR_RESULT => '',
                    self::OPPONENT_COMMON_ID => '',
                    self::YOUR_RATING_PROGRESS => '',
                ]
            ];
        }

        return $gameStats;
    }

    public static function getGamesByCommonIdCount($commonId, array $filters = []): ?int
    {
        return DB::queryValue(
            ORM::select(['count(1)'], self::GAMES_STATS_TABLE)
            // Пока строим статистику только для игр на 2 игрока
            . ORM::where(self::PLAYERS_NUMBER_FIELD, '=', 2, true)
            . ' AND ( '
            . ORM::getWhereCondition('1_player_id', '=', $commonId, true)
            . ORM::orWhere('2_player_id', '=', $commonId, true)
            . ' ) '
            . (
                $filters[StatsController::FILTER_PLAYER_PARAM] ?? false
                    ? (' AND ( '
                        . ORM::getWhereCondition('1_player_id', '=', StatsController::$Request[StatsController::FILTER_PLAYER_PARAM], true)
                        . ORM::orWhere('2_player_id', '=', StatsController::$Request[StatsController::FILTER_PLAYER_PARAM], true)
                        . ' ) ')
                    : ''
            )
        ) ?: null;
    }
}
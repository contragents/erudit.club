<?php

use BaseController as BC;

/**
 * @property int $_id
 * @property int $_common_id
 * @property string $_date_achieved
 * @property string $_event_type
 * @property string $_event_period
 * @property string $_word
 * @property string $_event_value
 * @property bool $_is_active
 * @property float $_reward
 * @property float $_income
 * @property int $_game_name_id
 *
 **/
class AchievesModel extends BaseModel
{
    const TABLE_NAME = 'achieves';

    const LIMIT = 10;

    const COMMON_ID_FIELD = 'common_id';
    const DATE_ACHIEVED_FIELD = 'date_achieved';
    const EVENT_TYPE_FIELD = 'event_type';
    const EVENT_PERIOD_FIELD = 'event_period';
    const WORD_FIELD = 'word';
    const EVENT_VALUE_FIELD = 'event_value';
    const IS_ACTIVE_FIELD = 'is_active';
    const REWARD_FIELD = 'reward';
    const INCOME_FIELD = 'income';
    const GAME_NAME_ID_FIELD = 'game_name_id';

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
        self::COMMON_ID_FIELD => "Player ID",
        self::DATE_ACHIEVED_FIELD => 'Date',
        self::EVENT_TYPE_FIELD => 'Type',
        self::EVENT_PERIOD_FIELD => 'Period',
        self::WORD_FIELD => 'Word',
        self::EVENT_VALUE_FIELD => 'Points/letters',
        self::GAME_DATE_FIELD => 'Date',
        self::YOUR_RESULT => 'Result',
        self::OPPONENT_COMMON_ID => 'Opponents',
        self::YOUR_RATING_PROGRESS => 'Rating',
        'games_count' => 'Games in total',
        'wins' => 'Winning count',
        'delta_rating' => 'Increase/loss in rating',
        'win_percent' => '% of wins',
    ];

    public const WORD_LEN = 'word_len';
    public const GAME_PRICE = 'game_price';
    public const TURN_PRICE = 'turn_price';
    public const WORD_PRICE = 'word_price';
    public const GAMES_PLAYED = 'games_played';
    public const REF_COUNT = 'ref_count'; // КОличество рефералов (не используется)

    public const VALID_RECORD_TYPES = [
        self::WORD_LEN,
        self::GAME_PRICE,
        self::TURN_PRICE,
        self::WORD_PRICE,
        self::GAMES_PLAYED
    ];

    public const DAY_PERIOD = 'day';
    public const WEEK_PERIOD = 'week';
    public const MONTH_PERIOD = 'month';
    public const YEAR_PERIOD = 'year';

    public const VALID_PERIODS = [self::DAY_PERIOD, self::WEEK_PERIOD, self::MONTH_PERIOD, self::YEAR_PERIOD];

    public const TOP_TYPE = 'top';

    // todo сделать переводы T::S()
    public const PRIZE_TITLES = [
        self::GAME_PRICE . '-year' => 'Очки за ИГРУ - Рекорд Года!',
        self::GAME_PRICE . '-month' => 'Очки за ИГРУ - Рекорд Месяца!',
        self::GAME_PRICE . '-week' => 'Очки за ИГРУ - Рекорд Недели!',
        self::GAME_PRICE . '-day' => 'Очки за ИГРУ - Рекорд Дня!',

        self::TURN_PRICE . '-year' => 'Очки за ХОД - Рекорд Года!',
        self::TURN_PRICE . '-month' => 'Очки за ХОД - Рекорд Месяца!',
        self::TURN_PRICE . '-week' => 'Очки за ХОД - Рекорд Недели!',
        self::TURN_PRICE . '-day' => 'Очки за ХОД - Рекорд Дня!',

        self::WORD_PRICE . '-year' => 'Очки за СЛОВО - Рекорд Года!',
        self::WORD_PRICE . '-month' => 'Очки за СЛОВО - Рекорд Месяца!',
        self::WORD_PRICE . '-week' => 'Очки за СЛОВО - Рекорд Недели!',
        self::WORD_PRICE . '-day' => 'Очки за СЛОВО - Рекорд Дня!',

        self::WORD_LEN . '-year' => 'Самое длинное СЛОВО - Рекорд Года!',
        self::WORD_LEN . '-month' => 'Самое длинное СЛОВО - Рекорд Месяца!',
        self::WORD_LEN . '-week' => 'Самое длинное СЛОВО - Рекорд Недели!',
        self::WORD_LEN . '-day' => 'Самое длинное СЛОВО - Рекорд Дня!',

        self::GAMES_PLAYED . '-year' => 'Сыграно ПАРТИЙ - Рекорд Года!',
        self::GAMES_PLAYED . '-month' => 'Сыграно ПАРТИЙ - Рекорд Месяца!',
        self::GAMES_PLAYED . '-week' => 'Сыграно ПАРТИЙ - Рекорд Недели!',
        self::GAMES_PLAYED . '-day' => 'Сыграно ПАРТИЙ - Рекорд Дня!',

        self::TOP_TYPE . '-year' => 'ТОП 1 по рейтингу!',
        self::TOP_TYPE . '-month' => 'ТОП 2 по рейтингу!',
        self::TOP_TYPE . '-week' => 'ТОП 3 по рейтингу!',
        self::TOP_TYPE . '-day' => 'В десятке лучших по рейтингу!',
    ];

    public const PRIZE_LINKS = [
        'top-year' => 'img/prizes/top_1.svg',
        'top-month' => 'img/prizes/top_2.svg',
        'top-week' => 'img/prizes/top_3.svg',
        'top-day' => 'img/prizes/top_10.svg',

        self::GAME_PRICE . '-year' => BC::BASE_URL . 'img/prizes/yearly/ochki_za_igru_year.svg',
        self::GAME_PRICE . '-month' => BC::BASE_URL . 'img/prizes/monthly/ochki_za_igru_month.svg',
        self::GAME_PRICE . '-week' => BC::BASE_URL . 'img/prizes/weekly/ochki_za_igru_week.svg',
        self::GAME_PRICE . '-day' => BC::BASE_URL . 'img/prizes/daily/ochki_za_igru_day.svg',

        self::TURN_PRICE . '-year' => BC::BASE_URL . 'img/prizes/yearly/ochki_za_hod_year.svg',
        self::TURN_PRICE . '-month' => BC::BASE_URL . 'img/prizes/monthly/ochki_za_hod_month.svg',
        self::TURN_PRICE . '-week' => BC::BASE_URL . 'img/prizes/weekly/ochki_za_hod_week.svg',
        self::TURN_PRICE . '-day' => BC::BASE_URL . 'img/prizes/daily/ochki_za_hod_day.svg',

        self::WORD_PRICE . '-year' => BC::BASE_URL . 'img/prizes/yearly/ochki_za_slovo_year.svg',
        self::WORD_PRICE . '-month' => BC::BASE_URL . 'img/prizes/monthly/ochki_za_slovo_month.svg',
        self::WORD_PRICE . '-week' => BC::BASE_URL . 'img/prizes/weekly/ochki_za_slovo_week.svg',
        self::WORD_PRICE . '-day' => BC::BASE_URL . 'img/prizes/daily/ochki_za_slovo_day.svg',

        self::WORD_LEN . '-year' => BC::BASE_URL . 'img/prizes/yearly/dlinnoe_slovo_year.svg',
        self::WORD_LEN . '-month' => BC::BASE_URL . 'img/prizes/monthly/dlinnoe_slovo_month.svg',
        self::WORD_LEN . '-week' => BC::BASE_URL . 'img/prizes/weekly/dlinnoe_slovo_week.svg',
        self::WORD_LEN . '-day' => BC::BASE_URL . 'img/prizes/daily/dlinnoe_slovo_day.svg',

        self::GAMES_PLAYED . '-year' => BC::BASE_URL . 'img/prizes/yearly/sygrano_partiy_year.svg',
        self::GAMES_PLAYED . '-month' => BC::BASE_URL . 'img/prizes/monthly/sygrano_partiy_month.svg',
        self::GAMES_PLAYED . '-week' => BC::BASE_URL . 'img/prizes/weekly/sygrano_partiy_week.svg',
        self::GAMES_PLAYED . '-day' => BC::BASE_URL . 'img/prizes/daily/sygrano_partiy_day.svg',
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
    public const ACHIEVES_ELEMENT_ID = 'achieves_table';

    const GOLD_ACHIEVE_TYPE = 'gold';
    const SILVER_ACHIEVE_TYPE = 'silver';
    const BRONZE_ACHIEVE_TYPE = 'bronze';
    const STONE_ACHIEVE_TYPE = 'stone';

    const TOP_TYPES = [
        1 => self::GOLD_ACHIEVE_TYPE,
        2 => self::SILVER_ACHIEVE_TYPE,
        3 => self::BRONZE_ACHIEVE_TYPE,
        4 => self::STONE_ACHIEVE_TYPE,
        5 => self::STONE_ACHIEVE_TYPE,
        6 => self::STONE_ACHIEVE_TYPE,
        7 => self::STONE_ACHIEVE_TYPE,
        8 => self::STONE_ACHIEVE_TYPE,
        9 => self::STONE_ACHIEVE_TYPE,
        10 => self::STONE_ACHIEVE_TYPE,
        self::YEAR_PERIOD => self::GOLD_ACHIEVE_TYPE,
        self::MONTH_PERIOD => self::SILVER_ACHIEVE_TYPE,
        self::WEEK_PERIOD => self::BRONZE_ACHIEVE_TYPE,
        self::DAY_PERIOD => self::STONE_ACHIEVE_TYPE,
    ];
    const PATREON_TYPE = 'patreon';
    const PURPLE_CARD = 'purple';

    public ?int $_id = null;
    public ?int $_common_id = null;
    public ?string $_date_achieved = null;
    public ?string $_event_type = null;
    public ?string $_event_period = null;
    public ?string $_word = null;
    public ?string $_event_value = null;
    public bool $_is_active = false;
    public ?float $_reward = null;
    public ?float $_income = null;
    public ?int $_game_name_id = null;
    /**
     * @var AchievesModel[]|BaseModel|mixed|object|string|null
     */

    /**
     * @return static[]
     */
    public static function getActiveO(): array
    {
        $queryObject = static::find()
            ->where([
                        static::IS_ACTIVE_FIELD => true,
                        static::GAME_NAME_ID_FIELD => BaseModel::GAME_IDS[Game::$gameName]
                    ]);

        return $queryObject->all();
    }

    public static function getDescription(string $eventType, string $eventPeriod, string $gameName = ''): string
    {
        $res = '';

        if ($eventType === AchievesModel::TOP_TYPE) {
            $res = T::S('rank position') . ' ' . T::S(AchievesModel::TOP_TYPE . '_' . $eventPeriod);
        } else {
            $res = T::S('record of the ' . $eventPeriod) . ' - ' . T::S($eventType);
        }

        if ($gameName) {
            $res .= ' (' . T::S('game_name') . ')';
        }

        return $res;
    }

    public static function getPastAchievesByCommonId(int $commonId)
    {
        $query = ORM::select(
                [
                    "substring(" . self::DATE_ACHIEVED_FIELD . ",1,10) as " . self::DATE_ACHIEVED_FIELD,
                    self::EVENT_TYPE_FIELD,
                    self::EVENT_PERIOD_FIELD,
                    self::WORD_FIELD,
                    self::EVENT_VALUE_FIELD
                ],
                self::TABLE_NAME
            )
            . ORM::where(self::COMMON_ID_FIELD, '=', $commonId, true)
            . ORM::andWhere(self::IS_ACTIVE_FIELD, '=', 0, true)
            . ORM::andWhere(self::GAME_NAME_ID_FIELD, '=', BaseModel::GAME_IDS[Game::$gameName], true)
            . ORM::orderBy(self::ID_FIELD, false)
            . ORM::limit(30);

        $res = DB::queryArray($query) ?: [];

        return $res;
    }

    public static function getPatreonAchievesByCommonId(int $commonId): array
    {
        $patreonAchievesModels = AchievesModel::find()
            ->where(
                [
                    self::COMMON_ID_FIELD => $commonId,
                    self::EVENT_TYPE_FIELD => self::PATREON_TYPE,
                    self::IS_ACTIVE_FIELD => true,
                    self::GAME_NAME_ID_FIELD => BaseModel::GAME_IDS[BaseModel::ALL_GAMES]
                ]
            )->all();

        return array_map(fn($obj) => $obj->toArray(), $patreonAchievesModels);
    }

    public static function getCurrentAchievesByCommonId(int $commonId): array
    {
        // todo CLUB-468 Переделать на ::find()->where()->order... ->toArray как в методе getPatreonAchievesByCommonId
        $query = ORM::select(
                [
                    "substring(" . self::DATE_ACHIEVED_FIELD . ",1,10) as " . self::DATE_ACHIEVED_FIELD,
                    self::EVENT_TYPE_FIELD,
                    self::EVENT_PERIOD_FIELD,
                    self::WORD_FIELD,
                    self::EVENT_VALUE_FIELD
                ],
                self::TABLE_NAME
            )
            . ORM::where(self::COMMON_ID_FIELD, '=', $commonId, true)
            . ORM::andWhere(self::IS_ACTIVE_FIELD, '=', 1, true)
            . ORM::andWhere(self::GAME_NAME_ID_FIELD, '=', BaseModel::GAME_IDS[Game::$gameName], true)
            . ORM::orderBy(self::ID_FIELD, false);

        $res = DB::queryArray($query) ?: [];

        return $res;
    }

    public static function getAchievesByCommonId(int $commonId, int $limit = 10, int $page = 1, array $filters = [])
    {
        $query = ORM::select(
                [
                    "substring(" . self::DATE_ACHIEVED_FIELD . ",1,10) as " . self::DATE_ACHIEVED_FIELD,
                    "concat_ws('-'," . self::EVENT_TYPE_FIELD . ", " . self::EVENT_PERIOD_FIELD . ") as " . self::EVENT_TYPE_FIELD,
                    self::WORD_FIELD,
                    self::EVENT_VALUE_FIELD
                ],
                self::TABLE_NAME
            )
            . ORM::where(self::COMMON_ID_FIELD, '=', $commonId, true)
            . ORM::andWhere(self::GAME_NAME_ID_FIELD, '=', BaseModel::GAME_IDS[Game::$gameName], true)
            . ($filters[StatsController::NO_STONE_PARAM] ?? false ? ORM::andWhere(
                self::EVENT_PERIOD_FIELD,
                '!=',
                self::DAY_PERIOD
            ) : '')
            . ($filters[StatsController::NO_BRONZE_PARAM] ?? false ? ORM::andWhere(
                self::EVENT_PERIOD_FIELD,
                '!=',
                self::WEEK_PERIOD
            ) : '')
            . ($filters[StatsController::NO_SILVER_PARAM] ?? false ? ORM::andWhere(
                self::EVENT_PERIOD_FIELD,
                '!=',
                self::MONTH_PERIOD
            ) : '')
            . ($filters[StatsController::NO_GOLD_PARAM] ?? false ? ORM::andWhere(
                self::EVENT_PERIOD_FIELD,
                '!=',
                self::YEAR_PERIOD
            ) : '')
            . ORM::orderBy(self::ID_FIELD, false)
            . ORM::limit($limit, ($page - 1) * $limit);

        $res = DB::queryArray($query);

        if (empty($res)) {
            $res = [
                0 => [
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
            . ORM::where(self::COMMON_ID_FIELD, '=', $commonId, true)
            . ORM::andWhere(self::GAME_NAME_ID_FIELD, '=', BaseModel::GAME_IDS[Game::$gameName], true)
            . ($filters[StatsController::NO_STONE_PARAM] ?? false ? ORM::andWhere(
                self::EVENT_PERIOD_FIELD,
                '!=',
                self::DAY_PERIOD
            ) : '')
            . ($filters[StatsController::NO_BRONZE_PARAM] ?? false ? ORM::andWhere(
                self::EVENT_PERIOD_FIELD,
                '!=',
                self::WEEK_PERIOD
            ) : '')
            . ($filters[StatsController::NO_SILVER_PARAM] ?? false ? ORM::andWhere(
                self::EVENT_PERIOD_FIELD,
                '!=',
                self::MONTH_PERIOD
            ) : '')
            . ($filters[StatsController::NO_GOLD_PARAM] ?? false ? ORM::andWhere(
                self::EVENT_PERIOD_FIELD,
                '!=',
                self::YEAR_PERIOD
            ) : '')
        );
    }

    public static function getGamesByCommonIdV2(
        int $commonId,
        int $limit = 10,
        int $page = 1,
        array $filters = []
    ): array {
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
            . ORM::andWhere(self::GAME_NAME_ID_FIELD, '=', BaseModel::GAME_IDS[Game::$gameName], true)
            . ' AND ( '
            . ORM::getWhereCondition('1_player_id', '=', $commonId, true)
            . ORM::orWhere('2_player_id', '=', $commonId, true)
            . ' ) '
            . (
                $filters[StatsController::FILTER_PLAYER_PARAM] ?? false
                ? (' AND ( '
                . ORM::getWhereCondition(
                    '1_player_id',
                    '=',
                    StatsController::$Request[StatsController::FILTER_PLAYER_PARAM],
                    true
                )
                . ORM::orWhere(
                    '2_player_id',
                    '=',
                    StatsController::$Request[StatsController::FILTER_PLAYER_PARAM],
                    true
                )
                . ' ) ')
                : ''
            )
            . ORM::orderBy(self::GAME_ID_FIELD, false)
            . ORM::limit($limit, ($page - 1) * $limit);

        $res = DB::queryArray($query);

        $gameStats = [];

        // todo сделать подгрузку классов централизованно
        //include_once(__DIR__ . '/../../../autoload_helper.php');

        foreach ($res as $row) {
            $opponentCommonId = $row[self::PLAYER1_ID_FIELD] != $commonId ? $row[self::PLAYER1_ID_FIELD] : $row[self::PLAYER2_ID_FIELD];

            $gameStats[] = [
                self::GAME_DATE_FIELD => date('Y-m-d', $row[self::GAME_DATE_FIELD]),
                self::YOUR_RESULT => $row[self::WINNER_ID_FIELD] == $commonId
                    ? T::S('Victory')
                    : T::S('Losing'),
                self::YOUR_RATING_PROGRESS => $row[self::PLAYER1_ID_FIELD] == $commonId
                    ? ((string)($row[self::RATING_OLD_1_FIELD] + $row[self::RATING_DELTA_1_FIELD]) . ' (' . ($row[self::RATING_DELTA_1_FIELD] > 0 ? '+' : '') . $row[self::RATING_DELTA_1_FIELD] . ')')
                    : ((string)($row[self::RATING_OLD_2_FIELD] + $row[self::RATING_DELTA_2_FIELD]) . ' (' . ($row[self::RATING_DELTA_2_FIELD] > 0 ? '+' : '') . $row[self::RATING_DELTA_2_FIELD] . ')'),
                'new_rating' => $row[self::PLAYER1_ID_FIELD] == $commonId
                    ? (string)($row[self::RATING_OLD_1_FIELD] + $row[self::RATING_DELTA_1_FIELD])
                    : (string)($row[self::RATING_OLD_2_FIELD] + $row[self::RATING_DELTA_2_FIELD]),
                'delta_rating' => $row[self::PLAYER1_ID_FIELD] == $commonId
                    ? ('(' . ($row[self::RATING_DELTA_1_FIELD] > 0 ? '+' : '') . $row[self::RATING_DELTA_1_FIELD] . ')')
                    : ('(' . ($row[self::RATING_DELTA_2_FIELD] > 0 ? '+' : '') . $row[self::RATING_DELTA_2_FIELD] . ')'),
                self::OPPONENT_COMMON_ID => $opponentCommonId,
                'opponent_avatar_url' => PlayerModel::getAvatarUrl($opponentCommonId),
                'opponent_name' => self::getPlayerNameByCommonId($opponentCommonId),
                'opponent_filter_url' => StatsController::getUrl(
                    'gamesV2',
                    [
                        'common_id' => StatsController::$Request['common_id'] ?? '',
                        'refresh' => '1',
                        (StatsController::$Request[StatsController::FILTER_PLAYER_PARAM] ?? 0) == $opponentCommonId
                            ? 'none'
                            : StatsController::FILTER_PLAYER_PARAM
                        => $opponentCommonId,
                        'lang' => T::$lang
                    ]
                ),
                'opponent_filter_title' => (StatsController::$Request[StatsController::FILTER_PLAYER_PARAM] ?? 0) == $opponentCommonId
                    ? T::S('Remove filter')
                    : T::S('Filter by player'),
                'delta_coins' => BalanceHistoryModel::getDeltaCoins($commonId, $row[self::GAME_ID_FIELD]),
            ];
        }

        if (empty($gameStats)) {
            $gameStats = [];
        }

        return $gameStats;
    }

    public static function getGamesByCommonId(int $commonId, int $limit = 10, int $page = 1, array $filters = [])
    {
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
            . ORM::andWhere(self::GAME_NAME_ID_FIELD, '=', BaseModel::GAME_IDS[Game::$gameName], true)
            . ' AND ( '
            . ORM::getWhereCondition('1_player_id', '=', $commonId, true)
            . ORM::orWhere('2_player_id', '=', $commonId, true)
            . ' ) '
            . (
                $filters[StatsController::FILTER_PLAYER_PARAM] ?? false
                ? (' AND ( '
                . ORM::getWhereCondition(
                    '1_player_id',
                    '=',
                    StatsController::$Request[StatsController::FILTER_PLAYER_PARAM],
                    true
                )
                . ORM::orWhere(
                    '2_player_id',
                    '=',
                    StatsController::$Request[StatsController::FILTER_PLAYER_PARAM],
                    true
                )
                . ' ) ')
                : ''
            )
            . ORM::orderBy(self::GAME_ID_FIELD, false)
            . ORM::limit($limit, ($page - 1) * $limit);

        $res = DB::queryArray($query);

        $gameStats = [];

        // todo сделать подгрузку классов централизованно
        include_once(__DIR__ . '/../../../autoload_helper.php');

        foreach ($res as $row) {
            $opponentCommonId = $row[self::PLAYER1_ID_FIELD] != $commonId ? $row[self::PLAYER1_ID_FIELD] : $row[self::PLAYER2_ID_FIELD];

            $gameStats[] = [
                self::GAME_DATE_FIELD =>
                    ViewHelper::tag(
                        'span',
                        date('Y-m-d', $row[self::GAME_DATE_FIELD]),
                        [
                            'style' => 'white-space: nowrap;'
                        ]
                    )
                    . (BaseController::isAjaxRequest()
                        ? ''
                        : ViewHelper::tag(
                            'a',
                            ' ...',
                            [
                                'href' => '/' . GameController::getUrl($row[self::GAME_ID_FIELD]),
                                'title' => 'Перейти в игру'
                            ]
                        )
                    ),
                self::YOUR_RESULT => $row[self::WINNER_ID_FIELD] == $commonId
                    ? ViewHelper::tag('span', 'Победа', ['class' => 'badge badge-success'])
                    : ViewHelper::tag('span', 'Проигрыш', ['class' => 'badge badge-warning']),
                self::YOUR_RATING_PROGRESS => $row[self::PLAYER1_ID_FIELD] == $commonId
                    ? ((string)($row[self::RATING_OLD_1_FIELD] + $row[self::RATING_DELTA_1_FIELD]) . ' (' . ($row[self::RATING_DELTA_1_FIELD] > 0 ? '+' : '') . $row[self::RATING_DELTA_1_FIELD] . ')')
                    : ((string)($row[self::RATING_OLD_2_FIELD] + $row[self::RATING_DELTA_2_FIELD]) . ' (' . ($row[self::RATING_DELTA_2_FIELD] > 0 ? '+' : '') . $row[self::RATING_DELTA_2_FIELD] . ')'),
                self::OPPONENT_COMMON_ID =>
                    (BaseController::isAjaxRequest()
                        ? ''
                        : ViewHelper::tagOpen(
                            'a',
                            '',
                            [
                                'href' => '/' . StatsController::getUrl(
                                        'games',
                                        ['common_id' => $opponentCommonId,]
                                    ),
                                'title' => T::S("Go to player's stats")
                            ]
                        ))
                    . ViewHelper::tag(
                        'img',
                        '',
                        [
                            'src' => PlayerModel::getAvatarUrl($opponentCommonId),
                            //'width' => '50px',
                            'style' => 'border-radius: 5px 5px 5px 5px; margin-bottom: 9px;',
                            'height' => '75px',
                            'max-width' => '100px',
                        ]
                    )
                    . (BaseController::isAjaxRequest()
                        ? ''
                        : ViewHelper::tagClose('a'))
                    . ViewHelper::tagOpen('br')
                    . ViewHelper::tag(
                        BaseController::isAjaxRequest() ? 'button' : 'a',
                        self::getPlayerNameByCommonId($opponentCommonId),
                        [
                            'class' => 'btn btn-sm ' . (StatsController::$Request[StatsController::FILTER_PLAYER_PARAM] ?? 0) == $opponentCommonId
                                ? 'btn-outline-secondary'
                                : 'btn-outline-primary',
                            'title' => (StatsController::$Request[StatsController::FILTER_PLAYER_PARAM] ?? 0) == $opponentCommonId
                                ? T::S('Remove filter')
                                : T::S('Filter by player'),
                            'onClick' => ViewHelper::onClick(
                                'refreshId',
                                AchievesModel::ACHIEVES_ELEMENT_ID,
                                StatsController::getUrl(
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
                            ),
                            (BaseController::isAjaxRequest() ? 'nothing' : 'href') => '/' . StatsController::getUrl(
                                    'games',
                                    [
                                        'common_id' => StatsController::$Request['common_id'] ?? '',
                                        'refresh' => '1',
                                        (StatsController::$Request[StatsController::FILTER_PLAYER_PARAM] ?? 0) == $opponentCommonId
                                            ? 'none'
                                            : StatsController::FILTER_PLAYER_PARAM
                                        => $opponentCommonId,
                                    ]
                                ),
                        ]
                    )
            ];
        }
        if (empty($gameStats)) {
            $gameStats = [
                0 => [
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
            . ORM::andWhere(self::GAME_NAME_ID_FIELD, '=', BaseModel::GAME_IDS[Game::$gameName], true)
            . ' AND ( '
            . ORM::getWhereCondition('1_player_id', '=', $commonId, true)
            . ORM::orWhere('2_player_id', '=', $commonId, true)
            . ' ) '
            . (
                $filters[StatsController::FILTER_PLAYER_PARAM] ?? false
                ? (' AND ( '
                . ORM::getWhereCondition(
                    '1_player_id',
                    '=',
                    StatsController::$Request[StatsController::FILTER_PLAYER_PARAM],
                    true
                )
                . ORM::orWhere(
                    '2_player_id',
                    '=',
                    StatsController::$Request[StatsController::FILTER_PLAYER_PARAM],
                    true
                )
                . ' ) ')
                : ''
            )
        ) ?: null;
    }

    public static function getStatsVsOpponent($commonId, $opponentId)
    {
        $query = ORM::select(
                [
                    'COUNT(1) as games_count',
                    "SUM(CASE WHEN winner_player_id=$commonId THEN 1 ELSE 0 END) as wins",
                    "SUM(CASE WHEN 1_player_id=$commonId THEN 1_player_rating_delta ELSE 2_player_rating_delta END) as delta_rating"
                ],
                self::GAMES_STATS_TABLE
            )
            // Пока строим статистику только для игр на 2 игрока
            . ORM::where(self::PLAYERS_NUMBER_FIELD, '=', 2, true)
            . ORM::andWhere(self::GAME_NAME_ID_FIELD, '=', BaseModel::GAME_IDS[Game::$gameName], true)
            . ' AND ( '
            . ORM::getWhereCondition('1_player_id', '=', $commonId, true)
            . ORM::orWhere('2_player_id', '=', $commonId, true)
            . ' ) '
            . ' AND ( '
            . ORM::getWhereCondition('1_player_id', '=', $opponentId, true)
            . ORM::orWhere('2_player_id', '=', $opponentId, true)
            . ' ) ';

        $res = DB::queryArray($query);

        if (count($res) && $res[0]['games_count']) {
            $res[0]['win_percent'] = number_format($res[0]['wins'] / $res[0]['games_count'] * 100, 1, ',', ' ') . '%';
        } else {
            $res = [
                0 => ['games_count' => 0, 'win_percent' => 0, 'delta_rating' => 0, 'wins' => 0]
            ];
        }

        return $res;
    }

    public static function getPlayerNameByCommonId(int $commonId): string
    {
        $cookie = PlayerModel::getOne($commonId)['cookie'] ?? '';

        return PlayerModel::getPlayerName(
            [
                'ID' => $cookie,
                'common_id' => $commonId
            ]
        );
    }

    /**
     * @param string $gameName
     * @param string $type
     * @param string $period
     * @return self[]
     */
    public static function getActiveAchievesO(string $gameName, string $type = '', string $period = ''): array
    {
        $rows = self::getActive($gameName, $type, $period);
        $res = [];

        foreach ($rows as $row) {
            $res[] = self::arrayToObject($row);
        }

        return $res;
    }

    public static function getActive(string $gameName, string $type = '', string $period = ''): array
    {
        return DB::queryArray(
            self::select(['*'])
            . ORM::where(self::IS_ACTIVE_FIELD, '=', 1, true)
            . ORM::andWhere(self::GAME_NAME_ID_FIELD, '=', BaseModel::GAME_IDS[$gameName])
            . ($type ? ORM::andWhere(self::EVENT_TYPE_FIELD, '=', $type) : '')
            . ($period ? ORM::andWhere(self::EVENT_PERIOD_FIELD, '=', $period) : '')
            . ORM::orderBy(self::REWARD_FIELD, false)
        ) ?: [];
    }
}
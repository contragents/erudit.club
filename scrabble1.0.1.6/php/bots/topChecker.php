<?php

include_once(__DIR__ . '/../autoload.php');

class TopChecker {
    const TOP_PARAMS = [
        AchievesModel::YEAR_PERIOD => [1, null],
        AchievesModel::MONTH_PERIOD => [2, null],
        AchievesModel::WEEK_PERIOD => [3, null],
        AchievesModel::DAY_PERIOD => [4, 10],
    ];

    public static function Run() {
        /*$top1PlayersNow = PlayerModel::getCustom(
            PlayerModel::RATING_FIELD,
            '=',
            PlayerModel::select([ORM::agg(ORM::MAX, PlayerModel::RATING_FIELD)], true),
            true,
            false,
            [PlayerModel::COMMON_ID_FIELD, PlayerModel::RATING_FIELD]
        );

        //print_r($top1PlayersNow);
*/
        foreach(self::TOP_PARAMS as $period => $params) {
            // todo переделать на CommonIdRating
            $topPlayersNow = PlayerModel::getTopPlayers(... $params);

            print_r($topPlayersNow);

            foreach($topPlayersNow as $top) { // Получили разбивку по номеру ТОП
                foreach ($top as $player) { // Получили игроков в данном ТОПе
                    $lastPlayerTopAchieveQuery = AchievesModel::select([AchievesModel::EVENT_PERIOD_FIELD])
                        . ORM::where(AchievesModel::EVENT_TYPE_FIELD, '=', AchievesModel::TOP_TYPE)
                        . ORM::andWhere(
                            AchievesModel::COMMON_ID_FIELD,
                            '=',
                            $player[PlayerModel::COMMON_ID_FIELD],
                            true
                        )
                        . ORM::orderBy(AchievesModel::ID_FIELD, false)
                        . ORM::limit(1);

                    if (DB::queryValue($lastPlayerTopAchieveQuery) != $period) {
                        print 'Inserting ' . print_r($player, true);
                        if ($newId = AchievesModel::add(
                            [
                                AchievesModel::COMMON_ID_FIELD => $player[PlayerModel::COMMON_ID_FIELD],
                                AchievesModel::EVENT_TYPE_FIELD => AchievesModel::TOP_TYPE,
                                AchievesModel::EVENT_PERIOD_FIELD => $period,
                                AchievesModel::EVENT_VALUE_FIELD => $player[PlayerModel::RATING_FIELD],
                                AchievesModel::DATE_ACHIEVED_FIELD => date('Y-m-d H:i:s'),
                                AchievesModel::WORD_FIELD => '',
                                AchievesModel::IS_ACTIVE_FIELD => 1,
                                AchievesModel::REWARD_FIELD => MonetizationService::REWARD[$period],
                                AchievesModel::INCOME_FIELD => MonetizationService::INCOME[$period],
                            ]
                        )) {
                            // Ставим игроку is_active=0 для других его топов
                            AchievesModel::setParamMass(
                                AchievesModel::IS_ACTIVE_FIELD,
                                new ORM(0),
                                [
                                    [
                                        'field_name' => AchievesModel::ID_FIELD,
                                        'condition' => BaseModel::CONDITIONS['!='],
                                        'value' => $newId,
                                        'raw' => true,
                                    ],
                                    [
                                        'field_name' => AchievesModel::EVENT_TYPE_FIELD,
                                        'condition' => BaseModel::CONDITIONS['='],
                                        'value' => AchievesModel::TOP_TYPE,
                                        'raw' => false,
                                    ],
                                    [
                                        'field_name' => AchievesModel::EVENT_PERIOD_FIELD,
                                        'condition' => BaseModel::CONDITIONS['in'],
                                        'value' => "('". implode("', '", array_keys(self::TOP_PARAMS)) . "')",
                                        'raw' => true,
                                    ],
                                    [
                                        'field_name' => AchievesModel::IS_ACTIVE_FIELD,
                                        'condition' => BaseModel::CONDITIONS['='],
                                        'value' => 1,
                                        'raw' => true,
                                    ],
                                    [
                                        'field_name' => AchievesModel::COMMON_ID_FIELD,
                                        'condition' => BaseModel::CONDITIONS['='],
                                        'value' => $player[PlayerModel::COMMON_ID_FIELD],
                                        'raw' => true,
                                    ],
                                ]
                            );

                            if($period !== AchievesModel::DAY_PERIOD) {
                                // Выключаем все активные топы с рейтингом != текущему для ТОП 1,2,3
                                AchievesModel::setParamMass(
                                    AchievesModel::IS_ACTIVE_FIELD,
                                    new ORM(0),
                                    [
                                        [
                                            'field_name' => AchievesModel::EVENT_VALUE_FIELD,
                                            'condition' => BaseModel::CONDITIONS['!='],
                                            'value' => $player[PlayerModel::RATING_FIELD],
                                            'raw' => true,
                                        ],
                                        [
                                            'field_name' => AchievesModel::EVENT_TYPE_FIELD,
                                            'condition' => BaseModel::CONDITIONS['='],
                                            'value' => AchievesModel::TOP_TYPE,
                                            'raw' => false,
                                        ],
                                        [
                                            'field_name' => AchievesModel::EVENT_PERIOD_FIELD,
                                            'condition' => BaseModel::CONDITIONS['='],
                                            'value' => $period,
                                            'raw' => false,
                                        ],
                                        [
                                            'field_name' => AchievesModel::IS_ACTIVE_FIELD,
                                            'condition' => BaseModel::CONDITIONS['='],
                                            'value' => 1,
                                            'raw' => true,
                                        ],
                                    ]
                                );
                            } else {
                                // Для ТОП 10 делаем неактивными всех, у кого рейтинг
                                $top11Rows = DB::queryArray(ORM::select(['*'], AchievesModel::TABLE_NAME)
                                    . ORM::where(AchievesModel::IS_ACTIVE_FIELD,'=', 1, true)
                                    . ORM::andWhere(AchievesModel::EVENT_TYPE_FIELD,'=', AchievesModel::TOP_TYPE)
                                    . ORM::andWhere(AchievesModel::EVENT_PERIOD_FIELD, '=', AchievesModel::DAY_PERIOD)
                                    . ORM::orderBy(AchievesModel::EVENT_VALUE_FIELD, false)
                                    ) ?: [];

                                $currentRating = 0;
                                $numRatingChanges = 0;
                                foreach($top11Rows as $row) {
                                    if ($numRatingChanges >= 7) {
                                        AchievesModel::setParam(
                                            $row[AchievesModel::ID_FIELD],
                                            AchievesModel::IS_ACTIVE_FIELD,
                                            0,
                                            true
                                        );

                                        continue;
                                    }

                                    if($row[AchievesModel::EVENT_VALUE_FIELD] != $currentRating) {
                                        $currentRating = $row[AchievesModel::EVENT_VALUE_FIELD];
                                        $numRatingChanges++;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }


/*
        PlayerModel::getTopPlayers(1);
        PlayerModel::getTopPlayers(2);
        PlayerModel::getTopPlayers(3);
        PlayerModel::getTopPlayers(4, 10);
*/


    }
}

TopChecker::Run();
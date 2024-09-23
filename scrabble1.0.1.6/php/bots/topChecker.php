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
            $topPlayersNow = CommonIdRatingModel::getTopPlayers(...$params);

            print_r($topPlayersNow);

            foreach($topPlayersNow as $top) { // Получили разбивку по номеру ТОП
                foreach ($top as $player) { // Получили игроков в данном ТОПе
                    $lastPlayerTopAchieveQuery = AchievesModel::select([AchievesModel::EVENT_PERIOD_FIELD])
                        . ORM::where(AchievesModel::EVENT_TYPE_FIELD, '=', AchievesModel::TOP_TYPE)
                        . ORM::andWhere(
                            AchievesModel::COMMON_ID_FIELD,
                            '=',
                            $player[CommonIdRatingModel::COMMON_ID_FIELD],
                            true
                        )
                        . ORM::andWhere(AchievesModel::IS_ACTIVE_FIELD, '=', 1, true)
                        . ORM::orderBy(AchievesModel::ID_FIELD, false)
                        . ORM::limit(1);

                    if (DB::queryValue($lastPlayerTopAchieveQuery) != $period) {
                        print 'Inserting ' . print_r($player, true);
                        if ($newId = AchievesModel::add(
                            [
                                AchievesModel::COMMON_ID_FIELD => $player[CommonIdRatingModel::COMMON_ID_FIELD],
                                AchievesModel::EVENT_TYPE_FIELD => AchievesModel::TOP_TYPE,
                                AchievesModel::EVENT_PERIOD_FIELD => $period,
                                AchievesModel::EVENT_VALUE_FIELD => $player[CommonIdRatingModel::RATING_FIELD_PREFIX . CommonIdRatingModel::ERUDIT],
                                AchievesModel::DATE_ACHIEVED_FIELD => date('Y-m-d H:i:s'),
                                AchievesModel::WORD_FIELD => '',
                                AchievesModel::IS_ACTIVE_FIELD => 1,
                                AchievesModel::REWARD_FIELD => MonetizationService::REWARD[$period],
                                AchievesModel::INCOME_FIELD => MonetizationService::INCOME[$period],
                            ]
                        )) {
                            // Начисляем reward
                            BalanceModel::changeBalance($player[CommonIdRatingModel::COMMON_ID_FIELD], MonetizationService::REWARD[$period], AchievesModel::getDescription(AchievesModel::TOP_TYPE, $period));
                            // todo начислить income за первый час

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
                                        'value' => $player[CommonIdRatingModel::COMMON_ID_FIELD],
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
                                // Для ТОП 10 делаем неактивными всех, у кого рейтинг соответствует топ11 и ниже
                                $top11Rows = DB::queryArray( $query =
                                    ORM::select(
                                        [
                                            AchievesModel::ID_FIELD,
                                            AchievesModel::COMMON_ID_FIELD,
                                            CommonIdRatingModel::select(
                                                [CommonIdRatingModel::RATING_FIELD_PREFIX . CommonIdRatingModel::ERUDIT],
                                                true,
                                                ORM::where(
                                                    CommonIdRatingModel::COMMON_ID_FIELD,
                                                    '=',
                                                    AchievesModel::COMMON_ID_FIELD,
                                                    true
                                                ),
                                                'rating'
                                            )
                                        ],
                                        AchievesModel::TABLE_NAME
                                    )
                                    . ORM::where(AchievesModel::IS_ACTIVE_FIELD,'=', 1, true)
                                    . ORM::andWhere(AchievesModel::EVENT_TYPE_FIELD,'=', AchievesModel::TOP_TYPE)
                                    . ORM::andWhere(AchievesModel::EVENT_PERIOD_FIELD, '=', AchievesModel::DAY_PERIOD)
                                    . ORM::orderBy('rating', false)
                                    ) ?: [];

                                print $query;
                                $currentRating = 0;
                                $numRatingChanges = 0;
                                foreach($top11Rows as $row) {
                                    if($row['rating'] != $currentRating) {
                                        $currentRating = $row['rating'];
                                        $numRatingChanges++;
                                    }

                                    if ($numRatingChanges >= 8) {
                                        AchievesModel::setParam(
                                            $row[AchievesModel::ID_FIELD],
                                            AchievesModel::IS_ACTIVE_FIELD,
                                            0,
                                            true
                                        );

                                        continue;
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
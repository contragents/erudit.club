<?php

include_once(__DIR__ . '/../autoload.php');

class TopChecker
{
    const TOP_PARAMS = [
        AchievesModel::YEAR_PERIOD => [1, null],
        AchievesModel::MONTH_PERIOD => [2, null],
        AchievesModel::WEEK_PERIOD => [3, null],
        AchievesModel::DAY_PERIOD => [4, 10],
    ];

    /*
     * Раздаем позиции ТОП, начисляем income за каждый час
     */
    public static function Run()
    {
        try {
            foreach ([Game::ERUDIT, Game::SCRABBLE, BaseModel::ALL_GAMES] as $gameName) {
                T::setLangGame(T::GAME_MODE_LANG[$gameName], $gameName);

                self::processIncomes($gameName);
            }
        } catch (Throwable $e) {
            print $e->__toString();
        }

        foreach ([Game::ERUDIT, Game::SCRABBLE] as $gameName) {
            T::setLangGame(T::GAME_MODE_LANG[$gameName], $gameName);

            $gameNameId = BaseModel::GAME_IDS[$gameName];

            foreach (self::TOP_PARAMS as $period => $params) {
                $topPlayersNow = CommonIdRatingModel::getTopPlayers(...array_merge([$gameName], $params));

                print_r($topPlayersNow);

                foreach ($topPlayersNow as $top) { // Получили разбивку по номеру ТОП
                    foreach ($top as $player) { // Получили игроков в данном ТОПе
                        $lastPlayerTopAchieveQuery = AchievesModel::select([AchievesModel::EVENT_PERIOD_FIELD])
                            . ORM::where(AchievesModel::EVENT_TYPE_FIELD, '=', AchievesModel::TOP_TYPE)
                            . ORM::andWhere(
                                AchievesModel::COMMON_ID_FIELD,
                                '=',
                                $player[CommonIdRatingModel::COMMON_ID_FIELD],
                                true
                            )
                            . ORM::andWhere(AchievesModel::GAME_NAME_ID_FIELD, '=', $gameNameId)
                            . ORM::andWhere(AchievesModel::IS_ACTIVE_FIELD, '=', 1, true)
                            . ORM::orderBy(AchievesModel::ID_FIELD, false)
                            . ORM::limit(1);

                        // Проверяем, поменялся ли тип ТОПа для игрока
                        if (DB::queryValue($lastPlayerTopAchieveQuery) != $period) {
                            print 'Inserting ' . print_r($player, true);
                            if ($newId = AchievesModel::add(
                                [
                                    AchievesModel::COMMON_ID_FIELD => $player[CommonIdRatingModel::COMMON_ID_FIELD],
                                    AchievesModel::EVENT_TYPE_FIELD => AchievesModel::TOP_TYPE,
                                    AchievesModel::EVENT_PERIOD_FIELD => $period,
                                    AchievesModel::EVENT_VALUE_FIELD => $player[CommonIdRatingModel::RATING_FIELD_PREFIX . $gameName],
                                    AchievesModel::DATE_ACHIEVED_FIELD => date('Y-m-d H:i:s'),
                                    AchievesModel::WORD_FIELD => '',
                                    AchievesModel::IS_ACTIVE_FIELD => 1,
                                    AchievesModel::REWARD_FIELD => MonetizationService::REWARD[$period],
                                    AchievesModel::INCOME_FIELD => MonetizationService::INCOME[$period],
                                    AchievesModel::GAME_NAME_ID_FIELD => $gameNameId,
                                ]
                            )) {
                                // Начисляем reward
                                BalanceModel::changeBalance(
                                    $player[CommonIdRatingModel::COMMON_ID_FIELD],
                                    MonetizationService::REWARD[$period],
                                    AchievesModel::getDescription(AchievesModel::TOP_TYPE, $period, $gameName),
                                    IncomeHistoryModel::TYPE_IDS[IncomeHistoryModel::ACHIEVE_TYPE],
                                    $newId
                                );

                                // начисляем income за первый час
                                IncomeModel::changeIncome(
                                    $player[CommonIdRatingModel::COMMON_ID_FIELD],
                                    MonetizationService::INCOME[$period],
                                    AchievesModel::getDescription(AchievesModel::TOP_TYPE, $period, $gameName),
                                    IncomeHistoryModel::TYPE_IDS[IncomeHistoryModel::ACHIEVE_TYPE],
                                    $newId
                                );

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
                                            'field_name' => AchievesModel::GAME_NAME_ID_FIELD,
                                            'condition' => BaseModel::CONDITIONS['='],
                                            'value' => $gameNameId,
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

                                if ($period !== AchievesModel::DAY_PERIOD) {
                                    // Выключаем все активные топы с рейтингом != текущему для ТОП 1,2,3

                                    // Для ТОП 1-3 делаем неактивными всех, у кого рейтинг отличается от текущего игрока
                                    $topRows = DB::queryArray(
                                        $query = ORM::select(
                                                [
                                                    AchievesModel::ID_FIELD,
                                                    AchievesModel::COMMON_ID_FIELD,
                                                    CommonIdRatingModel::select(
                                                        [CommonIdRatingModel::RATING_FIELD_PREFIX . $gameName],
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
                                            . ORM::where(AchievesModel::IS_ACTIVE_FIELD, '=', 1, true)
                                            . ORM::andWhere(AchievesModel::GAME_NAME_ID_FIELD, '=', $gameNameId, true)
                                            . ORM::andWhere(
                                                AchievesModel::EVENT_TYPE_FIELD,
                                                '=',
                                                AchievesModel::TOP_TYPE
                                            )
                                            . ORM::andWhere(
                                                AchievesModel::EVENT_PERIOD_FIELD,
                                                '=',
                                                $period
                                            )
                                            . ORM::orderBy('rating', false)
                                    ) ?: [];

                                    print $query;

                                    foreach ($topRows as $row) {
                                        if ($row['rating'] != $player[CommonIdRatingModel::RATING_FIELD_PREFIX . $gameName]) {
                                            AchievesModel::setParam(
                                                $row[AchievesModel::ID_FIELD],
                                                AchievesModel::IS_ACTIVE_FIELD,
                                                0,
                                                true
                                            );
                                        }
                                    }
                                } else {
                                    // Для ТОП 10 делаем неактивными всех, у кого рейтинг соответствует топ11 и ниже
                                    $top11Rows = DB::queryArray(
                                        $query =
                                            ORM::select(
                                                [
                                                    AchievesModel::ID_FIELD,
                                                    AchievesModel::COMMON_ID_FIELD,
                                                    CommonIdRatingModel::select(
                                                        [CommonIdRatingModel::RATING_FIELD_PREFIX . $gameName],
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
                                            . ORM::where(AchievesModel::IS_ACTIVE_FIELD, '=', 1, true)
                                            . ORM::andWhere(AchievesModel::GAME_NAME_ID_FIELD, '=', $gameNameId, true)
                                            . ORM::andWhere(
                                                AchievesModel::EVENT_TYPE_FIELD,
                                                '=',
                                                AchievesModel::TOP_TYPE
                                            )
                                            . ORM::andWhere(
                                                AchievesModel::EVENT_PERIOD_FIELD,
                                                '=',
                                                AchievesModel::DAY_PERIOD
                                            )
                                            . ORM::orderBy('rating', false)
                                    ) ?: [];

                                    print $query;
                                    $currentRating = 0;
                                    $numRatingChanges = 0;
                                    foreach ($top11Rows as $row) {
                                        if ($row['rating'] != $currentRating) {
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
                        } else { // Тип ТОПа не поменялся - нужно проверить, вдруг текущий рейтинг вырос (тогда заменить на новый)
                            $achieveModel = Record::find()
                                ->where([
                                            Record::COMMON_ID_FIELD => $player[CommonIdRatingModel::COMMON_ID_FIELD],
                                            Record::IS_ACTIVE_FIELD => true,
                                            Record::EVENT_TYPE_FIELD => Record::TOP_TYPE,
                                            Record::EVENT_PERIOD_FIELD => $period,
                                            Record::GAME_NAME_ID_FIELD => $gameNameId,
                                        ])
                                ->one();

                            if (!($achieveModel->_event_value ?? null)) {
                                continue;
                            }

                            $currentPlayerRating
                                = CommonIdRatingModel::getOneO($player[CommonIdRatingModel::COMMON_ID_FIELD])
                                ->rating ?? null;

                            if ($currentPlayerRating > $achieveModel->_event_value) {
                                $achieveModel->_event_value = $currentPlayerRating;
                                $achieveModel->save();
                            }
                        }
                    }
                }
            }
        }
    }

    private static function processIncomes($gameName)
    {
        $gameAchieves = AchievesModel::getActive($gameName);

        foreach ($gameAchieves as $achieve) {
            if (IncomeModel::changeIncome(
                $achieve[AchievesModel::COMMON_ID_FIELD],
                $achieve[AchievesModel::EVENT_TYPE_FIELD] !== AchievesModel::PATREON_TYPE // Для патронов начисляем 1/24 от указанного значения (там за сутки)
                    ? $achieve[AchievesModel::INCOME_FIELD]
                    : $achieve[AchievesModel::INCOME_FIELD] / 24,
                $achieve[AchievesModel::EVENT_TYPE_FIELD] !== AchievesModel::PATREON_TYPE // Для патронов отдельное описание
                    ? AchievesModel::getDescription(
                    $achieve[AchievesModel::EVENT_TYPE_FIELD],
                    $achieve[AchievesModel::EVENT_PERIOD_FIELD],
                    $gameName
                )
                    : 'patron income',
                IncomeHistoryModel::TYPE_IDS[IncomeHistoryModel::ACHIEVE_TYPE],
                $achieve[AchievesModel::ID_FIELD]
            )) {
                print("Income processed: " . var_export($achieve, true) . "\n");
            }
        }
    }
}

TopChecker::Run();
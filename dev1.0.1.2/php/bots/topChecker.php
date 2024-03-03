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
                        AchievesModel::add(
                            [
                                AchievesModel::COMMON_ID_FIELD => $player[PlayerModel::COMMON_ID_FIELD],
                                AchievesModel::EVENT_TYPE_FIELD => AchievesModel::TOP_TYPE,
                                AchievesModel::EVENT_PERIOD_FIELD => $period,
                                AchievesModel::EVENT_VALUE_FIELD => $player[PlayerModel::RATING_FIELD],
                                AchievesModel::DATE_ACHIEVED_FIELD => date('Y-m-d H:i:s'),
                                AchievesModel::WORD_FIELD => '',
                            ]
                        );
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
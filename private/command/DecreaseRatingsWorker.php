<?php

include_once __DIR__ . '/../../autoload.php';

class DecreaseRatingsWorker
{
    const DAYS_NOT_PLAYED = 7;
    const RATING_CHARGE = -7;
    const RATING_FIELD = CommonIdRatingModel::RATING_FIELD_PREFIX . Game::ERUDIT;
    const LIMIT_PLAYERS = 1000;
    const RATING_DECREASE_USER_ID = UserModel::SYSTEM_ACCOUNTS['Rating_decrease'];


    public function run()
    {
        self::decreaseRatings();
    }

    private static function decreaseRatings()
    {
        $daysNotPlayed = self::DAYS_NOT_PLAYED;

        $playersArrQuery = RatingHistoryModel::select(
                [
                    RatingHistoryModel::COMMON_ID_FIELD . ' as ' . CommonIdRatingModel::COMMON_ID_FIELD,
                    CommonIdRatingModel::select(
                        [CommonIdRatingModel::RATING_FIELD_PREFIX . Game::ERUDIT],
                        true,
                        ORM::where(
                            CommonIdRatingModel::COMMON_ID_FIELD,
                            '=',
                            RatingHistoryModel::COMMON_ID_FIELD,
                            true
                        ),
                        self::RATING_FIELD
                    )
                ]
            )
            . ORM::groupBy([RatingHistoryModel::COMMON_ID_FIELD])
            . ORM::having(
                ORM::getWhereCondition(
                    ORM::agg(ORM::MAX, RatingHistoryModel::CREATED_AT_FIELD),
                    '<',
                    "UNIX_TIMESTAMP() - $daysNotPlayed * 24 * 60 * 60",
                    true
                )
            )
            . ORM::orderBy(self::RATING_FIELD, false)
            . ORM::limit(self::LIMIT_PLAYERS);

        // print $playersArrQuery; exit;

        $playersArr = DB::queryArray($playersArrQuery);

        foreach ($playersArr as $player) {
            $commonIdRatingModel = CommonIdRatingModel::new($player);
            print PHP_EOL . $commonIdRatingModel->_id . PHP_EOL;

            // занести в game_stats таблицу
            $newGameId = Cache::incr(Queue::GAMES_COUNTER);

            if ($newGameId == 1) {
                $newGameId = GamesModel::getLastID() + 1;
                Cache::set(Queue::GAMES_COUNTER, $newGameId);
            }

            GameStatsModel::add(
                $queryParams =
                    [
                        GameStatsModel::GAME_ID_FIELD => $newGameId + GameController::GAME_ID_BASE_INC,
                        GameStatsModel::PLAYERS_NUM_FIELD => 2,
                        GameStatsModel::GAME_ENDED_AT_FIELD => date('U'),
                        GameStatsModel::WINNER_ID_FIELD => self::RATING_DECREASE_USER_ID,
                        GameStatsModel::GAME_NAME_ID => BaseModel::GAME_IDS[Game::ERUDIT],
                    ]
                    + [
                        '1_player_id' => $commonIdRatingModel->_id,
                        '1_player_rating_delta' => self::RATING_CHARGE,
                        '1_player_old_rating' => $commonIdRatingModel->_rating_erudit,
                    ]
                    +
                    [
                        '2_player_id' => self::RATING_DECREASE_USER_ID,
                        '2_player_rating_delta' => 0,
                        '2_player_old_rating' => 0,
                    ]
            );

            if (!DB::insertID()) {
                print "STATS_FAILED" . PHP_EOL . $queryParams;

                continue;
            }

            // занести в RatingHistoryModel
            print RatingHistoryModel::addRatingChange(
                $commonIdRatingModel->_id,
                $commonIdRatingModel->_rating_erudit,
                $commonIdRatingModel->_rating_erudit + self::RATING_CHARGE,
                false,
                $newGameId,
                Game::ERUDIT
            );

            // занести в CommonIdRatingmodel
            var_export(CommonIdRatingModel::changeUserRating(
                $commonIdRatingModel->_id,
                $commonIdRatingModel->_rating_erudit + self::RATING_CHARGE,
                Game::ERUDIT
            ));
        }
    }
}

(new DecreaseRatingsWorker())->run();
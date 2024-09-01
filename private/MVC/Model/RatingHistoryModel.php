<?php

class RatingHistoryModel extends BaseModel
{
    const TABLE_NAME = 'rating_history';
    const GAME_ID_FIELD = 'game_id';
    const GAME_NAME_ID_FIELD = 'game_name_id';
    const IS_WINNER_FIELD = 'is_winner';
    const RATING_BEFORE_FIELD = 'rating_before';
    const RATING_AFTER_FIELD = 'rating_after';

    public static function addRatingChange(
        int $commonId,
        int $oldRating,
        int $newRating,
        bool $isWinner,
        int $gameId,
        string $gameName = self::ERUDIT
    ) {
        return self::add(
            [
                self::COMMON_ID_FIELD => $commonId,
                self::RATING_BEFORE_FIELD => $oldRating,
                self::RATING_AFTER_FIELD => $newRating,
                self::IS_WINNER_FIELD => $isWinner ? 1 : 0,
                self::GAME_ID_FIELD => $gameId,
                self::GAME_NAME_ID_FIELD => self::GAME_IDS[$gameName]
            ]
        );
    }
}
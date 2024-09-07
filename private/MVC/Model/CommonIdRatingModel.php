<?php

class CommonIdRatingModel extends BaseModel
{
    const TABLE_NAME = 'common_id_rating';
    const COMMON_ID_FIELD = self::ID_FIELD;

    const RATING_FIELD_PREFIX = 'rating_';

    const INITIAL_RATING = 1700;

    public static function changeUserRating(int $commonId, int $newRating, string $gameName = self::ERUDIT): bool
    {
        if (self::update($commonId, [self::RATING_FIELD_PREFIX . $gameName => $newRating])){
            return true;
        } else {
            // 2 options - ratings are equal OR no common_id record present
            if (
                self::exists($commonId)
                && self::getOne($commonId)[self::RATING_FIELD_PREFIX . $gameName] == $newRating
            ) {
                return true;
            } else {
                self::add([self::ID_FIELD => $commonId, self::RATING_FIELD_PREFIX . $gameName => $newRating]);

                if (
                    self::exists($commonId)
                    && self::getOne($commonId)[self::RATING_FIELD_PREFIX . $gameName] == $newRating
                ) {
                    return true;
                } else {
                    return false;
                }
            }
        }
    }

    public static function getRating($commonId, string $gameName = BaseModel::ERUDIT): int
    {
        return (int)DB::queryValue(
            ORM::select([self::RATING_FIELD_PREFIX . $gameName], self::TABLE_NAME)
            . ORM::where(self::COMMON_ID_FIELD, '=', $commonId, true)
        );
    }

}
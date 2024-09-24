<?php

class CommonIdRatingModel extends BaseModel
{
    const TABLE_NAME = 'common_id_rating';
    const COMMON_ID_FIELD = self::ID_FIELD;

    const RATING_FIELD_PREFIX = 'rating_';

    const INITIAL_RATING = 1700;

    public static function changeUserRating(int $commonId, int $newRating, string $gameName = Game::SCRABBLE): bool
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

    public static function getRating(int $commonId, string $gameName): int
    {
        return (int)DB::queryValue(
            ORM::select([self::RATING_FIELD_PREFIX . $gameName], self::TABLE_NAME)
            . ORM::where(self::COMMON_ID_FIELD, '=', $commonId, true)
        );
    }

    public static function getTopByRating(int $rating, string $gameName = Game::SCRABBLE): int
    {
        $topQuery = ORM::select(
            ['count(1) + 1 as top'],
            "(select 1 as num from " . self::TABLE_NAME . " where rating_" . $gameName . " > $rating group by rating_" . $gameName . ") dd"
        );

        return (int)DB::queryValue($topQuery);
    }

    /**
     * @param int $top Номер в рейтинге - 1,2,3 ...
     * @param int $topMax Максимальный номер в рейтинге (для поиска ТОП10 задать 4,10)
     * @return array
     */
    public static function getTopPlayers(string $gameName, int $top, ?int $topMax = null): array
    {
        if ($top >= ($topMax ?? PlayerModel::TOP_10)) {
            return [];
        }

        $ratingField = self::RATING_FIELD_PREFIX . $gameName;

        $topRatingsQuery = self::select([$ratingField])
            . ORM::where($ratingField, '>', PlayerModel::MIN_TOP_RATING, true)
            . ORM::groupBy([$ratingField])
            . ORM::orderBy($ratingField, false)
            . ORM::limit($topMax ? $topMax - $top + 1 : 1, $top - 1);

        $topRatings = DB::queryArray($topRatingsQuery) ?: [];

        $resultRatings = [];

        for ($i = $top; $i <= $topMax ?: $top; $i++) {
            $currentRating = $topRatings[$i - $top][$ratingField] ?? false;
            if (!$currentRating) {
                break;
            }

            $resultRatings[$i] = DB::queryArray(
                self::select([self::COMMON_ID_FIELD, $ratingField])
                . ORM::where($ratingField, '=', $currentRating, true)
            ) ?: [];
        }

        return $resultRatings;
    }
}
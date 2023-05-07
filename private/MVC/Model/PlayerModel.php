<?php

class PlayerModel extends BaseModel
{
    const TABLE_NAME = 'players';

    public static function getCommonIdFromCookie(string $cookie)
    {
        // todo remove after model
        $commonIDQuery = "SELECT common_id FROM players WHERE cookie = '$cookie' LIMIT 1";
        $commonIDQuery = ORM::select(['common_id'], self::TABLE_NAME)
            . ORM::where('cookie', '=', $cookie)
            . ORM::limit(1);

        return DB::queryValue($commonIDQuery);
    }

    public static function getCommonIdFromUserId($userId)
    {
        // todo remove after model
        /*$commonIDQuery = "SELECT common_id FROM players
                                WHERE user_id = $userId
                                AND common_id IS NOT NULL 
                                LIMIT 1";*/

        $commonIDQuery = ORM::select(['common_id'], self::TABLE_NAME)
            . ORM::where('user_id', '=', $userId, true)
            . ORM::andWhere('common_id', 'IS', 'NOT NULL', true)
            . ORM::limit(1);

        return DB::queryValue($commonIDQuery);
    }
}
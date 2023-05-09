<?php

class UserModel extends BaseModel
{
    const TABLE_NAME = 'users';

    public static function updateUrl(int $commonID, string $url): bool {

        // todo remove after model tested
        $avatarUpdateQuery = "UPDATE users
                SET 
                    avatar_url = '" . DB::escapeString($url) . "'
                WHERE 
                    id = $commonID";

        $avatarUpdateQuery = ORM::update(self::TABLE_NAME)
            .ORM::insertFields(['avatar_url'=>DB::escapeString($url)])
            .ORM::where('id','=',$commonID, true);

        return DB::queryInsert($avatarUpdateQuery) ? true : false;
    }

    public static function getNameByCommonId(int $commonId){
        return self::getOne($commonId)['name'] ?? false;
    }
}
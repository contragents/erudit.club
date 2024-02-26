<?php

class UserModel extends BaseModel
{
    const TABLE_NAME = 'users';
    const AVATAR_URL_FIELD = 'avatar_url';

    public static function updateUrl(int $commonID, string $url): bool {
        $avatarUpdateQuery = ORM::update(self::TABLE_NAME)
            . ORM::set(['field' => self::AVATAR_URL_FIELD, 'value' => DB::escapeString($url)])
            .ORM::where('id','=',$commonID, true);

        $res = DB::queryInsert($avatarUpdateQuery);

        return $res;
    }

    public static function getNameByCommonId(int $commonId) {
        return self::getOne($commonId)['name'] ?? false;
    }
}
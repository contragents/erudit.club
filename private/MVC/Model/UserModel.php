<?php

class UserModel extends BaseModel
{
    const TABLE_NAME = 'users';
    const AVATAR_URL_FIELD = 'avatar_url';
    const COMMON_ID_FIELD = self::ID_FIELD;

    public static function updateUrl(int $commonId, string $url): bool {
        // Добавим версию для перекеширования
        if (strpos($url, '?') === false) {
            $url .= ('?ver=' . date('U'));
        }

        if (self::existsCustom([self::COMMON_ID_FIELD => $commonId])) {
            $avatarUpdateQuery = ORM::update(self::TABLE_NAME)
                . ORM::set(
                    [
                        ['field' => self::AVATAR_URL_FIELD, 'value' => DB::escapeString($url)],
                        ['field' => self::UPDATED_AT_FIELD, 'value' => new ORM('CURRENT_TIMESTAMP()')]
                    ]
                )
                . ORM::where('id', '=', $commonId, true);

            $res = DB::queryInsert($avatarUpdateQuery);
        } else {
            self::add(
                [
                    self::COMMON_ID_FIELD => $commonId,
                    self::AVATAR_URL_FIELD => $url,
                ]
            );

            $res = self::exists($commonId);
        }

        return (bool)$res;
    }

    public static function getNameByCommonId(int $commonId) {
        return self::getOne($commonId)['name'] ?? false;
    }
}
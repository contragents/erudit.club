<?php

class UserModel extends BaseModel
{
    const TABLE_NAME = 'users';
    const AVATAR_URL_FIELD = 'avatar_url';
    const COMMON_ID_FIELD = self::ID_FIELD;
    const NAME_FIELD = 'name';
    const UPDATABLE_FIELDS = [self::NAME_FIELD, self::AVATAR_URL_FIELD];

    public static function updateUrl(int $commonId, string $url): bool {
        // Добавим версию для перекеширования
        if (strpos($url, '?') === false) {
            $url .= ('?ver=' . date('U'));
        }

        return self::updateFieldWithCreate($commonId, self::AVATAR_URL_FIELD, $url);
    }

    public static function updateNickname(int $commonId, string $nickName): bool {
        return self::updateFieldWithCreate($commonId, self::NAME_FIELD, $nickName);
    }

    private static function updateFieldWithCreate(int $commonId, string $field, string $value): bool {
        if (!in_array($field, self::UPDATABLE_FIELDS)) {
            return false;
        }

        if (self::existsCustom([self::COMMON_ID_FIELD => $commonId])) {
            $updateQuery = ORM::update(self::TABLE_NAME)
                . ORM::set(
                    [
                        ['field' => $field, 'value' => DB::escapeString($value)],
                    ]
                )
                . ORM::where(self::COMMON_ID_FIELD, '=', $commonId, true);

            $res = DB::queryInsert($updateQuery);
        } else {
            self::add(
                [
                    self::COMMON_ID_FIELD => $commonId,
                    $field => $value,
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
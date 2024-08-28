<?php

class TgUserModel extends BaseModel
{
    const TABLE_NAME = 'tg_user';
    const TG_ID_FIELD = 'tg_id';
    const DATA_FIELD = 'data';

    const BOT_TOKEN_CONFIG_KEY = [T::RU_LANG => 'BOT_TOKEN', T::EN_LANG => 'SCRABBLE_BOT_TOKEN'];

    public static function refresh(array $tgUser): bool
    {
        $data = json_encode($tgUser, JSON_UNESCAPED_UNICODE);

        if ($id = self::getOneCustom(self::TG_ID_FIELD, $tgUser['user']['id'], true)[self::ID_FIELD] ?? false) {
            return self::update(
                $id,
                ['field' => self::DATA_FIELD, 'value' => DB::escapeString($data)]
            );
        } else {
            $common_id = PlayerModel::getOneCustom(
                    PlayerModel::COOKIE_FIELD,
                    $tgUser['user']['id']
                )[self::COMMON_ID_FIELD] ?? false;
            if ($common_id) {
                return self::add(
                    [
                        self::TG_ID_FIELD => $tgUser['user']['id'],
                        self::COMMON_ID_FIELD => $common_id,
                        self::DATA_FIELD => $data
                    ]
                );
            }
        }

        return false;
    }
}

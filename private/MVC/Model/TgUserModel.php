<?php

class TgUserModel extends BaseModel
{
    const TABLE_NAME = 'tg_user';
    const TG_ID_FIELD = 'tg_id';
    const DATA_FIELD = 'data';

    const BOT_TOKEN_CONFIG_KEY = [T::RU_LANG => 'BOT_TOKEN', T::EN_LANG => 'SCRABBLE_BOT_TOKEN'];

    public static function checkUserDataUnsafe($data): bool
    {
        $botToken = Config::$envConfig[self::BOT_TOKEN_CONFIG_KEY[T::$lang]];
        $hash = false;
        $newData = [];

        foreach ($data as $key => $value) {
            if ($key == 'hash') {
                $hash = $value;
                continue;
            }
            if ($key == 'tg_authorize') {
                continue;
            }
            $newData += [$key => $value];
        }

        if (!$hash) {
            return false;
        }

        ksort($newData);

        $arrayData = [];
        foreach ($newData as $k => $v) {
            array_push($arrayData, "$k=" . $v);
        }

        $arrayData = implode("\n", $arrayData);

        $secret_key = hash_hmac('sha256', $botToken, "WebAppData", true);

        if (hash_hmac('sha256', $arrayData, $secret_key) == $hash) {
           return true;
        } else {
            return false;
        }
    }

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

    public static function tgInitDataDecode($data): array
    {
        $tgUser = $data;
        unset($tgUser['tg_authorize']);
        $tgUser['user'] = json_decode($tgUser['user'], true);

        return $tgUser;
    }
}
<?php

/**
 * @property int $_id
 * @property int $_common_id
 * @property int $_tg_id
 * @property array $_data
 **/

class TgUserModel extends BaseModel
{
    const TABLE_NAME = 'tg_user';
    const TG_ID_FIELD = 'tg_id';
    const DATA_FIELD = 'data';

    const BOT_TOKEN_CONFIG_KEY = [T::RU_LANG => 'BOT_TOKEN', T::EN_LANG => 'SCRABBLE_BOT_TOKEN'];

    // public ?int $_id = null; // наследует
    public ?int $_tg_id = null;
    public ?int $_common_id = null;
    public array $_data = [];

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

    public static function getOneByCommonIdO(int $commonId): ?self
    {
        $query = self::select(['*'])
            . ORM::where(self::COMMON_ID_FIELD, '=', $commonId, true)
            . ORM::orderBy(self::UPDATED_AT_FIELD, false)
            . ORM::limit(1);

        $row = DB::queryArray($query)[0] ?? null;

        return $row
            ? self::arrayToObject($row)
            : null;
    }
}

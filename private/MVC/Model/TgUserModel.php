<?php

/**
 * @property int $_id
 * @property int $_common_id
 * @property int $_tg_id
 * @property array $_data
 * @property string $_wallet_address
 **/

class TgUserModel extends BaseModel
{
    const TABLE_NAME = 'tg_user';
    const TG_ID_FIELD = 'tg_id';
    const DATA_FIELD = 'data';
    const WALLET_FIELD = 'wallet_address';

    const BOT_TOKEN_CONFIG_KEY = [T::RU_LANG => 'BOT_TOKEN', T::EN_LANG => 'SCRABBLE_BOT_TOKEN'];

    public ?int $_tg_id = null;
    public ?int $_common_id = null;
    public array $_data = [];
    public ?string $_wallet_address = null;


    /**
     * Находит модель по данным из массива $tgUser и пересохраняет tgUser в БД в виде JSON
     * @param array $tgUser
     * @return bool
     */
    public static function refreshByTgUserArr(array $tgUser): bool
    {
        $tgUserModel = self::getOneCustomO(self::TG_ID_FIELD, $tgUser['user']['id'], true);

        if ($tgUserModel) {
            $tgUserModel->_data = $tgUser;
            return $tgUserModel->save();
        } else {
            $playerModel = PlayerModel::getOneCustomO(PlayerModel::COOKIE_FIELD, $tgUser['user']['id']);
            if ($playerModel) {
                $common_id = $playerModel->_common_id;
                return self::new(
                    [
                        self::TG_ID_FIELD => $tgUser['user']['id'],
                        self::COMMON_ID_FIELD => $common_id,
                        self::DATA_FIELD => $tgUser,
                    ]
                )->save();
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

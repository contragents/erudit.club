<?php

/**
 * @property int $_id
 * @property int $_common_id
 * @property int $_ref_tg_id
 * @property string $_name
 * @property string $_created_at
 **/

class RefModel extends BaseModel
{
    const TABLE_NAME = 'refs';

    const COMMON_ID_FIELD = 'common_id';
    const REF_TG_ID_FIELD = 'ref_tg_id';
    const NAME_FIELD = 'name';

    // public ?int $_id = null; // =common_id наследует
    public ?int $_common_id = null;
    public ?int $_ref_tg_id = null;
    public ?string $_name = null;
    public ?string $_created_at = null;

    public static function addRef(int $commonId, int $refTgId, string $name = ''): bool
    {
        $ref = self::new(
            [
                self::COMMON_ID_FIELD => $commonId,
                self::REF_TG_ID_FIELD => $refTgId,
                self::NAME_FIELD => $name
            ]
        );

        return $ref->save();
    }
}
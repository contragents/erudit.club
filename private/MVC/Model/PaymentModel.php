<?php

/**
 * @property int $_id
 * @property int $_common_id
 * @property float $_summ
 * @property string $_payment_system
 * @property string $_status
 * @property int $_ref_id
 * @property string $_created_at
 * @property string $_updated_at
 *
 **/

class PaymentModel extends BaseModel
{
    const TABLE_NAME = 'payment';

    const COMMON_ID_FIELD = 'common_id';
    const REF_ID_FIELD = 'ref_id';
    const SYSTEM_FIELD = 'payment_system';
    const STATUS_FIELD = 'status';
    const SUMM_FIELD = 'summ';

    const YUMONEY_SYSTEM = 'yumoney';

    const INIT_STATUS = 'init';
    const COMPLETE_STATUS = 'complt';
    const FAIL_STATUS = 'fail';
    const BAD_CONFIRM_STATUS = 'badcnf';

    public ?int $_common_id = null;
    public ?float $_summ = null;
    public string $_payment_system = self::YUMONEY_SYSTEM;
    public string $_status = self::INIT_STATUS;
    public ?int $_ref_id = null;
    public ?string $_created_at = null;
    public ?string $_updated_at = null;
}
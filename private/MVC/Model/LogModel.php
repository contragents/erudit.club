<?php

class LogModel extends BaseModel
{
    const TABLE_NAME = 'log';

    const MESSAGE_FIELD = 'message';
    const CATEGORY_FIELD = 'category';
    const COMMON_ID_FIELD = 'common_id';

    const FIELDS =
        parent::FIELDS +
        [
            self::CREATED_AT_FIELD => self::TYPE_DATE,
            self::CATEGORY_FIELD => self::TYPE_STRING,
            self::MESSAGE_FIELD => self::TYPE_STRING
        ];

    const CATEGORY_QUERY_RESULT_CHECK = 'query_result';
    const CATEGORY_BOT_ERROR = 'bot_error';
    const CATEGORY_RULANG_ERROR = 'lang_error';
    const CATEGORY_SUBMIT_ERROR = 'submit_error';
    const CATEGORY_RECORD_ERROR = 'record_error';
    const CATEGORY_PAYMENT_NOTIFY = 'payment_notification';
    const CATEGORIES = [
        self::CATEGORY_BOT_ERROR,
        self::CATEGORY_RULANG_ERROR,
        self::CATEGORY_SUBMIT_ERROR,
        self::CATEGORY_RECORD_ERROR,
        self::CATEGORY_PAYMENT_NOTIFY
    ];


    public static function add(array $processedFieldsVals)
    {
        // Добавляем в лог common_id текущего игрока
        return parent::add(
            $processedFieldsVals
            + ((!isset($processedFieldsVals[self::COMMON_ID_FIELD]) && Game::$commonID)
                ? [self::COMMON_ID_FIELD => Game::$commonID]
                : []
            )
        );
    }

}
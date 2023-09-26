<?php

class LogModel extends BaseModel
{
    const TABLE_NAME = 'log';

    const MESSAGE_FIELD = 'message';
    const CATEGORY_FIELD = 'category';

    const FIELDS =
        parent::FIELDS +
        [
            self::CREATED_AT_FIELD => self::TYPE_DATE,
            self::CATEGORY_FIELD => self::TYPE_STRING,
            self::MESSAGE_FIELD => self::TYPE_STRING
        ];

    const CATEGORY_BOT_ERROR = 'bot_error';
    const CATEGORY_RULANG_ERROR = 'lang_error';
    const CATEGORIES = [
        self::CATEGORY_BOT_ERROR,
        self::CATEGORY_RULANG_ERROR,
    ];

}
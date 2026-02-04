<?php

/**
 * @property string $_created_at
 * @property string $_message
 * @property string $_category
 * @property int $_common_id
 */

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

    public ?string $_created_at = null;
    public ?string $_message = null;
    public ?string $_category = null;
    public ?int $_common_id = null;

    const CATEGORY_QUERY_RESULT_CHECK = 'query_result';
    const CATEGORY_BOT_ERROR = 'bot_error';
    const CATEGORY_RULANG_ERROR = 'lang_error';
    const CATEGORY_SUBMIT_ERROR = 'submit_error';
    const CATEGORY_RECORD_ERROR = 'record_error';
    const CATEGORY_PAYMENT_NOTIFY = 'payment_notification';
    const CATEGORY_REFERRAL_NOTIFY = 'category_referral';
    const CATEGORY_QUERY_ERROR = 'query_error';
    const CATEGORY_BAD_COMBINATION = 'bad_combination';
    const CATEGORY_SAVE_RATINGS_TEST = 'save_ratings_test';

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

    public static function logQuery(string $query, ?Throwable $e = null)
    {
        if (!DB::isTransactionStarted()) {
            self::add([
                          LogModel::CATEGORY_FIELD => LogModel::CATEGORY_QUERY_ERROR,
                          LogModel::MESSAGE_FIELD => $query . ($e ? ("\n" . $e->__toString()) : ''),
                      ]);
        } else {
            // Если транзакция начата, мы записываем лог в Cache
            Cache::setex(self::CATEGORY_QUERY_ERROR . '|' . $query, 60 * 10, [$query, $e->__toString()]);
        }
    }

}
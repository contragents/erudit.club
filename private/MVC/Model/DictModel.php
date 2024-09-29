<?php

class DictModel extends BaseModel
{
    const TABLE_NAME = 'dict';
    const WORD_FIELD = 'slovo';
    const COMENT_FIELD = 'comment';
    const IS_DELETED_FIELD = 'deleted';
    const LENGTH_FIELD = 'length';
    const LANG_FIELD = 'lng';

    public static function checkWord(string $word) : bool {
        return (bool)DB::queryValue(
            ORM::select(['count(1)'], self::TABLE_NAME)
            . ORM::where(self::WORD_FIELD,'=', $word)
            .ORM::andWhere(self::IS_DELETED_FIELD, '=', 0, true)
            . ORM::limit(1)
        );
        //"SELECT count(1) as cnt FROM " . self::$dictTable . " WHERE slovo='$word' AND deleted = 0 LIMIT 1;");
    }
}
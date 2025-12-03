<?php

/**
 * @property int $_id
 * @property string $_slovo
 * @property string $_comment
 * @property string $_slovar
 * @property bool $_deleted
 * @property int $_length
 * @property int $_lng // Language id 1-Russian, 2-English
 * @property int $_word_score
 **/

class DictModel extends BaseModel
{
    const TABLE_NAME = 'dict';
    const WORD_FIELD = 'slovo';
    const COMENT_FIELD = 'comment';
    const IS_DELETED_FIELD = 'deleted';
    const LENGTH_FIELD = 'length';
    const LANG_FIELD = 'lng';
    const WORD_SCORE_FIELD = 'word_score';

    public ?string $_slovo = null;
    public ?string $_comment = null;
    public ?string $_slovar = null;
    public bool $_deleted = false;
    public ?int $_length = null;
    public ?int $_lng = null;
    public ?int $_word_score = null;

    public static function checkWord(string $word): bool
    {
        return (bool)DB::queryValue(
            ORM::select(['count(1)'], self::TABLE_NAME)
            . ORM::where(self::WORD_FIELD, '=', $word)
            . ORM::andWhere(self::IS_DELETED_FIELD, '=', 0, true)
            . ORM::limit(1)
        );
        //"SELECT count(1) as cnt FROM " . self::$dictTable . " WHERE slovo='$word' AND deleted = 0 LIMIT 1;");
    }
}
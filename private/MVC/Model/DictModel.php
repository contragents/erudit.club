<?php

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

    /** Language id 1-Russian, 2-English */
    public ?int $_lng = null;
    public ?int $_word_score = null;

    const FIND_WORDS_LIMIT = 20;
    const REPLACE = ['*' => '.*', '?' => '.', 'е' => '[её]', 'Е' => '[её]'];

    const TRANSLIT = [
        'А' => 'A',
        'Б' => 'B',
        'В' => 'V',
        'Г' => 'G',
        'Д' => 'D',
        'Е' => 'E',
        'Ё' => 'JE',
        'Ж' => 'ZH',
        'З' => 'Z',
        'И' => 'I',
        'Й' => 'Y',
        'К' => 'K',
        'Л' => 'L',
        'М' => 'M',
        'Н' => 'N',
        'О' => 'O',
        'П' => 'P',
        'Р' => 'R',
        'С' => 'S',
        'Т' => 'T',
        'У' => 'U',
        'Ф' => 'F',
        'Х' => 'KH',
        'Ц' => 'C',
        'Ч' => 'CH',
        'Ш' => 'SH',
        'Щ' => 'JSH',
        'Ъ' => 'HH',
        'Ы' => 'IH',
        'Ь' => 'JH',
        'Э' => 'EH',
        'Ю' => 'JU',
        'Я' => 'JA',
    ];

    public static function ruToEn(string $word): string
    {
        return strtr(mb_strtoupper($word, 'utf-8'), self::TRANSLIT);
    }

    public static function enToRu(string $word): string
    {
        return mb_strtolower(strtr(mb_strtoupper($word, 'utf-8'), array_flip(self::TRANSLIT)), 'utf-8');
    }

    public static function findWords(string $pattern, int $numWords = self::FIND_WORDS_LIMIT): array
    {
        // удаляем пробелы, которые вставляет клавиатура мобил
        $pattern = str_replace(' ', '', $pattern);

        if (preg_match('/[^a-zA-ZА-Яа-яЁёЫыРрТтУуФфХхЦцЧчШшЩщЪъЬьЭэЮю*?\[\]]/', $pattern)) {
            return [['slovo' => T::S('QUERY_ERROR_MSG')]];
        }

        $pattern = '^' . str_replace(array_keys(self::REPLACE), self::REPLACE, $pattern) . '$';

        $query = ORM::select(['slovo'], self::TABLE_NAME)
            . ORM::where('slovo', 'REGEXP', "\"$pattern\"", true)
            . ORM::andWhere(self::IS_DELETED_FIELD, '=', 0, true)
            . ORM::orderBy('rand()')
            . ORM::limit($numWords);

        return DB::queryArray($query) ?: [['slovo' => T::S('No words found')]];
    }

    public static function checkWord(string $word): bool
    {
        return (bool)DB::queryValue(
            ORM::select(['count(1)'], self::TABLE_NAME)
            . ORM::where(self::WORD_FIELD, '=', $word)
            . ORM::andWhere(self::IS_DELETED_FIELD, '=', 0, true)
            . ORM::limit(1)
        );
    }

}

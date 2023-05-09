<?php

class BaseModel
{
    const CONDITIONS = ['=' => '=', '!=' => '!=', 'in' => 'in', '<' => '<', '>' => '>', '<=' => '<=', '>=' => '>=',];
    const TABLE_NAME = 'players';
    const AND_NOT_DELETED = ' AND is_deleted = 0 ';

    const UPDATED_AT_FIELD = 'updated_at';
    const CURRENT_TIMESTAMP = 'CURRENT_TIMESTAMP';

    public static function add(array $fieldsVals)
    {
        $query = ORM::insert(static::TABLE_NAME, 'IGNORE')
            . ORM::insertFields(array_keys($fieldsVals))
            . ORM::rawValues(
                array_map(
                    fn($value) => $value instanceof ORM
                        ? $value->rawExpression
                        : ("'" . DB::escapeString($value) . "'"),
                    $fieldsVals
                )
            );

        if (DB::queryInsert($query)) {
            return DB::insertID();
        } else {
            return false;
        }
    }

    /**
     * Sets 1 param of model
     * @param $id
     * @param $field
     * @param $value
     * @return bool
     */
    public static function setParam($id, $field, $value)
    {
        $updateQuery = ORM::update(static::TABLE_NAME)
            . ORM::set(['field' => $field, 'value' => DB::escapeString($value)])
            . ORM::where('id', '=', $id);

        if (DB::queryInsert($updateQuery)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Sets field value for all table
     * @param string $field
     * @param mixed $value raw value or ORM Expression
     * @param array $where = []
     * @return false|int
     */
    public static function setParamMass(string $field, $value, array $where = [])
    {
        if (is_array($field) && is_array($value)) {
            $setValues = array_map(
                fn($fld, $val) => [
                    'field' => $fld,
                    'value' => $val instanceof ORM ? $val : DB::escapeString($val)
                ],
                $field,
                $value
            );
        } else {
            $setValues = [
                'field' => $field,
                'value' => $value instanceof ORM ? $value : DB::escapeString($value)
            ];
        }

        return DB::queryInsert(
            ORM::update(static::TABLE_NAME)
            . ORM::set($setValues)
            . (
            empty($where)
                ? ''
                : ORM::where($where['field_name'], $where['condition'], $where['value'], $where['raw'] ?? false)
            )
        );
    }

    /**
     * @param $id
     * @param array $fieldsvals [$field1=>$val1, $field2=>$val2 ...]
     * @return bool
     */
    public static function update($id, array $fieldsvals): bool
    {
        $updateQuery = ORM::update(static::TABLE_NAME)
            . ORM::set($fieldsvals)
            . ORM::where('id', '=', $id);

        if (DB::queryInsert($updateQuery)) {
            return true;
        } else {
            return false;
        }
    }

    public static function getLastID()
    {
        return DB::queryValue("SELECT max(id) as mx FROM " . static::TABLE_NAME) ?: 0;
    }

    public static function findAll(array $fieldList = [])
    {
        //$query = "SELECT * FROM " . static::TABLE_NAME;
        $query = "SELECT " . (empty($fieldList) ? '*' : implode(',', $fieldList)) . " FROM " . static::TABLE_NAME;

        return DB::queryArray($query) ?: [];
    }

    /**
     * Simple custom selector wich makes when with field=value [AND field2=value2...] clause
     * @param mixed $field - field name or array with field names
     * @param mixed $value - field desired value or array with corresponding values
     * @param false $isRaw
     * @param array $fieldList = []
     * @return array
     */
    public static function findAllCustom($field, $value, $isRaw = false, array $fieldList = []): array
    {
        $query = "SELECT " . (empty($fieldList) ? '*' : implode(',', $fieldList)) . " FROM " . static::TABLE_NAME;

        if (!is_array($field)) {
            $query .= " WHERE $field = " . ($isRaw ? $value : "'$value'");
        } else {
            if (!is_array($value)) {
                return [];
            }
            $conditions = [];
            foreach ($field as $num => $fld) {
                $conditions[] = $fld
                    . " = "
                    . ($isRaw ? $value[$num] : "'{$value[$num]}'");
            }
            $query .= " WHERE " . implode(' AND ', $conditions);
        }

        return DB::queryArray($query) ?: [];
    }

    /**
     * Получаем все записи ИЗ БД по условию и обновляем КЭШ
     * @param string $field
     * @param string $condition
     * @param $value
     * @param bool $isRaw
     * @param bool $noResult Признак ненужности результата - только обновить кэш моделей
     * @param array $fields
     * @return array
     */
    public static function getCustom(
        string $field,
        string $condition,
        $value,
        bool $isRaw = false,
        bool $noResult = false,
        array $fields = []
    ): array {
        // todo сделать 'IN' по чанкам
        $query = ORM::select($fields ?: ['*'], static::TABLE_NAME);
        if ($condition == self::CONDITIONS['in']) {
            if (
                !is_array($value)
                ||
                (count($value) > self::TEASERS_IN_CHUNK)
                ||
                empty($value)
            ) {
                mp($value, 'VALUE is WRONG!!!', __METHOD__);

                return [];
            }

            $query .= ORM::whereIn($field, $value);
        } else {
            if (!in_array($condition, self::CONDITIONS)) {
                mp($condition, 'CONDITION is WRONG!!!', __METHOD__);

                return [];
            }
            $query .= ORM::where($field, $condition, $value, $isRaw);
        }

        return DB::queryArray($query);
    }

    /**
     * Returns one record or []
     * @param $field
     * @param $value
     * @param false $isRaw
     * @return array
     */
    public static function getOneCustom($field, $value, $isRaw = false): array
    {
        if (!is_array($field)) {
            $query = "SELECT * FROM "
                . static::TABLE_NAME
                . " WHERE $field = "
                . ($isRaw ? $value : "'$value'")
                . " LIMIT 1";
        } else {
            if (!is_array($value)) {
                return [];
            }
            $conditions = [];
            foreach ($field as $num => $fld) {
                $conditions[] = $fld
                    . " = "
                    . ($isRaw ? $value[$num] : "'{$value[$num]}'");
            }
            $query = "SELECT * FROM "
                . static::TABLE_NAME
                . " WHERE "
                . implode(' AND ', $conditions)
                . " LIMIT 1";
        }

        return DB::queryArray($query)[0] ?? [];
    }

    /**
     * Returns model from DB
     * @param int $id
     * @return array
     */
    public static function getOne(int $id): array
    {
        $query = "SELECT * FROM " . static::TABLE_NAME . " WHERE id = $id LIMIT 1";

        return DB::queryArray($query)[0] ?? [];
    }

    public static function getOneNext($id, $isDelited = ''): array
    {
        $query = ORM::select(['*'], static::TABLE_NAME)
            . ORM::where('id', '>', $id, true)
            . $isDelited
            . ORM::orderBy('id')
            . ORM::limit(1);

        //"SELECT * FROM " . static::TABLE_NAME . " WHERE id > $id $isDelited ORDER BY id ASC LIMIT 1";

        return DB::queryArray($query)[0] ?? [];
    }

    public static function getRand($isDeleted = '')
    {
        $maxID = DB::queryValue(
            ORM::select(['max(id)'], static::TABLE_NAME)
        );

        $query = ORM::select(['*'], static::TABLE_NAME)
            . ORM::where('id', '>', rand(1, $maxID), true)
            . $isDeleted
            . ORM::orderBy('id')
            . ORM::limit(1);

        return DB::queryArray($query)[0] ?? false;
    }

    public static function exists(int $id)
    {
        return !empty(static::getOne($id)) ? true : false;
    }

    public function __construct()
    {
        return true;
    }

}
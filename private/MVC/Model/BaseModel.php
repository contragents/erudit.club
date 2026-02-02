<?php


/**
 * @property int $_id
 * @method static bool exists(int $id) Вызов через BaseModel::exists($id)
 * @method bool exists() Вызов через $model->find()->where(...)->exists()
 **/

class BaseModel implements Iterator
{
    use QueryTrait;

    const NUMERIC_TYPES = [
        'boolean',
        'bool',
        'integer',
        'int',
        'double',
        'float',
    ];

    const CONDITIONS = ['=' => '=', '!=' => '!=', 'in' => 'in', '<' => '<', '>' => '>', '<=' => '<=', '>=' => '>=',];
    const TABLE_NAME = 'players';
    const AND_NOT_DELETED = ' AND is_deleted = 0 ';

    const DEFAULT_BATCH_CHUNK_SIZE = 10;

    /** int */
    const ID_FIELD = 'id';
    const CREATED_AT_FIELD = 'created_at';
    const UPDATED_AT_FIELD = 'updated_at';
    /** bool */
    const IS_DELETED_FIELD = 'is_deleted';
    const COMMON_ID_FIELD = 'common_id';

    const SKIP_ATTR_TYPES = ["object", "resource", "resource (closed)", "NULL", "unknown type"];

    const CURRENT_TIMESTAMP = 'CURRENT_TIMESTAMP';

    const FIELDS = [self::ID_FIELD => self::TYPE_INT];

    const TYPE_INT = 'int';
    const TYPE_STRING = 'string';
    const TYPE_DATE = 'timestamp';
    const ITEMS_IN_CHUNK = 1000;
    const DECIMALS = 2; // Число десятичных знаков для Formatted-свойств

    const ALL_GAMES = 'all';

    const GAME_IDS = [
        self::ALL_GAMES => 0,
        Game::ERUDIT => 1,
        Game::SCRABBLE => 2,
        Game::SUDOKU => 3,
        Game::GOMOKU => 4,
    ];


    public ?int $_id = null;

    public array $relations = []; // Массив для динамически запрашиваемых связей между моделями

    /**
     * @param int $tgId
     * @return static[]
     */
    public static function getActiveForTgIdO(int $tgId): array
    {
        return static::find()->where([static::IS_DELETED_FIELD => false, static::TG_ID_FIELD => $tgId])->all();
    }

    /**
     * @return static[]
     */
    public static function getActiveO(): array
    {
        return static::getCustomO(static::IS_DELETED_FIELD, '=', 0, true);
    }

    /**
     * SOFT deletion of model
     * @return bool
     */
    public function del(): bool
    {
        $properties = get_object_vars($this);

        if (isset($properties['_is_deleted'])) {
            /** @var  bool $_is_deleted */
            $this->_is_deleted = true;
            return $this->save();
        }

        return false;
    }

    /**
     * Получает последнюю по порядку запись в соответствии с $where
     * @param array $where
     * @return static|null
     */
    public static function getlastO(array $where = []): ?self
    {
        if (empty($where)) {
            return self::getOneO(self::getLastID());
        }

        $query = self::select(['*'])
            . ORM::where('1', '=', 1, true)
            . implode(
                ' ',
                array_map(fn($field, $value) => ORM::andWhere($field, '=', $value), array_keys($where), $where)
            )
            . ORM::orderBy(static::ID_FIELD, false)
            . ORM::limit(1);

        $row = DB::queryArray($query)[0] ?? null;

        return $row ? self::arrayToObject($row) : null;
    }

    private static function fieldName(string $property): string
    {
        return ltrim($property, '_');
    }

    private static function isFieldName(string $property): bool
    {
        return substr($property, 0, 1) === '_';
    }

    private function from_boolean(bool $value): int
    {
        return (int)$value;
    }

    private function from_array(array $value): string
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE);
    }

    public function to_boolean(int $value): bool
    {
        return (bool)$value;
    }

    public function to_array(string $value): array
    {
        return json_decode($value, true);
    }

    public function save(): bool
    {
        $properties = get_object_vars($this);
        $fieldsVals = [];
        $debugArr = [];

        foreach ($properties as $property => $value) {
            $valueType = gettype($value);
            // todo remove $debugArr CLUB-471
            $debugArr[$property] = ['value' => $value, 'type' => $valueType];
            if (self::isFieldName($property) && !in_array($valueType, self::SKIP_ATTR_TYPES)) {
                $methodName = "from_$valueType";
                //if (is_callable([$this, $methodName])) {
                if (method_exists($this, $methodName)) {
                    try {
                        // $value = call_user_func([$this, "from_$valueType"], $value);
                        $value = $this->$methodName($value);
                        $debugArr[$property]['called_func'] = $methodName;
                        $debugArr[$property]['new_value'] = $value;
                    } catch (Throwable $e) {
                        continue;
                    }
                } elseif (!is_int($value)) {
                    // Добавим экранирование спецсимволов
                    $value = DB::escapeString($value);
                    $debugArr[$property]['new_value'] = $value;
                }

                $fieldsVals[self::fieldName($property)] = $value;
            }
        }
        LogModel::add(
            [
                LogModel::CATEGORY_FIELD => 'register_test',
                LogModel::MESSAGE_FIELD => [
                    '$debugArr' => $debugArr,
                    '$fieldsVals' => $fieldsVals,
                    '$properties' => $properties
                ],
            ]
        );
        if (($this->_id ?? false) && static::exists($this->_id)) {
            unset($fieldsVals['id']);
            return self::update($this->_id, $fieldsVals);
        } else {
            $id = self::add($fieldsVals);
            if (is_int($id)) {
                $this->_id = $id;
            }

            return (bool)$id;
        }
    }

    /**
     * @param array $fieldsVals
     * @return static|null
     */
    public static function new(array $fieldsVals = []): ?self
    {
        // Нужно удалить '_' в начале названия полей
        $newFieldsVals = [];
        foreach ($fieldsVals as $field => $value) {
            $newFieldsVals[self::fieldName($field)] = $value;
        }

        return self::arrayToObject($newFieldsVals);
    }

    /**
     * converts $rows to array of models
     * @param array $rows
     * @return static[]
     */
    protected static function rowsToObjects(array $rows): array
    {
        $res = [];

        foreach ($rows as $row) {
            $res[] = self::arrayToObject($row);
        }

        return $res;
    }

    /**
     * Представляем модель в виде массива [field1=>value1, field2=>value2,...]
     * @return array
     */
    public function toArray(): array
    {
        $res = [];
        $properties = get_class_vars(static::class);
        foreach ($properties as $property => $nothing) {
            if (self::isFieldName($property)) {
                $res[self::fieldName($property)] = $this->$property;
            }
        }

        return $res;
    }

    /**
     * @param array $row
     * @return static
     */
    protected static function arrayToObject(array $row): self
    {
        $res = new static();
        $initializedProperties = get_object_vars($res);
        $properties = get_class_vars(static::class);

        foreach ($row as $field => $value) {
            $property = '_' . $field;

            if (!array_key_exists($property, $properties)) {
                continue;
            }

            try {
                if (isset($initializedProperties[$property])) {
                    $propertyType = gettype($res->$property);
                    if (is_callable([$res, "to_$propertyType"])) {
                        $value = call_user_func([$res, "to_$propertyType"], $value);
                    }
                } elseif (!isset($value)) {
                    // для NOT NULL атрибутов-полей поддерживаются только типы без преобразований - не array, bool
                    continue;
                }

                $res->$property = $value;
            } catch (Throwable $e) {
                continue;
            }
        }

        return $res;
    }

    /**
     * Обновляет модель из БД. Если $_id не установлен или не найден, то ничего не делает и возвращает false
     * @return bool
     */
    public function refresh(): bool
    {
        if (!$this->_id) {
            return false;
        }

        if ($freshModel = static::getOneO($this->_id)) {
            $this->relations = [];

            foreach (get_object_vars($freshModel) as $property => $value) {
                $this->$property = $value;
            }

            // Делаем еще один прогон присвоения - чтобы исключить поля, записанные как null в БД
            foreach (get_object_vars($this) as $property => $nothing) {
                $this->$property = $freshModel->$property;
            }


            return true;
        } else {
            return false;
        }
    }

    /**
     * Gets $num last records according to id field
     * @param int $num
     * @return static[]
     */
    public static function getLastRecordsO(int $num): array
    {
        $query = static::select()
            . ORM::orderBy(static::ID_FIELD, false)
            . ORM::limit($num);

        $rows = DB::queryArray($query) ?: [];

        return self::rowsToObjects($rows);
    }

    /**
     * @param $field
     * @param $value
     * @param bool $isRaw
     * @param array|null $conditionArr
     * @param string|null $orderBy
     * @return static|null
     */
    public static function getOneCustomO(
        $field,
        $value,
        bool $isRaw = false,
        ?array $conditionArr = null,
        ?string $orderBy = null
    ): ?self {
        $row = self::getOneCustom($field, $value, $isRaw, $conditionArr, $orderBy);

        return empty($row) ? null : self::arrayToObject($row);
    }

    /**
     * @param array|string $field may be field=>value array, than $condition = null, $value = null
     * @param array|string $condition
     * @param array|string|int|float $value
     * @param bool $isRaw
     * @return static[]
     */
    public static function getCustomComplexO($field, $condition, $value, bool $isRaw = false): array
    {
        if (is_array($field) && is_null($condition) && is_null($value)) {
            $condition = array_fill(0, count($field), '=');
            $value = array_values($field);
            $rows = self::getCustomComplex(array_keys($field), $condition, $value, $isRaw);
        } else {
            $rows = self::getCustomComplex($field, $condition, $value, $isRaw);
        }

        $res = [];

        foreach ($rows as $row) {
            $res[] = self::arrayToObject($row);
        }

        return $res;
    }

    /**
     * @param string $field
     * @param string $condition
     * @param $value
     * @param bool $isRaw
     * @return static[]
     */
    public static function getCustomO(string $field, string $condition, $value, bool $isRaw = false): array
    {
        $rows = self::getCustom($field, $condition, $value, $isRaw);
        $res = [];

        foreach ($rows as $row) {
            $res[] = self::arrayToObject($row);
        }

        return $res;
    }

    /**
     * @param int $id
     * @param bool $createIfNotExists
     * @return static|null
     */
    public static function getOneO(?int $id = null, bool $createIfNotExists = false): ?object
    {
        if ($id === null) {
            return null;
        }

        $res = null;

        $row = self::getOne($id);

        if (!empty($row)) {
            $res = self::arrayToObject($row);
        } elseif ($createIfNotExists) {
            $res = new static();
            $res->_id = $id;
        }

        return $res;
    }

    /**
     * @param array $fields
     * @param string $where
     * @param string $other
     * @return static[]
     */
    public static function selectO(array $fields = [], string $where = '', string $other = ''): array
    {
        $query = ORM::select($fields, static::TABLE_NAME)
            . ' ' . $where
            . ' ' . $other;

        $rows = DB::queryArray($query) ?: [];

        $res = [];

        foreach ($rows as $row) {
            $res[] = self::arrayToObject($row);
        }

        return $res;
    }

    public static function select(array $fields = [], bool $skobki = false, string $where = '', string $as = ''): string
    {
        return ($skobki ? ' ( ' : '')
            . ORM::select($fields, static::TABLE_NAME)
            . $where
            . ($skobki ? ' ) ' : '')
            . ($as ? " as $as" : '');
    }

    public static function getFieldWithTable(string $field): string
    {
        return ' ' . static::TABLE_NAME . '.' . $field . ' ';
    }

    /**
     * @param array $processedFieldsVals
     * @return bool|int
     */
    public static function add(array $fieldsVals)
    {
        $processedFieldsVals = $fieldsVals;

        // Приводим массивы и булевы к соответствующим типам в БД
        foreach ($processedFieldsVals as $field => &$value) {
            if (is_array($value)) {
                $value = json_encode($value, JSON_UNESCAPED_UNICODE);
            } elseif (is_bool($value)) {
                $value = $value ? 1 : 0;
            }
        }

        try {
            $query = ORM::insert(static::TABLE_NAME, 'IGNORE')
                . ORM::insertFields(array_keys($processedFieldsVals))
                . ORM::rawValues(
                    array_map(
                        fn($value) => $value instanceof ORM
                            ? $value->rawExpression
                            : ("'" . DB::escapeString($value) . "'"),
                        $processedFieldsVals
                    )
                );

            if (DB::queryInsert($query)) {
                return DB::insertID() ?: true;
            } else {
                LogModel::logQuery($query);

                return false;
            }
        } catch (Throwable $e) {
            LogModel::logQuery($query ?? '', $e);

            return false;
        }
    }

    /**
     * Sets 1 param of model
     * @param $id
     * @param $field
     * @param $value
     * @param bool $raw
     * @return bool
     */
    public static function setParam($id, $field, $value, bool $raw = false): bool
    {
        $updateQuery = ORM::update(static::TABLE_NAME)
            . ORM::set(
                [
                    'field' => $field,
                    'value' => ($raw || ($value instanceof ORM)) ? $value : DB::escapeString($value),
                    'raw' => $raw
                ]
            )
            . ORM::where('id', '=', $id, true);

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

        $ormWhere = '';

        if (isset($where['field_name'])) {
            $ormWhere = ORM::where($where['field_name'], $where['condition'], $where['value'], $where['raw'] ?? false);
        } elseif (isset($where[0]['field_name'])) {
            $ormWhere = ORM::where('1', '=', 1, true)
                . implode(
                    ' ',
                    array_map(
                        fn($andWhere) => ORM::andWhere(
                            $andWhere['field_name'],
                            $andWhere['condition'],
                            $andWhere['value'],
                            $andWhere['raw'] ?? false
                        ),
                        $where
                    )
                );
        }

        $query = ORM::update(static::TABLE_NAME)
            . ORM::set($setValues)
            . $ormWhere;

        return DB::queryInsert($query);
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
            LogModel::logQuery($updateQuery);

            return false;
        }
    }

    public static function getLastID(): int
    {
        return (int)(DB::queryValue("SELECT max(id) as mx FROM " . static::TABLE_NAME) ?: 0);
    }

    public static function getFirstID(): ?int
    {
        return (int)(DB::queryValue("SELECT min(id) as mn FROM " . static::TABLE_NAME) ?: 0) ?: null;
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
     * @param string|array $field
     * @param string|array $condition
     * @param $value
     * @param bool $isRaw
     * @param bool $noResult Признак ненужности результата - только обновить кэш моделей
     * @param array $fields
     * @return array
     */
    public static function getCustomComplex(
        $field,
        $condition,
        $value,
        bool $isRaw = false,
        bool $noResult = false,
        array $fields = []
    ): array {
        $query = ORM::select($fields ?: ['*'], static::TABLE_NAME);
        // todo сделать 'IN' по чанкам
        // Обрабатываем массивы полей-условий-значений
        if (is_array($field) || is_array($condition) || is_array($value)) {
            if (!is_array($field) || !is_array($condition) || !is_array($value)) {
                return [];
            }

            if (count($field) != count($condition) || count($field) != count($value) || count($field) == 0) {
                return [];
            }

            $iteration = false;
            foreach ($field as $num => $fld) {
                if ($condition[$num] == self::CONDITIONS['in']) {
                    if (
                        !is_array($value[$num])
                        ||
                        (count($value[$num]) > 1000) // todo разбить по чанкам
                        ||
                        empty($value[$num])
                    ) {
                        return [];
                    }

                    $query .= $iteration
                        ? ORM::andWhereIn($fld, $value[$num])
                        : ORM::whereIn($fld, $value[$num]);
                } else {
                    if (!in_array($condition[$num], self::CONDITIONS)) {
                        return [];
                    }

                    $query .= $iteration
                        ? ORM::andWhere($fld, $condition[$num], $value[$num], $isRaw)
                        : ORM::where($fld, $condition[$num], $value[$num], $isRaw);
                }

                $iteration = true;
            }
        } else {
            if ($condition == self::CONDITIONS['in']) {
                if (
                    !is_array($value)
                    ||
                    (count($value) > 1000)
                    ||
                    empty($value)
                ) {
                    return [];
                }

                $query .= ORM::whereIn($field, $value);
            } else {
                if (!in_array($condition, self::CONDITIONS)) {
                    return [];
                }
                $query .= ORM::where($field, $condition, $value, $isRaw);
            }
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
                (count($value) > self::ITEMS_IN_CHUNK)
                ||
                empty($value)
            ) {
                return [];
            }

            $query .= ORM::whereIn($field, $value);
        } else {
            if (!in_array($condition, self::CONDITIONS)) {
                return [];
            }
            $query .= ORM::where($field, $condition, $value, $isRaw);
        }

        return DB::queryArray($query) ?: [];
    }

    /**
     * Returns one record or []
     * @param $field
     * @param $value
     * @param false $isRaw
     * @param array|null $conditionArr
     * @param string|null $orderBy
     * @return array
     */
    public static function getOneCustom(
        $field,
        $value,
        $isRaw = false,
        ?array $conditionArr = null,
        ?string $orderBy = null
    ): array {
        if (!is_array($field)) {
            $query = "SELECT * FROM "
                . static::TABLE_NAME
                . " WHERE $field = "
                . ($isRaw ? $value : "'$value'")
                . (' ' . ($orderBy ?? '') . ' ')
                . " LIMIT 1";
        } else {
            if (!is_array($value)) {
                return [];
            }
            $conditions = [];
            foreach ($field as $num => $fld) {
                $conditions[] = $fld
                    . ' ' . ($conditionArr[$num] ?? '=') . ' '
                    . ($isRaw ? $value[$num] : "'{$value[$num]}'");
            }
            $query = "SELECT * FROM "
                . static::TABLE_NAME
                . " WHERE "
                . implode(' AND ', $conditions)
                . (' ' . ($orderBy ?? '') . ' ')
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
        // todo CLUB-471
        LogModel::add([LogModel::CATEGORY_FIELD => '__callStatic_test', LogModel::MESSAGE_FIELD => $query]);

        return DB::queryArray($query)[0] ?? [];
    }

    /**
     * @param int $id
     * @param bool $ignoreDeleted
     * @return static|null
     */
    public static function getOneNextO(int $id, bool $ignoreDeleted = true): ?self
    {
        $row = self::getOneNext($id, $ignoreDeleted ? '' : '');

        if (!empty($row)) {
            $res = self::arrayToObject($row);
        }

        return $res ?: null;
    }

    public static function getOneNext($id, $isDelited = ''): array
    {
        $query = ORM::select(['*'], static::TABLE_NAME)
            . ORM::where('id', '>', $id, true)
            . $isDelited
            . ORM::orderBy('id')
            . ORM::limit(1);

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

    /**
     * Checks existing of $id or by queryParts->where conditions
     * @param int|null $id
     * @return bool
     */
    // 1. Переименовываем реальную логику (делаем защищенной)
    protected function internalExists(): ?bool
    {
        return empty($this->queryParts)
            ? null
            : !empty($this->one());
    }

    // 2. Обработка вызова $obj->exists()
    public function __call($name, $arguments)
    {
        // todo CLUB-471
        LogModel::add(
            [
                LogModel::CATEGORY_FIELD => '__callDinamic_test',
                LogModel::MESSAGE_FIELD => ['$name' => $name, '$arguments' => $arguments]
            ]
        );
        switch ($name) {
            case 'exists':
                return count($arguments) === 0
                    ? $this->internalExists()
                    : self::__callStatic('exists', $arguments);
            default:
                return null;
        }
    }

    // 3. Обработка вызова Class::exists()
    public static function __callStatic($name, $arguments)
    {
        // todo CLUB-471
        LogModel::add(
            [
                LogModel::CATEGORY_FIELD => '__callStatic_test',
                LogModel::MESSAGE_FIELD => ['$name' => $name, '$arguments' => $arguments]
            ]
        );
        switch ($name) {
            case 'exists':
                if ($id = $arguments[0] ?? null) {
                    return !empty(static::getOne($id));
                } else {
                    return null;
                }
            default:
                return null;
        }
    }

    /**
     * if at least one record exists
     * @param array $conditions [field=>value] list
     * @param bool $raw
     * @return bool
     */
    public
    static function existsCustom(
        array $conditions,
        bool $raw = false
    ): bool {
        $query = ORM::select(['count(1)'], static::TABLE_NAME)
            . ORM::where(1, '=', 1, true)
            . implode(
                ' ',
                array_map(
                    fn($field, $value) => ORM::andWhere($field, '=', $value, $raw),
                    array_keys($conditions),
                    $conditions
                )
            );

        return ((int)DB::queryValue($query) ?: 0) > 0;
    }

    public
    function __construct(
        array $fieldsVals = []
    ) {
        $properties = get_class_vars(static::class);
        foreach ($properties as $property => $nothing) {
            if (isset($fieldsVals[$property])) {
                try {
                    $this->$property = $fieldsVals[$property];
                } catch (Throwable $e) {
                    continue;
                }
            }
        }

        return true;
    }

    /**
     * Mass update fields=>values by conditions
     * @param array $fieldsVals ['f1'=>'v1', 'f2'=>'v2'...]
     * @param array $whereArr [[$field, $condition, $value, $isRaw = false],[]...] OR [$field, $condition, $value, $isRaw = false]
     */
    public
    static function updateWhere(
        array $fieldsVals,
        array $whereArr
    ): bool {
        $updateQuery = ORM::update(static::TABLE_NAME)
            . ORM::set($fieldsVals)
            . ORM::where(1, '=', 1, true)
            . implode(
                ' ',
                array_map(
                    fn($where) => ORM::andWhere($where[0], $where[1], $where[2], $where[3] ?? false),
                    is_array($whereArr[0]) ? $whereArr : [$whereArr]
                )
            );

        if (DB::queryInsert($updateQuery)) {
            return true;
        } else {
            return false;
        }
    }

    public
    function __get(
        $attr
    ) {
        try {
            if (is_callable([$this, $attr])) {
                return $this->{$attr}();
            }

            // Проверим на связь один ко многим
            if (substr($attr, -1) === 's') {
                // Определяем модель, записи которой указывают на нашу модель
                $modelClass = '\\' . ucfirst(substr($attr, 0, strlen($attr) - 1)) . 'Model';

                if (class_exists($modelClass)) {
                    // Проверяем, закеширована ли переменная
                    if (key_exists($attr, $this->relations)) {
                        return $this->relations[$attr];
                    }

                    // Определяем название поля в ссылающейся модели
                    $selfIdField = lcfirst(str_replace(['\\', 'Model'], '', get_class($this)) . '_id');

                    /** @var BaseModel $modelClass */
                    // Получаем записи из ссылающейся модели
                    $this->relations[$attr] = $modelClass::getCustomO($selfIdField, '=', $this->_id, true);

                    return $this->relations[$attr];
                }
            }

            if (strPos($attr, 'Formatted0')) {
                $newAttr = str_replace('Formatted0', '', $attr);

                if (isset($this->$newAttr)) {
                    return number_format($this->$newAttr, static::DECIMALS, '.', '');
                }

                if (is_callable([$this, $newAttr])) {
                    return number_format($this->{$newAttr}(), static::DECIMALS, '.', '');
                }
            }

            if (strPos($attr, 'Formatted')) {
                $newAttr = str_replace('Formatted', '', $attr);

                if (isset($this->$newAttr)) {
                    return rtrim(rtrim(number_format($this->$newAttr, static::DECIMALS, '.', ''), '0'), '.');
                }

                if (is_callable([$this, $newAttr])) {
                    return rtrim(rtrim(number_format($this->{$newAttr}(), static::DECIMALS, '.', ''), '0'), '.');
                }
            }

            // Пытаемся связать с моделью, если класс модели существует
            $modelClass = '\\' . ucfirst($attr) . 'Model';
            if (class_exists($modelClass)) {
                // Проверяем, закеширована ли переменная
                if (key_exists($attr, $this->relations)) {
                    return $this->relations[$attr];
                }

                $entityIdAttr = '_' . $attr . '_id';
                $entityId = $this->$entityIdAttr ?? null;
                if (isset($entityId)) {
                    // Кешируем связь, чтобы сохранять консистентность для дальнейшего использования
                    /** @var $modelClass static */
                    $this->relations[$attr] = $modelClass::getOneO($entityId);

                    return $this->relations[$attr];
                }
            }
        } catch (Throwable $e) {
            Cache::setex('test.__get', 600, $e->__toString());
        }

        return null;
    }
}

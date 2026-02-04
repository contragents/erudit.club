<?php


trait QueryTrait
{
    protected ?QueryParts $queryParts = null;

    private ?string $model = null;

    /**
     * @var static|null the value for the current iteration
     */
    private ?self $Value = null;
    /**
     * @var int|null the limit of query (without offset)
     */
    private ?int $Limit = null;
    /**
     * @var int the offset of query
     */
    private int $Offset = 0;
    /**
     * @var int the current iteration
     */
    private int $Iteration = 0;
    /**
     * @var bool|null if current element is valid
     */
    private ?bool $isValid = null;

    private int $chunkSize = self::DEFAULT_BATCH_CHUNK_SIZE;
    private array $currentChunk = [];

    /**
     * @param string[] $fields
     * @return static
     */
    public static function find(array $fields = ['*']): self
    {
        $model = new static(['model' => get_called_class()]);
        $model->queryParts = new QueryParts();
        $model->queryParts->fields = $fields;

        return $model;
    }

    /**
     * @param array $conditions [field1=>value1, f2=>v2] or [['field_name' => fn1, 'condition' => c1, 'value' => v1, 'raw' => r1],...]
     * @return static
     */
    public function where(array $conditions): self
    {
        foreach ($conditions as $num => $condition) {
            if (is_array($condition)) {
                $value = $condition['value'] ?? ($condition[2] ?? null);
                if ($value === true || $value === false) {
                    $value = (int)$value;
                }

                if (isset($value)) {
                    $fieldName = $condition['field_name'] ?? $condition[0];

                    $this->queryParts->where[] = [
                        'field_name' => $fieldName,
                        'condition' => $condition['condition'] ?? ($condition[1] ?? '='),
                        'value' => $value,
                        'raw' => $condition['raw'] ?? ($condition[3] ?? in_array(
                                    $this->model::getType($fieldName),
                                    self::NUMERIC_TYPES
                                )),
                    ];
                }
            } else {
                $value = $condition;
                if ($value === true || $value === false) {
                    $value = (int)$value;
                }

                $this->queryParts->where[] =
                    [
                        'field_name' => (is_numeric($num) ? static::ID_FIELD : $num),
                        // если ключ - число, то поле - это id - добавить проверку
                        'condition' => '=',
                        'value' => $value,
                        'raw' => in_array(self::getType($num), self::NUMERIC_TYPES),
                    ];
            }
        }

        return $this;
    }

    /**
     * @param string $orderField field for order
     * @param bool $asc true for ascending, false for descending
     * @return static
     */
    public function order(string $orderField, bool $asc = true): self
    {
        $this->queryParts->order = [$orderField, $asc];

        return $this;
    }

    /**
     * @param int $limit
     * @param int $offset
     * @return static
     */
    public function limit(int $limit, int $offset = 0): self
    {
        $this->queryParts->limit = $offset
            ? [$limit, $offset]
            : $limit;

        return $this;
    }

    /**
     * Выдает SQL от объекта с подготовленным запросом
     * @return string|null
     */
    public function getSQL(): ?string
    {
        try {
            $where = $this->buildWhere();

            return ORM::select($this->queryParts->fields, static::TABLE_NAME)
                . ' ' . $where
                . ' ' . $this->getOrder() . $this->getLimit();
        } catch (Throwable $e) {
            return null;
        }
    }

    private function buildWhere(): string
    {
        $where = '';

        foreach ($this->queryParts->where as $partWhere) {
            $where .= (
            empty($where)
                ? ORM::where(...array_values($partWhere))
                : ORM::andWhere(...array_values($partWhere))
            );
        }

        return $where;
    }

    /**
     * @return static|null
     */
    public function one(): ?self
    {
        $limit = $this->queryParts->limit;
        $this->limit(1, $this->queryParts->limit[1] ?? 0);

        $res = $this->all()[0] ?? null;

        $this->queryParts->limit = $limit;

        return $res;
    }

    public function getQuery(): string
    {
        $where = $this->buildWhere();

        return ORM::select($this->queryParts->fields, static::TABLE_NAME)
            . ' ' . $where
            . ' ' . $this->getOrder() . $this->getLimit();
    }

    public function count(): int
    {
        try {
            $tpmFields = $this->queryParts->fields; // Временное сохранение полей селекта
            $this->queryParts->fields = ['count(1)']; // делаем count
            $query = $this->getQuery(); // готовим запрос
            $this->queryParts->fields = $tpmFields; // возврат полей

            return DB::queryValue($query) ?: 0;
        } catch (Throwable $e) {
            $this->queryParts->fields = $tpmFields; // возврат полей

            if(!($this instanceof LogModel)) {
                LogModel::logQuery($query ?? '', $e);
            }

            return 0;
        }
    }

    /**
     * @return static[]
     */
    public function all(): array
    {
        $where = '';

        foreach ($this->queryParts->where as $partWhere) {
            $where .= (
            empty($where)
                ? ORM::where(...array_values($partWhere))
                : ORM::andWhere(...array_values($partWhere))
            );
        }

        return self::selectO(
            $this->queryParts->fields,
            $where,
            $this->getOrder() . $this->getLimit()
        );
    }

    private static function getType(string $field): string
    {
        $field = ltrim($field, '_');
        $field = '_' . $field;

        try {
            $rp = new ReflectionProperty(get_called_class(), $field);
            return $rp->getType()->getName() ?? 'null';
        } catch (Throwable $e) {
            return 'null';
        }
    }

    private function getOrder(): string
    {
        if (!$this->queryParts->order) {
            return '';
        }

        return ORM::orderBy(
            is_array($this->queryParts->order)
                ? $this->queryParts->order[0]
                : $this->queryParts->order,
            $this->queryParts->order[1] ?? true
        );
    }

    private function getLimit(): string
    {
        if (!$this->queryParts->limit) {
            return '';
        }

        return ORM::limit(
            is_array($this->queryParts->limit)
                ? $this->queryParts->limit[0]
                : $this->queryParts->limit,
            $this->queryParts->limit[1] ?? 0
        );
    }

    /**
     * @return static[]
     */
    public function each(int $chunkSize = self::DEFAULT_BATCH_CHUNK_SIZE): Iterator
    {
        return new static(
            [
                'model' => get_called_class(),
                'queryParts' => $this->queryParts,
                'Limit' => $this->queryParts->limit[0] ?? $this->queryParts->limit,
                'Offset' => $this->queryParts->limit[1] ?? 0,
                'chunkSize' => $chunkSize,
            ]
        );
    }

    /**
     * Return the current element
     * @link https://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     */
    public function current(): ?self
    {
        // Делаем rewind, если current вызвали до него
        if($this->Value === null && $this->isValid === null) {
            $this->rewind();
        }

        if($this->valid()) {
            return $this->Value ?? null;
        } else {
            return null;
        }
    }

    /**
     * Move forward to next element
     * @link https://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next(): void
    {
        unset ($this->Value);
        if ($this->isValid === false) {
            return;
        }

        $this->Iteration++;
        if(isset($this->Limit) && $this->Iteration >= $this->Limit){
            $this->isValid = false;

            return;
        }

        if($this->Iteration % $this->chunkSize !== 0) {
            $this->Value = $this->currentChunk[$this->Iteration % $this->chunkSize] ?? null;
        } else {
            $this->queryParts->limit = [$this->chunkSize, $this->Offset + $this->Iteration * $this->chunkSize];

            $this->currentChunk = $this->all();
            $this->Value = $this->currentChunk[0] ?? null;
        }

        $this->isValid = (bool)$this->Value;
    }

    /**
     * Return the key of the current element
     * @link https://php.net/manual/en/iterator.key.php
     * @return string|float|int|bool|null scalar on success, or null on failure.
     */
    #[\ReturnTypeWillChange]
    public function key()
    {
        return $this->valid() ? $this->Iteration : null;
    }

    /**
     * Checks if current position is valid
     * @link https://php.net/manual/en/iterator.valid.php
     * @return bool The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     */
    public function valid(): bool
    {
        return $this->isValid ?? false;
    }

    /**
     * Rewind the Iterator to the first element
     * @link https://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind(): void
    {
        $this->Iteration = 0;

        $this->queryParts->limit = [$this->chunkSize, $this->Offset + $this->Iteration * $this->chunkSize];

        $this->currentChunk = $this->all();
        $this->Value = $this->currentChunk[0] ?? null;

        $this->isValid = (bool)$this->Value;
    }
}
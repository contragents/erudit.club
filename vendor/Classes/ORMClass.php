<?php
    /** Класс ORM для работы с MariaDB */
class ORM
{
    public $rawExpression;

    public function __construct($expression)
    {
        $this->rawExpression = $expression;
    }

    /**
     * Use ORM::set() as next constructor element
     * @param $tblName
     * @return string
     */
    public static function update($tblName)
    {
        return "UPDATE `$tblName` ";
    }

    /**
     * @param array $fieldsvals - ['field'=>,'value'=>,'raw'=>] or [['field'=>,'value'=>,'raw'=>],['field'=>,'value'=>,'raw'=>]..]
     * @return string
     */
    public static function set(array $fieldsvals)
    {
        if (!isset($fieldsvals[0])) {
            return " SET {$fieldsvals['field']} = "
                . (
                $fieldsvals['value'] instanceof ORM
                    ? $fieldsvals['value']->rawExpression
                    : ($fieldsvals['raw'] ?? false
                    ? $fieldsvals['value']
                    : "'{$fieldsvals['value']}'")
                )
                . ' ';
        }

        $fields = [];
        foreach ($fieldsvals as $fv) {
            $fields[] = " {$fv['field']} = "
                . (
                $fv['value'] instanceof ORM
                    ? $fv['value']->rawExpression
                    : ($fv['raw'] ?? false
                    ? $fv['value']
                    : "'{$fv['value']}'")
                )
                . ' ';
        }

        return ' SET ' . implode(',', $fields);
    }

    public static function where($fieldName, $cond, $value, $isRaw = false)
    {
        return " WHERE ($fieldName $cond " . ($isRaw ? $value : "'$value'") . ') ';
    }

    public static function andWhere($fieldName, $cond, $value, $isRaw = false)
    {
        return " AND ($fieldName $cond " . ($isRaw ? $value : "'$value'") . ') ';
    }

    public static function orBegin($odin = '')
    {
        return " OR $odin ";
    }

    public static function andGrBegin($odin = '') {
        return " AND ( $odin ";
    }

    public static function grEnd() {
        return " ) ";
    }

    public static function insert($tblName, $ignore = '')
    {
        return "INSERT $ignore INTO `$tblName` ";
    }

    public static function insertFields(array $fields)
    {
        return " (`" . implode('`, `', $fields) . "`) ";
    }

    public static function rawValues(array $values)
    {
        return " VALUES (" . implode(", ", $values) . ") ";
    }

    public static function onDupRaw(array $fieldsVals, $conflictKeys=[])
    {
        if (is_array($fieldsVals[0])) {
            $expressions = array_map(function ($expr) {
                return '`' . $expr[0] . '`' . ' = ' . $expr[1];
            }, $fieldsVals);
        } else {
            $expressions = ['`' . $fieldsVals[0] . '`' . ' = ' . $fieldsVals[1]];
        }

        return " ON DUPLICATE KEY UPDATE " . implode(', ', $expressions);
    }

    public static function orderBy(string $orderCond, $asc = true): string
    {
        return " ORDER BY $orderCond " . ($asc ? 'ASC' : 'DESC');
    }

    public static function orderByRand($asc = true): string
    {
        return " ORDER BY rand() " . ($asc ? 'ASC' : 'DESC');
    }

    public static function select(array $fieldArr, string $tableName): string
    {
        return " SELECT " . implode(',', $fieldArr) . " FROM $tableName ";
    }

    public static function limit(int $count, $offset = 0): string
    {
        return " LIMIT $count " . ($offset ? " OFFSET $offset " : '');
    }

    public static function union(): string
    {
        return ' UNION ';
    }

    public static function innerJoin($tableName): string
    {
        return " INNER JOIN $tableName ";
    }

    public static function leftJoin($tableName): string
    {
        return " LEFT JOIN $tableName ";
    }

    public static function on($fieldName, $cond, $value, $isRaw = false)
    {
        return " ON ($fieldName $cond " . ($isRaw ? $value : "'$value'") . ') ';
    }

    public static function unixtime($value){
        return "FROM_UNIXTIME({$value})";
    }
}

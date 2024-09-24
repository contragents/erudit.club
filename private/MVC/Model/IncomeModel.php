<?php

class IncomeModel extends BaseModel
{
    const TABLE_NAME = 'income';
    const COMMON_ID_FIELD = self::ID_FIELD;
    const SUDOKU_INCOME_FIELD = 'sudoku';

    const SYSTEM_COMMON_ID = 0;

    public static function changeIncome(int $commonId, float $deltaIncome, string $description = '', ?int $typeId = null, ?int $ref = null): bool
    {
        if (self::getIncome($commonId) === false) {
            if(!self::createIncome($commonId)) {
                return false;
            }
        }

        //return true;

        DB::transactionStart();

        // 1. В поле sudoku таблицы balance записать значение из поля sudoku + $deltabalance
        if(!self::setParam(
            $commonId,
            self::SUDOKU_INCOME_FIELD,
            self::SUDOKU_INCOME_FIELD . ' + ' . $deltaIncome,
            true
        )) {
            DB::transactionRollback();

            return false;
        }

        //return true;

        // 2. Завести запись в историю транзакций о списании/начислении. значения брать из balance.sudoku
        if(!IncomeHistoryModel::addTransaction($commonId, $deltaIncome, $description, $typeId, $ref)) {
            DB::transactionRollback();

            return false;
        }

        DB::transactionCommit();

        return true;
    }

    public static function getIncome(int $commonId)
    {
        return DB::queryValue(
            ORM::select([self::SUDOKU_INCOME_FIELD], self::TABLE_NAME)
            . ORM::where(self::COMMON_ID_FIELD, '=', $commonId, true)
        );
    }

    private static function createIncome(int $commonId): bool
    {
        return (bool)self::add([self::COMMON_ID_FIELD => $commonId]);
    }
}

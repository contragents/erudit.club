<?php

class BalanceModel extends BaseModel
{
    const TABLE_NAME = 'balance';
    const COMMON_ID_FIELD = self::ID_FIELD;
    const SUDOKU_BALANCE_FIELD = 'sudoku';

    const SYSTEM_COMMON_ID = 0;

    public static function changeBalance(int $commonId, int $deltaBalance, string $description = ''): bool
    {
        if (self::getBalance($commonId) === false) {
            if(!self::createBalance($commonId)) {
                return false;
            }
        }

        //return true;

        DB::transactionStart();

        // 1. В поле sudoku таблицы balance записать значение из поля sudoku + $deltabalance
        if(!self::setParam(
            $commonId,
            self::SUDOKU_BALANCE_FIELD,
            self::SUDOKU_BALANCE_FIELD . ' + ' . $deltaBalance,
            true
        )) {
            DB::transactionRollback();

            return false;
        }

        //return true;

        // 2. Завести запись в историю транзакций о списании/начислении. значения брать из balance.sudoku
        if(!BalanceHistoryModel::addTransaction($commonId, $deltaBalance, $description)) {
            DB::transactionRollback();

            return false;
        }

        DB::transactionCommit();

        return true;

    }

    public static function getBalance(int $commonId)
    {
        return DB::queryValue(
            ORM::select([self::SUDOKU_BALANCE_FIELD], self::TABLE_NAME)
            . ORM::where(self::COMMON_ID_FIELD, '=', $commonId, true)
        );
    }

    private static function createBalance(int $commonId): bool
    {
        return (bool)self::add([self::COMMON_ID_FIELD => $commonId]);
    }
}

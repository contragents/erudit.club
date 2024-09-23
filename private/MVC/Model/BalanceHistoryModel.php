<?php

class BalanceHistoryModel extends BaseModel
{
    const TABLE_NAME = 'balance_history';

    const DESCRIPTION_FIELD = 'descr';
    const PREV_BALANCE_FIELD = 'prev_count'; // varchar!
    const NEW_BALANCE_FIELD = 'new_count'; // varchar!
    const CURRENCY_ID_FIELD = 'currency_id'; // 1 - sudoku default

    public static function addTransaction(int $commonId, $deltaBalance, string $description = ''): bool {
        return (bool)self::add(
            [
                self::COMMON_ID_FIELD => $commonId,
                self::DESCRIPTION_FIELD => $description,
                self::PREV_BALANCE_FIELD => new ORM(
                    ORM::skobki(
                        ORM::select([BalanceModel::SUDOKU_BALANCE_FIELD], BalanceModel::TABLE_NAME)
                        . ORM::where(BalanceModel::COMMON_ID_FIELD, '=', $commonId)
                    )
                    . ' - ' . $deltaBalance
                ),
                self::NEW_BALANCE_FIELD => new ORM(
                    ORM::skobki(
                        ORM::select([BalanceModel::SUDOKU_BALANCE_FIELD], BalanceModel::TABLE_NAME)
                        . ORM::where(BalanceModel::COMMON_ID_FIELD, '=', $commonId)
                    )
                ),
            ]
        );
    }
}
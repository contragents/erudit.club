<?php

class IncomeHistoryModel extends BaseModel
{
    const TABLE_NAME = 'income_history';

    const DESCRIPTION_FIELD = 'descr';
    const PREV_BALANCE_FIELD = 'prev_count'; // varchar!
    const NEW_BALANCE_FIELD = 'new_count'; // varchar!
    const CURRENCY_ID_FIELD = 'currency_id'; // 1 - sudoku default
    const TYPE_ID_FIELD = 'transaction_type_id'; // 1 - achieve, 3 - withdraw, 4 - motivation
    const REF_FIELD = 'reference';

    const ACHIEVE_TYPE = BalanceHistoryModel::ACHIEVE_TYPE;
    const WITHDRAW_TYPE = BalanceHistoryModel::WITHDRAW_TYPE;
    const MOTIVATION_TYPE = BalanceHistoryModel::MOTIVATION_TYPE;

    const TYPE_IDS = [
        self::ACHIEVE_TYPE => 1,
        self::WITHDRAW_TYPE => 3,
        self::MOTIVATION_TYPE => 4
    ];


    public static function addTransaction(int $commonId, float $deltaBalance, string $description = '', ?int $typeId = null, ?int $ref = null): bool {
        return (bool)self::add(
            [
                self::COMMON_ID_FIELD => $commonId,
                self::DESCRIPTION_FIELD => $description,
                self::PREV_BALANCE_FIELD => new ORM(
                    ORM::skobki(
                        ORM::select([IncomeModel::SUDOKU_INCOME_FIELD], IncomeModel::TABLE_NAME)
                        . ORM::where(IncomeModel::COMMON_ID_FIELD, '=', $commonId)
                    )
                    . ' - ' . $deltaBalance
                ),
                self::NEW_BALANCE_FIELD => new ORM(
                    ORM::skobki(
                        ORM::select([IncomeModel::SUDOKU_INCOME_FIELD], IncomeModel::TABLE_NAME)
                        . ORM::where(IncomeModel::COMMON_ID_FIELD, '=', $commonId)
                    )
                ),
            ]
            + ($typeId ? [self::TYPE_ID_FIELD => $typeId] : [])
            + ($ref ? [self::REF_FIELD => $ref] : [])
        );
    }
}
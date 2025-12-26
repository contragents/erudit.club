<?php

/**
 * Class BalanceHistoryModel
 * @property int $_id
 * @property int $_common_id
 * @property int $_prev_count
 * @property int $_new_count
 * @property int $_reference
 */

class BalanceHistoryModel extends BaseModel
{
    const TABLE_NAME = 'balance_history';

    const DESCRIPTION_FIELD = 'descr';
    const PREV_BALANCE_FIELD = 'prev_count'; // varchar!
    const NEW_BALANCE_FIELD = 'new_count'; // varchar!
    const CURRENCY_ID_FIELD = 'currency_id'; // 1 - sudoku default
    const TYPE_ID_FIELD = 'transaction_type_id'; // 0 - game (default), 1 - achieve, 2 - deposit, 3 - withdraw, 4 - motivation
    const REF_FIELD = 'reference';

    const GAME_TYPE = 'game';
    const ACHIEVE_TYPE = 'achieve';
    const DEPOSIT_TYPE = 'deposit';
    const WITHDRAW_TYPE = 'withdraw';
    const MOTIVATION_TYPE = 'motivation';
    const GREETING_DEPOSIT_TYPE = 'greeting';
    const CLAIM_INCOME_TYPE = 'claim';

    const TYPE_IDS = [
        self::GAME_TYPE => 0,
        self::ACHIEVE_TYPE => 1,
        self::DEPOSIT_TYPE => 2,
        self::WITHDRAW_TYPE => 3,
        self::MOTIVATION_TYPE => 4,
        self::GREETING_DEPOSIT_TYPE => 5,
        self::CLAIM_INCOME_TYPE => 6,
        self::WINNER_REWARD_TYPE => 7,
    ];
    const WINNER_REWARD_TYPE = 'Winner reward';

    public ?int $_common_id = null;
    public ?int $_prev_count = null;
    public ?int $_new_count = null;
    public ?int $_reference = null;

    public static function addTransaction(
        int $commonId,
        $deltaBalance,
        string $description = '',
        ?int $typeId = null,
        ?int $ref = null
    ): bool {
        if (!$ref) {
            // Ставим $ref равным - последний ID, чтобы соблюдать уникальность индекса
            $ref = -1 * self::getLastID();
        }

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
            + ($typeId ? [self::TYPE_ID_FIELD => $typeId] : [])
            + ([self::REF_FIELD => $ref])
        );
    }

    public static function getDeltaCoins(int $commonId, int $gameId): int
    {
        $balanceHistoryModelArr = self::getCustomComplexO(
            [self::COMMON_ID_FIELD, self::REF_FIELD],
            ['=', '='],
            [$commonId, $gameId],
            true
        );

        $inBalance = 0;
        $outBalance = 0;

        foreach ($balanceHistoryModelArr as $balanceHistoryModel) {
            $inBalance += $balanceHistoryModel->_prev_count;
            $outBalance += $balanceHistoryModel->_new_count;
        }

        return $outBalance - $inBalance;
    }
}
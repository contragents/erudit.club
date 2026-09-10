<?php

/**
 * Class GameCounterModel
 * @property int $_id
 */

class GameCounterModel extends BaseModel
{
    const TABLE_NAME = 'game_counter';

    const COUNTER_NAME_FIELD = 'counter_name';
    const COUNTER_NAME = 'game';

    /** Уникальное имя счетчика  */
    public string $_counter_name = self::COUNTER_NAME;

    public static function getNewGameId(): int
    {
        return self::setLastGameId(self::getLastGameId() + 1) ?? 1;
    }

    public static function getLastGameId(): int
    {
        $lastId = self::find()->one()->_id ?? null;

        if($lastId) return $lastId;

        $lastCachedId = Cache::get(Queue::GAMES_COUNTER) ?: null;

        return self::setLastGameId($lastCachedId);
    }

    private static function setLastGameId(?int $lastCachedId = null): ?int
    {
        $counterModel = self::find()->one() ?? new self();
        $counterModel->_id = $lastCachedId ?? max(GamesModel::getLastID(), $counterModel->_id ?? 0);

        return $counterModel->save() ? $counterModel->_id : null;
    }

    public function save(): bool
    {
        $updateQuery = "INSERT INTO game_counter (id, counter_name) 
            VALUES ($this->_id, '$this->_counter_name') 
            ON DUPLICATE KEY UPDATE id = $this->_id";

        return !!DB::queryInsert($updateQuery);
    }
}

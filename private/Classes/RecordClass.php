<?php

/**
 * @inheritDoc
 * @property string $cookie
 */
class Record extends \AchievesModel
{
    public ?string $cookie = null;

    /**
     * Получаем модель рекорда
     * @param string $type
     * @param string $period
     * @param bool $forceCurrentPeriod
     * @return self|null
     */
    public static function getRecord(
        string $type = self::GAMES_PLAYED,
        string $period = self::DAY_PERIOD,
        string $gameName = Game::ERUDIT,
        bool $forceCurrentPeriod = true
    ): ?self {
        if (!in_array($type, self::VALID_RECORD_TYPES) || !in_array($period, self::VALID_PERIODS)) {
            return null;
        }

        $gameNameCondition = [];
        if($gameName) {
            if (!in_array($gameName, array_keys(BaseModel::GAME_IDS))) {
                return null;
            } else {
                $gameNameCondition = [self::GAME_NAME_ID_FIELD => BaseModel::GAME_IDS[$gameName]];
            }
        }

        $timeConditionArr = [];
        if ($forceCurrentPeriod) {
            switch ($period) {
                case self::DAY_PERIOD:
                    $timestamp = date('Y-m-d');
                    break;
                case self::WEEK_PERIOD:
                    $timestamp = date('Y-m-d', strtotime('monday this week'));
                    break;
                case self::MONTH_PERIOD:
                    $timestamp = date('Y-m-01');
                    break;
                case self::YEAR_PERIOD:
                    $timestamp = date('Y-01-01');
                    break;
                default:
                    $timestamp = date('Y-m-d');
            }

            $timeConditionArr = [
                'field_name' => self::DATE_ACHIEVED_FIELD,
                'condition' => '>',
                'value' => $timestamp,
                'raw' => false
            ];
        }

        return self::find()->where(
            [
                self::EVENT_TYPE_FIELD => $type,
                self::EVENT_PERIOD_FIELD => $period,
                self::IS_ACTIVE_FIELD => true,
            ]
            + [$timeConditionArr] // Условие по времени
            + $gameNameCondition // Условие по id вида игры
        )->one();
    }
}
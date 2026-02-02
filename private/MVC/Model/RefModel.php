<?php

/**
 * @property int $_id
 * @property int $_common_id whose invite link was common_id
 * @property int $_ref_common_id common_id given to referral
 * @property int $_ref_tg_id tg_id of referral
 * @property string $_name
 * @property string $_created_at
 * @property bool $_is_active Ref marked active after fraud check
 **/

class RefModel extends BaseModel
{
    const TABLE_NAME = 'refs';

    const COMMON_ID_FIELD = 'common_id';
    const REF_TG_ID_FIELD = 'ref_tg_id';
    const NAME_FIELD = 'name';
    const IS_ACTIVE_FIELD = 'is_active';

    const REF_COMMON_ID_FIELD = 'ref_common_id';
    const MAX_EMPTY_REFS = 5; // Максимальное количество рефералов, которые не играют (фейковые)
    const MIN_GAMES_PER_REF = 10; // Число игр на одного активного реферала, чтобы начислить более 5ти рефов
    public ?int $_common_id = null;
    public ?int $_ref_common_id = null;
    public ?int $_ref_tg_id = null;
    public ?string $_name = null;
    public ?string $_created_at = null;
    public bool $_is_active = false;

    public static function addRef(int $commonId, int $refTgId, string $name = ''): bool
    {
        $ref = self::new(
            [
                self::COMMON_ID_FIELD => $commonId,
                self::REF_TG_ID_FIELD => $refTgId,
                self::NAME_FIELD => $name,
                self::IS_ACTIVE_FIELD => true,
            ]
        );

        return $ref->save();
    }

    /**
     * Checks and creates new Referral. Makes it active if checking passed. Giving rewards
     * @param $fromCommonId
     * @param $refCommonId
     * @return bool|null True is ref is registered and became active, false - registered only, null - not registered
     */
    public static function register($fromCommonId, $refCommonId, ?string $name = null): ?bool
    {
        LogModel::add([
                          LogModel::CATEGORY_FIELD => 'register_test',
                          LogModel::MESSAGE_FIELD => [
                              '$fromCommonId' => $fromCommonId,
                              '$refCommonId' => $refCommonId,
                              '$name' => $name
                          ]
                      ]);

        if (!ctype_digit((string)$fromCommonId) || !ctype_digit((string)$refCommonId)) {
            return null;
        }

        // Проверим, что реферал уже создан
        if (self::find()->where([self::REF_COMMON_ID_FIELD => $refCommonId])->exists()) {
            return null;
        }

        $ref = self::new([
                             self::COMMON_ID_FIELD => $fromCommonId,
                             self::REF_COMMON_ID_FIELD => $refCommonId,
                             self::NAME_FIELD => $name,
                         ]);

        LogModel::add([
            LogModel::CATEGORY_FIELD => 'register_test',
            LogModel::MESSAGE_FIELD => ['$ref' => $ref]
                      ]);

        if ($ref->save() ?? false) {
            $activeUserRefs = self::find()
                ->where(
                    [
                        self::COMMON_ID_FIELD => $fromCommonId,
                        self::IS_ACTIVE_FIELD => true,
                        self::REF_COMMON_ID_FIELD => new ORM('IS NOT NULL')
                    ]
                )->count();

            if ($activeUserRefs >= self::MAX_EMPTY_REFS) {
                $totalRefsGamesPlayed = self::getNumGamesPlayedByCommonIdRefs($fromCommonId);
            }

            // Если рефералов менее 5 штук, то следующего делаем активным автоматом
            // Иначе, считаем эффективность рефов - должно быть в среднем 10 игр на реферала
            if ($activeUserRefs < self::MAX_EMPTY_REFS || ($totalRefsGamesPlayed / $activeUserRefs >= self::MIN_GAMES_PER_REF)) {
                $ref->_is_active = true;

                return $ref->save()
                    && self::setRewardToRef($ref) // Начислили бонус Рефу
                    && self::setRewardForRef($ref); // Начислили бонус Рефоводу и выдали/обновили карточку партнера
            }

            return false;
        }

        return null; // Ref не создан по какимто причинам
    }

    public
    static function setRewardToRef(
        ?self $ref
    ): bool {
        if (!$ref) {
            return false;
        }

        return BalanceModel::changeBalance(
            $ref->_ref_common_id,
            MonetizationService::REWARD[AchievesModel::DAY_PERIOD],
            'Referral bonus to ' . $ref->_name ?? $ref->_ref_common_id,
            BalanceHistoryModel::TYPE_IDS[BalanceHistoryModel::MOTIVATION_TYPE]
        );
    }

    public
    static function setRewardForRef(
        ?self $ref
    ): bool {
        if (!$ref) {
            return false;
        }

        if (BalanceModel::changeBalance(
            $ref->_common_id,
            MonetizationService::REWARD[AchievesModel::DAY_PERIOD],
            'Referral bonus for ' . $ref->_ref_common_id,
            BalanceHistoryModel::TYPE_IDS[BalanceHistoryModel::MOTIVATION_TYPE]
        )) {
            // Обновляем/создаем карточку патрона
            $patreonAchieveModel = AchievesModel::getPatreonAchievesByCommonId($ref->_common_id)[0]
                ?? AchievesModel::new([
                                          AchievesModel::COMMON_ID_FIELD => $ref->_common_id,
                                          AchievesModel::EVENT_TYPE_FIELD => AchievesModel::PATREON_TYPE,
                                          AchievesModel::EVENT_PERIOD_FIELD => AchievesModel::DAY_PERIOD,
                                          AchievesModel::REWARD_FIELD => 0,
                                          AchievesModel::INCOME_FIELD => MonetizationService::PATREON_INCOME[AchievesModel::DAY_PERIOD],
                                          AchievesModel::GAME_NAME_ID_FIELD => BaseModel::GAME_IDS[BaseModel::ALL_GAMES],
                                          AchievesModel::IS_ACTIVE_FIELD => true,
                                      ]);
            // Начисляем аналог в рублях за приз в монетах
            $patreonAchieveModel->_event_value += MonetizationService::REWARD[AchievesModel::DAY_PERIOD] * MonetizationService::SUDOKU_PRICE;
            // Повышаем уровень патрона, если он достигнут
            $nextLevel = Record::nextLevel($patreonAchieveModel->_event_period);
            if ($patreonAchieveModel->_event_value > MonetizationService::PATREON_LEVELS[$nextLevel]) {
                $patreonAchieveModel->_event_period = $nextLevel;
                $patreonAchieveModel->_income = MonetizationService::PATREON_INCOME[$patreonAchieveModel->_event_period];
            }

            if (!$patreonAchieveModel->save()) {
                LogModel::add([
                                  LogModel::CATEGORY_FIELD => LogModel::CATEGORY_REFERRAL_NOTIFY,
                                  LogModel::MESSAGE_FIELD => $patreonAchieveModel->toArray(),
                              ]);

                return false;
            }

            return true;
        }

        return false;
    }

    public
    static function getNumGamesPlayedByCommonIdRefs(
        $fromCommonId
    ): int {
        return
            RatingHistoryModel::find()
                ->where([
                            [
                                RatingHistoryModel::COMMON_ID_FIELD,
                                'in',
                                new ORM( // todo можно переделать RefModel::find()->where()->getSQL()..
                                    RefModel::select([RefModel::REF_COMMON_ID_FIELD],
                                                     true,
                                                     ORM::where(
                                                         RefModel::REF_COMMON_ID_FIELD,
                                                         '=',
                                                         $fromCommonId,
                                                         true
                                                     )
                                                     . ORM::andWhere(
                                                         RefModel::REF_COMMON_ID_FIELD,
                                                         'IS',
                                                         'NOT NULL',
                                                         true
                                                     ))
                                ),
                                true
                            ]
                        ])->count();
    }
}
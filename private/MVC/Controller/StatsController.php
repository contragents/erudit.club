<?php

class StatsController extends BaseController
{
    const NO_STONE_PARAM = 'no_stone';
    const NO_BRONZE_PARAM = 'no_bronze';
    const NO_SILVER_PARAM = 'no_silver';
    const NO_GOLD_PARAM = 'no_gold';
    const FILTER_PLAYER_PARAM = 'opponent_id';

    const COMMON_URL = 'mvc/stats/';

    private static function addTranslationsToAchieves(array &$achieves): void
    {
        foreach($achieves as &$achieve) {
            if ($achieve['event_type'] === AchievesModel::TOP_TYPE) {
                $achieve['record_type_text'] = T::S('rank position');
                $achieve['event_type_text'] = T::S(AchievesModel::TOP_TYPE .'_'. $achieve[AchievesModel::EVENT_PERIOD_FIELD]);
                $achieve['points_text'] = $achieve[AchievesModel::EVENT_VALUE_FIELD];
            } else {
                $achieve['record_type_text'] = T::S('record of the ' . $achieve[AchievesModel::EVENT_PERIOD_FIELD]);
                $achieve['event_type_text'] = T::S($achieve[AchievesModel::EVENT_TYPE_FIELD]);
                $achieve['points_text'] = $achieve['word'] == ''
                    ?  $achieve[AchievesModel::EVENT_VALUE_FIELD]
                    : ($achieve['word'] . ' - ' . $achieve[AchievesModel::EVENT_VALUE_FIELD]);
            }

            $achieve[AchievesModel::REWARD_FIELD] = self::trimRightZeros(MonetizationService::REWARD[$achieve[AchievesModel::EVENT_PERIOD_FIELD]],2);
            $achieve[AchievesModel::INCOME_FIELD] = self::trimRightZeros(MonetizationService::INCOME[$achieve[AchievesModel::EVENT_PERIOD_FIELD]],4);
        }
    }

    public static function trimRightZeros(string $value, int $numDecimals = 4): string
    {
        $value = number_format($value, $numDecimals, '.', ' ');

        for ($i = 0; $i < $numDecimals; $i++) {
            $value = rtrim($value, '0');
        }

        return rtrim($value, '.,');
    }

    private static function getViewFilters(): array
    {
        return [
            self::NO_STONE_PARAM => self::$Request[self::NO_STONE_PARAM] ?? false,
            self::NO_BRONZE_PARAM => self::$Request[self::NO_BRONZE_PARAM] ?? false,
            self::NO_SILVER_PARAM => self::$Request[self::NO_SILVER_PARAM] ?? false,
            self::NO_GOLD_PARAM => self::$Request[self::NO_GOLD_PARAM] ?? false,
        ];
    }

    private static function getGamesFilters(): array
    {
        return [
            self::FILTER_PLAYER_PARAM => self::$Request[self::FILTER_PLAYER_PARAM] ?? false,
        ];
    }

    public function Run()
    {
        return parent::Run();
    }

    public function prizesAction(): string
    {
        $res = '';

        foreach(AchievesModel::PRIZE_LINKS as $link) {
            $res .= ViewHelper::img(['src' => '/' . $link]);
        }

        return $res;
    }

    public function gamesV2Action(bool $initialRequest = false)
    {
        $baseUrl = self::getUrl('gamesV2', self::$Request, ['page']);

        $gamesCount = AchievesModel::getGamesByCommonIdCount(self::$Request['common_id'], self::getGamesFilters());

        if ($gamesCount < ((self::$Request['page'] ?? 1) - 1) * AchievesModel::LIMIT) {
            unset(self::$Request['page']);
        }

        $baseUrlPage = self::getUrl('gamesV2', self::$Request);

        $games = AchievesModel::getGamesByCommonIdV2(
            self::$Request['common_id'],
            AchievesModel::LIMIT,
            self::$Request['page'] ?? 1,
            self::getGamesFilters()
        );

        $gamesCount = AchievesModel::getGamesByCommonIdCount(self::$Request['common_id'], self::getGamesFilters());

        if ($gamesCount < ((self::$Request['page'] ?? 1) - 1) * AchievesModel::LIMIT) {
            unset(self::$Request['page']);
        }

        $pagination = ViewHelper::paginationArr(
            StatsController::$Request['page'] ?? 1,
            ceil($gamesCount / AchievesModel::LIMIT),
            $baseUrl
        );
        $res = ['games' => $games, 'pagination' => $pagination];

        if ($initialRequest) {
            return $res;
        } else {
            if(self::getGamesFilters()[self::FILTER_PLAYER_PARAM]) {
                $opponentStats = AchievesModel::getStatsVsOpponent(self::$Request['common_id'], self::getGamesFilters()[self::FILTER_PLAYER_PARAM]);

                $res['opponent_stats'] = $opponentStats;
            }

            return json_encode($res, JSON_UNESCAPED_UNICODE);
        }
    }

    public function viewV2Action(): string
    {
        //ini_set("display_errors", 1);
        //error_reporting(E_ALL);
        try {
        $result = ['player_name' => AchievesModel::getPlayerNameByCommonId(self::$Request['common_id']), 'player_avatar_url' => PlayerModel::getAvatarUrl(self::$Request['common_id'])];

        $baseUrl = self::getUrl('viewV2', self::$Request, ['page']);

        $baseUrlPage = self::getUrl('viewV2', self::$Request);

        $achieves = AchievesModel::getCurrentAchievesByCommonId(self::$Request['common_id']);
        $pastAchieves = AchievesModel::getPastAchievesByCommonId(self::$Request['common_id']);

        self::addTranslationsToAchieves($achieves);
        self::addTranslationsToAchieves($pastAchieves);

        $result['current_achieves'] = $achieves;
        $result['past_achieves'] = $pastAchieves;

        $result += $this->gamesV2Action(true);
            } catch(Throwable $e) {
            return json_encode(['exception' => $e->__toString()] , JSON_UNESCAPED_UNICODE);
        }

        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    public function viewAction()
    {

        $baseUrl = self::getUrl('view', self::$Request, ['page']);

        $achievesCount = AchievesModel::getAchievesByCommonIdCount(self::$Request['common_id'], self::getViewFilters());
        if ($achievesCount < ((self::$Request['page'] ?? 1) - 1) * AchievesModel::LIMIT) {
            unset(self::$Request['page']);
        }

        $baseUrlPage = self::getUrl('view', self::$Request);

        $achieves = AchievesModel::getAchievesByCommonId(
            self::$Request['common_id'],
            AchievesModel::LIMIT,
            self::$Request['page'] ?? 1,
            self::getViewFilters()
        );

        foreach ($achieves as $num => $row) {
            $achieves[$num][AchievesModel::EVENT_TYPE_FIELD] = ViewHelper::tag(
                'img',
                '',
                [
                    'src' => '/' . (AchievesModel::PRIZE_LINKS[$row[AchievesModel::EVENT_TYPE_FIELD]] ?? ''),
                    'width' => '100%',
                    'alt' => 'Пусто'
                ]
            );

            $achieves[$num][AchievesModel::DATE_ACHIEVED_FIELD] = ViewHelper::tag(
                'span',
                $row[AchievesModel::DATE_ACHIEVED_FIELD],
                [
                    'style' => 'white-space: nowrap;'
                ]
            );
        }

        if (BaseController::isAjaxRequest()) {
            return StatsAchievesView::render($baseUrl, $baseUrlPage, $achieves, $achievesCount);
        } else {
            return StatsAchievesView::renderFull([$baseUrl, $baseUrlPage, $achieves, $achievesCount]);
        }
    }

    public function testAction()
    {
        return var_export(self::isAjaxRequest(), true);
    }

    public function gamesAction()
    {
        $baseUrl = self::getUrl('games', self::$Request, ['page']);

        $gamesCount = AchievesModel::getGamesByCommonIdCount(self::$Request['common_id'], self::getGamesFilters());

        if ($gamesCount < ((self::$Request['page'] ?? 1) - 1) * AchievesModel::LIMIT) {
            unset(self::$Request['page']);
        }

        $baseUrlPage = self::getUrl('games', self::$Request);

        $games = AchievesModel::getGamesByCommonId(
            self::$Request['common_id'],
            AchievesModel::LIMIT,
            self::$Request['page'] ?? 1,
            self::getGamesFilters()
        );

        if(self::getGamesFilters()[self::FILTER_PLAYER_PARAM]) {
            $opponentStats = AchievesModel::getStatsVsOpponent(self::$Request['common_id'], self::getGamesFilters()[self::FILTER_PLAYER_PARAM]);
        } else $opponentStats = false;


        if (BaseController::isAjaxRequest()) {
            return StatsAchievesGamesView::render($baseUrl, $baseUrlPage, $games, $gamesCount, $opponentStats);
        } else {
            return StatsAchievesGamesView::renderFull([$baseUrl, $baseUrlPage, $games, $gamesCount, $opponentStats]);
        }
    }
}
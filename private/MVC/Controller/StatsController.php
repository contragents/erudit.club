<?php

class StatsController extends BaseController
{
    const NO_STONE_PARAM = 'no_stone';
    const NO_BRONZE_PARAM = 'no_bronze';
    const NO_SILVER_PARAM = 'no_silver';
    const NO_GOLD_PARAM = 'no_gold';
    const FILTER_PLAYER_PARAM = 'opponent_id';

    const COMMON_URL = 'mvc/stats/';

    public static function getUrl(string $action, array $params = [], array $excludedParams = [])
    {
        return self::COMMON_URL
            . $action . '/'
            . (!empty($params)
                ? ('?' . implode(
                        '&',
                        array_map(
                            fn($param, $value) => !in_array($param, $excludedParams) ? "$param=$value" : null,
                            array_keys($params),
                            $params
                        )
                    ))
                : '');
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
        ini_set("display_errors", 1);
        error_reporting(E_ALL);

        return parent::Run();
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

        return StatsAchievesView::render($baseUrl, $baseUrlPage, $achieves, $achievesCount);
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

        return StatsAchievesGamesView::render($baseUrl, $baseUrlPage, $games, $gamesCount, $opponentStats);
    }
}
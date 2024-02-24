<?php

class StatsController extends BaseController
{
    const SMALL_ZHETON_WIDTH = '20%';
    const ACHIEVES_ELEMENT_ID = 'achieves_table';
    const NO_STONE_PARAM = 'no_stone';
    const NO_BRONZE_PARAM = 'no_bronze';
    const NO_SILVER_PARAM = 'no_silver';
    const NO_GOLD_PARAM = 'no_gold';
    const OFF_OPACITY = 0.3;

    private static function getFilters()
    {
        return [
            self::NO_STONE_PARAM => self::$Request[self::NO_STONE_PARAM] ?? false,
            self::NO_BRONZE_PARAM => self::$Request[self::NO_BRONZE_PARAM] ?? false,
            self::NO_SILVER_PARAM => self::$Request[self::NO_SILVER_PARAM] ?? false,
            self::NO_GOLD_PARAM => self::$Request[self::NO_GOLD_PARAM] ?? false,
        ];
    }

    public function Run()
    {
        return parent::Run();
    }

    public function viewAction()
    {
        ini_set("display_errors", 1);
        error_reporting(E_ALL);

        $baseUrl = 'mvc/stats/view/?';
        $baseUrl .= implode('&', array_map(
            fn($param, $value) => $param != 'page' ? "$param=$value" : null,
            array_keys(self::$Request),
            self::$Request
        ));

        $achievesCount = AchievesModel::getAchievesByCommonIdCount(self::$Request['common_id'], self::getFilters());
        if ($achievesCount < ((self::$Request['page'] ?? 1) - 1) * AchievesModel::LIMIT) {
            unset(self::$Request['page']);
        }

        $baseUrlPage = $baseUrl . (self::$Request['page'] ?? false ? '&page=' . self::$Request['page'] : '');



        $achieves = AchievesModel::getAchievesByCommonId(
            self::$Request['common_id'],
            AchievesModel::LIMIT,
            self::$Request['page'] ?? 1,
            self::getFilters()
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
        }

        $attributeLabels = AchievesModel::ATTRIBUTE_LABELS;
        $attributeLabels[AchievesModel::EVENT_TYPE_FIELD] .= ViewHelper::tagOpen('br')
            . ViewHelper::tag(
                'img',
                '',
                [
                    'src' => '/' . AchievesModel::PRIZE_LINKS['game_price-day'],
                    'width' => self::SMALL_ZHETON_WIDTH,
                    'onClick' => "refreshId('" . self::ACHIEVES_ELEMENT_ID . "', '" . str_replace(self::NO_STONE_PARAM, 'none', $baseUrlPage) . (self::$Request[self::NO_STONE_PARAM] ?? false ? '' : '&' . self::NO_STONE_PARAM . '=1') . "')",
                    'style' => 'opacity: '. (self::$Request[self::NO_STONE_PARAM] ?? false ? self::OFF_OPACITY : 1),
                ]
            )
            . ViewHelper::tag(
                'img',
                '',
                [
                    'src' => '/' . AchievesModel::PRIZE_LINKS['game_price-week'],
                    'width' => self::SMALL_ZHETON_WIDTH,
                    'onClick' => "refreshId('" . self::ACHIEVES_ELEMENT_ID . "', '" . str_replace(self::NO_BRONZE_PARAM, 'none', $baseUrlPage) . (self::$Request[self::NO_BRONZE_PARAM] ?? false ? '' : '&' . self::NO_BRONZE_PARAM . '=1') . "')",
                    'style' => 'opacity: '. (self::$Request[self::NO_BRONZE_PARAM] ?? false ? self::OFF_OPACITY : 1),
                ]
            )
            . ViewHelper::tag(
                'img',
                '',
                [
                    'src' => '/' . AchievesModel::PRIZE_LINKS['game_price-month'],
                    'width' => self::SMALL_ZHETON_WIDTH,
                    'onClick' => "refreshId('" . self::ACHIEVES_ELEMENT_ID . "', '" . str_replace(self::NO_SILVER_PARAM, 'none', $baseUrlPage) . (self::$Request[self::NO_SILVER_PARAM] ?? false ? '' : '&' . self::NO_SILVER_PARAM . '=1') . "')",
                    'style' => 'opacity: '. (self::$Request[self::NO_SILVER_PARAM] ?? false ? self::OFF_OPACITY : 1),
                ]
            )
            . ViewHelper::tag(
                'img',
                '',
                [
                    'src' => '/' . AchievesModel::PRIZE_LINKS['game_price-year'],
                    'width' => self::SMALL_ZHETON_WIDTH,
                    'onClick' => "refreshId('" . self::ACHIEVES_ELEMENT_ID . "', '" . str_replace(self::NO_GOLD_PARAM, 'none', $baseUrlPage) . (self::$Request[self::NO_GOLD_PARAM] ?? false ? '' : '&' . self::NO_GOLD_PARAM . '=1') . "')",
                    'style' => 'opacity: '. (self::$Request[self::NO_GOLD_PARAM] ?? false ? self::OFF_OPACITY : 1),
                ]
            );

        return json_encode(
            [
                'message' => ViewHelper::tag(
                    (self::$Request['refresh'] ?? false) ? '' : 'div',
                    ViewHelper::renderGridFromQueryResult(
                        $achieves,
                        'Достижения игрока',
                        $attributeLabels
                    ),
                    ['id' => 'achieves_table']
                ),
                'pagination' => ViewHelper::pagination(
                    self::$Request['page'] ?? 1,
                    ceil($achievesCount / AchievesModel::LIMIT),
                    self::ACHIEVES_ELEMENT_ID,
                    $baseUrl
                )
            ],
            JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES
        );
    }
}
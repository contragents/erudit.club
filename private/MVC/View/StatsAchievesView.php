<?php

class StatsAchievesView
{
    public const ACHIEVES_ELEMENT_ID = 'achieves_table';
    public const SMALL_ZHETON_WIDTH = '20%';
    public const OFF_OPACITY = 0.3;

    const ZHETONS_FILTERS = [
        StatsController::NO_STONE_PARAM => AchievesModel::PRIZE_LINKS['game_price-day'],
        StatsController::NO_BRONZE_PARAM => AchievesModel::PRIZE_LINKS['game_price-week'],
        StatsController::NO_SILVER_PARAM => AchievesModel::PRIZE_LINKS['game_price-month'],
        StatsController::NO_GOLD_PARAM => AchievesModel::PRIZE_LINKS['game_price-year'],
    ];

    public static function render($baseUrl, $baseUrlPage, $achieves, $count, $some = false): string
    {
        $attributeLabels = AchievesModel::ATTRIBUTE_LABELS;
        $attributeLabels[AchievesModel::EVENT_TYPE_FIELD] .= ViewHelper::tagOpen('br');

        foreach (self::ZHETONS_FILTERS as $filter => $link) {
            $attributeLabels[AchievesModel::EVENT_TYPE_FIELD] .= ViewHelper::tag(
                    'img',
                    '',
                    [
                        'src' => '/' . $link,
                        'width' => self::SMALL_ZHETON_WIDTH,
                        'onClick' => "refreshId('" . self::ACHIEVES_ELEMENT_ID . "', '"
                            . str_replace($filter, 'none', $baseUrlPage)
                            . (StatsController::$Request[$filter] ?? false ? '' : "&$filter=1")
                            . "')",
                        'style' => 'opacity: ' . (StatsController::$Request[$filter] ?? false ? self::OFF_OPACITY : 1),
                        'title' => StatsController::$Request[$filter] ?? false ? 'Снять фильтр' : 'Фильтровать'
                    ]
                );
        }

        return json_encode(
            [
                'message' => ViewHelper::tag(
                    (StatsController::$Request['refresh'] ?? false) ? '' : 'div',
                    ViewHelper::renderGridFromQueryResult(
                        $achieves,
                        'Достижения игрока | '
                        . ViewHelper::tag(
                            'a',
                            'Партии',
                            [
                                'onClick' => "refreshId('" . self::ACHIEVES_ELEMENT_ID . "', '"
                                    . StatsController::getUrl(
                                        'games',
                                        [
                                            'common_id' => StatsController::$Request['common_id'] ?? '',
                                            'refresh' => '1',
                                        ]
                                    )
                                    . "')",
                                'class' => "link-underline-primary",
                            ]
                        ),
                        $attributeLabels
                    ),
                    ['id' => 'achieves_table']
                ),
                'pagination' => ViewHelper::pagination(
                    StatsController::$Request['page'] ?? 1,
                    ceil($count / AchievesModel::LIMIT),
                    self::ACHIEVES_ELEMENT_ID,
                    $baseUrl
                )
            ],
            JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES
        );
    }
}

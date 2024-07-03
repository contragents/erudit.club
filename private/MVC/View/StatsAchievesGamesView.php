<?php

class StatsAchievesGamesView extends StatsAchievesView
{
    public static function render($baseUrl, $baseUrlPage, $games, $count, $opponentStats = false): string
    {
        $attributeLabels = AchievesModel::ATTRIBUTE_LABELS;
        $attributeLabels[AchievesModel::EVENT_TYPE_FIELD] .= ViewHelper::tagOpen('br');

        return json_encode(
            [
                'message' => ViewHelper::tag(
                    (StatsController::$Request['refresh'] ?? false) ? '' : 'div',
                    ViewHelper::renderGridFromQueryResult(
                        $games,
                        ViewHelper::tag(
                            'a',
                            self::PLAYER_ACHIEVES_MSG . ' ' . ViewHelper::tag('strong',AchievesModel::getPlayerNameByCommonId(StatsController::$Request['common_id'] ?? 0)),
                            [
                                'onClick' => ViewHelper::onClick(
                                    'refreshId',
                                    AchievesModel::ACHIEVES_ELEMENT_ID,
                                    StatsController::getUrl(
                                        'view',
                                        [
                                            'common_id' => StatsController::$Request['common_id'] ?? '',
                                            'refresh' => '1',
                                        ]
                                    )),
                                'href' => '/' . StatsController::getUrl(
                                    'view',
                                    [
                                        'common_id' => StatsController::$Request['common_id'] ?? '',
                                        'refresh' => '1',
                                    ]
                                ),
                                'class' => "link-underline-primary",
                            ]
                        )
                        . ' | Партии',
                        $attributeLabels
                    ),
                    ['id' => AchievesModel::ACHIEVES_ELEMENT_ID]
                )
                . ($opponentStats
                    ? ViewHelper::renderGridFromQueryResult($opponentStats, '', $attributeLabels)
                    : ''),
                'pagination' => ViewHelper::pagination(
                    StatsController::$Request['page'] ?? 1,
                    ceil($count / AchievesModel::LIMIT),
                    $baseUrl
                )
            ],
            JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES
        );
    }
}

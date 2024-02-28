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
                            'Достижения игрока',
                            [
                                'onClick' => "refreshId('" . self::ACHIEVES_ELEMENT_ID . "', '"
                                    . StatsController::getUrl(
                                        'view',
                                        [
                                            'common_id' => StatsController::$Request['common_id'] ?? '',
                                            'refresh' => '1',
                                        ]
                                    )
                                    . "')",
                                'class' => "link-underline-primary",
                            ]
                        )
                        . ' | Партии',
                        $attributeLabels
                    ),
                    ['id' => 'achieves_table']
                )
                . ($opponentStats
                    ? ViewHelper::renderGridFromQueryResult($opponentStats, '', $attributeLabels)
                    : ''),
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

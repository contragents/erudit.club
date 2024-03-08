<?php

/**
 * @var array $deltaRating
 * @var array $user
 * @var array $rating
 * @var bool $canDeleteBan
 * @var int|false $currentUserCommonId
 * @var int|false $commonId
 */

if (isset($_POST['vertical'])) {
    $vertical = true;
} else {
    $vertical = false;
}
$top = $rating['top'];
$marginTop = '';

return ViewHelper::tag(
    'tr',
    ViewHelper::tag(
        'td',
        ViewHelper::tag(
            'table',
            ViewHelper::tag(
                'tr',
                ViewHelper::tag(
                    'td',
                    ($user['ID'] == $this->User
                        ? "Вы"
                        : $this->gameStatus['users'][$this->gameStatus[$user['ID']]]['username'])
                    . (!$rating['isActive']
                        ? '&nbsp;<img title="Игрок отключился" height="24px" src="https://xn--d1aiwkc2d.club/img/no-network-logo.png" />'
                        : '')
                    . ViewHelper::br()
                    . ViewHelper::tag(
                        'button',
                        $rating['playerName'],
                        [
                            'class' => 'btn btn-sm btn-outline-primary',
                            'style' => 'word-break: break-word;',
                            $user['ID'] == $this->User ? 'id' : 'none' => 'playersNikname',
                            'title' => 'Открыть статистику игрока',
                            'onClick' => ViewHelper::onClick(
                                'refreshId',
                                AchievesModel::ACHIEVES_ELEMENT_ID,
                                StatsController::getUrl(
                                    'view',
                                    [
                                        'common_id' => $user['common_id'] ?? '',
                                        'refresh' => '1',
                                    ]
                                )
                            )
                        ]
                    )
                    . ($canDeleteBan
                        ? (ViewHelper::br() . ViewHelper::tag(
                                'button',
                                'Снять Бан',
                                [
                                    'class' => 'btn btn-success mt-2',
                                    'onclick' => "deleteBan($currentUserCommonId);return false;"
                                ]
                            ))
                        : ''),
                    ['width' => '50%']
                ) // td1
                . ViewHelper::tag(
                    'td',
                    ($rating['playerAvatarUrl']
                        ? ViewHelper::img(
                            [
                                'style' => 'border-radius: 5px 5px 5px 5px;' . ($vertical === true ? '' : 'padding-top:0px;'),
                                'alt' => '😰',
                                'src' => $rating['playerAvatarUrl'],
                                'height' => '75px',
                                'max-width' => '100px'
                            ]
                        )
                        : ''),
                    ['width' => '50%']
                ) // td1.1
            ), // tr
            ['width' => '100%']
        ) // table
    ) // td1.2
    . ViewHelper::tag(
        'td',
        ViewHelper::tag('strong', $rating['rating'])
        . ViewHelper::tag(
            'span',
            ViewHelper::nbsp() . ($deltaRating['delta'] > 0 ? '+' : '') . $deltaRating['delta'],
            [
                'title' => 'Последнее изменение рейтинга',
                'style' => 'color:' . ($deltaRating['delta'] <= 0 ? 'indianred' : 'lawngreen'),
            ],$deltaRating !== false
        )
        . (is_numeric($top = $rating['top'])
            ? ($top <= 3
                ? (ViewHelper::br() . ViewHelper::tag(
                        'img',
                        '',
                        [
                            'style' => 'cursor: pointer;',
                            'title' => 'Кликните для увеличения изображения',
                            'id' => $user['ID'],
                            'onclick' => ViewHelper::onClick(
                                'showFullImage',
                                $user['ID'],
                                500
                            ),
                            'src' => 'https://xn--d1aiwkc2d.club/img/prizes/top_$top.svg',
                            'width' => '192px'
                        ]
                    ))
                : ($top <= 10
                    ? (" " . ViewHelper::tag('strong', "№$top") . ViewHelper::br() . ViewHelper::tag(
                            'img',
                            '',
                            [
                                'style' => 'cursor: pointer;',
                                'title' => 'Кликните для увеличения изображения',
                                'id' => $user['ID'],
                                'onclick' => ViewHelper::onClick(
                                    'showFullImage',
                                    $user['ID'],
                                    500
                                ),
                                'src' => 'https://xn--d1aiwkc2d.club/img/prizes/top_10.svg',
                                'width' => '192px'
                            ]
                        ))
                    : (ViewHelper::br() . ViewHelper::tag('strong', "№$top"))))
            : ''),
        ['class' => 'text-center']
    ) // td2
    . ViewHelper::tag('td', ViewHelper::tag('strong', $rating['games_played']), ['class' => 'text-center'], !$vertical),
    ['style' => 'border-top:1px solid black !important;']
); // tr
<?php
//ini_set("display_errors", 1); error_reporting(E_ALL);
if (isset($_POST['vertical'])) {
    $vertical = true;
} else {
    $vertical = false;
}

return ViewHelper::tagOpen(
    'form',
    ViewHelper::tagOpen(
        'table',
        ViewHelper::tagOpen('col', '', ['style' => 'width:65%'])
        . ViewHelper::tagOpen('col', '', ['style' => 'width:35%'])
        . ViewHelper::tagOpen(
            'thead',
            ViewHelper::tag(
                'tr',
                ViewHelper::tag('th', 'Игроки', ['scope' => 'col', 'class' => 'align-top text-center'])
                . ViewHelper::tag('th', 'Рейтинг&nbsp;/ ТОП', ['scope' => 'col', 'class' => 'align-top text-center']) .
                ($vertical
                    ? ''
                    : ViewHelper::tag('th', 'Сыграл игр', ['scope' => 'col', 'class' => 'align-top text-center'])
                )
            )
        ) . ViewHelper::tagOpen('tbody'),
        ['class' => 'table table-sm table-borderless']),
    ['id' => AchievesModel::ACHIEVES_ELEMENT_ID]);
<?php

/**
 * @var array $deltaRating
 * @var array $user
 * @var array $rating
 */

if (isset($_POST['vertical'])) {
    $vertical = true;
} else {
    $vertical = false;
}
$top = $rating['top'];
$marginTop = '';

return "<tr style='border-top:1px solid black !important;'>
      <th scope=\"row\">"
    . "<div style='margin-right: 0px; " . ($vertical === true ? '' : 'display: inline;') . "'>"
    . ($user['ID'] == $this->User
        ? "Вы"
        : $this->gameStatus['users'][$this->gameStatus[$user['ID']]]['username'])
    . (!$rating['isActive']
        ? '&nbsp;<img title="Игрок отключился" height="24px" src="https://xn--d1aiwkc2d.club/img/no-network-logo.png" />'
        : '')
    . "<br />
      <span style='white-space: nowrap;color:forestgreen;cursor:pointer'" . ($user['ID'] == $this->User ? " id='playersNikname' " : '') . " title='Никнейм игрока'>
      	{$rating['playerName']}
      </span>"
    . "</div>"
    . ($rating['playerAvatarUrl']
        ? "<div style='" . ($vertical === true ? '' : 'display: inline;') . "'>
                <img style=\"border-radius: 5px 5px 5px 5px; margin-left:20px;" . ($vertical === true ? '' : 'padding-top:0px;') . "\" alt=\"😰\" src=\"{$rating['playerAvatarUrl']}\" height=\"75px\" max-width=\"100px\" />
            </div>"
        : '')
    . "</th>
      <td class=\"text-center\"><strong>{$rating['rating']}</strong>"
    . ($deltaRating !== false
        ? ($deltaRating['delta'] <= 0
            ? '&nbsp;<span title="Последнее изменение рейтинга" style="color:indianred">' . $deltaRating['delta'] . '</span>'
            : '&nbsp;<span title="Последнее изменение рейтинга" style="color:lawngreen">+' . $deltaRating['delta'] . '</span>')
        : '')
    . (is_numeric($top = $rating['top'])
        ? '' . ($top <= 3
            ? "<br /><img style=\"cursor: pointer;\" title=\"Кликните для увеличения изображения\" id=\"{$user['ID']}\" onclick=\"showFullImage('{$user['ID']}', 500);\" src=\"https://xn--d1aiwkc2d.club/img/prizes/top_$top.svg\" width = \"192px\"/>"
            : ($top <= 10
                ? " <strong>№$top</strong><br /><img style=\"cursor: pointer;\" title=\"Кликните для увеличения изображения\" id=\"{$user['ID']}\" onclick=\"showFullImage('{$user['ID']}', 500);\" src=\"https://xn--d1aiwkc2d.club/img/prizes/top_10.svg\" width = \"192px\"/>"
                : " <br /><strong>№$top</strong>"))
        : '')
    . "</td>"
    . ($vertical
        ? ''
        : "
      <td class=\"text-center\"><strong>{$rating['games_played']}</strong></td>
      <!--<td class=\"text-center\"><strong>{$rating['win_percent']}</strong> / " .
        ($rating['inactive_percent'] != 'N/A'
            ? round($rating['inactive_percent'])
            : 'N/A')
        . "</td>-->")
    . "</tr>";
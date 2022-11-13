<?php
return "<tr>
      <th scope=\"row\">"
      .( $user['ID'] == $this->User 
      ? "<strong>Вы</strong>" 
      : $this->gameStatus['users'][$this->gameStatus[$user['ID']]]['username'] )
      .(!$rating['isActive'] 
      ? '&nbsp;<img title="Игрок отключился" height="24px" src="img/no-network-logo.png" />'
      : '')
      ."</th>
      <td class=\"text-center\"><strong>{$rating['rating']}</strong>".(is_numeric($rating['top']) ? ' <br /><strong>№'.$rating['top']. '</strong>' : '')."</td>
      <td class=\"text-center\">{$rating['games_played']}</td>
      <td class=\"text-center\"><strong>{$rating['win_percent']}</strong> / ".
      ($rating['inactive_percent'] != 'N/A' 
      ? round($rating['inactive_percent']) 
      : 'N/A')
      ."</td>
    </tr>";
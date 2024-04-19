<?php
/** @var $game array */
/** @var $i int */

$game_id = $i + GameController::GAME_ID_BASE_INC;
$row = "<tr>
        <th scope=\"row\"><a href=\"/game/$game_id\" target=\"_blank\">$game_id</a></th>
        <td>".(isset($game['results']) ? 'Завершена в '.date('H:i:s',$game['turnBeginTime']+3*3600) : 'Ход №'.$game['turnNumber'])."</td>
        <td>";
foreach($game['users'] as $user)
    $row .= "<a href=\"checkStatus.php?user={$user['ID']}\" target=\"_blank\">{$user['ID']}</a>".'&nbsp;'. (isset($user['lastActiveTime']) 
    ? date('H:i:s',$user['lastActiveTime']+3*3600) 
    : 'Отключился')
    .(isset($user['userID']) 
    ? ' userID:'.$user['userID'] 
    : '')
    .'<br />';
$row.= "</td>
      <td>";
foreach($game['users'] as $user)
    $row .= $user['score'].'<br />';
$row.= "</td>
    </tr>";
    
return $row;
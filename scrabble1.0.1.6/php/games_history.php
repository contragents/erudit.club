<?php
//ini_set("display_errors", 1); error_reporting(E_ALL);
if (isset($_SERVER['HTTP_ORIGIN']) && $_SERVER['HTTP_ORIGIN'] != '') {
    header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
    header('Access-Control-Allow-Credentials: true');
}

include_once(__DIR__ . '/DadataDB.php');

$playerIDSelect = "SELECT id from players where user_id IN (select user_id from players where common_id = {$_GET['word']})";
$ids = DB::queryArray($playerIDSelect);
if (is_array($ids)) {
    $ids = array_map(function ($arr) {
        return $arr['id'];
    }, $ids);
}

$idsString = implode(',', $ids);

$gamesSelect = "SELECT * 
FROM games_stats
WHERE
1_player_id IN ($idsString)
OR 
2_player_id IN ($idsString)
OR 
3_player_id IN ($idsString)
OR 
4_player_id IN ($idsString)
ORDER BY id DESC 
LIMIT 20";

$res = DB::queryArray($gamesSelect);
if (!is_array($res)) {
    print "Игр не найдено.";
    return false;
}

?>
<table>
    <thead>
    <th>
        Номер Игры
    </th>
    <th>
        Дата время окончания
    </th>
    <th>
        Игрок №1
    </th>
    <th>
        Игрок №2
    </th>
    <th>
        Игрок №3
    </th>
    <th>
        Игрок №4
    </th>
    </thead>
    <tbody>
    <?php foreach ($res as $game) { ?>
        <tr>
            <td>
                <?= $game['game_id'] ?>
            </td>
            <td>
                <?= date('d.m.Y G:i:s ', $game['game_ended_date'] + 3 * 60 * 60) . "MSK" ?>
            </td>
            <td>
                <?= in_array($game['1_player_id'], $ids) ? "Вы" : "Противник" ?>
            </td>
            <td>
                <?= in_array($game['2_player_id'], $ids) ? "Вы" : "Противник" ?>
            </td>
            <td>
                <?php if ($game['3_player_id']) { ?>
                    <?= in_array($game['3_player_id'], $ids) ? "Вы" : "Противник" ?>
                <?php } ?>
            </td>
            <td> <?php if ($game['3_player_id']) { ?>
                    <?= in_array($game['4_player_id'], $ids) ? "Вы" : "Противник" ?>
                <?php } ?>
            </td>
        </tr>
    <?php } ?>
    </tbody>
</table>
print "
<pre>" . print_r($res, true) . "</pre>";


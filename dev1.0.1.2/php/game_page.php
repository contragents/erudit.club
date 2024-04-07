<?php
include_once 'EruditGame.php';

use Dadata\DB;

$instance = new \Erudit\Game();

$gameDataQuery = "SELECT uncompress(game_data) FROM games WHERE id = {$_GET['game_id']}";

$gameData = unserialize(DB::queryValue($gameDataQuery));

$players = [];
foreach ($gameData['users'] as $num => $player) {
    $players[$num] = $player;
    $players[$num]['nickName'] = $instance->getPlayerName($player);
    $players[$num]['avatarUrl'] = $instance->getAvatarUrl($player['ID']);
}
print "Играют " . implode('&nbsp;vs&nbsp;', array_map(function ($player) {
        return ViewHelper::tag('a', $player['nickName'], ['href' => '/mvc/stats/view?common_id=' . $player['common_id']])
            . '&nbsp;'
            . $player['rating']
            . '&nbsp;'
            . "<img src=\"{$player['avatarUrl']}\" style=\"max-width:100px;\" />";
    }, $players));
print "<pre>" . print_r($gameData['gameLog'], true) . "</pre>";
print "<pre>" . print_r(delIds($gameData), true) . "</pre>";

function delIds($game): array {
    foreach($game['users'] as $num => $nothing) {
        unset($game['users'][$num]['ID']);
    }

    foreach($game as $key=>$nothing) {
        if (preg_match('/^[0-9a-fA-F]{32}$/', $key)) {
            unset($game[$key]);
        }
    }

    foreach($game['results']['lostUsers'] as $num => $nothing) {
        $game['results']['lostUsers'][$num] = 'Проиграл';
    }

    // todo Подставить номер выигравшего
    $game['results']['winner'] = 'Выиграл';

    return $game;
}

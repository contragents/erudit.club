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
        return $player['nickName']
            . '&nbsp;'
            . $player['rating']
            . '&nbsp;'
            . "<img src=\"{$player['avatarUrl']}\" style=\"max-width:100px;\" />";
    }, $players));
print "<pre>" . print_r($gameData['gameLog'], true) . "</pre>";
print "<pre>" . print_r($gameData, true) . "</pre>";

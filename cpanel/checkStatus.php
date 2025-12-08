<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body>
<?php
ini_set("display_errors", 1);
error_reporting(E_ALL);
const CACHE_KEYS = [
    'pre' => ['get_game_', 'user_', 'user_preference_'],
    'past' => ['', '_last_activity', '']
];
include_once __DIR__ . '/../autoload.php';
$playerCookie = PlayerModel::find()
    ->where([['field_name' => PlayerModel::COOKIE_FIELD, 'value' => $_GET['user'] . '%', 'condition' => 'like']])
    ->one()
    ->_cookie;
foreach (CACHE_KEYS['pre'] as $num => $key) {
    print $key . CACHE_KEYS['past'][$num] . ': '
        . print_r(
            Cache::get("erudit.$key{$playerCookie}" . CACHE_KEYS['past'][$num]),
            true
        )
        . '<br />';
}
?>
</body>
</html>
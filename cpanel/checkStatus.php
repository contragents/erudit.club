<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body>
<?php
const CACHE_KEYS = [
    'pre' => ['get_game_', 'user_', 'user_preference_'],
    'past' => ['', '_last_activity', '']
];
include_once __DIR__ . '/../autoload.php';
foreach (CACHE_KEYS['pre'] as $num => $key) {
    print $key . CACHE_KEYS['past'][$num] . ': '
        . print_r(
            Cache::get("erudit.$key{$_GET['user']}" . CACHE_KEYS['past'][$num]),
            true
        )
    .'<br />';
}
?>
</body>
</html>
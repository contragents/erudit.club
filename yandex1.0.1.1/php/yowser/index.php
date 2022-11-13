<?php
/*Old settings
$script = $_SERVER['REQUEST_URI'];
$script = explode('/', $script);
$cookie = $script[count($script) - 2];
$script = $script[count($script) - 1];
$script = substr($script, 0, strpos($script, '?') > 0 ? strpos($script, '?') : 100);
*/
//Production...
$script = $_GET['script'];
$cookie = $_GET['cooki'];
//Production

//test...
$script = $_GET['script'];
$cookie = $_GET['cooki'];
//test
if ($cookie == 'EMPTY_SESSION_ID') {
    if (isset($_SERVER['HTTP_ORIGIN']) && $_SERVER['HTTP_ORIGIN'] != '') {
        $server_name = str_replace('https://', '', $_SERVER['HTTP_ORIGIN']);
        header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
        header('Access-Control-Allow-Credentials: true');
    }

    $cookie = $_COOKIE['erudit_user_session_ID'] ?? md5($_SERVER['HTTP_USER_AGENT'] . time() . rand(1, 100));
    print json_encode(['gameState' => 'register', 'cookie' => $cookie]);

    exit();
} else {
    $_SERVER['HTTP_COOKIE'] = "erudit_user_session_ID=$cookie";
    $_COOKIE = ['erudit_user_session_ID' => $cookie];
    include_once('../' . $script);
}

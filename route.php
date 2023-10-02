<?php

$path = parse_url($_SERVER['REQUEST_URI'])['path'];
$pathParts = explode('/', $path);
if (strpos($pathParts[1], 'ndex.php')) {
    header('HTTP/1.0 403 Forbidden');

    echo 'Доступ запрещен';
    exit();
}

if (count($pathParts) > 2) {
    $controller = ucfirst($pathParts[2]) . 'Controller';
    $action = $pathParts[3];
    // print_r([$controller, $action]);
    if (is_callable([$controller, $action . 'Action'])) {
        $res = (new $controller($action, $_REQUEST))->Run();
        print (is_array($res) ? json_encode($res,JSON_UNESCAPED_UNICODE) : $res);
    } else {
        header('HTTP/1.0 403 Forbidden');
        echo 'Доступ запрещен';
    }
}

exit();
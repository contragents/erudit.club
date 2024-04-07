<?php
const DEFAULT_MODULE = 'mvc';
const DEFAULT_ACTION = 'index';

$path = parse_url($_SERVER['REQUEST_URI'])['path'];
$pathParts = explode('/', $path);
if (strpos($pathParts[1], 'ndex.php')) {
    header('HTTP/1.0 403 Forbidden');

    echo 'Доступ запрещен';
    exit();
}

if (count($pathParts) > 2) {
    $module = $pathParts[1];

    if ($module == DEFAULT_MODULE) {
        $controller = ucfirst($pathParts[2]) . 'Controller';
        $action = $pathParts[3] ?? DEFAULT_ACTION;
    } else {
        $controller = ucfirst($pathParts[1]) . 'Controller';
        $action = $pathParts[2] ?? DEFAULT_ACTION;
    }

    if (is_numeric($action)) {
        $mainParam = $action;
        $action = DEFAULT_ACTION;
    }

    if (is_callable([$controller, $action . 'Action'])) {
        if(isset($mainParam)) {
            $_REQUEST[$controller::MAIN_PARAM] = $mainParam;
        }

        $res = (new $controller($action, $_REQUEST))->Run();
        print is_array($res) ? json_encode($res,JSON_UNESCAPED_UNICODE) : $res;
    } else {
        header('HTTP/1.0 403 Forbidden');
        echo 'Доступ запрещен';
    }
}

exit();
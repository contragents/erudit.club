<?php
const DEFAULT_MODULE = 'mvc';
const DEFAULT_ACTION = 'index';

// ini_set("display_errors", 1); error_reporting(E_ALL);

$path = parse_url($_SERVER['REQUEST_URI'])['path'];
$pathParts = explode('/', $path);
if (strpos($pathParts[1], 'ndex.php')) {
    header('HTTP/1.0 403 Forbidden');
    echo 'Forbidden';

    exit;
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

    // Для Блога переназначаем action = DEFAULT, $mainParam - title статьи
    if($controller === 'BlogController') {
        $action = $controller::DEFAULT_ACTION;
        $mainParam = $pathParts[2] ?? null; // Сохраняем title статьи для поиска в контроллере
    }

    if (is_numeric($action)) {
        $mainParam = $action;
        $action = DEFAULT_ACTION;
    }

    if (class_exists($controller) && method_exists($controller, $action . 'Action')) {
        $res = (new $controller($action, $_REQUEST))->Run();
        echo is_array($res) ? json_encode($res,JSON_UNESCAPED_UNICODE) : $res;
    } else {
        header('HTTP/1.0 403 Forbidden');
        echo 'Forbidden';
    }

    exit;
}

exit();
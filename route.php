<?php

const ERUDIT_DOMAIN = 'xn--d1aiwkc2d.club';
const CONTROLLER_SUFFIX = 'Controller';
const DEFAULT_CONTROLLER = 'Moderation';
const DEFAULT_ACTION = 'index';

// Проверяем контроллер в субдомене
$subDomain = substr($_SERVER['HTTP_HOST'], 0, (strpos($_SERVER['HTTP_HOST'], ERUDIT_DOMAIN) ?: 1) - 1);
//print '!!!!' . $subDomain; exit();
$controller = ucfirst($subDomain);

// Проверяем контроллер в ГЕТ-параметрах
if (isset($_GET['module'])) {
    $controller .= ucfirst($_GET['module']);
}

//Получаем имя класса контроллера
$controller = ($controller ?: DEFAULT_CONTROLLER) . CONTROLLER_SUFFIX;

try {
    $response = (new $controller($_GET['action'] ?? DEFAULT_ACTION, $_REQUEST))->Run();
    print $response;
} catch (Throwable $exception) {
    http_response_code(400);
    print json_encode(['status' => 'error', 'message' => 'Script error', 'comments' => $exception]);
}

exit();
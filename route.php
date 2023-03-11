<?php
// Обработаем вызов PostbackController-контроллера
if (isset($_GET['module'])) {
    $controller = ucfirst($_GET['module']) . 'Controller';
    try {
        $response = (new $controller($_GET['action'] ?? 'default', $_REQUEST))->Run();
    } catch (Throwable $exception) {
        http_response_code(400);
        print json_encode(['status' => 'error', 'message' => 'Bad request', 'comments'=> $exception]);
    }

    exit();
}
<?php

if (strpos(__DIR__, 'dev1.0.1.2')) {
    ini_set("display_errors", 1);
    error_reporting(E_ALL);
}

spl_autoload_register(
    function ($class_name) {
        $Exploded_class = explode('\\', $class_name);
        $namespace = $Exploded_class[count($Exploded_class) - 2] ?? '';
        $class = $Exploded_class[count($Exploded_class) - 1];

        $fileName = __DIR__ . "/$namespace$class.php";
        if (file_exists($fileName)) {
            include_once $fileName;
            return;
        }
    }
);

include_once __DIR__ . "/../../autoload.php";

set_error_handler(
    function ($err_severity, $err_msg, $err_file, $err_line, $err_context = []) {
        \BadRequest::sendBadRequest(
            [
                'err_severity' => $err_severity,
                'err_msg' => $err_msg,
                'err_file' => $err_file,
                'err_line' => $err_line,
                'err_context' => $err_context
            ]
        );
    },
    E_ALL & ~E_NOTICE
);
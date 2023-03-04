<?php

spl_autoload_register(
    function ($class_name) {
        $Exploded_class = explode('\\', $class_name);
        include $Exploded_class[count($Exploded_class) - 1] . 'LangProvider.php';
    }
);

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
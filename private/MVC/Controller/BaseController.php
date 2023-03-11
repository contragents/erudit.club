<?php

class BaseController
{
    public static $Request;
    public $Action;

    const FIELDS_TO_LOWER = ['platform', 'device_type'];

    const VIEW_PATH = __DIR__ . '/../View/';

    public function __construct($action, array $request)
    {
        static::$Request = $request;

        // Приводим параметры к нижнему регистру в соответствии со списком полей FIELDS_TO_LOWER
        foreach (self::FIELDS_TO_LOWER as $field) {
            if (isset(static::$Request[$field])) {
                static::$Request[$field] = strtolower(static::$Request[$field]);
            }
        }

        $this->Action = $action . 'Action';
    }

    public function Run()
    {
        return $this->{$this->Action}();
    }

    /**
     * @param string $viewName = 'Index'
     * @return string
     */
    protected function render($viewName = 'Index'): string
    {
        $res = self::include(static::VIEW_PATH . $viewName . 'View.php');
        return nl2br($res);
    }

    private function include($filename)
    {
        if (is_file($filename)) {
            ob_start();
            include $filename;

            return ob_get_clean();
        }

        return '';
    }
}

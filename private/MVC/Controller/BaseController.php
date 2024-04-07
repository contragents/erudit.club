<?php

class BaseController
{
    public static $Request;
    public $Action;

    const MAIN_PARAM = 'id';

    const VIEW_PATH = __DIR__ . '/../View/';

    public function __construct($action, array $request)
    {
        static::$Request = $request;

        $this->Action = $action . 'Action';
    }

    public static function isAjaxRequest(): bool
    {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
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

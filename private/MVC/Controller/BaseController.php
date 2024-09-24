<?php

class BaseController
{
    public static $Request;
    public $Action;

    const COMMON_URL = 'game/';

    const MAIN_PARAM = 'id';

    const VIEW_PATH = __DIR__ . '/../View/';

    public function __construct($action, array $request)
    {
        static::$Request = $request;

        $this->Action = $action . 'Action';

        if (!empty(self::$Request['lang'])) {
            T::$lang = self::$Request['lang'];
        } else {
            T::$lang = self::getLang();
        }
    }

    public static function isAjaxRequest(): bool
    {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
    }

    public static function getUrl(string $action, array $params = [], array $excludedParams = [])
    {
        return static::COMMON_URL
            . $action . '/'
            . (!empty($params)
                ? ('?' . implode(
                        '&',
                        array_filter(
                            array_map(
                                fn($param, $value) => !in_array($param, $excludedParams) ? "$param=$value" : null,
                                array_keys($params),
                                $params
                            )
                        )
                    )
                )
                : '');
    }

    /*
     * Определяем язык (игру) по рефереру
     */
    public static function getLang(): string
    {
        if (strpos($_SERVER['HTTP_REFERER'] ?? '', 'dev.html')) {
            return T::GAME_MODE_LANG['dev'];
        } elseif (strpos($_SERVER['HTTP_REFERER'] ?? '', 'private.html')) {
            return T::GAME_MODE_LANG['yandex'];
        } elseif  (strpos($_SERVER['HTTP_REFERER'] ?? '', 'scramble.html')) {
            return T::GAME_MODE_LANG['scrabble'];
        } else {
            return T::GAME_MODE_LANG['yandex'];
        }
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

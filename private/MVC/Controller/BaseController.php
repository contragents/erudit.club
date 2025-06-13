<?php

class BaseController
{
    public const BASE_URL = 'https://xn--d1aiwkc2d.club/';
    public static $Request;
    public $Action;

    const COMMON_URL = 'game/';

    const MAIN_PARAM = 'id';

    const COMMON_ID_PARAM = 'common_id';
    const TG_ID_PARAM = 'tg_id';

    const VIEW_PATH = __DIR__ . '/../View/';

    public function __construct($action, array $request)
    {
        self::cors();

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
            return T::GAME_MODE_LANG[Game::SCRABBLE];
        } else {
            // SUD-51
            if (isset($_SERVER['HTTP_REFERER'])) {
                foreach (Yandex::GAMES_ID_LANG as $gameId => $lang) {
                    if (strstr($_SERVER['HTTP_REFERER'], (string)$gameId) !== false) {
                        return $lang;
                    }
                }
            }

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

    private static function cors()
    {
        if (isset($_SERVER['HTTP_ORIGIN']) && $_SERVER['HTTP_ORIGIN'] != '') {
            header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
            header('Access-Control-Allow-Credentials: true');
        }
    }

    public static function isYandexApp()
    {
        if (isset($_SERVER['HTTP_REFERER']) && (strpos($_SERVER['HTTP_REFERER'], 'yandex') !== false)) {
            return true;
        }

        return false;
    }
}

<?php

class GameController extends BaseController
{
    const COMMON_URL = 'game/';
    const GAME_ID_BASE_INC = 300000; // ID игры в БД больше, чем ID в игре

    public function indexAction(): string
    {
        //return print_r(self::$Request, true);
        // ini_set("display_errors", 1); error_reporting(E_ALL);
        $_GET['game_id'] = self::$Request[self::MAIN_PARAM];

        ob_start();

        include(__DIR__ . '/../../../yandex1.0.1.1/php/autoload.php');
        $title = 'Игра №' . $_GET['game_id'];
        include (__DIR__ . '/../../../tpl/main_header.php');
        include(__DIR__ . '/../../../yandex1.0.1.1/php/game_page.php');
        include (__DIR__ . '/../../../tpl/main_footer.php');

        return ob_get_clean();
    }
}
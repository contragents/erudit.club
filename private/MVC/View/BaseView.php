<?php
class BaseView
{
    public static function renderFull(array $params): string
    {
        $res = json_decode(static::render(...$params), true);

        return self::getIncludeContents('Tpl/main_header.php')
            . ($res['message'] ?? '')
            . ($res['pagination'] ?? '')
            . self::getIncludeContents('Tpl/main_footer.php');
    }

    private static function getIncludeContents($filename): string
    {
        ob_start();
        @include $filename;
        return ob_get_clean();
    }
}

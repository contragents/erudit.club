<?php
class BaseView
{
    public static function renderFull(array $params): string
    {
        $res = json_decode(static::render(...$params), true);

        return ($res['message'] ?? '') . ($res['pagination'] ?? '');
    }
}

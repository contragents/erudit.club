<?php

class ViewHelper
{
    const MAX_PAGE_LINKS = 16;

    public static function __callStatic($tag, $arguments)
    {
        return self::tag($tag, ...$arguments);
    }

    public static function renderGridFromQueryResult(array $queryResult, string $title = '', array $attributeLabels = []): string {
        $grid = $title ? self::tag('h5', $title) : '';
        $grid .= self::tagOpen('table', '', ['class' => 'table table-sm', 'style' => 'word-wrap: break-word;']);
        $grid .= self::tagOpen('tr');
        foreach ($queryResult[0] ?? [] as $field => $nothing) {
            $grid .= self::tag('th', $attributeLabels[$field] ?? $field);
        }
        $grid .= self::tagClose('tr');

        reset($queryResult);
        foreach($queryResult as $num => $row) {
            $grid .= self::tagOpen('tr');
            foreach ($row as $field => $value) {
                $grid .= self::tag('td', $value);
            }
            $grid .= self::tagClose('tr');
        }

        return $grid;
    }

    /**
     * @param string $tag
     * @param string $content
     * @param array $options
     * @param bool $condition Условие, при котором тег рендерится
     * @return string
     */
    public static function tag(string $tag, string $content = '', array $options = [], bool $condition = true) {
        if (!$condition) return '';

        if(empty($tag)) {
            return $content;
        }

        return self::tagOpen($tag, $content, $options) . self::tagClose($tag);
    }

    public static function tagOpen(string $tag, string $content = '', array $options = []) {
        return '<' . $tag . ' '
            . implode(' ', array_map(fn($key, $value) => $key . "=\"$value\"", array_keys($options), $options))
            . '>'
            . $content;
    }

    public static function tagClose(string $tag)
    {
        return "</$tag>";
    }

    public static function paginationArr(int $curentPage, int $pageQuantity, string $baseUrl): array
    {
        $res = [];
        for ($i = 1; $i <= $pageQuantity && $i <= self::MAX_PAGE_LINKS; $i++) {
            $res[$i] = ['is_link' => $i != $curentPage, 'value' => $i != $curentPage ? "$baseUrl&page=$i" : $i];
        }

        return $res;
    }


    /**
     * @param int $curentPage
     * @param int $pageQuantity
     * @param string $baseUrl
     * @return string
     */
    public static function pagination(int $curentPage, int $pageQuantity, string $baseUrl): string
    {
        $res = self::tagOpen('span','', ['style' => 'word-wrap: break-word;']);
        for ($i = 1; $i <= $pageQuantity && $i <= self::MAX_PAGE_LINKS; $i++) {
            $res .= '&nbsp; &nbsp;' . self::tag(
                $i != $curentPage ? 'a' : 'span',
                $i,
                [
                    $i != $curentPage ? 'onClick' : 'none' => ViewHelper::onClick(
                        'refreshId',
                        AchievesModel::ACHIEVES_ELEMENT_ID,
                        "$baseUrl&page=$i"
                    ),
                    'href' => "$baseUrl&page=$i",
                    'class' => "link-underline-primary",
                ]
            );
        }

        if ($i == 2) {
            return '';
        }

        $res .= self::tagClose('span');

        return $res;
    }

    public static function onClick(string $function, string $elementId, string $url): string
    {
        return "$function('$elementId', '$url'); return false;";
    }

    public static function br(): string
    {
        return '<br />';
    }

    public static function nbsp(): string
    {
        return '&nbsp;';
    }

    public static function img(array $options): string
    {
        return self::tagOpen('img','',$options);
    }
}

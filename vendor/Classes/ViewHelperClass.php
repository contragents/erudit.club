<?php

class ViewHelper
{
    const MAX_PAGE_LINKS = 16;

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

    public static function tag(string $tag, string $content = '', array $options = []) {
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

    private static function tagClose(string $tag)
    {
        return "</$tag>";
    }

    /**
     * @param int $curentPage
     * @param int $pageQuantity
     * @param string $element_id
     * @param string $baseUrl
     * @return string
     */
    public static function pagination(int $curentPage, int $pageQuantity, string $element_id, string $baseUrl): string
    {
        $res = self::tagOpen('span','', ['style' => 'word-wrap: break-word;']);
        for ($i = 1; $i <= $pageQuantity && $i <= self::MAX_PAGE_LINKS; $i++) {
            $res .= '&nbsp; &nbsp;' . self::tag(
                $i != $curentPage ? 'a' : 'span',
                $i,
                [
                    $i != $curentPage ? 'onClick' : 'none' => "refreshId('$element_id', '$baseUrl&page=".$i."')",
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
}

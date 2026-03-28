<?php


class GameRequest
{
    public ?string $secret = null;

    public function __construct(string $referer)
    {
        if (!empty($referer)) {
            // 1. Извлекаем только строку запроса (всё, что после '?')
            $queryString = parse_url($referer, PHP_URL_QUERY);

            if ($queryString) {
                // 2. Разбираем строку 'secret=abc&id=123' в массив $params
                parse_str($queryString, $params);

                // 3. Сопоставляем со свойствами класса
                foreach ($params as $key => $value) {
                    if (property_exists($this, $key)) {
                        $this->$key = $value;
                    }
                }
            }
        }
    }
}

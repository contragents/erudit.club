<?php

class Yandex
{
    private const USER_ID_PARAM = 'yandex_user_id';

    public static ?string $yandexUser = null;
    public static ?int $commonId = null;

    public static function authorize(): bool
    {
        if (!empty($_REQUEST[self::USER_ID_PARAM])) {
            self::$yandexUser = md5($_REQUEST[self::USER_ID_PARAM]); // Ключ яндекса длиннее 32
            self::$commonId = (int)PlayerModel::getPlayerID(self::$yandexUser, true);

            return true;
        }

        return false;
    }
}
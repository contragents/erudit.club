<?php

class Yandex
{
    private const USER_ID_PARAM = 'yandex_user_id';

    public static ?string $yandexUser = null;
    public static ?int $commonId = null;

    const GAMES_ID_LANG = [
        '443011' => T::EN_LANG, // Scramble
        '393661' => T::RU_LANG, // Эрудит
    ];

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
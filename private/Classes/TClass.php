<?php

class T
{
    const RU_LANG = 'RU';
    const EN_LANG = 'EN';

    public static $lang = self::EN_LANG;

    public static function getInviteFriendPrompt(): string
    {
        return self::PHRASES['invite_friend_prompt'][self::$lang];
    }

    const GAME_MODE_LANG = [
        'scrabble' => self::EN_LANG,
        'yandex' => self::RU_LANG,
        'dev' => self::EN_LANG,
    ];

    const PHRASES = [
        'invite_friend_prompt' => [
            self::EN_LANG => 'Join the online game Scrabble on Telegram! Get the maximum rating, earn coins and withdraw tokens to your wallet',
            self::RU_LANG => 'Присоединяйся к онлайн игре Эрудит в Telegram! Набери максимальный рейтинг, зарабатывай монеты и выводи токены на кошелек'
        ],
        'game_bot_url' => [
            self::EN_LANG => 'https://t.me/scrabble_online_bot',
            self::RU_LANG => 'https://t.me/erudit_club_bot'
        ],
        'loading_text' => [
            self::EN_LANG => 'Scrabble is loading...',
            self::RU_LANG => 'Загружаем игру...'
        ],
        'ground_file' => [
            self::EN_LANG => 'field_source_scrabble.svg',
            self::RU_LANG => 'field_source_nd_20.svg',//'field_source_nd_17.svg' //
        ],
        'switch_tg_button' => [
            self::EN_LANG => 'Switch to Telegram',
            self::RU_LANG => 'Перейти на Telegram'
        ],
        'invite_tg_button' => [
            self::EN_LANG => 'Invite a friend',
            self::RU_LANG => 'Пригласить друга'
        ],
    ];
}
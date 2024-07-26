<?php

class T
{
    const RU_LANG = 'RU';
    const EN_LANG = 'EN';

public static function getInviteFriendPrompt($lang = self::EN_LANG): string
{
    return self::PHRASES['invite_friend_prompt'][$lang];
}

const PHRASES = [
    'invite_friend_prompt' => [
        self::EN_LANG => 'Join the online game Scrabble on Telegram! Get the maximum rating, earn coins and withdraw tokens to your wallet',
        self::RU_LANG => 'Присоединяйся к онлайн игре Эрудит в Telegram! Набери максимальный рейтинг, зарабатывай монеты и выводи токены на кошелек'
    ],
    'game_bot_url' => [
        self::EN_LANG => 'https://t.me/scrabble_online_bot',
        self::RU_LANG => 'https://t.me/erudit_club_bot'
    ]
];
}
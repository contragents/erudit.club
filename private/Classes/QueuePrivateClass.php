<?php

class QueuePrivate extends Queue
{
    const QUEUES = [
        'erudit.rating_waiters' => 'erudit.private.rating_waiters',
        'erudit.2players_waiters' => 'erudit.private.2players_waiters',
        'erudit.2ENplayers_waiters' => 'erudit.private.2ENplayers_waiters',
        'erudit.4players_waiters' => 'erudit.private.2ENplayers_waiters',
        'erudit.4ENplayers_waiters' => 'erudit.private.4players_waiters',
        'erudit.inviteplayers_waiters' => 'erudit.private.inviteplayers_waiters',
        'erudit.inviteENplayers_waiters' => 'erudit.private.inviteENplayers_waiters',
    ];

    const PREFS_KEY = 'erudit.private.user_preference_';
    const USER_STATUS_PREFIX = 'erudit.private.user_status_';
    const CURRENT_GAME_KEY = 'erudit.private.current_game_';
    const GAMES_COUNTER = 'erudit.private.num_games';
    const GET_GAME_KEY = 'erudit.private.get_game_';
    const GAME_KEY = 'erudit.private.game_';

    protected static function getRuClass(): string
    {
        return RuPrivate::class;
    }

    protected static function getEngClass(): string
    {
        return EngPrivate::class;
    }
}


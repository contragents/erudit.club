<?php

class PrizesScrabble extends PrizesErudit
{
    const GAME_NAME = Game::SCRABBLE;

    protected const ALL_RECORDS = 'scrabble_all_records';

    protected const DAY = 'day';
    protected const WEEK = 'week';
    protected const MONTH = 'month';
    protected const YEAR = 'year';

    const PERIODS = [
        self::DAY => 'day',
        self::WEEK => 'week',
        self::MONTH => 'month',
        self::YEAR => 'year'
    ];
}
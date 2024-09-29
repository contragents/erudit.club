<?php

class PrizesScrabble extends PrizesErudit
{
    const GAME_NAME = Game::SCRABBLE;

    protected const ALL_RECORDS = 'scrabble_all_records';

    protected const WORD_LEN_DAILY = 'scrabble_word_len_daily_';
    protected const WORD_LEN_WEEKLY = 'scrabble_word_len_weekly_';
    protected const WORD_LEN_MONTHLY = 'scrabble_word_len_monthly_';
    protected const WORD_LEN_YEARLY = 'scrabble_word_len_yearly_';

    protected const WORD_PRICE_DAILY = 'scrabble_word_price_daily_';
    protected const WORD_PRICE_WEEKLY = 'scrabble_word_price_weekly_';
    protected const WORD_PRICE_MONTHLY = 'scrabble_word_price_monthly_';
    protected const WORD_PRICE_YEARLY = 'scrabble_word_price_yearly_';

    protected const TURN_PRICE_DAILY = 'scrabble_turn_price_daily_';
    protected const TURN_PRICE_WEEKLY = 'scrabble_turn_price_weekly_';
    protected const TURN_PRICE_MONTHLY = 'scrabble_turn_price_monthly_';
    protected const TURN_PRICE_YEARLY = 'scrabble_turn_price_yearly_';

    protected const GAME_PRICE_DAILY = 'scrabble_game_price_daily_';
    protected const GAME_PRICE_WEEKLY = 'scrabble_game_price_weekly_';
    protected const GAME_PRICE_MONTHLY = 'scrabble_game_price_monthly_';
    protected const GAME_PRICE_YEARLY = 'scrabble_game_price_yearly_';

    protected const GAMES_PLAYED_DAILY = 'scrabble_games_played_daily_';
    protected const GAMES_PLAYED_WEEKLY = 'scrabble_games_played_weekly_';
    protected const GAMES_PLAYED_MONTHLY = 'scrabble_games_played_monthly_';
    protected const GAMES_PLAYED_YEARLY = 'scrabble_games_played_yearly_';
}
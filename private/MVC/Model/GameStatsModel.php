<?php

class GameStatsModel extends BaseModel
{
    const TABLE_NAME = 'games_stats';

    const GAME_ENDED_AT_FIELD = 'game_ended_date'; //datetime
    const GAME_ID_FIELD = 'game_id';
    const PLAYERS_NUM_FIELD = 'players_num';
    const WINNER_ID_FIELD = 'winner_player_id';
    const GAME_NAME_ID = 'game_name_id';
}
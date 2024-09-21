<?php

namespace Erudit;

use \Queue as Queue;
use RatingHistoryModel;
use T;

class Game extends \Game
{
    public function __construct()
    {
        $this->Queue = Queue::class;
        $this->dir = __DIR__;

        preg_match('/((scrabble|release|yandex|dev)(\d\.\d\.\d\.\d))/', $this->dir, $matches);
        $gameMode = $matches[2];

        $this->gameName = ((T::GAME_MODE_LANG[$gameMode] ?? T::EN_LANG) === T::EN_LANG) ? RatingHistoryModel::SCRABBLE : RatingHistoryModel::ERUDIT;

        parent::__construct();
    }
}


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

        //preg_match('/((scrabble|release|yandex|dev)(\d\.\d\.\d\.\d))/', $this->dir, $matches);
        //$gameMode = $matches[2];

        parent::__construct();
    }
}


<?php

namespace Erudit;

use \Queue as Queue;
use QueueScrabble;
use T;

class Game extends \Game
{
    public function __construct()
    {
        $this->Queue = T::$lang === T::RU_LANG ? Queue::class : QueueScrabble::class;
        $this->dir = __DIR__;

        parent::__construct();
    }
}

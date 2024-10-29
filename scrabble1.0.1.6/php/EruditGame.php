<?php

namespace Erudit;

use \Queue as Queue;

class Game extends \Game
{
    public function __construct()
    {
        $this->Queue = Queue::class;
        $this->dir = __DIR__;

        parent::__construct();
    }
}


<?php

include_once __DIR__ . '/../../autoload.php';

class BotBalanceWorker
{
    const MAX_SUDOKU_FOR_BOT = 5000;
    const MIN_SUDOKU_PER_BOT = 1000;

    public function run()
    {
        print DB::queryInsert(
            "UPDATE balance 
SET sudoku = sudoku + " . self::MAX_SUDOKU_FOR_BOT . " * rand() 
WHERE
	id IN (
	SELECT
		common_id 
	FROM
		players 
WHERE
	cookie LIKE '" . Game::BOT_TPL . "%')
  AND sudoku < " . self::MIN_SUDOKU_PER_BOT
        );
    }
}

(new BotBalanceWorker())->run();
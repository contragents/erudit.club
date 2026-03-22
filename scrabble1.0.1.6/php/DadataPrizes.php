<?php

namespace Dadata;

use Record;
use Game;
use PrizesErudit;
use PrizesScrabble;

class Prizes
{
    public static function checkGamesPlayedRecord(array $players): array
    {
        if (Game::$gameName === Game::SCRABBLE) {
            return PrizesScrabble::checkGamesPlayedRecord($players);
        } else {
            return PrizesErudit::checkGamesPlayedRecord($players);
        }
    }

    public static function checkGamePriceRecord($price, $commonId): array
    {
        if (Game::$gameName === Game::SCRABBLE) {
            return PrizesScrabble::checkRecord($price, $commonId, Record::GAME_PRICE);
        } else {
            return PrizesErudit::checkRecord($price, $commonId, Record::GAME_PRICE);
        }
    }

    public static function checkTurnPriceRecord($price, ?int $commonId = null): array
    {
        if (!$commonId) {
            return [];
        }

        if (Game::$gameName === Game::SCRABBLE) {
            return PrizesScrabble::checkRecord($price, $commonId, Record::TURN_PRICE);
        } else {
            return PrizesErudit::checkRecord($price, $commonId, Record::TURN_PRICE);
        }
    }

    public static function checkWordTurnRecord($number, ?int $commonId = null): array
    {
        if (!$commonId) {
            return [];
        }

        if (Game::$gameName === Game::SCRABBLE) {
            return PrizesScrabble::checkRecord($number, $commonId, Record::WORD_PER_TURN);
        } else {
            return PrizesErudit::checkRecord($number, $commonId, Record::WORD_PER_TURN);
        }
    }

    public static function checkTverdNumRecord($number, ?int $commonId = null): array
    {
        if (!$commonId) {
            return [];
        }

        if (Game::$gameName === Game::SCRABBLE) {
            return [];
        } else {
            return PrizesErudit::checkRecord($number, $commonId, Record::TVERD_NUM);
        }
    }

    public static function checkWordPriceRecord(int $price, ?int $commonId, ?string $word)
    {
        if (!$commonId) {
            return [];
        }

        if (Game::$gameName === Game::SCRABBLE) {
            return PrizesScrabble::checkRecord($price, $commonId, Record::WORD_PRICE, $word);
        } else {
            return PrizesErudit::checkRecord($price, $commonId, Record::WORD_PRICE, $word);
        }
    }

    public static function checkWordLenRecord($checkValue, $commonId, $word): array
    {
        if (!$commonId) {
            return [];
        }

        if (Game::$gameName === Game::SCRABBLE) {
            return PrizesScrabble::checkRecord($checkValue, $commonId, Record::WORD_LEN, $word);
        } else {
            return PrizesErudit::checkRecord($checkValue, $commonId, Record::WORD_LEN, $word);
        }
    }
}

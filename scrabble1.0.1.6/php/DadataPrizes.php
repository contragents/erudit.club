<?php

namespace Dadata;

use Record;
use Game;
use PrizesErudit;
use PrizesScrabble;

class Prizes
{
    public static function getRandomRecord(): ?array
    {
        if (Game::$gameName === Game::SCRABBLE) {
            return PrizesScrabble::getRandomRecord();
        } else {
            return PrizesErudit::getRandomRecord();
        }
    }

    public static function checkDayGamesPlayedRecord(array $players)
    {
        if (Game::$gameName === Game::SCRABBLE) {
            return PrizesScrabble::checkDayGamesPlayedRecord($players);
        } else {
            return PrizesErudit::checkDayGamesPlayedRecord($players);
        }
    }

    public static function checkDayGamePriceRecord($price, $cookie)
    {
        if (Game::$gameName === Game::SCRABBLE) {
            return PrizesScrabble::checkDayGamePriceRecord($price, $cookie);
        } else {
            return PrizesErudit::checkDayGamePriceRecord($price, $cookie);
        }
    }

    public static function checkDayTurnPriceRecord($price, ?int $commonId = null): array
    {
        if (!$commonId) {
            return [];
        }

        if (Game::$gameName === Game::SCRABBLE) {
            return PrizesScrabble::checkDayTurnPriceRecord($price, $commonId);
        } else {
            return PrizesErudit::checkDayTurnPriceRecord($price, $commonId);
        }
    }

    public static function checkDayWordPriceRecord($word, $price, $cookie)
    {
        if (Game::$gameName === Game::SCRABBLE) {
            return PrizesScrabble::checkDayWordPriceRecord($word, $price, $cookie);
        } else {
            return PrizesErudit::checkDayWordPriceRecord($word, $price, $cookie);
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

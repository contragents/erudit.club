<?php

namespace Dadata;

use Game;
use PrizesErudit;
use PrizesScrabble;

class Prizes
{
    public static function playerCurrentRecords($cookie = false)
    {
        if (Game::$gameName === Game::SCRABBLE) {
            return PrizesScrabble::playerCurrentRecords($cookie);
        } else {
            return PrizesErudit::playerCurrentRecords($cookie);
        }
    }

    public static function getRandomRecord()
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

    public static function checkDayTurnPriceRecord($price, $cookie)
    {
        if (Game::$gameName === Game::SCRABBLE) {
            return PrizesScrabble::checkDayTurnPriceRecord($price, $cookie);
        } else {
            return PrizesErudit::checkDayTurnPriceRecord($price, $cookie);
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

    public static function checkDayWordLenRecord($word, $cookie)
    {
        if (Game::$gameName === Game::SCRABBLE) {
            return PrizesScrabble::checkDayWordLenRecord($word, $cookie);
        } else {
            return PrizesErudit::checkDayWordLenRecord($word, $cookie);
        }
    }
}

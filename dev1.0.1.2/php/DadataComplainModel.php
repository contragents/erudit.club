<?php

namespace Dadata;

class ComplainModel
{
    const TABLE_NAME = 'complains';
    const COMPLAINS_PER_DAY = 5; // todo сделать 5

    public static function add(int $fromCommonId, int $toCommonId, array $chatLog): bool
    {
        $numComplains = DB::queryValue(
            "SELECT count(1) "
            . "FROM " . self::TABLE_NAME . " "
            . "WHERE "
            . "date_uniq = '" . date('Y-m-d') . "' "
            . "AND "
            . "from_common_id = $fromCommonId "
        ) ?: 0;
        if ($numComplains >= self::COMPLAINS_PER_DAY) {
            return false;
        }

        return DB::queryInsert(
            "INSERT INTO " . self::TABLE_NAME . " "
            . "SET "
            . "from_common_id = $fromCommonId, "
            . "to_common_id = $toCommonId, "
            . "date_uniq = '" . date('Y-m-d') . "', "
            . "chat_log = compress('" . DB::escapeString(serialize($chatLog)) . "') "
        ) ? true : false;
    }
}
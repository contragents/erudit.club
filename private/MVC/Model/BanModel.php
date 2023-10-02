<?php

class BanModel extends BaseModel
{
    const TABLE_NAME = 'ban';

    const BAN_TOTAL_COMPLAINT_COUNT = 5; // 5 жалоб до полного бана
    const BAN_PERSONAL_TTL = 60 * 60 * 24 * 14; // 2 недели персональный бан
    const BAN_TOTAL_TTL = self::BAN_PERSONAL_TTL * 2;

    const COMMON_ID_FIELD = 'common_id';
    const COMPLAINER_ID_FIELD = 'complainer_id';
    const TS_FROM_FIELD = 'date_from';
    const TS_TO_FIELD = 'date_to';

    public static function ban(int $commonId, int $complainerId): bool
    {
        self::personalBan($commonId, $complainerId);

        if (self::countComplaints($commonId) >= self::BAN_TOTAL_COMPLAINT_COUNT) {
            self::totalBan($commonId);
        }

        return true;
    }

    private static function personalBan(int $commonId, int $complainerId)
    {
        self::add(
            [
                self::COMMON_ID_FIELD => $commonId,
                self::COMPLAINER_ID_FIELD => $complainerId,
                self::TS_FROM_FIELD => time(),
                self::TS_TO_FIELD => time() + self::BAN_PERSONAL_TTL,
            ]
        );
    }

    private static function countComplaints(int $commonId): int
    {
        $countComplaintsQuery = ORM::select([ORM::agg(ORM::COUNT, self::ID_FIELD)], self::TABLE_NAME)
            . ORM::where(self::COMMON_ID_FIELD, '=', $commonId, true)
            . ORM::andWhere(self::IS_DELETED_FIELD, '=', 0, true)
            . ORM::limit(1);

        return DB::queryValue($countComplaintsQuery) ?: 0;
    }

    private static function totalBan(int $commonId)
    {
        self::add(
            [
                self::COMMON_ID_FIELD => $commonId,
                self::TS_FROM_FIELD => time(),
                self::TS_TO_FIELD => time() + self::BAN_TOTAL_TTL,
            ]
        );
    }

    public static function isBannedTotal(int $commonId): int
    {
        $totalBannedQuery = ORM::select([ORM::agg(ORM::MAX, self::TS_TO_FIELD)], self::TABLE_NAME)
            . ORM::where(self::COMMON_ID_FIELD, '=', $commonId, true)
            . ORM::andWhere(self::COMPLAINER_ID_FIELD, 'IS', 'NULL', true)
            . ORM::andWhere(self::TS_TO_FIELD, '>', time(), true)
            . ORM::andWhere(self::IS_DELETED_FIELD, '=', 0, true);

        $res = DB::queryValue($totalBannedQuery);

        return $res
            ? $res
            : 0;
    }

    public static function bannedBy(int $commonId): array
    {
        $bannedQuery = ORM::select(
                [ORM::agg(ORM::MAX, self::TS_TO_FIELD, self::TS_TO_FIELD), self::COMPLAINER_ID_FIELD],
                self::TABLE_NAME
            )
            . ORM::where(self::COMMON_ID_FIELD, '=', $commonId, true)
            . ORM::andWhere(self::TS_TO_FIELD, '>', time(), true)
            . ORM::andWhere(self::IS_DELETED_FIELD, '=', 0, true)
            . ORM::groupBy([self::COMPLAINER_ID_FIELD]);

        $res = DB::queryArray($bannedQuery) ?: [];

        $res = array_combine(array_column($res, self::COMPLAINER_ID_FIELD), array_column($res, self::TS_TO_FIELD));

        return $res;
    }

    public static function hasBanned(int $commonId): array
    {
        $bannedQuery = ORM::select(
                [ORM::agg(ORM::MAX, self::TS_TO_FIELD, self::TS_TO_FIELD), self::COMMON_ID_FIELD],
                self::TABLE_NAME
            )
            . ORM::where(self::COMPLAINER_ID_FIELD, '=', $commonId, true)
            . ORM::andWhere(self::TS_TO_FIELD, '>', time(), true)
            . ORM::andWhere(self::IS_DELETED_FIELD, '=', 0, true)
            . ORM::groupBy([self::COMMON_ID_FIELD]);

        $res = DB::queryArray($bannedQuery) ?: [];

        $res = array_combine(array_column($res, self::COMMON_ID_FIELD), array_column($res, self::TS_TO_FIELD));

        return $res;
    }

    public static function delete($commonId, $complainerId): bool
    {
        $query = ORM::update(self::TABLE_NAME)
            . ORM::set(['field' => self::IS_DELETED_FIELD, 'value' => 1])
            . ORM::where(self::COMMON_ID_FIELD, '=', $commonId, true)
            . ORM::andWhere(self::COMPLAINER_ID_FIELD, '=', $complainerId, true);

        return DB::queryInsert($query) ? true : false;
    }
}
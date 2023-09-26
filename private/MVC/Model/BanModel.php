<?php

class BanModel extends BaseModel
{
    const TABLE_NAME = 'ban';

    const BAN_TOTAL_COMPLAINT_COUNT = 5; // 5 жалоб до полного бана
    const BAN_PERSONAL_TTL = 60 * 60 * 24 * 14; // 2 недели персональный бан
    const BAN_TOTAL_TTL = self::BAN_PERSONAL_TTL * 2;

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
                'common_id' => $commonId,
                'complainer_id' => $complainerId,
                'date_from' => time(),
                'date_to' => time() + self::BAN_PERSONAL_TTL,
            ]
        );
    }

    private static function countComplaints(int $commonId): int
    {
        $countComplaintsQuery = ORM::select(['count(id)'], self::TABLE_NAME)
            . ORM::where('to_common_id', '=', $commonId, true)
            . ORM::limit(1);

        return DB::queryValue($countComplaintsQuery) ?: 0;
    }

    private static function totalBan(int $commonId)
    {
        self::add(
            [
                'common_id' => $commonId,
                'date_from' => time(),
                'date_to' => time() + self::BAN_TOTAL_TTL,
            ]
        );
    }

    public static function isBannedTotal(int $commonId): int
    {
        $totalBannedQuery = ORM::select(['max(date_to)'], self::TABLE_NAME)
            . ORM::where('common_id', '=', $commonId, true)
            . ORM::andWhere('complainer_id', 'IS', 'NULL', true)
            . ORM::andWhere('date_to', '>', time(), true);

        $res = DB::queryValue($totalBannedQuery);

        return $res
            ? $res
            : 0;
    }

    public static function bannedBy(int $commonId): array
    {
        $bannedQuery = ORM::select(['max(date_to) as date_to', 'complainer_id'], self::TABLE_NAME)
            . ORM::where('common_id', '=', $commonId, true)
            . ORM::andWhere('date_to', '>', time(), true)
            . ORM::groupBy(['complainer_id']);

        $res = DB::queryArray($bannedQuery) ?: [];

        $res = array_combine(array_column($res, 'complainer_id'), array_column($res, 'date_to'));

        return $res;
    }
}
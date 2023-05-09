<?php

class AvatarModel extends BaseModel
{
    const TABLE_DEFAULT_URL = 'avatar_urls';

    public static function getDefaultAvatar($playerID)
    {
        $maxImgId = 34768;
        $imgId = $playerID % $maxImgId;
        $query = ORM::select(['concat(site,mini_url)'], self::TABLE_DEFAULT_URL)
            .ORM::where('site_img_id', '>=', $imgId, true)
            .ORM::limit(1);

        return DB::queryValue($query);
    }
}
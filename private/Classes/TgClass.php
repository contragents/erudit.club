<?php

class Tg
{
    const ILYA_TG_ID = 294789510;
    public const TG_USER_INFO_ = 'tg_user_info_';
    public const TG_USER_CACHE_TTL = 7 * 24 * 60 * 60;

    private const BOT_URL = 'https://xn--d1aiwkc2d.club/bot/send?';
    private const GAME_PARAM = 'game';
    private const TEXT_PARAM = 'text';
    private const TG_ID_PARAM = 'tg_id';

    public static ?array $tgUser = null;
    public static ?int $commonId = null;

    public static function botSendMessage(string $textHtml, ?int $tgId = null, string $gameName = Game::ERUDIT): bool
    {
        $params = [
                self::TEXT_PARAM => urlencode($textHtml),
                self::GAME_PARAM => $gameName,
            ]
            + ($tgId
                ? [self::TG_ID_PARAM => $tgId]
                : []);
        $result = @file_get_contents(
            self::BOT_URL
            . implode('&',array_map(fn($param, $value) => "$param=$value", array_keys($params), $params))
        );

        $result = json_decode($result, true) ?? [];

        return ($result['status'] ?? false) === 'success';
    }

    public static function authorize(): bool
    {
        // looking for tg authorization/ signature data
        if (self::$tgUser) {
            return true;
        }

        if ($_POST['tg_authorize'] ?? false) {
            if (self::checkUserDataUnsafe($_POST)) {
                self::$tgUser = self::tgInitDataDecode($_POST);

                Cache::setex(
                    self::TG_USER_INFO_ . self::$tgUser['hash'] . '_' . self::$tgUser['user']['id'],
                    self::TG_USER_CACHE_TTL,
                    self::$tgUser
                );
                self::$commonId = PlayerModel::getPlayerID(self::$tgUser['user']['id'], true);
                TgUserModel::refreshByTgUserArr(self::$tgUser);

                return true;
            }
        } // looking hash+tg_id in cache
        elseif (
            !empty($_REQUEST['tg_hash'])
            && !empty($_REQUEST['tg_id'])
            && (self::$tgUser = Cache::get(self::TG_USER_INFO_ . $_REQUEST['tg_hash'] . '_' . $_REQUEST['tg_id']))
        ) {
            self::$commonId = (int)PlayerModel::getPlayerID(self::$tgUser['user']['id']);

            return true;
        }

        return false;
    }

    public static function checkUserDataUnsafe($data): bool
    {
        $botToken = Config::$envConfig[TgUserModel::BOT_TOKEN_CONFIG_KEY[T::$lang]];
        $hash = false;
        $newData = [];

        foreach ($data as $key => $value) {
            if ($key == 'hash') {
                $hash = $value;
                continue;
            }
            if ($key == 'tg_authorize') {
                continue;
            }
            $newData += [$key => $value];
        }

        if (!$hash) {
            return false;
        }

        ksort($newData);

        $arrayData = [];
        foreach ($newData as $k => $v) {
            array_push($arrayData, "$k=" . $v);
        }

        $arrayData = implode("\n", $arrayData);

        $secret_key = hash_hmac('sha256', $botToken, "WebAppData", true);

        if (hash_hmac('sha256', $arrayData, $secret_key) == $hash) {
            return true;
        } else {
            return false;
        }
    }

    public static function tgInitDataDecode($data): array
    {
        $tgUser = $data;
        unset($tgUser['tg_authorize']);
        $tgUser['user'] = json_decode($tgUser['user'], true);

        return $tgUser;
    }
}
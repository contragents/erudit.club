<?php

class BanController extends BaseController
{
    const USER_NOT_FOUND_ERROR = ['result' => 'error', 'message' => 'Пользователь не найден'];
    const BANNED_USER_NOT_FOUND_ERROR = ['result' => 'error', 'message' => 'Бан пользователя не найден'];

    const SUCCESS = ['result' => 'success', 'message' => 'Игрок разблокирован'];

    public function Run()
    {
        return parent::Run();
    }

    public function removeAction()
    {
        $cookie = $_COOKIE[Cookie::COOKIE_NAME] ?? false;
        if (!$cookie) {
            return self::USER_NOT_FOUND_ERROR;
        }

        $commonId = PlayerModel::getCommonID($cookie);

        if (!$commonId) {
            return self::USER_NOT_FOUND_ERROR;
        }

        return BanModel::delete(self::$Request[BanModel::COMMON_ID_FIELD], $commonId)
            ? self::SUCCESS
            : self::BANNED_USER_NOT_FOUND_ERROR;

        return ['I banned' => BanModel::hasBanned($commonId), 'I was banned' => BanModel::bannedBy($commonId)];
    }
}
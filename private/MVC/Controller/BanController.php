<?php

class BanController extends BaseController
{
    const USER_NOT_FOUND_ERROR = ['result' => 'error', 'message' => 'Player not found'];
    const BANNED_USER_NOT_FOUND_ERROR = ['result' => 'error', 'message' => 'Player`s ban not found'];

    const SUCCESS = ['result' => 'success', 'message' => 'Player is unbanned'];

    public function Run()
    {
        return parent::Run();
    }

    public function removeAction()
    {
        $cookie = $_COOKIE[Cookie::COOKIE_NAME] ?? false;
        if (!$cookie) {
            return T::SA(self::USER_NOT_FOUND_ERROR);
        }

        $commonId = PlayerModel::getCommonID($cookie);

        if (!$commonId) {
            return T::SA(self::USER_NOT_FOUND_ERROR);
        }

        return T::SA(BanModel::delete(self::$Request[BanModel::COMMON_ID_FIELD], $commonId)
            ? self::SUCCESS
            : self::BANNED_USER_NOT_FOUND_ERROR
        );
    }
}
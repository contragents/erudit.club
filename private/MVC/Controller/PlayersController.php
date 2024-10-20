<?php

class PlayersController extends BaseController
{
    const GAME_ID_PARAM = 'game_id';
    const HIDE_PARAM = 'hide'; // hide/show

    const PARAM_VALUES = [
        self::HIDE_PARAM => [self::HIDE, self::SHOW],
    ];

    const COMMON_URL = 'mvc/players/';
    const HIDE = 'hide';
    const SHOW = 'show';


    public function Run()
    {
        ini_set("display_errors", 1); error_reporting(E_ALL);

        return parent::Run();
    }

    public function hideBalanceAction(): string
    {
        $res = [];
        $res['message'] = T::S('Error changing settings. Try again later');

        $hide = self::$Request[self::HIDE_PARAM] ?? false;

        $commonId = self::$Request[self::COMMON_ID_PARAM] ?? false;

        if($commonId && PlayerModel::validateCommonIdByCookie($commonId, $_COOKIE[Cookie::COOKIE_NAME])) {
            if (in_array($hide, self::PARAM_VALUES[self::HIDE_PARAM])) {
                $user = UserModel::getOneO($commonId, true);

                $user->_is_balance_hidden = $hide === self::HIDE;

                if ($user->save()) {
                    $balance = BalanceModel::getBalance($user->_id) ?: 0;
                    $res['balance'] = $user->_is_balance_hidden
                        ? "**$balance**"
                        : $balance;
                    $res[UserModel::BALANCE_HIDDEN_FIELD] = (bool)($user->_is_balance_hidden ?? false);
                    unset($res['message']);
                }
            }
        }

        return json_encode(
            $res,
            JSON_UNESCAPED_UNICODE
        );
    }

    public function infoAction(): string
    {
        // todo проверять куки на соответствие common_id

        $gameId = self::$Request[self::GAME_ID_PARAM] ?? false;
        $commonId = self::$Request[self::COMMON_ID_PARAM] ?? false;
        $res = [];

        if ($gameId && $gameId > 0 && $commonId) {
            $gameStatus = Cache::get(Game::GAME_STATUS_KEY . $gameId);

            if (is_array($gameStatus)) {
                foreach ($gameStatus['users'] ?? [] as $numUser => $user) {
                    $res[$numUser]['common_id'] = $user['common_id'] ?? 0;
                    $res[$numUser]['you'] = $res[$numUser]['common_id'] == $commonId;

                    if ($res[$numUser]['common_id'] == 0) {
                        continue;
                    }

                    $thisUser = UserModel::getOneO($res[$numUser]['common_id'], true);

                    $res[$numUser]['nickname'] = $thisUser->_name
                        ?: PlayerModel::getPlayerName($user);

                    $res[$numUser]['avatar_url'] = $thisUser->_avatar_url
                        ?? PlayerModel::getAvatarUrl($thisUser->_id, true);

                    $res[$numUser]['stats_url'] = '/' . StatsController::getUrl(
                            'viewV2',
                            [StatsController::COMMON_ID_PARAM => $thisUser->_id]
                        );

                    $res[$numUser][UserModel::BALANCE_HIDDEN_FIELD] = ($thisUser->_is_balance_hidden ?? false);

                    $balance = BalanceModel::getBalance($thisUser->_id) ?: 0;
                    $res[$numUser]['balance'] = !($thisUser->_is_balance_hidden ?? false)
                        ? $balance
                        : (
                        $res[$numUser]['you']
                            ? "**$balance**"
                            : BalanceModel::HIDDEN_BALANCE_REPLACEMENT
                        );

                    $res[$numUser]['rating'] = CommonIdRatingModel::getRating($thisUser->_id, Game::$gameName);
                    $res[$numUser]['rating_position'] = CommonIdRatingModel::getTopByRating(
                        $res[$numUser]['rating'],
                        Game::$gameName
                    );

                    $res[$numUser]['games_played'] = RatingHistoryModel::getNumGamesPlayed(
                        $thisUser->_id,
                        Game::$gameName
                    );

                    if ($res[$numUser]['rating_position'] <= 10) {
                        $res[$numUser]['top_bage_url'] = '/img/prizes/top_'
                            . ($res[$numUser]['rating_position'] <= 3 ? $res[$numUser]['rating_position'] : '10')
                            . '.svg';
                    }


                    $achieves = AchievesModel::getCurrentAchievesByCommonId($thisUser->_id);
                    if (!empty($achieves)) {
                        $res[$numUser]['achieves'] = [];
                        foreach ($achieves as $achieve) {
                            $res[$numUser]['achieves'][] = [
                                AchievesModel::DATE_ACHIEVED_FIELD => $achieve[AchievesModel::DATE_ACHIEVED_FIELD],
                                AchievesModel::EVENT_TYPE_FIELD => $achieve[AchievesModel::EVENT_TYPE_FIELD],
                                AchievesModel::EVENT_PERIOD_FIELD => $achieve[AchievesModel::EVENT_PERIOD_FIELD],
                                AchievesModel::WORD_FIELD => $achieve[AchievesModel::WORD_FIELD],
                                AchievesModel::EVENT_VALUE_FIELD => $achieve[AchievesModel::EVENT_VALUE_FIELD]
                            ];
                        }
                    }
                }
            }
        }

        return json_encode(
            $res,
            JSON_UNESCAPED_UNICODE
        );
    }
}
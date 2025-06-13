<?php

class StatsController extends BaseController
{
    const NO_STONE_PARAM = 'no_stone';
    const NO_BRONZE_PARAM = 'no_bronze';
    const NO_SILVER_PARAM = 'no_silver';
    const NO_GOLD_PARAM = 'no_gold';
    const FILTER_PLAYER_PARAM = 'opponent_id';

    const COMMON_URL = 'mvc/stats/';

    const RATING_PARAM = 'rating';
    const ACHIEVE_PARAM = 'achieves';
    const COIN_PARAM = 'coins';

    const RATING_POSITION_FROM_PARAM = 'rating_position_from';
    const RATING_POSITION_TO_PARAM = 'rating_position_to';
    const RATING_CHUNK = 20;
    const DATA_TYPE_PARAM = 'data_type';
    const COIN_POSITION_FROM_PARAM = 'coin_position_from';
    const COIN_POSITION_TO_PARAM = 'coin_position_to';
    const ANONYM_AVATAR_URL = 'https://avatarko.ru/img/avatar/1/avatarko_anonim.jpg';

    public function wordAction(): string
    {
        $content = '';

        $CONTENT_SELECT = "SELECT 
content COLLATE utf8_general_ci, 
content_perevod COLLATE utf8_general_ci
FROM 
gufo_me 
WHERE 
slovo = '" . urldecode($_REQUEST['word']) . "'
UNION
SELECT
comment COLLATE utf8_general_ci as content,
substring(comment,1,0) COLLATE utf8_general_ci as content_perevod
FROM
dict_cambrige
WHERE 
slovo = '" . urldecode($_REQUEST['word']) . "'
UNION
SELECT
comment COLLATE utf8_general_ci as content,
substring(comment,1,0) COLLATE utf8_general_ci as content_perevod
FROM
dict
WHERE 
slovo = '" . urldecode(self::$Request['word']) . "';";

        $res = DB::queryArray($CONTENT_SELECT);
        if (!is_array($res) || empty($res)) {
            $content .= T::S("Слово не найдено.");
        } else {
            $row = current($res);
            if (!is_array($row) || empty($row)) {
                $content .= T::S("Слово не найдено.");
            }

            foreach ($row as $field => $value) {
                if ($spacePos = strpos($field, ' ')) {
                    $row[substr($field, 0, $spacePos)] = $value;
                }
            }
        }

        // убираем всякую херню после парсинга
        if (strstr($_SERVER['HTTP_REFERER'] ?? '', 'andex') || strstr($_SERVER['HTTP_REFERER'] ?? '', '-5.su')) {
            $row['content'] = str_replace('href=', '', $row['content']);
            $row['content_perevod'] = str_replace('href=', '', $row['content_perevod']);
        }

        $row['content'] = str_ireplace(
            $_REQUEST['word'] . ' noun',
            '<h2>' . strtoupper($_REQUEST['word']) . ' noun</h2>',
            $row['content']
        );

        $content .= str_replace(
            ["\r\n", "\n"],
            '<br />',
            str_replace('href="', 'href="' . Config::$config['domain'], $row['content'] . $row['content_perevod'])
        );

        $content = preg_replace('/googletag\.cmd\.push\(.{0,400}\}\);/','', $content);

        if(T::$lang === T::EN_LANG) {
            $content = str_replace(T::CYR,'', $content);
        }

        return json_encode(['result' => $content], JSON_UNESCAPED_UNICODE);
    }

    /**
     * @param AchievesModel $achieveModel
     * @return array
     */
    private static function getAchieveTranslated(AchievesModel $achieveModel): array
    {
        $res = [];
        $res['record_type'] = strtoupper(T::S('record of the ' . $achieveModel->_event_period));
        $res['record_value'] = ($achieveModel->_word ?? '') . ' ' . $achieveModel->_event_value;
        $res['avatar_url'] = PlayerModel::getAvatarUrl($achieveModel->_common_id);
        $res['nickname'] = AchievesModel::getPlayerNameByCommonId($achieveModel->_common_id);
        $res['card_type'] = AchievesModel::TOP_TYPES[$achieveModel->_event_period] ?? '';

        return $res;
    }

    public function leadersAction(): string
    {
        $result = [self::RATING_PARAM => [], self::ACHIEVE_PARAM => [], self::COIN_PARAM => []];

        try {
            // Собираем лидеров по рейтингам
            if (in_array(self::$Request[self::DATA_TYPE_PARAM] ?? '', [self::RATING_PARAM, ''])) {
                $fromRatingPos = self::$Request[self::RATING_POSITION_FROM_PARAM] ?? 1;
                $toRatingPos = self::$Request[self::RATING_POSITION_TO_PARAM] ?? ($fromRatingPos + self::RATING_CHUNK - 1);

                if (($toRatingPos - $fromRatingPos) >= self::RATING_CHUNK) {
                    $toRatingPos = $fromRatingPos + self::RATING_CHUNK - 1;
                }

                $ratingModels = CommonIdRatingModel::getTopPlayersO(Game::$gameName, $fromRatingPos, $toRatingPos);

                foreach ($ratingModels as $top => $rows) {
                    foreach ($rows as $ratingModel) {
                        $result[self::RATING_PARAM][$top][] = [
                            'avatar_url' => PlayerModel::getAvatarUrl($ratingModel->_id),
                            'card_type' => AchievesModel::TOP_TYPES[$top] ?? '',
                            self::RATING_PARAM => $ratingModel->{'_rating_' . Game::$gameName},
                            'nickname' => AchievesModel::getPlayerNameByCommonId($ratingModel->_id)
                        ];
                    }
                }
            }

            // Собираем обладателей достижений
            if (in_array(self::$Request[self::DATA_TYPE_PARAM] ?? '', [self::ACHIEVE_PARAM, ''])) {
                $achieves = AchievesModel::getActiveO(Game::$gameName);

                foreach ($achieves as $achieveModel) {
                    // Игнорируем ТОПов по рейтингу
                    if ($achieveModel->_event_type != AchievesModel::TOP_TYPE) {
                        $result[self::ACHIEVE_PARAM]
                        [strtoupper(T::S($achieveModel->_event_type))]
                        [$achieveModel->_event_period] = self::getAchieveTranslated($achieveModel);
                    }
                }
            }

            // Собираем лидеров по монетам
            if (in_array(self::$Request[self::DATA_TYPE_PARAM] ?? '', [self::COIN_PARAM, ''])) {
                $fromCoinPos = self::$Request[self::COIN_POSITION_FROM_PARAM] ?? 1;
                $toCoinPos = self::$Request[self::COIN_POSITION_TO_PARAM] ?? ($fromCoinPos + self::RATING_CHUNK - 1);

                if (($toCoinPos - $fromCoinPos) >= self::RATING_CHUNK) {
                    $toCoinPos = $fromCoinPos + self::RATING_CHUNK - 1;
                }

                $balanceModels = BalanceModel::getTopPlayersO($fromCoinPos, $toCoinPos);
                foreach ($balanceModels as $top => $rows) {
                    foreach ($rows as $balanceModel) {
                        $userModel = UserModel::getOneO($balanceModel->_id);
                        $result[self::COIN_PARAM][$top][] = [
                            'avatar_url' => ($userModel->_is_balance_hidden ?? false)
                                ? self::ANONYM_AVATAR_URL
                                : PlayerModel::getAvatarUrl($balanceModel->_id),
                            'card_type' => AchievesModel::TOP_TYPES[$top] ?? '',
                            self::COIN_PARAM => $balanceModel->_sudoku,
                            'nickname' => ($userModel->_is_balance_hidden ?? false)
                                ? T::S(UserModel::BALANCE_HIDDEN_FIELD)
                                : AchievesModel::getPlayerNameByCommonId($balanceModel->_id)
                        ];
                    }
                }
            }
        } catch (Throwable $e) {
            return json_encode(['exception' => $e->__toString()], JSON_UNESCAPED_UNICODE);
        }

        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    public static function addTranslationsToAchieves(array &$achieves): void
    {
        foreach ($achieves as &$achieve) {
            if ($achieve['event_type'] === AchievesModel::TOP_TYPE) {
                $achieve['record_type_text'] = T::S('rank position');
                $achieve['event_type_text'] = T::S(
                    AchievesModel::TOP_TYPE . '_' . $achieve[AchievesModel::EVENT_PERIOD_FIELD]
                );
                $achieve['points_text'] = $achieve[AchievesModel::EVENT_VALUE_FIELD];
            } else {
                $achieve['record_type_text'] = T::S('record of the ' . $achieve[AchievesModel::EVENT_PERIOD_FIELD]);
                $achieve['event_type_text'] = T::S($achieve[AchievesModel::EVENT_TYPE_FIELD]);
                $achieve['points_text'] = $achieve['word'] == ''
                    ? $achieve[AchievesModel::EVENT_VALUE_FIELD]
                    : ($achieve['word'] . ' - ' . $achieve[AchievesModel::EVENT_VALUE_FIELD]);
            }

            $achieve[AchievesModel::REWARD_FIELD] = self::trimRightZeros(
                MonetizationService::REWARD[$achieve[AchievesModel::EVENT_PERIOD_FIELD]],
                2
            );
            $achieve[AchievesModel::INCOME_FIELD] = self::trimRightZeros(
                MonetizationService::INCOME[$achieve[AchievesModel::EVENT_PERIOD_FIELD]],
                4
            );
        }
    }

    public static function trimRightZeros(string $value, int $numDecimals = 4): string
    {
        $value = number_format($value, $numDecimals, '.', ' ');

        for ($i = 0; $i < $numDecimals; $i++) {
            $value = rtrim($value, '0');
        }

        return rtrim($value, '.,');
    }

    private static function getViewFilters(): array
    {
        return [
            self::NO_STONE_PARAM => self::$Request[self::NO_STONE_PARAM] ?? false,
            self::NO_BRONZE_PARAM => self::$Request[self::NO_BRONZE_PARAM] ?? false,
            self::NO_SILVER_PARAM => self::$Request[self::NO_SILVER_PARAM] ?? false,
            self::NO_GOLD_PARAM => self::$Request[self::NO_GOLD_PARAM] ?? false,
        ];
    }

    private static function getGamesFilters(): array
    {
        return [
            self::FILTER_PLAYER_PARAM => self::$Request[self::FILTER_PLAYER_PARAM] ?? false,
        ];
    }

    public function Run()
    {
        return parent::Run();
    }

    public function prizesAction(): string
    {
        $res = '';

        foreach (AchievesModel::PRIZE_LINKS as $link) {
            $res .= ViewHelper::img(['src' => $link]);
        }

        return $res;
    }

    public function gamesV2Action(bool $initialRequest = false)
    {
        $baseUrl = self::getUrl('gamesV2', self::$Request, ['page']);

        $gamesCount = AchievesModel::getGamesByCommonIdCount(self::$Request['common_id'], self::getGamesFilters());

        if ($gamesCount < ((self::$Request['page'] ?? 1) - 1) * AchievesModel::LIMIT) {
            unset(self::$Request['page']);
        }

        $games = AchievesModel::getGamesByCommonIdV2(
            self::$Request['common_id'],
            AchievesModel::LIMIT,
            self::$Request['page'] ?? 1,
            self::getGamesFilters()
        );

        $pagination = ViewHelper::paginationArr(
            StatsController::$Request['page'] ?? 1,
            ceil($gamesCount / AchievesModel::LIMIT),
            $baseUrl
        );
        $res = ['games' => $games, 'pagination' => $pagination];

        if ($initialRequest) {
            return $res;
        } else {
            if (self::getGamesFilters()[self::FILTER_PLAYER_PARAM]) {
                $opponentStats = AchievesModel::getStatsVsOpponent(
                    self::$Request['common_id'],
                    self::getGamesFilters()[self::FILTER_PLAYER_PARAM]
                );

                $res['opponent_stats'] = $opponentStats;
            }

            return json_encode($res, JSON_UNESCAPED_UNICODE);
        }
    }

    public function viewV2Action(): string
    {
        try {
            $result = [
                'player_name' => AchievesModel::getPlayerNameByCommonId(self::$Request['common_id']),
                'player_avatar_url' => PlayerModel::getAvatarUrl(self::$Request['common_id'])
            ];

            $baseUrl = self::getUrl('viewV2', self::$Request, ['page']);

            $baseUrlPage = self::getUrl('viewV2', self::$Request);

            $achieves = AchievesModel::getCurrentAchievesByCommonId(self::$Request['common_id']);
            $pastAchieves = AchievesModel::getPastAchievesByCommonId(self::$Request['common_id']);

            self::addTranslationsToAchieves($achieves);
            self::addTranslationsToAchieves($pastAchieves);

            $result['current_achieves'] = $achieves;
            $result['past_achieves'] = $pastAchieves;

            $result += $this->gamesV2Action(true);
        } catch (Throwable $e) {
            return json_encode(['exception' => $e->__toString()], JSON_UNESCAPED_UNICODE);
        }

        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    public function viewAction()
    {
        $baseUrl = self::getUrl('view', self::$Request, ['page']);

        $achievesCount = AchievesModel::getAchievesByCommonIdCount(self::$Request['common_id'], self::getViewFilters());
        if ($achievesCount < ((self::$Request['page'] ?? 1) - 1) * AchievesModel::LIMIT) {
            unset(self::$Request['page']);
        }

        $baseUrlPage = self::getUrl('view', self::$Request);

        $achieves = AchievesModel::getAchievesByCommonId(
            self::$Request['common_id'],
            AchievesModel::LIMIT,
            self::$Request['page'] ?? 1,
            self::getViewFilters()
        );

        foreach ($achieves as $num => $row) {
            $achieves[$num][AchievesModel::EVENT_TYPE_FIELD] = ViewHelper::tag(
                'img',
                '',
                [
                    'src' => (AchievesModel::PRIZE_LINKS[$row[AchievesModel::EVENT_TYPE_FIELD]] ?? ''),
                    'width' => '100%',
                    'alt' => 'Пусто'
                ]
            );

            $achieves[$num][AchievesModel::DATE_ACHIEVED_FIELD] = ViewHelper::tag(
                'span',
                $row[AchievesModel::DATE_ACHIEVED_FIELD],
                [
                    'style' => 'white-space: nowrap;'
                ]
            );
        }

        if (BaseController::isAjaxRequest()) {
            return StatsAchievesView::render($baseUrl, $baseUrlPage, $achieves, $achievesCount);
        } else {
            return StatsAchievesView::renderFull([$baseUrl, $baseUrlPage, $achieves, $achievesCount]);
        }
    }

    public function testAction()
    {
        return var_export(self::isAjaxRequest(), true);
    }

    public function gamesAction()
    {
        $baseUrl = self::getUrl('games', self::$Request, ['page']);

        $gamesCount = AchievesModel::getGamesByCommonIdCount(self::$Request['common_id'], self::getGamesFilters());

        if ($gamesCount < ((self::$Request['page'] ?? 1) - 1) * AchievesModel::LIMIT) {
            unset(self::$Request['page']);
        }

        $baseUrlPage = self::getUrl('games', self::$Request);

        $games = AchievesModel::getGamesByCommonId(
            self::$Request['common_id'],
            AchievesModel::LIMIT,
            self::$Request['page'] ?? 1,
            self::getGamesFilters()
        );

        if (self::getGamesFilters()[self::FILTER_PLAYER_PARAM]) {
            $opponentStats = AchievesModel::getStatsVsOpponent(
                self::$Request['common_id'],
                self::getGamesFilters()[self::FILTER_PLAYER_PARAM]
            );
        } else {
            $opponentStats = false;
        }


        if (BaseController::isAjaxRequest()) {
            return StatsAchievesGamesView::render($baseUrl, $baseUrlPage, $games, $gamesCount, $opponentStats);
        } else {
            return StatsAchievesGamesView::renderFull([$baseUrl, $baseUrlPage, $games, $gamesCount, $opponentStats]);
        }
    }
}
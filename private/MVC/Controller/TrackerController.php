<?php

class TrackerController extends BaseController
{
    const WRONG_TRAFFIC_TYPE = 'wrong device type or Country';

    const MACROSES = [
        'getSid' => '[SID]',
        'getTid' => '[TID]',
        'getBid' => '[BID]',
        'getCid' => '[CID]',
        'getClickID' => '[CLICK]'
    ];

    const CHECK_STATUS = ['success' => 'success', 'error' => 'error'];

    /**
     * @var false|int
     */
    private static $ClickInsertID = false;

    public function Run()
    {
        if (empty(self::$Request['fhash'])) {
            return [
                'status' => self::CHECK_STATUS['error'],
                'message' => 'Bad Request'
            ];
        }

        //Проверка наличия площадки в системе
        $siteCheckStatus = self::checkSite();
        if ($siteCheckStatus['status'] != self::CHECK_STATUS['success']) {
            return $siteCheckStatus;
        }

        //Наполнение ГЕО-данных по IP
        Teaser::gatherGeoInfo(self::$Request);

        //Проверка const, tmp - ID, создание новых.
        //Далее const и tmp берем из Customer::
        Customer::checkToken(self::$Request);

        return parent::Run();
    }

    private static function checkSite(): array
    {
        if (
            empty(self::$Request['host'])
            ||
            empty(self::$Request['site_id'])
            ||
            !SiteModel::check(self::$Request['host'], self::$Request['site_id'])
        ) {
            return [
                'status' => self::CHECK_STATUS['error'],
                'message' => SiteModel::ACCESS_DENIED
                    . (Config::$config['env'] == Config::DEV
                        ? (' domain: ' . (self::$Request['host'] ?? '') . ' |  hash: ' . (self::$Request['site_id'] ?? ''))
                        : '')
            ];
        } else {
            return ['status' => self::CHECK_STATUS['success']];
        }
    }

    public function visitAction()
    {
        $numTeasers = SiteModel::getNumTeasers(
            self::$Request['host'],
            self::$Request['site_id'],
        );

        $siteCategories = SiteModel::getCategories(
            self::$Request['host'],
            self::$Request['site_id'],
        );

        // mp($siteCategories,'Sitecategories','Categories'); // test

        if (isset($_REQUEST['cli_mode'])) {
            print "Num Teasers: $numTeasers\n";
        }

        $resultTeasersArr =
            array_merge(
                ['status' => 'success'],
                [
                    'constv_id' => Customer::$constToken,
                    'tmpv_id' => Customer::$tmpToken
                ],
                [
                    'teasers' => Customer::getTeasers(
                        $numTeasers,
                        $siteCategories,
                        self::$Request['device_type'],
                        self::$Request['country_id'],
                    )
                ]
            );

        if (Config::isDebug()) {
            mp(
                [
                    'request' => self::$Request,
                    'cookies' => $_SERVER["HTTP_COOKIE"],
                    'response' => $resultTeasersArr
                ],
                'Result Teasers Array',
                __METHOD__
            );
        }

        return $resultTeasersArr;
    }

    public function viewAction()
    {
        $badTeasers = [];
        $trafType = self::$Request['device_type'];

        foreach (self::$Request['ids'] as $numID => $teaserID) {
            if (!Teaser::checkTeaser($teaserID['tid'], self::$Request['country_id'])) {
                $badTeasers[self::WRONG_TRAFFIC_TYPE][$teaserID['tid']] = $trafType;
                continue; // Если Тизер не прошел проверку, то статистика по нему не учитывается
            }

            if (self::$Request['action'] == 'view' && !isset(self::$Request['scroll'])) {
                self::$Request['scroll'] = '0%';
                //continue; Не учитываем вью, если не прислали scroll - глюк браузера
            }

            $isUniq = false;

            if (Tracker::isUniq(
                self::$Request['host'],
                $teaserID['tid'],
                Customer::$constToken,
                Customer::$tmpToken,
                self::$Request['action']
            )
            ) {
                $isUniq = true;

                // Сохраняем факт просмотра тизера в кеш для последующего обновления bid_ctr-множеств
                Tracker::incEventInTmpSet(
                    $teaserID['tid'],
                    self::$Request['country_id'],
                    $trafType,
                    self::$Request['action']
                );

                Tracker::updateInstantCTR(
                    $teaserID['tid'],
                    $trafType,
                    self::$Request['action'],
                    self::$Request['country_id']
                );
            } else {
                $badTeasers["{$teaserID['tid']} " . self::$Request['action']] = 'Is Not UNIQ';
            }

            if (self::$Request['action'] == 'view') {
                // Сохраняем уникальные и неуникальные просмотры в БД
                ViewModel::save(
                    [
                        'customer_id' => Customer::$constToken,
                        'visit_id' => Customer::$tmpToken,
                        'timestamp' => time(),
                        'block_id' => $teaserID['bid'],
                        'traf_type' => $trafType,
                        'country_id' => self::$Request['country_id'],
                        'fhash' => self::$Request['fhash'],
                        'scroll' => self::$Request['scroll'] ?? '0%', // Иногда scroll не приходит
                        'teaser_id' => $teaserID['tid'],
                        'is_unique' => $isUniq ? 'true' : 'false'
                    ]
                );

                // Обновление счетчика показов бонусного тизера
                Teaser::incrTeaserBonusView($teaserID['tid']);
            } else {
                // Сохраняем клики в БД и получаем click_id
                self::$ClickInsertID = ClickModel::save(
                    [
                        'visit_id' => Customer::$tmpToken,
                        'timestamp' => time(),
                        'block_id' => $teaserID['bid'],
                        'teaser_id' => $teaserID['tid'],
                        'customer_id' => Customer::$constToken,
                        'device_type_id' => AdvCampaignModel::DEVICE_TYPE[self::$Request['device_type']] ?? AdvCampaignModel::DEVICE_TYPE['default'],
                        'country_id' => self::$Request['country_id'],
                        'is_unique' => $isUniq ? 'true' : 'false'
                    ]
                );
            }
        }

        return empty($badTeasers)
            ? ['status' => 'success']
            : ['status' => 'errors', 'info' => $badTeasers];
    }

    public function clickAction()
    {
        // Подготовка $Request..
        self::$Request['ids'][] = ['tid' => self::$Request['tid'], 'bid' => self::$Request['bid']];
        self::$Request['action'] = 'click';

        $result = $this->viewAction();

        if ($result['status'] == 'success') {
            $this->charge();
            $this->custTeaserNotShowSave();
        } else {
            mp($result, 'Click IS NOT Uniq!', __METHOD__);
        }

        if (!isset($_REQUEST['cli_mode'])) {
            //Возвращаем ссылку для переадресации
            $teaserUrl = TeaserModel::getOne(self::$Request['tid'])['url'];
            if (strpos($teaserUrl, '[')) {
                foreach (self::MACROSES as $method => $macros) {
                    $teaserUrl = str_replace($macros, $this->$method(), $teaserUrl);
                }
            }

            // Возвращаем статус переадресации и URL ссылки
            header("Location: $teaserUrl", true, 302);
            exit();
        } else {
            return $result;
        }
    }

    private function getSid()
    {
        return self::$Request['site_id'];
    }

    private function getTid()
    {
        return self::$Request['tid'];
    }

    private function getBid()
    {
        return self::$Request['bid'];
    }

    private function getCid()
    {
        return TeaserModel::getOne(self::$Request['tid'])['advc_id'] ?? 'NA';
    }

    private function getClickID($default = 'NA')
    {
        return self::$ClickInsertID ?: $default;
    }

    public static function response(array $response, $cliMode = false)
    {
        if (!$cliMode) {
            return json_encode($response);
        } elseif (isset($response['teasers'])) {
            Tracker::$response = $response;
        }

        return print_r($response, true);
    }

    private function charge()
    {
        $clickID = $this->getClickID(0);
        $teaserID = self::getTid();
        $blockID = self::getBid();
        $teaserInfo = TeaserModel::getOne($teaserID);
        $siteUserID = SiteModel::getUserID(self::$Request['host'], self::$Request['site_id']);
        $teaserUserID = $teaserInfo['user_id'];
        $teaserRkID = $teaserInfo['advc_id'];
        $cpc = $teaserInfo['geo_country'][self::$Request['country_id']] ?? 0;
        $comission = BalanceModel::DEFAULT_COMISSION;
        if (BalanceModel::makeCharge(
            $teaserUserID,
            $siteUserID,
            $cpc,
            $comission,
            $clickID,
            $teaserID,
            $blockID,
            self::$Request['country_id'],
            AdvCampaignModel::DEVICE_TYPE[self::$Request['device_type']] ?? AdvCampaignModel::DEVICE_TYPE['default']
        )) {
            // Если транзакция по списанию за клик прошла успешно, изменяем счетчики лимитов Тизеров, РК
            // 1. Общий лимит РК
            BalanceModel::limitDecr(
                $teaserRkID,
                BalanceModel::LIMIT_PERIODS['absolute'],
                BalanceModel::ENTITY['rk'],
                $cpc
            );
            // 2. Общий лимит Тизера
            BalanceModel::limitDecr(
                $teaserID,
                BalanceModel::LIMIT_PERIODS['absolute'],
                BalanceModel::ENTITY['teaser'],
                $cpc
            );
            // 3. Дневной лимит РК
            BalanceModel::limitDecr(
                $teaserRkID,
                BalanceModel::LIMIT_PERIODS['daily'],
                BalanceModel::ENTITY['rk'],
                $cpc
            );
            // 4. Дневной лимит Тизера
            BalanceModel::limitDecr(
                $teaserID,
                BalanceModel::LIMIT_PERIODS['daily'],
                BalanceModel::ENTITY['teaser'],
                $cpc
            );
        }
    }

    private function custTeaserNotShowSave()
    {
        $custHkey = self::getTeaserNotShowKey();
        $teaserID = self::getTid();
        Cache::hset($custHkey, $teaserID, 1);
        // Команда expiremember только в кеййдб, редис не поддерживает
        Cache::rawcommand('expiremember', [$custHkey, $teaserID, CustomerModel::CUSTOMER_CACHE_TTL]);
    }

    public static function getTeaserNotShowKey()
    {
        return Tracker::combineKeys(
            [Tracker::TEASER_SET_PREFIX, Teaser::NOT_SHOW_KEY_PREFIX, Customer::$constToken, SiteModel::$id]
        );
    }
}
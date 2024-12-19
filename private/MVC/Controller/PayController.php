<?php

class PayController extends BaseController
{
    const COMMON_URL = 'mvc/pay/';

    const SUMM_PARAM = 'summ';
    const COMMON_ID_HASH_PARAM = 'common_id_hash';

    const EPSILON = 0.01;

    public function Run()
    {
        ini_set("display_errors", 1);
        error_reporting(E_ALL);

        return parent::Run();
    }

    public function claimAction(): array
    {
        if (!self::checkCommonIdHash(
            self::$Request[self::COMMON_ID_PARAM],
            self::$Request[self::COMMON_ID_HASH_PARAM]
        )) {
            return ['result' => 'error', 'message' => T::S('Access denied')];
        }

        $incomeToClaim = IncomeModel::getIncome(self::$Request[self::COMMON_ID_PARAM]);
        if (!($incomeToClaim > 0)) {
            return ['result' => 'error', 'message' => T::S('Nothing to claim')];
        }

        $res = ['result' => 'error', 'message' => T::S('Error changing settings. Try again later')];
        DB::transactionStart();

        if(!BalanceModel::changeBalance(self::$Request[self::COMMON_ID_PARAM], $incomeToClaim, 'income claiming', BalanceHistoryModel::TYPE_IDS[BalanceHistoryModel::CLAIM_INCOME_TYPE])) {
            return $res;
        }

        if(!IncomeModel::changeIncome(self::$Request[self::COMMON_ID_PARAM], -1 * $incomeToClaim, 'income claiming', BalanceHistoryModel::TYPE_IDS[BalanceHistoryModel::CLAIM_INCOME_TYPE])) {
            return $res;
        }

        DB::transactionCommit();

        $newSudokuBalance = BalanceModel::getBalance(self::$Request[self::COMMON_ID_PARAM]);
        return [
            'result' => 'success',
            'message' => T::S('success'),
            'SUDOKU_BALANCE' => $newSudokuBalance,
            'SUDOKU_TOP' => BalanceModel::getTopByBalance($newSudokuBalance),
            'rewards' => IncomeModel::getIncome(self::$Request[self::COMMON_ID_PARAM])
        ];
    }

    /**
     * Создает транзакцию на новый платеж, получает от yumoney ссылку на оплату, возвращает клиенту ссылку или ошибку
     * @return array|string
     */
    public function payAction()
    {
        $badResult = ['result' => 'error', 'message' => T::S('Ошибка создания нового платежа')];
        $summ = self::$Request[self::SUMM_PARAM];
        $commonId = self::$Request[self::COMMON_ID_PARAM];

        $transaction = PaymentModel::new(
            [PaymentModel::SUMM_FIELD => $summ, PaymentModel::COMMON_ID_FIELD => $commonId]
        );

        if (!$transaction->save()) {
            return $badResult;
        }

        $formData = [
            'receiver' => '4100138808308',
            'label' => $transaction->_id,
            'sum' => $transaction->_summ,
            'quickpay-form' => 'button',
        ];

        $response = self::makeRequest('https://yoomoney.ru/quickpay/confirm', [], $formData);

        $redirectUrl = self::getLocation($response['headers'] ?? []);
        if (!$redirectUrl) {
            return $badResult;
        }

        return ['result' => 'success', 'location' => $redirectUrl, 'response' => $response];
    }

    private static function getLocation(array $headers): string
    {
        foreach ($headers as $header) {
            if (stripos($header, 'location:') !== false) {
                return substr($header, 10);
            }
        }

        return '';
    }

    protected static function makeRequest(
        string $url,
        array $params = [],
        array $post = []
    ): array {
        $opts = [
            "http" => [
                    "method" => (!empty($post)) ? "POST" : "GET",
                    "header" => "Accept-language: en\r\n"
                        . "Referer: {$_SERVER['HTTP_REFERER']}\r\n" // https://xn--d1aiwkc2d.club/ todo вынести в константу или переменную
                        . ($post ? "Content-Type: application/x-www-form-urlencoded\r\n" : ''),
                ]
                + ((!empty($post)) ? ['content' => http_build_query($post)] : [])
        ];

        $context = stream_context_create($opts);

        $res = file_get_contents(
            $url
            . ($params
                ? ('?' . implode('&', array_map(fn($param, $value) => "$param=$value", array_keys($params), $params)))
                : ''
            ),
            false,
            $context
        );

        return ['result' => $res, 'headers' => $http_response_header];
    }

    public function successAction(): string
    {
        // Сохраняет в кеш запрос и возвращает предыдущий запрос
        $res = Cache::get('yumoney');
        Cache::setex('yumoney', 3600, json_encode(self::$Request + ['method' => 'success'], JSON_UNESCAPED_UNICODE));

        if ($transactionId = (self::$Request['label'] ?? false)) {
            $transaction = PaymentModel::getOneO($transactionId);
            if ($transaction) {
                // Транзакция найдена

                /** @var float $summConfirmed */
                $summConfirmed = self::$Request['amount'] ?? 0;

                if (
                    $transaction->_status === PaymentModel::INIT_STATUS
                    && self::checkSum($transaction->_summ, $summConfirmed, 0.03)
                ) {
                    // Статус транзакции и сумма соответствуют
                    if (BalanceModel::changeBalance(
                        $transaction->_common_id,
                        ceil($transaction->_summ / 10),
                        T::S('Coins purchased'),
                        BalanceHistoryModel::TYPE_IDS[BalanceHistoryModel::DEPOSIT_TYPE],
                    )) {
                        $transaction->_status = PaymentModel::COMPLETE_STATUS;
                    } else {
                        $transaction->_status = PaymentModel::FAIL_STATUS;
                    }

                    $transaction->save();
                } else {
                    $transaction->_status = PaymentModel::BAD_CONFIRM_STATUS;
                    $transaction->save();
                    $badConfirm = PaymentModel::new(
                        [
                            PaymentModel::STATUS_FIELD => PaymentModel::BAD_CONFIRM_STATUS,
                            PaymentModel::REF_ID_FIELD => $transactionId,
                            PaymentModel::COMMON_ID_FIELD => 0,
                            PaymentModel::SUMM_FIELD => $summConfirmed,
                        ]
                    );
                    $badConfirm->save();
                }
            }
        }

        Tg::botSendMessage(
            json_encode(
                ['request' => self::$Request, 'transaction' => $transaction, 'badConfirm' => $badConfirm ?? false],
                JSON_UNESCAPED_UNICODE
            ),
            null,
            Game::$gameName
        );

        return $res;
    }


    private static function checkSum(float $summ, float $summConfirmed, float $comsa = 0.03): bool
    {
        return abs(($summ - $summConfirmed) / $summ - $comsa) < self::EPSILON;
    }

    public static function checkCommonIdHash(int $commonId, string $commonIdHash): bool
    {
        $salt = Config::$envConfig['SALT'];

        return $commonIdHash === md5($commonId . $salt);
    }

    public static function getCommonIdHash(int $commonId): string
    {
        $salt = Config::$envConfig['SALT'];

        return md5($commonId . $salt);
    }
}
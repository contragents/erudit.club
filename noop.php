<?php

ini_set("display_errors", 1);
error_reporting(E_ALL);

const BASE_CROSS_COURSE = 7.43;
const INIT_USDT = 1000.0;
const CURRENCIES = ['usdt'=>'usdt', 'ton' => 'ton'];
const POOL_COMMISSION = 0.002; // 0,2%
const NUM_CYCLES = 1000;

$currentCC = BASE_CROSS_COURSE;
$currentUSDT = INIT_USDT;
$currentTon = INIT_USDT / BASE_CROSS_COURSE;
$tonSellClients = 0;
// Повышательный тренд
for ($i = 0; $i <= NUM_CYCLES; $i++) {
    $currencyToSell = array_rand(CURRENCIES); // что меняем

    $amountToChange = mt_rand(1, 1000) / 100; // сколько меняем
    $amountToChange = 1;

    if ($currencyToSell == CURRENCIES['ton']) {
        $tonSellClients++;
        // $amountToChange = $amountToChange / $currentCC; // коэффициент КК
        $currentTon += $amountToChange;

        $currentCC = $currentUSDT / $currentTon;

        $usdtToGet = $amountToChange * $currentCC * (1 - POOL_COMMISSION);
        $currentUSDT -= $usdtToGet;
    } else {
        $currentUSDT += $amountToChange;

        $currentCC = $currentUSDT / $currentTon;

        $tonToGet = ($amountToChange / $currentCC) * (1 - POOL_COMMISSION);
        $currentTon -= $tonToGet;
    }

    // $currentCC = $currentUSDT / $currnetTon;
}
?>
<table>
    <tr>
    <td>Stage</td>
    <td>USDT in pool</td>
    <td>TON in pool</td>
    <td>Course</td>
    <td>Pool value</td>
    </tr>
    <tr>
        <td>Initial</td>
        <td><?= INIT_USDT ?></td>
        <td><?= INIT_USDT / BASE_CROSS_COURSE ?></td>
        <td><?= BASE_CROSS_COURSE ?></td>
        <td> <?= '$' . (INIT_USDT + (INIT_USDT / BASE_CROSS_COURSE) * BASE_CROSS_COURSE) ?></td>
    </tr>
    <tr>
        <td>Final</td>
        <td><?= $currentUSDT . " (" . (NUM_CYCLES - $tonSellClients) . " selling clients)" ?></td>
        <td><?= $currentTon . " ($tonSellClients selling clients)" ?></td>
        <td><?= $currentCC ?></td>
        <td> <?= '$' . ($currentUSDT + $currentTon * $currentCC) ?></td>
    </tr>
</table>
<?php
exit;

$url = 'https://pro-api.coinmarketcap.com/v1/cryptocurrency/quotes/latest';
$parameters = [
    'slug' => 'toncoin',
    //'start' => '1',
    //'limit' => '5000',
    //'convert' => 'USD'
];

$headers = [
    'Accepts: application/json',
    'X-CMC_PRO_API_KEY: 052ca221-e987-4c2d-9c69-29d264967606'
];
$qs = http_build_query($parameters); // query string encode the parameters
$request = "{$url}?{$qs}"; // create the request URL


$curl = curl_init(); // Get cURL resource
// Set cURL options
curl_setopt_array(
    $curl,
    array(
        CURLOPT_URL => $request,            // set the request URL
        CURLOPT_HTTPHEADER => $headers,     // set the headers
        CURLOPT_RETURNTRANSFER => 1         // ask for raw response instead of bool
    )
);

$response = curl_exec($curl); // Send the request, save the response
print json_encode(json_decode($response), JSON_UNESCAPED_UNICODE + JSON_PRETTY_PRINT); // print json decoded response
curl_close($curl); // Close request
exit;

const FIND_VALUES = ['"510000000"',/* '"520000000"'*/];
const TRANSACTIONS_URL = 'https://toncenter.com/api/v3/transactions?workchain=0&start_utime=';
const URL_PART_2 = '&sort=desc&limit=256';

$numBytesReceived = 0;
for ($i = 0; $i <= 60; $i++) {
    $res = file_get_contents(TRANSACTIONS_URL . (time() - $i * 15) . URL_PART_2);

    foreach (FIND_VALUES as $value) {
        if (strpos($res, $value)) {
            print $res . $value;

            file_put_contents("found_$value.txt", $res);
            exit;
        }
    }

    $numBytesReceived += strlen($res);
    print $i;

    sleep(3);
}

print "NOT FOUND " . $numBytesReceived;

exit;

class Test
{
    const TEST = 'test3';
}

class Test4
{
    const TEST = 'test4';
}

class RunTest
{
    public $test = Test::class;

    public function Run()
    {
        print $this->test::TEST;
    }
}

print (new RunTest())->test::TEST;
(new RunTest())->Run();


exit;
print implode(', ', []);
exit;
print_r($_SERVER);
var_export(date('Y-m-d') < '2022-11-06');
exit;
ini_set("display_errors", 1);
error_reporting(E_ALL);

$timestamp = '2023-06-09 20:40:16.665770';
print_r(['ts' => strtotime($timestamp), 'date' => date('Y-m-d H:i:s', strtotime($timestamp))]);
exit();
require_once 'autoload.php';
Config::$config = ['cache' => ['HOST' => 'localhost', 'PORT' => 6389]];
require_once 'yandex1.0.1.1/php/EruditGame.php';

foreach (Cache::hgetall(Erudit\Game::LOG_BOT_ERRORS_KEY) ?: [] as $error) {
    print_r($error);
}

foreach (Cache::hgetall(Erudit\Game::BOT_ERRORS_KEY) ?: [] as $error) {
    print_r($error);
}

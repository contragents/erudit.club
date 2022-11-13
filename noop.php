<?php
print json_encode('ООО "Марксвебб Ранк энд Репорт"');
use function json_encode;
print json_encode('ООО "Марксвебб Ранк энд Репорт"');
print json_encode('ООО "Марксвебб Ранк энд Репорт"', JSON_HEX_QUOT);
print json_encode('ООО "Марксвебб Ранк энд Репорт"', JSON_HEX_QUOT);
exit();
function mp(){
	print __CLASS__;
	print __METHOD__;
}
class tst {
	public static function ttt(){
	mp();
	}
}

tst::ttt();
exit();
print var_dump(0.0 === 0); exit();
print long2ip(2678104481);
exit();
ini_set("display_errors", 1); error_reporting(E_ALL);
print date('H').'-'.date('H', strtotime('-1 hour'));
print_r($_SERVER); exit();
print phpinfo(); exit();
print_r($_SERVER); exit();
$origin = new DateTime('2021-12-16');
$target = new DateTime('2021-12-23');
$interval = $origin->diff($target);
echo $interval->format('%a дней');
exit();
$method = "aes128";
$iv_length = openssl_cipher_iv_length($method);
$iv = openssl_random_pseudo_bytes($iv_length);
print base64_encode($iv); exit();
$val1 = '551';
$val2 = '0551';
print (0551 == 551 ? 'true' : 'false'); exit();

include ('yandex1.0.1.1/php/hash_str_2_int.php');
print hash_str_2_int('00035901f58bda321b3189a340e96583'); exit();
print phpinfo(); exit();
print mb_strpos('exit', 'ex',0,'UTF-8');
print mb_strlen('ex', 'UTF-8');
print $_SERVER['HTTP_ORIGIN']; exit();
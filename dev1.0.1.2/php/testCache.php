<?php
use \Dadata\Cache;
use \Dadata\Prizes;
spl_autoload_register(function ($class_name) {
    $Exploded_class = explode('\\', $class_name);
    include $Exploded_class[count($Exploded_class) - 1] . 'LangProvider.php';
});
//$p = \Dadata\Cache::getInstance();
Cache::setex('erudit_test',1,'WoW!');

print Cache::get('erudit_test');
sleep(3);
print '!!!!!!!!!!!!!!!!'.Cache::get('erudit_test');

print 'PRIZES-'.Prizes::checkDayWordLenRecord('пирамида13');


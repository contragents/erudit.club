<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body>    
<?php
ini_set("display_errors", 1); error_reporting(E_ALL);

include '../vendor/DB/CacheProvider.php';
$p = \Dadata\Cache::getInstance();

print print_r(unserialize($p->redis->get("erudit.{$_GET['user']}")),true);

// Open the file using the HTTP headers set above


?>
</body>
</html>
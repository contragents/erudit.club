<?php
function xcache_isset($key) {
    return apcu_exists ($key);
}

function xcache_set($key,$value,$ttl = 0) {
    return apcu_store ($key, $value, $ttl);//Написать про кэш!!!
}

function xcache_inc($key,$inc=1) {
    return apcu_inc($key,$inc);//Написать про кэш!!!
}

function xcache_get($key) {
    return apcu_fetch($key);//Написать про кэш!!!
}

function xcache_get_from_cli($key) {
    return file_get_contents('https://contragents.ru/modules/utils/apcu_get.php?key='.$key);
}

function mysql_pconnect($SQL_HOST=SQL_HOST,$SQL_USER=SQL_USER,$SQL_PASSWORD=SQL_PASSWORD,$DB=SQL_DB) {

    $connection = mysqli_init();

    $connection->options(MYSQLI_OPT_CONNECT_TIMEOUT, 1);

    $connection->options(MYSQLI_OPT_READ_TIMEOUT, 1);

    $connection->real_connect($SQL_HOST,$SQL_USER,$SQL_PASSWORD, $DB, SOCKET);
    
    return $connection;
}

function mysql_insert_id($link='') {
    if ($link == '') {
        GLOBAL $UTMLink;
        return mysqli_insert_id($UTMLink);
    }
    else
        return mysqli_insert_id($link);
}

function mysql_connect($SQL_HOST=SQL_HOST,$SQL_USER=SQL_USER,$SQL_PASSWORD=SQL_PASSWORD,$DB=SQL_DB) {

    return $link = mysqli_connect($SQL_HOST, $SQL_USER, $SQL_PASSWORD, $DB);
}

function mysql_error() {
    return mysqli_connect_error();
}

function mysql_select_db($SQL_DB) {
    GLOBAL $UTMLink;
    return mysqli_select_db($UTMLink,$SQL_DB);
}

function mysql_query($query,$link='') {
    if ($link=='') {
        GLOBAL $UTMLink;
        $res = mysqli_query($UTMLink,$query);
        if ($UTMLink->errno) {
            //$UTMLink=mysql_pconnect();//реконнект
            $UTMLink = new mysqli(SQL_HOST, SQL_USER, SQL_PASSWORD, SQL_DB);
            $res = mysqli_query($UTMLink,$query);
        }
        return $res;
    }
    else
        return mysqli_query($link,$query);
    
    //старая версия без реконнекта
    if ($link=='') {
        GLOBAL $UTMLink;
        return mysqli_query($UTMLink,$query);
    }
    else
        return mysqli_query($link,$query);
}

function mysql_num_rows($res='') {
    return mysqli_num_rows($res);
}

function mysql_real_escape_string($string) {
    GLOBAL $UTMLink;
    return mysqli_real_escape_string($UTMLink,$string);
}

function mysql_result($res, $row, $field=0) {
    if (is_object($res))
        if (method_exists($res,'data_seek')) {
            $res->data_seek($row);
            $datarow = $res->fetch_array();
            if (isset($datarow[$field]))
                return $datarow[$field];
            else
                return false;
        }
        else
            return false;
}

function mysql_fetch_assoc($res) {
    return mysqli_fetch_assoc($res);
}

function mysql_affected_rows() {
    GLOBAL $UTMLink;
    return mysqli_affected_rows($UTMLink);
}

//ini_set("display_errors", 1); error_reporting(E_ALL);
define('SQL_HOST', 'localhost');
define('SQL_USER', 'ili');
define('SQL_PASSWORD', 'Aw!gP!mx_Jh6M.V');
define('SQL_DB', 'erudit');
define ('SOCKET', '/var/lib/mysql/mysql.sock');
$UTMLink = new mysqli(SQL_HOST, SQL_USER, SQL_PASSWORD, SQL_DB);
/*f ($mysqli->connect_errno) {
    echo "Не удалось подключиться к MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}
echo $mysqli->host_info . "\n";
exit();*/
//$UTMLink=mysql_pconnect(SQL_HOST,SQL_USER,SQL_PASSWORD,SQL_DB) or die(mysql_error());
?>
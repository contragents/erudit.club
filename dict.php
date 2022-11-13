<?php
//ini_set("display_errors", 1); error_reporting(E_ALL);
if ($_GET['word'] == '') {
    include ('dict_2_letters.php');
    exit();
}
$title = 'Игра Эрудит.CLUB Слова из '. ((isset($_GET['strlen']) && ($_GET['strlen']>2) ) ? $_GET['strlen'] : двух). ' букв';
include ('tpl/main_header.php');
?>
<h1><?=$_GET['word']?></h1>
<?php
include('yandex1.0.1.1/php/word.php');
include ('tpl/main_footer.php');
exit();

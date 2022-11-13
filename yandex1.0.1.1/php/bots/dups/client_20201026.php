<?php
$_COOKIE['erudit_user_session_ID'] = 'bot#1';
$resp = ['gameState' => 1];
$zaprosNum = 1;
ob_start();
$resp = include '../new_game.php';
$obj->__destruct();
$resp = include '../game_starter.php';
$obj->__destruct();

while ($resp['gameState'] !== 'gameResults') {
ob_end_clean();
ob_start();
$_GET['queryNumber'] = $zaprosNum++;
$resp = include '../status_checker.php';
$obj->__destruct();
$resp = json_decode(ob_get_contents(), true);
ob_end_clean();
print $resp['gameState'];
print "\n";
print $resp['query_number']; 
print "\n";
ob_start();
if ($resp['gameState'] == 'myTurn') {
    //ob_end_clean();
    //print_r($resp);
    $turn_submit = sendResponse($resp);
    $turn_submit = json_decode($turn_submit,true);
    
}
sleep(10);
}
ob_end_clean();

function sendResponse(&$data) {
    if (isset($data['desk']))
        $_POST['cells'] = json_encode($data['desk']);
    else
        $_POST['cells'] = json_encode(\Lang\Ru::init_desk());
    $resp = include '../turn_submitter.php';
    $obj->__destruct();
    return $resp;
}
<?php

include 'func/sqlite.php';

// Returns json formatted Object like this:
/*
 * {"seq":4,"sounds":[{"id":0,"seq":1,"key":"...","title":"..."},
*/
// For sounds with seq greater than param seq

$cmdSeq = 0;
if(isset($_GET['seq']) and !empty($_GET['seq'])){
    $cmdSeq = $_GET['seq'];
}

$cmdAppVersion = 0;
if(isset($_GET['v']) and !empty($_GET['v'])){
    $cmdAppVersion = $_GET['v'];
}


$db = DB::getInstance();
$result = array(
    "seq"     => $db->getSeq(), 
    "sounds"  => $db->findBySeqGreaterThan($cmdSeq)
);

if($cmdAppVersion > 0){
    $result["deletes"] = $db->findDeleteBySeqGreaterThan($cmdSeq);
}

header('Content-Type: application/json');
echo json_encode($result);




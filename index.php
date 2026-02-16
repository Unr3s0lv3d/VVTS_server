<?php

require_once dirname(__FILE__) . "/autoload.php";

use \VVTS\Classes\SqliteTokenStorage;

header("Content-Type: application/json");

$oStorage = new SqliteTokenStorage();

if (isset($_GET['create_token'])) {
    $szToken = base64_encode(random_bytes(24));
    $oStorage->StoreToken($szToken, 300000);
    $oObj = new stdClass;
    $oObj->status = "OK";
    $oObj->error = "";
    $oObj->token = $szToken;
    echo json_encode($oObj);
} else if (isset($_GET['query'])) {
    $szToken = $_GET['query'];
    $eFlag = $oStorage->RetrieveFlag($szToken);
    $oObj = new stdClass;
    if ($eFlag === STATUS_NONEXISTENT) {
        $oObj->status = "Error";
        $oObj->error = "Token does not exist";
    } else {
        $oObj->status = "OK";
        $oObj->error = "";
        switch ($eFlag) {
            case STATUS_NOT_FLAGGED: $oObj->flagged = "NOT_FLAGGED"; break;
            case STATUS_FLAGGED: $oObj->flagged = "FLAGGED"; break;
        }
    }
    echo json_encode($oObj);
} else if (isset($_GET['flag'])) {
    $szToken = $_GET['flag'];
    $oStorage->FlagToken($szToken);
    $oObj = new stdClass;
    $oObj->status = "OK";
    $oObj->error = "";
    echo json_encode($oObj);
} else {
    echo "{}";
}

?>
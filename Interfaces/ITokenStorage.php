<?php

namespace VVTS\Interfaces;

require_once(dirname(__FILE__) . "/../autoload.php");

define("STATUS_NONEXISTENT", 0);
define("STATUS_NOT_FLAGGED", 1);
define("STATUS_FLAGGED", 2);

interface ITokenStorage {
    function StoreToken($szToken, $dwLifetime);
    function FlagToken($szToken);
    function RetrieveFlag($szToken);
}

?>
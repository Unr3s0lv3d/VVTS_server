<?php

namespace VVTS\Classes;

require_once(dirname(__FILE__) . "/../autoload.php");

use \SQLite3;
use \VVTS\Interfaces\ITokenStorage;

class SqliteTokenStorage implements ITokenStorage {
    var $hSqlite;

    function __construct() {
        $this->hSqlite = new SQLite3(sys_get_temp_dir() . "/vvts_tokenstorage.sqlite");
        $this->hSqlite->query(
            "CREATE TABLE IF NOT EXISTS `tokens` (" .
	            "`token`	TEXT NOT NULL UNIQUE," .
	            "`status`	INTEGER NOT NULL," .
	            "`expiry`	INTEGER NOT NULL," .
	            "`ip`		TEXT," .
	            "PRIMARY KEY(`token`)" .
            ");"
        );
        @$this->hSqlite->query("ALTER TABLE `tokens` ADD COLUMN `ip` TEXT;");
        $this->hSqlite->query("CREATE UNIQUE INDEX IF NOT EXISTS `token_index` ON `tokens` (`token`);");
        $this->hSqlite->query("CREATE INDEX IF NOT EXISTS `expiry_index` ON `tokens` (`expiry`);");
    }

    function CleanExpired() {
        $dwNow = intval(microtime(1) / 1000);
        $hStatement = $this->hSqlite->prepare("DELETE FROM `tokens` where `expiry` < :now;");
        $hStatement->bindValue(":now", $dwNow);
        $hStatement->execute();
    }

    function StoreToken($szToken, $dwLifetime) {
        $hStatement = $this->hSqlite->prepare("INSERT INTO `tokens` (`token`, `status`, `expiry`) VALUES (:token, :status, :expiry);");
        $dwExpiry = intval(microtime(1) / 1000) + $dwLifetime;
        $hStatement->bindValue(":token", $szToken);
        $hStatement->bindValue(":status", STATUS_NOT_FLAGGED);
        $hStatement->bindValue(":expiry", $dwExpiry);
        $hStatement->execute();
        $this->CleanExpired();
    }

    function FlagToken($szToken, $szIp) {
        $hStatement = $this->hSqlite->prepare("UPDATE `tokens` SET `status` = :status, `ip` = :ip WHERE `token` = :token;");
        $hStatement->bindValue(":status", STATUS_FLAGGED);
        $hStatement->bindValue(":ip", $szIp);
        $hStatement->bindValue(":token", $szToken);
        $hStatement->execute();
        $this->CleanExpired();
    }

    function RetrieveFlag($szToken) {
        $hStatement = $this->hSqlite->prepare("SELECT `status`, `ip` FROM `tokens` WHERE `token` = :token;");
        $hStatement->bindValue(":token", $szToken);
        $hResult = $hStatement->execute();
        $adwResult = $hResult->fetchArray(SQLITE3_NUM);
        $this->CleanExpired();
        if ($adwResult === false) {
            return false;
        }
        $oResult = new \stdClass;
        $oResult->status = intval($adwResult[0]);
        $oResult->ip = $adwResult[1];
        return $oResult;
    }
}

?>
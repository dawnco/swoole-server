<?php
/**
 * @author WhoAmI
 * @date   2019-07-24
 */

define("APP_NAME", "app");
include __DIR__ . "/wmi/autoload.php";
$server = new \wmi\core\Server();
$server->start();
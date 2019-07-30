<?php
/**
 * @author WhoAmI
 * @date   2019-07-24
 */

define("APP_NAME", "app");
include __DIR__ . "/wmi/server.php";
$app = new \wmi\core\App();
$app->start();
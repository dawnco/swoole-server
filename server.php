<?php
/**
 * @author WhoAmI
 * @date   2019-07-24
 */

define("APP_NAME", "app");
include __DIR__ . "/wmi/autoload.php";
$server = new \wmi\core\Server();
$act    = $argv[1] ?? 'start';

$allow = ['start', 'reload', 'stop'];
if (in_array($act, $allow)) {
    $server->$act();
} else {
    \wmi\lib\Log::console("不支持的命令 允许", implode(",", $allow));
}


<?php
/**
 * @author WhoAmI
 * @date   2019-07-18
 */

define("ROOT", dirname(__DIR__));

spl_autoload_register(function ($class) {
    $file = ROOT . DIRECTORY_SEPARATOR . $class . '.php';
    $file = str_replace("\\", DIRECTORY_SEPARATOR, $file);
    if (is_file($file)) {
        include $file;
    }
});

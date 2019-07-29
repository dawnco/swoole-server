<?php
/**
 * @author WhoAmI
 * @date   2019-07-24
 */

namespace wmi\lib;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use wmi\core\Config;

class Log {

    protected static $instance = null;

    protected static function instance() {
        // create a log channel
        static::$instance = new Logger('name');

        $level = Logger::ERROR;
        switch (Config::get("app", 'log_level')) {
            case 'debug':
                $level = Logger::DEBUG;
            break;
            case 'notice':
                $level = Logger::NOTICE;
            break;
            case 'info':
                $level = Logger::INFO;
            break;
            case 'error':
                $level = Logger::ERROR;
            break;
        }
        static::$instance->pushHandler(new StreamHandler(ROOT . "/data/log/" . date("Y-m-d") . ".log", $level));

    }

    /**
     * @param $name
     * @param $arguments
     */
    public static function __callStatic($name, $arguments) {
        if (!static::$instance) {
            static::instance();
        }
        static::$instance->$name(...$arguments);
    }

    public static function console($msg) {
        echo date("Y - m - d H:i:s# ");
        echo $msg;
        echo "\n";
    }

}
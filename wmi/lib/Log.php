<?php
/**
 * @author WhoAmI
 * @date   2019-07-24
 */

namespace wmi\lib;

use wmi\core\Config;
use wmi\lib\command\Color;

class Log {

    protected static $instance = null;

    protected static function instance() {

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

    public static function console(...$args) {
        self::printMessage('console', $args);
    }

    public static function error(...$args) {
        self::printMessage('error', $args);
    }

    public static function info(...$args) {
        self::printMessage('info', $args);
    }

    public static function debug(...$args) {
        self::printMessage('debug', $args);
    }

    protected static function printMessage($type, $args) {
        echo sprintf("%s %s - %s%s",
            date("Y-m-d H:i:s"),
            strtoupper($type),
            self::printColorMessage($type, implode(" ", $args)),
            PHP_EOL
        );
    }

    protected static function printColorMessage($level, $message) {
        // 带颜色打印
        switch ($level) {
            case 'error':
                $message = Color::new(Color::FG_RED)->sprint($message);
            break;
            case 'warning':
                $message = Color::new(Color::FG_YELLOW)->sprint($message);
            break;
            case 'notice':
                $message = Color::new(Color::FG_GREEN)->sprint($message);
            break;
            //case 'info':
            //    $message = Color::new(Color::FG_BLUE)->sprint($message);
            //break;
        }

        return $message;
    }
}
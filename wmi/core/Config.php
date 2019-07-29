<?php
/**
 * @author WhoAmI
 * @date   2019-07-23
 */

namespace wmi\core;


class Config {
    private static $data = [];
    private static $file = [];

    public static function get($name) {
        self::load();

        $keys = explode(".", $name);
        $val  = self::$data;

        foreach ($keys as $v) {
            if (isset($val[$v])) {
                $val = $val[$v];
            } else {
                return null;
            }
        }
        return $val;
    }

    public static function load($force = false) {
        if (!self::$data || $force) {
            self::$data = include ROOT . "/wmi/config/config.php";
        }
    }

    public static function file($file, $force = false) {
        if (!isset(self::$file[$file]) || $force) {
            self::$file[$file] = include ROOT . "/wmi/config/$file.php";
        }
        return self::$file[$file];
    }

    public static function reload() {
        self::load(true);
        foreach (self::$file as $k => $f) {
            self::file($k, true);
        }
    }
}
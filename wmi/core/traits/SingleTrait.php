<?php
/**
 * @author WhoAmI
 * @date   2019-07-24
 */

namespace wmi\core\traits;


Trait SingleTrait {
    protected static $_instance = null;

    public static function getSingleton() {
        if (self::$_instance == null) {
            self::$_instance = new static();
        }
        return self::$_instance;
    }

}
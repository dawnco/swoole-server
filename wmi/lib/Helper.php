<?php
/**
 * @author WhoAmI
 * @date   2019-07-31
 */

namespace wmi\lib;


use wmi\lib\database\Mysql;
use wmi\lib\database\Redis;

class Helper {

    /**
     * @param string $poolName
     * @return Mysql
     */
    public static function database($poolName = 'mysql') {
        return new Mysql($poolName);
    }

    /**
     * @param string $poolName
     * @return Redis
     */
    public static function redis($poolName = 'redis') {
        return new Redis($poolName);
    }
}
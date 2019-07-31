<?php
/**
 * @author WhoAmI
 * @date   2019-07-31
 */

namespace wmi\lib\database;


use wmi\contract\Database;
use wmi\core\PoolManager;

class Redis extends Database {

    public function __call($name, $arguments) {
        return call_user_func_array([$this->link, $name], $arguments);
    }

}
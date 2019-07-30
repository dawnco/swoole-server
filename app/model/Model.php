<?php
/**
 * @author WhoAmI
 * @date   2019-07-25
 */

namespace app\model;


use wmi\core\PoolManager;

class Model {

    public function index() {
        $db = PoolManager::getConntent("mysql");
    }

}
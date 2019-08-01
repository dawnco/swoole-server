<?php
/**
 * @author WhoAmI
 * @date   2019-07-25
 */

namespace app\model;


use wmi\core\PoolManager;
use wmi\lib\Helper;

class Model {
    public function index() {
        $db = Helper::database();
    }

}